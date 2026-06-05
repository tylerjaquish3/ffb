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
    $weekResult = query("SELECT MAX(week) as maxWeek FROM rosters WHERE year = $selectedSeason");
    $weekRow = fetch_array($weekResult);

    if ($weekRow && $weekRow['maxWeek']) {
        $selectedWeek = $weekRow['maxWeek'] + 1;
    } else {
        $selectedWeek = 1;
    }
} else {
    $selectedWeek = $_GET['week'];
}

// Check if selected week is NOT in playoffs and redirect to regular newsletter
$playoffStartWeek = ($selectedSeason >= 2021) ? 15 : 14;
if ($selectedWeek < $playoffStartWeek) {
    $redirectUrl = 'newsletter.php?year=' . $selectedSeason . '&week=' . $selectedWeek;
    header('Location: ' . $redirectUrl);
    exit;
}

// Determine playoff round name
$weeksSincePlayoffStart = $selectedWeek - $playoffStartWeek;
switch ($weeksSincePlayoffStart) {
    case 0:  $playoffRound = 'Quarterfinal'; break;
    case 1:  $playoffRound = 'Semifinal';    break;
    case 2:  $playoffRound = 'Final';        break;
    default: $playoffRound = 'Playoff';
}

// Meta properties
$customMetaTitle = "Week $selectedWeek $playoffRound Newsletter | $selectedSeason Suntown FFB";
$customMetaDescription = "The best league in all the land";
$customMetaImage = "http://suntownffb.us/images/football.ico";

// Get playoff schedule info
$scheduleInfo = getPlayoffScheduleInfo($selectedSeason, $selectedWeek, $playoffRound);

// Get statistics data
$topPerformers = getCurrentSeasonTopPerformers();
$everyoneRecord = getRecordAgainstEveryone();

// Fetch newsletter content
$recapContent = "Recap content is not available for this week.";
$previewContent = "Preview content is not available for this week.";
$newsletterDate = null;

$recapQuery = query("SELECT recap, preview, created_at FROM newsletters WHERE year = $selectedSeason AND week = $selectedWeek");
$recapRow = fetch_array($recapQuery);
if ($recapRow) {
    if (!empty($recapRow['recap'])) {
        $recapContent = $recapRow['recap'];
    }
    if (!empty($recapRow['preview'])) {
        $previewContent = $recapRow['preview'];
    }
    if (!empty($recapRow['created_at'])) {
        $newsletterDate = new DateTime($recapRow['created_at'], new DateTimeZone('UTC'));
        $newsletterDate->setTimezone(new DateTimeZone('America/Los_Angeles'));
    }
}

// Recap headline label
if ($playoffRound === 'Quarterfinal') {
    $lastRegularWeek = ($selectedSeason >= 2021) ? 14 : 13;
    $recapLabel = "Week $lastRegularWeek Recap";
} else {
    $recapLabel = ($playoffRound === 'Semifinal' ? 'Quarterfinal' : 'Semifinal') . " Recap";
}

$version = "v5.7.1";
$vDate = "(05/19/26)";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($customMetaTitle); ?></title>

    <meta property="og:title" content="<?php echo htmlspecialchars($customMetaTitle); ?>" />
    <meta property="og:description" content="<?php echo htmlspecialchars($customMetaDescription); ?>" />
    <meta property="og:url" content="http://suntownffb.us/playoffNewsletter.php" />
    <meta property="og:image" content="<?php echo htmlspecialchars($customMetaImage); ?>" />

    <link rel="icon" type="image/png" href="/images/football.ico">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Abril+Fatface&family=Playfair+Display:ital,wght@0,400;0,700;0,900;1,400;1,700&family=Lora:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="/assets/datatables.min.css">
    <link rel="stylesheet" href="/assets/icomoon.css">
    <link rel="stylesheet" href="/assets/newsletter.css">
</head>

<body>

<!-- MASTHEAD -->
<div class="masthead-wrapper">
    <div class="masthead-top-bar">
        <a href="/">&larr; Back to Dashboard</a>
        <span>
            <?php
                $dayNames = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
                $monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];
                $displayDate = $newsletterDate ?? new DateTime('now', new DateTimeZone('America/Los_Angeles'));
                echo $dayNames[(int)$displayDate->format('w')] . ', ' . $monthNames[(int)$displayDate->format('n')-1] . ' ' . $displayDate->format('j') . ', ' . $displayDate->format('Y');
            ?>
        </span>
        <span>Suntown Fantasy Football League &mdash; Since 2006</span>
    </div>

    <div class="masthead-main">
        <div class="masthead-title">Weekly Sun News</div>
        <div class="masthead-rule-set">
            <span></span>
            <em>Official Fantasy Football Newsletter</em>
            <span></span>
        </div>
    </div>
</div>

<!-- Edition selector -->
<div class="edition-bar">
    <span style="opacity:0.5;letter-spacing:0.2em;font-size:0.62rem;text-transform:uppercase;">Edition:</span>
    <div class="edition-field">
        <label for="year-select">Season</label>
        <select id="year-select">
            <?php
            $result = query("SELECT DISTINCT year FROM rosters ORDER BY year DESC");
            $yearsInDB = array();
            while ($row = fetch_array($result)) {
                $yearsInDB[] = $row['year'];
            }
            if (!in_array($currentYear, $yearsInDB)) {
                array_unshift($yearsInDB, $currentYear);
            }
            rsort($yearsInDB);
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
    <div class="edition-field">
        <label for="week-select">Week</label>
        <select id="week-select">
            <?php
            $weeks = [];
            $maxPlayoffWeek = ($selectedSeason >= 2021) ? 17 : 16;

            $scheduleResult = query("SELECT DISTINCT week FROM schedule WHERE year = $selectedSeason ORDER BY week ASC");
            while ($row = fetch_array($scheduleResult)) {
                if ($row['week'] < $playoffStartWeek) {
                    $weeks[] = $row['week'];
                }
            }

            if (empty($weeks)) {
                $rosterResult = query("SELECT DISTINCT week FROM rosters WHERE year = $selectedSeason ORDER BY week ASC");
                while ($row = fetch_array($rosterResult)) {
                    if ($row['week'] < $playoffStartWeek) {
                        $weeks[] = $row['week'];
                    }
                }
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

            for ($playoffWeek = $playoffStartWeek; $playoffWeek <= $maxPlayoffWeek; $playoffWeek++) {
                $weeks[] = $playoffWeek;
            }

            if (empty($weeks)) { $weeks[] = 1; }
            sort($weeks);

            foreach ($weeks as $week) {
                $weekLabel = "Week $week";
                if ($week >= $playoffStartWeek) {
                    $wk = $week - $playoffStartWeek;
                    switch ($wk) {
                        case 0: $weekLabel = "Week $week (Quarterfinal)"; break;
                        case 1: $weekLabel = "Week $week (Semifinal)";    break;
                        case 2: $weekLabel = "Week $week (Final)";        break;
                        default: $weekLabel = "Week $week";
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

<!-- PAGE BODY -->
<div class="newspaper-page">

    <!-- SCHEDULE -->
    <div class="section-label accent-label"><span><?php echo $playoffRound; ?> Matchups &mdash; Week <?php echo $selectedWeek; ?></span></div>
    <div class="schedule-section box-score-wrapper">
        <?php if (!empty($scheduleInfo)): ?>
            <table id="datatable-schedule" class="box-score-table">
                <thead>
                    <tr>
                        <th>Manager</th>
                        <th class="vs-col">&mdash;</th>
                        <th>Opponent</th>
                        <th>H2H (Reg)</th>
                        <th>H2H (Post)</th>
                        <th>Streak</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($scheduleInfo as $matchup): ?>
                        <tr>
                            <td>
                                <?php if ($matchup['is_bye'] || empty($matchup['manager1_id'])): ?>
                                    <?php echo htmlspecialchars($matchup['manager1']); ?>
                                <?php else: ?>
                                    <?php $manager1_name = getManagerName($matchup['manager1_id']); ?>
                                    <a href="profile.php?id=<?php echo urlencode($manager1_name); ?>&versus=<?php echo urlencode($matchup['manager2_id']); ?>" target="_blank" rel="noopener">
                                        <?php echo htmlspecialchars($matchup['manager1']); ?>
                                    </a>
                                <?php endif; ?>
                            </td>
                            <td class="vs-col">vs</td>
                            <td>
                                <?php if ($matchup['is_bye'] || empty($matchup['manager2_id'])): ?>
                                    <?php echo empty($matchup['manager2']) ? '&mdash;' : htmlspecialchars($matchup['manager2']); ?>
                                <?php else: ?>
                                    <?php $manager2_name = getManagerName($matchup['manager2_id']); ?>
                                    <a href="profile.php?id=<?php echo urlencode($manager2_name); ?>&versus=<?php echo urlencode($matchup['manager1_id']); ?>" target="_blank" rel="noopener">
                                        <?php echo htmlspecialchars($matchup['manager2']); ?>
                                    </a>
                                <?php endif; ?>
                            </td>
                            <td><?php echo ($matchup['is_bye'] || empty($matchup['record'])) ? '&mdash;' : htmlspecialchars($matchup['record']); ?></td>
                            <td><?php echo ($matchup['is_bye'] || empty($matchup['postseason_record'])) ? '&mdash;' : htmlspecialchars($matchup['postseason_record']); ?></td>
                            <td><?php echo ($matchup['is_bye'] || empty($matchup['streak'])) ? '&mdash;' : htmlspecialchars($matchup['streak']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="not-available">
                No playoff schedule information available for the <?php echo $playoffRound; ?> (Week <?php echo $selectedWeek; ?>) of the <?php echo $selectedSeason; ?> season.
                <?php if ($playoffRound === 'Quarterfinal'): ?>
                    Regular season standings may not yet be finalized.
                <?php else: ?>
                    Matchups will be determined based on previous round results.
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- RECAP + PREVIEW -->
    <div class="section-label accent-label"><span><?php echo $recapLabel; ?> &amp; <?php echo $playoffRound; ?> Preview</span></div>
    <div class="content-columns">
        <div class="column-divider">
            <div class="article-headline"><?php echo $recapLabel; ?></div>
            <div class="article-byline">By the Weekly Sun News Editorial Staff &middot; <?php echo $selectedSeason; ?></div>
            <div class="article-body has-dropcap"><?php echo nl2br($recapContent); ?></div>
        </div>
        <div class="preview-column">
            <div class="article-headline"><?php echo $playoffRound; ?> Preview</div>
            <div class="article-byline">What to Watch &middot; <?php echo $selectedSeason; ?></div>
            <div class="article-body"><?php echo nl2br($previewContent); ?></div>
        </div>
    </div>

    <!-- TOP PERFORMERS + RECORD AGAINST EVERYONE -->
    <div class="section-label"><span>Season Leaders &amp; Standings</span></div>
    <div class="stats-grid">
        <!-- Top Performers Sidebar -->
        <div>
            <div class="section-label accent-label" style="margin-top:0;"><span>Top Performers</span></div>
            <ul class="performers-list">
                <li>
                    <div class="perf-icon"><i class="icon-coin-dollar"></i></div>
                    <div>
                        <div class="perf-label">Top Week Performance</div>
                        <div class="perf-manager"><?php echo isset($topPerformers['topPerformer']) ? htmlspecialchars($topPerformers['topPerformer']['manager']).' &mdash; Wk '.$topPerformers['topPerformer']['week'] : 'N/A'; ?></div>
                        <div class="perf-detail"><?php echo isset($topPerformers['topPerformer']) ? htmlspecialchars($topPerformers['topPerformer']['player']).' &mdash; '.$topPerformers['topPerformer']['points'].' pts' : 'Data not available'; ?></div>
                    </div>
                </li>
                <li>
                    <div class="perf-icon"><i class="icon-clipboard"></i></div>
                    <div>
                        <div class="perf-label">Best Draft Pick</div>
                        <div class="perf-manager"><?php echo isset($topPerformers['bestDraftPick']) ? htmlspecialchars($topPerformers['bestDraftPick']['manager']) : 'N/A'; ?></div>
                        <div class="perf-detail"><?php echo isset($topPerformers['bestDraftPick']) ? htmlspecialchars($topPerformers['bestDraftPick']['player']).' &mdash; '.$topPerformers['bestDraftPick']['points'].' pts' : 'Data not available'; ?></div>
                    </div>
                </li>
                <li>
                    <div class="perf-icon"><i class="icon-flag"></i></div>
                    <div>
                        <div class="perf-label">Most Total TDs (incl. BN)</div>
                        <div class="perf-manager"><?php echo isset($topPerformers['mostTds']) ? htmlspecialchars($topPerformers['mostTds']['manager']) : 'N/A'; ?></div>
                        <div class="perf-detail"><?php echo isset($topPerformers['mostTds']) ? $topPerformers['mostTds']['points'] : 'Data not available'; ?></div>
                    </div>
                </li>
                <li>
                    <div class="perf-icon"><i class="icon-earth"></i></div>
                    <div>
                        <div class="perf-label">Most Total Yards (incl. BN)</div>
                        <div class="perf-manager"><?php echo isset($topPerformers['mostYds']) ? htmlspecialchars($topPerformers['mostYds']['manager']) : 'N/A'; ?></div>
                        <div class="perf-detail"><?php echo isset($topPerformers['mostYds']) ? $topPerformers['mostYds']['points'] : 'Data not available'; ?></div>
                    </div>
                </li>
                <li>
                    <div class="perf-icon"><i class="icon-power-cord"></i></div>
                    <div>
                        <div class="perf-label">Best Bench</div>
                        <div class="perf-manager"><?php echo isset($topPerformers['bestBench']) ? htmlspecialchars($topPerformers['bestBench']['manager']) : 'N/A'; ?></div>
                        <div class="perf-detail"><?php echo isset($topPerformers['bestBench']) ? $topPerformers['bestBench']['points'] : 'Data not available'; ?></div>
                    </div>
                </li>
            </ul>
        </div>

        <!-- Record Against Everyone -->
        <div>
            <div class="section-label accent-label" style="margin-top:0;"><span>Record Against Everyone</span></div>
            <table class="news-table" id="datatable-everyone">
                <thead>
                    <tr>
                        <th>Manager</th>
                        <th>W</th>
                        <th>L</th>
                        <th>Win %</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($everyoneRecord)): ?>
                        <?php foreach ($everyoneRecord as $manager => $array): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($manager); ?></td>
                                <td><?php echo $array['wins']; ?></td>
                                <td><?php echo $array['losses']; ?></td>
                                <td><?php echo round(($array['wins'] / ($array['wins'] + $array['losses'])) * 100, 1) . '%'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4">Data not available</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div><!-- /.newspaper-page -->

<!-- FOOTER -->
<div class="newspaper-footer">
    Copyright <?php echo date("Y"); ?> &copy; Suntown FFB &nbsp;&middot;&nbsp;
    <?php echo $version.' '.$vDate; ?> &nbsp;&middot;&nbsp;
    <a href="/admin.php">Admin</a>
</div>

<script src="/assets/datatables.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const yearSelect = document.getElementById('year-select');
    const weekSelect = document.getElementById('week-select');

    yearSelect.addEventListener('change', function() {
        const selectedYear = this.value;
        const selectedWeek = weekSelect.value;
        const playoffStartWeek = (selectedYear >= 2021) ? 15 : 14;
        if (selectedWeek < playoffStartWeek) {
            window.location.href = `newsletter.php?year=${selectedYear}&week=${selectedWeek}`;
        } else {
            window.location.href = `playoffNewsletter.php?year=${selectedYear}&week=${selectedWeek}`;
        }
    });

    weekSelect.addEventListener('change', function() {
        const selectedWeek = this.value;
        const selectedYear = yearSelect.value;
        const playoffStartWeek = (selectedYear >= 2021) ? 15 : 14;
        if (selectedWeek < playoffStartWeek) {
            window.location.href = `newsletter.php?year=${selectedYear}&week=${selectedWeek}`;
        } else {
            window.location.href = `playoffNewsletter.php?year=${selectedYear}&week=${selectedWeek}`;
        }
    });

    if ($('#datatable-schedule').length) {
        $('#datatable-schedule').DataTable({
            searching: false,
            paging: false,
            info: false,
            order: []
        });
    }

    if ($('#datatable-everyone').length) {
        $('#datatable-everyone').DataTable({
            searching: false,
            paging: false,
            info: false,
            order: [[3, "desc"]]
        });
    }
});
</script>

</body>
</html>
