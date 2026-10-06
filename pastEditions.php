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

if (!in_array($ip, ['127.0.0.1', '127.0.0.2'], true)) {
    $logEntry = "$timestamp\t$ip\t$visitorId\t$userAgent\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

$pageName = "Past Editions";

// Include functions first to have access to database functions
include_once 'functions.php';
include 'data/pastEditions.php';

$customMetaTitle = "Past Editions | Suntown FFB";
$customMetaDescription = "The best league in all the land";
$customMetaImage = "http://suntownffb.us/images/football.ico";

$editionsByYear = getPastEditions();

include_once 'version.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($customMetaTitle); ?></title>

    <?php if ($APP_ENV === 'production'): ?>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-2B6X5W9X3W"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag() { dataLayer.push(arguments); }
        gtag('js', new Date());
        gtag('config', 'G-2B6X5W9X3W');
    </script>
    <?php endif; ?>

    <meta property="og:title" content="<?php echo htmlspecialchars($customMetaTitle); ?>" />
    <meta property="og:description" content="<?php echo htmlspecialchars($customMetaDescription); ?>" />
    <meta property="og:url" content="https://suntownffb.us/pastEditions.php" />
    <meta property="og:image" content="<?php echo htmlspecialchars($customMetaImage); ?>" />

    <link rel="icon" type="image/png" href="/images/football.ico">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Abril+Fatface&family=Playfair+Display:ital,wght@0,400;0,700;0,900;1,400;1,700&family=Lora:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">

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
        <span>Suntown Fantasy Football League &mdash; Since 2006</span>
    </div>

    <div class="masthead-main">
        <div class="masthead-title">Weekly Sun News</div>
        <div class="masthead-rule-set">
            <span></span>
            <em>Past Editions</em>
            <span></span>
        </div>
    </div>
</div>

<!-- ============================================================
     PAGE BODY
     ============================================================ -->
<div class="newspaper-page">

    <?php if (empty($editionsByYear)): ?>
        <div class="section-label"><span>Past Editions</span></div>
        <div class="not-available">No published editions yet.</div>
    <?php else: ?>
        <?php foreach ($editionsByYear as $year => $editions): ?>
            <div class="section-label accent-label"><span><?php echo $year; ?> Season</span></div>
            <ul class="edition-list">
                <?php foreach ($editions as $edition): ?>
                    <li>
                        <a href="<?php echo htmlspecialchars($edition['url']); ?>">
                            <span class="ed-week">
                                <?php echo $edition['isPlayoff']
                                    ? htmlspecialchars($edition['roundLabel'] ?? ('Week ' . $edition['week']))
                                    : 'Week ' . $edition['week']; ?>
                            </span>
                            <span class="ed-headline">
                                <?php echo htmlspecialchars($edition['headline'] ?: ('Week ' . $edition['week'] . ' Recap')); ?>
                            </span>
                            <?php if (!empty($edition['created_at'])): ?>
                                <?php
                                    $edDate = new DateTime($edition['created_at'], new DateTimeZone('UTC'));
                                    $edDate->setTimezone(new DateTimeZone('America/Los_Angeles'));
                                ?>
                                <span class="ed-date"><?php echo $edDate->format('M j, Y'); ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endforeach; ?>
    <?php endif; ?>

</div><!-- /.newspaper-page -->

<!-- FOOTER -->
<div class="newspaper-footer">
    Copyright <?php echo date("Y"); ?> &copy; Suntown FFB &nbsp;&middot;&nbsp;
    <?php echo $version.' '.$vDate; ?> &nbsp;&middot;&nbsp;
    <a href="/admin.php">Admin</a>
</div>

</body>
</html>
