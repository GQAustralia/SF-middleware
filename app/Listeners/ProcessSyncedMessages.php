<?php

namespace App\Listeners;

use App\Events\SqsMessagesWasSynced;
use App\Http\Controllers\StatusCodes;
use App\Message;
use App\Repositories\Contracts\MessageLogRepositoryInterface;
use App\Repositories\Contracts\MessageRepositoryInterface;
use App\Resolvers\MessageStatusResolver;
use App\Resolvers\ProvidesUnSerializationOfSalesForceMessages;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Contracts\Queue\ShouldQueue;

class ProcessSyncedMessages implements ShouldQueue, StatusCodes
{
    use ProvidesUnSerializationOfSalesForceMessages;

    const SENT = 'sent';
    const FAILED = 'failed';
    const HTTP_NOT_FOUND = 'HTTP/1.1 404 Not Found';

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
     * @var MessageStatusResolver
     */
    protected $messageStatusResolver;

    /**
     * SqsMessagesWasSyncedEventListener constructor.
     * @param MessageRepositoryInterface $message
     * @param GuzzleClient $guzzleClient
     * @param MessageLogRepositoryInterface $messageLog
     * @param MessageStatusResolver $messageStatusResolver
     */
    public function __construct(
        MessageRepositoryInterface $message,
        GuzzleClient $guzzleClient,
        MessageLogRepositoryInterface $messageLog,
        MessageStatusResolver $messageStatusResolver
    ) {
        $this->message = $message;
        $this->guzzleClient = $guzzleClient;
        $this->messageLog = $messageLog;
        $this->messageStatusResolver = $messageStatusResolver;
    }

    /**
     * @param SqsMessagesWasSynced $event
     */
    public function handle(SqsMessagesWasSynced $event)
    {
        $messagesForResolve = [];

        $messages = $this->message->findAllWhereIn('message_id', $event->messageIdList, ['action']);

        collect($messages)->each(function ($message) {
            if ($this->hasSubscribers($message)) {
                $this->handleMessageSubscribers($message);
                $messagesForResolve[] = $message->message_id;
            }
        });

        $this->messageStatusResolver->resolve($messagesForResolve);
    }

    /**
     * @param Message $message
     * @return integer
     */
    private function hasSubscribers(Message $message)
    {
        return $message->action->subscriber->count();
    }

    /**
     * @param Message $message
     */
    private function handleMessageSubscribers(Message $message)
    {
        $subscriberMessageLogs = collect([]);

        collect($message->action->subscriber)->each(function ($subscriber) use ($subscriberMessageLogs, $message) {

            $isValidUrl = $this->guardIsValidUrl($subscriber->url);

            if ($isValidUrl) {
                $response = $this->sendMessageToSubscriber($subscriber->url, $message->message_content);
                $sentMessage = $this->insertSentMessage($message, $subscriber->id, $response->getStatusCode());

                $messageLogPayload = $this->buildMessageLogPayload(
                    $sentMessage->id,
                    $response->getStatusCode(),
                    $response->getBody()->getContents()
                );
            }

            if (!$isValidUrl) {
                $sentMessage = $this->insertSentMessage($message, $subscriber->id, 404);
                $messageLogPayload = $this->buildMessageLogPayload($sentMessage->id, 404, self::HTTP_NOT_FOUND);
            }

            $subscriberMessageLogs->push($messageLogPayload);
        });

        $this->messageLog->insertBulk($subscriberMessageLogs->toArray());
    }

    /**
     * @param string $url
     * @return bool
     */
    private function guardIsValidUrl($url)
    {
        $file_headers = @get_headers($url);

        if (!$file_headers || $file_headers[0] == self::HTTP_NOT_FOUND) {
            return false;
        }

        return true;
    }

    /**
     * @param string $url
     * @param string $message
     * @return \Psr\Http\Message\ResponseInterface
     */
    private function sendMessageToSubscriber($url, $message)
    {
        $formParams = array_merge(
            ['http_errors' => false],
            ['form_params' => $this->unSerializeSalesForceMessage($message)]
        );

        return $this->guzzleClient->post($url, $formParams);
    }

    /**
     * @param Message $message
     * @param integer $subscriberId
     * @param integer $statusCode
     * @return Subscriber[belongsToMany]
     */
    private function insertSentMessage(Message $message, $subscriberId, $statusCode)
    {
        $result = $this->message->attachSubscriber(
            $message,
            [$subscriberId => ['status' => $this->isResponseSentOrFailed($statusCode)]]
        );

        return $result->subscriber[0]->pivot;
    }

    /**
     * @param integer $responseCode
     * @return string
     */
    private function isResponseSentOrFailed($responseCode)
    {
        if ($responseCode == 200) {
            return self::SENT;
        }

        return self::FAILED;
    }

    /**
     * @param integer $sentMessageId
     * @param integer $responseCode
     * @param string $responseBody
     * @return array
     */
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
}
