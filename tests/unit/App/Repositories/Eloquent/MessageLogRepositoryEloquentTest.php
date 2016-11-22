<?php

use App\InboundMessageLog;
use App\Repositories\Eloquent\InboundMessageLogRepositoryEloquent;

class MessageLogRepositoryEloquentTest extends BaseTestCase
{
    protected $repository;

    public function setUp()
    {
        parent::setUp();

        $this->repository = $this->app->make(InboundMessageLogRepositoryEloquent::class);
    }

    /** @test */
    public function locateTest()
    {
        $this->runningTestFor(get_class($this));
    }

    /** @test */
    public function it_returns_true_on_successful_insert()
    {
        $dateNow = date('Y-m-d');

        $input = factory(InboundMessageLog::class, 3)->make(['created_at' => $dateNow, 'updated_at' => $dateNow]);

        $result = $this->repository->insertBulk($input->toArray());

        $this->assertEquals(3, InboundMessageLog::all()->count());
        $this->assertEquals(1, $result);
        $this->assertMultipleSeeInDatabase('inbound_message_log', $input->toArray());
    }

    /** @test */
    public function it_does_not_save_when_payload_is_empty()
    {
        $result = $this->repository->insertBulk([]);

        $this->assertNull($result);
    }
}
