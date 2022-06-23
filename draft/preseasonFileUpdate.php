<?php

include('../functions.php');


$currentYear = date('Y');
// This takes in a csv file and will update preseason_rankings
// Files should be team abbr and then a column for one of the following:
// team changes, byes, sos, oline

// Remove retired players and player data
// Set all values as null before starting any of these updates (so skipped players dont have old data)


if (isset($_POST['team-change'])) {
    $file = 'files/'.$currentYear.'/teamChange.csv';
    $sql = $conn->prepare("UPDATE preseason_rankings SET team = ? WHERE player = ? OR alias = ?");

    if (($handle = fopen($file, "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {

            $player = remove_utf8_bom($data[0]);
            $team = $data[1];

            echo $player.' -> '.$team.PHP_EOL;
            // Save the data
            if ( false===$sql ) {
                die('prepare() failed: ' . htmlspecialchars($mysqli->error));
            }
            $rc = $sql->bind_param('sss', $team, $player, $player);
            if ( false===$rc ) {
                die('bind_param() failed: ' . htmlspecialchars($sql->error));
            }
            $rc = $sql->execute();
            if ( false===$rc ) {
                die('execute() failed: ' . htmlspecialchars($sql->error));
            }
        }
        fclose($handle);
        echo 'Finished updating team changes';
    }

}

if (isset($_POST['team-byes'])) {
    $file = 'files/'.$currentYear.'/byes.csv';
    if (($handle = fopen($file, "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            updateTeams('bye', remove_utf8_bom($data[0]), $data[1]);
        }
        fclose($handle);
        echo 'Finished updating byes';
    }
}

if (isset($_POST['team-sos'])) {
    $file = 'files/'.$currentYear.'/sos.csv';
    if (($handle = fopen($file, "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            updateTeams('sos', remove_utf8_bom($data[0]), $data[1]);
        }
        fclose($handle);
        echo 'Finished updating sos';
    }
}

if (isset($_POST['team-oline'])) {
    $file = 'files/'.$currentYear.'/oline.csv';
    if (($handle = fopen($file, "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            updateTeams('line', remove_utf8_bom($data[0]), $data[1]);
        }
        fclose($handle);
        echo 'Finished updating oline';
    }
}


function updateTeams($field, $team, $value) {
    global $conn;
    echo $team.PHP_EOL;
    $team = strtolower($team);

    $sql = $conn->prepare("UPDATE preseason_rankings SET $field = ? WHERE id = ?");
    $result = mysqli_query($conn, "SELECT * FROM preseason_rankings WHERE LOWER(team) = '{$team}'");
    while ($row = mysqli_fetch_array($result)) {
        // Save the data
        if ( false===$sql ) {
            die('prepare() failed: ' . htmlspecialchars($mysqli->error));
        }
        $rc = $sql->bind_param('ii', $value, $row['id']);
        if ( false===$rc ) {
            die('bind_param() failed: ' . htmlspecialchars($sql->error));
        }
        $rc = $sql->execute();
        if ( false===$rc ) {
            die('execute() failed: ' . htmlspecialchars($sql->error));
        }
    }
}

function remove_utf8_bom($text)
{
    $bom = pack('H*','EFBBBF');
    $text = preg_replace("/^$bom/", '', $text);
    $text = str_replace("'", "''", $text);
    $text = rtrim($text, " ");

    return $text;
}





// Player file updates
// Add rookies
// Update adp, points, proj_points

if (isset($_POST['player-rookies'])) {

    $file = 'files/'.$currentYear.'/rookies.csv';
    if (($handle = fopen($file, "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $player = remove_utf8_bom($data[0]);
            $pos = remove_utf8_bom($data[1]);
            $team = remove_utf8_bom($data[2]);
            echo $player;

            $sql = $conn->prepare("INSERT INTO preseason_rankings (player, position, team) VALUES (?,?,?)");
            $sql->bind_param('sss', $player, $pos, $team);
            $sql->execute();
        }
        fclose($handle);
        echo 'Finished adding rookies';
    }
}

if (isset($_POST['player-adp'])) {

    $file = 'files/'.$currentYear.'/adp.csv';
    if (($handle = fopen($file, "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            updatePlayers('adp', remove_utf8_bom($data[0]), $data[1]);
        }
        fclose($handle);
        echo 'Finished updating ADP';
    }
}

if (isset($_POST['player-projPoints'])) {

    $file = 'files/'.$currentYear.'/projPoints.csv';
    if (($handle = fopen($file, "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            updatePlayers('proj_points', remove_utf8_bom($data[0]), $data[1]);
        }
        fclose($handle);
        echo 'Finished updating proj points';
    }
}

if (isset($_POST['player-points'])) {

    $year = $currentYear-1;
    $result = mysqli_query($conn, "SELECT * FROM player_data WHERE year = $year and type = 'REG'");
    while ($row = mysqli_fetch_array($result)) {
        $player = $row['preseason_ranking_id'];

        $pass = ($row['pass_yards'] * .04) + ($row['pass_touchdowns'] * 4) + ($row['pass_interceptions'] * -2);
        $rush = ($row['rush_yards'] * .1) + ($row['rush_touchdowns'] * 6) + ($row['fumbles'] * -3);
        $rec = ($row['rec_yards'] * .1) + ($row['rec_touchdowns'] * 6) + ($row['rec_receptions'] * .5);

        // This number doesn't account for 2PT conv
        $points = $pass + $rush + $rec;

        // Save the data
        $sql = $conn->prepare("UPDATE preseason_rankings SET points = ? WHERE id = ?");
        $sql->bind_param('di', $points, $player);
        $sql->execute();
    }
    echo '<br>Finished updating points';
}


function updatePlayers($field, $player, $value) 
{
    global $conn;

    $found = false;
    $sql = $conn->prepare("UPDATE preseason_rankings SET $field = ? WHERE id = ?");
    $result = mysqli_query($conn, "SELECT * FROM preseason_rankings WHERE player = '{$player}' OR alias = '{$player}'");
    while ($row = mysqli_fetch_array($result)) {

        $found = true;
        // Save the data
        if ( false===$sql ) {
            echo $player;
            die('prepare() failed: ' . htmlspecialchars($mysqli->error));
        }
        $rc = $sql->bind_param('ii', $value, $row['id']);
        if ( false===$rc ) {
            echo $player;
            die('bind_param() failed: ' . htmlspecialchars($sql->error));
        }
        $rc = $sql->execute();
        if ( false===$rc ) {
            echo $player;
            die('execute() failed: ' . htmlspecialchars($sql->error));
        }
    }

    if (!$found) {
        echo $player.' Not found<br>';
    }
}

