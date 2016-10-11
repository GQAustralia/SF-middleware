<?php

class TestCase extends Laravel\Lumen\Testing\TestCase
{
    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        return require __DIR__.'/../bootstrap/app.php';
    }

    public function setUp()
    {
        parent::setUp();

        $this->refreshApplication();

        $this->artisan('migrate');
    }

    public function tearDown()
    {
        $this->artisan('migrate:reset');

        parent::tearDown();
    }
}