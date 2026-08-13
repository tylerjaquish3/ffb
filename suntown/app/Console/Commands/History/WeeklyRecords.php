<?php

namespace App\Console\Commands\History;

use App\Jobs\History\UpdateWeeklyRecords;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('weekly:records {year?} {week?} {--fun-fact-id= : Process only this specific fun fact ID} {--test : Run in test mode for debugging} {--sync : Run synchronously instead of queueing}')]
#[Description('Update weekly records in the ffb database and log them')]
class WeeklyRecords extends Command
{
    public function handle()
    {
        $year = $this->argument('year') ?: null;
        $week = $this->argument('week') ?: null;
        $funFactId = $this->option('fun-fact-id') ?: null;
        $testMode = $this->option('test');
        $syncMode = $this->option('sync');

        if ($testMode) {
            $this->info('Running weekly records in TEST MODE for debugging');
            $yearText = $year ? "year: $year" : 'all years (full history)';
            $funFactText = $funFactId ? ", fun fact ID: $funFactId" : '';
            $this->info("Using $yearText".($week ? ", week: $week" : '').$funFactText);

            (new UpdateWeeklyRecords($year, $week, true, $funFactId))->handle();

            $this->info('Test completed.');
        } else {
            $yearText = $year ? "year: $year" : 'all years (full history)';
            $funFactText = $funFactId ? ", fun fact ID: $funFactId" : '';
            $this->info("Updating weekly records for $yearText".($week ? ", week: $week" : '').$funFactText);

            if ($syncMode) {
                $this->info('Running synchronously (this may take a while)...');
                $result = (new UpdateWeeklyRecords($year, $week, false, $funFactId))->handle();
                $status = ($result === 0) ? 'completed successfully' : 'failed';
                $this->info("Weekly records update $status.");
            } else {
                dispatch(new UpdateWeeklyRecords($year, $week, false, $funFactId));
                $this->info('Weekly records update job dispatched successfully.');
            }
        }

        return 0;
    }
}
