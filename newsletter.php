<?php

$pageName = "Newsletter";
include 'header.php';
include 'sidebar.html';

// Fetch newsletter content from database
$recapContent = "Recap content is not available for this week.";
$previewContent = "Preview content is not available for this week.";

$recapQuery = query("SELECT recap FROM newsletters WHERE year = $selectedSeason AND week = " . $selectedWeek);
$recapRow = fetch_array($recapQuery);

if ($recapRow && !empty($recapRow['recap'])) {
    $recapContent = $recapRow['recap'];
}

// Get preview for current week
$previewQuery = query("SELECT preview FROM newsletters WHERE year = $selectedSeason AND week = $selectedWeek");
$previewRow = fetch_array($previewQuery);
if ($previewRow && !empty($previewRow['preview'])) {
    $previewContent = $previewRow['preview'];
}

?>

<div class="app-content content container-fluid">
    <div class="content-wrapper">
        <div class="content-header row"></div>

        <div class="content-body">
            
            <!-- Dropdown selections -->
            <div class="row" style="direction: ltr;">
                <div class="col-sm-12 col-md-2">
                    <label for="year-select" style="color: #fff;">Season:</label>
                    <select id="year-select" class="form-control">
                        <?php
                        $currentYear = date('Y');
                        $result = query("SELECT DISTINCT year FROM rosters ORDER BY year DESC");
                        $yearsInDB = array();
                        
                        // Collect all years from database
                        while ($row = fetch_array($result)) {
                            $yearsInDB[] = $row['year'];
                        }
                        
                        // Add current year if not in database
                        if (!in_array($currentYear, $yearsInDB)) {
                            array_unshift($yearsInDB, $currentYear);
                        }
                        
                        // Sort years in descending order
                        rsort($yearsInDB);
                        
                        // Display options
                        foreach ($yearsInDB as $year) {
                            if ($year == $selectedSeason) {
                                echo '<option selected value="'.$year.'">'.$year.'</option>';
                            } else {
                                echo '<option value="'.$year.'">'.$year.'</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="col-sm-12 col-md-2">
                    <label for="week-select" style="color: #fff;">Week:</label>
                    <select id="week-select" class="form-control">
                        <?php
                        $result = query("SELECT DISTINCT week FROM rosters WHERE year = $selectedSeason ORDER BY week ASC");
                        $weeks = [];
                        while ($row = fetch_array($result)) {
                            $weeks[] = $row['week'];
                        }
                        
                        // If no weeks found and this is the current year, default to Week 1
                        if (empty($weeks) && $selectedSeason == date('Y')) {
                            $weeks[] = 1;
                        }
                        
                        // Display week options
                        foreach ($weeks as $week) {
                            if ($week == $selectedWeek) {
                                echo '<option selected value="'.$week.'">Week '.$week.'</option>';
                            } else {
                                echo '<option value="'.$week.'">Week '.$week.'</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>
            <br>
            
            <!-- Schedule Table - visible for all weeks -->
            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header" style="direction: ltr;">
                            <h4>Week <?php echo $selectedWeek; ?> Schedule</h4>
                        </div>
                        <div class="card-body" style="background: #fff;">
                            <?php if (!empty($scheduleInfo)): ?>
                                <table id="datatable-schedule" class="table table-striped table-bordered" style="direction: ltr;">
                                    <thead>
                                        <tr>
                                            <th>Manager 1</th>
                                            <th>Manager 2</th>
                                            <th>Regular Season H2H</th>
                                            <th>Postseason H2H</th>
                                            <th>Current Streak</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($scheduleInfo as $matchup): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($matchup['manager1']); ?></td>
                                                <td><?php echo htmlspecialchars($matchup['manager2']); ?></td>
                                                <td><?php echo htmlspecialchars($matchup['record']); ?></td>
                                                <td><?php echo htmlspecialchars($matchup['postseason_record']); ?></td>
                                                <td><?php echo htmlspecialchars($matchup['streak']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <p>No schedule information available for Week <?php echo $selectedWeek; ?> of the <?php echo $selectedSeason; ?> season.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php if ($selectedWeek == 1): ?>
                <!-- Week 1: Show year recap and preview -->
                <div class="row">
                    <div class="col-sm-12 col-lg-6">
                        <div class="card">
                            <div class="card-header" style="direction: ltr;">
                                <h4><?php echo ($selectedSeason - 1); ?> Recap</h4>
                            </div>
                            <div class="card-body" style="background: #fff; direction: ltr">
                                <?php echo nl2br(htmlspecialchars($recapContent)); ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 col-lg-6">
                        <div class="card">
                            <div class="card-header" style="direction: ltr;">
                                <h4>Week <?php echo $selectedWeek; ?> Preview</h4>
                            </div>
                            <div class="card-body" style="background: #fff; direction: ltr">
                                <?php echo nl2br(htmlspecialchars($previewContent)); ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- Week 2+: Show full content -->
                <div class="row">
                    <div class="col-sm-12 col-lg-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="media">
                                    <div class="p-2 text-xs-center bg-green-ffb media-left media-middle">
                                        <i class="icon-coin-dollar font-large-2 white"></i>
                                    </div>
                                    <div class="p-2 bg-green-ffb media-body">
                                        <h5>Top Week Performance</h5>
                                        <h5 class="text-bold-400"><?php echo $topPerformers['topPerformer']['manager'].' - Week '.$topPerformers['topPerformer']['week']; ?><br />
                                            <?php echo $topPerformers['topPerformer']['player'].' - '.$topPerformers['topPerformer']['points'].' points'; ?>
                                        </h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <div class="media">
                                    <div class="p-2 text-xs-center bg-green-ffb media-left media-middle">
                                        <i class="icon-clipboard font-large-2 white"></i>
                                    </div>
                                    <div class="p-2 bg-green-ffb media-body">
                                        <h5>Best Draft Pick</h5>
                                        <h5 class="text-bold-400"><?php echo $topPerformers['bestDraftPick']['manager']; ?><br />
                                            <?php echo $topPerformers['bestDraftPick']['player'].' - '.$topPerformers['bestDraftPick']['points'].' points'; ?>
                                        </h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <div class="media">
                                    <div class="p-2 text-xs-center bg-green-ffb media-left media-middle">
                                        <i class="icon-flag font-large-2 white"></i>
                                    </div>
                                    <div class="p-2 bg-green-ffb media-body">
                                        <h5>Most Total TDs (incl. BN)</h5>
                                        <h5 class="text-bold-400"><?php echo $topPerformers['mostTds']['manager']; ?><br />
                                            <?php echo $topPerformers['mostTds']['points']; ?>
                                        </h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <div class="media">
                                    <div class="p-2 text-xs-center bg-green-ffb media-left media-middle">
                                        <i class="icon-earth font-large-2 white"></i>
                                    </div>
                                    <div class="p-2 bg-green-ffb media-body">
                                        <h5>Most Total Yards (incl. BN)</h5>
                                        <h5 class="text-bold-400"><?php echo $topPerformers['mostYds']['manager']; ?><br />
                                            <?php echo $topPerformers['mostYds']['points']; ?>
                                        </h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <div class="media">
                                    <div class="p-2 text-xs-center bg-green-ffb media-left media-middle">
                                        <i class="icon-power-cord font-large-2 white"></i>
                                    </div>
                                    <div class="p-2 bg-green-ffb media-body">
                                        <h5>Best Bench</h5>
                                        <h5 class="text-bold-400"><?php echo $topPerformers['bestBench']['manager']; ?><br />
                                            <?php echo $topPerformers['bestBench']['points']; ?>
                                        </h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-12 col-lg-8 table-padding">
                        <div class="card">
                            <div class="card-header">
                                <h4 style="float: right">Week <?php echo $selectedWeek - 1; ?> Recap</h4>
                            </div>
                            <div class="card-body" style="background: #fff; direction: ltr">
                                <?php echo nl2br(htmlspecialchars($recapContent)); ?>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header">
                                <h4 style="float: right">Week <?php echo $selectedWeek; ?> Preview</h4>
                            </div>
                            <div class="card-body" style="background: #fff; direction: ltr">
                                <?php echo nl2br(htmlspecialchars($previewContent)); ?>
                            </div>
                        </div>
                    </div>

                </div>
                </div>
                <div class="row">
                    <div class="col-sm-12 table-padding">
                        <div class="card">
                            <div class="card-header">
                                <h4 style="float: right">Week <?php echo $selectedWeek - 1; ?> Top Performers</h4>
                            </div>
                            <div class="card-body" style="background: #fff; direction: ltr">
                                <table class="stripe nowrap row-border order-column full-width" id="datatable-bestWeek">
                                    <thead>
                                        <?php
                                        foreach ($bestWeek as $manager => $values) {
                                            $headers = array_keys($values);
                                            $currentPointsColCount = count($headers);
                                            foreach ($headers as $header) {
                                                echo '<th>Top '.$header.'</th>';
                                            }
                                            break;
                                        }
                                        ?>
                                    </thead>
                                    <tbody>
                                        <?php
                                        foreach ($bestWeek as $week => $players) { 
                                            if ($week != ($selectedWeek - 1)) {
                                                continue;
                                            }
                                            ?>
                                            <tr>
                                                <?php foreach ($players as $pos => $stuff) {
                                                    if ($pos != 'qb') { ?>
                                                        <td data-order="<?php echo $stuff['points']; ?>">
                                                            <strong><?php echo $stuff['manager']; ?></strong><br />
                                                            <?php echo $stuff['player']; ?><br />
                                                            <i><?php echo $stuff['points']. ' points'; ?></i>
                                                        </td>
                                                    <?php }
                                                } ?>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12 col-lg-6 table-padding">
                        <div class="card">
                            <div class="card-header">
                                <h4 style="float: right">Season Stats</h4>
                            </div>
                            <div class="card-body" style="background: #fff; direction: ltr">
                                <table class="stripe nowrap row-border order-column" id="datatable-currentStats">
                                    <thead>
                                        <th>Manager</th>
                                        <th>Total Yds</th>
                                        <th>Total TDs</th>
                                        <th>Pass Yds</th>
                                        <th>Pass TDs</th>
                                        <th>Ints</th>
                                        <th>Rush Yds</th>
                                        <th>Rush TDs</th>
                                        <th>Rec</th>
                                        <th>Rec Yds</th>
                                        <th>Rec TDs</th>
                                    </thead>
                                    <tbody>
                                        <?php
                                        while ($row = fetch_array($stats)) { ?>
                                            <tr>
                                                <td><?php echo $row['manager']; ?></td>
                                                <td><?php echo $row['pass_yds'] + $row['rush_yds'] + $row['rec_yds']; ?></td>
                                                <td><?php echo $row['pass_tds'] + $row['rush_tds'] + $row['rec_tds']; ?></td>
                                                <td><?php echo $row['pass_yds']; ?></td>
                                                <td><?php echo $row['pass_tds']; ?></td>
                                                <td><?php echo $row['ints']; ?></td>
                                                <td><?php echo $row['rush_yds']; ?></td>
                                                <td><?php echo $row['rush_tds']; ?></td>
                                                <td><?php echo $row['rec']; ?></td>
                                                <td><?php echo $row['rec_yds']; ?></td>
                                                <td><?php echo $row['rec_tds']; ?></td>
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
                                <h4 style="float: right">Week <?php echo $selectedWeek - 1; ?> Stats</h4>
                            </div>
                            <div class="card-body" style="background: #fff; direction: ltr">
                                <table class="stripe nowrap row-border order-column" id="datatable-currentWeekStats">
                                    <thead>
                                        <th>Manager</th>
                                        <th>Total Yds</th>
                                        <th>Total TDs</th>
                                        <th>Pass Yds</th>
                                        <th>Pass TDs</th>
                                        <th>Ints</th>
                                        <th>Rush Yds</th>
                                        <th>Rush TDs</th>
                                        <th>Rec</th>
                                        <th>Rec Yds</th>
                                        <th>Rec TDs</th>
                                    </thead>
                                    <tbody>
                                        <?php
                                        while ($row = fetch_array($weekStats)) { 
                                            if ($row['week'] != ($selectedWeek - 1)) {
                                                continue;
                                            }
                                            ?>
                                            <tr>
                                                <td><?php echo $row['manager']; ?></td>
                                                <td><?php echo $row['pass_yds'] + $row['rush_yds'] + $row['rec_yds']; ?></td>
                                                <td><?php echo $row['pass_tds'] + $row['rush_tds'] + $row['rec_tds']; ?></td>
                                                <td><?php echo $row['pass_yds']; ?></td>
                                                <td><?php echo $row['pass_tds']; ?></td>
                                                <td><?php echo $row['ints']; ?></td>
                                                <td><?php echo $row['rush_yds']; ?></td>
                                                <td><?php echo $row['rush_tds']; ?></td>
                                                <td><?php echo $row['rec']; ?></td>
                                                <td><?php echo $row['rec_yds']; ?></td>
                                                <td><?php echo $row['rec_tds']; ?></td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12 col-lg-4 table-padding">
                        <div class="card">
                            <div class="card-header">
                                <h4 style="float: right">Record Against Everyone</h4>
                            </div>
                            <div class="card-body" style="background: #fff; direction: ltr">
                                <table class="table table-striped nowrap" id="datatable-everyone">
                                    <thead>
                                        <th>Manager</th>
                                        <th>Wins</th>
                                        <th>Losses</th>
                                        <th>Win %</th>
                                    </thead>
                                    <tbody>
                                        <?php
                                        foreach ($everyoneRecord as $manager => $array) { ?>
                                            <tr>
                                                <td><?php echo $manager; ?></td>
                                                <td><?php echo $array['wins']; ?></td>
                                                <td><?php echo $array['losses']; ?></td>
                                                <td><?php echo round(($array['wins'] / ($array['wins'] + $array['losses'])) * 100, 1) . ' %'; ?></td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
               
                    <div class="col-sm-12 col-lg-8 table-padding">
                        <div class="card">
                            <div class="card-header">
                                <h4 style="float: right">Standings By Week</h4>
                            </div>
                            <div class="card-body chart-block" style="background: #fff; direction: ltr">
                                <canvas id="standingsChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<script src="/assets/dataTables-fixedColumns.min.js" type="text/javascript"></script>

<script type="text/javascript">
    $(document).ready(function() {

        let baseUrl = "<?php echo $BASE_URL; ?>";
        
        // Initialize schedule DataTable for all weeks
        $('#datatable-schedule').DataTable({
            searching: false,
            paging: false,
            info: false,
            order: []
        });
        
        $('#year-select').change(function() {
            var selectedYear = $('#year-select').val();
            
            // Update week dropdown based on selected year
            $.ajax({
                url: 'dataLookup.php',
                type: 'GET',
                data: {
                    dataType: 'weeks-by-year',
                    year: selectedYear
                },
                success: function(response) {
                    var weeks = JSON.parse(response);
                    var weekSelect = $('#week-select');
                    weekSelect.empty();
                    
                    $.each(weeks, function(index, week) {
                        weekSelect.append('<option value="' + week.value + '">' + week.text + '</option>');
                    });
                    
                    // Auto-select the latest week
                    if (weeks.length > 0) {
                        weekSelect.val(weeks[weeks.length - 1].value);
                    }
                    
                    // Navigate to the new page
                    var selectedWeek = weekSelect.val();
                    window.location = baseUrl+'newsletter.php?year='+selectedYear+'&week='+selectedWeek;
                }
            });
        });
        
        $('#week-select').change(function() {
            var selectedYear = $('#year-select').val();
            var selectedWeek = $('#week-select').val();
            window.location = baseUrl+'newsletter.php?year='+selectedYear+'&week='+selectedWeek;
        });
        
        // Only initialize DataTables if we're not in week 1
        <?php if ($selectedWeek != 1): ?>
        let currentPointsColCount = parseInt("<?php echo $currentPointsColCount; ?>");
        $('#datatable-currentPoints').DataTable({
            searching: false,
            paging: false,
            info: false,
            scrollX: "100%",
            scrollCollapse: true,
            fixedColumns:   {
                leftColumns: 1
            },
            order: [
                [currentPointsColCount+1, "desc"]
            ],
            initComplete: function() {
                var api = this.api();
                
                api.columns(':not(:first)').every(function() {
                    var col = this.index();
                    var array = [];
                    api.cells(null, col).every(function() {
                        var cell = this.node();
                        var record_id = $(cell).attr("data-order");
                        array.push(record_id)
                    })

                    last = array.length-1;
                    array.sort(function(a, b){return b-a});

                    api.cells(null, col).every( function() {
                        var cell = this.node();
                        var record_id = $( cell ).attr( "data-order" );
                        if (record_id === array[0]) {
                            $(this.node()).css('background-color', 'rgb(172, 240, 172)')
                        } else if (record_id === array[last]) {
                            $(this.node()).css('background-color', 'rgba(255, 85, 85, 0.32)')
                        }
                    });
                });
            }
        });

        $('#datatable-currentStats').DataTable({
            searching: false,
            paging: false,
            info: false,
            scrollX: "100%",
            scrollCollapse: true,
            fixedColumns:   {
                left: 1
            },
            order: [
                [2, "desc"]
            ],
            initComplete: function() {
                var api = this.api();
                api.columns(':not(:first)').every(function() {
                    var col = this.index();
                    var data = this.data().unique().map(function(value) {
                        return parseInt(value);
                    }).toArray().sort(function(a, b){return b-a});

                    last = data.length-1;
                    api.cells(null, col).every(function() {
                        var cell = parseInt(this.data());
                        if (cell === data[0]) {
                            $(this.node()).css('background-color', 'rgb(172, 240, 172)')
                        } else if (cell === data[last]) {
                            $(this.node()).css('background-color', 'rgba(255, 85, 85, 0.32)')
                        }
                    });
                });
            }
        });

        $('#datatable-currentWeekStats').DataTable({
            scrollX: "100%",
            searching: false,
            paging: false,
            info: false,
            scrollCollapse: true,
            fixedColumns:   {
                left: 1
            },
            order: [
                [2, "desc"]
            ],
            initComplete: function() {
                var api = this.api();
                api.columns(':not(:first)').every(function() {
                    var col = this.index();
                    var data = this.data().unique().map(function(value) {
                        return parseInt(value);
                    }).toArray().sort(function(a, b){return b-a});

                    last = data.length-1;
                    api.cells(null, col).every(function() {
                        var cell = parseInt(this.data());
                        if (cell === data[0]) {
                            $(this.node()).css('background-color', 'rgb(172, 240, 172)')
                        } else if (cell === data[last]) {
                            $(this.node()).css('background-color', 'rgba(255, 85, 85, 0.32)')
                        }
                    });
                });
            }
        });

        $('#datatable-bestWeek').DataTable({
            searching: false,
            paging: false,
            info: false,
            sort: false,
            scrollX: "100%",
            scrollCollapse: true,
        });

        $('#datatable-bestTeamWeek').DataTable({
            searching: false,
            info: false,
            scrollX: "100%",
            scrollCollapse: true,
            fixedColumns:   {
                left: 1
            },
            order: [
                [0, "desc"]
            ]
        });

        $('#datatable-everyone').DataTable({
            searching: false,
            paging: false,
            info: false,
            order: [
                [3, "desc"]
            ]
        });

        let weeks = <?php echo json_encode($weekStandings['weeks']); ?>;
        let managers = <?php echo json_encode($weekStandings['managers']); ?>;
        
        var ctx = $('#standingsChart');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: weeks,
                datasets: managers
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Rank',
                            font: {
                                size: 20
                            }
                        },
                        reverse: true
                    },
                    x: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Week',
                            font: {
                                size: 20
                            }
                        }
                    }
                }
            }
        });
        <?php endif; ?>
    });
</script>

<style>
    #datatable-currentStats_wrapper, #datatable-statsAgainst_wrapper {
        max-width: 1100px;
    }
    #datatable-drafted_wrapper {
        max-width: 800px;
    }
    #datatable-optimal_wrapper {
        max-width: 1365px;
    }
</style>