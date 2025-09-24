<?php

$pageName = "Newsletter";

// Include functions first to have access to database functions
include_once 'functions.php';

// Set default year and week if not provided in GET parameters
$currentYear = date('Y');
$selectedSeason = isset($_GET['year']) ? $_GET['year'] : $currentYear;

// Determine current week based on roster data
if (!isset($_GET['week'])) {
    // Get the latest week from rosters table for the current year
    $weekResult = query("SELECT MAX(week) as maxWeek FROM rosters WHERE year = $selectedSeason");
    $weekRow = fetch_array($weekResult);
    
    if ($weekRow && $weekRow['maxWeek']) {
        // Use the week after the latest week in rosters
        $selectedWeek = $weekRow['maxWeek'] + 1;
    } else {
        // No records for current year, default to week 1
        $selectedWeek = 1;
    }
} else {
    $selectedWeek = $_GET['week'];
}

// Set up custom meta properties for newsletter before including header
// Get newsletter metadata image if available
$customMetaTitle = "Week $selectedWeek Newsletter | $selectedSeason Suntown FFB";
$customMetaDescription = "The best league in all the land";
$customMetaImage = "http://suntownffb.us/images/football.ico"; // default

$metaQuery = query("SELECT preview, metadata_image FROM newsletters WHERE year = $selectedSeason AND week = $selectedWeek");
$metaRow = fetch_array($metaQuery);
if ($metaRow) {
    if (!empty($metaRow['preview'])) {
        $cleanPreview = strip_tags($metaRow['preview']);
        $cleanPreview = preg_replace('/\s+/', ' ', trim($cleanPreview));
        if (strlen($cleanPreview) > 160) {
            $cleanPreview = substr($cleanPreview, 0, 157) . '...';
        }
        if (!empty($cleanPreview)) {
            $customMetaDescription = $cleanPreview;
        }
    }
    if (!empty($metaRow['metadata_image'])) {
        // Use absolute URL for meta image
        $customMetaImage = "http://suntownffb.us" . $metaRow['metadata_image'];
    }
}

// Pass $customMetaImage to header.php
include 'header.php';
include 'sidebar.html';

// Check for rosters with the selected year and week
$rosterQuery = query("SELECT * FROM rosters WHERE year = $selectedSeason AND week = $selectedWeek-1");
$rosterData = fetch_array($rosterQuery);
$rosterAvailable = !empty($rosterData);

// Check for newsletter content
$newsletterQuery = query("SELECT * FROM newsletters WHERE year = $selectedSeason AND week = $selectedWeek");
$contentData = fetch_array($newsletterQuery);
$contentAvailable = !empty($contentData);

// If it's week 1, always show content
if ($selectedWeek == 1) {
    $contentAvailable = true;
}

// Get schedule info specifically for this newsletter's selected week
$scheduleInfo = getScheduleInfo($selectedSeason, $selectedWeek);

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

<div class="app-content content">
    <div class="content-wrapper">
        <div class="content-body">
            
            <!-- Dropdown selections -->
            <div class="row" style="direction: ltr;">
                <div class="col-sm-12 col-md-2 text-right">
                    <?php
                    if (isset($APP_ENV) && $APP_ENV !== 'production') {
                        echo '<a class="btn btn-secondary" href="/editNewsletter.php" target="_blank">Edit Newsletter</a>';
                    }
                    ?>
                </div>
                <div class="col-sm-12 col-md-2">
                    <label for="year-select" style="color: #fff;">Season:</label>
                    <select id="year-select" class="form-control">
                        <?php
                        // Use the already defined $currentYear from above
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
                        // Get weeks for the selected season
                        $weeks = [];
                        
                        // First try to get weeks from schedule table
                        $scheduleResult = query("SELECT DISTINCT week FROM schedule WHERE year = $selectedSeason ORDER BY week ASC");
                        while ($row = fetch_array($scheduleResult)) {
                            $weeks[] = $row['week'];
                        }
                        
                        // If no weeks in schedule (especially for current year), check rosters
                        if (empty($weeks)) {
                            $rosterResult = query("SELECT DISTINCT week FROM rosters WHERE year = $selectedSeason ORDER BY week ASC");
                            while ($row = fetch_array($rosterResult)) {
                                $weeks[] = $row['week'];
                            }
                            
                            // For current year, if we have roster data, add the next week too
                            if ($selectedSeason == $currentYear && !empty($weeks)) {
                                $maxWeekResult = query("SELECT MAX(week) as maxWeek FROM rosters WHERE year = $selectedSeason");
                                $maxWeekRow = fetch_array($maxWeekResult);
                                if ($maxWeekRow && isset($maxWeekRow['maxWeek'])) {
                                    $nextWeek = $maxWeekRow['maxWeek'] + 1;
                                    if (!in_array($nextWeek, $weeks)) {
                                        $weeks[] = $nextWeek;
                                    }
                                }
                            }
                        }
                        
                        // If still no weeks found, default to Week 1
                        if (empty($weeks)) {
                            $weeks[] = 1;
                        }
                        
                        // Sort weeks in ascending order
                        sort($weeks);
                        
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
                                <table id="datatable-schedule" class="table table-striped table-bordered table-responsive" style="direction: ltr;">
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
            
            <?php if (!$contentAvailable): ?>
                <!-- Content Not Available Message -->
                <div class="row">
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-header" style="direction: ltr;">
                                <h4>Newsletter Content</h4>
                            </div>
                            <div class="card-body p-1" style="background: #fff; direction: ltr;">
                                <h4 class="alert-heading">Content Not Available</h4>
                                <p>The newsletter for Week <?php echo $selectedWeek; ?> of the <?php echo $selectedSeason; ?> season is not available.</p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php elseif ($selectedWeek == 1): ?>
                <!-- Week 1: Show year recap and preview -->
                <div class="row">
                    <div class="col-sm-12 col-lg-6">
                        <div class="card">
                            <div class="card-header" style="direction: ltr;">
                                <h4><?php echo ($selectedSeason - 1); ?> Recap</h4>
                            </div>
                            <div class="card-body p-1" style="background: #fff; direction: ltr">
                                <?php echo nl2br($recapContent); ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 col-lg-6">
                        <div class="card">
                            <div class="card-header" style="direction: ltr;">
                                <h4>Week <?php echo $selectedWeek; ?> Preview</h4>
                            </div>
                            <div class="card-body p-1" style="background: #fff; direction: ltr">
                                <?php echo nl2br($previewContent); ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- Week 2+: Show week recap and preview -->
                <div class="row">
                    <div class="col-sm-12 col-lg-6">
                        <div class="card">
                            <div class="card-header" style="direction: ltr;">
                                <h4>Week <?php echo ($selectedWeek - 1); ?> Recap</h4>
                            </div>
                            <div class="card-body p-1" style="background: #fff; direction: ltr">
                                <?php echo nl2br($recapContent); ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 col-lg-6">
                        <div class="card">
                            <div class="card-header" style="direction: ltr;">
                                <h4>Week <?php echo $selectedWeek; ?> Preview</h4>
                            </div>
                            <div class="card-body p-1" style="background: #fff; direction: ltr">
                                <?php echo nl2br($previewContent); ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <?php if ($selectedWeek > 1 && $rosterAvailable): ?>
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
                    <?php if ($selectedWeek != 2): ?>
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
                    <?php endif; ?>
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
               
                    <?php if ($selectedWeek != 2): ?>
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
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<script type="text/javascript">
    $(document).ready(function() {

        let baseUrl = "<?php echo $BASE_URL; ?>";
        
        // Year and week selectors
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
        
        // Initialize schedule DataTable for all weeks
        if ($('#datatable-schedule').length) {
            $('#datatable-schedule').DataTable({
                searching: false,
                paging: false,
                info: false,
                order: []
            });
        }
        
        <?php if ($selectedWeek != 1 && $rosterAvailable): ?>
        // Initialize DataTables for weeks with roster data
        
        if ($('#datatable-currentPoints').length) {
            let currentPointsColCount = <?php echo isset($currentPointsColCount) ? 'parseInt("'.$currentPointsColCount.'")' : '0'; ?>;
            $('#datatable-currentPoints').DataTable({
                searching: false,
                paging: false,
                info: false,
                scrollX: "100%",
                scrollCollapse: true,
                fixedColumns: {
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
        }

        <?php if ($selectedWeek != 2): ?>
        if ($('#datatable-currentStats').length) {
            $('#datatable-currentStats').DataTable({
                searching: false,
                paging: false,
                info: false,
                scrollX: "100%",
                scrollCollapse: true,
                fixedColumns: {
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
        }
        <?php endif; ?>

        if ($('#datatable-currentWeekStats').length) {
            $('#datatable-currentWeekStats').DataTable({
                scrollX: "100%",
                searching: false,
                paging: false,
                info: false,
                scrollCollapse: true,
                fixedColumns: {
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
        }

        if ($('#datatable-bestWeek').length) {
            $('#datatable-bestWeek').DataTable({
                searching: false,
                paging: false,
                info: false,
                sort: false,
                scrollX: "100%",
                scrollCollapse: true,
            });
        }

        if ($('#datatable-bestTeamWeek').length) {
            $('#datatable-bestTeamWeek').DataTable({
                searching: false,
                info: false,
                scrollX: "100%",
                scrollCollapse: true,
                fixedColumns: {
                    left: 1
                },
                order: [
                    [0, "desc"]
                ]
            });
        }

        if ($('#datatable-everyone').length) {
            $('#datatable-everyone').DataTable({
                searching: false,
                paging: false,
                info: false,
                order: [
                    [3, "desc"]
                ]
            });
        }

        <?php if (isset($weekStandings) && isset($weekStandings['weeks']) && isset($weekStandings['managers']) && $selectedWeek != 2): ?>
        if ($('#standingsChart').length) {
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
        }
        <?php endif; ?>
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