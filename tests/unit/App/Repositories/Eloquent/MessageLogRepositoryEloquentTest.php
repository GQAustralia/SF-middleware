<?php

use App\MessageLog;
use App\Repositories\Eloquent\MessageLogRepositoryEloquent;

class MessageLogRepositoryEloquentTest extends BaseTestCase
{
    protected $repository;

    public function setUp()
    {
        parent::setUp();

        $this->repository = $this->app->make(MessageLogRepositoryEloquent::class);
    }

    /** @test */
    public function it_returns_true_on_successful_insert()
    {
        $dateNow = date('Y-m-d');

        $input = factory(MessageLog::class, 3)->make(['created_at' => $dateNow, 'updated_at' => $dateNow]);

        $result = $this->repository->insertBulk($input->toArray());

        $this->assertEquals(3, MessageLog::all()->count());
        $this->assertEquals(1, $result);
        $this->assertMultipleSeeInDatabase('message_log', $input->toArray());
    }

    /** @test */
    public function it_does_not_save_when_payload_is_empty()
    {
        $result = $this->repository->insertBulk([]);

        $this->assertNull($result);
    }
}
