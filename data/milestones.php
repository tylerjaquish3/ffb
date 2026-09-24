<?php
// Milestones page data layer.
//
// All Milestones charts and alerts derive from a single spec list. Each spec
// declares two queries: totals_sql (one row per manager) and events_sql (one
// row per game/week for the top-5 managers — used to walk the cumulative
// total chronologically and detect tier crossings).
//
// Included by /milestones.php; depends on query()/fetch_array()/getManagerName()
// from functions.php.

function _milestonePositionPointsRegSql($rosterSpot)
{
    return [
        'totals' => "SELECT m.id AS mid, m.name AS mgr_name,
                        ROUND(COALESCE(SUM(r.points), 0), 2) AS value
                     FROM managers m
                     LEFT JOIN rosters r ON r.manager = m.name AND r.position = '$rosterSpot' AND r.roster_spot != 'BN'
                     GROUP BY m.id, m.name
                     ORDER BY value DESC",
        'events' => "SELECT r.year AS yr, r.week AS wk, 'Week ' || r.week AS wk_label,
                        m.id AS mid, SUM(r.points) AS inc
                     FROM rosters r
                     JOIN managers m ON m.name = r.manager
                     WHERE r.position = '$rosterSpot' AND r.roster_spot != 'BN' AND m.id IN (:ids)
                     GROUP BY m.id, r.year, r.week
                     ORDER BY r.year ASC, r.week ASC",
    ];
}

function _milestonePositionPointsPostSql($rosterSpot)
{
    $roundOrd = "CASE pr.round WHEN 'Quarterfinal' THEN 1 WHEN 'Semifinal' THEN 2 WHEN 'Final' THEN 3 ELSE 0 END";
    return [
        'totals' => "SELECT m.id AS mid, m.name AS mgr_name,
                        ROUND(COALESCE(SUM(pr.points), 0), 2) AS value
                     FROM managers m
                     LEFT JOIN playoff_rosters pr ON pr.manager = m.name AND pr.roster_spot = '$rosterSpot'
                     GROUP BY m.id, m.name
                     ORDER BY value DESC",
        'events' => "SELECT pr.year AS yr, $roundOrd AS wk, pr.round AS wk_label,
                        m.id AS mid, SUM(pr.points) AS inc
                     FROM playoff_rosters pr
                     JOIN managers m ON m.name = pr.manager
                     WHERE pr.roster_spot = '$rosterSpot' AND m.id IN (:ids)
                     GROUP BY m.id, pr.year, pr.round
                     ORDER BY pr.year ASC, wk ASC",
    ];
}

function _milestoneSpecs()
{
    $specs = [];

    // ── REGULAR SEASON ────────────────────────────────────────────────────
    $specs[] = [
        'id' => 'reg-points', 'tab' => 'regular-season',
        'title' => 'Career Points', 'category' => 'regular season points',
        'step' => 1000, 'unit' => 'points',
        'totals_sql' => "SELECT m.id AS mid, m.name AS mgr_name,
                            ROUND(COALESCE(SUM(rsm.manager1_score), 0), 2) AS value
                         FROM managers m
                         LEFT JOIN regular_season_matchups rsm ON rsm.manager1_id = m.id
                         GROUP BY m.id, m.name ORDER BY value DESC",
        'events_sql' => "SELECT year AS yr, week_number AS wk,
                            'Week ' || week_number AS wk_label,
                            manager1_id AS mid, manager1_score AS inc
                         FROM regular_season_matchups WHERE manager1_id IN (:ids)
                         ORDER BY year ASC, week_number ASC, id ASC",
    ];
    $specs[] = [
        'id' => 'reg-wins', 'tab' => 'regular-season',
        'title' => 'Career Wins', 'category' => 'regular season wins',
        'step' => 25, 'unit' => 'wins',
        'totals_sql' => "SELECT m.id AS mid, m.name AS mgr_name,
                            COALESCE(COUNT(rsm.id), 0) AS value
                         FROM managers m
                         LEFT JOIN regular_season_matchups rsm
                            ON rsm.winning_manager_id = m.id AND rsm.manager1_id = m.id
                         GROUP BY m.id, m.name ORDER BY value DESC",
        'events_sql' => "SELECT year AS yr, week_number AS wk,
                            'Week ' || week_number AS wk_label,
                            winning_manager_id AS mid, 1 AS inc
                         FROM regular_season_matchups
                         WHERE winning_manager_id IN (:ids) AND manager1_id = winning_manager_id
                         ORDER BY year ASC, week_number ASC, id ASC",
    ];
    foreach (['QB', 'RB', 'WR', 'TE', 'K', 'DEF'] as $spot) {
        $sql = _milestonePositionPointsRegSql($spot);
        $specs[] = [
            'id'    => 'reg-' . strtolower($spot),
            'tab'   => 'regular-season',
            'title' => "Career $spot Points",
            'category' => "regular season $spot points",
            'step' => 1000, 'unit' => 'points',
            'totals_sql' => $sql['totals'],
            'events_sql' => $sql['events'],
        ];
    }

    // ── POSTSEASON ────────────────────────────────────────────────────────
    $specs[] = [
        'id' => 'post-points', 'tab' => 'postseason',
        'title' => 'Career Points', 'category' => 'postseason points',
        'step' => 100, 'unit' => 'points',
        'totals_sql' => "SELECT m.id AS mid, m.name AS mgr_name,
                            ROUND(COALESCE(SUM(CASE WHEN pm.manager1_id = m.id THEN pm.manager1_score
                                                    WHEN pm.manager2_id = m.id THEN pm.manager2_score END), 0), 2) AS value
                         FROM managers m
                         LEFT JOIN playoff_matchups pm ON pm.manager1_id = m.id OR pm.manager2_id = m.id
                         GROUP BY m.id, m.name ORDER BY value DESC",
        'events_sql' => "SELECT year AS yr,
                            CASE round WHEN 'Quarterfinal' THEN 1 WHEN 'Semifinal' THEN 2 WHEN 'Final' THEN 3 ELSE 0 END AS wk,
                            round AS wk_label, manager1_id AS mid, manager1_score AS inc, id
                         FROM playoff_matchups WHERE manager1_id IN (:ids)
                         UNION ALL
                         SELECT year AS yr,
                            CASE round WHEN 'Quarterfinal' THEN 1 WHEN 'Semifinal' THEN 2 WHEN 'Final' THEN 3 ELSE 0 END AS wk,
                            round AS wk_label, manager2_id AS mid, manager2_score AS inc, id
                         FROM playoff_matchups WHERE manager2_id IN (:ids)
                         ORDER BY yr ASC, wk ASC, id ASC",
    ];
    $specs[] = [
        'id' => 'post-wins', 'tab' => 'postseason',
        'title' => 'Career Wins', 'category' => 'postseason wins',
        'step' => 5, 'unit' => 'wins',
        'totals_sql' => "SELECT m.id AS mid, m.name AS mgr_name, COALESCE(COUNT(pm.id), 0) AS value
                         FROM managers m
                         LEFT JOIN playoff_matchups pm ON
                            (pm.manager1_id = m.id AND pm.manager1_score > pm.manager2_score) OR
                            (pm.manager2_id = m.id AND pm.manager2_score > pm.manager1_score)
                         GROUP BY m.id, m.name ORDER BY value DESC",
        'events_sql' => "SELECT yr, wk, wk_label, mid, 1 AS inc, id FROM (
                            SELECT year AS yr,
                                CASE round WHEN 'Quarterfinal' THEN 1 WHEN 'Semifinal' THEN 2 WHEN 'Final' THEN 3 ELSE 0 END AS wk,
                                round AS wk_label, manager1_id AS mid, id
                            FROM playoff_matchups WHERE manager1_score > manager2_score AND manager1_id IN (:ids)
                            UNION ALL
                            SELECT year AS yr,
                                CASE round WHEN 'Quarterfinal' THEN 1 WHEN 'Semifinal' THEN 2 WHEN 'Final' THEN 3 ELSE 0 END AS wk,
                                round AS wk_label, manager2_id AS mid, id
                            FROM playoff_matchups WHERE manager2_score > manager1_score AND manager2_id IN (:ids)
                         ) ORDER BY yr ASC, wk ASC, id ASC",
    ];
    foreach (['QB', 'RB', 'WR', 'TE', 'K', 'DEF'] as $spot) {
        $sql = _milestonePositionPointsPostSql($spot);
        $specs[] = [
            'id'    => 'post-' . strtolower($spot),
            'tab'   => 'postseason',
            'title' => "Career $spot Points",
            'category' => "postseason $spot points",
            'step' => 100, 'unit' => 'points',
            'totals_sql' => $sql['totals'],
            'events_sql' => $sql['events'],
        ];
    }

    return $specs;
}

function _milestoneFetchTotals($spec)
{
    $rows = [];
    $r = query($spec['totals_sql']);
    while ($row = fetch_array($r)) {
        $rows[] = [
            'manager_id'   => (int) $row['mid'],
            'manager_name' => $row['mgr_name'],
            'points'       => (float) $row['value'],
        ];
    }
    return $rows;
}

function _milestoneFetchCrossings($spec, $top5Rows)
{
    if (empty($top5Rows)) return [];
    $ids = implode(',', array_map(fn($r) => (int) $r['manager_id'], $top5Rows));
    $sql = str_replace(':ids', $ids, $spec['events_sql']);
    $r = query($sql);

    $totals = [];
    foreach ($top5Rows as $row) $totals[$row['manager_id']] = 0.0;

    $crossings = [];
    while ($row = fetch_array($r)) {
        $mid = (int) $row['mid'];
        if (!isset($totals[$mid])) continue;
        $prev = $totals[$mid];
        $curr = $prev + (float) $row['inc'];
        $totals[$mid] = $curr;
        $step = $spec['step'];
        $prevStep = (int) floor($prev / $step);
        $currStep = (int) floor($curr / $step);
        for ($s = max($prevStep + 1, 1); $s <= $currStep; $s++) {
            $crossings[] = [
                'manager_id'   => $mid,
                'manager_name' => getManagerName($mid),
                'tier'         => $s * $step,
                'year'         => (int) $row['yr'],
                'when'         => $row['wk_label'],
                'sort_key'     => (int) $row['yr'] * 100 + (int) $row['wk'],
            ];
        }
    }
    return $crossings;
}

function _milestoneOrdinal($n)
{
    if ($n % 100 >= 11 && $n % 100 <= 13) return $n . 'th';
    switch ($n % 10) {
        case 1: return $n . 'st';
        case 2: return $n . 'nd';
        case 3: return $n . 'rd';
        default: return $n . 'th';
    }
}

function _milestoneBuildAlerts($crossings, $categoryLabel, $latestSeason, $tab)
{
    if (empty($crossings)) return [];

    // Who (chronologically) crossed each specific tier first, and everyone's
    // rank at that tier — both scoped to the current top-5 population.
    $byTier = [];
    foreach ($crossings as $c) {
        $byTier[$c['tier']][] = $c;
    }
    $firstByTier          = [];
    $rankByTierAndManager = [];
    foreach ($byTier as $tier => $tierCrossings) {
        usort($tierCrossings, fn($a, $b) => $a['sort_key'] <=> $b['sort_key']);
        $firstByTier[$tier] = $tierCrossings[0];
        foreach ($tierCrossings as $i => $tc) {
            $rankByTierAndManager[$tier][$tc['manager_id']] = $i + 1;
        }
    }

    // Each manager's own highest tier reached is their current milestone
    // standing — lower tiers they crossed earlier are superseded by it.
    $latestByManager = [];
    foreach ($crossings as $c) {
        $mid = $c['manager_id'];
        if (!isset($latestByManager[$mid]) || $c['tier'] > $latestByManager[$mid]['tier']) {
            $latestByManager[$mid] = $c;
        }
    }

    $alerts = [];
    foreach ($latestByManager as $c) {
        $tier     = $c['tier'];
        $first    = $firstByTier[$tier];
        $isFirst  = $first['manager_id'] === $c['manager_id'] && $first['sort_key'] === $c['sort_key'];
        $isRecent = $c['year'] === $latestSeason;
        $place    = $rankByTierAndManager[$tier][$c['manager_id']] ?? null;

        $tierStr = number_format($tier);
        if ($isFirst) {
            $text = $c['manager_name'] . " was the first to reach $tierStr career $categoryLabel";
            $type = $isRecent ? 'first-recent' : 'first';
        } else {
            $verb = $isRecent ? 'just went over' : 'has reached';
            $text = $c['manager_name'] . " $verb $tierStr career $categoryLabel";
            $type = $isRecent ? 'recent' : 'standing';
        }

        $alerts[] = [
            'type'         => $type,
            'text'         => $text,
            'place'        => $place,
            'when'         => $c['year'] . ' ' . $c['when'],
            'manager_id'   => $c['manager_id'],
            'manager_name' => $c['manager_name'],
            'tier'         => $tier,
            'category'     => $categoryLabel,
            'tab'          => $tab,
            'sort_key'     => $c['sort_key'],
        ];
    }
    return $alerts;
}

// ── SCORIGAMI ────────────────────────────────────────────────────────────
// Grid of (winner score, loser score) -> how many times that exact rounded
// score pair has occurred in a regular-season matchup. The most extreme 1%
// of individual scores at each end (by frequency, not by distinct value) are
// excluded from the grid's range and reported separately as outliers, along
// with any matchup touching one of those scores.

// Returns [lo, hi]: the score range remaining after trimming $pct% of
// individual score instances off each end of a sorted list.
function _scorigamiPercentileRange($sortedScores, $pct = 1)
{
    $n = count($sortedScores);
    if ($n === 0) return [0, 0];
    $loIdx = (int) floor($n * $pct / 100);
    $hiIdx = (int) ceil($n * (100 - $pct) / 100) - 1;
    $hiIdx = max($hiIdx, $loIdx);
    return [$sortedScores[$loIdx], $sortedScores[$hiIdx]];
}

function getScorigamiData()
{
    $r = query("SELECT manager1_id AS m1, manager2_id AS m2,
                    manager1_score AS s1, manager2_score AS s2,
                    year AS yr, week_number AS wk
                FROM regular_season_matchups
                WHERE manager1_id < manager2_id
                ORDER BY year ASC, week_number ASC, id ASC");

    $matchups     = [];
    $scoreInstances = [];
    while ($row = fetch_array($r)) {
        $s1 = (int) round((float) $row['s1']);
        $s2 = (int) round((float) $row['s2']);
        $tie = $s1 === $s2;
        if ($s1 >= $s2) {
            $winScore = $s1; $winName = getManagerName((int) $row['m1']);
            $loseScore = $s2; $loseName = getManagerName((int) $row['m2']);
        } else {
            $winScore = $s2; $winName = getManagerName((int) $row['m2']);
            $loseScore = $s1; $loseName = getManagerName((int) $row['m1']);
        }
        $matchups[] = [
            'win_score' => $winScore, 'win_name' => $winName,
            'lose_score' => $loseScore, 'lose_name' => $loseName,
            'tie' => $tie, 'year' => (int) $row['yr'], 'week' => (int) $row['wk'],
        ];
        $scoreInstances[] = $winScore;
        $scoreInstances[] = $loseScore;
    }

    if (empty($scoreInstances)) {
        return ['min' => 0, 'max' => 0, 'cells' => [], 'outliers' => [], 'recent' => []];
    }

    sort($scoreInstances, SORT_NUMERIC);
    [$min, $max] = _scorigamiPercentileRange($scoreInstances, 1);

    $cellsByKey  = [];
    $outliers    = [];
    foreach ($matchups as $m) {
        if ($m['win_score'] < $min || $m['win_score'] > $max ||
            $m['lose_score'] < $min || $m['lose_score'] > $max) {
            $outliers[] = $m;
            continue;
        }
        $key = $m['win_score'] . '-' . $m['lose_score'];
        if (!isset($cellsByKey[$key])) {
            $cellsByKey[$key] = [
                'win_score' => $m['win_score'], 'lose_score' => $m['lose_score'],
                'count' => 0, 'games' => [],
            ];
        }
        $cellsByKey[$key]['count']++;
        $cellsByKey[$key]['games'][] = [
            'year' => $m['year'], 'week' => $m['week'], 'tie' => $m['tie'],
            'winner' => $m['win_name'], 'loser' => $m['lose_name'],
        ];
    }

    // Outliers sorted highest score first; ties in scores broken by year/week.
    usort($outliers, fn($a, $b) => $b['win_score'] <=> $a['win_score']
        ?: $b['year'] <=> $a['year'] ?: $b['week'] <=> $a['week']);

    // Scorigamis: the first time each score pair happened, within the
    // grid's range — games within a cell are already in chronological order,
    // so games[0] is that pair's debut, even if it has since repeated.
    $recent = [];
    foreach ($cellsByKey as $cell) {
        $g = $cell['games'][0];
        $recent[] = [
            'win_score' => $cell['win_score'], 'lose_score' => $cell['lose_score'],
            'winner' => $g['winner'], 'loser' => $g['loser'], 'tie' => $g['tie'],
            'year' => $g['year'], 'week' => $g['week'], 'count' => $cell['count'],
        ];
    }
    usort($recent, fn($a, $b) => $b['year'] <=> $a['year'] ?: $b['week'] <=> $a['week']);

    return [
        'min'      => $min,
        'max'      => $max,
        'cells'    => array_values($cellsByKey),
        'outliers' => $outliers,
        'recent'   => $recent,
    ];
}

// Top-5 totals for every spec, keyed by spec id.
function getMilestoneTotals()
{
    $out = [];
    foreach (_milestoneSpecs() as $spec) {
        $totals = _milestoneFetchTotals($spec);
        $average = $totals ? array_sum(array_column($totals, 'points')) / count($totals) : 0;
        $out[$spec['id']] = [
            'spec'    => $spec,
            'top5'    => array_slice($totals, 0, 5),
            'average' => $average,
        ];
    }
    return $out;
}

// Deduped alerts across every Milestones spec. Top-5 populations match the
// chart populations (same totals source).
function getCareerPointsAlerts()
{
    $latestSeason = 0;
    $r = query("SELECT MAX(year) AS yr FROM regular_season_matchups");
    if ($row = fetch_array($r)) $latestSeason = (int) $row['yr'];

    $allAlerts = [];
    foreach (_milestoneSpecs() as $spec) {
        $totals    = _milestoneFetchTotals($spec);
        $top5      = array_slice($totals, 0, 5);
        $crossings = _milestoneFetchCrossings($spec, $top5);
        $allAlerts = array_merge($allAlerts, _milestoneBuildAlerts($crossings, $spec['category'], $latestSeason, $spec['tab']));
    }

    usort($allAlerts, fn($a, $b) => $b['sort_key'] <=> $a['sort_key']);

    // De-duplicate: keep only the most recent alert per (manager, type, category).
    $deduped = [];
    $seen    = [];
    foreach ($allAlerts as $a) {
        $key = $a['manager_id'] . '|' . $a['type'] . '|' . $a['category'];
        if (isset($seen[$key])) continue;
        $seen[$key] = true;
        $deduped[] = $a;
    }
    return $deduped;
}
