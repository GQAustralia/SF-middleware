<?php

class OutboundMessageTest extends BaseTestCase
{
    /** @test */
    public function locateTest()
    {
        $this->runningTestFor(get_class($this));
    }

    /** @test */
    public function it_has_one_message_log()
    {
        $model = Mockery::mock('App\OutboundMessage[hasOne]');

        $model->shouldReceive('hasOne')->andReturn(true);

        $this->assertTrue($model->log());
    }
}