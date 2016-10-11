<?php


use App\Queue;
use App\Resolvers\SQSClientResolver;
use Aws\Result;
use Aws\Sqs\Exception\SqsException;
use Illuminate\Support\Facades\Schema;

class MessageQueueControllerTest extends BaseTestCase
{
    const QUEUE_NAME_SAMPLE = 'SampleQueFromTest';
    const QUEUE_NAME_WITH_NO_MESSAGES_SAMPLE = 'SampleQueFromTestWithNoMessages';
    const QUEUE_MESSAGE_SAMPLE = 'Sample message from test';

    private $sqs;

    public function setUp()
    {
        parent::setUp();

        $this->sqs = new SQSClientResolver();

        $this->SET_UP_SQS();
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
            $this->sqs->client()->createQueue(['QueueName' => self::QUEUE_NAME_WITH_NO_MESSAGES_SAMPLE])->get('QueueUrl');
            $queueURL = $this->sqs->client()->createQueue(['QueueName' => self::QUEUE_NAME_SAMPLE])->get('QueueUrl');

            $this->sqs->client()->sendMessage(array(
                'QueueUrl' => $queueURL,
                'MessageBody' => self::QUEUE_MESSAGE_SAMPLE
            ));
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
        factory(Queue::class, 2)->create(['aws_queue_name' => self::QUEUE_NAME_WITH_NO_MESSAGES_SAMPLE]);

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
    public function it_returns_an_invalid_response_on_database_insert_error()
    {
        $this->setConnection('test_mysql_database');

        factory(Queue::class)->create(['aws_queue_name' => self::QUEUE_NAME_SAMPLE]);

        Schema::table('message', function ($table) {
            $table->dropColumn('message_id');
        });

        $this->post('sync');

        $this->assertEquals('Insert Ignore Bulk Error.', $this->getContent());
        $this->assertResponseStatus(500);
    }

    /** @test */
    public function it_returns_a_valid_response_on_successful_sync()
    {
        $this->setConnection('test_mysql_database');

        $queue = factory(Queue::class)->create(['aws_queue_name' => self::QUEUE_NAME_SAMPLE]);

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
}