<?php


    $row = 1;
    if (($handle = fopen("2019regseason.csv", "r")) !== FALSE) {
        
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            // $num = count($data);
            // echo "<p> $num fields in line $row: <br /></p>\n";
            $row++;

            // for ($c=0; $c < $num; $c++) {
            //     echo $data[$c] . "<br />\n";
            // }

            $year = $data[0];
            $week = $data[1];

            //local connection
            $servername = "localhost";
            $username = "root";
            $password = "";
            $dbname = "ffb";
            $port = '3307';

            // $servername = "tylerjaquish32172.ipagemysql.com";
            // $username = "kdc_admin";
            // $password = "kdc_ffb1";
            // $dbname = "ffb";
            // $port = "3306";

            // Create connection
            $conn = new mysqli($servername, $username, $password, $dbname, $port);
            // Check connection
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            } 

            $manager1name = $data[2];
            $manager2name = $data[3];

            $score = explode(" - ",$data[5]);
            $manager1score = $score[0];
            $manager2score = $score[1];

            // look up manager id
            $result = query("SELECT * FROM managers WHERE name = '$manager1name'");
            while($row = fetch_array($result)) 
            {
                $manager1 = $row['id'];
            }

            $result = query("SELECT * FROM managers WHERE name = '$manager2name'");
            while($row = fetch_array($result)) 
            {
                $manager2 = $row['id'];
            }

            if ($manager1score > $manager2score) {
                $winningManager = $manager1;
                $losingManager = $manager2;
            } else {
                $winningManager = $manager2;
                $losingManager = $manager1;
            }

            if ($year != '' && isset($manager1)) {
                $sql = "INSERT INTO regular_season_matchups (year, week_number, manager1_id, manager2_id, manager1_score, manager2_score, winning_manager_id, losing_manager_id) 
                VALUES ($year, $week, $manager1, $manager2, $manager1score, $manager2score, $winningManager, $losingManager)";
                var_dump($sql);
                query($sql);
            }

        }

        var_dump('done');

        fclose($handle);

    }
