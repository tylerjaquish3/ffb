<?php

namespace App\Console\Commands\History;

use App\Jobs\History\CalculateOptimalJob;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('calculateOptimal {year? : Specific year to calculate (omit for all)} {week? : Specific week to calculate (omit for all)}')]
#[Description('Calculate and store optimal lineup scores in the ffb database regular_season_matchups table')]
class CalculateOptimal extends Command
{
    public function handle(): void
    {
        $year = $this->argument('year') ? (int) $this->argument('year') : null;
        $week = $this->argument('week') ? (int) $this->argument('week') : null;

        $scope = $year ? "year=$year".($week ? " week=$week" : '') : 'all seasons';
        $this->info("Calculating optimal scores for $scope...");

        CalculateOptimalJob::dispatchSync($year, $week);

        $this->info('Done.');
    }
}
