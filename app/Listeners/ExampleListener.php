<?php

namespace App\Listeners;

use App\Events\ExampleEvent;
use App\Message;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class ExampleListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Handle the event.
     *
     * @param  ExampleEvent  $event
     * @return void
     */
    public function handle(ExampleEvent $event)
    {
        factory(Message::class)->create(['queue_id' => 1, 'message_content' => 'jemjemjem']);
    }
}
