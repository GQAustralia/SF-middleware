<?php

use App\Jobs\SyncAllAwsSqsMessagesJob;

class SyncSQSMessagesCommandTest extends BaseTestCase
{
    /** @test */
    public function it_returns_when_the_command_is_called()
    {
        $this->expectsJobs([SyncAllAwsSqsMessagesJob::class]);

        $this->artisan('inbound:sync');

        $this->assertEquals('Sync Successful.', $this->getActualOutput());
    }
}