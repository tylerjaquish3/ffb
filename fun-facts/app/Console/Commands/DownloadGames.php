<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\DownloadGamesCsv;

class DownloadGames extends Command
{
    protected $signature = 'importGames';

    protected $description = 'Parse a pasted Pro Football Reference games table (storage/app/games/raw.txt) into storage/app/games/YYYY.csv';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): void
    {
        dispatch_sync(new DownloadGamesCsv);
    }
}
