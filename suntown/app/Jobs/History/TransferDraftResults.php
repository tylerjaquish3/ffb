<?php

namespace App\Jobs\History;

use App\Models\History\Draft;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class TransferDraftResults implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Manager ID mapping from the ../draft project's league_managers to the ffb project's managers
    private const MANAGER_MAPPING = [
        1 => 6,  // Andy: draft ID 1 -> FFB ID 6
        2 => 2,  // AJ: draft ID 2 -> FFB ID 2
        3 => 10, // Ben: draft ID 3 -> FFB ID 10
        4 => 4,  // Matt: draft ID 4 -> FFB ID 4
        5 => 3,  // Gavin: draft ID 5 -> FFB ID 3
        6 => 8,  // Justin: draft ID 6 -> FFB ID 8
        7 => 7,  // Everett: draft ID 7 -> FFB ID 7
        8 => 5,  // Cameron: draft ID 8 -> FFB ID 5
        9 => 9,  // Cole: draft ID 9 -> FFB ID 9
        10 => 1, // Tyler: draft ID 10 -> FFB ID 1
    ];

    public function handle(): void
    {
        $success = true;
        $message = '';

        try {
            $currentYear = date('Y');
            echo "Transferring draft results for year: {$currentYear}".PHP_EOL;

            $draftResults = DB::connection('draft_source')
                ->table('draft_selections AS ds')
                ->join('players AS p', 'ds.player_id', '=', 'p.id')
                ->join('league_managers AS lm', 'ds.manager_id', '=', 'lm.id')
                ->join('positions AS pos', 'p.position_id', '=', 'pos.id')
                ->where('ds.year', $currentYear)
                ->orderBy('ds.pick_number')
                ->select([
                    'ds.year',
                    'ds.pick_number',
                    'ds.manager_id',
                    'p.name as player_name',
                    'pos.name as position_name',
                    'lm.name as manager_name',
                ])
                ->get();

            if ($draftResults->isEmpty()) {
                $message = "No draft results found for year {$currentYear}";
                echo $message.PHP_EOL;

                return;
            }

            echo 'Found '.$draftResults->count().' draft picks to transfer'.PHP_EOL;

            $existingCount = Draft::where('year', $currentYear)->count();
            if ($existingCount > 0) {
                echo "Draft data for {$currentYear} already exists in FFB database. Skipping transfer.".PHP_EOL;

                return;
            }

            $insertedCount = 0;
            foreach ($draftResults as $pick) {
                $round = ceil($pick->pick_number / 10); // Assuming 10 teams
                $roundPick = (($pick->pick_number - 1) % 10) + 1;

                $ffbManagerId = self::MANAGER_MAPPING[$pick->manager_id] ?? $pick->manager_id;

                echo "Pick {$pick->pick_number}: {$pick->player_name} ({$pick->position_name}) - {$pick->manager_name} (Draft ID: {$pick->manager_id} -> FFB ID: {$ffbManagerId})".PHP_EOL;

                Draft::create([
                    'year' => $pick->year,
                    'round' => $round,
                    'round_pick' => $roundPick,
                    'overall_pick' => $pick->pick_number,
                    'manager_id' => $ffbManagerId,
                    'position' => $pick->position_name,
                    'player' => $pick->player_name,
                ]);

                $insertedCount++;

                if ($insertedCount % 20 === 0) {
                    echo "Inserted {$insertedCount} picks...".PHP_EOL;
                }
            }

            $message = "Successfully transferred {$insertedCount} draft picks for {$currentYear}";
            echo $message.PHP_EOL;
        } catch (\Exception $e) {
            $success = false;
            $message = $e->getMessage();
            echo 'Error: '.$message.PHP_EOL;
            throw $e;
        }
    }
}
