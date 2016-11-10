<?php
namespace App\Services;

use App\Exceptions\AWSSQSServerException;
use App\Exceptions\NoMessagesToSyncException;
use App\OutboundMessage;
use App\Repositories\Contracts\OutboundMessageInterface;
use Aws\Sqs\Exception\SqsException;
use Aws\Sqs\SqsClient;

class OutboundMessageSyncService
{
    const OUTBOUND_QUEUE = 'CRMOutboundQueue';

    /**
     * @var SqsClient
     */
    protected $client;

    /**
     *
     * @var String
     */
    protected $queueURI;

    /**
     * @var SQSClientService
     */
    protected $sqs;

    /**
     * @var OutboundSalesforceService
     */
    protected $outboundSalesforceService;

    /**
     * @var OutboundMessageInterface
     */
    protected $outboundMessage;

    /**
     * OutboundService constructor.
     * @param SQSClientService $sqs
     * @param OutboundSalesforceService $outboundSalesforceService
     * @param OutboundMessageInterface $outboundMessage
     */
    public function __construct(
        SQSClientService $sqs,
        OutboundSalesforceService $outboundSalesforceService,
        OutboundMessageInterface $outboundMessage
    ) {
        $this->sqs = $sqs;
        $this->outboundSalesforceService = $outboundSalesforceService;
        $this->outboundMessage = $outboundMessage;
    }

    /**
     * @param null $queueName
     * @throws NoMessagesToSyncException
     */
    public function handle($queueName = null)
    {
        $queueName = (!($queueName) ? self::OUTBOUND_QUEUE : $queueName);

        $queueUrl = $this->getQueueUrlOrFail($queueName);
        $messages = $this->getQueueMessages($queueUrl);

        if ($messages !== false) {
            foreach ($messages as $message) {
                $body = $message['Body'];
                $attributes = [];
                if (!empty($message['MessageAttributes'])) {
                    foreach ($message['MessageAttributes'] as $key => $attr) {
                        $attributes[$key] = $attr['StringValue'];
                    }
                }

                $outbound = $this->insertOutboundMessage($message);

                $result = $this->outboundSalesforceService
                    ->setLogId($outbound->id)
                    ->sendToSalesforce($body, $attributes);

                if ($result) {
                    $this->setOutboundStatusToSent($outbound->id);
                }

                $this->deleteOutboundSQSMessage($queueUrl, $message['ReceiptHandle']);
            }
            $this->handle($queueName);
        }
    }

    /**
     * @param string $queueName
     * @return string
     * @throws AWSSQSServerException
     */
    private function getQueueUrlOrFail($queueName)
    {
        try {
            return $this->sqs->client()->getQueueUrl(['QueueName' => $queueName])->get('QueueUrl');
        } catch (SqsException $exception) {
            throw new AWSSQSServerException($this->extractSQSMessage($exception->getMessage()));
        }
    }

    /**
     * @param string $queueUrl
     * @return bool|mixed|null
     */
    private function getQueueMessages($queueUrl)
    {
        try {
            $result = $this->sqs->client()->receiveMessage([

                'QueueUrl' => $queueUrl,
                'MessageAttributeNames' => ['All', '.*']
            ]);
            $messages = $result->get('Messages');
            if (count($messages) > 0) {
                return $messages;
            }
            return false;
        } catch (\Exception $ex) {
            echo $ex->getMessage();
            return false;
        }
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
     * @param array $payload
     * @return OutboundMessage
     */
    private function insertOutboundMessage($payload)
    {
        return $this->outboundMessage->create([
            'message_id' => $payload['MessageId'],
            'message_body' => json_encode($payload['Body']),
            'message_attributes' => json_encode($payload['MessageAttributes']),
            'status' => 'failed'
        ]);
    }

    /**
     * @param int $outboundId
     * @return mixed
     */
    private function setOutboundStatusToSent(int $outboundId)
    {
        return $this->outboundMessage->update(['status' => 'sent'], $outboundId);
    }

    /**
     * @param string $queueUrl
     * @param string $receiptHandle
     *
     * @return \Aws\Result
     */
    private function deleteOutboundSQSMessage($queueUrl, $receiptHandle)
    {
        return $this->sqs->client()->deleteMessage(['QueueUrl' => $queueUrl, 'ReceiptHandle' => $receiptHandle]);
    }
}
