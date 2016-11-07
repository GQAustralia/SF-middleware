<?php

use App\Action;
use App\Message;
use App\Services\SQSClientService;
use App\Subscriber;
use Aws\Result;
use Aws\Sqs\Exception\SqsException;

class SyncSQSMessagesCommandTest extends BaseTestCase
{
    use AWSTestHelpers;

    public function setUp()
    {
        parent::setUp();

        $this->setConnection('test_mysql_database');

        $this->sqs = new SQSClientService();
    }

    /**
     * Setup a SQS que and messages inside it.
     *
     * To create will wait 60secs after delete.
     * Important this test must be always placed on top of all tests.
     *
     * @coversNothing
     * @test
     */
    public function SET_UP_SQS()
    {
        try {
            $this->sqs->client()->createQueue(['QueueName' => $this->QUEUE_NAME_WITH_NO_MESSAGES_SAMPLE()])->get('QueueUrl');
            $validQueueURL = $this->sqs->client()->createQueue(['QueueName' => $this->QUEUE_NAME_SAMPLE()])->get('QueueUrl');
            $queWithInvalidMessagesUrl = $this->sqs->client()->createQueue(['QueueName' => $this->QUEUE_NAME_WITH_INVALID_MESSAGES_SAMPLE()])->get('QueueUrl');
            $this->sqs->client()->sendMessage([
                'QueueUrl' => $validQueueURL,
                'MessageBody' => $this->SAMPLE_SALESFORCE_TO_SQS_MESSAGE()
            ]);

            $this->sqs->client()->sendMessage([
                'QueueUrl' => $queWithInvalidMessagesUrl,
                'MessageBody' => 'INVALID MESSAGE'
            ]);

        } catch (SqsException $exception) {
            echo "ERROR: SET_UP_SQS method" . PHP_EOL;
            die($this->extractSQSMessage($exception->getMessage()));
        }

        $this->assertTrue(true, true);
    }

    /** @test */
    public function it_gives_an_an_invalid_response_when_queue_does_not_exist()
    {
        $this->artisan('inbound:sync', ['queue' => 'nonExistingQueue']);

        $this->assertEquals('The specified queue does not exist for this wsdl version.', $this->getActualOutput());
    }

    /** @test */
    public function it_returns_an_valid_message_when_no_message_to_sync()
    {
        $this->artisan('inbound:sync', ['queue' => $this->QUEUE_NAME_WITH_NO_MESSAGES_SAMPLE()]);

        $this->assertEquals('No available Queues Messages for sync.', $this->getActualOutput());

    }

    /** @test */
    public function it_returns_an_invalid_message_on_database_query_errors()
    {
        $this->artisan('migrate:rollback');

        $this->artisan('inbound:sync', ['queue' => $this->QUEUE_NAME_SAMPLE()]);

        $this->assertEquals('Database error please contact your Administrator.', $this->getActualOutput());
    }

    /** @test */
    public function it_returns_an_invalid_message_when_queue_messages_are_all_invalid()
    {
        factory(Action::class)->create(['name' => 'changed']);

        $this->artisan('inbound:sync', ['queue' => $this->QUEUE_NAME_WITH_INVALID_MESSAGES_SAMPLE()]);

        sleep(10);

        $this->assertEquals('No valid messages from queue to sync.', $this->getActualOutput());
    }

    /** @test */
    public function it_returns_a_valid_message_on_already_synced_database_and_sqs()
    {
        $this->SET_UP_SQS();

        sleep(15);

        $action = factory(Action::class)->create(['name' => 'changed']);
        $actionUrl = $this->sqs->client()->getQueueUrl(['QueueName' => $this->QUEUE_NAME_SAMPLE()])->get('QueueUrl');

        while ($availableMessage = $this->getAQueueMessage($actionUrl, 30)) {
            factory(\App\Message::class)->create([
                'message_id' => $availableMessage['MessageId'],
                'action_id' => $action->id,
                'message_content' => $availableMessage['Body'],
                'completed' => 'N'
            ]);
        }

        sleep(30);

        $this->artisan('inbound:sync', ['queue' => $this->QUEUE_NAME_SAMPLE()]);

        sleep(10);

        $this->assertEquals('Database already synced.', $this->getActualOutput());
    }

    /** @test */
    public function it_returns_an_invalid_response_on_database_insert_error()
    {
        $this->SET_UP_SQS();


        factory(Action::class)->create(['name' => 'changed']);

        Schema::table('message', function ($table) {
            $table->dropColumn('message_id');
        });

        $this->artisan('inbound:sync', ['queue' => $this->QUEUE_NAME_SAMPLE()]);

        $this->assertEquals('Insert Ignore Bulk Error.', $this->getActualOutput());
    }

    /** @test */
    public function it_returns_a_valid_message_on_successful_sync()
    {
        $action = factory(Action::class)->create(['name' => 'changed']);
        $message = factory(Message::class)->create([
            'action_id' => $action->id,
            'message_content' => $this->SAMPLE_SALESFORCE_TO_SQS_MESSAGE()
        ]);

        $validQueueURL = $this->sqs->client()->createQueue(['QueueName' => $this->QUEUE_NAME_SAMPLE()])->get('QueueUrl');
        $this->sqs->client()->sendMessage([
            'QueueUrl' => $validQueueURL,
            'MessageBody' => $this->SAMPLE_SALESFORCE_TO_SQS_MESSAGE()
        ]);

        $subscriber = factory(Subscriber::class, 3)->create(['url' => url($this->SUCCESS_RESPONSE_SITE())]);
        $action->subscriber()->attach(collect($subscriber)->pluck('id')->toArray());

        $this->artisan('inbound:sync', ['queue' => $this->QUEUE_NAME_SAMPLE()]);

        $this->assertEquals('Sync Successful.', $this->getActualOutput());
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
        $queueURLWithNoMessages = $this->sqs->client()->getQueueUrl(['QueueName' => $this->QUEUE_NAME_WITH_NO_MESSAGES_SAMPLE()])->get('QueueUrl');
        $queueURLWithInvalidMessages = $this->sqs->client()->getQueueUrl(['QueueName' => $this->QUEUE_NAME_WITH_INVALID_MESSAGES_SAMPLE()])->get('QueueUrl');

        $queueURLResult = $this->sqs->client()->deleteQueue(['QueueUrl' => $queueUrl]);
        $queueURLWithNoMessagesResult = $this->sqs->client()->deleteQueue(['QueueUrl' => $queueURLWithNoMessages]);
        $queueURLWithInvalidMessagesResult = $this->sqs->client()->deleteQueue(['QueueUrl' => $queueURLWithInvalidMessages]);

        sleep(60);
    }

    /**
     * @param string $url
     * @param int $visibilityTimeout
     * @return mixed
     */
    private function getAQueueMessage($url, $visibilityTimeout = 15)
    {
        $message = $this->sqs->client()
            ->receiveMessage(['QueueUrl' => $url, 'VisibilityTimeout' => $visibilityTimeout])
            ->get('Messages');

        return array_first($message);
    }
}