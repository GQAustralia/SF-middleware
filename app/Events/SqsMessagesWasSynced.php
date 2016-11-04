<?php

namespace App\Events;

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
