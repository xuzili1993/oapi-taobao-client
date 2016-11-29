<?php

namespace App\Console\Commands;

use App\Http\Controllers\Util\SyncOrdersFromApi\Base;
use Illuminate\Console\Command;

class SyncTradesLogsDelete extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'SyncTrades:logsDelete';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'delete logs';

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
    public function handle()
    {
        (new Base())->SyncTradesLogsDelete();
    }
}
