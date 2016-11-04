<?php

namespace App\Resolvers;

trait ProvidesDecodingOfSalesForceMessages
{
    /**
     * @param string $message
     * @return string
     */
    public function deCodeSalesForceMessage($message)
    {
        return json_decode($message, true);
    }
}
