<?php

namespace App\Repositories\Contracts;

interface MessageLogRepositoryInterface extends RepositoryInterface
{
    /**
     * @param array $payload
     * @return mixed
     */
    public function insertBulk(array $payload);
}