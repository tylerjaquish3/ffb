<?php

namespace Tests\Browser\PlayoffRosters;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use App\Models\Manager;
use App\Models\PlayoffMatchup;
use App\Models\PlayoffRoster;
use App\Models\RegularSeasonMatchup;
use App\Models\SeasonManager;
use App\Traits\TestHelper;

class RosterTest extends DuskTestCase
{
    use TestHelper;
    protected $year = 2011;
    protected $manager;

    // php artisan dusk --filter=testPlayoffRoster

    /**
     * Go to the team page for each team and scrape the data
     */
    public function testPlayoffRoster()
    {
        $weeks = $this->getWeeks();
        $playoffTeams = $this->getPlayoffTeams();
        $seasons = config('services.seasons');
        $leagueId = $seasons[$this->year]['league_id'];

        $path = storage_path('app/public/');
        $fileName = 'playoff_rosters_'.$this->year.'.csv';

        $csvData = [];
        $csv = fopen($path.$fileName, 'w');

        $this->browse(function (Browser $browser) use ($leagueId, $weeks, $playoffTeams, $csv) {
            // For each team in league
            foreach ($playoffTeams as $t) {

                $this->manager = $this->getManagerName($t);

                foreach ($weeks as $week) {

                    $round = $this->getRound($week);
                    // Check if this manager has a matchup for this round of the playoffs
                    $stillIn = $this->checkStillIn($round, $t);
                    
                    if (!$stillIn) {
                        continue;
                    }
                    
                    echo 'Round: '.$round.' Week: '.$week.' Manager: '.$this->manager.' Team: '.$t.PHP_EOL;
                    
                    try {

                        $browser->visit('https://football.fantasysports.yahoo.com/'.$this->year.'/f1/'.$leagueId.'/'.$t.'/team?&week='.$week);

                        if ($browser->element('#login-username')) {
                            $browser
                                ->type('#login-username', env('YAHOO_USER'))
                                ->press('Next')
                                ->pause(4000)
                                ->waitForText('Enter password')
                                ->type('#login-passwd', env('YAHOO_PW'))
                                ->press('Next');
                        }

                        // $browser->scrollIntoView('#statTable0');
                        for ($x = 1; $x < 18; $x++) {
                            if ($browser->element('#statTable0 tbody tr:nth-child('.$x.')')) {
                                $browser->with('#statTable0 tbody', function ($tr) use ($week, $round, $x, $csv) {
                                    $row = $tr->text('tr:nth-child('.$x.')');
                                    $stuff = preg_split('/\r\n|\r|\n/', $row);

                                    if (count($stuff) > 1) {
                                        // add the year, week, round, manager name to the array
                                        $stuff[] = $this->year;
                                        $stuff[] = $week;
                                        $stuff[] = $round;
                                        $stuff[] = $this->manager;

                                        fputcsv($csv, $stuff);
                                        // $this->insertRow($stuff, $week);
                                    }
                                });
                            }
                        }

                        // Kicker table
                        // $browser->scrollIntoView('#statTable1');
                        for ($x = 1; $x < 4; $x++) {
                            if ($browser->element('#statTable1 tbody tr:nth-child('.$x.')')) {
                                $browser->with('#statTable1 tbody', function ($tr) use ($week, $round, $x, $csv) {
                                    $row = $tr->text('tr:nth-child('.$x.')');
                                    $stuff = preg_split('/\r\n|\r|\n/', $row);
                                    if (count($stuff) > 1) {
                                        // add the year, week, round, manager name to the array
                                        $stuff[] = $this->year;
                                        $stuff[] = $week;
                                        $stuff[] = $round;
                                        $stuff[] = $this->manager;
                                        fputcsv($csv, $stuff);
                                        // $this->insertRow($stuff, $week);
                                    }
                                });
                            }
                        }

                        // Defense table
                        // $browser->scrollIntoView('#statTable2');
                        for ($x = 1; $x < 8; $x++) {
                            if ($browser->element('#statTable2 tbody tr:nth-child('.$x.')')) {
                                $browser->with('#statTable2 tbody', function ($tr) use ($week, $round, $x, $csv) {
                                    $row = $tr->text('tr:nth-child('.$x.')');
                                    $stuff = preg_split('/\r\n|\r|\n/', $row);
                                    if (count($stuff) > 1) {
                                        // add the year, week, round, manager name to the array
                                        $stuff[] = $this->year;
                                        $stuff[] = $week;
                                        $stuff[] = $round;
                                        $stuff[] = $this->manager;
                                        fputcsv($csv, $stuff);
                                        // $this->insertRow($stuff, $week);
                                    }
                                });
                            }
                        }

                    } catch (\Exception $e) {
                        echo $e->getMessage();
                    }
                }
            }
        });

        fclose($csv);
    }

    // Strip off the status from the player's name (Q, IR, IR-R) case sensitive
    protected function getPlayerName(string $name)
    {
        $player = rtrim($name, 'Q');
        $player = rtrim($player, 'IR');
        $player = rtrim($player, 'IR-R');
        $player = rtrim($player, 'NA');

        return $player;
    }

    // Use the week and year to determine the round of the playoffs
    // Return 'Quarterfinal, 'Semifinal', or 'Final'
    protected function getRound($week)
    {
        $max = RegularSeasonMatchup::where('year', $this->year)
            ->max('week_number');
        
        if ($week == $max + 1) {
            return 'Quarterfinal';
        } elseif ($week == $max + 2) {
            return 'Semifinal';
        } elseif ($week == $max + 3) {
            return 'Final';
        }
    }

    // Find the weeks for the playoffs based on the year
    // Use the regular_season_matchups table, get the highest number in the weeks column
    // Then return the 3 weeks after that max number
    protected function getWeeks()
    {
        $max = RegularSeasonMatchup::where('year', $this->year)
            ->max('week_number');

        $weeks = [];
        for ($i = $max + 1; $i < $max + 4; $i++) {
            $weeks[] = $i;
        }

        return $weeks;
    }

    // Find the teams that made the playoffs for the given year
    // Use the playoff_matchups table to find all unique managers
    // The managers are the manager1_id and manager2_id
    protected function getPlayoffTeams()
    {
        $ids = PlayoffMatchup::where('year', $this->year)
            ->select('manager1_id')
            ->distinct()
            ->get()->pluck('manager1_id')->toArray();
        $ids2 = PlayoffMatchup::where('year', $this->year)
            ->select('manager2_id')
            ->distinct()
            ->get()->pluck('manager2_id')->toArray();

        $ids = array_merge($ids, $ids2);
        $ids = array_unique($ids, SORT_REGULAR);

        $yahooIds = SeasonManager::where('year', $this->year)
            ->whereIn('manager_id', $ids)
            ->get()->pluck('yahoo_id')->toArray();

        return $yahooIds;
    }

    // Use the PlayoffMatchup table to see if the manager is still in the playoffs
    protected function checkStillIn($round, $yahooId)
    {
        $seasonManager = SeasonManager::where('yahoo_id', $yahooId)
            ->where('year', $this->year)
            ->first();
        $team = $seasonManager->manager_id;

        $matchup = PlayoffMatchup::where('year', $this->year)
            ->where('round', $round)
            ->where(function ($query) use ($team) {
                $query->where('manager1_id', $team)
                    ->orWhere('manager2_id', $team);
            })
            ->first();
        
        if ($matchup) {
            return true;
        }

        return false;
    }

    // protected function getPlayerName(string $input) 
    // {
    //     $teams = [
    //         'Ari','Atl','Bal','Cin','Cle','Dal','SF','Sea','LAR','Oak','LV','NYG','Phi','Was',
    //         'Pit','Mia','NYJ','NE','Buf','Ten','Jax','Jac','KC','SD','LAC','Ind','Hou','Chi',
    //         'Min','Det','GB','Den','NO','Car','TB'
    //     ];

    //     $teams = implode('', $teams);
    //     $newName = rtrim($input, $teams);
    //     $newName = rtrim($newName);

    //     return $newName;
    // }
    
    protected function getPosition(string $input) 
    {
        $positions = ['QB','RB','WR','TE','K','DEF'];

        foreach ($positions as $position) {
            if (strpos($input, $position) !== false) {
                return strtoupper($position);
            }
        }
    }
   
    protected function getTeam(string $input) 
    {
        $teams = [
            'Ari','Atl','Bal','Cin','Cle','Dal','SF','Sea','LAR','Oak','LV','NYG','Phi','Was',
            'Pit','Mia','NYJ','NE','Buf','Ten','Jax','Jac','KC','SD','LAC','Ind','Hou','Chi',
            'Min','Det','GB','Den','NO','Car','TB'
        ];

        // Search $input for team
        // $substring = substr($input, -4);
        foreach ($teams as $team) {
            if (strpos($input, $team) !== false) {
                return strtoupper($team);
            }
        }
    }

    /**
     * Insert a row for position player
     */
    protected function insertRow(array $row, int $week)
    {
        PlayoffRoster::updateOrCreate([
            'year' => $this->year,
            'week' => $week,
            'round' => $this->getRound($week),
            'player' => $this->getPlayerName($row[1]),
            'team' => $this->getTeam($row[2])
        ],[
            'manager' => $this->manager,
            'position' => $this->getPosition($row[2]),
            'roster_spot' => $row[0] == 'Q/W' ? 'Q/W/R/T' : $row[0],
            'points' => $this->getPoints($row)
        ]);
    }

    /**
     * Get the manager name from the season_managers table
     */
    protected function getManagerName(int $yahooId)
    {
        $seasonManager = SeasonManager::where('yahoo_id', $yahooId)
            ->where('year', $this->year)
            ->first();

        $manager = Manager::find($seasonManager->manager_id);

        return $manager->name;
    }

}
