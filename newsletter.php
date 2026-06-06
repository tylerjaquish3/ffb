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

// Check if selected week is in playoffs and redirect to playoff newsletter
$playoffStartWeek = ($selectedSeason >= 2021) ? 15 : 14;
if ($selectedWeek >= $playoffStartWeek) {
    // Redirect to playoff newsletter with same parameters
    $redirectUrl = 'playoffNewsletter.php?year=' . $selectedSeason . '&week=' . $selectedWeek;
    header('Location: ' . $redirectUrl);
    exit;
}

// Set up custom meta properties for newsletter before including header
// Get newsletter metadata image if available
$customMetaTitle = "Week $selectedWeek Newsletter | $selectedSeason Suntown FFB";
$customMetaDescription = "The best league in all the land";
$customMetaImage = "http://suntownffb.us/images/football.ico"; // default

$metaQuery = query("SELECT recap, metadata_image, headline, hero_image, created_at FROM newsletters WHERE year = $selectedSeason AND week = $selectedWeek");
$metaRow = fetch_array($metaQuery);
$newsletterDate = null;
$newsletterHeadline = null;
$heroImage = null;
if ($metaRow) {
    if (!empty($metaRow['recap'])) {
        $cleanRecap = strip_tags($metaRow['recap']);
        $cleanRecap = preg_replace('/\s+/', ' ', trim($cleanRecap));
        if (strlen($cleanRecap) > 160) {
            $cleanRecap = substr($cleanRecap, 0, 157) . '...';
        }
        if (!empty($cleanRecap)) {
            $customMetaDescription = $cleanRecap;
        }
    }
    if (!empty($metaRow['metadata_image'])) {
        $customMetaImage = "http://suntownffb.us" . $metaRow['metadata_image'];
    }
    if (!empty($metaRow['created_at'])) {
        $newsletterDate = new DateTime($metaRow['created_at'], new DateTimeZone('UTC'));
        $newsletterDate->setTimezone(new DateTimeZone('America/Los_Angeles'));
    }
    $newsletterHeadline = !empty($metaRow['headline']) ? $metaRow['headline'] : null;
    $heroImage = !empty($metaRow['hero_image']) ? $metaRow['hero_image'] : null;
}

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
    <meta property="og:url" content="http://suntownffb.us/newsletter.php" />
    <meta property="og:image" content="<?php echo htmlspecialchars($customMetaImage); ?>" />

    <link rel="icon" type="image/png" href="/images/football.ico">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Abril+Fatface&family=Playfair+Display:ital,wght@0,400;0,700;0,900;1,400;1,700&family=Lora:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">

    <!-- DataTables CSS only (needed for table functionality) -->
    <link rel="stylesheet" href="/assets/datatables.min.css">
    <link rel="stylesheet" href="/assets/icomoon.css">
    <link rel="stylesheet" href="/assets/newsletter.css">
</head>

<body>

<!-- ============================================================
     MASTHEAD
     ============================================================ -->
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
            $playoffStartWeek = ($selectedSeason >= 2021) ? 15 : 14;
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

            if (empty($weeks)) {
                $weeks[] = 1;
            }
            sort($weeks);

            foreach ($weeks as $week) {
                $weekLabel = "Week $week";
                if ($week >= $playoffStartWeek) {
                    $weeksSincePlayoffStart = $week - $playoffStartWeek;
                    switch ($weeksSincePlayoffStart) {
                        case 0: $weekLabel = "Week $week (Quarterfinal)"; break;
                        case 1: $weekLabel = "Week $week (Semifinal)"; break;
                        case 2: $weekLabel = "Week $week (Final)"; break;
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

<!-- ============================================================
     PAGE BODY
     ============================================================ -->
<div class="newspaper-page">

    <?php if ($heroImage): ?>
        <div class="hero-image-wrapper">
            <img src="<?php echo htmlspecialchars($heroImage); ?>" alt="">
        </div>
    <?php endif; ?>
    <?php if ($newsletterHeadline): ?>
        <h1 class="newsletter-headline"><?php echo htmlspecialchars($newsletterHeadline); ?></h1>
    <?php endif; ?>

    <!-- RECAP (above matchups) -->
    <?php if (!$contentAvailable): ?>
        <div class="section-label"><span>Newsletter</span></div>
        <div class="not-available">
            The newsletter for Week <?php echo $selectedWeek; ?> of the <?php echo $selectedSeason; ?> season is not yet available.
        </div>
    <?php elseif ($selectedWeek == 1): ?>
        <div class="section-label accent-label"><span><?php echo ($selectedSeason - 1); ?> Season Recap</span></div>
        <div class="article-full">
            <div class="article-headline"><?php echo ($selectedSeason - 1); ?> Season &mdash; Year in Review</div>
            <div class="article-byline">By the Editorial Staff &middot; <?php echo $selectedSeason; ?></div>
            <div class="article-body has-dropcap"><?php echo nl2br($recapContent); ?></div>
        </div>
    <?php else: ?>
        <div class="section-label accent-label"><span>Week <?php echo ($selectedWeek - 1); ?> Recap</span></div>
        <div class="article-full">
            <div class="article-headline">Week <?php echo ($selectedWeek - 1); ?> &mdash; Recap</div>
            <div class="article-byline">By the Editorial Staff &middot; <?php echo $selectedSeason; ?></div>
            <div class="article-body has-dropcap"><?php echo nl2br($recapContent); ?></div>
        </div>
    <?php endif; ?>

    <!-- SCHEDULE -->
    <div class="section-label accent-label"><span>This Week&rsquo;s Matchups</span></div>
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
                                <a href="profile.php?id=<?php echo urlencode($matchup['manager1_clean'] ?? $matchup['manager1']); ?>&versus=<?php echo urlencode($matchup['manager2_id']); ?>" target="_blank" rel="noopener">
                                    <?php echo htmlspecialchars($matchup['manager1']); ?>
                                </a>
                            </td>
                            <td class="vs-col">vs</td>
                            <td>
                                <a href="profile.php?id=<?php echo urlencode($matchup['manager2_clean'] ?? $matchup['manager2']); ?>&versus=<?php echo urlencode($matchup['manager1_id']); ?>" target="_blank" rel="noopener">
                                    <?php echo htmlspecialchars($matchup['manager2']); ?>
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars($matchup['record']); ?></td>
                            <td><?php echo htmlspecialchars($matchup['postseason_record']); ?></td>
                            <td><?php echo htmlspecialchars($matchup['streak']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="not-available">No schedule information available for Week <?php echo $selectedWeek; ?> of the <?php echo $selectedSeason; ?> season.</div>
        <?php endif; ?>
    </div>

    <!-- PREVIEW (below matchups) -->
    <?php if ($contentAvailable): ?>
        <?php if ($selectedWeek == 1): ?>
            <div class="section-label accent-label"><span><?php echo $selectedSeason; ?> Season Preview</span></div>
            <div class="article-full">
                <div class="article-headline">Week <?php echo $selectedWeek; ?> Preview</div>
                <div class="article-byline">Looking Ahead</div>
                <div class="article-body has-dropcap"><?php echo nl2br($previewContent); ?></div>
            </div>
        <?php else: ?>
            <div class="section-label accent-label"><span>Week <?php echo $selectedWeek; ?> Preview</span></div>
            <div class="article-full">
                <div class="article-headline">Week <?php echo $selectedWeek; ?> Preview</div>
                <div class="article-byline">What to Watch</div>
                <div class="article-body has-dropcap"><?php echo nl2br($previewContent); ?></div>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if ($selectedWeek > 1 && $rosterAvailable): ?>

        <!-- TOP PERFORMERS + RECORD AGAINST EVERYONE -->
        <div class="section-label"><span>League Standings &amp; Performers</span></div>
        <div class="stats-grid">
            <!-- Top Performers Sidebar -->
            <div>
                <div class="section-label accent-label" style="margin-top:0;"><span>Top Performers</span></div>
                <ul class="performers-list">
                    <li>
                        <div class="perf-icon"><i class="icon-coin-dollar"></i></div>
                        <div>
                            <div class="perf-label">Top Week Performance</div>
                            <div class="perf-manager"><?php echo $topPerformers['topPerformer']['manager'].' &mdash; Wk '.$topPerformers['topPerformer']['week']; ?></div>
                            <div class="perf-detail"><?php echo $topPerformers['topPerformer']['player'].' &mdash; '.$topPerformers['topPerformer']['points'].' pts'; ?></div>
                        </div>
                    </li>
                    <li>
                        <div class="perf-icon"><i class="icon-clipboard"></i></div>
                        <div>
                            <div class="perf-label">Best Draft Pick</div>
                            <div class="perf-manager"><?php echo $topPerformers['bestDraftPick']['manager']; ?></div>
                            <div class="perf-detail"><?php echo $topPerformers['bestDraftPick']['player'].' &mdash; '.$topPerformers['bestDraftPick']['points'].' pts'; ?></div>
                        </div>
                    </li>
                    <li>
                        <div class="perf-icon"><i class="icon-flag"></i></div>
                        <div>
                            <div class="perf-label">Most Total TDs (incl. BN)</div>
                            <div class="perf-manager"><?php echo $topPerformers['mostTds']['manager']; ?></div>
                            <div class="perf-detail"><?php echo $topPerformers['mostTds']['points']; ?></div>
                        </div>
                    </li>
                    <li>
                        <div class="perf-icon"><i class="icon-earth"></i></div>
                        <div>
                            <div class="perf-label">Most Total Yards (incl. BN)</div>
                            <div class="perf-manager"><?php echo $topPerformers['mostYds']['manager']; ?></div>
                            <div class="perf-detail"><?php echo $topPerformers['mostYds']['points']; ?></div>
                        </div>
                    </li>
                    <li>
                        <div class="perf-icon"><i class="icon-power-cord"></i></div>
                        <div>
                            <div class="perf-label">Best Bench</div>
                            <div class="perf-manager"><?php echo $topPerformers['bestBench']['manager']; ?></div>
                            <div class="perf-detail"><?php echo $topPerformers['bestBench']['points']; ?></div>
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
                        <?php foreach ($everyoneRecord as $manager => $array) { ?>
                            <tr>
                                <td><?php echo $manager; ?></td>
                                <td><?php echo $array['wins']; ?></td>
                                <td><?php echo $array['losses']; ?></td>
                                <td><?php echo round(($array['wins'] / ($array['wins'] + $array['losses'])) * 100, 1) . '%'; ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- WEEK TOP PERFORMERS CARDS -->
        <div class="section-label accent-label"><span>Week <?php echo $selectedWeek - 1; ?> Top Performers by Position</span></div>
        <div class="position-cards">
            <?php
            foreach ($bestWeek as $week => $players) {
                if ($week != ($selectedWeek - 1)) { continue; }
                foreach ($players as $pos => $stuff) { ?>
                    <div class="pos-card">
                        <div class="pos-card-label"><?php echo htmlspecialchars($pos); ?></div>
                        <div class="pos-card-manager"><?php echo htmlspecialchars($stuff['manager']); ?></div>
                        <div class="pos-card-player"><?php echo htmlspecialchars($stuff['player']); ?></div>
                        <div class="pos-card-points"><?php echo $stuff['points']; ?> <span>pts</span></div>
                    </div>
                <?php }
            }
            ?>
        </div>

        <!-- SEASON STATS + WEEK STATS -->
        <?php if ($selectedWeek != 2): ?>
            <div class="section-label"><span>Season Stats</span></div>
            <div class="full-table-section">
                <table class="news-table" id="datatable-currentStats">
                    <thead>
                        <tr>
                            <th>Manager</th>
                            <th>Tot Yds</th>
                            <th>Tot TDs</th>
                            <th>Pass Yds</th>
                            <th>Pass TDs</th>
                            <th>INTs</th>
                            <th>Rush Yds</th>
                            <th>Rush TDs</th>
                            <th>Rec</th>
                            <th>Rec Yds</th>
                            <th>Rec TDs</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = fetch_array($stats)) { ?>
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
        <?php endif; ?>

        <div class="section-label"><span>Week <?php echo $selectedWeek - 1; ?> Stats</span></div>
        <div class="full-table-section">
            <table class="news-table" id="datatable-currentWeekStats">
                <thead>
                    <tr>
                        <th>Manager</th>
                        <th>Tot Yds</th>
                        <th>Tot TDs</th>
                        <th>Pass Yds</th>
                        <th>Pass TDs</th>
                        <th>INTs</th>
                        <th>Rush Yds</th>
                        <th>Rush TDs</th>
                        <th>Rec</th>
                        <th>Rec Yds</th>
                        <th>Rec TDs</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = fetch_array($weekStats)) {
                        if ($row['week'] != ($selectedWeek - 1)) { continue; }
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

        <!-- STANDINGS CHART -->
        <?php if ($selectedWeek != 2): ?>
            <div class="section-label accent-label"><span>Standings By Week</span></div>
            <div class="chart-section">
                <div class="chart-canvas-wrapper">
                    <canvas id="standingsChart"></canvas>
                </div>
            </div>
        <?php endif; ?>

    <?php endif; ?>

</div><!-- /.newspaper-page -->

<!-- FOOTER -->
<div class="newspaper-footer">
    Copyright <?php echo date("Y"); ?> &copy; Suntown FFB &nbsp;&middot;&nbsp;
    <?php echo $version.' '.$vDate; ?> &nbsp;&middot;&nbsp;
    <a href="/admin.php">Admin</a>
</div>

<!-- Scripts -->
<script src="/assets/datatables.min.js"></script>
<script src="/assets/chart.min.js"></script>
<script src="/assets/chartjs-plugin-datalabels.min.js"></script>

<script type="text/javascript">
$(document).ready(function() {

    let baseUrl = "<?php echo $BASE_URL; ?>";

    // Year and week selectors
    $('#year-select').change(function() {
        var selectedYear = $('#year-select').val();
        $.ajax({
            url: 'dataLookup.php',
            type: 'GET',
            data: { dataType: 'weeks-by-year', year: selectedYear },
            success: function(response) {
                var weeks = JSON.parse(response);
                var weekSelect = $('#week-select');
                weekSelect.empty();
                $.each(weeks, function(index, week) {
                    weekSelect.append('<option value="' + week.value + '">' + week.text + '</option>');
                });
                if (weeks.length > 0) {
                    weekSelect.val(weeks[weeks.length - 1].value);
                }
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

    // Schedule table
    if ($('#datatable-schedule').length) {
        $('#datatable-schedule').DataTable({
            searching: false,
            paging: false,
            info: false,
            order: []
        });
    }

    <?php if ($selectedWeek != 1 && $rosterAvailable): ?>

        if ($('#datatable-currentPoints').length) {
            let currentPointsColCount = <?php echo isset($currentPointsColCount) ? 'parseInt("'.$currentPointsColCount.'")' : '0'; ?>;
            $('#datatable-currentPoints').DataTable({
                searching: false,
                paging: false,
                info: false,
                scrollX: "100%",
                scrollCollapse: true,
                fixedColumns: { leftColumns: 1 },
                order: [[currentPointsColCount+1, "desc"]],
                initComplete: function() {
                    var api = this.api();
                    api.columns(':not(:first)').every(function() {
                        var col = this.index();
                        var array = [];
                        api.cells(null, col).every(function() {
                            array.push($(this.node()).attr("data-order"));
                        });
                        last = array.length-1;
                        array.sort(function(a,b){return b-a});
                        api.cells(null, col).every(function() {
                            var record_id = $(this.node()).attr("data-order");
                            if (record_id === array[0]) {
                                $(this.node()).css('background-color', 'rgb(172, 240, 172)');
                            } else if (record_id === array[last]) {
                                $(this.node()).css('background-color', 'rgba(255, 85, 85, 0.32)');
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
                    fixedColumns: { left: 1 },
                    order: [[2, "desc"]],
                    initComplete: function() {
                        var api = this.api();
                        api.columns(':not(:first)').every(function() {
                            var col = this.index();
                            var data = this.data().unique().map(function(v){ return parseInt(v); }).toArray().sort(function(a,b){return b-a});
                            last = data.length-1;
                            api.cells(null, col).every(function() {
                                var cell = parseInt(this.data());
                                if (cell === data[0]) { $(this.node()).css('background-color', 'rgb(172, 240, 172)'); }
                                else if (cell === data[last]) { $(this.node()).css('background-color', 'rgba(255, 85, 85, 0.32)'); }
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
                fixedColumns: { left: 1 },
                order: [[2, "desc"]],
                initComplete: function() {
                    var api = this.api();
                    api.columns(':not(:first)').every(function() {
                        var col = this.index();
                        var data = this.data().unique().map(function(v){ return parseInt(v); }).toArray().sort(function(a,b){return b-a});
                        last = data.length-1;
                        api.cells(null, col).every(function() {
                            var cell = parseInt(this.data());
                            if (cell === data[0]) { $(this.node()).css('background-color', 'rgb(172, 240, 172)'); }
                            else if (cell === data[last]) { $(this.node()).css('background-color', 'rgba(255, 85, 85, 0.32)'); }
                        });
                    });
                }
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

        <?php if (isset($weekStandings) && isset($weekStandings['weeks']) && isset($weekStandings['managers']) && $selectedWeek != 2): ?>
            if ($('#standingsChart').length) {
                let weeks = <?php echo json_encode($weekStandings['weeks']); ?>;
                let managers = <?php echo json_encode($weekStandings['managers']); ?>;
                var ctx = $('#standingsChart');
                new Chart(ctx, {
                    type: 'line',
                    data: { labels: weeks, datasets: managers },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                display: true,
                                title: { display: true, text: 'Rank', font: { size: 20 } },
                                reverse: true
                            },
                            x: {
                                display: true,
                                title: { display: true, text: 'Week', font: { size: 20 } }
                            }
                        }
                    }
                });
            }
        <?php endif; ?>

    <?php endif; ?>

    // Handle dropdown navigation
    const yearSelect = document.getElementById('year-select');
    const weekSelect = document.getElementById('week-select');

    if (yearSelect && weekSelect) {
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
    }
});
</script>

</body>
</html>
