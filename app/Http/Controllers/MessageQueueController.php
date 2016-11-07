<?php

namespace App\Http\Controllers;

use App\Exceptions\AWSSQSServerException;
use App\Exceptions\DatabaseAlreadySyncedException;
use App\Exceptions\InsertIgnoreBulkException;
use App\Exceptions\NoMessagesToSyncException;
use App\Exceptions\NoValidMessagesFromQueueException;
use App\Services\InboundMessagesSync;
use Illuminate\Database\QueryException;
use Laravel\Lumen\Http\ResponseFactory;

/**
 * Class MessageQueueController
 * @package App\Http\Controllers
 */
class MessageQueueController extends Controller
{
    const DATABASE_ERROR_MESSAGE = 'Database error please contact your Administrator.';
    const SYNC_SUCCESS = 'Sync Successful.';

    /**
     * @var ResponseFactory
     */
    protected $responseFactory;

    /**
     * @var InboundMessagesSync
     */
    private $inbound;

    /**
     * MessageQueueController constructor.
     * @param ResponseFactory $responseFactory
     * @param InboundMessagesSync $inbound
     */
    public function __construct(ResponseFactory $responseFactory, InboundMessagesSync $inbound)
    {
        $this->responseFactory = $responseFactory;
        $this->inbound = $inbound;
    }

    /**
     * @param string $queue
     *
     * @throws AWSSQSServerException
     * @throws NoMessagesToSyncException
     * @throws DatabaseAlreadySyncedException
     * @throws NoValidMessagesFromQueueException
     * @throws InsertIgnoreBulkException
     * @throws QueryException
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function sync($queue)
    {
        try {
            $this->inbound->handle($queue);
        } catch (AWSSQSServerException $exc) {
            return $this->responseFactory->make($exc->getMessage(), self::BAD_REQUEST_STATUS_CODE);
        } catch (NoMessagesToSyncException $exc) {
            return $this->responseFactory->make($exc->getMessage(), self::SUCCESS_STATUS_CODE);
        } catch (DatabaseAlreadySyncedException $exc) {
            return $this->responseFactory->make($exc->getMessage(), self::SUCCESS_STATUS_CODE);
        } catch (NoValidMessagesFromQueueException $exc) {
            return $this->responseFactory->make($exc->getMessage(), self::BAD_REQUEST_STATUS_CODE);
        } catch (InsertIgnoreBulkException $exc) {
            return $this->responseFactory->make($exc->getMessage(), self::INTERNAL_SERVER_ERROR_STATUS_CODE);
        } catch (QueryException $exc) {
            return $this->responseFactory->make(self::DATABASE_ERROR_MESSAGE, self::INTERNAL_SERVER_ERROR_STATUS_CODE);
        }

        return $this->responseFactory->make(self::SYNC_SUCCESS, self::SUCCESS_STATUS_CODE);
    }
}
