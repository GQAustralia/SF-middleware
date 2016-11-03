<?php namespace App\Console\Commands;

use App\Jobs\Exceptions\AWSSQSServerException;
use App\Jobs\Exceptions\DatabaseAlreadySyncedException;
use App\Jobs\Exceptions\InsertIgnoreBulkException;
use App\Jobs\Exceptions\NoMessagesToSyncException;
use App\Jobs\Exceptions\NoValidMessagesFromQueueException;
use App\Jobs\SyncAllAwsSqsMessagesJob;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Laravel\Lumen\Routing\ProvidesConvenienceMethods;

class SyncSQSMessagesCommand extends Command
{

    use ProvidesConvenienceMethods;

    const DATABASE_ERROR_MESSAGE = 'Database error please contact your Administrator.';
    const SYNC_SUCCESS = 'Sync Successful.';

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'sync:sqs {queue=CRMInwardQueue}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calls the MessageQueueController with dynamic parameter queue name.';

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
            $this->dispatch(new SyncAllAwsSqsMessagesJob($this->argument('queue')));
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

        echo $resultMessage;
    }

}