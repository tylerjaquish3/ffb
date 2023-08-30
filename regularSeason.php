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
                <div class="col-xs-12 col-lg-5 table-padding">
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
                <div class="col-xs-12 col-lg-7 table-padding">
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
            <div class="row">
                <div class="col-xs-12 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">Points For and Against</h4>
                        </div>
                        <div class="card-body">
                            <div class="card-block">
                                <canvas id="scatterChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">PF/PA vs Wins</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <div class="card-block">
                                <canvas id="pfwinsChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xs-12 col-md-6 col-lg-4 table-padding">
                <div class="card">
                    <div class="card-header">
                        <h4>Results</h4>
                        <span id="count"></span>
                    </div>
                    <div class="card-body" style="background: #fff; direction: ltr">
                        <table class="table table-responsive" id="datatable-results">
                            <thead>
                                <th>Year</th>
                                <th>Week</th>
                                <th>Record</th>
                                <th>Points</th>
                            </thead>
                            <tbody id="postData"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-xs-12 col-md-6 col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h4 style="float: right">Standings Lookup</h4>
                    </div>
                    <div class="card-body" style="background: #fff; direction: ltr; text-align: center;">
                        <h3>When was the last time ... </h3>
                        <select id="manager1-select">
                            <?php
                            $result = query("SELECT * FROM managers ORDER BY name ASC");
                            while ($row = fetch_array($result)) {
                                echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';
                            }
                            ?>
                        </select><br>
                        <h3>... was in ...</h3>
                        <select id="place1">
                            <option value="1">First</option>
                            <option value="2">Second</option>
                            <option value="3">Third</option>
                            <option value="4">Fourth</option>
                            <option value="5">Fifth</option>
                            <option value="6">Sixth</option>
                            <option value="7">Seventh</option>
                            <option value="8">Eighth</option>
                            <option value="9">Ninth</option>
                            <option value="10">Tenth</option>
                        </select><br>
                        <h3>... place?</h3>
                        <br />
                        <button id="lookup-btn">Search</button>
                        <br /><br />
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
            "pageLength": 20,
            "order": [
                [0, "desc"]
            ]
        });

        $('#datatable-pfpawins').DataTable({
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


        // Chart for scatter of weekly points
        var ctx2 = $("#scatterChart");

        var points = <?php echo json_encode($scatterChart); ?>;
        let pointColor = '#000';
        let i = 0;
        let dataset2 = [];
        for (const [key, value] of Object.entries(points)) {

            if (key.includes('Wins')) {
                pointColor = '#90BE6D';
            } else {
                pointColor = '#F3722C';
            }

            let obj = {};
            obj.label = key;
            obj.data = value;
            obj.showLine = false;
            obj.pointBackgroundColor = pointColor;
            dataset2.push(obj);
            i++;
        }

        let scatterChart = new Chart(ctx2, {
            type: 'scatter',
            data: {
                datasets: dataset2
            },
            options: {
                scales: {
                    xAxes: [{
                        type: 'linear',
                        position: 'bottom'
                    }]
                },
                scales: {
                    yAxes: [{
                        scaleLabel: {
                            display: true,
                            labelString: 'Manager Score vs. League Average',
                            fontSize: 20
                        }
                    }],
                    xAxes: [{
                        scaleLabel: {
                            display: true,
                            labelString: 'Opponent Score vs. League Average',
                            fontSize: 20
                        }
                    }]
                }
            }
        });


        // Chart for scatter of season wins and points
        let ctx3 = $("#pfwinsChart");

        let pfpawins = <?php echo json_encode($pfwins); ?>;
        let j = 0;
        let dataset3 = [];
        for (const [key, value] of Object.entries(pfpawins)) {

            if (key.includes('For')) {
                pointColor = '#90BE6D';
            } else {
                pointColor = '#F3722C';
            }
            let obj = {};
            obj.label = key;
            obj.data = value;
            obj.showLine = false;
            obj.pointBackgroundColor = pointColor;
            dataset3.push(obj);
            j++;
        }

        let scatterChart2 = new Chart(ctx3, {
            type: 'scatter',
            data: {
                datasets: dataset3
            },
            options: {
                scales: {
                    xAxes: [{
                        type: 'linear',
                        position: 'bottom'
                    }]
                },
                scales: {
                    yAxes: [{
                        scaleLabel: {
                            display: true,
                            labelString: 'Season Points',
                            fontSize: 20
                        }
                    }],
                    xAxes: [{
                        scaleLabel: {
                            display: true,
                            labelString: 'Season Wins',
                            fontSize: 20
                        }
                    }]
                }
            }
        });

        $('#lookup-btn').click(function () {

            manager1Id = $('#manager1-select').val();
            place1 = $('#place1').val();

            $.ajax({
                url : 'dataLookup.php',
                method: 'POST',
                dataType: 'text',
                data: {
                    dataType: "standings",
                    manager1: manager1Id,
                    place: place1
                },
                cache: false,
                success: function(response) {
                    let data = JSON.parse(response);
                    $("#postData").html(data.return);
                    $("#count").html('Count: '+data.count);
                }
            });
        });

        $('#datatable-results').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [
                [0, "desc"],
                [1, "desc"],
            ]
        });

    });
</script>