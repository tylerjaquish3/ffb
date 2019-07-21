<?php

//local connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ffb";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, '3306');
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
}

function getDashboardNumbers($conn)
{
    $response = [];

    $result = mysqli_query($conn,"select count(distinct(year)) as num_years from finishes");
    while($row = mysqli_fetch_array($result)) 
    {
        $response['seasons'] = $row['num_years'];
    }

    $result = mysqli_query($conn,"SELECT count(distinct(manager_id)) as winners FROM finishes WHERE finish = 1");
    while($row = mysqli_fetch_array($result)) 
    {
        $response['unique_winners'] = $row['winners'];
    }

    $result = mysqli_query($conn, "SELECT MAX(championships) as championships FROM (SELECT count(manager_id) as championships FROM finishes WHERE finish = 1 group by manager_id ORDER BY championships DESC LIMIT 1) as max_num");
    while($row = mysqli_fetch_array($result)) 
    {
        $response['most_championships_number'] = $row['championships'];
    }

    $tempName = '';
    $result = mysqli_query($conn, "SELECT count(manager_id) as championships, name FROM finishes JOIN managers on managers.id = finishes.manager_id  WHERE finish = 1 GROUP BY name HAVING count(manager_id) = ".$response['most_championships_number']);
    while($row = mysqli_fetch_array($result)) 
    {
        if ($tempName == '') {
            $tempName = $row['name'];
        } else {
            $tempName .= ', '.$row['name'];
        }
    }

    $response['most_championships_manager'] = $tempName;

    $result = mysqli_query($conn, "SELECT count(manager1_id) as wins FROM regular_season_matchups rsm WHERE manager1_score > manager2_score GROUP BY manager1_id ORDER BY count(manager1_id) DESC LIMIT 1");
    while($row = mysqli_fetch_array($result)) 
    {
        $response['most_wins_number'] = $row['wins'];
    }

    $tempName = '';
    $result = mysqli_query($conn, "SELECT count(manager1_id) as championships, name FROM regular_season_matchups rsm JOIN managers on managers.id = rsm.manager1_id   WHERE manager1_score > manager2_score GROUP BY name HAVING count(manager1_id) = ".$response['most_wins_number']);
    while($row = mysqli_fetch_array($result)) 
    {
        if ($tempName == '') {
            $tempName = $row['name'];
        } else {
            $tempName .= ', '.$row['name'];
        }
    }

    $response['most_wins_manager'] = $tempName;

    return $response;
}

function getPostseasonChartNumbers($conn)
{
    $response = [];
    
    $managers = '';
    $result2 = mysqli_query($conn, "SELECT * FROM managers");
    while($manager = mysqli_fetch_array($result2)) 
    {
        $response['managers'][] = $manager['name'];
        $managerId = $manager['id'];

        $ships = $appearances = $shipAppearances = 0;
        $year = 0000;
        $result = mysqli_query($conn, "SELECT * FROM playoff_matchups WHERE manager1_id = $managerId OR manager2_id = $managerId");
        while($row = mysqli_fetch_array($result)) 
        {
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

        $result = mysqli_query($conn, "SELECT * FROM managers WHERE name = '".$_GET['id']."'");
        while($row = mysqli_fetch_array($result)) 
        {
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
        while($row = mysqli_fetch_array($result)) 
        {
            if ($row['name'] == $_GET['id']) {
                $response['record'] = $row['wins']." - ".$row['losses'];
                $response['recordRank'] = $rank;
            }
            
            $rank++;
        }

        $ships = 0;
        $years = '';
        $result = mysqli_query($conn, "SELECT * FROM playoff_matchups WHERE manager1_id = $managerId OR manager2_id = $managerId");
        while($row = mysqli_fetch_array($result)) 
        {
            // Calc championships
            if ($row['round'] == 'Final') {
                if ($row['manager1_id'] == $managerId && $row['manager1_score'] > $row['manager2_score']) {
                    $ships++;
                    $years .= $row['year'].', ';
                }

                if ($row['manager2_id'] == $managerId && $row['manager2_score'] > $row['manager1_score']) {
                    $ships++;
                    $years .= $row['year'].', ';
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
        while($row = mysqli_fetch_array($result)) 
        {
            // Calc playoff record
            if ($row['name'] == $_GET['id']) {
                $wins = $row['winsTop'] + $row['winsBottom'];
                $losses = $row['lossesTop'] + $row['lossesBottom'];

                $response['playoffRecord'] = $wins." - ".$losses;
                $response['playoffRecordRank'] = $rank;
            }

            $rank++;
        }

        // Calc total points and rank
        $rank = 1;
        $result = mysqli_query($conn, "SELECT SUM(manager1_score) as total_points, manager1_id 
            FROM regular_season_matchups 
            GROUP BY manager1_id ORDER BY total_points DESC;");
        while($row = mysqli_fetch_array($result)) 
        {
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