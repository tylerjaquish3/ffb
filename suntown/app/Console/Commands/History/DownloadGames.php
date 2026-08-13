<?php

namespace App\Console\Commands\History;

use App\Jobs\History\DownloadGamesCsv;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('importGames')]
#[Description('Parse a pasted Pro Football Reference games table (storage/app/private/games/raw.txt) into storage/app/private/games/YYYY.csv')]
class DownloadGames extends Command
{
    public function handle(): void
    {
        DownloadGamesCsv::dispatchSync();
    }
}
