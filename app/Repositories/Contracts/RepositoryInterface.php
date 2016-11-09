<?php

namespace App\Repositories\Contracts;

interface RepositoryInterface
{
    /**
     * @param array $input
     * @return mixed
     */
    public function create($input = []);

    /**
     * @return mixed
     */
    public function all();

    /**
     * @param string $attribute
     * @param string|integer $value
     * @return mixed
     */
    public function findBy($attribute, $value);

    /**
     * @param string $attribute
     * @param string|integer $value
     * @return mixed
     */
    public function findAllBy($attribute, $value);
}