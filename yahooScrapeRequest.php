<?php
set_time_limit(300);

include 'yahooSharedFunctions.php';

session_start();

if (isset($APP_ENV) && $APP_ENV === 'production' && empty($_SESSION['admin_auth'])) {
    http_response_code(403);
    echo '<div class="alert alert-danger">Unauthorized.</div>';
    exit;
}

$year = (int)($_POST['year'] ?? 0);
$section = $_POST['section'] ?? '';
$weeks = $_POST['weeks'] ?? [];
$manager = $_POST['manager'] ?? '';

$scraperDir = __DIR__ . '/scraper';
$args = ['--section=' . $section, '--year=' . $year];
if (!empty($weeks)) {
    $args[] = '--weeks=' . implode(',', (array)$weeks);
}
if ($manager !== '') {
    $args[] = '--manager=' . $manager;
}

$nodeBin = '/Users/tyler.jaquish/.nvm/versions/node/v22.23.0/bin/node';
$cmd = escapeshellarg($nodeBin) . ' ' . escapeshellarg($scraperDir . '/scrape.js') . ' ' . implode(' ', array_map('escapeshellarg', $args));

$descriptorSpec = [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
$process = proc_open($cmd, $descriptorSpec, $pipes, $scraperDir);

if (!is_resource($process)) {
    echo '<div class="alert alert-danger">Error: could not start the scraper process.</div>';
    exit;
}

fclose($pipes[0]);
$stdout = stream_get_contents($pipes[1]);
$stderr = stream_get_contents($pipes[2]);
fclose($pipes[1]);
fclose($pipes[2]);
$exitCode = proc_close($process);

if ($exitCode !== 0) {
    $safeError = htmlspecialchars(trim($stderr) ?: 'Unknown scraper error.');
    echo '<div class="alert alert-danger"><strong>Scrape failed for ' . htmlspecialchars($section) . ':</strong> ' . $safeError . '</div>';
    exit;
}

$data = json_decode($stdout, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo '<div class="alert alert-danger">Scraper returned invalid JSON for ' . htmlspecialchars($section) . '.</div>';
    exit;
}

$itemCount = is_array($data) ? count($data) : 1;
echo '<div class="alert alert-success">Scraped ' . htmlspecialchars($section) . ' successfully (' . $itemCount . ' item(s)). Writing this section to the database is added in a follow-up task.</div>';
