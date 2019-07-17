<?php

    $nameQuarter = $nameSemi = $nameFinal = '';

    $row = 1;
    if (($handle = fopen("playoffMatchups.csv", "r")) !== FALSE) {
        
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {

            $row++;

            // var_dump($data);

            $getFirst = true;

            $year = $data[0];

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

            // first manager in matchup is set
            if ($nameFinal != '') {

                $nameFinal2 = getName($data[5]);
                $seedFinal2 = getSeed($data[5]);
                $scoreFinal2 = $data[6];

                // look up manager id
                $result = mysqli_query($conn, "SELECT * FROM managers WHERE name = '$nameFinal'");
                while($row = mysqli_fetch_array($result)) 
                {
                    $manager1 = $row['id'];
                }

                $result = mysqli_query($conn, "SELECT * FROM managers WHERE name = '$nameFinal2'");
                while($row = mysqli_fetch_array($result)) 
                {
                    $manager2 = $row['id'];
                }

                if ($year != '' && isset($manager1)) {
                    $sql = "INSERT INTO playoff_matchups (year, round, manager1_id, manager2_id, manager1_seed, manager2_seed, manager1_score, manager2_score) 
                    VALUES ($year, 'Final', $manager1, $manager2, $seedFinal, $seedFinal2, $scoreFinal, $scoreFinal2)";
                    var_dump($sql);
                    mysqli_query($conn, $sql);
                }

                $nameFinal = '';

                $getFirst = false;
            } 
            
            if ($nameSemi != '') {

                $nameSemi2 = getName($data[3]);
                $seedSemi2 = getSeed($data[3]);
                $scoreSemi2 = $data[4];

                // look up manager id
                $result = mysqli_query($conn, "SELECT * FROM managers WHERE name = '$nameSemi'");
                while($row = mysqli_fetch_array($result)) 
                {
                    $manager1 = $row['id'];
                }

                $result = mysqli_query($conn, "SELECT * FROM managers WHERE name = '$nameSemi2'");
                while($row = mysqli_fetch_array($result)) 
                {
                    $manager2 = $row['id'];
                }

                if ($year != '' && isset($manager1)) {
                    $sql = "INSERT INTO playoff_matchups (year, round, manager1_id, manager2_id, manager1_seed, manager2_seed, manager1_score, manager2_score) 
                    VALUES ($year, 'Semifinal', $manager1, $manager2, $seedSemi, $seedSemi2, $scoreSemi, $scoreSemi2)";
                    var_dump($sql);
                    mysqli_query($conn, $sql);
                }

                $nameSemi = '';

                $getFirst = false;

            } 
            if ($nameQuarter != '') {

                if ($data[1] != 'Bye') {
                    
                    $nameQuarter2 = getName($data[1]);
                    $seedQuarter2 = getSeed($data[1]);
                    $scoreQuarter2 = $data[2];

                    // look up manager id
                    $result = mysqli_query($conn, "SELECT * FROM managers WHERE name = '$nameQuarter'");
                    while($row = mysqli_fetch_array($result)) 
                    {
                        $manager1 = $row['id'];
                    }

                    $result = mysqli_query($conn, "SELECT * FROM managers WHERE name = '$nameQuarter2'");
                    while($row = mysqli_fetch_array($result)) 
                    {
                        $manager2 = $row['id'];
                    }

                    if ($year != '' && isset($manager1)) {
                        $sql = "INSERT INTO playoff_matchups (year, round, manager1_id, manager2_id, manager1_seed, manager2_seed, manager1_score, manager2_score) 
                        VALUES ($year, 'Quarterfinal', $manager1, $manager2, $seedQuarter, $seedQuarter2, $scoreQuarter, $scoreQuarter2)";
                        var_dump($sql);
                        mysqli_query($conn, $sql);
                    }

                }

                $nameQuarter = '';

                $getFirst = false;

            } 
            
            if ($getFirst) {
                // Get the first manager in each matchup
                if ($data[1] != '') {
                    $nameQuarter = getName($data[1]);
                    $seedQuarter = getSeed($data[1]);
                    $scoreQuarter = $data[2];
                }

                if ($data[3] != '') {
                    $nameSemi = getName($data[3]);
                    $seedSemi = getSeed($data[3]);
                    $scoreSemi = $data[4];
                }

                if ($data[5] != '') {
                    $nameFinal = getName($data[5]);
                    $seedFinal = getSeed($data[5]);
                    $scoreFinal = $data[6];
                }
            }

        }

        var_dump('done');

        fclose($handle);
    }

    function getName($string) 
    {
        $name = str_replace("(", "", $string);
        $name = str_replace(")", "", $name);
        $seedName = explode(" ", $name);

        if (isset($seedName[1])) {
            return $seedName[1];
        }

        return '';
    }

    function getSeed($string) 
    {
        $name = str_replace("(", "", $string);
        $name = str_replace(")", "", $name);
        $seedName = explode(" ", $name);

        if (isset($seedName[0])) {
            return $seedName[0];
        }

        return '';
    }
