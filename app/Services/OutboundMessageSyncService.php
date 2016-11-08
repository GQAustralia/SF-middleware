<?php
namespace App\Services;

use App\Exceptions\AWSSQSServerException;
use App\Exceptions\NoMessagesToSyncException;
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
     * OutboundService constructor.
     * @param SQSClientService $sqs
     * @param OutboundSalesforceService $outboundSalesforceService
     */
    public function __construct(SQSClientService $sqs, OutboundSalesforceService $outboundSalesforceService)
    {
        $this->sqs = $sqs;
        $this->outboundSalesforceService = $outboundSalesforceService;
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

                $response = $this->outboundSalesforceService->sendToSalesforce($body, $attributes);

                if ($response) {
                  /*  $mid = $message['MessageId'];
                    $reciptHandles = $message['ReceiptHandle'];
                    $result = $this->sqs->client()->deleteMessage([
                        'QueueUrl' => $this->queueURI,
                        'ReceiptHandle' => $reciptHandles,
                    ]);*/
                }
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
}
