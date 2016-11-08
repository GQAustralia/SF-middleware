<?php namespace App\Console\Commands;

use App\Exceptions\AWSSQSServerException;
use App\Exceptions\DatabaseAlreadySyncedException;
use App\Exceptions\InsertIgnoreBulkException;
use App\Exceptions\NoMessagesToSyncException;
use App\Exceptions\NoValidMessagesFromQueueException;
use App\Services\InboundMessagesSync;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Laravel\Lumen\Routing\ProvidesConvenienceMethods;

class SyncInboundMessagesCommand extends Command
{
    use ProvidesConvenienceMethods;

    const DATABASE_ERROR_MESSAGE = 'Database error please contact your Administrator.';
    const SYNC_SUCCESS = 'Sync Successful.';

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'inbound:sync {queue=CRMInwardQueue}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Inbound AWS Messages';

    /**
     * @var InboundMessagesSync
     */
    private $inbound;

    /**
     * SyncSQSMessagesCommand constructor.
     * @param InboundMessagesSync $inbound
     */
    public function __construct(InboundMessagesSync $inbound)
    {
        parent::__construct();

        $this->inbound = $inbound;
    }

    /**
     * @throws AWSSQSServerException
     * @throws NoMessagesToSyncException
     * @throws DatabaseAlreadySyncedException
     * @throws NoValidMessagesFromQueueException
     * @throws InsertIgnoreBulkException
     * @throws QueryException
     *
     * @return string
     */
    public function fire()
    {
        $resultMessage = self::SYNC_SUCCESS;

        try {
            $this->inbound->handle($this->argument('queue'));
        } catch (AWSSQSServerException $exc) {
            $resultMessage = $exc->getMessage();
        } catch (NoMessagesToSyncException $exc) {
            $resultMessage = $exc->getMessage();
        } catch (DatabaseAlreadySyncedException $exc) {
            $resultMessage = $exc->getMessage();
        } catch (NoValidMessagesFromQueueException $exc) {
            $resultMessage = $exc->getMessage();
        } catch (InsertIgnoreBulkException $exc) {
            $resultMessage = $exc->getMessage();
        } catch (QueryException $exc) {
            $resultMessage = self::DATABASE_ERROR_MESSAGE;
        }

        $this->info($resultMessage);
    }

}