<?php

include 'connections.php';

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($pageName == 'Dashboard') {
    $dashboardNumbers = getDashboardNumbers($conn);
    $postseasonChart = getPostseasonChartNumbers($conn);
}
if ((strpos($pageName, 'Profile') !== false)) {
    $profileNumbers = getProfileNumbers($conn);
    $finishesChart = getFinishesChartNumbers($conn);
    $seasonNumbers = getSeasonNumbers($conn);
}
if ($pageName == 'Regular Season') {
    $regSeasonMatchups = getRegularSeasonMatchups($conn);
}
if ($pageName == 'Postseason') {
    $postseasonMatchups = getPostseasonMatchups($conn);
}
if ($pageName == 'Draft') {
    $draftResults = getDraftResults($conn);
}
if ((strpos($pageName, 'Recap') !== false)) {
    $regSeasonMatchups = getRegularSeasonMatchups($conn);
    $postseasonMatchups = getPostseasonMatchups($conn);
    $seasonNumbers = getAllNumbersBySeason($conn);
    $draftResults = getDraftResults($conn);
}
if ($pageName == 'Current Season') {

    $result = mysqli_query($conn, "SELECT year FROM rosters ORDER BY year DESC LIMIT 1");
    while ($row = mysqli_fetch_array($result)) {
        $season = $row['year'];
    }

    $points = getCurrentSeasonPoints($conn, $season);
    $stats = getCurrentSeasonStats($conn, $season);
    $statsAgainst = getCurrentSeasonStatsAgainst($conn, $season);
    $bestWeek = getCurrentSeasonBestWeek($conn, $season);
    $topPerformers = getCurrentSeasonTopPerformers($conn, $season);
}

// include 'saveFunFacts.php';

function getDashboardNumbers($conn)
{
    $response = [];

    $result = mysqli_query($conn, "select count(distinct(year)) as num_years from finishes");
    while ($row = mysqli_fetch_array($result)) {
        $response['seasons'] = $row['num_years'];
    }

    $result = mysqli_query($conn, "SELECT count(distinct(manager_id)) as winners FROM finishes WHERE finish = 1");
    while ($row = mysqli_fetch_array($result)) {
        $response['unique_winners'] = $row['winners'];
    }

    $result = mysqli_query($conn, "SELECT MAX(championships) as championships FROM (SELECT count(manager_id) as championships FROM finishes WHERE finish = 1 group by manager_id ORDER BY championships DESC LIMIT 1) as max_num");
    while ($row = mysqli_fetch_array($result)) {
        $response['most_championships_number'] = $row['championships'];
    }

    $tempName = '';
    $result = mysqli_query($conn, "SELECT count(manager_id) as championships, name FROM finishes JOIN managers on managers.id = finishes.manager_id  WHERE finish = 1 GROUP BY name HAVING count(manager_id) = " . $response['most_championships_number']);
    while ($row = mysqli_fetch_array($result)) {
        if ($tempName == '') {
            $tempName = $row['name'];
        } else {
            $tempName .= ', ' . $row['name'];
        }
    }

    $response['most_championships_manager'] = $tempName;

    $result = mysqli_query($conn, "SELECT count(manager1_id) as wins FROM regular_season_matchups rsm WHERE manager1_score > manager2_score GROUP BY manager1_id ORDER BY count(manager1_id) DESC LIMIT 1");
    while ($row = mysqli_fetch_array($result)) {
        $response['most_wins_number'] = $row['wins'];
    }

    $tempName = '';
    $result = mysqli_query($conn, "SELECT count(manager1_id) as championships, name FROM regular_season_matchups rsm JOIN managers on managers.id = rsm.manager1_id   WHERE manager1_score > manager2_score GROUP BY name HAVING count(manager1_id) = " . $response['most_wins_number']);
    while ($row = mysqli_fetch_array($result)) {
        if ($tempName == '') {
            $tempName = $row['name'];
        } else {
            $tempName .= ', ' . $row['name'];
        }
    }

    $response['most_wins_manager'] = $tempName;

    return $response;
}

function getPostseasonChartNumbers($conn)
{
    $response = [];

    $result2 = mysqli_query($conn, "SELECT * FROM managers");
    while ($manager = mysqli_fetch_array($result2)) {
        $response['managers'][] = $manager['name'];
        $managerId = $manager['id'];

        $ships = $appearances = $shipAppearances = 0;
        $year = 0000;
        $result = mysqli_query($conn, "SELECT * FROM playoff_matchups WHERE manager1_id = $managerId OR manager2_id = $managerId");
        while ($row = mysqli_fetch_array($result)) {
            // Calc championships
            if ($row['round'] == 'Final') {
                if ($row['manager1_id'] == $managerId) {
                    $shipAppearances++;

                    if ($row['manager1_score'] > $row['manager2_score']) {
                        $ships++;
                    }
                }

                if ($row['manager2_id'] == $managerId) {
                    $shipAppearances++;

                    if ($row['manager2_score'] > $row['manager1_score']) {
                        $ships++;
                    }
                }
            }

            if ($year != $row['year']) {
                $appearances++;
            }

            $year = $row['year'];
        }

        $response['appearances'][] = $appearances;
        $response['shipAppearances'][] = $shipAppearances;
        $response['ships'][] = $ships;
    }

    return $response;
}

function getProfileNumbers($conn)
{
    $response = [];
    $managerId = 0;

    if (isset($_GET)) {

        $result = mysqli_query($conn, "SELECT * FROM managers WHERE name = '" . $_GET['id'] . "'");
        while ($row = mysqli_fetch_array($result)) {
            $managerId = $row['id'];
        }

        $result = mysqli_query($conn, "SELECT name, wins, losses, total, wins/total AS win_pct
            FROM managers
            JOIN (
                SELECT COUNT(manager1_id) AS wins, manager1_id FROM regular_season_matchups rsm
                WHERE manager1_score > manager2_score GROUP BY manager1_id
            ) w ON w.manager1_id = managers.id

            JOIN (
                SELECT COUNT(manager1_id) AS losses, manager1_id FROM regular_season_matchups rsm
                WHERE manager1_score < manager2_score GROUP BY manager1_id
            ) l ON l.manager1_id = managers.id

            JOIN (
                SELECT COUNT(manager1_id) AS total, manager1_id FROM regular_season_matchups rsm
                GROUP BY manager1_id
            ) t ON t.manager1_id = managers.id
            ORDER BY win_pct DESC");
        $rank = 1;
        while ($row = mysqli_fetch_array($result)) {
            if ($row['name'] == $_GET['id']) {
                $response['record'] = $row['wins'] . " - " . $row['losses'];
                $response['recordRank'] = $rank;
            }

            $rank++;
        }

        $ships = 0;
        $years = '';
        $result = mysqli_query($conn, "SELECT * FROM playoff_matchups WHERE manager1_id = $managerId OR manager2_id = $managerId");
        while ($row = mysqli_fetch_array($result)) {
            // Calc championships
            if ($row['round'] == 'Final') {
                if ($row['manager1_id'] == $managerId && $row['manager1_score'] > $row['manager2_score']) {
                    $ships++;
                    $years .= $row['year'] . ', ';
                }

                if ($row['manager2_id'] == $managerId && $row['manager2_score'] > $row['manager1_score']) {
                    $ships++;
                    $years .= $row['year'] . ', ';
                }
            }
        }

        $years = rtrim($years, ', ');

        if ($ships == 0) {
            $years = 'N/A';
        }

        $response['championships'] = $ships;
        $response['championshipYears'] = $years;

        // Calc playoff record and rank
        $wins = $losses = 0;
        $rank = 1;
        $result = mysqli_query($conn, "SELECT name, IFNULL(winsTop, 0) as winsTop, winsBottom, lossesTop, lossesBottom, totalTop, totalBottom, (IFNULL(winsTop, 0)+winsBottom)/(totalTop+totalBottom) AS win_pct
            FROM managers
            LEFT JOIN (
                SELECT COUNT(manager1_id) AS winsTop, manager1_id FROM playoff_matchups rsm
                WHERE manager1_score > manager2_score GROUP BY manager1_id
            ) w ON w.manager1_id = managers.id

            LEFT JOIN (
                SELECT COUNT(manager1_id) AS lossesTop, manager1_id FROM playoff_matchups rsm
                WHERE manager1_score < manager2_score GROUP BY manager1_id
            ) l ON l.manager1_id = managers.id

            LEFT JOIN (
                SELECT COUNT(manager2_id) AS winsBottom, manager2_id FROM playoff_matchups rsm
                WHERE manager2_score > manager1_score GROUP BY manager2_id
            ) w2 ON w2.manager2_id = managers.id

            LEFT JOIN (
                SELECT COUNT(manager2_id) AS lossesBottom, manager2_id FROM playoff_matchups rsm
                WHERE manager2_score < manager1_score GROUP BY manager2_id
            ) l2 ON l2.manager2_id = managers.id

            LEFT JOIN (
                SELECT COUNT(manager1_id) AS totalTop, manager1_id FROM playoff_matchups rsm
                GROUP BY manager1_id
            ) t ON t.manager1_id = managers.id

            LEFT JOIN (
                SELECT COUNT(manager2_id) as totalBottom, manager2_id FROM playoff_matchups rsm
                GROUP BY manager2_id
            ) t2 ON t2.manager2_id = managers.id
            ORDER BY win_pct DESC");
        while ($row = mysqli_fetch_array($result)) {
            // Calc playoff record
            if ($row['name'] == $_GET['id']) {
                $wins = $row['winsTop'] + $row['winsBottom'];
                $losses = $row['lossesTop'] + $row['lossesBottom'];

                $response['playoffRecord'] = $wins . " - " . $losses;
                $response['playoffRecordRank'] = $rank;
            }

            $rank++;
        }

        // Calc total points and rank
        $rank = 1;
        $result = mysqli_query($conn, "SELECT SUM(manager1_score) as total_points, manager1_id
            FROM regular_season_matchups
            GROUP BY manager1_id ORDER BY total_points DESC;");
        while ($row = mysqli_fetch_array($result)) {
            if ($row['manager1_id'] == $managerId) {
                $response['totalPoints'] = number_format($row['total_points'], 2, '.', ',');
                $response['totalPointsRank'] = $rank;
            }

            $rank++;
        }
    } else {
        // redirect to index
    }

    return $response;
}

function getFinishesChartNumbers($conn)
{
    $results = ['years' => '', 'finishes' => ''];

    if (isset($_GET)) {

        $result = mysqli_query($conn, "SELECT * FROM finishes
            JOIN managers ON managers.id = finishes.manager_id
            WHERE name = '" . $_GET['id'] . "'");
        while ($row = mysqli_fetch_array($result)) {
            $results['years'] .= $row['year'] . ',';
            $results['finishes'] .= $row['finish'] . ',';
        }

        $results['years'] = rtrim($results['years'], ',');
        $results['finishes'] = rtrim($results['finishes'], ',');
    } else {
        // redirect to index
    }

    return $results;
}

function getSeasonNumbers($conn)
{
    $results = [];

    if (isset($_GET)) {

        $result = mysqli_query($conn, "SELECT * FROM finishes
            JOIN managers ON managers.id = finishes.manager_id
            WHERE name = '" . $_GET['id'] . "'");
        while ($row = mysqli_fetch_array($result)) {
            $managerId = $row['manager_id'];
            $year = $row['year'];

            $pf = $pa = $wins = $losses = 0;
            $result2 = mysqli_query($conn, "SELECT * FROM regular_season_matchups
                WHERE manager1_id = " . $managerId . " AND year = " . $year);
            while ($row2 = mysqli_fetch_array($result2)) {
                $pf += $row2['manager1_score'];
                $pa += $row2['manager2_score'];
                if ($row2['manager1_score'] > $row2['manager2_score']) {
                    $wins++;
                } else {
                    $losses++;
                }
            }

            $winPct = $wins * 100 / ($wins + $losses);
            $results[$year] = [
                'year' => $year,
                'finish' => $row['finish'],
                'record' => $wins . ' - ' . $losses,
                'win_pct' => round($winPct, 1),
                'pf' => $pf,
                'pa' => $pa
            ];
        }
    } else {
        // redirect to index
    }

    return $results;
}

function getRegularSeasonMatchups($conn)
{
    $results = [];
    $prevWeek = $prevYear = 0;

    $result = mysqli_query($conn, "SELECT m.name as m1, l.name as m2, rsm.year, rsm.week_number, rsm.manager1_score, rsm.manager2_score
        FROM managers m
        JOIN regular_season_matchups rsm ON rsm.manager1_id = m.id
        LEFT JOIN (
        SELECT name, manager2_id, year, week_number, manager2_score FROM regular_season_matchups rsm2
            JOIN managers ON managers.id = rsm2.manager2_id
        ) l ON l.manager2_id = rsm.manager2_id AND l.year = rsm.year AND l.week_number = rsm.week_number
        ORDER BY rsm.year, rsm.week_number ASC");
    while ($row = mysqli_fetch_array($result)) {

        $currentYear = $row['year'];
        $currentWeek = $row['week_number'];

        if ($currentYear != $prevYear || $currentWeek != $prevWeek) {
            $manager1s = [];
        }

        if (!in_array($row['m2'], $manager1s)) {
            $manager1s[] = $row['m1'];

            $winner = 'm2';
            if ($row['manager1_score'] > $row['manager2_score']) {
                $winner = 'm1';
            }

            $results[] = [
                'year' => $row['year'],
                'week' => $row['week_number'],
                'manager1' => $row['m1'],
                'manager2' => $row['m2'],
                'score' => $row['manager1_score'] . ' - ' . $row['manager2_score'],
                'winner' => $winner
            ];
        }

        $prevYear = $currentYear;
        $prevWeek = $currentWeek;
    }

    return $results;
}

function getPostseasonMatchups($conn)
{
    $results = [];

    $result = mysqli_query($conn, "SELECT m.name as m1, l.name as m2, rsm.year, rsm.round, rsm.manager1_seed, rsm.manager2_seed, rsm.manager1_score, rsm.manager2_score
        FROM managers m
        JOIN playoff_matchups rsm ON rsm.manager1_id = m.id
        LEFT JOIN (
        SELECT name, manager2_id, year, round, manager2_score FROM playoff_matchups rsm2
            JOIN managers ON managers.id = rsm2.manager2_id
        ) l ON l.manager2_id = rsm.manager2_id AND l.year = rsm.year AND l.round = rsm.round
        ORDER BY rsm.year, rsm.round ASC");
    while ($row = mysqli_fetch_array($result)) {

        $winner = 'm2';
        if ($row['manager1_score'] > $row['manager2_score']) {
            $winner = 'm1';
        }

        // Add sorting so that order is quarter, semi, final
        if ($row['round'] == 'Quarterfinal') {
            $sort = 1;
        } elseif ($row['round'] == 'Semifinal') {
            $sort = 2;
        } else {
            $sort = 3;
        }

        $results[] = [
            'year' => $row['year'],
            'round' => $row['round'],
            'manager1' => $row['m1'],
            'manager2' => $row['m2'],
            'score' => $row['manager1_score'] . ' - ' . $row['manager2_score'],
            'winner' => $winner,
            'm1seed' => $row['manager1_seed'],
            'm2seed' => $row['manager2_seed'],
            'sort' => $sort
        ];
    }

    return $results;
}

function getDraftResults($conn)
{
    $results = [];

    $result = mysqli_query($conn, "SELECT *
        FROM managers m
        JOIN draft ON manager_id = m.id
        ");
    while ($row = mysqli_fetch_array($result)) {
        $results[] = $row;
    }

    return $results;
}

function getAllNumbersBySeason($conn)
{
    $results = [];

    if (isset($_GET['id'])) {
        $season = $_GET['id'];
    } else {
        $result = mysqli_query($conn, "SELECT DISTINCT year FROM finishes ORDER BY year DESC LIMIT 1");
        while ($row = mysqli_fetch_array($result)) {
            $season = $row['year'];
        }
    }

    $result = mysqli_query($conn, "SELECT finishes.manager_id, finishes.year, managers.name as manager_name,
        team_names.name AS team_name, moves, finish, trades
        FROM finishes
        JOIN managers ON managers.id = finishes.manager_id
        JOIN team_names ON managers.id = team_names.manager_id AND finishes.year = team_names.year
        WHERE finishes.YEAR  = '" . $season . "'");
    while ($row = mysqli_fetch_array($result)) {
        $managerId = $row['manager_id'];
        $year = $row['year'];

        $pf = $pa = $wins = $losses = 0;
        $result2 = mysqli_query($conn, "SELECT * FROM regular_season_matchups
            WHERE manager1_id = " . $managerId . " AND year = " . $year);
        while ($row2 = mysqli_fetch_array($result2)) {
            $pf += $row2['manager1_score'];
            $pa += $row2['manager2_score'];
            if ($row2['manager1_score'] > $row2['manager2_score']) {
                $wins++;
            } else {
                $losses++;
            }
        }

        $winPct = $wins * 100 / ($wins + $losses);
        $results[$managerId] = [
            'manager' => $row['manager_name'],
            'finish' => $row['finish'],
            'record' => $wins . ' - ' . $losses,
            'win_pct' => round($winPct, 1),
            'pf' => $pf,
            'pa' => $pa,
            'team_name' => $row['team_name'],
            'moves' => $row['moves'],
            'trades' => $row['trades']
        ];
    }

    usort($results, function($a, $b) {
        if ($b['win_pct'] - $a['win_pct'] == 0) {
            return $b['pf'] - $a['pf'];
        }
        return $b['win_pct'] - $a['win_pct'];
    });

    foreach ($results as $key => &$result) {
        $result['seed'] = $key+1;
    }

    return $results;
}

function getCurrentSeasonPoints($conn, $season)
{
    $result = mysqli_query($conn, "SELECT manager, roster_spot, SUM(points) AS points, SUM(projected) AS projected FROM rosters r
        WHERE YEAR = $season
        GROUP BY manager, roster_spot");
    while ($row = mysqli_fetch_array($result)) {
        $points[$row['manager']][$row['roster_spot']] = [
            'projected' => $row['projected'],
            'points' => $row['points']
        ];
    }

    return $points;
}

function getCurrentSeasonStats($conn, $season)
{
    $result = mysqli_query($conn, "SELECT manager, SUM(pass_yds) AS pass_yds, SUM(pass_tds) AS pass_tds, SUM(ints) AS ints, SUM(rush_yds) AS rush_yds, SUM(rush_tds) AS rush_tds,
        SUM(receptions) AS rec, SUM(rec_yds) AS rec_yds, SUM(rec_tds) AS rec_tds, SUM(fumbles) AS fum, SUM(fg_made) AS fg_made, SUM(pat_made) AS pat_made,
        SUM(def_sacks) AS def_sacks, SUM(def_int) AS def_int, SUM(def_fum) AS def_fum
        FROM rosters r
        JOIN stats s ON s.roster_id = r.id
        WHERE YEAR = $season and roster_spot != 'BN'
        GROUP BY manager");

    return $result;
}

function getCurrentSeasonBestWeek($conn)
{
    $bestWeek = [];
    $result = mysqli_query($conn, "SELECT WEEK, MAX(IF(roster_spot='QB', points, NULL)) AS top_qb,
        MAX(IF(roster_spot='RB', points, NULL)) AS top_rb,
        MAX(IF(roster_spot='WR', points, NULL)) AS top_wr,
        MAX(IF(roster_spot='TE', points, NULL)) AS top_te,
        MAX(IF(roster_spot='W/R/T', points, NULL)) AS top_wrt,
        MAX(IF(roster_spot='Q/W/R/T', points, NULL)) AS top_qwrt,
        MAX(IF(roster_spot='K', points, NULL)) AS top_k,
        MAX(IF(roster_spot='DEF', points, NULL)) AS top_def,
        MAX(IF(roster_spot='BN', points, NULL)) AS top_bn
        FROM rosters
        WHERE YEAR = 2019
        GROUP BY week");
    while ($row = mysqli_fetch_array($result)) {
        $week = $row['WEEK'];

        $bestWeek[$week]['qb'] = queryBestWeekPlayer($conn, $week, $row['top_qb']);
        $bestWeek[$week]['rb'] = queryBestWeekPlayer($conn, $week, $row['top_rb']);
        $bestWeek[$week]['wr'] = queryBestWeekPlayer($conn, $week, $row['top_wr']);
        $bestWeek[$week]['te'] = queryBestWeekPlayer($conn, $week, $row['top_te']);
        $bestWeek[$week]['wrt'] = queryBestWeekPlayer($conn, $week, $row['top_wrt']);
        if ($row['top_qwrt'])
        $bestWeek[$week]['qwrt'] = queryBestWeekPlayer($conn, $week, $row['top_qwrt']);
        $bestWeek[$week]['k'] = queryBestWeekPlayer($conn, $week, $row['top_k']);
        $bestWeek[$week]['def'] = queryBestWeekPlayer($conn, $week, $row['top_def']);
        $bestWeek[$week]['bn'] = queryBestWeekPlayer($conn, $week, $row['top_bn']);
    }

    return $bestWeek;
}

function queryBestWeekPlayer($conn, $week, $pts)
{
    $response = [];

    $result = mysqli_query($conn, "SELECT * FROM rosters WHERE week = $week AND points = $pts");
    while ($row = mysqli_fetch_array($result)) {
        $response = [
            'manager' => $row['manager'],
            'player' => ($row['player']),
            'points' => round($pts, 1)
        ];
    }

    return $response;
}

function getCurrentSeasonStatsAgainst($conn, $season)
{
    $managers = ['Tyler', 'Matt', 'Justin', 'Ben', 'AJ', 'Gavin', 'Cameron', 'Cole', 'Everett', 'Andy'];
    foreach ($managers as $manager) {
        $response[$manager] = [
            'pass_yds' => 0,
            'pass_tds' => 0,
            'ints' => 0,
            'rush_yds' => 0,
            'rush_tds' => 0,
            'receptions' => 0,
            'rec_yds' => 0,
            'rec_tds' => 0,
            'fumbles' => 0
        ];
    }

    $result = mysqli_query($conn, "SELECT year, week_number, name, manager2_id FROM regular_season_matchups rsm
        JOIN managers ON rsm.manager1_id = managers.id
        WHERE year = $season
        ORDER BY week_number");
    while ($row = mysqli_fetch_array($result)) {
        $week = $row['week_number'];
        $opponent = $row['manager2_id'];


        $result2 = mysqli_query($conn, "SELECT manager, SUM(pass_yds) AS pass_yds, SUM(pass_tds) AS pass_tds, SUM(ints) AS ints, SUM(rush_yds) AS rush_yds, SUM(rush_tds) AS rush_tds,
            SUM(receptions) AS rec, SUM(rec_yds) AS rec_yds, SUM(rec_tds) AS rec_tds, SUM(fumbles) AS fum, SUM(fg_made) AS fg_made, SUM(pat_made) AS pat_made,
            SUM(def_sacks) AS def_sacks, SUM(def_int) AS def_int, SUM(def_fum) AS def_fum
            FROM rosters r
            JOIN managers m ON m.name = r.manager
            JOIN stats s ON s.roster_id = r.id
            WHERE YEAR = $season AND week = $week AND m.id = $opponent and roster_spot != 'BN'
            GROUP BY manager");
        while ($row2 = mysqli_fetch_array($result2)) {
            $response[$row['name']]['pass_yds'] += $row2['pass_yds'];
            $response[$row['name']]['pass_tds'] += $row2['pass_tds'];
            $response[$row['name']]['ints'] += $row2['ints'];
            $response[$row['name']]['rush_yds'] += $row2['rush_yds'];
            $response[$row['name']]['rush_tds'] += $row2['rush_tds'];
            $response[$row['name']]['receptions'] += $row2['rec'];
            $response[$row['name']]['rec_yds'] += $row2['rec_yds'];
            $response[$row['name']]['rec_tds'] += $row2['rec_tds'];
            $response[$row['name']]['fumbles'] += $row2['fum'];
        }
    }

    return $response;
}

function getCurrentSeasonTopPerformers($conn, $season)
{
    $response = [];

    $result = mysqli_query($conn, "SELECT * FROM rosters WHERE YEAR = $season ORDER BY points DESC LIMIT 1");
    while ($row = mysqli_fetch_array($result)) {
        $response['topPerformer'] = [
            'manager' => $row['manager'],
            'week' => $row['week'],
            'player' => $row['player'],
            'points' => round($row['points'], 1)
        ];
    }

    $result = mysqli_query($conn, "SELECT * FROM rosters WHERE YEAR = $season ORDER BY points - projected DESC LIMIT 1");
    while ($row = mysqli_fetch_array($result)) {
        $response['outperform'] = [
            'manager' => $row['manager'],
            'week' => $row['week'],
            'player' => $row['player'],
            'points' => round($row['points'] - $row['projected'], 1)
        ];
    }

    $result = mysqli_query($conn, "SELECT manager, (SUM(pass_tds)+SUM(rush_tds)+SUM(rec_tds)) AS total_tds
        FROM rosters
        JOIN stats ON stats.roster_id = rosters.id
        WHERE YEAR = $season
        GROUP BY manager
        ORDER BY total_tds DESC LIMIT 1");
    while ($row = mysqli_fetch_array($result)) {
        $response['mostTds'] = [
            'manager' => $row['manager'],
            'points' => $row['total_tds'],
        ];
    }

    $result = mysqli_query($conn, "SELECT manager, (SUM(pass_yds)+SUM(rush_yds)+SUM(rec_yds)) AS total_yds
        FROM rosters
        JOIN stats ON stats.roster_id = rosters.id
        WHERE YEAR = $season
        GROUP BY manager
        ORDER BY total_yds DESC LIMIT 1");
    while ($row = mysqli_fetch_array($result)) {
        $response['mostYds'] = [
            'manager' => $row['manager'],
            'points' => $row['total_yds'],
        ];
    }

    $result = mysqli_query($conn, "SELECT manager, SUM(points) AS bench_pts
        FROM rosters
        WHERE YEAR = $season AND roster_spot = 'BN'
        GROUP BY manager
        ORDER BY bench_pts DESC LIMIT 1");
    while ($row = mysqli_fetch_array($result)) {
        $response['bestBench'] = [
            'manager' => $row['manager'],
            'points' => round($row['bench_pts'], 1),
        ];
    }

    return $response;
}
