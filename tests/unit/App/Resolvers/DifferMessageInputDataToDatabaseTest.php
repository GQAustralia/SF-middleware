<?php

use App\InboundMessage;
use App\Resolvers\DifferMessageInputDataToDatabase;

class DifferMessageInputDataToDatabaseTest extends BaseTestCase
{
    use DifferMessageInputDataToDatabase;

    /** @test */
    public function locateTest()
    {
        $this->runningTestFor(get_class($this));
    }

    /** @test */
    public function it_returns_non_duplicate_message_id()
    {
        $message = factory(InboundMessage::class)->create(['created_at' => date('Y-m-d')]);

        $filteredMessageIdList = $this->computeDifference([
            $message->message_id,
            'nonDuplicateIdOne',
            'nonDuplicateIdTwo'
        ]);

        $this->assertEquals(2, count($filteredMessageIdList));
        $this->assertEquals([1 => 'nonDuplicateIdOne', 2 => 'nonDuplicateIdTwo'], $filteredMessageIdList);
    }
}