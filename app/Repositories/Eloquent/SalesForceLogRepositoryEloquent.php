<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\SalesForceLogInterface;
use App\SalesForceLog;

class SalesForceLogRepositoryEloquent extends RepositoryEloquent implements SalesForceLogInterface
{
    /**
     * @var SalesForceLog
     */
    protected $salesForceLog;

    /**
     * SalesForceLogRepository constructor.
     * @param SalesForceLog $salesForceLog
     */
    public function __construct(SalesForceLog $salesForceLog)
    {
        $this->salesForceLog = $salesForceLog;
    }

    /**
     * @return SalesForceLog
     */
    public function model()
    {
        return $this->salesForceLog;
    }
}