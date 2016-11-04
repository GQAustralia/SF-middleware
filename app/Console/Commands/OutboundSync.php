<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\OutboundService;

class OutboundSync extends Command
{
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
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
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
//    public function handle()
//    {
//        //
//    }
    
     /**
   * Execute the console command.
   *
   * @return mixed
   */
  public function fire()
  {
     $outboundService = new OutboundService();
     $outboundService->sendMessagesToSalesforce();
    $this->info('Run Succesfully');
  }

  /**
   * Get the console command arguments.
   *
   * @return array
   */
  protected function getArguments()
  {
    return array(
      //array('example', InputArgument::REQUIRED, 'An example argument.'),
    );
  }

  /**
   * Get the console command options.
   *
   * @return array
   */
  protected function getOptions()
  {
    return array(
//      array('example', null, InputOption::VALUE_OPTIONAL,
//        'An example option.' ,null),
    );
  }
}
