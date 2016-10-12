<?php

namespace App\Events;

class SqsMessagesWasSyncedEvent extends Event
{
    /**
     * @var
     */
    private $queueMessages;

    /**
     * SqsMessagesWasSyncedEvent constructor.
     * @param $queueMessages
     */
    public function __construct($queueMessages)
    {
        $this->queueMessages = $queueMessages;
    }
}
