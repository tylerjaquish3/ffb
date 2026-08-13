<?php

namespace App\Console\Commands\History;

use App\Jobs\History\TransferDraftResults;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('transferDraft')]
#[Description('Gets draft results from the ../draft project and inserts them into the ffb database')]
class TransferDraft extends Command
{
    public function handle(): void
    {
        TransferDraftResults::dispatchSync();
    }
}
