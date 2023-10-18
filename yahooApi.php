<?php

include 'yahooSharedFunctions.php';


// https://api.login.yahoo.com/oauth2/request_auth?client_id=dj0yJmk9Mjc0ZUJKQmk3NHVaJmQ9WVdrOVlrSkVNRkJ6Y1ZvbWNHbzlNQT09JnM9Y29uc3VtZXJzZWNyZXQmc3Y9MCZ4PWRl&redirect_uri=oob&response_type=code&language=en-us

// **** MAIN PROGRAM STARTS HERE ****

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

    <button type="submit">Submit</button>
</form>

<?php
// **** HELPER FUNCTIONS START HERE ****
  
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
    
    return $url.'?'.$param_string;

    // var_dump($url.'?'.$param_string);die;
    // $response_data = make_curl_request('GET', $url.'?'.$param_string, '', true);

    // // echo $response_data['contents'];die;

    // // var_dump($response_data);die;

    // if ($response_data && $response_data['return_code'] == 200) {

    //     $contents = $response_data['contents'];
    //     $data = oauth_response_to_array($contents);

    //     // print_r( $data );

    //     return $data;
    // }

    // return false;
}

