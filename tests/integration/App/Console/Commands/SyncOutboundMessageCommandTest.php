<?php

use App\Services\SQSClientService;
use Aws\Sqs\Exception\SqsException;

class SyncOutboundMessageCommandTest extends BaseTestCase
{
    use AWSTestHelpers;

    public function setUp()
    {
        parent::setUp();

        $this->setConnection('test_mysql_database');

        $this->sqs = new SQSClientService();
    }

    /** @test */
    public function locateTest()
    {
        $this->runningTestFor(get_class($this));
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
            $this->sqs->client()->createQueue(['QueueName' => $this->QUEUE_NAME_WITH_NO_MESSAGES_SAMPLE()]);
        } catch (SqsException $exception) {
            echo "ERROR: SET_UP_SQS method" . PHP_EOL;
            die($this->extractSQSMessage($exception->getMessage()));
        }

        $this->assertTrue(true, true);
    }

    /** @test */
    public function it_returns_an_invalid_message_if_queue_does_not_exist()
    {
        $this->artisan('outbound:sync', ['queue' => 'nonExistingQueue']);

        $this->assertEquals('The specified queue does not exist for this wsdl version.', $this->getArtisanOutput());
    }
}