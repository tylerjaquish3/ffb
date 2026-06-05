<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\DownloadGamesCsv;

class DownloadGames extends Command
{
    protected $signature = 'downloadGames';

    protected $description = 'Download the current season\'s NFL schedule from Pro Football Reference and save to storage/app/games/YYYY.csv';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): void
    {
        dispatch_sync(new DownloadGamesCsv);
    }
}
