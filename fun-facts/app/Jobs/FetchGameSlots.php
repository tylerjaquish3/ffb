<?php

namespace App\Jobs;

use App\Models\Roster;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class FetchGameSlots implements ShouldQueue
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

            // Get all rosters rows where game_time is not null
            $roster = Roster::whereNotNull('game_time')->get();
            foreach ($roster as $row) {
                $gameTime = Carbon::make($row->game_time);

                if (!$gameTime) {
                    continue;
                }

                // Determine if the game time was a thursday
                if ($gameTime->dayOfWeek == 4) {
                    $row->game_slot = 1;
                } elseif ($gameTime->dayOfWeek == 5) { // friday
                    $row->game_slot = 2;
                } elseif ($gameTime->dayOfWeek == 1) { // monday
                    $row->game_slot = 6;
                } elseif ($gameTime->dayOfWeek == 0) { // sunday
                    // Check if time was 10am EST
                    if ($gameTime->hour == 13) { // sunday morning
                        $row->game_slot = 3;
                    } elseif ($gameTime->hour == 16) { // sunday afternoon
                        $row->game_slot = 4;
                    } elseif ($gameTime->hour == 20) { // Sunday night
                        $row->game_slot = 5;
                    }
                } elseif ($gameTime->dayOfWeek == 2) {
                    $row->game_slot = 6;
                }

                $row->save();
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