<?php

use App\Jobs\Exceptions\AWSSQSServerException;
use App\Jobs\Exceptions\EmptyQueuesException;
use App\Jobs\SyncAllAwsSqsMessagesJob;
use App\Message;
use App\Queue;
use App\Resolvers\SQSClientResolver;
use Aws\Result;
use Aws\Sqs\Exception\SqsException;
use Illuminate\Contracts\Bus\Dispatcher;

class SyncAwsSqsMessagesJobTest extends BaseTestCase
{
    const QUEUE_NAME_SAMPLE = 'SampleQueFromTest';
    const QUEUE_NAME_WITH_NO_MESSAGES_SAMPLE = 'SampleQueFromTestWithNoMessages';
    const QUEUE_MESSAGE_SAMPLE = 'Sample message from test';

    private $dispatcher;
    private $sqs;

    public function setUp()
    {
        parent::setUp();

        $this->sqs = new SQSClientResolver();
        $this->dispatcher = $this->app->make(Dispatcher::class);
    }

    /**
     * Setup a SQS que and messages inside it.
     *
     * Will wait 60secs after each que create or delete as rule of AWS.
     * Important this test must be always placed on top of all tests.
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
    public function it_throws_an_exception_if_queues_does_not_exist_in_database()
    {
        $this->expectException(EmptyQueuesException::class);

        $this->dispatcher->dispatch(new SyncAllAwsSqsMessagesJob());
    }

    /** @test */
    public function it_throws_an_exception_if_a_queue_does_not_exist_in_aws()
    {
        $this->expectException(AWSSQSServerException::class);
        $this->expectExceptionMessage('The specified queue does not exist for this wsdl version.');

        factory(Queue::class, 2)->create(['aws_queue_name' => 'nonExistingQue']);

        $this->dispatcher->dispatch(new SyncAllAwsSqsMessagesJob());
    }

    /**
     * @test
     */
    public function it_returns_zero_when_messages_does_not_exist_in_a_que()
    {
        factory(Queue::class)->create(['aws_queue_name' => self::QUEUE_NAME_WITH_NO_MESSAGES_SAMPLE]);

        $result = $this->dispatcher->dispatch(new SyncAllAwsSqsMessagesJob());

        $this->assertEquals(0, $result);
        $this->assertEquals(0, Message::all()->count());
    }

    public function it_prepares_the_storing_of_the_message_from_sqs_to_database()
    {
        factory(Queue::class, 2)->create(['aws_queue_name' => self::QUEUE_NAME_SAMPLE]);

        $result = $this->dispatcher->dispatch(new SyncAllAwsSqsMessagesJob());

    }

    /**
     * Deletes the newly created SQS Que and its messages.
     * This test should be placed always at the bottom of each tests.
     *
     * @test
     */
    public function RESET_SQS()
    {
        $queueURL = $this->sqs->client()->getQueueUrl(['QueueName' => self::QUEUE_NAME_SAMPLE])->get('QueueUrl');
        $queueURLWithNoMessages = $this->sqs->client()->getQueueUrl(['QueueName' => self::QUEUE_NAME_WITH_NO_MESSAGES_SAMPLE])->get('QueueUrl');

        $queueURLResult = $this->sqs->client()->deleteQueue(['QueueUrl' => $queueURL]);
        $queueURLWithNoMessagesResult = $this->sqs->client()->deleteQueue(['QueueUrl' => $queueURLWithNoMessages]);

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