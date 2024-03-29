<?php

namespace App\Repositories\Eloquent;

use App\Action;
use App\MessageLog;
use App\Repositories\Contracts\MessageLogRepositoryInterface;

class MessageLogRepositoryEloquent extends RepositoryEloquent implements MessageLogRepositoryInterface
{
    /**
     * @var SentMessage
     */
    private $messageLog;

    /**
     * MessageLogRepositoryEloquent constructor.
     * @param MessageLog $messageLog
     */
    public function __construct(MessageLog $messageLog)
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
