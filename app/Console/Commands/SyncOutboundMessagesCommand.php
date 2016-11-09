<?php

namespace App\Console\Commands;

use App\Exceptions\AWSSQSServerException;
use App\Exceptions\NoMessagesToSyncException;
use Illuminate\Console\Command;
use App\Services\OutboundMessageSyncService;
use Laravel\Lumen\Routing\ProvidesConvenienceMethods;

class SyncOutboundMessagesCommand extends Command
{
    use ProvidesConvenienceMethods;

    const SYNC_SUCCESS = 'Sync Successful.';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'outbound:sync {queue=CRMOutboundQueue}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Outbound AWS Messages';
    /**
     * @var OutboundMessageSyncService
     */
    private $outbound;

    /**
     * OutboundSync constructor.
     * @param OutboundMessageSyncService $outbound
     */
    public function __construct(OutboundMessageSyncService $outbound)
    {
        parent::__construct();

        $this->outbound = $outbound;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $resultMessage = self::SYNC_SUCCESS;

        try {
            $this->outbound->handle($this->argument('queue'));
        } catch (AWSSQSServerException $exc) {
            $resultMessage = $exc->getMessage();
        }

        $this->info($resultMessage);
    }
}
