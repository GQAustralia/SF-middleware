<?php

use App\Message;
use App\Queue;
use App\Services\SQSClientService;
use App\Subscriber;
use Aws\Result;
use Aws\Sqs\Exception\SqsException;
use Illuminate\Support\Facades\Schema;

class MessageQueueControllerTest extends BaseTestCase
{
    use AWSTestHelpers;

    private $sqs;

    public function setUp()
    {
        parent::setUp();

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
            $queueURL = $this->sqs->client()->createQueue(['QueueName' => $this->QUEUE_NAME_SAMPLE()])->get('QueueUrl');

            $this->sqs->client()->sendMessage([
                'QueueUrl' => $queueURL,
                'MessageBody' => $this->SAMPLE_SALESFORCE_TO_SQS_MESSAGE()
            ]);

        } catch (SqsException $exception) {
            echo "ERROR: SET_UP_SQS method" . PHP_EOL;
            die($this->extractSQSMessage($exception->getMessage()));
        }

        $this->assertTrue(true, true);
    }

    /** @test */
    public function it_gives_an_an_invalid_response_when_queues_does_not_exist()
    {
        $this->post('sync');

        $this->assertEquals('Queues does not exist.', $this->getContent());
        $this->assertResponseStatus(400);
    }

    /** @test */
    public function it_gives_an_invalid_response_when_queues_on_aws_does_not_exist()
    {
        factory(Queue::class, 2)->create(['aws_queue_name' => 'nonExistingQue']);

        $this->post('sync');

        $this->assertEquals('The specified queue does not exist for this wsdl version.', $this->getContent());
        $this->assertResponseStatus(400);
    }

    /** @test */
    public function it_returns_a_valid_response_when_no_messages_to_sync()
    {
        factory(Queue::class, 2)->create(['aws_queue_name' => $this->QUEUE_NAME_WITH_NO_MESSAGES_SAMPLE()]);

        $this->post('sync');

        $this->assertEquals('No available Queues Messages for sync.', $this->getContent());
        $this->assertResponseOk();
    }

    /** @test */
    public function it_returns_an_invalid_response_on_database_query_errors()
    {
        $this->setConnection('test_mysql_database');

        $this->artisan('migrate:rollback');

        $this->post('sync');

        $this->assertEquals('Database error please contact your Administrator.', $this->getContent());
        $this->assertResponseStatus(500);
    }

    /** @test */
    public function it_returns_a_valid_response_on_already_synced_database_and_sqs()
    {
        $this->SET_UP_SQS();

        $this->setConnection('test_mysql_database');

        sleep(15);

        $queue = factory(Queue::class)->create(['aws_queue_name' => $this->QUEUE_NAME_SAMPLE()]);
        $queueUrl = $this->sqs->client()->getQueueUrl(['QueueName' => $this->QUEUE_NAME_SAMPLE()])->get('QueueUrl');

        while ($availableMessage = $this->getAQueueMessage($queueUrl, 30)) {
            factory(\App\Message::class)->create([
                'message_id' => $availableMessage['MessageId'],
                'queue_id' => $queue->id,
                'message_content' => $availableMessage['Body'],
                'completed' => 'N'
            ]);
        }

        sleep(30);

        $this->post('sync');

        sleep(10);

        $this->assertEquals('Database already synced.', $this->getContent());
    }

    /** @test */
    public function it_returns_an_invalid_response_on_database_insert_error()
    {
        $this->SET_UP_SQS();
        
        $this->setConnection('test_mysql_database');

        factory(Queue::class)->create(['aws_queue_name' => $this->QUEUE_NAME_SAMPLE()]);

        Schema::table('message', function ($table) {
            $table->dropColumn('message_id');
        });

        $this->post('sync');

        sleep(10);

        $this->assertResponseStatus(500);
    }

    /** @test */
    public function it_returns_a_valid_response_on_when_queue_in_messages_does_not_have_an_associated_subscriber()
    {
        $this->setConnection('test_mysql_database');

        $queue = factory(Queue::class)->create(['aws_queue_name' => $this->QUEUE_NAME_SAMPLE()]);

        $this->post('sync');

        sleep(10);

        $this->assertResponseOk();
    }

    /** @test */
    public function it_throws_an_exception_on_invalid_sqs_message()
    {

    }

    /** @test */
    public function it_returns_a_valid_response_on_successful_sync()
    {
        $this->setConnection('test_mysql_database');

        $queue = factory(Queue::class)->create(['aws_queue_name' => $this->QUEUE_NAME_SAMPLE()]);
        $message = factory(Message::class)->create([
            'queue_id' => $queue->id,
            'message_content' => $this->SAMPLE_SALESFORCE_TO_SQS_MESSAGE()
        ]);
        $subscriber = factory(Subscriber::class, 3)->create(['url' => url($this->SUCCESS_RESPONSE_SITE())]);
        $queue->subscriber()->attach(collect($subscriber)->pluck('id')->toArray());

        $this->post('sync');

        $this->assertResponseOk();
        $this->assertEquals('Sync Successful.', $this->getContent());
        $this->seeInDatabase('message', ['queue_id' => $queue->id]);
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

        $queueURL = $this->sqs->client()->getQueueUrl(['QueueName' => $this->QUEUE_NAME_SAMPLE()])->get('QueueUrl');
        $queueURLWithNoMessages = $this->sqs->client()->getQueueUrl(['QueueName' => $this->QUEUE_NAME_WITH_NO_MESSAGES_SAMPLE()])->get('QueueUrl');

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
    private function getAQueueMessage($url, $visibilityTimtout = 15)
    {
        $message = $this->sqs->client()
            ->receiveMessage(['QueueUrl' => $url, 'VisibilityTimeout' => $visibilityTimtout])
            ->get('Messages');

        return array_first($message);
    }
}