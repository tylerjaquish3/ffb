<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\FetchGameTimes as FetchGameTimesJob;

class GameTimes extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'gameTimes';

    /**
     * The console command description.
     */
    protected $description = 'Inserts game times into the rosters table';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        dispatch_sync(new FetchGameTimesJob);
    }
}
