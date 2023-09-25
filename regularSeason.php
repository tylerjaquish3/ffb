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
                <div class="col-sm-12 col-lg-7 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">Regular Season Matchups</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <table class="table table-responsive" id="datatable-regSeason">
                                <thead>
                                    <th>Year</th>
                                    <th>Week</th>
                                    <th>Manager 1</th>
                                    <th>Manager 2</th>
                                    <th>Score 1</th>
                                    <th>Note</th>
                                    <th>Score 2</th>
                                    <th>Note</th>
                                    <th>Search 1</th>
                                    <th>Search 2</th>
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
                                            <td><?php echo $matchup['score1']; ?></td>
                                            <td style="font-size: 11px;"><?php echo $matchup['score1note']; ?></td>
                                            <td><?php echo $matchup['score2']; ?></td>
                                            <td style="font-size: 11px;"><?php echo $matchup['score2note']; ?></td>
                                            <td><?php echo $matchup['score1noteSearch']; ?></td>
                                            <td><?php echo $matchup['score2noteSearch']; ?></td>
                                        </tr>

                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12 col-lg-5 table-padding">
                    <div class="card-header" style="float: left">
                        <h4>Regular Season</h4>
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

            <div class="row">
                <div class="col-sm-12 table-padding">
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
                <div class="col-sm-12 table-padding">
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
                <div class="col-sm-12 table-padding">
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

            <div class="row" style="direction: ltr;">
                <div class="col-sm-12 col-md-6 col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">Team Standings Lookup</h4>
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
                            <button class="btn btn-secondary" id="lookup-btn">Search</button>
                            <br /><br />
                        </div>
                    </div>
                </div>

                <div class="col-sm-12 col-md-6 col-lg-4 table-padding">
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
            </div>

            <div class="row" style="direction: ltr;">
                <div class="col-sm-12 col-md-6 col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">League Standings History</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr; text-align: center;">
                            <h3>
                                Year
                                <select id="year-select1">
                                    <?php
                                    $result = query("SELECT distinct year FROM regular_season_matchups order by year desc");
                                    while ($row = fetch_array($result)) {
                                        echo '<option value="'.$row['year'].'">'.$row['year'].'</option>';
                                    }
                                    ?>
                                </select>
                                Week
                                <select id="week-select">
                                    <?php
                                    $result = query("SELECT distinct week_number FROM regular_season_matchups");
                                    while ($row = fetch_array($result)) {
                                        echo '<option value="'.$row['week_number'].'">'.$row['week_number'].'</option>';
                                    }
                                    ?>
                                </select><br>
                            </h3>
                            
                            <button class="btn btn-secondary" id="lookup-standings-btn">Search</button>
                            <br /><br />
                        </div>
                    </div>
                </div>

                <div class="col-sm-12 col-md-6 col-lg-4 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4>Results</h4>
                            <span id="count"></span>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <table class="table table-responsive" id="datatable-league-standings">
                                <thead>
                                    <th>Rank</th>
                                    <th>Manager</th>
                                    <th>Record</th>
                                    <th>Points</th>
                                </thead>
                                <tbody id="postData-standings"></tbody>
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

        $('#datatable-regSeason').DataTable({
            "pageLength": 25,
            "order": [
                [0, "desc"],
                [1, "desc"]
            ],
            "columnDefs": [{
                "targets": [8,9],
                "visible": false,
            }],
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
        let colors = ["#4f267f","#a6c6fa","#3cf06e","#f33c47","#c0f6e6","#def89f","#dca130","#ff7f2c","#ecb2b6"," #f87598"];
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

        $('#lookup-standings-btn').click(function () {

            year = $('#year-select1').val();
            week = $('#week-select').val();

            $.ajax({
                url : 'dataLookup.php',
                method: 'POST',
                dataType: 'text',
                data: {
                    dataType: "league-standings",
                    year: year,
                    week: week
                },
                cache: false,
                success: function(response) {
                    let data = JSON.parse(response);
                    $("#postData-standings").html(data.return);
                }
            });
        });

        $('#datatable-league-standings').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [
                [0, "desc"],
                [1, "desc"],
            ]
        });

        $('#regMiscStats').change(function() {
            showRegTable($('#regMiscStats').val());
        });

        function showRegTable(tableId) {
            for (i = 1; i < 14; i++) {
                $('#datatable-misc' + i).hide();
            }
            $('#datatable-misc' + tableId).show();
        }

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

    });
</script>