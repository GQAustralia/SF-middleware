<?php

namespace App\Providers;

use App\Repositories\Contracts\MessageRepositoryInterface;
use App\Repositories\Contracts\QueueRepositoryInterface;
use App\Repositories\Contracts\SubscriberRepositoryInterface;
use App\Repositories\Eloquent\MessageRepositoryEloquent;
use App\Repositories\Eloquent\QueueRepositoryEloquent;
use App\Repositories\Eloquent\SubscriberRepositoryEloquent;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(MessageRepositoryInterface::class, MessageRepositoryEloquent::class);
        $this->app->bind(QueueRepositoryInterface::class, QueueRepositoryEloquent::class);
        $this->app->bind(SubscriberRepositoryInterface::class, SubscriberRepositoryEloquent::class);
    }
}
