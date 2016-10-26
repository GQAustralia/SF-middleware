<?php

namespace App\Resolvers;

use App\Message;

trait DifferMessageInputDataToDatabase
{
    /**
     * @param array $input
     * @return array
     */
    public function computeDifference(array $input)
    {
        $dateNow = date('Y-m-d H:i:s');
        $dateFrom = date('Y-m-d H:i:s', (strtotime('-3 day', strtotime($dateNow))));

        $message = Message::whereBetween('created_at', [$dateFrom, $dateNow])->get();

        $messageIds = collect($message)->pluck('message_id')->toArray();

        return collect($input)->diff($messageIds)->all();
    }
}