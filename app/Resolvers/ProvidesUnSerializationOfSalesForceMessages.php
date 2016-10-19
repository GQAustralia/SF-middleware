<?php

namespace App\Resolvers;

trait ProvidesUnSerializationOfSalesForceMessages
{
    /**
     * @param string $message
     * @return string
     */
    public function unSerializeSalesForceMessage($message)
    {
        return unserialize(str_replace('\'', '"', $message));
    }
}