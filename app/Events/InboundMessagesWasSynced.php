<?php

namespace App\Events;

class InboundMessagesWasSynced extends Event
{
    public $messageIdList = [];

    /**
     * InboundMessagesWasSynced constructor.
     * @param array $messageIdList
     */
    public function __construct($messageIdList)
    {
        $this->messageIdList = $messageIdList;
    }
}
