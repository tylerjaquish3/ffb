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
 * same figures.
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

/**
 * Write scraped trades data to the database. Mirrors yahooApiRequest.php's
 * handle_trades(), but works from scrape.js's already-normalized flat
 * array (one object per player moved: { player, fromTeamYahooId,
 * toTeamYahooId, date, tradeIdentifier }) rather than the API's nested
 * transaction/player JSON. Writes via firstOrCreate() — NOT
 * updateOrCreate() — exactly like handle_trades(): this table is
 * insert-if-not-exists (keyed on player+year+manager_from_id), never an
 * upsert, so a trade already recorded (e.g. previously fetched via the
 * API) is left untouched rather than overwritten.
 *
 * The scraped `date` (a plain Y-m-d string, since Yahoo's rendered
 * timestamp has no explicit year) is turned into a week number via the
 * same lookup_week() the API path uses, now shared via
 * yahooSharedFunctions.php rather than reimplemented here.
 *
 * IMPORTANT CAVEAT: unlike handle_scraped_team_names()/
 * handle_scraped_matchups(), the extractor this reads from
 * (extractTrades() in scrape.js) has never been exercised against a real
 * trade row — investigated 2026-09-15: the current 2026 league has had
 * zero trades all season (confirmed both by the live Yahoo page and by
 * this exact `trades` table already having zero 2026 rows), and this
 * scraping account isn't in any other reachable league/season with a
 * trade to check against either. Its non-empty-row parsing is therefore a
 * best-effort structural inference, not a verified pattern (see
 * scrape.js's extractTrades() for the full reasoning). The caller below
 * surfaces an extra caution banner whenever this actually writes 1+ rows,
 * so a real trade is never silently trusted the first time this runs
 * against one.
 */
function handle_scraped_trades(array $trades, int $year): int
{
    $written = 0;
    foreach ($trades as $trade) {
        $player = $trade['player'] ?? null;
        $fromTeamYahooId = isset($trade['fromTeamYahooId']) ? (int)$trade['fromTeamYahooId'] : 0;
        $toTeamYahooId = isset($trade['toTeamYahooId']) ? (int)$trade['toTeamYahooId'] : 0;
        $date = $trade['date'] ?? null;
        $tradeIdentifier = isset($trade['tradeIdentifier']) ? (int)$trade['tradeIdentifier'] : 0;

        if (!$player || !$fromTeamYahooId || !$toTeamYahooId || !$date || !$tradeIdentifier) {
            echo 'Skipping malformed scraped trade entry.<br>';
            continue;
        }

        $managerFromId = lookupManager($fromTeamYahooId, $year);
        $managerToId = lookupManager($toTeamYahooId, $year);

        if (!$managerFromId || !$managerToId) {
            echo 'No manager found for Yahoo team ID ' . $fromTeamYahooId . ' or ' . $toTeamYahooId . ', skipping trade for ' . htmlspecialchars($player) . '.<br>';
            continue;
        }

        $week = lookup_week($date, $year);

        firstOrCreate('trades', [
            'player' => $player,
            'year' => $year,
            'manager_from_id' => $managerFromId,
        ], [
            'week' => $week,
            'manager_to_id' => $managerToId,
            'trade_identifier' => $tradeIdentifier,
        ]);

        echo 'Week ' . $week . ': manager ' . $managerFromId . ' traded ' . htmlspecialchars($player) . ' to manager ' . $managerToId . '.<br>';
        $written++;
    }

    return $written;
}

/**
 * Write scraped rosters/stats data to the database. Mirrors
 * yahooApiRequest.php's handle_team_rosters() + get_player_stats(), but
 * works from scrape.js's already-normalized flat array (one object per
 * rostered player-week: { week, yahooTeamId, player, position, team,
 * rosterSpot, points, stats }) instead of the API's nested Yahoo JSON.
 * Unlike the API path (which is always called once per manager/week),
 * this can receive multiple weeks for the same manager in one call (since
 * extractRosters() loops --weeks internally the same way extractMatchups()
 * does) — grouped below by (manager, week) so calculateOptimalForManager()
 * and the regular_season_matchups optimal-column writes happen exactly
 * once per week actually written, at the end, exactly like
 * handle_team_rosters() does at the end of its own per-manager/week call.
 *
 * `stats` is only written (to the `stats` table) when roster_spot isn't
 * 'IR' — same rule as handle_team_rosters(). Any stat category genuinely
 * not shown for a given player (e.g. a kicker has no passing stats) comes
 * through from scrape.js as a MISSING key, not a null one, so it's simply
 * never part of $cleanStats and that column stays NULL in `stats` — same
 * as the OAuth API's own behavior (confirmed against existing rows). A
 * category that IS shown but reads as a dash on the page comes through as
 * a real `null` value and gets converted to 0 here, also matching the API
 * path's null-to-0 conversion.
 */
function handle_scraped_rosters(array $players, int $year): int
{
    $written = 0;
    $weeksByManager = [];
    $managerIdByYahooId = [];
    $managerNameById = [];

    foreach ($players as $p) {
        $yahooTeamId = isset($p['yahooTeamId']) ? (int)$p['yahooTeamId'] : 0;
        $week = isset($p['week']) ? (int)$p['week'] : 0;
        $playerName = $p['player'] ?? null;
        $position = $p['position'] ?? null;
        $team = $p['team'] ?? null;
        $rosterSpot = $p['rosterSpot'] ?? null;
        $points = isset($p['points']) ? (float)$p['points'] : 0.0;
        $stats = isset($p['stats']) && is_array($p['stats']) ? $p['stats'] : [];

        if ($yahooTeamId <= 0 || $week <= 0 || !$playerName || !$position || !$team || !$rosterSpot) {
            echo 'Skipping malformed scraped roster entry.<br>';
            continue;
        }

        if (!isset($managerIdByYahooId[$yahooTeamId])) {
            $managerIdByYahooId[$yahooTeamId] = lookupManager($yahooTeamId, $year);
        }
        $managerId = $managerIdByYahooId[$yahooTeamId];
        if (!$managerId) {
            echo 'No manager found for Yahoo team ID ' . $yahooTeamId . ', skipping.<br>';
            continue;
        }

        if (!isset($managerNameById[$managerId])) {
            $managerResult = query("SELECT name FROM managers WHERE id = $managerId");
            $managerRow = fetch_array($managerResult);
            $managerNameById[$managerId] = $managerRow ? $managerRow['name'] : null;
        }
        $manager = $managerNameById[$managerId];
        if (!$manager) {
            echo 'Manager id ' . $managerId . ' has no matching name, skipping.<br>';
            continue;
        }

        // Insert player into rosters (same params/values shape as
        // handle_team_rosters()'s updateOrCreate() call).
        $rosterId = updateOrCreate('rosters', [
            'manager' => $manager,
            'year' => $year,
            'week' => $week,
            'player' => $playerName,
            'position' => $position,
        ], [
            'team' => $team,
            'roster_spot' => $rosterSpot,
            'points' => $points,
        ]);

        echo htmlspecialchars($manager) . ' - ' . htmlspecialchars($playerName) . ' (' . htmlspecialchars($team) . ' - ' . htmlspecialchars($position) . ' - ' . htmlspecialchars($rosterSpot) . ') Points: ' . $points . '<br>';
        $written++;

        if ($rosterSpot !== 'IR' && !empty($stats)) {
            // Convert any nulls to 0 (same as handle_team_rosters()) —
            // categories genuinely not applicable to this player are
            // simply absent from $stats entirely, not included as null,
            // so they never reach this array and the DB column stays NULL.
            $cleanStats = array_map(function ($value) {
                return $value === null ? 0 : $value;
            }, $stats);

            updateOrCreate('stats', [
                'roster_id' => $rosterId,
            ], $cleanStats);
        } elseif ($rosterSpot === 'IR') {
            // Defensive cleanup discovered during real verification: `rosters`
            // uses a plain `INTEGER PRIMARY KEY` (no AUTOINCREMENT), so a
            // brand-new roster row's id is whatever id is next free — and
            // this DB already has ~113 pre-existing `stats` rows across all
            // years whose `roster_id` points at a `rosters` row that no
            // longer exists (confirmed: same orphaned rows exist in the
            // untouched production DB, unrelated to this scraper — a
            // pre-existing app-level data-integrity gap, present in
            // handle_team_rosters()'s identical code path too). A brand-new
            // roster row can land on one of those stale ids purely by
            // coincidence (confirmed happening for a real 2026 week 1 IR
            // player during this task's own verification). Since IR players
            // are never supposed to have a `stats` row at all, explicitly
            // clear out anything sitting under this roster_id rather than
            // silently leaving a stale, unrelated stat line attached to it.
            query("DELETE FROM stats WHERE roster_id = " . (int)$rosterId);
        }

        $weeksByManager[$manager][$week] = true;
    }

    // Calculate optimal lineup once per (manager, week) actually written
    // above, and persist to both sides of regular_season_matchups — same
    // UPDATE queries handle_team_rosters() runs at the end of its own call.
    foreach ($weeksByManager as $manager => $weeks) {
        foreach (array_keys($weeks) as $week) {
            $optimal = calculateOptimalForManager($manager, $year, $week);
            $safeManager = str_replace("'", "''", $manager);
            $optimalVal = round($optimal, 2);

            query("UPDATE regular_season_matchups
                SET manager1_optimal = $optimalVal
                WHERE manager1_id = (SELECT id FROM managers WHERE name = '$safeManager')
                AND year = $year AND week_number = $week");

            query("UPDATE regular_season_matchups
                SET manager2_optimal = $optimalVal
                WHERE manager2_id = (SELECT id FROM managers WHERE name = '$safeManager')
                AND year = $year AND week_number = $week");

            echo 'Optimal for ' . htmlspecialchars($manager) . ' week ' . $week . ': ' . $optimalVal . '<br>';
        }
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
} elseif ($section === 'rosters') {
    $writtenCount = handle_scraped_rosters($data, $year);
    echo '<div class="alert alert-success">Scraped and saved ' . htmlspecialchars($section) . ' (' . $writtenCount . ' of ' . $itemCount . ' player-week row(s) written).</div>';
} elseif ($section === 'trades') {
    $writtenCount = handle_scraped_trades($data, $year);
    if ($writtenCount > 0) {
        echo '<div class="alert alert-warning">Scraped and saved ' . htmlspecialchars($section) . ' (' . $writtenCount . ' of ' . $itemCount . ' trade(s) written). <strong>Caution:</strong> this extractor has never been verified against a real trade — manually check these rows against the Yahoo transactions page before trusting them.</div>';
    } else {
        echo '<div class="alert alert-success">Scraped ' . htmlspecialchars($section) . ' (0 of ' . $itemCount . ' trade(s) written — no trades found).</div>';
    }
} else {
    echo '<div class="alert alert-success">Scraped ' . htmlspecialchars($section) . ' successfully (' . $itemCount . ' item(s)). Writing this section to the database is added in a follow-up task.</div>';
}
