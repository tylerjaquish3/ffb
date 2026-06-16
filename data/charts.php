<?php
// Charts page data layer.
//
// Builds frame-by-frame data sets that drive the D3 visualisations on
// /charts.php. Each chart returns a self-contained payload that the page
// hands to its renderer.
//
// Depends on query()/fetch_array() from functions.php.

// Manager color palette (matches Milestones and Current Season > Charts).
function getChartsManagerPalette()
{
    return [
        1  => '#9c68d9',
        2  => '#a6c6fa',
        3  => '#3cf06e',
        4  => '#f33c47',
        5  => '#c0f6e6',
        6  => '#def89f',
        7  => '#dca130',
        8  => '#ff7f2c',
        9  => '#2dd4bf',
        10 => '#f87598',
    ];
}

// Animated bar chart race for career regular-season points.
//
// Returns:
//   [
//     'managers' => [mid => ['name' => ..., 'color' => ...], ...],
//     'frames'   => [
//        ['year' => 2006, 'week' => 1, 'label' => '2006 — Week 1',
//         'values' => [mid => cumulative_points, ...]],
//        ...
//     ],
//   ]
//
// One frame per (year, week_number). Values are cumulative career regular
// season points through that week. Managers who have not yet played are
// omitted from `values` for that frame (the renderer treats missing as
// "not yet in the race").
function getCareerPointsRaceData()
{
    $palette = getChartsManagerPalette();

    $managers = [];
    $res = query("SELECT id, name FROM managers ORDER BY id");
    while ($row = fetch_array($res)) {
        $mid = (int) $row['id'];
        $managers[$mid] = [
            'name'  => $row['name'],
            'color' => $palette[$mid] ?? '#9c68d9',
        ];
    }

    // Weekly totals per manager: sum of all of manager1_score rows for that
    // (year, week, manager1_id). Each matchup is recorded twice in
    // regular_season_matchups (once from each manager's perspective), so
    // grouping by manager1_id covers everyone.
    $sql = "SELECT year, week_number, manager1_id, SUM(manager1_score) AS pts
            FROM regular_season_matchups
            GROUP BY year, week_number, manager1_id
            ORDER BY year ASC, week_number ASC";

    // weeklyByFrame[$yearWeekKey][mid] = points scored that week
    $weeklyByFrame = [];
    $frameOrder    = []; // ordered list of (year, week) tuples

    $res = query($sql);
    while ($row = fetch_array($res)) {
        $y   = (int) $row['year'];
        $w   = (int) $row['week_number'];
        $mid = (int) $row['manager1_id'];
        $pts = (float) $row['pts'];
        $key = $y . '-' . $w;
        if (!isset($weeklyByFrame[$key])) {
            $weeklyByFrame[$key] = [];
            $frameOrder[] = [$y, $w, $key];
        }
        $weeklyByFrame[$key][$mid] = $pts;
    }

    // Walk frames in order, accumulating per-manager career totals.
    $running = []; // mid => cumulative
    $frames  = [];
    foreach ($frameOrder as [$year, $week, $key]) {
        foreach ($weeklyByFrame[$key] as $mid => $pts) {
            $running[$mid] = ($running[$mid] ?? 0) + $pts;
        }
        $snapshot = [];
        foreach ($running as $mid => $total) {
            $snapshot[$mid] = round($total, 2);
        }
        $frames[] = [
            'year'   => $year,
            'week'   => $week,
            'label'  => $year . ' — Week ' . $week,
            'values' => $snapshot,
        ];
    }

    return [
        'managers' => $managers,
        'frames'   => $frames,
    ];
}

// Treemap of points by position per manager, one snapshot per
// (year, week) in the regular season.
//
// Returns:
//   [
//     'positions'      => ['QB','RB','WR','TE','K','DEF'],
//     'positionColors' => ['QB' => '#...', ...],
//     'managers'       => ['Tyler','AJ',...],
//     'frames'         => [
//        ['year' => 2006, 'week' => 1, 'key' => '2006-1',
//         'weekly' => [pos => [manager => points_that_week]]],
//        ...
//     ],
//   ]
//
// Aggregates rosters.points by roster_spot. Treats the legacy roster_spot
// 'D' as 'DEF' (used pre-2020). Flex slots (W/R, W/R/T, …) and IDP slots
// (DB, DL, LB) are intentionally excluded so a manager's "RB points"
// matches what the rest of the site reports.
//
// The page-side renderer walks frames in order, accumulating per-position
// per-manager totals, so each playback step grows the treemap by exactly
// that week's points.
function getPositionTreemapData()
{
    $positions = ['QB', 'RB', 'WR', 'TE', 'K', 'DEF'];
    $positionColors = [
        'QB'  => '#ef4444',
        'RB'  => '#22c55e',
        'WR'  => '#3b82f6',
        'TE'  => '#f59e0b',
        'K'   => '#a855f7',
        'DEF' => '#6b7280',
    ];

    $managers = [];
    $res = query("SELECT name FROM managers ORDER BY id");
    while ($row = fetch_array($res)) {
        $managers[] = $row['name'];
    }

    $sql = "SELECT year, week,
                   CASE WHEN roster_spot = 'D' THEN 'DEF' ELSE roster_spot END AS pos,
                   manager,
                   ROUND(SUM(points), 2) AS pts
            FROM rosters
            WHERE roster_spot IN ('QB','RB','WR','TE','K','DEF','D')
            GROUP BY year, week, pos, manager
            ORDER BY year ASC, week ASC";

    $weeklyBuckets = []; // key => [pos => [mgr => pts]]
    $order         = []; // ordered [[year, week, key], ...]

    $res = query($sql);
    while ($row = fetch_array($res)) {
        $y   = (int) $row['year'];
        $w   = (int) $row['week'];
        $key = $y . '-' . $w;
        $pos = $row['pos'];
        $mgr = $row['manager'];
        $pts = (float) $row['pts'];

        if (!isset($weeklyBuckets[$key])) {
            $weeklyBuckets[$key] = [];
            $order[] = [$y, $w, $key];
        }
        $weeklyBuckets[$key][$pos][$mgr] = $pts;
    }

    $frames = [];
    foreach ($order as [$year, $week, $key]) {
        $frames[] = [
            'year'   => $year,
            'week'   => $week,
            'key'    => $key,
            'weekly' => $weeklyBuckets[$key],
        ];
    }

    return [
        'positions'      => $positions,
        'positionColors' => $positionColors,
        'managers'       => $managers,
        'frames'         => $frames,
    ];
}

// Multi-line lineup accuracy chart — accuracy % per manager per season.
//
// Uses pre-computed manager1_optimal from regular_season_matchups.
// Seasons where optimal is NULL (no roster data computed) are omitted.
//
// Returns:
//   [
//     'seasons' => [2012, 2013, ...],
//     'series'  => [
//       ['mid' => 1, 'name' => 'Tyler', 'color' => '#9c68d9',
//        'byYear' => [2012 => 87.34, 2013 => 91.02, ...]],
//       ...
//     ],
//   ]
function getLineupAccuracyData()
{
    $palette = getChartsManagerPalette();

    $managers = [];
    $res = query("SELECT id, name FROM managers ORDER BY id");
    while ($row = fetch_array($res)) {
        $mid = (int) $row['id'];
        $managers[$mid] = [
            'name'  => $row['name'],
            'color' => $palette[$mid] ?? '#9c68d9',
        ];
    }

    $sql = "SELECT rsm.year,
                   rsm.manager1_id AS mid,
                   SUM(rsm.manager1_score)   AS total_actual,
                   SUM(rsm.manager1_optimal) AS total_optimal
            FROM regular_season_matchups rsm
            WHERE rsm.manager1_optimal IS NOT NULL
            GROUP BY rsm.year, rsm.manager1_id
            HAVING SUM(rsm.manager1_optimal) > 0
            ORDER BY rsm.year ASC, rsm.manager1_id ASC";

    $byManager = [];
    $seasonSet = [];

    $res = query($sql);
    while ($row = fetch_array($res)) {
        $year    = (int)   $row['year'];
        $mid     = (int)   $row['mid'];
        $actual  = (float) $row['total_actual'];
        $optimal = (float) $row['total_optimal'];
        if ($optimal <= 0) continue;
        $byManager[$mid][$year] = round($actual * 100 / $optimal, 2);
        $seasonSet[$year] = true;
    }

    $seasons = array_keys($seasonSet);
    sort($seasons);

    $series = [];
    foreach ($managers as $mid => $info) {
        if (empty($byManager[$mid])) continue;
        $series[] = [
            'mid'    => $mid,
            'name'   => $info['name'],
            'color'  => $info['color'],
            'byYear' => $byManager[$mid],
        ];
    }

    return [
        'seasons' => $seasons,
        'series'  => $series,
    ];
}
