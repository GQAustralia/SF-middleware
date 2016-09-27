<?php

namespace App\Http\Controllers;

use App\Resolvers\SQSClientResolver;
use Aws\Sqs\SqsClient;
use Illuminate\Http\Request;

class MessageQueueController extends Controller
{
    const QUEUE_URL = 'https://sqs.ap-southeast-2.amazonaws.com/187591088561/JemQue';

    /**
     * @var SqsClient
     */
    private $sqs;

    /**
     * ExampleController constructor.
     * @param SQSClientResolver $sqs
     */
    public function __construct(SQSClientResolver $sqs)
    {
        $this->sqs = $sqs;
    }

    /**
     * @param Request $request
     */
    public function create(Request $request)
    {
        $response = $this->sqs->client()->receiveMessage([
            'QueueUrl' => self::QUEUE_URL,
            'AttributeNames' => ['All'],
            'MaxNumberOfMessages' => 10,
        ]);
    }
}
