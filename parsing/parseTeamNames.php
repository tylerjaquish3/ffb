<?php

$headerRow = [];

$row = 1;

if (($handle = fopen("teamNames.csv", "r")) !== FALSE) {

    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {

        var_dump($data);


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

        $year = $data[0];
        $teamName = mysqli_real_escape_string($conn, $data[1]);
        $managername = $data[2];
        $moves = $data[3];
        $trades = $data[4];

        if (isset($data[2]) && $data[2] != '') {

            // look up manager id
            $result = mysqli_query($conn, "SELECT * FROM managers WHERE name = '$managername'");
            while ($row2 = mysqli_fetch_array($result)) {
                $manager = $row2['id'];
            }

            if (isset($manager)) {
                $sql = "INSERT INTO team_names (manager_id, year, name, moves, trades) 
                    VALUES ($manager, $year, '$teamName', $moves, $trades)";
                // var_dump($sql);
                mysqli_query($conn, $sql);
            }
        }

        $row++;
    }

    var_dump('done');

    fclose($handle);
}
