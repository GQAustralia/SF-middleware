<?php

namespace App\Repositories\Contracts;

interface OutboundMessageInterface extends RepositoryInterface
{
    /**
     * @param array $input
     * @param mixed $field
     * @return mixed
     */
    public function update(array $input, $field);
}