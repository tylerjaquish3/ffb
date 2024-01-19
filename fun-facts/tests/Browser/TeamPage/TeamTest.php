<?php

namespace Tests\Browser\TeamPage;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use App\Models\Roster;
use App\Models\Stat;
use App\Models\Manager;
use App\Traits\TestHelper;
use Illuminate\Support\Facades\DB;

class TeamTest extends DuskTestCase
{
    use TestHelper;
    protected $year = 2023;
    protected $manager;

    // php artisan dusk --filter=testTeam
    // Run this after everything else, then commit the sqlite db in the other repo (ffb)
    // php artisan dusk --filter=testUpdateSqlite

    /**
     * Undocumented function
     *
     * @group weekly
     * @return void
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
                        $browser->visit('https://football.fantasysports.yahoo.com/f1/'.$leagueId.'/'.$t.'/team?&week='.$week);

                        if ($browser->element('#login-username')) {
                            $browser
                                ->type('#login-username', env('YAHOO_USER'))
                                ->press('Next')
                                ->pause(4000)
                                ->waitForText('Enter password')
                                ->type('#login-passwd', env('YAHOO_PW'))
                                ->press('Next');
                        }

                        $browser->waitForText('Yahoo Fantasy')
                            ->scrollIntoView('#statTable0');

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
        if (isset($row[1]) && $row[1] == '(Empty)') {
            $playerArray = ['', ''];
        } else {
            $playerArray = explode(' - ', $row[1]);
        }
        if ($row['0'] == 'Q/W') {
            $playerArray = explode(' - ', $row[2]);
        }
        $x = abs(21 - count($row));
        // var_dump($row);
        // var_dump($x);
        if (count($row) == 20) {
            $x = 2;
        }
        if ($row[2] == 'Bye' || $row[3] == 'Bye') {
            $x = 5;
        }

        $roster = Roster::updateOrCreate([
            'year' => $this->year,
            'week' => $week,
            'player' => $this->getPlayerName($playerArray[0]),
            'team' => $this->getTeam($playerArray[0])
        ],[
            'manager' => $this->manager,
            'position' => $playerArray[1],
            'roster_spot' => $row[0] == 'Q/W' ? 'Q/W/R/T' : $row[0],
            'projected' => $row[5+$x] == "-" ? 0 : $row[5+$x],
            'points' => $row[4+$x] == "-" ? 0 : $row[4+$x]
        ]);

        if (count($row) > 20 && $row[2] != 'Bye') {
            Stat::updateOrCreate(['roster_id' => $roster->id],
            [
                'pass_yds' => $row[7+$x] == "-" ? 0 : $row[7+$x],
                'pass_tds' => $row[8+$x] == "-" ? 0 : $row[8+$x],
                'ints' => $row[9+$x] == "-" ? 0 : $row[9+$x],
                'rush_yds' => $row[11+$x] == "-" ? 0 : $row[11+$x],
                'rush_tds' => $row[12+$x] == "-" ? 0 : $row[12+$x],
                'receptions' => $row[13+$x] == "-" ? 0 : $row[13+$x],
                'rec_yds' => !isset($row[14+$x]) || $row[14+$x] == "-" ? 0 : $row[14+$x],
                'rec_tds' => !isset($row[15+$x]) || $row[15+$x] == "-" ? 0 : $row[15+$x],
                'fumbles' => !isset($row[20+$x]) || $row[20+$x] == "-" ? 0 : $row[20+$x]
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
   
    protected function getTeam(string $input) 
    {
        $teams = [
            'Ari','Atl','Bal','Cin','Cle','Dal','SF','Sea','LAR','Oak','LV','NYG','Phi','Was',
            'Pit','Mia','NYJ','NE','Buf','Ten','Jax','Jac','KC','SD','LAC','Ind','Hou','Chi',
            'Min','Det','GB','Den','NO','Car','TB'
        ];

        // Search $input for team
        $substring = substr($input, -4);
        foreach ($teams as $team) {
            if (strpos($substring, $team) !== false) {
                return strtoupper($team);
            }
        }
    }

    // This function is not being used
    protected function clean(string $string) {
        $string = rtrim($string, ' ');
    
        return preg_replace('/[^A-Za-z.0-9\-]/', ' ', $string); // Removes special chars.
    }

    /**
     * Undocumented function
     *
     * @param array $row
     * @param int $week
     * @return void
     */
    protected function insertKickerRow(array $row, int $week)
    {
        $playerArray = explode(' - ', $row[1]);
        // var_dump($row);
        
        $projectedCol = 7;
        $pointsCol = 6;
        $fgYardsCol = 13;
        $patCol = 12;
        
        if ($this->manager == 'Tyler') {
            $projectedCol = 5;
            $pointsCol = 4;
            $fgYardsCol = 9;
            $patCol = 8;
        }
        
        if ($row[2] == 'Bye') {
            $projected = $points = 0;
        } else {
            $projected = $row[$projectedCol] == "-" ? 0 : $row[$projectedCol];
            $points = $row[$pointsCol] == "-" ? 0 : $row[$pointsCol];
        }

        $roster = Roster::updateOrCreate([
            'year' => $this->year,
            'week' => $week,
            'player' => $this->getPlayerName($playerArray[0]),
            'team' => $this->getTeam($playerArray[0])
        ], [
            'manager' => $this->manager,
            'position' => $playerArray[1],
            'roster_spot' => $row[0],
            'projected' => $projected,
            'points' => $points
        ]);

        if ($row[2] != 'Bye') {
            Stat::updateOrCreate(['roster_id' => $roster->id],
            [
                // 'fg_made' => (int)$row[11+$x],
                'fg_made' => null,
                'fg_yards' => (int)$row[$fgYardsCol],
                'pat_made' => $row[$patCol] == "-" ? 0 : $row[$patCol]
            ]);
        }
    }

    /**
     * Insert a defense for rosters & stats tables
     */
    protected function insertDefenseRow(array $row, int $week)
    {
        $playerArray = explode(' - ', $row[1]);
        $x = abs(17 - count($row));
        // var_dump($row);
        // var_dump($x);
        if ($row[2] == 'Bye') {
            $x = 4;
        }

        $roster = Roster::updateOrCreate([
            'year' => $this->year,
            'week' => $week,
            'player' => $this->getPlayerName($playerArray[0]),
            'team' => $this->getTeam($playerArray[0])
        ], [
            'manager' => $this->manager,
            'position' => $playerArray[1],
            'roster_spot' => $row[0],
            'projected' => $row[5+$x] == "-" ? 0 : $row[5+$x],
            'points' => $row[4+$x] == "-" ? 0 : $row[4+$x]
        ]);

        if ($row[2] != 'Bye') {
            Stat::updateOrCreate(['roster_id' => $roster->id],
            [
                'def_sacks' => (int)$row[8+$x],
                'def_int' => (int)$row[10+$x],
                'def_fum' => (int)$row[11+$x]
            ]);
        }
    }

    /**
     * Undocumented function
     *
     * @param integer $yahooId
     * @return void
     */
    protected function getManagerName(int $yahooId)
    {
        $manager = Manager::where('yahoo_id', $yahooId)
            ->first();

        return $manager->name;
    }

    // This should check for any ids higher than what's in the sqlite db, then insert just those
    public function testUpdateSqlite()
    {
        $tables = ['rosters', 'stats', 'regular_season_matchups', 'team_names'];
        foreach ($tables as $table) {
            echo PHP_EOL.'Table: '.$table.PHP_EOL;
            $this->updateTable($table);
        }

        echo 'Done.';
    }

    private function updateTable(string $table)
    {
        $sqliteQuery = DB::connection('sqlite')->select('select id from '.$table.' order by id desc limit 1');
        $mysqlQuery = DB::connection('mysql')->select('select id from '.$table.' order by id desc limit 1');

        $sqliteHighestId = (int)$sqliteQuery[0]->id;
        $mysqlHighestId = (int)$mysqlQuery[0]->id;

        
        if ($sqliteHighestId != $mysqlHighestId) {
            
            $chunkSize = 100;
            if ($table == 'stats' || $table == 'rosters') {
                $chunkSize = 10;
            }

            // Do it in chunks
            $subQuery = DB::table($table)->where('id', '>', $sqliteHighestId);
            DB::query()->fromSub($subQuery, 'alias')->orderBy('alias.id')->chunk($chunkSize, function ($chunk) use ($table) {
                $chunk = $chunk->toArray();
                
                $rows = array_map(function ($value) {
                    return (array)$value;
                }, $chunk);

                DB::connection('sqlite')->table($table)->insert($rows);
            });
        } else {
            echo $table.' table is up to date!'.PHP_EOL;
        }
    }

    // This should only need to be run once, leaving it for future reference if needed
    public function testStripTeamFromPlayer()
    {
        $teams = [
            'Ari','Atl','Bal','Cin','Cle','Dal','SF','Sea','LAR','Oak','LV','NYG','Phi','Was',
            'Pit','Mia','NYJ','NE','Buf','Ten','Jax','Jac','KC','SD','LAC','Ind','Hou','Chi',
            'Min','Det','GB','Den','NO','Car','TB'
        ];

        $teams = implode('', $teams);

        $players = Roster::where('year', '>', '2019')->get();
        
        foreach ($players as &$player) {

            $name = $player->player;
            $newName = rtrim($name, $teams);
            $newName = rtrim($newName);

            $player->player = $newName;
            $player->save();
        }
    }

}
