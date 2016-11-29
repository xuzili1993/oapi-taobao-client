<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Util\SyncOrdersFromApi\StartSync;

class SyncTradesFromApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'SyncTrades:newOrder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'sync from api';

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
        (new StartSync())->run('taobao');
        (new StartSync())->run('tongcheng');
        (new StartSync())->run('newbd');
    }
}
