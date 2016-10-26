<?php

namespace App\Repositories\Eloquent;

use App\Queue;
use App\Repositories\Contracts\QueueRepositoryInterface;
use App\Subscriber;

class QueueRepositoryEloquent extends RepositoryEloquent implements QueueRepositoryInterface
{
    /**
     * @var Queue
     */
    private $queue;

    /**
     * QueueRepositoryEloquent constructor.
     * @param Queue $queue
     */
    public function __construct(Queue $queue)
    {
        $this->queue = $queue;
    }

    /**
     * @return Queue
     */
    public function model()
    {
        return $this->queue;
    }

    /**
     * Associate Subscriber to Que.
     *
     * @param int $queueId
     * @param int $subscriberId
     * @return Subscriber
     */
    public function attachSubscriber($queueId, $subscriberId)
    {
        $queue = $this->queue->find($queueId);

        if (!$queue) {
            return null;
        }

        $queue->subscriber()->attach($subscriberId);

        return $queue;
    }
}