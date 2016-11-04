<?php

namespace App\Repositories\Contracts;

interface SubscriberRepositoryInterface extends RepositoryInterface
{
    /**
     * Associate Que on Subscriber.
     *
     * @param int $subscriberId
     * @param int $actionId
     * @return mixed
     */
    public function attachAction($subscriberId, $actionId);
}