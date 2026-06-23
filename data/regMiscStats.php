<?php
require_once '../functions.php';

$stat = isset($_GET['stat']) ? (int)$_GET['stat'] : 0;

switch ($stat) {

case 1: ?>
<table class="table table-responsive table-striped nowrap" id="datatable-misc1">
    <thead>
        <th>Manager</th>
        <th>Longest Win Streak</th>
        <th>Win Start</th>
        <th>Win End</th>
        <th>Longest Lose Streak</th>
        <th>Lose Start</th>
        <th>Lose End</th>
    </thead>
    <tbody>
        <?php
        $response = [];
        $result2 = query("SELECT * FROM managers");
        while ($manager = fetch_array($result2)) {

            $managerId = $manager['id'];
            $managerName = $manager['name'];

            $winStreak = $loseStreak = $longestWinStreak = $longestLoseStreak = 0;
            $winStreakStartYear = $winStreakStartWeek = $winStreakEndYear = $winStreakEndWeek = 0;
            $loseStreakStartYear = $loseStreakStartWeek = $loseStreakEndYear = $loseStreakEndWeek = 0;
            $longestWinStartYear = $longestWinStartWeek = $longestWinEndYear = $longestWinEndWeek = 0;
            $longestLoseStartYear = $longestLoseStartWeek = $longestLoseEndYear = $longestLoseEndWeek = 0;

            $result = query("SELECT name, w.year, w.week_number, win, lose
                FROM managers
                JOIN regular_season_matchups rsm ON managers.id = rsm.manager1_id
                JOIN (
                    SELECT SUM(CASE
                    WHEN manager1_id = $managerId AND manager1_score > manager2_score THEN 1
                    ELSE 0
                    END) AS win, year, week_number
                    FROM regular_season_matchups
                    GROUP BY year, week_number
                ) w ON w.year = rsm.year AND w.week_number = rsm.week_number

                JOIN (
                    SELECT SUM(CASE
                    WHEN manager1_id = $managerId AND manager1_score < manager2_score THEN 1
                    ELSE 0
                    END) AS lose, year, week_number
                    FROM regular_season_matchups
                    GROUP BY year, week_number
                ) w2 ON w2.year = rsm.year AND w2.week_number = rsm.week_number
                WHERE name = '$managerName'
                ORDER BY w.year ASC, w.week_number ASC");
            while ($row = fetch_array($result)) {
                if ($row['win'] == 1) {
                    if ($winStreak == 0) {
                        $winStreakStartYear = $row['year'];
                        $winStreakStartWeek = $row['week_number'];
                    }
                    $winStreak++;
                    $winStreakEndYear = $row['year'];
                    $winStreakEndWeek = $row['week_number'];

                    if ($loseStreak > $longestLoseStreak) {
                        $longestLoseStreak = $loseStreak;
                        $longestLoseStartYear = $loseStreakStartYear;
                        $longestLoseStartWeek = $loseStreakStartWeek;
                        $longestLoseEndYear = $loseStreakEndYear;
                        $longestLoseEndWeek = $loseStreakEndWeek;
                    }
                    $loseStreak = 0;

                    if ($winStreak > $longestWinStreak) {
                        $longestWinStreak = $winStreak;
                        $longestWinStartYear = $winStreakStartYear;
                        $longestWinStartWeek = $winStreakStartWeek;
                        $longestWinEndYear = $winStreakEndYear;
                        $longestWinEndWeek = $winStreakEndWeek;
                    }
                } else {
                    if ($loseStreak == 0) {
                        $loseStreakStartYear = $row['year'];
                        $loseStreakStartWeek = $row['week_number'];
                    }
                    $loseStreak++;
                    $loseStreakEndYear = $row['year'];
                    $loseStreakEndWeek = $row['week_number'];

                    if ($winStreak > $longestWinStreak) {
                        $longestWinStreak = $winStreak;
                        $longestWinStartYear = $winStreakStartYear;
                        $longestWinStartWeek = $winStreakStartWeek;
                        $longestWinEndYear = $winStreakEndYear;
                        $longestWinEndWeek = $winStreakEndWeek;
                    }
                    $winStreak = 0;

                    if ($loseStreak > $longestLoseStreak) {
                        $longestLoseStreak = $loseStreak;
                        $longestLoseStartYear = $loseStreakStartYear;
                        $longestLoseStartWeek = $loseStreakStartWeek;
                        $longestLoseEndYear = $loseStreakEndYear;
                        $longestLoseEndWeek = $loseStreakEndWeek;
                    }
                }
            }

            if ($loseStreak > $longestLoseStreak) {
                $longestLoseStreak = $loseStreak;
                $longestLoseStartYear = $loseStreakStartYear;
                $longestLoseStartWeek = $loseStreakStartWeek;
                $longestLoseEndYear = $loseStreakEndYear;
                $longestLoseEndWeek = $loseStreakEndWeek;
            }
            if ($winStreak > $longestWinStreak) {
                $longestWinStreak = $winStreak;
                $longestWinStartYear = $winStreakStartYear;
                $longestWinStartWeek = $winStreakStartWeek;
                $longestWinEndYear = $winStreakEndYear;
                $longestWinEndWeek = $winStreakEndWeek;
            }

            $response[] = [
                'manager' => $managerName,
                'winStreak' => $longestWinStreak,
                'winStart' => $longestWinStreak > 0 ? $longestWinStartYear . ' Wk ' . $longestWinStartWeek : '',
                'winEnd' => $longestWinStreak > 0 ? $longestWinEndYear . ' Wk ' . $longestWinEndWeek : '',
                'loseStreak' => $longestLoseStreak,
                'loseStart' => $longestLoseStreak > 0 ? $longestLoseStartYear . ' Wk ' . $longestLoseStartWeek : '',
                'loseEnd' => $longestLoseStreak > 0 ? $longestLoseEndYear . ' Wk ' . $longestLoseEndWeek : '',
            ];
        }

        foreach ($response as $row) { ?>
            <tr>
                <td><?php echo $row['manager']; ?></td>
                <td><?php echo $row['winStreak']; ?></td>
                <td><?php echo $row['winStart']; ?></td>
                <td><?php echo $row['winEnd']; ?></td>
                <td><?php echo $row['loseStreak']; ?></td>
                <td><?php echo $row['loseStart']; ?></td>
                <td><?php echo $row['loseEnd']; ?></td>
            </tr>
        <?php } ?>
    </tbody>
    <tfoot>
        <tr><td colspan=7>Streaks span across seasons</td></tr>
    </tfoot>
</table>
<?php break;

case 2: ?>
<table class="table table-responsive table-striped nowrap" id="datatable-misc2">
    <thead>
        <th>Manager</th>
        <th>Points For</th>
        <th>Points Against</th>
        <th>Difference</th>
    </thead>
    <tbody>
        <?php
        $result = query("SELECT managers.name, SUM(manager1_score) AS points_for,
            SUM(manager2_score) AS points_against, SUM(manager1_score) - SUM(manager2_score) AS diff
            FROM regular_season_matchups rsm
            JOIN managers ON managers.id = rsm.manager1_id
            GROUP BY manager1_id");
        while ($row = fetch_array($result)) { ?>
            <tr>
                <td><?php echo $row['name']; ?></td>
                <td><?php echo number_format($row['points_for'], 2, '.', ','); ?></td>
                <td><?php echo number_format($row['points_against'], 2, '.', ','); ?></td>
                <td><?php echo number_format($row['diff'], 2, '.', ','); ?></td>
            </tr>
        <?php } ?>
    </tbody>
    <tfoot>
        <tr><td colspan=4>Total points over the course of our league history</td></tr>
    </tfoot>
</table>
<?php break;

case 3: ?>
<table class="table table-responsive table-striped nowrap" id="datatable-misc3">
    <thead>
        <th>Manager</th>
        <th>Most PF</th>
        <th>Least PF</th>
        <th>Most PA</th>
        <th>Least PA</th>
    </thead>
    <tbody>
        <?php
        $result = query("SELECT name, MAX(points_for) as max_pf, MAX(points_against) as max_pa,
            MIN(points_for) as min_pf, MIN(points_against) as min_pa
            FROM (
                SELECT managers.name, year, SUM(manager1_score) AS points_for,
                SUM(manager2_score) AS points_against
                FROM regular_season_matchups rsm
                JOIN managers ON managers.id = rsm.manager1_id
                WHERE year < ".$season."
                GROUP BY manager1_id, year
            ) as all_years
            GROUP BY name");
        while ($row = fetch_array($result)) { ?>
            <tr>
                <td><?php echo $row['name']; ?></td>
                <td><?php echo number_format($row['max_pf'], 2, '.', ','); ?></td>
                <td><?php echo number_format($row['min_pf'], 2, '.', ','); ?></td>
                <td><?php echo number_format($row['max_pa'], 2, '.', ','); ?></td>
                <td><?php echo number_format($row['min_pa'], 2, '.', ','); ?></td>
            </tr>
        <?php } ?>
    </tbody>
    <tfoot>
        <tr><td colspan=5>Min and max points for and against for a season</td></tr>
    </tfoot>
</table>
<?php break;

case 4: ?>
<table class="table table-responsive table-striped nowrap" id="datatable-misc4">
    <thead>
        <th>Manager</th>
        <th>Avg PF</th>
        <th>Avg PA</th>
        <th>Difference</th>
    </thead>
    <tbody>
        <?php
        $result = query("SELECT managers.name, AVG(manager1_score) as avg_pf, AVG(manager2_score) as avg_pa,
            AVG(manager1_score) - AVG(manager2_score) as diff
            FROM regular_season_matchups rsm
            JOIN managers ON managers.id = rsm.manager1_id
            GROUP BY manager1_id");
        while ($row = fetch_array($result)) { ?>
            <tr>
                <td><?php echo $row['name']; ?></td>
                <td><?php echo number_format($row['avg_pf'], 2, '.', ','); ?></td>
                <td><?php echo number_format($row['avg_pa'], 2, '.', ','); ?></td>
                <td><?php echo number_format($row['diff'], 2, '.', ','); ?></td>
            </tr>
        <?php } ?>
    </tbody>
    <tfoot>
        <tr><td colspan=4>Average weekly points for and against</td></tr>
    </tfoot>
</table>
<?php break;

case 5: ?>
<table class="table table-responsive table-striped nowrap" id="datatable-misc5">
    <thead>
        <th>Manager</th>
        <th>Best Start</th>
        <th>Year</th>
        <th>Finish</th>
        <th>Worst Start</th>
        <th>Year</th>
        <th>Finish</th>
    </thead>
    <tbody>
        <?php
        $startStreaks = [];
        for ($x = 1; $x < 11; $x++) {

            $years = [];
            $result = query("SELECT * FROM regular_season_matchups
                WHERE manager1_id = $x and winning_manager_id = $x
                order by year asc, week_number asc");
            while ($row = fetch_array($result)) {
                $years[$row['year']][] = $row;
            };

            $myStreakWin = $myLongestWin = 0;
            foreach ($years as $y => $weeks) {
                $lastWeek = 0;
                foreach ($weeks as $w) {
                    if ($w['week_number'] == 1) {
                        $myStreakWin = 0;
                    }
                    if ($w['week_number'] == ($lastWeek + 1)) {
                        $myStreakWin++;
                        if ($myStreakWin > $myLongestWin) {
                            $myLongestWin = $myStreakWin;
                            $winYear = $y;
                        }
                        $lastWeek = $w['week_number'];
                    } else {
                        break;
                    }
                }
            }

            $years = [];
            $result = query("SELECT * FROM regular_season_matchups
                WHERE manager1_id = $x and losing_manager_id = $x
                order by year asc, week_number asc");
            while ($row = fetch_array($result)) {
                $years[$row['year']][] = $row;
            };

            $myStreakLose = $myLongestLose = 0;
            foreach ($years as $y => $weeks) {
                $lastWeek = 0;
                foreach ($weeks as $w) {
                    if ($w['week_number'] == 1) {
                        $myStreakLose = 0;
                    }
                    if ($w['week_number'] == ($lastWeek + 1)) {
                        $myStreakLose++;
                        if ($myStreakLose > $myLongestLose) {
                            $myLongestLose = $myStreakLose;
                            $loseYear = $y;
                        }
                        $lastWeek = $w['week_number'];
                    } else {
                        break;
                    }
                }
            }

            $result2 = query("SELECT * FROM managers WHERE id = $x");
            while ($row2 = fetch_array($result2)) {
                $manager = $row2['name'];
            }

            $winYearFinish = '-';
            $result3 = query("SELECT * FROM finishes WHERE manager_id = $x AND year = $winYear");
            while ($row3 = fetch_array($result3)) {
                $winYearFinish = $row3['finish'];
            }

            $loseYearFinish = '-';
            $result4 = query("SELECT * FROM finishes WHERE manager_id = $x AND year = $loseYear");
            while ($row4 = fetch_array($result4)) {
                $loseYearFinish = $row4['finish'];
            }

            $startStreaks[] = [
                'manager' => $manager,
                'winStreak' => $myLongestWin . ' - 0',
                'winYear' => $winYear,
                'winYearFinish' => $winYearFinish,
                'loseStreak' => '0 - ' . $myLongestLose,
                'loseYear' => $loseYear,
                'loseYearFinish' => $loseYearFinish
            ];
        }

        foreach ($startStreaks as $row) { ?>
            <tr>
                <td><?php echo $row['manager']; ?></td>
                <td><?php echo $row['winStreak']; ?></td>
                <td><?php echo $row['winYear']; ?></td>
                <td><?php echo $row['winYearFinish']; ?></td>
                <td><?php echo $row['loseStreak']; ?></td>
                <td><?php echo $row['loseYear']; ?></td>
                <td><?php echo $row['loseYearFinish']; ?></td>
            </tr>
        <?php } ?>
    </tbody>
    <tfoot>
        <tr><td colspan=7>Longest winning or losing streak to start a season and how it ended</td></tr>
    </tfoot>
</table>
<?php break;

case 6: ?>
<table class="table table-responsive table-striped nowrap" id="datatable-misc6">
    <thead>
        <th>Manager</th>
        <th>Biggest Win</th>
        <th>Smallest Win</th>
        <th>Biggest Loss</th>
        <th>Smallest Loss</th>
    </thead>
    <tbody>
        <?php
        $result = query("SELECT managers.name, MAX(manager1_score - manager2_score) as biggest_win,
            MAX(manager2_score - manager1_score) as biggest_loss,
            MIN(CASE WHEN manager1_score > manager2_score THEN manager1_score - manager2_score ELSE null END) as smallest_win,
            MIN(CASE WHEN manager1_score < manager2_score THEN manager2_score - manager1_score ELSE null END) as smallest_loss
            FROM regular_season_matchups rsm
            JOIN managers ON managers.id = rsm.manager1_id
            GROUP BY manager1_id");
        while ($row = fetch_array($result)) { ?>
            <tr>
                <td><?php echo $row['name']; ?></td>
                <td><?php echo number_format($row['biggest_win'], 2, '.', ','); ?></td>
                <td><?php echo number_format($row['smallest_win'], 2, '.', ','); ?></td>
                <td><?php echo number_format($row['biggest_loss'], 2, '.', ','); ?></td>
                <td><?php echo number_format($row['smallest_loss'], 2, '.', ','); ?></td>
            </tr>
        <?php } ?>
    </tbody>
    <tfoot>
        <tr><td colspan=5>Min and max margin of victory and defeat</td></tr>
    </tfoot>
</table>
<?php break;

case 7: ?>
<table class="table table-responsive table-striped nowrap" id="datatable-misc7">
    <thead>
        <th>Manager</th>
        <th>Most PF</th>
        <th>Least PF</th>
        <th>Most PA</th>
        <th>Least PA</th>
    </thead>
    <tbody>
        <?php
        $result = query("SELECT managers.name, MAX(manager1_score) as max_pf, MAX(manager2_score) as max_pa,
            MIN(manager1_score) as min_pf, MIN(manager2_score) as min_pa
            FROM regular_season_matchups rsm
            JOIN managers ON managers.id = rsm.manager1_id
            GROUP BY manager1_id");
        while ($row = fetch_array($result)) { ?>
            <tr>
                <td><?php echo $row['name']; ?></td>
                <td><?php echo number_format($row['max_pf'], 2, '.', ','); ?></td>
                <td><?php echo number_format($row['min_pf'], 2, '.', ','); ?></td>
                <td><?php echo number_format($row['max_pa'], 2, '.', ','); ?></td>
                <td><?php echo number_format($row['min_pa'], 2, '.', ','); ?></td>
            </tr>
        <?php } ?>
    </tbody>
    <tfoot>
        <tr><td colspan=5>Regular season min and max points for and points against</td></tr>
    </tfoot>
</table>
<?php break;

case 8: ?>
<table class="table table-responsive table-striped nowrap" id="datatable-misc8">
    <thead>
        <th>Manager</th>
        <th>Wks with Top 3 Pts</th>
        <th>Losses with Top 3 Pts</th>
        <th>Pct</th>
    </thead>
    <tbody>
        <?php
        $year = $week = 0;
        $index = -1;
        $first = true;

        $men = ['AJ','Ben','Tyler','Matt','Justin','Andy','Cole','Everett','Cameron','Gavin'];
        $managers = [];
        foreach ($men as $man) {
            $managers[$man] = ['top' => 0, 'losses' => 0];
        }

        $result = query("SELECT * FROM regular_season_matchups rsm
            JOIN managers ON managers.id = rsm.manager1_id
            ORDER BY year, week_number, manager1_score DESC");
        while ($row = fetch_array($result)) {
            $currentYear = $row['year'];
            $currentWeek = $row['week_number'];
            if ($year != $currentYear || $week != $currentWeek) {
                $index = -1;
            }
            $index++;
            if ($index < 3) {
                if ($first || ($year == $currentYear && $week == $currentWeek)) {
                    $managers[$row['name']]['top']++;
                    if ($row['manager1_score'] < $row['manager2_score']) {
                        $managers[$row['name']]['losses']++;
                    }
                    $first = false;
                }
            }
            $year = $currentYear;
            $week = $currentWeek;
        }

        foreach ($managers as $manager => $array) { ?>
            <tr>
                <td><?php echo $manager; ?></td>
                <td><?php echo $array['top']; ?></td>
                <td><?php echo $array['losses']; ?></td>
                <td><?php echo round(($array['losses'] / $array['top']) * 100, 1) . ' %'; ?></td>
            </tr>
        <?php } ?>
    </tbody>
    <tfoot>
        <tr><td colspan=4>How many times we were top 3 in points for the week and unluckily lost</td></tr>
    </tfoot>
</table>
<?php break;

case 9: ?>
<table class="table table-responsive table-striped nowrap" id="datatable-misc9">
    <thead>
        <th>Manager</th>
        <th>Wks with Bottom 3 Pts</th>
        <th>Wins with Bottom 3 Pts</th>
        <th>Pct</th>
    </thead>
    <tbody>
        <?php
        $year = $week = 0;
        $index = -1;
        $first = true;

        $men = ['AJ','Ben','Tyler','Matt','Justin','Andy','Cole','Everett','Cameron','Gavin'];
        $managers = [];
        foreach ($men as $man) {
            $managers[$man] = ['bottom' => 0, 'wins' => 0];
        }

        $result = query("SELECT * FROM regular_season_matchups rsm
            JOIN managers ON managers.id = rsm.manager1_id
            ORDER BY year, week_number, manager1_score ASC");
        while ($row = fetch_array($result)) {
            $currentYear = $row['year'];
            $currentWeek = $row['week_number'];
            if ($year != $currentYear || $week != $currentWeek) {
                $index = -1;
            }
            $index++;
            if ($index < 3) {
                if ($first || ($year == $currentYear && $week == $currentWeek)) {
                    $managers[$row['name']]['bottom']++;
                    if ($row['manager1_score'] > $row['manager2_score']) {
                        $managers[$row['name']]['wins']++;
                    }
                    $first = false;
                }
            }
            $year = $currentYear;
            $week = $currentWeek;
        }

        foreach ($managers as $manager => $array) { ?>
            <tr>
                <td><?php echo $manager; ?></td>
                <td><?php echo $array['bottom']; ?></td>
                <td><?php echo $array['wins']; ?></td>
                <td><?php echo round(($array['wins'] / $array['bottom']) * 100, 1) . ' %'; ?></td>
            </tr>
        <?php } ?>
    </tbody>
    <tfoot>
        <tr><td colspan=4>How many times we were bottom 3 in points for the week and luckily won</td></tr>
    </tfoot>
</table>
<?php break;

case 10: ?>
<table class="table table-responsive table-striped nowrap" id="datatable-misc10">
    <thead>
        <th>Manager</th>
        <th>Wins</th>
        <th>Losses</th>
        <th>Win %</th>
    </thead>
    <tbody>
        <?php
        $men = ['AJ','Ben','Tyler','Matt','Justin','Andy','Cole','Everett','Cameron','Gavin'];
        $managers = [];
        foreach ($men as $man) {
            $managers[$man] = ['losses' => 0, 'wins' => 0];
        }

        $scores = [];
        $result = query("SELECT year, week_number, name, manager1_score FROM regular_season_matchups rsm
            JOIN managers ON managers.id = rsm.manager1_id
            ORDER BY year, week_number, manager1_score ASC");
        while ($row = fetch_array($result)) {
            $scores[$row['year']][$row['week_number']][$row['name']] = $row['manager1_score'];
        }
        foreach ($scores as $year => $weekArray) {
            foreach ($weekArray as $week) {
                $index = 0;
                foreach ($week as $manager => $value) {
                    if (count($week) == 8) {
                        $managers[$manager]['wins'] += $index;
                        $managers[$manager]['losses'] += 7 - $index;
                    } else {
                        $managers[$manager]['wins'] += $index;
                        $managers[$manager]['losses'] += 9 - $index;
                    }
                    $index++;
                }
            }
        }

        foreach ($managers as $manager => $array) { ?>
            <tr>
                <td><?php echo $manager; ?></td>
                <td><?php echo $array['wins']; ?></td>
                <td><?php echo $array['losses']; ?></td>
                <td><?php echo round(($array['wins'] / ($array['wins'] + $array['losses'])) * 100, 1) . ' %'; ?></td>
            </tr>
        <?php } ?>
    </tbody>
    <tfoot>
        <tr><td colspan=4>Had we played every manager every week, our record would be...</td></tr>
    </tfoot>
</table>
<?php break;

case 11: ?>
<table class="table table-responsive table-striped nowrap" id="datatable-misc11">
    <thead>
        <th>Manager</th>
        <th>#1 Picks</th>
        <th>#10 Picks</th>
        <th>Avg. Position</th>
    </thead>
    <tbody>
        <?php
        $result = query("SELECT name, coalesce(pick1, 0) as pick1, coalesce(pick10, 0) as pick10, adp
            FROM managers
            LEFT JOIN (
                SELECT COUNT(id) as pick1, manager_id FROM draft
                WHERE overall_pick = 1
                GROUP BY manager_id
            ) p1 ON p1.manager_id = managers.id
            LEFT JOIN (
                SELECT COUNT(id) as pick10, manager_id FROM draft
                WHERE overall_pick = 10
                GROUP BY manager_id
            ) p10 ON p10.manager_id = managers.id
            LEFT JOIN (
                SELECT AVG(overall_pick) as adp, manager_id FROM draft
                WHERE round = 1
                GROUP BY manager_id
            ) average ON average.manager_id = managers.id");
        while ($row = fetch_array($result)) { ?>
            <tr>
                <td><?php echo $row['name']; ?></td>
                <td><?php echo $row['pick1']; ?></td>
                <td><?php echo $row['pick10']; ?></td>
                <td><?php echo round($row['adp'], 1); ?></td>
            </tr>
        <?php } ?>
    </tbody>
    <tfoot>
        <tr><td colspan=4>Number of times with #1 or #10 pick and average draft position</td></tr>
    </tfoot>
</table>
<?php break;

case 12: ?>
<table class="table table-responsive table-striped nowrap" id="datatable-misc12">
    <thead>
        <th>Manager</th>
        <th>Moves</th>
        <th>Trades</th>
        <th>Total Per Year</th>
    </thead>
    <tbody>
        <?php
        $result = query("SELECT managers.name, SUM(moves) as moves, SUM(trades) as trades,
                SUM(moves+trades) as total, COUNT(DISTINCT year) as num_years,
                ROUND(CAST(SUM(moves+trades) AS FLOAT) / COUNT(DISTINCT year), 1) as per_year
            FROM team_names
            JOIN managers on manager_id = managers.id
            GROUP BY managers.name");
        while ($row = fetch_array($result)) { ?>
            <tr>
                <td><?php echo $row['name']; ?></td>
                <td><?php echo $row['moves']; ?></td>
                <td><?php echo $row['trades']; ?></td>
                <td><?php echo $row['per_year']; ?></td>
            </tr>
        <?php } ?>
    </tbody>
    <tfoot>
        <tr><td colspan=4>Number of adds/drops/trades</td></tr>
    </tfoot>
</table>
<?php break;

case 13: ?>
<table class="table table-responsive table-striped nowrap" id="datatable-misc13">
    <thead>
        <th>Manager</th>
        <th>Points</th>
        <th>Optimal Points</th>
        <th>Accuracy</th>
    </thead>
    <tbody>
        <?php
        $result = query("SELECT managers.name,
            SUM(manager1_score) AS points,
            SUM(manager1_optimal) AS optimal
            FROM regular_season_matchups rsm
            JOIN managers ON managers.id = rsm.manager1_id
            WHERE manager1_optimal IS NOT NULL
            GROUP BY manager1_id
            ORDER BY managers.name");
        while ($row = fetch_array($result)) {
            $accuracy = $row['optimal'] > 0 ? round(($row['points'] / $row['optimal']) * 100, 1) : 0;
            ?>
            <tr>
                <td><?php echo $row['name']; ?></td>
                <td><?php echo number_format($row['points'], 1, '.', ','); ?></td>
                <td><?php echo number_format($row['optimal'], 1, '.', ','); ?></td>
                <td><?php echo $accuracy; ?>%</td>
            </tr>
        <?php } ?>
    </tbody>
</table>
<?php break;

case 14: ?>
<table class="table table-responsive table-striped nowrap" id="datatable-misc14">
    <thead>
        <th>Manager</th>
        <th>QB</th>
        <th>RB</th>
        <th>WR</th>
        <th>TE</th>
        <th>K</th>
        <th>DEF</th>
        <th>BN</th>
    </thead>
    <tbody>
        <?php
        $result = query("SELECT
                r.manager,
                SUM(CASE WHEN r.position = 'QB' AND r.roster_spot != 'BN' THEN r.points ELSE 0 END) AS qb_points,
                SUM(CASE WHEN r.position = 'RB' AND r.roster_spot != 'BN' THEN r.points ELSE 0 END) AS rb_points,
                SUM(CASE WHEN r.position = 'WR' AND r.roster_spot != 'BN' THEN r.points ELSE 0 END) AS wr_points,
                SUM(CASE WHEN r.position = 'TE' AND r.roster_spot != 'BN' THEN r.points ELSE 0 END) AS te_points,
                SUM(CASE WHEN r.position = 'K' AND r.roster_spot != 'BN' THEN r.points ELSE 0 END) AS k_points,
                SUM(CASE WHEN r.position = 'DEF' AND r.roster_spot != 'BN' THEN r.points ELSE 0 END) AS def_points,
                SUM(CASE WHEN r.roster_spot = 'BN' THEN r.points ELSE 0 END) AS bn_points
            FROM rosters r
            WHERE r.points IS NOT NULL
            GROUP BY r.manager
            ORDER BY manager ASC");
        while ($row = fetch_array($result)) { ?>
            <tr>
                <td><?php echo $row['manager']; ?></td>
                <td><?php echo number_format($row['qb_points'], 2, '.', ','); ?></td>
                <td><?php echo number_format($row['rb_points'], 2, '.', ','); ?></td>
                <td><?php echo number_format($row['wr_points'], 2, '.', ','); ?></td>
                <td><?php echo number_format($row['te_points'], 2, '.', ','); ?></td>
                <td><?php echo number_format($row['k_points'], 2, '.', ','); ?></td>
                <td><?php echo number_format($row['def_points'], 2, '.', ','); ?></td>
                <td><?php echo number_format($row['bn_points'], 2, '.', ','); ?></td>
            </tr>
        <?php } ?>
    </tbody>
</table>
<?php break;

case 15: ?>
<table class="table table-responsive table-striped nowrap" id="datatable-misc15">
    <thead>
        <th>Manager</th>
        <th>Season</th>
        <th>QB</th>
        <th>RB</th>
        <th>WR</th>
        <th>TE</th>
        <th>K</th>
        <th>DEF</th>
        <th>BN</th>
    </thead>
    <tbody>
        <?php
        $result = query("SELECT
                r.manager,
                r.year,
                SUM(CASE WHEN r.position = 'QB' AND r.roster_spot != 'BN' THEN r.points ELSE 0 END) AS qb_points,
                SUM(CASE WHEN r.position = 'RB' AND r.roster_spot != 'BN' THEN r.points ELSE 0 END) AS rb_points,
                SUM(CASE WHEN r.position = 'WR' AND r.roster_spot != 'BN' THEN r.points ELSE 0 END) AS wr_points,
                SUM(CASE WHEN r.position = 'TE' AND r.roster_spot != 'BN' THEN r.points ELSE 0 END) AS te_points,
                SUM(CASE WHEN r.position = 'K' AND r.roster_spot != 'BN' THEN r.points ELSE 0 END) AS k_points,
                SUM(CASE WHEN r.position = 'DEF' AND r.roster_spot != 'BN' THEN r.points ELSE 0 END) AS def_points,
                SUM(CASE WHEN r.roster_spot = 'BN' THEN r.points ELSE 0 END) AS bn_points
            FROM rosters r
            WHERE r.points IS NOT NULL
            GROUP BY r.manager, r.year
            ORDER BY r.manager, r.year DESC");
        while ($row = fetch_array($result)) { ?>
            <tr>
                <td><?php echo $row['manager']; ?></td>
                <td><?php echo $row['year']; ?></td>
                <td><?php echo number_format($row['qb_points'], 2, '.', ','); ?></td>
                <td><?php echo number_format($row['rb_points'], 2, '.', ','); ?></td>
                <td><?php echo number_format($row['wr_points'], 2, '.', ','); ?></td>
                <td><?php echo number_format($row['te_points'], 2, '.', ','); ?></td>
                <td><?php echo number_format($row['k_points'], 2, '.', ','); ?></td>
                <td><?php echo number_format($row['def_points'], 2, '.', ','); ?></td>
                <td><?php echo number_format($row['bn_points'], 2, '.', ','); ?></td>
            </tr>
        <?php } ?>
    </tbody>
</table>
<?php break;

case 16: ?>
<table class="table table-responsive table-striped nowrap" id="datatable-misc16">
    <thead>
        <th>Manager</th>
        <th>Season</th>
        <th>Week</th>
        <th>QB</th>
        <th>RB</th>
        <th>WR</th>
        <th>TE</th>
        <th>K</th>
        <th>DEF</th>
        <th>BN</th>
    </thead>
    <tbody>
        <?php
        $result = query("SELECT
                r.year,
                r.week,
                r.manager,
                SUM(CASE WHEN r.position = 'QB' AND r.roster_spot != 'BN' THEN r.points ELSE 0 END) AS qb_points,
                SUM(CASE WHEN r.position = 'RB' AND r.roster_spot != 'BN' THEN r.points ELSE 0 END) AS rb_points,
                SUM(CASE WHEN r.position = 'WR' AND r.roster_spot != 'BN' THEN r.points ELSE 0 END) AS wr_points,
                SUM(CASE WHEN r.position = 'TE' AND r.roster_spot != 'BN' THEN r.points ELSE 0 END) AS te_points,
                SUM(CASE WHEN r.position = 'K' AND r.roster_spot != 'BN' THEN r.points ELSE 0 END) AS k_points,
                SUM(CASE WHEN r.position = 'DEF' AND r.roster_spot != 'BN' THEN r.points ELSE 0 END) AS def_points,
                SUM(CASE WHEN r.roster_spot = 'BN' THEN r.points ELSE 0 END) AS bn_points
            FROM rosters r
            WHERE r.points IS NOT NULL
            GROUP BY r.year, r.week, r.manager
            ORDER BY r.year DESC, r.week ASC");
        while ($row = fetch_array($result)) { ?>
            <tr>
                <td><?php echo $row['manager']; ?></td>
                <td><?php echo $row['year']; ?></td>
                <td><?php echo $row['week']; ?></td>
                <td><?php echo number_format($row['qb_points'], 2, '.', ','); ?></td>
                <td><?php echo number_format($row['rb_points'], 2, '.', ','); ?></td>
                <td><?php echo number_format($row['wr_points'], 2, '.', ','); ?></td>
                <td><?php echo number_format($row['te_points'], 2, '.', ','); ?></td>
                <td><?php echo number_format($row['k_points'], 2, '.', ','); ?></td>
                <td><?php echo number_format($row['def_points'], 2, '.', ','); ?></td>
                <td><?php echo number_format($row['bn_points'], 2, '.', ','); ?></td>
            </tr>
        <?php } ?>
    </tbody>
</table>
<?php break;

case 17: ?>
<table class="table table-responsive table-striped nowrap" id="datatable-misc17">
    <thead>
        <th>Manager</th>
        <th>Top Scorer (Week)</th>
        <th>Bottom Scorer (Week)</th>
        <th>Top Scorer (Season)</th>
        <th>Bottom Scorer (Season)</th>
    </thead>
    <tbody>
        <?php
        $managerList = ['Tyler', 'AJ', 'Gavin', 'Matt', 'Cameron', 'Andy', 'Everett', 'Justin', 'Cole', 'Ben'];

        $weeklyTopScorers = [];
        $result = query("SELECT rsm.manager1_id, COUNT(*) as count
            FROM regular_season_matchups rsm
            WHERE rsm.manager1_score = (
                SELECT MAX(rsm2.manager1_score)
                FROM regular_season_matchups rsm2
                WHERE rsm2.year = rsm.year AND rsm2.week_number = rsm.week_number
            )
            GROUP BY rsm.manager1_id");
        while ($row = fetch_array($result)) {
            $weeklyTopScorers[$row['manager1_id']] = $row['count'];
        }

        $weeklyBottomScorers = [];
        $result = query("SELECT rsm.manager1_id, COUNT(*) as count
            FROM regular_season_matchups rsm
            WHERE rsm.manager1_score = (
                SELECT MIN(rsm2.manager1_score)
                FROM regular_season_matchups rsm2
                WHERE rsm2.year = rsm.year AND rsm2.week_number = rsm.week_number
            )
            GROUP BY rsm.manager1_id");
        while ($row = fetch_array($result)) {
            $weeklyBottomScorers[$row['manager1_id']] = $row['count'];
        }

        $seasonalTopScorers = [];
        $result = query("SELECT season_totals.manager1_id, COUNT(*) as count
            FROM (
                SELECT rsm.manager1_id, rsm.year, SUM(rsm.manager1_score) as total_points
                FROM regular_season_matchups rsm
                GROUP BY rsm.manager1_id, rsm.year
            ) season_totals
            WHERE season_totals.total_points = (
                SELECT MAX(st2.total_points)
                FROM (
                    SELECT rsm2.manager1_id, rsm2.year, SUM(rsm2.manager1_score) as total_points
                    FROM regular_season_matchups rsm2
                    GROUP BY rsm2.manager1_id, rsm2.year
                ) st2
                WHERE st2.year = season_totals.year
            )
            GROUP BY season_totals.manager1_id");
        while ($row = fetch_array($result)) {
            $seasonalTopScorers[$row['manager1_id']] = $row['count'];
        }

        $seasonalBottomScorers = [];
        $result = query("SELECT season_totals.manager1_id, COUNT(*) as count
            FROM (
                SELECT rsm.manager1_id, rsm.year, SUM(rsm.manager1_score) as total_points
                FROM regular_season_matchups rsm
                GROUP BY rsm.manager1_id, rsm.year
            ) season_totals
            WHERE season_totals.total_points = (
                SELECT MIN(st2.total_points)
                FROM (
                    SELECT rsm2.manager1_id, rsm2.year, SUM(rsm2.manager1_score) as total_points
                    FROM regular_season_matchups rsm2
                    GROUP BY rsm2.manager1_id, rsm2.year
                ) st2
                WHERE st2.year = season_totals.year
            )
            GROUP BY season_totals.manager1_id");
        while ($row = fetch_array($result)) {
            $seasonalBottomScorers[$row['manager1_id']] = $row['count'];
        }

        for ($i = 1; $i <= 10; $i++) {
            $managerName = $managerList[$i-1];
            ?>
            <tr>
                <td><?php echo $managerName; ?></td>
                <td><?php echo isset($weeklyTopScorers[$i]) ? $weeklyTopScorers[$i] : 0; ?></td>
                <td><?php echo isset($weeklyBottomScorers[$i]) ? $weeklyBottomScorers[$i] : 0; ?></td>
                <td><?php echo isset($seasonalTopScorers[$i]) ? $seasonalTopScorers[$i] : 0; ?></td>
                <td><?php echo isset($seasonalBottomScorers[$i]) ? $seasonalBottomScorers[$i] : 0; ?></td>
            </tr>
        <?php } ?>
    </tbody>
</table>
<?php break;

case 18:
$_activeManagers = [];
$_res = query("SELECT id, name FROM managers ORDER BY id");
while ($_row = fetch_array($_res)) {
    $_activeManagers[] = ['id' => (int)$_row['id'], 'name' => $_row['name']];
}

$_regStreaks = [];
foreach ($_activeManagers as $_mgr) {
    $_mid = $_mgr['id'];
    $_res2 = query("
        SELECT CASE WHEN winning_manager_id = $_mid THEN 1 ELSE 0 END as won
        FROM regular_season_matchups
        WHERE manager1_id = $_mid AND winning_manager_id IS NOT NULL
        ORDER BY year ASC, week_number ASC
    ");
    $_games = [];
    while ($_g = fetch_array($_res2)) $_games[] = (int)$_g['won'];
    $_streak = 0;
    for ($_i = count($_games) - 1; $_i >= 0; $_i--) {
        if ($_games[$_i] === 1) $_streak++;
        else break;
    }
    $_regStreaks[] = ['manager' => $_mgr['name'], 'streak' => $_streak];
}
usort($_regStreaks, fn($a, $b) => $b['streak'] - $a['streak']);

$_h2hStreaks = [];
$_n = count($_activeManagers);
for ($_i = 0; $_i < $_n; $_i++) {
    for ($_j = $_i + 1; $_j < $_n; $_j++) {
        $_id1   = $_activeManagers[$_i]['id'];
        $_id2   = $_activeManagers[$_j]['id'];
        $_name1 = $_activeManagers[$_i]['name'];
        $_name2 = $_activeManagers[$_j]['name'];
        $_res2 = query("
            SELECT winning_manager_id
            FROM regular_season_matchups
            WHERE manager1_id = $_id1 AND manager2_id = $_id2
              AND winning_manager_id IS NOT NULL
            ORDER BY year DESC, week_number DESC
        ");
        $_activeWin = null; $_streak = 0; $_first = true;
        while ($_g = fetch_array($_res2)) {
            $_wid = (int)$_g['winning_manager_id'];
            if ($_first) { $_activeWin = $_wid; $_streak = 1; $_first = false; }
            elseif ($_wid === $_activeWin) { $_streak++; }
            else break;
        }
        if ($_streak >= 2 && $_activeWin !== null) {
            $_winName = ($_activeWin === $_id1) ? $_name1 : $_name2;
            $_losName = ($_activeWin === $_id1) ? $_name2 : $_name1;
            $_h2hStreaks[] = ['winner' => $_winName, 'loser' => $_losName, 'streak' => $_streak];
        }
    }
}
usort($_h2hStreaks, fn($a, $b) => $b['streak'] - $a['streak']);
$_top3H2H = array_slice($_h2hStreaks, 0, 3);
?>
<div id="datatable-misc18">
    <p class="mb-1"><strong>Active Winning Streak (Regular Season)</strong></p>
    <table class="table table-responsive table-striped nowrap mb-3" id="datatable-misc18a">
        <thead><tr><th>Manager</th><th>Active Streak</th></tr></thead>
        <tbody>
            <?php foreach ($_regStreaks as $_row): ?>
            <tr><td><?php echo $_row['manager']; ?></td><td><?php echo $_row['streak']; ?></td></tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot><tr><td colspan="2">Consecutive regular season wins through the most recent game played</td></tr></tfoot>
    </table>
    <p class="mb-1"><strong>Active Win Streak vs. Opponent (Regular Season) — Top 3</strong></p>
    <table class="table table-responsive table-striped nowrap" id="datatable-misc18c">
        <thead><tr><th>Manager</th><th>Opponent</th><th>Active Streak</th></tr></thead>
        <tbody>
            <?php if (empty($_top3H2H)): ?>
            <tr><td colspan="3" class="text-muted">No active streaks of 2+ games found</td></tr>
            <?php else: foreach ($_top3H2H as $_row): ?>
            <tr>
                <td><?php echo $_row['winner']; ?></td>
                <td><?php echo $_row['loser']; ?></td>
                <td><?php echo $_row['streak']; ?></td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
        <tfoot><tr><td colspan="3">Consecutive regular season wins against the same opponent</td></tr></tfoot>
    </table>
</div>
<?php break;

default:
    http_response_code(400);
    echo 'Invalid stat';
    break;
}
