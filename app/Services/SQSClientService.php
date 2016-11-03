<?php

namespace App\Services;

use App\Resolvers\ProvidesAWSConnectionParameters;
use Aws\Sqs\SqsClient;

class SQSClientService implements AWSClientInterface
{
    use ProvidesAWSConnectionParameters;

    /**
     * @var SqsClient
     */
    protected $client;

    /**
     * SQSClientResolver constructor.
     */
    public function __construct()
    {
        $this->client = new SqsClient($this->awsFullCredentials());
    }

    /**
     * @return SqsClient
     */
    public function client()
    {
        return $this->client;
    }
}
