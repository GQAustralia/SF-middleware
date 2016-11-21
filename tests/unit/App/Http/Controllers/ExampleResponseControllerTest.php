<?php

class ExampleResponseControllerTest extends BaseTestCase
{
    /** @test */
    public function locateTest()
    {
        $this->runningTestFor(get_class($this));
    }

    /** @test */
    public function it_returns_valid_response()
    {
        $this->post('example-response/success');

        $this->assertResponseOk();
    }

    /** @test */
    public function it_returns_invalid_response()
    {
        $this->post('example-response/failed');

        $this->assertResponseStatus(400);
    }

    /** @test */
    public function it_returns_an_array_of_form_params()
    {
        $sampleFormParams = ['param1' => 'one', 'param2' => 'two', 'param3' => 'three'];

        $this->post('example-response/form_params', $sampleFormParams);

        $this->assertEquals('one,two,three', $this->getContent());
        $this->assertResponseOk();
    }

}