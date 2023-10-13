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

// Lookup schedule outcomes by manager
if (isset($_POST['dataType']) && $_POST['dataType'] == 'schedule') {

    $manager = $_POST['manager'];
    $currentYear = date('Y');

    $schedule = $allMatchups = [];
    $result = query("SELECT * FROM schedule where manager1_id = $manager OR manager2_id = $manager ORDER BY week ASC");
    while ($row = fetch_array($result)) {
        if ($row['manager1_id'] == $manager) {
            $schedule[$row['week']]['id'] = $row['manager2_id'];
            $schedule[$row['week']]['name'] = getManagerName($row['manager2_id']);
        } else {
            $schedule[$row['week']]['id'] = $row['manager1_id'];
            $schedule[$row['week']]['name'] = getManagerName($row['manager1_id']);
        }
    }
    
    $wins = $losses = 0;
    foreach ($schedule as $week => $opp) {
        $points['manName'] = getManagerName($manager);
        $points['oppName'] = $opp['name'];

        $points['mine'] = getPoints($manager, $week);
        $points['opp'] = getPoints((int)$opp['id'], $week);
        $points['week'] = $week;

        $allMatchups[] = $points;
        if ($points['mine'] > $points['opp']) {
            $wins++;
        } else {
            $losses++;
        }
    }
    $data = [
        'record' => $wins.' - '.$losses,
        'allMatchups' => $allMatchups
    ];

    echo json_encode($data);
    die;
}

/**
 * Undocumented function
 */
function getPoints($manager, $week)
{
    $allPositions = ['QB','RB','RB','WR','WR','WR','TE','W/R/T','Q/W/R/T','K','DEF','BN','BN','BN','BN','BN','BN'];

    $myRoster = [];
    $wrt = ['RB','WR','TE'];
    $qwrt = ['QB','RB','WR','TE'];
    foreach ($allPositions as $pos) {
        $myRoster[] = [$pos => null];
    }
    $result = query("SELECT * FROM preseason_rankings
        JOIN draft_selections ON preseason_rankings.id = draft_selections.ranking_id
        WHERE manager_id = $manager AND bye != $week ORDER BY pick_number ASC"
    );
    while ($row = fetch_array($result)) {
        foreach ($myRoster as $key => &$rosterPos) {
            foreach ($rosterPos as $k => &$pos) {
                $filled = false;
                if ($pos == null && $k == $row['position']) {
                    $myRoster[$key] = $row;
                    $filled = true;
                    break;
                } elseif ($pos == null && $k == 'W/R/T' && in_array($row['position'], $wrt)) {
                    $myRoster[$key] = $row;
                    $filled = true;
                    break;
                } elseif ($pos == null && $k == 'Q/W/R/T' && in_array($row['position'], $qwrt)) {
                    $myRoster[$key] = $row;
                    $filled = true;
                    break;
                } elseif ($pos == null && $k == 'BN') {
                    $myRoster[$key] = $row;
                    $filled = true;
                    break;
                }
            }
            if ($filled) {
                break;
            }
        }
    }

    $count = $myTotal = 0;
    foreach ($allPositions as $rosterSpot) {
        $row = $myRoster[$count];
        if ($row && isset($row['position'])) {
            $pts = round($row['proj_points'] / 17, 1);
            if ($rosterSpot != 'BN') {
                $myTotal += $pts;
            }
        }
        $count++;
    }

    return round($myTotal, 1);
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

            $projected = $points = 0;
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

                    $opponentProjected = $opponentPoints = 0;
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
                            $opponentProjected += $team['projected'];
                            $opponentPoints += $team['points'];
                        }
                    }
                }

                $roster[] = [
                    'pos' => $row['position'],
                    'points' => (float)$row['points']
                ];

                if ($row['roster_spot'] != 'BN' && $row['roster_spot'] != 'IR') {
                    $projected += $row['projected'];
                    $points += $row['points'];
                }
            }

            $optimal = checkRosterForOptimal($roster);
            $opponentOptimal = checkRosterForOptimal($opponentRoster);

            $response[] = [
                'manager' => $manager,
                'week' => $week,
                'roster_link' => '<a href="/rosters.php?year='.$selectedSeason.'&week='.$week.'&manager='.$manager.'"><i class="icon-clipboard"></i></a>',
                'year' => $selectedSeason,
                'optimal' => round($optimal, 2),
                'points' => round($points, 2),
                'projected' => round($projected, 2),
                'result' => $winLoss,
                'opponent' => $opponent,
                'oppPoints' => round($opponentPoints, 2),
                'oppProjected' => round($opponentProjected, 2),
                'oppOptimal' => round($opponentOptimal, 2),
                'margin' => abs(round($points - $opponentPoints, 2)),
                'optimalMargin' => abs(round($optimal - $opponentOptimal, 2))
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


?>