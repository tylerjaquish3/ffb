<?php

namespace App\Console\Commands;

use App\Jobs\CalculateOptimalJob;
use Illuminate\Console\Command;

class CalculateOptimal extends Command
{
    protected $signature = 'calculateOptimal {year? : Specific year to calculate (omit for all)} {week? : Specific week to calculate (omit for all)}';
    protected $description = 'Calculate and store optimal lineup scores in regular_season_matchups';

    public function handle()
    {
        $year = $this->argument('year') ? (int)$this->argument('year') : null;
        $week = $this->argument('week') ? (int)$this->argument('week') : null;

        $scope = $year ? "year=$year" . ($week ? " week=$week" : '') : 'all seasons';
        $this->info("Calculating optimal scores for $scope...");

        CalculateOptimalJob::dispatchSync($year, $week);

        $this->info('Done.');
    }
}
