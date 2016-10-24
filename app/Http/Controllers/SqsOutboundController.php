<?php

namespace App\Http\Controllers;

use App\Jobs\Exceptions\AWSSQSServerException;
use App\Jobs\Exceptions\DatabaseAlreadySyncedException;
use App\Jobs\Exceptions\EmptyQueuesException;
use App\Jobs\Exceptions\InsertIgnoreBulkException;
use App\Jobs\Exceptions\NoMessagesToSyncException;
use App\Jobs\SyncAllAwsSqsMessagesJob;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Laravel\Lumen\Http\ResponseFactory;
use Aws\Sns\Message;
use Aws\Sns\MessageValidator;
use Aws\Sns\Exception\InvalidSnsMessageException;
use App\Services\OutboundService;

class SqsOutboundController extends Controller {

    const DATABASE_ERROR_MESSAGE = 'Database error please contact your Administrator.';
    const SYNC_SUCCESS = 'Sync Successful.';
    private $outboundQueue;

    /**
     * @var ResponseFactory
     */
    protected $responseFactory;

    /**
     * MessageQueueController constructor.
     * @param ResponseFactory $responseFactory
     */
    public function __construct() {
//        $this->responseFactory = $responseFactory;
//        $this->outboundQueue = env('outboundQueue');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function sync(Request $request) {
        try {
            $this->dispatch(new SyncAllAwsSqsMessagesJob());
        } catch (EmptyQueuesException $exc) {
            return $this->responseFactory->make($exc->getMessage(), self::BAD_REQUEST_STATUS_CODE);
        } catch (AWSSQSServerException $exc) {
            return $this->responseFactory->make($exc->getMessage(), self::BAD_REQUEST_STATUS_CODE);
        } catch (NoMessagesToSyncException $exc) {
            return $this->responseFactory->make($exc->getMessage(), self::SUCCESS_STATUS_CODE);
        } catch (DatabaseAlreadySyncedException $exc) {
            return $this->responseFactory->make($exc->getMessage(), self::SUCCESS_STATUS_CODE);
        } catch (InsertIgnoreBulkException $exc) {
            return $this->responseFactory->make($exc->getMessage(), self::INTERNAL_SERVER_ERROR_STATUS_CODE);
        } catch (QueryException $exc) {
            return $this->responseFactory->make(self::DATABASE_ERROR_MESSAGE, self::INTERNAL_SERVER_ERROR_STATUS_CODE);
        }

        return $this->responseFactory->make(self::SYNC_SUCCESS, self::SUCCESS_STATUS_CODE);
    }

    /**
     * 
     */
    public function notify() {


//      Instantiate the Message and Validator
        $message = Message::fromRawPostData();
        $validator = new MessageValidator();

//      Validate the message and log errors if invalid.
        try {
            $validator->validate($message);
        } catch (InvalidSnsMessageException $e) {
            // Pretend we're not here if the message is invalid.
            http_response_code(404);
            error_log('SNS Message Validation Error: ' . $e->getMessage());
            die();
        }

//          Check the type of the message and handle the subscription.
        if ($message['Type'] === 'SubscriptionConfirmation') {
            // Confirm the subscription by sending a GET request to the SubscribeURL
            file_get_contents($message['SubscribeURL']);
        }

        if ($message['Type'] === 'Notification') {
            // Do whatever you want with the message body and data.
            echo $message['MessageId'] . ': ' . $message['Message'] . "\n";
        }

        if ($message['Type'] === 'UnsubscribeConfirmation') {
            file_get_contents($message['SubscribeURL']);
        }
    }
    
    public function testZoho(){
         $outboundService = new OutboundService();
     $outboundService->sendMessagesToZOHO();
    }

}
