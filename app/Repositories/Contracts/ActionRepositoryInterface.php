<?php

namespace App\Repositories\Contracts;

interface ActionRepositoryInterface extends RepositoryInterface
{
    /**
     * Associate Subscriber to Action.
     *
     * @param  int $actionId
     * @param  int $subscriberId
     * @return mixed
     */
    public function attachSubscriber($actionId, $subscriberId);
}
