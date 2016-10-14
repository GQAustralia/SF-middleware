<?php

namespace App\Events;

use App\Message;

class SqsMessagesWasSynced extends Event
{
    public $messageIdList = [];

    /**
     * SqsMessagesWasSynced constructor.
     * @param $messageIdList
     */
    public function __construct($messageIdList)
    {
        $this->messageIdList = $messageIdList;
    }
}
