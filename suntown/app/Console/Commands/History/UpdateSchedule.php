<?php

namespace App\Console\Commands\History;

use App\Jobs\History\UpdateSchedule as UpdateScheduleJob;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('updateSchedule')]
#[Description('Updates the ffb database schedule table')]
class UpdateSchedule extends Command
{
    public function handle(): void
    {
        UpdateScheduleJob::dispatchSync();
    }
}
