<?php

namespace App\Repositories\Eloquent;

use App\Action;
use App\Repositories\Contracts\ActionRepositoryInterface;
use App\Subscriber;

class ActionRepositoryEloquent extends RepositoryEloquent implements ActionRepositoryInterface
{
    /**
     * @var Action
     */
    private $action;

    /**
     * ActionRepositoryEloquent constructor.
     * @param Action $action
     */
    public function __construct(Action $action)
    {
        $this->action = $action;
    }

    /**
     * @return Action
     */
    public function model()
    {
        return $this->action;
    }

    /**
     * Associate Subscriber to Action.
     *
     * @param int $actionId
     * @param int $subscriberId
     * @return Subscriber
     */
    public function attachSubscriber($actionId, $subscriberId)
    {
        $action = $this->action->find($actionId);

        if (!$action) {
            return null;
        }

        $action->subscriber()->attach($subscriberId);

        return $action;
    }
}
