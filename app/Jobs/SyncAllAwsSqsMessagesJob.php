<?php

namespace App\Jobs;

use App\Jobs\Exceptions\AWSSQSServerException;
use App\Jobs\Exceptions\EmptyQueuesException;
use App\Jobs\Exceptions\InsertIgnoreBulkException;
use App\Jobs\Exceptions\NoMessagesToSyncException;
use App\Jobs\Exceptions\QueuesMessageDeleteException;
use App\Repositories\Contracts\MessageRepositoryInterface;
use App\Repositories\Contracts\QueueRepositoryInterface;
use App\Repositories\Eloquent\MessageRepositoryEloquent;
use App\Resolvers\SQSClientResolver;
use Aws\Sqs\Exception\SqsException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\QueryException;

class SyncAllAwsSqsMessagesJob extends Job
{
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
     * @param SQSClientResolver $sqs
     * @param QueueRepositoryInterface $queue
     * @param MessageRepositoryInterface $message
     * @return mixed
     */
    public function handle(SQSClientResolver $sqs, QueueRepositoryInterface $queue, MessageRepositoryInterface $message)
    {
        $databaseQueues = $this->findAllDatabaseQueuesOrFail($queue);
        $queues = $this->collectQueuesIdAndUrlOrFail($sqs, $databaseQueues);
        $queueMessages = $this->collectQueuesMessagesOrFail($sqs, $queues);

        $insertPayload = $this->buildMessagePayloadForInsert($queueMessages);
        $this->insertBulkMessagesOrFail($message, $insertPayload);

        $this->deleteQueueMessagesGroupByQueueUrl($sqs, $queueMessages);

        //@todo fire event to send
    }

    /**
     * @param MessageRepositoryEloquent $message
     * @param array $insertPayload
     * @throws InsertIgnoreBulkException
     */
    public function insertBulkMessagesOrFail(MessageRepositoryEloquent $message, $insertPayload)
    {
        try {
            $message->insertIgnoreBulk($insertPayload);
        } catch (QueryException $exception) {
            throw new InsertIgnoreBulkException('Insert Ignore Bulk Error.');
        }
    }

    /**
     * @param SQSClientResolver $sqs
     * @param array $queueMessages
     */
    private function deleteQueueMessagesGroupByQueueUrl(SQSClientResolver $sqs, $queueMessages)
    {
        $messagesGroupByQueue = collect($queueMessages)->groupBy('queue_url')->toArray();

        collect($messagesGroupByQueue)->each(function ($messages, $queueUrl) use ($sqs) {
            $this->deleteAllQueueMessages($sqs, $queueUrl, $messages);
        });
    }

    /**
     * @param SQSClientResolver $sqs
     * @param string $queueUrl
     * @param array $messages
     * @throws QueuesMessageDeleteException
     */
    private function deleteAllQueueMessages(SQSClientResolver $sqs, $queueUrl, $messages)
    {
        try {
            $receiptHandles = collect($messages)->pluck('ReceiptHandle')->toArray();

            collect($receiptHandles)->each(function ($receiptHandle) use ($queueUrl, $sqs) {
                $sqs->client()->deleteMessage(['QueueUrl' => $queueUrl, 'ReceiptHandle' => $receiptHandle]);
            });

        } // @codeCoverageIgnoreStart
        catch (SqsException $exception) {
            throw new QueuesMessageDeleteException($this->extractSQSMessage($exception->getMessage()));
        }// @codeCoverageIgnoreEnd
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
     * @param SQSClientResolver $sqs
     * @param array $queueList
     * @return array
     * @throws AWSSQSServerException
     */
    private function collectQueuesIdAndUrlOrFail(SQSClientResolver $sqs, $queueList)
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
     * @param SQSClientResolver $sqs
     * @param array $queues
     * @return array|mixed
     * @throws NoMessagesToSyncException
     */
    private function collectQueuesMessagesOrFail(SQSClientResolver $sqs, $queues)
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
     * @param SQSClientResolver $sqs
     * @param string $queueId
     * @param string $queueUrl
     * @return array
     */
    private function getAvailableQueueMessageAndAttachId(SQSClientResolver $sqs, $queueId, $queueUrl)
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
     * @param SQSClientResolver $sqs
     * @param string $url
     * @return array
     */
    private function getAQueueMessage(SQSClientResolver $sqs, $url)
    {
        $message = $sqs->client()
            ->receiveMessage(['QueueUrl' => $url, 'VisibilityTimeout' => 30])
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
                    'message_content' => $message['Body'],
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