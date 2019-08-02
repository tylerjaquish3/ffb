<?php

$result = mysqli_query($conn, "select count(id) as num_rows FROM regular_season_matchups");
while ($row = mysqli_fetch_array($result)) {
    $values = $row['num_rows'];
}

// if process needs to run
if ($values > 1507) {

    // initialize empty array foreach manager
    for ($i = 1; $i < 11; $i++) {
        $results[$i] = [
            'mostWeekPf' => 0,
            'mostWeekPa' => 0,
            'leastWeekPf' => 9999,
            'leastWeekPa' => 9999,
            'mostSeasonPf' => 0,
            'mostSeasonPa' => 0,
            'leastSeasonPf' => 99999,
            'leastSeasonPa' => 99999,
            'seasonWins' => 0,
            'seasonLosses' => 0,
            'alltimeWins' => 0,
            'alltimeLosses' => 0,
            'alltimePf' => 0,
            'alltimePa' => 0,
            'numWeeks' => 0
        ];
    }

    $result = mysqli_query($conn, "SELECT * FROM managers");
    while ($manager = mysqli_fetch_array($result)) {

        $managerId = $manager['id'];
        $managerName = $manager['name'];

        $seasonWins = $seasonLosses = $alltimeWins = $alltimeLosses = 0;
        $seasonPf = $seasonPa = $alltimePf = $alltimePa = 0;
        $prevWeek = $prevYear = 0;
        $numWeeks = 0;

        $result2 = mysqli_query($conn, "SELECT * FROM regular_season_matchups rsm WHERE manager1_id = " . $managerId);
        while ($rsm = mysqli_fetch_array($result2)) {

            $currentYear = $rsm['year'];
            $currentWeek = $rsm['week_number'];

            if ($currentYear != $prevYear && $numWeeks != 0) {
                // new year, check last years sums to see if they are bigger or smaller
                if ($seasonPf > $results[$managerId]['mostSeasonPf']) {
                    $results[$managerId]['mostSeasonPf'] = $seasonPf;
                }
                if ($seasonPa > $results[$managerId]['mostSeasonPa']) {
                    $results[$managerId]['mostSeasonPa'] = $seasonPa;
                }
                if ($seasonPf < $results[$managerId]['leastSeasonPf']) {
                    $results[$managerId]['leastSeasonPf'] = $seasonPf;
                }
                if ($seasonPa < $results[$managerId]['leastSeasonPa']) {
                    $results[$managerId]['leastSeasonPa'] = $seasonPa;
                }
                if ($seasonWins > $results[$managerId]['seasonWins']) {
                    $results[$managerId]['seasonWins'] = $seasonWins;
                }
                if ($seasonLosses > $results[$managerId]['seasonLosses']) {
                    $results[$managerId]['seasonLosses'] = $seasonLosses;
                }
                $seasonWins = $seasonLosses = $seasonPf = $seasonPa = 0;
            }

            $numWeeks++;
            if ($rsm['manager1_score'] > $rsm['manager2_score']) {
                $seasonWins++;
                $alltimeWins++;
            } else {
                $seasonLosses++;
                $alltimeLosses++;
            }

            $seasonPf += $rsm['manager1_score'];
            $seasonPa += $rsm['manager2_score'];
            $alltimePf += $rsm['manager1_score'];
            $alltimePa += $rsm['manager2_score'];

            if ($rsm['manager1_score'] > $results[$managerId]['mostWeekPf']) {
                $results[$managerId]['mostWeekPf'] = $rsm['manager1_score'];
            }
            if ($rsm['manager2_score'] > $results[$managerId]['mostWeekPa']) {
                $results[$managerId]['mostWeekPa'] = $rsm['manager2_score'];
            }
            if ($rsm['manager1_score'] < $results[$managerId]['leastWeekPf']) {
                $results[$managerId]['leastWeekPf'] = $rsm['manager1_score'];
            }
            if ($rsm['manager2_score'] < $results[$managerId]['leastWeekPa']) {
                $results[$managerId]['leastWeekPa'] = $rsm['manager2_score'];
            }

            $prevWeek = $currentWeek;
            $prevYear = $currentYear;
        }
        $results[$managerId]['alltimeWins'] = $alltimeWins;
        $results[$managerId]['alltimeLosses'] = $alltimeLosses;
        $results[$managerId]['alltimePf'] = $alltimePf;
        $results[$managerId]['alltimePa'] = $alltimePa;
        $results[$managerId]['numWeeks'] = $numWeeks;
    }

    var_dump($results);
    // die;

    $mostAlltimeWins = array_keys(array_sort($results, 'alltimeWins', SORT_DESC))[0];
    insertFunFact($conn, $mostAlltimeWins, 'Most Wins (All Time)', 1, $results[$mostAlltimeWins]['alltimeWins']);
    $mostAlltimeLosses = array_keys(array_sort($results, 'alltimeLosses', SORT_DESC))[0];
    insertFunFact($conn, $mostAlltimeLosses, 'Most Losses (All Time)', 1, $results[$mostAlltimeLosses]['alltimeLosses']);

    die;
}

function insertFunFact($conn, $managerId, $funFact, $rank = null, $value = null, $note = null)
{
    $mffid = $funFactId = 0;
    $result = mysqli_query($conn, "SELECT ff.id, mff.id as mff_id FROM fun_facts ff LEFT JOIN manager_fun_facts mff ON mff.fun_fact_id = ff.id WHERE fact = '" . $funFact . "'");
    while ($row = mysqli_fetch_array($result)) {
        $funFactId = $row['id'];

        if (isset($row['mff_id']) && $row['mff_id'] != null) {
            $mffid = $row['mff_id'];
        }
    }

    if ($funFactId != 0) {
        if ($mffid) {
            $sql = $conn->prepare("UPDATE manager_fun_facts SET manager_id = ?, fun_fact_id = ?, rank = ?, value = ?, note = ? WHERE id = " . $mffid);
            $sql->bind_param('iisss', $managerId, $funFactId, $rank, $value, $note);
            $succeeded = $sql->execute();
        } else {
            $sql = $conn->prepare("INSERT INTO manager_fun_facts (manager_id, fun_fact_id, rank, value, note)  VALUES (?,?,?,?,?)");
            $sql->bind_param('iisss', $managerId, $funFactId, $rank, $value, $note);
            $succeeded = $sql->execute();
        }
    }

    return $succeeded;
}

function array_sort($array, $on, $order = SORT_ASC)
{
    $new_array = array();
    $sortable_array = array();

    if (count($array) > 0) {
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                foreach ($v as $k2 => $v2) {
                    if ($k2 == $on) {
                        $sortable_array[$k] = $v2;
                    }
                }
            } else {
                $sortable_array[$k] = $v;
            }
        }

        switch ($order) {
            case SORT_ASC:
                asort($sortable_array);
                break;
            case SORT_DESC:
                arsort($sortable_array);
                break;
        }

        foreach ($sortable_array as $k => $v) {
            $new_array[$k] = $array[$k];
        }
    }

    return $new_array;
}
