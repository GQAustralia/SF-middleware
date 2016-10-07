<?php

use App\Message;
use App\Queue;
use App\Repositories\Eloquent\MessageRepositoryEloquent;
use App\Repositories\Exceptions\DuplicateRecordsException;
use App\Repositories\Exceptions\FailedSyncManyToMany;
use App\Subscriber;
use Illuminate\Database\Eloquent\Collection;

class MessageRepositoryEloquentTest extends BaseTestCase
{
    protected $repository;

    public function setUp()
    {
        parent::setUp();

        $this->repository = $this->app->make(MessageRepositoryEloquent::class);
    }

    /** @test */
    public function it_returns_message_on_instantiated_model()
    {
        $repository = new MessageRepositoryEloquent(new Message());

        $this->assertInstanceOf(Message::class, $repository->model());
    }

    /** @test */
    public function it_returns_message_on_create()
    {
        $que = factory(Queue::class)->create();
        $input = factory(Message::class)->make(['queue_id' => $que->id]);

        $result = $this->repository->create($input->toArray());

        $this->assertInstanceOf(Message::class, $result);
        $this->assertAttributesExpectedValues(
            ['message_id', 'queue_id', 'message_content', 'completed'],
            $input,
            $result
        );
        $this->seeInDatabase('message', [
            'message_id' => $input->message_id,
            'queue_id' => $input->queue_id,
            'message_content' => $input->message_content,
            'completed' => $input->completed
        ]);
    }

    /**
     * @test
     */
    public function it_throws_an_exception_on_create_when_duplicate_message_id()
    {
        $queue = factory(Queue::class)->create();
        $message = factory(Message::class)->create(['queue_id' => $queue->id]);
        $input = factory(Message::class)->make(['queue_id' => $queue->id, 'message_id' => $message->message_id]);

        $this->expectException(DuplicateRecordsException::class);

        $this->repository->create($input->toArray());
    }

    /** @test */
    public function it_returns_an_empty_collection_when_no_message_exist()
    {
        $result = $this->repository->all();

        $this->assertEmpty($result);
    }

    /** @test */
    public function it_returns_a_collection_of_message()
    {
        factory(Message::class, 5)->create();

        $result = $this->repository->all();

        $this->assertEquals(5, count($result));
        $this->assertInstanceOf(Message::class, $result[0]);
        $this->assertInstanceOf(Collection::class, $result);
    }

    /** @test */
    public function it_returns_a_collection_of_message_when_search_all_by_attribute()
    {
        $initialQueue = factory(Queue::class)->create();
        $extraQueue = factory(Queue::class, 2)->create();

        $message = factory(Message::class, 2)->create(['queue_id' => $initialQueue->id]);

        $result = $this->repository->findAllBy('queue_id', $initialQueue->id);

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertInstanceOf(Message::class, $result[0]);
        $this->assertEquals(2, count($result));
    }

    /** @test */
    public function it_returns_an_empty_collection_when_search_all_by_attribute()
    {
        $result = $this->repository->findAllBy('queue_id', 'unknownName');

        $this->assertEmpty($result);
        $this->assertInstanceOf(Collection::class, $result);
    }

    /** @test */
    public function it_returns_a_message_when_search_by_attribute()
    {
        $queue = factory(Queue::class)->create();
        $message = factory(Message::class)->create(['queue_id' => $queue->id]);

        $result = $this->repository->findBy('message_id', $message->message_id);

        $this->assertInstanceOf(Message::class, $result);
        $this->assertEquals($message->message_id, $result->message_id);
    }

    /** @test */
    public function it_returns_null_on_searching_que_by_attribute_when_no_queue_exist()
    {
        $result = $this->repository->findBy('message_id', 'unknownValue');

        $this->assertNull($result);
    }

    /** @test */
    public function it_inserts_multiple_attach_of_subscriber_on_sent_message_table()
    {
        $queue = factory(Queue::class)->create();
        $message = factory(Message::class)->create(['queue_id' => $queue->id]);
        $subscribers = factory(Subscriber::class, 4)->create();

        $input = collect($subscribers)->map(function ($subscriber) {
            return [$subscriber->id => ['status' => 'sent']];
        })->flatten(1)->toArray();

        $result = $this->repository->attachSubscriber($message, $input);

        $this->seeInDatabase('sent_message', [
            'message_id' => $message->id,
            'subscriber_id' => $subscribers[0]->id
        ]);
        $this->assertEquals(3, count($result->subscriber));
        $this->assertInstanceOf(Collection::class, $result->subscriber);
        $this->assertInstanceOf(Subscriber::class, $result->subscriber[0]);
        $this->assertInstanceOf(Message::class, $result);
    }

    /** @test */
    public function it_throws_exception_on_attach_subscriber_when_input_is_empty()
    {
        $this->expectException(FailedSyncManyToMany::class);

        $queue = factory(Queue::class)->create();
        $message = factory(Message::class)->create(['queue_id' => $queue->id]);

        $this->repository->attachSubscriber($message, []);
    }

    /** @test */
    public function it_throws_exception_on_attach_subscriber_when_message_does_not_exist()
    {
        $this->expectException(FailedSyncManyToMany::class);

        $subscribers = factory(Subscriber::class, 2)->create();
        $input = collect($subscribers)->map(function ($subscriber) {
            return [$subscriber->id => ['status' => 'sent']];
        })->flatten(1)->toArray();

        $this->repository->attachSubscriber(new Message(), $input);
    }

    /**
     * @test
     */
    public function it_returns_total_numbers_of_inserted_rows_on_each_insert()
    {
        $this->setConnection('test_mysql_database');

        $queue = factory(Queue::class)->create();
        $message = factory(Message::class, 2)->make(['queue_id' => $queue->id]);

        $result = $this->repository->insertIgnoreBulk($message->toArray());

        $this->assertEquals(2, $result);
        $this->assertMultipleSeeInDatabase('message', $message);
    }

    /** @test */
    public function it_returns_zero_when_no_insert_has_been_made()
    {
        $this->setConnection('test_mysql_database');

        $queue = factory(Queue::class)->create();
        $existingMessage = factory(Message::class, 2)->create(['queue_id' => $queue->id]);

        $result = collect($existingMessage)->map(function ($message) use ($queue) {
            return factory(Message::class)->make([
                'queue_id' => $queue->id,
                'message_id' => $message->message_id
            ])->toArray();
        })->toArray();

        $result = $this->repository->insertIgnoreBulk($result);

        $this->assertEquals(0, $result);
        $this->assertMultipleSeeInDatabase('message', $existingMessage);

    }
}