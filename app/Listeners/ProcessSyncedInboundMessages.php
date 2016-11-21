<?php

namespace App\Listeners;

use App\Events\InboundMessagesWasSynced;
use App\Http\Controllers\StatusCodes;
use App\InboundMessage;
use App\Repositories\Contracts\InboundMessageRepositoryInterface;
use App\Repositories\Contracts\InboundMessageLogRepositoryInterface;
use App\Resolvers\InboundMessageStatusResolver;
use App\Resolvers\ProvidesDecodingOfSalesForceMessages;
use App\Subscriber;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Contracts\Queue\ShouldQueue;

class ProcessSyncedInboundMessages implements ShouldQueue, StatusCodes
{
    use ProvidesDecodingOfSalesForceMessages;

    const SENT = 'sent';
    const FAILED = 'failed';
    const HTTP_NOT_FOUND = 'HTTP/1.1 404 Not Found';
    const AUTH_TOKEN = '5vZmU6HGAsmN8Qfc-YDoDwhH454950';

    /**
     * @var InboundMessageRepositoryInterface
     */
    protected $message;

    /**
     * @var GuzzleClient
     */
    protected $guzzleClient;

    /**
     * @var InboundMessageLogRepositoryInterface
     */
    protected $messageLog;

    /**
     * @var InboundMessageStatusResolver
     */
    protected $messageStatusResolver;

    /**
     * SqsMessagesWasSyncedEventListener constructor.
     * @param InboundMessageRepositoryInterface $message
     * @param GuzzleClient $guzzleClient
     * @param InboundMessageLogRepositoryInterface $messageLog
     * @param InboundMessageStatusResolver $messageStatusResolver
     */
    public function __construct(
        InboundMessageRepositoryInterface $message,
        GuzzleClient $guzzleClient,
        InboundMessageLogRepositoryInterface $messageLog,
        InboundMessageStatusResolver $messageStatusResolver
    ) {
        $this->message = $message;
        $this->guzzleClient = $guzzleClient;
        $this->messageLog = $messageLog;
        $this->messageStatusResolver = $messageStatusResolver;
    }

    /**
     * @param InboundMessagesWasSynced $event
     */
    public function handle(InboundMessagesWasSynced $event)
    {
        $messagesForResolve = [];

        $messages = $this->message->findAllWhereIn('message_id', $event->messageIdList, ['action']);

        collect($messages)->each(function ($message) use (&$messagesForResolve) {
            if ($this->hasSubscribers($message)) {
                $this->handleMessageSubscribers($message);
                $messagesForResolve[] = $message->message_id;
            }
        });

        $this->messageStatusResolver->resolve($messagesForResolve);
    }

    /**
     * @param InboundMessage $message
     * @return integer
     */
    private function hasSubscribers(InboundMessage $message)
    {
        return $message->action->subscriber->count();
    }

    /**
     * @param InboundMessage $message
     */
    private function handleMessageSubscribers(InboundMessage $message)
    {
        $subscriberMessageLogs = collect([]);

        collect($message->action->subscriber)->each(function ($subscriber) use ($subscriberMessageLogs, $message) {

            $isValidUrl = $this->guardIsValidUrl($subscriber->url);

            if ($isValidUrl) {
                $response = $this->sendMessageToSubscriber(
                    $subscriber->url,
                    $this->buildHttpPostParameters($message->message_content)
                );

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
     * @param array $message
     * @return array
     */
    private function buildHttpPostParameters($message)
    {
        $salesForceParameters = array_merge(
            $this->deCodeSalesForceMessage($this->cleanMessageContentForSending($message)),
            ['authToken' => self::AUTH_TOKEN]
        );

        return array_merge(
            ['http_errors' => false],
            ['form_params' => $salesForceParameters]
        );
    }

    /**
     * @param string $url
     * @param array $formParams
     * @return \Psr\Http\Message\ResponseInterface
     */
    private function sendMessageToSubscriber($url, $formParams)
    {
        return $this->guzzleClient->post($url, $formParams);
    }

    /**
     * @param string $message
     * @return string
     */
    private function cleanMessageContentForSending($message)
    {
        return str_replace('\'', '"', $message);
    }

    /**
     * @param InboundMessage $message
     * @param integer $subscriberId
     * @param integer $statusCode
     * @return Subscriber[belongsToMany]
     */
    private function insertSentMessage(InboundMessage $message, $subscriberId, $statusCode)
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
     * @param integer $inboundSentMessageId
     * @param integer $responseCode
     * @param string $responseBody
     * @return array
     */
    private function buildMessageLogPayload($inboundSentMessageId, $responseCode, $responseBody)
    {
        $dateNow = date('Y-m-d');

        return [
            'inbound_sent_message_id' => $inboundSentMessageId,
            'response_code' => $responseCode,
            'response_body' => $responseBody,
            'created_at' => $dateNow,
            'updated_at' => $dateNow
        ];
    }
}
