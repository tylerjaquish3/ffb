<?php

namespace App\Jobs;

use App\Models\Roster;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class FixRosterPositions implements ShouldQueue
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

            $legitPositions = ['QB', 'RB', 'WR', 'TE', 'K', 'DEF', 'D', 'DB', 'LB', 'DL'];
            $roster = Roster::all()->groupBy('player')->skip(1800)->take(100);

            $skip = ['Alex Smith', 'Brandon Marshall', 'Roy Williams', 'Cordarrelle Patterson', '(Empty)',
                'Terrelle Pryor Sr.', 'Matt Jones', 'Dexter McCluster'];

            foreach ($roster as $player => $rows) {

                if (in_array($player, $skip)) {
                    continue;
                }

                $playerPos = $lastIdentifier = null;
                foreach ($rows as $row) {

                    // Get year, team, position combo
                    $newIdentifier = $row->year.':'.$row->team.':'.$row->position;

                    // need to check for 2 players with the same name
                    if ($lastIdentifier != $newIdentifier) {

                    }

                    if (in_array($row->position, $legitPositions)) {
                        $playerPos = $row->position;
                        continue;
                    }

                    if ($playerPos) {
                        echo 'Updated '.$row->player.' '.$newIdentifier.' from '.$row->position.' to '.$playerPos.PHP_EOL;
                        $row->position = $playerPos;
                        // $row->save();
                    }

                    $lastIdentifier = $newIdentifier;
                }
            }

        } catch (\Exception $e) {
            $success = false;
            $message = $e->getMessage();
            dd($e);
        }

        if ($success) {
            echo 'Finished!'.PHP_EOL;
        }

        echo $message;
    }


}