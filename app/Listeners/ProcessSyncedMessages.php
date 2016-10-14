<?php

namespace App\Listeners;

use App\Events\SqsMessagesWasSynced;
use App\Message;
use App\Repositories\Contracts\MessageRepositoryInterface;
use Illuminate\Contracts\Queue\ShouldQueue;

class ProcessSyncedMessages implements ShouldQueue
{
    /**
     * @var MessageRepositoryInterface
     */
    private $message;

    /**
     * SqsMessagesWasSyncedEventListener constructor.
     * @param MessageRepositoryInterface $message
     */
    public function __construct(MessageRepositoryInterface $message)
    {
        $this->message = $message;
    }

    /**
     * @param SqsMessagesWasSynced $event
     * @return array
     */
    public function handle(SqsMessagesWasSynced $event)
    {
        $messages = $this->message->findAllWhereIn('message_id', $event->messageIdList, ['queue']);

        return $result = collect($messages)->map(function ($message) {
            $this->message->attachSubscriber($message, $this->buildAttachInputAndSendMessageToSubscriber($message));
        })->toArray();
    }

    /**
     * @param Message $message
     * @return array
     */
    private function buildAttachInputAndSendMessageToSubscriber(Message $message)
    {
        $subscriberAttachInput = collect([]);

        collect($message->queue->subscriber)->each(function ($subscriber) use ($subscriberAttachInput, $message) {
            $subscriberAttachInput->put(
                $subscriber->id,
                ['status' => $this->sendMessageToSubscriber($subscriber->url, $message->content)]
            );
        });

        return $subscriberAttachInput->toArray();
    }

    private function sendMessageToSubscriber($url, $content)
    {
        return 'Y';
    }

}
