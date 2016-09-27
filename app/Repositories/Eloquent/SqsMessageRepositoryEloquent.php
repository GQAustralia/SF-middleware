<?php

namespace App\Repositories\Eloquent;

use App\SqsMessage;

class SqsMessageRepositoryEloquent extends RepositoryEloquent
{
    /**
     * @var SqsMessage
     */
    private $sqsMessage;

    /**
     * SqsMessageRepositoryEloquent constructor.
     * @param SqsMessage $sqsMessage
     */
    public function __construct(SqsMessage $sqsMessage)
    {
        $this->sqsMessage = $sqsMessage;
    }

    /**
     * @return SqsMessage
     */
    public function model()
    {
        return $this->sqsMessage;
    }
}