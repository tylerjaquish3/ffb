<?php

$pageName = "Postseason";
include 'header.php';
include 'sidebar.php';

?>
<style>
#datatable-champions td, #datatable-champions th { white-space: nowrap !important; }
.h2h-table th.h2h-col-header {
    writing-mode: vertical-lr;
    transform: rotate(180deg);
    white-space: nowrap;
    vertical-align: bottom;
    padding: 4px 6px;
    font-weight: 600;
}
.h2h-table td, .h2h-table th { padding: 4px 6px; }
</style>

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
                            W/L Records
                        </button>
                    </div>
                </div>
            </div>

            <div class="row card-section" id="overview">
                <div class="col-sm-12 col-md-8 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4>Postseason</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <div class="row">
                                <div class="col-sm-12">
                                    <select id="postMiscStats" class="dropdown form-control">
                                        <option value="20">Average Finish</option>
                                        <option value="21">First Round Byes</option>
                                        <option value="22">Appearances</option>
                                        <option value="23">Underdog Wins</option>
                                        <option value="24">Top Seed Losses</option>
                                        <option value="25">Playoff Points</option>
                                        <option value="26">Win/Loss Margin</option>
                                    </select>
                                    <?php include 'postMiscStats.php'; ?>
                                </div>
                            </div>
                        </div>
                    </div>
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
                <div class="col-sm-12 col-lg-8 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">Matchups</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <table class="table table-responsive table-striped nowrap full-width" id="datatable-postseason">
                                <thead>
                                    <th>Year</th>
                                    <th>Round</th>
                                    <th>Manager 1</th>
                                    <th>Manager 2</th>
                                    <th>Score 1</th>
                                    <th>Score 2</th>
                                    <th>Margin</th>
                                    <th>Combined</th>
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
                                            <td><?php echo $matchup['score1']; ?></td>
                                            <td><?php echo $matchup['score2']; ?></td>
                                            <td><?php echo round($matchup['margin'], 2); ?></td>
                                            <td><?php echo $matchup['combined']; ?></td>
                                            <td><?php echo $matchup['sort']; ?></td>
                                        </tr>

                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12 col-lg-4 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">Head to Head Matchup Counts</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr; overflow-x: auto;">
                            <table class="table table-striped table-bordered h2h-table" style="font-size: 0.82em;">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <?php foreach ($playoffH2H['managers'] as $m): ?>
                                            <th class="text-center h2h-col-header"><?php echo $m; ?></th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($playoffH2H['managers'] as $row): ?>
                                        <tr>
                                            <td><strong><?php echo $row; ?></strong></td>
                                            <?php foreach ($playoffH2H['managers'] as $col): ?>
                                                <?php if ($row === $col): ?>
                                                    <td class="text-center text-muted">—</td>
                                                <?php else: ?>
                                                    <td class="text-center">
                                                        <?php
                                                        $count = $playoffH2H['grid'][$row][$col];
                                                        echo $count > 0 ? $count : '<span class="text-muted">0</span>';
                                                        ?>
                                                    </td>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endforeach; ?>
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
                            <div style="overflow-x: auto;">
                            <table class="table table-striped nowrap full-width" id="datatable-champions">
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
                <div class="col-sm-12 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">Championships by Draft Pick</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <div class="card-block" style="height: 300px; max-height: 320px;">
                                <canvas id="draftPickChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row card-section" id="records-championships" style="display: none;">
                <div class="col-sm-12 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">W/L Records</h4>
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
</div>

<?php include 'footer.php'; ?>

<script type="text/javascript">
    $(document).ready(function() {

        $('#postMiscStats').change(function() {
            showPostTable($('#postMiscStats').val());
        });

        let postseasonTable = $('#datatable-postseason').DataTable({
            columnDefs: [{
                targets: [8],
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
        let colors = ["#9c68d9","#a6c6fa","#3cf06e","#f33c47","#c0f6e6","#def89f","#dca130","#ff7f2c","#2dd4bf"," #f87598"];
        
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

        // Draft pick championship chart
        <?php
            $draftPickCounts = array_fill(1, 10, 0);
            $draftPickDetails = array_fill(1, 10, []);
            foreach ($champions as $champ) {
                $pick = $champ['draft_pick'];
                if (is_numeric($pick) && $pick >= 1 && $pick <= 10) {
                    $draftPickCounts[$pick]++;
                    $draftPickDetails[$pick][] = $champ['year'] . ' — ' . $champ['name'];
                }
            }
            $pickCountsOrdered = array_values($draftPickCounts);
            $pickDetailsOrdered = array_values($draftPickDetails);
        ?>
        var draftPickCtx = $('#draftPickChart');
        var draftPickCounts = <?php echo json_encode($pickCountsOrdered); ?>;
        var draftPickDetails = <?php echo json_encode($pickDetailsOrdered); ?>;
        var pickLabels = ['Pick 1','Pick 2','Pick 3','Pick 4','Pick 5','Pick 6','Pick 7','Pick 8','Pick 9','Pick 10'];
        var barColors = pickLabels.map(function(_, i) {
            return draftPickCounts[i] > 0 ? '#9c68d9' : '#e0d0f5';
        });

        new Chart(draftPickCtx, {
            type: 'bar',
            data: {
                labels: pickLabels,
                datasets: [{
                    label: 'Championships',
                    data: draftPickCounts,
                    backgroundColor: barColors,
                    datalabels: { anchor: 'end', align: 'end' }
                }]
            },
            options: {
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 },
                        title: { display: true, text: 'Championships' }
                    },
                    x: {
                        title: { display: true, text: 'Draft Pick Position' }
                    }
                },
                plugins: {
                    legend: { display: false },
                    datalabels: {
                        color: 'black',
                        font: { weight: 'bold' },
                        formatter: function(value) { return value > 0 ? value : ''; }
                    },
                    tooltip: {
                        callbacks: {
                            afterBody: function(items) {
                                var idx = items[0].dataIndex;
                                var details = draftPickDetails[idx];
                                return details.length ? details : ['No championships'];
                            }
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