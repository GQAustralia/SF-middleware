<?php

class MessageQueueControllerTest extends BaseTestCase
{
    /** @test */
    public function it_returns_true()
    {
        $result = $this->call('POST','create', []);

        $this->assertEquals('jem', $this->printContent());
    }
}