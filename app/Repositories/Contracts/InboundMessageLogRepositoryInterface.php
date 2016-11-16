<?php

namespace App\Repositories\Contracts;

interface InboundMessageLogRepositoryInterface extends RepositoryInterface
{
    /**
     * @param array $payload
     * @return mixed
     */
    public function insertBulk(array $payload);
}