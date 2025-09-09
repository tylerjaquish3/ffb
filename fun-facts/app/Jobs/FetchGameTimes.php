<?php

namespace App\Jobs;

use App\Models\NflTeam;
use App\Models\PlayoffRoster;
use App\Models\Roster;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
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
        $thisYear = date('Y');

        try {

            // Use pro-football-reference website to get games
            // https://www.pro-football-reference.com/years/2024/games.htm

            // Get all files in storage/app/games directory
            $files = Storage::files('games');

            foreach ($files as $file) {

                // Get the file name without .csv
                $year = pathinfo($file, PATHINFO_FILENAME);

                // Skip all files except this year
                if ($year != $thisYear) {
                    continue;
                }
                
                $data = $this->getDataArray($file);

                $players = Roster::where('year', $year);
                $playoffPlayers = PlayoffRoster::where('year', $year);
                
                // only needs to be run once to get all the older years
                // $this->updatePlayoffRosterTeams($players, $playoffPlayers);

                $this->saveGameTimes($players, $data);
                $this->saveGameTimes($playoffPlayers, $data);
            }

            // Now update game slots
            // Get all rosters rows where game_time is not null
            $roster = Roster::whereNotNull('game_time')->get();
            $this->saveGameSlots($roster);
            $playoffRoster = PlayoffRoster::whereNotNull('game_time')->get();
            $this->saveGameSlots($playoffRoster);

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

    private function saveGameTimes(Builder $rosterPlayers, array $data)
    {
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

    // Save game slots for each roster row
    private function saveGameSlots(Collection $roster)
    {
        foreach ($roster as $row) {
            $gameTime = Carbon::make($row->game_time);

            if (!$gameTime) {
                continue;
            }

            // Determine if the game time was a thursday
            if ($gameTime->dayOfWeek == 4) {
                $row->game_slot = 1; // thursday
            } elseif ($gameTime->dayOfWeek == 5) { // friday
                $row->game_slot = 2;
            } elseif ($gameTime->dayOfWeek == 1) { // monday
                $row->game_slot = 6;
            } elseif ($gameTime->dayOfWeek == 0) { // sunday
                // Check if time was 10am EST
                if ($gameTime->hour <= 13) { // sunday morning
                    $row->game_slot = 3;
                } elseif ($gameTime->hour == 16) { // sunday afternoon
                    $row->game_slot = 4;
                } elseif ($gameTime->hour == 20) { // Sunday night
                    $row->game_slot = 5;
                }
            } elseif ($gameTime->dayOfWeek == 2) {
                $row->game_slot = 7; // Tuesday
            } else {
                $row->game_slot = 8;
            }

            $row->save();
        }
    }

    // // This will only need to be run once to get all the older years
    // private function updatePlayoffRosterTeams(Builder $roster, Builder $playoffRoster)
    // {
    //     $playoffPlayers = $playoffRoster->get();
    //     // Update the team and position foreach player, looking at the oldest rows of data from $roster 
    //     foreach ($playoffPlayers as $row) {
    //         $player = $roster->clone()->where('player', $row->player)
    //             ->orderBy('week', 'desc')->first();

    //         if ($player) {
    //             $row->update([
    //                 'position' => $player->position,
    //                 'team' => $player->team
    //             ]);
    //         } else {
    //             // dd($row, $player);
    //         }
    //     }
    // }

    // private function insertPlayoffRosters()
    // {
    //     $data = $this->getDataArray('public/master.csv');
    //     foreach ($data as $row) {
    //         $rosterSpot = $row[0];
    //         $player     = $this->getPlayerName($row[1]);

    //         $points     = $this->getPoints($row);
    //         $managerKey = $this->getManager($row);
    //         if (!$managerKey) {
    //             continue;
    //         }
    //         $manager    = $row[$managerKey];
    //         $round      = $row[$managerKey - 1];
    //         $week       = $row[$managerKey - 2];
    //         $year       = $row[$managerKey - 3];

    //         PlayoffRoster::firstOrCreate([
    //             'roster_spot' => $rosterSpot,
    //             'player'      => $player,
    //             'points'      => $points,
    //             'manager'     => $manager,
    //             'round'       => $round,
    //             'week'        => $week,
    //             'year'        => $year
    //         ]);
    //     }
    // }

    // // Strip off the status from the player's name (Q, IR, IR-R) case sensitive
    // protected function getPlayerName(string $name)
    // {
    //     $player = rtrim($name, 'Q');
    //     $player = rtrim($player, 'IR');
    //     $player = rtrim($player, 'IR-R');
    //     $player = rtrim($player, 'NA');

    //     return $player;
    // }

    // private function getPoints(array $row)
    // {
    //     // Find the first item in the $row that has a % in it and get the key
    //     foreach ($row as $key => $item) {
    //         if (strpos($item, '%') !== false) {
    //             break;
    //         }
    //     }
    //     $points = $row[$key - 2];

    //     // if the points are a string, convert to float
    //     if (is_string($points)) {
    //         // dd($row);
    //         $points = (float) $points;
    //     }

    //     return round($points, 2);
    // }

    // private function getManager(array $row)
    // {
    //     // manager will be last item in the row that has something in it, return the key for that item
    //     $managerKey = 0;
    //     foreach ($row as $key => $item) {
    //         if ($item) {
    //             $managerKey = $key;
    //         }
    //     }

    //     return $managerKey;
    // }

}