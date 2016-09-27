<?php

namespace App\Resolvers;

interface AWSClientInterface
{
    /**
     * Returns new Instance of AWS Client
     *
     * @return mixed
     */
    public function client();
}