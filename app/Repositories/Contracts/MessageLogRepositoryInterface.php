<?php

namespace App\Repositories\Contracts;

use App\Message;

interface MessageLogRepositoryInterface
{
    /**
     * @param array $payload
     * @return mixed
     */
    public function insertBulk(array $payload);
}