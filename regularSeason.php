<?php

$pageName = "Regular Season";
include 'header.php';
include 'sidebar.html';

?>

<div class="app-content content container-fluid">
    <div class="content-wrapper">
        <div class="content-header row"></div>

        <div class="content-body">
            <div class="row">
                <div class="col-xs-12 col-md-5 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">Regular Season Matchups</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <table class="table table-responsive" id="datatable-regSeason">
                                <thead>
                                    <th>Year</th>
                                    <th>Week</th>
                                    <th>Manager</th>
                                    <th>Opponent</th>
                                    <th>Score</th>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($regSeasonMatchups as $matchup) { ?>
                                        <tr>
                                            <td><?php echo $matchup['year']; ?></td>
                                            <td><?php echo $matchup['week']; ?></td>

                                            <?php if ($matchup['winner'] == 'm1') {
                                                echo '<td><span class="badge badge-primary">' . $matchup['manager1'] . '</span></td>';
                                            } else {
                                                echo '<td><span class="badge badge-secondary">' . $matchup['manager1'] . '</span></td>';
                                            }
                                            if ($matchup['winner'] == 'm2') {
                                                echo '<td><span class="badge badge-primary">' . $matchup['manager2'] . '</span></td>';
                                            } else {
                                                echo '<td><span class="badge badge-secondary">' . $matchup['manager2'] . '</span></td>';
                                            } ?>
                                            <td><?php echo $matchup['score']; ?></td>
                                        </tr>

                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-md-7 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">Wins By Season</h4>
                        </div>
                        <div class="card-body">
                            <div class="card-block">
                                <canvas id="winsChart" class="height-400"></canvas>
                                <br />
                                <table class="table table-responsive" id="datatable-wins">
                                    <thead>
                                        <th>Year</th>
                                        <th>Ben</th>
                                        <th>Justin</th>
                                        <th>Gavin</th>
                                        <th>Matt</th>
                                        <th>AJ</th>
                                        <th>Andy</th>
                                        <th>Cameron</th>
                                        <th>Tyler</th>
                                        <th>Everett</th>
                                        <th>Cole</th>
                                    </thead>
                                    <tbody>
                                        <?php
                                        foreach ($seasonWins as $year => $array) { ?>
                                            <tr>
                                                <td><?php echo $year; ?></td>
                                                <td><?php echo $array['ben']; ?></td>
                                                <td><?php echo $array['justin']; ?></td>
                                                <td><?php echo $array['gavin']; ?></td>
                                                <td><?php echo $array['matt']; ?></td>
                                                <td><?php echo $array['aj']; ?></td>
                                                <td><?php echo isset($array['andy']) ? $array['andy'] : 'N/A'; ?></td>
                                                <td><?php echo isset($array['cameron']) ? $array['cameron'] : 'N/A'; ?></td>
                                                <td><?php echo $array['tyler']; ?></td>
                                                <td><?php echo $array['everett']; ?></td>
                                                <td><?php echo $array['cole']; ?></td>
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

        $('#datatable-regSeason').DataTable({
            "order": [
                [0, "desc"]
            ]
        });

        $('#datatable-wins').DataTable({
            "paging": false,
            "searching": false,
            "info": false,
            "order": [
                [0, "desc"]
            ]
        });

        var ctx = $("#winsChart");

        var years = <?php echo json_encode($winsChart['years']); ?>;
        var yearLabels = years.split(",");
        var teams = <?php echo json_encode($winsChart['wins']); ?>;
        let colors = ['#F94144', '#F3722C', '#F8961E', '#F9844A', '#F9C74F', '#90BE6D', '#43AA8B', '#4D908E', '#577590', '#277DA1'];
        let x = 0;
        let dataset = [];
        for (const [key, value] of Object.entries(teams)) {
            let obj = {};
            obj.label = key;
            obj.data = value.split(",");
            obj.backgroundColor = 'rgba(39, 125, 161, 0.1)';
            obj.borderColor = colors[x];
            dataset.push(obj);
            x++;
        }

        var data = {
            labels: yearLabels,
            datasets: dataset
        };

        var options = {
            scales: {
                yAxes: [{
                    scaleLabel: {
                        display: true,
                        labelString: 'Wins',
                        fontSize: 20
                    }
                }]
            }
        };

        var myBarChart = new Chart(ctx, {
            type: 'line',
            data: data,
            options: options,

        });

    });
</script>