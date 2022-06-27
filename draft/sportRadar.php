<?php

    include '../functions.php';

    // Only need to do this once to save the nfl teams
    // $teams = file_get_contents('files/leagueHierarchy.json');
    // saveTeams($teams);

    // This is if you need to lookup and save a single player using the sandbox
    // saveOnePlayer();
    // die;

?>

<!DOCTYPE html>
<html lang="en" data-textdirection="rtl" class="loading">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">

    <title>Suntown FFB</title>

    <link rel="icon" type="image/png" href="/images/favicon.jpg">

    <meta property="og:title" content="Suntown Fantasy Football League" />
    <meta property="og:description" content="The best league in all the land" />
    <meta property="og:url" content="http://suntownffb.us" />
    <meta property="og:image" content="http://suntownffb.us/images/favicon.jpg" />

    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-touch-fullscreen" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <!-- BEGIN VENDOR CSS-->
    <link rel="stylesheet" type="text/css" href="../assets/bootstrap.min.css">
    <!-- font icons-->
    <link rel="stylesheet" type="text/css" href="../assets/icomoon.css">
    <link rel="stylesheet" type="text/css" href="../assets/flag-icon.min.css">
    <link rel="stylesheet" type="text/css" href="../assets/pace.css">
    <!-- END VENDOR CSS-->
    <!-- BEGIN ROBUST CSS-->
    <link rel="stylesheet" type="text/css" href="../assets/bootstrap-extended.min.css">
    <link rel="stylesheet" type="text/css" href="../assets/app.min.css">
    <link rel="stylesheet" type="text/css" href="../assets/colors.min.css">
    <!-- END ROBUST CSS-->
    <link rel="stylesheet" type="text/css" href="../assets/suntown.css">
    <link rel="stylesheet" type="text/css" href="../assets/responsive.css">

</head>

<body data-open="click" data-menu="vertical-menu" data-col="2-columns" class="vertical-layout vertical-menu 2-columns  fixed-navbar">

    <!-- navbar-fixed-top-->
    <nav class="header-navbar navbar navbar-with-menu navbar-fixed-top navbar-semi-dark navbar-shadow">
        <div class="navbar-wrapper">
            <div class="navbar-header">
                <ul class="nav navbar-nav">
                    <li class="nav-item mobile-menu hidden-md-up float-xs-left"><a class="nav-link nav-menu-main menu-toggle hidden-xs"><i class="icon-menu5 font-large-1"></i></a></li>
                    <li class="nav-item tab-size">
                        <a href="/"><h2>Suntown FFB</h2></a>
                    </li>
                    <li class="nav-item tab-size-alt">
                        <a href="/"><h2>FFB</h2></a>
                    </li>
                    <li class="nav-item hidden-md-up float-xs-right"><a data-toggle="collapse" data-target="#navbar-mobile" class="nav-link open-navbar-container"><i class="icon-ellipsis pe-2x icon-icon-rotate-right-right"></i></a></li>
                </ul>
            </div>
            <div class="navbar-container content container-fluid">
                <div id="navbar-mobile">
                    <h2>Preseason Tools</h2>
                </div>
            </div>
        </div>
    </nav>


    <div class="app-content content container-fluid">
        <div class="content-wrapper">
            <div class="content-header row"></div>

            <div class="content-body">
                <div class="row">
                    <div class="col-xl-3 col-lg-6 col-xs-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="media">
                                    <div class="p-2 text-xs-center bg-green-ffb media-left media-middle">
                                        <i class="icon-star-full font-large-2 white"></i>
                                    </div>
                                    <div class="p-2 bg-green-ffb media-body">
                                        <h4>Choose team(s) for player data</h4>
                                        <form action="sportRadar.php" method="POST">
                                            <select name="team" class="form-control">
                                                <option value="all">All</option>
                                                <option value="afc">AFC</option>
                                                <option value="nfc">NFC</option>
                                                <option value="afcw">AFC West</option>
                                                <option value="afce">AFC East</option>
                                                <option value="afcs">AFC South</option>
                                                <option value="afcn">AFC North</option>
                                                <option value="nfcs">NFC South</option>
                                                <option value="nfcw">NFC West</option>
                                                <option value="nfce">NFC East</option>
                                                <option value="nfcn">NFC North</option>
                                                <?php
                                                $result = mysqli_query($conn, "SELECT * FROM nfl_teams");
                                                while ($row = mysqli_fetch_array($result)) {
                                                    $id = $row['id'];
                                                    echo '<option value="'.$id.'">'.$row['name'].'</option>';
                                                } ?>
                                            </select>
                                            <button type="submit" class="btn btn-secondary">Start</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row" style="background: #fff">
                    <div class="col-xs-12">
                    <?php
                    if (isset($_POST) && isset($_POST['team'])) {

                        $allTeams = [];
                        for ($x = 1; $x < 33; $x++) {
                            $allTeams[] = $x;
                        }
                        switch ($_POST['team']) {
                            case 'all':
                                $nflTeams = $allTeams;
                                break;
                            case 'afc':
                                $nflTeams = array_slice($allTeams, 0, 16);
                                break;
                            case 'nfc':
                                $nflTeams = array_slice($allTeams, 16, 16);
                                break;
                            case 'afcw':
                                $nflTeams = array_slice($allTeams, 0, 4);
                                break;
                            case 'afce':
                                $nflTeams = array_slice($allTeams, 4, 4);
                                break;
                            case 'afcs':
                                $nflTeams = array_slice($allTeams, 8, 4);
                                break;
                            case 'afcn':
                                $nflTeams = array_slice($allTeams, 12, 4);
                                break;
                            case 'nfcs':
                                $nflTeams = array_slice($allTeams, 16, 4);
                                break;
                            case 'nfcw':
                                $nflTeams = array_slice($allTeams, 20, 4);
                                break;
                            case 'nfce':
                                $nflTeams = array_slice($allTeams, 24, 4);
                                break;
                            case 'nfcn':
                                $nflTeams = array_slice($allTeams, 28, 4);
                                break;
                            default:
                                $nflTeams = [$_POST['team']];
                        }

                        $currentYear = date('Y');
                        $lastYear = $currentYear - 1;

                        $rankedNames = $rankedIds = [];
                        $result = mysqli_query($conn, "SELECT * FROM preseason_rankings");
                        while ($row = mysqli_fetch_array($result)) {
                            $rankedNames[] = [
                                'id' => (int)$row['id'],
                                'name' => $row['player'],
                                'alias' => $row['alias'],
                                'pos' => $row['position']
                            ];
                        }

                        // Foreach team, get roster
                        $nflTeams = join("','", $nflTeams);
                        $result = mysqli_query($conn, "SELECT * FROM nfl_teams WHERE id IN ('$nflTeams')");
                        while ($row = mysqli_fetch_array($result)) {
                            $teamId = $row['sportradar_id'];
                            echo '<hr><h1>'.$row['name'].'</h1>';

                            // Get roster for this team
                            $roster = makeRequest('teams/'.$teamId.'/full_roster.json');
                            // $roster = file_get_contents('draft/files/exRoster.json');
                            // $roster = json_decode($roster);
                            foreach ($roster->players as $player) {
                                echo $player->name.' looking...<br>';
                                $key = array_search($player->name, array_column($rankedNames, 'name'));

                                // If the name isn't there, search by alias
                                if (!$key) {
                                    $key = array_search($player->name, array_column($rankedNames, 'alias'));
                                }

                                // If found, check position
                                if ($key) {
                                    if ($rankedNames[$key]['pos'] != $player->position) {
                                        continue;
                                    }
                                } else {
                                    continue;
                                }

                                $rankingId = $rankedNames[$key]['id'];

                                $foundLastYear = false;
                                // Check if player already has data from last year
                                $result2 = mysqli_query($conn, "SELECT * FROM player_data WHERE preseason_ranking_id = $rankingId AND year = $lastYear");
                                while ($row2 = mysqli_fetch_array($result2)) {
                                    $foundLastYear = true;
                                }

                                if ($foundLastYear) {
                                    continue;
                                }
                                
                                echo $player->name.' found<br>';

                                // This player is in my rankings so look him up
                                $playerId = $player->id;
                                // Player profile
                                $url = 'players/'.$playerId.'/profile.json';
                                $playerData = makeRequest($url);

                                // $playerData = file_get_contents('draft/files/exPlayer.json');
                                // $playerData = json_decode($playerData);
                                if ($playerData && property_exists($playerData, 'seasons')) {
                                    foreach ($playerData->seasons as $season) {
                                        // dd($player);
                                        // Only interested in last year
                                        if ($season->year == $lastYear && $rankingId) {

                                            foreach ($season->teams as $team) {
                                                // dd($team->statistics);
                                                $stat = $team->statistics;
                                                $gp = $passAtt = $comp = $passYds = $passTds = $int = $rushAtt = $rushYds = $rushTds = $tar = $rec = $recYds = $recTds = $fum = 0;

                                                $gp = $stat->games_played;
                                                if (property_exists($stat, 'passing')) {
                                                    $pass = $stat->passing;
                                                    $passAtt = $pass->attempts;
                                                    $comp = $pass->completions;
                                                    $passYds = $pass->yards;
                                                    $passTds = $pass->touchdowns;
                                                    $int = $pass->interceptions;
                                                }
                                                if (property_exists($stat, 'rushing')) {
                                                    $rush = $stat->rushing;
                                                    $rushAtt = $rush->attempts;
                                                    $rushYds = $rush->yards;
                                                    $rushTds = $rush->touchdowns;
                                                }
                                                if (property_exists($stat, 'receiving')) {
                                                    $receiving = $stat->receiving;
                                                    $tar = $receiving->targets;
                                                    $rec = $receiving->receptions;
                                                    $recYds = $receiving->yards;
                                                    $recTds = $receiving->touchdowns;
                                                }
                                                if (property_exists($stat, 'fumbles')) {
                                                    $fum = $stat->fumbles->lost_fumbles;
                                                }

                                                // Save the data
                                                echo 'Save data for '.$player->name.': pos: '.$player->position.' team: '.$team->name.' gp: '.$gp.'<br>';
                                                $sql = $conn->prepare("INSERT INTO player_data (preseason_ranking_id, sportradar_id, year, type, team_abbr,
                                                    games_played, pass_attempts, pass_completions, pass_yards, pass_touchdowns, pass_interceptions,
                                                    rush_attempts, rush_yards, rush_touchdowns, rec_targets, rec_receptions, rec_yards, rec_touchdowns, fumbles)
                                                    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
                                                $sql->bind_param('isissiiiiiiiiiiiiii', $rankingId, $playerData->id, $season->year, $season->type, $team->alias,
                                                    $gp, $passAtt, $comp, $passYds, $passTds, $int, $rushAtt, $rushYds, $rushTds, $tar, $rec, $recYds, $recTds, $fum);
                                                $sql->execute();
                                            }
                                            
                                        }
                                    }
                                }
                            }
                        }
                    }

                    ?>
                    <br><br><a href="index.html">Back to Draft page</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>



<?php

    function makeRequest(string $url)
    {
        sleep(2);
        $response = '';
        $curl = curl_init();

        $endpoint = 'http://api.sportradar.us/nfl/official/trial/v6/en/'.$url.'?api_key=788revq5yw5uzjyyqfqcbtw5';

        curl_setopt_array($curl, [
        CURLOPT_URL => $endpoint,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => [
            "Accept: */*",
            "Accept-Encoding: gzip, deflate",
            "Cache-Control: no-cache",
            "Connection: keep-alive",
            "Content-Type: text/plain",
            "cache-control: no-cache",
        ],
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        return json_decode($response);
    }

    function saveTeams($teams)
    {
        global $conn;
        $teams = json_decode($teams);

        foreach ($teams->conferences as $conf) {
            foreach ($conf->divisions as $div) {
                foreach ($div->teams as $team) {

                    $name = $team->market.' '.$team->name;
                    $id = $team->id;
                    // Save the team
                    $sql = $conn->prepare("INSERT INTO nfl_teams (name, sportradar_id) VALUES (?,?)");
                    $sql->bind_param('ss', $name, $id);
                    $sql->execute();
                }
            }
        }
    }

    function saveOnePlayer()
    {
        global $conn;
        // First save the player profile response in the file
        $playerData = file_get_contents('draft/files/exPlayer.json');
        $playerData = json_decode($playerData);

        // Lookup the rankingId manually from preseason_rankings table
        $rankingId = 118;

        if ($playerData && property_exists($playerData, 'seasons')) {
            foreach ($playerData->seasons as $season) {
var_dump($season);
                foreach ($season->teams as $team) {

                    $stat = $team->statistics;
                    $gp = $passAtt = $comp = $passYds = $passTds = $int = $rushAtt = $rushYds = $rushTds = $tar = $rec = $recYds = $recTds = $fum = 0;

                    $gp = $stat->games_played;
                    if (property_exists($stat, 'passing')) {
                        $pass = $stat->passing;
                        $passAtt = $pass->attempts;
                        $comp = $pass->completions;
                        $passYds = $pass->yards;
                        $passTds = $pass->touchdowns;
                        $int = $pass->interceptions;
                    }
                    if (property_exists($stat, 'rushing')) {
                        $rush = $stat->rushing;
                        $rushAtt = $rush->attempts;
                        $rushYds = $rush->yards;
                        $rushTds = $rush->touchdowns;
                    }
                    if (property_exists($stat, 'receiving')) {
                        $receiving = $stat->receiving;
                        $tar = $receiving->targets;
                        $rec = $receiving->receptions;
                        $recYds = $receiving->yards;
                        $recTds = $receiving->touchdowns;
                    }
                    if (property_exists($stat, 'fumbles')) {
                        $fum = $stat->fumbles->lost_fumbles;
                    }

                    // Save the data
                    $sql = $conn->prepare("INSERT INTO player_data (preseason_ranking_id, sportradar_id, year, type, team_abbr,
                        games_played, pass_attempts, pass_completions, pass_yards, pass_touchdowns, pass_interceptions,
                        rush_attempts, rush_yards, rush_touchdowns, rec_targets, rec_receptions, rec_yards, rec_touchdowns, fumbles)
                        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
                    $sql->bind_param('isissiiiiiiiiiiiiii', $rankingId, $playerData->id, $season->year, $season->type, $team->alias,
                        $gp, $passAtt, $comp, $passYds, $passTds, $int, $rushAtt, $rushYds, $rushTds, $tar, $rec, $recYds, $recTds, $fum);
                    $sql->execute();
                }
            }
        }
    }

?>