<?php 
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Console\Commands\SyncOutboundMessagesCommand;

class CommandServiceProvider extends ServiceProvider
{

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('command.outbound.sync', function()
        {
            return new SyncOutboundMessagesCommand;
        });

        $this->commands(
            'command.outbound.sync'
        );
    }
}