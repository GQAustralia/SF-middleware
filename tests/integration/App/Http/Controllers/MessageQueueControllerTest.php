<?php


use App\Jobs\SyncAllAwsSqsMessagesJob;

class MessageQueueControllerTest extends BaseTestCase
{

    /** @test */
    public function it_calls_the_command()
    {
        //$this->expectsJobs(SyncAwsSqsMessagesJob::class);


        $this->post('create', []);

       // $this->assertEquals('', $this->getContent());
        print_r($this->getContent());
    }
}