<?php

namespace App\Http\Controllers;

use App\Exceptions\AWSSQSServerException;
use App\Exceptions\DatabaseAlreadySyncedException;
use App\Exceptions\InsertIgnoreBulkException;
use App\Exceptions\NoMessagesToSyncException;
use App\Exceptions\NoValidMessagesFromQueueException;
use App\Services\InboundMessagesSyncService;
use Illuminate\Database\QueryException;
use Laravel\Lumen\Http\ResponseFactory;
use App\Services\OutboundSalesforceService;
use App\OutboundMessage;
use App\Repositories\Contracts\OutboundMessageInterface;

/**
 * Class MessageQueueController
 *
 * @codeCoverageIgnore
 *
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
     * @var InboundMessagesSyncService
     */
    protected $inbound;

    /**
     * @var OutboundSalesforceService
     */
    protected $outboundSalesforceService;

    /**
     * @var OutboundMessageInterface
     */
    protected $outboundMessage;

    /**
     * MessageQueueController constructor.
     *
     * @param ResponseFactory            $responseFactory
     * @param InboundMessagesSyncService $inbound
     * @param OutboundSalesforceService $outboundSalesforceService
     * @param OutboundMessageInterface $outboundMessage
     */
    public function __construct(
        ResponseFactory $responseFactory,
        InboundMessagesSyncService $inbound, 
        OutboundSalesforceService $outboundSalesforceService, 
        OutboundMessageInterface $outboundMessage
    ) {
        $this->responseFactory = $responseFactory;
        $this->inbound = $inbound;
        $this->outboundSalesforceService = $outboundSalesforceService;
        $this->outboundMessage = $outboundMessage;
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

    /**
     * test for zoho
     *
     * @codeCoverageIgnore
     */
    public function testZoho()
    {
        $body = '{"object":"OpportunityLineItem","fields":{"Id":"00kp0000003by1xAAA"},"parents":[{"object":"Opportunity","fields":{"Target_RTO__c":{"object":"Account","relations":{"or":[{"Zoho_RTO_Id__c":"696292000010931874"},{"Zoho_RTO_Id__c":"zcrm_696292000010931874"},{"Id":"696292000010931874"}]}}},"childRelation":"OpportunityId"}],"parentfields":["OpportunityId"]}';
        $body = '{"object":"OpportunityLineItem","fields":{"Id":"00kp0000003by1xAAA","UnitPrice":"1500"},"parents":[{"object":"Opportunity","fields":{"Qualification_Name__c":"Diploma of Business Administration (Release 1)","Qualification_Demanded_Code__c":"BSB50415"},"childRelation":"OpportunityId"},{"object":"PricebookEntry","fields":{"Cost_Price__c":"400"},"childRelation":"PricebookEntryId"}],"parentfields":["OpportunityId","PricebookEntryId"]}';
        $attributes = ['operation'=>'update'];
        $result = $this->outboundSalesforceService
                    ->setLogId(1)
                    ->sendToSalesforce($body, $attributes);
        //****You need to use constructor dependency injection to execute this ****

        //$outboundService = new OutboundMessageSyncService();
        //$outboundService->sendMessagesToSalesforce();
    }
}
