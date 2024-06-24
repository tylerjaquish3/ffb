<?php

namespace App\Jobs;

use App\Models\NflTeam;
use App\Models\Roster;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Storage;

class FetchGameTimes implements ShouldQueue
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

            // Get all files in storage/app/games directory
            $files = Storage::files('games');
            foreach ($files as $file) {

                // Get the file name without .csv
                $year = pathinfo($file, PATHINFO_FILENAME);

                $data = $this->getDataArray($file);

                $rosterPlayers = Roster::where('year', $year);
                
                // Lookup each row in rosters table where year matches
                foreach ($data as $row) {

                    // Skip the first row
                    if ($row[0] == 'Week') {
                        continue;
                    }

                    $week = $row[0];

                    $abbr1 = $this->lookupTeamAbbr($row[4]);
                    $abbr2 = $this->lookupTeamAbbr($row[6]);

                    // Filter $rosterPlayers by week and team1 or team2
                    $players = $rosterPlayers->clone()
                        ->where('week', $week)
                        ->where(function ($query) use ($abbr1, $abbr2) {
                            $query->where('team', $abbr1)
                                ->orWhere('team', $abbr2);
                        })
                        ->get();

                    // Make datetime from elements 2 and 3 from the row
                    $gametime = date('Y-m-d H:i:s', strtotime($row[2].' '.$row[3]));
     
                    // Update the game time for each player in the roster
                    foreach ($players as $player) {
                        $player->update(['game_time' => $gametime]);
                    }

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

    /**
     * Get data from file and return it in array
     */
    private function getDataArray(string $filepath)
    {
        $csvData = Storage::get($filepath);
        $lines = explode(PHP_EOL, $csvData);
        $array = [];
        foreach ($lines as $line) {
            $array[] = str_getcsv($line, ",", '"', '#');
        }

        return $array;
    }

    /**
     * Look up team name, accounting for some name changes
     */
    private function lookupTeamAbbr(string $teamName)
    {
        if ($teamName == 'Washington Football Team' || $teamName == 'Washington Redskins') {
            return 'WAS';
        }
        if ($teamName == 'St. Louis Rams') {
            return 'STL';
        }
        if ($teamName == 'San Diego Chargers') {
            return 'SD';
        }
        if ($teamName == 'Oakland Raiders') {
            return 'OAK';
        }
        $team = NflTeam::where('name', $teamName)->first();
        if ($team) {
            return $team->abbr;
        }

        return null;
    }

}