<?php

$headerRow = [];

$row = 1;
if (($handle = fopen("seasonCSV.csv", "r")) !== FALSE) {

    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {

        if ($row > 7) {
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

            $manager = $data[0];
            $week = $data[1];
            $rosterSpot = $data[2];
            $player = $data[3];

            $sql = "SELECT * FROM 2019_rosters WHERE manager = '{$manager}' AND week = $week AND roster_spot = '{$rosterSpot}'";
            // var_dump($sql);
            $rosters = query($sql);
            while($dbRow = fetch_array($rosters)) {

                similar_text($dbRow['player'], $player, $perc);

                // var_dump('file row: '.$player);
                // var_dump('db name: '.$dbRow['player']);
                // var_dump('%: '.$perc);
                if ($perc > 50) {
// var_dump('entered: '.$dbRow['player']);
                    $rosterId = $dbRow['id'];
                }
            }

            $playerArray = explode('- ', $data[3]);
            $player = mysqli_real_escape_string($conn, $playerArray[0]);
            $position = $playerArray[1];

            if ($rosterSpot == "QB" || $rosterSpot == "WR" || $rosterSpot == "RB" || $rosterSpot == "TE" || $rosterSpot == "W/R/T") {
                addOffense($rosterId, $data, $conn);
            } elseif ($rosterSpot == "K") {
                addKicker($rosterId, $data, $conn);
            } elseif ($rosterSpot == "DEF") {
                addDefense($rosterId, $data, $conn);
            } elseif ($rosterSpot == "D" || $rosterSpot == "DB") {
                addIdp($rosterId, $data, $conn);
            } else {

                // Bench spots could be any position
                if (count($data) == 19) {
                    // Offense
                    addOffense($rosterId, $data, $conn);
                } elseif(count($data) == 18) {
                    // IDP
                    addIdp($rosterId, $data, $conn);
                } elseif (count($data) == 14) {
                    // Def
                    addDefense($rosterId, $data, $conn);
                } elseif (count($data) == 13) {
                    // Kicker
                    addKicker($rosterId, $data, $conn);
                }
            }
        }

        // if ($row == 12) {die;}

        $row++;
    }

    var_dump('done');

    fclose($handle);
}


function addOffense($rosterId, $stats, $conn)
{
    // var_dump($rosterId);die;
    $passYds = $passTds = $ints = $rushYds = $rushTds = $receptions = $recYds = $recTds = $fumbles = $fgMade = $patMade = "null";
    $tackles = $tackleAsst = $idpSacks = $idpInt = $idpFum = $defSacks = $defInt = $defFum = "null";

    $passYds = $stats[5];
    $passTds = $stats[6];
    $ints = $stats[7];
    $rushYds = $stats[9];
    $rushTds = $stats[10];
    $receptions = $stats[12];
    $recYds = $stats[13];
    $recTds = $stats[14];
    $fumbles = $stats[18];

    $sql = "INSERT INTO 2019_stats (roster_id, pass_yds, pass_tds, ints, rush_yds, rush_tds, receptions, rec_yds, rec_tds, fumbles, fg_made, pat_made,
            tackles, tackle_asst, idp_sacks, idp_int, idp_fum, def_sacks, def_int, def_fum)
        VALUES ($rosterId, $passYds, $passTds, $ints, $rushYds, $rushTds, $receptions, $recYds, $recTds, $fumbles, $fgMade, $patMade,
            $tackles, $tackleAsst, $idpSacks, $idpInt, $idpFum, $defSacks, $defInt, $defFum)";
    // var_dump($sql);
    query($sql);
}

function addKicker($rosterId, $stats, $conn)
{
    // var_dump($rosterId);die;
    $passYds = $passTds = $ints = $rushYds = $rushTds = $receptions = $recYds = $recTds = $fumbles = $fgMade = $patMade = "null";
    $tackles = $tackleAsst = $idpSacks = $idpInt = $idpFum = $defSacks = $defInt = $defFum = "null";

    $fgMade = $stats[4] + $stats[5] + $stats[6] + $stats[7] + $stats[8];
    $patMade = $stats[12];

    $sql = "INSERT INTO 2019_stats (roster_id, pass_yds, pass_tds, ints, rush_yds, rush_tds, receptions, rec_yds, rec_tds, fumbles, fg_made, pat_made,
            tackles, tackle_asst, idp_sacks, idp_int, idp_fum, def_sacks, def_int, def_fum)
        VALUES ($rosterId, $passYds, $passTds, $ints, $rushYds, $rushTds, $receptions, $recYds, $recTds, $fumbles, $fgMade, $patMade,
            $tackles, $tackleAsst, $idpSacks, $idpInt, $idpFum, $defSacks, $defInt, $defFum)";
    // var_dump($sql);
    query($sql);
}

function addDefense($rosterId, $stats, $conn)
{
    // var_dump($rosterId);die;
    $passYds = $passTds = $ints = $rushYds = $rushTds = $receptions = $recYds = $recTds = $fumbles = $fgMade = $patMade = "null";
    $tackles = $tackleAsst = $idpSacks = $idpInt = $idpFum = $defSacks = $defInt = $defFum = "null";

    $defSacks = $stats[5];
    $defInt = $stats[7];
    $defFum = $stats[8];

    $sql = "INSERT INTO 2019_stats (roster_id, pass_yds, pass_tds, ints, rush_yds, rush_tds, receptions, rec_yds, rec_tds, fumbles, fg_made, pat_made,
            tackles, tackle_asst, idp_sacks, idp_int, idp_fum, def_sacks, def_int, def_fum)
        VALUES ($rosterId, $passYds, $passTds, $ints, $rushYds, $rushTds, $receptions, $recYds, $recTds, $fumbles, $fgMade, $patMade,
            $tackles, $tackleAsst, $idpSacks, $idpInt, $idpFum, $defSacks, $defInt, $defFum)";
     //var_dump($sql);
    query($sql);
}

function addIdp($rosterId, $stats, $conn)
{
    // var_dump($rosterId);die;
    $passYds = $passTds = $ints = $rushYds = $rushTds = $receptions = $recYds = $recTds = $fumbles = $fgMade = $patMade = "null";
    $tackles = $tackleAsst = $idpSacks = $idpInt = $idpFum = $defSacks = $defInt = $defFum = "null";

    $tackles = $stats[6];
    $tackleAsst = $stats[7];
    $idpSacks = $stats[9];
    $idpInt = $stats[13];
    $idpFum = $stats[14] + $stats[15];

    $sql = "INSERT INTO 2019_stats (roster_id, pass_yds, pass_tds, ints, rush_yds, rush_tds, receptions, rec_yds, rec_tds, fumbles, fg_made, pat_made,
            tackles, tackle_asst, idp_sacks, idp_int, idp_fum, def_sacks, def_int, def_fum)
        VALUES ($rosterId, $passYds, $passTds, $ints, $rushYds, $rushTds, $receptions, $recYds, $recTds, $fumbles, $fgMade, $patMade,
            $tackles, $tackleAsst, $idpSacks, $idpInt, $idpFum, $defSacks, $defInt, $defFum)";
     //var_dump($sql);
    query($sql);
}
