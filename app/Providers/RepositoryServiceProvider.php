<?php

namespace App\Providers;

use App\Repositories\Contracts\ActionRepositoryInterface;
use App\Repositories\Contracts\InboundMessageLogRepositoryInterface;
use App\Repositories\Contracts\InboundMessageRepositoryInterface;
use App\Repositories\Contracts\OutboundMessageInterface;
use App\Repositories\Contracts\OutboundMessageLogInterface;
use App\Repositories\Contracts\SubscriberRepositoryInterface;
use App\Repositories\Eloquent\ActionRepositoryEloquent;
use App\Repositories\Eloquent\InboundMessageLogRepositoryEloquent;
use App\Repositories\Eloquent\InboundMessageRepositoryEloquent;
use App\Repositories\Eloquent\OutboundMessageLogRepositoryEloquent;
use App\Repositories\Eloquent\OutboundMessageRepositoryEloquent;
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
        $this->app->bind(InboundMessageRepositoryInterface::class, InboundMessageRepositoryEloquent::class);
        $this->app->bind(ActionRepositoryInterface::class, ActionRepositoryEloquent::class);
        $this->app->bind(SubscriberRepositoryInterface::class, SubscriberRepositoryEloquent::class);
        $this->app->bind(InboundMessageLogRepositoryInterface::class, InboundMessageLogRepositoryEloquent::class);
        $this->app->bind(OutboundMessageInterface::class, OutboundMessageRepositoryEloquent::class);
        $this->app->bind(OutboundMessageLogInterface::class, OutboundMessageLogRepositoryEloquent::class);
    }
}
