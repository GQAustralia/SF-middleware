<?php

use App\Message;
use App\Resolvers\InsertIgnoreBulkMySqlResolver;

class InsertIgnoreBulkMySqlResolverTest extends TestCase
{
    use InsertIgnoreBulkMySqlResolver;

    /** @test */
    public function it_returns_query()
    {
        $message = factory(Message::class, 2)->make();

        $table = 'message';
        $insertFields = ['message_id', 'queue_id', 'message_content', 'completed'];
        $values = $message->toArray();
        $updateFields = ['completed', 'message_content'];

        $expected = 'INSERT IGNORE INTO message (completed,message_content,message_id,queue_id) VALUES ';
        $expected .= '("' . $message[0]->completed . '",' . '"' . $message[0]->message_content . '",' . '"' . $message[0]->message_id . '","' . $message[0]->queue_id . '"' . '),';
        $expected .= '("' . $message[1]->completed . '",' . '"' . $message[1]->message_content . '",' . '"' . $message[1]->message_id . '","' . $message[1]->queue_id . '"' . ')';

        $result = $this->resolve($table, $insertFields, $values, $updateFields);

        $this->assertEquals($expected, $result);
    }
}