<?php

namespace App\Repositories\Eloquent;

use App\Action;
use App\InboundMessageLog;
use App\Repositories\Contracts\InboundMessageLogRepositoryInterface;

class InboundMessageLogRepositoryEloquent extends RepositoryEloquent implements InboundMessageLogRepositoryInterface
{
    /**
     * @var InboundMessageLog
     */
    private $messageLog;

    /**
     * InboundMessageLogRepositoryEloquent constructor.
     *
     * @param InboundMessageLog $messageLog
     */
    public function __construct(InboundMessageLog $messageLog)
    {
        $this->messageLog = $messageLog;
    }

    /**
     * @return Action
     */
    public function model()
    {
        return $this->messageLog;
    }

    /**
     * Insert Bulk
     *
     * @param array $payload
     * @return bool
     */
    public function insertBulk(array $payload)
    {
        if (empty($payload)) {
            return null;
        }

        return $this->messageLog->insert($payload);
    }
}
