<?php

namespace App\Listeners;

use App\Events\SqsMessagesWasSynced;
use App\Http\Controllers\StatusCodes;
use App\MessageLog;
use App\Repositories\Contracts\MessageRepositoryInterface;
use App\Resolvers\ProvidesUnSerializationOfSalesForceMessages;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;

class ProcessSyncedMessages implements ShouldQueue, StatusCodes
{
    use ProvidesUnSerializationOfSalesForceMessages;

    const SENT = 'sent';
    const FAILED = 'failed';

    /**
     * @var MessageRepositoryInterface
     */
    protected $message;

    /**
     * @var GuzzleClient
     */
    protected $guzzleClient;

    /**
     * @var MessageLog
     */
    protected $messageLog;

    /**
     * SqsMessagesWasSyncedEventListener constructor.
     * @param MessageRepositoryInterface $message
     * @param GuzzleClient $guzzleClient
     * @param MessageLog $messageLog
     */
    public function __construct(MessageRepositoryInterface $message, GuzzleClient $guzzleClient, MessageLog $messageLog)
    {
        $this->message = $message;
        $this->guzzleClient = $guzzleClient;
        $this->messageLog = $messageLog;
    }


    public function handle(SqsMessagesWasSynced $event)
    {
        $messages = $this->message->findAllWhereIn('message_id', $event->messageIdList, ['queue']);

        return collect($messages)->each(function ($message) {
            if (!empty($message->queue->subscriber->count())) {

                $attachInput = $this->handleSubscribers($message->queue->subscriber, $message->message_content);

                //array for attach input
                $this->message->attachSubscriber(
                    $message,
                    $attachInput->toArray()
                );

                //aray for message log
            }
        });
    }

    /**
     * @param Collection $subscribers
     * @param $messageContent
     * @return \Illuminate\Support\Collection
     */
    private function handleSubscribers(Collection $subscribers, $messageContent)
    {
        $subscriberAttachInput = collect([]);
        $subscriberMessageLogs = collect([]);

        collect($subscribers)->each(function ($subscriber) use ($subscriberAttachInput, $subscriberMessageLogs, $messageContent) {

            $isValidUrl = $this->guardIsValidUrl($subscriber->url);

            if ($isValidUrl) {

                $formParams = $this->buildPostParams($this->unSerializeSalesForceMessage($messageContent));

                $result = $this->sendMessageToSubscriber($subscriber->url, $formParams);

                $subscriberAttachInput->put(
                    $subscriber->id,
                    ['status' => ($result->getStatusCode() == 200) ? self::SENT : self::FAILED]
                );

            }

            if (!$isValidUrl) {
                $subscriberAttachInput->put($subscriber->id, ['status' => self::FAILED]);
            }

        });

        return $subscriberAttachInput;
    }

    /**
     * @param string $message
     * @return array
     */
    private function buildPostParams($message)
    {
        return array_merge(['http_errors' => false], ['form_params' => $message]);
    }

    /**
     * @param string $url
     * @param array $options
     * @return string
     */
    private function sendMessageToSubscriber($url, $options = [])
    {
        return $result = $this->guzzleClient->post($url, $options);

        if ($result->getStatusCode() === self::SUCCESS_STATUS_CODE) {
            return self::SENT;
        }

        return self::FAILED;
    }

    /**
     * @param string $url
     * @return bool
     */
    private function guardIsValidUrl($url)
    {
        $file_headers = @get_headers($url);

        if (!$file_headers || $file_headers[0] == 'HTTP/1.1 404 Not Found') {
            return false;
        }

        return true;
    }

}
