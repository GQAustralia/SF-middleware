<?php

namespace App\Services;

use App\Resolvers\ProvidesAWSConnectionParameters;
use Aws\Sqs\SqsClient;
use App\Services\OutboundSalesforceService;

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
        } catch (\Exception $ex) {
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
        } catch (\Exception $ex) {
            echo $ex->getMessage();
            return false;
        }
       
    }
    public function sendMessagesToSalesforce(){
        
//        $xml = "<Products><row no='1'><FL val='Id'>696292000024527753</FL><FL val='Product Name'><![CDATA[Certificate IV in Meat Processing (Quality Assurance) (Release 1)]]></FL>
//						<FL val='Product Code'><![CDATA[AMP40415]]></FL>
//						
//						<FL val='Qualification Level'>Certificate IV</FL>
//						<FL val='Product Active'>false</FL>
//						<FL val='Usage Status'>Current</FL>
//						<FL val='Provided Online'>false</FL>
//						<FL val='Training Package'><![CDATA[australian meat processing training package]]></FL>
//						<FL val='View on training.gov.au'><![CDATA[http://training.gov.au/Training/Details/AMP40415]]></FL>
//						<FL val='Description'><![CDATA[test]]></FL>
//
//						<FL val='Unit Price'>3000</FL>
//						<FL val='Online Price'>0</FL>
//						</row></Products>";
        
//        $attr = array
//(
//    'authtoken' => 'ff5196138d9b9112b7fe675a9c6025d0',
//    'function' => 'upd',
//    'module' => 'Products',
//    'response_type' => 'json'
//);
        
//$xml = "<Vendors><row no='1'><FL val='Id'>696292000040395206</FL><FL val='Vendor Name'><![CDATA[RTO Test Data]]></FL>
//                                                <FL val='Provider Code'><![CDATA[RTO]]></FL>
//                                                <FL val='CURRENT'>true</FL>
//                                                <FL val='Description'><![CDATA[]]></FL>
//                                        </row></Vendors>";
//              $attr = array
//(
//    'authtoken' => 'ff5196138d9b9112b7fe675a9c6025d0',
//    'function' => 'upd',
//    'module' => 'Vendors',
//    'response_type' => 'json'
//);
              
$xml = '<Leads>
	    	<row no="1">
				<FL val="Id">6962920000309034431</FL>
				<FL val="Target RTO_ID">696292000010931848</FL>
                </row>
		</Leads>';
              $attr = array
(
    'authtoken' => 'ff5196138d9b9112b7fe675a9c6025d0',
    'function' => 'upd',
    'module' => 'Leads',
    'response_type' => 'json'
);              
              
        
        $OutboundSalesforceService = new OutboundSalesforceService;
        $response = $OutboundSalesforceService->sendToSalesforce($xml,$attr);
        
//        $messages = $this->getQueueMessages();
//        if($messages !== false){
//            foreach($messages as $message){
//                $xml = $message['Body'];
//                $attributes = array();
//                if(!empty($message['MessageAttributes'])) {
//                    foreach($message['MessageAttributes'] as $key => $attr){
//                        $attributes[$key] = $attr['StringValue'];
//                    }
//                }
//                echo $xml;
//                print_r($attributes);
//                exit();
//               $response = $OutboundSalesforceService->sendToSalesforce($xml,$attributes);
//               $response = false;
//               if($response){
//                   $mid = $message['MessageId'];
//                   $reciptHandles = $message['ReceiptHandle'];
//                   $result = $client->deleteMessage(array(
//                        // QueueUrl is required
//                        'QueueUrl' => $this->queueURI,
//                        // ReceiptHandle is required
//                        'ReceiptHandle' => $reciptHandles,
//                    ));
//               }
//               
//            }
//            $this->sendMessagesToSalesforce();
//        }
        
    }
}