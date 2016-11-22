<?php

namespace App\Resolvers;

use App\InboundMessage;

trait DifferMessageInputDataToDatabase
{
    /**
     * [computeDifference Computes the difference of message id existing on database to fetched message_ids]
     *
     * Note that this is fetching all messages past 3 days.
     *
     * @param  array $input
     * @return array
     */
    public function computeDifference(array $input)
    {
        $dateNow = date('Y-m-d H:i:s');
        $dateFrom = date('Y-m-d H:i:s', (strtotime('-3 day', strtotime($dateNow))));

        $message = InboundMessage::whereBetween('created_at', [$dateFrom, $dateNow])->get();

        $messageIds = collect($message)->pluck('message_id')->toArray();

        return collect($input)->diff($messageIds)->all();
    }
}
