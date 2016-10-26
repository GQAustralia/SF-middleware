<?php

use App\Message;
use App\Queue;
use App\Subscriber;

class MessageTest extends TestCase
{
    /**
     * @test
     */
    public function it_belongs_to_a_queue()
    {
        $model = Mockery::mock('App\Message[belongsTo]');

        $model->shouldReceive('belongsTo')->with(Queue::class)->andReturn(true);

        $this->assertTrue($model->queue());
    }

    /** @test */
    public function it_returns_queue_on_calling_on_a_belongs_to_queue_relationship()
    {
        $queue = factory(Queue::class)->create();
        $message = factory(Message::class, 2)->create([
            'queue_id' => $queue->id
        ]);

        $messageWithQueue = Message::with('queue')->where('queue_id', $queue->id)->get();

        $this->assertInstanceOf(Queue::class, $messageWithQueue[0]->queue);
        $this->assertEquals(2, count($messageWithQueue));
    }

    /**
     * @test
     */
    public function it_belongs_to_many_subscriber()
    {
        $model = Mockery::mock('App\Message[belongsToMany]');

        $model->shouldReceive('belongsToMany')->with(Subscriber::class, 'sent_message')->andReturnSelf();
        $model->shouldReceive('withPivot')->andReturnSelf();
        $model->shouldReceive('withTimestamps')->andReturn(true);

        $this->assertTrue($model->subscriber());
    }

    /** @test */
    public function it_returns_subscriber_on_calling_on_a_many_to_many_subscriber_relationship()
    {
        $subscriber = factory(Subscriber::class)->create();
        $message = factory(Message::class)->create();

        $message->subscriber()->attach([$subscriber->id => ['status' => 'sent']]);

        $this->assertInstanceOf(Subscriber::class, $message->subscriber[0]);
        $this->assertEquals('sent', $message->subscriber[0]->pivot->status);
    }
}