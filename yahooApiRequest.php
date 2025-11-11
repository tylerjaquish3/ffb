<?php
// Increase maximum execution time to 300 seconds (5 minutes)
set_time_limit(300);

include 'yahooSharedFunctions.php';

$year = (int)$_POST['year'];
$access_token = $_POST['token'];
$section = '';
$weeks = [];
$manager = isset($_POST['manager']) ? $_POST['manager'] : 0;

// To get new game codes, uncomment the following lines. Then go to the yahooApi.php page (Admin)
// Click Verify button, Yahoo will give you a code to copy. Close that window and paste it into the Admin page.
// Select any options and click Submit. All the game codes will be dumped on the screen. New one on bottom.
// $request_uri = '/games;game_codes=nfl';
// $teams = get_data($request_uri, $access_token);
// do_dump($teams);die;

$seasons = [
    2025 => ['league_id' => 23237, 'game_code' => 461],
    2024 => ['league_id' => 98957, 'game_code' => 449],
    2023 => ['league_id' => 74490, 'game_code' => 423],
    2022 => ['league_id' => 84027, 'game_code' => 414],
    2021 => ['league_id' => 16064, 'game_code' => 406],
    2020 => ['league_id' => 43673, 'game_code' => 399],
    2019 => ['league_id' => 201651, 'game_code' => 390],
    2018 => ['league_id' => 224863, 'game_code' => 380],
    2017 => ['league_id' => 262191, 'game_code' => 371],
    2016 => ['league_id' => 477642, 'game_code' => 359],
    2015 => ['league_id' => 217861, 'game_code' => 348],
    2014 => ['league_id' => 53077, 'game_code' => 331],
    2013 => ['league_id' => 27577, 'game_code' => 314],
    2012 => ['league_id' => 26725, 'game_code' => 273],
    2011 => ['league_id' => 163601, 'game_code' => 257],
    2010 => ['league_id' => 35443, 'game_code' => 242],
    2009 => ['league_id' => 42150, 'game_code' => 222],
    2008 => ['league_id' => 8224, 'game_code' => 199],
    2007 => ['league_id' => 73988, 'game_code' => 175],
    2006 => ['league_id' => 48909, 'game_code' => 153],
];
// get league id based on year
$leagueId = $seasons[$year]['league_id'];
$gameCode = $seasons[$year]['game_code'];

if (isset($_POST['section'])) {
    $section = $_POST['section'];
} 
if (isset($_POST['weeks'])) {
    $weeks = $_POST['weeks'];
} 

// $data = [];
// $result = query("SELECT * FROM rosters ORDER by id desc limit 20");
// while ($row = fetch_array($result)) {
//     $data[] = $row;
// }
// do_dump($data);die;

// Check league settings
// $request_uri = '/league/nfl.l.'.$leagueId.'/settings';
// $teams = get_data($request_uri, $access_token);
// do_dump($teams);die;

// Check user leagues
// $request_uri = '/users;use_login=1/games;game_keys=nfl/leagues';
// $teams = get_data($request_uri, $access_token);
// do_dump($teams);die;

if ($section == 'yahoo_ids') {
    // first need to update yahoo id (if necessary, changes each year)
    // yahoo id is used going forward with all these other functions
    echo 'Getting manager Yahoo IDs...<br />';
    // Don't need to get older ones anymore (they don't change)
    // foreach ($seasons as $year => $season) {

        // Just update the year entered
        $season = $seasons[$year];
        $leagueId = $season['league_id'];
        $gameCode = $season['game_code'];

        $request_uri = '/league/'.$gameCode.'.l.'.$leagueId.'/teams';
        $teams = get_data($request_uri, $access_token);
        if ($teams) {
            handle_managers($teams, $year);
        }
    // }
    echo '<hr />';die;
}

if ($section == 'team_names') {
    // Get team names, moves, trades
    echo 'Getting team names, moves, trades...<br />';
    $request_uri = '/league/'.$gameCode.'.l.'.$leagueId.'/teams';
    $teams = get_data($request_uri, $access_token);
    // do_dump($teams);die;
    if ($teams) {
        handle_teams($teams);
    }
    echo '<hr />';die;
}

if ($section == 'matchups') {
    // Get regular season matchup scores for specific week 
    echo 'Getting scoreboard...<br />';
    for ($managerId = 1; $managerId < 11; $managerId++) {
        $request_uri = '/team/'.$gameCode.'.l.'.$leagueId.'.t.'.$managerId.'/matchups';
        $matchups = get_data($request_uri, $access_token);
        if ($matchups) {
            handle_team_matchups($managerId, $matchups);
        }
    }
    echo '<hr />';die;
}

if ($section == 'rosters') {
    // Get team roster and stats
    echo 'Getting rosters...<br />';
    if (count($weeks) == 0) {
        echo 'No weeks selected';
    } else {
        foreach ($weeks as $week) {
            try {
                echo 'Yahoo ID: '.$manager.' | Week '.$week.'<br />';
                $request_uri = '/team/'.$gameCode.'.l.'.$leagueId.'.t.'.$manager.'/roster;week='.$week;
                $rosters = get_data($request_uri, $access_token);
                if ($rosters) {
                    handle_team_rosters($manager, $week, $rosters);
                }
            } catch (Exception $e) {
                echo 'Caught exception: ',  $e->getMessage(), "\n";
                // try again
                $rosters = get_data($request_uri, $access_token);
                if ($rosters) {
                    handle_team_rosters($manager, $week, $rosters);
                }
            }
        }
    }
    echo '<hr />';die;
}

if ($section == 'trades') {
    echo 'Getting trades...<br />';
    $request_uri = '/league/'.$gameCode.'.l.'.$leagueId.'/transactions;types=trade';
    $transactions = get_data($request_uri, $access_token);
    if ($transactions) {
        handle_trades($transactions);
    }

    echo '<hr />';die;
}

if ($section == 'fun_facts') {
    echo 'Getting fun facts...<br />';
    echo 'Debug: Starting fun_facts processing at ' . date('Y-m-d H:i:s') . '<br />';
    try {
        handle_fun_facts();
        echo 'Debug: Fun facts command completed successfully.<br />';
    } catch (Exception $e) {
        echo 'Debug: Error running fun facts command: ' . $e->getMessage() . '<br />';
    }
    echo '<hr />';die;
}


function get_data(string $request_uri, string $token)
{
    $base_url = 'https://fantasysports.yahooapis.com/fantasy/v2';
    $final_url = $base_url.$request_uri.'?format=json';

    $request_data = make_curl_request('GET', $final_url, '', $token);

    if(!$request_data) {
        echo "Request failed\n";
    }
    
    $return_code = $request_data['return_code'];
    if ($return_code != 200) {
        echo "Request failed with code {$return_code}\n";
        echo "Error: {$request_data['error_str']} ({$request_data['errno']})\n";
    }

    if ($return_code == 999) {
        sleep(5);
        get_data($request_uri, $token);
    }

    if ($request_data['contents'] == '') {
        sleep(5);
        get_data($request_uri, $token);
    } else {
        $contents = json_decode($request_data['contents']);
        if (property_exists($contents, 'fantasy_content')) {
            return $contents->fantasy_content;
        } else {
            dd($request_data['contents']);
        }
    
        return $contents;
    }
}

function handle_managers(object $data, int $year)
{
    $teams = $data->league[1]->teams;
    foreach ($teams as $team) {
        if (gettype($team) == 'object') {
            // do_dump($team);die;
            // Search $team->team[0] for managers key
            foreach ($team->team[0] as $key => $value) {
                if (gettype($value) == 'object') {
                    if (property_exists($value, 'managers')) {
                        $managers = $value->managers;
                    }
                }
            }
            $nickname = $managers[0]->manager->nickname;
            $yahooTeamId = (int)$team->team[0][1]->team_id;
    
            $nicknames = [
                'Coley Bear'    => 'Cole',
                'James'         => 'Matt',
                'Tweak'         => 'Justin',
                'Tyler'         => 'Tyler',
                'Ben'           => 'Ben',
                'cameron'       => 'Cameron',
                'A.J.'          => 'AJ',
                'Everett'       => 'Everett',
                'Gavin'         => 'Gavin',
                'Andy'          => 'Andy',
                'Andy Stamschror' => 'Andy',
                '-- hidden --'    => 'Tyler',
                '--hidden--'    => 'Andy',
            ];

            $manager = $nicknames[$nickname];
            $result = query("SELECT * FROM managers where name = '".$manager."'");
            while ($manager = fetch_array($result)) {
                $managerId = $manager['id'];
            }
            
            echo 'Manager ID: '.$managerId.' = Yahoo ID: '.$yahooTeamId.'= '.$nickname.'<br>';
            // update manager's yahoo_id
            updateOrCreate('season_managers', [
                'year' => $year,
                'manager_id' => $managerId
            ], [
                'yahoo_id' => $yahooTeamId,
            ]);
        }
    }
}

function handle_teams(object $data)
{
    global $year;
    
    $teams = $data->league[1]->teams;
    foreach ($teams as $team) {
        if (gettype($team) == 'object') {
            // do_dump($team);die;
            // Find team name (encode it to handle apostrophe)
            $teamName = str_replace("'", "''", $team->team[0][2]->name);
            // Find yahoo team id
            $yahooTeamId = (int)$team->team[0][1]->team_id;
            $moves = (int)$team->team[0][10]->number_of_moves;
            $trades = (int)$team->team[0][11]->number_of_trades;
    
            echo $teamName.' = '.$yahooTeamId.'<br>';
            // Match up nickname to manager_id
            $managerId = lookupManager($yahooTeamId, $year);
    
            // update team names, moves, trades
            updateOrCreate('team_names', [
                'manager_id' => $managerId,
                'year' => $year
            ], [
                'name' => $teamName,
                'moves' => $moves,
                'trades' => $trades
            ]);
        }
    }
}

function lookupManager(int $yahooTeamId, int $year)
{
    $result = query("SELECT managers.id FROM season_managers 
        JOIN managers on managers.id = season_managers.manager_id
        WHERE yahoo_id = $yahooTeamId and year = $year");
    while ($manager = fetch_array($result)) {
        return $manager['id'];
    }
}

function handle_team_matchups(int $yahooTeamId, object $data)
{
    global $year;
    // do_dump($data);die;

    $managerId = lookupManager($yahooTeamId, $year);
    echo 'Manager ID: '.$managerId.' Weeks: ';
 
    // Loop through each week for this manager
    foreach ($data->team[1]->matchups as $index => $m) {

        if (gettype($m) != 'object') {
            continue;
        }
        $matchup = $m->matchup;

        // Only insert if the matchup is over
        if ($matchup->status == 'postevent') {
            $week = $matchup->week;
            echo '| '.$week.' ';
            $managerScore = (float)$matchup->{0}->teams->{0}->team[1]->team_points->total;
            $managerProjected = (float)$matchup->{0}->teams->{0}->team[1]->team_projected_points->total;
    
            $oppYahooId = (int)$matchup->{0}->teams->{1}->team[0][1]->team_id;
            $oppId = lookupManager($oppYahooId, $year);
            $oppScore = (float)$matchup->{0}->teams->{1}->team[1]->team_points->total;
            $oppProjected = (float)$matchup->{0}->teams->{1}->team[1]->team_projected_points->total;
    
            // echo $managerId.' vs '.$oppId.' in week '.$week.' ('.$managerScore.' - '.$oppScore.')<br>';
            updateOrCreate('regular_season_matchups', [
                'manager1_id' => $managerId,
                'year' => $year,
                'week_number' => $week
            ], [
                'manager2_id' => $oppId,
                'manager1_score' => $managerScore,
                'manager2_score' => $oppScore,
                'winning_manager_id' => $managerScore > $oppScore ? $managerId : $oppId,
                'losing_manager_id' => $managerScore > $oppScore ? $oppId : $managerId,
                'manager1_projected' => $managerProjected,
                'manager2_projected' => $oppProjected
            ]);
        }
    }

    echo '...done<br>';
}

function handle_team_rosters(int $yahooId, int $week, object $data)
{
    global $year;
    // do_dump($data);die;

    $managerId = lookupManager($yahooId, $year);
    $result = query("SELECT * FROM managers where id = $managerId");
    while ($row = fetch_array($result)) {
        $manager = $row['name'];
    }

    $roster = $data->team[1]->roster->{0}->players;
    // Loop through the roster
    foreach ($roster as $index => $p) {

        if (gettype($p) != 'object') {
            continue;
        }
        $player = $p->player;
        // do_dump($player);
        $playerName = $player[0][2]->name->full;

        // Loop through the player properties to find team
        foreach ($player[0] as $key => $value) {
            if (gettype($value) == 'object') {
                if (property_exists($value, 'editorial_team_abbr')) {
                    $teamKey = $value->editorial_team_abbr;
                }
                if (property_exists($value, 'primary_position')) {
                    $pos = $value->primary_position;
                }
            }
        }

        // Use player key to lookup stats
        $playerKey = $player[0][0]->player_key;
        $stats = get_player_stats($playerKey, $week);

        if (!$stats) {
            continue;
        }
        
        $team = strtoupper($teamKey);
        $spot = $player[1]->selected_position[1]->position;
        // Projected is not available from API
        $projected = 0;
        $points = $stats['points'];

        echo $manager.' - '.$playerName.' ('.$team.' - '.$pos.' - '.$spot.')<br>';
        echo 'Points: '.$points.'<br>';
        // Insert player into rosters
        $rosterId = updateOrCreate('rosters', [
            'manager' => $manager,
            'year' => $year,
            'week' => $week,
            'player' => $playerName,
            'position' => $pos
        ], [
            'team' => $team,
            'roster_spot' => $spot,
            'projected' => $projected,
            'points' => $points
        ]);

        if ($spot != 'IR' && isset($stats['stats']) && is_array($stats['stats'])) {
            
            // Insert stats - make sure we have valid stats array
            // Convert any nulls to 0 to prevent SQL issues
            $cleanStats = array_map(function($value) {
                return $value === null ? 0 : $value;
            }, $stats['stats']);

            updateOrCreate('stats', [
                'roster_id' => $rosterId
            ], $cleanStats);
            
        }
    }
}

function get_player_stats(string $playerKey, int $week)
{
    global $access_token, $leagueId, $gameCode;
    $request_uri = '/league/'.$gameCode.'.l.'.$leagueId.'/players;player_keys='.$playerKey.'/stats;type=week;week='.$week;
    $data = get_data($request_uri, $access_token);

    if (!$data) {
        return;
    }
    // do_dump($data); die;

    $statIds = [
        4 => 'pass_yds',
        5 => 'pass_tds',
        6 => 'ints',
        9 => 'rush_yds',
        10 => 'rush_tds',
        11 => 'receptions',
        12 => 'rec_yds',
        13 => 'rec_tds',
        18 => 'fumbles',
        29 => 'pat_made',
        84 => 'fg_yards',
        85 => 'fg_made',
        33 => 'def_int',
        34 => 'def_fum',
        32 => 'def_sacks'
    ];

    $player = $data->league[1]->players->{0}->player;

    $values = [
        'projected' => null, // NA in API
        'points' => (float)$player[1]->player_points->total,
        'stats' => []
    ];
    
    // Loop through player's stats
    $stats = $player[1]->player_stats->stats;
    foreach ($stats as $stat) {
        if (gettype($stat) == 'object') {
            $statId = $stat->stat->stat_id;
            $statValue = $stat->stat->value;
            // use $statIds to put values in $values['stats']
            if (array_key_exists($statId, $statIds)) {
                $values['stats'][$statIds[$statId]] = (int)$statValue;
            }
        }
    }
    // do_dump($values);die;

    return $values;
}

function handle_trades(object $data)
{
    global $year, $leagueId, $conn, $DB_TYPE;
    
    $transactions = $data->league[1]->transactions;
    foreach ($transactions as $trans) {
        if (gettype($trans) == 'object') {
            // do_dump($trans);die;
            
            $tradeId = $leagueId.$trans->transaction[0]->transaction_id;
            $timestamp = $trans->transaction[0]->timestamp;
            // make date from timestamp
            $date = date('Y-m-d', $timestamp);
            $currentWeek = lookup_week($date);
            
            foreach ($trans->transaction[1]->players as $player) {
                // do_dump($player);die;
                if (gettype($player) != 'object') {
                    continue;
                }

                $manager1 = find_manager_id($player->player[1]->transaction_data[0]->source_team_key);
                $manager2 = find_manager_id($player->player[1]->transaction_data[0]->destination_team_key);

                $player = str_replace("'", "''", $player->player[0][2]->name->full);
                
                echo 'Current Week: '.$currentWeek.' | Trade ID: '.$tradeId.'<br>';
                echo $manager1.' traded '.$player.' to '.$manager2.'<br>';
                // update trades table
                firstOrCreate('trades', [
                    'player' => $player,
                    'year' => $year,
                    'manager_from_id' => $manager1,
                ], [
                    'week' => $currentWeek,
                    'manager_to_id' => $manager2,
                    'trade_identifier' => $tradeId
                ]);
            }
        }
    }
}

function find_manager_id(string $teamKey)
{
    global $year;
    // get everything after the last period in the $teamKey
    $yahooId = (int)substr($teamKey, strrpos($teamKey, '.') + 1);
    $id = lookupManager($yahooId, $year);

    return $id;
}

function lookup_week(string $date)
{
    global $year;

    // week 1 ends the first monday after labor day
    $week1 = date('Y-m-d', strtotime('second monday of september '.$year));
    // add one week until getting to $date
    $week = 1;
    while ($week1 < $date) {
        $week1 = date('Y-m-d', strtotime('+1 week '.$week1));
        $week++;
    }

    return $week;
}

function handle_fun_facts()
{
    // Change directory to fun-facts and run artisan command
    $fun_facts_dir = __DIR__ . '/fun-facts';
    
    echo "Debug: Running artisan command from {$fun_facts_dir}<br>";
    
    // Couldn't get this to work...yet
}

function dd($text)
{
    // Move down so its below the header
    echo "<br /><br /><br />";
    echo '<pre style="direction: ltr; float: left;">';
    print_r($text);
    echo '</pre>';
    die;
}

?>