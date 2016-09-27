<?php

use App\Message;
use App\Queue;
use App\Subscriber;

class QueueTest extends TestCase
{

    /** @test */
    public function it_has_many_message()
    {
        $model = Mockery::mock('App\Queue[hasMany]');

        $model->shouldReceive('hasMany')->with(Message::class)->andReturn(true);

        $this->assertTrue($model->message());
    }

    /** @test */
    public function it_returns_message_on_calling_on_a_has_many_message_relationship()
    {
        $queue = factory(Queue::class)->create();
        $message = factory(Message::class, 3)->create(['queue_id' => $queue->id]);

        $this->assertInstanceOf(Message::class, $queue->message[0]);
        $this->assertEquals(3, count($queue->message));
    }

    /** @test */
    public function it_belongs_to_many_subscriber()
    {
        $model = Mockery::mock('App\Queue[belongsToMany]');

        $model->shouldReceive('belongsToMany')->with(Subscriber::class)->andReturnSelf();
        $model->shouldReceive('withTimestamps')->andReturn(true);

        $this->assertTrue($model->subscriber());
    }

    /** @test */
    public function it_returns_subscriber_on_calling_a_many_to_many_subscriber_relationship()
    {
        $queue = factory(Queue::class)->create();
        $subscriber = factory(Subscriber::class)->create();

        $queue->subscriber()->attach($subscriber->id);

        $this->assertInstanceOf(Subscriber::class, $queue->subscriber[0]);
    }
}