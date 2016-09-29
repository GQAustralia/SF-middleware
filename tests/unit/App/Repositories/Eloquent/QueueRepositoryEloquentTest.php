<?php

use App\Queue;
use App\Repositories\Eloquent\QueueRepositoryEloquent;
use App\Subscriber;
use Illuminate\Database\Eloquent\Collection;

class QueueRepositoryEloquentTest extends BaseTestCase
{
    private $repository;

    public function setUp()
    {
        parent::setUp();

        $this->repository = $this->app->make(QueueRepositoryEloquent::class);
    }

    /** @test */
    public function it_returns_queue_on_model_assign()
    {
        $repository = new QueueRepositoryEloquent(new Queue());

        $this->assertInstanceOf(Queue::class, $repository->model());
    }

    /** @test */
    public function it_returns_queue_on_create()
    {
        $input = factory(Queue::class)->make();

        $result = $this->repository->create($input->toArray());

        $this->assertInstanceOf(Queue::class, $result);
        $this->seeInDatabase('queue', [
            'queue_name' => $input->queue_name,
            'aws_queue_name' => $input->aws_queue_name,
            'arn' => $input->arn
        ]);
        $this->assertAttributesExpectedValues(['queue_name', 'aws_queue_name', 'arn'], $input, $result);
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
        factory(Queue::class, 5)->create();

        $result = $this->repository->all();

        $this->assertEquals(5, count($result));
        $this->assertInstanceOf(Queue::class, $result[0]);
        $this->assertInstanceOf(Collection::class, $result);
    }

    /** @test */
    public function it_returns_a_collection_of_queue_when_search_all_by_attribute()
    {
        $initialQueue = factory(Queue::class, 5)->create();
        $extraQueue = factory(Queue::class, 1)->create(['queue_name' => 'unknownName']);

        $result = $this->repository->findAllBy('queue_name', 'unknownName');

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertInstanceOf(Queue::class, $result[0]);
        $this->assertEquals(1, count($result));
    }

    /** @test */
    public function it_returns_an_empty_collection_when_search_all_by_attribute()
    {
        $result = $this->repository->findAllBy('queue_name', 'unknownName');

        $this->assertEmpty($result);
        $this->assertInstanceOf(Collection::class, $result);
    }

    /** @test */
    public function it_returns_a_que_when_search_by_attribute()
    {
        $queue = factory(Queue::class, 1)->create(['queue_name' => 'unknownName']);

        $result = $this->repository->findBy('queue_name', $queue->queue_name);

        $this->assertInstanceOf(Queue::class, $result);
        $this->assertEquals($queue->queue_name, $result->queue_name);
    }

    /** @test */
    public function it_returns_null_on_searching_que_by_attribute_when_no_queue_exist()
    {
        $result = $this->repository->findBy('queue_name', 'unknownName');

        $this->assertNull($result);
    }

    /** @test */
    public function it_returns_queue_when_subscriber_is_attached()
    {
        $subscriber = factory(Subscriber::class)->create();
        $queue = factory(Queue::class)->create();

        $result = $this->repository->attachSubscriber($queue->id, $subscriber->id);

        $this->seeInDatabase('queue_subscriber', ['queue_id' => $queue->id, 'subscriber_id' => $subscriber->id]);
        $this->assertInstanceOf(Queue::class, $result);
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