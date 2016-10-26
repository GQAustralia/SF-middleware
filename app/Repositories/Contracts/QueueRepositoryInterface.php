<?php

namespace App\Repositories\Contracts;

interface QueueRepositoryInterface extends RepositoryInterface
{
    /**
     * Associate Subscriber to Que.
     *
     * @param int $queueId
     * @param int $subscriberId
     * @return mixed
     */
    public function attachSubscriber($queueId, $subscriberId);
}