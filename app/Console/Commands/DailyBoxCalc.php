<?php

namespace App\Console\Commands;

use App\Http\Controllers\HomeController;
use Illuminate\Console\Command;

class DailyBoxCalc extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'daily:task';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run daily tasks at 00:05 AM and 11:00 AM';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $controller = new HomeController();
        $controller->calcOpenedBoxForToday();
        $this->info('Custom task executed successfully!');
    }
}
