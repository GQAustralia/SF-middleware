<?php

use App\OutboundMessage;
use App\Repositories\Eloquent\OutboundMessageLogRepositoryEloquent;
use App\OutboundMessageLog;
use Illuminate\Support\Collection;

class OutboundMessageLogRepositoryEloquentTest extends BaseTestCase
{
    private $repository;

    public function setUp()
    {
        parent::setUp();

        $this->repository = $this->app->make(OutboundMessageLogRepositoryEloquent::class);
    }

    /** @test */
    public function it_returns_outbound_message_log_on_instantiated_model()
    {
        $repository = new OutboundMessageLogRepositoryEloquent(new OutboundMessageLog());

        $this->assertInstanceOf(OutboundMessageLog::class, $repository->model());
    }

    /** @test */
    public function it_returns_outbound_message_log_on_create()
    {
        $input = factory(OutboundMessageLog::class)->make();

        $input = $result = $this->repository->create($input->toArray());

        $this->assertInstanceOf(OutboundMessageLog::class, $result);
        $this->assertAttributesExpectedValues(
            ['outbound_message_id', 'operation', 'request_object', 'object_name'],
            $input,
            $result
        );
        $this->seeInDatabase('outbound_message_log', [
            'outbound_message_id' => $input->outbound_message_id,
            'operation' => $input->operation,
            'request_object' => $input->request_object,
            'object_name' => $input->object_name
        ]);
    }

    /** @test */
    public function it_returns_an_empty_collection_when_no_outbound_message_log_exist()
    {
        $result = $this->repository->all();

        $this->assertEmpty($result);
    }

    /** @test */
    public function it_returns_a_collection_of_outbound_message()
    {
        factory(OutboundMessageLog::class, 5)->create();

        $result = $this->repository->all();

        $this->assertEquals(5, count($result));
        $this->assertInstanceOf(OutboundMessageLog::class, $result[0]);
        $this->assertInstanceOf(Collection::class, $result);
    }

    /** @test */
    public function it_returns_a_outbound_message_log_when_search_by_attribute()
    {
        $outbound_message_log = factory(OutboundMessageLog::class)->create();

        $result = $this->repository->findBy('id', $outbound_message_log->id);

        $this->assertInstanceOf(OutboundMessageLog::class, $result);
        $this->assertEquals($outbound_message_log->message, $result->message);
    }

    /** @test */
    public function it_returns_null_on_searching_outbound_message_log_by_attribute_when_no_action_exist()
    {
        $result = $this->repository->findBy('id', 'unknownValue');

        $this->assertNull($result);
    }

    /** @test */
    public function it_returns_a_collection_of_message_when_search_all_by_attribute()
    {
        factory(OutboundMessageLog::class, 2)->create(['operation' => 'insert']);

        $result = $this->repository->findAllBy('operation', 'insert');

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertInstanceOf(OutboundMessageLog::class, $result[0]);
        $this->assertEquals(2, count($result));
    }

    /** @test */
    public function it_returns_an_empty_collection_when_search_all_by_attribute()
    {
        $result = $this->repository->findAllBy('id', 'unknownName');

        $this->assertEmpty($result);
        $this->assertInstanceOf(Collection::class, $result);
    }
}