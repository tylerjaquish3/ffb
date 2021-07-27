<!DOCTYPE html>
<html lang="en" data-textdirection="rtl" class="loading">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">

    <title>Players List</title>

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
                    <h2>Player List</h2>
                </div>
            </div>
        </div>
    </nav>

    <?php include 'functions.php';
    $currentYear = 2021;
    ?>

    <div class="app-content container-fluid">
        <div class="content-wrapper">
            <div class="content-header row"></div>

            <div class="content-body">

                <div class="row">
                    <div class="col-xs-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="position-relative">
                                    <div class="card-header">
                                        <a type="button" id="hide-selected">Hide Selected</a>
                                        &nbsp;|&nbsp;
                                        <a type="button" id="hide-te">Hide TE</a>
                                        &nbsp;|&nbsp;
                                        <a type="button" id="hide-def">Hide DEF</a>
                                        &nbsp;|&nbsp;
                                        <a type="button" id="hide-k">Hide K</a>

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
                                            <th>GP</th>
                                            <th>Pass Att</th>
                                            <th>Pass Comp</th>
                                            <th>Pass Yds</th>
                                            <th>Pass TDs</th>
                                            <th>Int</th>
                                            <th>Rush Att</th>
                                            <th>Rush Yds</th>
                                            <th>Rush TDs</th>
                                            <th>Tar</th>
                                            <th>Rec</th>
                                            <th>Rec Yds</th>
                                            <th>Rec TDs</th>
                                            <th>Fum</th>
                                            <th>Pts</th>
                                            <th>Pts/Gm</th>
                                            <th>Proj Pts</th>
                                            <th></th>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $count = 0;
                                            $result = mysqli_query($conn,
                                                "SELECT * FROM preseason_rankings pr
                                                LEFT JOIN draft_selections ON pr.id = draft_selections.ranking_id
                                                LEFT JOIN player_data pd ON pd.preseason_ranking_id = pr.id AND pd.type = 'REG' AND pd.year = ($currentYear-1)
                                                ORDER BY my_rank ASC"
                                            );
                                            while ($row = mysqli_fetch_array($result)) {
                                                $count++;

                                                if ($row['ranking_id']) {
                                                    $color = 'gray';
                                                } else {
                                                    $color = $row['position'];
                                                }
                                            ?>

                                                <tr class="color-<?php echo $color; ?>">
                                                    <td><?php echo $row['my_rank']; ?></td>
                                                    <td><?php echo $row['adp']; ?></td>
                                                    <td><?php echo '<a data-toggle="modal" data-target="#player-data" onclick="showPlayerData('.(int)$row[0].')">'.$row['player'].'</a>'; ?></td>
                                                    <td><?php echo $row['position']; ?></td>
                                                    <td><?php echo $row['team']; ?></td>
                                                    <td><?php echo $row['bye']; ?></td>
                                                    <td><?php echo $row['sos']; ?></td>
                                                    <td><?php echo $row['tier']; ?></td>
                                                    <td><?php echo $row['games_played']; ?></td>
                                                    <td><?php echo $row['pass_attempts']; ?></td>
                                                    <td><?php echo $row['pass_completions']; ?></td>
                                                    <td><?php echo $row['pass_yards']; ?></td>
                                                    <td><?php echo $row['pass_touchdowns']; ?></td>
                                                    <td><?php echo $row['pass_interceptions']; ?></td>
                                                    <td><?php echo $row['rush_attempts']; ?></td>
                                                    <td><?php echo $row['rush_yards']; ?></td>
                                                    <td><?php echo $row['rush_touchdowns']; ?></td>
                                                    <td><?php echo $row['rec_targets']; ?></td>
                                                    <td><?php echo $row['rec_receptions']; ?></td>
                                                    <td><?php echo $row['rec_yards']; ?></td>
                                                    <td><?php echo $row['rec_touchdowns']; ?></td>
                                                    <td><?php echo $row['fumbles']; ?></td>
                                                    <td><?php echo $row['points']; ?></td>
                                                    <td><?php echo $row['games_played'] > 0 ? round($row['points'] / $row['games_played'], 1) : null; ?></td>
                                                    <td><?php echo $row['proj_points']; ?></td>
                                                    <td><?php echo $row['ranking_id'] ? 'true' : 'false'; ?></td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


<?php include 'footer.html'; ?>

<script type="text/javascript">

    $(document).ready(function() {

        var playersTable = $('#datatable-players').DataTable({
            "pageLength": 25,
            "order": [
                [0, "asc"]
            ],
            "columnDefs": [{
                "targets": [25],
                "visible": false
            }]
        });

        $('#hide-selected').click(function () {
            playersTable.columns([25]).search('false').draw();
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

    });
</script>

<style>
    .app-content.container-fluid {
        background: white;
        direction: ltr;
    }

    .taken {
        background-color: #fa887f;
    }

    .mine {
        background-color: #8cfa84;
    }

    .color-gray td, .color-gray {
        background-color: lightgray;
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

</style>