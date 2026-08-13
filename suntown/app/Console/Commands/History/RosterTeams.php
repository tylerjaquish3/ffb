<?php

namespace App\Console\Commands\History;

use App\Jobs\History\FetchRosterTeams as FetchRosterTeamsJob;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('rosterTeams')]
#[Description('Updates the ffb database rosters table with players missing team')]
class RosterTeams extends Command
{
    public function handle(): void
    {
        FetchRosterTeamsJob::dispatchSync();
    }
}
