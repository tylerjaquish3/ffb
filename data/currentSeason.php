<?php
// Current Season page data layer.
//
// Builds the roster-rank chart datasets for the Charts tab on
// /currentSeason.php. Ranks are computed in bulk via SQL window functions
// rather than looping the per-player getPlayerRank()/getPlayerPositionRank()
// helpers in functions.php, which would mean thousands of individual
// queries to cover a full season.
//
// Depends on query()/fetch_array() from functions.php.

// Manager color palette (matches getChartsManagerPalette() in
// data/charts.php and the $colors array in getSeasonStandings()).
// Last regular-season week number for a given year, per regular_season_matchups.
function getLastRegularWeek($year)
{
    $res = query("SELECT MAX(week_number) AS last_week FROM regular_season_matchups WHERE year = $year");
    $row = fetch_array($res);
    return $row ? (int) $row['last_week'] : 0;
}

function getRosterRankManagerPalette()
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

// Avg overall week rank per manager per week, for the "Avg Weekly Roster
// Rank" line chart. Only starters count toward a manager's average (BN/IR
// excluded) so a stacked bench a manager never played doesn't flatter their
// number — but bench players stay in the ranking pool itself, since they're
// still real rostered competition for everyone else's rank that week.
// Restricted to the regular season, matching the Standings
// By Week chart above it — playoff weeks have a much smaller rostered pool
// (only playoff teams), which would understate rank (look artificially
// better) for the teams that made it and skew any season-long average.
//
// Returns:
//   [
//     'weeks'    => ['Week 1', 'Week 2', ...],
//     'datasets' => [
//        ['label' => 'Tyler', 'data' => [12.3, 8.1, ...], 'borderColor' => '#...', ...],
//        ...
//     ],
//   ]
function getOverallWeeklyRankChartData($year)
{
    $palette = getRosterRankManagerPalette();

    $managers = [];
    $res = query("SELECT id, name FROM managers ORDER BY id");
    while ($row = fetch_array($res)) {
        $managers[(int) $row['id']] = $row['name'];
    }

    $lastWeek = getLastRegularWeek($year);

    $sql = "WITH pool AS (
                SELECT week, manager, roster_spot, points FROM rosters WHERE year = $year AND week <= $lastWeek
            ),
            ranked AS (
                SELECT week, manager, roster_spot,
                       RANK() OVER (PARTITION BY week ORDER BY points DESC) AS rnk
                FROM pool
            )
            SELECT manager, week, AVG(rnk) AS avg_rank
            FROM ranked
            WHERE roster_spot NOT IN ('IR', 'BN')
            GROUP BY manager, week
            ORDER BY week ASC, manager ASC";

    $byManager = [];
    $weekSet = [];

    $res = query($sql);
    while ($row = fetch_array($res)) {
        $week = (int) $row['week'];
        $byManager[$row['manager']][$week] = round((float) $row['avg_rank'], 1);
        $weekSet[$week] = true;
    }

    $weeks = array_keys($weekSet);
    sort($weeks);

    $datasets = [];
    foreach ($managers as $mid => $name) {
        if (empty($byManager[$name])) {
            continue;
        }
        $data = [];
        foreach ($weeks as $week) {
            $data[] = $byManager[$name][$week] ?? null;
        }
        $datasets[] = [
            'label' => $name,
            'data' => $data,
            'borderColor' => $palette[$mid] ?? '#9c68d9',
            'backgroundColor' => $palette[$mid] ?? '#9c68d9',
            'pointStyle' => 'circle',
            'pointRadius' => 4,
            'pointHoverRadius' => 6,
            'tension' => 0.3,
            'fill' => false,
        ];
    }

    return [
        'weeks' => array_map(fn ($w) => 'Week ' . $w, $weeks),
        'datasets' => $datasets,
    ];
}

// Avg position rank per manager per position, for the "Avg Position Rank"
// small-multiples chart (one horizontal bar panel per position). Restricted
// to the six core positions (legacy 'D' folded into 'DEF'; IDP/flex spots
// dropped) so each panel stays readable. Only starters count toward a
// manager's average (BN/IR excluded) — see getOverallWeeklyRankChartData()
// for why bench stays in the ranking pool. Regular season only — see
// getOverallWeeklyRankChartData() for why playoff weeks (a much smaller
// rostered pool) are left out.
//
// Returns one entry per position, each with its managers pre-sorted best
// (lowest avg rank) first:
//   [
//     ['position' => 'QB', 'rows' => [
//        ['manager' => 'Tyler', 'avgRank' => 3.2, 'color' => '#...'],
//        ...
//     ]],
//     ...
//   ]
function getPositionRankChartData($year)
{
    $palette = getRosterRankManagerPalette();

    $managerColors = [];
    $res = query("SELECT id, name FROM managers ORDER BY id");
    while ($row = fetch_array($res)) {
        $managerColors[$row['name']] = $palette[(int) $row['id']] ?? '#9c68d9';
    }

    $positions = ['QB', 'RB', 'WR', 'TE', 'K', 'DEF'];
    $positionList = "'QB','RB','WR','TE','K','DEF','D'";
    $lastWeek = getLastRegularWeek($year);

    $sql = "WITH pool AS (
                SELECT week, manager, roster_spot,
                       CASE WHEN position = 'D' THEN 'DEF' ELSE position END AS pos,
                       points
                FROM rosters
                WHERE year = $year AND week <= $lastWeek AND position IN ($positionList)
            ),
            ranked AS (
                SELECT manager, pos, roster_spot,
                       RANK() OVER (PARTITION BY week, pos ORDER BY points DESC) AS rnk
                FROM pool
            )
            SELECT manager, pos, AVG(rnk) AS avg_rank
            FROM ranked
            WHERE roster_spot NOT IN ('IR', 'BN')
            GROUP BY manager, pos";

    $byPosition = [];
    $res = query($sql);
    while ($row = fetch_array($res)) {
        $name = $row['manager'];
        if (!isset($managerColors[$name])) {
            continue;
        }
        $byPosition[$row['pos']][] = [
            'manager' => $name,
            'avgRank' => round((float) $row['avg_rank'], 1),
            'color' => $managerColors[$name],
        ];
    }

    $panels = [];
    foreach ($positions as $pos) {
        $rows = $byPosition[$pos] ?? [];
        usort($rows, fn ($a, $b) => $a['avgRank'] <=> $b['avgRank']);
        $panels[] = [
            'position' => $pos,
            'rows' => $rows,
        ];
    }

    return $panels;
}
