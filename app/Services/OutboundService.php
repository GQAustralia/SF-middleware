<?php
namespace App\Services;

use App\Resolvers\ProvidesAWSConnectionParameters;
use Aws\Sqs\SqsClient;

class OutboundService implements AWSClientInterface
{

    use ProvidesAWSConnectionParameters;

    /**
     * @var SqsClient
     */
    protected $client;

    /**
     *
     * @var String
     */
    protected $outboundQueue;

    /**
     *
     * @var String
     */
    protected $queueURI;

    /**
     * OutboundService constructor.
     */
    public function __construct()
    {
        $this->client = new SqsClient($this->awsFullCredentials());
        $this->outboundQueue = env('outboundQueue', 'CRMOutboundQueue');
        $this->getQueueURI();
    }

    /**
     * @return SqsClient
     */
    public function client()
    {
        return $this->client;
    }

    /**
     * Set SQS Queue URL
     */
    private function getQueueURI()
    {
        try {
            $result = $this->client->getQueueUrl([
                'QueueName' => $this->outboundQueue
            ]);
            $this->queueURI = $result->get('QueueUrl');
        } catch (\Exception $ex) {
            echo $ex->getMessage();
        }
    }

    /**
     *
     * @return Mixed $message or false
     */
    private function getQueueMessages()
    {
        try {
            $result = $this->client->receiveMessage([
                'MaxNumberOfMessages' => 10,
                'QueueUrl' => $this->queueURI, // REQUIRED
                'MessageAttributeNames' => ['All', '.*']
            ]);
            $messages = $result->get('Messages');
            if (count($messages) > 0)
                return $messages;
            return false;
        } catch (\Exception $ex) {
            echo $ex->getMessage();
            return false;
        }
    }

    /**
     * Get Queue Message from SQS and send to Salesforce
     */
    public function sendMessagesToSalesforce()
    {
        $OutboundSalesforceService = new OutboundSalesforceService;

        $messages = $this->getQueueMessages();
        if ($messages !== false) {
            foreach ($messages as $message) {
                $body = $message['Body'];
                $attributes = array();
                if (!empty($message['MessageAttributes'])) {
                    foreach ($message['MessageAttributes'] as $key => $attr) {
                        $attributes[$key] = $attr['StringValue'];
                    }
                }
                $response = $OutboundSalesforceService->sendToSalesforce($body, $attributes);

                if ($response) {
                    $mid = $message['MessageId'];
                    $reciptHandles = $message['ReceiptHandle'];
                    $result = $this->client->deleteMessage(array(
                        // QueueUrl is required
                        'QueueUrl' => $this->queueURI,
                        // ReceiptHandle is required
                        'ReceiptHandle' => $reciptHandles,
                    ));
                }
            }
            $this->sendMessagesToSalesforce();
        }
    }
}
