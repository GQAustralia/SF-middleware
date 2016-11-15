<?php

use App\OutboundMessage;
use App\Repositories\Eloquent\OutboundMessageRepositoryEloquent;
use Illuminate\Support\Collection;

class OutboundMessageRepositoryEloquentTest extends BaseTestCase
{
    private $repository;

    public function setUp()
    {
        parent::setUp();

        $this->repository = $this->app->make(OutboundMessageRepositoryEloquent::class);
    }

    /** @test */
    public function locateTest()
    {
        $this->runningTestFor(get_class($this));
    }

    /** @test */
    public function it_returns_outbound_message_on_instantiated_model()
    {
        $repository = new OutboundMessageRepositoryEloquent(new OutboundMessage());

        $this->assertInstanceOf(OutboundMessage::class, $repository->model());
    }

    /** @test */
    public function it_returns_outbound_message_on_create()
    {
        $outbound_messageInput = factory(OutboundMessage::class)->make();

        $outbound_messageInput = $result = $this->repository->create($outbound_messageInput->toArray());

        $this->assertInstanceOf(OutboundMessage::class, $result);
        $this->assertAttributesExpectedValues(
            ['message_id', 'message_body', 'message_attributes', 'status'],
            $outbound_messageInput,
            $result
        );
        $this->seeInDatabase('outbound_message', [
            'message_id' => $outbound_messageInput->message_id,
            'message_body' => $outbound_messageInput->message_body,
            'message_attributes' => $outbound_messageInput->message_attributes,
            'status' => $outbound_messageInput->status
        ]);
    }

    /** @test */
    public function it_returns_an_empty_collection_when_no_outbound_message_exist()
    {
        $result = $this->repository->all();

        $this->assertEmpty($result);
    }

    /** @test */
    public function it_returns_a_collection_of_outbound_message()
    {
        factory(OutboundMessage::class, 5)->create();

        $result = $this->repository->all();

        $this->assertEquals(5, count($result));
        $this->assertInstanceOf(OutboundMessage::class, $result[0]);
        $this->assertInstanceOf(Collection::class, $result);
    }

    /** @test */
    public function it_returns_a_outbound_message_when_search_by_attribute()
    {
        $outbound_message = factory(OutboundMessage::class)->create();

        $result = $this->repository->findBy('id', $outbound_message->id);

        $this->assertInstanceOf(OutboundMessage::class, $result);
        $this->assertEquals($outbound_message->message, $result->message);
    }

    /** @test */
    public function it_returns_null_on_searching_outbound_message_by_attribute_when_no_action_exist()
    {
        $result = $this->repository->findBy('id', 'unknownValue');

        $this->assertNull($result);
    }

    /** @test */
    public function it_returns_a_collection_of_message_when_search_all_by_attribute()
    {
        $initialoutbound_message = factory(OutboundMessage::class)->create(['message_id' => 'sampleMessageId']);
        $extraoutbound_message = factory(OutboundMessage::class,
            2)->create(['message_id' => 'invalidSampleMessageId']);

        $result = $this->repository->findAllBy('message_id', 'sampleMessageId');

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertInstanceOf(OutboundMessage::class, $result[0]);
        $this->assertEquals(1, count($result));
    }

    /** @test */
    public function it_returns_an_empty_collection_when_search_all_by_attribute()
    {
        $result = $this->repository->findAllBy('id', 'unknownName');

        $this->assertEmpty($result);
        $this->assertInstanceOf(Collection::class, $result);
    }

    /** @test */
    public function it_returns_null_on_update_when_outbound_message_does_not_exist()
    {
        $result = $this->repository->update(['status' => 'sent'], 'unknownId');

        $this->assertNull($result);
    }

    /** @test */
    public function it_returns_outbound_message_on_update_on_successful_update()
    {
        $message = factory(OutboundMessage::class)->create(['status' => 'failed']);

        $updateInput = ['status' => 'sent'];

        $result = $this->repository->update($updateInput, $message->id);

        $this->assertInstanceOf(OutboundMessage::class, $result);
        $this->seeInDatabase('outbound_message', [
            'message_id' => $message->message_id,
            'status' => 'sent'
        ]);
    }
}