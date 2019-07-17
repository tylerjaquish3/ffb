<?php

//local connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ffb";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, '3307');
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 

$dashboardNumbers = getDashboardNumbers($conn);
$profileName = getProfileName($conn);


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

function getProfileName($conn)
{
    $response = '';
    if (isset($_GET)) {
        $result = mysqli_query($conn, "SELECT * FROM managers WHERE id = ".$_GET['id']);
        while($row = mysqli_fetch_array($result)) 
        {
            $response = $row['name'];
        }
    } else {
        // redirect to index
    }

    return $response; 
}