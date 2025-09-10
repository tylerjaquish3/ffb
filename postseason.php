<?php

$pageName = "Postseason";
include 'header.php';
include 'sidebar.html';

?>

<div class="app-content content">
    <div class="content-wrapper">

        <div class="content-body">

            <!-- Tabs Navigation -->
            <div class="row mb-1">
                <div class="col-sm-12">
                    <div class="tab-buttons-container">
                        <button class="tab-button active" id="overview-tab" onclick="showCard('overview')">
                            Overview
                        </button>
                        <button class="tab-button" id="matchups-tab" onclick="showCard('matchups')">
                            Matchups
                        </button>
                        <button class="tab-button" id="champion-details-tab" onclick="showCard('champion-details')">
                            Champion Details
                        </button>
                        <button class="tab-button" id="records-championships-tab" onclick="showCard('records-championships')">
                            Records
                        </button>
                    </div>
                </div>
            </div>

            <div class="row card-section" id="overview">
                <div class="col-sm-12 col-md-8 table-padding">
                    <div class="card-header" style="float: left">
                        <h4>Postseason</h4>
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
                <div class="col-sm-12 col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">Championships</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <div class="card-block" style="height: 400px; max-height:450px;">
                                <canvas id="winsChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row card-section" id="matchups">
                <div class="col-sm-12 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">Matchups</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <table class="table table-responsive table-striped nowrap full-width" id="datatable-postseason">
                                <thead>
                                    <th>Year</th>
                                    <th>Round</th>
                                    <th>Manager</th>
                                    <th>Opponent</th>
                                    <th>Score</th>
                                    <th>Margin</th>
                                    <th></th>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($postseasonMatchups as $matchup) { ?>
                                        <tr>
                                            <td><?php echo $matchup['year']; ?></td>
                                            <td><?php echo $matchup['round']; ?></td>

                                            <?php if ($matchup['winner'] == 'm1') {
                                                echo '<td><span class="badge badge-primary">' . $matchup['manager1'] . '<span class="seed">' . $matchup['m1seed'] . '</span></span></td>';
                                            } else {
                                                echo '<td><span class="badge badge-secondary">' . $matchup['manager1'] . '<span class="seed">' . $matchup['m1seed'] . '</span></span></td>';
                                            }
                                            if ($matchup['winner'] == 'm2') {
                                                echo '<td><span class="badge badge-primary">' . $matchup['manager2'] . '<span class="seed">' . $matchup['m2seed'] . '</span></span></td>';
                                            } else {
                                                echo '<td><span class="badge badge-secondary">' . $matchup['manager2'] . '<span class="seed">' . $matchup['m2seed'] . '</span></span></td>';
                                            } ?>
                                            <td><?php echo $matchup['score']; ?></td>
                                            <td><?php echo $matchup['margin']; ?></td>
                                            <td><?php echo $matchup['sort']; ?></td>
                                        </tr>

                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
            </div>

            <div class="row card-section" id="champion-details" style="display: none;">
                <div class="col-sm-12 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">Champion Details</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <table class="table table-responsive table-striped nowrap full-width" id="datatable-champions">
                                <thead>
                                    <th>Year</th>
                                    <th>Champion</th>
                                    <th>Draft Pick</th>
                                    <th>Record</th>
                                    <th>Seed</th>
                                    <th>Trades</th>
                                    <th>Top Draft Pick</th>
                                    <th>Top Add</th>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($champions as $champion) { ?>
                                        <tr>
                                            <td><?php echo $champion['year']; ?></td>
                                            <td><?php echo $champion['name']; ?></td>
                                            <td><?php echo $champion['draft_pick']; ?></td>
                                            <td><?php echo $champion['record']; ?></td>
                                            <td><?php echo $champion['seed']; ?></td>
                                            <td><?php echo $champion['trades']; ?></td>
                                            <td><?php echo $champion['top_draft_pick']; ?></td>
                                            <td><?php echo $champion['top_add']; ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row card-section" id="records-championships" style="display: none;">
                <div class="col-sm-12 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">Records</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <div class="table-container" style="overflow-x: auto;">
                                <table class="table table-striped nowrap responsive" id="datatable-records">
                                <thead>
                                    <th>Manager</th>
                                    <th>Quarter Wins</th>
                                    <th>Quarter Losses</th>
                                    <th>Semi Wins</th>
                                    <th>Semi Losses</th>
                                    <th>Final Wins</th>
                                    <th>Final Losses</th>
                                    <th>Total Wins</th>
                                    <th>Total Losses</th>
                                    <th>Overall Win %</th>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($postseasonRecord as $manager) { ?>
                                        <tr>
                                            <td><?php echo $manager['name']; ?></td>
                                            <td><?php echo $manager['quarter_wins']; ?></td>
                                            <td><?php echo $manager['quarter_losses']; ?></td>
                                            <td><?php echo $manager['semi_wins']; ?></td>
                                            <td><?php echo $manager['semi_losses']; ?></td>
                                            <td><?php echo $manager['final_wins']; ?></td>
                                            <td><?php echo $manager['final_losses']; ?></td>
                                            <td><?php echo $manager['wins']; ?></td>
                                            <td><?php echo $manager['losses']; ?></td>
                                            <td><?php echo round($manager['win_pct'], 1); ?></td>
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

<?php include 'footer.php'; ?>

<script type="text/javascript">
    $(document).ready(function() {

        $('#postMiscStats').change(function() {
            showPostTable($('#postMiscStats').val());
        });

        function showPostTable(tableId) {
            for (i = 20; i < 27; i++) {
                $('#datatable-misc' + i).hide();
            }
            $('#datatable-misc' + tableId).show();
        }

        let postseasonTable = $('#datatable-postseason').DataTable({
            columnDefs: [{
                targets: [6],
                visible: false,
                searchable: false
            }],
            search: {
                "caseInsensitive": false
            },
            order: [
                [0, "desc"],
                [6, "desc"]
            ]
        });

        $('#datatable-misc20').DataTable({
            searching: false,
            paging: false,
            info: false,
            order: [
                [1, "asc"]
            ]
        });
        $('#datatable-misc21').DataTable({
            searching: false,
            paging: false,
            info: false,
            order: [
                [3, "desc"]
            ]
        });
        $('#datatable-misc22').DataTable({
            searching: false,
            paging: false,
            info: false,
            order: [
                [1, "desc"]
            ]
        });
        $('#datatable-misc23').DataTable({
            searching: false,
            paging: false,
            info: false,
            order: [
                [4, "desc"]
            ]
        });
        $('#datatable-misc24').DataTable({
            searching: false,
            paging: false,
            info: false,
            order: [
                [4, "desc"]
            ]
        });
        $('#datatable-misc25').DataTable({
            searching: false,
            paging: false,
            info: false,
            order: [
                [3, "desc"]
            ]
        });
        $('#datatable-misc26').DataTable({
            searching: false,
            paging: false,
            info: false,
            order: [
                [1, "desc"]
            ]
        });

        $('#datatable-records').DataTable({
            searching: false,
            paging: false,
            info: false,
            scrollX: "100%",
            scrollCollapse: true,
            fixedColumns:   {
                leftColumns: 1
            },
            order: [
                [9, "desc"]
            ]
        });

        $('#datatable-champions').DataTable({
            searching: false,
            paging: false,
            info: false,
            order: [
                [0, "desc"]
            ]
        });

        var ctx = $('#winsChart');
        var managers = <?php echo json_encode($winsChart['managers']); ?>;
        var wins = <?php echo json_encode($winsChart['wins']); ?>;
        let colors = ["#9c68d9","#a6c6fa","#3cf06e","#f33c47","#c0f6e6","#def89f","#dca130","#ff7f2c","#ecb2b6"," #f87598"];
        
        let obj = {};
        obj.label = 'Wins';
        obj.data = wins;
        obj.backgroundColor = colors;
        obj.datalabels = {
            align: 'end'
        };

        var winsChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: managers,
                datasets: [obj]
            },
            options: {
                plugins: {
                    legend: {
                        display: false,
                    },
                    datalabels: {
                        formatter: function(value, context) {
                            return context.chart.data.labels[context.dataIndex]+': '+value;
                        },
                        color: 'black',
                        font: {
                            weight: 'bold'
                        }
                    }
                }
            },
            plugins: [ChartDataLabels]
        });

        // Initialize the page with Matchups & Stats tab active
        showCard('overview');
        
    });
</script>