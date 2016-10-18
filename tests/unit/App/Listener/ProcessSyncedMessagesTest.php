<?php

use App\Events\SqsMessagesWasSynced;
use App\Listeners\ProcessSyncedMessages;
use App\Message;
use App\Queue;
use App\Repositories\Eloquent\MessageRepositoryEloquent;
use App\Subscriber;

class ProcessSyncedMessagesTest extends BaseTestCase
{
    const SUCCESS_RESPONSE_SITE = 'http://gq-message-queuing-service.dev/example-response/success';
    const UNSUCCESSFUL_RESPONSE_SITE = 'http://gq-message-queuing-service.dev/example-response/failed';
    const FORMS_PARAMS_RESPONSE_SITE = 'http://gq-message-queuing-service.dev/example-response/form_params';

    /**
     * @var MessageRepositoryEloquent
     */
    private $message;

    /**
     * @var ProcessSyncedMessages
     */
    private $listener;

    /**
     *
     */
    public function setUp()
    {
        parent::setUp(); // TODO: Change the autogenerated stub

        $this->listener = $this->app->make(ProcessSyncedMessages::class);
        $this->message = $this->app->make(MessageRepositoryEloquent::class);
    }

    /** @test */
    public function it_sets_status_sent_on_successful_message_sent_to_subscriber()
    {
        $eventInstance = $this->prepareInstanceOfSqsMessageWasSynced(['url' => url(self::SUCCESS_RESPONSE_SITE)]);

        $messages = $this->message->findAllWhereIn('message_id', $eventInstance->messageIdList, ['queue']);
        $subscriberAttachInput = collect([]);
        foreach ($messages as $message) {
            foreach ($message->queue->subscriber as $subscriber) {
                $subscriberAttachInput->put($subscriber->id, ['status' => 'sent']);
            }
        }

        $this->listener->handle($eventInstance);

        $this->assertMultipleSeeInDatabase('sent_message', $subscriberAttachInput->toArray());
    }

    /** @test */
    public function it_sets_status_failed_to_unsuccessful_message_sent_to_subscriber()
    {
        $eventInstance = $this->prepareInstanceOfSqsMessageWasSynced(['url' => url(self::UNSUCCESSFUL_RESPONSE_SITE)]);

        $messages = $this->message->findAllWhereIn('message_id', $eventInstance->messageIdList, ['queue']);
        $subscriberAttachInput = collect([]);
        foreach ($messages as $message) {
            foreach ($message->queue->subscriber as $subscriber) {
                $subscriberAttachInput->put($subscriber->id, ['status' => 'failed']);
            }
        }

        $this->listener->handle($eventInstance);

        $this->assertMultipleSeeInDatabase('sent_message', $subscriberAttachInput->toArray());
    }

    /** @test */
    public function it_sets_status_failed_when_subscriber_url_is_invalid()
    {
        $eventInstance = $this->prepareInstanceOfSqsMessageWasSynced(['url' => url('http://nonExistingUrl.com')]);
        $subscriberAttachInput = $this->buildToAssertIfEqualMessageInput($eventInstance->messageIdList, 'failed');

        $this->listener->handle($eventInstance);

        $this->assertMultipleSeeInDatabase('sent_message', $subscriberAttachInput->toArray());
    }

    /** @test */
    public function it_does_not_add_to_attach_if_there_is_no_associated_subscriber_on_message_queue()
    {
        $firstQueue = factory(Queue::class)->create();
        $secondQueue = factory(Queue::class)->create();
        $messagesOnFirstQueue = factory(Message::class, 5)->create([
            'queue_id' => $firstQueue->id,
            'message_content' => $this->sampleSalesForceMessage()
        ]);
        $messagesOnSecondQueue = factory(Message::class, 5)->create([
            'queue_id' => $secondQueue->id,
            'message_content' => $this->sampleSalesForceMessage()
        ]);
        $subscriberOne = factory(Subscriber::class, 3)->create(['url' => url(self::SUCCESS_RESPONSE_SITE)]);

        $firstQueue->subscriber()->attach(collect($subscriberOne)->pluck('id')->toArray());

        $firstMessageIdList = $this->collectMessageIdsOnQueues($messagesOnFirstQueue);
        $secondMessageIdList = $this->collectMessageIdsOnQueues($messagesOnSecondQueue);

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

    //to test unserialized input
    public function it_receives_the_unserialized_form_input()
    {
        $queue = factory(Queue::class)->create();
        $message = factory(Message::class)->create([
            'queue_id' => $queue->id,
            'message_content' => $this->sampleSalesForceMessage()
        ]);

        $subscriber = factory(Subscriber::class)->create(['url' => url(self::FORMS_PARAMS_RESPONSE_SITE)]);

        $queue->subscriber()->attach($subscriber->id);

        $sqsMessageWasSynced = new SqsMessagesWasSynced([$message->message_id]);
        $this->listener->handle($sqsMessageWasSynced);
    }

    /**
     * @param array $subscriberCreateOptions
     * @return SqsMessagesWasSynced
     */
    private function prepareInstanceOfSqsMessageWasSynced($subscriberCreateOptions = [])
    {
        $firstQueue = factory(Queue::class)->create();
        $secondQueue = factory(Queue::class)->create();

        $messagesOnFirstQueue = factory(Message::class, 5)->create([
            'queue_id' => $firstQueue->id,
            'message_content' => $this->sampleSalesForceMessage()
        ]);
        $messagesOnSecondQueue = factory(Message::class, 5)->create([
            'queue_id' => $secondQueue->id,
            'message_content' => $this->sampleSalesForceMessage()
        ]);

        $subscriberOne = factory(Subscriber::class, 3)->create($subscriberCreateOptions);
        $subscriberTwo = factory(Subscriber::class, 2)->create($subscriberCreateOptions);

        $firstQueue->subscriber()->attach(collect($subscriberOne)->pluck('id')->toArray());
        $secondQueue->subscriber()->attach(collect($subscriberTwo)->pluck('id')->toArray());

        $firstMessageIdList = $this->collectMessageIdsOnQueues($messagesOnFirstQueue);
        $secondMessageIdList = $this->collectMessageIdsOnQueues($messagesOnSecondQueue);

        $messageIdList = array_merge($firstMessageIdList, $secondMessageIdList);

        return new SqsMessagesWasSynced($messageIdList);
    }

    /**
     * @return string
     */
    private function sampleSalesForceMessage()
    {
        return "a:11:{s:6:'amount';s:0:'';s:8:'assessor';s:18:'696292000018247009';s:2:'op';s:7:'changed';s:6:'status';s:4:'Open';s:3:'rto';s:5:'31718';s:5:'token';s:20:'fb706b1e933ef01e4fb6';s:2:'mb';s:0:'';s:4:'qual';s:41:'Certificate IV in Training and Assessment';s:4:'cost';s:5:'350.0';s:3:'cid';s:18:'696292000014545306';s:5:'cname';s:11:'Kylie Drost';}";
    }

    /**
     * @param array $messages
     * @return array
     */
    private function collectMessageIdsOnQueues($messages)
    {
        $messageIdList = [];

        foreach ($messages as $message) {
            $messageIdList[] = $message->message_id;
        }

        return $messageIdList;
    }

    /**
     * @param array $messageIdList
     * @param string $status
     * @return \Illuminate\Support\Collection
     */
    private function buildToAssertIfEqualMessageInput($messageIdList, $status = 'sent')
    {
        $messages = $this->message->findAllWhereIn('message_id', $messageIdList, ['queue']);
        $subscriberAttachInput = collect([]);
        foreach ($messages as $message) {
            foreach ($message->queue->subscriber as $subscriber) {
                $subscriberAttachInput->put($subscriber->id, ['status' => $status]);
            }
        }

        return $subscriberAttachInput;
    }

}