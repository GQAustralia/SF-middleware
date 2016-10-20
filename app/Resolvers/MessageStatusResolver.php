<?php

namespace App\Resolvers;

use App\Repositories\Contracts\MessageRepositoryInterface;

class MessageStatusResolver
{
    /**
     * @var MessageRepositoryInterface
     */
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
     * @return bool
     */
    public function resolve(array $messageIdList = [])
    {
        if (empty($messageIdList)) {
            return null;
        }

        collect($messageIdList)->each(function ($messageId) {
            $totalFail = $this->message->getTotalFailSentMessage($messageId);

            if ($totalFail === 0) {
                $this->message->update(['completed' => 'Y'], $messageId);
            }
        });

        return true;

    }
}