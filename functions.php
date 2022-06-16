<?php

include 'connections.php';


$result = mysqli_query($conn, "SELECT year FROM rosters ORDER BY year DESC LIMIT 1");
while ($row = mysqli_fetch_array($result)) {
    $season = $row['year'];
}

$result = mysqli_query($conn, "SELECT MAX(WEEK) AS maxweek FROM rosters WHERE YEAR = $season");
while ($row = mysqli_fetch_array($result)) {
    $week = $row['maxweek'];
}

if (!isset($pageName)) {
    $pageName = 'update';
} else {
    $ignore = ['User Activity', 'File Not Found', 'Update Database'];

    if (!in_array($pageName, $ignore)) {
        // Dumb idea, IP addresses change when users are on phones
        // saveUserActivity($pageName);
    }
}

if ($pageName == 'Dashboard') {
    $dashboardNumbers = getDashboardNumbers();
    $postseasonChart = getPostseasonChartNumbers();
}
if ((strpos($pageName, 'Profile') !== false)) {
    $profileNumbers = getProfileNumbers();
    $finishesChart = getFinishesChartNumbers();
    $seasonNumbers = getSeasonNumbers();
}
if ($pageName == 'Regular Season') {
    $regSeasonMatchups = getRegularSeasonMatchups();
    $seasonWins = getSeasonWins();
    $winsChart = getWinsChartNumbers();
    $scatterChart = getPointMargins();
    $pfwins = getPfWinsData();
}
if ($pageName == 'Postseason') {
    $postseasonMatchups = getPostseasonMatchups();
    $postseasonRecord = getPostseasonRecord();
}
if ($pageName == 'Draft') {
    $draftResults = getDraftResults();
}
if ((strpos($pageName, 'Recap') !== false)) {
    $regSeasonMatchups = getRegularSeasonMatchups();
    $postseasonMatchups = getPostseasonMatchups();
    $seasonNumbers = getAllNumbersBySeason();
    $draftResults = getDraftResults();
}
if ($pageName == 'Current Season') {
    $points = getCurrentSeasonPoints();
    $stats = getCurrentSeasonStats();
    $statsAgainst = getCurrentSeasonStatsAgainst();
    $bestWeek = getCurrentSeasonBestWeek();
    $topPerformers = getCurrentSeasonTopPerformers();
    $teamWeek = getCurrentSeasonBestTeamWeek();
    $optimal = getOptimalLineupPoints();
    $draftedPoints = getDraftPoints();
    $worstDraft = getWorstDraftPicks();
    $bestDraft = getBestDraftPicks();
    $everyoneRecord = getRecordAgainstEveryone();
    $draftPerformance = getAllDraftedPlayerDetails();
    $draftRounds = getBestRoundPicks();
}

function saveUserActivity($pageName)
{
    global $conn;

    try {
        // Lookup manager id by IP address
        $managerId = null;
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        $result = mysqli_query($conn, "SELECT manager_id from ip_addresses WHERE ip_address = '".$ipAddress."'");
        while ($row = mysqli_fetch_array($result)) {
            $managerId = $row['manager_id'];
        }

        // Save activity
        date_default_timezone_set('America/Los_Angeles');
        $timestamp = date('Y-m-d H:i:s');
        $sql = $conn->prepare("INSERT INTO user_activity (ip_address, manager_id, page, created_at) VALUES (?,?,?,?)");
        $sql->bind_param('siss', $ipAddress, $managerId, $pageName, $timestamp);
        $sql->execute();
    } catch (\Exception $ex) {
        // Do nothing, just don't fail on this
    }
}

/**
 * Undocumented function
 *
 * @return array
 */
function getDashboardNumbers()
{
    global $conn;
    $response = [];

    $result = mysqli_query($conn, "select count(distinct(year)) as num_years from finishes");
    while ($row = mysqli_fetch_array($result)) {
        $response['seasons'] = $row['num_years'];
    }

    $result = mysqli_query($conn, "SELECT count(distinct(manager_id)) as winners FROM finishes WHERE finish = 1");
    while ($row = mysqli_fetch_array($result)) {
        $response['unique_winners'] = $row['winners'];
    }

    $result = mysqli_query($conn, "SELECT name FROM finishes JOIN managers on managers.id = finishes.manager_id WHERE finish = 1 order by year desc limit 1");
    while ($row = mysqli_fetch_array($result)) {
        $response['defending_champ'] = $row['name'];
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

/**
 * Undocumented function
 *
 * @return array
 */
function getPostseasonChartNumbers()
{
    global $conn;
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

/**
 * Undocumented function
 *
 * @return array
 */
function getProfileNumbers()
{
    global $conn;
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

/**
 * Undocumented function
 *
 * @return array
 */
function getFinishesChartNumbers()
{
    global $conn;
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

/**
 * Undocumented function
 *
 * @return array
 */
function getSeasonNumbers()
{
    global $conn;
    $results = [];

    if (isset($_GET)) {

        $result = mysqli_query($conn, "SELECT * FROM finishes
            JOIN managers ON managers.id = finishes.manager_id
            JOIN team_names ON team_names.manager_id = managers.id AND team_names.year = finishes.year
            WHERE managers.name = '" . $_GET['id'] . "'");
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
                'pa' => $pa,
                'team_name' => $row['name'],
                'moves' => $row['moves'],
                'trades' => $row['trades']
            ];
        }
    } else {
        // redirect to index
    }

    return $results;
}

/**
 * Undocumented function
 *
 * @return array
 */
function getRegularSeasonMatchups()
{
    global $conn;
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

/**
 * Undocumented function
 *
 * @return array
 */
function getSeasonWins()
{
    global $conn;

    $response = [];
    $result = mysqli_query($conn, "SELECT * FROM finishes
        JOIN managers ON managers.id = finishes.manager_id");
    while ($row = mysqli_fetch_array($result)) {
        $managerId = $row['manager_id'];
        $managerName = strtolower($row['name']);
        $year = $row['year'];
        $wins = 0;
        $result2 = mysqli_query($conn, "SELECT * FROM regular_season_matchups
            WHERE manager1_id = " . $managerId . " AND year = " . $year);
        while ($row2 = mysqli_fetch_array($result2)) {
            if ($row2['manager1_score'] > $row2['manager2_score']) {
                $wins++;
            }
        }

        $response[$year][$managerName] = $wins;
    }

    return $response;
}

/**
 * Undocumented function
 *
 * @return array
 */
function getWinsChartNumbers()
{
    global $conn;
    $response = ['years' => ''];

    $result = mysqli_query($conn, "SELECT DISTINCT year FROM finishes");
    while ($row = mysqli_fetch_array($result)) {
        $response['years'] .= $row['year'].', ';
    }

    $result = mysqli_query($conn, "SELECT * FROM finishes
        JOIN managers ON managers.id = finishes.manager_id");
    while ($row = mysqli_fetch_array($result)) {
        $managerId = $row['manager_id'];
        $managerName = $row['name'];
        $year = $row['year'];
        $wins = 0;
        $result2 = mysqli_query($conn, "SELECT * FROM regular_season_matchups
            WHERE manager1_id = ".$managerId." AND year = ".$year);
        while ($row2 = mysqli_fetch_array($result2)) {
            if ($row2['manager1_score'] > $row2['manager2_score']) {
                $wins++;
            }
        }

        if (!isset($response['wins'][$managerName])) {
            $response['wins'][$managerName] = '';
        }

        $response['wins'][$managerName] .= $wins.', ';
    }

    $response['years'] = rtrim($response['years'], ', ');
    foreach ($response['wins'] as $team => &$wins) {
        // Add 2 blank years for andy and cam
        if ($team == 'Andy' || $team == 'Cameron') {
            $wins = ', ,'.$wins;
        }
        $wins = rtrim($wins, ', ');
    }

    return $response;
}

/**
 * Undocumented function
 *
 * @return array
 */
function getPointMargins()
{
    global $conn, $season;
    $response = [];
    $priorYear = null;

    $backFive = $season - 5;
    $result = mysqli_query($conn, "SELECT year, week_number, AVG(manager1_score) as average FROM regular_season_matchups rsm
        WHERE year > $backFive
        GROUP BY year, week_number");
    while ($row = mysqli_fetch_array($result)) {

        $year = $row['year'];
        $week = $row['week_number'];
        $average = $row['average'];
        $matchups = [];

        if ($year != $priorYear) {
            $yearWinMatchups = $yearLossMatchups = [];
        }
        $priorYear = $year;

        $result2 = mysqli_query($conn, "SELECT * FROM regular_season_matchups
            WHERE year = $year AND week_number = $week");
        while ($row2 = mysqli_fetch_array($result2)) {

            $win = $row2['manager1_score'] > $row2['manager2_score'];

            $matchups = [
                'x' => round($average - $row2['manager1_score'], 1),
                'y' => round($average - $row2['manager2_score'], 1)
            ];

            if ($win) {
                $yearWinMatchups[] = $matchups;
            } else {
                $yearLossMatchups[] = $matchups;
            }
        }

        $response[$year. ' Wins'] = $yearWinMatchups;
        $response[$year. ' Losses'] = $yearLossMatchups;
    }

    return $response;
}

/**
 * Undocumented function
 *
 * @return array
 */
function getPfWinsData()
{
    global $conn;
    $response = [];

    $result = mysqli_query($conn, "SELECT * FROM finishes
        JOIN managers ON managers.id = finishes.manager_id");
    while ($row = mysqli_fetch_array($result)) {
        $managerId = $row['manager_id'];
        $managerName = $row['name'];
        $year = $row['year'];
        $wins = $seasonPoints = $seasonPointsAgainst = 0;
        $array = [];

        $result2 = mysqli_query($conn, "SELECT * FROM regular_season_matchups
            WHERE manager1_id = " . $managerId . " AND year = " . $year);
        while ($row2 = mysqli_fetch_array($result2)) {
            if ($row2['manager1_score'] > $row2['manager2_score']) {
                $wins++;
            }
            $seasonPoints += $row2['manager1_score'];
            $seasonPointsAgainst += $row2['manager2_score'];

        }
        $array1[] = [
            'x' => $wins,
            'y' => round($seasonPoints,0)
        ];
        $array2[] = [
            'x' => $wins,
            'y' => round($seasonPointsAgainst, 0)
        ];

        $response['Points For'] = $array1;
        $response['Points Against'] = $array2;

    }

    return $response;
}

/**
 * Undocumented function
 *
 * @return array
 */
function getPostseasonMatchups()
{
    global $conn;
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

function getPostseasonRecord()
{
    global $conn;
    $response = [];

    $managers = ['Tyler', 'AJ', 'Gavin', 'Matt', 'Cameron', 'Andy', 'Everett', 'Justin', 'Cole', 'Ben'];
    foreach ($managers as $manager) {
        $response[$manager] = [
            'final_wins' => 0,
            'semi_wins' => 0,
            'quarter_wins' => 0,
            'final_losses' => 0,
            'semi_losses' => 0,
            'quarter_losses' => 0,
            'wins' => 0,
            'losses' => 0,
            'win_pct' => 0
        ];
    }

    $result = mysqli_query($conn, "SELECT
        SUM(if(ROUND = 'Final' and manager1_score > manager2_score, 1,0)) AS final_wins,
        SUM(if(ROUND = 'Semifinal' and manager1_score > manager2_score, 1,0)) AS semi_wins,
        SUM(if(ROUND = 'Quarterfinal' and manager1_score > manager2_score, 1,0)) AS quarter_wins,
        SUM(if(ROUND = 'Final' and manager1_score < manager2_score, 1,0)) AS final_losses,
        SUM(if(ROUND = 'Semifinal' and manager1_score < manager2_score, 1,0)) AS semi_losses,
        SUM(if(ROUND = 'Quarterfinal' and manager1_score < manager2_score, 1,0)) AS quarter_losses,
        SUM(if(manager1_score > manager2_score, 1,0)) AS wins,
        SUM(if(manager1_score < manager2_score, 1,0)) AS losses,
        name
        FROM playoff_matchups
        JOIN managers ON manager1_id = managers.id
        GROUP BY name
        UNION
        SELECT
        SUM(if(ROUND = 'Final' and manager2_score > manager1_score, 1,0)) AS final_wins,
        SUM(if(ROUND = 'Semifinal' and manager2_score > manager1_score, 1,0)) AS semi_wins,
        SUM(if(ROUND = 'Quarterfinal' and manager2_score > manager1_score, 1,0)) AS quarter_wins,
        SUM(if(ROUND = 'Final' and manager2_score < manager1_score, 1,0)) AS final_losses,
        SUM(if(ROUND = 'Semifinal' and manager2_score < manager1_score, 1,0)) AS semi_losses,
        SUM(if(ROUND = 'Quarterfinal' and manager2_score < manager1_score, 1,0)) AS quarter_losses,
        SUM(if(manager2_score > manager1_score, 1,0)) AS wins,
        SUM(if(manager2_score < manager1_score, 1,0)) AS losses,
        name
        FROM playoff_matchups
        JOIN managers ON manager2_id = managers.id
        GROUP BY name");
    while ($row = mysqli_fetch_array($result)) {
        $response[$row['name']]['name'] = $row['name'];
        $response[$row['name']]['final_wins'] += $row['final_wins'];
        $response[$row['name']]['semi_wins'] += $row['semi_wins'];
        $response[$row['name']]['quarter_wins'] += $row['quarter_wins'];
        $response[$row['name']]['final_losses'] += $row['final_losses'];
        $response[$row['name']]['semi_losses'] += $row['semi_losses'];
        $response[$row['name']]['quarter_losses'] += $row['quarter_losses'];
        $response[$row['name']]['wins'] += $row['wins'];
        $response[$row['name']]['losses'] += $row['losses'];
        $totalWins = $response[$row['name']]['wins'];
        $totalMatchups = $totalWins + $response[$row['name']]['losses'];
        $response[$row['name']]['win_pct'] = $totalWins / ($totalMatchups) * 100;
    }

    return $response;
}

/**
 * Undocumented function
 *
 * @return array
 */
function getDraftResults()
{
    global $conn;
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

/**
 * Undocumented function
 *
 * @return array
 */
function getAllNumbersBySeason()
{
    global $conn;
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

/**
 * Undocumented function
 *
 * @return array
 */
function getCurrentSeasonPoints()
{
    global $conn, $season;

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

/**
 * Undocumented function
 *
 * @return void
 */
function getCurrentSeasonStats()
{
    global $conn, $season;

    $result = mysqli_query($conn, "SELECT manager, SUM(pass_yds) AS pass_yds, SUM(pass_tds) AS pass_tds, SUM(ints) AS ints, SUM(rush_yds) AS rush_yds, SUM(rush_tds) AS rush_tds,
        SUM(receptions) AS rec, SUM(rec_yds) AS rec_yds, SUM(rec_tds) AS rec_tds, SUM(fumbles) AS fum, SUM(fg_made) AS fg_made, SUM(pat_made) AS pat_made,
        SUM(def_sacks) AS def_sacks, SUM(def_int) AS def_int, SUM(def_fum) AS def_fum
        FROM rosters r
        JOIN stats s ON s.roster_id = r.id
        WHERE YEAR = $season and roster_spot != 'BN'
        GROUP BY manager");

    return $result;
}

/**
 * Undocumented function
 *
 * @return array
 */
function getCurrentSeasonBestWeek()
{
    global $conn, $season;
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
        WHERE YEAR = $season
        GROUP BY week");
    while ($row = mysqli_fetch_array($result)) {
        $week = $row['WEEK'];

        $bestWeek[$week]['qb'] = queryBestWeekPlayer($week, $row['top_qb'], 'QB');
        $bestWeek[$week]['rb'] = queryBestWeekPlayer($week, $row['top_rb'], 'RB');
        $bestWeek[$week]['wr'] = queryBestWeekPlayer($week, $row['top_wr'], 'WR');
        $bestWeek[$week]['te'] = queryBestWeekPlayer($week, $row['top_te'], 'TE');
        $bestWeek[$week]['wrt'] = queryBestWeekPlayer($week, $row['top_wrt'], 'W/R/T');
        $bestWeek[$week]['qwrt'] = queryBestWeekPlayer($week, $row['top_qwrt'], 'Q/W/R/T');
        $bestWeek[$week]['k'] = queryBestWeekPlayer($week, $row['top_k'], 'K');
        $bestWeek[$week]['def'] = queryBestWeekPlayer($week, $row['top_def'], 'DEF');
        $bestWeek[$week]['bn'] = queryBestWeekPlayer($week, $row['top_bn'], 'BN');
    }

    return $bestWeek;
}

/**
 * Undocumented function
 *
 * @param [type] $week
 * @param [type] $pts
 * @param [type] $pos
 * @return array
 */
function queryBestWeekPlayer($week, $pts, $pos)
{
    global $conn;
    $response = [];

    $result = mysqli_query($conn, "SELECT * FROM rosters WHERE week = $week AND points = $pts and roster_spot = '$pos'");
    while ($row = mysqli_fetch_array($result)) {
        $response = [
            'manager' => $row['manager'],
            'player' => ($row['player']),
            'points' => round($pts, 1)
        ];
    }

    return $response;
}

/**
 * Undocumented function
 *
 * @return array
 */
function getCurrentSeasonStatsAgainst()
{
    global $conn, $season;
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

/**
 * Undocumented function
 *
 * @return array
 */
function getCurrentSeasonTopPerformers()
{
    global $conn, $season;
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

    $result = getBestDraftPicks();
    $response['bestDraftPick'] = [
        'manager' => $result[0]['manager'],
        'player' => $result[0]['player'],
        'points' => round($result[0]['points'], 1)
    ];

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

/**
 * Undocumented function
 *
 * @return array
 */
function getCurrentSeasonBestTeamWeek()
{
    global $conn, $season;
    $response = [];

    $result = mysqli_query($conn, "SELECT m.name as m1, l.name as m2, rsm.year, rsm.week_number, rsm.manager1_score, rsm.manager2_score
        FROM managers m
        JOIN regular_season_matchups rsm ON rsm.manager1_id = m.id
        LEFT JOIN (
        SELECT name, manager2_id, year, week_number, manager2_score FROM regular_season_matchups rsm2
            JOIN managers ON managers.id = rsm2.manager2_id
        ) l ON l.manager2_id = rsm.manager2_id AND l.year = rsm.year AND l.week_number = rsm.week_number
        WHERE rsm.year = $season
        ORDER BY rsm.manager1_score DESC");
    while ($row = mysqli_fetch_array($result)) {
        $response['best'][] = [
            'manager' => $row['m1'],
            'week' => $row['week_number'],
            'opponent' => $row['m2'],
            'points' => round($row['manager1_score'], 2),
            'result' => $row['manager1_score'] > $row['manager2_score'] ? 'Win' : 'Loss'
        ];
    }

    return $response;
}

/**
 * Undocumented function
 *
 * @return array
 */
function getDraftedPoints($dir, $round)
{
    global $conn, $season;
    $response = [];

    mysqli_query($conn, "SET SQL_BIG_SELECTS=1");

    $result = mysqli_query($conn, "SELECT rosters.manager, sum(points) as points FROM rosters
        JOIN managers ON rosters.manager = managers.name
        JOIN draft ON rosters.player LIKE CONCAT(draft.player, '%') AND managers.id = draft.manager_id AND rosters.year = draft.year
        WHERE rosters.year = $season AND roster_spot NOT IN ('BN', 'IR') and draft.round $dir $round
        GROUP BY manager");

    while ($row = mysqli_fetch_array($result)) {
        $response[] = [
            'manager' => $row['manager'],
            'points' => $row['points'],
        ];
    }

    return $response;
}

/**
 * Undocumented function
 *
 * @return array
 */
function getDraftPoints()
{
    global $conn, $week, $season;
    $response = [];

    $drafted = getDraftedPoints('>', 0);
    $lateRound = getDraftedPoints('>', 9);
    $earlyRound = getDraftedPoints('<', 6);

    $retained = [];
    $result = mysqli_query($conn, "SELECT manager, COUNT(rosters.player) as players FROM rosters
        JOIN managers ON rosters.manager = managers.name
        JOIN draft ON rosters.player LIKE CONCAT(draft.player, '%') AND managers.id = draft.manager_id AND rosters.year = draft.year
        WHERE rosters.year = $season AND WEEK = $week
        GROUP BY manager");
    while ($row = mysqli_fetch_array($result)) {
        $retained[] = $row;
    }

    $result = mysqli_query($conn, "SELECT rosters.manager, sum(points) AS points FROM rosters
        WHERE rosters.YEAR = $season AND roster_spot NOT IN ('BN', 'IR')
        GROUP BY manager");
    while ($row = mysqli_fetch_array($result)) {

        foreach ($drafted as $item) {
            if ($item['manager'] == $row['manager']) {
                $response[] = [
                    'manager' => $row['manager'],
                    'undrafted_points' => $row['points'] - $item['points'],
                    'drafted_points' => $item['points'],
                ];
            }
        }
    }

    foreach ($retained as $item) {
        foreach ($response as &$row) {
            if ($item['manager'] == $row['manager']) {
                $row['retained'] = $item['players'];
            }
        }
    }

    foreach ($lateRound as $item) {
        foreach ($response as &$row) {
            if ($item['manager'] == $row['manager']) {
                $row['late_round'] = $item['points'];
            }
        }
    }

    foreach ($earlyRound as $item) {
        foreach ($response as &$row) {
            if ($item['manager'] == $row['manager']) {
                $row['early_round'] = $item['points'];
            }
        }
    }

    return $response;
}

/**
 * Undocumented function
 *
 * @return array
 */
function getWorstDraftPicks()
{
    global $conn, $season;
    $response = [];

    mysqli_query($conn, "SET SQL_BIG_SELECTS=1");

    $qbMedian = getMedian('qb');
    $wrtMedian = getMedian('wrt');

    // Use multiplier to find sweet spot
    // Don't want to just be above average, but to be a bit worse than that
    $multiplier = .8;

    $result = mysqli_query($conn, "SELECT rosters.manager, draft.overall_pick, draft.position, rosters.player, sum(points) AS points FROM rosters
        JOIN managers ON rosters.manager = managers.name
        JOIN draft ON rosters.player LIKE CONCAT(draft.player, '%') AND managers.id = draft.manager_id AND rosters.year = draft.year
        WHERE rosters.YEAR = $season
        GROUP BY manager, overall_pick, player, position");
    while ($row = mysqli_fetch_array($result)) {

        if ($row['position'] == 'QB') {
            if ($row['points'] < ($qbMedian*$multiplier) && $row['overall_pick'] < 40) {
                $response[] = $row;
            } elseif ($row['points'] < $qbMedian && $row['overall_pick'] < 15) {
                // QBs drafted higher should be held to higher standard
                $response[] = $row;
            }
        } else {
            if ($row['points'] < ($wrtMedian*$multiplier) && $row['overall_pick'] < 70) {
                $response[] = $row;
            } elseif ($row['points'] < $wrtMedian && $row['overall_pick'] < 30) {
                $response[] = $row;
            }
        }
    }

    usort($response, function($a, $b) {
        return $a['points'] <=> $b['points'];
    });

    return array_slice($response,0,15);
}

function getMedian($pos)
{
    global $conn, $season, $week;

    $result = mysqli_query($conn, "SELECT position, avg(points) AS points 
        FROM rosters WHERE YEAR = $season
        GROUP BY position");
    while ($row = mysqli_fetch_array($result)) {
        $data[$row['position']] = $row['points'];
    }
    
    if ($pos == 'qb') {
        return $data['QB'] * $week;
    }
    $avg = ($data['WR'] + $data['TE'] + $data['RB']) / 3;

    return $avg * $week;
}

/**
 * Undocumented function
 *
 * @return array
 */
function getBestDraftPicks()
{
    global $conn, $season;
    $response = [];

    $qbMedian = getMedian('qb');
    $wrtMedian = getMedian('wrt');
    // Use multiplier to find sweet spot
    // Don't want to just be above average, but to be a bit better than that
    $multiplier = 1.4;

    $result = mysqli_query($conn, "SELECT rosters.manager, draft.overall_pick, draft.position, rosters.player, sum(points) AS points FROM rosters
        JOIN managers ON rosters.manager = managers.name
        JOIN draft ON rosters.player LIKE CONCAT(draft.player, '%') AND managers.id = draft.manager_id AND rosters.year = draft.year
        WHERE rosters.year = $season
        GROUP BY manager, overall_pick, player, position");
    while ($row = mysqli_fetch_array($result)) {

        if ($row['position'] == 'QB') {
            if ($row['points'] > ($qbMedian*$multiplier) && $row['overall_pick'] > 40) {
                $response[] = $row;
            }
        } else {
            if ($row['points'] > ($wrtMedian*$multiplier) && $row['overall_pick'] > 70) {
                $response[] = $row;
            }
        }
    }

    usort($response, function($a, $b) {
        return $b['points'] <=> $a['points'];
    });

    return array_slice($response,0,15);
}

/**
 * Undocumented function
 *
 * @return array
 */
function getPlayersRetained()
{
    global $conn, $week, $season;
    $response = [];

    $result = mysqli_query($conn, "SELECT manager, COUNT(rosters.player) as players FROM rosters
        JOIN managers ON rosters.manager = managers.name
        JOIN draft ON rosters.player LIKE CONCAT(draft.player, '%') AND managers.id = draft.manager_id AND rosters.year = draft.year
        WHERE rosters.year = $season AND WEEK = $week
        GROUP BY manager");
    while ($row = mysqli_fetch_array($result)) {
        $response[] = $row;
    }

    return $response;
}

/**
 * Undocumented function
 *
 * @return array
 */
function getRecordAgainstEveryone()
{
    global $conn, $season;
    $index = -1;

    $managers = [
        'AJ' => ['losses' => 0,'wins' => 0],
        'Ben' => ['losses' => 0,'wins' => 0],
        'Tyler' => ['losses' => 0, 'wins' => 0],
        'Matt' => ['losses' => 0, 'wins' => 0],
        'Justin' => ['losses' => 0, 'wins' => 0],
        'Andy' => ['losses' => 0, 'wins' => 0],
        'Cole' => ['losses' => 0, 'wins' => 0],
        'Everett' => ['losses' => 0, 'wins' => 0],
        'Cameron' => ['losses' => 0, 'wins' => 0],
        'Gavin' => ['losses' => 0, 'wins' => 0]
    ];
    $scores = [];
    $result = mysqli_query($conn, "SELECT week_number, name, manager1_score FROM regular_season_matchups rsm
        JOIN managers ON managers.id = rsm.manager1_id
        where year = $season
        ORDER BY year, week_number, manager1_score ASC");
    while ($row = mysqli_fetch_array($result)) {
        $scores[$season][$row['week_number']][$row['name']] = $row['manager1_score'];
    }
    foreach ($scores as $year => $weekArray) {
        foreach ($weekArray as $week) {
            $index = 0;
            foreach ($week as $manager => $value) {
                $managers[$manager]['wins'] += $index;
                $managers[$manager]['losses'] += 9 - $index;

                $index++;
            }
        }
    }

    return $managers;
}

function getAllDraftedPlayerDetails()
{
    global $conn, $season;
    $response = [];

    $result = mysqli_query($conn, "SELECT rosters.manager, draft.overall_pick, draft.position, draft.round, rosters.player,
        SUM(points) AS points, COUNT(rosters.player) AS GP
        FROM rosters
        JOIN managers ON rosters.manager = managers.name
        JOIN draft ON rosters.player LIKE CONCAT(draft.player, '%') AND managers.id = draft.manager_id AND rosters.year = draft.year
        WHERE rosters.year = $season AND rosters.roster_spot NOT IN ('BN','IR')
        GROUP BY manager, overall_pick, player, position, round");
    while ($row = mysqli_fetch_array($result)) {
        $response[] = $row;
    }

    return $response;
}

function getBestRoundPicks()
{
    $result = getAllDraftedPlayerDetails();

    $best = [];
    for ($x = 1; $x < 18; $x++) {
        $best[$x] = ['manager' => '', 'player' => '', 'points' => 0];
    }

    $qbMultiplier = .8;

    foreach ($result as $row) {
        $pts = $row['points'];
        if ($row['position'] == 'QB') {
            $pts = $pts * $qbMultiplier;
        }
        
        if ($pts > $best[$row['round']]['points']) {
            $best[$row['round']]['points'] = $pts;
            $best[$row['round']]['manager'] = $row['manager'];
            $best[$row['round']]['player'] = $row['player'];
        }
    }

    return $best;
}

/**
 * Undocumented function
 *
 * @return array
 */
function getOptimalLineupPoints()
{
    global $conn, $season;
    $response = [];

    $result1 = mysqli_query($conn, "SELECT distinct week FROM rosters WHERE YEAR = $season");
    while ($week = mysqli_fetch_array($result1)) {
        $week = $week['week'];

        $result2 = mysqli_query($conn, "SELECT distinct manager FROM rosters
            WHERE YEAR = $season AND week = $week");
        while ($manager = mysqli_fetch_array($result2)) {
            $manager = $manager['manager'];

            $projected = $points = 0;
            $roster = [];

            $result3 = mysqli_query($conn, "SELECT * FROM rosters
                WHERE YEAR = $season AND week = $week and manager = '".$manager."'");
            while ($row = mysqli_fetch_array($result3)) {

                $result4 = mysqli_query($conn, "SELECT * FROM regular_season_matchups
                    join managers on regular_season_matchups.manager1_id = managers.id
                    WHERE YEAR = $season AND week_number = $week and managers.name = '".$manager."'");
                while ($row2 = mysqli_fetch_array($result4)) {

                    $winLoss = ($row2['manager1_score'] > $row2['manager2_score']) ? 'Win' : 'Loss';
                    $manager2 = $row2['manager2_id'];

                    $opponentProjected = $opponentPoints = 0;
                    $opponentRoster = [];

                    $result5 = mysqli_query($conn, "SELECT * FROM managers
                        JOIN rosters on rosters.manager = managers.name
                        WHERE YEAR = $season AND week = $week and managers.id = $manager2");
                    while ($team = mysqli_fetch_array($result5)) {
                        $opponent = $team['name'];

                        $opponentRoster[] = [
                            'pos' => $team['position'],
                            'points' => (float)$team['points']
                        ];

                        if ($team['roster_spot'] != 'BN') {
                            $opponentProjected += $team['projected'];
                            $opponentPoints += $team['points'];
                        }
                    }
                }

                $roster[] = [
                    'pos' => $row['position'],
                    'points' => (float)$row['points']
                ];

                if ($row['roster_spot'] != 'BN') {
                    $projected += $row['projected'];
                    $points += $row['points'];
                }
            }

            $optimal = checkRosterForOptimal($roster);
            $opponentOptimal = checkRosterForOptimal($opponentRoster);

            $response[] = [
                'manager' => $manager,
                'week' => $week,
                'optimal' => round($optimal, 2),
                'points' => round($points, 2),
                'projected' => round($projected, 2),
                'result' => $winLoss,
                'opponent' => $opponent,
                'oppPoints' => round($opponentPoints, 2),
                'oppProjected' => round($opponentProjected, 2),
                'oppOptimal' => round($opponentOptimal, 2)
            ];
        }
    }

    return $response;
}

/**
 * Undocumented function
 *
 * @param array $roster
 * @return float
 */
function checkRosterForOptimal(array $roster)
{
    usort($roster, function($a, $b) {
        return $b['points'] <=> $a['points'];
    });

     $optimalRoster = [
        'qb' => 0,
        'rb1' => 0,
        'rb2' => 0,
        'wr1' => 0,
        'wr2' => 0,
        'wr3' => 0,
        'te' => 0,
        'wrt' => 0,
        'qwrt' => 0,
        'k' => 0,
        'def' => 0
    ];

    $fullRoster = 0;
    foreach ($roster as $player) {
        if ($fullRoster < 11) {
            if ($player['pos'] == 'QB') {
                if ($optimalRoster['qb'] == 0) {
                    $optimalRoster['qb'] = $player['points'];
                    $fullRoster++;
                } elseif ($optimalRoster['qwrt'] == 0) {
                    $optimalRoster['qwrt'] = $player['points'];
                    $fullRoster++;
                }
            } elseif ($player['pos'] == 'RB') {
                if ($optimalRoster['rb1'] == 0) {
                    $optimalRoster['rb1'] = $player['points'];
                    $fullRoster++;
                } elseif ($optimalRoster['rb2'] == 0) {
                    $optimalRoster['rb2'] = $player['points'];
                    $fullRoster++;
                } elseif ($optimalRoster['wrt'] == 0) {
                    $optimalRoster['wrt'] = $player['points'];
                    $fullRoster++;
                } elseif ($optimalRoster['qwrt'] == 0) {
                    $optimalRoster['qwrt'] = $player['points'];
                    $fullRoster++;
                }
            } elseif ($player['pos'] == 'WR') {
                if ($optimalRoster['wr1'] == 0) {
                    $optimalRoster['wr1'] = $player['points'];
                    $fullRoster++;
                } elseif ($optimalRoster['wr2'] == 0) {
                    $optimalRoster['wr2'] = $player['points'];
                    $fullRoster++;
                } elseif ($optimalRoster['wr3'] == 0) {
                    $optimalRoster['wr3'] = $player['points'];
                    $fullRoster++;
                } elseif ($optimalRoster['wrt'] == 0) {
                    $optimalRoster['wrt'] = $player['points'];
                    $fullRoster++;
                } elseif ($optimalRoster['qwrt'] == 0) {
                    $optimalRoster['qwrt'] = $player['points'];
                    $fullRoster++;
                }
            } elseif ($player['pos'] == 'TE') {
                if ($optimalRoster['te'] == 0) {
                    $optimalRoster['te'] = $player['points'];
                    $fullRoster++;
                } elseif ($optimalRoster['wrt'] == 0) {
                    $optimalRoster['wrt'] = $player['points'];
                    $fullRoster++;
                } elseif ($optimalRoster['qwrt'] == 0) {
                    $optimalRoster['qwrt'] = $player['points'];
                    $fullRoster++;
                }
            } elseif ($player['pos'] == 'K') {
                if ($optimalRoster['k'] == 0) {
                    $optimalRoster['k'] = $player['points'];
                    $fullRoster++;
                }
            } elseif ($player['pos'] == 'DEF') {
                if ($optimalRoster['def'] == 0) {
                    $optimalRoster['def'] = $player['points'];
                    $fullRoster++;
                }
            }
        }
    }
    $optimal = 0;
    foreach ($optimalRoster as $pos => $score) {
        $optimal += $score;
    }

    return $optimal;
}

function isfloat($val) 
{
    return ($val == (string)(float)$val);
}

function isDecimal($val)
{
    return is_numeric( $val ) && floor( $val ) != $val;
}

function dd($text)
{
    // var_dump($text);
    echo '<pre>';
    var_dump($text);
    echo '</pre>';
    die;
}

// Do DB update with mysql inserts
if(isset($_POST['sql-stmt'])) {
    $sql = $_POST['sql-stmt'];
    var_dump($sql);
    $allStatements = explode(';', $sql);

    foreach ($allStatements as $stmt) {

        if ($stmt != '') {
            $success = mysqli_query($conn, $stmt);

            if (!$success) {
                dd(mysqli_error($conn));
            }

            while ($row = mysqli_fetch_array($success)) {
                echo '<pre>';
                var_dump($row);
                echo '<pre>';
            }
        }
    }
    var_dump('Done');
}