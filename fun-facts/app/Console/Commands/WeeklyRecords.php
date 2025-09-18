<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\UpdateWeeklyRecords;

class WeeklyRecords extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'weekly:records {year?} {week?} {--test : Run in test mode for debugging} {--sync : Run synchronously instead of queueing}';
    

    /**
     * The console command description.
     */
    protected $description = 'Update weekly records and log them';

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
    public function handle()
    {
        $year = $this->argument('year') ?: null;
        $week = $this->argument('week') ?: null;
        $testMode = $this->option('test');
        $syncMode = $this->option('sync');
        
        if ($testMode) {
            $this->info("Running weekly records in TEST MODE for debugging");
            $yearText = $year ? "year: $year" : "all years (full history)";
            $this->info("Using $yearText" . ($week ? ", week: $week" : " (no specific week)"));
            
            // Run the job synchronously for immediate feedback
            (new UpdateWeeklyRecords($year, $week, true))->handle();
            
            $this->info('Test completed.');
        } else {
            $yearText = $year ? "year: $year" : "all years (full history)";
            $this->info("Updating weekly records for $yearText" . ($week ? ", week: $week" : ""));
            
            if ($syncMode) {
                $this->info("Running synchronously (this may take a while)...");
                // Run the job synchronously for immediate execution
                $result = (new UpdateWeeklyRecords($year, $week))->handle();
                $status = ($result === 0) ? 'completed successfully' : 'failed';
                $this->info("Weekly records update $status.");
            } else {
                // Dispatch the job to update weekly records
                dispatch(new UpdateWeeklyRecords($year, $week));
                $this->info('Weekly records update job dispatched successfully.');
            }
        }
        
        return 0;
    }
}
