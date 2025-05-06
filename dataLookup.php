<?php

include 'functions.php';

// var_dump($_POST);die;

// Lookup standings by manager
if (isset($_GET['dataType']) && $_GET['dataType'] == 'standings') {

    $return = [];
    $manager = $_GET['manager1'];
    $place = $_GET['place1'];
    $standings = $allYears = [];
    $currYear = null;

    $result = query("SELECT distinct year FROM regular_season_matchups ORDER BY YEAR DESC");
    while ($row = fetch_array($result)) {
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

        $result = query("SELECT * FROM regular_season_matchups WHERE year = $year ORDER BY YEAR DESC, week_number asc");
        while ($row = fetch_array($result)) {
            $week = $row['week_number']; 
        
            // If going to the next week, then store the standings at this point
            if ($week != $lastWeek) {
                // Sort by wins and then points (if tied for wins)
                usort($yearStandings, function($b, $a) { 
                    $rdiff = $a['wins'] - $b['wins'];
                    if ($rdiff) return $rdiff; 

                    if ($a['points'] > $b['points']) {
                        return 1;
                    } else if ($a['points'] < $b['points']) {
                        return -1;
                    }
                    return 0; 
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
        usort($yearStandings, function($b, $a) { 
            $rdiff = $a['wins'] - $b['wins'];
            if ($rdiff) return $rdiff; 

            if ($a['points'] > $b['points']) {
                return 1;
            } else if ($a['points'] < $b['points']) {
                return -1;
            }
            return 0; 
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
        $rdiff = $a['wins'] - $b['wins'];
        if ($rdiff) return $rdiff; 

        if ($a['points'] > $b['points']) {
            return 1;
        } else if ($a['points'] < $b['points']) {
            return -1;
        }
        return 0; 
    });

    // Return table rows
    foreach ($allInPlace as $data) {
        $losses = $data['week'] - $data['wins'];
        $record = $data['wins'].' - '.$losses;
        $return[] = [
            'year'      => $data['year'],
            'week'      => $data['week'],
            'record'    => $record,
            'points'    => round($data['points'], 1)
        ];
    }

    $content = new \stdClass();
    $content->data = $return;

    echo json_encode($content);
    die;

    // echo json_encode($done);
    // die;
}

if (isset($_GET['dataType']) && $_GET['dataType'] == 'league-standings') {

    $return = [];
    $year = $_GET['year'];
    $week = $_GET['week'];
    $nextWeek = $week + 1;
    $standings = [];

    for ($x = 1; $x < 11; $x++) {
        $standings[] = [
            'man' => $x, 'wins' => 0, 'losses' => 0, 'points' => 0, 'name' => '', 'next' => ''
        ];
    }

    $result = query("SELECT * FROM regular_season_matchups 
        JOIN managers ON regular_season_matchups.manager1_id = managers.id
        WHERE year = $year and week_number <= $week");
    while ($row = fetch_array($result)) {
        $week = $row['week_number']; 
    
        foreach ($standings as &$standing) {
            if ($standing['man'] == $row['manager1_id']) {
                if ($row['winning_manager_id'] == $row['manager1_id']) {
                    $standing['wins']++;
                } else {
                    $standing['losses']++;
                }
                $standing['name'] = $row['name'];
                $standing['points'] += $row['manager1_score'];
                
                // query to get the following week's matchup
                $result2 = query("SELECT * FROM regular_season_matchups 
                JOIN managers ON regular_season_matchups.manager2_id = managers.id
                WHERE year = $year AND week_number = $nextWeek AND manager1_id = ".$row['manager1_id']);
                while ($row2 = fetch_array($result2)) {
                    $win = $row2['winning_manager_id'] == $row2['manager1_id'] ? 'W' : 'L';
                    $standing['next'] = $row2['name'].' ('.$win.')';
                }
            }
        } 
    }

    // Sort by wins and points to get rank
    usort($standings, function($b, $a) { 
        $rdiff = $a['wins'] - $b['wins'];
        if ($rdiff) return $rdiff; 

        if ($a['points'] > $b['points']) {
            return 1;
        } else if ($a['points'] < $b['points']) {
            return -1;
        }
        return 0; 
    });

    $rank = 1;
    foreach ($standings as $data) {
        $record = $data['wins'].' - '.$data['losses'];
        $return[] = [
            'rank' => $rank,
            'manager' => $data['name'],
            'record' => $record,
            'points' => round($data['points'], 2),
            'next' => $data['next']
        ];
        $rank++;
    }

    $content = new \stdClass();
    $content->data = $return;

    echo json_encode($content);
    die;
}

if (isset($_GET['dataType']) && $_GET['dataType'] == 'all-players') {
    
    $result = query("SELECT player, SUM(points) as points from rosters 
        WHERE player != '' AND player != '(Empty)'
        GROUP BY player");
    while ($row = fetch_array($result)) {

        $players[] = [
            'player' => $row['player'],
            'points' => number_format($row['points'], 2)
        ];
    }
    $content = new \stdClass();
    $content->data = $players;

    echo json_encode($content);
    die;
}

if (isset($_GET['dataType']) && $_GET['dataType'] == 'optimal-lineups') {
    
    $selectedSeason = $_GET['season'];
    $response = [];

    $result1 = query("SELECT distinct week FROM rosters WHERE YEAR = $selectedSeason");
    while ($week = fetch_array($result1)) {
        $week = $week['week'];

        $result2 = query("SELECT distinct manager FROM rosters
            WHERE YEAR = $selectedSeason AND week = $week");
        while ($manager = fetch_array($result2)) {
            $manager = $manager['manager'];

            $points = 0;
            $roster = [];

            $result3 = query("SELECT * FROM rosters
                WHERE YEAR = $selectedSeason AND week = $week and manager = '".$manager."'");
            while ($row = fetch_array($result3)) {

                $result4 = query("SELECT * FROM regular_season_matchups
                    join managers on regular_season_matchups.manager1_id = managers.id
                    WHERE YEAR = $selectedSeason AND week_number = $week and managers.name = '".$manager."'");
                while ($row2 = fetch_array($result4)) {

                    $winLoss = ($row2['manager1_score'] > $row2['manager2_score']) ? 'Win' : 'Loss';
                    $manager2 = $row2['manager2_id'];

                    $opponentPoints = 0;
                    $opponentRoster = [];

                    $result5 = query("SELECT * FROM managers
                        JOIN rosters on rosters.manager = managers.name
                        WHERE YEAR = $selectedSeason AND week = $week and managers.id = $manager2");
                    while ($team = fetch_array($result5)) {
                        $opponent = $team['name'];

                        $opponentRoster[] = [
                            'pos' => $team['position'],
                            'points' => (float)$team['points']
                        ];

                        if ($team['roster_spot'] != 'BN' && $team['roster_spot'] != 'IR') {
                            $opponentPoints += (float)$team['points'];
                        }
                    }
                }

                $roster[] = [
                    'pos' => $row['position'],
                    'points' => (float)$row['points']
                ];

                if ($row['roster_spot'] != 'BN' && $row['roster_spot'] != 'IR') {
                    $points += (float)$row['points'];
                }
            }

            $optimal = checkRosterForOptimal($roster, $selectedSeason);
            $opponentOptimal = checkRosterForOptimal($opponentRoster, $selectedSeason);

            $response[] = [
                'manager' => $manager,
                'week' => $week,
                'roster_link' => '<a href="/rosters.php?year='.$selectedSeason.'&week='.$week.'&manager='.$manager.'"><i class="icon-clipboard"></i></a>',
                'year' => $selectedSeason,
                'optimal' => round($optimal, 2),
                'points' => round($points, 2),
                'result' => $winLoss,
                'opponent' => $opponent,
                'oppPoints' => round($opponentPoints, 2),
                'oppOptimal' => round($opponentOptimal, 2),
                'margin' => abs(round($points - $opponentPoints, 2)),
                'optimalMargin' => abs(round($optimal - $opponentOptimal, 2)),
                'accuracy' => round($points * 100 / $optimal, 2).'%'
            ];
        }
    }

    $content = new \stdClass();
    $content->data = $response;

    echo json_encode($content);
    die;
}

if (isset($_GET['dataType']) && $_GET['dataType'] == 'lineup-accuracy') {
    $selectedSeason = $_GET['season'];
    $response = [];
    $data = [];
    
    // Get all roster data for the season in a single query instead of multiple queries per week/manager
    $query = "SELECT manager, week, roster_spot, position, points 
              FROM rosters 
              WHERE YEAR = $selectedSeason 
              ORDER BY manager, week";
    
    $result = query($query);
    
    // Organize data by manager and week
    $managerWeekData = [];
    while ($row = fetch_array($result)) {
        $manager = $row['manager'];
        $week = $row['week'];
        
        if (!isset($managerWeekData[$manager])) {
            $managerWeekData[$manager] = [];
        }
        
        if (!isset($managerWeekData[$manager][$week])) {
            $managerWeekData[$manager][$week] = [
                'actual' => 0,
                'roster' => []
            ];
        }
        
        // Add to roster array for optimal calculation
        $managerWeekData[$manager][$week]['roster'][] = [
            'pos' => $row['position'],
            'points' => (float)$row['points']
        ];
        
        // Calculate actual points (excluding bench and IR)
        if ($row['roster_spot'] != 'BN' && $row['roster_spot'] != 'IR') {
            $managerWeekData[$manager][$week]['actual'] += (float)$row['points'];
        }
    }

    // Calculate optimal and accuracy for each manager
    foreach ($managerWeekData as $manager => $weeks) {
        $totalActual = 0;
        $totalOptimal = 0;
        
        foreach ($weeks as $weekData) {
            $totalActual += $weekData['actual'];

            // Calculate optimal lineup once per week
            $optimal = checkRosterForOptimal($weekData['roster'], $selectedSeason);
            $totalOptimal += $optimal;
        }
        
        // Only add to response if we have valid data
        if ($totalOptimal > 0) {
            $response[] = [
                'manager' => $manager,
                'points' => round($totalActual, 2),
                'optimal' => round($totalOptimal, 2),
                'accuracy' => round($totalActual * 100 / $totalOptimal, 2).'%'
            ];
        }
    }

    $content = new \stdClass();
    $content->data = $response;

    echo json_encode($content);
    die;
}

if (isset($_GET['dataType']) && $_GET['dataType'] == 'player-info') {

    $return = '';
    // This fixes search for players like D'Andre
    $player = str_replace("'", "''", $_GET['player']);
    $details = [];

    $result = query("SELECT year, manager, player, team, COUNT(week) as weeks, SUM(points) as points, SUM(projected) as projected 
        FROM rosters 
        JOIN managers ON rosters.manager = managers.name
        WHERE player = '$player'
        GROUP BY year, manager, player, team
        ORDER BY year DESC");
    while ($row = fetch_array($result)) {
        $details[] = [
            'year' => $row['year'],
            'player' => $row['player'],
            'team' => $row['team'],
            // 'manager' => '<a href="/profile.php?id='.$row['manager'].'">'.$row['manager'].'</a>',
            'manager' => $row['manager'],
            'weeks' => $row['weeks'],
            'points' => round($row['points'], 2)
        ];
    }

    echo json_encode($details);
    die;
}

function clean($string) {
    $string = rtrim($string, ' ');
    $string = str_replace("'", '', $string);

    return preg_replace('/[^A-Za-z.\'0-9\-]/', ' ', $string); // Removes special chars.
}


if (isset($_GET['dataType']) && $_GET['dataType'] == 'weekly-ranks') {
    
    $allRanks = getAllRanks();

    $managers = [];
    foreach ($allRanks as $manager => $years) {
        foreach ($years as $year => $weeks) {
            // get average of all values within $weeks
            $total = count($weeks);
            $sum = 0;
            foreach ($weeks as $week => $rank) {
                $sum += $rank;
            }            
            
            $managers[] = [
                'manager' => $manager,
                'year' => $year,
                'avg_rank' => number_format($sum/$total, 2)
            ];
        }
    }
    $content = new \stdClass();
    $content->data = $managers;

    echo json_encode($content);
    die;
}

if (isset($_GET['dataType']) && $_GET['dataType'] == 'get-season-ranks') {
    $manager = $_GET['manager'];
    $year = $_GET['year'];

    $ranks = getAllRanks($manager, $year);

    $opponents = [];
    $result = query("SELECT *
        FROM regular_season_matchups rsm
        JOIN managers on managers.id = manager1_id
        WHERE year = $year AND name = '".$manager."'
        ORDER BY week_number asc");
    while ($row = fetch_array($result)) {
        $opponents[$row['week_number']] = getManagerName($row['manager2_id']);
    }
 
    $content = [];
    foreach ($ranks as $week => $rank) {
        $oppRanks = getAllRanks($opponents[$week], $year);

        $content[] = [
            'Week'          => $week,
            'Rank'          => $rank,
            'Opponent'      => $opponents[$week],
            'Opponent Rank' => $oppRanks[$week],
            'Result'        => $rank < $oppRanks[$week] ? 'Win' : 'Loss',
            // 'R'             => '<a href="/rosters.php?year='.$year.'&week='.$week.'&manager='.$manager.'"><i class="icon-clipboard"></i></a>'
        ];
    }

    echo json_encode($content);
    die;
}

function getAllRanks(string $manager = null, int $year = null)
{
    $ranks = [];
    $rank = 1;
    $lastWeek = null;

    $result = query("SELECT name, year, week_number, manager1_score
        FROM regular_season_matchups rsm
        JOIN managers on managers.id = manager1_id
        ORDER BY year, week_number, manager1_score desc");
    while ($row = fetch_array($result)) {

        if ($row['week_number'] != $lastWeek) {
            $rank = 1;
        }

        $ranks[$row['name']][$row['year']][$row['week_number']] = $rank;

        $lastWeek = $row['week_number'];
        $rank++;
    }

    if ($manager && $year) {
        return $ranks[$manager][$year];
    }

    return $ranks;
}

if (isset($_GET['dataType']) && $_GET['dataType'] == 'positions-drafted') {

    $manager = $_GET['manager'];
    $rounds = $qb = $rb = $wr = $te = $k = $def = $idp = [];

    // put numbers 1-25 in rounds array
    for ($x = 1; $x < 26; $x++) {
        $rounds[] = $x;
    }
    // for $qb, $rb, $wr, $te, $k, $def, initialize 25 zeros in each array
    for ($x = 1; $x < 26; $x++) {
        $qb[] = null;
        $rb[] = null;
        $wr[] = null;
        $te[] = null;
        $k[] = null;
        $def[] = null;
        $idp[] = 0;
    }

    $sql = "SELECT round, CASE WHEN position IN ('QB','RB','WR','TE','K','DEF') THEN position ELSE 'IDP' END as position, count(position) as num 
        FROM draft";
    if ($manager != 'all') {
        $sql .= " WHERE manager_id = '$manager'";
    }
    $sql .= " GROUP BY round, position
        ORDER BY round, position";
    $result = query($sql);
    while ($row = fetch_array($result)) {

        $pos = $row['position'];

        // update the proper array by the round number
        if ($pos == 'QB') {
            $qb[$row['round']-1] = $row['num'];
        } elseif ($pos == 'RB') {
            $rb[$row['round']-1] = $row['num'];
        } elseif ($pos == 'WR') {
            $wr[$row['round']-1] = $row['num'];
        } elseif ($pos == 'TE') {
            $te[$row['round']-1] = $row['num'];
        } elseif ($pos == 'K') {
            $k[$row['round']-1] = $row['num'];
        } elseif ($pos == 'DEF') {
            $def[$row['round']-1] = $row['num'];
        } else {
            $idp[$row['round']-1] += $row['num'];
        }
    }

    // loop through each array and if value is still 0, set it to null
    foreach ($idp as $key => $value) {
        if ($value == 0) {
            $idp[$key] = null;
        }
    }

    echo json_encode([
        'labels' => $rounds,
        'QB' => $qb,
        'RB' => $rb,
        'WR' => $wr,
        'TE' => $te,
        'K' => $k,
        'DEF' => $def,
        'IDP' => $idp
    ]);
    die;
}

if (isset($_GET['dataType']) && $_GET['dataType'] == 'points-by-week') {

    $manager = $_GET['manager'];
    $startWeek = explode('_', $_GET['startWeek'])[0];
    $startYear = explode('_', $_GET['startWeek'])[1];
    $endWeek = explode('_', $_GET['endWeek'])[0];
    $endYear = explode('_', $_GET['endWeek'])[1];
    $onlyWeek = $_GET['onlyWeek'];

    $points = [];
    $weeks = [];

    $sql = "SELECT week_number, year, manager1_score as points
        FROM regular_season_matchups
        WHERE manager1_id = '$manager' 
        AND ((year = $startYear AND week_number >= $startWeek) 
        OR (year > $startYear AND year < $endYear) 
        OR (year = $endYear AND week_number <= $endWeek))";

    if ($onlyWeek) {
        $sql .= " AND week_number = $onlyWeek";
    }

    $sql .= " ORDER BY year, week_number";

    $result = query($sql);
    while ($row = fetch_array($result)) {
        $points[] = $row['points'];
        $weeks[] = 'Wk. '.$row['week_number'].' '.$row['year'];
    }

    echo json_encode([
        'points' => $points,
        'weeks' => $weeks
    ]);
    die;
}

?>