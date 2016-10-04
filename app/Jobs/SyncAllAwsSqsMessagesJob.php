<?php

namespace App\Jobs;

use App\Jobs\Exceptions\AWSSQSServerException;
use App\Jobs\Exceptions\EmptyQueuesException;
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
     * @param MessageRepositoryInterface $messageRepository
     * @return null
     * @throws EmptyQueuesException
     */
    public function handle(
        SQSClientResolver $sqs,
        QueueRepositoryInterface $queue,
        MessageRepositoryInterface $messageRepository
    ) {
        $databaseQueues = $queue->all();

        if ($this->guardEmptyQueue($databaseQueues)) {
            throw new EmptyQueuesException();
        }

        $awsQueuesUrl = $this->collectQueuesIdAndUrlOrFail($sqs, $databaseQueues);
        $messages = $this->collectMessagesFromQueues($sqs, $awsQueuesUrl);

        if (!$messages) {
            return null;
        }
    }

    /**
     * @param SQSClientResolver $sqs
     * @param array $queueList
     * @return array
     * @throws AWSSQSServerException
     */
    protected function collectQueuesIdAndUrlOrFail(SQSClientResolver $sqs, $queueList)
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
     * @return array
     */
    protected function collectMessagesFromQueues(SQSClientResolver $sqs, $queues)
    {
        return collect($queues)->map(function ($queue) use ($sqs) {

            $messages = [];

            while ($availableMessage = $this->getAvailableQueueMessage($sqs, $queue['url'])) {
                if (!empty($availableMessage)) {
                    $messages[] = $this->buildMessageForInsert($availableMessage, $queue['id']);
                }
            }
            return $messages;
        })->reject(function ($queue) {
            return empty($queue);
        })->toArray();
    }

    /**
     * @param SQSClientResolver $sqs
     * @param string $url
     * @return array|null
     */
    private function getAvailableQueueMessage(SQSClientResolver $sqs, $url)
    {
        $message = $sqs->client()
            ->receiveMessage(['QueueUrl' => $url, 'VisibilityTimeout' => 5])
            ->get('Messages');

        return array_first($message);
    }

    /**
     * @param string $message
     * @param int $queueId
     * @return array
     */
    private function buildMessageForInsert($message, $queueId)
    {
        return [
            'message_id' => $message['MessageId'],
            'queue_id' => $queueId,
            'message_content' => $message['Body'],
            'receipt_handle' => $message['ReceiptHandle']
        ];
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