<?php

namespace App\Resolvers;

trait UnserializeSalesForceMessages
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