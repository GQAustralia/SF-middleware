<?php

use App\Message;
use App\Resolvers\DifferMessageInputDataToDatabase;

class DifferMessageInputDataToDatabaseTest extends BaseTestCase
{
    use DifferMessageInputDataToDatabase;

    /** @test */
    public function it_returns_non_duplicate_message_id()
    {
        $message = factory(Message::class)->create(['created_at' => date('Y-m-d')]);

        $filteredMessageIdList = $this->computeDifference([
            $message->message_id,
            'nonDuplicateIdOne',
            'nonDuplicateIdTwo'
        ]);

        $this->assertEquals(2, count($filteredMessageIdList));
        $this->assertEquals([1 => 'nonDuplicateIdOne', 2 => 'nonDuplicateIdTwo'], $filteredMessageIdList);
    }
}