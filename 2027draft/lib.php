<?php
// Shared query helpers for the 2027 Draft Order ("Closest to 200") game.
// Used by index.php (render) and ../data/draftOrderGame.php (admin mutations)
// so pool/eligibility/points logic never drifts between the two.
//
// A manager's pick is a HISTORY, not a single current value: switching
// players mid-season doesn't retroactively move earlier weeks' points to the
// new player. draft_order_pick_history stores one row per assignment, each
// tagged with the week it took effect. A manager's season total is the sum
// of whichever player was assigned during each week, segment by segment.

const DRAFT_ORDER_GAME_YEAR = 2026;
const DRAFT_ORDER_GAME_TARGET = 200.0;
const DRAFT_ORDER_GAME_PASSWORD = 'suntown';
// The full NFL season, not just the FFB league's own regular season/playoff bracket —
// a pool player keeps racking up real stats through week 18 even if their manager's
// FFB team didn't make the league playoffs. Note: rosters only has weeks 1-14 synced
// as of this writing; weeks 15-18 need an admin Yahoo sync (yahooApi.php) run for ALL
// managers, not just playoff qualifiers, once those weeks are played.
const DRAFT_ORDER_GAME_WEEKS = 18;

function getManagers(SQLite3 $conn) {
    $managers = [];
    $result = $conn->query("SELECT id, name FROM managers ORDER BY id ASC");
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $managers[] = $row;
    }
    return $managers;
}

// Top 100 overall picks from the real league draft for $year — the selectable player pool.
function getPool(SQLite3 $conn, $year) {
    $stmt = $conn->prepare("SELECT overall_pick, round, round_pick, position, player FROM draft WHERE year = :year ORDER BY overall_pick LIMIT 100");
    $stmt->bindValue(':year', $year, SQLITE3_INTEGER);
    $result = $stmt->execute();
    $pool = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $pool[] = $row;
    }
    return $pool;
}

// Set of pool player names currently marked ineligible (injured/inactive) for $year.
function getIneligiblePlayers(SQLite3 $conn, $year) {
    $stmt = $conn->prepare("SELECT player FROM draft_order_ineligible_players WHERE year = :year");
    $stmt->bindValue(':year', $year, SQLITE3_INTEGER);
    $result = $stmt->execute();
    $ineligible = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $ineligible[$row['player']] = true;
    }
    return $ineligible;
}

// manager_id => ordered list of {id, player, effective_week} assignments for $year,
// sorted so the last element of each manager's list is always their current pick.
function getPickHistory(SQLite3 $conn, $year) {
    $stmt = $conn->prepare("
        SELECT id, manager_id, player, effective_week
        FROM draft_order_pick_history
        WHERE year = :year
        ORDER BY manager_id ASC, effective_week ASC, id ASC
    ");
    $stmt->bindValue(':year', $year, SQLITE3_INTEGER);
    $result = $stmt->execute();
    $history = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $history[(int)$row['manager_id']][] = [
            'id' => (int)$row['id'],
            'player' => $row['player'],
            'effective_week' => (int)$row['effective_week'],
        ];
    }
    return $history;
}

// manager_id => current player (the last assignment in each manager's history), or absent if no pick.
function getCurrentPicks(array $pickHistory) {
    $current = [];
    foreach ($pickHistory as $managerId => $assignments) {
        $last = end($assignments);
        $current[$managerId] = $last['player'];
    }
    return $current;
}

// manager_id-free: player => week => points for every manual override in $year.
// Manual overrides exist because rosters only has data for players who were on
// SOME manager's fantasy roster that week — a pool player who got dropped/waived
// has no rosters row at all for the weeks they sat on waivers, which would
// otherwise silently score as 0. The admin fills those gaps in by hand.
function getManualPoints(SQLite3 $conn, $year) {
    $stmt = $conn->prepare("SELECT player, week, points FROM draft_order_manual_points WHERE year = :year");
    $stmt->bindValue(':year', $year, SQLITE3_INTEGER);
    $result = $stmt->execute();
    $manual = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $manual[$row['player']][(int)$row['week']] = (float)$row['points'];
    }
    return $manual;
}

// Season fantasy points for $player in $year restricted to weeks [$startWeek, $endWeek],
// bridging draft/rosters name-spelling differences (e.g. "Travis Etienne" vs
// "Travis Etienne Jr.") via player_aliases. A degenerate range (start > end) is 0
// without querying — this is how a superseded same-week assignment naturally
// contributes nothing. A manual override for a given week takes precedence over
// whatever (if anything) rosters has for that same week — it's not additive.
function getPlayerPointsForWeekRange(SQLite3 $conn, $year, $player, $startWeek, $endWeek) {
    if ($startWeek > $endWeek) {
        return 0.0;
    }

    $manualStmt = $conn->prepare("SELECT week, points FROM draft_order_manual_points WHERE year = :year AND player = :player AND week BETWEEN :start_week AND :end_week");
    $manualStmt->bindValue(':year', $year, SQLITE3_INTEGER);
    $manualStmt->bindValue(':player', $player, SQLITE3_TEXT);
    $manualStmt->bindValue(':start_week', $startWeek, SQLITE3_INTEGER);
    $manualStmt->bindValue(':end_week', $endWeek, SQLITE3_INTEGER);
    $manualResult = $manualStmt->execute();
    $manualTotal = 0.0;
    $overriddenWeeks = [];
    while ($row = $manualResult->fetchArray(SQLITE3_ASSOC)) {
        $manualTotal += (float)$row['points'];
        $overriddenWeeks[] = (int)$row['week'];
    }

    $sql = "
        SELECT COALESCE(SUM(r.points), 0) AS pts
        FROM rosters r
        WHERE r.year = :year
          AND r.week BETWEEN :start_week AND :end_week
          AND (
            r.player = :player
            OR EXISTS (
                SELECT 1 FROM player_aliases pa
                WHERE (pa.player = :player OR pa.alias_1 = :player OR pa.alias_2 = :player OR pa.alias_3 = :player)
                  AND (r.player = pa.player OR r.player = pa.alias_1 OR r.player = pa.alias_2 OR r.player = pa.alias_3)
            )
          )
    ";
    if ($overriddenWeeks) {
        $sql .= " AND r.week NOT IN (" . implode(',', array_map('intval', $overriddenWeeks)) . ")";
    }
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':year', $year, SQLITE3_INTEGER);
    $stmt->bindValue(':start_week', $startWeek, SQLITE3_INTEGER);
    $stmt->bindValue(':end_week', $endWeek, SQLITE3_INTEGER);
    $stmt->bindValue(':player', $player, SQLITE3_TEXT);
    $result = $stmt->execute();
    $row = $result->fetchArray(SQLITE3_ASSOC);
    return (float)$row['pts'] + $manualTotal;
}

// Total season points for one manager's list of {player, effective_week} assignments,
// summed segment by segment: each assignment counts only for the weeks up until the
// next assignment (or the end of the season, for the current/last one).
function getManagerSeasonPoints(SQLite3 $conn, $year, array $assignments) {
    $total = 0.0;
    $count = count($assignments);
    for ($i = 0; $i < $count; $i++) {
        $startWeek = $assignments[$i]['effective_week'];
        $endWeek = ($i + 1 < $count) ? $assignments[$i + 1]['effective_week'] - 1 : DRAFT_ORDER_GAME_WEEKS;
        $total += getPlayerPointsForWeekRange($conn, $year, $assignments[$i]['player'], $startWeek, $endWeek);
    }
    return $total;
}

// The week a new pick assignment should default to taking effect: the week after the
// most recent one with any scored data for $year (matching functions.php's own
// "MAX(week) FROM rosters" convention for "what week are we on"), or week 1 if the
// season hasn't started scoring yet. Clamped to the last valid week.
function getDefaultEffectiveWeek(SQLite3 $conn, $year) {
    $stmt = $conn->prepare("SELECT MAX(week) AS max_week FROM rosters WHERE year = :year");
    $stmt->bindValue(':year', $year, SQLITE3_INTEGER);
    $result = $stmt->execute();
    $row = $result->fetchArray(SQLITE3_ASSOC);
    $maxWeek = $row['max_week'] !== null ? (int)$row['max_week'] : 0;
    return min($maxWeek + 1, DRAFT_ORDER_GAME_WEEKS);
}

// One row per manager: name, current player (or null), position, season points
// (summed across every assignment segment), diff from target, rank.
// Managers with no pick sort last and carry no rank.
function getSummaryRows(SQLite3 $conn, $year) {
    $managers = getManagers($conn);
    $pickHistory = getPickHistory($conn, $year);
    $pool = getPool($conn, $year);
    $poolByPlayer = [];
    foreach ($pool as $p) {
        $poolByPlayer[$p['player']] = $p;
    }

    $rows = [];
    foreach ($managers as $manager) {
        $assignments = $pickHistory[$manager['id']] ?? [];
        $currentPlayer = $assignments ? end($assignments)['player'] : null;
        $row = [
            'manager_id' => $manager['id'],
            'manager' => $manager['name'],
            'player' => $currentPlayer,
            'position' => $currentPlayer && isset($poolByPlayer[$currentPlayer]) ? $poolByPlayer[$currentPlayer]['position'] : null,
            'overall_pick' => $currentPlayer && isset($poolByPlayer[$currentPlayer]) ? (int)$poolByPlayer[$currentPlayer]['overall_pick'] : null,
            'points' => null,
            'diff' => null,
            'history' => $assignments,
        ];
        if ($assignments) {
            $points = getManagerSeasonPoints($conn, $year, $assignments);
            $row['points'] = $points;
            $row['diff'] = abs($points - DRAFT_ORDER_GAME_TARGET);
        }
        $rows[] = $row;
    }

    // Ties (equal diff from target) break by: 1) fewest pick changes, then
    // 2) later draft slot (higher overall_pick) of the current player.
    usort($rows, function ($a, $b) {
        if ($a['diff'] === null && $b['diff'] === null) return 0;
        if ($a['diff'] === null) return 1;
        if ($b['diff'] === null) return -1;
        if ($a['diff'] !== $b['diff']) return $a['diff'] <=> $b['diff'];

        $aChanges = count($a['history']);
        $bChanges = count($b['history']);
        if ($aChanges !== $bChanges) return $aChanges <=> $bChanges;

        return $b['overall_pick'] <=> $a['overall_pick'];
    });

    $rank = 0;
    foreach ($rows as &$row) {
        if ($row['diff'] !== null) {
            $rank++;
            $row['rank'] = $rank;
        } else {
            $row['rank'] = null;
        }
    }
    unset($row);

    return $rows;
}
