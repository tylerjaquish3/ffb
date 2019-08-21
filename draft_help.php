<!DOCTYPE html>
<html lang="en" data-textdirection="rtl" class="loading">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">

    <title>Suntown FFB</title>

    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-touch-fullscreen" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <!-- BEGIN VENDOR CSS-->
    <link rel="stylesheet" type="text/css" href="assets/bootstrap.min.css">
    <!-- font icons-->
    <link rel="stylesheet" type="text/css" href="assets/icomoon.css">
    <!-- END VENDOR CSS-->
    <!-- BEGIN ROBUST CSS-->
    <link rel="stylesheet" type="text/css" href="assets/bootstrap-extended.min.css">
    <link rel="stylesheet" type="text/css" href="assets/app.min.css">

    <!-- END Page Level CSS-->
    <!-- END Custom CSS-->
    <link href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="assets/suntown.css">
    <link rel="stylesheet" type="text/css" href="assets/responsive.css">

</head>

<body data-open="click" data-menu="vertical-menu" data-col="2-columns" class="vertical-layout vertical-menu 2-columns fixed-navbar">

    <?php
    $pageName = "Draft Helper";
    ?>

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
                    <h2><?php echo $pageName ?></h2>
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
                                            "SELECT count(id) as picks FROM 2019_selections"
                                        );
                                        while ($row = mysqli_fetch_array($result)) { 
                                            $currentPick = $row['picks'] + 1;
                                        }?>
                                    <div class="card-header">
                                        <h3>
                                            Players
                                            - Current Pick: <?php echo $currentPick; ?>
                                        </h3>
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
                                            <th></th>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $result = mysqli_query(
                                                $conn,
                                                "SELECT * FROM 2019_rankings
                                                LEFT JOIN 2019_selections ON 2019_rankings.id = 2019_selections.ranking_id
                                                WHERE ranking_id IS NULL 
                                                ORDER BY my_rank ASC"
                                            );
                                            while ($row = mysqli_fetch_array($result)) { ?>
                                                <tr class="color-<?php echo $row['position']; ?>">
                                                    <td><?php echo $row['my_rank']; ?></td>
                                                    <td><?php echo $row['adp']; ?></td>
                                                    <td><?php echo $row['player']; ?></td>
                                                    <td><?php echo $row['position']; ?></td>
                                                    <td><?php echo $row['team']; ?></td>
                                                    <td><?php echo $row['bye']; ?></td>
                                                    <td><?php echo $row['sos']; ?></td>
                                                    <td><a class="btn btn-secondary taken">Taken</a><a class="btn btn-secondary mine">Mine!</a></td>
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
                                        </thead>
                                        <tbody>
                                            <?php
                                            $result = mysqli_query(
                                                $conn,
                                                "SELECT * FROM 2019_selections 
                                                JOIN 2019_rankings ON 2019_rankings.id = 2019_selections.ranking_id
                                                WHERE is_mine = 1 ORDER BY my_rank ASC"
                                            );
                                            while ($row = mysqli_fetch_array($result)) { ?>
                                                <tr class="color-<?php echo $row['position']; ?>">
                                                    <td><?php echo $row['position']; ?></td>
                                                    <td><?php echo $row['player']; ?></td>
                                                    <td><?php echo $row['bye']; ?></td>
                                                </tr>

                                            <?php } ?>
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
                                        <h3>Top 3 by Position</h3>
                                    </div>
                                    
                                    <table class="table table-responsive" id="datatable-top3">
                                        <thead>
                                            <th>My Rank</th>
                                            <th>ADP</th>
                                            <th>Player</th>
                                            <th>Pos</th>
                                            <th>Team</th>
                                            <th>Bye</th>
                                            <th>SoS</th>
                                            <th></th>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $result = mysqli_query(
                                                $conn,
                                                "(
                                                select *
                                                from 2019_rankings 
                                                LEFT JOIN 2019_selections ON 2019_rankings.id = 2019_selections.ranking_id
                                                where position = 'QB' AND ranking_id IS NULL 
                                                order by my_rank asc
                                                LIMIT 3
                                                )
                                                UNION ALL
                                                (
                                                select *
                                                from 2019_rankings 
                                                LEFT JOIN 2019_selections ON 2019_rankings.id = 2019_selections.ranking_id
                                                where position = 'RB' AND ranking_id IS NULL 
                                                order by my_rank asc
                                                LIMIT 3
                                                )
                                                UNION ALL
                                                (
                                                select *
                                                from 2019_rankings 
                                                LEFT JOIN 2019_selections ON 2019_rankings.id = 2019_selections.ranking_id
                                                where position = 'WR' AND ranking_id IS NULL 
                                                order by my_rank asc
                                                LIMIT 3
                                                )
                                                UNION ALL
                                                (
                                                select *
                                                from 2019_rankings 
                                                LEFT JOIN 2019_selections ON 2019_rankings.id = 2019_selections.ranking_id
                                                where position = 'TE' AND ranking_id IS NULL 
                                                order by my_rank asc
                                                LIMIT 3
                                                )
                                                UNION ALL
                                                (
                                                select *
                                                from 2019_rankings 
                                                LEFT JOIN 2019_selections ON 2019_rankings.id = 2019_selections.ranking_id
                                                where position = 'DEF' AND ranking_id IS NULL 
                                                order by my_rank asc
                                                LIMIT 3
                                                )
                                                UNION ALL
                                                (
                                                select *
                                                from 2019_rankings 
                                                LEFT JOIN 2019_selections ON 2019_rankings.id = 2019_selections.ranking_id
                                                where position = 'K' AND ranking_id IS NULL 
                                                order by my_rank asc
                                                LIMIT 3
                                                )
                                                UNION ALL
                                                (
                                                select *
                                                from 2019_rankings 
                                                LEFT JOIN 2019_selections ON 2019_rankings.id = 2019_selections.ranking_id
                                                where position = 'IDP' AND ranking_id IS NULL 
                                                order by my_rank asc
                                                LIMIT 3
                                                )"
                                            );
                                            while ($row = mysqli_fetch_array($result)) { ?>
                                                <tr class="color-<?php echo $row['position']; ?>">
                                                    <td><?php echo $row['my_rank']; ?></td>
                                                    <td><?php echo $row['adp']; ?></td>
                                                    <td><?php echo $row['player']; ?></td>
                                                    <td><?php echo $row['position']; ?></td>
                                                    <td><?php echo $row['team']; ?></td>
                                                    <td><?php echo $row['bye']; ?></td>
                                                    <td><?php echo $row['sos']; ?></td>
                                                    <td><a class="btn btn-secondary taken">Taken</a><a class="btn btn-secondary mine">Mine!</a></td>
                                                </tr>

                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php 
                    $turnPlayers = [
                        'Ben' => ['QB'=>0,'RB'=>0,'WR'=>0,'TE'=>0,'K'=>0,'DEF'=>0,'IDP'=>0],
                        'Cam' => ['QB'=>0,'RB'=>0,'WR'=>0,'TE'=>0,'K'=>0,'DEF'=>0,'IDP'=>0],
                        'Justin' => ['QB'=>0,'RB'=>0,'WR'=>0,'TE'=>0,'K'=>0,'DEF'=>0,'IDP'=>0]
                    ];
                    $result = mysqli_query($conn, "select position, count(2019_rankings.id) as spots from 2019_selections
                        join 2019_rankings on ranking_id = 2019_rankings.id
                        where pick_number in (8,13,28,33,48,53,68,73,88,93,108,113,128,133,148,153,168,173,188,193,208,213)
                        group by position");
                    while ($row = mysqli_fetch_array($result)) {
                        $turnPlayers['Ben'][$row['position']] = $row['spots'];
                    }
                    $result = mysqli_query($conn, "select position, count(2019_rankings.id) as spots from 2019_selections
                        join 2019_rankings on ranking_id = 2019_rankings.id
                        where pick_number in (9,12,29,32,49,52,69,72,89,92,109,112,129,132,149,152,169,172,189,192,209, 212)
                        group by position");
                    while ($row = mysqli_fetch_array($result)) {
                        $turnPlayers['Cam'][$row['position']] = $row['spots'];
                    }
                    $result = mysqli_query($conn, "select position, count(2019_rankings.id) as spots from 2019_selections
                        join 2019_rankings on ranking_id = 2019_rankings.id
                        where pick_number in (10,11,30,31,50,51,70,71,90,91,110,111,130,131,150,151,170,171,190,191,210,211)
                        group by position");
                    while ($row = mysqli_fetch_array($result)) {
                        $turnPlayers['Justin'][$row['position']] = $row['spots'];
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
                                            <th>Ben</th>
                                            <th>Cam</th>
                                            <th>Justin</th>
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
                                            <tr><td>IDP</td>
                                            <?php 
                                            foreach ($turnPlayers as $player) {
                                                echo '<td>'.$player['IDP'].'</td>';
                                            } 
                                            ?>
                                            </tr>
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

        var top3Table = $('#datatable-top3').DataTable({
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

        $('#datatable-players').on('click', 'tbody .taken', function () {
            var data_row = playersTable.row($(this).closest('tr')).data();
            data_row.push('taken');
            console.log(data_row);
            var formData = {data: data_row};

            saveSelection(formData);
        });

        $('#datatable-players').on('click', 'tbody .mine', function () {
            var data_row = playersTable.row($(this).closest('tr')).data();
            data_row.push('mine');
            console.log(data_row);
            var formData = {data: data_row};

            saveSelection(formData);
        });

        $('#datatable-top3').on('click', 'tbody .taken', function () {
            var data_row = top3Table.row($(this).closest('tr')).data();
            data_row.push('taken');
            console.log(data_row);
            var formData = {data: data_row};

            saveSelection(formData);
        });

        $('#datatable-top3').on('click', 'tbody .mine', function () {
            var data_row = top3Table.row($(this).closest('tr')).data();
            data_row.push('mine');
            console.log(data_row);
            var formData = {data: data_row};

            saveSelection(formData);
        });

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

    .color-QB td {
        background-color: aquamarine;
    }

    .color-RB td {
        background-color:burlywood;
    }

    .color-WR td {
        background-color: #fa9cff;
    }

    .color-TE td {
        background-color: #69cfff;
    }

    .color-DEF td {
        background-color: #dffcde;
    }

    .color-K td {
        background-color: #f7cbcc;
    }

    .color-IDP td {
        background-color: #fcf8b3;
    }


</style>