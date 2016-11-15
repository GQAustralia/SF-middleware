<?php

use App\Events\InboundMessagesWasSynced;
use Faker\Factory as Faker;

class InboundMessagesWasSyncedTest extends BaseTestCase
{
    /** @test */
    public function locateTest()
    {
        $this->runningTestFor(get_class($this));
    }

    /** @test */
    public function it_instantiate_new_instance_of_event()
    {
        $faker = (new Faker)->create();
        $messageIds = [];

        for ($i = 0; $i <= 10; $i++) {
            $messageIds[] = $faker->shuffleString('abcdefghijklmnopqrstuvwxyz1234567890-');
        }

        $event = new InboundMessagesWasSynced($messageIds);

        $this->assertEquals($messageIds, $event->messageIdList);
        $this->assertInstanceOf(InboundMessagesWasSynced::class, $event);
    }
}