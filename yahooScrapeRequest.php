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

/**
 * Write scraped matchups data to the database. Mirrors yahooApiRequest.php's
 * handle_team_matchups(), but works from scrape.js's already-normalized
 * per-matchup JSON (one object per REAL matchup, both sides already present
 * — { week, team1: { yahooTeamId, name, score, projected }, team2: {...} })
 * rather than the API's per-manager loop. Because both sides are already
 * known, this writes BOTH `regular_season_matchups` rows for each matchup
 * itself (each manager as manager1 once), instead of relying on being
 * called once per manager the way handle_team_matchups() is.
 *
 * `projected` is carried through as a real number, not nulled out: unlike
 * team_names' missing trades count, Yahoo's website DOES keep showing a
 * matchup's original projected points ("Orig Proj") even after the week is
 * final — confirmed against a real logged-in capture of both the
 * all-matchups module and an individual matchup/recap page agreeing on the
 * same figures. See matchups-extractor-report.md for the real verification
 * run before this was trusted against the standings-rewriting side effect
 * below.
 *
 * IMPORTANT: exactly like handle_team_matchups(), calls
 * updateStandingsForWeek() for every week that had at least one genuinely
 * NEW matchup (i.e. manager1's own row didn't already exist) — which
 * DELETEs and fully recalculates the `standings` table for that week from
 * ALL matchups through it, not just the ones just written.
 */
function handle_scraped_matchups(array $matchups, int $year): int
{
    $written = 0;
    $weeksToUpdate = [];

    foreach ($matchups as $m) {
        $week = (int)($m['week'] ?? 0);
        $team1 = $m['team1'] ?? null;
        $team2 = $m['team2'] ?? null;

        if ($week <= 0 || !is_array($team1) || !is_array($team2)
            || !isset($team1['yahooTeamId'], $team2['yahooTeamId'], $team1['score'], $team2['score'])) {
            echo 'Skipping malformed scraped matchup entry.<br>';
            continue;
        }

        $manager1Id = lookupManager((int)$team1['yahooTeamId'], $year);
        $manager2Id = lookupManager((int)$team2['yahooTeamId'], $year);

        if (!$manager1Id || !$manager2Id) {
            echo 'No manager found for Yahoo team ID ' . (int)$team1['yahooTeamId'] . ' or ' . (int)$team2['yahooTeamId'] . ' in week ' . $week . ', skipping.<br>';
            continue;
        }

        $score1 = (float)$team1['score'];
        $score2 = (float)$team2['score'];
        $projected1 = isset($team1['projected']) && $team1['projected'] !== null ? (float)$team1['projected'] : null;
        $projected2 = isset($team2['projected']) && $team2['projected'] !== null ? (float)$team2['projected'] : null;

        $winningManagerId = $score1 > $score2 ? $manager1Id : $manager2Id;
        $losingManagerId = $score1 > $score2 ? $manager2Id : $manager1Id;

        // A matchup is "new" if manager1's own row doesn't already exist —
        // same check handle_team_matchups() uses, just against the side
        // we're about to write first.
        $existingResult = query("SELECT id FROM regular_season_matchups WHERE manager1_id = $manager1Id AND year = $year AND week_number = $week");
        $wasNewMatchup = !fetch_array($existingResult);

        $values1 = [
            'manager2_id' => $manager2Id,
            'manager1_score' => $score1,
            'manager2_score' => $score2,
            'winning_manager_id' => $winningManagerId,
            'losing_manager_id' => $losingManagerId,
        ];
        if ($projected1 !== null) {
            $values1['manager1_projected'] = $projected1;
        }
        if ($projected2 !== null) {
            $values1['manager2_projected'] = $projected2;
        }

        updateOrCreate('regular_season_matchups', [
            'manager1_id' => $manager1Id,
            'year' => $year,
            'week_number' => $week,
        ], $values1);

        $values2 = [
            'manager2_id' => $manager1Id,
            'manager1_score' => $score2,
            'manager2_score' => $score1,
            'winning_manager_id' => $winningManagerId,
            'losing_manager_id' => $losingManagerId,
        ];
        if ($projected2 !== null) {
            $values2['manager1_projected'] = $projected2;
        }
        if ($projected1 !== null) {
            $values2['manager2_projected'] = $projected1;
        }

        updateOrCreate('regular_season_matchups', [
            'manager1_id' => $manager2Id,
            'year' => $year,
            'week_number' => $week,
        ], $values2);

        echo 'Week ' . $week . ': manager ' . $manager1Id . ' (' . $score1 . ') vs manager ' . $manager2Id . ' (' . $score2 . ')<br>';
        $written++;

        if ($wasNewMatchup) {
            $weeksToUpdate[] = $week;
        }
    }

    foreach (array_unique($weeksToUpdate) as $week) {
        echo 'Updating standings for week ' . $week . '.<br>';
        updateStandingsForWeek($year, $week);
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
} elseif ($section === 'matchups') {
    $writtenCount = handle_scraped_matchups($data, $year);
    echo '<div class="alert alert-success">Scraped and saved ' . htmlspecialchars($section) . ' (' . $writtenCount . ' of ' . $itemCount . ' matchup(s) written).</div>';
} else {
    echo '<div class="alert alert-success">Scraped ' . htmlspecialchars($section) . ' successfully (' . $itemCount . ' item(s)). Writing this section to the database is added in a follow-up task.</div>';
}
