<?php

$pageName = $_GET['id'] . "'s Profile";
include 'header.php';
include 'sidebar.html';

$result = mysqli_query($conn, "SELECT * FROM managers WHERE name = '" . $_GET['id'] . "'");
while ($row = mysqli_fetch_array($result)) {
    $managerId = $row['id'];
}

?>

<div class="app-content content container-fluid">
    <div class="content-wrapper">
        <div class="content-header row"></div>

        <div class="content-body">
            <!-- Statistics -->
            <div class="row">
                <div class="col-xl-3 col-lg-6 col-xs-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="media">
                                <div class="p-2 text-xs-center bg-green media-left media-middle">
                                    <i class="icon-star-full font-large-2 white"></i>
                                </div>
                                <div class="p-2 bg-green white media-body">
                                    <h5>Total Points</h5>
                                    <h5 class="text-bold-400"><?php echo $profileNumbers['totalPoints'] . ' (Rank: ' . $profileNumbers['totalPointsRank'] . ')'; ?>&#x200E;</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-xs-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="media">
                                <div class="p-2 text-xs-center bg-green media-left media-middle">
                                    <i class="icon-stats-bars font-large-2 white"></i>
                                </div>
                                <div class="p-2 bg-green white media-body">
                                    <h5>Postseason Record</h5>
                                    <h5 class="text-bold-400"><?php echo $profileNumbers['playoffRecord'] . ' (Rank: ' . $profileNumbers['playoffRecordRank'] . ')'; ?>&#x200E;</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-xs-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="media">
                                <div class="p-2 text-xs-center bg-green media-left media-middle">
                                    <i class="icon-trophy font-large-2 white"></i>
                                </div>
                                <div class="p-2 bg-green white media-body">
                                    <h5>Championships</h5>
                                    <h5 class="text-bold-400"><?php echo $profileNumbers['championships'] . ' (' . $profileNumbers['championshipYears'] . ')'; ?>&#x200E;</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-xs-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="media">
                                <div class="p-2 text-xs-center bg-green media-left media-middle">
                                    <i class="icon-calendar font-large-2 white"></i>
                                </div>
                                <div class="p-2 bg-green white media-body">
                                    <h5>Reg. Season Record</h5>
                                    <h5 class="text-bold-400"><?php echo $profileNumbers['record'] . ' (Rank: ' . $profileNumbers['recordRank'] . ')'; ?>&#x200E;</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xl-4 col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="position-relative">
                                <div class="card-header">
                                    <h3>Record vs. Opponent</h3>
                                </div>
                                <select id="oppRecordSelector" class="dropdown">
                                    <option value="reg">Regular Season</option>
                                    <option value="post">Postseason</option>
                                </select>
                                <table class="table table-responsive" id="datatable-regSeason">
                                    <thead>
                                        <th>Manager</th>
                                        <th>Wins</th>
                                        <th>Losses</th>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $result = mysqli_query(
                                            $conn,
                                            "SELECT name, SUM(CASE
                                                WHEN manager1_score > manager2_score THEN 1
                                                ELSE 0
                                            END) AS wins,
                                            SUM(CASE
                                                WHEN manager1_score < manager2_score THEN 1
                                                ELSE 0
                                            END) AS losses
                                            FROM regular_season_matchups rsm
                                            JOIN managers ON managers.id = rsm.manager2_id
                                            WHERE manager1_id = $managerId
                                            GROUP BY manager2_id
                                            ORDER BY wins DESC"
                                        );
                                        while ($row = mysqli_fetch_array($result)) { ?>
                                            <tr>
                                                <td><?php echo $row['name']; ?></td>
                                                <td><?php echo $row['wins']; ?></td>
                                                <td><?php echo $row['losses']; ?></td>
                                            </tr>

                                        <?php } ?>
                                    </tbody>
                                </table>

                                <table class="table table-responsive" id="datatable-postseason" style="display:none;">
                                    <thead>
                                        <th>Manager</th>
                                        <th>Wins</th>
                                        <th>Losses</th>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $result = mysqli_query(
                                            $conn,
                                            "SELECT name, w.wins+w2.wins AS totalWins, l.losses+l2.losses AS totalLosses 
                                            FROM managers 
                                            JOIN (
                                                SELECT SUM(CASE
                                                WHEN manager1_id = $managerId AND manager1_score > manager2_score THEN 1
                                                ELSE 0
                                                END) AS wins, manager2_id 
                                                FROM playoff_matchups rsm 
                                                GROUP BY manager2_id
                                            ) w ON w.manager2_id = managers.id

                                            JOIN (
                                                SELECT SUM(CASE
                                                WHEN manager2_id = $managerId AND manager2_score > manager1_score THEN 1
                                                ELSE 0
                                                END) AS wins, manager1_id 
                                                FROM playoff_matchups rsm 
                                                GROUP BY manager1_id
                                            ) w2 ON w2.manager1_id = managers.id

                                            JOIN (
                                                SELECT SUM(CASE
                                                WHEN manager1_id = $managerId AND manager1_score < manager2_score THEN 1
                                                ELSE 0
                                                END) AS losses, manager2_id 
                                                FROM playoff_matchups rsm 
                                                GROUP BY manager2_id
                                            ) l ON l.manager2_id = managers.id

                                            JOIN (
                                                SELECT SUM(CASE
                                                WHEN manager2_id = $managerId AND manager2_score < manager1_score THEN 1
                                                ELSE 0
                                                END) AS losses, manager1_id 
                                                FROM playoff_matchups rsm 
                                                GROUP BY manager1_id
                                            ) l2 ON l2.manager1_id = managers.id
                                            WHERE name != '" . $_GET['id'] . "'"
                                        );
                                        while ($row = mysqli_fetch_array($result)) { ?>
                                            <tr>
                                                <td><?php echo $row['name']; ?></td>
                                                <td><?php echo $row['totalWins']; ?></td>
                                                <td><?php echo $row['totalLosses']; ?></td>
                                            </tr>

                                        <?php } ?>
                                    </tbody>
                                </table>
                                <br /><br />
                                <table class="table table-responsive" id="datatable-teamNames">
                                    <thead>
                                        <th>Year</th>
                                        <th>Team Name</th>
                                        <th>Moves</th>
                                        <th>Trades</th>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $result = mysqli_query(
                                            $conn,
                                            "SELECT name, moves, trades, year
                                            FROM team_names 
                                            WHERE manager_id = $managerId"
                                        );
                                        while ($array = mysqli_fetch_array($result)) { ?>
                                            <tr>
                                                <td><?php echo $array['year']; ?></td>
                                                <td><?php echo $array['name']; ?></td>
                                                <td><?php echo $array['moves']; ?></td>
                                                <td><?php echo $array['trades']; ?></td>
                                            </tr>

                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-8 col-lg-12 table-padding">
                    <div class="card">
                        <div class="card-body">
                            <div class="card-block">
                                <canvas id="finishesChart" class="height-400"></canvas>
                                <br />
                                <table class="table table-responsive" id="datatable-seasons">
                                    <thead>
                                        <th>Year</th>
                                        <th>Record</th>
                                        <th>Win %</th>
                                        <th>PF</th>
                                        <th>PA</th>
                                        <th>Finish</th>
                                    </thead>
                                    <tbody>
                                        <?php
                                        foreach ($seasonNumbers as $year => $array) { ?>
                                            <tr>
                                                <td><?php echo $year; ?></td>
                                                <td><?php echo $array['record']; ?></td>
                                                <td><?php echo $array['win_pct'] . ' %'; ?></td>
                                                <td><?php echo $array['pf']; ?></td>
                                                <td><?php echo $array['pa']; ?></td>
                                                <td><?php echo $array['finish']; ?></td>
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
                <div class="col-xs-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Drafts</h4>
                        </div>
                        <div class="card-body">
                            <div class="card-block">
                                <table class="table table-responsive" id="datatable-drafts">
                                    <thead>
                                        <th>Year</th>
                                        <th>Pick #</th>
                                        <th>1st Pick</th>
                                        <th>2nd Pick</th>
                                        <th>3rd Pick</th>
                                        <th>4th Pick</th>
                                        <th>5th Pick</th>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $result = mysqli_query(
                                            $conn,
                                            "SELECT d.year, 
                                            (SELECT round_pick FROM draft WHERE manager_id = $managerId AND round = 1 AND year = d.year) as position,
                                            (SELECT CONCAT(player, ' - ', position) FROM draft WHERE manager_id = $managerId AND round = 1 AND year = d.year) as r1_pick,
                                            (SELECT CONCAT(player, ' - ', position) FROM draft WHERE manager_id = $managerId AND round = 2 AND year = d.year) as r2_pick,
                                            (SELECT CONCAT(player, ' - ', position) FROM draft WHERE manager_id = $managerId AND round = 3 AND year = d.year) as r3_pick,
                                            (SELECT CONCAT(player, ' - ', position) FROM draft WHERE manager_id = $managerId AND round = 4 AND year = d.year) as r4_pick,
                                            (SELECT CONCAT(player, ' - ', position) FROM draft WHERE manager_id = $managerId AND round = 5 AND year = d.year) as r5_pick
                                            FROM draft d
                                            WHERE manager_id = $managerId AND round = 1"
                                        );
                                        while ($array = mysqli_fetch_array($result)) { ?>
                                            <tr>
                                                <td><?php echo $array['year']; ?></td>
                                                <td><?php echo $array['position']; ?></td>
                                                <td><?php echo $array['r1_pick']; ?></td>
                                                <td><?php echo $array['r2_pick']; ?></td>
                                                <td><?php echo $array['r3_pick']; ?></td>
                                                <td><?php echo $array['r4_pick']; ?></td>
                                                <td><?php echo $array['r5_pick']; ?></td>
                                            </tr>

                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">Con Categories</h4>
                            <h4 style="float: left">Pro Categories</h4>
                        </div>
                        <div class="card-body">

                        </div>
                    </div>
                </div>
            </div> -->
        </div>
    </div>
</div>

<?php include 'footer.html'; ?>

<script type="text/javascript">
    $(document).ready(function() {

        $('#oppRecordSelector').change(function() {
            if ($('#oppRecordSelector').val() == 'reg') {
                $('#datatable-regSeason').show();
                $('#datatable-postseason').hide();
            } else {
                $('#datatable-regSeason').hide();
                $('#datatable-postseason').show();
            }
        });

        $('#datatable-regSeason').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [
                [1, "desc"]
            ]
        });

        $('#datatable-postseason').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [
                [1, "desc"]
            ]
        });

        $('#datatable-seasons').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [
                [0, "asc"]
            ]
        });

        $('#datatable-teamNames').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [
                [0, "asc"]
            ]
        });

        $('#datatable-drafts').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [
                [0, "asc"]
            ]
        });

        var ctx = $('#finishesChart');

        var years = <?php echo json_encode($finishesChart['years']); ?>;
        var yearLabels = years.split(",");
        var finishes = <?php echo json_encode($finishesChart['finishes']); ?>;
        var finishData = finishes.split(",");

        var line = new Chart(ctx, {
            type: 'line',
            data: {
                labels: yearLabels,
                datasets: [{
                    label: 'Finish',
                    data: finishData,
                    borderColor: '#2eff37',
                }]
            },
            options: {
                scales: {
                    yAxes: [{
                        ticks: {
                            reverse: true,
                        }
                    }]
                }
            }
        });

    });
</script>