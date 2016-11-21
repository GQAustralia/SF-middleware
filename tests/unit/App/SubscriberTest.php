<?php

use App\Action;
use App\InboundMessage;
use App\Subscriber;

class SubscriberTest extends BaseTestCase
{
    /** @test */
    public function locateTest()
    {
        $this->runningTestFor(get_class($this));
    }

    /** @test */
    public function it_belongs_to_many_action()
    {
        $model = Mockery::mock('App\Subscriber[belongsToMany]');

        $model->shouldReceive('belongsToMany')->with(Action::class)->andReturnSelf();
        $model->shouldReceive('withTimestamps')->andReturn(true);

        $this->assertTrue($model->action());
    }

    /** @test */
    public function it_returns_action_on_a_many_to_many_action_relationship()
    {
        $subscriber = factory(Subscriber::class)->create();
        $action = factory(Action::class)->create();

        $subscriber->action()->attach($subscriber->id);

        $this->assertInstanceOf(Action::class, $subscriber->action[0]);
    }

    /** @test */
    public function it_belongs_to_many_inbound_message()
    {
        $model = Mockery::mock('App\Subscriber[belongsToMany]');

        $model->shouldReceive('belongsToMany')->with(Action::class)->andReturnSelf();
        $model->shouldReceive('withPivot')->andReturnSelf();
        $model->shouldReceive('withTimestamps')->andReturn(true);

        $this->assertTrue($model->action());
    }

    /** @test */
    public function it_returns_message_on_calling_on_a_many_to_many_message_relationship()
    {
        $subscriber = factory(Subscriber::class)->create();
        $inboundMessage = factory(InboundMessage::class)->create();

        $subscriber->inboundMessage()->attach([$inboundMessage->id => ['status' => 'sent']]);

        $this->assertInstanceOf(InboundMessage::class, $subscriber->inboundMessage[0]);
    }
}
