<?php

header('Content-Type: application/json');
require_once '../functions.php';

$dashboardNumbers = getDashboardNumbers();
$postseasonChart = getPostseasonChartNumbers();

echo json_encode([
    'dashboardNumbers' => $dashboardNumbers,
    'postseasonChart' => $postseasonChart
]);

/**
 * Get dashboard numbers for the main statistics
 */
function getDashboardNumbers()
{
    $response = [];

    $result = query("select count(distinct(year)) as num_years from finishes");
    while ($row = fetch_array($result)) {
        $response['seasons'] = $row['num_years'];
    }

    $result = query("SELECT count(distinct(manager_id)) as winners FROM finishes WHERE finish = 1");
    while ($row = fetch_array($result)) {
        $response['unique_winners'] = $row['winners'];
    }

    $result = query("SELECT name FROM finishes JOIN managers on managers.id = finishes.manager_id WHERE finish = 1 order by year desc limit 1");
    while ($row = fetch_array($result)) {
        $response['defending_champ'] = $row['name'];
    }

    $result = query("SELECT MAX(championships) as championships FROM (SELECT count(manager_id) as championships FROM finishes WHERE finish = 1 group by manager_id ORDER BY championships DESC LIMIT 1) as max_num");
    while ($row = fetch_array($result)) {
        $response['most_championships_number'] = $row['championships'];
    }

    $tempName = '';
    $result = query("SELECT count(manager_id) as championships, name FROM finishes JOIN managers on managers.id = finishes.manager_id  WHERE finish = 1 GROUP BY name HAVING count(manager_id) = " . $response['most_championships_number']);
    while ($row = fetch_array($result)) {
        if ($tempName == '') {
            $tempName = $row['name'];
        } else {
            $tempName .= ', ' . $row['name'];
        }
    }

    $response['most_championships_manager'] = $tempName;

    $result = query("SELECT count(manager1_id) as wins FROM regular_season_matchups rsm WHERE manager1_score > manager2_score GROUP BY manager1_id ORDER BY count(manager1_id) DESC LIMIT 1");
    while ($row = fetch_array($result)) {
        $response['most_wins_number'] = $row['wins'];
    }

    $tempName = '';
    $result = query("SELECT count(manager1_id) as championships, name FROM regular_season_matchups rsm JOIN managers on managers.id = rsm.manager1_id WHERE manager1_score > manager2_score GROUP BY name HAVING count(manager1_id) = " . $response['most_wins_number']);
    while ($row = fetch_array($result)) {
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
 * Get postseason chart numbers for the main statistics
 */
function getPostseasonChartNumbers()
{
    $response = [];

    $result2 = query("SELECT * FROM managers");
    while ($manager = fetch_array($result2)) {
        $response['managers'][] = $manager['name'];
        $managerId = $manager['id'];

        $ships = $appearances = $shipAppearances = 0;
        $year = 0000;
        $result = query("SELECT * FROM playoff_matchups WHERE manager1_id = $managerId OR manager2_id = $managerId");
        while ($row = fetch_array($result)) {
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