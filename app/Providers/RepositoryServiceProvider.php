<?php

namespace App\Providers;

use App\Repositories\Contracts\ActionRepositoryInterface;
use App\Repositories\Contracts\MessageLogRepositoryInterface;
use App\Repositories\Contracts\MessageRepositoryInterface;
use App\Repositories\Contracts\SubscriberRepositoryInterface;
use App\Repositories\Eloquent\ActionRepositoryEloquent;
use App\Repositories\Eloquent\MessageLogRepositoryEloquent;
use App\Repositories\Eloquent\MessageRepositoryEloquent;
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
        $this->app->bind(ActionRepositoryInterface::class, ActionRepositoryEloquent::class);
        $this->app->bind(SubscriberRepositoryInterface::class, SubscriberRepositoryEloquent::class);
        $this->app->bind(MessageLogRepositoryInterface::class, MessageLogRepositoryEloquent::class);
    }
}
