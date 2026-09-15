<?php
set_time_limit(300);

include 'yahooSharedFunctions.php';

/**
 * Write scraped team_names data to the database. Mirrors yahooApiRequest.php's
 * handle_teams(), but simpler: the JSON from scrape.js is already normalized
 * (one object per team, { yahooTeamId, name, moves, trades }), so this just
 * loops it and writes via updateOrCreate() the same way handle_teams() does.
 *
 * `trades` is nullable: Yahoo's league standings page (the page this section
 * scrapes) has no per-team trades count anywhere in its DOM or network
 * traffic — only a "Moves" column exists (confirmed against a real capture,
 * see team-names-extractor-report.md). When trades is null, it's left out
 * of the update entirely so an existing trades value in the DB is preserved
 * rather than being zeroed out.
 */
function handle_scraped_team_names(array $teams, int $year): int
{
    $written = 0;
    foreach ($teams as $team) {
        $yahooTeamId = (int)($team['yahooTeamId'] ?? 0);
        if ($yahooTeamId <= 0 || !isset($team['name'])) {
            echo 'Skipping malformed scraped team entry.<br>';
            continue;
        }

        $teamName = (string)$team['name'];
        $managerId = lookupManager($yahooTeamId, $year);
        if (!$managerId) {
            echo 'No manager found for Yahoo team ID ' . $yahooTeamId . ', skipping.<br>';
            continue;
        }

        $values = ['name' => $teamName];
        if (isset($team['moves'])) {
            $values['moves'] = (int)$team['moves'];
        }
        if (isset($team['trades']) && $team['trades'] !== null) {
            $values['trades'] = (int)$team['trades'];
        }

        updateOrCreate('team_names', [
            'manager_id' => $managerId,
            'year' => $year,
        ], $values);

        echo $teamName . ' = ' . $yahooTeamId . ' (manager_id ' . $managerId . ')<br>';
        $written++;
    }

    return $written;
}

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

$leagueId = getSeasonLeagueId($year);
if ($leagueId === null) {
    echo '<div class="alert alert-danger">No league_id configured for year ' . htmlspecialchars((string)$year) . '.</div>';
    exit;
}

$scraperDir = __DIR__ . '/scraper';
$args = ['--section=' . $section, '--year=' . $year, '--league-id=' . $leagueId];
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

if ($section === 'team_names') {
    $writtenCount = handle_scraped_team_names($data, $year);
    echo '<div class="alert alert-success">Scraped and saved ' . htmlspecialchars($section) . ' (' . $writtenCount . ' of ' . $itemCount . ' team(s) written).</div>';
} else {
    echo '<div class="alert alert-success">Scraped ' . htmlspecialchars($section) . ' successfully (' . $itemCount . ' item(s)). Writing this section to the database is added in a follow-up task.</div>';
}
