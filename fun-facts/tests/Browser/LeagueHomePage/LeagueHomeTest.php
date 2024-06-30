<?php

namespace Tests\Browser\TeamPage;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use App\Models\TeamName;
use App\Models\Manager;
use App\Models\RegularSeasonMatchup;
use App\Traits\TestHelper;

class LeagueHomeTest extends DuskTestCase
{
    use TestHelper;
    protected $year = 2023;

    // Every year, the league page id changes. for 2022, it was 84027 but it will change. change this in 
    // Need to update the other dusk tests too
    // Also, the league managers Yahoo ID changes each year. update those before running all these tests
    // If chomedriver fails, download the right version from the chrome website, then put it in /vendor/laravel/dusk/bin
    // https://googlechromelabs.github.io/chrome-for-testing


    // php artisan dusk --filter=testTeamNames
    // php artisan dusk --filter=testMatchups

    /**
     * Update the team name for each manager
     */
    public function testTeamNames()
    {
        $this->browse(function (Browser $browser) {
            try {
                $leagueId = config('services.yahoo_league_id');
                $browser->visit('https://football.fantasysports.yahoo.com/f1/'.$leagueId);

                if ($browser->element('#login-username')) {
                    $browser
                        ->type('#login-username', env('YAHOO_USER'))
                        ->press('Next')
                        ->pause(4000)
                        ->waitForText('Enter password')
                        ->type('#login-passwd', env('YAHOO_PW'))
                        ->press('Next');
                }

                $browser->waitForText('Suntown');
                $browser->scrollIntoView('#leaguehomestandings');

                for ($x = 1; $x < 11; $x++) {
                    if ($browser->element('#standingstable tbody tr:nth-child('.$x.')')) {
                        $browser->with('#standingstable tbody', function ($tr) use ($x) {
                            $href = $tr->attribute('tr:nth-child('.$x.')', 'data-target');

                            $row = $tr->text('tr:nth-child('.$x.')');
                            $stuff = explode(' ', $row);

                            $teamName = '';
                            foreach ($stuff as $item) {
                                $key = array_search($item, $stuff);
                                if (strpos($item, '-') !== false) {
                                    $winLoss = $key;
                                    for ($x = 1; $x < $winLoss; $x++) {
                                        $teamName .= $stuff[$x].' ';
                                    }
                                    break;
                                }
                            }

                            $teamName = rtrim($teamName);
                            $teamName = $this->removeEmoji($teamName);
                            $moves = array_pop($stuff);
                            if ($moves == '-') {
                                $moves = 0;
                            }

                            $array = explode('/', $href);
                            $yahooId = array_pop($array);
                            $this->insertTeamName($teamName, $yahooId, $moves);

                        });
                    }
                }

            } catch (\Exception $e) {
                echo $e->getMessage();
                echo $e->getTraceAsString();
            }
        });
    }

    protected function insertTeamName($teamName, $yahooId, $moves)
    {
        $manager = Manager::where('yahoo_id', (int)$yahooId)->first();

        $team = TeamName::updateOrCreate([
            'year' => $this->year,
            'manager_id' => $manager->id
        ],[
            'name' => $teamName,
            'moves' => $moves
        ]);
    }

    /**
     * Get matchups from the league home page, based on the Schedule table
     */
    public function testMatchups()
    {
        $this->browse(function (Browser $browser) {
            try {
                $leagueId = config('services.yahoo_league_id');
                $browser->visit('https://football.fantasysports.yahoo.com/f1/'.$leagueId);

                if ($browser->element('#login-username')) {
                    $browser
                        ->type('#login-username', env('YAHOO_USER'))
                        ->press('Next')
                        ->pause(4000)
                        ->waitForText('Enter password')
                        ->type('#login-passwd', env('YAHOO_PW'))
                        ->press('Next');
                }

                $browser->waitForText('Suntown');
                $browser->scrollIntoView('#leaguehomestandings')
                    ->waitForText('Standings')
                    ->click('a[data-target="#lhstschedtab"]')
                    ->pause(4000);

                for ($i = 1; $i < 11; $i++) {
                    // Loop through each manager in the schedule table left side list of teams
                    if ($browser->element('#schedsubnav li:nth-child('.$i.')')) {
                        $browser->with('#schedsubnav', function ($teamTr) use ($i, $browser) {

                            $teamRow = $teamTr->text('li:nth-child('.$i.')');
                            $managerId = $this->getManagerId($teamRow);

                            if ($managerId) {
                                // Click on the team to see their schedule
                                $teamTr->click('li:nth-child('.$i.') a')
                                    ->pause(3000);

                                // Loop through the weeks as set in config
                                $weeks = config('services.weeks');

                                foreach ($weeks as $week) {

                                    if ($browser->element('#schedule .Table-interactive tbody tr:nth-child('.$week.')')) {
                                        $browser->with('#schedule .Table-interactive tbody', function ($tr) use ($week, $managerId) {

                                            $row = $tr->text('tr:nth-child('.$week.')');
                                            $stuff = explode(' ', $row);

                                            $opponent = '';
                                            // Need to get the team name even though it's broken up
                                            foreach ($stuff as $item) {
                                                $key = array_search($item, $stuff);
                                                if ($item == 'Win' || $item == 'Loss') {
                                                    $winLoss = $key;
                                                    for ($x = 1; $x < $winLoss; $x++) {
                                                        $opponent .= $stuff[$x].' ';
                                                    }
                                                    break;
                                                }
                                            }

                                            // Clean up the team name
                                            $opponent = rtrim($opponent);
                                            $opponent = ltrim($opponent, '* ');

                                            $this->insertRegularSeasonMatchup($managerId, $opponent, $stuff);
                                        });
                                    }
                                }
                            }
                        });
                    }
                }

            } catch (\Exception $e) {
                echo $e->getMessage();
                echo $e->getTraceAsString();
            }
        });
    }

    /**
     * Undocumented function
     */
    protected function insertRegularSeasonMatchup(int $manager1, string $opponent, array $row)
    {
        $opponentId = $this->getManagerId($opponent);

        $length = count($row);
        $winningId = $row[$length - 4] > $row[$length - 2] ? $manager1 : $opponentId;
        $losingId = $row[$length - 4] < $row[$length - 2] ? $manager1 : $opponentId;

        $matchup = RegularSeasonMatchup::updateOrCreate([
            'year' => $this->year,
            'week_number' => $row[0],
            'manager1_id' => $manager1,
            'manager2_id' => $opponentId
        ],[
            'manager1_score' => $row[$length - 4],
            'manager2_score' => $row[$length - 2],
            'winning_manager_id' => $winningId,
            'losing_manager_id' => $losingId
        ]);
    }

    /**
     * Undocumented function
     */
    protected function getManagerId(string $teamName)
    {
        $teamName = $this->removeEmoji($teamName);
        $team = TeamName::where('name', $teamName)
            ->where('year', $this->year)
            ->first();

        return $team ? $team->manager_id : null;
    }
}