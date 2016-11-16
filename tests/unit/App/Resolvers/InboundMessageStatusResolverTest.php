<?php

use App\Action;
use App\InboundMessage;
use App\Repositories\Eloquent\InboundMessageRepositoryEloquent;
use App\Resolvers\InboundMessageStatusResolver;
use App\Subscriber;

class InboundMessageStatusResolverTest extends BaseTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->resolver = $this->app->make(InboundMessageStatusResolver::class);
        $this->repository = $this->app->make(InboundMessageRepositoryEloquent::class);
    }

    /** @test */
    public function locateTest()
    {
        $this->runningTestFor(get_class($this));
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
        $queue = factory(Action::class)->create();
        $message = factory(InboundMessage::class)->create(['action_id' => $queue->id, 'completed' => 'N']);

        $subscriber = factory(Subscriber::class)->create();
        $subscriberSecond = factory(Subscriber::class)->create();

        $this->repository->attachSubscriber($message, [
            $subscriber->id => ['status' => 'sent'],
            $subscriberSecond->id => ['status' => 'sent']
        ]);

        $this->resolver->resolve([$message->message_id]);

    }
}