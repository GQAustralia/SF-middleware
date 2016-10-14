<?php

use App\Events\SqsMessagesWasSynced;
use Faker\Factory as Faker;

class SqsMessagesWasSyncedEventTest extends BaseTestCase
{
    /** @test */
    public function it_instantiate_new_instance_of_event()
    {
        $faker = (new Faker)->create();
        $messageIds = [];

        for ($i = 0; $i <= 10; $i++) {
            $messageIds[] = $faker->shuffleString('abcdefghijklmnopqrstuvwxyz1234567890-');
        }

        $event = new SqsMessagesWasSynced($messageIds);

        $this->assertEquals($messageIds, $event->messageIdList);
        $this->assertInstanceOf(SqsMessagesWasSynced::class, $event);
    }
}