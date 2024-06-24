<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\FetchRosterTeams as FetchRosterTeamsJob;

class RosterTeams extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'rosterTeams';

    /**
     * The console command description.
     */
    protected $description = 'Updates rosters table with players missing team';

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
        dispatch_sync(new FetchRosterTeamsJob);
    }
}
