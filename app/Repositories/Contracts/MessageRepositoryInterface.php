<?php

namespace App\Repositories\Contracts;

use App\Message;

interface MessageRepositoryInterface extends RepositoryInterface
{
    /**
     * @param Message $message
     * @param array $input
     * @return mixed
     */
    public function attachSubscriber(Message $message, array $input);
}