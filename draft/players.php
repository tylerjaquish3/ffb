<?php
    $pageName = "Players";
    include 'header.php';
?>

<body>

    <?php
    $currentYear = date('Y');
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
                                        <span id="filter-btns">
                                            <a type="button">QB</a>
                                            &nbsp;|&nbsp;
                                            <a type="button">RB</a>
                                            &nbsp;|&nbsp;
                                            <a type="button">WR</a>
                                            &nbsp;|&nbsp;
                                            <a type="button">TE</a>
                                            &nbsp;|&nbsp;
                                            <a type="button">W/R/T</a>
                                            &nbsp;|&nbsp;
                                            <a type="button">DEF</a>
                                            &nbsp;|&nbsp;
                                            <a type="button">K</a>
                                        </span>
                                        &nbsp;|&nbsp;
                                        <a type="button" id="show-all">Show All</a>
                                    </div>
                                    <table class="table table-responsive" id="datatable-players">
                                        <thead>
                                            <th>My Rank</th>
                                            <th>ADP</th>
                                            <th>Player</th>
                                            <th>Team</th>
                                            <th>Bye</th>
                                            <th>SoS</th>
                                            <th>Tier</th>
                                            <th>VOLS</th>
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
                                            <th>Pts</th>
                                            <th>Pts/Gm</th>
                                            <th>Proj Pts</th>
                                            <th>Selected?</th>
                                            <th>Pos</th>
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
                                                    <td>
                                                        <?php echo '<a data-toggle="modal" data-target="#player-data" onclick="showPlayerData('.(int)$row[0].')">'.$row['player'].'</a>'; ?>
                                                        <?php echo desigIcon($row['designation'], $row['notes'] ? true : false); ?>
                                                    </td>
                                                    <td><?php echo $row['team']; ?></td>
                                                    <td><?php echo $row['bye']; ?></td>
                                                    <td><?php echo $row['sos']; ?></td>
                                                    <td><?php echo $row['tier']; ?></td>
                                                    <td><?php echo $row['vols']; ?></td>
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
                                                    <td><?php echo $row['points']; ?></td>
                                                    <td><?php echo $row['games_played'] > 0 ? round($row['points'] / $row['games_played'], 1) : null; ?></td>
                                                    <td><?php echo $row['proj_points']; ?></td>
                                                    <td><?php echo $row['ranking_id'] ? 'true' : 'false'; ?></td>
                                                    <td><?php echo $row['position']; ?></td>
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


<?php 
    include 'playerModal.php';
    include '../footer.html'; 
?>

<script type="text/javascript">

    $(document).ready(function() {

        var playersTable = $('#datatable-players').DataTable({
            "pageLength": 25,
            "order": [
                [0, "asc"]
            ],
            "columnDefs": [
                { "width": "10px", "targets": 0},
                { "width": "150px", "targets": 2},
                {"targets": [24,25],"visible": false}
            ]
        });

        $('#hide-selected').click(function () {
            playersTable.columns([24]).search('false').draw();
        });

        $('#show-all').click(function () {
            playersTable.columns('').search('').draw();
        });

        $('#filter-btns a').click(function () {
            let criteria = $(this)[0].outerText;
            if (criteria == 'W/R/T') {
                criteria = 'WR|RB|TE';
            }
            playersTable.columns([25]).search(criteria, true, true).draw();
        });

    });

</script>

<style>

    body {
        padding-top: 0;
    }

    .app-content.container-fluid {
        background: white;
        direction: ltr;
    }

    table#player-history td, th {
        padding: 10px 15px;
    }

    table.dataTable tbody th, table.dataTable tbody td {
        padding: 2px 10px;
    }

    a, a:link, a:visited {
        color: black;
        cursor: pointer;
    }

    .yep {
        color: #8cfa84 !important;
    }

</style>