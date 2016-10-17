<?php

class ExampleResponseControllerTest extends BaseTestCase
{
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

}