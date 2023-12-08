<?php

include 'yahooSharedFunctions.php';

// 1. Get Request Token URL
$request_token_url = get_request_token_url($consumer_key);

if( ! $request_token_url ) {
    print "Could not retrieve request token data\n";
    exit;
}

// 2. Direct user to Yahoo! for authorization (retrieve verifier)
echo "Hey! Go to this URL and tell us the verifier you get at the end.<br /><br />";
echo '<a href="'.$request_token_url.'" target="_blank">Verify</a><br /><br />';

?>

<form action="yahooApiRequest.php" method="POST">
    <input type="text" name="code">

    <h3>League</h3>
    <input type="text" name="league_id" value="74490">

    <h3>Sections to Update</h3>
    <input type="checkbox" name="sections[]" value="yahoo_ids"> Manager Yahoo IDs<br>
    <input type="checkbox" name="sections[]" value="team_names" checked="checked"> Team Names<br>
    <input type="checkbox" name="sections[]" value="matchups" checked="checked"> Matchups<br>
    <input type="checkbox" name="sections[]" value="rosters" checked="checked"> Rosters<br>
    <input type="checkbox" name="sections[]" value="trades" checked="checked"> Trades<br>
    <input type="checkbox" name="sections[]" value="fun_facts" checked="checked"> Fun Facts<br>

    <h3>Weeks</h3>
    <input type="checkbox" name="weeks[]" value="1"> 1<br>
    <input type="checkbox" name="weeks[]" value="2"> 2<br>
    <input type="checkbox" name="weeks[]" value="3"> 3<br>
    <input type="checkbox" name="weeks[]" value="4"> 4<br>
    <input type="checkbox" name="weeks[]" value="5"> 5<br>
    <input type="checkbox" name="weeks[]" value="6"> 6<br>
    <input type="checkbox" name="weeks[]" value="7"> 7<br>
    <input type="checkbox" name="weeks[]" value="8"> 8<br>
    <input type="checkbox" name="weeks[]" value="9"> 9<br>
    <input type="checkbox" name="weeks[]" value="10"> 10<br>
    <input type="checkbox" name="weeks[]" value="11"> 11<br>
    <input type="checkbox" name="weeks[]" value="12"> 12<br>
    <input type="checkbox" name="weeks[]" value="13"> 13<br>
    <input type="checkbox" name="weeks[]" value="14"> 14<br>

    <button type="submit">Submit</button>
</form>

<?php
  
///////////////////////////////////////////////////////////////////////////////
//  FUNCTION get_request_token
/// @brief Get a request token for a given application.
///////////////////////////////////////////////////////////////////////////////
function get_request_token_url(string $consumer_key) 
{

    $url = 'https://api.login.yahoo.com/oauth2/request_auth';

    $params = [
        'client_id' => $consumer_key,
        'redirect_uri' => 'oob',  // Set OOB for ease of use -- could be a URL
        'response_type' => 'code',
        'language' => 'en-us'
    ];

    // Urlencode params and generate param string
    $param_list = [];
    foreach( $params as $key => $value ) {
      $param_list[] = urlencode( $key ) . '=' . urlencode( $value );
    }
    $param_string = join('&', $param_list);

    // Return url like this
    // https://api.login.yahoo.com/oauth2/request_auth?client_id=dj0yJmk9Mjc0ZUJKQmk3NHVaJmQ9WVdrOVlrSkVNRkJ6Y1ZvbWNHbzlNQT09JnM9Y29uc3VtZXJzZWNyZXQmc3Y9MCZ4PWRl&redirect_uri=oob&response_type=code&language=en-us
    
    return $url.'?'.$param_string;
}

