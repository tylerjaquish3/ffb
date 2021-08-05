<?php

    $pageName = 'Draft Helper';
	require_once 'header.php';

    $currentYear = $_POST['currentYear'];
    $draftOrder = (array) json_decode($_POST['draftOrder']);

    $output = '<div class="row">';
    $top5table = getTop5Table($currentYear);
    $addPlayer = '
            <br />
            <input type="text" id="new-player"><button id="new-player-btn">Add Player</button>
            <br /><br /><br />';
    $turnPosTable = getTurnPosTable($draftOrder);
    $rollingListTable = getRollingListTable();

    $output .= $top5table.$turnPosTable.$addPlayer.$rollingListTable;

    $output .= '</div></div>';

    echo $output;
    die;

    function getTop5Table($currentYear)
    {
        global $conn;
        $output = '
            <div class="col-xs-12 col-md-9">
                <div class="card">
                    <div class="card-body">
                        <div class="position-relative">
                            <div class="card-header">
                                <h3>Top 5 by Position</h3>
                            </div>

                            <table class="table table-responsive" id="datatable-top5">
                                <thead>
                                    <th>My Rank</th>
                                    <th>ADP</th>
                                    <th>Player</th>
                                    <th>Team</th>
                                    <th>Bye</th>
                                    <th>SoS</th>
                                    <th>line</th>
                                    <th>Tier</th>
                                    <th>Diff</th>
                                    <th></th>
                                    <th>GP</th>
                                    <th>Pts</th>
                                    <th>Pts/Gm</th>
                                    <th>Yds</th>
                                    <th>TDs</th>
                                    <th>Rec</th>
                                    <th>Proj Pts</th>
                                    <th></th>
                                </thead>
                                <tbody>';

                                $lastProj = $lastPos = null;
                                $result = mysqli_query( $conn,
                                    "(
                                    select *
                                    from preseason_rankings pr
                                    LEFT JOIN draft_selections ON pr.id = draft_selections.ranking_id
                                    LEFT JOIN player_data pd ON pd.preseason_ranking_id = pr.id AND pd.type = 'REG' AND pd.year = ($currentYear-1)
                                    where position = 'QB' AND ranking_id IS NULL
                                    order by my_rank asc
                                    LIMIT 5
                                    ) UNION ALL (
                                    select *
                                    from preseason_rankings pr
                                    LEFT JOIN draft_selections ON pr.id = draft_selections.ranking_id
                                    LEFT JOIN player_data pd ON pd.preseason_ranking_id = pr.id AND pd.type = 'REG' AND pd.year = ($currentYear-1)
                                    where position = 'RB' AND ranking_id IS NULL
                                    order by my_rank asc
                                    LIMIT 5
                                    ) UNION ALL (
                                    select *
                                    from preseason_rankings pr
                                    LEFT JOIN draft_selections ON pr.id = draft_selections.ranking_id
                                    LEFT JOIN player_data pd ON pd.preseason_ranking_id = pr.id AND pd.type = 'REG' AND pd.year = ($currentYear-1)
                                    where position = 'WR' AND ranking_id IS NULL
                                    order by my_rank asc
                                    LIMIT 5
                                    ) UNION ALL (
                                    select *
                                    from preseason_rankings pr
                                    LEFT JOIN draft_selections ON pr.id = draft_selections.ranking_id
                                    LEFT JOIN player_data pd ON pd.preseason_ranking_id = pr.id AND pd.type = 'REG' AND pd.year = ($currentYear-1)
                                    where position = 'TE' AND ranking_id IS NULL
                                    order by my_rank asc
                                    LIMIT 5
                                    ) UNION ALL (
                                    select *
                                    from preseason_rankings pr
                                    LEFT JOIN draft_selections ON pr.id = draft_selections.ranking_id
                                    LEFT JOIN player_data pd ON pd.preseason_ranking_id = pr.id AND pd.type = 'REG' AND pd.year = ($currentYear-1)
                                    where position = 'DEF' AND ranking_id IS NULL
                                    order by my_rank asc
                                    LIMIT 5
                                    ) UNION ALL (
                                    select *
                                    from preseason_rankings pr
                                    LEFT JOIN draft_selections ON pr.id = draft_selections.ranking_id
                                    LEFT JOIN player_data pd ON pd.preseason_ranking_id = pr.id AND pd.type = 'REG' AND pd.year = ($currentYear-1)
                                    where position = 'K' AND ranking_id IS NULL
                                    order by my_rank asc
                                    LIMIT 5
                                    ) UNION ALL (
                                    select *
                                    from preseason_rankings pr
                                    LEFT JOIN draft_selections ON pr.id = draft_selections.ranking_id
                                    LEFT JOIN player_data pd ON pd.preseason_ranking_id = pr.id AND pd.type = 'REG' AND pd.year = ($currentYear-1)
                                    where (position = 'D' OR position = 'DB') AND ranking_id IS NULL
                                    order by my_rank asc
                                    LIMIT 5
                                    )"
                                );
                                while ($row = mysqli_fetch_array($result)) {

                                    $sosColor = ($row['sos'] > 25) ? 'bad' : ($row['sos'] < 7 ? 'good' : '');
                                    $lineColor = ($row['line'] > 25) ? 'bad' : ($row['line'] < 7 ? 'good' : '');
                                    $ppg = ($row['games_played'] > 0) ? round($row['points'] / $row['games_played'], 1) : null;
                                    $diff = ($lastProj && $lastPos == $row['position']) ? $lastProj-$row['proj_points'] : '';
                                    $icon = desigIcon($row['designation'], $row['notes'] ? true : false);

                                    $output .= '
                                    <tr class="color-'.$row['position'].'">
                                        <td>'.$row['my_rank'].'</td>
                                        <td>'.$row['adp'].'</td>
                                        <td><a data-toggle="modal" data-target="#player-data" onclick="showPlayerData('.(int)$row[0].')">'.$row['player'].'</a></td>
                                        <td>'.$row['team'].'</td>
                                        <td>'.$row['bye'].'</td>
                                        <td class="color-'.$sosColor.'">'.$row['sos'].'</td>
                                        <td class="color-'.$lineColor.'">'.$row['line'].'</td>
                                        <td>'.$row['tier'].'</td>
                                        <td>'.$diff.'</td>
                                        <td><a class="btn btn-secondary taken">Taken</a><a class="btn btn-secondary mine">Mine!</a></td>
                                        <td>'.$row['games_played'].'</td>
                                        <td>'.$row['points'].'</td>
                                        <td>'.$ppg.'</td>
                                        <td>'.($row['pass_yards'] + $row['rush_yards'] + $row['rec_yards']).'</td>
                                        <td>'.($row['pass_touchdowns']+$row['rush_touchdowns']+$row['rec_touchdowns']).'</td>
                                        <td>'.$row['rec_receptions'].'</td>
                                        <td>'.$row['proj_points'].'</td>
                                        <td>'.$icon.'</td>
                                    </tr>';

                                    $lastProj = $row['proj_points'];
                                    $lastPos = $row['position'];
                                }
                            $output .= '
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>';

        return $output;
    }

    function getTurnPosTable($draftOrder)
    {
        global $conn;
        $manOrder = array_keys($draftOrder);
        $myKey = array_search('Tyler', $manOrder);
        $turnPicks = $turnPlayers = [];
        if ($myKey < 5) {
            $turnManagers = array_slice($manOrder, 0, $myKey);
            for ($x = 1; $x <= $myKey; $x++) {
                $turnPicks[] = $x;
            }
        } else {
            $turnManagers = array_slice($manOrder, $myKey+1);
            for ($x = $myKey+2; $x <= 10; $x++) {
                $turnPicks[] = $x;
            }
        }

        foreach ($turnManagers as $man) {
            $turnPlayers[$man] = ['QB'=>0,'RB'=>0,'WR'=>0,'TE'=>0,'K'=>0,'DEF'=>0,'D'=>0,'DB'=>0];
        }

        $i = 0;
        foreach ($turnManagers as $man) {
            $pickNum = $turnPicks[$i];
            $allPickNums = [];

            // Build the pick numbers for the query
            for ($x = $pickNum; $x < 221; $x+= 20) {
                $allPickNums[] = $x;
            }
            for ($x = 21-$pickNum; $x < 221; $x+= 20) {
                $allPickNums[] = $x;
            }
            $allPickNumsString = implode(",", $allPickNums);

            $result = mysqli_query($conn, "select position, count(preseason_rankings.id) as spots from draft_selections
                join preseason_rankings on ranking_id = preseason_rankings.id
                where pick_number in (".$allPickNumsString.")
                group by position");
            while ($row = mysqli_fetch_array($result)) {
                $turnPlayers[$man][$row['position']] = $row['spots'];
            }

            $i++;
        }

        $output = '
            <div class="col-xs-12 col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="position-relative">
                            <div class="card-header">
                                <h3>Turn Positions</h3>
                            </div>
                            <table class="table table-responsive" id="datatable-turn">
                                <thead>
                                    <th>Pos</th>';
                                    foreach ($turnManagers as $man) {
                                        $output .= '<th>'.$man.'</th>';
                                    }
                                $output .= '
                                </thead>
                                <tbody>
                                    <tr><td>QB</td>';
                                    foreach ($turnPlayers as $player) {
                                        $output .= '<td>'.$player['QB'].'</td>';
                                    }
                                    $output .= '
                                    </tr>
                                    <tr><td>RB</td>';
                                    foreach ($turnPlayers as $player) {
                                        $output .= '<td>'.$player['RB'].'</td>';
                                    }
                                    $output .= '
                                    </tr>
                                    <tr><td>WR</td>';
                                    foreach ($turnPlayers as $player) {
                                        $output .= '<td>'.$player['WR'].'</td>';
                                    }
                                    $output .= '
                                    </tr>
                                    <tr><td>TE</td>';
                                    foreach ($turnPlayers as $player) {
                                        $output .= '<td>'.$player['TE'].'</td>';
                                    }
                                    $output .= '
                                    </tr>
                                    <tr><td>DEF</td>';
                                    foreach ($turnPlayers as $player) {
                                        $output .= '<td>'.$player['DEF'].'</td>';
                                    }
                                    $output .= '
                                    </tr>
                                    <tr><td>K</td>';
                                    foreach ($turnPlayers as $player) {
                                        $output .= '<td>'.$player['K'].'</td>';
                                    }
                                    $output .= '
                                    </tr>
                                    <tr><td>D</td>';
                                    foreach ($turnPlayers as $player) {
                                        $output .= '<td>'.$player['D'].'</td>';
                                    }
                                    $output .= '
                                    </tr>
                                    <tr><td>DB</td>';

                                    foreach ($turnPlayers as $player) {
                                        $output .= '<td>'.$player['DB'].'</td>';
                                    }
                                    $output .= '
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            ';

            return $output;
        }

        function getRollingListTable()
        {
            global $conn;
            $output = '
            <div class="card">
                <div class="card-body">
                    <div class="position-relative">
                        <div class="card-header">
                            <h3>Rolling List</h3>
                        </div>
                        <table class="table table-responsive" id="datatable-rolling-list">
                        <thead>
                            <th>Pick</th>
                            <th>Player</th>
                            <th>ADP</th>
                        </thead>
                        <tbody>';
                            $result = mysqli_query(
                                $conn,
                                "SELECT pick_number, player, position, adp FROM draft_selections ds
                                JOIN preseason_rankings pr ON pr.id = ds.ranking_id
                                ORDER BY pick_number desc LIMIT 20"
                            );
                            while ($row = mysqli_fetch_array($result)) {
                                $goodPick = $row['pick_number'] >= $row['adp'] ? 'good-pick' : 'bad-pick';

                                $output .= '<tr class="color-'.$row['position'].'">
                                <td><span class="sub '.$goodPick.'">'.$row['pick_number'].'</span></td>
                                <td>'.$row['player'].'</td>
                                <td>'.$row['adp'].'</td>
                                </tr>';

                            }

                        $output .= '
                        </tbody>
                    </table>
                </div>
            </div>
        ';

        return $output;

    }

    echo $output;

?>