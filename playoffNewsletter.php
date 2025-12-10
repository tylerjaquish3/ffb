<?php
// Visit tracking
$logFile = __DIR__ . '/visit_log.txt';
$dt = new DateTime('now', new DateTimeZone('America/Los_Angeles'));
$timestamp = $dt->format('Y-m-d H:i:s');
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

// Generate or retrieve visitor ID from cookie
$visitorId = $_COOKIE['visitor_id'] ?? null;
if (!$visitorId) {
    $visitorId = bin2hex(random_bytes(8));
    setcookie('visitor_id', $visitorId, time() + 60*60*24*365, '/'); // 1 year
}

$logEntry = "$timestamp\t$ip\t$visitorId\t$userAgent\n";
file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);

$pageName = "Playoff Newsletter";

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

// Check if selected week is NOT in playoffs and redirect to regular newsletter
$playoffStartWeek = ($selectedSeason >= 2021) ? 15 : 14;
if ($selectedWeek < $playoffStartWeek) {
    // Redirect to regular newsletter with same parameters
    $redirectUrl = 'newsletter.php?year=' . $selectedSeason . '&week=' . $selectedWeek;
    header('Location: ' . $redirectUrl);
    exit;
}

// Determine playoff round name
$weeksSincePlayoffStart = $selectedWeek - $playoffStartWeek;
switch ($weeksSincePlayoffStart) {
    case 0:
        $playoffRound = 'Quarterfinal';
        break;
    case 1:
        $playoffRound = 'Semifinal';
        break;
    case 2:
        $playoffRound = 'Final';
        break;
    default:
        $playoffRound = 'Playoff';
}

// Set up custom meta properties for playoff newsletter before including header
$customMetaTitle = "Week $selectedWeek $playoffRound Newsletter | $selectedSeason Suntown FFB";
$customMetaDescription = "The best league in all the land";
$customMetaImage = "http://suntownffb.us/images/football.ico"; // default

// Pass $customMetaImage to header.php
include 'header.php';
include 'sidebar.php';

// Get playoff schedule info specifically for this newsletter's selected week
$scheduleInfo = getPlayoffScheduleInfo($selectedSeason, $selectedWeek, $playoffRound);

// Get statistics data for playoff newsletter
$topPerformers = getCurrentSeasonTopPerformers();
$everyoneRecord = getRecordAgainstEveryone();

// Fetch newsletter content from database
$recapContent = "Recap content is not available for this week.";
$previewContent = "Preview content is not available for this week.";

// For playoffs, get recap from current week (previous round) and preview for current round
$recapQuery = query("SELECT recap FROM newsletters WHERE year = $selectedSeason AND week = $selectedWeek");
$recapRow = fetch_array($recapQuery);

if ($recapRow && !empty($recapRow['recap'])) {
    $recapContent = $recapRow['recap'];
}

// Get preview for current playoff round
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
                        echo '<a class="btn btn-secondary" href="/editNewsletter.php">Edit Newsletter</a>';
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
                        // Get weeks for the selected season including playoffs
                        $weeks = [];
                        $playoffStartWeek = ($selectedSeason >= 2021) ? 15 : 14;
                        $maxPlayoffWeek = ($selectedSeason >= 2021) ? 17 : 16;
                        
                        // First try to get weeks from schedule table
                        $scheduleResult = query("SELECT DISTINCT week FROM schedule WHERE year = $selectedSeason ORDER BY week ASC");
                        while ($row = fetch_array($scheduleResult)) {
                            if ($row['week'] < $playoffStartWeek) {
                                $weeks[] = $row['week'];
                            }
                        }
                        
                        // If no weeks in schedule (especially for current year), check rosters
                        if (empty($weeks)) {
                            $rosterResult = query("SELECT DISTINCT week FROM rosters WHERE year = $selectedSeason ORDER BY week ASC");
                            while ($row = fetch_array($rosterResult)) {
                                if ($row['week'] < $playoffStartWeek) {
                                    $weeks[] = $row['week'];
                                }
                            }
                            
                            // For current year, if we have roster data, add the next week too (if it's regular season)
                            if ($selectedSeason == $currentYear && !empty($weeks)) {
                                $maxWeekResult = query("SELECT MAX(week) as maxWeek FROM rosters WHERE year = $selectedSeason");
                                $maxWeekRow = fetch_array($maxWeekResult);
                                if ($maxWeekRow && isset($maxWeekRow['maxWeek'])) {
                                    $nextWeek = $maxWeekRow['maxWeek'] + 1;
                                    if (!in_array($nextWeek, $weeks) && $nextWeek < $playoffStartWeek) {
                                        $weeks[] = $nextWeek;
                                    }
                                }
                            }
                        }
                        
                        // Add playoff weeks 
                        for ($playoffWeek = $playoffStartWeek; $playoffWeek <= $maxPlayoffWeek; $playoffWeek++) {
                            $weeks[] = $playoffWeek;
                        }
                        
                        // If still no weeks found, default to Week 1
                        if (empty($weeks)) {
                            $weeks[] = 1;
                        }
                        
                        // Sort weeks in ascending order
                        sort($weeks);
                        
                        // Display week options with playoff round names
                        foreach ($weeks as $week) {
                            $weekLabel = "Week $week";
                            if ($week >= $playoffStartWeek) {
                                $weeksSincePlayoffStart = $week - $playoffStartWeek;
                                switch ($weeksSincePlayoffStart) {
                                    case 0:
                                        $weekLabel = "Week $week (Quarterfinal)";
                                        break;
                                    case 1:
                                        $weekLabel = "Week $week (Semifinal)";
                                        break;
                                    case 2:
                                        $weekLabel = "Week $week (Final)";
                                        break;
                                    default:
                                        $weekLabel = "Week $week";
                                }
                            }
                            
                            if ($week == $selectedWeek) {
                                echo '<option selected value="'.$week.'">'.$weekLabel.'</option>';
                            } else {
                                echo '<option value="'.$week.'">'.$weekLabel.'</option>';
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
                            <h4>Week <?php echo $selectedWeek; ?> Schedule (<?php echo $playoffRound; ?>)</h4>
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
                                                <td>
                                                    <?php if ($matchup['is_bye'] || empty($matchup['manager1_id'])): ?>
                                                        <?php echo htmlspecialchars($matchup['manager1']); ?>
                                                    <?php else: ?>
                                                        <?php 
                                                        $manager1_name = getManagerName($matchup['manager1_id']); 
                                                        ?>
                                                        <a href="profile.php?id=<?php echo urlencode($manager1_name); ?>&versus=<?php echo urlencode($matchup['manager2_id']); ?>" target="_blank" rel="noopener">
                                                            <?php echo htmlspecialchars($matchup['manager1']); ?>
                                                        </a>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($matchup['is_bye'] || empty($matchup['manager2_id'])): ?>
                                                        <?php echo empty($matchup['manager2']) ? '—' : htmlspecialchars($matchup['manager2']); ?>
                                                    <?php else: ?>
                                                        <?php 
                                                        $manager2_name = getManagerName($matchup['manager2_id']); 
                                                        ?>
                                                        <a href="profile.php?id=<?php echo urlencode($manager2_name); ?>&versus=<?php echo urlencode($matchup['manager1_id']); ?>" target="_blank" rel="noopener">
                                                            <?php echo htmlspecialchars($matchup['manager2']); ?>
                                                        </a>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo ($matchup['is_bye'] || empty($matchup['record'])) ? '—' : htmlspecialchars($matchup['record']); ?></td>
                                                <td><?php echo ($matchup['is_bye'] || empty($matchup['postseason_record'])) ? '—' : htmlspecialchars($matchup['postseason_record']); ?></td>
                                                <td><?php echo ($matchup['is_bye'] || empty($matchup['streak'])) ? '—' : htmlspecialchars($matchup['streak']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <p>No playoff schedule information available for the <?php echo $playoffRound; ?> (Week <?php echo $selectedWeek; ?>) of the <?php echo $selectedSeason; ?> season. 
                                <?php if ($playoffRound === 'Quarterfinal'): ?>
                                    This could be because the regular season standings are not yet available.
                                <?php else: ?>
                                    Matchups will be determined based on previous round results.
                                <?php endif; ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Playoff Newsletter Content -->
            <div class="row">
                <div class="col-sm-12 col-lg-6">
                    <div class="card">
                        <div class="card-header" style="direction: ltr;">
                            <h4>
                                <?php 
                                if ($playoffRound === 'Quarterfinal') {
                                    $lastRegularWeek = ($selectedSeason >= 2021) ? 14 : 13;
                                    echo "Week $lastRegularWeek Recap";
                                } else {
                                    echo ($playoffRound === 'Semifinal' ? 'Quarterfinal' : 'Semifinal') . " Recap";
                                }
                                ?>
                            </h4>
                        </div>
                        <div class="card-body p-1" style="background: #fff; direction: ltr">
                            <?php echo nl2br($recapContent); ?>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12 col-lg-6">
                    <div class="card">
                        <div class="card-header" style="direction: ltr;">
                            <h4><?php echo $playoffRound; ?> Preview</h4>
                        </div>
                        <div class="card-body p-1" style="background: #fff; direction: ltr">
                            <?php echo nl2br($previewContent); ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Playoff Statistics Section -->
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
                                    <h5 class="text-bold-400"><?php echo isset($topPerformers['topPerformer']) ? $topPerformers['topPerformer']['manager'].' - Week '.$topPerformers['topPerformer']['week'] : 'N/A'; ?><br />
                                        <?php echo isset($topPerformers['topPerformer']) ? $topPerformers['topPerformer']['player'].' - '.$topPerformers['topPerformer']['points'].' points' : 'Data not available'; ?>
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
                                    <h5 class="text-bold-400"><?php echo isset($topPerformers['bestDraftPick']) ? $topPerformers['bestDraftPick']['manager'] : 'N/A'; ?><br />
                                        <?php echo isset($topPerformers['bestDraftPick']) ? $topPerformers['bestDraftPick']['player'].' - '.$topPerformers['bestDraftPick']['points'].' points' : 'Data not available'; ?>
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
                                    <h5 class="text-bold-400"><?php echo isset($topPerformers['mostTds']) ? $topPerformers['mostTds']['manager'] : 'N/A'; ?><br />
                                        <?php echo isset($topPerformers['mostTds']) ? $topPerformers['mostTds']['points'] : 'Data not available'; ?>
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
                                    <h5 class="text-bold-400"><?php echo isset($topPerformers['mostYds']) ? $topPerformers['mostYds']['manager'] : 'N/A'; ?><br />
                                        <?php echo isset($topPerformers['mostYds']) ? $topPerformers['mostYds']['points'] : 'Data not available'; ?>
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
                                    <h5 class="text-bold-400"><?php echo isset($topPerformers['bestBench']) ? $topPerformers['bestBench']['manager'] : 'N/A'; ?><br />
                                        <?php echo isset($topPerformers['bestBench']) ? $topPerformers['bestBench']['points'] : 'Data not available'; ?>
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
                                    <?php if (isset($everyoneRecord)): ?>
                                        <?php foreach ($everyoneRecord as $manager => $array): ?>
                                            <tr>
                                                <td><?php echo $manager; ?></td>
                                                <td><?php echo $array['wins']; ?></td>
                                                <td><?php echo $array['losses']; ?></td>
                                                <td><?php echo round(($array['wins'] / ($array['wins'] + $array['losses'])) * 100, 1) . ' %'; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4">Data not available</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const yearSelect = document.getElementById('year-select');
        const weekSelect = document.getElementById('week-select');
        
        yearSelect.addEventListener('change', function() {
            const selectedYear = this.value;
            const selectedWeek = weekSelect.value;
            window.location.href = `playoffNewsletter.php?year=${selectedYear}&week=${selectedWeek}`;
        });
        
        weekSelect.addEventListener('change', function() {
            const selectedWeek = this.value;
            const selectedYear = yearSelect.value;
            
            // Determine if this week should go to regular newsletter or playoff newsletter
            const playoffStartWeek = (selectedYear >= 2021) ? 15 : 14;
            
            if (selectedWeek < playoffStartWeek) {
                window.location.href = `newsletter.php?year=${selectedYear}&week=${selectedWeek}`;
            } else {
                window.location.href = `playoffNewsletter.php?year=${selectedYear}&week=${selectedWeek}`;
            }
        });
        
        // Initialize DataTable for Record Against Everyone
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
    });
</script>

<?php include 'footer.php'; ?>