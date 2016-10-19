<?php

namespace App\Listeners;

use App\Events\SqsMessagesWasSynced;
use App\Http\Controllers\StatusCodes;
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
     * SqsMessagesWasSyncedEventListener constructor.
     * @param MessageRepositoryInterface $message
     * @param GuzzleClient $guzzleClient
     */
    public function __construct(MessageRepositoryInterface $message, GuzzleClient $guzzleClient)
    {
        $this->message = $message;
        $this->guzzleClient = $guzzleClient;
    }

    /**
     * @param SqsMessagesWasSynced $event
     * @return array
     */
    public function handle(SqsMessagesWasSynced $event)
    {
        $messages = $this->message->findAllWhereIn('message_id', $event->messageIdList, ['queue']);

        return collect($messages)->each(function ($message) {
            if (!empty($message->queue->subscriber->count())) {
                $this->message->attachSubscriber(
                    $message,
                    $this->buildAttachInput($message->queue->subscriber, $message->message_content)->toArray()
                );
            }
        });
    }

    /**
     * @param Collection $subscribers
     * @param $messageContent
     * @return \Illuminate\Support\Collection
     */
    private function buildAttachInput(Collection $subscribers, $messageContent)
    {
        $subscriberAttachInput = collect([]);

        collect($subscribers)->each(function ($subscriber) use ($subscriberAttachInput, $messageContent) {

            $formParams = $this->buildPostParams($this->unSerializeSalesForceMessage($messageContent));

            $subscriberAttachInput->put(
                $subscriber->id,
                ['status' => $this->getUrlStatus($subscriber->url, $formParams)]
            );
        });

        return $subscriberAttachInput;
    }

    /**
     * @param string $url
     * @param array $formParams
     * @return string
     */
    public function getUrlStatus($url, $formParams)
    {
        $urlStatus = self::FAILED;

        if ($this->guardIsValidUrl($url)) {
            $urlStatus = $this->sendMessageToSubscriber($url, $formParams);
        }

        return $urlStatus;
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
        $result = $this->guzzleClient->post($url, $options);

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
