<?php

namespace App\Listeners;

use App\Events\SqsMessagesWasSynced;
use App\Http\Controllers\StatusCodes;
use App\Repositories\Contracts\MessageLogRepositoryInterface;
use App\Repositories\Contracts\MessageRepositoryInterface;
use App\Resolvers\ProvidesUnSerializationOfSalesForceMessages;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use App\Message;

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
     * @var MessageLogRepositoryInterface
     */
    protected $messageLog;

    /**
     * SqsMessagesWasSyncedEventListener constructor.
     * @param MessageRepositoryInterface $message
     * @param GuzzleClient $guzzleClient
     * @param MessageLogRepositoryInterface $messageLog
     */
    public function __construct(
        MessageRepositoryInterface $message,
        GuzzleClient $guzzleClient,
        MessageLogRepositoryInterface $messageLog
    )
    {
        $this->message = $message;
        $this->guzzleClient = $guzzleClient;
        $this->messageLog = $messageLog;
    }

    /**
     * @param SqsMessagesWasSynced $event
     * @return $this
     */
    public function handle(SqsMessagesWasSynced $event)
    {
        $messages = $this->message->findAllWhereIn('message_id', $event->messageIdList, ['queue']);

        return collect($messages)->each(function ($message) {
            if ($this->hasSubscribers($message)) {

                $messageLogs = $this->handleMessageSubscribers($message);

                $result = $this->messageLog->insertBulk($messageLogs->toArray());
            }
        });
    }

    private function hasSubscribers(Message $message)
    {
        return $message->queue->subscriber->count();
    }

    /**
     * @param Message $message
     * @return \Illuminate\Support\Collection
     */
    private function handleMessageSubscribers(Message $message)
    {
        $subscriberMessageLogs = collect([]);

        collect($message->queue->subscriber)->each(function ($subscriber) use ($subscriberMessageLogs, $message) {

            $isValidUrl = $this->guardIsValidUrl($subscriber->url);

            if ($isValidUrl) {

                $response = $this->sendMessageToSubscriber($subscriber->url, $message->message_content);

                $result = $this->message->attachSubscriber(
                    $message,
                    [$subscriber->id => ['status' => ($response->getStatusCode() == 200) ? self::SENT : self::FAILED]]
                );

                $messageLogPayload = $this->buildMessageLogPayload(
                    $result->subscriber[0]->pivot->id,
                    $response->getStatusCode(),
                    $response->getBody()->getContents()
                );
            }

            if (!$isValidUrl) {
                $result = $this->message->attachSubscriber(
                    $message,
                    [$subscriber->id => ['status' => self::FAILED]]
                );

                $messageLogPayload = $this->buildMessageLogPayload(
                    $result->subscriber[0]->pivot->id,
                    404,
                    'Page not Found'
                );
            }

            $subscriberMessageLogs->push($messageLogPayload);

        });

        return $subscriberMessageLogs;
    }

    private function buildMessageLogPayload($sentMessageId, $responseCode, $responseBody)
    {
        $dateNow = date('Y-m-d');

        return [
            'sent_message_id' => $sentMessageId,
            'response_code' => $responseCode,
            'response_body' => $responseBody,
            'created_at' => $dateNow,
            'updated_at' => $dateNow
        ];
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
    private function sendMessageToSubscriber($url, $message)
    {
        $formParams = $this->buildPostParams($this->unSerializeSalesForceMessage($message));

        return $this->guzzleClient->post($url, $formParams);
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
