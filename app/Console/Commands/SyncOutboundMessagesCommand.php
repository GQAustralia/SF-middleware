<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\OutboundService;

class SyncOutboundMessagesCommand extends Command
{
    const SYNC_SUCCESS = 'Sync Successful.';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'outbound:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Outbound AWS Messages';

    /**
     * OutboundSync constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $outboundService = new OutboundService();
        $outboundService->sendMessagesToSalesforce();

        $this->info(self::SYNC_SUCCESS);
    }
}
