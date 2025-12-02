<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\RunQuery as RunQueryJob;

class RunQuery extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'runQuery';

    /**
     * The console command description.
     */
    protected $description = 'Run a temporary query to find discrepancies between rosters and regular season matchups';

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
        dispatch_sync(new RunQueryJob);
    }
}
