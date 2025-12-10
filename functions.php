<?php

include 'connections.php';

// phpinfo(); exit;

function query($sql)
{
    global $conn;

    try {
        $run = $conn->query($sql);
    } catch (\Exception $e) {
        dd($e);
    }
    
    return $run;
}

function draft_query($sql)
{
    global $conn2;
    
    // Check if connection exists
    if ($conn2 === null) {
        return false;
    }

    try {
        return $conn2->query($sql);
    } catch (\Exception $e) {
        // Log the error and return false instead of throwing
        error_log("Draft query error: " . $e->getMessage());
        return false;
    }
}

function fetch_array($result)
{
    // Check if $result is a valid database result resource
    if ($result === false || $result === null) {
        // Return false instead of throwing an error
        return false;
    }

    return $result->fetchArray();
}

$result = query("SELECT year FROM rosters ORDER BY year DESC LIMIT 1");
while ($row = fetch_array($result)) {
    $season = $row['year'];
}

$result = query("SELECT MAX(WEEK) AS maxweek FROM rosters WHERE YEAR = $season");
while ($row = fetch_array($result)) {
    $week = $row['maxweek'];
}

if (!isset($pageName)) {
    $pageName = 'update';
}

if ((strpos($pageName, 'Profile') !== false)) {
    $profileNumbers = getProfileNumbers();
    $finishesChart = getFinishesChartNumbers();
    $seasonNumbers = getSeasonNumbers();
    $foes = getFoesArray();
    $winsChart = getProfileWinsChartNumbers();
    $postseasonWinsChart = getProfilePostseasonWinsChartNumbers();
    $allWeeks = getAllWeekOptions();
}

if ($pageName == 'Postseason') {
    $postseasonMatchups = getPostseasonMatchups();
    $postseasonRecord = getPostseasonRecord();
    $winsChart = getChampChartNumbers();
    $champions = getChampions();
}
if ($pageName == 'Schedule') {
    $selectedSeason = isset($_GET['id']) ? $_GET['id'] : $season;
    $scheduleData = getFullSchedule($selectedSeason);
}
if ($pageName == 'Draft') {
    $draftResults = getDraftResults();
    $draftSpotChart = getDraftChartNumbers();
}
if ((strpos($pageName, 'Recap') !== false)) {
    $regSeasonMatchups = getRegularSeasonMatchups();
    $postseasonMatchups = getPostseasonMatchups();
    $seasonNumbers = getAllNumbersBySeason();
    $draftResults = getDraftResults();
    $trades = getTrades();
    $weekStandings = getSeasonStandings();
}
if ($pageName == 'Current Season') {
    $selectedSeason = isset($_GET['id']) ? $_GET['id'] : $season;

    $points = getCurrentSeasonPoints();
    $stats = getCurrentSeasonStats();
    $weekStats = getCurrentSeasonWeekStats();
    $statsAgainst = getCurrentSeasonStatsAgainst();
    $weekStatsAgainst = getCurrentSeasonWeekStatsAgainst();
    $bestWeek = getCurrentSeasonBestWeek();
    $topPerformers = getCurrentSeasonTopPerformers();
    $teamWeek = getCurrentSeasonBestTeamWeek();
    $draftedPoints = getDraftPoints();
    $worstDraft = getWorstDraftPicks();
    $bestDraft = getBestDraftPicks();
    $everyoneRecord = getRecordAgainstEveryone();
    $draftPerformance = getAllDraftedPlayerDetails();
    $draftRounds = getBestRoundPicks();
    $scatterChart = getPointsForScatter();
    $weekStandings = getSeasonStandings($selectedSeason);
    $weeklyScores = getWeeklyScoresData();
}
if ($pageName == 'Rosters') {
    $recap = getMatchupRecapNumbers();
    $posPointsChart = getPositionPointsChartNumbers();
    $gameTimeChart = getGameTimeChartNumbers();
}
if ($pageName == 'Fact Finder') {
    $factFinderData = getFactFinderData();
    $availableWeeks = $factFinderData['availableWeeks'];
    $currentMatchups = $factFinderData['currentMatchups'];
}
if ($pageName == 'Newsletter') {
    $selectedSeason = isset($_GET['year']) ? $_GET['year'] : $season;
    $selectedWeek = isset($_GET['week']) ? $_GET['week'] : $week+1;

    // Check for rosters with the year and week
    $rosterQuery = query("SELECT * FROM rosters WHERE year = $selectedSeason AND week = $selectedWeek-1");
    $rosterData = fetch_array($rosterQuery);
    $rosterAvailable = !empty($rosterData);

    // Check for a newsletter
    $newsletterQuery = query("SELECT * FROM newsletters WHERE year = $selectedSeason AND week = $selectedWeek");
    $contentData = fetch_array($newsletterQuery);
    $contentAvailable = !empty($contentData);

    // Only load data if we're not in week 1 and have roster data
    if ($rosterAvailable) {
        $bestWeek = getCurrentSeasonBestWeek();
        $topPerformers = getCurrentSeasonTopPerformers();
        $stats = getCurrentSeasonStats();
        $weekStats = getCurrentSeasonWeekStats();
        $everyoneRecord = getRecordAgainstEveryone();
        $teamWeek = getCurrentSeasonBestTeamWeek();
        $weekStandings = getSeasonStandings($selectedSeason);
    } 
    
    // Load schedule info for all weeks
    $scheduleInfo = getScheduleInfo($selectedSeason, $selectedWeek);
}

function getManagerName($id) {
    $managers = ['Tyler', 'AJ', 'Gavin', 'Matt', 'Cameron', 'Andy', 'Everett', 'Justin', 'Cole', 'Ben'];

    return $managers[$id-1];
}

function getManagerId($name) {
    $managers = ['Tyler', 'AJ', 'Gavin', 'Matt', 'Cameron', 'Andy', 'Everett', 'Justin', 'Cole', 'Ben'];

    return array_search($name, $managers) + 1;
}

/**
 * Undocumented function
 */
function getProfileNumbers()
{
    $response = [];
    $managerId = 0;

    if (isset($_GET)) {

        $result = query("SELECT * FROM managers WHERE name = '" . $_GET['id'] . "'");
        while ($row = fetch_array($result)) {
            $managerId = $row['id'];
        }

        $result = query("SELECT name, wins, losses, total, (wins*1.0)/total AS win_pct
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
        while ($row = fetch_array($result)) {
            if ($row['name'] == $_GET['id']) {
                $response['record'] = $row['wins'] . " - " . $row['losses'];
                $response['recordRank'] = $rank;
            }

            $rank++;
        }

        $ships = 0;
        $years = '';
        $result = query("SELECT * FROM playoff_matchups WHERE manager1_id = $managerId OR manager2_id = $managerId");
        while ($row = fetch_array($result)) {
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
        $result = query("SELECT name, coalesce(winsTop, 0) as winsTop, winsBottom, lossesTop, lossesBottom, totalTop, totalBottom, (coalesce(winsTop, 0)+winsBottom) * 1.0/(totalTop+totalBottom) AS win_pct
            FROM managers
            LEFT JOIN (
                SELECT COUNT(manager1_id) AS winsTop, manager1_id FROM playoff_matchups
                WHERE manager1_score > manager2_score GROUP BY manager1_id
            ) w ON w.manager1_id = managers.id

            LEFT JOIN (
                SELECT COUNT(manager1_id) AS lossesTop, manager1_id FROM playoff_matchups
                WHERE manager1_score < manager2_score GROUP BY manager1_id
            ) l ON l.manager1_id = managers.id

            LEFT JOIN (
                SELECT COUNT(manager2_id) AS winsBottom, manager2_id FROM playoff_matchups
                WHERE manager2_score > manager1_score GROUP BY manager2_id
            ) w2 ON w2.manager2_id = managers.id

            LEFT JOIN (
                SELECT COUNT(manager2_id) AS lossesBottom, manager2_id FROM playoff_matchups
                WHERE manager2_score < manager1_score GROUP BY manager2_id
            ) l2 ON l2.manager2_id = managers.id

            LEFT JOIN (
                SELECT COUNT(manager1_id) AS totalTop, manager1_id FROM playoff_matchups
                GROUP BY manager1_id
            ) t ON t.manager1_id = managers.id

            LEFT JOIN (
                SELECT COUNT(manager2_id) as totalBottom, manager2_id FROM playoff_matchups
                GROUP BY manager2_id
            ) t2 ON t2.manager2_id = managers.id
            ORDER BY win_pct DESC");
        while ($row = fetch_array($result)) {
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
        $result = query("SELECT SUM(manager1_score) as total_points, manager1_id
            FROM regular_season_matchups
            GROUP BY manager1_id ORDER BY total_points DESC;");
        while ($row = fetch_array($result)) {
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
 */
function getFinishesChartNumbers()
{
    $results = ['years' => '', 'finishes' => '', 'regSeasons' => ''];

    if (isset($_GET)) {

        $result = query("SELECT * FROM finishes
            JOIN managers ON managers.id = finishes.manager_id
            WHERE name = '" . $_GET['id'] . "'");
        while ($row = fetch_array($result)) {
            $results['years'] .= $row['year'] . ',';
            $results['finishes'] .= $row['finish'] . ',';
        }

        $results['years'] = rtrim($results['years'], ',');
        $results['finishes'] = rtrim($results['finishes'], ',');

        // Get regular season standings at end of reg season
        $years = explode(',', $results['years']);
        $week = 14;
        
        foreach ($years as $year) {
            
            $standings = [];
            for ($x = 1; $x < 11; $x++) {
                $standings[] = [
                    'man' => $x, 'wins' => 0, 'losses' => 0, 'points' => 0, 'name' => ''
                ];
            }

            $result = query("SELECT * FROM regular_season_matchups 
                JOIN managers ON regular_season_matchups.manager1_id = managers.id
                WHERE year = $year and week_number <= $week");
            while ($row = fetch_array($result)) {
                foreach ($standings as &$standing) {
                    if ($standing['man'] == $row['manager1_id']) {
                        if ($row['winning_manager_id'] == $row['manager1_id']) {
                            $standing['wins']++;
                        } else {
                            $standing['losses']++;
                        }
                        $standing['name'] = $row['name'];
                        $standing['points'] += $row['manager1_score'];
                    }
                } 
            }
    
            // Sort by wins and points to get rank
            usort($standings, function($b, $a) { 
                $rdiff = $a['wins'] - $b['wins'];
                if ($rdiff) return $rdiff; 
    
                if ($a['points'] > $b['points']) {
                    return 1;
                } else if ($a['points'] < $b['points']) {
                    return -1;
                }
                return 0; 
            });

            $rank = 1;
            foreach ($standings as $data) {
                if ($data['name'] == $_GET['id']) {
                    $results['regSeasons'] .= $rank.',';
                }
                $rank++;
            }

        }
        $results['regSeasons'] = rtrim($results['regSeasons'], ',');
    } else {
        // redirect to index
    }

    return $results;
}

/**
 * Undocumented function
 */
function getSeasonNumbers()
{
    $results = [];

    if (isset($_GET)) {
        // Get all finishes for this manager
        $result = query("SELECT * FROM finishes
            JOIN managers ON managers.id = finishes.manager_id
            JOIN team_names ON team_names.manager_id = managers.id AND team_names.year = finishes.year
            WHERE managers.name = '" . $_GET['id'] . "'");
        $years_in_finishes = [];
        while ($row = fetch_array($result)) {
            $managerId = $row['manager_id'];
            $year = $row['year'];
            $years_in_finishes[] = $year;

            $pf = $pa = $wins = $losses = 0;
            $result2 = query("SELECT * FROM regular_season_matchups
                WHERE manager1_id = " . $managerId . " AND year = " . $year);
            while ($row2 = fetch_array($result2)) {
                $pf += $row2['manager1_score'];
                $pa += $row2['manager2_score'];
                if ($row2['manager1_score'] > $row2['manager2_score']) {
                    $wins++;
                } else {
                    $losses++;
                }
            }

            $winPct = $wins * 100 / ($wins + $losses);
            // Get seed from playoff_matchups
            $seed = null;
            $seedRes = query("SELECT manager1_seed FROM playoff_matchups WHERE year = $year AND manager1_id = $managerId LIMIT 1");
            $seedRow = fetch_array($seedRes);
            if (!empty($seedRow) && isset($seedRow['manager1_seed'])) {
                $seed = $seedRow['manager1_seed'];
            } else {
                $seedRes2 = query("SELECT manager2_seed FROM playoff_matchups WHERE year = $year AND manager2_id = $managerId LIMIT 1");
                $seedRow2 = fetch_array($seedRes2);
                if (!empty($seedRow2) && isset($seedRow2['manager2_seed'])) {
                    $seed = $seedRow2['manager2_seed'];
                }
            }
            $results[$year] = [
                'year' => $year,
                'finish' => $row['finish'],
                'record' => $wins . ' - ' . $losses,
                'win_pct' => round($winPct, 1),
                'pf' => $pf,
                'pa' => $pa,
                'team_name' => $row['name'],
                'moves' => $row['moves'],
                'trades' => $row['trades'],
                'seed' => $seed
            ];
        }

        // Now, check if current season is missing from finishes
        $currentSeasonRes = query("SELECT year FROM rosters ORDER BY year DESC LIMIT 1");
        $currentSeasonRow = fetch_array($currentSeasonRes);
        if ($currentSeasonRow && !in_array($currentSeasonRow['year'], $years_in_finishes)) {
            $currentYear = $currentSeasonRow['year'];
            // Get managerId for this manager
            $managerRes = query("SELECT id FROM managers WHERE name = '" . $_GET['id'] . "'");
            $managerRow = fetch_array($managerRes);
            $managerId = $managerRow ? $managerRow['id'] : null;
            // Get team name for current year
            $teamNameRes = query("SELECT name FROM team_names WHERE manager_id = $managerId AND year = $currentYear");
            $teamNameRow = fetch_array($teamNameRes);
            $teamName = $teamNameRow ? $teamNameRow['name'] : '';

            $pf = $pa = $wins = $losses = 0;
            $result2 = query("SELECT * FROM regular_season_matchups WHERE manager1_id = $managerId AND year = $currentYear");
            while ($row2 = fetch_array($result2)) {
                $pf += $row2['manager1_score'];
                $pa += $row2['manager2_score'];
                if ($row2['manager1_score'] > $row2['manager2_score']) {
                    $wins++;
                } else {
                    $losses++;
                }
            }
            $winPct = ($wins + $losses) > 0 ? $wins * 100 / ($wins + $losses) : 0;

            // Moves and finish and seed blank
            $results[$currentYear] = [
                'year' => $currentYear,
                'finish' => '',
                'record' => $wins . ' - ' . $losses,
                'win_pct' => round($winPct, 1),
                'pf' => $pf,
                'pa' => $pa,
                'team_name' => $teamName,
                'moves' => '',
                'trades' => '',
                'seed' => ''
            ];
        }
        // Badge logic removed; raw data only returned
    } else {
        // redirect to index
    }

    return $results;
}

/**
 * Calculate highs and lows against each manager for profile page
 */
function getFoesArray()
{
    $results = [
        'reg_season_matchups' => ['manager' => '', 'value' => ''],
        'reg_season_wins' => ['manager' => '', 'value' => ''],
        'reg_season_losses' => ['manager' => '', 'value' => ''],
        'postseason_matchups' => ['manager' => '', 'value' => ''],
        'postseason_wins' => ['manager' => '', 'value' => ''],
        'postseason_losses' => ['manager' => '', 'value' => ''],
        'overall_win_pct' => ['manager' => '', 'value' => ''],
        'total_pf' => ['manager' => '', 'value' => ''],
        'total_pa' => ['manager' => '', 'value' => ''],
        'average_pf' => ['manager' => '', 'value' => ''],
        'average_pa' => ['manager' => '', 'value' => ''],
        'biggest_win' => ['manager' => '', 'value' => ''],
        'biggest_loss' => ['manager' => '', 'value' => ''],
        'closest_win' => ['manager' => '', 'value' => ''],
        'closest_loss' => ['manager' => '', 'value' => '']
    ];

    $categories = array_keys($results);

    if (isset($_GET)) {

        $allFoes = [];
        $result = query("SELECT * FROM managers");
        while ($row = fetch_array($result)) {
            if ($row['name'] == $_GET['id']) {
                $managerId = $row['id'];
            } else {
                $allFoes[] = $row['id'];
            }
        }

        $allOpponents = [];
        foreach ($allFoes as $versus) {

            $data = [];
            $result = query("SELECT * FROM (
                SELECT year, week_number, manager1_id AS man1, manager2_id AS man2,
                manager1_score AS man1score, manager2_score AS man2score, winning_manager_id
                FROM regular_season_matchups
                WHERE manager1_id = $managerId
                AND manager2_id = $versus
            UNION
                SELECT year, round, manager1_id AS man1, manager2_id AS man2,
                manager1_score AS man1score, manager2_score AS man2score, CASE WHEN manager1_score > manager2_score THEN manager1_id ELSE manager2_id END
                FROM playoff_matchups
                WHERE (manager1_id = $managerId AND manager2_id = $versus)
            UNION
                SELECT year, round, manager2_id AS man2, manager1_id AS man1,
                manager2_score AS man1score, manager1_score AS man2score, CASE WHEN manager1_score > manager2_score THEN manager1_id ELSE manager2_id END
                FROM playoff_matchups
                WHERE (manager1_id = $versus AND manager2_id = $managerId)
            ) AS T");
            while ($row = fetch_array($result)) {
                $postseasonTypes = ['Final', 'Semifinal', 'Quarterfinal'];
                $data[] = [
                    'opponent' => getManagerName($versus),
                    'is_postseason' => in_array($row['week_number'], $postseasonTypes) ? true : false,
                    'week' => $row['week_number'],
                    'manager1_id' => $row['man1'],
                    'manager2_id' => $row['man2'],
                    'manager1_score' => $row['man1score'],
                    'manager2_score' => $row['man2score'],
                    'margin' => $row['man1score'] - $row['man2score'],
                    'winning_manager_id' => $row['winning_manager_id']
                ];
            }

            $totals = [
                'reg_season_matchups' => 0,'reg_season_wins' => 0,'reg_season_losses' => 0,
                'postseason_matchups' => 0,'postseason_wins' => 0,'postseason_losses' => 0,
                'overall_wins' => 0,'overall_matchups' => 0,
                'overall_win_pct' => 0,
                'total_pf' => 0,'total_pa' => 0,
                'average_pf' => 0,'average_pa' => 0,
                'biggest_win' => 0,'biggest_loss' => 0,
                'closest_win' => 417417,'closest_loss' => -417417
            ];
            foreach ($data as $match) {
                
                $totals['opponent'] = $match['opponent'];
                if ($match['is_postseason']) {
                    $totals['postseason_matchups']++;
                    $totals['postseason_wins'] += $match['winning_manager_id'] == $managerId ? 1 : 0;
                    $totals['postseason_losses'] += $match['winning_manager_id'] != $managerId ? 1 : 0;
                } else {
                    $totals['reg_season_matchups']++;
                    $totals['reg_season_wins'] += $match['winning_manager_id'] == $managerId ? 1 : 0;
                    $totals['reg_season_losses'] += $match['winning_manager_id'] != $managerId ? 1 : 0;
                }
                $totals['total_pf'] += $match['manager1_score'];
                $totals['total_pa'] += $match['manager2_score'];
                $totals['overall_wins'] += $match['winning_manager_id'] == $managerId ? 1 : 0;
                $totals['overall_matchups']++;
                $totals['overall_win_pct'] = round($totals['overall_wins'] * 100 / $totals['overall_matchups'], 1);
                $totals['average_pf'] = round($totals['total_pf'] / $totals['overall_matchups'], 1);
                $totals['average_pa'] = round($totals['total_pa'] / $totals['overall_matchups'], 1);
                if ($match['margin'] > $totals['biggest_win']) {
                    $totals['biggest_win'] = round($match['margin'], 2);
                }
                if ($match['margin'] < $totals['biggest_loss']) {
                    $totals['biggest_loss'] = round($match['margin'], 2);
                }
                if ($match['margin'] < $totals['closest_win'] && $match['margin'] > 0) {
                    $totals['closest_win'] = round($match['margin'], 2);
                }
                if ($match['margin'] > $totals['closest_loss'] && $match['margin'] < 0) {
                    $totals['closest_loss'] = round($match['margin'], 2);
                }
            }
            $allOpponents[] = $totals;
        }

        foreach ($categories as $category) {
            $results = sortAndAssign($allOpponents, $category, $results);
        }

    } else {
        // redirect to index
    }

    return $results;
}

function sortAndAssign(array $array, string $category, array $result)
{
    usort($array, function ($item1, $item2) use ($category) {
        return $item2[$category] <=> $item1[$category];
    });

    // Sort asc for these categories
    if ($category == "biggest_loss" || $category == "closest_win") {
        usort($array, function ($item1, $item2) use ($category) {
            return $item1[$category] <=> $item2[$category];
        });
    }

    // Account for ties
    $best = $result[$category]['value'] = $array[0][$category];
    $result[$category]['manager'] = $array[0]['opponent'];
    foreach ($array as $key => $opp) {
        if ($key != 0 && $opp[$category] == $best) {
            $result[$category]['manager'] .= ', '.$opp['opponent'];
            $result[$category]['value'] = $opp[$category];
        }
    }

    return $result;
}

/**
 * Undocumented function
 */
function getProfileWinsChartNumbers()
{
    $response = ['managers' => ''];
    $allFoes = $wins = [];
    $result = query("SELECT * FROM managers ORDER BY id ASC");
    while ($row = fetch_array($result)) {
        if ($row['name'] == $_GET['id']) {
            $managerId = $row['id'];
        } else {
            $allFoes[] = $row['name'];
        }
    }

    $response['managers'] = $allFoes;

    $result = query("SELECT name, SUM(CASE
            WHEN manager1_score > manager2_score THEN 1
            ELSE 0
        END) AS wins,
        SUM(CASE
            WHEN manager1_score < manager2_score THEN 1
            ELSE 0
        END) AS losses
        FROM regular_season_matchups rsm
        JOIN managers ON managers.id = rsm.manager2_id
        WHERE manager1_id = $managerId
        GROUP BY manager2_id
        ORDER BY managers.id ASC");
    while ($row = fetch_array($result)) {
        $wins[] = $row['wins'];
    }

    $response['wins'] = $wins;

    return $response;
}

/**
 * Undocumented function
 */
function getProfilePostseasonWinsChartNumbers()
{
    $response = ['managers' => ''];
    $allFoes = $wins = [];
    $result = query("SELECT * FROM managers ORDER BY id ASC");
    while ($row = fetch_array($result)) {
        if ($row['name'] == $_GET['id']) {
            $managerId = $row['id'];
        } else {
            $allFoes[] = $row['id'];
            $wins[$row['id']] = 0;
        }
    }

    $response['managers'] = $allFoes;
    $result = query("SELECT * from playoff_matchups pm
    WHERE manager1_id = $managerId or manager2_id = $managerId");
    while ($row = fetch_array($result)) {
        foreach ($allFoes as $foe) {
            if ($row['manager1_id'] == $foe) {
                $wins[$row['manager1_id']] += $row['manager2_score'] > $row['manager1_score'] ? 1 : 0;
            } elseif ($row['manager2_id'] == $foe) {
                $wins[$row['manager2_id']] += $row['manager1_score'] > $row['manager2_score'] ? 1 : 0;
            }
        }
    }

    $managers = [];
    foreach ($wins as $man => $winCount) {
        if ($winCount == 0) {
            unset($wins[$man]);
        } else {
            $result = query("SELECT * FROM managers where id = $man");
            while ($row = fetch_array($result)) {
                $managers[] = $row['name'];
            }
        }
    }

    $response['wins'] = array_values($wins);
    $response['managers'] = $managers;

    return $response;
}

function getAllWeekOptions()
{
    $result = query("SELECT DISTINCT week_number || '_' || year as week_id, 'Wk. ' || week_number || ' ' || year as week_display 
        FROM regular_season_matchups");
    $weeks = [];
    while ($row = fetch_array($result)) {
        $weeks[] = [
            'week_id' => $row['week_id'],
            'week_display' => $row['week_display']
        ];
    }

    return $weeks;
}

/**
 * Query for all regular season matchups
 */
function getRegularSeasonMatchups()
{
    $results = [];
    $prevWeek = $prevYear = 0;

    $result = query("SELECT m.name as m1, l.name as m2, rsm.year, rsm.week_number, rsm.manager1_score, rsm.manager2_score
        FROM managers m
        JOIN regular_season_matchups rsm ON rsm.manager1_id = m.id
        LEFT JOIN (
        SELECT name, manager2_id, year, week_number, manager2_score FROM regular_season_matchups rsm2
            JOIN managers ON managers.id = rsm2.manager2_id
        ) l ON l.manager2_id = rsm.manager2_id AND l.year = rsm.year AND l.week_number = rsm.week_number
        ORDER BY rsm.year, rsm.week_number ASC
        ");
    while ($row = fetch_array($result)) {

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

            $margin = abs($row['manager1_score'] - $row['manager2_score']);
            $combined = $row['manager1_score'] + $row['manager2_score'];
            $results[] = [
                'year' => $row['year'],
                'week' => $row['week_number'],
                'manager1' => $row['m1'],
                'manager2' => $row['m2'],
                'score1' => $row['manager1_score'],
                'score2' => $row['manager2_score'],
                'winner' => $winner,
                'margin' => round($margin, 2),
                'combined' => round($combined, 2)
            ];
        }

        $prevYear = $currentYear;
        $prevWeek = $currentWeek;
    }

    $results = getNotes($results, 'week');
    $results = getNotes($results, 'year');
    $results = getNotes($results, 'all time');

    return $results;
}

/**
 * Take regular season matchups and find notes about the scores
 */
function getNotes(array $matchups, string $period) 
{
    $groupBy = $return = [];

    if ($period == 'all time') {
        $groupBy['all'] = $matchups;
    } else {
        foreach ($matchups as &$element) {
            if (!isset($element['score1note'])) {
                $element['score1note'] = $element['score1noteSearch'] = '';
            }
            if (!isset($element['score2note'])) {
                $element['score2note'] = $element['score2noteSearch'] = '';
            }

            $groupBy[$element['year']][] = $element;
        }
    }

    if ($period == 'week') {
        $firstGroup = $groupBy;
        $groupBy = [];
        foreach ($firstGroup as $year => $matchups) {
            foreach ($matchups as $matchup) {
                $groupBy[$year.' - '.$matchup['week']][] = $matchup;
            }
        }
    }

    $period = ucwords($period);

    foreach ($groupBy as $group => $head) {
        $highest = 0;
        $lowest = 417417;
        // Find the highest and lowest score in the group
        foreach ($head as $element) {
            if ($element['score1'] > $highest) {
                $highest = $element['score1'];
            }
            if ($element['score2'] > $highest) {
                $highest = $element['score2'];
            }
            if ($element['score1'] < $lowest) {
                $lowest = $element['score1'];
            }
            if ($element['score2'] < $lowest) {
                $lowest = $element['score2'];
            }
        }

        foreach ($head as $key => $element) {
            if ($element['score1'] == $highest) {
                $head[$key]['score1note'] = '<span class="badge badge-primary" alt="all time high">'.$period.'<i class="icon-arrow-up"></i></span>';
                $head[$key]['score1noteSearch'] = $period.' high';
            }
            if ($element['score1'] == $lowest) {
                $head[$key]['score1note'] = '<span class="badge badge-secondary">'.$period.'<i class="icon-arrow-down"></i></span>';
                $head[$key]['score1noteSearch'] = $period.' low';
            }
            if ($element['score2'] == $highest) {
                $head[$key]['score2note'] = '<span class="badge badge-primary">'.$period.'<i class="icon-arrow-up"></i></span>';
                $head[$key]['score2noteSearch'] = $period.' high';
            }
            if ($element['score2'] == $lowest) {
                $head[$key]['score2note'] = '<span class="badge badge-secondary">'.$period.'<i class="icon-arrow-down"></i></span>';
                $head[$key]['score1noteSearch'] = $period.' low';
            }
        }

        $return = array_merge($return, $head);
    }

    return $return;
}

/**
 * Undocumented function
 */
function getPostseasonMatchups()
{
    $results = [];

    $result = query("SELECT m.name as m1, l.name as m2, rsm.year, rsm.round, rsm.manager1_seed, rsm.manager2_seed, rsm.manager1_score, rsm.manager2_score
        FROM managers m
        JOIN playoff_matchups rsm ON rsm.manager1_id = m.id
        LEFT JOIN (
        SELECT name, manager2_id, year, round, manager2_score FROM playoff_matchups rsm2
            JOIN managers ON managers.id = rsm2.manager2_id
        ) l ON l.manager2_id = rsm.manager2_id AND l.year = rsm.year AND l.round = rsm.round
        ORDER BY rsm.year, rsm.round ASC");
    while ($row = fetch_array($result)) {

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
            'margin' => abs($row['manager1_score']-$row['manager2_score']),
            'winner' => $winner,
            'm1seed' => $row['manager1_seed'],
            'm2seed' => $row['manager2_seed'],
            'sort' => $sort
        ];
    }

    return $results;
}

/**
 * Get postseason record for each manager separated by playoff round
 */
function getPostseasonRecord()
{
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

    $result = query("SELECT
        SUM(CASE WHEN ROUND = 'Final' and manager1_score > manager2_score THEN 1 ELSE 0 END) AS final_wins,
        SUM(CASE WHEN ROUND = 'Semifinal' and manager1_score > manager2_score THEN 1 ELSE 0 END) AS semi_wins,
        SUM(CASE WHEN ROUND = 'Quarterfinal' and manager1_score > manager2_score THEN 1 ELSE 0 END) AS quarter_wins,
        SUM(CASE WHEN ROUND = 'Final' and manager1_score < manager2_score THEN 1 ELSE 0 END) AS final_losses,
        SUM(CASE WHEN ROUND = 'Semifinal' and manager1_score < manager2_score THEN 1 ELSE 0 END) AS semi_losses,
        SUM(CASE WHEN ROUND = 'Quarterfinal' and manager1_score < manager2_score THEN 1 ELSE 0 END) AS quarter_losses,
        SUM(CASE WHEN manager1_score > manager2_score THEN 1 ELSE 0 END) AS wins,
        SUM(CASE WHEN manager1_score < manager2_score THEN 1 ELSE 0 END) AS losses,
        name
        FROM playoff_matchups
        JOIN managers ON manager1_id = managers.id
        GROUP BY name
        UNION
        SELECT
        SUM(CASE WHEN ROUND = 'Final' and manager2_score > manager1_score THEN 1 ELSE 0 END) AS final_wins,
        SUM(CASE WHEN ROUND = 'Semifinal' and manager2_score > manager1_score THEN 1 ELSE 0 END) AS semi_wins,
        SUM(CASE WHEN ROUND = 'Quarterfinal' and manager2_score > manager1_score THEN 1 ELSE 0 END) AS quarter_wins,
        SUM(CASE WHEN ROUND = 'Final' and manager2_score < manager1_score THEN 1 ELSE 0 END) AS final_losses,
        SUM(CASE WHEN ROUND = 'Semifinal' and manager2_score < manager1_score THEN 1 ELSE 0 END) AS semi_losses,
        SUM(CASE WHEN ROUND = 'Quarterfinal' and manager2_score < manager1_score THEN 1 ELSE 0 END) AS quarter_losses,
        SUM(CASE WHEN manager2_score > manager1_score THEN 1 ELSE 0 END) AS wins,
        SUM(CASE WHEN manager2_score < manager1_score THEN 1 ELSE 0 END) AS losses,
        name
        FROM playoff_matchups
        JOIN managers ON manager2_id = managers.id
        GROUP BY name");
    while ($row = fetch_array($result)) {
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

function getChampChartNumbers()
{
    $wins = $managers = [];

    $result = query("SELECT SUM(CASE WHEN finish = 1 THEN 1 ELSE 0 END) as wins, name
        FROM finishes
        JOIN managers ON managers.id = finishes.manager_id
        GROUP BY name
        ORDER BY wins DESC");
    while ($row = fetch_array($result)) {
        if ($row['wins'] == 0) {
            continue;
        }
        $wins[] = $row['wins'];
        $managers[] = $row['name'];
    }

    $response['managers'] = $managers;
    $response['wins'] = $wins;

    return $response;
}

function getChampions()
{
    $champions = [];
    
    // Get all champions with basic info
    $result = query("SELECT f.year, f.manager_id, m.name 
        FROM finishes f 
        JOIN managers m ON f.manager_id = m.id 
        WHERE f.finish = 1 
        ORDER BY f.year");
    
    while ($row = fetch_array($result)) {
        $year = $row['year'];
        $managerId = $row['manager_id'];
        $name = $row['name'];
        
        $champion = [
            'year' => $year,
            'manager_id' => $managerId,
            'name' => $name,
            'draft_pick' => 'N/A',
            'trades' => 0,
            'top_draft_pick' => 'N/A',
            'top_add' => 'N/A',
            'record' => 'N/A',
            'seed' => 'N/A'
        ];
        
        // Get draft pick (first round pick position)
        $draftResult = query("SELECT overall_pick FROM draft 
            WHERE year = $year AND manager_id = $managerId AND round = 1");
        if ($draftRow = fetch_array($draftResult)) {
            $champion['draft_pick'] = $draftRow['overall_pick'];
        }
        
        // Count trades/moves for the season (unique trade identifiers)
        $tradesResult = query("SELECT COUNT(DISTINCT trade_identifier) as trade_count 
            FROM trades 
            WHERE year = $year AND (manager_from_id = $managerId OR manager_to_id = $managerId)");
        if ($tradesRow = fetch_array($tradesResult)) {
            $champion['trades'] = $tradesRow['trade_count'];
        }
        
        // Get top drafted player (highest scoring drafted player)
        $topDraftResult = query("SELECT d.player, SUM(r.points) as total_points 
            FROM draft d 
            LEFT JOIN player_aliases pa ON d.player = pa.player 
                OR d.player = pa.alias_1 
                OR d.player = pa.alias_2 
                OR d.player = pa.alias_3
            LEFT JOIN rosters r ON (
                (r.player = d.player OR 
                 r.player = pa.player OR 
                 r.player = pa.alias_1 OR 
                 r.player = pa.alias_2 OR 
                 r.player = pa.alias_3)
                AND d.year = r.year AND d.manager_id = (
                    SELECT id FROM managers WHERE name = r.manager
                )
            )
            WHERE d.year = $year AND d.manager_id = $managerId 
            GROUP BY d.player 
            ORDER BY total_points DESC 
            LIMIT 1");
        if ($topDraftRow = fetch_array($topDraftResult)) {
            $champion['top_draft_pick'] = $topDraftRow['player'] . ' (' . round($topDraftRow['total_points'], 1) . ' pts)';
        }
        
        // Get top waiver/free agent pickup (highest scoring non-drafted player)
        $topAddResult = query("SELECT r.player, SUM(r.points) as total_points 
            FROM rosters r 
            WHERE r.year = $year AND r.manager = '$name' 
            AND r.player NOT IN (
                SELECT DISTINCT COALESCE(pa.player, d.player) 
                FROM draft d 
                LEFT JOIN player_aliases pa ON d.player = pa.player 
                    OR d.player = pa.alias_1 
                    OR d.player = pa.alias_2 
                    OR d.player = pa.alias_3
                WHERE d.year = $year
            )
            GROUP BY r.player 
            ORDER BY total_points DESC 
            LIMIT 1");
        if ($topAddRow = fetch_array($topAddResult)) {
            $champion['top_add'] = $topAddRow['player'] . ' (' . round($topAddRow['total_points'], 1) . ' pts)';
        }
        
        // Get regular season record
        $wins = 0;
        $losses = 0;
        
        $recordResult = query("SELECT 
            SUM(CASE WHEN manager1_score > manager2_score THEN 1 ELSE 0 END) as wins,
            SUM(CASE WHEN manager1_score < manager2_score THEN 1 ELSE 0 END) as losses
            FROM regular_season_matchups 
            WHERE year = $year AND manager1_id = $managerId");
        
        while ($recordRow = fetch_array($recordResult)) {
            $wins += $recordRow['wins'];
            $losses += $recordRow['losses'];
        }
        
        $champion['record'] = $wins . '-' . $losses;
        
        // Get playoff seed
        $seedResult = query("SELECT manager1_seed, manager2_seed, manager1_id, manager2_id
            FROM playoff_matchups 
            WHERE year = $year AND (manager1_id = $managerId OR manager2_id = $managerId)
            ORDER BY round 
            LIMIT 1");
        if ($seedRow = fetch_array($seedResult)) {
            if ($seedRow['manager1_id'] == $managerId) {
                $champion['seed'] = $seedRow['manager1_seed'];
            } else {
                $champion['seed'] = $seedRow['manager2_seed'];
            }
        }
        
        $champions[] = $champion;
    }
    
    return $champions;
}

/**
 * Get players drafted
 */
function getDraftResults()
{
    $results = [];

    $result = query("SELECT draft.year, round, round_pick, overall_pick, position, draft.player, name, points 
        FROM draft
        JOIN managers m ON m.id = draft.manager_id
        LEFT JOIN (SELECT sum(points) as points, YEAR, player
            FROM rosters r GROUP BY r.year, r.player) AS rosters 
        ON draft.player = rosters.player and draft.year = rosters.year");
    while ($row = fetch_array($result)) {
        $results[] = $row;
    }

    return $results;
}

/**
 * Query draft data for draft position by year chart
 */
function getDraftChartNumbers()
{
    $response = ['years' => ''];

    $result = query("SELECT DISTINCT year FROM draft ORDER BY year ASC");
    while ($row = fetch_array($result)) {
        $response['years'] .= $row['year'].', ';
    }

    $result = query("SELECT * FROM draft
        JOIN managers ON managers.id = draft.manager_id
        WHERE round = 1
        ORDER BY year ASC");
    while ($row = fetch_array($result)) {
        $managerName = $row['name'];
        $spot = $row['overall_pick'];

        if (!isset($response['spot'][$managerName])) {
            $response['spot'][$managerName] = '';
        }

        $response['spot'][$managerName] .= $spot.', ';
    }

    $response['years'] = rtrim($response['years'], ', ');
    foreach ($response['spot'] as $team => &$spot) {
        // Add 2 blank years for andy and cam
        if ($team == 'Andy' || $team == 'Cameron') {
            $spot = ', ,'.$spot;
        }
        $spot = rtrim($spot, ', ');
    }

    return $response;
}

/**
 * Undocumented function
 */
function getAllNumbersBySeason()
{
    $results = [];

    if (isset($_GET['id'])) {
        $season = $_GET['id'];
    } else {
        $result = query("SELECT DISTINCT year FROM finishes ORDER BY year DESC LIMIT 1");
        while ($row = fetch_array($result)) {
            $season = $row['year'];
        }
    }

    $result = query("SELECT finishes.manager_id, finishes.year, managers.name as manager_name,
        team_names.name AS team_name, moves, finish, trades
        FROM finishes
        JOIN managers ON managers.id = finishes.manager_id
        JOIN team_names ON managers.id = team_names.manager_id AND finishes.year = team_names.year
        WHERE finishes.YEAR  = '" . $season . "'");
    while ($row = fetch_array($result)) {
        $managerId = $row['manager_id'];
        $year = $row['year'];

        $pf = $pa = $wins = $losses = 0;
        $result2 = query("SELECT * FROM regular_season_matchups
            WHERE manager1_id = " . $managerId . " AND year = " . $year);
        while ($row2 = fetch_array($result2)) {
            $pf += $row2['manager1_score'];
            $pa += $row2['manager2_score'];
            if ($row2['winning_manager_id'] == $managerId) {
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
 * Query for trades based on season selected
 */
function getTrades()
{
    $results = [];

    if (isset($_GET['id'])) {
        $season = $_GET['id'];
    } else {
        $result = query("SELECT DISTINCT year FROM finishes ORDER BY year DESC LIMIT 1");
        while ($row = fetch_array($result)) {
            $season = $row['year'];
        }
    }

    $result = query("SELECT m.name as m1, l.name as m2, trades.year, player, trade_identifier, week
        FROM trades 
        LEFT JOIN managers m ON trades.manager_from_id = m.id
        LEFT JOIN (
            SELECT trades.id, name, manager_to_id, year FROM trades 
                JOIN managers ON managers.id = trades.manager_to_id
            ) l ON l.manager_to_id = trades.manager_to_id AND l.id = trades.id
        WHERE trades.year = $season
        ORDER BY trade_identifier, m1");
    while ($row = fetch_array($result)) {

        $pointsBefore = $pointsAfter = 0;
        $player = $row['player'];
        // Look up player's points before trade week
        $result2 = query('SELECT sum(points) as points FROM rosters
            WHERE player = "'.$player.'" AND year = '.$row["year"].' AND week < '.$row["week"]);
        while ($row2 = fetch_array($result2)) {
            if ($row2['points'] != null) {
                $pointsBefore = $row2['points'];
            }
        }

        // Look up player's points after trade week
        $result2 = query('SELECT sum(points) as points FROM rosters
            WHERE player = "'.$player.'" AND year = '.$row["year"].' AND week >= '.$row["week"]);
        while ($row2 = fetch_array($result2)) {
            if ($row2['points'] != null) {
                $pointsAfter = $row2['points'];
            }
        }

        $row['points_before'] = $pointsBefore;
        $row['points_after'] = $pointsAfter;
        $results[] = $row;
    }

    return $results;
}

function getSeasonStandings($season = null)
{
    if (isset($_GET['id'])) {
        $season = $_GET['id'];
    } elseif (!$season) {
        $result = query("SELECT DISTINCT year FROM finishes ORDER BY year DESC LIMIT 1");
        while ($row = fetch_array($result)) {
            $season = $row['year'];
        }
    }

    $result = query("SELECT * FROM regular_season_matchups 
        WHERE year = $season ORDER BY week_number DESC LIMIT 1");
    while ($row = fetch_array($result)) {
        $lastWeek = $row['week_number'];
    }
    $standings = [];
    for ($week = 1; $week <= $lastWeek; $week++) {
        $standings[] = weekStandings($season, $week);
    }

    $weeks = range(1, $lastWeek);
    $managers = [];
    foreach ($standings as $wk => $rankings) {
        foreach ($rankings as $man => $rank) {
            $managers[$man][] = $rank;
        }
    }

    $return = [];
    $colors = ["#9c68d9","#a6c6fa","#3cf06e","#f33c47","#c0f6e6","#def89f","#dca130","#ff7f2c","#ecb2b6"," #f87598"];
    $i = 0;
    foreach ($managers as $name => $ranks) {
        $return[] = [
            'label' => $name, 
            'data' => $ranks,
            'borderColor' => $colors[$i],
            'backgroundColor' => $colors[$i],
            'pointStyle' => 'circle',
            'pointRadius' => 5,
            'pointHoverRadius' => 7
        ];
        $i++;
    }

    return [
        'weeks' => $weeks,
        'managers' => $return
    ];
}

function weekStandings(int $year, int $week)
{
    $return = [];

    // Get standings from the standings table for the specified week
    $result = query("SELECT s.rank, m.name
        FROM standings s
        JOIN managers m ON s.manager_id = m.id
        WHERE s.year = $year AND s.week = $week
        ORDER BY s.rank ASC");
    
    while ($row = fetch_array($result)) {
        $return[$row['name']] = $row['rank'];
    }

    return $return;
}

/**
 * Undocumented function
 */
function getCurrentSeasonPoints()
{
    global $selectedSeason;

    $points = [];
    $managers = ['Tyler', 'Matt', 'Justin', 'Ben', 'AJ', 'Gavin', 'Cameron', 'Cole', 'Everett', 'Andy'];
    foreach ($managers as $manager) {
        $points[$manager]['BN'] = [
            'projected' => 0,
            'points' => 0
        ];
    }

    $result = query("SELECT manager, roster_spot, SUM(points) AS points, SUM(projected) AS projected FROM rosters r
        WHERE YEAR = $selectedSeason
        GROUP BY manager, roster_spot");
    while ($row = fetch_array($result)) {

        if ($row['roster_spot'] == 'BN' || $row['roster_spot'] == 'IR') {
            $points[$row['manager']]['BN']['projected'] += round($row['projected'] ?? 0, 1);
            $points[$row['manager']]['BN']['points'] += round($row['points'] ?? 0, 1);

        } else {
            $points[$row['manager']][$row['roster_spot']] = [
                'projected' => round($row['projected'] ?? 0, 1),
                'points' => round($row['points'] ?? 0, 1)
            ];
        }
    }

    // Arrange in order based on posOrder
    $posOrder = ['QB', 'RB', 'WR', 'TE', 'W/R/T', 'W/R', 'W/T', 'Q/W/R/T', 'K', 'DEF', 'D', 'DL', 'DB', 'BN', 'IR'];
    foreach ($points as $manager => &$point) {
        $orderedPoints = [];
        foreach ($posOrder as $pos) {
            if (isset($point[$pos])) {
                $orderedPoints[$pos] = $point[$pos];
            }
        }
        $point = $orderedPoints;
    }

    return $points;
}

/**
 * Get total season stats by manager for Current Season page
 */
function getCurrentSeasonStats()
{
    global $selectedSeason;

    $result = query("SELECT manager, SUM(pass_yds) AS pass_yds, SUM(pass_tds) AS pass_tds, SUM(ints) AS ints, SUM(rush_yds) AS rush_yds, SUM(rush_tds) AS rush_tds,
        SUM(receptions) AS rec, SUM(rec_yds) AS rec_yds, SUM(rec_tds) AS rec_tds, SUM(fumbles) AS fum, SUM(fg_made) AS fg_made, SUM(pat_made) AS pat_made
        FROM rosters r
        JOIN stats s ON s.roster_id = r.id
        WHERE YEAR = $selectedSeason and roster_spot != 'BN' and roster_spot != 'IR'
        GROUP BY manager");

    return $result;
}

/**
 * Get season stats by manager and week for Current Season page
 */
function getCurrentSeasonWeekStats()
{
    global $selectedSeason;

    $result = query("SELECT manager, week, SUM(pass_yds) AS pass_yds, SUM(pass_tds) AS pass_tds, SUM(ints) AS ints, SUM(rush_yds) AS rush_yds, SUM(rush_tds) AS rush_tds,
        SUM(receptions) AS rec, SUM(rec_yds) AS rec_yds, SUM(rec_tds) AS rec_tds, SUM(fumbles) AS fum, SUM(fg_made) AS fg_made, SUM(pat_made) AS pat_made
        FROM rosters r
        JOIN stats s ON s.roster_id = r.id
        WHERE YEAR = $selectedSeason and roster_spot != 'BN' and roster_spot != 'IR'
        GROUP BY manager, week");

    return $result;
}

/**
 * Get the best performance of each week for each position
 */
function getCurrentSeasonBestWeek()
{
    global $selectedSeason;
    $bestWeek = [];
    $result = query("SELECT week, MAX(CASE WHEN roster_spot='QB' THEN points ELSE NULL END) AS top_qb,
        MAX(CASE WHEN roster_spot='RB' THEN points ELSE NULL END) AS top_rb,
        MAX(CASE WHEN roster_spot='WR' THEN points ELSE NULL END) AS top_wr,
        MAX(CASE WHEN roster_spot='TE' THEN points ELSE NULL END) AS top_te,
        MAX(CASE WHEN roster_spot='W/R' THEN points ELSE NULL END) AS top_wrflex,
        MAX(CASE WHEN roster_spot='W/T' THEN points ELSE NULL END) AS top_wtflex,
        MAX(CASE WHEN roster_spot='W/R/T' THEN points ELSE NULL END) AS top_wrt,
        MAX(CASE WHEN roster_spot='Q/W/R/T' THEN points ELSE NULL END) AS top_qwrt,
        MAX(CASE WHEN roster_spot='K' THEN points ELSE NULL END) AS top_k,
        MAX(CASE WHEN roster_spot='DEF' THEN points ELSE NULL END) AS top_def,
        MAX(CASE WHEN roster_spot='DB' THEN points ELSE NULL END) AS top_db,
        MAX(CASE WHEN roster_spot='D' THEN points ELSE NULL END) AS top_d,
        MAX(CASE WHEN roster_spot='BN' THEN points ELSE NULL END) AS top_bn
        FROM rosters
        WHERE rosters.year = $selectedSeason
        GROUP BY week");
    while ($row = fetch_array($result)) {
        $week = $row['week'];

        $bestWeek[$week]['QB'] = queryBestWeekPlayer($week, $row['top_qb'], 'QB');
        $bestWeek[$week]['RB'] = queryBestWeekPlayer($week, $row['top_rb'], 'RB');
        $bestWeek[$week]['WR'] = queryBestWeekPlayer($week, $row['top_wr'], 'WR');
        $bestWeek[$week]['TE'] = queryBestWeekPlayer($week, $row['top_te'], 'TE');

        if ($row['top_wrt'])
        $bestWeek[$week]['W/R/T'] = queryBestWeekPlayer($week, $row['top_wrt'], 'W/R/T');
        if ($row['top_wrflex'])
        $bestWeek[$week]['W/R'] = queryBestWeekPlayer($week, $row['top_wrflex'], 'W/R');
        if ($row['top_wtflex'])
        $bestWeek[$week]['W/T'] = queryBestWeekPlayer($week, $row['top_wtflex'], 'W/T');
        if ($row['top_qwrt'])
        $bestWeek[$week]['Q/W/R/T'] = queryBestWeekPlayer($week, $row['top_qwrt'], 'Q/W/R/T');
        if ($row['top_db'])
        $bestWeek[$week]['DB'] = queryBestWeekPlayer($week, $row['top_db'], 'DB');
        if ($row['top_d'])
        $bestWeek[$week]['D'] = queryBestWeekPlayer($week, $row['top_d'], 'D');

        $bestWeek[$week]['K'] = queryBestWeekPlayer($week, $row['top_k'], 'K');
        $bestWeek[$week]['DEF'] = queryBestWeekPlayer($week, $row['top_def'], 'DEF');
        $bestWeek[$week]['BN'] = queryBestWeekPlayer($week, $row['top_bn'], 'BN');
    }

    return $bestWeek;
}

/**
 * Run query for best week by roster spot
 */
function queryBestWeekPlayer($week, $pts, $pos)
{
    global $selectedSeason;
    $response = [];
    if (!$pts || $pts == 'Bye') {
        return ['manager' => '', 'player' => '', 'points' => ''];
    }

    $result = query("SELECT * FROM rosters 
        WHERE year = $selectedSeason AND week = $week AND points = $pts and roster_spot = '$pos'");
    while ($row = fetch_array($result)) {
        $response = [
            'manager' => $row['manager'],
            'player' => ($row['player']),
            'points' => round($pts, 1)
        ];
    }

    return $response;
}

/**
 * Compile all stats against
 */
function getCurrentSeasonStatsAgainst()
{
    global $selectedSeason;
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

    $result = query("SELECT year, week_number, name, manager2_id FROM regular_season_matchups rsm
        JOIN managers ON rsm.manager1_id = managers.id
        WHERE year = $selectedSeason
        ORDER BY week_number");
    while ($row = fetch_array($result)) {
        $week = $row['week_number'];
        $opponent = $row['manager2_id'];

        $result2 = query("SELECT manager, SUM(pass_yds) AS pass_yds, SUM(pass_tds) AS pass_tds, SUM(ints) AS ints, SUM(rush_yds) AS rush_yds, SUM(rush_tds) AS rush_tds,
            SUM(receptions) AS rec, SUM(rec_yds) AS rec_yds, SUM(rec_tds) AS rec_tds
            FROM rosters r
            JOIN managers m ON m.name = r.manager
            JOIN stats s ON s.roster_id = r.id
            WHERE YEAR = $selectedSeason AND week = $week AND m.id = $opponent and roster_spot != 'BN'
            GROUP BY manager");
        while ($row2 = fetch_array($result2)) {
            $response[$row['name']]['pass_yds'] += $row2['pass_yds'];
            $response[$row['name']]['pass_tds'] += $row2['pass_tds'];
            $response[$row['name']]['ints'] += $row2['ints'];
            $response[$row['name']]['rush_yds'] += $row2['rush_yds'];
            $response[$row['name']]['rush_tds'] += $row2['rush_tds'];
            $response[$row['name']]['receptions'] += $row2['rec'];
            $response[$row['name']]['rec_yds'] += $row2['rec_yds'];
            $response[$row['name']]['rec_tds'] += $row2['rec_tds'];
        }
    }
    if ($response['Tyler']['pass_yds'] == 0) {
        return [];
    }

    return $response;
}

/**
 * Compile all stats against by week
 */
function getCurrentSeasonWeekStatsAgainst()
{
    global $selectedSeason;

    $weekOpponents = [];
    $result = query("SELECT year, week_number, name, manager2_id FROM regular_season_matchups rsm
        JOIN managers ON rsm.manager1_id = managers.id
        WHERE year = $selectedSeason
        ORDER BY week_number");
    while ($row = fetch_array($result)) {
        $weekOpponents[$row['week_number']][$row['name']] = $row['manager2_id'];
    }
    
    $response = [];
    $result = query("SELECT manager, week, SUM(pass_yds) AS pass_yds, SUM(pass_tds) AS pass_tds, SUM(ints) AS ints, SUM(rush_yds) AS rush_yds, SUM(rush_tds) AS rush_tds,
        SUM(receptions) AS receptions, SUM(rec_yds) AS rec_yds, SUM(rec_tds) AS rec_tds, SUM(fumbles) AS fum, SUM(fg_made) AS fg_made, SUM(pat_made) AS pat_made
        FROM rosters r
        JOIN stats s ON s.roster_id = r.id
        WHERE YEAR = $selectedSeason and roster_spot != 'BN' and roster_spot != 'IR'
        GROUP BY manager, week");
    while ($row = fetch_array($result)) {
        $opponent = $weekOpponents[$row['week']][$row['manager']];
        $row['manager'] = getManagerName($opponent);
        $response[] = $row;
    }

    return $response;
}

/**
 * Undocumented function
 */
function getCurrentSeasonTopPerformers()
{
    global $selectedSeason;
    $response = [
        'mostTds' => [
            'manager' => 'Stats Not Available',
            'points' => '',
        ],
        'mostYds' => [
            'manager' => 'Stats Not Available',
            'points' => '',
        ]
    ];

    $result = query("SELECT * FROM rosters WHERE YEAR = $selectedSeason ORDER BY points DESC LIMIT 1");
    while ($row = fetch_array($result)) {
        $response['topPerformer'] = [
            'manager' => $row['manager'],
            'week' => $row['week'],
            'player' => $row['player'],
            'points' => round((int)$row['points'], 1)
        ];
    }

    $result = getBestDraftPicks();
    $response['bestDraftPick'] = [
        'manager' => $result[0]['manager'],
        'player' => $result[0]['player'],
        'points' => round($result[0]['points'], 1)
    ];

    $result = query("SELECT manager, (SUM(pass_tds)+SUM(rush_tds)+SUM(rec_tds)) AS total_tds
        FROM rosters
        JOIN stats ON stats.roster_id = rosters.id
        WHERE YEAR = $selectedSeason
        GROUP BY manager
        ORDER BY total_tds DESC LIMIT 1");
    while ($row = fetch_array($result)) {
        $response['mostTds'] = [
            'manager' => $row['manager'],
            'points' => $row['total_tds'],
        ];
    }

    $result = query("SELECT manager, (SUM(pass_yds)+SUM(rush_yds)+SUM(rec_yds)) AS total_yds
        FROM rosters
        JOIN stats ON stats.roster_id = rosters.id
        WHERE YEAR = $selectedSeason
        GROUP BY manager
        ORDER BY total_yds DESC LIMIT 1");
    while ($row = fetch_array($result)) {
        $response['mostYds'] = [
            'manager' => $row['manager'],
            'points' => $row['total_yds'],
        ];
    }

    $result = query("SELECT manager, SUM(points) AS bench_pts
        FROM rosters
        WHERE YEAR = $selectedSeason AND (roster_spot = 'BN' or roster_spot = 'IR')
        GROUP BY manager
        ORDER BY bench_pts DESC LIMIT 1");
    while ($row = fetch_array($result)) {
        $response['bestBench'] = [
            'manager' => $row['manager'],
            'points' => round($row['bench_pts'], 1),
        ];
    }

    return $response;
}

/**
 * Calculate optimal lineups for each team
 */
function getCurrentSeasonBestTeamWeek()
{
    global $selectedSeason;
    $response = [];

    $result = query("SELECT m.name as m1, l.name as m2, rsm.year, rsm.week_number, rsm.manager1_score, rsm.manager2_score
        FROM managers m
        JOIN regular_season_matchups rsm ON rsm.manager1_id = m.id
        LEFT JOIN (
        SELECT name, manager2_id, year, week_number, manager2_score FROM regular_season_matchups rsm2
            JOIN managers ON managers.id = rsm2.manager2_id
        ) l ON l.manager2_id = rsm.manager2_id AND l.year = rsm.year AND l.week_number = rsm.week_number
        WHERE rsm.year = $selectedSeason
        ORDER BY rsm.manager1_score DESC");
    while ($row = fetch_array($result)) {
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
 */
function getDraftedPoints($dir, $round)
{
    global $selectedSeason;
    $response = [];

    $query = "SELECT manager, sum(points) as points FROM (
        SELECT r.manager, sum(r.points) as points
        FROM rosters r
        JOIN managers m ON r.manager = m.name
        JOIN draft d ON d.manager_id = m.id AND d.year = r.year AND r.player = d.player
        WHERE r.year = $selectedSeason 
            AND r.roster_spot NOT IN ('BN', 'IR') 
            AND d.round $dir $round
        GROUP BY r.manager
        UNION
        SELECT r.manager, sum(r.points) as points
        FROM rosters r
        JOIN managers m ON r.manager = m.name
        JOIN draft d ON d.manager_id = m.id AND d.year = r.year
        JOIN player_aliases pa ON d.player = pa.player 
            OR d.player = pa.alias_1 
            OR d.player = pa.alias_2 
            OR d.player = pa.alias_3
        WHERE r.year = $selectedSeason 
            AND r.roster_spot NOT IN ('BN', 'IR') 
            AND d.round $dir $round
            AND (r.player = pa.player OR 
                 r.player = pa.alias_1 OR 
                 r.player = pa.alias_2 OR 
                 r.player = pa.alias_3)
        GROUP BY r.manager
    ) combined
    GROUP BY manager";

    $result = query($query);

    while ($row = fetch_array($result)) {
        $response[] = [
            'manager' => $row['manager'],
            'points' => $row['points'],
        ];
    }

    return $response;
}

/**
 * Calculate points earned from drafted players
 */
function getDraftPoints()
{
    global $selectedSeason;
    $response = [];

    $drafted = getDraftedPoints('>', 0);
    $lateRound = getDraftedPoints('>', 9);
    $earlyRound = getDraftedPoints('<', 6);

    $result = query("SELECT MAX(WEEK) AS maxweek FROM rosters WHERE YEAR = $selectedSeason");
    while ($row = fetch_array($result)) {
        $week = $row['maxweek'];
    }

    $retained = [];
    $query = "SELECT manager, COUNT(player) as players FROM (
        SELECT r.manager, r.player
        FROM rosters r
        JOIN managers m ON r.manager = m.name
        JOIN draft d ON d.manager_id = m.id AND d.year = r.year AND r.player = d.player
        WHERE r.year = $selectedSeason AND r.week = $week
        UNION
        SELECT r.manager, r.player
        FROM rosters r
        JOIN managers m ON r.manager = m.name
        JOIN draft d ON d.manager_id = m.id AND d.year = r.year
        JOIN player_aliases pa ON d.player = pa.player 
            OR d.player = pa.alias_1 
            OR d.player = pa.alias_2 
            OR d.player = pa.alias_3
        WHERE r.year = $selectedSeason AND r.week = $week
            AND (r.player = pa.player OR 
                 r.player = pa.alias_1 OR 
                 r.player = pa.alias_2 OR 
                 r.player = pa.alias_3)
    ) combined
    GROUP BY manager";
    $result = query($query);
    while ($row = fetch_array($result)) {
        $retained[] = $row;
    }

    // Get undrafted points directly - players in rosters but NOT in draft
    $undraftedQuery = "SELECT r.manager, sum(r.points) as undrafted_points
        FROM rosters r
        JOIN managers m ON r.manager = m.name
        WHERE r.year = $selectedSeason AND r.roster_spot NOT IN ('BN', 'IR')
        AND NOT EXISTS (
            SELECT 1 FROM draft d 
            WHERE d.manager_id = m.id AND d.year = r.year AND d.player = r.player
        )
        AND NOT EXISTS (
            SELECT 1 FROM draft d
            JOIN player_aliases pa ON d.player = pa.player 
                OR d.player = pa.alias_1 
                OR d.player = pa.alias_2 
                OR d.player = pa.alias_3
            WHERE d.manager_id = m.id AND d.year = r.year
                AND (r.player = pa.player OR 
                     r.player = pa.alias_1 OR 
                     r.player = pa.alias_2 OR 
                     r.player = pa.alias_3)
        )
        GROUP BY r.manager";
    
    $undraftedResult = query($undraftedQuery);
    $undraftedPoints = [];
    while ($row = fetch_array($undraftedResult)) {
        $undraftedPoints[$row['manager']] = $row['undrafted_points'];
    }

    // Combine drafted and undrafted points
    foreach ($drafted as $item) {
        $response[] = [
            'manager' => $item['manager'],
            'undrafted_points' => isset($undraftedPoints[$item['manager']]) ? $undraftedPoints[$item['manager']] : 0,
            'drafted_points' => $item['points'],
        ];
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
 * Query and logic for worst draft picks based on points and pick number
 */
function getWorstDraftPicks()
{
    global $selectedSeason;
    $response = [];

    $response = [];
    $year = $selectedSeason;
    
    $qbMedian = getMedian($year, 'QB');
    $qbAvgPick = getAveragePick($year, 'QB');
    $rbMedian = getMedian($year, 'RB');
    $rbAvgPick = getAveragePick($year, 'RB');
    $wrMedian = getMedian($year, 'WR');
    $wrAvgPick = getAveragePick($year, 'WR');
    $teMedian = getMedian($year, 'TE');
    $teAvgPick = getAveragePick($year, 'TE');
    $defMedian = getMedian($year, 'DEF');
    $defAvgPick = getAveragePick($year, 'DEF');
    $kMedian = getMedian($year, 'K');
    $kAvgPick = getAveragePick($year, 'K');

    $sql = "SELECT r.manager, m.id as manager_id, d.overall_pick, d.position, 
        COALESCE(pa1.player, pa2.player, d.player) as player, sum(r.points) AS points, d.year
        FROM rosters r
        JOIN managers m ON r.manager = m.name
        JOIN draft d ON d.manager_id = m.id AND d.year = r.year
        LEFT JOIN player_aliases pa1 ON d.player = pa1.player 
            OR d.player = pa1.alias_1 
            OR d.player = pa1.alias_2 
            OR d.player = pa1.alias_3
        LEFT JOIN player_aliases pa2 ON r.player = pa2.player 
            OR r.player = pa2.alias_1 
            OR r.player = pa2.alias_2 
            OR r.player = pa2.alias_3
        WHERE r.year = $year
            AND (r.player = d.player OR 
                 r.player = pa1.player OR 
                 r.player = pa1.alias_1 OR 
                 r.player = pa1.alias_2 OR 
                 r.player = pa1.alias_3 OR
                 d.player = pa2.player OR 
                 d.player = pa2.alias_1 OR 
                 d.player = pa2.alias_2 OR 
                 d.player = pa2.alias_3)
        GROUP BY r.manager, d.overall_pick, COALESCE(pa1.player, pa2.player, d.player), d.position";

    $result = query($sql);

    while ($row = fetch_array($result)) {
        $row['year'] = $year;

        if ($row['position'] == 'QB') {
            $row['points_diff'] = $row['points'] - $qbMedian;
            $row['pick_diff'] = $row['overall_pick'] - $qbAvgPick;
            $row['median'] = $qbMedian;
            $row['avg_pick'] = $qbAvgPick;
        } elseif ($row['position'] == 'RB') {
            $row['points_diff'] = $row['points'] - $rbMedian;
            $row['pick_diff'] = $row['overall_pick'] - $rbAvgPick;
            $row['median'] = $rbMedian;
            $row['avg_pick'] = $rbAvgPick;
        } elseif ($row['position'] == 'WR') {
            $row['points_diff'] = $row['points'] - $wrMedian;
            $row['pick_diff'] = $row['overall_pick'] - $wrAvgPick;
            $row['median'] = $wrMedian;
            $row['avg_pick'] = $wrAvgPick;
        } elseif ($row['position'] == 'TE') {
            $row['points_diff'] = $row['points'] - $teMedian;
            $row['pick_diff'] = $row['overall_pick'] - $teAvgPick;
            $row['median'] = $teMedian;
            $row['avg_pick'] = $teAvgPick;
        } elseif ($row['position'] == 'DEF') {
            $row['points_diff'] = $row['points'] - $defMedian;
            $row['pick_diff'] = $row['overall_pick'] - $defAvgPick;
            $row['median'] = $defMedian;
            $row['avg_pick'] = $defAvgPick;
        } elseif ($row['position'] == 'K') {
            $row['points_diff'] = $row['points'] - $kMedian;
            $row['pick_diff'] = $row['overall_pick'] - $kAvgPick;
            $row['median'] = $kMedian;
            $row['avg_pick'] = $kAvgPick;
        } else {
            continue;
        }

        $row['score'] = $row['points_diff'] + $row['pick_diff'];
        $response[] = $row;
    }
    
    usort($response, function($a, $b) {
        return $a['score'] <=> $b['score'];
    });

    return array_slice($response,0,15);
}

/**
 * Undocumented function
 */
function getBestDraftPicks()
{
    global $selectedSeason;
    $response = [];
    $year = $selectedSeason;
    
    $qbMedian = getMedian($year, 'QB');
    $qbAvgPick = getAveragePick($year, 'QB');
    $rbMedian = getMedian($year, 'RB');
    $rbAvgPick = getAveragePick($year, 'RB');
    $wrMedian = getMedian($year, 'WR');
    $wrAvgPick = getAveragePick($year, 'WR');
    $teMedian = getMedian($year, 'TE');
    $teAvgPick = getAveragePick($year, 'TE');
    $defMedian = getMedian($year, 'DEF');
    $defAvgPick = getAveragePick($year, 'DEF');
    $kMedian = getMedian($year, 'K');
    $kAvgPick = getAveragePick($year, 'K');

    $sql = "SELECT r.manager, m.id as manager_id, d.overall_pick, d.position, 
        COALESCE(pa1.player, pa2.player, d.player) as player, sum(r.points) AS points, d.year
        FROM rosters r
        JOIN managers m ON r.manager = m.name
        JOIN draft d ON d.manager_id = m.id AND d.year = r.year
        LEFT JOIN player_aliases pa1 ON d.player = pa1.player 
            OR d.player = pa1.alias_1 
            OR d.player = pa1.alias_2 
            OR d.player = pa1.alias_3
        LEFT JOIN player_aliases pa2 ON r.player = pa2.player 
            OR r.player = pa2.alias_1 
            OR r.player = pa2.alias_2 
            OR r.player = pa2.alias_3
        WHERE r.year = $year
            AND (r.player = d.player OR 
                 r.player = pa1.player OR 
                 r.player = pa1.alias_1 OR 
                 r.player = pa1.alias_2 OR 
                 r.player = pa1.alias_3 OR
                 d.player = pa2.player OR 
                 d.player = pa2.alias_1 OR 
                 d.player = pa2.alias_2 OR 
                 d.player = pa2.alias_3)
        GROUP BY r.manager, d.overall_pick, COALESCE(pa1.player, pa2.player, d.player), d.position";

    $result = query($sql);

    while ($row = fetch_array($result)) {
        $row['year'] = $year;

        if ($row['position'] == 'QB') {
            $row['points_diff'] = $row['points'] - $qbMedian;
            $row['pick_diff'] = $row['overall_pick'] - $qbAvgPick;
            $row['median'] = $qbMedian;
            $row['avg_pick'] = $qbAvgPick;
        } elseif ($row['position'] == 'RB') {
            $row['points_diff'] = $row['points'] - $rbMedian;
            $row['pick_diff'] = $row['overall_pick'] - $rbAvgPick;
            $row['median'] = $rbMedian;
            $row['avg_pick'] = $rbAvgPick;
        } elseif ($row['position'] == 'WR') {
            $row['points_diff'] = $row['points'] - $wrMedian;
            $row['pick_diff'] = $row['overall_pick'] - $wrAvgPick;
            $row['median'] = $wrMedian;
            $row['avg_pick'] = $wrAvgPick;
        } elseif ($row['position'] == 'TE') {
            $row['points_diff'] = $row['points'] - $teMedian;
            $row['pick_diff'] = $row['overall_pick'] - $teAvgPick;
            $row['median'] = $teMedian;
            $row['avg_pick'] = $teAvgPick;
        } elseif ($row['position'] == 'DEF') {
            $row['points_diff'] = $row['points'] - $defMedian;
            $row['pick_diff'] = $row['overall_pick'] - $defAvgPick;
            $row['median'] = $defMedian;
            $row['avg_pick'] = $defAvgPick;
        } elseif ($row['position'] == 'K') {
            $row['points_diff'] = $row['points'] - $kMedian;
            $row['pick_diff'] = $row['overall_pick'] - $kAvgPick;
            $row['median'] = $kMedian;
            $row['avg_pick'] = $kAvgPick;
        } else {
            continue;
        }

        if ($row['points'] <= $row['median']) {
            continue;
        }
        $row['score'] = $row['points_diff'] + $row['pick_diff'];
        $response[] = $row;
    }
    
    usort($response, function($a, $b) {
        return $b['score'] <=> $a['score'];
    });

    return array_slice($response,0,15);
}

/**
 * Find the average score by position
 */
function getMedian($season, string $pos)
{
    $result = query("SELECT player, sum(points) AS points
        FROM rosters
        WHERE year = $season AND position = '$pos'
        AND roster_spot NOT IN ('BN', 'IR')
        GROUP BY player");

    $total = $count = 0;
    while ($row = fetch_array($result)) {
        $total += $row['points'];
        $count++;
    }

    return $count > 0 ? $total / $count : 0;
}

/**
 * Find the average overall_pick by position
 */
function getAveragePick($season, string $pos)
{
    $result = query("SELECT position, avg(overall_pick) AS overall_pick
        FROM draft
        WHERE year = $season AND position = '$pos'
        GROUP BY position");
    
    while ($row = fetch_array($result)) {
        return $row['overall_pick'];
    }
    
    return 0; // Return 0 if no records found
}

/**
 * Undocumented function
 */
function getPlayersRetained()
{
    global $week, $selectedSeason;
    $response = [];

    $result = query("SELECT manager, COUNT(player) as players FROM (
        SELECT r.manager, r.player
        FROM rosters r
        JOIN managers m ON r.manager = m.name
        JOIN draft d ON d.manager_id = m.id AND d.year = r.year AND r.player = d.player
        WHERE r.year = $selectedSeason AND r.week = $week
        UNION
        SELECT r.manager, r.player
        FROM rosters r
        JOIN managers m ON r.manager = m.name
        JOIN draft d ON d.manager_id = m.id AND d.year = r.year
        JOIN player_aliases pa ON d.player = pa.player 
            OR d.player = pa.alias_1 
            OR d.player = pa.alias_2 
            OR d.player = pa.alias_3
        WHERE r.year = $selectedSeason AND r.week = $week
            AND (r.player = pa.player OR 
                 r.player = pa.alias_1 OR 
                 r.player = pa.alias_2 OR 
                 r.player = pa.alias_3)
    ) retained
    GROUP BY manager");
    while ($row = fetch_array($result)) {
        $response[] = $row;
    }

    return $response;
}

/**
 * Calculate record if we had played against everyone every week
 */
function getRecordAgainstEveryone()
{
    global $selectedSeason;
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
    $result = query("SELECT week_number, name, manager1_score FROM regular_season_matchups rsm
        JOIN managers ON managers.id = rsm.manager1_id
        where year = $selectedSeason
        ORDER BY year, week_number, manager1_score ASC");
    while ($row = fetch_array($result)) {
        $scores[$selectedSeason][$row['week_number']][$row['name']] = $row['manager1_score'];
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

/**
 * Get the details of drafted players in a specific season
 */
function getAllDraftedPlayerDetails()
{
    global $selectedSeason;
    $response = [];

    $result = query("SELECT managers.name as manager, draft.overall_pick, draft.position, draft.round, draft.player,
        SUM(COALESCE(rosters.points, 0)) AS points, SUM(IF(rosters.roster_spot NOT IN ('BN','IR'), 1, 0)) AS GP
        FROM draft
        JOIN managers ON draft.manager_id = managers.id 
        LEFT JOIN player_aliases pa ON draft.player = pa.player 
            OR draft.player = pa.alias_1 
            OR draft.player = pa.alias_2 
            OR draft.player = pa.alias_3
        LEFT JOIN rosters ON (
            (rosters.player = draft.player OR 
             rosters.player = pa.player OR 
             rosters.player = pa.alias_1 OR 
             rosters.player = pa.alias_2 OR 
             rosters.player = pa.alias_3)
            AND rosters.year = draft.year 
            AND rosters.manager = managers.name
        )
        WHERE draft.year = $selectedSeason
        GROUP BY managers.name, draft.overall_pick, draft.player, draft.position, draft.round
        ORDER BY draft.overall_pick asc");
    
    $response = [];
    while ($row = fetch_array($result)) {
        $response[] = $row;
    }

    return $response;
}

/**
 * Undocumented function
 */
function getBestRoundPicks()
{
    $result = getAllDraftedPlayerDetails();

    $best = [];
    for ($x = 1; $x < 26; $x++) {
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
 * Get PF & PA for this season to show on current season page
 */
function getPointsForScatter()
{
    global $selectedSeason;
    $response = [];
    $totalPts = 0;

    $result = query("SELECT name, sum(manager1_score) as pf, sum(manager2_score) as pa FROM regular_season_matchups rsm
        JOIN managers ON managers.id = rsm.manager1_id
        WHERE year = $selectedSeason
        GROUP BY name");
    while ($row = fetch_array($result)) {
        $totalPts += $row['pf'];
        $response[$row['name']] = [
            [
                'x' => round($row['pf'], 1),
                'y' => round($row['pa'], 1)
            ]
        ];
    }

    $data = [
        'average' => round($totalPts / 10, 1),
        'chart' => $response,
    ];

    return $data;
}

/**
 * Check a roster for the optimal lineup configuration
 */
function checkRosterForOptimal(array $roster, ?int $season = null)
{
    global $selectedSeason;
    
    if (!$season) {
        $season = $selectedSeason;
    }

    usort($roster, function($a, $b) {
        return $b['points'] <=> $a['points'];
    });

    // Get season positions dynamically
    $seasonPositions = getSeasonPositions($season);
    $optimalRoster = [];

    // Initialize optimal roster positions with 0 values
    foreach ($seasonPositions['positions'] as $position) {
        // For positions that have multiple slots (like RB1, RB2), create separate entries
        if (isset($seasonPositions['counts'][$position]) && $seasonPositions['counts'][$position] > 1) {
            for ($i = 1; $i <= $seasonPositions['counts'][$position]; $i++) {
                $optimalRoster[strtolower($position) . $i] = 0;
            }
        } else {
            $optimalRoster[strtolower($position)] = 0;
        }
    }

    // Flex positions need special handling
    $flexKey = null;
    if (array_key_exists('wrt', $optimalRoster)) {
        $flexKey = 'wrt';
    } elseif (array_key_exists('w/r/t', $optimalRoster)) {
        $flexKey = 'w/r/t';
    } elseif (array_key_exists('w/r', $optimalRoster)) {
        $flexKey = 'w/r';
    } elseif (array_key_exists('w/t', $optimalRoster)) {
        $flexKey = 'w/t';
    }
    
    $superFlexKey = null;
    if (array_key_exists('qwrt', $optimalRoster)) {
        $superFlexKey = 'qwrt';
    } elseif (array_key_exists('q/w/r/t', $optimalRoster)) {
        $superFlexKey = 'q/w/r/t';
    }

    $fullRoster = 0;
    $maxRosterSize = $seasonPositions['total'];

    foreach ($roster as $player) {
        if ($fullRoster < $maxRosterSize) {
            if ($player['pos'] == 'QB') {
                if (isset($optimalRoster['qb1']) && $optimalRoster['qb1'] == 0) {
                    $optimalRoster['qb1'] = $player['points'];
                    $fullRoster++;
                } elseif (isset($optimalRoster['qb2']) && $optimalRoster['qb2'] == 0) {
                    $optimalRoster['qb2'] = $player['points'];
                    $fullRoster++;
                } elseif (isset($optimalRoster['qb']) && $optimalRoster['qb'] == 0) {
                    $optimalRoster['qb'] = $player['points'];
                    $fullRoster++;
                } elseif ($superFlexKey && isset($optimalRoster[$superFlexKey]) && $optimalRoster[$superFlexKey] == 0) {
                    $optimalRoster[$superFlexKey] = $player['points'];
                    $fullRoster++;
                }
            } elseif ($player['pos'] == 'RB') {
                if (isset($optimalRoster['rb1']) && $optimalRoster['rb1'] == 0) {
                    $optimalRoster['rb1'] = $player['points'];
                    $fullRoster++;
                } elseif (isset($optimalRoster['rb2']) && $optimalRoster['rb2'] == 0) {
                    $optimalRoster['rb2'] = $player['points'];
                    $fullRoster++;
                } elseif ($flexKey && isset($optimalRoster[$flexKey]) && $optimalRoster[$flexKey] == 0) {
                    $optimalRoster[$flexKey] = $player['points'];
                    $fullRoster++;
                } elseif ($superFlexKey && isset($optimalRoster[$superFlexKey]) && $optimalRoster[$superFlexKey] == 0) {
                    $optimalRoster[$superFlexKey] = $player['points'];
                    $fullRoster++;
                }
            } elseif ($player['pos'] == 'WR') {
                if (isset($optimalRoster['wr1']) && $optimalRoster['wr1'] == 0) {
                    $optimalRoster['wr1'] = $player['points'];
                    $fullRoster++;
                } elseif (isset($optimalRoster['wr2']) && $optimalRoster['wr2'] == 0) {
                    $optimalRoster['wr2'] = $player['points'];
                    $fullRoster++;
                } elseif (isset($optimalRoster['wr3']) && $optimalRoster['wr3'] == 0) {
                    $optimalRoster['wr3'] = $player['points'];
                    $fullRoster++;
                } elseif (isset($optimalRoster['wr4']) && $optimalRoster['wr4'] == 0) {
                    $optimalRoster['wr4'] = $player['points'];
                    $fullRoster++;
                } elseif ($flexKey && isset($optimalRoster[$flexKey]) && $optimalRoster[$flexKey] == 0) {
                    $optimalRoster[$flexKey] = $player['points'];
                    $fullRoster++;
                } elseif ($superFlexKey && isset($optimalRoster[$superFlexKey]) && $optimalRoster[$superFlexKey] == 0) {
                    $optimalRoster[$superFlexKey] = $player['points'];
                    $fullRoster++;
                }
            } elseif ($player['pos'] == 'TE') {
                if (isset($optimalRoster['te']) && $optimalRoster['te'] == 0) {
                    $optimalRoster['te'] = $player['points'];
                    $fullRoster++;
                } elseif ($flexKey && isset($optimalRoster[$flexKey]) && $optimalRoster[$flexKey] == 0) {
                    $optimalRoster[$flexKey] = $player['points'];
                    $fullRoster++;
                } elseif ($superFlexKey && isset($optimalRoster[$superFlexKey]) && $optimalRoster[$superFlexKey] == 0) {
                    $optimalRoster[$superFlexKey] = $player['points'];
                    $fullRoster++;
                }
            } elseif ($player['pos'] == 'K') {
                if (isset($optimalRoster['k']) && $optimalRoster['k'] == 0) {
                    $optimalRoster['k'] = $player['points'];
                    $fullRoster++;
                }
            } elseif ($player['pos'] == 'DEF') {
                if (isset($optimalRoster['def1']) && $optimalRoster['def1'] == 0) {
                    $optimalRoster['def1'] = $player['points'];
                    $fullRoster++;
                } elseif (isset($optimalRoster['def2']) && $optimalRoster['def2'] == 0) {
                    $optimalRoster['def2'] = $player['points'];
                    $fullRoster++;
                } elseif (isset($optimalRoster['def']) && $optimalRoster['def'] == 0) {
                    $optimalRoster['def'] = $player['points'];
                    $fullRoster++;
                }
            } elseif (in_array($player['pos'], ['D', 'DL', 'LB', 'DB'])) {
                // Handle individual defensive positions
                $posLower = strtolower($player['pos']);
                
                if (isset($optimalRoster[$posLower.'1']) && $optimalRoster[$posLower.'1'] == 0) {
                    $optimalRoster[$posLower.'1'] = $player['points'];
                    $fullRoster++;
                } elseif (isset($optimalRoster[$posLower.'2']) && $optimalRoster[$posLower.'2'] == 0) {
                    $optimalRoster[$posLower.'2'] = $player['points'];
                    $fullRoster++;
                } elseif (isset($optimalRoster[$posLower]) && $optimalRoster[$posLower] == 0) {
                    $optimalRoster[$posLower] = $player['points'];
                    $fullRoster++;
                } elseif (isset($optimalRoster['d']) && $optimalRoster['d'] == 0 && $player['pos'] != 'DEF') {
                    // If there's a generic 'D' slot available, use it for any defensive player
                    $optimalRoster['d'] = $player['points'];
                    $fullRoster++;
                } elseif (isset($optimalRoster['d1']) && $optimalRoster['d1'] == 0 && $player['pos'] != 'DEF') {
                    $optimalRoster['d1'] = $player['points'];
                    $fullRoster++;
                } elseif (isset($optimalRoster['d2']) && $optimalRoster['d2'] == 0 && $player['pos'] != 'DEF') {
                    $optimalRoster['d2'] = $player['points'];
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

/**
 * Get roster positions for a specific season from season_positions table
 */
function getSeasonPositions($season = null)
{
    global $selectedSeason;
    
    if (!$season) {
        $season = $selectedSeason;
    }
    
    $positions = [];
    $positionCounts = [];
    
    $result = query("SELECT * FROM season_positions WHERE year = $season AND position not in ('BN', 'IR') ORDER BY sort_order ASC");
    while ($row = fetch_array($result)) {
        $position = strtolower($row['position']);
        $positions[] = $position;
        $positionCounts[$position] = isset($positionCounts[$position]) ? $positionCounts[$position] + 1 : 1;
    }
    
    // Filter out bench and IR positions as they're not part of the optimal lineup
    $positions = array_filter($positions, function($pos) {
        return $pos != 'BN' && $pos != 'IR';
    });
    
    return [
        'positions' => $positions,
        'counts' => $positionCounts,
        'total' => count($positions)
    ];
}

function getMatchupRecapNumbers()
{
    global $season;
    $result = query("SELECT week FROM rosters where year = $season ORDER BY week DESC LIMIT 1");
    while ($row = fetch_array($result)) {
        $week = $row['week'];
    }

    $managerName = 'Andy';
    $managerId = getManagerId($managerName);
    
    if (isset($_GET['manager'])) {
        $managerName = $_GET['manager'];
        $managerId = getManagerId($managerName);
        if (isset($_GET['year'])) {
            $season = $_GET['year'];
        }
        if (isset($_GET['week'])) {
            $week = $_GET['week'];
        }
    }
    $recap = [
        'man1' => '', 'man2' => '', 'margin1' => 0, 'margin2' => 0, 'projected1' => 0, 'projected2' => 0,
        'top_scorer1' => 0, 'top_scorer2' => 0, 'bottom_scorer1' => 417, 'bottom_scorer2' => 417,
        'top_scorer_name1' => '', 'top_scorer_name2' => '', 'bottom_scorer_name1' => '', 'bottom_scorer_name2' => '',
        'bench1' => 0, 'bench2' => 0, 'points1' => 0, 'points2' => 0,
        'record1before' => '', 'record2before' => '', 'record1after' => '', 'record2after' => ''
    ];

    $versus = '';
    $versusId = null;

    $result = query("SELECT distinct week_number FROM regular_season_matchups WHERE year = $season ORDER BY week_number ASC");
    $lastWeek = 0;
    while ($row = fetch_array($result)) {
        $lastWeek++;
    }

     // Find round based on week
     if ($week == $lastWeek+1) {
        $round = 'Quarterfinal';
    } else if ($week == $lastWeek+2) {
        $round = 'Semifinal';
    } else if ($week >= $lastWeek+3) {
        $round = 'Final';
    }

    if ($week > $lastWeek) {
        $result = query("SELECT * FROM playoff_rosters pr
            JOIN managers ON managers.name = pr.manager
            JOIN playoff_matchups pm on pm.year = pr.year and pm.round = pr.round and (manager1_id = $managerId or manager2_id = $managerId)
            WHERE pr.year = $season and pr.round = '$round' and managers.name = '$managerName'");
    } else {
        $result = query("SELECT * FROM rosters
            JOIN managers on managers.name = rosters.manager
            JOIN regular_season_matchups rsm on rsm.year = rosters.year and rsm.week_number = rosters.week
            and rsm.manager1_id = managers.id
            WHERE rosters.year = $season and rosters.week = $week and manager = '".$managerName."'");
    }

    while ($row = fetch_array($result)) {
        if ($row['manager1_id'] == $managerId) {
            $versusId = $row['manager2_id'];
            $managerPoints = $row['manager1_score'];
            $versusPoints = $row['manager2_score'];
        } else {
            $versusId = $row['manager1_id'];
            $managerPoints = $row['manager2_score'];
            $versusPoints = $row['manager1_score'];
        }
        $margin1 = $managerPoints - $versusPoints;
        $margin2 = $versusPoints - $managerPoints;

        if ($row['roster_spot'] == 'BN' || $row['roster_spot'] == 'IR') {
            $recap['bench1'] += (float)$row['points'];
        } else {
            $recap['projected1'] = isset($row['manager1_projected']) ? (float)$row['manager1_projected'] : 'N/A';
            $recap['projected2'] = isset($row['manager2_projected']) ? (float)$row['manager2_projected'] : 'N/A';
            
            if ($row['points'] > $recap['top_scorer1']) {
                $recap['top_scorer1'] = $row['points'];
                $recap['top_scorer_name1'] = $row['player'];
            }
            if ($row['points'] < $recap['bottom_scorer1']) {
                $recap['bottom_scorer1'] = $row['points'];
                $recap['bottom_scorer_name1'] = $row['player'];
            }
        }
    }

    if (!$versusId) {
        return $recap;
    }
    
    if ($week > $lastWeek) {
        $result = query("SELECT * FROM playoff_rosters
            JOIN managers ON managers.name = playoff_rosters.manager
            WHERE playoff_rosters.year = $season and round = '$round' and managers.id = '".$versusId."'");
    } else {
        $result = query("SELECT * FROM managers 
            JOIN rosters on managers.name = rosters.manager
            WHERE rosters.year = $season AND rosters.week = $week AND managers.id = ".$versusId);
    }
    while ($row = fetch_array($result)) {
        $versus = $row['manager'];
        if ($row['roster_spot'] == 'BN' || $row['roster_spot'] == 'IR') {
            $recap['bench2'] += $row['points'];
        } else {

            if ($row['points'] > $recap['top_scorer2']) {
                $recap['top_scorer2'] = $row['points'];
                $recap['top_scorer_name2'] = $row['player'];
            }
            if ($row['points'] < $recap['bottom_scorer2']) {
                $recap['bottom_scorer2'] = $row['points'];
                $recap['bottom_scorer_name2'] = $row['player'];
            }
        }
    }

    // Lookup records
    if ($week == 1) {
        $recap['record1before'] = '0 - 0';
        $recap['record2before'] = '0 - 0';
    } else {
        $recap['record1before'] = getRecord($managerName, $season, $week-1);
        $recap['record2before'] = getRecord($versus, $season, $week-1);
    }
    $recap['record1after'] = getRecord($managerName, $season, $week);
    $recap['record2after'] = getRecord($versus, $season, $week);

    $recap['man1'] = $managerPoints > $versusPoints ? '<span class="badge badge-primary">'.$managerName.'</span>' : '<span class="badge badge-secondary">'.$managerName.'</span>';
    $recap['man2'] = $managerPoints > $versusPoints ? '<span class="badge badge-secondary">'.$versus.'</span>' : '<span class="badge badge-primary">'.$versus.'</span>';
    $recap['margin1'] = $margin1;
    $recap['margin2'] = $margin2;
    $recap['points1'] = $managerPoints;
    $recap['points2'] = $versusPoints;
    $recap['top_scorer1'] = $recap['top_scorer_name1'].' ('.$recap['top_scorer1'].')';
    $recap['top_scorer2'] = $recap['top_scorer_name2'].' ('.$recap['top_scorer2'].')';
    $recap['bottom_scorer1'] = $recap['bottom_scorer_name1'].' ('.$recap['bottom_scorer1'].')';
    $recap['bottom_scorer2'] = $recap['bottom_scorer_name2'].' ('.$recap['bottom_scorer2'].')';
    
    return $recap;
}

function getRecord($managerName, $year, $week)
{
    // Get wins and losses from standings table for the specified week
    $result = query("SELECT s.wins, s.losses
        FROM standings s
        JOIN managers m ON s.manager_id = m.id
        WHERE m.name = '$managerName' AND s.year = $year AND s.week = $week");
    
    $row = fetch_array($result);
    if ($row) {
        return $row['wins'].'-'.$row['losses'];
    }
    
    return '0-0';
}

function getPositionPointsChartNumbers()
{
    global $season;
    $result = query("SELECT week FROM rosters where year = $season ORDER BY week DESC LIMIT 1");
    while ($row = fetch_array($result)) {
        $week = $row['week'];
    }
    $lastWeek = $week;

    $managerName = 'Andy';
    $managerId = getManagerId($managerName);

    if (isset($_GET['manager'])) {
        $managerName = $_GET['manager'];
        $managerId = getManagerId($managerName);
        if (isset($_GET['year'])) {
            $season = $_GET['year'];
        }
        if (isset($_GET['week'])) {
            $week = $_GET['week'];
        }
    }

    if ($week == $lastWeek+1) {
        $round = 'Quarterfinal';
    } else if ($week == $lastWeek+2) {
        $round = 'Semifinal';
    } else if ($week >= $lastWeek+3) {
        $round = 'Final';
    }

    $versus = null;
    if ($week > $lastWeek) {
        $result = query("SELECT * FROM playoff_rosters pr
            JOIN managers ON managers.name = pr.manager
            JOIN playoff_matchups pm on pm.year = pr.year and pm.round = pr.round and (manager1_id = $managerId or manager2_id = $managerId)
            WHERE pr.year = $season and pr.round = '$round' and managers.name = '$managerName'");
    } else {
        $result = query("SELECT * FROM regular_season_matchups rsm
            JOIN managers on managers.id = rsm.manager1_id
            WHERE year = $season and week_number = $week and managers.name = '$managerName'");
    }

    $versus = null;
    
    while ($row = fetch_array($result)) {
        $versus = $row['manager1_id'] == $managerId ? $row['manager2_id'] : $row['manager1_id'];
    }
    if (!$versus) {
        return [
            'labels' => [],
            'points' => []
        ];
    }
    $versusName = getManagerName($versus);

    $posOrder = ['QB', 'RB', 'WR', 'TE', 'W/R/T', 'W/R', 'W/T', 'Q/W/R/T', 'K', 'DEF', 'D', 'DL', 'DB', 'BN', 'IR'];

    $labels = $points = [];
    if ($week > $lastWeek) {
        $result = query("SELECT manager, roster_spot, sum(points) as points FROM playoff_rosters
            JOIN managers on managers.name = playoff_rosters.manager
            JOIN playoff_matchups pm on pm.year = playoff_rosters.year and pm.round = playoff_rosters.round
            and (pm.manager1_id = managers.id OR pm.manager2_id = managers.id)
            WHERE playoff_rosters.year = $season and playoff_rosters.week = $week and (manager = '$managerName' OR manager = '$versusName')
            AND roster_spot != 'IR'
            GROUP BY manager, roster_spot
            ORDER BY CASE roster_spot 
                WHEN 'QB' THEN 0 
                WHEN 'RB' THEN 1
                WHEN 'WR' THEN 2
                WHEN 'TE' THEN 3
                WHEN 'W/R/T' THEN 4
                WHEN 'W/R' THEN 5
                WHEN 'W/T' THEN 6
                WHEN 'Q/W/R/T' THEN 7 
                WHEN 'K' THEN 8
                WHEN 'DEF' THEN 9
                WHEN 'D' THEN 10
                WHEN 'DL' THEN 11
                WHEN 'DB' THEN 12
                WHEN 'BN' THEN 13
            END");  
    } else {
        $result = query("SELECT manager, roster_spot, sum(points) as points FROM rosters
            JOIN managers on managers.name = rosters.manager
            JOIN regular_season_matchups rsm on rsm.year = rosters.year and rsm.week_number = rosters.week
            and rsm.manager1_id = managers.id
            WHERE rosters.year = $season and rosters.week = $week and (manager = '".$managerName."' OR manager = '".$versusName."')
            AND roster_spot != 'IR'
            GROUP BY manager, roster_spot
            ORDER BY CASE roster_spot 
                WHEN 'QB' THEN 0 
                WHEN 'RB' THEN 1
                WHEN 'WR' THEN 2
                WHEN 'TE' THEN 3
                WHEN 'W/R/T' THEN 4
                WHEN 'W/R' THEN 5
                WHEN 'W/T' THEN 6
                WHEN 'Q/W/R/T' THEN 7 
                WHEN 'K' THEN 8
                WHEN 'DEF' THEN 9
                WHEN 'D' THEN 10
                WHEN 'DL' THEN 11
                WHEN 'DB' THEN 12
                WHEN 'BN' THEN 13
            END");  
    }
    while ($row = fetch_array($result)) {

        if (!in_array($row['roster_spot'], $labels)) {
            $labels[] = $row['roster_spot'];
        }
        $points[$row['manager']][] = $row['points'];
    }

    // sort labels and points based on posOrder
    $labels = array_values(array_intersect($posOrder, $labels));

    return [
        'labels' => $labels,
        'points' => $points
    ];
}

function getGameTimeChartNumbers()
{
    global $season;
    $result = query("SELECT week FROM rosters where year = $season ORDER BY week DESC LIMIT 1");
    while ($row = fetch_array($result)) {
        $lastWeek = $row['week'];
    }
    $week = $lastWeek;
    $managerName = 'Andy';
    $managerId = getManagerId($managerName);

    if (isset($_GET['manager'])) {
        $managerName = $_GET['manager'];
        $managerId = getManagerId($managerName);
        if (isset($_GET['year'])) {
            $season = $_GET['year'];
        }
        if (isset($_GET['week'])) {
            $week = $_GET['week'];
        }
    }

    // Find round based on week
    if ($week == $lastWeek+1) {
        $round = 'Quarterfinal';
    } else if ($week == $lastWeek+2) {
        $round = 'Semifinal';
    } else if ($week >= $lastWeek+3) {
        $round = 'Final';
    }

    if ($week > $lastWeek) {
        $result = query("SELECT * FROM playoff_rosters pr
            JOIN managers ON managers.name = pr.manager
            JOIN playoff_matchups pm on pm.year = pr.year and pm.round = pr.round and (manager1_id = $managerId or manager2_id = $managerId)
            WHERE pr.year = $season and pr.round = '$round' and managers.name = '$managerName'");
    } else {
        $result = query("SELECT * FROM regular_season_matchups rsm
            JOIN managers on managers.id = rsm.manager1_id
            WHERE year = $season and week_number = $week and managers.name = '$managerName'");
    }

    $versus = null;
    
    while ($row = fetch_array($result)) {
        $versus = $row['manager1_id'] == $managerId ? $row['manager2_id'] : $row['manager1_id'];
    }
    if (!$versus) {
        return [
            'labels' => [],
            'points' => []
        ];
    }
    $versusName = getManagerName($versus);

    $labels = [
        1 => 'Thursday',
        2 => 'Friday',
        3 => 'Sunday Early',
        4 => 'Sunday Afternoon',
        5 => 'Sunday Night',
        6 => 'Monday',
        7 => 'Tuesday',
        8 => 'Other'
    ];
    $totalPoints = 0;
    foreach ($labels as $id => $label) {
        if ($week > $lastWeek) {
            $result = query("SELECT manager, game_slot, sum(points) as points FROM playoff_rosters
                JOIN managers on managers.name = playoff_rosters.manager
                WHERE playoff_rosters.year = $season and playoff_rosters.week = $week and manager = '$managerName'
                AND roster_spot NOT IN ('IR', 'BN')
                AND game_slot = $id
                GROUP BY manager, game_slot
                ORDER BY game_slot ASC");
        } else {

            $result = query("SELECT manager, game_slot, sum(points) as points FROM rosters
                JOIN managers on managers.name = rosters.manager
                WHERE rosters.year = $season and rosters.week = $week and manager = '$managerName'
                AND roster_spot NOT IN ('IR', 'BN')
                AND game_slot = $id
                GROUP BY manager, game_slot
                ORDER BY game_slot ASC");
        }
        while ($row = fetch_array($result)) {
            $totalPoints += $row['points'];
        }
        $points[$managerName][$id] = $totalPoints;
    }
    
    $totalPoints = 0;
    foreach ($labels as $id => $label) {
        if ($week > $lastWeek) {
            $result = query("SELECT manager, game_slot, sum(points) as points FROM playoff_rosters
                JOIN managers on managers.name = playoff_rosters.manager
                WHERE playoff_rosters.year = $season and playoff_rosters.week = $week and manager = '$versusName'
                AND roster_spot NOT IN ('IR', 'BN')
                AND game_slot = $id
                GROUP BY manager, game_slot
                ORDER BY game_slot ASC");
        } else {
            $result = query("SELECT manager, game_slot, sum(points) as points FROM rosters
                JOIN managers on managers.name = rosters.manager
                WHERE rosters.year = $season and rosters.week = $week and manager = '$versusName'
                AND roster_spot NOT IN ('IR', 'BN')
                AND game_slot = $id
                GROUP BY manager, game_slot
                ORDER BY game_slot ASC");
        }
        while ($row = fetch_array($result)) {
            $totalPoints += $row['points'];
        }
        $points[$versusName][$id] = $totalPoints;
    }

    // Check if we can remove friday and tuesday and other
    if ($points[$managerName][1] == $points[$managerName][2] && $points[$versusName][1] == $points[$versusName][2]) {
        unset($labels[2]);
        unset($points[$managerName][2]);
        unset($points[$versusName][2]);
    }
    if ($points[$managerName][7] == $points[$managerName][8] && $points[$versusName][7] == $points[$versusName][8]) {
        unset($labels[8]);
        unset($points[$managerName][8]);
        unset($points[$versusName][8]);
    }
    if ($points[$managerName][6] == $points[$managerName][7] && $points[$versusName][6] == $points[$versusName][7]) {
        unset($labels[7]);
        unset($points[$managerName][7]);
        unset($points[$versusName][7]);
    }

    // get array_values for each of the managers' points
    $points = array_map(function($manager) {
        return array_values($manager);
    }, $points);

    return [
        'labels' => array_values($labels),
        'points' => $points
    ];
}

function lookupGameSpot($slot)
{
    switch ($slot) {
        case 1:
            return 'Thursday';
        case 2:
            return 'Friday';
        case 3:
            return 'Sunday Early';
        case 4:
            return 'Sunday Afternoon';
        case 5:
            return 'Sunday Night';
        case 6:
            return 'Monday';
        case 7:
            return 'Tuesday';
    }
}

function getPlayerRank($player, $year, $week)
{
    // Determine if its playoff week or not
    $result = query("SELECT week FROM rosters where year = $year ORDER BY week DESC LIMIT 1");
    while ($row = fetch_array($result)) {
        $lastWeek = $row['week'];
    }

    if ($week > $lastWeek) {
        $result = query("SELECT * FROM playoff_rosters
            WHERE year = $year and week = $week
            ORDER BY points desc");
    } else {
        $result = query("SELECT * FROM rosters
            WHERE year = $year and week = $week
            ORDER BY points desc");
    }
    $rank = 1;
    while ($row = fetch_array($result)) {
        
        if ($row['player'] == $player) {
            if ($row['roster_spot'] == 'IR') {
                return 'N/A';
            }
            return $rank;
        }
        $rank++;
    }
}

function getPlayerPositionRank($player, $rosterSpot, $position, $year, $week)
{
    if ($rosterSpot == 'IR') {
        return 'N/A';
    }

    // Determine if its playoff week or not
    $result = query("SELECT week FROM rosters where year = $year ORDER BY week DESC LIMIT 1");
    while ($row = fetch_array($result)) {
        $lastWeek = $row['week'];
    }

    if ($week > $lastWeek) {
        $result = query("SELECT * FROM playoff_rosters
            WHERE year = $year and week = $week and position = '".$position."'
            ORDER BY points desc");
    } else {
        $result = query("SELECT * FROM rosters
            WHERE year = $year and week = $week and position = '".$position."'
            ORDER BY points desc");
    }

    $rank = 1;
    while ($row = fetch_array($result)) {
        if ($row['player'] == $player) {
            return $rank;
        }
        $rank++;
    }
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
    // Move down so its below the header
    echo "<br /><br /><br />";
    echo '<pre style="direction: ltr; float: left;">';
    print_r($text);
    echo '</pre>';
    die;
}

/**
 * Better GI than print_r or var_dump -- but, unlike var_dump, you can only dump one variable.  
 * Added htmlentities on the var content before echo, so you see what is really there, and not the mark-up.
 *
 * @param mixed $var  -- variable to dump
 * @param string $var_name  -- name of variable (optional) -- displayed in printout making it easier to sort out what variable is what in a complex output
 * @param string $indent -- used by internal recursive call (no known external value)
 * @param $reference -- used by internal recursive call (no known external value)
 */
function do_dump(&$var, $var_name = NULL, $indent = NULL, $reference = NULL)
{
    $do_dump_indent = "<span style='color:#666666;'>|</span> &nbsp;&nbsp; ";
    $reference = $reference.$var_name;
    $keyvar = 'the_do_dump_recursion_protection_scheme'; $keyname = 'referenced_object_name';
    
    // So this is always visible and always left justified and readable
    echo "<div style='direction: ltr; text-align:left; background-color:white; font: 100% monospace; color:black; height: 100%'>";

    if (is_array($var) && isset($var[$keyvar])) {
        $real_var = &$var[$keyvar];
        $real_name = &$var[$keyname];
        $type = ucfirst(gettype($real_var));
        echo "$indent$var_name <span style='color:#666666'>$type</span> = <span style='color:#e87800;'>&amp;$real_name</span><br>";
    } else {
        $var = array($keyvar => $var, $keyname => $reference);
        $avar = &$var[$keyvar];

        $type = ucfirst(gettype($avar));
        if ($type == "String") $type_color = "<span style='color:green'>";
        elseif ($type == "Integer") $type_color = "<span style='color:red'>";
        elseif ($type == "Double"){ $type_color = "<span style='color:#0099c5'>"; $type = "Float"; }
        elseif ($type == "Boolean") $type_color = "<span style='color:#92008d'>";
        elseif ($type == "NULL") $type_color = "<span style='color:black'>";

        if (is_array($avar)) {
            $count = count($avar);
            echo "$indent" . ($var_name ? "$var_name => ":"") . "<span style='color:#666666'>$type ($count)</span><br>$indent(<br>";
            $keys = array_keys($avar);
            foreach($keys as $name)
            {
                $value = &$avar[$name];
                do_dump($value, "['$name']", $indent.$do_dump_indent, $reference);
            }
            echo "$indent)<br>";
        } elseif (is_object($avar)) {
            echo "$indent$var_name <span style='color:#666666'>$type</span><br>$indent(<br>";
            foreach($avar as $name=>$value) do_dump($value, "$name", $indent.$do_dump_indent, $reference);
            echo "$indent)<br>";
        }
        elseif (is_int($avar)) echo "$indent$var_name = <span style='color:#666666'>$type(".strlen($avar).")</span> $type_color".htmlentities($avar)."</span><br>";
        elseif (is_string($avar)) echo "$indent$var_name = <span style='color:#666666'>$type(".strlen($avar).")</span> $type_color\"".htmlentities($avar)."\"</span><br>";
        elseif (is_float($avar)) echo "$indent$var_name = <span style='color:#666666'>$type(".strlen($avar).")</span> $type_color".htmlentities($avar)."</span><br>";
        elseif (is_bool($avar)) echo "$indent$var_name = <span style='color:#666666'>$type(".strlen($avar).")</span> $type_color".($avar == 1 ? "TRUE":"FALSE")."</span><br>";
        elseif (is_null($avar)) echo "$indent$var_name = <span style='color:#666666'>$type(".strlen($avar).")</span> {$type_color}NULL</span><br>";
        else echo "$indent$var_name = <span style='color:#666666'>$type(".strlen($avar).")</span> ".htmlentities($avar)."<br>";

        $var = $var[$keyvar];
    }
    
    echo "</div>";
}

/**
 * Get regular season champions data
 * Returns an array of regular season winners with year, champion, record, points, and runner up
 */
function getRegularSeasonWinners()
{
    $winners = [];
    
    $result = query("SELECT DISTINCT year FROM finishes ORDER BY year DESC");
    while ($row = fetch_array($result)) {
        $year = $row['year'];
        
        // Get all manager records for this year
        $standings = [];
        $result2 = query("SELECT managers.name, COUNT(CASE WHEN manager1_score > manager2_score THEN 1 END) as wins, 
                        COUNT(CASE WHEN manager1_score < manager2_score THEN 1 END) as losses,
                        SUM(manager1_score) as points
                        FROM regular_season_matchups
                        JOIN managers ON managers.id = regular_season_matchups.manager1_id
                        WHERE year = $year
                        GROUP BY managers.name
                        ORDER BY wins DESC, points DESC");
        
        while ($row2 = fetch_array($result2)) {
            $standings[] = [
                'name' => $row2['name'],
                'wins' => $row2['wins'],
                'losses' => $row2['losses'],
                'points' => $row2['points']
            ];
        }
        
        // Make sure we have at least 2 managers for each year
        if (count($standings) >= 2) {
            $winners[] = [
                'year' => $year,
                'champion' => $standings[0]['name'],
                'record' => $standings[0]['wins'] . '-' . $standings[0]['losses'],
                'points' => $standings[0]['points'],
                'runner_up' => $standings[1]['name']
            ];
        }
    }
    
    return $winners;
}


/**
 * Get the date range for a specific week and year
 */
function getWeekDateRange($year, $week)
{
    // Query the min and max game times for this week and year
    $result = query("SELECT MIN(game_time) as start_date, MAX(game_time) as end_date 
                    FROM rosters 
                    WHERE year = $year AND week = $week AND game_time IS NOT NULL");
    
    while ($row = fetch_array($result)) {
        if ($row['start_date'] && $row['end_date']) {
            // Parse the dates
            $startDate = new DateTime($row['start_date']);
            $endDate = new DateTime($row['end_date']);
            
            // Format the dates
            return [
                'start_date' => $startDate->format('M j'),
                'end_date' => $endDate->format('M j, Y')
            ];
        }
    }
    
    return null;
}

/**
 * Get schedule information with historical records and current streaks
 */
function getScheduleInfo($year, $week)
{
    $currentYear = date('Y');
    $schedule = [];
    
    // Get schedule based on whether it's current year or historical
    if ($year == $currentYear) {
        // Use schedule table for current year
        $result = query("SELECT s.manager1_id, s.manager2_id, m1.name as manager1, m2.name as manager2
            FROM schedule s
            JOIN managers m1 ON s.manager1_id = m1.id
            JOIN managers m2 ON s.manager2_id = m2.id
            WHERE s.year = $year AND s.week = $week");
    } else {
        // Use regular_season_matchups for historical years - only get unique matchups (avoid duplicates)
        $result = query("SELECT DISTINCT 
            CASE WHEN rsm.manager1_id < rsm.manager2_id THEN rsm.manager1_id ELSE rsm.manager2_id END as manager1_id,
            CASE WHEN rsm.manager1_id < rsm.manager2_id THEN rsm.manager2_id ELSE rsm.manager1_id END as manager2_id,
            m1.name as manager1, m2.name as manager2
            FROM regular_season_matchups rsm
            JOIN managers m1 ON (CASE WHEN rsm.manager1_id < rsm.manager2_id THEN rsm.manager1_id ELSE rsm.manager2_id END) = m1.id
            JOIN managers m2 ON (CASE WHEN rsm.manager1_id < rsm.manager2_id THEN rsm.manager2_id ELSE rsm.manager1_id END) = m2.id
            WHERE rsm.year = $year AND rsm.week_number = $week");
    }
    
    while ($row = fetch_array($result)) {
        $manager1_id = $row['manager1_id'];
        $manager2_id = $row['manager2_id'];
        $manager1 = $row['manager1'];
        $manager2 = $row['manager2'];
        
        // Get historical head-to-head record - only count each game once by looking at one direction
        $h2hResult = query("SELECT 
            SUM(CASE WHEN manager1_score > manager2_score THEN 1 ELSE 0 END) as manager1_wins,
            SUM(CASE WHEN manager2_score > manager1_score THEN 1 ELSE 0 END) as manager2_wins
            FROM regular_season_matchups 
            WHERE manager1_id = $manager1_id AND manager2_id = $manager2_id");
        
        $h2hRow = fetch_array($h2hResult);
        $manager1_wins = $h2hRow['manager1_wins'] ?? 0;
        $manager2_wins = $h2hRow['manager2_wins'] ?? 0;
        $record = $manager1_wins . '-' . $manager2_wins;
        
        // Get postseason head-to-head record between these two managers
        $postseasonH2HResult = query("SELECT 
            SUM(CASE WHEN manager1_id = $manager1_id AND manager1_score > manager2_score THEN 1 ELSE 0 END) +
            SUM(CASE WHEN manager2_id = $manager1_id AND manager2_score > manager1_score THEN 1 ELSE 0 END) as manager1_wins,
            SUM(CASE WHEN manager1_id = $manager2_id AND manager1_score > manager2_score THEN 1 ELSE 0 END) +
            SUM(CASE WHEN manager2_id = $manager2_id AND manager2_score > manager1_score THEN 1 ELSE 0 END) as manager2_wins
            FROM playoff_matchups 
            WHERE (manager1_id = $manager1_id AND manager2_id = $manager2_id) 
               OR (manager1_id = $manager2_id AND manager2_id = $manager1_id)");
        
        $postseasonH2HRow = fetch_array($postseasonH2HResult);
        $manager1_postseason_wins = $postseasonH2HRow['manager1_wins'] ?? 0;
        $manager2_postseason_wins = $postseasonH2HRow['manager2_wins'] ?? 0;
        $postseason_record = $manager1_postseason_wins . '-' . $manager2_postseason_wins;
        
        // Get current streak - only look at one direction to avoid duplicates
        $streakResult = query("SELECT year, week_number, manager1_id, manager2_id, manager1_score, manager2_score
            FROM regular_season_matchups 
            WHERE (manager1_id = $manager1_id AND manager2_id = $manager2_id)
            ORDER BY year DESC, week_number DESC");
        
        $streak = 0;
        $streakWinner = '';
        $lastWinner = '';
        
        while ($streakRow = fetch_array($streakResult)) {
            $gameWinner = '';
            if ($streakRow['manager1_score'] > $streakRow['manager2_score']) {
                $gameWinner = getManagerName($streakRow['manager1_id']);
            } elseif ($streakRow['manager2_score'] > $streakRow['manager1_score']) {
                $gameWinner = getManagerName($streakRow['manager2_id']);
            } else {
                continue; // Skip ties
            }
            
            if ($lastWinner == '') {
                $lastWinner = $gameWinner;
                $streakWinner = $gameWinner;
                $streak = 1;
            } elseif ($lastWinner == $gameWinner) {
                $streak++;
            } else {
                break;
            }
        }
        
        $streakText = $streak > 0 ? $streakWinner . ' ' . $streak : 'Even';
        
        $schedule[] = [
            'manager1' => $manager1,
            'manager2' => $manager2,
            'manager1_id' => $manager1_id,
            'manager2_id' => $manager2_id,
            'record' => $record,
            'streak' => $streakText,
            'postseason_record' => $postseason_record
        ];
    }
    
    return $schedule;
}

/**
 * Get all matchups for a season to display on the schedule page
 */
function getFullSchedule($year = null)
{
    global $season;
    
    // Use current season if not specified
    $selectedYear = $year ? $year : $season;
    
    $response = [];
    $processedMatchups = [];
    
    // Get all played matchups from regular_season_matchups for selected season
    $result = query("SELECT rsm.year, rsm.week_number, 
                     m1.name as manager1_name, m2.name as manager2_name,
                     rsm.manager1_id, rsm.manager2_id, 
                     rsm.manager1_score, rsm.manager2_score,
                     rsm.winning_manager_id, rsm.losing_manager_id
                     FROM regular_season_matchups rsm
                     JOIN managers m1 ON m1.id = rsm.manager1_id
                     JOIN managers m2 ON m2.id = rsm.manager2_id
                     WHERE rsm.year = $selectedYear
                     ORDER BY rsm.week_number, rsm.id");
    
    while ($row = fetch_array($result)) {
        $weekNum = $row['week_number'];
        $manager1Id = $row['manager1_id'];
        $manager2Id = $row['manager2_id'];
        
        // Create a unique key for this matchup
        $matchupKey = $weekNum . '_' . min($manager1Id, $manager2Id) . '_' . max($manager1Id, $manager2Id);
        
        // Skip if we've already processed this matchup
        if (isset($processedMatchups[$matchupKey])) {
            continue;
        }
        
        // Mark this matchup as processed
        $processedMatchups[$matchupKey] = true;
        
        if (!isset($response[$weekNum])) {
            $response[$weekNum] = [
                'week' => $weekNum,
                'year' => $row['year'],
                'matchups' => [],
                'is_completed' => true
            ];
        }
        
        $response[$weekNum]['matchups'][] = [
            'manager1_name' => $row['manager1_name'],
            'manager2_name' => $row['manager2_name'],
            'manager1_id' => $row['manager1_id'],
            'manager2_id' => $row['manager2_id'],
            'manager1_score' => $row['manager1_score'],
            'manager2_score' => $row['manager2_score'],
            'winning_manager_id' => $row['winning_manager_id'],
            'losing_manager_id' => $row['losing_manager_id'],
            'is_completed' => true
        ];
    }
    
    // Get all future matchups from schedule table
    $result = query("SELECT s.year, s.week, 
                     m1.name as manager1_name, m2.name as manager2_name,
                     s.manager1_id, s.manager2_id
                     FROM schedule s
                     JOIN managers m1 ON m1.id = s.manager1_id
                     JOIN managers m2 ON m2.id = s.manager2_id
                     WHERE s.year = $selectedYear
                     ORDER BY s.week, s.id");
    
    while ($row = fetch_array($result)) {
        $weekNum = $row['week'];
        $manager1Id = $row['manager1_id'];
        $manager2Id = $row['manager2_id'];
        
        // Create a unique key for this matchup
        $matchupKey = $weekNum . '_' . min($manager1Id, $manager2Id) . '_' . max($manager1Id, $manager2Id);
        
        // Skip if we've already processed this matchup (either from rsm or schedule)
        if (isset($processedMatchups[$matchupKey])) {
            continue;
        }
        
        // Mark this matchup as processed
        $processedMatchups[$matchupKey] = true;
        
        // If this week already exists in our response, it means we have some results
        // from regular_season_matchups for this week, but not this specific matchup
        if (!isset($response[$weekNum])) {
            $response[$weekNum] = [
                'week' => $weekNum,
                'year' => $row['year'],
                'matchups' => [],
                'is_completed' => false
            ];
        }
        
        $response[$weekNum]['matchups'][] = [
            'manager1_name' => $row['manager1_name'],
            'manager2_name' => $row['manager2_name'],
            'manager1_id' => $row['manager1_id'],
            'manager2_id' => $row['manager2_id'],
            'is_completed' => false
        ];
    }
    
    // Sort by week
    ksort($response);
    
    // Add date ranges for each week
    foreach ($response as $weekNum => &$weekData) {
        $dateRange = getWeekDateRange($selectedYear, $weekNum);
        if ($dateRange) {
            $weekData['date_range'] = $dateRange['start_date'] . ' - ' . $dateRange['end_date'];
        }
    }
    
    return $response;
}


function getWeeklyScoresData()
{
    global $selectedSeason;
    
    // Query to get all scores grouped by week
    $query = "SELECT week_number, 
                     MAX(manager1_score) AS max_score1,
                     MAX(manager2_score) AS max_score2,
                     MIN(manager1_score) AS min_score1, 
                     MIN(manager2_score) AS min_score2,
                     AVG(manager1_score) AS avg_score1,
                     AVG(manager2_score) AS avg_score2
              FROM regular_season_matchups 
              WHERE year = $selectedSeason
              GROUP BY week_number
              ORDER BY week_number ASC";
    
    $result = query($query);
    $weeks = [];
    $maxScores = [];
    $minScores = [];
    $avgScores = [];
    
    while ($row = fetch_array($result)) {
        $week = $row['week_number'];
        $weeks[] = "Week " . $week;
        
        // Compare max scores between manager1 and manager2
        $maxScore = max($row['max_score1'], $row['max_score2']);
        $maxScores[] = round($maxScore, 2);
        
        // Compare min scores between manager1 and manager2
        $minScore = min($row['min_score1'], $row['min_score2']);
        $minScores[] = round($minScore, 2);
        
        // Calculate true average of all scores in that week
        // We need to average both manager1 and manager2 scores
        $avgScore = ($row['avg_score1'] + $row['avg_score2']) / 2;
        $avgScores[] = round($avgScore, 2);
    }
    
    return [
        'weeks' => $weeks,
        'maxScores' => $maxScores,
        'minScores' => $minScores,
        'avgScores' => $avgScores
    ];
}

/**
 * Calculate strength of schedule data for all managers in a season
 */
function getStrengthOfSchedule($season) {
    $managers = [];
    $managerNames = [];
    $currentYear = date('Y');
    
    // Get all manager names
    $result = query("SELECT id, name FROM managers");
    while ($row = fetch_array($result)) {
        $managerNames[$row['id']] = $row['name'];
    }
    
    // Initialize the data structure for each manager
    for ($id = 1; $id <= 10; $id++) {
        if (isset($managerNames[$id])) {
            $managers[$id] = [
                'id' => $id,
                'name' => $managerNames[$id],
                'opponent_wins' => 0,
                'opponent_losses' => 0,
                'opponent_points' => 0,
                'games_played' => 0,
                'scheduled_games' => 0,
                'future_opponents' => []
            ];
        }
    }
    
    // Check if we're dealing with a completed past season or current season
    $isCurrentSeason = ($season == $currentYear);
    
    // If current season, we'll track scheduled games and projected opponents
    if ($isCurrentSeason) {
        // Get the highest week number with completed games
        $completedWeeks = [];
        $result = query("SELECT DISTINCT week_number FROM regular_season_matchups WHERE year = $season AND manager1_score > 0 ORDER BY week_number");
        while ($row = fetch_array($result)) {
            $completedWeeks[] = $row['week_number'];
        }
        
        $lastCompletedWeek = !empty($completedWeeks) ? max($completedWeeks) : 0;
        
        // Track processed matchups to avoid duplicates
        $processedMatchups = [];
        
        // Process matchups for the current season
        // First, get all matchups (only one record per matchup) and process both managers
        $result = query("
            SELECT week_number, manager1_id, manager2_id, manager1_score, manager2_score
            FROM regular_season_matchups 
            WHERE year = $season 
            AND week_number <= $lastCompletedWeek
            AND manager1_id < manager2_id
            ORDER BY week_number
        ");
        
        // Initialize manager stats
        foreach ($managers as $managerId => $managerData) {
            $managers[$managerId]['wins'] = 0;
            $managers[$managerId]['losses'] = 0;
            $managers[$managerId]['points'] = 0;
        }
        
        while ($row = fetch_array($result)) {
            $manager1 = $row['manager1_id'];
            $manager2 = $row['manager2_id'];
            $score1 = $row['manager1_score'];
            $score2 = $row['manager2_score'];
            
            // Process manager1
            if (isset($managers[$manager1])) {
                $managers[$manager1]['points'] += $score1;
                if ($score1 > $score2) {
                    $managers[$manager1]['wins']++;
                } else if ($score1 < $score2) {
                    $managers[$manager1]['losses']++;
                }
            }
            
            // Process manager2
            if (isset($managers[$manager2])) {
                $managers[$manager2]['points'] += $score2;
                if ($score2 > $score1) {
                    $managers[$manager2]['wins']++;
                } else if ($score2 < $score1) {
                    $managers[$manager2]['losses']++;
                }
            }
        }
        
        // Now process the strength of schedule for current season
        // Get all matchups once and process opponent data for each manager
        $result = query("
            SELECT week_number, manager1_id, manager2_id, manager1_score, manager2_score
            FROM regular_season_matchups 
            WHERE year = $season 
            AND week_number <= $lastCompletedWeek
            AND manager1_id < manager2_id
            ORDER BY week_number
        ");
        
        // Initialize opponent stats for all managers
        foreach ($managers as $managerId => $managerData) {
            $managers[$managerId]['opponent_wins'] = 0;
            $managers[$managerId]['opponent_losses'] = 0;
            $managers[$managerId]['opponent_points'] = 0;
            $managers[$managerId]['games_played'] = 0;
            $managers[$managerId]['unique_opponents'] = [];
        }
        
        // Process each matchup and update opponent data for both managers
        while ($row = fetch_array($result)) {
            $manager1 = $row['manager1_id'];
            $manager2 = $row['manager2_id'];
            $score1 = $row['manager1_score'];
            $score2 = $row['manager2_score'];
            
            // Update manager1's opponent data (opponent is manager2)
            if (isset($managers[$manager1])) {
                $managers[$manager1]['opponent_points'] += $score2;
                $managers[$manager1]['games_played']++;
                if (!in_array($manager2, $managers[$manager1]['unique_opponents'])) {
                    $managers[$manager1]['unique_opponents'][] = $manager2;
                }
            }
            
            // Update manager2's opponent data (opponent is manager1)
            if (isset($managers[$manager2])) {
                $managers[$manager2]['opponent_points'] += $score1;
                $managers[$manager2]['games_played']++;
                if (!in_array($manager1, $managers[$manager2]['unique_opponents'])) {
                    $managers[$manager2]['unique_opponents'][] = $manager1;
                }
            }
        }
        
        // Calculate combined opponent record for each manager
        foreach ($managers as $managerId => $managerData) {
            foreach ($managers[$managerId]['unique_opponents'] as $opponentId) {
                if (isset($managers[$opponentId])) {
                    $managers[$managerId]['opponent_wins'] += $managers[$opponentId]['wins'];
                    $managers[$managerId]['opponent_losses'] += $managers[$opponentId]['losses'];
                }
            }
        }
    } else {
        // For past seasons, use a completely different approach focused on actual matchups
        
        // First, get the actual number of regular season weeks in this season
        $result = query("SELECT MAX(week_number) as max_week FROM regular_season_matchups WHERE year = $season");
        $row = fetch_array($result);
        $totalWeeks = $row['max_week'] ?? 14; // Default to 14 weeks if unknown
        
        // Initialize data structures for each manager
        for ($id = 1; $id <= 10; $id++) {
            if (isset($managers[$id])) {
                $managers[$id]['opponent_wins'] = 0;
                $managers[$id]['opponent_losses'] = 0;
                $managers[$id]['opponent_points'] = 0;
                $managers[$id]['games_played'] = 0;
                $managers[$id]['weekly_opponent_points'] = [];
                $managers[$id]['opponents_faced'] = [];
            }
        }
        
        // Process each manager's actual schedule
        // Use manager1_id < manager2_id to ensure each matchup is only counted once
        $result = query("
            SELECT manager1_id, manager2_id, manager1_score, manager2_score, week_number
            FROM regular_season_matchups 
            WHERE year = $season
            AND manager1_id < manager2_id  -- Only get one record per matchup
            ORDER BY week_number
        ");
        
        // First pass: record all matchups and points
        $weeklyScores = [];
        $processedMatchups = []; // To prevent duplicates
        for ($id = 1; $id <= 10; $id++) {
            $weeklyScores[$id] = [];
        }
        
        while ($row = fetch_array($result)) {
            $week = $row['week_number'];
            $manager1 = $row['manager1_id'];
            $manager2 = $row['manager2_id'];
            
            // Create a unique key for this matchup
            $matchupKey = $season . '-' . $week . '-' . min($manager1, $manager2) . '-' . max($manager1, $manager2);
            
            // Skip if we've already processed this matchup
            if (isset($processedMatchups[$matchupKey])) {
                continue;
            }
            
            // Mark this matchup as processed
            $processedMatchups[$matchupKey] = true;
            
            // Record this week's scores for both managers
            $weeklyScores[$manager1][$week] = $row['manager1_score'];
            $weeklyScores[$manager2][$week] = $row['manager2_score'];
            
            // Record who played whom in each week
            if (!isset($managers[$manager1]['opponents_faced'][$week])) {
                $managers[$manager1]['opponents_faced'][$week] = $manager2;
            }
            
            if (!isset($managers[$manager2]['opponents_faced'][$week])) {
                $managers[$manager2]['opponents_faced'][$week] = $manager1;
            }
        }
        
        // Now calculate each manager's record
        $managerRecords = [];
        for ($id = 1; $id <= 10; $id++) {
            $managerRecords[$id] = ['wins' => 0, 'losses' => 0, 'total_points' => 0, 'games' => 0];
        }
        
        // Calculate wins and losses for each manager
        for ($week = 1; $week <= $totalWeeks; $week++) {
            for ($managerId = 1; $managerId <= 10; $managerId++) {
                if (isset($managers[$managerId]['opponents_faced'][$week])) {
                    $opponentId = $managers[$managerId]['opponents_faced'][$week];
                    
                    if (isset($weeklyScores[$managerId][$week]) && isset($weeklyScores[$opponentId][$week])) {
                        $managerScore = $weeklyScores[$managerId][$week];
                        $opponentScore = $weeklyScores[$opponentId][$week];
                        
                        $managerRecords[$managerId]['total_points'] += $managerScore;
                        $managerRecords[$managerId]['games']++;
                        
                        if ($managerScore > $opponentScore) {
                            $managerRecords[$managerId]['wins']++;
                        } else if ($managerScore < $opponentScore) {
                            $managerRecords[$managerId]['losses']++;
                        }
                    }
                }
            }
        }
        
        // Now calculate strength of schedule for each manager
        for ($managerId = 1; $managerId <= 10; $managerId++) {
            if (isset($managers[$managerId])) {
                // Track all unique opponents faced and their combined records
                $uniqueOpponents = [];
                $totalOpponentWins = 0;
                $totalOpponentLosses = 0;
                $totalOpponentPoints = 0;
                $gameCount = 0;
                
                // Go through each week and collect opponent data
                for ($week = 1; $week <= $totalWeeks; $week++) {
                    if (isset($managers[$managerId]['opponents_faced'][$week])) {
                        $opponentId = $managers[$managerId]['opponents_faced'][$week];
                        
                        // Store opponent's weekly score for this specific game
                        if (isset($weeklyScores[$opponentId][$week])) {
                            $managers[$managerId]['weekly_opponent_points'][] = $weeklyScores[$opponentId][$week];
                            $totalOpponentPoints += $weeklyScores[$opponentId][$week];
                            $gameCount++;
                        }
                        
                        // Track unique opponents for record calculation
                        if (!in_array($opponentId, $uniqueOpponents)) {
                            $uniqueOpponents[] = $opponentId;
                        }
                    }
                }
                
                // Calculate combined opponent record (each opponent counted once)
                foreach ($uniqueOpponents as $opponentId) {
                    if (isset($managerRecords[$opponentId])) {
                        $totalOpponentWins += $managerRecords[$opponentId]['wins'];
                        $totalOpponentLosses += $managerRecords[$opponentId]['losses'];
                    }
                }
                
                // Store the calculated values
                $managers[$managerId]['opponent_wins'] = $totalOpponentWins;
                $managers[$managerId]['opponent_losses'] = $totalOpponentLosses;
                $managers[$managerId]['opponent_points'] = $totalOpponentPoints;
                $managers[$managerId]['games_played'] = $gameCount;
            }
        }
    }
    
    // Calculate average opponent win percentage and points for ranking
    $strengthData = [];
    foreach ($managers as $id => $data) {
        if (isset($data['weekly_opponent_points']) && !empty($data['weekly_opponent_points'])) {
            // For past seasons, use the weekly opponent points data
            $gamesPlayed = count($data['weekly_opponent_points']);
            $totalGames = $data['opponent_wins'] + $data['opponent_losses'];
            $winPercentage = $totalGames > 0 ? $data['opponent_wins'] / $totalGames : 0;
            $averagePoints = array_sum($data['weekly_opponent_points']) / $gamesPlayed;
            
            $strengthData[] = [
                'id' => $id,
                'name' => $data['name'],
                'opponent_record' => $data['opponent_wins'] . '-' . $data['opponent_losses'],
                'win_percentage' => $winPercentage,
                'opponent_points' => round(array_sum($data['weekly_opponent_points']), 2),
                'avg_opponent_points' => round($averagePoints, 2)
            ];
        } 
        else if ($data['games_played'] > 0) {
            // For current season or if no weekly data
            $totalGames = $data['opponent_wins'] + $data['opponent_losses'];
            $winPercentage = $totalGames > 0 ? $data['opponent_wins'] / $totalGames : 0;
            $averagePoints = $data['opponent_points'] / $data['games_played'];
            
            $strengthData[] = [
                'id' => $id,
                'name' => $data['name'],
                'opponent_record' => $data['opponent_wins'] . '-' . $data['opponent_losses'],
                'win_percentage' => $winPercentage,
                'opponent_points' => round($data['opponent_points'], 2),
                'avg_opponent_points' => round($averagePoints, 2)
            ];
        }
    }
    
    // If no strength data yet (no games played), return empty array
    if (empty($strengthData)) {
        return [];
    }
    
    // First, recalculate and ensure all records have the right win percentages
    foreach ($strengthData as &$teamData) {
        // Parse the opponent record to get wins and losses
        list($wins, $losses) = explode('-', $teamData['opponent_record']);
        $wins = (int)$wins;
        $losses = (int)$losses;
        $total = $wins + $losses;
        
        // Calculate the win percentage with proper precision
        $teamData['win_percentage'] = $total > 0 ? $wins / $total : 0;
    }
    unset($teamData); // Break reference to last element
    
    // Sort by win percentage (ascending) - lower win percentage means easier schedule (rank #1)
    usort($strengthData, function($a, $b) {
        // Primary sort by opponent win percentage (easiest schedule has lowest percentage)
        if ($a['win_percentage'] != $b['win_percentage']) {
            return ($a['win_percentage'] > $b['win_percentage']) ? 1 : -1;
        }
        
        // If win percentages are equal, sort by average points as tiebreaker (lower points = easier)
        return ($a['avg_opponent_points'] > $b['avg_opponent_points']) ? 1 : -1;
    });
    
    // Add ranking (1 is easiest and 10 is hardest)
    $rank = 1;
    foreach ($strengthData as &$data) {
        $data['rank'] = $rank++;
    }
    
    return $strengthData;
}

/**
 * Get data needed for the Fact Finder page
 */
function getFactFinderData() {
    global $season, $week;
    
    // Always use the current season from global variable
    $selectedYear = $season;
    
    // Get selected week from URL parameters, defaulting to current week + 1
    $selectedWeek = isset($_GET['week']) ? (int)$_GET['week'] : ($week + 1);
    
    // Get available weeks for current season (1-17 for regular season)
    $availableWeeks = [];
    for ($i = 1; $i <= 17; $i++) {
        $availableWeeks[] = $i;
    }
    
    // Get current season matchups for the selected week
    $currentMatchups = [];
    if ($selectedWeek) {
        $matchupsQuery = query("SELECT s.manager1_id, s.manager2_id, m1.name as manager1_name, m2.name as manager2_name 
                              FROM schedule s 
                              JOIN managers m1 ON s.manager1_id = m1.id 
                              JOIN managers m2 ON s.manager2_id = m2.id 
                              WHERE s.year = $selectedYear AND s.week = $selectedWeek");
        while ($row = fetch_array($matchupsQuery)) {
            $currentMatchups[] = [
                'manager1_id' => $row['manager1_id'],
                'manager2_id' => $row['manager2_id'],
                'manager1_name' => $row['manager1_name'],
                'manager2_name' => $row['manager2_name']
            ];
        }
    }
    
    return [
        'availableWeeks' => $availableWeeks,
        'currentMatchups' => $currentMatchups,
        'selectedYear' => $selectedYear,
        'selectedWeek' => $selectedWeek
    ];
}

/**
 * Get head-to-head fun facts between two managers
 */
function getHeadToHeadFacts($manager1_id, $manager2_id, $manager1_name, $manager2_name) {
    global $season;
    $facts = [];
    
    // Get all-time head-to-head record (avoid double counting by using manager1_id < manager2_id)
    $h2hQuery = query("SELECT 
        SUM(CASE WHEN (manager1_id = $manager1_id AND manager1_score > manager2_score) OR 
                     (manager1_id = $manager2_id AND manager2_score > manager1_score) THEN 1 ELSE 0 END) as manager1_wins,
        SUM(CASE WHEN (manager1_id = $manager2_id AND manager1_score > manager2_score) OR 
                     (manager1_id = $manager1_id AND manager2_score > manager1_score) THEN 1 ELSE 0 END) as manager2_wins,
        COUNT(*) as total_games
        FROM regular_season_matchups 
        WHERE ((manager1_id = $manager1_id AND manager2_id = $manager2_id) OR 
               (manager1_id = $manager2_id AND manager2_id = $manager1_id))
        AND manager1_id < manager2_id");
    
    $h2hRow = fetch_array($h2hQuery);
    if ($h2hRow && $h2hRow['total_games'] > 0) {
        $facts[] = "All-time record: {$manager1_name} {$h2hRow['manager1_wins']}-{$h2hRow['manager2_wins']} vs {$manager2_name}";
    }
    
    // Get highest scoring game between these managers (avoid duplicates)
    $highScoreQuery = query("SELECT year, week_number, manager1_score, manager2_score,
        (manager1_score + manager2_score) as total_points
        FROM regular_season_matchups 
        WHERE ((manager1_id = $manager1_id AND manager2_id = $manager2_id) OR 
               (manager1_id = $manager2_id AND manager2_id = $manager1_id))
        AND manager1_id < manager2_id
        ORDER BY total_points DESC LIMIT 1");
    
    $highScoreRow = fetch_array($highScoreQuery);
    if ($highScoreRow) {
        // Determine which score belongs to which manager
        if ($manager1_id < $manager2_id) {
            $score1 = $highScoreRow['manager1_score'];
            $score2 = $highScoreRow['manager2_score'];
        } else {
            $score1 = $highScoreRow['manager2_score'];
            $score2 = $highScoreRow['manager1_score'];
        }
        $facts[] = "Highest-scoring H2H game: {$score1}-{$score2} ({$highScoreRow['year']} Week {$highScoreRow['week_number']})";
    }
    
    // Get biggest margin of victory for manager1 (avoid duplicates)
    $biggestWinQuery = query("SELECT year, week_number, manager1_score, manager2_score,
        ABS(manager1_score - manager2_score) as margin
        FROM regular_season_matchups 
        WHERE ((manager1_id = $manager1_id AND manager2_id = $manager2_id AND manager1_score > manager2_score) OR 
               (manager1_id = $manager2_id AND manager2_id = $manager1_id AND manager2_score > manager1_score))
        AND manager1_id < manager2_id
        ORDER BY margin DESC LIMIT 1");
    
    $biggestWinRow = fetch_array($biggestWinQuery);
    if ($biggestWinRow) {
        // Determine winner and scores based on manager IDs
        if ($manager1_id < $manager2_id) {
            $winner_score = $biggestWinRow['manager1_score'];
            $loser_score = $biggestWinRow['manager2_score'];
            $winner_name = $biggestWinRow['manager1_score'] > $biggestWinRow['manager2_score'] ? $manager1_name : $manager2_name;
        } else {
            $winner_score = $biggestWinRow['manager2_score'];
            $loser_score = $biggestWinRow['manager1_score'];
            $winner_name = $biggestWinRow['manager2_score'] > $biggestWinRow['manager1_score'] ? $manager1_name : $manager2_name;
        }
        $facts[] = "{$winner_name}'s biggest win: {$winner_score}-{$loser_score} (margin: {$biggestWinRow['margin']})";
    }
    
    // Check for recent trends (last 3 years) - avoid double counting
    $recentYears = $season - 2;
    $recentTrendQuery = query("SELECT 
        SUM(CASE WHEN (manager1_id = $manager1_id AND manager1_score > manager2_score) OR 
                     (manager1_id = $manager2_id AND manager2_score > manager1_score) THEN 1 ELSE 0 END) as recent_wins,
        COUNT(*) as recent_games
        FROM regular_season_matchups 
        WHERE ((manager1_id = $manager1_id AND manager2_id = $manager2_id) OR 
               (manager1_id = $manager2_id AND manager2_id = $manager1_id))
        AND manager1_id < manager2_id
        AND year >= $recentYears");
    
    $recentRow = fetch_array($recentTrendQuery);
    if ($recentRow && $recentRow['recent_games'] > 0) {
        $recent_losses = $recentRow['recent_games'] - $recentRow['recent_wins'];
        if ($recentRow['recent_wins'] > $recent_losses) {
            $facts[] = "Recent dominance: {$manager1_name} won {$recentRow['recent_wins']} of last {$recentRow['recent_games']} games vs {$manager2_name}";
        } elseif ($recent_losses > $recentRow['recent_wins']) {
            $facts[] = "Recent dominance: {$manager2_name} won {$recent_losses} of last {$recentRow['recent_games']} games vs {$manager1_name}";
        }
    }
    
    // Check for playoff history (avoid double counting)
    $playoffQuery = query("SELECT COUNT(*) as playoff_meetings, 
        SUM(CASE WHEN (manager1_id = $manager1_id AND manager1_score > manager2_score) OR 
                     (manager1_id = $manager2_id AND manager2_score > manager1_score) THEN 1 ELSE 0 END) as playoff_wins
        FROM playoff_matchups 
        WHERE ((manager1_id = $manager1_id AND manager2_id = $manager2_id) OR 
               (manager1_id = $manager2_id AND manager2_id = $manager1_id))
        AND manager1_id < manager2_id");
    
    $playoffRow = fetch_array($playoffQuery);
    if ($playoffRow && $playoffRow['playoff_meetings'] > 0) {
        $playoff_losses = $playoffRow['playoff_meetings'] - $playoffRow['playoff_wins'];
        $facts[] = "Playoff history: {$manager1_name} {$playoffRow['playoff_wins']}-{$playoff_losses} vs {$manager2_name}";
    }
    
    // Check for revenge scenarios (if they met in playoffs last year)
    $lastYear = $season - 1;
    $revengeQuery = query("SELECT COUNT(*) as revenge_count, round
        FROM playoff_matchups 
        WHERE ((manager1_id = $manager1_id AND manager2_id = $manager2_id) OR 
               (manager1_id = $manager2_id AND manager2_id = $manager1_id))
        AND manager1_id < manager2_id
        AND year = $lastYear");
    
    $revengeRow = fetch_array($revengeQuery);
    if ($revengeRow && $revengeRow['revenge_count'] > 0) {
        $facts[] = "Revenge game! These teams met in the {$revengeRow['round']} last year";
    }
    
    // Check if either manager appears in notable categories against each other
    $foesData = getManagerVsManagerStats($manager1_id, $manager2_id, $manager1_name, $manager2_name);
    
    // Add foes-based facts
    if (!empty($foesData)) {
        // Check for extreme records
        if (isset($foesData['overall_win_pct']) && $foesData['overall_win_pct'] >= 80) {
            $leader = $foesData['overall_win_pct'] > 50 ? $manager1_name : $manager2_name;
            $facts[] = "Domination alert: {$leader} has a {$foesData['overall_win_pct']}% win rate in this matchup";
        }
        
        // Check for high-scoring averages
        if (isset($foesData['average_combined']) && $foesData['average_combined'] > 250) {
            $facts[] = "High-scoring rivalry: These teams average {$foesData['average_combined']} combined points";
        }
        
        // Check for extreme margins
        if (isset($foesData['biggest_blowout']) && $foesData['biggest_blowout'] > 50) {
            $facts[] = "Historic blowout: Largest margin of victory was {$foesData['biggest_blowout']} points";
        }
        
        // Check for nail-biters
        if (isset($foesData['closest_game']) && $foesData['closest_game'] < 5) {
            $facts[] = "Nail-biter history: Closest game decided by only {$foesData['closest_game']} points";
        }
    }
    
    return $facts;
}

/**
 * Get specific head-to-head stats between two managers (similar to getFoesArray but for any two managers)
 */
function getManagerVsManagerStats($manager1_id, $manager2_id, $manager1_name, $manager2_name) {
    $stats = [];
    
    // Get all matchups between these two managers
    $matchupQuery = query("SELECT year, week_number, manager1_id, manager2_id, manager1_score, manager2_score,
        ABS(manager1_score - manager2_score) as margin,
        (manager1_score + manager2_score) as total_points
        FROM regular_season_matchups 
        WHERE ((manager1_id = $manager1_id AND manager2_id = $manager2_id) OR 
               (manager1_id = $manager2_id AND manager2_id = $manager1_id))
        AND manager1_id < manager2_id");
    
    $games = [];
    while ($row = fetch_array($matchupQuery)) {
        $games[] = $row;
    }
    
    if (count($games) > 0) {
        $manager1_wins = 0;
        $total_points = 0;
        $margins = [];
        
        foreach ($games as $game) {
            // Determine who won based on the actual manager IDs in the game
            if (($game['manager1_id'] == $manager1_id && $game['manager1_score'] > $game['manager2_score']) ||
                ($game['manager1_id'] == $manager2_id && $game['manager2_score'] > $game['manager1_score'])) {
                $manager1_wins++;
            }
            
            $total_points += $game['total_points'];
            $margins[] = $game['margin'];
        }
        
        $stats['total_games'] = count($games);
        $stats['manager1_wins'] = $manager1_wins;
        $stats['manager2_wins'] = $stats['total_games'] - $manager1_wins;
        $stats['overall_win_pct'] = round(($manager1_wins / $stats['total_games']) * 100, 1);
        $stats['average_combined'] = round($total_points / $stats['total_games'], 1);
        $stats['biggest_blowout'] = max($margins);
        $stats['closest_game'] = min($margins);
    }
    
    return $stats;
}

/**
 * Get playoff schedule info based on year, week, and playoff round
 */
function getPlayoffScheduleInfo($year, $week, $round)
{
    $schedule = [];
    $playoffStartWeek = ($year >= 2021) ? 15 : 14;
    $lastRegularWeek = $playoffStartWeek - 1;
    
    if ($round === 'Quarterfinal') {
        // Get final regular season standings to determine seeding
        $standings = weekStandings($year, $lastRegularWeek);
        
        // If no standings data available, return empty schedule
        if (empty($standings)) {
            return [];
        }
        
        // Convert standings to array sorted by rank
        $sortedStandings = [];
        foreach ($standings as $managerName => $rank) {
            $sortedStandings[$rank] = [
                'name' => $managerName,
                'seed' => $rank,
                'manager_id' => getManagerId($managerName)
            ];
        }
        ksort($sortedStandings); // Sort by seed (rank)
        
        // Add bye weeks for #1 and #2 seeds
        if (isset($sortedStandings[1])) {
            $schedule[] = [
                'manager1' => $sortedStandings[1]['name'] . ' (#1 seed - Bye)',
                'manager2' => '',
                'manager1_id' => $sortedStandings[1]['manager_id'],
                'manager2_id' => '',
                'record' => '',
                'streak' => '',
                'postseason_record' => '',
                'is_bye' => true
            ];
        }
        
        if (isset($sortedStandings[2])) {
            $schedule[] = [
                'manager1' => $sortedStandings[2]['name'] . ' (#2 seed - Bye)',
                'manager2' => '',
                'manager1_id' => $sortedStandings[2]['manager_id'],
                'manager2_id' => '',
                'record' => '',
                'streak' => '',
                'postseason_record' => '',
                'is_bye' => true
            ];
        }
        
        // Add matchups: #3 vs #6, #4 vs #5 (only if we have 6+ teams)
        if (isset($sortedStandings[3]) && isset($sortedStandings[6])) {
            $manager1_id = $sortedStandings[3]['manager_id'];
            $manager2_id = $sortedStandings[6]['manager_id'];
            $manager1 = $sortedStandings[3]['name'] . ' (#3 seed)';
            $manager2 = $sortedStandings[6]['name'] . ' (#6 seed)';
            
            // Get H2H records
            $h2hInfo = getManagerH2HInfo($manager1_id, $manager2_id);
            
            $schedule[] = [
                'manager1' => $manager1,
                'manager2' => $manager2,
                'manager1_id' => $manager1_id,
                'manager2_id' => $manager2_id,
                'record' => $h2hInfo['regular_record'],
                'streak' => $h2hInfo['streak'],
                'postseason_record' => $h2hInfo['postseason_record'],
                'is_bye' => false
            ];
        }
        
        if (isset($sortedStandings[4]) && isset($sortedStandings[5])) {
            $manager1_id = $sortedStandings[4]['manager_id'];
            $manager2_id = $sortedStandings[5]['manager_id'];
            $manager1 = $sortedStandings[4]['name'] . ' (#4 seed)';
            $manager2 = $sortedStandings[5]['name'] . ' (#5 seed)';
            
            // Get H2H records
            $h2hInfo = getManagerH2HInfo($manager1_id, $manager2_id);
            
            $schedule[] = [
                'manager1' => $manager1,
                'manager2' => $manager2,
                'manager1_id' => $manager1_id,
                'manager2_id' => $manager2_id,
                'record' => $h2hInfo['regular_record'],
                'streak' => $h2hInfo['streak'],
                'postseason_record' => $h2hInfo['postseason_record'],
                'is_bye' => false
            ];
        }
        
    } elseif ($round === 'Semifinal' || $round === 'Final') {
        // Get matchups from playoff_matchups table for current round
        $result = query("SELECT pm.manager1_id, pm.manager2_id, pm.manager1_seed, pm.manager2_seed,
            m1.name as manager1, m2.name as manager2
            FROM playoff_matchups pm
            JOIN managers m1 ON pm.manager1_id = m1.id
            JOIN managers m2 ON pm.manager2_id = m2.id
            WHERE pm.year = $year AND pm.round = '$round'");
        
        $hasMatchups = false;
        while ($row = fetch_array($result)) {
            $hasMatchups = true;
            $manager1_id = $row['manager1_id'];
            $manager2_id = $row['manager2_id'];
            $manager1 = $row['manager1'];
            $manager2 = $row['manager2'];
            
            // Get H2H records
            $h2hInfo = getManagerH2HInfo($manager1_id, $manager2_id);
            
            $schedule[] = [
                'manager1' => $manager1 . ' (#' . $row['manager1_seed'] . ' seed)',
                'manager2' => $manager2 . ' (#' . $row['manager2_seed'] . ' seed)',
                'manager1_id' => $manager1_id,
                'manager2_id' => $manager2_id,
                'record' => $h2hInfo['regular_record'],
                'streak' => $h2hInfo['streak'],
                'postseason_record' => $h2hInfo['postseason_record'],
                'is_bye' => false
            ];
        }
        
        // If no matchups found, show placeholder message
        if (!$hasMatchups) {
            $schedule[] = [
                'manager1' => 'Matchups will be determined based on',
                'manager2' => 'previous round results',
                'manager1_id' => '',
                'manager2_id' => '',
                'record' => '',
                'streak' => '',
                'postseason_record' => '',
                'is_bye' => true
            ];
        }
    }
    
    return $schedule;
}

/**
 * Get head-to-head information between two managers
 */
function getManagerH2HInfo($manager1_id, $manager2_id)
{
    // Get historical head-to-head record - only count each game once by looking at one direction
    $h2hResult = query("SELECT 
        SUM(CASE WHEN manager1_score > manager2_score THEN 1 ELSE 0 END) as manager1_wins,
        SUM(CASE WHEN manager2_score > manager1_score THEN 1 ELSE 0 END) as manager2_wins
        FROM regular_season_matchups 
        WHERE manager1_id = $manager1_id AND manager2_id = $manager2_id");
    
    $h2hRow = fetch_array($h2hResult);
    $manager1_wins = $h2hRow['manager1_wins'] ?? 0;
    $manager2_wins = $h2hRow['manager2_wins'] ?? 0;
    $regular_record = $manager1_wins . '-' . $manager2_wins;
    
    // Get postseason head-to-head record between these two managers
    $postseasonH2HResult = query("SELECT 
        SUM(CASE WHEN manager1_id = $manager1_id AND manager1_score > manager2_score THEN 1 ELSE 0 END) +
        SUM(CASE WHEN manager2_id = $manager1_id AND manager2_score > manager1_score THEN 1 ELSE 0 END) as manager1_wins,
        SUM(CASE WHEN manager1_id = $manager2_id AND manager1_score > manager2_score THEN 1 ELSE 0 END) +
        SUM(CASE WHEN manager2_id = $manager2_id AND manager2_score > manager1_score THEN 1 ELSE 0 END) as manager2_wins
        FROM playoff_matchups 
        WHERE (manager1_id = $manager1_id AND manager2_id = $manager2_id) 
           OR (manager1_id = $manager2_id AND manager2_id = $manager1_id)");
    
    $postseasonH2HRow = fetch_array($postseasonH2HResult);
    $manager1_postseason_wins = $postseasonH2HRow['manager1_wins'] ?? 0;
    $manager2_postseason_wins = $postseasonH2HRow['manager2_wins'] ?? 0;
    $postseason_record = $manager1_postseason_wins . '-' . $manager2_postseason_wins;
    
    // Get current streak - only look at one direction to avoid duplicates
    $streakResult = query("SELECT year, week_number, manager1_id, manager2_id, manager1_score, manager2_score
        FROM regular_season_matchups 
        WHERE (manager1_id = $manager1_id AND manager2_id = $manager2_id)
        ORDER BY year DESC, week_number DESC");
    
    $streak = 0;
    $streakWinner = '';
    $lastWinner = '';
    
    while ($streakRow = fetch_array($streakResult)) {
        $gameWinner = '';
        if ($streakRow['manager1_score'] > $streakRow['manager2_score']) {
            $gameWinner = getManagerName($streakRow['manager1_id']);
        } elseif ($streakRow['manager2_score'] > $streakRow['manager1_score']) {
            $gameWinner = getManagerName($streakRow['manager2_id']);
        } else {
            continue; // Skip ties
        }
        
        if ($lastWinner == '') {
            $lastWinner = $gameWinner;
            $streakWinner = $gameWinner;
            $streak = 1;
        } elseif ($lastWinner == $gameWinner) {
            $streak++;
        } else {
            break;
        }
    }
    
    $streakText = $streak > 0 ? $streakWinner . ' ' . $streak : 'Even';
    
    return [
        'regular_record' => $regular_record,
        'postseason_record' => $postseason_record,
        'streak' => $streakText
    ];
}
