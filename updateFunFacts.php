<?php

/**
* Check if people are tied for the top spot
*/
function checkMultiple(string $query, string $field) : array
{
    $result = query($query);
    $rows = [];
    while ($row = fetch_array($result)) {
        $rows[] = $row;
    }
    $return = [];
    $first = true;
    $topValue = null;
    foreach ($rows as $item) {
        
        if (gettype($item) == "object") {
            $value = $item->{$field};
        } else {
            $value = $item[$field];
        }

        if ($first || $value == $topValue) {
            $return[] = $item;
            $topValue = $value;
        }
        $first = false;
    }

    return $return;
}

/**
* Add a manager fun fact
*/
function insertFunFact(int $ffId, string $manId, string $value, array $notes, array $tops)
{
    $facts = [];
    $result = query("SELECT * FROM manager_fun_facts WHERE fun_fact_id = {$ffId}");
    while($row = fetch_array($result)) {
        $facts[] = $row;
    }

    if (count($tops) > 1) {
        // Multiple managers are tied for the top spot
        // Get the existing ones to determine if any leaders are new
        // $facts = ManagerFunFact::where('fun_fact_id', $ffId)->get();
        foreach ($tops as $top) {
            $top->new_leader = 1;
            foreach ($facts as $fact) {
                if ($fact['manager_id'] == $top->{$manId}) {
                    $top->new_leader = 0;
                    break;
                }
            }
        }

        // Delete them all so we can prevent duplicates
        // ManagerFunFact::whereIn('id', $facts->pluck('id'))->delete();
        query("DELETE FROM manager_fun_facts WHERE fun_fact_id = {$ffId}");

        foreach ($tops as $top) {
            $note = '';
            foreach ($notes as $n) {
                $note .= is_null($top->{$n}) ? $n.' ' : $top->{$n}.' ';
            }

            firstOrCreate('manager_fun_facts', [
                'fun_fact_id' => $ffId,
                'manager_id' => $top->{$manId}
            ],[
                'rank' => 1,
                'value' => round($top->{$value},2),
                'note' => $note,
                'new_leader' => $top->new_leader,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }
        
    } else {
        // One manager has the top spot on their own
        $top = $tops[0];
        $newLeader = 1;
        // $facts = ManagerFunFact::where('fun_fact_id', $ffId)->get();
        foreach ($facts as $fact) {
            if ($fact['manager_id'] == $top[$manId]) {
                $newLeader = 0;
                break;
            }
        }

        // If there's more than one manager saved for this fact, delete them all
        // because we now only have one
        if (count($facts) > 1) {
            // ManagerFunFact::whereIn('id', $facts->pluck('id'))->delete();
            query("DELETE FROM manager_fun_facts WHERE fun_fact_id = {$ffId}");
        }
        $note = '';
        foreach ($notes as $n) {
            $note .= isset($top[$n]) ? $top[$n].' ' : $n.' ' ;
        }

        updateOrCreate('manager_fun_facts',[
            'fun_fact_id' => $ffId,
        ],[
            'manager_id' => $top[$manId],
            'rank' => 1,
            'value' => round($top[$value],2),
            'note' => $note,
            'new_leader' => $newLeader,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }
}

// 1,2,3
function mostPointsFor()
{
    // Most PF (All Time)
    $q = "SELECT manager1_id, SUM(manager1_score) as pts FROM regular_season_matchups GROUP BY manager1_id ORDER BY pts DESC";

    $tops = checkMultiple($q, 'pts');
    insertFunFact(1, 'manager1_id', 'pts', [], $tops);

    // Most PF (Season)
    $q = "SELECT manager1_id, year, SUM(manager1_score) as pts FROM regular_season_matchups GROUP BY manager1_id, year ORDER BY pts DESC";

    $tops = checkMultiple($q, 'pts');
    insertFunFact(2, 'manager1_id', 'pts', ['year'], $tops);
    
    // // Most PF (Week)
    $q = "SELECT manager1_id, manager1_score, week_number, year FROM regular_season_matchups ORDER BY manager1_score DESC";

    $tops = checkMultiple($q, 'manager1_score');
    insertFunFact(3, 'manager1_id', 'manager1_score', ['Wk','week_number','year'], $tops);
}

// 4,5,6
function mostPostseasonPointsFor()
{
    // Most PF (All Time)
    $q = 'SELECT managers.id, ptsTop+ptsBottom AS pts, gamest+gamesb
        FROM managers
        LEFT JOIN (
        SELECT COUNT(id) as gamest, SUM(manager1_score) AS ptsTop, manager1_id FROM playoff_matchups i
        GROUP BY manager1_id
        ) w ON w.manager1_id = managers.id
        LEFT JOIN (
        SELECT COUNT(id) as gamesb, SUM(manager2_score) AS ptsBottom, manager2_id FROM playoff_matchups i
        GROUP BY manager2_id
        ) l ON l.manager2_id = managers.id
        ORDER BY pts desc';

    $tops = checkMultiple($q, 'pts');
    insertFunFact(4, 'id', 'pts', [], $tops);

    // Most PF (Season)
    // $i = PlayoffMatchup::selectRaw('manager1_id, year, SUM(manager1_score) as pts')
    //     ->orderBy('pts', 'desc')
    //     ->groupBy('manager1_id', 'year')
    //     ->get();
    $i = [];
    $q = query("SELECT manager1_id, year, SUM(manager1_score) as pts FROM playoff_matchups GROUP BY manager1_id, year ORDER BY pts DESC");
    while ($row = fetch_array($q)) {
        $i[] = $row;
    }

    // $j = PlayoffMatchup::selectRaw('manager2_id, year, SUM(manager2_score) as pts')
    //     ->orderBy('pts', 'desc')
    //     ->groupBy('manager2_id', 'year')
    //     ->get();
    $j = [];
    $q = query("SELECT manager2_id, year, SUM(manager2_score) as pts FROM playoff_matchups GROUP BY manager2_id, year ORDER BY pts DESC");
    while ($row = fetch_array($q)) {
        $j[] = $row;
    }

    $end = [];

    foreach ($j as $bottom) {
        $end[$bottom['year']][$bottom['manager2_id']] = $bottom['pts'];
    }
    foreach ($i as $top) {
        if (!isset($end[$top['year']][$top['manager1_id']])) {
            $end[$top['year']][$top['manager1_id']] = 0;
        }

        $end[$top['year']][$top['manager1_id']] += $top['pts'];
        foreach ($j as $bottom) {
            $topPts = $bottomPts = 0;
            if ($top['manager1_id'] == $bottom['manager2_id'] && $top['year'] == $bottom['year']) {
                $end[$top['year']][$top['manager1_id']] += $bottomPts;
            }
        }
    }

    $largest = 0;
    foreach ($end as $year => $managers) {
        foreach ($managers as $id => $value) {
            if ($value > $largest) {
                $largest = $value;
                $lYear = $year;
                $lId = $id;
            }
        }
    }

    ManagerFunFact::updateOrCreate([
        'fun_fact_id' => 5,
    ],[
        'value' => round($largest,2),
        'manager_id' => $lId,
        'rank' => 1,
        'note' => $lYear
    ]);

    // Most PF (Week)
    $i = PlayoffMatchup::orderBy('manager1_score', 'desc')->first();
    $j = PlayoffMatchup::orderBy('manager2_score', 'desc')->first();

    $bestTop = true;
    if ($i->manager1_score < $j->manager2_score) {
        $bestTop = false;
    }

    ManagerFunFact::updateOrCreate([
        'fun_fact_id' => 6,
    ],[
        'manager_id' => $bestTop ? $i->manager1_id : $j->manager2_id,
        'value' => $bestTop ? round($i->manager1_score,2) : round($j->manager2_score,2),
        'rank' => 1,
        'note' => $bestTop ? $i->round.' '.$i->year : $j->round.' '.$j->year
    ]);
}

// 7,8,9,89,90,91
function leastPointsAgainst()
{
    // Least PA (All Time)
    $i = RegularSeasonMatchup::selectRaw('manager1_id, SUM(manager2_score) as pts')
        ->orderBy('pts', 'asc')
        ->groupBy('manager1_id')
        ->get();

    $tops = checkMultiple($i, 'pts');
    insertFunFact(7, 'manager1_id', 'pts', [], $tops);
    
    // Least PA (Season)
    $i = RegularSeasonMatchup::selectRaw('regular_season_matchups.manager1_id, regular_season_matchups.year, SUM(regular_season_matchups.manager2_score) as pts')
        ->join('playoff_matchups', 'playoff_matchups.year', '=', 'regular_season_matchups.year')
        ->orderBy('pts', 'asc')
        ->groupBy('manager1_id', 'year')
        ->get();

    $tops = checkMultiple($i, 'pts');
    insertFunFact(8, 'manager1_id', 'pts', ['year'], $tops);

    // Least PA (Week)
    $i = RegularSeasonMatchup::selectRaw('manager1_id, manager2_score, week_number, year')
        ->orderBy('manager2_score', 'asc')
        ->get();

    $tops = checkMultiple($i, 'manager2_score');
    insertFunFact(9, 'manager1_id', 'manager2_score', ['Wk','week_number','year'], $tops);

    // Most PA (All Time)
    $i = RegularSeasonMatchup::selectRaw('manager1_id, SUM(manager2_score) as pts')
        ->orderBy('pts', 'desc')
        ->groupBy('manager1_id')
        ->get();

    $tops = checkMultiple($i, 'pts');
    insertFunFact(89, 'manager1_id', 'pts', [], $tops);
    
    // Most PA (Season)
    $i = RegularSeasonMatchup::selectRaw('regular_season_matchups.manager1_id, regular_season_matchups.year, SUM(regular_season_matchups.manager2_score) as pts')
        ->join('playoff_matchups', 'playoff_matchups.year', '=', 'regular_season_matchups.year')
        ->orderBy('pts', 'desc')
        ->groupBy('manager1_id', 'year')
        ->get();

    $tops = checkMultiple($i, 'pts');
    insertFunFact(90, 'manager1_id', 'pts', ['year'], $tops);

    // Most PA (Week)
    $i = RegularSeasonMatchup::selectRaw('manager1_id, manager2_score, week_number, year')
        ->orderBy('manager2_score', 'desc')
        ->get();

    $tops = checkMultiple($i, 'manager2_score');
    insertFunFact(91, 'manager1_id', 'manager2_score', ['Wk','week_number','year'], $tops);
}

function mostWins()
{
    // Most Wins (All time)
    $i = RegularSeasonMatchup::selectRaw('manager1_id, count(id) as wins')
        ->whereRaw('manager1_score > manager2_score')
        ->groupBy('manager1_id')
        ->orderBy('wins', 'desc')
        ->get();

    $tops = checkMultiple($i, 'wins');
    insertFunFact(10, 'manager1_id', 'wins', [], $tops);
    
    // Most Wins (Season)
    $i = RegularSeasonMatchup::selectRaw('manager1_id, count(id) as wins, year')
        ->whereRaw('manager1_score > manager2_score')
        ->groupBy('manager1_id', 'year')
        ->orderBy('wins', 'desc')
        ->get();

    $tops = checkMultiple($i, 'wins');;
    insertFunFact(11, 'manager1_id', 'wins', ['year'], $tops);
}

function leastPointsFor()
{
    // Least PF (All Time)
    $i = RegularSeasonMatchup::selectRaw('manager1_id, SUM(manager1_score) as pts')
        ->orderBy('pts', 'asc')
        ->groupBy('manager1_id')
        ->get();

    $tops = checkMultiple($i, 'pts');;
    insertFunFact(13, 'manager1_id', 'pts', [], $tops);

    // Least PF (Season)
    $i = RegularSeasonMatchup::selectRaw('regular_season_matchups.manager1_id, regular_season_matchups.year, SUM(regular_season_matchups.manager1_score) as pts')
        ->join('playoff_matchups', 'playoff_matchups.year', '=', 'regular_season_matchups.year')
        ->orderBy('pts', 'asc')
        ->groupBy('manager1_id', 'year')
        ->get();
        
    $tops = checkMultiple($i, 'pts');;
    insertFunFact(14, 'manager1_id', 'pts', ['year'], $tops);

    // Least PF (Week)
    $i = RegularSeasonMatchup::selectRaw('manager1_id, manager1_score, week_number, year')
        ->orderBy('manager1_score', 'asc')
        ->get();

    $tops = checkMultiple($i, 'manager1_score');
    insertFunFact(15, 'manager1_id', 'manager1_score', ['Wk','week_number','year'], $tops);
}

function mostLosses()
{
    // Most Losses (All time)
    $i = RegularSeasonMatchup::selectRaw('manager1_id, count(id) as losses')
        ->whereRaw('manager1_score < manager2_score')
        ->groupBy('manager1_id')
        ->orderBy('losses', 'desc')
        ->get();

    $tops = checkMultiple($i, 'losses');
    insertFunFact(16, 'manager1_id', 'losses', [], $tops);

    // Most Losses (Season)
    $i = RegularSeasonMatchup::selectRaw('manager1_id, count(id) as losses, year')
        ->whereRaw('manager1_score < manager2_score')
        ->groupBy('manager1_id', 'year')
        ->orderBy('losses', 'desc')
        ->limit(50)
        ->get();

    $tops = checkMultiple($i, 'losses');
    insertFunFact(17, 'manager1_id', 'losses', ['year'], $tops);
}

function postseasonRecords()
{
    $p = PlayoffMatchup::all();

    $types = ['total', 'topSeedLoss', 'underdogWin'];
    for ($x = 1; $x < 11; $x++) {
        foreach ($types as $type) {
            $all[$x][$type] = [
                'Total' => 0,
                'Quarterfinal' => 0,
                'Semifinal' => 0,
                'Final' => 0,
            ];
        }
    }

    foreach ($p as $m) {
        if ($m->manager1_score > $m->manager2_score) {
            // m1 won
            $all[$m->manager1_id]['total']['Total']++;
            $all[$m->manager1_id]['total'][$m->round]++;
            if ($m->manager1_seed < $m->manager2_seed) {
                // man 1 is top seed
            } else {
                // man 2 is top seed
                $all[$m->manager1_id]['underdogWin'][$m->round]++;
                $all[$m->manager1_id]['underdogWin']['Total']++;
                $all[$m->manager2_id]['topSeedLoss'][$m->round]++;
                $all[$m->manager2_id]['topSeedLoss']['Total']++;
            }
        } else {
            // m2 won
            $all[$m->manager2_id]['total']['Total']++;
            $all[$m->manager2_id]['total'][$m->round]++;
            if ($m->manager1_seed < $m->manager2_seed) {
                // man 1 is top seed
                $all[$m->manager2_id]['underdogWin'][$m->round]++;
                $all[$m->manager2_id]['underdogWin']['Total']++;
                $all[$m->manager1_id]['topSeedLoss'][$m->round]++;
                $all[$m->manager1_id]['topSeedLoss']['Total']++;
            } else {
                // man 2 is top seed
            }
        }
    }

    $mostWins = $mostQ = $mostS = $mostF = 0;
    $mostTopLoss = $mostTopLossQ = $mostTopLossS = $mostTopLossF = 0;
    $mostUnderWins = $mostUnderWinsQ = $mostUnderWinsS = $mostUnderWinsF = 0;
    
    foreach ($all as $manId => $array) {
        foreach ($array['total'] as $round => $val) {
            if ($round == 'Total' && $val > $mostWins) {
                $mostWins = $val;
            } elseif ($round == 'Quarterfinal' && $val > $mostQ) {
                $mostQ = $val;
            } elseif ($round == 'Semifinal' && $val > $mostS) {
                $mostS = $val;
            } elseif ($round == 'Final' && $val > $mostF) {
                $mostF = $val;
            }
        }
        foreach ($array['topSeedLoss'] as $round => $val) {
            if ($round == 'Total' && $val > $mostTopLoss) {
                $mostTopLoss = $val;
            } elseif ($round == 'Quarterfinal' && $val > $mostTopLossQ) {
                $mostTopLossQ = $val;
            } elseif ($round == 'Semifinal' && $val > $mostTopLossS) {
                $mostTopLossS = $val;
            } elseif ($round == 'Final' && $val > $mostTopLossF) {
                $mostTopLossF = $val;
            }
        }
        foreach ($array['underdogWin'] as $round => $val) {
            if ($round == 'Total' && $val > $mostUnderWins) {
                $mostUnderWins = $val;
            } elseif ($round == 'Quarterfinal' && $val > $mostUnderWinsQ) {
                $mostUnderWinsQ = $val;
            } elseif ($round == 'Semifinal' && $val > $mostUnderWinsS) {
                $mostUnderWinsS = $val;
            } elseif ($round == 'Final' && $val > $mostUnderWinsF) {
                $mostUnderWinsF = $val;
            }
        }
    }

    // Now need to check if any are tied with leader
    $mostWinsa = $mostQa = $mostSa = $mostFa = [];
    $mostTopLossa = $mostTopLossQa = $mostTopLossSa = $mostTopLossFa = [];
    $mostUnderWinsa = $mostUnderWinsQa = $mostUnderWinsSa = $mostUnderWinsFa = [];

    foreach ($all as $manId => $array) {
        foreach ($array['total'] as $round => $val) {
            if ($round == 'Total' && $val == $mostWins) {
                $mostWinsa[] = (object)['val' => $val, 'man' => $manId];
            } elseif ($round == 'Quarterfinal' && $val == $mostQ) {
                $mostQa[] = (object)['val' => $val, 'man' => $manId];
            } elseif ($round == 'Semifinal' && $val == $mostS) {
                $mostSa[] = (object)['val' => $val, 'man' => $manId];
            } elseif ($round == 'Final' && $val == $mostF) {
                $mostFa[] = (object)['val' => $val, 'man' => $manId];
            }
        }
        foreach ($array['topSeedLoss'] as $round => $val) {
            if ($round == 'Total' && $val == $mostTopLoss) {
                $mostTopLossa[] = (object)['val' => $val, 'man' => $manId];
            } elseif ($round == 'Quarterfinal' && $val == $mostTopLossQ) {
                $mostTopLossQa[] = (object)['val' => $val, 'man' => $manId];
            } elseif ($round == 'Semifinal' && $val == $mostTopLossS) {
                $mostTopLossSa[] = (object)['val' => $val, 'man' => $manId];
            } elseif ($round == 'Final' && $val == $mostTopLossF) {
                $mostTopLossFa[] = (object)['val' => $val, 'man' => $manId];
            }
        }
        foreach ($array['underdogWin'] as $round => $val) {
            if ($round == 'Total' && $val == $mostUnderWins) {
                $mostUnderWinsa[] = (object)['val' => $val, 'man' => $manId];
            } elseif ($round == 'Quarterfinal' && $val == $mostUnderWinsQ) {
                $mostUnderWinsQa[] = (object)['val' => $val, 'man' => $manId];
            } elseif ($round == 'Semifinal' && $val == $mostUnderWinsS) {
                $mostUnderWinsSa[] = (object)['val' => $val, 'man' => $manId];
            } elseif ($round == 'Final' && $val == $mostUnderWinsF) {
                $mostUnderWinsFa[] = (object)['val' => $val, 'man' => $manId];
            }
        }
    }

    $mostWinsa = collect($mostWinsa);
    $tops = checkMultiple($mostWinsa, 'val');
    insertFunFact(12, 'man', 'val', [], $tops);

    $mostQa = collect($mostQa);
    $tops = checkMultiple($mostQa, 'val');
    insertFunFact(65, 'man', 'val', [], $tops);

    $mostSa = collect($mostSa);
    $tops = checkMultiple($mostSa, 'val');
    insertFunFact(66, 'man', 'val', [], $tops);

    $mostFa = collect($mostFa);
    $tops = checkMultiple($mostFa, 'val');
    insertFunFact(31, 'man', 'val', [], $tops);

    $mostUnderWinsa = collect($mostUnderWinsa);
    $tops = checkMultiple($mostUnderWinsa, 'val');
    insertFunFact(18, 'man', 'val', [], $tops);

    $mostUnderWinsQa = collect($mostUnderWinsQa);
    $tops = checkMultiple($mostUnderWinsQa, 'val');
    insertFunFact(19, 'man', 'val', [], $tops);

    $mostUnderWinsSa = collect($mostUnderWinsSa);
    $tops = checkMultiple($mostUnderWinsSa, 'val');
    insertFunFact(20, 'man', 'val', [], $tops);

    $mostUnderWinsFa = collect($mostUnderWinsFa);
    $tops = checkMultiple($mostUnderWinsFa, 'val');
    insertFunFact(21, 'man', 'val', [], $tops);

    $mostTopLossa = collect($mostTopLossa);
    $tops = checkMultiple($mostTopLossa, 'val');
    insertFunFact(22, 'man', 'val', [], $tops);

    $mostTopLossQa = collect($mostTopLossQa);
    $tops = checkMultiple($mostTopLossQa, 'val');
    insertFunFact(23, 'man', 'val', [], $tops);

    $mostTopLossSa = collect($mostTopLossSa);
    $tops = checkMultiple($mostTopLossSa, 'val');
    insertFunFact(24, 'man', 'val', [], $tops);

    $mostTopLossFa = collect($mostTopLossFa);
    $tops = checkMultiple($mostTopLossFa, 'val');
    insertFunFact(25, 'man', 'val', [], $tops);
}

function highestSeeds()
{
    $i = PlayoffMatchup::selectRaw('manager1_id, count(id) as num1')
        ->where('round', 'Semifinal')
        ->where('manager1_seed', 1)
        ->groupBy('manager1_id')
        ->orderBy('num1', 'desc')
        ->get();

    $j = PlayoffMatchup::selectRaw('manager1_id, count(id) as num2')
        ->where('round', 'Semifinal')
        ->where('manager1_seed', 2)
        ->groupBy('manager1_id')
        ->orderBy('num2', 'desc')
        ->get();

    $k = PlayoffMatchup::selectRaw('manager2_id, count(id) as num2')
        ->where('round', 'Semifinal')
        ->where('manager2_seed', 2)
        ->groupBy('manager2_id')
        ->orderBy('num2', 'desc')
        ->get();

    for ($x = 1; $x < 11; $x++) {
        $all[$x] = [
            'num1' => 0,
            'num2' => 0,
            'total' => 0,
            'manId' => $x
        ];
    }

    foreach ($i as $num1) {
        $all[$num1->manager1_id]['num1'] = $num1->num1;
        $all[$num1->manager1_id]['total'] = $num1->num1;
    }
    foreach ($j as $num2) {
        $all[$num2->manager1_id]['total'] += $num2->num2;
        $all[$num2->manager1_id]['num2'] += $num2->num2;
    }
    foreach ($k as $num2) {
        $all[$num2->manager2_id]['total'] += $num2->num2;
        $all[$num2->manager2_id]['num2'] += $num2->num2;
    }
    // Sort by #1 seeds
    usort($all, function ($item1, $item2) {
        return $item2['num1'] <=> $item1['num1'];
    });
    $sorted1 = $all;
    // Convert the arrays to objects
    foreach ($sorted1 as &$arrays) {
        $arrays = (object)$arrays;
    }
    $oneSeeds = collect($sorted1);
    $tops = checkMultiple($oneSeeds, 'num1');
    insertFunFact(26, 'manId', 'num1', [], $tops);

    // Sort by #2 seeds
    usort($all, function ($item1, $item2) {
        return $item2['num2'] <=> $item1['num2'];
    });
    $sorted2 = $all;
    foreach ($sorted2 as &$arrays) {
        $arrays = (object)$arrays;
    }
    $twoSeeds = collect($sorted2);
    $tops = checkMultiple($twoSeeds, 'num2');
    insertFunFact(27, 'manId', 'num2', [], $tops);

    // Sort by total
    usort($all, function ($item1, $item2) {
        return $item2['total'] <=> $item1['total'];
    });
    $sorted3 = $all;
    foreach ($sorted3 as &$arrays) {
        $arrays = (object)$arrays;
    }
    $topSeeds = collect($sorted3);
    $tops = checkMultiple($topSeeds, 'total');
    insertFunFact(28, 'manId', 'total', [], $tops);
}

// 67,68,29,30,69,70
function singleOpponent()
{
    $i = RegularSeasonMatchup::selectRaw('name, manager2_id, SUM(manager2_score) as pts, COUNT(regular_season_matchups.id) as gms')
        ->join('managers', 'managers.id', '=', 'regular_season_matchups.manager1_id')
        ->orderBy('pts', 'desc')
        ->groupBy('manager1_id', 'manager2_id')
        ->get();

    $tops = checkMultiple($i, 'pts');
    insertFunFact(67, 'manager2_id', 'pts', ['gms','matchups vs.','name'], $tops);

    $i = RegularSeasonMatchup::selectRaw('name, manager2_id, SUM(manager2_score) as pts, COUNT(regular_season_matchups.id) as gms')
        ->join('managers', 'managers.id', '=', 'regular_season_matchups.manager1_id')
        ->orderBy('pts', 'asc')
        ->groupBy('manager1_id', 'manager2_id')
        ->get();

    $tops = checkMultiple($i, 'pts');
    insertFunFact(68, 'manager2_id', 'pts', ['gms','matchups vs.','name'], $tops);

    // Wins/losses vs single opponent
    $i = RegularSeasonMatchup::selectRaw('manager1_id, manager2_id, m1.name as m1name, m2.name as m2name, SUM(IF(winning_manager_id = manager1_id, 1, 0)) as wins')
        ->join('managers as m1', 'm1.id', '=', 'regular_season_matchups.manager1_id')
        ->join('managers as m2', 'm2.id', '=', 'regular_season_matchups.manager2_id')
        ->orderBy('wins', 'desc')
        ->groupBy('manager1_id', 'manager2_id')
        ->get();

    $tops = checkMultiple($i, 'wins');
    insertFunFact(29, 'manager1_id', 'wins', ['vs.','m2name'], $tops);
    insertFunFact(30, 'manager2_id', 'wins', ['vs.','m1name'], $tops);

    $i = RegularSeasonMatchup::selectRaw('manager1_id, manager2_id, m1.name as m1name, m2.name as m2name, SUM(IF(winning_manager_id = manager1_id, 1, 0)) as wins')
        ->join('managers as m1', 'm1.id', '=', 'regular_season_matchups.manager1_id')
        ->join('managers as m2', 'm2.id', '=', 'regular_season_matchups.manager2_id')
        ->orderBy('wins', 'asc')
        ->groupBy('manager1_id', 'manager2_id')
        ->get();

    $tops = checkMultiple($i, 'wins');
    insertFunFact(69, 'manager1_id', 'wins', ['vs.','m2name'], $tops);
    insertFunFact(70, 'manager2_id', 'wins', ['vs.','m1name'], $tops);
}

// 32
function leastChampionships()
{
    $finishes = Finish::selectRaw('manager_id, SUM(IF(finish = 1, 1, 0)) as wins')
        ->orderBy('wins', 'asc')
        ->groupBy('manager_id')
        ->get();

    $tops = checkMultiple($finishes, 'wins');
    insertFunFact(32, 'manager_id', 'wins', [], $tops);
}

// 50,51,52,53,54,55
function postseasonMargin()
{
    $i = PlayoffMatchup::selectRaw('manager1_id, manager2_id, year, round, ABS(manager1_score - manager2_score) as diff, IF(manager1_score > manager2_score, manager1_id, manager2_id) as winner')
        ->orderBy('diff', 'desc')
        ->get();

    $tops = checkMultiple($i, 'diff');
    insertFunFact(50, 'winner', 'diff', ['year','round'], $tops);

    $i = PlayoffMatchup::selectRaw('manager1_id, manager2_id, year, round, ABS(manager1_score - manager2_score) as diff,
        IF(manager1_score > manager2_score, manager1_id, manager2_id) as winner, IF(manager1_score > manager2_score, manager2_id, manager1_id) as loser')
        ->where('round', 'Final')
        ->orderBy('diff', 'desc')
        ->get();

    $tops = checkMultiple($i, 'diff');
    insertFunFact(52, 'winner', 'diff', ['year'], $tops);

    $tops = checkMultiple($i, 'diff');
    insertFunFact(54, 'loser', 'diff', ['year'], $tops);

    $i = PlayoffMatchup::selectRaw('manager1_id, manager2_id, year, round, ABS(manager1_score - manager2_score) as diff, IF(manager1_score > manager2_score, manager1_id, manager2_id) as winner')
        ->orderBy('diff', 'asc')
        ->get();

    $tops = checkMultiple($i, 'diff');
    insertFunFact(51, 'winner', 'diff', ['year','round'], $tops);

    $i = PlayoffMatchup::selectRaw('manager1_id, manager2_id, year, round, ABS(manager1_score - manager2_score) as diff,
        IF(manager1_score > manager2_score, manager1_id, manager2_id) as winner, IF(manager1_score > manager2_score, manager2_id, manager1_id) as loser')
        ->where('round', 'Final')
        ->orderBy('diff', 'asc')
        ->get();

    $tops = checkMultiple($i, 'diff');
    insertFunFact(53, 'winner', 'diff', ['year'], $tops);

    $tops = checkMultiple($i, 'diff');
    insertFunFact(55, 'loser', 'diff', ['year'], $tops);
}

// 39,40,56,57,60,61
function streaks()
{
    $longestWin = $longestLose = 0;

    for ($x = 1; $x < 11; $x++) {
        $rsm = RegularSeasonMatchup::where('manager1_id', $x)->orderBy('year', 'asc')->orderBy('week_number', 'asc')->get();

        $myStreakWin = $myLongestWin = $myStreakLose = $myLongestLose = 0;
        foreach ($rsm as $m) {
            if ($m->winning_manager_id == $x) {
                $myStreakLose = 0;
                $myStreakWin++;
                if ($myStreakWin > $myLongestWin) {
                    $myLongestWin = $myStreakWin;
                }
            } else {
                $myStreakWin = 0;
                $myStreakLose++;
                if ($myStreakLose > $myLongestLose) {
                    $myLongestLose = $myStreakLose;
                }
            }
        }
        if ($myLongestWin > $longestWin) {
            $longestWin = $myLongestWin;
        }
        if ($myLongestLose > $longestLose) {
            $longestLose = $myLongestLose;
        }
    }

    // Now loop through again to account for managers that are tied
    $longestWina = $longestLosea = [];
    for ($x = 1; $x < 11; $x++) {
        $rsm = RegularSeasonMatchup::where('manager1_id', $x)->orderBy('year', 'asc')->orderBy('week_number', 'asc')->get();

        $myStreakWin = $myLongestWin = $myStreakLose = $myLongestLose = 0;
        foreach ($rsm as $m) {
            if ($m->winning_manager_id == $x) {
                $myStreakLose = 0;
                $myStreakWin++;
                if ($myStreakWin > $myLongestWin) {
                    $myLongestWin = $myStreakWin;
                }
            } else {
                $myStreakWin = 0;
                $myStreakLose++;
                if ($myStreakLose > $myLongestLose) {
                    $myLongestLose = $myStreakLose;
                }
            }
        }
        if ($myLongestWin == $longestWin) {
            $longestWina[] = (object)['val' => $myLongestWin, 'man' => $x];
        }
        if ($myLongestLose == $longestLose) {
            $longestLosea[] = (object)['val' => $myLongestLose, 'man' => $x];
        }
    }

    $longestWina = collect($longestWina);
    $longestLosea = collect($longestLosea);

    $tops = checkMultiple($longestWina, 'val');
    insertFunFact(39, 'man', 'val', [], $tops);

    $tops = checkMultiple($longestLosea, 'val');
    insertFunFact(40, 'man', 'val', [], $tops);


    $longestWin = $longestLose = 0;
    $longestWina = $longestLosea = [];

    for ($x = 1; $x < 11; $x++) {
        $rsm = RegularSeasonMatchup::where('manager1_id', $x)
            ->where('winning_manager_id', $x)
            ->orderBy('year', 'asc')
            ->orderBy('week_number', 'asc')
            ->get();
        $years = $rsm->groupBy('year');

        // manager win streak
        $myStreakWin = $myLongestWin = 0;
        foreach ($years as $y => $weeks) {
            $lastWeek = 0;
            foreach ($weeks as $w) {
                if ($w->week_number == 1) {
                    $myStreakWin  = 0;
                }
                if ($w->week_number == ($lastWeek + 1)) {
                    $myStreakWin++;
                    if ($myStreakWin > $myLongestWin) {
                        $myLongestWin = $myStreakWin;
                    }
                    $lastWeek = $w->week_number;
                } else {
                    break; // go to next year
                }
            }
        }
        
        // manager lose streak
        $rsm = RegularSeasonMatchup::where('manager1_id', $x)
            ->where('losing_manager_id', $x)
            ->orderBy('year', 'asc')
            ->orderBy('week_number', 'asc')
            ->get();
        $years = $rsm->groupBy('year');

        $myStreakLose = $myLongestLose = 0;
        foreach ($years as $y => $weeks) {
            $lastWeek = 0;
            foreach ($weeks as $w) {
                if ($w->week_number == 1) {
                    $myStreakLose  = 0;
                }
                if ($w->week_number == ($lastWeek + 1)) {
                    $myStreakLose++;
                    if ($myStreakLose > $myLongestLose) {
                        $myLongestLose = $myStreakLose;
                    }
                    $lastWeek = $w->week_number;
                } else {
                    break; // go to next year
                }
            }
        }
        
        if ($myLongestWin > $longestWin) {
            $longestWin = $myLongestWin;
        }
        if ($myLongestLose > $longestLose) {
            $longestLose = $myLongestLose;
        }
    }

    // now loop through again to see if any managers are tied
    for ($x = 1; $x < 11; $x++) {
        $rsm = RegularSeasonMatchup::where('manager1_id', $x)
            ->where('winning_manager_id', $x)
            ->orderBy('year', 'asc')
            ->orderBy('week_number', 'asc')
            ->get();
        $years = $rsm->groupBy('year');

        // manager win streak
        $myStreakWin = $myLongestWin = 0;
        foreach ($years as $y => $weeks) {
            $lastWeek = 0;
            foreach ($weeks as $w) {
                if ($w->week_number == 1) {
                    $myStreakWin  = 0;
                }
                if ($w->week_number == ($lastWeek + 1)) {
                    $myStreakWin++;
                    if ($myStreakWin > $myLongestWin) {
                        $myLongestWin = $myStreakWin;
                        $myLongestWinYear = $y;
                    }
                    $lastWeek = $w->week_number;
                } else {
                    break; // go to next year
                }
            }
        }
        
        // manager lose streak
        $rsm = RegularSeasonMatchup::where('manager1_id', $x)
            ->where('losing_manager_id', $x)
            ->orderBy('year', 'asc')
            ->orderBy('week_number', 'asc')
            ->get();
        $years = $rsm->groupBy('year');

        $myStreakLose = $myLongestLose = 0;
        foreach ($years as $y => $weeks) {
            $lastWeek = 0;
            foreach ($weeks as $w) {
                if ($w->week_number == 1) {
                    $myStreakLose  = 0;
                }
                if ($w->week_number == ($lastWeek + 1)) {
                    $myStreakLose++;
                    if ($myStreakLose > $myLongestLose) {
                        $myLongestLose = $myStreakLose;
                        $myLongestLoseYear = $y;
                    }
                    $lastWeek = $w->week_number;
                } else {
                    break; // go to next year
                }
            }
        }
        
        if ($myLongestWin == $longestWin) {
            $longestWina[] = (object)['val' => $myLongestWin, 'man' => $x, 'year' => $myLongestWinYear];
        }
        if ($myLongestLose == $longestLose) {
            $longestLosea[] = (object)['val' => $myLongestLose, 'man' => $x, 'year' => $myLongestLoseYear];
        }
    }

    $longestWina = collect($longestWina);
    $tops = checkMultiple($longestWina, 'val');
    insertFunFact(56, 'man', 'val', ['year'], $tops);
    
    $longestLosea = collect($longestLosea);
    $tops = checkMultiple($longestLosea, 'val');
    insertFunFact(57, 'man', 'val', ['year'], $tops);
}

// 62,63,71,72
function draft()
{
    $i = Draft::selectRaw('manager_id, AVG(round_pick) as avg_pick')
        ->where('round', 1)
        ->groupBy('manager_id')
        ->orderBy('avg_pick', 'asc')
        ->get();

    $tops = checkMultiple($i, 'avg_pick');
    insertFunFact(62, 'manager_id', 'avg_pick', [], $tops);

    $i = Draft::selectRaw('manager_id, AVG(round_pick) as avg_pick')
        ->where('round', 1)
        ->groupBy('manager_id')
        ->orderBy('avg_pick', 'desc')
        ->get();

    $tops = checkMultiple($i, 'avg_pick');
    insertFunFact(63, 'manager_id', 'avg_pick', [], $tops);

    $ones = Draft::selectRaw('manager_id, count(manager_id) as num1')
        ->where('round', 1)
        ->where('round_pick', 1)
        ->groupBy('manager_id')
        ->orderBy('num1', 'desc')
        ->get();

    $tops = checkMultiple($ones, 'num1');
    insertFunFact(71, 'manager_id', 'num1', [], $tops);

    $ones = Draft::selectRaw('manager_id, count(manager_id) as num1')
        ->where('round', 1)
        ->where('round_pick', 1)
        ->groupBy('manager_id')
        ->orderBy('num1', 'asc')
        ->get();

    // This is to handle the managers that have 0
    $ones = DB::select('SELECT managers.id, COALESCE(post_count, 0) AS num1
        FROM managers
        LEFT JOIN (
            SELECT manager_id, SUM(if(draft.manager_id is not null,1,0)) AS post_count
            FROM draft
            WHERE `round` = 1 and `round_pick` = 1 
            GROUP BY manager_id
        ) post_counts ON post_counts.manager_id = managers.id
        ORDER BY num1 asc');
    $ones = collect($ones);

    $tops = checkMultiple($ones, 'num1');
    insertFunFact(72, 'id', 'num1', [], $tops);
}

// 73,74,75
function moves()
{
    $i = TeamName::selectRaw('manager_id, sum(trades) as trades')
        ->orderBy('trades', 'desc')
        ->groupBy('manager_id')
        ->get();

    $tops = checkMultiple($i, 'trades');
    insertFunFact(73, 'manager_id', 'trades', [], $tops);

    $i = TeamName::selectRaw('manager_id, sum(moves) as moves')
        ->orderBy('moves', 'desc')
        ->groupBy('manager_id')
        ->get();

    $tops = checkMultiple($i, 'moves');
    insertFunFact(74, 'manager_id', 'moves', [], $tops);

    $i = TeamName::selectRaw('manager_id, sum(moves) as moves')
        ->orderBy('moves', 'asc')
        ->groupBy('manager_id')
        ->get();

    $tops = checkMultiple($i, 'moves');
    insertFunFact(75, 'manager_id', 'moves', [], $tops);
}

// 76,77,78,79,80
function currentSeasonStats()
{
    $r = Roster::selectRaw('managers.id, sum(points) as pts')
        ->join('managers', 'managers.name', '=', 'rosters.manager')
        ->where('roster_spot', 'BN')
        ->where('year', currentSeason)
        ->orderBy('pts', 'desc')
        ->groupBy('managers.id')
        ->get();

    $tops = checkMultiple($r, 'pts');
    insertFunFact(80, 'id', 'pts', [], $tops);

    $r = Roster::with('stat')
        ->where('roster_spot', '!=', 'BN')
        ->where('roster_spot', '!=', 'IR')
        ->where('year', currentSeason)
        ->get();

    $t = $r->groupBy('manager');

    $mostYds = $mostTds = 0;
    $leastYds = $leastTds = 417417417;
    foreach ($t as $man => $rosters) {
        $myYds = $myTds = 0;
        foreach ($rosters as $r) {
            if ($r->stat) {
                $myYds += $r->stat->pass_yds + $r->stat->rush_yds + $r->stat->rec_yds;
                $myTds += $r->stat->pass_tds + $r->stat->rush_tds + $r->stat->rec_tds;
            }
        }
        if ($myYds > $mostYds) {
            $mostYds = $myYds;
        }
        if ($myTds > $mostTds) {
            $mostTds = $myTds;
        }
        if ($myYds < $leastYds) {
            $leastYds = $myYds;
        }
        if ($myTds < $leastTds) {
            $leastTds = $myTds;
        }
    }
    // Put in arrays to handle case when managers are tied
    $mostYdsa = $mostTdsa = [];
    $leastYdsa = $leastTdsa = [];
    foreach ($t as $man => $rosters) {
        $myYds = $myTds = 0;
        foreach ($rosters as $r) {
            if ($r->stat) {
                $myYds += $r->stat->pass_yds + $r->stat->rush_yds + $r->stat->rec_yds;
                $myTds += $r->stat->pass_tds + $r->stat->rush_tds + $r->stat->rec_tds;
            }
        }

        if ($myYds == $mostYds) {
            $mostYdsa[] = (object)['val' => $myYds, 'man' => Manager::where('name', $man)->first()->id];
        }
        if ($myTds == $mostTds) {
            $mostTdsa[] = (object)['val' => $myTds, 'man' => Manager::where('name', $man)->first()->id];
        }
        if ($myYds == $leastYds) {
            $leastYdsa[] = (object)['val' => $leastYds, 'man' => Manager::where('name', $man)->first()->id];
        }
        if ($myTds == $leastTds) {
            $leastTdsa[] = (object)['val' => $leastTds, 'man' => Manager::where('name', $man)->first()->id];
        }
    }

    $mostYdsa = collect($mostYdsa);
    $tops = checkMultiple($mostYdsa, 'val');
    insertFunFact(76, 'man', 'val', [], $tops);
    
    $mostTdsa = collect($mostTdsa);
    $tops = checkMultiple($mostTdsa, 'val');
    insertFunFact(78, 'man', 'val', [], $tops);
    
    $leastYdsa = collect($leastYdsa);
    $tops = checkMultiple($leastYdsa, 'val');
    insertFunFact(77, 'man', 'val', [], $tops);
    
    $leastTdsa = collect($leastTdsa);
    $tops = checkMultiple($leastTdsa, 'val');
    insertFunFact(79, 'man', 'val', [], $tops);
}

// 45,46,47,48
function margins()
{
    $i = RegularSeasonMatchup::selectRaw('year, week_number, MAX(ABS(manager1_score - manager2_score)) as diff,
        IF(manager1_score > manager2_score, manager1_id, manager2_id) as winner, IF(manager1_score > manager2_score, manager2_id, manager1_id) as loser')
        ->groupBy('winner', 'loser', 'year', 'week_number')
        ->orderBy('diff', 'desc')
        ->limit(50)
        ->get();

    $tops = checkMultiple($i, 'diff');
    insertFunFact(45, 'winner', 'diff', ['Wk.','week_number','year'], $tops);
    insertFunFact(47, 'loser', 'diff', ['Wk.','week_number','year'], $tops);

    $i = RegularSeasonMatchup::selectRaw('year, week_number, MAX(ABS(manager1_score - manager2_score)) as diff,
        IF(manager1_score > manager2_score, manager1_id, manager2_id) as winner, IF(manager1_score > manager2_score, manager2_id, manager1_id) as loser')
        ->groupBy('winner', 'loser', 'year', 'week_number')
        ->orderBy('diff', 'asc')
        ->limit(50)
        ->get();

    $tops = checkMultiple($i, 'diff');
    insertFunFact(46, 'winner', 'diff', ['Wk.','week_number','year'], $tops);
    insertFunFact(48, 'loser', 'diff', ['Wk.','week_number','year'], $tops);
}

// 41,42
function appearances()
{
    $i = Finish::selectRaw('manager_id, count(manager_id) as app')
        ->where('finish', '<', 7)
        ->groupBy('manager_id')
        ->orderBy('app', 'desc')
        ->get();

    $tops = checkMultiple($i, 'app');
    insertFunFact(41, 'manager_id', 'app', [], $tops);

    $i = Finish::selectRaw('manager_id, count(manager_id) as app')
        ->where('finish', '<', 7)
        ->groupBy('manager_id')
        ->orderBy('app', 'asc')
        ->get();

    $tops = checkMultiple($i, 'app');
    insertFunFact(42, 'manager_id', 'app', [], $tops);
}

// 60,61
function currentPostseasonStreak()
{
    global $lastSeason;
    $streaks = [];
    for ($y = $lastSeason; $y > 2005; $y--) {
        $i = Finish::where('finish', '<', 7)
            ->where('year', $y)
            ->get();

        foreach ($i as $finish) {
            $streaks[$finish->manager_id][] = $y;
        }
    }

    $longest = [];
    foreach ($streaks as $man => $years) {
        $lastYear = $lastSeason;

        foreach ($years as $y) {
            if ($y == $lastYear) {
                $longest[$man] = 1;
                $lastYear = $y;
                continue;
            }

            if (isset($longest[$man]) && $y == ($lastYear - 1)) {
                $longest[$man]++;
            } else {
                $lastYear = $y;
                break;
            }
            $lastYear = $y;
        }
    }

    $best = 0;
    foreach ($longest as $man => $num) {
        if ($num > $best) {
            $best = $num;
        }
    }

    ManagerFunFact::where('fun_fact_id', 60)->delete();
    foreach ($longest as $man => $num) {
        if ($num == $best) {
            ManagerFunFact::create([
                'manager_id' => $man,
                'fun_fact_id' => 60,
                'rank' => 1,
                'value' => $num.' seasons'
            ]);
        }
    }

    $longest = [];
    foreach ($streaks as $man => $years) {
        $longest[$man] = $lastSeason - $years[0];
    }

    $worst = 0;
    foreach ($longest as $man => $num) {
        if ($num > $worst) {
            $worst = $num;
        }
    }

    ManagerFunFact::where('fun_fact_id', 61)->delete();
    foreach ($longest as $man => $num) {
        if ($num == $worst) {
            ManagerFunFact::create([
                'manager_id' => $man,
                'fun_fact_id' => 61,
                'rank' => 1,
                'value' => $num.' seasons'
            ]);
        }
    }
}

// 58,59
function postseasonWinPct()
{
    $p = PlayoffMatchup::all();

    for ($x = 1; $x < 11; $x++) {
        $all[$x]= [
            'matchups' => 0,
            'wins' => 0,
            'losses' => 0
        ];
    }

    foreach ($p as $m) {
        $all[$m->manager1_id]['matchups']++;
        $all[$m->manager2_id]['matchups']++;
        if ($m->manager1_score > $m->manager2_score) {
            // m1 won
            $all[$m->manager1_id]['wins']++;
            $all[$m->manager2_id]['losses']++;
        } else {
            // m2 won
            $all[$m->manager2_id]['wins']++;
            $all[$m->manager1_id]['losses']++;
        }
    }

    $best = 0;
    $worst = 417;
    foreach ($all as $man => $match) {
        $pct = ($match['wins'] / $match['matchups']) * 100;
        
        if ($pct > $best) {                
            $best = $pct;
        }
        if ($pct < $worst) {
            $worst = $pct;
        }
    }

    // Put in arrays to handle case when managers are tied
    $besta = $worsta = [];
    foreach ($all as $man => $match) {
        $pct = ($match['wins'] / $match['matchups']) * 100;

        if ($pct == $best) {
            $besta[] = (object)['man' => $man, 'val' => $pct];
        }
        if ($pct == $worst) {
            $worsta[] = (object)['man' => $man, 'val' => $pct];
        }
    }

    $besta = collect($besta);
    $worsta = collect($worsta);

    $tops = checkMultiple($besta, 'val');
    insertFunFact(58, 'man', 'val', [], $tops);

    $tops = checkMultiple($worsta, 'val');
    insertFunFact(59, 'man', 'val', [], $tops);
}

//81,82,83,84,85,86,87
function currentSeasonPoints()
{
    global $currentSeason;
    // Most points in week
    $r = Roster::selectRaw('managers.id, week, sum(points) as pts')
        ->join('managers', 'managers.name', '=', 'rosters.manager')
        ->where('roster_spot', '!=', 'BN')
        ->where('year', $currentSeason)
        ->orderBy('pts', 'desc')
        ->groupBy('week','managers.id')
        ->get();

    $tops = checkMultiple($r, 'pts');
    insertFunFact(81, 'id', 'pts', ['Wk.','week'], $tops);

    // Fewest points in week
    $r = Roster::selectRaw('managers.id, week, sum(points) as pts')
        ->join('managers', 'managers.name', '=', 'rosters.manager')
        ->where('roster_spot', '!=', 'BN')
        ->where('year', $currentSeason)
        ->orderBy('pts', 'asc')
        ->groupBy('week','managers.id')
        ->get();
    
    $tops = checkMultiple($r, 'pts');
    insertFunFact(83, 'id', 'pts', ['Wk.','week'], $tops);

    // Win/loss margins
    $i = RegularSeasonMatchup::selectRaw('year, week_number, MAX(ABS(manager1_score - manager2_score)) as diff,
        IF(manager1_score > manager2_score, manager1_id, manager2_id) as winner, IF(manager1_score > manager2_score, manager2_id, manager1_id) as loser')
        ->where('year', $currentSeason)
        ->groupBy('winner', 'loser', 'year', 'week_number')
        ->orderBy('diff', 'desc')
        ->limit(50)
        ->get();

    $tops = checkMultiple($i, 'diff');
    insertFunFact(84, 'winner', 'diff', ['Wk.','week_number','year'], $tops);
    insertFunFact(86, 'loser', 'diff', ['Wk.','week_number','year'], $tops);

    $i = RegularSeasonMatchup::selectRaw('year, week_number, MAX(ABS(manager1_score - manager2_score)) as diff,
        IF(manager1_score > manager2_score, manager1_id, manager2_id) as winner, IF(manager1_score > manager2_score, manager2_id, manager1_id) as loser')
        ->where('year', $currentSeason)
        ->groupBy('winner', 'loser', 'year', 'week_number')
        ->orderBy('diff', 'asc')
        ->limit(50)
        ->get();

    $tops = checkMultiple($i, 'diff');
    insertFunFact(85, 'winner', 'diff', ['Wk.','week_number','year'], $tops);
    insertFunFact(87, 'loser', 'diff', ['Wk.','week_number','year'], $tops);
}   

/**
* Undocumented function
*/
function getOptimalLineupPoints()
{
    global $currentSeason;
    $response = [];

    $r = Roster::selectRaw('distinct week')->where('year', $currentSeason)->get();
    
    foreach ($r as $week) {
        $week = $week->week;

        $r2 = Roster::selectRaw('distinct manager')->where('week', $week)->where('year', $currentSeason)->get();
        foreach ($r2 as $manager) {
        
            $manager = $manager->manager;

            $points = 0;
            $roster = [];

            $r3 = Roster::where('manager', $manager)->where('week', $week)->where('year', $currentSeason)->get();
            foreach ($r3 as $row) {

                $roster[] = [
                    'pos' => $row->position,
                    'points' => (float)$row->points
                ];
            }

            $optimal = checkRosterForOptimal($roster);

            $response[] = [
                'manager' => $manager,
                'week' => $week,
                'optimal' => round($optimal, 2)
            ];
        }
    }

    $best = 0;
    $worst = 417417;
    foreach ($response as $array) {
        if ($array['optimal'] > $best) {
            $best = $array['optimal'];
        }
        if ($array['optimal'] < $worst) {
            $worst = $array['optimal'];
        }
    }

    // Put in arrays to handle case when managers are tied
    $besta = $worsta = [];
    foreach ($response as $array) {
        if ($array['optimal'] == $best) {
            $man = Manager::where('name', $array['manager'])->first()->id;
            $besta[] = (object)['man' => $man, 'val' => $array['optimal']];
        }
        if ($array['optimal'] == $worst) {
            $man = Manager::where('name', $array['manager'])->first()->id;
            $worsta[] = (object)['man' => $man, 'val' => $array['optimal']];
        }
    }

    $besta = collect($besta);
    $worsta = collect($worsta);

    $tops = checkMultiple($besta, 'val');
    insertFunFact(82, 'man', 'val', [], $tops);
    $tops = checkMultiple($worsta, 'val');
    insertFunFact(88, 'man', 'val', [], $tops);
}

/**
* Check the roster for optimal lineups
*/
function checkRosterForOptimal(array $roster)
{
    usort($roster, function($a, $b) {
        return $b['points'] <=> $a['points'];
    });

    $optimalRoster = [
        'qb' => 0,'rb1' => 0,'rb2' => 0,'wr1' => 0,'wr2' => 0,'wr3' => 0,'te' => 0,'wrt' => 0,'qwrt' => 0,'k' => 0,'def' => 0
    ];

    $fullRoster = 0;
    foreach ($roster as $player) {
        if ($fullRoster < 11) {
            if ($player['pos'] == 'QB') {
                if ($optimalRoster['qb'] == 0) {
                    $optimalRoster['qb'] = $player['points'];
                    $fullRoster++;
                } elseif ($optimalRoster['qwrt'] == 0) {
                    $optimalRoster['qwrt'] = $player['points'];
                    $fullRoster++;
                }
            } elseif ($player['pos'] == 'RB') {
                if ($optimalRoster['rb1'] == 0) {
                    $optimalRoster['rb1'] = $player['points'];
                    $fullRoster++;
                } elseif ($optimalRoster['rb2'] == 0) {
                    $optimalRoster['rb2'] = $player['points'];
                    $fullRoster++;
                } elseif ($optimalRoster['wrt'] == 0) {
                    $optimalRoster['wrt'] = $player['points'];
                    $fullRoster++;
                } elseif ($optimalRoster['qwrt'] == 0) {
                    $optimalRoster['qwrt'] = $player['points'];
                    $fullRoster++;
                }
            } elseif ($player['pos'] == 'WR') {
                if ($optimalRoster['wr1'] == 0) {
                    $optimalRoster['wr1'] = $player['points'];
                    $fullRoster++;
                } elseif ($optimalRoster['wr2'] == 0) {
                    $optimalRoster['wr2'] = $player['points'];
                    $fullRoster++;
                } elseif ($optimalRoster['wr3'] == 0) {
                    $optimalRoster['wr3'] = $player['points'];
                    $fullRoster++;
                } elseif ($optimalRoster['wrt'] == 0) {
                    $optimalRoster['wrt'] = $player['points'];
                    $fullRoster++;
                } elseif ($optimalRoster['qwrt'] == 0) {
                    $optimalRoster['qwrt'] = $player['points'];
                    $fullRoster++;
                }
            } elseif ($player['pos'] == 'TE') {
                if ($optimalRoster['te'] == 0) {
                    $optimalRoster['te'] = $player['points'];
                    $fullRoster++;
                } elseif ($optimalRoster['wrt'] == 0) {
                    $optimalRoster['wrt'] = $player['points'];
                    $fullRoster++;
                } elseif ($optimalRoster['qwrt'] == 0) {
                    $optimalRoster['qwrt'] = $player['points'];
                    $fullRoster++;
                }
            } elseif ($player['pos'] == 'K') {
                if ($optimalRoster['k'] == 0) {
                    $optimalRoster['k'] = $player['points'];
                    $fullRoster++;
                }
            } elseif ($player['pos'] == 'DEF') {
                if ($optimalRoster['def'] == 0) {
                    $optimalRoster['def'] = $player['points'];
                    $fullRoster++;
                }
            }
        }
    }
    $optimal = 0;
    foreach ($optimalRoster as $pos => $score) {
        $optimal += $score;
    }

    return $optimal;
}

function updateProjected()
{
    $managersInOrder = ['Tyler', 'AJ', 'Gavin', 'Matt', 'Cameron', 'Andy', 'Everett', 'Justin', 'Cole', 'Ben'];

    // Get each matchup from regular season
    $result = query("SELECT * FROM regular_season_matchups ORDER BY year DESC, week_number DESC");
    while ($row = fetch_array($result)) {

        $manager = $managersInOrder[$row['manager1_id']-1];
        $opp = $managersInOrder[$row['manager2_id']-1];
        $year = $row['year'];
        $week = $row['week_number'];

        $managerProj = getPlayerProjectionTotal($manager, $year, $week);
        $oppProj = getPlayerProjectionTotal($opp, $year, $week);

        query("UPDATE regular_season_matchups SET manager1_projected = $managerProj, manager2_projected = $oppProj
            WHERE manager1_id = {$row['manager1_id']} AND manager2_id = {$row['manager2_id']} AND year = $year AND week_number = $week");
    }
}

function getPlayerProjectionTotal(string $manager, int $year, int $week)
{
    $query = "SELECT sum(projected) as proj FROM rosters WHERE manager = '$manager' AND year = $year AND week = $week
        AND roster_spot != 'BN' AND roster_spot != 'IR'";
    $result = query($query);
    while ($row = fetch_array($result)) {
        return $row['proj'];
    }
}