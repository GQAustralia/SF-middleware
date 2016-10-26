<?php

use App\Message;
use App\Action;
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
        $action = factory(Action::class)->create();
        $input = factory(Message::class)->make(['action_id' => $action->id]);

        $result = $this->repository->create($input->toArray());

        $this->assertInstanceOf(Message::class, $result);
        $this->assertAttributesExpectedValues(
            ['message_id', 'action_id', 'message_content', 'completed'],
            $input,
            $result
        );
        $this->seeInDatabase('message', [
            'message_id' => $input->message_id,
            'action_id' => $input->action_id,
            'message_content' => $input->message_content,
            'completed' => $input->completed
        ]);
    }

    /**
     * @test
     */
    public function it_throws_an_exception_on_create_when_duplicate_message_id()
    {
        $action = factory(Action::class)->create();
        $message = factory(Message::class)->create(['action_id' => $action->id]);
        $input = factory(Message::class)->make(['action_id' => $action->id, 'message_id' => $message->message_id]);

        $this->setExpectedException(DuplicateRecordsException::class);

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
        $initialAction = factory(Action::class)->create();
        $extraAction = factory(Action::class, 2)->create();

        $message = factory(Message::class, 2)->create(['action_id' => $initialAction->id]);

        $result = $this->repository->findAllBy('action_id', $initialAction->id);

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertInstanceOf(Message::class, $result[0]);
        $this->assertEquals(2, count($result));
    }

    /** @test */
    public function it_returns_an_empty_collection_when_search_all_by_attribute()
    {
        $result = $this->repository->findAllBy('action_id', 'unknownName');

        $this->assertEmpty($result);
        $this->assertInstanceOf(Collection::class, $result);
    }

    /** @test */
    public function it_returns_a_message_when_search_by_attribute()
    {
        $action = factory(Action::class)->create();
        $message = factory(Message::class)->create(['action_id' => $action->id]);

        $result = $this->repository->findBy('message_id', $message->message_id);

        $this->assertInstanceOf(Message::class, $result);
        $this->assertEquals($message->message_id, $result->message_id);
    }

    /** @test */
    public function it_returns_null_on_searching_que_by_attribute_when_no_action_exist()
    {
        $result = $this->repository->findBy('message_id', 'unknownValue');

        $this->assertNull($result);
    }

    /** @test */
    public function it_inserts_multiple_attach_of_subscriber_on_sent_message_table()
    {
        $action = factory(Action::class)->create();
        $message = factory(Message::class)->create(['action_id' => $action->id]);
        $subscribers = factory(Subscriber::class, 4)->create();

        $subscriberAttachInput = collect([]);

        foreach ($subscribers as $subscriber) {
            $subscriberAttachInput->put($subscriber->id, ['status' => 'Y']);
        }

        $result = $this->repository->attachSubscriber($message, $subscriberAttachInput->toArray());

        $this->assertMultipleSeeInDatabase('sent_message', $subscriberAttachInput->toArray());

        $this->assertEquals(4, count($result->subscriber));
        $this->assertInstanceOf(Collection::class, $result->subscriber);
        $this->assertInstanceOf(Subscriber::class, $result->subscriber[0]);
        $this->assertInstanceOf(Message::class, $result);
    }

    /** @test */
    public function it_throws_exception_on_attach_subscriber_when_input_subscriber_is_empty()
    {
        $this->setExpectedException(FailedSyncManyToMany::class, 'Subscribers does not exist.');

        $action = factory(Action::class)->create(['name' => 'changed']);
        $message = factory(Message::class)->create(['action_id' => $action->id]);

        $this->repository->attachSubscriber($message, []);
    }

    /** @test */
    public function it_throws_exception_on_attach_subscriber_when_message_does_not_exist()
    {
        $this->setExpectedException(FailedSyncManyToMany::class, 'Message does not exist.');

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

        $action = factory(Action::class)->create();
        $message = factory(Message::class, 2)->make(['action_id' => $action->id]);

        $result = $this->repository->insertIgnoreBulk($message->toArray());

        $this->assertEquals(2, $result);
        $this->assertMultipleSeeInDatabase('message', $message);
    }

    /** @test */
    public function it_returns_zero_when_no_insert_has_been_made()
    {
        $this->setConnection('test_mysql_database');

        $action = factory(Action::class)->create();
        $existingMessage = factory(Message::class, 2)->create(['action_id' => $action->id]);

        $result = collect($existingMessage)->map(function ($message) use ($action) {
            return factory(Message::class)->make([
                'action_id' => $action->id,
                'message_id' => $message->message_id
            ])->toArray();
        })->toArray();

        $result = $this->repository->insertIgnoreBulk($result);

        $this->assertEquals(0, $result);
        $this->assertMultipleSeeInDatabase('message', $existingMessage);
    }

    /** @test */
    public function it_returns_a_collection_of_message_when_searched_using_wherein()
    {
        $action = factory(Action::class)->create();
        $message = factory(Message::class, 5)->create(['action_id' => $action->id]);

        $messageIds = collect($message->toArray())->pluck('message_id');

        $result = $this->repository->findAllWhereIn('message_id', $messageIds, ['action']);

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertInstanceOf(Message::class, $result[0]);
    }

    /** @test */
    public function it_returns_a_list_of_subscribers_to_each_message_when_using_wherein()
    {
        $action = factory(Action::class)->create();
        $message = factory(Message::class, 5)->create(['action_id' => $action->id]);
        $subscriber = factory(Subscriber::class, 5)->create();
        $subscriberIds = collect($subscriber)->pluck('id')->toArray();

        $action->subscriber()->attach($subscriberIds);

        $messageIds = collect($message->toArray())->pluck('message_id');

        $result = $this->repository->findAllWhereIn('message_id', $messageIds, ['action']);

        $this->assertInstanceOf(Action::class, $result[0]->action);
        $this->assertInstanceOf(Subscriber::class, $result[0]->action->subscriber[0]);
    }

    /** @test */
    public function it_returns_an_empty_collection_on_using_wherein_when_message_id_does_not_exist()
    {
        $result = $this->repository->findAllWhereIn('message_id', [], ['action']);

        $this->assertEmpty($result);
    }

    /** @test */
    public function it_returns_a_collection_on_using_wherein_and_optional_where()
    {
        $action = factory(Action::class)->create();
        $message = factory(Message::class, 5)->create(['action_id' => $action->id]);
        $subscriber = factory(Subscriber::class, 5)->create();
        $subscriberIds = collect($subscriber)->pluck('id')->toArray();

        $action->subscriber()->attach($subscriberIds);

        $messageIds = collect($message->toArray())->pluck('message_id');

        $result = $this->repository->findAllWhereIn('message_id', $messageIds, ['action'],
            ['action_id' => $action->id]);

        $this->assertInstanceOf(Action::class, $result[0]->action);
        $this->assertInstanceOf(Subscriber::class, $result[0]->action->subscriber[0]);
    }

    /** @test */
    public function it_returns_an_empty_collection_on_using_wherein_and_optional_where_when_optional_where_is_not_found(
    )
    {
        $action = factory(Action::class)->create();
        $message = factory(Message::class, 5)->create(['action_id' => $action->id]);
        $subscriber = factory(Subscriber::class, 5)->create();
        $subscriberIds = collect($subscriber)->pluck('id')->toArray();

        $action->subscriber()->attach($subscriberIds);

        $messageIds = collect($message->toArray())->pluck('message_id');

        $result = $this->repository->findAllWhereIn('message_id', $messageIds, ['action'], ['action_id' => 2]);

        $this->assertEmpty($result);
    }

    /** @test */
    public function it_returns_total_number_of_failed_sent_message()
    {
        $action = factory(Action::class)->create();
        $message = factory(Message::class)->create(['action_id' => $action->id]);
        $subscriber = factory(Subscriber::class)->create();

        $message->subscriber()->attach([$subscriber->id => ['status' => 'failed']]);

        $result = $this->repository->getTotalFailSentMessage($message->message_id);

        $this->assertEquals(1, $result);
    }

    /** @test */
    public function it_returns_null_on_update_when_message_does_not_exist()
    {
        $result = $this->repository->update(['completed' => 'Y'], 'unknownId');

        $this->assertNull($result);
    }

    /** @test */
    public function it_returns_message_on_update_on_successful_update()
    {
        $message = factory(Message::class)->create(['completed' => 'N']);

        $updateInput = ['completed' => 'Y'];

        $result = $this->repository->update($updateInput, $message->message_id);

        $this->assertInstanceOf(Message::class, $result);
        $this->seeInDatabase('message', [
            'message_id' => $message->message_id,
            'completed' => 'Y'
        ]);
    }

}
