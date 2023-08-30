<?php

$headerRow = [];

$row = 1;
if (($handle = fopen("2019_stats.csv", "r")) !== FALSE) {

    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {

        if ($row > 1) {
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

            $player = $data[0];
            $games = $data[1];
            $points = $data[2];
            $yards = $data[3];
            $touchdowns = $data[4];
            $receptions = $data[5];

            $sql = "UPDATE preseason_rankings SET
                games = $games,
                rec = $receptions,
                yards = $yards,
                touchdowns = $touchdowns,
                points = $points
                WHERE player = '{$player}'";

            // var_dump($sql);die;
            query($sql);

        }

        $row++;
    }

    var_dump('done');

    fclose($handle);
}
