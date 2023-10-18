<?php

include 'yahooSharedFunctions.php';

echo '<br /><br /><a href="/yahooApi.php">Start Over</a><br /><br />';

$verifier = $_POST['code'];

if (!$verifier) {
    echo 'Verifier code no good';
    exit;
}

echo 'Verifier code: '.$verifier.PHP_EOL;
  
// 3. Get Access Token
$access_token_data = get_access_token($consumer_key, $consumer_secret, $verifier);
$access_token = $access_token_data->access_token;

if (!$access_token) {
    echo "Could not get access token";
    exit;
}
  
// 4. Make request using Access Token
$base_url = 'https://fantasysports.yahooapis.com/fantasy/v2';
$request_uri = '/league/nfl.l.74490';
$request_url = $base_url . $request_uri;

echo "Making request for ${request_url}\n";

$request_data = make_signed_request($access_token, $request_url, []);

if( ! $request_data ) {
    echo "Request failed\n";
}

$return_code = $request_data['return_code'];
$contents = $request_data['contents'];

echo "Return code: ".$return_code."<br /><br />";
echo "Contents: <br />:".$contents;

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


///////////////////////////////////////////////////////////////////////////////
//  FUNCTION _make_signed_request
/// @brief Helper function to make a signed OAuth request. Only allows GET
///        requests at the moment. Will add on standard OAuth params, but
///        you may need to fill in non-generic ones ahead of time.
///
/// @param[in]  $token             Token (request or access token)
/// @param[in]  $url               URL to make request to
/// @param[in]  $params            Array of key=>val for params. Don't
///                                urlencode ahead of time, we'll do that here.
///////////////////////////////////////////////////////////////////////////////
function make_signed_request($token, $url, $params = []) 
{
    $params['format'] = 'json';
  
    // Urlencode params and generate param string
    $param_list = [];
    foreach ($params as $key => $value ) {
      $param_list[] = rawurlencode($key).'='.rawurlencode($value);
    }
    $param_string = join('&', $param_list);
  
    $final_url = $url . '?' . $param_string;

    $data = make_curl_request('GET', $final_url, '', $token);

    return $data;
}

function getTeam(string $input)
{
    $teams = ['Ari', 'Atl', 'Jax', 'Mia'];

    // Search the last 4 characters of $input for a team
    $substring = substr($input, -4);

    foreach ($teams as $team) {
        // if substring contains a team, return that team
        if (strpos($substring, $team) !== false) {

            // return uppercase
            return 
        }
    }

}

?>