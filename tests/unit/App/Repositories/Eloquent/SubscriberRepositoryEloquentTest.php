<?php

use App\Action;
use App\Repositories\Eloquent\SubscriberRepositoryEloquent;
use App\Subscriber;
use Illuminate\Database\Eloquent\Collection;

class SubscriberRepositoryEloquentTest extends BaseTestCase
{
    private $repository;

    public function setUp()
    {
        parent::setUp();

        $this->repository = $this->app->make(SubscriberRepositoryEloquent::class);
    }

    /** @test */
    public function it_returns_subscriber_on_model_assign()
    {
        $repository = new SubscriberRepositoryEloquent(new Subscriber());

        $this->assertInstanceOf(Subscriber::class, $repository->model());
    }

    /** @test */
    public function it_returns_subscriber_on_create()
    {
        $input = factory(Subscriber::class)->make();

        $result = $this->repository->create($input->toArray());

        $this->assertInstanceOf(Subscriber::class, $result);
        $this->seeInDatabase('subscriber', [
            'platform_name' => $input->platform_name,
            'url' => $input->url
        ]);
        $this->assertAttributesExpectedValues(['platform_name', 'url'], $input, $result);
    }

    /** @test */
    public function it_returns_an_empty_collection_when_no_subscriber_exist()
    {
        $result = $this->repository->all();

        $this->assertEmpty($result);
    }

    /** @test */
    public function it_returns_a_collection_of_subscriber()
    {
        factory(Subscriber::class, 5)->create();

        $result = $this->repository->all();

        $this->assertEquals(5, count($result));
        $this->assertInstanceOf(Subscriber::class, $result[0]);
    }

    /** @test */
    public function it_returns_a_collection_of_subscriber_when_search_all_by_attribute()
    {
        $initialsubscriber = factory(Subscriber::class, 5)->create();
        $extrasubscriber = factory(Subscriber::class, 1)->create(['platform_name' => 'unknownName']);

        $result = $this->repository->findAllBy('platform_name', 'unknownName');

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertInstanceOf(Subscriber::class, $result[0]);
        $this->assertEquals(1, count($result));
    }

    /** @test */
    public function it_returns_an_empty_collection_when_search_all_by_attribute()
    {
        $result = $this->repository->findAllBy('platform_name', 'unknownName');

        $this->assertEmpty($result);
        $this->assertInstanceOf(Collection::class, $result);
    }

    /** @test */
    public function it_returns_a_subscriber_when_search_by_attribute()
    {
        $subscriber = factory(Subscriber::class, 1)->create(['platform_name' => 'unknownName']);

        $result = $this->repository->findBy('platform_name', $subscriber->platform_name);

        $this->assertInstanceOf(Subscriber::class, $result);
        $this->assertEquals($subscriber->platform_name, $result->platform_name);
    }

    /** @test */
    public function it_returns_null_on_searching_subscriber_by_attribute_when_no_subscriber_exist()
    {
        $result = $this->repository->findBy('platform_name', 'unknownName');

        $this->assertNull($result);
    }

    /** @test */
    public function it_returns_subscriber_with_collection_of_action_pivot_when_action_is_attached()
    {
        $subscriber = factory(Subscriber::class)->create();
        $action = factory(Action::class)->create();

        $result = $this->repository->attachAction($subscriber->id, $action->id);

        $this->seeInDatabase('action_subscriber', ['action_id' => $action->id, 'subscriber_id' => $subscriber->id]);
        $this->assertInstanceOf(Subscriber::class, $result);
        $this->assertInstanceOf(Collection::class, $result->action);
        $this->assertInstanceOf(Action::class, $result->action[0]);
    }

    /** @test */
    public function it_returns_null_on_action_attachment_when_subscriber_does_not_exist()
    {
        $action = factory(Action::class)->create();

        $result = $this->repository->attachAction('unknownId', $action->id);

        $this->assertNull($result);
    }
}
