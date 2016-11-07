<?php

namespace App\Services;

use App\Events\SqsMessagesWasSynced;
use App\Exceptions\AWSSQSServerException;
use App\Exceptions\DatabaseAlreadySyncedException;
use App\Exceptions\InsertIgnoreBulkException;
use App\Exceptions\NoMessagesToSyncException;
use App\Exceptions\NoValidMessagesFromQueueException;
use App\Exceptions\QueuesMessageDeleteException;
use App\Repositories\Contracts\ActionRepositoryInterface;
use App\Repositories\Contracts\MessageRepositoryInterface;
use App\Resolvers\DifferMessageInputDataToDatabase;
use App\Resolvers\ProvidesDecodingOfSalesForceMessages;
use Aws\Sqs\Exception\SqsException;
use Illuminate\Database\QueryException;

class InboundMessagesSync
{
    const INBOUND_QUEUE = 'CRMInwardQueue';

    use DifferMessageInputDataToDatabase, ProvidesDecodingOfSalesForceMessages;

    /**
     * @var int
     */
    private $messageVisibility = 60;

    /**
     * @var array
     */
    private $availableActionList = [];

    /**
     * @var SQSClientService
     */
    private $sqs;

    /**
     * @var ActionRepositoryInterface
     */
    private $action;

    /**
     * @var MessageRepositoryInterface
     */
    private $message;

    /**
     * InboundMessagesSync constructor.
     * @param SQSClientService $sqs
     * @param ActionRepositoryInterface $action
     * @param MessageRepositoryInterface $message
     */
    public function __construct(
        SQSClientService $sqs,
        ActionRepositoryInterface $action,
        MessageRepositoryInterface $message
    ) {
        $this->sqs = $sqs;
        $this->action = $action;
        $this->message = $message;

    }

    /**
     * @param string $queueName
     */
    public function handle($queueName)
    {
        $this->availableActionList = collect($this->action->all())->pluck('id', 'name')->all();

        $queueName = (!($queueName) ? self::INBOUND_QUEUE : $queueName);

        $queueUrl = $this->getQueueUrlOrFail($queueName);
        $queueMessages = $this->collectQueueMessagesOrFail($queueUrl);
        $filteredQueueMessages = $this->removeDuplicateAndValidateIfDatabaseSynced($queueMessages);
        $messagesForInsert = $this->buildMessagesPayloadForInsertOrFail($filteredQueueMessages);

        $this->insertBulkMessagesOrFail($messagesForInsert);
        $this->deleteAwsQueueMessages($queueUrl, $filteredQueueMessages);

        event(new SqsMessagesWasSynced(
            collect($filteredQueueMessages)->pluck('MessageId')->toArray()
        ));
    }

    /**
     * @param int $total
     * @return $this
     */
    public function messageVisibility($total = 60)
    {
        $this->messageVisibility = $total;

        return $this;
    }

    /**
     * @param string $queueUrl
     * @return array
     * @throws NoMessagesToSyncException
     */
    private function collectQueueMessagesOrFail($queueUrl)
    {
        $collectedMessages = $this->getAvailableQueueMessages($queueUrl);

        $messages = collect($collectedMessages)->unique('MessageId')->toArray();

        if (!$messages) {
            throw new NoMessagesToSyncException('No available Queues Messages for sync.');
        }

        return $messages;
    }

    /**
     * @param string $queueName
     * @return string
     * @throws AWSSQSServerException
     */
    private function getQueueUrlOrFail($queueName)
    {
        try {
            return $this->sqs->client()->getQueueUrl(['QueueName' => $queueName])->get('QueueUrl');
        } catch (SqsException $exception) {
            throw new AWSSQSServerException($this->extractSQSMessage($exception->getMessage()));
        }
    }

    /**
     * @param string $queueUrl
     * @return array
     */
    private function getAvailableQueueMessages($queueUrl)
    {
        $messages = [];

        while ($availableMessage = $this->getAQueueMessage($queueUrl)) {
            $messages[] = [
                'MessageId' => $availableMessage['MessageId'],
                'Body' => $availableMessage['Body'],
                'ReceiptHandle' => $availableMessage['ReceiptHandle']
            ];
        }

        return $messages;
    }

    /**
     * @param string $url
     * @return array
     */
    private function getAQueueMessage($url)
    {
        $message = $this->sqs->client()
            ->receiveMessage(['QueueUrl' => $url, 'VisibilityTimeout' => $this->messageVisibility])
            ->get('Messages');

        return array_first($message);
    }

    /**
     * @param array $queueMessages
     * @return array
     * @throws DatabaseAlreadySyncedException
     */
    private function removeDuplicateAndValidateIfDatabaseSynced(array $queueMessages)
    {
        $filteredMessages = $this->computeDifference(collect($queueMessages)->pluck('MessageId')->toArray());

        $result = collect($queueMessages)->whereIn('MessageId', $filteredMessages)->toArray();

        if (empty($result)) {
            throw new DatabaseAlreadySyncedException('Database already synced.');
        }

        return $result;
    }

    /**
     * @param array $messages
     * @return array
     * @throws NoValidMessagesFromQueueException
     */
    private function buildMessagesPayloadForInsertOrFail($messages)
    {
        $dateNow = date('Y-m-d');

        $result = collect($messages)
            ->map(function ($message) use ($dateNow) {

                $messageContent = $this->validateMessageContent($message['Body']);

                if ($messageContent) {
                    return [
                        'message_id' => $message['MessageId'],
                        'action_id' => $this->getActionIdFromMessageContent($message['Body']),
                        'message_content' => $this->cleanMessageContentForInsert($message['Body']),
                        'completed' => 'N',
                        'create_at' => $dateNow,
                        'updated_at' => $dateNow
                    ];
                }
            })->reject(function ($message) {
                return empty($message);
            })->toArray();

        if (empty($result)) {
            throw new NoValidMessagesFromQueueException('No valid messages from queue to sync.');
        }

        return $result;
    }

    /**
     * @param string $messageContent
     * @return mixed
     */
    private function getActionIdFromMessageContent($messageContent)
    {
        $messageContent = $this->deCodeSalesForceMessage($messageContent);

        return $this->availableActionList[$messageContent['op']];
    }

    /**
     * @param string $message
     * @return string
     */
    private function cleanMessageContentForInsert($message)
    {
        return str_replace('"', '\'', $message);
    }

    /**
     * @param string $messageContent
     * @return bool|string
     */
    private function validateMessageContent($messageContent)
    {
        $decodedMessage = json_decode($messageContent, true);

        if (!$decodedMessage) {
            return false;
        }

        if (!array_key_exists('op', $decodedMessage)) {
            return false;
        }

        if ($decodedMessage['op'] == '') {
            return false;
        }

        if (!array_key_exists($decodedMessage['op'], $this->availableActionList)) {
            return false;
        }

        return true;
    }

    /**
     * @param array $insertPayload
     * @throws DatabaseAlreadySyncedException
     * @throws InsertIgnoreBulkException
     */
    public function insertBulkMessagesOrFail($insertPayload)
    {
        try {
            $this->message->insertIgnoreBulk($insertPayload);
        } catch (QueryException $exception) {
            throw new InsertIgnoreBulkException('Insert Ignore Bulk Error.');
        }
    }


    /**
     * @param string $queueUrl
     * @param array $messages
     * @throws QueuesMessageDeleteException
     */
    private function deleteAwsQueueMessages($queueUrl, $messages)
    {
        try {
            $receiptHandles = collect($messages)->pluck('ReceiptHandle')->toArray();
            collect($receiptHandles)->each(function ($receiptHandle) use ($queueUrl) {
                $this->sqs->client()->deleteMessage(['QueueUrl' => $queueUrl, 'ReceiptHandle' => $receiptHandle]);
            });
        } // @codeCoverageIgnoreStart
        catch (SqsException $exception) {
            throw new QueuesMessageDeleteException($this->extractSQSMessage($exception->getMessage()));
        }// @codeCoverageIgnoreEnd
    }

    /**
     * @param string $message
     * @return string
     */
    private function extractSQSMessage($message)
    {
        $message = explode('<Message>', $message);
        $message = explode('</Message>', $message[1]);

        return reset($message);
    }
}
