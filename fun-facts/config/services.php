<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'year' => 2024,
    'yahoo_league_id' => 98957,
    'weeks' => [5],

    // For reference
    // 'seasons' => [
    //     2024 => ['league_id' => 98957, 'game_code' => 449],
    //     2023 => ['league_id' => 74490, 'game_code' => 423],
    //     2022 => ['league_id' => 84027, 'game_code' => 414],
    //     2021 => ['league_id' => 16064, 'game_code' => 406],
    //     2020 => ['league_id' => 43673, 'game_code' => 399],
    //     2019 => ['league_id' => 201651, 'game_code' => 390],
    //     2018 => ['league_id' => 224863, 'game_code' => 380],
    //     2017 => ['league_id' => 262191, 'game_code' => 371],
    //     2016 => ['league_id' => 477642, 'game_code' => 359],
    //     2015 => ['league_id' => 217861, 'game_code' => 348],
    //     2014 => ['league_id' => 53077, 'game_code' => 331],
    //     2013 => ['league_id' => 27577, 'game_code' => 314],
    //     2012 => ['league_id' => 26725, 'game_code' => 273],
    //     2011 => ['league_id' => 163601, 'game_code' => 257],
    //     2010 => ['league_id' => 35443, 'game_code' => 242],
    //     2009 => ['league_id' => 42150, 'game_code' => 222],
    //     2008 => ['league_id' => 8224, 'game_code' => 199],
    //     2007 => ['league_id' => 73988, 'game_code' => 175],
    //     2006 => ['league_id' => 48909, 'game_code' => 153],
    // ]
];
