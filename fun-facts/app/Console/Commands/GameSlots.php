<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\FetchGameSlots as FetchGameSlotsJob;

class GameSlots extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'gameSlots';

    /**
     * The console command description.
     */
    protected $description = 'Inserts game slots into the rosters table based on game time';

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
        dispatch_sync(new FetchGameSlotsJob);
    }
}
