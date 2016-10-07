<?php

namespace App\Jobs;

use App\Jobs\Exceptions\AWSSQSServerException;
use App\Jobs\Exceptions\EmptyQueuesException;
use App\Jobs\Exceptions\NoMessagesToSyncException;
use App\Repositories\Contracts\MessageRepositoryInterface;
use App\Repositories\Contracts\QueueRepositoryInterface;
use App\Resolvers\SQSClientResolver;
use Aws\Sqs\Exception\SqsException;
use Illuminate\Database\Eloquent\Collection;

class SyncAllAwsSqsMessagesJob extends Job
{
    /**
     * SyncAwsSqsMessagesJob constructor.
     */
    public function __construct()
    {
        //
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
        $messages = $this->buildMessageForInsert($queueMessages);

        return $message->insertIgnoreBulk($messages);
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
            return collect($queueList)->map(function ($que) use ($sqs) {
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
        $collectedMessages = collect($queues)->map(function ($queue) use ($sqs) {
            return $this->getAvailableQueueMessageAndAttachId($sqs, $queue['id'], $queue['url']);
        })->flatten(1);

        $messages = collect($collectedMessages)->unique('message_id')->toArray();

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
                'message_id' => $availableMessage['MessageId'],
                'message_content' => $availableMessage['Body'],
                'receipt_handle' => $availableMessage['ReceiptHandle'],
                'queue_id' => $queueId
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
            ->receiveMessage(['QueueUrl' => $url, 'VisibilityTimeout' => 15])
            ->get('Messages');

        return array_first($message);
    }

    /**
     * @param array $message
     * @return array
     */
    private function buildMessageForInsert($message)
    {
        $currentDate = date('Y-m-d');

        return collect($message)->map(function ($message) use ($currentDate) {

            return [
                'message_id' => $message['message_id'],
                'queue_id' => $message['queue_id'],
                'message_content' => $message['message_content'],
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