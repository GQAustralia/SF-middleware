<?php

use App\Action;
use App\Exceptions\AWSSQSServerException;
use App\Exceptions\DatabaseAlreadySyncedException;
use App\Exceptions\InsertIgnoreBulkException;
use App\Exceptions\NoMessagesToSyncException;
use App\Exceptions\NoValidMessagesFromQueueException;
use App\Message;
use App\Services\InboundMessagesSync;
use App\Services\SQSClientService;
use Aws\Result;
use Aws\Sqs\Exception\SqsException;
use Illuminate\Database\QueryException;

class InboundMessagesSyncTest extends BaseTestCase
{
    use AWSTestHelpers;

    private $inbound;
    private $sqs;
    private $message;

    public function setUp()
    {
        parent::setUp();

        $this->sqs = new SQSClientService();
        $this->inbound = $this->app->make(InboundMessagesSync::class);

        $this->withoutEvents();
    }

    /** @test */
    public function it_stores_aws_queues_messages_to_messages_table_and_deletes_the_messages_to_aws_queue()
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

        sleep(30);

        $message = array_first($message);

        $this->inbound->messageVisibility(30)->handle($this->QUEUE_NAME_SAMPLE());

        sleep(10);

        $deletedQueResults = $this->sqs->client()->getQueueAttributes([
            'QueueUrl' => $queueUrl,
            'AttributeNames' => ['ApproximateNumberOfMessages']
        ]);

        $this->assertEquals($actionAttributes['Attributes']['ApproximateNumberOfMessages'], Message::all()->count());
        $this->seeInDatabase('message', [
            'message_id' => $message['MessageId'],
            'action_id' => $action->id,
            'message_content' => str_replace('"', '\'', $message['Body']),
            'completed' => 'N'
        ]);

        $this->assertEquals($deletedQueResults['Attributes']['ApproximateNumberOfMessages'], 0);
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
    public function it_throws_an_exception_when_queue_does_not_exist_in_aws()
    {
        $this->setExpectedException(AWSSQSServerException::class);

        $this->setConnection('test_mysql_database');
        factory(Action::class)->create(['name' => 'changed']);

        $this->inbound->messageVisibility(30)->handle('unknownQueue');
    }

    /**
     * @test
     */
    public function it_throws_an_exception_when_messages_does_not_exist_in_a_queue()
    {
        $this->setExpectedException(NoMessagesToSyncException::class, 'No available queue messages for sync.');

        factory(Action::class)->create(['name' => 'changed']);

        $this->inbound->messageVisibility(30)->handle($this->QUEUE_NAME_WITH_NO_MESSAGES_SAMPLE());
    }

    /** @test */
    public function it_throws_an_exception_when_database_is_already_sync_to_sqs()
    {
        $this->setConnection('test_mysql_database');

        $this->setExpectedException(DatabaseAlreadySyncedException::class, 'Database already synced.');

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

        $this->inbound->messageVisibility(30)->handle($this->QUEUE_NAME_SAMPLE());
    }

    /** @test */
    public function it_throws_an_exception_when_no_message_is_valid_for_insert()
    {
        $this->setExpectedException(NoValidMessagesFromQueueException::class);

        $this->setConnection('test_mysql_database');

        factory(Action::class)->create(['name' => 'invalidName']);

        $queueUrl = $this->sqs->client()->getQueueUrl([
            'QueueName' => $this->QUEUE_NAME_SAMPLE(),
        ])->get('QueueUrl');

        $this->sqs->client()->sendMessage([
            'QueueUrl' => $queueUrl,
            'MessageBody' => 'invalidSalesForceMessageBody'
        ])->get('MessageId');

        $this->inbound->messageVisibility(30)->handle($this->QUEUE_NAME_SAMPLE());

        sleep(5);
    }

    /** @test */
    public function it_does_not_insert_when_message_payload_does_not_have_a_fieldname_op()
    {
        $this->setConnection('test_mysql_database');

        factory(Action::class)->create(['name' => 'changed']);

        $queueUrl = $this->sqs->client()->getQueueUrl([
            'QueueName' => $this->QUEUE_NAME_SAMPLE(),
        ])->get('QueueUrl');

        $messageToDisregard = $this->sqs->client()->sendMessage([
            'QueueUrl' => $queueUrl,
            'MessageBody' => $this->SAMPLE_SALESFORCE_TO_SQS_MESSAGE_WITHOUT_OP()
        ])->get('MessageId');

        $messageToSave = $this->sqs->client()->sendMessage([
            'QueueUrl' => $queueUrl,
            'MessageBody' => $this->SAMPLE_SALESFORCE_TO_SQS_MESSAGE()
        ])->get('MessageId');

        $this->inbound->messageVisibility(30)->handle($this->QUEUE_NAME_SAMPLE());

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
            'MessageBody' => $this->SAMPLE_SALESFORCE_TO_SQS_MESSAGE_WITH_BLANK_OP()
        ])->get('MessageId');

        $messageToSave = $this->sqs->client()->sendMessage([
            'QueueUrl' => $queueUrl,
            'MessageBody' => $this->SAMPLE_SALESFORCE_TO_SQS_MESSAGE()
        ])->get('MessageId');

        $this->inbound->messageVisibility(30)->handle($this->QUEUE_NAME_SAMPLE());

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
            'MessageBody' => $this->SAMPLE_SALESFORCE_TO_SQS_MESSAGE_WITH_INVALID_OP()
        ])->get('MessageId');

        $messageToSave = $this->sqs->client()->sendMessage([
            'QueueUrl' => $queueUrl,
            'MessageBody' => $this->SAMPLE_SALESFORCE_TO_SQS_MESSAGE()
        ])->get('MessageId');

        $this->inbound->messageVisibility(30)->handle($this->QUEUE_NAME_SAMPLE());

        $this->notSeeInDatabase('message', ['message_id' => $messageToDisregard]);
        $this->seeInDatabase('message', ['message_id' => $messageToSave]);
    }

    /**
     * @test
     */
    public function it_throws_an_exception_on_database_query_errors()
    {
        $this->setExpectedException(QueryException::class);

        $this->setConnection('test_mysql_database');
        $this->artisan('migrate:rollback');

        $this->inbound->messageVisibility(30)->handle($this->QUEUE_NAME_SAMPLE());

        $this->artisan('migrate');
    }

    /** @test */
    public function it_throws_an_error_on_saving_to_database_if_any_database_related_exception_occurs()
    {
        $this->setExpectedException(InsertIgnoreBulkException::class);

        $this->setConnection('test_mysql_database');

        sleep(7);

        factory(Action::class)->create(['name' => 'changed']);
        $queueUrl = $this->sqs->client()->getQueueUrl(['QueueName' => $this->QUEUE_NAME_SAMPLE()])->get('QueueUrl');
        $this->sqs->client()->receiveMessage(['QueueUrl' => $queueUrl, 'VisibilityTimeout' => 2])->get('Messages');

        $this->sqs->client()->sendMessage(array(
            'QueueUrl' => $queueUrl,
            'MessageBody' => $this->SAMPLE_SALESFORCE_TO_SQS_MESSAGE()
        ));

        sleep(7);

        Schema::table('message', function ($table) {
            $table->dropColumn('message_id');
        });

        $this->inbound->messageVisibility(30)->handle($this->QUEUE_NAME_SAMPLE());
    }

    /**
     * Deletes the newly created SQS Que and its messages.
     * This test should be placed always at the bottom of each tests.
     *
     * @test
     * @coversNothing
     */
    public function RESET_SQS()
    {
        echo PHP_EOL . 'DELETING QUEUES CREATED FROM THIS TEST....' . PHP_EOL;

        $queueUrl = $this->sqs->client()->getQueueUrl(['QueueName' => $this->QUEUE_NAME_SAMPLE()])->get('QueueUrl');
        $queueUrlWithNoMessages = $this->sqs->client()->getQueueUrl(['QueueName' => $this->QUEUE_NAME_WITH_NO_MESSAGES_SAMPLE()])->get('QueueUrl');

        $queueUrlResult = $this->sqs->client()->deleteQueue(['QueueUrl' => $queueUrl]);
        $queueUrlWithNoMessagesResult = $this->sqs->client()->deleteQueue(['QueueUrl' => $queueUrlWithNoMessages]);

        //sleep(60);

        $this->assertInstanceOf(Result::class, $queueUrlResult);
        $this->assertInstanceOf(Result::class, $queueUrlWithNoMessagesResult);
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
}