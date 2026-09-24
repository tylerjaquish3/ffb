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

// Animated bar chart race for career win totals — stepping week by week
// through each regular season and then through that year's playoff bracket
// (Quarterfinal -> Semifinal -> Final), with wins accumulating across the
// manager's whole career rather than resetting each season.
//
// Returns:
//   [
//     'managers' => [mid => ['name' => ..., 'color' => ...], ...],
//     'frames'   => [
//        ['year' => 2006, 'phase' => 'regular', 'week' => 1,
//         'label' => '2006 — Week 1', 'values' => [mid => career_wins_so_far, ...]],
//        ...
//        ['year' => 2006, 'phase' => 'playoff', 'round' => 'Quarterfinal',
//         'label' => '2006 — Quarterfinal', 'values' => [...]],
//        ['year' => 2006, 'phase' => 'playoff', 'round' => 'Final',
//         'label' => '2006 — Final', 'values' => [...], 'championMid' => 8],
//        ...
//     ],
//     'segments' => [mid => [['type' => 'regular', 'through' => 11],
//                             ['type' => 'playoff', 'through' => 12],
//                             ['type' => 'regular', 'through' => 23], ...], ...],
//   ]
//
// `segments` is each manager's career win total broken into alternating
// regular-season/playoff stretches, in chronological order, as a list of
// running-total breakpoints. The renderer walks a manager's segments up to
// their current frame value and shades whichever stretches are playoff wins
// darker — so postseason wins show up right where they happened along the
// bar (once each season, right after that year's regular-season stretch)
// instead of all being pooled at the end of the bar.
//
// A manager is added to `values` (starting at 0) the season they first play
// (covers the 2006-2007 seasons, which had 8 managers rather than the
// current 10) and stays on the board every frame after that. A season with
// no playoff rows yet (the in-progress current season) simply ends after
// its last played regular-season week — no playoff frames, no `championMid`.
function getSeasonWinsRaceData()
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

    // weeklyWins[year][week][mid] = 1 or 0
    $weeklyWins  = [];
    $weeksByYear = []; // year => ordered list of week numbers

    $res = query("SELECT year, week_number, manager1_id,
                         CASE WHEN manager1_score > manager2_score THEN 1 ELSE 0 END AS win
                  FROM regular_season_matchups
                  ORDER BY year ASC, week_number ASC");
    while ($row = fetch_array($res)) {
        $y   = (int) $row['year'];
        $w   = (int) $row['week_number'];
        $mid = (int) $row['manager1_id'];
        if (!isset($weeklyWins[$y][$w])) {
            $weeklyWins[$y][$w] = [];
            if (!isset($weeksByYear[$y]) || !in_array($w, $weeksByYear[$y], true)) {
                $weeksByYear[$y][] = $w;
            }
        }
        $weeklyWins[$y][$w][$mid] = (int) $row['win'];
    }

    // playoffRounds[year][round] = [[winnerMid], ...] one entry per game
    $roundOrder    = ['Quarterfinal', 'Semifinal', 'Final'];
    $playoffRounds = [];

    $res = query("SELECT year, round, manager1_id, manager2_id, manager1_score, manager2_score
                  FROM playoff_matchups");
    while ($row = fetch_array($res)) {
        $y      = (int) $row['year'];
        $round  = $row['round'];
        $s1     = (float) $row['manager1_score'];
        $s2     = (float) $row['manager2_score'];
        $winner = $s1 > $s2 ? (int) $row['manager1_id'] : (int) $row['manager2_id'];
        $playoffRounds[$y][$round][] = $winner;
    }

    $years = array_keys($weeksByYear);
    sort($years);

    $frames   = [];
    $running  = []; // mid => career wins so far, carried across seasons
    $segments = []; // mid => [['type' => 'regular'|'playoff', 'through' => N], ...]
    foreach ($years as $year) {
        $weeks = $weeksByYear[$year];
        sort($weeks);

        // Managers active this season, in a stable order. A manager new to
        // the league this year joins the board at 0 without disturbing
        // anyone else's running total.
        $activeMidSet = [];
        foreach ($weeklyWins[$year] as $weekWins) {
            foreach ($weekWins as $mid => $win) {
                $activeMidSet[$mid] = true;
            }
        }
        foreach ($managers as $mid => $info) {
            if (isset($activeMidSet[$mid]) && !isset($running[$mid])) {
                $running[$mid] = 0;
            }
        }

        foreach ($weeks as $week) {
            foreach ($weeklyWins[$year][$week] as $mid => $win) {
                $running[$mid] = ($running[$mid] ?? 0) + $win;
            }
            $frames[] = [
                'year'   => $year,
                'phase'  => 'regular',
                'week'   => $week,
                'label'  => $year . ' — Week ' . $week,
                'values' => $running,
            ];
        }

        // Close out this year's regular-season stretch for every manager
        // currently on the board, whether or not their total moved.
        foreach ($running as $mid => $val) {
            $segments[$mid][] = ['type' => 'regular', 'through' => $val];
        }

        if (empty($playoffRounds[$year])) {
            continue; // in-progress season, no playoff results yet
        }

        foreach ($roundOrder as $round) {
            if (empty($playoffRounds[$year][$round])) {
                continue;
            }
            foreach ($playoffRounds[$year][$round] as $winnerMid) {
                $running[$winnerMid] = ($running[$winnerMid] ?? 0) + 1;
            }
            $frame = [
                'year'   => $year,
                'phase'  => 'playoff',
                'round'  => $round,
                'label'  => $year . ' — ' . $round,
                'values' => $running,
            ];
            if ($round === 'Final') {
                $frame['championMid'] = $playoffRounds[$year][$round][0];
            }
            $frames[] = $frame;
        }

        // Close out this year's playoff stretch for every manager on the
        // board (a no-op, zero-length segment for anyone who didn't make
        // the playoffs — their bar just stays the base color there).
        foreach ($running as $mid => $val) {
            $segments[$mid][] = ['type' => 'playoff', 'through' => $val];
        }
    }

    return [
        'managers' => $managers,
        'segments' => $segments,
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

// Head-to-head win% heatmap — all-time record for every ordered manager
// pair (A vs B), combining regular season + playoffs.
//
// Regular season matchups already store one row per manager per game (see
// CLAUDE.md), so manager1_id = A AND manager2_id = B covers A's side of
// every meeting without double-counting. Playoff matchups store one row
// per game total, so both manager1/manager2 orderings must be checked.
//
// Returns:
//   [
//     'managers' => [['mid' => 1, 'name' => 'Tyler', 'color' => '#...'], ...],
//     'matrix'   => [mid => [opponentMid => ['wins' => .., 'total' => .., 'winPct' => ..], ...], ...],
//   ]
//
// `matrix[$a][$a]` is omitted (no self matchups). Pairs with zero career
// meetings are also omitted (none exist among the 10 original managers).
function getHeadToHeadHeatmapData()
{
    $palette = getChartsManagerPalette();

    $managers = [];
    $res = query("SELECT id, name FROM managers ORDER BY id");
    while ($row = fetch_array($res)) {
        $mid = (int) $row['id'];
        $managers[] = [
            'mid'   => $mid,
            'name'  => $row['name'],
            'color' => $palette[$mid] ?? '#9c68d9',
        ];
    }

    $wins  = []; // wins[$a][$b] = number of times $a beat $b
    $total = []; // total[$a][$b] = number of times $a and $b played

    $res = query("SELECT manager1_id AS a, manager2_id AS b,
                         SUM(CASE WHEN manager1_score > manager2_score THEN 1 ELSE 0 END) AS w,
                         COUNT(*) AS t
                  FROM regular_season_matchups
                  GROUP BY manager1_id, manager2_id");
    while ($row = fetch_array($res)) {
        $a = (int) $row['a'];
        $b = (int) $row['b'];
        $wins[$a][$b]  = ($wins[$a][$b]  ?? 0) + (int) $row['w'];
        $total[$a][$b] = ($total[$a][$b] ?? 0) + (int) $row['t'];
    }

    $res = query("SELECT manager1_id AS m1, manager2_id AS m2,
                         manager1_score AS s1, manager2_score AS s2
                  FROM playoff_matchups");
    while ($row = fetch_array($res)) {
        $m1 = (int) $row['m1'];
        $m2 = (int) $row['m2'];
        $s1 = (float) $row['s1'];
        $s2 = (float) $row['s2'];

        $total[$m1][$m2] = ($total[$m1][$m2] ?? 0) + 1;
        $total[$m2][$m1] = ($total[$m2][$m1] ?? 0) + 1;
        if ($s1 > $s2) {
            $wins[$m1][$m2] = ($wins[$m1][$m2] ?? 0) + 1;
        } elseif ($s2 > $s1) {
            $wins[$m2][$m1] = ($wins[$m2][$m1] ?? 0) + 1;
        }
    }

    $matrix = [];
    foreach ($managers as $ma) {
        $a = $ma['mid'];
        foreach ($managers as $mb) {
            $b = $mb['mid'];
            if ($a === $b) continue;
            $t = $total[$a][$b] ?? 0;
            if ($t <= 0) continue;
            $w = $wins[$a][$b] ?? 0;
            $matrix[$a][$b] = [
                'wins'   => $w,
                'total'  => $t,
                'winPct' => round($w * 100 / $t, 1),
            ];
        }
    }

    return [
        'managers' => $managers,
        'matrix'   => $matrix,
    ];
}

// "Lucky vs. Good" scatter data — points scored vs. wins, regular season
// only (playoff seeding would muddy what "wins" means here).
//
// Returns:
//   [
//     'managers' => [['mid' => 1, 'name' => 'Tyler', 'color' => '#...'], ...],
//     'seasons'  => [['mid' => 1, 'name' => 'Tyler', 'color' => '#...',
//                     'year' => 2006, 'points' => 2264.12, 'wins' => 3, 'games' => 13], ...],
//     'career'   => [['mid' => 1, 'name' => 'Tyler', 'color' => '#...',
//                     'points' => 51234.5, 'wins' => 142, 'games' => 300], ...],
//   ]
//
// `seasons` has one row per manager per year; `career` sums those into one
// row per manager. The renderer fits its own regression line client-side
// for whichever set is on screen.
function getLuckVsGoodData()
{
    $palette = getChartsManagerPalette();

    $managers = [];
    $res = query("SELECT id, name FROM managers ORDER BY id");
    while ($row = fetch_array($res)) {
        $mid = (int) $row['id'];
        $managers[$mid] = [
            'mid'   => $mid,
            'name'  => $row['name'],
            'color' => $palette[$mid] ?? '#9c68d9',
        ];
    }

    $sql = "SELECT year, manager1_id AS mid,
                   COUNT(*) AS games,
                   SUM(manager1_score) AS pts,
                   SUM(CASE WHEN manager1_score > manager2_score THEN 1 ELSE 0 END) AS wins
            FROM regular_season_matchups
            GROUP BY year, manager1_id
            ORDER BY year ASC, manager1_id ASC";

    $seasons = [];
    $careerTotals = []; // mid => ['points' => .., 'wins' => .., 'games' => ..]

    $res = query($sql);
    while ($row = fetch_array($res)) {
        $mid    = (int) $row['mid'];
        $year   = (int) $row['year'];
        $points = round((float) $row['pts'], 2);
        $wins   = (int) $row['wins'];
        $games  = (int) $row['games'];

        if (!isset($managers[$mid])) continue;

        $seasons[] = [
            'mid'    => $mid,
            'name'   => $managers[$mid]['name'],
            'color'  => $managers[$mid]['color'],
            'year'   => $year,
            'points' => $points,
            'wins'   => $wins,
            'games'  => $games,
        ];

        if (!isset($careerTotals[$mid])) {
            $careerTotals[$mid] = ['points' => 0, 'wins' => 0, 'games' => 0];
        }
        $careerTotals[$mid]['points'] += $points;
        $careerTotals[$mid]['wins']   += $wins;
        $careerTotals[$mid]['games']  += $games;
    }

    $career = [];
    foreach ($managers as $mid => $info) {
        if (!isset($careerTotals[$mid])) continue;
        $career[] = [
            'mid'    => $mid,
            'name'   => $info['name'],
            'color'  => $info['color'],
            'points' => round($careerTotals[$mid]['points'], 2),
            'wins'   => $careerTotals[$mid]['wins'],
            'games'  => $careerTotals[$mid]['games'],
        ];
    }

    return [
        'managers' => array_values($managers),
        'seasons'  => $seasons,
        'career'   => $career,
    ];
}
