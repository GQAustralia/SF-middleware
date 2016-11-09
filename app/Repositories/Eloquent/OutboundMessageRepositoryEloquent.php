<?php

namespace App\Repositories\Eloquent;

use App\OutboundMessage;
use App\Repositories\Contracts\OutboundMessageInterface;

class OutboundMessageRepositoryEloquent extends RepositoryEloquent implements OutboundMessageInterface
{
    /**
     * @var OutboundMessage
     */
    protected $outboundMessageLog;

    /**
     * SalesForceLogRepository constructor.
     *
     * @param OutboundMessage $outboundMessageLog
     */
    public function __construct(OutboundMessage $outboundMessageLog)
    {
        $this->outboundMessageLog = $outboundMessageLog;
    }

    /**
     * @return OutboundMessage
     */
    public function model()
    {
        return $this->outboundMessageLog;
    }
}