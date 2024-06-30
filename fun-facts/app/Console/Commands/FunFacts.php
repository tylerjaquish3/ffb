<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\UpdateFunFacts as UpdateFunFactsJob;

class FunFacts extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'funFacts';

    /**
     * The console command description.
     */
    protected $description = 'Run queries to update any fun facts in the database';

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
        dispatch_sync(new UpdateFunFactsJob);
    }
}
