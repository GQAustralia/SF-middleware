<?php

use App\Message;
use App\Queue;
use App\Subscriber;

class SubscriberTest extends TestCase
{
    /** @test */
    public function it_belongs_to_many_queue()
    {
        $model = Mockery::mock('App\Subscriber[belongsToMany]');

        $model->shouldReceive('belongsToMany')->with(Queue::class)->andReturnSelf();
        $model->shouldReceive('withTimestamps')->andReturn(true);

        $this->assertTrue($model->queue());
    }

    /** @test */
    public function it_returns_queue_on_a_many_to_many_queue_relationship()
    {
        $subscriber = factory(Subscriber::class)->create();
        $queue = factory(Queue::class)->create();

        $subscriber->queue()->attach($subscriber->id);

        $this->assertInstanceOf(Queue::class, $subscriber->queue[0]);
    }

    /** @test */
    public function it_belongs_to_many_message()
    {
        $model = Mockery::mock('App\Subscriber[belongsToMany]');

        $model->shouldReceive('belongsToMany')->with(Queue::class)->andReturnSelf();
        $model->shouldReceive('withPivot')->andReturnSelf();
        $model->shouldReceive('withTimestamps')->andReturn(true);

        $this->assertTrue($model->queue());
    }

    /** @test */
    public function it_returns_message_on_calling_on_a_many_to_many_message_relationship()
    {
        $subscriber = factory(Subscriber::class)->create();
        $message = factory(Message::class)->create();

        $subscriber->message()->attach([$message->id => ['status' => 'sent']]);

        $this->assertInstanceOf(Message::class, $subscriber->message[0]);
    }
}