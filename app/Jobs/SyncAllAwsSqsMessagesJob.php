<?php

namespace App\Jobs;

use App\Events\SqsMessagesWasSynced;
use App\Jobs\Exceptions\AWSSQSServerException;
use App\Jobs\Exceptions\DatabaseAlreadySyncedException;
use App\Jobs\Exceptions\InsertIgnoreBulkException;
use App\Jobs\Exceptions\NoMessagesToSyncException;
use App\Jobs\Exceptions\NoValidMessagesFromQueueException;
use App\Repositories\Contracts\ActionRepositoryInterface;
use App\Repositories\Contracts\MessageRepositoryInterface;
use App\Resolvers\DifferMessageInputDataToDatabase;
use App\Resolvers\ProvidesUnSerializationOfSalesForceMessages;
use App\Services\SQSClientService;
use Aws\Sqs\Exception\SqsException;
use Illuminate\Database\QueryException;

class SyncAllAwsSqsMessagesJob extends Job
{
    const INBOUND_QUEUE = 'CRMInwardQueue';

    use DifferMessageInputDataToDatabase, ProvidesUnSerializationOfSalesForceMessages;

    /**
     * @var int
     */
    private $messageVisibility;

    /**
     * @var string
     */
    private $queueName;

    /**
     * @var array
     */
    private $availableActionList = [];

    /**
     * SyncAwsSqsMessagesJob constructor.
     * @param string $queueName
     * @param int $messageVisibility
     */
    public function __construct($queueName = null, $messageVisibility = 2000)
    {
        $this->messageVisibility = $messageVisibility;
        $this->queueName = $queueName ?? self::INBOUND_QUEUE;
    }

    /**
     * @param SQSClientService $sqs
     * @param ActionRepositoryInterface $action
     * @param MessageRepositoryInterface $message
     */
    public function handle(SQSClientService $sqs, ActionRepositoryInterface $action, MessageRepositoryInterface $message)
    {
        $this->availableActionList = collect($action->all())->pluck('id', 'name')->all();

        $queueMessages = $this->collectQueueMessagesOrFail($sqs, $this->queueName);
        $filteredQueueMessages = $this->removeDuplicateAndValidateIfDatabaseSynced($queueMessages);
        $messagesForInsert = $this->buildMessagePayloadForInsertOrFail($filteredQueueMessages);

        $this->insertBulkMessagesOrFail($message, $messagesForInsert);

        event(new SqsMessagesWasSynced(
            collect($filteredQueueMessages)->pluck('MessageId')->toArray()
        ));
    }

    /**
     * @param SQSClientService $sqs
     * @param string $queueName
     * @return array
     * @throws NoMessagesToSyncException
     */
    private function collectQueueMessagesOrFail(SQSClientService $sqs, $queueName)
    {
        $queueUrl = $this->getQueueUrlOrFail($sqs, $queueName);
        $collectedMessages = $this->getAvailableQueueMessages($sqs, $queueUrl);

        $messages = collect($collectedMessages)->unique('MessageId')->toArray();

        if (!$messages) {
            throw new NoMessagesToSyncException('No available Queues Messages for sync.');
        }

        return $messages;
    }

    /**
     * @param SQSClientService $sqs
     * @param string $queueName
     * @return mixed|null
     * @throws AWSSQSServerException
     */
    private function getQueueUrlOrFail(SQSClientService $sqs, $queueName)
    {
        try {
            return $sqs->client()->getQueueUrl(['QueueName' => $queueName])->get('QueueUrl');
        } catch (SqsException $exception) {
            throw new AWSSQSServerException($this->extractSQSMessage($exception->getMessage()));
        }
    }

    /**
     * @param SQSClientService $sqs
     * @param string $queueUrl
     * @return array
     */
    private function getAvailableQueueMessages(SQSClientService $sqs, $queueUrl)
    {
        $messages = [];

        while ($availableMessage = $this->getAQueueMessage($sqs, $queueUrl)) {
            $messages[] = [
                'MessageId' => $availableMessage['MessageId'],
                'Body' => $availableMessage['Body']
            ];
        }

        return $messages;
    }

    /**
     * @param SQSClientService $sqs
     * @param string $url
     * @return array
     */
    private function getAQueueMessage(SQSClientService $sqs, $url)
    {
        $message = $sqs->client()
            ->receiveMessage(['QueueUrl' => $url, 'VisibilityTimeout' => 5])
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
     * @param string $message
     * @return array
     * @throws NoValidMessagesFromQueueException
     */
    private function buildMessagePayloadForInsertOrFail($message)
    {
        $result = collect($message)
            ->map(function ($message) {

                $messageContent = $this->validateAndSerializeMessageContent($message['Body']);

                if ($messageContent) {
                    return [
                        'message_id' => $message['MessageId'],
                        'action_id' => $this->getActionIdFromMessageContent($messageContent),
                        'message_content' => $messageContent,
                        'completed' => 'N'
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
        $messageContent = $this->unSerializeSalesForceMessage($messageContent);

        return $this->availableActionList[$messageContent['op']];
    }

    /**
     * @param string $messageContent
     * @return bool|string
     */
    private function validateAndSerializeMessageContent($messageContent)
    {
        $messageContent = str_replace('"', '\'', $messageContent);
        $unserializedMessage = @unserialize(str_replace('\'', '"', $messageContent));

        if (!$unserializedMessage) {
            return false;
        }

        if (!array_key_exists('op', $unserializedMessage)) {
            return false;
        }

        if ($unserializedMessage['op'] == '') {
            return false;
        }

        if (!array_key_exists($unserializedMessage['op'], $this->availableActionList)) {
            return false;
        }

        return $messageContent;
    }

    /**
     * @param MessageRepositoryInterface $message
     * @param array $insertPayload
     * @throws DatabaseAlreadySyncedException
     * @throws InsertIgnoreBulkException
     */
    public function insertBulkMessagesOrFail(MessageRepositoryInterface $message, $insertPayload)
    {
        try {
            $message->insertIgnoreBulk($insertPayload);
        } catch (QueryException $exception) {
            throw new InsertIgnoreBulkException('Insert Ignore Bulk Error: ' . $exception->getMessage());
        }
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
