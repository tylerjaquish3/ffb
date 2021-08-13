<?php

include '../connections.php';

// var_dump($_POST);die;

if (isset($_POST['positions'])) {

    $currentYear = $_POST['currentYear'];

    $data['current'] = getPositionsDraftedByYear($currentYear, 'c');
    $data['minus1'] = getPositionsDraftedByYear($currentYear-1);
    $data['minus2'] = getPositionsDraftedByYear($currentYear-2);
    $data['minus3'] = getPositionsDraftedByYear($currentYear-3);
    $data['inception'] = getPositionsDraftedByYear($currentYear, 'i');

    echo json_encode($data);
    die;
}

function getPositionsDraftedByYear(int $year, string $type = null)
{
    global $conn;
    for ($rd = 1; $rd < 18; $rd++) {
        $data[] = [
            'round' => $rd,
            'data' => ['QB' => 0, 'RB' => 0, 'WR' => 0, 'TE' => 0, 'K' => 0, 'DEF' => 0]
        ];
    }

    $sql = "SELECT * FROM draft where year =".$year;
    if ($type == 'c') {
        $sql = "SELECT pick_number, position FROM draft_selections ds JOIN preseason_rankings pr ON pr.id = ds.ranking_id";
    } elseif ($type == 'i') {
        $sql = "SELECT * FROM draft";
    }

    $result = mysqli_query($conn, $sql);
    while ($row = mysqli_fetch_array($result)) {

        if ($type == 'c') {
            $pos = $row['position'];
            $pick = $row['pick_number'];
            $round = ($pick % 10 == 0) ? floor($pick/10) : floor($pick/10) + 1;
        } else {
            $pos = $row['position'];
            $round = $row['round'];
        }

        foreach ($data as $key => &$value) {
            if ($value['round'] == $round && isset($value['data'][$pos])) {
                $value['data'][$pos]++;
            }
        }
    }

    return $data;
}

if (isset($_POST['depthChart'])) {

    $result = mysqli_query($conn, "SELECT team,
        max(case when position = 'QB' and depth = 1 then player ELSE '' end) as QB1,
        max(case when position = 'QB' and depth = 1 AND ranking_id IS NOT null then player ELSE '' end) as QB1p,
        max(case when position = 'QB' and depth = 2 then player ELSE '' end) as QB2,
        max(case when position = 'QB' and depth = 2 AND ranking_id IS NOT null then player ELSE '' end) as QB2p,
        max(case when position = 'RB' and depth = 1 then player ELSE '' end) as RB1,
        max(case when position = 'RB' and depth = 1 AND ranking_id IS NOT null then player ELSE '' end) as RB1p,
        max(case when position = 'RB' and depth = 2 then player ELSE '' end) as RB2,
        max(case when position = 'RB' and depth = 2 AND ranking_id IS NOT null then player ELSE '' end) as RB2p,
        max(case when position = 'WR' and depth = 1 then player ELSE '' end) as WR1,
        max(case when position = 'WR' and depth = 1 AND ranking_id IS NOT null then player ELSE '' end) as WR1p,
        max(case when position = 'WR' and depth = 2 then player ELSE '' end) as WR2,
        max(case when position = 'WR' and depth = 2 AND ranking_id IS NOT null then player ELSE '' end) as WR2p,
        max(case when position = 'WR' and depth = 3 then player ELSE '' end) as WR3,
        max(case when position = 'WR' and depth = 3 AND ranking_id IS NOT null then player ELSE '' end) as WR3p,
        max(case when position = 'WR' and depth = 4 then player ELSE '' end) as WR4,
        max(case when position = 'WR' and depth = 4 AND ranking_id IS NOT null then player ELSE '' end) as WR4p,
        max(case when position = 'TE' and depth = 1 then player ELSE '' end) as TE1,
        max(case when position = 'TE' and depth = 1 AND ranking_id IS NOT null then player ELSE '' end) as TE1p,
        max(case when position = 'K' then player ELSE '' end) as K1,
        max(case when position = 'K' and depth = 1 AND ranking_id IS NOT null then player ELSE '' end) as K1p
        FROM preseason_rankings pr
        LEFT JOIN draft_selections ds ON ds.ranking_id = pr.id
        WHERE team IS NOT null
        GROUP BY team");
    while ($row = mysqli_fetch_array($result)) {

        $data['data'][] = [
            'team' => $row['team'],
            'qb1' => $row['QB1'],
            'qb1class' => ($row['QB1'] == $row['QB1p']) ? 'color-gray' : 'color-QB',
            'qb2' => $row['QB2'],
            'qb2class' => ($row['QB2'] == $row['QB2p']) ? 'color-gray' : 'color-QB',
            'rb1' => $row['RB1'],
            'rb1class' => ($row['RB1'] == $row['RB1p']) ? 'color-gray' : 'color-RB',
            'rb2' => $row['RB2'],
            'rb2class' => ($row['RB2'] == $row['RB2p']) ? 'color-gray' : 'color-RB',
            'wr1' => $row['WR1'],
            'wr1class' => ($row['WR1'] == $row['WR1p']) ? 'color-gray' : 'color-WR',
            'wr2' => $row['WR2'],
            'wr2class' => ($row['WR2'] == $row['WR2p']) ? 'color-gray' : 'color-WR',
            'wr3' => $row['WR3'],
            'wr3class' => ($row['WR3'] == $row['WR3p']) ? 'color-gray' : 'color-WR',
            'wr4' => $row['WR4'],
            'wr4class' => ($row['WR4'] == $row['WR4p']) ? 'color-gray' : 'color-WR',
            'te1' => $row['TE1'],
            'te1class' => ($row['TE1'] == $row['TE1p']) ? 'color-gray' : 'color-TE',
            'k1' => $row['K1'],
            'k1class' => ($row['K1'] == $row['K1p']) ? 'color-gray' : 'color-K'
        ];
    }

    echo json_encode($data);
    die;
}

if (isset($_POST['standings'])) {

    $draftOrder = $_POST['draftOrder'];
    $data = getProjections($draftOrder);

    echo json_encode($data);
    die;
}

if (isset($_POST['projectedChart'])) {
    $draftOrder = $_POST['draftOrder'];
    $data = getProjections($draftOrder);
// dd($data);
    $mine = [];
    $league = ['QB' => 0,'RB' => 0,'WR' => 0,'TE' => 0,'K' => 0,'DEF' => 0,'BN' => 0];
    $filled = ['QB' => 0,'RB' => 0,'WR' => 0,'TE' => 0,'K' => 0,'DEF' => 0,'BN' => 0];
    foreach ($data['data'] as $team) {
        if ($team['manager'] != 'Tyler') {
            $league['QB'] += $team['qb'];
            $league['RB'] += $team['rb'];
            $league['WR'] += $team['wr'];
            $league['TE'] += $team['te'];
            $league['K'] += $team['k'];
            $league['DEF'] += $team['def'];
            // $league['Starting'] += $team['starting'];
            $league['BN'] += $team['bn'];
            // $league['Total'] += $team['total'];

            $filled['QB'] += ($team['qb'] > 0) ? 1 : 0;
            $filled['RB'] += ($team['rb'] > 0) ? 1 : 0;
            $filled['WR'] += ($team['wr'] > 0) ? 1 : 0;
            $filled['TE'] += ($team['te'] > 0) ? 1 : 0;
            $filled['K'] += ($team['k'] > 0) ? 1 : 0;
            $filled['DEF'] += ($team['def'] > 0) ? 1 : 0;
            $filled['BN'] += ($team['bn'] > 0) ? 1 : 0;
            // $filled['Total'] += ($team['total'] > 0) ? 1 : 0;
            // $filled['Starting'] += ($team['starting'] > 0) ? 1 : 0;
        } else {
            $mine[] = $team['qb'];
            $mine[] = $team['rb'];
            $mine[] = $team['wr'];
            $mine[] = $team['te'];
            $mine[] = $team['k'];
            $mine[] = $team['def'];
            // $mine[] = $team['starting'];
            $mine[] = $team['bn'];
            // $mine[] = $team['total'];
        }
    }

    $avg = [];
    foreach ($league as $pos => $sum) {
        if ($filled[$pos] > 0) {
            $avg[] = round($sum/$filled[$pos], 0);
        } else {
            $avg[] = 0;
        }
    }

    $data = [
        'mine' => $mine,
        'avg' => $avg
    ];

    echo json_encode($data);
    die;
}

function getProjections(array $draftOrder)
{
    global $conn;
    $drafted = [];
    foreach ($draftOrder as $man => $avatar) {
        $drafted[$man] = ['QB' => 0,'RB' => 0,'WR' => 0,'TE' => 0,'DEF' => 0,'K' => 0,'Total' => 0,
        'BN' => 0, 'Starting' => 0, 'LastYear' => 0,
        'QBc' => 0, 'RBc' => 0, 'WRc' => 0, 'TEc' => 0, 'DEFc' => 0, 'Kc' => 0,
        'QBm' => 2, 'RBm' => 3, 'WRm' => 3, 'TEm' => 1, 'DEFm' => 1, 'Km' => 1];
    }
    $count = 0;
    $result = mysqli_query($conn,
        "SELECT name, proj_points, position, pick_number, points
        FROM preseason_rankings
        JOIN draft_selections ON preseason_rankings.id = draft_selections.ranking_id
        JOIN managers ON managers.id = draft_selections.manager_id"
    );
    while ($row = mysqli_fetch_array($result)) {
        $pos = $row['position'];
        if ($pos) {
            // Only count the position if less than max at the position
            // This is to only sum the starting lineup, not all players
            if ($drafted[$row['name']][$pos.'c'] < $drafted[$row['name']][$pos.'m']) {
                $drafted[$row['name']][$pos] += (int)$row['proj_points'];
                $drafted[$row['name']][$pos.'c']++;
                $drafted[$row['name']]['Starting'] += (int)$row['proj_points'];
            } else {
                $drafted[$row['name']]['BN'] += (int)$row['proj_points'];
            }

            $drafted[$row['name']]['Total'] += (int)$row['proj_points'];
            $drafted[$row['name']]['LastYear'] += (int)$row['points'];
        }
    }

    foreach ($drafted as $man => $row) {

        $data['data'][] = [
            'manager' => $man,
            'qb' => $row['QB'],
            'rb' => $row['RB'],
            'wr' => $row['WR'],
            'te' => $row['TE'],
            'def' => $row['DEF'],
            'k' => $row['K'],
            'starting' => $row['Starting'],
            'bn' => $row['BN'],
            'total' => $row['Total'],
            'last_year' => $row['LastYear'],
        ];
    }

    return $data;
}

if (isset($_POST['cheatSheet'])) {

    $positions = ['QB','RB','WR','TE'];
    $tiers = [1,2,3,4,5,6,7,8];
    $posRank = 1;

    foreach ($positions as $pos) {
        foreach ($tiers as $tier) {
            $result = mysqli_query($conn,
                "SELECT tier, my_rank, player, position, pick_number, proj_points, is_mine
                FROM preseason_rankings pr
                LEFT JOIN draft_selections ds ON pr.id = ds.ranking_id
                WHERE position = '$pos'
                AND tier = $tier
                ORDER BY my_rank"
            );
            while ($row = mysqli_fetch_array($result)) {
                if ($tier == $row['tier']) {
                    $class = '';

                    if ($row['pick_number']) {
                        $class = 'strike';
                    }
                    if ($row['is_mine']) {
                        $class = 'strike mine';
                    }

                    $data[$pos][$tier][] = [
                        'class' => $class,
                        'player' => $row['my_rank'].'. '.$row['player'].' ('.$row['proj_points'].')'
                    ];
                }
            }
        }
    }

    echo json_encode($data);
    die;
}

if ($_POST['request'] == 'player_data') {
    $id = $_POST['id'];
    $data = [];

    $result = mysqli_query($conn, "SELECT * FROM preseason_rankings WHERE id = $id");
    if (mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_array($result)) {
            $data[] = $row;
        }
    }

    $result = mysqli_query($conn, "SELECT * FROM player_data WHERE preseason_ranking_id = $id and type = 'REG' order by year desc");
    if (mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_array($result)) {
            $data[] = $row;
        }
    }

    echo json_encode($data);
    die;
}

if ($_POST['request'] == 'notes') {
    $sql = $conn->prepare("UPDATE preseason_rankings SET notes = ? WHERE id = ?");
    $sql->bind_param('si', $_POST['notes'], $_POST['id']);
    $sql->execute();

    echo true;
    die;
}