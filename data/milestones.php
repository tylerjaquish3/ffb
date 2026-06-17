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
                     LEFT JOIN rosters r ON r.manager = m.name AND r.roster_spot = '$rosterSpot'
                     GROUP BY m.id, m.name
                     ORDER BY value DESC",
        'events' => "SELECT r.year AS yr, r.week AS wk, 'Week ' || r.week AS wk_label,
                        m.id AS mid, SUM(r.points) AS inc
                     FROM rosters r
                     JOIN managers m ON m.name = r.manager
                     WHERE r.roster_spot = '$rosterSpot' AND m.id IN (:ids)
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
        'tiers' => [30000, 40000, 50000], 'unit' => 'points',
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
        'tiers' => [100, 125, 150], 'unit' => 'wins',
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
    foreach ([
        ['QB',  [5000, 6000, 7000]],
        ['RB',  [5000, 6000, 7000]],
        ['WR',  [7000, 8000, 9000]],
        ['TE',  [1500, 2000, 2500]],
        ['K',   [1500, 2000, 2200]],
        ['DEF', [3000, 3500, 4000]],
    ] as [$spot, $tiers]) {
        $sql = _milestonePositionPointsRegSql($spot);
        $specs[] = [
            'id'    => 'reg-' . strtolower($spot),
            'tab'   => 'regular-season',
            'title' => "Career $spot Points",
            'category' => "regular season $spot points",
            'tiers' => $tiers, 'unit' => 'points',
            'totals_sql' => $sql['totals'],
            'events_sql' => $sql['events'],
        ];
    }

    // ── POSTSEASON ────────────────────────────────────────────────────────
    $specs[] = [
        'id' => 'post-points', 'tab' => 'postseason',
        'title' => 'Career Points', 'category' => 'postseason points',
        'tiers' => [2000, 4000, 6000], 'unit' => 'points',
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
        'tiers' => [10, 15, 20], 'unit' => 'wins',
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
    foreach ([
        ['QB',  [300, 500, 750]],
        ['RB',  [300, 500, 750]],
        ['WR',  [400, 700, 1000]],
        ['TE',  [100, 200, 300]],
        ['K',   [100, 150, 200]],
        ['DEF', [200, 350, 500]],
    ] as [$spot, $tiers]) {
        $sql = _milestonePositionPointsPostSql($spot);
        $specs[] = [
            'id'    => 'post-' . strtolower($spot),
            'tab'   => 'postseason',
            'title' => "Career $spot Points",
            'category' => "postseason $spot points",
            'tiers' => $tiers, 'unit' => 'points',
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
        foreach ($spec['tiers'] as $tier) {
            if ($prev < $tier && $curr >= $tier) {
                $crossings[] = [
                    'manager_id'   => $mid,
                    'manager_name' => getManagerName($mid),
                    'tier'         => $tier,
                    'year'         => (int) $row['yr'],
                    'when'         => $row['wk_label'],
                    'sort_key'     => (int) $row['yr'] * 100 + (int) $row['wk'],
                ];
            }
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

function _milestoneBuildAlerts($crossings, $categoryLabel, $latestSeason)
{
    if (empty($crossings)) return [];

    $firstByTier = [];
    foreach ($crossings as $c) {
        if (!isset($firstByTier[$c['tier']])) $firstByTier[$c['tier']] = $c;
    }

    // Compute each manager's rank (place) per tier, ordered by sort_key.
    $placeByTier = [];
    foreach ($crossings as $c) {
        $placeByTier[$c['tier']][] = $c;
    }
    $rankByTierAndManager = [];
    foreach ($placeByTier as $tier => $tierCrossings) {
        usort($tierCrossings, fn($a, $b) => $a['sort_key'] <=> $b['sort_key']);
        foreach ($tierCrossings as $i => $tc) {
            $rankByTierAndManager[$tier][$tc['manager_id']] = $i + 1;
        }
    }

    $alerts = [];
    foreach ($crossings as $c) {
        $first    = $firstByTier[$c['tier']];
        $isFirst  = $first['manager_id'] === $c['manager_id'] && $first['sort_key'] === $c['sort_key'];
        $isRecent = $c['year'] === $latestSeason;
        if (!$isFirst && !$isRecent) continue;

        $tierStr = number_format($c['tier']);
        if ($isFirst) {
            $text = $c['manager_name'] . " was the first to reach $tierStr career $categoryLabel";
            $type = $isRecent ? 'first-recent' : 'first';
        } else {
            $place = $rankByTierAndManager[$c['tier']][$c['manager_id']] ?? null;
            $text  = $c['manager_name'] . " just went over $tierStr career $categoryLabel";
            $type  = 'recent';
        }

        $alerts[] = [
            'type'         => $type,
            'text'         => $text,
            'place'        => $place ?? null,
            'when'         => $c['year'] . ' ' . $c['when'],
            'manager_id'   => $c['manager_id'],
            'manager_name' => $c['manager_name'],
            'tier'         => $c['tier'],
            'category'     => $categoryLabel,
            'sort_key'     => $c['sort_key'],
        ];
    }
    return $alerts;
}

// Top-5 totals for every spec, keyed by spec id.
function getMilestoneTotals()
{
    $out = [];
    foreach (_milestoneSpecs() as $spec) {
        $totals = _milestoneFetchTotals($spec);
        $out[$spec['id']] = [
            'spec' => $spec,
            'top5' => array_slice($totals, 0, 5),
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
        $allAlerts = array_merge($allAlerts, _milestoneBuildAlerts($crossings, $spec['category'], $latestSeason));
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
