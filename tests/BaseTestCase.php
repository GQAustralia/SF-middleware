<?php

use Illuminate\Support\Facades\DB;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class BaseTestCase extends TestCase
{
    use DatabaseMigrations, DatabaseTransactions;

    /**
     * @return mixed|string
     */
    protected function getContent()
    {
        $content = $this->response->getContent();

        $json = json_decode($content);

        if (json_last_error() === JSON_ERROR_NONE) {
            $content = $json;
        }

        return $content;
    }

    /**
     * @return void
     */
    protected function debugContent()
    {
        print_r($this->response->getContent());

        die('end of debug');
    }

    /**
     * @param array $keys
     * @param $expected
     * @param $actual
     */
    protected function assertAttributesExpectedValues($keys = [], $expected, $actual)
    {
        if (is_object($expected)) {
            $expected = json_decode(json_encode($expected), true);
        }
        if (is_object($actual)) {
            $actual = json_decode(json_encode($actual), true);
        }
        foreach ($keys as $key) {
            $this->assertEquals($expected[$key], $actual[$key]);
        }
    }

    /**
     * @param string $table
     * @param array|object $expected
     */
    protected function assertMultipleSeeInDatabase($table, $expected)
    {
        if (is_object($expected)) {
            $expected = json_decode(json_encode($expected), true);
        }
        foreach ($expected as $expect) {
            $this->seeInDatabase($table, $expect);
        }
    }

    /**
     * @param string $table
     * @param array|object $expected
     */
    protected function assertMultipleNotSeeInDatabase($table, $expected)
    {
        if (is_object($expected)) {
            $expected = json_decode(json_encode($expected), true);
        }
        foreach ($expected as $expect) {
            $this->notSeeInDatabase($table, $expect);
        }
    }

    /**
     * @param string $database
     * @param array $migrationParameters
     */
    protected function setConnection($database, $migrationParameters = [])
    {
        DB::setDefaultConnection($database);

        $this->artisan('migrate', $migrationParameters);
    }
}
