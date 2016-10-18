<?php

namespace App\Jobs;

use App\Events\SqsMessagesWasSynced;
use App\Jobs\Exceptions\AWSSQSServerException;
use App\Jobs\Exceptions\DatabaseAlreadySyncedException;
use App\Jobs\Exceptions\EmptyQueuesException;
use App\Jobs\Exceptions\InsertIgnoreBulkException;
use App\Jobs\Exceptions\NoMessagesToSyncException;
use App\Repositories\Contracts\MessageRepositoryInterface;
use App\Repositories\Contracts\QueueRepositoryInterface;
use App\Repositories\Eloquent\MessageRepositoryEloquent;
use App\Resolvers\DifferMessageInputDataToDatabase;
use App\Services\SQSClientService;
use Aws\Sqs\Exception\SqsException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\QueryException;

class SyncAllAwsSqsMessagesJob extends Job
{
    use DifferMessageInputDataToDatabase;
    /**
     * @var string
     */
    private $queueName;

    /**
     * @var int
     */
    private $messageVisibility;

    /**
     * SyncAwsSqsMessagesJob constructor.
     * @param string $queueName
     * @param int $messageVisibility
     */
    public function __construct($queueName = 'all', $messageVisibility = 2000)
    {
        $this->queueName = $queueName;
        $this->messageVisibility = $messageVisibility;
    }

    /**
     * @param SQSClientService $sqs
     * @param QueueRepositoryInterface $queue
     * @param MessageRepositoryInterface $message
     * @return mixed
     */
    public function handle(SQSClientService $sqs, QueueRepositoryInterface $queue, MessageRepositoryInterface $message)
    {
        $databaseQueues = $this->findAllDatabaseQueuesOrFail($queue);
        $queues = $this->collectQueuesIdAndUrlOrFail($sqs, $databaseQueues);
        $queueMessages = $this->collectQueuesMessagesOrFail($sqs, $queues);

        $filteredQueueMessages = $this->validateAndRemoveDuplicateMessages($queueMessages);

        $this->insertBulkMessagesOrFail($message, $this->buildMessagePayloadForInsert($filteredQueueMessages));

        event(new SqsMessagesWasSynced(
            collect($filteredQueueMessages)->pluck('MessageId')->toArray()
        ));
    }

    /**
     * @param MessageRepositoryEloquent $message
     * @param array $insertPayload
     * @throws DatabaseAlreadySyncedException
     * @throws InsertIgnoreBulkException
     */
    public function insertBulkMessagesOrFail(MessageRepositoryEloquent $message, $insertPayload)
    {
        try {
            $message->insertIgnoreBulk($insertPayload);
        } catch (QueryException $exception) {
            throw new InsertIgnoreBulkException('Insert Ignore Bulk Error: ' . $exception->getMessage());
        }
    }

    /**
     * @param QueueRepositoryInterface $queue
     * @return mixed
     * @throws EmptyQueuesException
     */
    private function findAllDatabaseQueuesOrFail(QueueRepositoryInterface $queue)
    {
        $databaseQueues = $queue->all();

        if ($this->guardEmptyQueue($databaseQueues)) {
            throw new EmptyQueuesException('Queues does not exist.');
        }

        return $databaseQueues;
    }

    /**
     * @param SQSClientService $sqs
     * @param array $queueList
     * @return array
     * @throws AWSSQSServerException
     */
    private function collectQueuesIdAndUrlOrFail(SQSClientService $sqs, $queueList)
    {
        try {
            return collect($queueList)
                ->map(function ($que) use ($sqs) {
                    return [
                        'id' => $que->id,
                        'url' => $sqs->client()->getQueueUrl(['QueueName' => $que->aws_queue_name])->get('QueueUrl')
                    ];
                })->toArray();
        } catch (SqsException $exception) {
            throw new AWSSQSServerException($this->extractSQSMessage($exception->getMessage()));
        }
    }

    /**
     * @param SQSClientService $sqs
     * @param array $queues
     * @return array|mixed
     * @throws NoMessagesToSyncException
     */
    private function collectQueuesMessagesOrFail(SQSClientService $sqs, $queues)
    {
        $collectedMessages = collect($queues)
            ->map(function ($queue) use ($sqs) {
                return $this->getAvailableQueueMessageAndAttachId($sqs, $queue['id'], $queue['url']);
            })->flatten(1);

        $messages = collect($collectedMessages)->unique('MessageId')->toArray();

        if (!$messages) {
            throw new NoMessagesToSyncException('No available Queues Messages for sync.');
        }

        return $messages;
    }

    /**
     * @param SQSClientService $sqs
     * @param string $queueId
     * @param string $queueUrl
     * @return array
     */
    private function getAvailableQueueMessageAndAttachId(SQSClientService $sqs, $queueId, $queueUrl)
    {
        $messages = [];

        while ($availableMessage = $this->getAQueueMessage($sqs, $queueUrl)) {
            $messages[] = [
                'MessageId' => $availableMessage['MessageId'],
                'Body' => $availableMessage['Body'],
                'ReceiptHandle' => $availableMessage['ReceiptHandle'],
                'queue_id' => $queueId,
                'queue_url' => $queueUrl
            ];
        }

        return $messages;
    }

    /**
     * @param array $queueMessages
     * @return array
     * @throws DatabaseAlreadySyncedException
     */
    private function validateAndRemoveDuplicateMessages(array $queueMessages)
    {
        $filteredMessages = $this->computeDifference(collect($queueMessages)->pluck('MessageId')->toArray());

        $result = collect($queueMessages)->whereIn('MessageId', $filteredMessages)->toArray();

        if (empty($result)) {
            throw new DatabaseAlreadySyncedException('Database already synced.');
        }

        return $result;
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
     * @param array $message
     * @return array
     */
    private function buildMessagePayloadForInsert($message)
    {
        return collect($message)
            ->map(function ($message) {
                return [
                    'message_id' => $message['MessageId'],
                    'queue_id' => $message['queue_id'],
                    'message_content' => str_replace('"', '\'', $message['Body']),
                    'completed' => 'N'
                ];
            })->toArray();
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

    /**
     * @param Collection $queueList
     * @return bool
     */
    private function guardEmptyQueue(Collection $queueList)
    {
        return $queueList->isEmpty();
    }
}