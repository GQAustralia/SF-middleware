<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Lumen\Http\ResponseFactory;

class ExampleResponseController extends Controller
{
    protected $responseFactory;

    /**
     * MessageQueueController constructor.
     *
     * @param ResponseFactory $responseFactory
     */
    public function __construct(ResponseFactory $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    /**
     * @return \Illuminate\Http\Response
     */
    public function success(Request $request)
    {
        return $this->responseFactory->make('Success.', self::SUCCESS_STATUS_CODE);
    }

    /**
     * @return \Illuminate\Http\Response
     */
    public function failed()
    {
        return $this->responseFactory->make('Failed.', self::BAD_REQUEST_STATUS_CODE);
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function formParams(Request $request)
    {
        $input = implode(',', $request->all());

        return $this->responseFactory->make($input, self::SUCCESS_STATUS_CODE);
    }
}
