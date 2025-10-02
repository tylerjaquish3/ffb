<?php
header('Content-Type: application/json');
require_once '../functions.php';

$response = [];

// Main data sets
$response['regSeasonMatchups'] = getRegularSeasonMatchups();
$response['seasonWins'] = getSeasonWins();
$response['winsChart'] = getWinsChartNumbers();
$response['scatterChart'] = getPointMargins();
$response['pfwins'] = getPfWinsData();
$response['allWeeks'] = getAllWeekOptions();
$response['recordsByWeek'] = getRecordsByWeek();
$response['regSeasonWinners'] = getRegularSeasonWinners();

// Managers for dropdowns
$managers = [];
$result = query("SELECT * FROM managers ORDER BY name ASC");
while ($row = fetch_array($result)) {
    $managers[] = $row;
}
$response['managers'] = $managers;

// Years for dropdowns
$years = [];
$result = query("SELECT distinct year FROM regular_season_matchups order by year desc");
while ($row = fetch_array($result)) {
    $years[] = $row['year'];
}
$response['years'] = $years;

// Weeks for dropdowns
$weeks = [];
$result = query("SELECT distinct week_number FROM regular_season_matchups");
while ($row = fetch_array($result)) {
    $weeks[] = $row['week_number'];
}
$response['weeks'] = $weeks;

// Game time points table
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
$gameTimePoints = [];
$sql = "SELECT sum(points) as points, year, week, manager, game_slot FROM rosters WHERE game_time is not null AND roster_spot NOT IN ('IR','BN') GROUP BY year, week, manager, game_slot";
$result = query($sql);
while ($row = fetch_array($result)) {
    $row['game_slot_label'] = isset($labels[$row['game_slot']]) ? $labels[$row['game_slot']] : null;
    $gameTimePoints[] = $row;
}
$response['gameTimePoints'] = $gameTimePoints;

// Total game time points table
$totalGameTimePoints = [];
$sql = "SELECT sum(points) as points, manager, game_slot FROM rosters WHERE points > 0 AND roster_spot NOT IN ('IR','BN') GROUP BY manager, game_slot";
$result = query($sql);
while ($row = fetch_array($result)) {
    $row['game_slot_label'] = isset($labels[$row['game_slot']]) ? $labels[$row['game_slot']] : null;
    $totalGameTimePoints[] = $row;
}
$response['totalGameTimePoints'] = $totalGameTimePoints;

echo json_encode($response);


/**
 * Get season wins for each manager for each year
 */
function getSeasonWins()
{
    $response = [];
    $result = query("SELECT * FROM finishes
        JOIN managers ON managers.id = finishes.manager_id");
    while ($row = fetch_array($result)) {
        $managerId = $row['manager_id'];
        $managerName = strtolower($row['name']);
        $year = $row['year'];
        $wins = 0;
        $result2 = query("SELECT * FROM regular_season_matchups
            WHERE manager1_id = " . $managerId . " AND year = " . $year);
        while ($row2 = fetch_array($result2)) {
            if ($row2['manager1_score'] > $row2['manager2_score']) {
                $wins++;
            }
        }

        $response[$year][$managerName] = $wins;
    }

    return $response;
}

/**
 * Get wins chart numbers for each manager
 */
function getWinsChartNumbers()
{
    $response = ['years' => ''];

    $result = query("SELECT DISTINCT year FROM finishes");
    while ($row = fetch_array($result)) {
        $response['years'] .= $row['year'].', ';
    }

    $result = query("SELECT * FROM finishes
        JOIN managers ON managers.id = finishes.manager_id");
    while ($row = fetch_array($result)) {
        $managerId = $row['manager_id'];
        $managerName = $row['name'];
        $year = $row['year'];
        $wins = 0;
        $result2 = query("SELECT * FROM regular_season_matchups
            WHERE manager1_id = ".$managerId." AND year = ".$year);
        while ($row2 = fetch_array($result2)) {
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
 * Get point margins for each matchup
 */
function getPointMargins()
{
    global $season;
    $response = [];
    $priorYear = null;

    $backFive = $season - 5;
    $result = query("SELECT year, week_number, AVG(manager1_score) as average FROM regular_season_matchups rsm
        WHERE year > $backFive
        GROUP BY year, week_number");
    while ($row = fetch_array($result)) {

        $year = $row['year'];
        $week = $row['week_number'];
        $average = $row['average'];
        $matchups = [];

        if ($year != $priorYear) {
            $yearWinMatchups = $yearLossMatchups = [];
        }
        $priorYear = $year;

        $result2 = query("SELECT * FROM regular_season_matchups
            WHERE year = $year AND week_number = $week");
        while ($row2 = fetch_array($result2)) {

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
 * Get points for wins data
 */
function getPfWinsData()
{
    $response = [];

    $result = query("SELECT * FROM finishes
        JOIN managers ON managers.id = finishes.manager_id");
    while ($row = fetch_array($result)) {
        $managerId = $row['manager_id'];
        $managerName = $row['name'];
        $year = $row['year'];
        $wins = $seasonPoints = $seasonPointsAgainst = 0;
        $array = [];

        $result2 = query("SELECT * FROM regular_season_matchups
            WHERE manager1_id = " . $managerId . " AND year = " . $year);
        while ($row2 = fetch_array($result2)) {
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
 * Get record by week for all managers
 * Returns an array with manager names as keys and arrays of week records as values
 */
function getRecordsByWeek()
{
    $sql = "SELECT 
                m.name as manager,
                rsm.week_number as week,
                SUM(CASE WHEN rsm.winning_manager_id = m.id THEN 1 ELSE 0 END) as wins,
                SUM(CASE WHEN rsm.losing_manager_id = m.id THEN 1 ELSE 0 END) as losses
            FROM 
                regular_season_matchups rsm
            JOIN
                managers m ON m.id = rsm.manager1_id
            GROUP BY 
                m.name, rsm.week_number
            ORDER BY 
                m.name, rsm.week_number";
    
    $result = query($sql);
    
    $recordsByWeek = array();
    $managers = array();
    $weeks = array();
    
    while ($row = fetch_array($result)) {
        $manager = $row['manager'];
        $week = $row['week'];
        $wins = $row['wins'];
        $losses = $row['losses'];
        
        if (!isset($recordsByWeek[$manager])) {
            $recordsByWeek[$manager] = array();
        }
        
        $recordsByWeek[$manager][$week] = $wins . '-' . $losses;
        $managers[$manager] = true;
        $weeks[$week] = true;
    }
    
    return array(
        'records' => $recordsByWeek,
        'managers' => array_keys($managers),
        'weeks' => array_keys($weeks)
    );
}