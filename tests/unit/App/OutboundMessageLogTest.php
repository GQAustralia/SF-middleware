<?php

class OutboundMessageLogTest extends BaseTestCase
{
    /** @test */
    public function it_belongs_to_an_outbound_message()
    {
        $model = Mockery::mock('App\OutboundMessageLog[belongsTo]');

        $model->shouldReceive('belongsTo')->andReturn(true);

        $this->assertTrue($model->message());
    }
}