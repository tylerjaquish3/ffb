<?php

    include '../functions.php';

    // Only need to do this once to save the nfl teams
    // $teams = file_get_contents('draft/files/leagueHierarchy.json');
    // saveTeams($teams);

    // This is if you need to lookup and save a single player using the sandbox
    // saveOnePlayer();
    // die;

    // Update this before running to just get last year's stats
    $currentYear = 2022;

    $rankedNames = $rankedIds = [];
    // $result = mysqli_query($conn, "SELECT * FROM preseason_rankings");
    $result = mysqli_query($conn, "SELECT pr.id, pr.player FROM preseason_rankings pr
        LEFT JOIN player_data pd ON pd.preseason_ranking_id = pr.id
        ");
    while ($row = mysqli_fetch_array($result)) {

        // Using abbr name accounts for names with Jr, III, apostrophes in name, Pat vs Patrick, etc.
        // $names = explode(' ', $row['player']);
        // if (isset($names[1])) {
        //     $abbr = substr($names[0], 0, 1).'.'.$names[1];
        // }
        
        // This will cause a few errors with people like Josh Allen, 
        // where there is another NFL player with his same name.
        // it will just add an extra row (or 2) into player_data
        $name = $row['player'];
        if (!in_array($row['id'], $rankedIds)) {
            $rankedNames[$row['id']] = $name;
            $rankedIds[] = $row['id'];
        }
    }
// dd($rankedNames);
    // Foreach team, get roster
    $result = mysqli_query($conn, "SELECT * FROM nfl_teams");
    while ($row = mysqli_fetch_array($result)) {
        $teamId = $row['sportradar_id'];
        echo '<hr><h1>'.$row['name'].'</h1>';

        // Get roster for this team
        $roster = makeRequest('teams/'.$teamId.'/full_roster.json');
        // $roster = file_get_contents('draft/files/exRoster.json');
        // $roster = json_decode($roster);
        foreach ($roster->players as $player) {
            if (in_array($player->name, $rankedNames)) {
                var_dump($player->name);

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
                        if ($season->year == $currentYear-1) {
                            // Lookup my ranking id
                            $rankingId = array_search($player->name, $rankedNames);

                            if ($rankingId) {
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
    }

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