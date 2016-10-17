<?php

use App\Jobs\Exceptions\AWSSQSServerException;
use App\Jobs\Exceptions\DatabaseAlreadySyncedException;
use App\Jobs\Exceptions\EmptyQueuesException;
use App\Jobs\Exceptions\InsertIgnoreBulkException;
use App\Jobs\Exceptions\NoMessagesToSyncException;
use App\Jobs\SyncAllAwsSqsMessagesJob;
use App\Message;
use App\Queue;
use App\Services\SQSClientService;
use Aws\Result;
use Aws\Sqs\Exception\SqsException;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Database\QueryException;

class SyncAwsSqsMessagesJobTest extends BaseTestCase
{
    const QUEUE_NAME_SAMPLE = 'SampleQueFromTest';
    const QUEUE_NAME_WITH_NO_MESSAGES_SAMPLE = 'SampleQueFromTestWithNoMessages';
    const QUEUE_MESSAGE_SAMPLE = 'Sample message from test';

    private $dispatcher;
    private $sqs;
    private $message;

    public function setUp()
    {
        parent::setUp();

        $this->sqs = new SQSClientService();
        $this->dispatcher = $this->app->make(Dispatcher::class);

        $this->SET_UP_SQS();
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
            $this->sqs->client()->createQueue(['QueueName' => self::QUEUE_NAME_WITH_NO_MESSAGES_SAMPLE])->get('QueueUrl');
            $queueURL = $this->sqs->client()->createQueue(['QueueName' => self::QUEUE_NAME_SAMPLE])->get('QueueUrl');


            $this->message = $this->sqs->client()->sendMessage(array(
                'QueueUrl' => $queueURL,
                'MessageBody' => self::QUEUE_MESSAGE_SAMPLE
            ));

        } catch (SqsException $exception) {
            echo "ERROR: SET_UP_SQS method" . PHP_EOL;
            die($this->extractSQSMessage($exception->getMessage()));
        }

        $this->assertTrue(true, true);
    }

    /**
     * @test
     */
    public function it_throws_an_exception_on_database_query_errors()
    {
        $this->expectException(QueryException::class);

        $this->setConnection('test_mysql_database');
        $this->artisan('migrate:rollback');

        $this->dispatcher->dispatch(new SyncAllAwsSqsMessagesJob());

        $this->artisan('migrate');
    }

    /** @test */
    public function it_throws_an_exception_if_queues_does_not_exist_in_database()
    {
        $this->expectException(EmptyQueuesException::class);
        $this->expectExceptionMessage('Queues does not exist.');

        $this->dispatcher->dispatch(new SyncAllAwsSqsMessagesJob());
    }

    /** @test */
    public function it_throws_an_exception_if_a_queue_does_not_exist_in_aws()
    {
        $this->expectException(AWSSQSServerException::class);
        $this->expectExceptionMessage('The specified queue does not exist for this wsdl version.');

        factory(Queue::class, 2)->create(['aws_queue_name' => 'nonExistentQueue']);

        $this->dispatcher->dispatch(new SyncAllAwsSqsMessagesJob());
    }

    /**
     * @test
     */
    public function it_throws_an_exception_when_messages_does_not_exist_in_a_queues()
    {
        $this->expectException(NoMessagesToSyncException::class);
        $this->expectExceptionMessage('No available Queues Messages for sync.');

        factory(Queue::class)->create(['aws_queue_name' => self::QUEUE_NAME_WITH_NO_MESSAGES_SAMPLE]);

        $this->dispatcher->dispatch(new SyncAllAwsSqsMessagesJob('all', '30'));
    }

    /** @test */
    public function it_throws_an_exception_when_database_is_already_sync_to_sqs()
    {
        $this->setConnection('test_mysql_database');

        $this->expectException(DatabaseAlreadySyncedException::class);
        $this->expectExceptionMessage('Database already synced.');

        sleep(15);

        $queue = factory(Queue::class)->create(['aws_queue_name' => self::QUEUE_NAME_SAMPLE]);
        $queueUrl = $this->sqs->client()->getQueueUrl(['QueueName' => self::QUEUE_NAME_SAMPLE])->get('QueueUrl');

        while ($availableMessage = $this->getAQueueMessage($queueUrl)) {
            factory(Message::class)->create([
                'message_id' => $availableMessage['MessageId'],
                'queue_id' => $queue->id,
                'message_content' => $availableMessage['Body'],
                'completed' => 'N'
            ]);
        }

        sleep(30);

        $this->dispatcher->dispatch(new SyncAllAwsSqsMessagesJob('all', 30));
    }

    /** @test */
    public function it_throws_an_error_on_saving_to_database_if_any_database_related_exception_occurs()
    {
        $this->expectException(InsertIgnoreBulkException::class);

        $this->setConnection('test_mysql_database');

        sleep(7);

        factory(Queue::class)->create(['aws_queue_name' => self::QUEUE_NAME_SAMPLE]);
        $queueUrl = $this->sqs->client()->getQueueUrl(['QueueName' => self::QUEUE_NAME_SAMPLE])->get('QueueUrl');
        $this->sqs->client()->receiveMessage(['QueueUrl' => $queueUrl, 'VisibilityTimeout' => 2])->get('Messages');

        sleep(7);

        Schema::table('message', function ($table) {
            $table->dropColumn('message_id');
        });

        $this->dispatcher->dispatch(new SyncAllAwsSqsMessagesJob('all', '30'));
    }

    /** @test */
    public function it_stores_aws_queues_messages_to_messages_table()
    {
        $this->withoutEvents();
        $this->setConnection('test_mysql_database');

        sleep(15);

        $queue = factory(Queue::class)->create(['aws_queue_name' => self::QUEUE_NAME_SAMPLE]);
        $queueUrl = $this->sqs->client()->getQueueUrl(['QueueName' => self::QUEUE_NAME_SAMPLE])->get('QueueUrl');
        $queueAttributes = $this->sqs->client()->getQueueAttributes([
            'QueueUrl' => $queueUrl,
            'AttributeNames' => ['ApproximateNumberOfMessages']
        ]);
        $message = $this->sqs->client()->receiveMessage([
            'QueueUrl' => $queueUrl,
            'VisibilityTimeout' => 2
        ])->get('Messages');

        sleep(15);

        $message = array_first($message);

        $this->dispatcher->dispatch(new SyncAllAwsSqsMessagesJob('all', '30'));

        sleep(10);

        $this->assertEquals($queueAttributes['Attributes']['ApproximateNumberOfMessages'], Message::all()->count());
        $this->seeInDatabase('message', [
            'message_id' => $message['MessageId'],
            'queue_id' => $queue->id,
            'message_content' => $message['Body'],
            'completed' => 'N'
        ]);
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

        $queueURL = $this->sqs->client()->getQueueUrl(['QueueName' => self::QUEUE_NAME_SAMPLE])->get('QueueUrl');
        $queueURLWithNoMessages = $this->sqs->client()->getQueueUrl(['QueueName' => self::QUEUE_NAME_WITH_NO_MESSAGES_SAMPLE])->get('QueueUrl');

        $queueURLResult = $this->sqs->client()->deleteQueue(['QueueUrl' => $queueURL]);
        $queueURLWithNoMessagesResult = $this->sqs->client()->deleteQueue(['QueueUrl' => $queueURLWithNoMessages]);

        sleep(60);

        $this->assertInstanceOf(Result::class, $queueURLResult);
        $this->assertInstanceOf(Result::class, $queueURLWithNoMessagesResult);
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
}