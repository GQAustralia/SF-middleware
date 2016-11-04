<?php

use App\Action;
use App\Repositories\Eloquent\ActionRepositoryEloquent;
use App\Subscriber;
use Illuminate\Database\Eloquent\Collection;

class ActionRepositoryEloquentTest extends BaseTestCase
{
    private $repository;

    public function setUp()
    {
        parent::setUp();

        $this->repository = $this->app->make(ActionRepositoryEloquent::class);
    }

    /** @test */
    public function it_returns_queue_on_model_assign()
    {
        $repository = new ActionRepositoryEloquent(new Action());

        $this->assertInstanceOf(Action::class, $repository->model());
    }

    /** @test */
    public function it_returns_queue_on_create()
    {
        $input = factory(Action::class)->make();

        $result = $this->repository->create($input->toArray());

        $this->assertInstanceOf(Action::class, $result);
        $this->seeInDatabase('action', ['name' => $input->name]);
        $this->assertAttributesExpectedValues(['name'], $input, $result);
    }

    /** @test */
    public function it_returns_an_empty_collection_when_no_queue_exist()
    {
        $result = $this->repository->all();

        $this->assertEmpty($result);
    }

    /** @test */
    public function it_returns_a_collection_of_queue()
    {
        factory(Action::class, 5)->create();

        $result = $this->repository->all();

        $this->assertEquals(5, count($result));
        $this->assertInstanceOf(Action::class, $result[0]);
        $this->assertInstanceOf(Collection::class, $result);
    }

    /** @test */
    public function it_returns_a_collection_of_queue_when_search_all_by_attribute()
    {
        $initialQueue = factory(Action::class, 5)->create();
        $extraQueue = factory(Action::class, 1)->create(['name' => 'unknownName']);

        $result = $this->repository->findAllBy('name', 'unknownName');

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertInstanceOf(Action::class, $result[0]);
        $this->assertEquals(1, count($result));
    }

    /** @test */
    public function it_returns_an_empty_collection_when_search_all_by_attribute()
    {
        $result = $this->repository->findAllBy('name', 'unknownName');

        $this->assertEmpty($result);
        $this->assertInstanceOf(Collection::class, $result);
    }

    /** @test */
    public function it_returns_a_que_when_search_by_attribute()
    {
        $action = factory(Action::class, 1)->create(['name' => 'unknownName']);

        $result = $this->repository->findBy('name', $action->name);

        $this->assertInstanceOf(Action::class, $result);
        $this->assertEquals($action->name, $result->name);
    }

    /** @test */
    public function it_returns_null_on_searching_que_by_attribute_when_no_queue_exist()
    {
        $result = $this->repository->findBy('name', 'unknownName');

        $this->assertNull($result);
    }

    /** @test */
    public function it_returns_queue_when_subscriber_is_attached()
    {
        $subscriber = factory(Subscriber::class)->create();
        $action = factory(Action::class)->create();

        $result = $this->repository->attachSubscriber($action->id, $subscriber->id);

        $this->seeInDatabase('action_subscriber', ['action_id' => $action->id, 'subscriber_id' => $subscriber->id]);
        $this->assertInstanceOf(Action::class, $result);
        $this->assertInstanceOf(Collection::class, $result->subscriber);
        $this->assertInstanceOf(Subscriber::class, $result->subscriber[0]);
    }

    /** @test */
    public function it_returns_null_on_subscriber_attachment_when_que_does_not_exist()
    {
        $subscriber = factory(Subscriber::class)->create();

        $result = $this->repository->attachSubscriber('unknownId', $subscriber->id);

        $this->assertNull($result);
    }
}
