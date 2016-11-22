<?php

use App\Exceptions\AWSSQSServerException;
use App\Services\OutboundMessageSyncService;
use App\Services\SQSClientService;

class OutboundMessageSyncServiceTest extends BaseTestCase
{
    private $outbound;
    private $sqs;

    public function setUp()
    {
        parent::setUp(); // TODO: Change the autogenerated stub

        $this->setConnection('test_mysql_database');

        $this->sqs = new SQSClientService();
        $this->outbound = $this->app->make(OutboundMessageSyncService::class);
    }

    /** @test */
    public function locateTest()
    {
        $this->runningTestFor(get_class($this));
    }

    /** @test */
    public function it_throws_an_exception_when_queue_does_not_exist()
    {
        $this->setExpectedException(AWSSQSServerException::class);

        $this->outbound->handle('nonExistingQueue');
    }
}