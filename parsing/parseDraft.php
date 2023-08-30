<?php

$headerRow = [];

$row = 1;
$overallPick = $prevYear = $roundPick = 0;
if (($handle = fopen("2019draft.csv", "r")) !== FALSE) {

    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {

        var_dump($data);

        $currentYear = $data[0];

        if ($currentYear != $prevYear) {
            $overallPick = 0;
        }

        if (strpos($data[1], 'Round') !== false) {
            $currentRound = $data[1];
        } else {
            $roundPick = $data[1];
            $overallPick++;
        }

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

        if (isset($data[2]) && $data[2] != '') {
            $playerArray = explode('(', $data[2]);
            $player = mysqli_real_escape_string($conn, $playerArray[0]);
            $postionArray = explode('- ', $playerArray[1]);
            $position = str_replace(')', '', $postionArray[1]);
            $managername = $data[3];

            $round = str_replace('Round ', '', $currentRound);

            // look up manager id
            $result = query("SELECT * FROM managers WHERE name = '$managername'");
            while ($row2 = fetch_array($result)) {
                $manager = $row2['id'];
            }

            if ($currentYear != '' && isset($manager) && isset($roundPick)) {
                $sql = "INSERT INTO draft (year, round, round_pick, overall_pick, manager_id, position, player) 
                    VALUES ($currentYear, $round, $roundPick, $overallPick, $manager, '$position', '$player')";
                // var_dump($sql);
                query($sql);
            }
        }

        $prevYear = $currentYear;

        $row++;
    }

    var_dump('done');

    fclose($handle);
}
