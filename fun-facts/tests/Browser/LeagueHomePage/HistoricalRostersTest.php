<?php

namespace Tests\Browser\LeagueHomePage;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use App\Models\Roster;
use App\Traits\TestHelper;

class HistoricalRostersTest extends DuskTestCase
{
    use TestHelper;
    protected $year;
    protected $manager;

    // php artisan dusk --filter=testHistoricalTeam

    /**
     * Undocumented function
     */
    public function testHistoricalTeam()
    {
        $seasons = $this->getSeasonsData();

        $this->browse(function (Browser $browser) use ($seasons) {
            // For each team in league
            foreach ($seasons as $season) {
                
                $leagueId = $season['season_id'];
                $this->year = $season['year'];
                foreach ($season['managers'] as $name => $yahooId) {

                    $this->manager = $name;

                    for ($week = 1; $week <= $season['weeks']; $week++) {
                        try {
                            $browser->visit('https://football.fantasysports.yahoo.com/'.$this->year.'/f1/'.$leagueId.'/'.$yahooId.'/team?&week='.$week);

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
                                ->scrollIntoView('#statTable0')
                                ->pause(1000);

                            // Position players table
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
                                            $this->insertRow($stuff, $week);
                                        }
                                    });
                                }
                            }

                            // Defense table
                            $browser->scrollIntoView('#statTable2');
                            for ($x = 1; $x < 5; $x++) {
                                if ($browser->element('#statTable2 tbody tr:nth-child('.$x.')')) {
                                    $browser->with('#statTable2 tbody', function ($tr) use ($week, $x) {
                                        $row = $tr->text('tr:nth-child('.$x.')');
                                        $stuff = preg_split('/\r\n|\r|\n/', $row);
                                        if (count($stuff) > 1) {
                                            $this->insertRow($stuff, $week);
                                        }
                                    });
                                }
                            }
                            
                            // IDP table
                            $browser->scrollIntoView('#statTable3');
                            for ($x = 1; $x < 10; $x++) {
                                if ($browser->element('#statTable3 tbody tr:nth-child('.$x.')')) {
                                    $browser->with('#statTable3 tbody', function ($tr) use ($week, $x) {
                                        $row = $tr->text('tr:nth-child('.$x.')');
                                        $stuff = preg_split('/\r\n|\r|\n/', $row);
                                        if (count($stuff) > 1) {
                                            $this->insertRow($stuff, $week);
                                        }
                                    });
                                }
                            }

                        } catch (\Exception $e) {
                            echo $e->getMessage();
                        }
                    }
                }
            }
        });
    }

    /**
     * Insert row of players into rosters table
     */
    protected function insertRow(array $row, int $week)
    {
        $projectedCol = 4;
        $pointsCol = 3;

        if ($this->manager == 'Tyler') {
            $projectedCol = 3;
            $pointsCol = 2;
        }

        if (str_contains($row[$projectedCol], '%')) {
            $projectedCol--;
            $pointsCol--; 
        }

        Roster::updateOrCreate([
            'year' => $this->year,
            'week' => $week,
            'player' => $row[1]
        ],[
            'manager' => $this->manager,
            'position' => $row[0],
            'roster_spot' => $row[0] == 'Q/W' ? 'Q/W/R/T' : $row[0],
            'projected' => $row[$projectedCol] == "–" ? 0 : $row[$projectedCol],
            'points' => $row[$pointsCol] == "–" ? 0 : $row[$pointsCol]
        ]);
    }

    protected function getSeasonsData()
    {
        $seasons = [
            [
                // 'year' => 2021,
                // 'season_id' => 16064,
                // 'weeks' => 14,
                // 'managers' => [
                    // 'AJ' => 9,
                    // 'Andy' => 4,
                    // 'Ben' => 3,
                    // 'Cameron' => 5,
                    // 'Cole' => 2,
                    // 'Everett' => 7,
                    // 'Gavin' => 10,
                    // 'Justin' => 6,
                    // 'Matt' => 8,
                    // 'Tyler' => 1,
                // ]
            // ],[
                // 'year' => 2019,
                // 'season_id' => 201651,
                // 'weeks' => 13,
                // 'managers' => [
                //     'AJ' => 2,
                //     'Andy' => 10,
                //     'Ben' => 4,
                //     'Cameron' => 8,
                //     'Cole' => 3,
                //     'Everett' => 6,
                //     'Gavin' => 9,
                //     'Justin' => 7,
                //     'Matt' => 5,
                //     'Tyler' => 1,
                // ]
            // ],[
                // 'year' => 2018,
                // 'season_id' => 224863,
                // 'weeks' => 13,
                // 'managers' => [
                //     'AJ' => 4,
                //     'Andy' => 3,
                //     'Ben' => 6,
                //     'Cameron' => 8,
                //     'Cole' => 2,
                //     'Everett' => 5,
                //     'Gavin' => 10,
                //     'Justin' => 7,
                //     'Matt' => 9,
                //     'Tyler' => 1,
                // ]
            // ],[
                // 'year' => 2017,
                // 'season_id' => 262191,
                // 'weeks' => 13,
                // 'managers' => [
                    // 'AJ' => 4,
                    // 'Andy' => 3,
                    // 'Ben' => 6,
                    // 'Cameron' => 8,
                    // 'Cole' => 2,
                    // 'Everett' => 5,
                    // 'Gavin' => 10,
                    // 'Justin' => 7,
                    // 'Matt' => 9,
                    // 'Tyler' => 1,
                // ]
            // ],[
                // 'year' => 2016,
                // 'season_id' => 477642,
                // 'weeks' => 13,
                // 'managers' => [
                //     'AJ' => 4,
                //     'Andy' => 3,
                //     'Ben' => 6,
                //     'Cameron' => 8,
                //     'Cole' => 2,
                //     'Everett' => 5,
                //     'Gavin' => 10,
                //     'Justin' => 7,
                //     'Matt' => 9,
                //     'Tyler' => 1,
                // ]
            // ],[
                // 'year' => 2015,
                // 'season_id' => 217861,
                // 'weeks' => 13,
                // 'managers' => [
                //     'AJ' => 5,
                //     'Andy' => 10,
                //     'Ben' => 7,
                //     'Cameron' => 4,
                //     'Cole' => 2,
                //     'Everett' => 3,
                //     'Gavin' => 8,
                //     'Justin' => 6,
                //     'Matt' => 9,
                //     'Tyler' => 1,
                // ]
            // ],[
                // 'year' => 2014,
                // 'season_id' => 53077,
                // 'weeks' => 13,
                // 'managers' => [
                //     'AJ' => 8,
                //     'Andy' => 10,
                //     'Ben' => 9,
                //     'Cameron' => 7,
                //     'Cole' => 3,
                //     'Everett' => 5,
                //     'Gavin' => 6,
                //     'Justin' => 2,
                //     'Matt' => 4,
                //     'Tyler' => 1,
                // ]
            // ],[
                // 'year' => 2013,
                // 'season_id' => 27577,
                // 'weeks' => 13,
                // 'managers' => [
                //     'AJ' => 3,
                //     'Andy' => 5,
                //     'Ben' => 8,
                //     'Cameron' => 2,
                //     'Cole' => 4,
                //     'Everett' => 9,
                //     'Gavin' => 10,
                //     'Justin' => 7,
                //     'Matt' => 6,
                //     'Tyler' => 1,
                // ]
            // ],[
                // 'year' => 2012,
                // 'season_id' => 26725,
                // 'weeks' => 13,
                // 'managers' => [
                //     'AJ' => 3,
                //     'Andy' => 10,
                //     'Ben' => 9,
                //     'Cameron' => 4,
                //     'Cole' => 2,
                //     'Everett' => 8,
                //     'Gavin' => 5,
                //     'Justin' => 6,
                //     'Matt' => 7,
                //     'Tyler' => 1,
            // ],[
                // 'year' => 2011,
                // 'season_id' => 163601,
                // 'weeks' => 13,
                // 'managers' => [
                    // 'AJ' => 8,
                    // 'Andy' => 10,
                    // 'Ben' => 6,
                    // 'Cameron' => 5,
                    // 'Cole' => 2,
                    // 'Everett' => 7,
                    // 'Gavin' => 9,
                    // 'Justin' => 3,
                    // 'Matt' => 4,
                    // 'Tyler' => 1,
                // ]
            // ],[
                // 'year' => 2010,
                // 'season_id' => 35443,
                // 'weeks' => 13,
                // 'managers' => [
                //     'AJ' => 8,
                //     'Andy' => 7,
                //     'Ben' => 5,
                //     'Cameron' => 3,
                //     'Cole' => 1,
                //     'Everett' => 2,
                //     'Gavin' => 4,
                //     'Justin' => 10,
                //     'Matt' => 6,
                //     'Tyler' => 9,
                // ]
            // ],[
                // 'year' => 2009,
                // 'season_id' => 42150,
                // 'weeks' => 13,
                // 'managers' => [
                //     'AJ' => 8,
                //     'Andy' => 4,
                //     'Ben' => 3,
                //     'Cameron' => 2,
                //     'Cole' => 7,
                //     'Everett' => 9,
                //     'Gavin' => 1,
                //     'Justin' => 5,
                //     'Matt' => 6,
                //     'Tyler' => 10,
                // ]
            // ],[
                // 'year' => 2008,
                // 'season_id' => 8224,
                // 'weeks' => 13,
                // 'managers' => [
                //     'AJ' => 7,
                //     'Andy' => 9,
                //     'Ben' => 8,
                //     'Cameron' => 5,
                //     'Cole' => 10,
                //     'Everett' => 6,
                //     'Gavin' => 3,
                //     'Justin' => 2,
                //     'Matt' => 4,
                //     'Tyler' => 1,
                // ]
            // ],[
                // 'year' => 2007,
                // 'season_id' => 73988,
                // 'weeks' => 13,
                // 'managers' => [
                //     'AJ' => 2,
                //     'Ben' => 4,
                //     'Cole' => 8,
                //     'Everett' => 3,
                //     'Gavin' => 7,
                //     'Justin' => 6,
                //     'Matt' => 5,
                //     'Tyler' => 1,
                // ]
            // ],[
                // 'year' => 2006,
                // 'season_id' => 48909,
                // 'weeks' => 1,
                // 'managers' => [
                //     'AJ' => 4,
                //     'Ben' => 8,
                //     'Cole' => 7,
                //     'Everett' => 5,
                //     'Gavin' => 2,
                //     'Justin' => 3,
                //     'Matt' => 6,
                //     'Tyler' => 1,
                // ]
            ]
        ];

        return $seasons;
    }
}
