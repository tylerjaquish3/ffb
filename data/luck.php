<?php
header('Content-Type: application/json');
require_once '../functions.php';

$wins = getMatchups(true);
$losses = getMatchups(false);

$goodLuck = filterByRank($wins);
$badLuck = filterByRank($losses);
$closeWins = filterByMargin($wins);
$closeLosses = filterByMargin($losses);

$response = [];
$response['goodLuck'] = $goodLuck;
$response['badLuck'] = $badLuck;
$response['closeWins'] = $closeWins;
$response['closeLosses'] = $closeLosses;
$response['goodLuckSummary'] = getLuckSummary($goodLuck, $closeWins);
$response['badLuckSummary'] = getLuckSummary($badLuck, $closeLosses);

echo json_encode($response);

/**
 * All manager1-side rows for a given week where the manager won/lost,
 * with weekly rank and margin already computed for downstream filtering.
 */
function getMatchups($won)
{
    $comparison = $won ? '>' : '<';

    $sql = "SELECT * FROM (
        SELECT
            rsm.year,
            rsm.week_number AS week,
            m1.name AS manager,
            m1.id AS manager_id,
            rsm.manager1_score AS score,
            m2.name AS opponent,
            rsm.manager2_score AS opponent_score,
            RANK() OVER (PARTITION BY rsm.year, rsm.week_number ORDER BY rsm.manager1_score DESC) AS rank_top,
            RANK() OVER (PARTITION BY rsm.year, rsm.week_number ORDER BY rsm.manager1_score ASC) AS rank_bottom,
            COUNT(*) OVER (PARTITION BY rsm.year, rsm.week_number) AS league_size
        FROM regular_season_matchups rsm
        JOIN managers m1 ON m1.id = rsm.manager1_id
        JOIN managers m2 ON m2.id = rsm.manager2_id
    ) ranked
    WHERE score $comparison opponent_score
    ORDER BY year DESC, week DESC";

    $rows = [];
    $result = query($sql);
    while ($row = fetch_array($result)) {
        $rows[] = [
            'year' => (int)$row['year'],
            'week' => (int)$row['week'],
            'manager' => $row['manager'],
            'manager_id' => (int)$row['manager_id'],
            'score' => (float)$row['score'],
            'opponent' => $row['opponent'],
            'opponent_score' => (float)$row['opponent_score'],
            'rank' => $won ? (int)$row['rank_bottom'] : (int)$row['rank_top'],
            'league_size' => (int)$row['league_size'],
            'margin' => round(abs($row['score'] - $row['opponent_score']), 2),
        ];
    }

    return $rows;
}

/**
 * Rows where the manager was in the bottom/top 3 scorers of the week
 * (win side uses bottom-3, loss side uses top-3 — 'rank' is already the
 * relevant one per getMatchups()).
 */
function filterByRank($rows)
{
    return array_values(array_filter($rows, function ($row) {
        return $row['rank'] <= 3;
    }));
}

/**
 * Rows decided by 5 points or fewer.
 */
function filterByMargin($rows)
{
    return array_values(array_filter($rows, function ($row) {
        return $row['margin'] <= 5;
    }));
}

/**
 * Per-manager counts for the "by Manager" summary tables: how many times
 * a manager shows up in the top/bottom-points set, how many times in the
 * close-margin set, and the total distinct games across both (a game
 * that qualifies for both isn't double-counted in the total).
 */
function getLuckSummary($primaryRows, $closeRows)
{
    $managers = [];
    $result = query("SELECT name FROM managers ORDER BY name ASC");
    while ($row = fetch_array($result)) {
        $managers[$row['name']] = [
            'primaryCount' => 0,
            'closeCount' => 0,
            'keys' => [],
        ];
    }

    foreach ($primaryRows as $row) {
        $managers[$row['manager']]['primaryCount']++;
        $managers[$row['manager']]['keys'][$row['year'] . '-' . $row['week']] = true;
    }

    foreach ($closeRows as $row) {
        $managers[$row['manager']]['closeCount']++;
        $managers[$row['manager']]['keys'][$row['year'] . '-' . $row['week']] = true;
    }

    $summary = [];
    foreach ($managers as $manager => $data) {
        $summary[] = [
            'manager' => $manager,
            'primaryCount' => $data['primaryCount'],
            'closeCount' => $data['closeCount'],
            'total' => count($data['keys']),
        ];
    }

    usort($summary, function ($a, $b) {
        return $b['total'] - $a['total'];
    });

    return $summary;
}
