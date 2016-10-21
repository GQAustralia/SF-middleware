<?php

use App\Message;
use App\Queue;
use App\Resolvers\MessageStatusResolver;
use App\Subscriber;
use App\Repositories\Eloquent\MessageRepositoryEloquent;

class MessageStatusResolverTest extends BaseTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->resolver = $this->app->make(MessageStatusResolver::class);
        $this->repository = $this->app->make(MessageRepositoryEloquent::class);
    }

    /** @test */
    public function it_returns_null_when_message_id_payload_is_empty()
    {
        $result = $this->resolver->resolve([]);

        $this->assertNull($result);
    }

    /** @test */
    public function it_does_not_update_message_status_when_one_subscriber_url_has_failed_to_send()
    {

    }

    /** @test */
    public function it_updates_message_status_to_complete_when_subscriber_url_are_all_sent()
    {
        $queue = factory(Queue::class)->create();
        $message = factory(Message::class)->create(['queue_id' => $queue->id, 'completed' => 'N']);

        $subscriber = factory(Subscriber::class)->create();
        $subscriberSecond = factory(Subscriber::class)->create();

        $this->repository->attachSubscriber($message, [
            $subscriber->id => ['status' => 'sent'],
            $subscriberSecond->id => ['status' => 'sent']
        ]);

        $this->resolver->resolve([$message->message_id]);

    }
}