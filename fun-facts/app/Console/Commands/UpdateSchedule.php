<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\UpdateSchedule as UpdateScheduleJob;

class UpdateSchedule extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'updateSchedule';

    /**
     * The console command description.
     */
    protected $description = 'Updates the schedule';

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
        dispatch_sync(new UpdateScheduleJob);
    }
}
