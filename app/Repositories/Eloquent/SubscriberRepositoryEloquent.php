<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\SubscriberRepositoryInterface;
use App\Subscriber;

class SubscriberRepositoryEloquent extends RepositoryEloquent implements SubscriberRepositoryInterface
{
    /**
     * @var Subscriber
     */
    private $subscriber;

    /**
     * SubscriberRepositoryEloquent constructor.
     *
     * @param Subscriber $subscriber
     */
    public function __construct(Subscriber $subscriber)
    {
        $this->subscriber = $subscriber;
    }

    /**
     * @return Subscriber
     */
    public function model()
    {
        return $this->subscriber;
    }

    /**
     * @param int $subscriberId
     * @param int $actionId
     * @return Subscriber
     */
    public function attachAction($subscriberId, $actionId)
    {
        $subscriber = $this->subscriber->find($subscriberId);

        if (!$subscriber) {
            return null;
        }

        $subscriber->action()->attach($actionId);

        return $subscriber;
    }
}
