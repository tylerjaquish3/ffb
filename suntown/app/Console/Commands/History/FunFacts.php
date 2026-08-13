<?php

namespace App\Console\Commands\History;

use App\Jobs\History\UpdateFunFacts as UpdateFunFactsJob;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('funFacts')]
#[Description('Run queries to update any fun facts in the ffb database')]
class FunFacts extends Command
{
    public function handle(): void
    {
        UpdateFunFactsJob::dispatchSync();
    }
}
