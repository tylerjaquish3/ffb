<?php

$pageName = "Regular Season";
include 'header.php';
include 'sidebar.php';

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
                                    <!-- Table body will be populated by JS -->
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
                            <option value="3" selected>Season Points</option>
                            <option value="4">Average PF/PA</option>
                            <option value="5">Start Streaks</option>
                            <option value="6">Win/Loss Margin</option>
                            <option value="7">Weekly Points</option>
                            <option value="8">Losses with Top 3 Pts</option>
                            <option value="9">Wins with Bottom 3 Pts</option>
                            <option value="10">Record Against Everyone</option>
                            <option value="11">Draft Positions</option>
                            <option value="12">Moves/Trades</option>
                            <option value="13">Lineup Accuracy</option>
                            <option value="14">Points by Position</option>
                            <option value="15">Points by Position & Season</option>
                            <option value="16">Points by Position & Week</option>
                        </select>
                    </div>
                    <div style="background: #fff; direction: ltr; clear: both; padding-top: 10px;">
                        <?php include 'regMiscStats.php'; ?>
                    </div>
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
                                                <!-- Week headers will be populated by JS -->
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Table body will be populated by JS -->
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
                                        <!-- Table body will be populated by JS after fetch -->
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
                                    <div class="col-sm-12" style="margin-bottom: 20px;">
                                        <!-- Button row for mobile-friendly layout -->
                                        <div class="btn-group-mobile" style="display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 15px;">
                                            <button class="btn btn-primary btn-mobile" id="allSeasonsRegular">All Seasons</button>
                                            <button class="btn btn-primary btn-mobile" id="currentSeasonRegular">Current Season</button>
                                            <button class="btn btn-primary btn-mobile" id="lastSeasonRegular">Last Season</button>
                                            <button class="btn btn-primary btn-mobile" id="lastFiveSeasonsRegular">Last 5 Seasons</button>
                                        </div>
                                        <!-- Filter controls row -->
                                        <div class="filter-controls" style="display: flex; flex-wrap: wrap; align-items: center; gap: 10px;">
                                            <div class="form-group-inline" style="display: flex; align-items: center; gap: 5px;">
                                                <label style="margin: 0; white-space: nowrap;"><strong>Start:</strong></label>
                                                <select id="startWeekRegular" class="dropdown form-control" style="width: auto; min-width: 120px;">
                                                    <!-- Options will be populated by JS -->
                                                </select>
                                            </div>
                                            <div class="form-group-inline" style="display: flex; align-items: center; gap: 5px;">
                                                <label style="margin: 0; white-space: nowrap;"><strong>End:</strong></label>
                                                <select id="endWeekRegular" class="dropdown form-control" style="width: auto; min-width: 120px;">
                                                    <!-- Options will be populated by JS -->
                                                </select>
                                            </div>
                                            <div class="form-group-inline" style="display: flex; align-items: center; gap: 5px;">
                                                <label style="margin: 0; white-space: nowrap;"><strong>Week:</strong></label>
                                                <select id="onlyWeekRegular" class="dropdown form-control" style="width: auto; min-width: 100px;">
                                                    <option value="0">All Weeks</option>
                                                    <?php
                                                    for ($i = 1; $i <= 14; $i++) {
                                                        echo '<option value="'.$i.'">Week '.$i.'</option>';
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12" style="height: 600px;">
                                        <canvas id="pointsByWeekChart"></canvas>
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
                <div class="col-sm-12 col-md-8 table-padding">
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
    
    /* Mobile-friendly button styles */
    .btn-mobile {
        min-width: 120px;
        padding: 8px 12px;
        font-size: 14px;
        margin-bottom: 5px;
    }
    
    .btn-group-mobile {
        justify-content: flex-start;
    }
    
    .filter-controls {
        justify-content: flex-start;
    }
    
    .form-group-inline {
        margin-bottom: 10px;
    }
    
    /* Mobile responsive styles */
    @media (max-width: 768px) {
        .btn-mobile {
            flex: 1;
            min-width: 0;
            max-width: 48%;
        }
        
        .btn-group-mobile {
            justify-content: space-between;
        }
        
        .filter-controls {
            flex-direction: column;
            align-items: flex-start !important;
            gap: 15px !important;
        }
        
        .form-group-inline {
            width: 100%;
            justify-content: space-between;
        }
        
        .form-group-inline select {
            min-width: 150px !important;
        }
    }
    
    /* Extra small mobile screens */
    @media (max-width: 480px) {
        .btn-mobile {
            max-width: 100%;
            width: 100%;
        }
        
        .btn-group-mobile {
            flex-direction: column;
        }
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
            "columnDefs": [
                {
                    "targets": [8,9],
                    "visible": false,
                },
                {
                    "targets": [1],
                    "type": "num"
                }
            ],
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
                        
                        // For Week column (index 1), use exact match with ^ and $ anchors
                        if (colIdx === 1 && this.value !== '') {
                            // Use ^value$ for exact numeric match
                            api
                                .column(colIdx)
                                .search('^' + this.value + '$', true, false)
                                .draw();
                        } else {
                            // For other columns, use the original regex search
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
                        }
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

        // Wins chart will be populated by JS after fetch

        // Chart for scatter of weekly points
        var ctx2 = $("#scatterChart");

        // Scatter chart will be populated by JS after fetch

        // Chart for scatter of season wins and points
        let ctx3 = $("#pfwinsChart");

        // PFPA chart will be populated by JS after fetch

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
        
        // Initialize with Season Points table (value 3) when page loads
        setTimeout(showRegTable(3), 1000);

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
        window.pointsByWeekChart = null;
        
        function updatePointsByWeekChart() {
            $.ajax({
                url: 'dataLookup.php',
                data: {
                    dataType: 'points-by-week-all-managers',
                    startWeek: $('#startWeekRegular').val(),
                    endWeek: $('#endWeekRegular').val(),
                    onlyWeek: $('#onlyWeekRegular').val()
                },
                error: function() {
                    console.log('Error loading points by week data');
                },
                success: function(response) {
                    var data = JSON.parse(response);
                    
                    var ctx = $('#pointsByWeekChart');
                    
                    // Destroy existing chart if it exists
                    if (window.pointsByWeekChart) {
                        window.pointsByWeekChart.destroy();
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
                    
                    window.pointsByWeekChart = new Chart(ctx, {
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
        
        // Season and allWeeks will be set by JS after fetch
        let season, allWeeks;
        // Fetch all regular season data after page load
        $(function() {
            fetch('data/regularSeason.php')
            .then(response => response.json())
            .then(data => {
                season = new Date().getFullYear();
                allWeeks = data.allWeeks;

                // Populate startWeekRegular and endWeekRegular dropdowns
                const $startWeekSelect = $('#startWeekRegular');
                const $endWeekSelect = $('#endWeekRegular');
                if ($startWeekSelect.length && $endWeekSelect.length && Array.isArray(allWeeks)) {
                    $startWeekSelect.empty();
                    $endWeekSelect.empty();
                    allWeeks.forEach(weekObj => {
                        $startWeekSelect.append($('<option>', {
                            value: weekObj.week_id,
                            text: weekObj.week_display
                        }));
                        $endWeekSelect.append($('<option>', {
                            value: weekObj.week_id,
                            text: weekObj.week_display
                        }));
                    });
                    // Set default values to first and last week
                    $startWeekSelect.val(allWeeks[0]?.week_id ?? '');
                    $endWeekSelect.val(allWeeks[allWeeks.length-1]?.week_id ?? '');
                }
                // Destroy existing DataTable if it exists
                if ($.fn.DataTable.isDataTable('#datatable-regSeason')) {
                    $('#datatable-regSeason').DataTable().clear().destroy();
                }
                // Initialize DataTable with fetched data
                $('#datatable-regSeason').DataTable({
                    data: data.regSeasonMatchups,
                    columns: [
                        { data: 'year' },
                        { data: 'week' },
                        { data: 'manager1', render: function(data, type, row) {
                            return `<span class="badge ${row.winner === 'm1' ? 'badge-primary' : 'badge-secondary'}">${data}</span>`;
                        }},
                        { data: 'manager2', render: function(data, type, row) {
                            return `<span class="badge ${row.winner === 'm2' ? 'badge-primary' : 'badge-secondary'}">${data}</span>`;
                        }},
                        { data: 'score1', render: function(data, type, row) {
                            return `<a href="/rosters.php?year=${row.year}&week=${row.week}&manager=${row.manager1}">${data}</a>`;
                        }},
                        { data: 'score1note', render: function(data) {
                            return `<span style='font-size: 11px;'>${data}</span>`;
                        }},
                        { data: 'score2', render: function(data, type, row) {
                            return `<a href="/rosters.php?year=${row.year}&week=${row.week}&manager=${row.manager2}">${data}</a>`;
                        }},
                        { data: 'score2note', render: function(data) {
                            return `<span style='font-size: 11px;'>${data}</span>`;
                        }},
                        { data: 'score1noteSearch' },
                        { data: 'score2noteSearch' }
                    ],
                    pageLength: 25,
                    order: [[0, 'desc'], [1, 'desc']],
                    columnDefs: [
                        { targets: [8,9], visible: false },
                        { targets: [1], type: 'num' }
                    ],
                    orderCellsTop: true,
                    fixedHeader: true,
                    initComplete: function () {
                        var api = this.api();
                        api.columns().eq(0).each(function (colIdx) {
                            var cell = $('.filters th').eq($(api.column(colIdx).header()).index());
                            var title = $(cell).text();
                            $(cell).html('<input type="text" placeholder="filter" />');
                            $('input',$('.filters th').eq($(api.column(colIdx).header()).index()))
                                .off('keyup change')
                                .on('change', function (e) {
                                    $(this).attr('title', $(this).val());
                                    var regexr = '({search})';
                                    if (colIdx === 1 && this.value !== '') {
                                        api.column(colIdx).search('^' + this.value + '$', true, false).draw();
                                    } else {
                                        api.column(colIdx).search(
                                            this.value != ''
                                                ? regexr.replace('{search}', '(((' + this.value + ')))')
                                                : '',
                                            this.value != '',
                                            this.value == ''
                                        ).draw();
                                    }
                                })
                                .on('keyup', function (e) {
                                    e.stopPropagation();
                                    $(this).trigger('change');
                                });
                        });
                    }
                });

                // Populate Managers Dropdown
                const managerSelect = document.getElementById('manager1-select');
                if (managerSelect) {
                    managerSelect.innerHTML = '';
                    data.managers.forEach(manager => {
                        const option = document.createElement('option');
                        option.value = manager.id;
                        option.textContent = manager.name;
                        managerSelect.appendChild(option);
                    });
                }

                // Populate Years Dropdown
                const yearSelect = document.getElementById('year-select1');
                if (yearSelect) {
                    yearSelect.innerHTML = '';
                    data.years.forEach(year => {
                        const option = document.createElement('option');
                        option.value = year;
                        option.textContent = year;
                        yearSelect.appendChild(option);
                    });
                }

                // Populate Weeks Dropdown
                const weekSelect = document.getElementById('week-select');
                if (weekSelect) {
                    weekSelect.innerHTML = '';
                    data.weeks.forEach(week => {
                        const option = document.createElement('option');
                        option.value = week;
                        option.textContent = week;
                        weekSelect.appendChild(option);
                    });
                }

                // Populate Wins By Season Table
                const winsTbody = document.querySelector('#datatable-wins tbody');
                if (winsTbody) {
                    winsTbody.innerHTML = '';
                    Object.entries(data.seasonWins).forEach(([year, array]) => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>${year}</td>
                            <td>${array.ben}</td>
                            <td>${array.justin}</td>
                            <td>${array.gavin}</td>
                            <td>${array.matt}</td>
                            <td>${array.aj}</td>
                            <td>${array.andy ?? 'N/A'}</td>
                            <td>${array.cameron ?? 'N/A'}</td>
                            <td>${array.tyler}</td>
                            <td>${array.everett}</td>
                            <td>${array.cole}</td>
                        `;
                        winsTbody.appendChild(tr);
                    });
                }

                // Populate Game Time Points Table with DataTables
                if ($.fn.DataTable.isDataTable('#datatable-game-time')) {
                    $('#datatable-game-time').DataTable().clear().destroy();
                }
                $('#datatable-game-time').DataTable({
                    data: data.gameTimePoints,
                    columns: [
                        { data: 'year' },
                        { data: 'week' },
                        { data: 'manager' },
                        { data: 'game_slot_label' },
                        { data: 'points', render: function(data, type, row) {
                            const pointsRounded = Number(data).toFixed(2);
                            return `<a href="/rosters.php?year=${row.year}&week=${row.week}&manager=${row.manager}">${pointsRounded}</a>`;
                        }}
                    ],
                    order: [[4, 'desc']],
                    pageLength: 25
                });

                // Populate Total Game Time Points Table with DataTables
                if ($.fn.DataTable.isDataTable('#datatable-game-time2')) {
                    $('#datatable-game-time2').DataTable().clear().destroy();
                }
                $('#datatable-game-time2').DataTable({
                    data: data.totalGameTimePoints,
                    columns: [
                        { data: 'manager' },
                        { data: 'game_slot_label' },
                        { data: 'points', render: function(data) {
                            return Number(data).toFixed(2);
                        }}
                    ],
                    order: [[2, 'desc']],
                    pageLength: 25
                });

                // Populate Regular Season Winners Table
                const regSeasonWinnersTbody = document.querySelector('#datatable-reg-season-winners tbody');
                if (regSeasonWinnersTbody) {
                    regSeasonWinnersTbody.innerHTML = '';
                    data.regSeasonWinners.forEach(winner => {
                        const tr = document.createElement('tr');
                        const pointsRounded = Number(winner.points).toFixed(2);
                        tr.innerHTML = `
                            <td>${winner.year}</td>
                            <td>${winner.champion}</td>
                            <td>${winner.record}</td>
                            <td>${pointsRounded}</td>
                            <td>${winner.runner_up}</td>
                        `;
                        regSeasonWinnersTbody.appendChild(tr);
                    });
                }

                // Populate Wins Chart
                const winsChart = document.getElementById('winsChart').getContext('2d');
                const yearLabels = data.winsChart.years.split(',');
                const teams = data.winsChart.wins;
                let colors = ["#9c68d9","#a6c6fa","#3cf06e","#f33c47","#c0f6e6","#def89f","#dca130","#ff7f2c","#ecb2b6","#f87598"];
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
                new Chart(winsChart, {
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
                                    font: { size: 20 }
                                }
                            }
                        }
                    }
                });

                // Populate Scatter Chart
                const scatterChart = document.getElementById('scatterChart').getContext('2d');
                let points = data.scatterChart;
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
                new Chart(scatterChart, {
                    type: 'scatter',
                    data: { datasets: dataset2 },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                display: true,
                                title: {
                                    display: true,
                                    text: 'Manager Score vs. League Average',
                                    font: { size: 20 }
                                }
                            },
                            x: {
                                display: true,
                                title: {
                                    display: true,
                                    text: 'Opponent Score vs. League Average',
                                    font: { size: 20 }
                                }
                            }
                        }
                    }
                });

                // Populate PFPA Chart
                const pfwinsChart = document.getElementById('pfwinsChart').getContext('2d');
                let pfpawins = data.pfwins;
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
                new Chart(pfwinsChart, {
                    type: 'scatter',
                    data: { datasets: dataset3 },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                display: true,
                                title: {
                                    display: true,
                                    text: 'Season Points',
                                    font: { size: 20 }
                                }
                            },
                            x: {
                                display: true,
                                title: {
                                    display: true,
                                    text: 'Season Wins',
                                    font: { size: 20 }
                                }
                            }
                        }
                    }
                });

                // Populate Record By Week Table
                const recordByWeekTbody = document.querySelector('#datatable-recordByWeek tbody');
                const recordByWeekThead = document.querySelector('#datatable-recordByWeek thead tr');
                if (recordByWeekTbody && recordByWeekThead) {
                    // Week headers
                    recordByWeekThead.innerHTML = '<th>Manager</th>';
                    data.recordsByWeek.weeks.sort().forEach(week => {
                        recordByWeekThead.innerHTML += `<th>Week ${week}</th>`;
                    });
                    // Table body
                    recordByWeekTbody.innerHTML = '';
                    data.recordsByWeek.managers.forEach(manager => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `<td>${manager}</td>`;
                        data.recordsByWeek.weeks.forEach(week => {
                            const record = data.recordsByWeek.records[manager]?.[week] ?? '0-0';
                            tr.innerHTML += `<td>${record}</td>`;
                        });
                        recordByWeekTbody.appendChild(tr);
                    });
                }
            });
        });
        
        // Button event handlers for points by season chart
        $('#startWeekRegular').change(function() {
            updatePointsByWeekChart();
        });
        
        $('#endWeekRegular').change(function() {
            updatePointsByWeekChart();
        });
        
        $('#onlyWeekRegular').change(function() {
            updatePointsByWeekChart();
        });
        
        $('#allSeasonsRegular').click(function() {
            $('#startWeekRegular').val('1_2006');
            $('#endWeekRegular').val(allWeeks[allWeeks.length-1]['week_id']);
            $('#onlyWeekRegular').val('0');
            updatePointsByWeekChart();
        });
        
        $('#currentSeasonRegular').click(function() {
            $('#startWeekRegular').val('1_'+season);
            $('#endWeekRegular').val(allWeeks[allWeeks.length-1]['week_id']);
            $('#onlyWeekRegular').val('0');
            updatePointsByWeekChart();
        });
        
        $('#lastSeasonRegular').click(function() {
            $('#startWeekRegular').val('1_'+(season-1));
            $('#endWeekRegular').val('14_'+(season-1));
            $('#onlyWeekRegular').val('0');
            updatePointsByWeekChart();
        });
        
        $('#lastFiveSeasonsRegular').click(function() {
            $('#startWeekRegular').val('1_'+(season-5));
            $('#endWeekRegular').val(allWeeks[allWeeks.length-1]['week_id']);
            $('#onlyWeekRegular').val('0');
            updatePointsByWeekChart();
        });
        
        // Initialize the chart when page loads
        if ($('#pfpa-correlation').css('display') !== 'none') {
            updatePointsByWeekChart();
        }

        $('#currentSeasonRegular').click(function() {
            $('#startWeekRegular').val('1_'+season);
            $('#endWeekRegular').val(allWeeks[allWeeks.length-1]['week_id']);
            updatePointsByWeekChart();
        });

        $('#lastSeasonRegular').click(function() {
            $('#startWeekRegular').val('1_'+(season-1));
            $('#endWeekRegular').val('14_'+(season-1));
            updatePointsByWeekChart();
        });

        $('#lastFiveSeasonsRegular').click(function() {
            $('#startWeekRegular').val('1_'+(season-5));
            $('#endWeekRegular').val(allWeeks[allWeeks.length-1]['week_id']);
            updatePointsByWeekChart();
        });

        // Initialize the points by season chart when the pfpa-correlation tab is shown
        // We'll trigger this when the tab is clicked
        $(document).on('click', '#pfpa-correlation-tab', function() {
            // Delay to ensure the tab content is visible
            setTimeout(function() {
                updatePointsByWeekChart();
            }, 100);
        });
    });
</script>