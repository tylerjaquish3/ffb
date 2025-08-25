<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\TransferDraftResults;

class TransferDraft extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'transferDraft';

    /**
     * The console command description.
     */
    protected $description = 'Gets draft results from draft db and inserts into ffb db';

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
        dispatch_sync(new TransferDraftResults);
    }
}
