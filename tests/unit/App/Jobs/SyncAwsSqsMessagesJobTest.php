<?php

use App\Action;
use App\Jobs\Exceptions\AWSSQSServerException;
use App\Jobs\Exceptions\DatabaseAlreadySyncedException;
use App\Jobs\Exceptions\InsertIgnoreBulkException;
use App\Jobs\Exceptions\NoMessagesToSyncException;
use App\Jobs\Exceptions\NoValidMessagesFromQueueException;
use App\Jobs\SyncAllAwsSqsMessagesJob;
use App\Message;
use App\Services\SQSClientService;
use Aws\Result;
use Aws\Sqs\Exception\SqsException;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Database\QueryException;

class SyncAwsSqsMessagesJobTest extends BaseTestCase
{
    use AWSTestHelpers;

    private $dispatcher;
    private $sqs;
    private $message;

    public function setUp()
    {
        parent::setUp();

        $this->sqs = new SQSClientService();
        $this->dispatcher = $this->app->make(Dispatcher::class);

        $this->withoutEvents();
    }

    /**
     * Setup a SQS que and messages inside it.
     *
     * To create will wait 60secs after delete.
     *
     * @coversNothing
     * @test
     */
    public function SET_UP_SQS()
    {
        try {
            $this->sqs->client()->createQueue(['QueueName' => $this->QUEUE_NAME_WITH_NO_MESSAGES_SAMPLE()])->get('QueueUrl');
            $queueUrl = $this->sqs->client()->createQueue(['QueueName' => $this->QUEUE_NAME_SAMPLE()])->get('QueueUrl');

            $this->message = $this->sqs->client()->sendMessage(array(
                'QueueUrl' => $queueUrl,
                'MessageBody' => $this->SAMPLE_SALESFORCE_TO_SQS_MESSAGE()
            ));

        } catch (SqsException $exception) {
            echo "ERROR: SET_UP_SQS method" . PHP_EOL;
            die($this->extractSQSMessage($exception->getMessage()));
        }

        $this->assertTrue(true, true);
    }

    /** @test */
    public function it_stores_aws_queues_messages_to_messages_table()
    {
        $this->SET_UP_SQS();

        $this->setConnection('test_mysql_database');

        sleep(15);

        $action = factory(Action::class)->create(['name' => 'changed']);
        $queueUrl = $this->sqs->client()->getQueueUrl(['QueueName' => $this->QUEUE_NAME_SAMPLE()])->get('QueueUrl');
        $actionAttributes = $this->sqs->client()->getQueueAttributes([
            'QueueUrl' => $queueUrl,
            'AttributeNames' => ['ApproximateNumberOfMessages']
        ]);
        $message = $this->sqs->client()->receiveMessage([
            'QueueUrl' => $queueUrl,
            'VisibilityTimeout' => 2
        ])->get('Messages');

        sleep(25);

        $message = array_first($message);

        $this->dispatcher->dispatch(new SyncAllAwsSqsMessagesJob($this->QUEUE_NAME_SAMPLE(), '30'));

        sleep(10);

        $this->assertEquals($actionAttributes['Attributes']['ApproximateNumberOfMessages'], Message::all()->count());
        $this->seeInDatabase('message', [
            'message_id' => $message['MessageId'],
            'action_id' => $action->id,
            'message_content' => $message['Body'],
            'completed' => 'N'
        ]);
    }

    /** @test */
    public function it_throws_an_exception_when_queue_does_not_exist_in_aws()
    {
        $this->expectException(AWSSQSServerException::class);

        $this->setConnection('test_mysql_database');
        $action = factory(Action::class)->create(['name' => 'changed']);

        $this->dispatcher->dispatch(new SyncAllAwsSqsMessagesJob('unknownQueue', '30'));
    }

    /**
     * @test
     */
    public function it_throws_an_exception_when_messages_does_not_exist_in_a_queues()
    {
        $this->expectException(NoMessagesToSyncException::class);
        $this->expectExceptionMessage('No available Queues Messages for sync.');

        factory(Action::class)->create(['name' => 'changed']);

        $this->dispatcher->dispatch(new SyncAllAwsSqsMessagesJob($this->QUEUE_NAME_WITH_NO_MESSAGES_SAMPLE(), '30'));
    }

    /** @test */
    public function it_throws_an_exception_when_database_is_already_sync_to_sqs()
    {
        $this->setConnection('test_mysql_database');

        $this->expectException(DatabaseAlreadySyncedException::class);
        $this->expectExceptionMessage('Database already synced.');

        sleep(10);

        $action = factory(Action::class)->create(['name' => 'changed']);
        $queueUrl = $this->sqs->client()->getQueueUrl(['QueueName' => $this->QUEUE_NAME_SAMPLE()])->get('QueueUrl');

        while ($availableMessage = $this->getAQueueMessage($queueUrl)) {
            factory(Message::class)->create([
                'message_id' => $availableMessage['MessageId'],
                'action_id' => $action->id,
                'message_content' => $availableMessage['Body'],
                'completed' => 'N'
            ]);
        }

        sleep(30);

        $this->dispatcher->dispatch(new SyncAllAwsSqsMessagesJob($this->QUEUE_NAME_SAMPLE(), 30));
    }

    /** @test */
    public function it_throws_an_exception_when_no_message_is_valid_for_insert()
    {
        $this->expectException(NoValidMessagesFromQueueException::class);

        $this->setConnection('test_mysql_database');

        $action = factory(Action::class)->create(['name' => 'invalidName']);

        $queueUrl = $this->sqs->client()->getQueueUrl([
            'QueueName' => $this->QUEUE_NAME_SAMPLE(),
        ])->get('QueueUrl');

        $messageId = $this->sqs->client()->sendMessage([
            'QueueUrl' => $queueUrl,
            'MessageBody' => 'invalidSalesForceMessageBody'
        ])->get('MessageId');

        $this->dispatcher->dispatch(new SyncAllAwsSqsMessagesJob($this->QUEUE_NAME_SAMPLE(), '30'));
    }

    /** @test */
    public function it_does_not_insert_an_invalid_message_content()
    {
        $this->setConnection('test_mysql_database');

        $action = factory(Action::class)->create(['name' => 'changed']);

        $queueUrl = $this->sqs->client()->getQueueUrl([
            'QueueName' => $this->QUEUE_NAME_SAMPLE(),
        ])->get('QueueUrl');

        $messageId = $this->sqs->client()->sendMessage([
            'QueueUrl' => $queueUrl,
            'MessageBody' => 'invalidSalesForceMessageBody'
        ])->get('MessageId');

        $this->dispatcher->dispatch(new SyncAllAwsSqsMessagesJob($this->QUEUE_NAME_SAMPLE(), '30'));

        $this->notSeeInDatabase('message', ['message_id' => $messageId]);
    }

    /** @test */
    public function it_does_not_insert_when_message_payload_does_not_have_a_fieldname_op()
    {
        $this->setConnection('test_mysql_database');

        $action = factory(Action::class)->create(['name' => 'changed']);

        $queueUrl = $this->sqs->client()->getQueueUrl([
            'QueueName' => $this->QUEUE_NAME_SAMPLE(),
        ])->get('QueueUrl');

        $messageToDisregard = $this->sqs->client()->sendMessage([
            'QueueUrl' => $queueUrl,
            'MessageBody' => $this->messageWithoutOp()
        ])->get('MessageId');

        $messageToSave = $this->sqs->client()->sendMessage([
            'QueueUrl' => $queueUrl,
            'MessageBody' => $this->SAMPLE_SALESFORCE_TO_SQS_MESSAGE()
        ])->get('MessageId');

        $this->dispatcher->dispatch(new SyncAllAwsSqsMessagesJob($this->QUEUE_NAME_SAMPLE(), '30'));

        $this->notSeeInDatabase('message', ['message_id' => $messageToDisregard]);
        $this->seeInDatabase('message', ['message_id' => $messageToSave]);
    }

    /** @test */
    public function it_does_not_insert_when_message_op_field_is_empty()
    {
        $this->setConnection('test_mysql_database');

        $action = factory(Action::class)->create(['name' => 'changed']);

        $queueUrl = $this->sqs->client()->getQueueUrl([
            'QueueName' => $this->QUEUE_NAME_SAMPLE(),
        ])->get('QueueUrl');

        $messageToDisregard = $this->sqs->client()->sendMessage([
            'QueueUrl' => $queueUrl,
            'MessageBody' => $this->messageWithBlankOp()
        ])->get('MessageId');

        $messageToSave = $this->sqs->client()->sendMessage([
            'QueueUrl' => $queueUrl,
            'MessageBody' => $this->SAMPLE_SALESFORCE_TO_SQS_MESSAGE()
        ])->get('MessageId');

        $this->dispatcher->dispatch(new SyncAllAwsSqsMessagesJob($this->QUEUE_NAME_SAMPLE(), '30'));

        $this->notSeeInDatabase('message', ['message_id' => $messageToDisregard]);
        $this->seeInDatabase('message', ['message_id' => $messageToSave]);
    }

    /** @test */
    public function it_does_not_insert_when_message_op_field_is_not_valid()
    {
        $this->setConnection('test_mysql_database');

        $action = factory(Action::class)->create(['name' => 'changed']);

        $queueUrl = $this->sqs->client()->getQueueUrl([
            'QueueName' => $this->QUEUE_NAME_SAMPLE(),
        ])->get('QueueUrl');

        $messageToDisregard = $this->sqs->client()->sendMessage([
            'QueueUrl' => $queueUrl,
            'MessageBody' => $this->messageWithInvalidOp()
        ])->get('MessageId');

        $messageToSave = $this->sqs->client()->sendMessage([
            'QueueUrl' => $queueUrl,
            'MessageBody' => $this->SAMPLE_SALESFORCE_TO_SQS_MESSAGE()
        ])->get('MessageId');

        $this->dispatcher->dispatch(new SyncAllAwsSqsMessagesJob($this->QUEUE_NAME_SAMPLE(), '30'));

        $this->notSeeInDatabase('message', ['message_id' => $messageToDisregard]);
        $this->seeInDatabase('message', ['message_id' => $messageToSave]);
    }

    /**
     * @test
     */
    public function it_throws_an_exception_on_database_query_errors()
    {
        $this->expectException(QueryException::class);

        $this->setConnection('test_mysql_database');
        $this->artisan('migrate:rollback');

        $this->dispatcher->dispatch(new SyncAllAwsSqsMessagesJob($this->QUEUE_NAME_SAMPLE(), '30'));

        $this->artisan('migrate');
    }

    /** @test */
    public function it_throws_an_error_on_saving_to_database_if_any_database_related_exception_occurs()
    {
        $this->expectException(InsertIgnoreBulkException::class);

        $this->setConnection('test_mysql_database');

        sleep(7);

        factory(Action::class)->create(['name' => 'changed']);
        $queueUrl = $this->sqs->client()->getQueueUrl(['QueueName' => $this->QUEUE_NAME_SAMPLE()])->get('QueueUrl');
        $this->sqs->client()->receiveMessage(['QueueUrl' => $queueUrl, 'VisibilityTimeout' => 2])->get('Messages');

        sleep(7);

        Schema::table('message', function ($table) {
            $table->dropColumn('message_id');
        });

        $this->dispatcher->dispatch(new SyncAllAwsSqsMessagesJob($this->QUEUE_NAME_SAMPLE(), '30'));
    }

    /**
     * Deletes the newly created SQS Que and its messages.
     * This test should be placed always at the bottom of each tests.
     *
     * @coversNothing
     */
    public function RESET_SQS()
    {
        echo PHP_EOL . 'DELETING QUEUES CREATED FROM THIS TEST....' . PHP_EOL;

        $queueUrl = $this->sqs->client()->getQueueUrl(['QueueName' => $this->QUEUE_NAME_SAMPLE()])->get('QueueUrl');
        $queueUrlWithNoMessages = $this->sqs->client()->getQueueUrl(['QueueName' => $this->QUEUE_NAME_WITH_NO_MESSAGES_SAMPLE()])->get('QueueUrl');

        $queueUrlResult = $this->sqs->client()->deleteQueue(['QueueUrl' => $queueUrl]);
        $queueUrlWithNoMessagesResult = $this->sqs->client()->deleteQueue(['QueueUrl' => $queueUrlWithNoMessages]);

        sleep(60);

        $this->assertInstanceOf(Result::class, $queueUrlResult);
        $this->assertInstanceOf(Result::class, $queueUrlWithNoMessagesResult);
    }

    /**
     * @param $message
     * @return string
     */
    private function extractSQSMessage($message)
    {
        $message = explode('<Message>', $message);
        $message = explode('</Message>', $message[1]);

        return reset($message);
    }

    /**
     * @param string $url
     * @return mixed
     */
    private function getAQueueMessage($url, $visibilityTimeout = 30)
    {
        $message = $this->sqs->client()
            ->receiveMessage(['QueueUrl' => $url, 'VisibilityTimeout' => $visibilityTimeout])
            ->get('Messages');

        return array_first($message);
    }

    private function messageWithoutOp()
    {
        return "a:11:{s:6:'amount';s:0:'';s:8:'assessor';s:18:'696292000018247009';s:6:'status';s:4:'Open';s:3:'rto';s:5:'31718';s:5:'token';s:20:'fb706b1e933ef01e4fb6';s:2:'mb';s:0:'';s:4:'qual';s:41:'Certificate IV in Training and Assessment';s:4:'cost';s:5:'350.0';s:3:'cid';s:18:'696292000014545306';s:5:'cname';s:11:'Kylie Drost';}";
    }

    private function messageWithBlankOp()
    {
        return "a:11:{s:6:'amount';s:0:'';s:8:'assessor';s:18:'696292000018247009';s:2:'op';s:0:'';s:6:'status';s:4:'Open';s:3:'rto';s:5:'31718';s:5:'token';s:20:'fb706b1e933ef01e4fb6';s:2:'mb';s:0:'';s:4:'qual';s:41:'Certificate IV in Training and Assessment';s:4:'cost';s:5:'350.0';s:3:'cid';s:18:'696292000014545306';s:5:'cname';s:11:'Kylie Drost';}";
    }

    private function messageWithInvalidOp()
    {
        return "a:11:{s:6:'amount';s:0:'';s:8:'assessor';s:18:'696292000018247009';s:2:'op';s:13:'nonexistingop';s:6:'status';s:4:'Open';s:3:'rto';s:5:'31718';s:5:'token';s:20:'fb706b1e933ef01e4fb6';s:2:'mb';s:0:'';s:4:'qual';s:41:'Certificate IV in Training and Assessment';s:4:'cost';s:5:'350.0';s:3:'cid';s:18:'696292000014545306';s:5:'cname';s:11:'Kylie Drost';}";
    }
}
