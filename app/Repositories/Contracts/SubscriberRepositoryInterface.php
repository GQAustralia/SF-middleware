<?php

namespace App\Repositories\Contracts;

interface SubscriberRepositoryInterface extends RepositoryInterface
{
    /**
     * Associate Que on Subscriber.
     *
     * @param int $subscriberId
     * @param int $queueId
     * @return mixed
     */
    public function attachQueue($subscriberId, $queueId);
}