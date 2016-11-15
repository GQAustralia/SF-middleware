<?php

use App\InboundMessage;
use App\Resolvers\InsertIgnoreBulkMySqlResolver;

class InsertIgnoreBulkMySqlResolverTest extends BaseTestCase
{
    use InsertIgnoreBulkMySqlResolver;

    /** @test */
    public function locateTest()
    {
        $this->runningTestFor(get_class($this));
    }

    /** @test */
    public function it_returns_query()
    {
        $message = factory(InboundMessage::class, 2)->make();

        $table = 'message';
        $insertFields = ['message_id', 'action_id', 'message_content', 'completed'];
        $values = $message->toArray();
        $updateFields = ['completed', 'message_content'];

        $expected = 'INSERT IGNORE INTO message (action_id,completed,message_content,message_id) VALUES ';
        $expected .= '("' . $message[0]->action_id . '",' . '"' . $message[0]->completed . '",' . '"' . $message[0]->message_content . '",' . '"' . $message[0]->message_id . '"' . '),';
        $expected .= '("' . $message[1]->action_id . '",' . '"' . $message[1]->completed . '",' . '"' . $message[1]->message_content . '",' . '"' . $message[1]->message_id . '"' . ')';

        $result = $this->resolve($table, $insertFields, $values, $updateFields);

        $this->assertEquals($expected, $result);
    }
}
