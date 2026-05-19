<?php

function getWhatIfChartData(): array {
    // regular_season_matchups has one row per manager per matchup (duplicated),
    // so manager1 side alone covers every game for every manager.
    $result = query("
        SELECT m.id, m.name,
            SUM(rsm.manager1_score)   AS total_actual,
            SUM(rsm.manager1_optimal) AS total_optimal
        FROM regular_season_matchups rsm
        JOIN managers m ON m.id = rsm.manager1_id
        WHERE rsm.manager1_optimal IS NOT NULL AND rsm.manager1_optimal > 0
        GROUP BY m.id, m.name
        ORDER BY m.name
    ");

    $managers = [];
    while ($row = fetch_array($result)) {
        $actual  = (float)$row['total_actual'];
        $optimal = (float)$row['total_optimal'];
        $managers[] = [
            'id'            => (int)$row['id'],
            'name'          => $row['name'],
            'total_actual'  => round($actual, 2),
            'total_optimal' => round($optimal, 2),
            'points_missed' => round($optimal - $actual, 2),
            'accuracy'      => $optimal > 0 ? round($actual / $optimal * 100, 2) : 0,
        ];
    }
    return $managers;
}

function getWhatIfChartDataBySeason(): array {
    $result = query("
        SELECT rsm.year, m.id, m.name,
            SUM(rsm.manager1_score)   AS total_actual,
            SUM(rsm.manager1_optimal) AS total_optimal
        FROM regular_season_matchups rsm
        JOIN managers m ON m.id = rsm.manager1_id
        WHERE rsm.manager1_optimal IS NOT NULL AND rsm.manager1_optimal > 0
        GROUP BY rsm.year, m.id, m.name
        ORDER BY rsm.year DESC, m.name
    ");

    $data = [];
    while ($row = fetch_array($result)) {
        $actual  = (float)$row['total_actual'];
        $optimal = (float)$row['total_optimal'];
        $key = $row['id'] . '-' . $row['year'];
        $data[$key] = [
            'id'        => (int)$row['id'],
            'name'      => $row['name'],
            'year'      => (int)$row['year'],
            'accuracy'  => $optimal > 0 ? round($actual / $optimal * 100, 2) : 0,
        ];
    }
    return $data;
}

function getWhatIfWinLoss(): array {
    // regular_season_matchups has one row per manager per matchup (duplicated),
    // so using only manager1 gives each manager's complete game history without double-counting.
    $result = query("
        SELECT
            m1.id AS m1_id, m1.name AS m1_name,
            rsm.manager1_score   AS my_score,
            rsm.manager2_score   AS opp_score,
            rsm.manager1_optimal AS my_opt,
            rsm.manager2_optimal AS opp_opt
        FROM regular_season_matchups rsm
        JOIN managers m1 ON m1.id = rsm.manager1_id
        WHERE rsm.manager1_optimal IS NOT NULL AND rsm.manager1_optimal > 0
          AND rsm.manager2_optimal IS NOT NULL AND rsm.manager2_optimal > 0
    ");

    $stats = [];

    while ($row = fetch_array($result)) {
        $my     = (float)$row['my_score'];
        $opp    = (float)$row['opp_score'];
        $myOpt  = (float)$row['my_opt'];
        $oppOpt = (float)$row['opp_opt'];
        $id     = $row['m1_id'];
        $name   = $row['m1_name'];

        if (!isset($stats[$id])) {
            $stats[$id] = [
                'id' => (int)$id, 'name' => $name,
                'games'         => 0,
                'actual_wins'   => 0,
                'self_opt_wins' => 0,
                'opp_opt_wins'  => 0,
                'both_opt_wins' => 0,
            ];
        }
        $stats[$id]['games']++;
        if ($my    > $opp)    $stats[$id]['actual_wins']++;
        if ($myOpt > $opp)    $stats[$id]['self_opt_wins']++;
        if ($my    > $oppOpt) $stats[$id]['opp_opt_wins']++;
        if ($myOpt > $oppOpt) $stats[$id]['both_opt_wins']++;
    }

    foreach ($stats as &$m) {
        $m['actual_losses']   = $m['games'] - $m['actual_wins'];
        $m['self_opt_losses'] = $m['games'] - $m['self_opt_wins'];
        $m['opp_opt_losses']  = $m['games'] - $m['opp_opt_wins'];
        $m['both_opt_losses'] = $m['games'] - $m['both_opt_wins'];
        $m['self_opt_delta']  = $m['self_opt_wins']  - $m['actual_wins'];
        $m['opp_opt_delta']   = $m['opp_opt_wins']   - $m['actual_wins'];
        $m['both_opt_delta']  = $m['both_opt_wins']  - $m['actual_wins'];
    }
    unset($m);

    uasort($stats, fn($a, $b) => $b['self_opt_delta'] <=> $a['self_opt_delta']);
    return array_values($stats);
}

function getWhatIfWinLossBySeason(): array {
    $result = query("
        SELECT
            rsm.year,
            m1.id AS m1_id, m1.name AS m1_name,
            rsm.manager1_score   AS my_score,
            rsm.manager2_score   AS opp_score,
            rsm.manager1_optimal AS my_opt,
            rsm.manager2_optimal AS opp_opt
        FROM regular_season_matchups rsm
        JOIN managers m1 ON m1.id = rsm.manager1_id
        WHERE rsm.manager1_optimal IS NOT NULL AND rsm.manager1_optimal > 0
          AND rsm.manager2_optimal IS NOT NULL AND rsm.manager2_optimal > 0
    ");

    $stats = [];

    while ($row = fetch_array($result)) {
        $my     = (float)$row['my_score'];
        $opp    = (float)$row['opp_score'];
        $myOpt  = (float)$row['my_opt'];
        $oppOpt = (float)$row['opp_opt'];
        $id     = $row['m1_id'];
        $name   = $row['m1_name'];
        $year   = (int)$row['year'];

        $key = "$id-$year";
        if (!isset($stats[$key])) {
            $stats[$key] = [
                'id' => (int)$id, 'name' => $name, 'year' => $year,
                'games'         => 0,
                'actual_wins'   => 0,
                'self_opt_wins' => 0,
                'opp_opt_wins'  => 0,
                'both_opt_wins' => 0,
            ];
        }
        $stats[$key]['games']++;
        if ($my    > $opp)    $stats[$key]['actual_wins']++;
        if ($myOpt > $opp)    $stats[$key]['self_opt_wins']++;
        if ($my    > $oppOpt) $stats[$key]['opp_opt_wins']++;
        if ($myOpt > $oppOpt) $stats[$key]['both_opt_wins']++;
    }

    foreach ($stats as &$m) {
        $m['actual_losses']   = $m['games'] - $m['actual_wins'];
        $m['self_opt_losses'] = $m['games'] - $m['self_opt_wins'];
        $m['opp_opt_losses']  = $m['games'] - $m['opp_opt_wins'];
        $m['both_opt_losses'] = $m['games'] - $m['both_opt_wins'];
        $m['self_opt_delta']  = $m['self_opt_wins']  - $m['actual_wins'];
        $m['opp_opt_delta']   = $m['opp_opt_wins']   - $m['actual_wins'];
        $m['both_opt_delta']  = $m['both_opt_wins']  - $m['actual_wins'];
    }
    unset($m);

    usort($stats, fn($a, $b) =>
        $b['year'] <=> $a['year'] ?: $b['self_opt_delta'] <=> $a['self_opt_delta']
    );
    return $stats;
}

function getPlayoffScenarios(): array {
    $result = query("
        SELECT pm.year, pm.round,
            m1.name AS manager1, m2.name AS manager2,
            m1.id AS m1_id, m2.id AS m2_id,
            pm.manager1_score, pm.manager2_score,
            pm.manager1_optimal, pm.manager2_optimal,
            pm.manager1_seed, pm.manager2_seed
        FROM playoff_matchups pm
        JOIN managers m1 ON m1.id = pm.manager1_id
        JOIN managers m2 ON m2.id = pm.manager2_id
        WHERE pm.manager1_optimal IS NOT NULL AND pm.manager2_optimal IS NOT NULL
        ORDER BY pm.year DESC,
            CASE pm.round WHEN 'Final' THEN 1 WHEN 'Semifinal' THEN 2 WHEN 'Quarterfinal' THEN 3 END
    ");

    $matchups = [];
    $summary  = [];

    while ($row = fetch_array($result)) {
        $m1Score = (float)$row['manager1_score'];
        $m2Score = (float)$row['manager2_score'];
        $m1Opt   = (float)$row['manager1_optimal'];
        $m2Opt   = (float)$row['manager2_optimal'];

        $m1Won        = $m1Score >= $m2Score;
        $m1Reversal   = !$m1Won && ($m1Opt > $m2Score);
        $m2Reversal   = $m1Won  && ($m2Opt > $m1Score);

        $actualWinner   = $m1Won ? $row['manager1'] : $row['manager2'];
        $actualLoser    = $m1Won ? $row['manager2'] : $row['manager1'];

        $matchups[] = [
            'year'           => (int)$row['year'],
            'round'          => $row['round'],
            'manager1'       => $row['manager1'],
            'manager2'       => $row['manager2'],
            'm1_id'          => (int)$row['m1_id'],
            'm2_id'          => (int)$row['m2_id'],
            'm1_seed'        => (int)$row['manager1_seed'],
            'm2_seed'        => (int)$row['manager2_seed'],
            'm1_score'       => round($m1Score, 2),
            'm2_score'       => round($m2Score, 2),
            'm1_optimal'     => round($m1Opt, 2),
            'm2_optimal'     => round($m2Opt, 2),
            'actual_winner'  => $actualWinner,
            'actual_loser'   => $actualLoser,
            'm1_reversal'    => $m1Reversal,
            'm2_reversal'    => $m2Reversal,
            'any_reversal'   => $m1Reversal || $m2Reversal,
        ];

        foreach (['m1' => $row['manager1'], 'm2' => $row['manager2']] as $side => $mgr) {
            if (!isset($summary[$mgr])) {
                $summary[$mgr] = ['Quarterfinal' => 0, 'Semifinal' => 0, 'Final' => 0, 'total' => 0];
            }
            $reversal = $side === 'm1' ? $m1Reversal : $m2Reversal;
            if ($reversal) {
                $summary[$mgr][$row['round']]++;
                $summary[$mgr]['total']++;
            }
        }
    }

    return ['matchups' => $matchups, 'summary' => $summary];
}
