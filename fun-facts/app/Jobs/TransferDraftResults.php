<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class TransferDraftResults implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $success = true;
        $message = "";

        try {
            $currentYear = date('Y');
            echo "Transferring draft results for year: {$currentYear}" . PHP_EOL;

            // Manager ID mapping from draft database to FFB database
            $managerMapping = [
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

            // Path to the draft database
            $draftDbPath = '/Users/tyler.jaquish/sites/draft/database/database.sqlite';
            
            // Connect to draft database
            $draftDb = new \PDO("sqlite:{$draftDbPath}");
            $draftDb->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            // Query to get draft selections with all joined data
            $query = "
                SELECT 
                    ds.year,
                    ds.pick_number,
                    ds.manager_id,
                    p.name as player_name,
                    pos.name as position_name,
                    lm.name as manager_name
                FROM draft_selections ds 
                INNER JOIN players p ON ds.player_id = p.id 
                INNER JOIN league_managers lm ON ds.manager_id = lm.id 
                INNER JOIN positions pos ON p.position_id = pos.id 
                WHERE ds.year = :year 
                ORDER BY ds.pick_number
            ";

            $stmt = $draftDb->prepare($query);
            $stmt->bindParam(':year', $currentYear, \PDO::PARAM_INT);
            $stmt->execute();
            
            $draftResults = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            if (empty($draftResults)) {
                $message = "No draft results found for year {$currentYear}";
                echo $message . PHP_EOL;
                return;
            }

            echo "Found " . count($draftResults) . " draft picks to transfer" . PHP_EOL;

            // Check if data already exists for this year
            $existingCount = \App\Models\Draft::where('year', $currentYear)->count();
            if ($existingCount > 0) {
                echo "Draft data for {$currentYear} already exists in FFB database. Skipping transfer." . PHP_EOL;
                return;
            }

            // Insert each draft pick into the FFB database
            $insertedCount = 0;
            foreach ($draftResults as $pick) {
                $round = ceil($pick['pick_number'] / 10); // Assuming 10 teams
                $roundPick = (($pick['pick_number'] - 1) % 10) + 1;

                // Map manager ID from draft database to FFB database
                $ffbManagerId = $managerMapping[$pick['manager_id']] ?? $pick['manager_id'];
                
                echo "Pick {$pick['pick_number']}: {$pick['player_name']} ({$pick['position_name']}) - {$pick['manager_name']} (Draft ID: {$pick['manager_id']} -> FFB ID: {$ffbManagerId})" . PHP_EOL;

                $draft = new \App\Models\Draft();
                $draft->year = $pick['year'];
                $draft->round = $round;
                $draft->round_pick = $roundPick;
                $draft->overall_pick = $pick['pick_number'];
                $draft->manager_id = $ffbManagerId;
                $draft->position = $pick['position_name'];
                $draft->player = $pick['player_name'];
                $draft->save();

                $insertedCount++;
                
                if ($insertedCount % 20 == 0) {
                    echo "Inserted {$insertedCount} picks..." . PHP_EOL;
                }
            }

            $message = "Successfully transferred {$insertedCount} draft picks for {$currentYear}";
            echo $message . PHP_EOL;

        } catch (\Exception $e) {
            $success = false;
            $message = $e->getMessage();
            echo "Error: " . $message . PHP_EOL;
            dd($e);
        }

    }


}