<?php

namespace App\Services;

interface AWSClientInterface
{
    /**
     * Returns new Instance of AWS Client
     *
     * @return mixed
     */
    public function client();
}