<?php

use App\Action;
use App\Events\SqsMessagesWasSynced;
use App\Listeners\ProcessSyncedMessages;
use App\Message;
use App\Repositories\Eloquent\MessageLogRepositoryEloquent;
use App\Repositories\Eloquent\MessageRepositoryEloquent;
use App\Subscriber;

class ProcessSyncedMessagesTest extends BaseTestCase
{
    use AWSTestHelpers;

    /**
     * @var MessageRepositoryEloquent
     */
    private $message;

    /**
     * @var ProcessSyncedMessages
     */
    private $listener;

    /**
     * @var MessageLogRepositoryEloquent
     */
    private $messageLog;

    /**
     *
     */
    public function setUp()
    {
        parent::setUp(); // TODO: Change the autogenerated stub

        $this->listener = $this->app->make(ProcessSyncedMessages::class);
        $this->message = $this->app->make(MessageRepositoryEloquent::class);
        $this->messageLog = $this->app->make(MessageLogRepositoryEloquent::class);
    }

    /** @test */
    public function it_sets_status_sent_on_successful_message_sent_to_subscriber()
    {
        $eventInstance = $this->prepareInstanceOfSqsMessageWasSynced(['url' => url($this->SUCCESS_RESPONSE_SITE())]);

        $messages = $this->message->findAllWhereIn('message_id', $eventInstance->messageIdList, ['action']);
        $subscriberAttachInput = collect([]);
        foreach ($messages as $message) {
            foreach ($message->action->subscriber as $subscriber) {
                $subscriberAttachInput->put($subscriber->id, ['status' => 'sent']);
            }
        }

        $this->listener->handle($eventInstance);

        $this->assertMultipleSeeInDatabase('sent_message', $subscriberAttachInput->toArray());
    }

    /**
     * @param array $subscriberCreateOptions
     * @return SqsMessagesWasSynced
     */
    private function prepareInstanceOfSqsMessageWasSynced($subscriberCreateOptions = [])
    {
        $firstaction = factory(Action::class)->create(['name' => 'changed']);
        $secondaction = factory(Action::class)->create(['name' => 'changed']);

        $messagesOnFirstaction = factory(Message::class, 5)->create([
            'action_id' => $firstaction->id,
            'message_content' => $this->SAMPLE_SALESFORCE_TO_SQS_MESSAGE()
        ]);
        $messagesOnSecondaction = factory(Message::class, 5)->create([
            'action_id' => $secondaction->id,
            'message_content' => $this->SAMPLE_SALESFORCE_TO_SQS_MESSAGE()
        ]);

        $subscriberOne = factory(Subscriber::class, 3)->create($subscriberCreateOptions);
        $subscriberTwo = factory(Subscriber::class, 2)->create($subscriberCreateOptions);

        $firstaction->subscriber()->attach(collect($subscriberOne)->pluck('id')->toArray());
        $secondaction->subscriber()->attach(collect($subscriberTwo)->pluck('id')->toArray());

        $firstMessageIdList = $this->collectMessageIdsOnactions($messagesOnFirstaction);
        $secondMessageIdList = $this->collectMessageIdsOnactions($messagesOnSecondaction);

        $messageIdList = array_merge($firstMessageIdList, $secondMessageIdList);

        return new SqsMessagesWasSynced($messageIdList);
    }

    /**
     * @param array $messages
     * @return array
     */
    private function collectMessageIdsOnactions($messages)
    {
        $messageIdList = [];

        foreach ($messages as $message) {
            $messageIdList[] = $message->message_id;
        }

        return $messageIdList;
    }

    /** @test */
    public function it_sets_status_failed_to_unsuccessful_message_sent_to_subscriber()
    {
        $eventInstance = $this->prepareInstanceOfSqsMessageWasSynced(['url' => url($this->UNSUCCESSFUL_RESPONSE_SITE())]);

        $messages = $this->message->findAllWhereIn('message_id', $eventInstance->messageIdList, ['action']);
        $subscriberAttachInput = collect([]);
        foreach ($messages as $message) {
            foreach ($message->action->subscriber as $subscriber) {
                $subscriberAttachInput->put($subscriber->id, ['status' => 'failed']);
            }
        }

        $this->listener->handle($eventInstance);

        $this->assertMultipleSeeInDatabase('sent_message', $subscriberAttachInput->toArray());
    }

    //to test unserialized input

    /** @test */
    public function it_sets_status_failed_when_subscriber_url_is_invalid()
    {
        $eventInstance = $this->prepareInstanceOfSqsMessageWasSynced(['url' => url('http://mueller.org/quas-vel-non-nisi-quia-tenetur-aut-culpa')]);
        $subscriberAttachInput = $this->buildToAssertIfEqualMessageInput($eventInstance->messageIdList, 'failed');

        $this->listener->handle($eventInstance);

        $this->assertMultipleSeeInDatabase('sent_message', $subscriberAttachInput->toArray());
    }

    /**
     * @param array $messageIdList
     * @param string $status
     * @return \Illuminate\Support\Collection
     */
    private function buildToAssertIfEqualMessageInput($messageIdList, $status = 'sent')
    {
        $messages = $this->message->findAllWhereIn('message_id', $messageIdList, ['action']);
        $subscriberAttachInput = collect([]);
        foreach ($messages as $message) {
            foreach ($message->action->subscriber as $subscriber) {
                $subscriberAttachInput->put($subscriber->id, ['status' => $status]);
            }
        }

        return $subscriberAttachInput;
    }

    /** @test */
    public function it_does_not_add_to_attach_if_there_is_no_associated_subscriber_on_message_action()
    {
        $firstaction = factory(Action::class)->create(['name' => 'changed']);
        $secondaction = factory(Action::class)->create(['name' => 'changed']);
        $messagesOnFirstaction = factory(Message::class, 5)->create([
            'action_id' => $firstaction->id,
            'message_content' => $this->SAMPLE_SALESFORCE_TO_SQS_MESSAGE()
        ]);
        $messagesOnSecondaction = factory(Message::class, 5)->create([
            'action_id' => $secondaction->id,
            'message_content' => $this->SAMPLE_SALESFORCE_TO_SQS_MESSAGE()
        ]);
        $subscriberOne = factory(Subscriber::class, 3)->create(['url' => url($this->SUCCESS_RESPONSE_SITE())]);

        $firstaction->subscriber()->attach(collect($subscriberOne)->pluck('id')->toArray());

        $firstMessageIdList = $this->collectMessageIdsOnactions($messagesOnFirstaction);
        $secondMessageIdList = $this->collectMessageIdsOnactions($messagesOnSecondaction);

        $messageIdList = array_merge($firstMessageIdList, $secondMessageIdList);
        $sqsMessageWasSynced = new SqsMessagesWasSynced($messageIdList);

        $subscriberAttachInput = $this->buildToAssertIfEqualMessageInput($sqsMessageWasSynced->messageIdList);

        $this->listener->handle($sqsMessageWasSynced);

        $savedMessages = Message::with('subscriber')->get();

        $total = 0;
        foreach ($savedMessages as $message) {
            $total += $message->subscriber->count();
        }

        $this->assertMultipleSeeInDatabase('sent_message', $subscriberAttachInput->toArray());
        $this->notSeeInDatabase('sent_message', ['status' => 'failed']);
        $this->assertEquals(15, $total);
    }

    public function it_receives_to_be_decoded_form_input()
    {
        $action = factory(Action::class)->create(['name' => 'changed']);
        $message = factory(Message::class)->create([
            'action_id' => $action->id,
            'message_content' => $this->SAMPLE_SALESFORCE_TO_SQS_MESSAGE()
        ]);

        $subscriber = factory(Subscriber::class)->create(['url' => url($this->FORMS_PARAMS_RESPONSE_SITE())]);

        $action->subscriber()->attach($subscriber->id);

        $sqsMessageWasSynced = new SqsMessagesWasSynced([$message->message_id]);
        $this->listener->handle($sqsMessageWasSynced);
    }

    /** @test */
    public function it_insert_to_message_logs()
    {
        $eventInstance = $this->prepareInstanceOfSqsMessageWasSynced(['url' => url($this->SUCCESS_RESPONSE_SITE())]);

        $messages = $this->message->findAllWhereIn('message_id', $eventInstance->messageIdList, ['action']);
        $subscriberAttachInput = collect([]);
        foreach ($messages as $message) {
            foreach ($message->action->subscriber as $subscriber) {
                $subscriberAttachInput->put($subscriber->id, ['status' => 'sent']);
            }
        }

        $this->listener->handle($eventInstance);

        $this->assertEquals(25, $this->messageLog->all()->count());
    }

}
