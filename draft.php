<?php

$pageName = "Draft";
include 'header.php';
include 'sidebar.html';

?>

<div class="app-content content container-fluid">
    <div class="content-wrapper">
        <div class="content-header row"></div>

        <div class="content-body">
            <div class="row">
                <div class="col-sm-12 col-lg-7 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">Draft Results</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <table class="table table-responsive table-striped nowrap" id="datatable-draft">
                                <thead>
                                    <th>Year</th>
                                    <th>Round</th>
                                    <th>Round Pick</th>
                                    <th>Overall Pick</th>
                                    <th>Player</th>
                                    <th>Manager</th>
                                    <th>Position</th>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($draftResults as $draft) { ?>
                                        <tr>
                                            <td><?php echo $draft['year']; ?></td>
                                            <td><?php echo $draft['round']; ?></td>
                                            <td><?php echo $draft['round_pick']; ?></td>
                                            <td><?php echo $draft['overall_pick']; ?></td>
                                            <td><?php echo $draft['player']; ?></td>
                                            <td><?php echo $draft['name']; ?></td>
                                            <td><?php echo $draft['position']; ?></td>
                                        </tr>

                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12 col-lg-5 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">Draft Positions</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <!-- Draft position -->
                            <table class="table table-responsive table-striped nowrap" id="datatable-misc11">
                                <thead>
                                    <th>Manager</th>
                                    <th>#1 Picks</th>
                                    <th>#10 Picks</th>
                                    <th>Avg. Position</th>
                                </thead>
                                <tbody>
                                    <?php
                                    $result = query("SELECT name, IFNULL(pick1, 0) as pick1, IFNULL(pick10, 0) as pick10, adp
                                        FROM managers
                                        LEFT JOIN (
                                            SELECT COUNT(id) as pick1, manager_id FROM draft
                                            WHERE overall_pick = 1
                                        GROUP BY manager_id
                                        ) p1 ON p1.manager_id = managers.id

                                        LEFT JOIN (
                                        SELECT COUNT(id) as pick10, manager_id FROM draft
                                        WHERE overall_pick = 10
                                        GROUP BY manager_id
                                        ) p10 ON p10.manager_id = managers.id

                                        LEFT JOIN (
                                        SELECT AVG(overall_pick) as adp, manager_id FROM draft
                                        WHERE round = 1
                                        GROUP BY manager_id
                                        ) average ON average.manager_id = managers.id");
                                    while ($row = fetch_array($result)) { ?>
                                        <tr>
                                            <td><?php echo $row['name']; ?></td>
                                            <td><?php echo $row['pick1']; ?></td>
                                            <td><?php echo $row['pick10']; ?></td>
                                            <td><?php echo round($row['adp'], 1); ?></td>
                                        </tr>

                                    <?php } ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan=4>Number of times with #1 or #10 pick and average draft position</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">Draft Spots</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <canvas id="draftSpotsChart" style="height: 600px"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Draft Positions by Round</h4>
                        </div>
                        <div class="card-body">
                            <div class="card-block">
                                <canvas id="posByRoundChart" style="direction: ltr;"></canvas>
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

        $('#datatable-draft').DataTable({
            "order": [
                [0, "desc"],
                [3, "asc"]
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

        var ctx = $('#draftSpotsChart');
        var years = <?php echo json_encode($draftSpotChart['years']); ?>;
        var yearLabels = years.split(",");
        var teams = <?php echo json_encode($draftSpotChart['spot']); ?>;
        let colors = ["#4f267f","#a6c6fa","#3cf06e","#f33c47","#c0f6e6","#def89f","#dca130","#ff7f2c","#ecb2b6"," #f87598"];
        let x = 0;
        let dataset = [];
        for (const [key, value] of Object.entries(teams)) {
            let obj = {};
            obj.label = key;
            obj.data = value.split(",");
            obj.backgroundColor = 'rgba(39, 125, 161, 0.1)';
            obj.borderColor = colors[x];
            obj.fill = false;
            dataset.push(obj);
            x++;
        }

        var myBarChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: yearLabels,
                datasets: dataset
            },
            options: {
                scales: {
                    y: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Draft Position',
                            font: {
                                size: 20
                            }
                        },
                        reverse: true
                    }
                }
            }
        });

        var ctx = $('#posByRoundChart');
        var stackedBar = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($posByRoundChart['labels']); ?>,
                datasets: [{
                        label: "QB",
                        data: <?php echo json_encode($posByRoundChart['QB']); ?>,
                        backgroundColor: '#4f267f'
                    },{
                        label: "RB",
                        data: <?php echo json_encode($posByRoundChart['RB']); ?>,
                        backgroundColor: '#a6c6fa'
                    },{
                        label: "WR",
                        data: <?php echo json_encode($posByRoundChart['WR']); ?>,
                        backgroundColor: '#3cf06e'
                    },{
                        label: "TE",
                        data: <?php echo json_encode($posByRoundChart['TE']); ?>,
                        backgroundColor: '#f33c47'
                    },{
                        label: "K",
                        data: <?php echo json_encode($posByRoundChart['K']); ?>,
                        backgroundColor: '#f87598'
                    },{
                        label: "DEF",
                        data: <?php echo json_encode($posByRoundChart['DEF']); ?>,
                        backgroundColor: '#ff7f2c'
                    }
                ]
            },
            options: {
                scales: {
                    x: {
                        stacked: true,
                        display: true,
                        title: {
                            display: true,
                            text: 'Draft Round',
                            font: {
                                size: 20
                            }
                        }
                    },
                    y: {
                        stacked: true,
                        display: true,
                        title: {
                            display: true,
                            text: 'Selections',
                            font: {
                                size: 20
                            }
                        }
                    }
                },
                plugins: {
                    datalabels: {
                        formatter: function(value, context) {
                            return Math.round(value * 10) / 10;
                        },
                        align: 'center',
                        anchor: 'center',
                        color: 'white',
                        font: {
                            weight: 'bold'
                        }
                    }
                }
            },
            plugins: [ChartDataLabels]
        });

    });
</script>