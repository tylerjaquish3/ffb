<?php

$consumer_data = [
    'key'    => 'dj0yJmk9Mjc0ZUJKQmk3NHVaJmQ9WVdrOVlrSkVNRkJ6Y1ZvbWNHbzlNQT09JnM9Y29uc3VtZXJzZWNyZXQmc3Y9MCZ4PWRl',
    'secret' => '45e85dc510dc193bfd6a0d77c0282f7d98e8055d'
];

$consumer_key = $consumer_data['key'];
$consumer_secret = $consumer_data['secret'];


// Make curl call
function make_curl_request(string $method, string $final_url, string $params = '', $bearer = '')
{
    $ch = curl_init();

    if ($method == 'POST') {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    }
    
    if ($bearer != '') {
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer '.$bearer]);
    }
    curl_setopt($ch, CURLOPT_URL, $final_url);
    curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

    $timeout = 2; // seconds
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);

    $contents = curl_exec($ch);
    $ret_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $errno = curl_errno($ch);
    $error_str = curl_error($ch);

    if( $errno || $error_str ) {
    //print "Error: ${error_str} (${errno})\n";
    }

    curl_close($ch);

    $data = [
        'return_code' => $ret_code,
        'contents'    => $contents,
        'error_str'   => $error_str,
        'errno'       => $errno
    ];

    return $data;
}

///////////////////////////////////////////////////////////////////////////////
//  FUNCTION oauth_response_to_array
/// @brief Break up the oauth response data into an associate array
///////////////////////////////////////////////////////////////////////////////
function oauth_response_to_array(string $response) {
    $data = [];
    foreach (explode('&', $response) as $param) {
        $parts = explode( '=', $param );
        if (count( $parts ) == 2) {
            $data[urldecode($parts[0])] = urldecode($parts[1]);
        }
    }

    return $data;
}


function dd($text)
{
    echo '<pre style="direction: ltr; float: left;">';
    var_dump($text);
    echo '</pre>';
    die;
}

?>