<?php

namespace App\Console\Commands\History;

use App\Jobs\History\FetchGameTimes as FetchGameTimesJob;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('gameTimes')]
#[Description('Inserts game times into the ffb database rosters table')]
class GameTimes extends Command
{
    public function handle(): void
    {
        FetchGameTimesJob::dispatchSync();
    }
}
