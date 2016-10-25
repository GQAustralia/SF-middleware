<?php

use App\Message;
use App\Action;
use App\Subscriber;

class MessageTest extends TestCase
{
    /**
     * @test
     */
    public function it_belongs_to_a_action()
    {
        $model = Mockery::mock('App\Message[belongsTo]');

        $model->shouldReceive('belongsTo')->with(Action::class)->andReturn(true);

        $this->assertTrue($model->action());
    }

    /** @test */
    public function it_returns_action_on_calling_on_a_belongs_to_action_relationship()
    {
        $action = factory(Action::class)->create();
        $message = factory(Message::class, 2)->create([
            'action_id' => $action->id
        ]);

        $messageWithQueue = Message::with('action')->where('action_id', $action->id)->get();

        $this->assertInstanceOf(Action::class, $messageWithQueue[0]->action);
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