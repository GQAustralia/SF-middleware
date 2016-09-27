<?php

use App\Resolvers\ProvidesAWSConnectionParameters;
use Illuminate\Support\Facades\Config;

class ProvidesAWSConnectionParametersTest extends TestCase
{
    use ProvidesAWSConnectionParameters;

    /** @test */
    public function it_returns_secret_key_matched_to_config_secret_key()
    {
        $this->assertEquals(Config::get('aws.secret'), $this->awsSecret());
    }

    /** @test */
    public function it_returns_key_matched_to_config_key()
    {
        $this->assertEquals(Config::get('aws.key'), $this->awsKey());
    }

    /** @test */
    public function it_returns_region_matched_to_config_region()
    {
        $this->assertEquals(Config::get('aws.region'), $this->awsRegion());
    }

    /** @test */
    public function it_returns_version_matched_to_config_version()
    {
        $this->assertEquals(Config::get('aws.version'), $this->awsVersion());
    }

    /** @test */
    public function it_returns_credential_payload_matched_to_config_key_and_secret()
    {
        $this->assertEquals([
            'secret' => Config::get('aws.secret'),
            'key' => Config::get('aws.key'),

        ], $this->awsCredentials());
    }

    /** @test */
    public function it_returns_complete_credentials()
    {
        $this->assertEquals([
            'credentials' => ['secret' => Config::get('aws.secret'), 'key' => Config::get('aws.key')],
            'region' => Config::get('aws.region'),
            'version' => Config::get('aws.version')
        ], $this->awsFullCredentials());
    }
}