<?php

$pageName = "Regular Season";
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
                        <button class="tab-button active" id="matchups-stats-tab" onclick="showCard('matchups-stats')">
                            Matchups & Stats
                        </button>
                        <button class="tab-button" id="weekly-rank-tab" onclick="showCard('weekly-rank')">
                            Weekly Rank
                        </button>
                        <button class="tab-button" id="team-standings-tab" onclick="showCard('team-standings')">
                            Team Standings Lookup
                        </button>
                        <button class="tab-button" id="league-standings-tab" onclick="showCard('league-standings')">
                            League Standings History
                        </button>
                        <button class="tab-button" id="wins-by-season-tab" onclick="showCard('wins-by-season')">
                            Wins by Season
                        </button>
                        <button class="tab-button" id="pfpa-correlation-tab" onclick="showCard('pfpa-correlation')">
                            PF/PA Charts
                        </button>
                        <button class="tab-button" id="game-time-tab" onclick="showCard('game-time')">
                            Game Time Analysis
                        </button>
                        <button class="tab-button" id="champions-tab" onclick="showCard('champions')">
                            Champions
                        </button>
                    </div>
                </div>
            </div>

            <div class="row card-section" id="matchups-stats">
                <div class="col-sm-12 col-lg-7 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">Regular Season Matchups</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <table class="table table-responsive table-striped nowrap" id="datatable-regSeason">
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
                                            <td><?php echo '<a href="/rosters.php?year='.$matchup["year"].'&week='.$matchup["week"].'&manager='.$matchup['manager1'].'">'.$matchup['score1'].'</a>'; ?></td>
                                            <td style="font-size: 11px;"><?php echo $matchup['score1note']; ?></td>
                                            <td><?php echo '<a href="/rosters.php?year='.$matchup["year"].'&week='.$matchup["week"].'&manager='.$matchup['manager2'].'">'.$matchup['score2'].'</a>'; ?></td>
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
        </div>

        <div class="row card-section" id="weekly-rank" style="display: none;">
            <div class="col-sm-12 table-padding">
                <div class="card">
                    <div class="card-header">
                        <h4>Weekly Rank by Points Scored</h4>
                    </div>
                    <div class="card-body" style="direction: ltr;">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="table-responsive">
                                    <table class="table table-striped nowrap" id="datatable-weeklyRanks" style="width: 100%;">
                                        <thead>
                                            <th></th>
                                            <th>Year</th>
                                            <th>Manager</th>
                                            <th>Avg. Weekly Rank</th>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- New Record by Week Card -->
            <div class="col-sm-12 table-padding">
                <div class="card">
                    <div class="card-header">
                        <h4>Record by Week</h4>
                    </div>
                    <div class="card-body" style="direction: ltr;">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="table-responsive">
                                    <table class="table table-striped nowrap" id="datatable-recordByWeek" style="width: 100%;">
                                        <thead>
                                            <tr>
                                                <th>Manager</th>
                                                <?php 
                                                sort($recordsByWeek['weeks']);
                                                foreach ($recordsByWeek['weeks'] as $week) {
                                                    echo '<th>Week ' . $week . '</th>';
                                                }
                                                ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            foreach ($recordsByWeek['managers'] as $manager) {
                                                echo '<tr>';
                                                echo '<td>' . $manager . '</td>';
                                                foreach ($recordsByWeek['weeks'] as $week) {
                                                    $record = isset($recordsByWeek['records'][$manager][$week]) ? $recordsByWeek['records'][$manager][$week] : '0-0';
                                                    echo '<td>' . $record . '</td>';
                                                }
                                                echo '</tr>';
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row card-section" id="team-standings" style="display: none;">
                <div class="col-sm-12 col-md-6 col-lg-4 table-padding">
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
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <table class="table table-responsive table-striped nowrap" id="datatable-results">
                                <thead>
                                    <th>Year</th>
                                    <th>Week</th>
                                    <th>Record</th>
                                    <th>Points</th>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row card-section" id="league-standings" style="display: none;">
                <div class="col-sm-12 col-md-6 col-lg-4 table-padding">
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
                                </select>
                                <br>
                            </h3>
                            
                            <button class="btn btn-secondary" id="lookup-standings-btn">Search</button> or 
                            <button class="btn btn-secondary" id="next-week-standings-btn">Next Week</button>
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
                            <table class="table table-responsive table-striped nowrap" id="datatable-league-standings">
                                <thead>
                                    <th>Rank</th>
                                    <th>Manager</th>
                                    <th>Record</th>
                                    <th>Points</th>
                                    <th>Next Week</th>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row card-section" id="wins-by-season" style="display: none;">
                <div class="col-sm-12 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">Wins By Season</h4>
                        </div>
                        <div class="card-body">
                            <div class="card-block chart-block">
                                <canvas id="winsChart"></canvas>
                            </div>
                            <div>
                                <br />
                                <table class="table table-responsive table-striped nowrap" id="datatable-wins">
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

            <div class="row card-section" id="pfpa-correlation" style="display: none;">
                <div class="col-sm-12 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">Points For and Against</h4>
                        </div>
                        <div class="card-body">
                            <div class="card-block chart-block">
                                <canvas id="scatterChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">PF/PA vs Wins</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <div class="card-block chart-block">
                                <canvas id="pfwinsChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">Points By Week</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <div class="card-block">
                                <div class="row">
                                    <div class="col-sm-12" style="display: flex; flex-wrap: wrap; align-items: center; gap: 10px; margin-bottom: 20px;">
                                        <button class="btn btn-primary" id="allSeasonsRegular">All Seasons</button>
                                        <button class="btn btn-primary" id="currentSeasonRegular">Current Season</button>
                                        <button class="btn btn-primary" id="lastSeasonRegular">Last Season</button>
                                        <button class="btn btn-primary" id="lastFiveSeasonsRegular">Last 5 Seasons</button>
                                        <label style="margin: 0;"><strong>Start:</strong></label>
                                        <select id="startWeekRegular" class="dropdown form-control" style="width: auto;">
                                            <?php
                                            foreach ($allWeeks as $week) {
                                                echo '<option value="'.$week['week_id'].'">'.$week['week_display'].'</option>';
                                            }
                                            ?>
                                        </select>
                                        <label style="margin: 0;"><strong>End:</strong></label>
                                        <select id="endWeekRegular" class="dropdown form-control" style="width: auto;">
                                            <?php
                                            foreach ($allWeeks as $week) {
                                                // if last, select it
                                                if ($week['week_id'] == $allWeeks[count($allWeeks)-1]['week_id']) {
                                                    echo '<option selected value="'.$week['week_id'].'">'.$week['week_display'].'</option>';
                                                } else {
                                                    echo '<option value="'.$week['week_id'].'">'.$week['week_display'].'</option>';
                                                }
                                            }
                                            ?>
                                        </select>
                                        <label style="margin: 0;"><strong>Week:</strong></label>
                                        <select id="onlyWeekRegular" class="dropdown form-control" style="width: auto;">
                                            <option value="0">All Weeks</option>
                                            <?php
                                            for ($i = 1; $i <= 14; $i++) {
                                                echo '<option value="'.$i.'">Week '.$i.'</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12" style="height: 600px;">
                                        <canvas id="pointsBySeasonChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row card-section" id="game-time" style="display: none;">
                <div class="col-sm-12 col-lg-6 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">Top Points by Game Time</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <table class="table table-responsive table-striped nowrap" id="datatable-game-time">
                                <thead>
                                    <th>Year</th>
                                    <th>Week</th>
                                    <th>Manager</th>
                                    <th>Game Time</th>
                                    <th>Points</th>
                                </thead>
                                <tbody>
                                    <?php
                                    $labels = [
                                        1 => 'Thursday',
                                        2 => 'Friday',
                                        3 => 'Sunday Early',
                                        4 => 'Sunday Afternoon',
                                        5 => 'Sunday Night',
                                        6 => 'Monday',
                                        7 => 'Tuesday',
                                        8 => 'Other'
                                    ];
                                    $sql = "SELECT sum(points) as points, year, week, manager, game_slot
                                        FROM rosters
                                        WHERE game_time is not null
                                        AND roster_spot NOT IN ('IR','BN')
                                        GROUP BY year, week, manager, game_slot";
                                    $result = query($sql);
                                    while ($row = fetch_array($result)) { ?>
                                        <tr>
                                            <td><?php echo $row['year']; ?></td>
                                            <td><?php echo $row['week']; ?></td>
                                            <td><?php echo $row['manager']; ?></td>
                                            <td><?php echo isset($labels[$row['game_slot']]) ? $labels[$row['game_slot']] : null; ?></td>
                                            <td><?php echo '<a href="/rosters.php?year='.$row["year"].'&week='.$row["week"].'&manager='.$row['manager'].'">'.$row['points'].'</a>'; ?></td>
                                        </tr>

                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12 col-lg-6 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">Total Game Time Points</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <table class="table table-responsive table-striped nowrap" id="datatable-game-time2">
                                <thead>
                                    <th>Manager</th>
                                    <th>Game Time</th>
                                    <th>Points</th>
                                </thead>
                                <tbody>
                                    <?php
                                    $sql = "SELECT sum(points) as points, manager, game_slot
                                        FROM rosters
                                        WHERE points > 0
                                        AND roster_spot NOT IN ('IR','BN')
                                        GROUP BY manager, game_slot";
                                    $result = query($sql);
                                    while ($row = fetch_array($result)) { ?>
                                        <tr>
                                            <td><?php echo $row['manager']; ?></td>
                                            <td><?php echo isset($labels[$row['game_slot']]) ? $labels[$row['game_slot']] : null; ?></td>
                                            <td><?php echo $row['points']; ?></td>
                                        </tr>

                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row card-section" id="champions" style="display: none;">
                <div class="col-sm-12 col-md-6 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">Regular Season Champions</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <table class="table table-responsive table-striped nowrap" id="datatable-reg-season-winners">
                                <thead>
                                    <th>Year</th>
                                    <th>Champion</th>
                                    <th>Record</th>
                                    <th>Points</th>
                                    <th>Runner Up</th>
                                </thead>
                                <tbody>
                                    <?php
                                    $regSeasonWinners = getRegularSeasonWinners();
                                    foreach ($regSeasonWinners as $winner) { ?>
                                        <tr>
                                            <td><?php echo $winner['year']; ?></td>
                                            <td><?php echo $winner['champion']; ?></td>
                                            <td><?php echo $winner['record']; ?></td>
                                            <td><?php echo $winner['points']; ?></td>
                                            <td><?php echo $winner['runner_up']; ?></td>
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

<style>
    #datatable-regSeason input[type=text] {
        width: 100%;
    }
    
    /* Reduce spacing for tab navigation */
    .tab-buttons-container {
        padding: 10px 0 !important;
    }
</style>



<?php include 'footer.php'; ?>

<script type="text/javascript">
    // Declare DataTable variables globally
    var lookupTable, standingsTable, table;

    $(document).ready(function() {

        $('#datatable-regSeason thead tr')
            .clone(true)
            .addClass('filters')
            .appendTo('#datatable-regSeason thead');

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
            orderCellsTop: true,
            fixedHeader: true,
            initComplete: function () {
                var api = this.api();
    
                // For each column
                api.columns()
                .eq(0)
                .each(function (colIdx) {
                    // Set the header cell to contain the input element
                    var cell = $('.filters th').eq($(api.column(colIdx).header()).index());
                    var title = $(cell).text();
                    $(cell).html('<input type="text" placeholder="filter" />');

                    // On every keypress in this input
                    $('input',$('.filters th').eq($(api.column(colIdx).header()).index()))
                    .off('keyup change')
                    .on('change', function (e) {
                        // Get the search value
                        $(this).attr('title', $(this).val());
                        var regexr = '({search})';
                        // Search the column for that value
                        api
                            .column(colIdx)
                            .search(
                                this.value != ''
                                    ? regexr.replace('{search}', '(((' + this.value + ')))')
                                    : '',
                                this.value != '',
                                this.value == ''
                            )
                            .draw();
                    })
                    .on('keyup', function (e) {
                        e.stopPropagation();
                        $(this).trigger('change');
                    });
                });
            }
        });

        $('#datatable-pfpawins').DataTable({
            "info": false,
            "order": [
                [0, "desc"]
            ]
        });

        $('#datatable-game-time').DataTable({
            "order": [
                [4, "desc"]
            ]
        });
        
        $('#datatable-game-time2').DataTable({
            "order": [
                [2, "desc"]
            ]
        });
        
        $('#datatable-reg-season-winners').DataTable({
            "pageLength": 25,
            "order": [
                [0, "asc"]
            ]
        });

        var ctx = $("#winsChart");
        var years = <?php echo json_encode($winsChart['years']); ?>;
        var yearLabels = years.split(",");
        var teams = <?php echo json_encode($winsChart['wins']); ?>;
        let colors = ["#9c68d9","#a6c6fa","#3cf06e","#f33c47","#c0f6e6","#def89f","#dca130","#ff7f2c","#ecb2b6"," #f87598"];
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

        var myBarChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: yearLabels,
                datasets: dataset
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Wins',
                            font: {
                                size: 20
                            }
                        }
                    }
                }
            },
        });

        // Chart for scatter of weekly points
        var ctx2 = $("#scatterChart");

        var points = <?php echo json_encode($scatterChart); ?>;
        let pointColor = '#000';
        let dataset2 = [];
        for (const [key, value] of Object.entries(points)) {

            if (key.includes('Wins')) {
                pointColor = '#acf0ac';
            } else {
                pointColor = '#ffbdc3';
            }

            let obj = {};
            obj.label = key;
            obj.data = value;
            obj.showLine = false;
            obj.pointBackgroundColor = pointColor;
            obj.borderColor = pointColor;
            dataset2.push(obj);
        }

        let scatterChart = new Chart(ctx2, {
            type: 'scatter',
            data: {
                datasets: dataset2
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Manager Score vs. League Average',
                            font: {
                                size: 20
                            }
                        }
                    },
                    x: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Opponent Score vs. League Average',
                            font: {
                                size: 20
                            }
                        }
                    }
                }
            }
        });

        // Chart for scatter of season wins and points
        let ctx3 = $("#pfwinsChart");

        let pfpawins = <?php echo json_encode($pfwins); ?>;
        let dataset3 = [];
        for (const [key, value] of Object.entries(pfpawins)) {

            if (key.includes('For')) {
                pointColor = '#acf0ac';
            } else {
                pointColor = '#ffbdc3';
            }
            let obj = {};
            obj.label = key;
            obj.data = value;
            obj.showLine = false;
            obj.pointBackgroundColor = pointColor;
            obj.borderColor = pointColor;
            dataset3.push(obj);
        }

        let scatterChart2 = new Chart(ctx3, {
            type: 'scatter',
            data: {
                datasets: dataset3
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Season Points',
                            font: {
                                size: 20
                            }
                        }
                    },
                    x: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Season Wins',
                            font: {
                                size: 20
                            }
                        }
                    }
                }
            }
        });

        $('#lookup-btn').click(function () {
            lookupTable.ajax.reload();
        });

        lookupTable = $('#datatable-results').DataTable({
            searching: false,
            ajax: {
                url: 'dataLookup.php',
                data: function (d) {
                    d.dataType = 'standings';
                    d.manager1 = $('#manager1-select').val();
                    d.place1 = $('#place1').val();
                }
            },
            columns: [
                { data: "year" },
                { data: "week" },
                { data: "record" },
                { data: "points" },
            ],
            order: [
                [0, "desc"],
                [1, "desc"]
            ]
        });

        standingsTable = $('#datatable-league-standings').DataTable({
            searching: false,
            paging: false,
            info: false,
            ajax: {
                url: 'dataLookup.php',
                data: function (d) {
                    d.dataType = 'league-standings';
                    d.year = $('#year-select1').val();
                    d.week = $('#week-select').val();
                }
            },
            columns: [
                { data: "rank" },
                { data: "manager" },
                { data: "record" },
                { data: "points" },
                { data: "next" },
            ],
            order: [
                [0, "asc"]
            ]
        });

        $('#next-week-standings-btn').click(function () {
            let currWeek = $('#week-select').val();
            let currYear = $('#year-select1').val();
            if (currWeek == 14) {
                $('#week-select').val(1);
                $('#year-select1').val(parseInt(currYear) + 1);
            } else {
                $('#week-select').val(parseInt(currWeek) + 1);
            }
            standingsTable.ajax.reload();
        });
        
        $('#lookup-standings-btn').click(function () {
            standingsTable.ajax.reload();
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

        table = $('#datatable-weeklyRanks').DataTable({
            pageLength: 10,
            scrollX: true,
            autoWidth: false,
            ajax: {
                url: 'dataLookup.php',
                data: {
                    dataType: 'weekly-ranks'
                }
            },
            columns: [
                {
                    className: 'dt-control',
                    orderable: false,
                    data: null,
                    defaultContent: '<i class="icon-plus"></i>',
                    width: '30px'
                },
                { data: "year", width: '80px' },
                { data: "manager", width: '120px' },
                { data: "avg_rank", width: '150px' }
            ],
            order: [
                [1, "desc"],
                [3, "asc"]
            ]
        });

        // Add event listener for opening and closing details
        table.on('click', 'td.dt-control', function (e) {
            let tr = e.target.closest('tr');
            let row = table.row(tr);
        
            if (row.child.isShown()) {
                // This row is already open - close it
                row.child.hide();
            }
            else {
                // Open this row
                row.child(format(row.data())).show();
            }
        });

        function format ( rowData ) {
            var div = $('<div/>')
                .addClass( 'loading' )
                .text( 'Loading...' );

            $.ajax( {
                url: '/dataLookup.php',
                data: {
                    dataType: 'get-season-ranks',
                    manager: rowData.manager,
                    year: rowData.year
                },
                dataType: 'json',
                success: function (data) {
                    if (!data || data.length === 0) {
                        div.removeClass('loading');
                        div.text('No data available');
                        return;
                    }

                    let count = 1;
                    const table = document.createElement("table");
                    table.className = "table table-striped table-condensed";
                    const thead = document.createElement("thead");
                    const tbody = document.createElement("tbody");
                    
                    for (const row of data) {
                        if (count == 1) {
                            const headerRow = document.createElement("tr");
                            for (const key of Object.keys(row)) {
                                const th = document.createElement("th");
                                th.textContent = key.charAt(0).toUpperCase() + key.slice(1);
                                headerRow.appendChild(th);
                            }
                            thead.appendChild(headerRow);
                            table.appendChild(thead);
                        } 
                        const tr = document.createElement("tr");
                        for (const key of Object.keys(row)) {
                            const td = document.createElement("td");
                            td.textContent = row[key];
                            tr.appendChild(td);
                        }
                        tbody.appendChild(tr);
                        count++;
                    }
                    table.appendChild(tbody);

                    div.removeClass('loading');
                    div.text('');
                    div.append(table);

                    // Initialize DataTable with error handling
                    try {
                        $(table).DataTable({
                            paging: false,
                            searching: false,
                            info: false,
                            ordering: true,
                            autoWidth: false,
                            responsive: true
                        });
                    } catch (error) {
                        console.warn('DataTable initialization failed:', error);
                        // Table will still be usable without DataTable features
                    }
                },
                error: function(xhr, status, error) {
                    div.removeClass('loading');
                    div.text('Error loading data: ' + error);
                }
            } );

            return div; 
        }

        // Initialize DataTable for Record By Week
        $('#datatable-recordByWeek').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
        });
        
        // Initialize the page with Matchups & Stats tab active
        showCard('matchups-stats');
        
        // Points by Season Chart functionality
        window.pointsBySeasonChart = null;
        
        function updatePointsBySeasonChart() {
            $.ajax({
                url: 'dataLookup.php',
                data: {
                    dataType: 'points-by-season-all-managers',
                    startWeek: $('#startWeekRegular').val(),
                    endWeek: $('#endWeekRegular').val(),
                    onlyWeek: $('#onlyWeekRegular').val()
                },
                error: function() {
                    console.log('Error loading points by season data');
                },
                success: function(response) {
                    var data = JSON.parse(response);
                    
                    var ctx = $('#pointsBySeasonChart');
                    
                    // Destroy existing chart if it exists
                    if (window.pointsBySeasonChart) {
                        window.pointsBySeasonChart.destroy();
                    }
                    
                    // Define colors for each manager
                    var colors = ["#9c68d9","#a6c6fa","#3cf06e","#f33c47","#c0f6e6","#def89f","#dca130","#ff7f2c","#ecb2b6","#f87598"];
                    var datasets = [];
                    var colorIndex = 0;
                    
                    // Create dataset for each manager
                    for (var managerName in data.managers) {
                        datasets.push({
                            label: managerName,
                            data: data.managers[managerName],
                            borderColor: colors[colorIndex % colors.length],
                            backgroundColor: colors[colorIndex % colors.length] + '20', // Add transparency
                            fill: false,
                            tension: 0.1,
                            pointRadius: 3,
                            pointHoverRadius: 5
                        });
                        colorIndex++;
                    }
                    
                    window.pointsBySeasonChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: data.weeks,
                            datasets: datasets
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    title: {
                                        display: true,
                                        text: 'Points',
                                        font: {
                                            size: 16
                                        }
                                    }
                                },
                                x: {
                                    title: {
                                        display: true,
                                        text: 'Week',
                                        font: {
                                            size: 16
                                        }
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'top'
                                }
                            }
                        }
                    });
                }
            });
        }
        
        // Season variables needed for button functionality
        var season = <?php echo date('Y'); ?>;
        var allWeeks = <?php echo json_encode($allWeeks); ?>;
        
        // Button event handlers for points by season chart
        $('#startWeekRegular').change(function() {
            updatePointsBySeasonChart();
        });
        
        $('#endWeekRegular').change(function() {
            updatePointsBySeasonChart();
        });
        
        $('#onlyWeekRegular').change(function() {
            updatePointsBySeasonChart();
        });
        
        $('#allSeasonsRegular').click(function() {
            $('#startWeekRegular').val('1_2006');
            $('#endWeekRegular').val(allWeeks[allWeeks.length-1]['week_id']);
            $('#onlyWeekRegular').val('0');
            updatePointsBySeasonChart();
        });
        
        $('#currentSeasonRegular').click(function() {
            $('#startWeekRegular').val('1_'+season);
            $('#endWeekRegular').val(allWeeks[allWeeks.length-1]['week_id']);
            $('#onlyWeekRegular').val('0');
            updatePointsBySeasonChart();
        });
        
        $('#lastSeasonRegular').click(function() {
            $('#startWeekRegular').val('1_'+(season-1));
            $('#endWeekRegular').val('14_'+(season-1));
            $('#onlyWeekRegular').val('0');
            updatePointsBySeasonChart();
        });
        
        $('#lastFiveSeasonsRegular').click(function() {
            $('#startWeekRegular').val('1_'+(season-5));
            $('#endWeekRegular').val(allWeeks[allWeeks.length-1]['week_id']);
            $('#onlyWeekRegular').val('0');
            updatePointsBySeasonChart();
        });
        
        // Initialize the chart when page loads
        if ($('#pfpa-correlation').css('display') !== 'none') {
            updatePointsBySeasonChart();
        }

        $('#currentSeasonRegular').click(function() {
            $('#startWeekRegular').val('1_'+season);
            $('#endWeekRegular').val(allWeeks[allWeeks.length-1]['week_id']);
            updatePointsBySeasonChart();
        });

        $('#lastSeasonRegular').click(function() {
            $('#startWeekRegular').val('1_'+(season-1));
            $('#endWeekRegular').val('14_'+(season-1));
            updatePointsBySeasonChart();
        });

        $('#lastFiveSeasonsRegular').click(function() {
            $('#startWeekRegular').val('1_'+(season-5));
            $('#endWeekRegular').val(allWeeks[allWeeks.length-1]['week_id']);
            updatePointsBySeasonChart();
        });

        // Initialize the points by season chart when the pfpa-correlation tab is shown
        // We'll trigger this when the tab is clicked
        $(document).on('click', '#pfpa-correlation-tab', function() {
            // Delay to ensure the tab content is visible
            setTimeout(function() {
                updatePointsBySeasonChart();
            }, 100);
        });
    });
</script>