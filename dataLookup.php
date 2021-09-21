<?php

include 'functions.php';

// var_dump($_POST);die;

// Lookup standings by manager
if (isset($_POST['dataType']) && $_POST['dataType'] == 'standings') {

    $return = '';
    $manager = $_POST['manager1'];
    $place = $_POST['place'];
    $standings = $allYears = [];
    $currYear = null;

    $result = mysqli_query($conn, "SELECT distinct year FROM regular_season_matchups ORDER BY YEAR DESC");
    while ($row = mysqli_fetch_array($result)) {
        $allYears[] = $row['year'];
    }

    $overallYearStandings = [];
    // Loop year by year
    foreach ($allYears as $year) {
        $yearStandings = [];
        $lastWeek = 1;
        for ($x = 1; $x < 11; $x++) {
            $yearStandings[] = [
                'man' => $x, 'wins' => 0, 'points' => 0
            ];
        }

        $result = mysqli_query($conn, "SELECT * FROM regular_season_matchups WHERE year = $year ORDER BY YEAR DESC, week_number asc");
        while ($row = mysqli_fetch_array($result)) {
            $week = $row['week_number']; 
        
            // If going to the next week, then store the standings at this point
            if ($week != $lastWeek) {
                // Sort by wins and then points (if tied for wins)
                usort($yearStandings, function($a, $b) {
                    if ($b['wins'] - $a['wins'] == 0) {
                        return $b['points'] - $a['points'];
                    }
                    return $b['wins'] - $a['wins'];
                });
                $overallYearStandings[$year][$week-1] = $yearStandings;
            }
            
            foreach ($yearStandings as &$standing) {
                if ($standing['man'] == $row['manager1_id']) {
                    if ($row['winning_manager_id'] == $row['manager1_id']) {
                        $standing['wins']++;
                    }
                    $standing['points'] += $row['manager1_score'];
                }
            }
            // Unset it so the reference (&) is removed
            unset($standing);
            
            $lastWeek = $week;
        }

        // Need to do this one more time to account for the last week of the season
        usort($yearStandings, function($a, $b) {
            if ($b['wins'] - $a['wins'] == 0) {
                return $b['points'] - $a['points'];
            }
            return $b['wins'] - $a['wins'];
        });
        $overallYearStandings[$year][$week] = $yearStandings;
    }

    // Now just get the ones associated to the place and manager passed in
    $allInPlace = [];
    foreach ($overallYearStandings as $year => $weeks) {
        foreach ($weeks as $week => $dude) {
            foreach ($dude as $spot => $man) {
                if ($place == ($spot+1) && $man['man'] == $manager && $man['points'] != 0) {
                    $allInPlace[] = [
                        'year' => $year,
                        'week' => $week,
                        'wins' => $man['wins'],
                        'points' => $man['points']
                    ];
                }
            }
        }
    }

    // Sort by year and week desc
    usort($allInPlace, function($b, $a) { 
        $rdiff = $a['year'] - $b['year'];
        if ($rdiff) return $rdiff; 
        return $a['week'] - $b['week']; 
    });

    // Return table rows
    foreach ($allInPlace as $data) {
        $losses = $data['week'] - $data['wins'];
        $record = $data['wins'].' - '.$losses;
        $return .= '<tr><td>'.$data['year'].'</td><td>'.$data['week'].'</td><td>'.$record.'</td><td>'.$data['points'].'</td></tr>';
    }

    $done = [
        'return' => $return,
        'count' => count($allInPlace)
    ];
    echo json_encode($done);
    die;
}

?>