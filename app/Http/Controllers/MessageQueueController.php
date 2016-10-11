<?php

namespace App\Http\Controllers;

use App\Jobs\Exceptions\AWSSQSServerException;
use App\Jobs\Exceptions\EmptyQueuesException;
use App\Jobs\Exceptions\InsertIgnoreBulkException;
use App\Jobs\Exceptions\NoMessagesToSyncException;
use App\Jobs\Exceptions\QueuesMessageDeleteException;
use App\Jobs\SyncAllAwsSqsMessagesJob;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Laravel\Lumen\Http\ResponseFactory;

class MessageQueueController extends Controller
{
    const DATABASE_ERROR_MESSAGE = 'Database error please contact your Administrator.';
    const SYNC_SUCCESS = 'Sync Successful.';

    /**
     * @var ResponseFactory
     */
    protected $responseFactory;

    /**
     * MessageQueueController constructor.
     * @param ResponseFactory $responseFactory
     */
    public function __construct(ResponseFactory $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function sync(Request $request)
    {
        try {
            $this->dispatch(new SyncAllAwsSqsMessagesJob());
        } catch (EmptyQueuesException $exc) {
            return $this->responseFactory->make($exc->getMessage(), self::BAD_REQUEST_STATUS_CODE);
        } catch (AWSSQSServerException $exc) {
            return $this->responseFactory->make($exc->getMessage(), self::BAD_REQUEST_STATUS_CODE);
        } catch (NoMessagesToSyncException $exc) {
            return $this->responseFactory->make($exc->getMessage(), self::SUCCESS_STATUS_CODE);
        } catch (InsertIgnoreBulkException $exc) {
            return $this->responseFactory->make($exc->getMessage(), self::INTERNAL_SERVER_ERROR_STATUS_CODE);
        } // @codeCoverageIgnoreStart
        catch (QueuesMessageDeleteException $exc) {
            return $this->responseFactory->make($exc->getMessage(), self::INTERNAL_SERVER_ERROR_STATUS_CODE);
            // @codeCoverageIgnoreEnd
        } catch (QueryException $exc) {
            return $this->responseFactory->make(self::DATABASE_ERROR_MESSAGE, self::INTERNAL_SERVER_ERROR_STATUS_CODE);
        }

        return $this->responseFactory->make(self::SYNC_SUCCESS, self::SUCCESS_STATUS_CODE);
    }
}
