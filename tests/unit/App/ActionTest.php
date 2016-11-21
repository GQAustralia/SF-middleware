<?php

use App\Action;
use App\InboundMessage;
use App\Subscriber;

class ActionTest extends BaseTestCase
{
    /** @test */
    public function locateTest()
    {
        $this->runningTestFor(get_class($this));
    }

    /** @test */
    public function it_has_many_inbound_message()
    {
        $model = Mockery::mock('App\Action[hasMany]');

        $model->shouldReceive('hasMany')->with(InboundMessage::class)->andReturn(true);

        $this->assertTrue($model->inboundMessage());
    }

    /** @test */
    public function it_returns_message_on_calling_on_a_has_many_inbound_message_relationship()
    {
        $action = factory(Action::class)->create();
        $message = factory(InboundMessage::class, 3)->create(['action_id' => $action->id]);

        $this->assertInstanceOf(InboundMessage::class, $action->inboundMessage[0]);
        $this->assertEquals(3, count($action->inboundMessage));
    }

    /** @test */
    public function it_belongs_to_many_subscriber()
    {
        $model = Mockery::mock('App\Action[belongsToMany]');

        $model->shouldReceive('belongsToMany')->with(Subscriber::class)->andReturnSelf();
        $model->shouldReceive('withTimestamps')->andReturn(true);

        $this->assertTrue($model->subscriber());
    }

    /** @test */
    public function it_returns_subscriber_on_calling_a_many_to_many_subscriber_relationship()
    {
        $action = factory(Action::class)->create();
        $subscriber = factory(Subscriber::class)->create();

        $action->subscriber()->attach($subscriber->id);

        $this->assertInstanceOf(Subscriber::class, $action->subscriber[0]);
    }
}