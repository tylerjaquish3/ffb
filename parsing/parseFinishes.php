<?php

    $headerRow = [];

    $row = 1;
    if (($handle = fopen("finishes.csv", "r")) !== FALSE) {
        
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {

            // var_dump($row);
            if ($row == 1) {
                $headerRow = $data; 
            } else {
                
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

                for ($i = 1; $i < 11; $i++) {
                    $managername = $headerRow[$i];
                    $finish = $data[$i];

                    // look up manager id
                    $result = mysqli_query($conn, "SELECT * FROM managers WHERE name = '$managername'");
                    while($row2 = mysqli_fetch_array($result)) 
                    {
                        $manager = $row2['id'];
                    }

                    if ($year != '' && isset($manager) && $finish != 'x') {
                        $sql = "INSERT INTO finishes (year, manager_id, finish) VALUES ($year, $manager, $finish)";
                        // var_dump($sql);
                        mysqli_query($conn, $sql);
                    }
                }

            }

            $row++;
        }

        var_dump('done');

        fclose($handle);

    }
