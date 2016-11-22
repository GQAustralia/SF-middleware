<?php

namespace App\Resolvers;

use App\Repositories\Contracts\InboundMessageRepositoryInterface;

class InboundMessageStatusResolver
{
    /**
     * @var InboundMessageRepositoryInterface
     */
    protected $message;

    /**
     * MessageStatusResolver constructor.
     * @param InboundMessageRepositoryInterface $message
     */
    public function __construct(InboundMessageRepositoryInterface $message)
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
