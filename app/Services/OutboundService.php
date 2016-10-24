<?php

namespace App\Services;

use App\Resolvers\ProvidesAWSConnectionParameters;
use Aws\Sqs\SqsClient;
use App\Services\OutboundZOHOService;

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
    protected  $outboundQueue;
    /**
     * SQSClientResolver constructor.
     */
    
    /**
     *
     * @var String
     */
    protected $queueURI;
    
    public function __construct()
    {
        $this->client = new SqsClient($this->awsFullCredentials());
        $this->outboundQueue = env('outboundQueue','CRMOutboundQueue');
        $this->getQueueURI();
        
    }

    /**
     * @return SqsClient
     */
    public function client()
    {
        return $this->client;
    }
    
    private function getQueueURI(){
        try {
             $result = $this->client->getQueueUrl([
                'QueueName' => $this->outboundQueue
            ]);
             $this->queueURI = $result->get('QueueUrl');
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }
    private function getQueueMessages(){
        try {
             $result = $this->client->receiveMessage([
                'MaxNumberOfMessages' => 10,
                'QueueUrl' => $this->queueURI, // REQUIRED
                 'MessageAttributeNames' => ['All','.*']
            ]);
             $messages = $result->get('Messages');
             if(count($messages)>0) return $messages;
             return false;
        } catch (Exception $ex) {
            echo $ex->getMessage();
            return false;
        }
       
    }
    public function sendMessagesToZOHO(){
        $OutboundZOHOService = new OutboundZOHOService;
        $messages = $this->getQueueMessages();
        if($messages !== false){
            foreach($messages as $message){
                $xml = $message['Body'];
                $attributes = array();
                if(!empty($message['MessageAttributes'])) {
                    foreach($message['MessageAttributes'] as $key => $attr){
                        $attributes[$key] = $attr['StringValue'];
                    }
                }
               $response = $OutboundZOHOService->sendToZOHO($xml,$attributes);
               var_dump($response);
            }
        }
        
    }
}