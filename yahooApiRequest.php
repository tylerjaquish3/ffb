<?php

include 'yahooSharedFunctions.php';

echo '<br /><br /><a href="/yahooApi.php">Start Over</a><br /><br />';

$access_token = '';
$access_token = null;

if (!$access_token) {

    $verifier = $_POST['code'];
    
    if (!$verifier) {
        echo 'Verifier code no good';
        exit;
    }
    
    // echo 'Verifier code: '.$verifier.PHP_EOL;
      
    // 3. Get Access Token
    $access_token_data = get_access_token($consumer_key, $consumer_secret, $verifier);
    $access_token = $access_token_data->access_token;

    echo $access_token;
}
  
echo '<hr />';

// Get team names, moves, trades
// Get regular season matchup scores for specific week 
// Get team roster and stats

$request_uri = '/teams';
$teams = get_data($request_uri, $access_token);
// var_dump($teams);
handle_teams($teams);

echo '<hr />';

// // Get standings
// $request_uri = '/standings';
// $standings = get_data($request_uri, $access_token);
// // var_dump($standings);
// handle_standings($standings);

// echo '<hr />';

// $request_uri = '/scoreboard';
// $scoreboard = get_data($request_uri, $access_token);
// // var_dump($scoreboard);
// handle_scoreboard($scoreboard);

// echo '<hr />';






echo "<br /><br />Successful";


///////////////////////////////////////////////////////////////////////////////
//  FUNCTION get_access_token
/// @brief Get an access token for a certain user and a certain application,
///        based on the request token and verifier
///////////////////////////////////////////////////////////////////////////////
function get_access_token($consumer_key, $consumer_secret, $verifier) {

    $url = 'https://api.login.yahoo.com/oauth2/get_token';

    // Add in the oauth verifier
    $params = [
        'client_id' => $consumer_key,
        'client_secret' => $consumer_secret,
        'redirect_uri' => 'oob',  // Set OOB for ease of use -- could be a URL
        'code' => $verifier,
        'grant_type' => 'authorization_code'
    ];

    // Urlencode params and generate param string
    $param_list = [];
    foreach ($params as $key => $value ) {
      $param_list[] = urlencode( $key ) . '=' . urlencode( $value );
    }
    $param_string = join( '&', $param_list );
    
    // var_dump($url.'?'.$param_string);die;
    $response_data = make_curl_request('POST', $url, $param_string);

    if ($response_data && $response_data['return_code'] == 200) {

        $contents = $response_data['contents'];
        $data = json_decode($contents);

        return $data;
    }

    return false;
}


function get_data(string $request_uri, string $token)
{
    $base_url = 'https://fantasysports.yahooapis.com/fantasy/v2/league/nfl.l.74490';
    $request_url = $base_url.$request_uri;
    $final_url = $request_url.'?format=json';

    $request_data = make_curl_request('GET', $final_url, '', $token);

    if(!$request_data) {
        echo "Request failed\n";
    }
    
    $return_code = $request_data['return_code'];
    if ($return_code != 200) {
        echo "Request failed with code ${return_code}\n";
        echo "Error: ${request_data['error_str']} (${request_data['errno']})\n";
    }

    $contents = json_decode($request_data['contents'])->fantasy_content->league[1];

    return $contents;
}


function handle_standings(object $data)
{
    $standings = $data->standings;
    foreach ($standings as $standing) {
        do_dump($standing);
    }
}

function handle_scoreboard(object $data)
{
    $scoreboards = $data->scoreboard;
    foreach ($scoreboards as $scoreboard) {
        do_dump($scoreboard);
    }
}

function handle_teams(object $data)
{
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
        'Andy'          => 'Andy'
    ];

    $teams = $data->teams;
    foreach ($teams as $team) {
        do_dump($team);
        // Find team name
        $teamName = $team->name;
        // Find nickname
        $teamNickname = $team->managers[0]->manager->nickname;
        // Match them up and save/update in DB
        $manager = $nicknames[$teamNickname];
        $managerId = lookupManager($manager);

        $query = "INSERT INTO team_names(id, manager, year, name, moves, trades) VALUES ($managerId, ) ON DUPLICATE KEY UPDATE c=VALUES(c);";
    }
}

function lookupManager(string $managerName)
{
    $result = query("SELECT * FROM managers where name = '".$managerName."'");
    while ($manager = fetch_array($result)) {
        return $manager['id'];
    }
}

?>