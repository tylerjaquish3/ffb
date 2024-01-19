<?php

namespace Tests\Browser\TeamPage;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use App\Models\TeamName;
use App\Models\Manager;
use App\Models\Schedule;
use App\Traits\TestHelper;

class ScheduleTest extends DuskTestCase
{
    use TestHelper;
    protected $year = 2021;

    public function testSchedules()
    {
        $this->browse(function (Browser $browser) {
            try {
                $browser->visit('https://football.fantasysports.yahoo.com/f1/16064');

                if ($browser->element('#login-username')) {
                    $browser
                        ->type('#login-username', 'tylerjaquish')
                        ->press('Next')
                        ->pause(4000)
                        ->waitForText('Enter password')
                        ->type('#login-passwd', 'kimberLYNN8-29')
                        ->press('Next');
                }

                $browser->pause(5000)
                    ->scrollIntoView('#leaguehomestandings')
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

                                // Loop through all weeks
                                for ($week = 1; $week < 15; $week++) {

                                    if ($browser->element('#schedule .Table-interactive tbody tr:nth-child('.$week.')')) {
                                        $browser->with('#schedule .Table-interactive tbody', function ($tr) use ($week, $managerId) {

                                            $row = $tr->text('tr:nth-child('.$week.')');
                                            $stuff = explode(' ', $row);

                                            $opponent = '';
                                            // var_dump($stuff);

                                            $ignore = ['', 'Win', 'Loss', '-', '0.00', 'Recap'];
                                            // Need to get the team name even though it's broken up
                                            for ($x = 1; $x < 10; $x++) {
                                                if (isset($stuff[$x])) {
                                                    if (!$this->isFloat($stuff[$x]) && !$this->isDecimal($stuff[$x])) {
                                                        if (!in_array($stuff[$x], $ignore)) {
                                                            $opponent .= $stuff[$x].' ';
                                                        }
                                                    }
                                                }
                                            }
                                            var_dump($opponent);
                                            
                                            // Clean up the team name
                                            $opponent = rtrim($opponent);
                                            $opponent = ltrim($opponent, '* ');

                                            $this->insertSchedule($managerId, $opponent, $stuff);
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

    // Update or create a schedule row
    protected function insertSchedule($manager1, $opponent, $row)
    {
        $opponentId = $this->getManagerId($opponent);
 
        Schedule::updateOrCreate([
            'year' => $this->year,
            'week' => $row[0],
            'manager1_id' => $manager1,
        ],[
            'manager2_id' => $opponentId
        ]);
    }

    // Look up manager id by team name
    protected function getManagerId(string $teamName)
    {
        $teamName = $this->removeEmoji($teamName);
        $team = TeamName::where('name', $teamName)
            ->where('year', $this->year)
            ->first();

        return $team ? $team->manager_id : null;
    }

    protected function isfloat($val) 
    {
        return ($val == (string)(float)$val);
    }

    protected function isDecimal($val)
    {
        return is_numeric( $val ) && floor( $val ) != $val;
    }

}