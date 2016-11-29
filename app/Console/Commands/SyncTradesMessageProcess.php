<?php

namespace App\Console\Commands;

use App\Http\Controllers\Util\SyncOrdersFromApi\Taobao;
use Illuminate\Console\Command;

class SyncTradesMessageProcess extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'SyncTrades:messageProcess {content}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'require from javasdk.json';

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
        (new Taobao())->syncOrderUpdate($this->argument('content'));
    }
}
