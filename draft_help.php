<!DOCTYPE html>
<html lang="en" data-textdirection="rtl" class="loading">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">

    <title>Draft Helper</title>

    <link rel="icon" type="image/png" href="/images/favicon.jpg">
    <meta property="og:title" content="Suntown Fantasy Football League" />
    <meta property="og:description" content="The best league in all the land" />
    <meta property="og:url" content="http://suntownffb.us" />
    <meta property="og:image" content="http://suntownffb.us/images/favicon.jpg" />

    <link rel="stylesheet" type="text/css" href="assets/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="assets/icomoon.css">
    <link rel="stylesheet" type="text/css" href="assets/bootstrap-extended.min.css">
    <link rel="stylesheet" type="text/css" href="assets/app.min.css">
    <!-- <link href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css" rel="stylesheet"> -->
    <link rel="stylesheet" type="text/css" href="assets/dataTables.min.css">
    <link rel="stylesheet" type="text/css" href="assets/suntown.css">
    <link rel="stylesheet" type="text/css" href="assets/responsive.css">
</head>

<?php
$currentYear = 2021;
$draftOrder = [
    'Ben',
    'Cameron',
    'AJ',
	'Tyler',
    'Gavin',
    'Everett',
    'Justin',
    'Andy',
    'Matt',
    'Cole',
];

function desigIcon($id, $hasNote)
{
    $note = '';
    if ($hasNote) {
        $note = '<i class="icon-file-text" title="Note"></i>';
    }
    if ($id == 'bust') {
        return '<i class="icon-aid-kit" title="Bust"></i>'.$note;
    }
    if ($id == 'value') {
        return '<i class="icon-price-tag" title="Value"></i>'.$note;
    }
    if ($id == 'sleeper') {
        return '<i class="icon-sleepy2" title="Sleeper"></i>'.$note;
    }
    if ($id == 'breakout') {
        return '<i class="icon-star-full" title="Breakout"></i>'.$note;
    }
    return $note;
}
?>

<body data-open="click" data-menu="vertical-menu" data-col="2-columns" class="vertical-layout vertical-menu 2-columns fixed-navbar">

    <!-- navbar-fixed-top-->
    <nav class="header-navbar navbar navbar-with-menu navbar-fixed-top navbar-semi-dark navbar-shadow">
        <div class="navbar-wrapper">
            <div class="navbar-header">
                <ul class="nav navbar-nav">
                    <li class="nav-item mobile-menu hidden-md-up float-xs-left"><a class="nav-link nav-menu-main menu-toggle hidden-xs"><i class="icon-menu5 font-large-1"></i></a></li>
                    <li class="nav-item">
                        <h2>Suntown FFB</h2>
                    </li>
                    <li class="nav-item hidden-md-up float-xs-right"><a data-toggle="collapse" data-target="#navbar-mobile" class="nav-link open-navbar-container"><i class="icon-ellipsis pe-2x icon-icon-rotate-right-right"></i></a></li>
                </ul>
            </div>
            <div class="navbar-container content container-fluid">
                <div id="navbar-mobile">
                    <h2>&nbsp;<?php echo $currentYear; ?> Draft Helper &nbsp;</h2>
                </div>
            </div>
        </div>
    </nav>

    <?php include 'functions.php'; ?>

    <div class="app-content container-fluid">
        <div class="content-wrapper">
            <div class="content-header row"></div>

            <div class="content-body">

                <div class="row">
                    <div class="col-xs-12 col-md-9">
                        <div class="card">
                            <div class="card-body">
                                <div class="position-relative">
                                    <?php
                                        $result = mysqli_query(
                                            $conn,
                                            "SELECT count(id) as picks FROM draft_selections"
                                        );
                                        while ($row = mysqli_fetch_array($result)) {
                                            $currentPick = $row['picks'] + 1;
                                        }

                                        $pickers = [];
                                        $allDraftOrder = array_reverse($draftOrder);
                                        for ($x = 0; $x < 30; $x++) {
                                            $allDraftOrder = array_reverse($allDraftOrder);
                                            if ($x % 2 != 0) {
                                                // Odd round
                                            }
                                            foreach ($allDraftOrder as $man) {
                                                $pickers[] = $man;
                                            }
                                        }

                                        $myKey = array_search('Tyler', $draftOrder);
                                        $oddRounds = $myKey + 1;
                                        $evenRounds = 10 - $myKey;
                                        $allMyPicks = [];
                                        for ($x = 0; $x < 30; $x++) {
                                            if ($x % 2 == 0) {
                                                // Even round
                                                $allMyPicks[] = $x * 10 + $oddRounds;
                                            } else {
                                                $allMyPicks[] = $x * 10 + $evenRounds;
                                            }
                                        }

                                        foreach ($allMyPicks as $pick) {
                                            if ($pick >= $currentPick) {
                                                $myNextPick = $pick - $currentPick;
                                                break;
                                            }
                                        }
                                        ?>
                                    <div class="card-header">
                                        <h3>
                                            Current Pick: <?php echo $pickers[$currentPick-1].' ('.$currentPick.')'; ?>
                                            &nbsp;|&nbsp;
                                            My Next Pick: <?php echo $myNextPick; ?>
                                        </h3>
                                        <a type="button" data-toggle="modal" data-target="#draft-board" href="#">Draft Board</a>
                                        &nbsp;|&nbsp;
                                        <a type="button" data-toggle="modal" data-target="#cheat-sheet" href="#">Cheat Sheet</a>
                                        &nbsp;|&nbsp;
                                        <a type="button" data-toggle="modal" data-target="#proj-standings" href="#">Projected Standings</a>
                                        &nbsp;|&nbsp;
                                        <a type="button" id="hide-te">Hide TE</a>
                                        &nbsp;|&nbsp;
                                        <a type="button" id="hide-def">Hide DEF</a>
                                        &nbsp;|&nbsp;
                                        <a type="button" id="hide-k">Hide K</a>
                                        &nbsp;|&nbsp;
                                        <a type="button" id="undoPick">Undo Pick</a>
                                        &nbsp;|&nbsp;
                                        <a type="button" id="restartDraft">Restart Draft</a>
                                    </div>
                                    <table class="table table-responsive" id="datatable-players">
                                        <thead>
                                            <th>My Rank</th>
                                            <th>ADP</th>
                                            <th>Player</th>
                                            <th>Pos</th>
                                            <th>Team</th>
                                            <th>Bye</th>
                                            <th>SoS</th>
                                            <th>Tier</th>
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
                                        <tbody>
                                            <?php
                                            $count = 0;
                                            $result = mysqli_query($conn,
                                                "SELECT * FROM preseason_rankings pr
                                                LEFT JOIN draft_selections ON pr.id = draft_selections.ranking_id
                                                JOIN player_data pd ON pd.preseason_ranking_id = pr.id
                                                WHERE ranking_id IS NULL AND pd.type = 'REG' AND pd.year = ($currentYear-1)
                                                ORDER BY my_rank ASC"
                                            );
                                            while ($row = mysqli_fetch_array($result)) {
                                                $count++;
                                                if ($count == $myNextPick) {
                                                    $myRank = $row['my_rank']+1;
                                                    echo '<tr class="color-black"><td>'.$myRank.'</td><td></td><td></td><td></td><td></td><td></td><td></td>
                                                    <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>';
                                                }
                                            ?>
                                                <tr class="color-<?php echo $row['position']; ?>">
                                                    <td><?php echo $row['my_rank']; ?></td>
                                                    <td><?php echo $row['adp']; ?></td>
                                                    <td><?php echo '<a data-toggle="modal" data-target="#player-data" onclick="showPlayerData('.(int)$row[0].')">'.$row['player'].'</a>'; ?></td>
                                                    <td><?php echo $row['position']; ?></td>
                                                    <td><?php echo $row['team']; ?></td>
                                                    <td><?php echo $row['bye']; ?></td>
                                                    <td><?php echo $row['sos']; ?></td>
                                                    <td><?php echo $row['tier']; ?></td>
                                                    <td><a class="btn btn-secondary taken">Taken</a><a class="btn btn-secondary mine">Mine!</a></td>
                                                    <td><?php echo $row['games_played']; ?></td>
                                                    <td><?php echo $row['points']; ?></td>
                                                    <td><?php echo $row['games_played'] > 0 ? round($row['points'] / $row['games_played'], 1) : null; ?></td>
                                                    <td><?php echo $row['pass_yards'] + $row['rush_yards'] + $row['rec_yards']; ?></td>
                                                    <td><?php echo $row['pass_touchdowns']+$row['rush_touchdowns']+$row['rec_touchdowns']; ?></td>
                                                    <td><?php echo $row['rec_receptions']; ?></td>
                                                    <td><?php echo $row['proj_points']; ?></td>
                                                    <td><?php echo desigIcon($row['designation'], $row['notes'] ? true : false); ?></td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-12 col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="position-relative">
                                    <div class="card-header">
                                        <h3>My Team</h3>
                                    </div>

                                    <table class="table table-responsive" id="datatable-team">
                                        <thead>
                                            <th>Pos</th>
                                            <th>Player</th>
                                            <th>Bye</th>
                                            <th>ADP</th>
                                            <th>Pick #</th>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $myPlayers = [];
                                            $result = mysqli_query(
                                                $conn,
                                                "SELECT * FROM preseason_rankings
                                                JOIN draft_selections ON preseason_rankings.id = draft_selections.ranking_id
                                                WHERE is_mine = 1 ORDER BY pick_number ASC"
                                            );
                                            while ($row = mysqli_fetch_array($result)) {
                                                $myPlayers[] = (int)$row['bye'];
                                                ?>
                                                <tr class="color-<?php echo $row['position']; ?>">
                                                    <td><?php echo $row['position']; ?></td>
                                                    <td><?php echo '<a data-toggle="modal" data-target="#player-data" onclick="showPlayerData('.(int)$row[0].')">'.$row['player'].'</a>' ?></td>
                                                    <td><?php echo $row['bye']; ?></td>
                                                    <td><?php echo $row['adp']; ?></td>
                                                    <td><?php echo $row['pick_number']; ?></td>
                                                </tr>
                                            <?php
                                            }
                                            $myPlayers = json_encode($myPlayers); ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
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
                                            <th>Pos</th>
                                            <th>Team</th>
                                            <th>Bye</th>
                                            <th>SoS</th>
                                            <th>Tier</th>
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
                                        <tbody>
                                            <?php
                                            $result = mysqli_query( $conn,
                                                "(
                                                select *
                                                from preseason_rankings pr
                                                LEFT JOIN draft_selections ON pr.id = draft_selections.ranking_id
                                                JOIN player_data pd ON pd.preseason_ranking_id = pr.id
                                                where position = 'QB' AND ranking_id IS NULL AND pd.type = 'REG' AND pd.year = ($currentYear-1)
                                                order by my_rank asc
                                                LIMIT 5
                                                ) UNION ALL (
                                                select *
                                                from preseason_rankings pr
                                                LEFT JOIN draft_selections ON pr.id = draft_selections.ranking_id
                                                JOIN player_data pd ON pd.preseason_ranking_id = pr.id
                                                where position = 'RB' AND ranking_id IS NULL AND pd.type = 'REG' AND pd.year = ($currentYear-1)
                                                order by my_rank asc
                                                LIMIT 5
                                                ) UNION ALL (
                                                select *
                                                from preseason_rankings pr
                                                LEFT JOIN draft_selections ON pr.id = draft_selections.ranking_id
                                                JOIN player_data pd ON pd.preseason_ranking_id = pr.id
                                                where position = 'WR' AND ranking_id IS NULL AND pd.type = 'REG' AND pd.year = ($currentYear-1)
                                                order by my_rank asc
                                                LIMIT 5
                                                ) UNION ALL (
                                                select *
                                                from preseason_rankings pr
                                                LEFT JOIN draft_selections ON pr.id = draft_selections.ranking_id
                                                JOIN player_data pd ON pd.preseason_ranking_id = pr.id
                                                where position = 'TE' AND ranking_id IS NULL AND pd.type = 'REG' AND pd.year = ($currentYear-1)
                                                order by my_rank asc
                                                LIMIT 5
                                                ) UNION ALL (
                                                select *
                                                from preseason_rankings pr
                                                LEFT JOIN draft_selections ON pr.id = draft_selections.ranking_id
                                                JOIN player_data pd ON pd.preseason_ranking_id = pr.id
                                                where position = 'DEF' AND ranking_id IS NULL AND pd.type = 'REG' AND pd.year = ($currentYear-1)
                                                order by my_rank asc
                                                LIMIT 5
                                                ) UNION ALL (
                                                select *
                                                from preseason_rankings pr
                                                LEFT JOIN draft_selections ON pr.id = draft_selections.ranking_id
                                                JOIN player_data pd ON pd.preseason_ranking_id = pr.id
                                                where position = 'K' AND ranking_id IS NULL AND pd.type = 'REG' AND pd.year = ($currentYear-1)
                                                order by my_rank asc
                                                LIMIT 5
                                                ) UNION ALL (
                                                select *
                                                from preseason_rankings pr
                                                LEFT JOIN draft_selections ON pr.id = draft_selections.ranking_id
                                                JOIN player_data pd ON pd.preseason_ranking_id = pr.id
                                                where (position = 'D' OR position = 'DB') AND ranking_id IS NULL AND pd.type = 'REG' AND pd.year = ($currentYear-1)
                                                order by my_rank asc
                                                LIMIT 5
                                                )"
                                            );
                                            while ($row = mysqli_fetch_array($result)) { ?>
                                                <tr class="color-<?php echo $row['position']; ?>">
                                                    <td><?php echo $row['my_rank']; ?></td>
                                                    <td><?php echo $row['adp']; ?></td>
                                                    <td><?php echo $row['data_url'] ? '<a data-toggle="modal" data-target="#player-data" onclick="showPlayerData('.(int)$row[0].')">'.$row['player'].'</a>' : $row['player'] ?></td>
                                                    <td><?php echo $row['position']; ?></td>
                                                    <td><?php echo $row['team']; ?></td>
                                                    <td><?php echo $row['bye']; ?></td>
                                                    <td><?php echo $row['sos']; ?></td>
                                                    <td><?php echo $row['tier']; ?></td>
                                                    <td><a class="btn btn-secondary taken">Taken</a><a class="btn btn-secondary mine">Mine!</a></td>
                                                    <td><?php echo $row['games_played']; ?></td>
                                                    <td><?php echo $row['points']; ?></td>
                                                    <td><?php echo $row['games_played'] > 0 ? round($row['points'] / $row['games_played'], 1) : null; ?></td>
                                                    <td><?php echo $row['pass_yards'] + $row['rush_yards'] + $row['rec_yards']; ?></td>
                                                    <td><?php echo $row['pass_touchdowns']+$row['rush_touchdowns']+$row['rec_touchdowns']; ?></td>
                                                    <td><?php echo $row['rec_receptions']; ?></td>
                                                    <td><?php echo $row['proj_points']; ?></td>
                                                    <td><?php echo desigIcon($row['designation'], $row['notes'] ? true : false); ?></td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php
                    $myKey = array_search('Tyler', $draftOrder);
                    $turnPicks = $turnPlayers = [];
                    if ($myKey < 5) {
                        $turnManagers = array_slice($draftOrder, 0, $myKey);
                        for ($x = 1; $x <= $myKey; $x++) {
                            $turnPicks[] = $x;
                        }
                    } else {
                        $turnManagers = array_slice($draftOrder, $myKey+1);
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

                    ?>
                    <div class="col-xs-12 col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="position-relative">
                                    <div class="card-header">
                                        <h3>Turn Positions</h3>
                                    </div>

                                    <table class="table table-responsive" id="datatable-turn">
                                        <thead>
                                            <th>Pos</th>
                                            <?php foreach ($turnManagers as $man) {
                                                echo '<th>'.$man.'</th>';
                                            }?>
                                        </thead>
                                        <tbody>
                                            <tr><td>QB</td>
                                            <?php
                                            foreach ($turnPlayers as $player) {
                                                echo '<td>'.$player['QB'].'</td>';
                                            }
                                            ?>
                                            </tr>
                                            <tr><td>RB</td>
                                            <?php
                                            foreach ($turnPlayers as $player) {
                                                echo '<td>'.$player['RB'].'</td>';
                                            }
                                            ?>
                                            </tr>
                                            <tr><td>WR</td>
                                            <?php
                                            foreach ($turnPlayers as $player) {
                                                echo '<td>'.$player['WR'].'</td>';
                                            }
                                            ?>
                                            </tr>
                                            <tr><td>TE</td>
                                            <?php
                                            foreach ($turnPlayers as $player) {
                                                echo '<td>'.$player['TE'].'</td>';
                                            }
                                            ?>
                                            </tr>
                                            <tr><td>DEF</td>
                                            <?php
                                            foreach ($turnPlayers as $player) {
                                                echo '<td>'.$player['DEF'].'</td>';
                                            }
                                            ?>
                                            </tr>
                                            <tr><td>K</td>
                                            <?php
                                            foreach ($turnPlayers as $player) {
                                                echo '<td>'.$player['K'].'</td>';
                                            }
                                            ?>
                                            </tr>
                                            <tr><td>D</td>
                                            <?php
                                            foreach ($turnPlayers as $player) {
                                                echo '<td>'.$player['D'].'</td>';
                                            }
                                            ?>
                                            </tr>
                                            <tr><td>DB</td>
                                            <?php
                                            foreach ($turnPlayers as $player) {
                                                echo '<td>'.$player['DB'].'</td>';
                                            }
                                            ?>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <br />
                        <input type="text" id="new-player"><button id="new-player-btn">Add Player</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="draft-board" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
                    <h4 class="modal-title">Draft Board</h4>
                </div>
                <div class="modal-body">

                    <table class="table table-responsive" id="datatable-board">
                        <thead>
                            <?php
                            foreach ($draftOrder as $man) {
                                echo '<th>'.$man.'</th>';
                            }
                            ?>
                        </thead>
                        <tbody>
                            <?php
                            for($round = 1; $round <= 22; $round++) {
                                $pickMin = ($round*10)-10;
                                $pickMax = $round*10;
                                $dir = 'asc';
                                // Even rounds go backwards
                                if ($round % 2 == 0) {
                                    $dir = 'desc';
                                }
                                echo '<tr>';

                                $result = mysqli_query(
                                    $conn,
                                    "SELECT pick_number, player, position, adp FROM draft_selections ds
                                    JOIN preseason_rankings pr ON pr.id = ds.ranking_id
                                    WHERE pick_number <= $pickMax
                                    AND pick_number > $pickMin
                                    ORDER BY pick_number $dir"
                                );

                                $count = mysqli_num_rows($result);
                                if ($round % 2 == 0 && $count < 10) {
                                    for ($x = 0; $x < (10-$count); $x++) {
                                        echo '<td></td>';
                                    }
                                }

                                while ($row = mysqli_fetch_array($result)) {
                                    $goodPick = $row['pick_number'] >= $row['adp'] ? 'good-pick' : 'bad-pick';
                                    ?>
                                    <td class="color-<?php echo $row['position']; ?>"><?php echo '<span class="sub '.$goodPick.'">'.$row['pick_number'].'</span>&nbsp;'.$row['player']; ?></td>
                            <?php }
                                echo '</tr>';
                            } ?>
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="cheat-sheet" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
                    <h4 class="modal-title">Cheat Sheet</h4>
                </div>
                <div class="modal-body" style="direction: ltr">
                    <div class="row">
                        <?php
                            $positions = ['QB','RB','WR','TE'];
                            $tiers = [1,2,3,4,5,6,7,8];
                            $posRank = 1;

                            foreach ($positions as $pos) {
                                echo '<div class="col-md-3">';
                                foreach ($tiers as $tier) {

                                    echo '<strong>Tier '.$tier.'</strong><br />';

                                    $result = mysqli_query(
                                        $conn,
                                        "SELECT tier, my_rank, player, position, pick_number, proj_points, is_mine
                                        FROM preseason_rankings pr
                                        LEFT JOIN draft_selections ds ON pr.id = ds.ranking_id
                                        WHERE position = '$pos'
                                        AND tier = $tier
                                        ORDER BY my_rank"
                                    );
                                    while ($row = mysqli_fetch_array($result)) {
                                        if ($tier == $row['tier']) {
                                            $class = '';

                                            if ($row['pick_number']) {
                                                $class = 'strike';
                                            }
                                            if ($row['is_mine']) {
                                                $class = 'strike mine';
                                            }
                                            echo '<span class="'.$class.'">'.$row['my_rank'].'. '.$row['player'].' ('.$row['proj_points'].')</span><br />';
                                        }
                                    }
                                }
                                echo '</div>';
                            }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="proj-standings" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
                    <h4 class="modal-title">Projected Standings</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-xs-12">
                            <table class="table table-responsive" id="datatable-standings">
                                <thead>
                                    <th>Manager</th>
                                    <th>QB</th>
                                    <th>RB</th>
                                    <th>WR</th>
                                    <th>TE</th>
                                    <th>DEF</th>
                                    <th>K</th>
                                    <th>Total</th>
                                </thead>
                                <tbody>
                                    <?php
                                    $drafted = [];
                                    foreach ($draftOrder as $man) {
                                        $drafted[$man] = ['QB' => 0,'RB' => 0,'WR' => 0,'TE' => 0,'DEF' => 0,'K' => 0,'Total' => 0,
                                        'QBc' => 0, 'RBc' => 0, 'WRc' => 0, 'TEc' => 0, 'DEFc' => 0, 'Kc' => 0,
                                        'QBm' => 2, 'RBm' => 3, 'WRm' => 3, 'TEm' => 1, 'DEFm' => 1, 'Km' => 1];

                                    }
                                    $count = 0;
                                    $result = mysqli_query($conn,
                                        "SELECT name, proj_points, position, pick_number
                                        FROM preseason_rankings
                                        JOIN draft_selections ON preseason_rankings.id = draft_selections.ranking_id
                                        JOIN managers ON managers.id = draft_selections.manager_id"
                                    );
                                    while ($row = mysqli_fetch_array($result)) {
                                        $pos = $row['position'];
                                        if ($pos) {
                                            // Only count the position if less than max at the position
                                            // This is to only sum the starting lineup, not all players
                                            if ($drafted[$row['name']][$pos.'c'] < $drafted[$row['name']][$pos.'m']) {
                                                $drafted[$row['name']][$pos] += (int)$row['proj_points'];
                                                $drafted[$row['name']][$pos.'c']++;
                                                $drafted[$row['name']]['Total'] += (int)$row['proj_points'];
                                            }
                                        }
                                    }
                                    foreach ($drafted as $man => $row) {
                                    ?>
                                        <tr>
                                            <td><?php echo $man; ?></td>
                                            <td><?php echo $row['QB']; ?></td>
                                            <td><?php echo $row['RB']; ?></td>
                                            <td><?php echo $row['WR']; ?></td>
                                            <td><?php echo $row['TE']; ?></td>
                                            <td><?php echo $row['DEF']; ?></td>
                                            <td><?php echo $row['K']; ?></td>
                                            <td><?php echo $row['Total']; ?></td>
                                        </tr>
                                    <?php
                                    } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="player-data" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content" style="direction: ltr">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
                    <h4 class="modal-title">Player Data</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-xs-12">
                            <input type="hidden" id="player-id">
                            <div id="player-header"></div>
                            <div id="fetched-data"></div>
                            <textarea id="player-notes" cols=150 rows=6></textarea>
                            <br /><a class="btn btn-secondary mine" id="save-note">Save</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include 'footer.html'; ?>

<script type="text/javascript">

    let myPlayers = "<?php echo $myPlayers; ?>";
    myPlayers = JSON.parse(myPlayers);

    $(document).ready(function() {

        var playersTable = $('#datatable-players').DataTable({
            "order": [
                [0, "asc"]
            ]
        });

        var teamTable = $('#datatable-team').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "sort": false
        });

        var top5Table = $('#datatable-top5').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "sort": false
        });

        var turnTable = $('#datatable-turn').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "sort": false
        });

        var standingsTable = $('#datatable-standings').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [
                [7, "desc"]
            ]
        });

        $('#datatable-players').on('click', 'tbody .taken', function () {
            var data_row = playersTable.row($(this).closest('tr')).data();
            pickManager = "<?php echo $pickers[$currentPick-1]; ?>";
            data_row.push(pickManager);
            data_row.push('taken');
            console.log(data_row);
            var formData = {data: data_row};

            saveSelection(formData);
        });

        $('#datatable-players').on('click', 'tbody .mine', function () {
            var data_row = playersTable.row($(this).closest('tr')).data();
            pickManager = "<?php echo $pickers[$currentPick-1]; ?>";
            data_row.push(pickManager);
            data_row.push('mine');
            console.log(data_row);
            var formData = {data: data_row};

            let good = checkByes(data_row);
            if (!good) {
                if (confirm('Check the byes, bro.')) {
                    saveSelection(formData);
                }
            } else {
                saveSelection(formData);
            }
        });

        $('#datatable-top5').on('click', 'tbody .taken', function () {
            var data_row = top5Table.row($(this).closest('tr')).data();
            pickManager = "<?php echo $pickers[$currentPick-1]; ?>";
            data_row.push(pickManager);
            data_row.push('taken');
            console.log(data_row);
            var formData = {data: data_row};

            saveSelection(formData);
        });

        $('#datatable-top5').on('click', 'tbody .mine', function () {
            var data_row = top5Table.row($(this).closest('tr')).data();
            pickManager = "<?php echo $pickers[$currentPick-1]; ?>";
            data_row.push(pickManager);
            data_row.push('mine');
            console.log(data_row);
            var formData = {data: data_row};

            let good = checkByes(data_row);
            if (!good) {
                if (confirm('Check the byes, bro.')) {
                    saveSelection(formData);
                }
            } else {
                saveSelection(formData);
            }
        });

        $('#hide-te').click(function () {
            doSearch('TE');
        });

        $('#hide-def').click(function () {
            doSearch('DEF');
        });

        $('#hide-k').click(function () {
            doSearch('K');
        });

        function checkByes(data)
        {
            let players = myPlayers.length;
            if (players) {
                let count = myPlayers.filter(x => x == data[5]).length;
                if (count/players > .2) {
                    return false;
                }
            }

            return true;
        }

        var posArray = [];
        function doSearch(pos) {
            posArray.push(pos);
            let regex = '^(';

            posArray.forEach(function (item) {
                regex += '(?!'+item+')';
            });
            regex += '.)*$';

            playersTable.columns([3]).search(regex, true, false).draw();
        }

        function saveSelection(formData)
        {
            $.ajax({
                url: 'updateSelected.php',
                type: "POST",
                data: formData,
                async: false,
                dataType: 'json',
                success: function (response) {
                    if (response.type == 'error') {
                        alert('error');
                    } else {
                        location.reload();
                    }
                }
            });
        }

        $('#new-player-btn').click(function () {
            $.ajax({
                url: 'updateSelected.php',
                type: "POST",
                data: {newname: $('#new-player').val()},
                async: false,
                dataType: 'json',
                success: function (response) {
                    if (response.type == 'error') {
                        alert('error');
                    } else {
                        location.reload();
                    }
                }
            });
        });

        $('#undoPick').click(function () {
            if (confirm('Are you sure?')) {
                $.ajax({
                    url: 'updateSelected.php',
                    type: "POST",
                    data: {request: 'undo'},
                    async: false,
                    dataType: 'json',
                    success: function (response) {
                        if (response.type == 'error') {
                            alert('error');
                        } else {
                            location.reload();
                        }
                    }
                });
            }
        });

        $('#restartDraft').click(function () {
            if (confirm('Are you sure?')) {
                $.ajax({
                    url: 'updateSelected.php',
                    type: "POST",
                    data: {request: 'restart'},
                    async: false,
                    dataType: 'json',
                    success: function (response) {
                        if (response.type == 'error') {
                            alert('error');
                        } else {
                            location.reload();
                        }
                    }
                });
            }
        });
    });

    function showPlayerData(id)
    {
        $.ajax({
            type : 'post',
            url : 'updateSelected.php',
            data :  {
                request: 'player_data',
                id: id
            },
            success : function(data){
                let table = '<table id="player-history"><thead>';
                table += '<th>Year</th><th>Team</th><th>GP</th>';
                table += '<th>Pass Att</th><th>Comp</th><th>Pass Yds</th><th>Pass TDs</th><th>Int</th>';
                table += '<th>Rush Att</th><th>Rush Yds</th><th>Rush TDs</th>';
                table += '<th>Tar</th><th>Rec</th><th>Rec Yds</th><th>Rec TDs</th><th>Fumbles</th>';
                table += '</thead><tbody>';

                data = JSON.parse(data);
                // Loop through data to add rows
                data.forEach(function (item, index) {

                    if (index == 0) {
                        let header = '<h4>'+item.player+' ('+item.position + ' | ' + item.team+')</h4>';
                        $('#player-header').html(header);
                        $('#player-notes').val(item.notes);
                        $('#player-id').val(item.id);
                    } else {
                        table += '<tr>'+
                            '<td>'+item.year+'</td>'+
                            '<td>'+item.team+'</td>'+
                            '<td>'+item.games_played+'</td>'+
                            '<td>'+item.pass_attempts+'</td>'+
                            '<td>'+item.pass_completions+'</td>'+
                            '<td>'+item.pass_yards+'</td>'+
                            '<td>'+item.pass_touchdowns+'</td>'+
                            '<td>'+item.pass_interceptions+'</td>'+
                            '<td>'+item.rush_attempts+'</td>'+
                            '<td>'+item.rush_yards+'</td>'+
                            '<td>'+item.rush_touchdowns+'</td>'+
                            '<td>'+item.rec_targets+'</td>'+
                            '<td>'+item.rec_receptions+'</td>'+
                            '<td>'+item.rec_yards+'</td>'+
                            '<td>'+item.rec_touchdowns+'</td>'+
                            '<td>'+item.fumbles+'</td>';
                    }
                });

                table += '</tbody></table>';
                $('#fetched-data').html(table);
            }
        });

        $('#save-note').click(function() {
            $.ajax({
                type : 'post',
                url : 'updateSelected.php',
                data :  {
                    request: 'notes',
                    id: $('#player-id').val(),
                    notes: $('#player-notes').val()
                },
                success : function(data){

                }
            });
        });

    }
</script>

<style>
    .app-content.container-fluid {
        background: white;
        direction: ltr;
    }

    a.btn.btn-secondary {
        padding: .2rem 1rem;
    }

    a, a:link, a:visited {
        color: black;
    }

    a:hover {
        color: white;
    }

    .strike {
        text-decoration: line-through;
        color: gray;
    }

    .taken {
        background-color: #fa887f;
    }

    .mine {
        background-color: #8cfa84;
    }

    .color-QB td, .color-QB {
        background-color: aquamarine;
    }

    .color-RB td, .color-RB {
        background-color: burlywood;
    }

    .color-WR td, .color-WR {
        background-color: #fa9cff;
    }

    .color-TE td, .color-TE {
        background-color: #69cfff;
    }

    .color-DEF td, .color-DEF {
        background-color: #dffcde;
    }

    .color-K td, .color-K {
        background-color: #f7cbcc;
    }

    .color-D td, .color-DB td, .color-D, .color-DB {
        background-color: #fcf8b3;
    }

    tr.color-black td {
        background-color: #000;
        padding: 0px !important;
    }

    .modal-lg {
        max-width: 90%;
    }

    #datatable-board td {
        padding: 15px;
        font-weight: bold;
    }

    #datatable-board .sub {
        font-weight: 400;
        font-size: 16px;
    }

    .good-pick {
        color: green;
    }

    .bad-pick {
        color: red;
    }

    table#player-history td, th {
        padding: 10px 15px;
    }
</style>