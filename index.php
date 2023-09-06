<?php
$pageName = 'Dashboard';
include 'header.php';
include 'sidebar.html';

?>

<div class="app-content content container-fluid">
    <div class="content-wrapper">
        <div class="content-header row"></div>

        <div class="content-body">
            <!-- Statistics -->
            <div class="row">
                <div class="col-xl-3 col-lg-6 col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="media">
                                <div class="p-2 text-xs-center bg-green-ffb media-left media-middle">
                                    <i class="icon-star-full font-large-2 white"></i>
                                </div>
                                <div class="p-2 bg-green-ffb media-body">
                                    <h5>Most Wins</h5>
                                    <h5 class="text-bold-400"><?php echo $dashboardNumbers['most_wins_manager'] . ' (' . $dashboardNumbers['most_wins_number'] . ')'; ?>&#x200E;</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="media">
                                <div class="p-2 text-xs-center bg-green-ffb media-left media-middle">
                                    <i class="icon-trophy font-large-2 white"></i>
                                </div>
                                <div class="p-2 bg-green-ffb media-body">
                                    <h5>Most Championships</h5>
                                    <h5 class="text-bold-400"><?php echo $dashboardNumbers['most_championships_manager'] . ' (' . $dashboardNumbers['most_championships_number'] . ')'; ?>&#x200E;</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="media">
                                <div class="p-2 text-xs-center bg-green-ffb media-left media-middle">
                                    <i class="icon-user font-large-2 white"></i>
                                </div>
                                <div class="p-2 bg-green-ffb media-body">
                                    <h5>Defending Champion</h5>
                                    <h5 class="text-bold-400"><?php echo $dashboardNumbers['defending_champ']; ?></h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="media">
                                <div class="p-2 text-xs-center bg-green-ffb media-left media-middle">
                                    <i class="icon-calendar font-large-2 white"></i>
                                </div>
                                <div class="p-2 bg-green-ffb media-body">
                                    <h5>Seasons</h5>
                                    <h5 class="text-bold-400"><?php echo $dashboardNumbers['seasons']; ?></h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title"><a href="trophy.php">Trophy</a></h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr;">
                            <div class="row">
                                <div class="col-sm-1"></div>
                                <div class="col-sm-10">
                                    <div class="row" style="direction: ltr;">
                                        <div class="col-sm-4"><div class="plaque"><a href="seasonRecaps.php?id=2008">2008 CHAMPION<br />TYLER JAQUISH</a></div></div>
                                        <div class="col-sm-4"><div class="plaque"><a href="seasonRecaps.php?id=2007">2007 CHAMPION<br />JUSTIN DIDIER</a></div></div>
                                        <div class="col-sm-4"><div class="plaque"><a href="seasonRecaps.php?id=2006">2006 CHAMPION<br />AJ SARTIN</a></div></div>
                                        <div class="col-sm-4"><div class="plaque"><a href="seasonRecaps.php?id=2011">2011 CHAMPION<br />BEN BARDELL</a></div></div>
                                        <div class="col-sm-4"><div class="plaque"><a href="seasonRecaps.php?id=2010">2010 CHAMPION<br />CAMERON BOBOTH</a></div></div>
                                        <div class="col-sm-4"><div class="plaque"><a href="seasonRecaps.php?id=2009">2009 CHAMPION<br />MATT REID</a></div></div>
                                        <div class="col-sm-4"><div class="plaque"><a href="seasonRecaps.php?id=2014">2014 CHAMPION<br />JUSTIN DIDIER</a></div></div>
                                        <div class="col-sm-4"><div class="plaque"><a href="seasonRecaps.php?id=2013">2013 CHAMPION<br />ANDY STAMSCHROR</a></div></div>
                                        <div class="col-sm-4"><div class="plaque"><a href="seasonRecaps.php?id=2012">2012 CHAMPION<br />AJ SARTIN</a></div></div>
                                        <div class="col-sm-4"><div class="plaque"><a href="seasonRecaps.php?id=2017">2017 CHAMPION<br />COLE BOBOTH</a></div></div>
                                        <div class="col-sm-4"><div class="plaque"><a href="seasonRecaps.php?id=2016">2016 CHAMPION<br />COLE BOBOTH</a></div></div>
                                        <div class="col-sm-4"><div class="plaque"><a href="seasonRecaps.php?id=2015">2015 CHAMPION<br />JUSTIN DIDIER</a></div></div>
                                        <div class="col-sm-4"><div class="plaque"><a href="seasonRecaps.php?id=2020">2020 CHAMPION<br />MATT REID</a></div></div>
                                        <div class="col-sm-4"><div class="plaque"><a href="seasonRecaps.php?id=2019">2019 CHAMPION<br />CAMERON BOBOTH</a></div></div>
                                        <div class="col-sm-4"><div class="plaque"><a href="seasonRecaps.php?id=2018">2018 CHAMPION<br />JUSTIN DIDIER</a></div></div>
                                        <div class="col-sm-4"><div class="plaque"><a href="currentSeason.php">2023 CHAMPION<br />TBD</a></div></div>
                                        <div class="col-sm-4"><div class="plaque"><a href="seasonRecaps.php?id=2022">2022 CHAMPION<br />JUSTIN DIDIER</a></div></div>
                                        <div class="col-sm-4"><div class="plaque"><a href="seasonRecaps.php?id=2021">2021 CHAMPION<br />JUSTIN DIDIER</a></div></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xl-4 col-sm-12 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title"><a href="regularSeason.php">Regular Season Record</a></h4>
                        </div>
                        <div class="card-body">
                            <div class="position-relative">
                                <table class="table table-responsive table-striped nowrap" id="datatable-wins">
                                    <thead>
                                        <th>Manager</th>
                                        <th>Wins</th>
                                        <th>Losses</th>
                                        <th>Win %&#x200E;</th>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $result = query(
                                            "SELECT name, wins, losses, total, 100.0*wins/total AS win_pct
                                            FROM managers
                                            JOIN (
                                                SELECT COUNT(manager1_id) AS wins, manager1_id FROM regular_season_matchups rsm
                                                WHERE manager1_score > manager2_score GROUP BY manager1_id
                                            ) w ON w.manager1_id = managers.id

                                            JOIN (
                                                SELECT COUNT(manager1_id) AS losses, manager1_id FROM regular_season_matchups rsm
                                                WHERE manager1_score < manager2_score GROUP BY manager1_id
                                            ) l ON l.manager1_id = managers.id

                                            JOIN (
                                                SELECT COUNT(manager1_id) AS total, manager1_id FROM regular_season_matchups rsm
                                                GROUP BY manager1_id
                                            ) t ON t.manager1_id = managers.id"
                                        );
                                        while ($row = fetch_array($result)) { ?>
                                            <tr>
                                                <td><?php echo $row['name']; ?></td>
                                                <td><?php echo $row['wins']; ?></td>
                                                <td><?php echo $row['losses']; ?></td>
                                                <td><?php echo number_format($row['win_pct'], 1) . ' %'; ?></td>
                                            </tr>

                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-8 col-sm-12 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title"><a href="postseason.php">Postseason</a></h4>
                        </div>
                        <div class="card-body">
                            <div class="card-block">
                                <canvas id="postseasonChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-sm-12 col-lg-6 table-padding">
                                    <div class="card-header" style="float: left">
                                        <h4><a href="postseason.php">Postseason Stats</a></h4>
                                    </div>
                                    <div style="float: right">
                                        <select id="postMiscStats" class="dropdown form-control">
                                            <option value="20">Average Finish</option>
                                            <option value="21">First Round Byes</option>
                                            <option value="22">Appearances</option>
                                            <option value="23">Underdog Wins</option>
                                            <option value="24">Top Seed Losses</option>
                                            <option value="25">Playoff Points</option>
                                            <option value="26">Win/Loss Margin</option>
                                        </select>
                                    </div>
                                    <?php include 'postMiscStats.php'; ?>
                                </div>
                                <div class="col-sm-12 col-lg-6 table-padding">
                                    <div class="card-header" style="float: left">
                                        <h4><a href="regularSeason.php">Regular Season Stats</a></h4>
                                    </div>
                                    <div style="float: right">
                                        <select id="regMiscStats" class="dropdown form-control">
                                            <option value="1">Win/Lose Streaks</option>
                                            <option value="2">Total Points</option>
                                            <option value="3">Season Points</option>
                                            <option value="4">Average PF/PA</option>
                                            <option value="5">Start Streaks</option>
                                            <option value="6">Win/Loss Margin</option>
                                            <option value="7">Weekly Points</option>
                                            <option value="8">Losses with Top 3 Pts</option>
                                            <option value="9">Wins with Bottom 3 Pts</option>
                                            <option value="10">Record Against Everyone</option>
                                            <option value="11">Draft Positions</option>
                                            <option value="12">Moves/Trades</option>
                                        </select>
                                    </div>
                                    <?php include 'regMiscStats.php'; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<script type="text/javascript">
    $(document).ready(function() {

        $('#regMiscStats').change(function() {
            showRegTable($('#regMiscStats').val());
        });

        $('#postMiscStats').change(function() {
            showPostTable($('#postMiscStats').val());
        });

        $('#datatable-wins').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [
                [3, "desc"]
            ]
        });

        // Misc tables
        $('#datatable-misc1').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [
                [1, "desc"]
            ]
        });
        $('#datatable-misc2').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [
                [1, "desc"]
            ]
        });
        $('#datatable-misc3').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [
                [1, "desc"]
            ]
        });
        $('#datatable-misc4').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [
                [3, "desc"]
            ]
        });
        $('#datatable-misc5').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [
                [1, "desc"]
            ]
        });
        $('#datatable-misc6').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [
                [3, "desc"]
            ]
        });
        $('#datatable-misc7').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [
                [1, "desc"]
            ]
        });
        $('#datatable-misc8').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [
                [3, "desc"]
            ]
        });
        $('#datatable-misc9').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [
                [3, "desc"]
            ]
        });
        $('#datatable-misc10').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [
                [3, "desc"]
            ]
        });
        $('#datatable-misc11').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [
                [3, "asc"]
            ]
        });
        $('#datatable-misc12').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [
                [3, "desc"]
            ]
        });
        $('#datatable-misc20').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [
                [1, "asc"]
            ]
        });
        $('#datatable-misc21').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [
                [3, "desc"]
            ]
        });
        $('#datatable-misc22').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [
                [1, "desc"]
            ]
        });
        $('#datatable-misc23').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [
                [4, "desc"]
            ]
        });
        $('#datatable-misc24').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [
                [4, "desc"]
            ]
        });
        $('#datatable-misc25').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [
                [3, "desc"]
            ]
        });
        $('#datatable-misc26').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [
                [1, "desc"]
            ]
        });

        Chart.defaults.global.defaultFontSize = 9;
        var ctx = $('#postseasonChart');

        var stackedBar = new Chart(ctx, {
            type: 'horizontalBar',
            data: {
                labels: <?php echo json_encode($postseasonChart['managers']); ?>,
                datasets: [{
                        label: 'Playoff Appearances',
                        data: <?php echo json_encode($postseasonChart['appearances']); ?>,
                        // backgroundColor: '#04015d'
                        backgroundColor: '#297eff'
                    },
                    {
                        label: 'Championship Appearances',
                        data: <?php echo json_encode($postseasonChart['shipAppearances']); ?>,
                    },
                    {
                        label: 'Championship Wins',
                        data: <?php echo json_encode($postseasonChart['ships']); ?>,
                        // backgroundColor: '#2eff37'
                        backgroundColor: '#2eb82e'
                    }
                ]
            },
            options: {
                scales: {
                    xAxes: [{
                        stacked: true
                    }],
                    yAxes: [{
                        stacked: true
                    }]
                }
            }
        });
    });

    function showRegTable(tableId) {
        for (i = 1; i < 14; i++) {
            $('#datatable-misc' + i).hide();
        }
        $('#datatable-misc' + tableId).show();
    }

    function showPostTable(tableId) {
        for (i = 20; i < 27; i++) {
            $('#datatable-misc' + i).hide();
        }
        $('#datatable-misc' + tableId).show();
    }
</script>