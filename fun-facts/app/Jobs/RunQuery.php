<?php

namespace App\Jobs;

use App\Models\Roster;
use App\Models\SeasonPosition;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;

class RunQuery implements ShouldQueue
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
            $count = 0;

            $roster = DB::select("SELECT r.year, week, manager, sum(points) as points
                FROM rosters r
                WHERE roster_spot != 'BN' and roster_spot != 'IR'
                GROUP BY r.year, week, manager 
                ORDER BY r.year desc, week desc");
            
            foreach ($roster as $row) {

                // lookup data from rsm
                $rsm = DB::select("SELECT * FROM regular_season_matchups
                    JOIN managers m on m.id = regular_season_matchups.manager1_id
                    WHERE year = ? and week_number = ? and m.name = ?", [$row->year, $row->week, $row->manager]);

                if (empty($rsm)) {
                    echo 'No data for '.$row->manager.' on Wk '.$row->week.' '.$row->year.PHP_EOL;
                    die;
                }
                // compare $row->points to $rsm->manager1_score
                $score1 = round($rsm[0]->manager1_score, 0);
                $score2 = round($row->points, 0);
                if ($score1 != $score2) {
                    // dd($row->points, $rsm[0]->manager1_score);
                    echo $score1.' vs '.$score2.' on Wk '.$row->week.' '.$row->year.' for '.$row->manager.PHP_EOL;
                    $count++;

                    $positions = SeasonPosition::where('year', $row->year)->where('position', '!=', 'IR')->get();;
                    $rosters = Roster::where('year', $row->year)
                        ->where('week', $row->week)
                        ->where('manager', $row->manager)
                        ->where('roster_spot', '!=', 'IR')
                        ->get();

                    if (count($rosters) < count($positions)) {
                        echo count($rosters).' vs '.count($positions).PHP_EOL;
                        // echo 'Missing positions for '.$row->manager.' on Wk '.$row->week.' '.$row->year.PHP_EOL;
                        foreach ($positions as $position) {
                            $found = false;
                            foreach ($rosters as $roster) {
                                if ($roster->roster_spot == $position->position) {
                                    $found = true;
                                    break;
                                }
                            }
                            if (!$found) {
                                echo 'Missing '.$position->position.PHP_EOL;
                            }
                        }
                    }
                    echo '=========================='.PHP_EOL;
                    if ($count > 10) {
                    // if ($row->year < 2018) {
                        die;
                    }
                }

            }

            echo 'Count: '.$count.PHP_EOL;


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