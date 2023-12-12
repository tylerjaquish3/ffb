<?php

include 'yahooSharedFunctions.php';

$verifier = $_POST['code'];
    
if (!$verifier) {
    echo 'Verifier code missing';
    exit;
}

// echo 'Verifier code: '.$verifier.PHP_EOL;
    
// 3. Get Access Token
$access_token_data = get_access_token($consumer_key, $consumer_secret, $verifier);


///////////////////////////////////////////////////////////////////////////////
//  FUNCTION get_access_token
/// @brief Get an access token for a certain user and a certain application,
///        based on the request token and verifier
///////////////////////////////////////////////////////////////////////////////
function get_access_token($consumer_key, $consumer_secret, $verifier) 
{
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

        $data = json_decode($response_data['contents']);

        echo $data->access_token;
        die;
    }

    echo false;
    die;
}