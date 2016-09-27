<?php

namespace App\Repositories\Contracts;

interface RepositoryInterface
{
    /**
     * @param array $input
     * @return mixed
     */
    public function create($input = []);
}