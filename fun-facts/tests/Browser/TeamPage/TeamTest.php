<?php

namespace Tests\Browser\TeamPage;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use App\Models\Roster;
use App\Models\Stat;
use App\Models\Manager;
use App\Models\SeasonManager;
use App\Traits\TestHelper;
use Illuminate\Support\Facades\DB;

class TeamTest extends DuskTestCase
{
    use TestHelper;
    protected $year = 2024;
    protected $manager;

    // php artisan dusk --filter=testTeam

    /**
     * Go to the team page for each team and scrape the data
     */
    public function testTeam()
    {
        $weeks = config('services.weeks');

        $this->browse(function (Browser $browser) use ($weeks) {
            // For each team in league
            for ($t = 1; $t < 11; $t++) {

                $this->manager = $this->getManagerName($t);

                foreach ($weeks as $week) {
                    try {
                        $leagueId = config('services.yahoo_league_id');
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

                        // $browser->waitForText('Suntown Fantasy Football')
                        $browser->scrollIntoView('#statTable0');

                        for ($x = 1; $x < 18; $x++) {
                            if ($browser->element('#statTable0 tbody tr:nth-child('.$x.')')) {
                                $browser->with('#statTable0 tbody', function ($tr) use ($week, $x) {
                                    $row = $tr->text('tr:nth-child('.$x.')');
                                    $stuff = preg_split('/\r\n|\r|\n/', $row);

                                    if (count($stuff) > 1) {
                                        $this->insertRow($stuff, $week);
                                    }
                                });
                            }
                        }

                        // Kicker table
                        $browser->scrollIntoView('#statTable1');
                        for ($x = 1; $x < 3; $x++) {
                            if ($browser->element('#statTable1 tbody tr:nth-child('.$x.')')) {
                                $browser->with('#statTable1 tbody', function ($tr) use ($week, $x) {
                                    $row = $tr->text('tr:nth-child('.$x.')');
                                    $stuff = preg_split('/\r\n|\r|\n/', $row);
                                    if (count($stuff) > 1) {
                                        $this->insertKickerRow($stuff, $week);
                                    }
                                });
                            }
                        }

                        // Defense table
                        $browser->scrollIntoView('#statTable2');
                        for ($x = 1; $x < 4; $x++) {
                            if ($browser->element('#statTable2 tbody tr:nth-child('.$x.')')) {
                                $browser->with('#statTable2 tbody', function ($tr) use ($week, $x) {
                                    $row = $tr->text('tr:nth-child('.$x.')');
                                    $stuff = preg_split('/\r\n|\r|\n/', $row);
                                    if (count($stuff) > 1) {
                                        $this->insertDefenseRow($stuff, $week);
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
    }

    /**
     * Insert a row for rosters & stats for position player
     */
    protected function insertRow(array $row, int $week)
    {
        // if (isset($row[1]) && $row[1] == '(Empty)') {
        //     $playerArray = ['', ''];
        // } else {
        //     $playerArray = explode(' - ', $row[1]);
        // }
        // if ($row['0'] == 'Q/W') {
        //     $playerArray = explode(' - ', $row[2]);
        // }
        $x = abs(25 - count($row));
        // var_dump($row);
        // var_dump($x);
        // if (count($row) == 25) {
        //     $x = 0;
        // }
        // if ($row[2] == 'Bye' || $row[3] == 'Bye') {
        //     $x = 5;
        // }

        $roster = Roster::updateOrCreate([
            'year' => $this->year,
            'week' => $week,
            'player' => $row[1],
            'team' => $this->getTeam($row[2])
        ],[
            'manager' => $this->manager,
            'position' => $this->getPosition($row[2]),
            'roster_spot' => $row[0] == 'Q/W' ? 'Q/W/R/T' : $row[0],
            'projected' => $row[8+$x] == "-" ? 0 : $row[8+$x],
            'points' => $row[7+$x] == "-" ? 0 : $row[7+$x]
        ]);

        if (count($row) > 20 && $row[2] != 'Bye') {
            Stat::updateOrCreate(['roster_id' => $roster->id],
            [
                'pass_yds' => $row[11+$x] == "-" ? 0 : $row[11+$x],
                'pass_tds' => $row[12+$x] == "-" ? 0 : $row[12+$x],
                'ints' => $row[13+$x] == "-" ? 0 : $row[13+$x],
                'rush_yds' => $row[15+$x] == "-" ? 0 : $row[15+$x],
                'rush_tds' => $row[16+$x] == "-" ? 0 : $row[16+$x],
                'receptions' => $row[17+$x] == "-" ? 0 : $row[17+$x],
                'rec_yds' => !isset($row[18+$x]) || $row[18+$x] == "-" ? 0 : $row[18+$x],
                'rec_tds' => !isset($row[19+$x]) || $row[19+$x] == "-" ? 0 : $row[19+$x],
                'fumbles' => !isset($row[24+$x]) || $row[24+$x] == "-" ? 0 : $row[24+$x]
            ]);
        }
    }

    protected function getPlayerName(string $input) 
    {
        $teams = [
            'Ari','Atl','Bal','Cin','Cle','Dal','SF','Sea','LAR','Oak','LV','NYG','Phi','Was',
            'Pit','Mia','NYJ','NE','Buf','Ten','Jax','Jac','KC','SD','LAC','Ind','Hou','Chi',
            'Min','Det','GB','Den','NO','Car','TB'
        ];

        $teams = implode('', $teams);
        $newName = rtrim($input, $teams);
        $newName = rtrim($newName);

        return $newName;
    }
    
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
     * Undocumented function
     */
    protected function insertKickerRow(array $row, int $week)
    {
        // $playerArray = explode(' - ', $row[1]);
        
        // $projectedCol = 7;
        // $pointsCol = 6;
        // $fgYardsCol = 13;
        // $patCol = 12;
        
        // if ($this->manager == 'Tyler') {
        //     $projectedCol = 5;
        //     $pointsCol = 4;
        //     $fgYardsCol = 9;
        //     $patCol = 8;
        // }
        
        // if ($row[2] == 'Bye') {
        //     $projected = $points = 0;
        // } else {
        //     $projected = $row[$projectedCol] == "-" ? 0 : $row[$projectedCol];
        //     $points = $row[$pointsCol] == "-" ? 0 : $row[$pointsCol];
        // }

        $x = abs(16 - count($row));

        $roster = Roster::updateOrCreate([
            'year' => $this->year,
            'week' => $week,
            'player' => $row[1],
            'team' => $this->getTeam($row[2])
        ], [
            'manager' => $this->manager,
            'position' => 'K',
            'roster_spot' => $row[0],
            'projected' => $row[8+$x],
            'points' => $row[7+$x]
        ]);

        if ($row[2] != 'Bye') {
            Stat::updateOrCreate(['roster_id' => $roster->id],
            [
                'fg_made' => null,
                'fg_yards' => (int)$row[15+$x],
                'pat_made' => $row[14+$x] == "-" ? 0 : $row[14+$x]
            ]);
        }
    }

    /**
     * Insert a defense for rosters & stats tables
     */
    protected function insertDefenseRow(array $row, int $week)
    {
        // $playerArray = explode(' - ', $row[1]);
        $x = abs(21 - count($row));
        // var_dump($row);
        // var_dump($x);
        // if ($row[2] == 'Bye') {
        //     $x = 4;
        // }

        $roster = Roster::updateOrCreate([
            'year' => $this->year,
            'week' => $week,
            'player' => $row[1],
            'team' => $this->getTeam($row[2])
        ], [
            'manager' => $this->manager,
            'position' => 'DEF',
            'roster_spot' => $row[0],
            'projected' => $row[8+$x] == "-" ? 0 : $row[8+$x],
            'points' => $row[7+$x] == "-" ? 0 : $row[7+$x]
        ]);

        if ($row[2] != 'Bye') {
            Stat::updateOrCreate(['roster_id' => $roster->id],
            [
                'def_sacks' => (int)$row[12+$x],
                'def_int' => (int)$row[14+$x],
                'def_fum' => (int)$row[15+$x]
            ]);
        }
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
