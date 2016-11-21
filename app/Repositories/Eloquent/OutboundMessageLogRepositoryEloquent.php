<?php

namespace App\Repositories\Eloquent;

use App\OutboundMessageLog;
use App\Repositories\Contracts\OutboundMessageLogInterface;

class OutboundMessageLogRepositoryEloquent extends RepositoryEloquent implements OutboundMessageLogInterface
{
    /**
     * @var OutboundMessage
     */
    protected $outboundMessageLog;

    /**
     * OutboundMessageLogRepositoryEloquent constructor.
     *
     * @param OutboundMessageLog $outboundMessageLog
     */
    public function __construct(OutboundMessageLog $outboundMessageLog)
    {
        $this->outboundMessageLog = $outboundMessageLog;
    }

    /**
     * @return OutboundMessageLog
     */
    public function model()
    {
        return $this->outboundMessageLog;
    }
}
