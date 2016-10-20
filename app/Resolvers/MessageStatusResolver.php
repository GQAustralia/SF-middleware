<?php

namespace App\Resolvers;

use App\Repositories\Contracts\MessageRepositoryInterface;

class MessageStatusResolver
{
    protected $message;

    /**
     * MessageStatusResolver constructor.
     * @param MessageRepositoryInterface $message
     */
    public function __construct(MessageRepositoryInterface $message)
    {
        $this->message = $message;
    }

    /**
     * @param array $messageIdList
     * @return null
     */
    public function resolve(array $messageIdList = [])
    {
        if (empty($messageIdList)) {
            return null;
        }

        collect($messageIdList)->each(function($messageId) {
            
        });

    }
}