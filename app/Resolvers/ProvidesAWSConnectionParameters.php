<?php

namespace App\Resolvers;

use Illuminate\Support\Facades\Config;

trait ProvidesAWSConnectionParameters
{
    /**
     * @return string
     */
    public function awsSecret()
    {
        return Config::get('aws.secret');
    }

    /**
     * @return string
     */
    public function awsKey()
    {
        return Config::get('aws.key');
    }

    /**
     * @return string
     */
    public function awsRegion()
    {
        return Config::get('aws.region');
    }

    /**
     * @return string
     */
    public function awsVersion()
    {
        return Config::get('aws.version');
    }

    /**
     * @return array
     */
    public function awsCredentials()
    {
        return [
            'secret' => Config::get('aws.secret'),
            'key' => Config::get('aws.key'),
        ];
    }

    /**
     * @return array
     */
    public function awsFullCredentials()
    {
        return [
            'credentials' => ['secret' => Config::get('aws.secret'), 'key' => Config::get('aws.key')],
            'region' => Config::get('aws.region'),
            'version' => Config::get('aws.version'),
        ];
    }
}