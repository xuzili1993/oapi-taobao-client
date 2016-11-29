<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\SyncTradesFromApi;
use App\Console\Commands\SyncTradesMessageProcess;
use App\Console\Commands\SyncTradesLogsDelete;
use App\Console\Commands\SyncTradesDelete;
class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        SyncTradesFromApi::class,               //同步淘宝订单的命令
        SyncTradesMessageProcess::class,
        SyncTradesLogsDelete::class,
        SyncTradesDelete::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('SyncTrades:newOrder')
                ->everyMinute();
        $schedule->command('SyncTrades:logsDelete')
                ->weekly('00:00');
        $schedule->command('SyncTrades:TradesDelete')
                ->quarterly('00:00');           //一季度运行一次
//        把输出的文件发送到以下路径的文件中
//                  ->sendOutputTo($filePath)
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }

}
