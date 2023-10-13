<?php

$pageName = $_GET['id'] . "'s Profile";
include 'header.php';
include 'sidebar.html';

$versusSet = false;
if (isset($_GET['id'])) {

    $managerName = $_GET['id'];
    $result = query("SELECT * FROM managers WHERE name = '" . $managerName . "'");
    while ($row = fetch_array($result)) {
        $managerId = $row['id'];
    }

    if (isset($_GET['versus'])) {
        $versus = $_GET['versus'];
        $versusSet = true;
    } else {
        while( in_array( ($versus = mt_rand(1,10)), [$managerId] ) );
    }

    $result = query("SELECT * FROM managers WHERE id = '" . $versus . "'");
    while ($row = fetch_array($result)) {
        $versusName = $row['name'];
    }
}

?>

<div class="app-content content container-fluid">
    <div class="content-wrapper">
        <div class="content-header row"></div>

        <div class="content-body">
            <!-- Headline Statistics -->
            <div class="row">
                <div class="col-xl-3 col-lg-6 col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="media">
                                <div class="p-2 text-xs-center bg-green-ffb media-left media-middle">
                                    <i class="icon-star-full font-large-2 white"></i>
                                </div>
                                <div class="p-2 bg-green-ffb media-body">
                                    <h5>Total Points</h5>
                                    <h5 class="text-bold-400"><?php echo $profileNumbers['totalPoints'] . ' (Rank: ' . $profileNumbers['totalPointsRank'] . ')'; ?>&#x200E;</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="media">
                                <div class="p-2 text-xs-center bg-green-ffb media-left media-middle">
                                    <i class="icon-stats-bars font-large-2 white"></i>
                                </div>
                                <div class="p-2 bg-green-ffb media-body">
                                    <h5>Postseason Record</h5>
                                    <h5 class="text-bold-400"><?php echo $profileNumbers['playoffRecord'] . ' (Rank: ' . $profileNumbers['playoffRecordRank'] . ')'; ?>&#x200E;</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="media">
                                <div class="p-2 text-xs-center bg-green-ffb media-left media-middle">
                                    <i class="icon-trophy font-large-2 white"></i>
                                </div>
                                <div class="p-2 bg-green-ffb media-body">
                                    <h5>Championships</h5>
                                    <h5 class="text-bold-400"><?php echo $profileNumbers['championships'] . ' (' . $profileNumbers['championshipYears'] . ')'; ?>&#x200E;</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="media">
                                <div class="p-2 text-xs-center bg-green-ffb media-left media-middle">
                                    <i class="icon-calendar font-large-2 white"></i>
                                </div>
                                <div class="p-2 bg-green-ffb media-body">
                                    <h5>Reg. Season Record</h5>
                                    <h5 class="text-bold-400"><?php echo $profileNumbers['record'] . ' (Rank: ' . $profileNumbers['recordRank'] . ')'; ?>&#x200E;</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title"><a href="awards.php">Awards</a></h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr;">
                            <div class="row">
                                <div class="col-lg-6 col-sm-12">
                                    <?php
                                    $result = query(
                                        "SELECT * FROM manager_fun_facts mff
                                        JOIN fun_facts ff ON mff.fun_fact_id = ff.id
                                        JOIN managers ON managers.id = mff.manager_id
                                        WHERE is_positive = 1 and manager_id = $managerId"
                                    );
                                    while ($row = fetch_array($result)) { 
                                        $value = $row['value'];
                                        if (isfloat($row['value']) && isDecimal($row['value'])) {
                                            $value = number_format($row['value'], 2, '.', ',');
                                        }
                                        echo '<div class="col-sm-6 award good">';
                                        if ($row['new_leader']) {
                                            echo '<i class="icon-warning" style="font-size: 15px"></i>';
                                        }
                                        echo '<strong>'.$row['fact'].'</strong><br />'.$value.'<br />'.$row['note'];
                                        echo '</div>';
                                    } ?>
                                </div>
                                <div class="col-lg-6 col-sm-12">
                                    <?php
                                    $result = query(
                                        "SELECT * FROM manager_fun_facts mff
                                        JOIN fun_facts ff ON mff.fun_fact_id = ff.id
                                        JOIN managers ON managers.id = mff.manager_id
                                        WHERE is_positive = 0 and manager_id = $managerId"
                                    );
                                    while ($row = fetch_array($result)) { 
                                        $value = $row['value'];
                                        if (isfloat($row['value']) && isDecimal($row['value'])) {
                                            $value = number_format($row['value'], 2, '.', ',');
                                        } 
                                        echo '<div class="col-sm-6 award bad">';
                                        if ($row['new_leader']) {
                                            echo '<i class="icon-warning" style="font-size: 15px"></i>';
                                        }
                                        echo '<strong>'.$row['fact'].'</strong><br />'.$value.'<br />'.$row['note'];
                                        echo '</div>';
                                    } ?>
                                </div>

                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12 col-md-3 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h3>Record vs. Opponent</h3>
                        </div>
                        <div class="card-body">
                            <div class="position-relative">
                                <select id="oppRecordSelector" class="dropdown">
                                    <option value="reg">Regular Season</option>
                                    <option value="post">Postseason</option>
                                </select>
                                <table class="table table-responsive table-striped nowrap" id="datatable-regSeason">
                                    <thead>
                                        <th>Manager</th>
                                        <th>Wins</th>
                                        <th>Losses</th>
                                        <th>Win %</th>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $result = query(
                                            "SELECT name, SUM(CASE
                                                WHEN manager1_score > manager2_score THEN 1
                                                ELSE 0
                                            END) AS wins,
                                            SUM(CASE
                                                WHEN manager1_score < manager2_score THEN 1
                                                ELSE 0
                                            END) AS losses
                                            FROM regular_season_matchups rsm
                                            JOIN managers ON managers.id = rsm.manager2_id
                                            WHERE manager1_id = $managerId
                                            GROUP BY manager2_id
                                            ORDER BY wins DESC"
                                        );
                                        while ($row = fetch_array($result)) { ?>
                                            <tr>
                                                <td><?php echo $row['name']; ?></td>
                                                <td><?php echo $row['wins']; ?></td>
                                                <td><?php echo $row['losses']; ?></td>
                                                <td><?php echo round(($row['wins'] * 100) / ($row['wins'] + $row['losses']), 1); ?></td>
                                            </tr>

                                        <?php } ?>
                                    </tbody>
                                </table>

                                <table class="table table-responsive table-striped nowrap" id="datatable-postseason" style="display:none;">
                                    <thead>
                                        <th>Manager</th>
                                        <th>Wins</th>
                                        <th>Losses</th>
                                        <th>Win %</th>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $result = query(
                                            "SELECT name, w.wins+w2.wins AS totalWins, l.losses+l2.losses AS totalLosses
                                            FROM managers
                                            JOIN (
                                                SELECT SUM(CASE
                                                WHEN manager1_id = $managerId AND manager1_score > manager2_score THEN 1
                                                ELSE 0
                                                END) AS wins, manager2_id
                                                FROM playoff_matchups rsm
                                                GROUP BY manager2_id
                                            ) w ON w.manager2_id = managers.id

                                            JOIN (
                                                SELECT SUM(CASE
                                                WHEN manager2_id = $managerId AND manager2_score > manager1_score THEN 1
                                                ELSE 0
                                                END) AS wins, manager1_id
                                                FROM playoff_matchups rsm
                                                GROUP BY manager1_id
                                            ) w2 ON w2.manager1_id = managers.id

                                            JOIN (
                                                SELECT SUM(CASE
                                                WHEN manager1_id = $managerId AND manager1_score < manager2_score THEN 1
                                                ELSE 0
                                                END) AS losses, manager2_id
                                                FROM playoff_matchups rsm
                                                GROUP BY manager2_id
                                            ) l ON l.manager2_id = managers.id

                                            JOIN (
                                                SELECT SUM(CASE
                                                WHEN manager2_id = $managerId AND manager2_score < manager1_score THEN 1
                                                ELSE 0
                                                END) AS losses, manager1_id
                                                FROM playoff_matchups rsm
                                                GROUP BY manager1_id
                                            ) l2 ON l2.manager1_id = managers.id
                                            WHERE name != '" . $_GET['id'] . "'"
                                        );
                                        while ($row = fetch_array($result)) { 
                                            $total = $row['totalWins'] + $row['totalLosses'];
                                            $sort = ($total == 0) ? 0 : round(($row['totalWins'] * 100) / ($total), 1);
                                            ?>
                                            <tr>
                                                <td><?php echo $row['name']; ?></td>
                                                <td><?php echo $row['totalWins']; ?></td>
                                                <td><?php echo $row['totalLosses']; ?></td>
                                                <td data-sort="<?php echo $sort; ?>"><?php 
                                                    if ($total == 0) {
                                                        echo 'N/A';
                                                    } else {
                                                        echo round(($row['totalWins'] * 100) / ($total), 1);
                                                    }
                                                ?></td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">Wins by Opponent</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <div class="card-block">
                                <canvas id="winsChart" height="250px;"></canvas>
                                <canvas id="postseasonWinsChart" height="250px;" style="display: none;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12 col-md-9 table-padding">
                    <div class="card">
                    <div class="card-header">
                            <h3>Seasons</h3>
                        </div>
                        <div class="card-body">
                            <div class="card-block">
                                <canvas id="finishesChart" class="height-400"></canvas>
                                <br />
                                <table class="table table-responsive table-striped nowrap" id="datatable-seasons">
                                    <thead>
                                        <th>Year</th>
                                        <th>Team Name</th>
                                        <th>Record</th>
                                        <th>Win %</th>
                                        <th>PF</th>
                                        <th>PA</th>
                                        <th>Finish</th>
                                        <th>Moves</th>
                                        <th>Trades</th>
                                    </thead>
                                    <tbody>
                                        <?php
                                        foreach ($seasonNumbers as $year => $array) { ?>
                                            <tr>
                                                <td><?php echo $year; ?></td>
                                                <td><?php echo $array['team_name']; ?></td>
                                                <td><?php echo $array['record']; ?></td>
                                                <td><?php echo $array['win_pct'] . ' %'; ?></td>
                                                <td><?php echo $array['pf']; ?></td>
                                                <td><?php echo $array['pa']; ?></td>
                                                <td><?php echo $array['finish']; ?></td>
                                                <td><?php echo $array['moves']; ?></td>
                                                <td><?php echo $array['trades']; ?></td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4><a href="draft.php">Drafts</a></h4>
                        </div>
                        <div class="card-body">
                            <div class="card-block">
                                <table class="table table-responsive table-striped nowrap" id="datatable-drafts">
                                    <thead>
                                        <th>Year</th>
                                        <th>Pick #</th>
                                        <th>1st Pick</th>
                                        <th>2nd Pick</th>
                                        <th>3rd Pick</th>
                                        <th>4th Pick</th>
                                        <th>5th Pick</th>
                                        <th>6th Pick</th>
                                        <th>7th Pick</th>
                                        <th>8th Pick</th>
                                        <th>9th Pick</th>
                                        <th>10th Pick</th>
                                    </thead>
                                    <tbody>
                                        <?php
                                         // Need to do different join for sqlite vs mysql
                                        $playerPos = "player || ' - ' || position";
                                        if ($DB_TYPE == 'mysql') {
                                            $playerPos = "CONCAT(player,' - ',position)";
                                        }
                                        $result = query(
                                            "SELECT d.year,
                                            (SELECT round_pick FROM draft WHERE manager_id = $managerId AND round = 1 AND year = d.year) as position,
                                            (SELECT $playerPos FROM draft WHERE manager_id = $managerId AND round = 1 AND year = d.year) as r1_pick,
                                            (SELECT $playerPos FROM draft WHERE manager_id = $managerId AND round = 2 AND year = d.year) as r2_pick,
                                            (SELECT $playerPos FROM draft WHERE manager_id = $managerId AND round = 3 AND year = d.year) as r3_pick,
                                            (SELECT $playerPos FROM draft WHERE manager_id = $managerId AND round = 4 AND year = d.year) as r4_pick,
                                            (SELECT $playerPos FROM draft WHERE manager_id = $managerId AND round = 5 AND year = d.year) as r5_pick,
                                            (SELECT $playerPos FROM draft WHERE manager_id = $managerId AND round = 6 AND year = d.year) as r6_pick,
                                            (SELECT $playerPos FROM draft WHERE manager_id = $managerId AND round = 7 AND year = d.year) as r7_pick,
                                            (SELECT $playerPos FROM draft WHERE manager_id = $managerId AND round = 8 AND year = d.year) as r8_pick,
                                            (SELECT $playerPos FROM draft WHERE manager_id = $managerId AND round = 9 AND year = d.year) as r9_pick,
                                            (SELECT $playerPos FROM draft WHERE manager_id = $managerId AND round = 10 AND year = d.year) as r10_pick
                                            FROM draft d
                                            WHERE manager_id = $managerId AND round = 1"
                                        );
                                        while ($array = fetch_array($result)) { ?>
                                            <tr>
                                                <td><?php echo '<a href="/draft.php?manager='.$managerName.'&year='.$array['year'].'">'.$array['year'].'</a>'; ?></td>
                                                <td><?php echo $array['position']; ?></td>
                                                <td><?php echo $array['r1_pick']; ?></td>
                                                <td><?php echo $array['r2_pick']; ?></td>
                                                <td><?php echo $array['r3_pick']; ?></td>
                                                <td><?php echo $array['r4_pick']; ?></td>
                                                <td><?php echo $array['r5_pick']; ?></td>
                                                <td><?php echo $array['r6_pick']; ?></td>
                                                <td><?php echo $array['r7_pick']; ?></td>
                                                <td><?php echo $array['r8_pick']; ?></td>
                                                <td><?php echo $array['r9_pick']; ?></td>
                                                <td><?php echo $array['r10_pick']; ?></td>
                                            </tr>

                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12 col-lg-4 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4><a href="draft.php">Top Drafted Players</a></h4>
                        </div>
                        <div class="card-body">
                            <table class="table table-responsive table-striped nowrap" id="datatable-topPlayers">
                                <thead>
                                    <th>Player</th>
                                    <th>Years</th>
                                    <th>Points</th>
                                </thead>
                                <tbody>
                                    <?php
                                    $result = query(
                                        "SELECT draft.player, COUNT(distinct draft.year) as times, sum(points) as points FROM draft
                                        JOIN rosters on draft.player = rosters.player and draft.year = rosters.year
                                        WHERE manager_id = $managerId
                                        GROUP BY draft.player
                                        HAVING times > 2
                                        ORDER BY times DESC");
                                    while ($array = fetch_array($result)) { ?>
                                        <tr>
                                            <td><?php echo $array['player']; ?></td>
                                            <td><?php echo $array['times']; ?></td>
                                            <td><?php echo round($array['points'], 1); ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12 col-lg-8 table-padding" id="versus">
                    <div class="card">
                        <div class="card-header">
                            <h4>Head to Head</h4>
                        </div>
                        <div class="card-body" style="direction: ltr;">
                            <div class="row">
                                <div class="col-sm-12 col-md-4 d-md-none">
                                    <h5 style="text-align: center;">Choose Opponent</h5>
                                </div>
                                <div class="col-sm-12 col-md-4">
                                    <select id="versus-select" class="form-control w-50">
                                        <?php
                                        $result = query("SELECT * FROM managers WHERE id != $managerId ORDER BY name ASC");
                                        while ($row = fetch_array($result)) {
                                            if ($row['id'] == $versus) {
                                                echo '<option selected value="'.$row['id'].'">'.$row['name'].'</option>';
                                            } else {
                                                echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 col-md-4">
                                    <table class="table table-responsive table-striped nowrap">
                                    <?php
                                        $wins = $losses = $total = $pf = $pa = $ptsAvg = $bigWin = $bigLoss = $postTotal = $postWins = $postLosses = 0;
                                        $closeLoss = -9999;
                                        $closeWin = 9999;
                                        $result = query(
                                            "SELECT year, week_number, manager1_id, manager2_id, manager1_score, manager2_score, winning_manager_id
                                            FROM regular_season_matchups
                                            WHERE manager1_id = $managerId
                                            AND manager2_id = $versus
                                            UNION
                                            SELECT year, round, manager1_id, manager2_id, manager1_score, manager2_score, IF(manager1_score > manager2_score, manager1_id, manager2_id)
                                            FROM playoff_matchups
                                            WHERE (manager1_id = $managerId AND manager2_id = $versus) OR (manager1_id = $versus AND manager2_id = $managerId)
                                            ORDER BY year, week_number DESC"
                                        );
                                        while ($array = fetch_array($result)) {

                                            $isPost = (int)$array['week_number'] == 0;

                                            $wins += (!$isPost && $array['winning_manager_id'] == $managerId) ? 1 : 0;
                                            $losses += (!$isPost && $array['winning_manager_id'] != $managerId) ? 1 : 0;
                                            $total += (!$isPost) ? 1: 0;
                                            $postWins += ($isPost && $array['winning_manager_id'] == $managerId) ? 1 : 0;
                                            $postLosses += ($isPost && $array['winning_manager_id'] != $managerId) ? 1 : 0;
                                            $postTotal += ($isPost) ? 1: 0;

                                            $manager1score = $array['manager1_score'];
                                            $manager2score = $array['manager2_score'];

                                            // Postseason matchup might be flipped!
                                            if ($isPost && $array['manager2_id'] == $managerId) {
                                                $manager1score = $array['manager2_score'];
                                                $manager2score = $array['manager1_score'];
                                            }

                                            $pf += $manager1score;
                                            $pa += $manager2score;

                                            $margin = $manager1score - $manager2score;
                                            $bigWin = ($margin > 0 && $margin > $bigWin) ? $margin : $bigWin;
                                            $closeLoss = ($margin < 0 && $margin > $closeLoss) ? $margin : $closeLoss;
                                            $closeWin = ($margin > 0 && $margin < $closeWin) ? $margin : $closeWin;
                                            $bigLoss = ($margin < 0 && $margin < $bigLoss) ? $margin : $bigLoss;
                                        } ?>
                                        <tr><td>Reg. Season Matchups</td><td><?php echo $total; ?></td></tr>
                                        <tr><td>Reg. Season Wins</td><td><?php echo $wins; ?></td></tr>
                                        <tr><td>Reg. Season Losses</td><td> <?php echo $losses; ?></td></tr>

                                        <tr><td>Postseason Matchups</td><td><?php echo $postTotal; ?></td></tr>
                                        <tr><td>Postseason Wins</td><td><?php echo $postWins; ?></td></tr>
                                        <tr><td>Postseason Losses</td><td> <?php echo $postLosses; ?></td></tr>

                                        <tr><td>Overall Winning %</td><td> <?php echo round(($wins + $postWins) * 100/ ($total + $postTotal), 1).' %'; ?></td></tr>

                                        <tr><td>Total Points For</td><td><?php echo $pf; ?></td></tr>
                                        <tr><td>Total Points Against</td><td><?php echo $pa; ?></td></tr>
                                        <tr><td>Average Points For</td><td><?php echo round($pf/($total+$postTotal), 1); ?></td></tr>
                                        <tr><td>Average Points Against</td><td><?php echo round($pa/($total+$postTotal), 1); ?></td></tr>

                                        <tr><td>Biggest Win</td><td><?php echo round($bigWin, 2); ?></td></tr>
                                        <tr><td>Biggest Loss</td><td><?php echo round($bigLoss, 2); ?></td></tr>
                                        <tr><td>Closest Win</td><td><?php echo round($closeWin, 2); ?></td></tr>
                                        <tr><td>Closest Loss</td><td><?php echo round($closeLoss, 2); ?></td></tr>

                                    </table>
                                </div>

                                <div class="col-sm-12 col-md-8">
                                    <table class="table table-responsive table-striped nowrap" id="datatable-versus">
                                        <thead>
                                            <th>Year</th>
                                            <th>Week</th>
                                            <th>Manager</th>
                                            <th>Score</th>
                                            <th>Opponent</th>
                                            <th>Margin</th>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $result = query(
                                                "SELECT * FROM (
                                                    SELECT year, week_number, manager1_id AS man1, manager2_id AS man2,
                                                    manager1_score AS man1score, manager2_score AS man2score, winning_manager_id
                                                    FROM regular_season_matchups
                                                    WHERE manager1_id = $managerId
                                                    AND manager2_id = $versus
                                                UNION
                                                    SELECT year, round, manager1_id AS man1, manager2_id AS man2,
                                                    manager1_score AS man1score, manager2_score AS man2score, IF(manager1_score > manager2_score, manager1_id, manager2_id)
                                                    FROM playoff_matchups
                                                    WHERE (manager1_id = $managerId AND manager2_id = $versus)
                                                UNION
                                                    SELECT year, round, manager2_id AS man2, manager1_id AS man1,
                                                    manager2_score AS man1score, manager1_score AS man2score, IF(manager1_score > manager2_score, manager1_id, manager2_id)
                                                    FROM playoff_matchups
                                                    WHERE (manager1_id = $versus AND manager2_id = $managerId)
                                                    ORDER BY YEAR
                                                ) a
                                                ORDER BY YEAR desc, CASE WHEN (week_number <> '0' AND CAST(week_number AS SIGNED) <> 0) THEN CAST(week_number AS SIGNED) ELSE 9999 END DESC
                                                "
                                            );
                                            while ($array = fetch_array($result)) {
                                                echo '<tr class="highlight">
                                                    <td>'.$array["year"].'</td>
                                                    <td>'.$array["week_number"].'</td>';
                                                    if ($array['winning_manager_id'] == $managerId) {
                                                        echo '<td><span class="badge badge-primary">'.$managerName.'</span></td>';
                                                    } else {
                                                        echo '<td><span class="badge badge-secondary">'.$managerName.'</span></td>';
                                                    }
                                                    echo '<td><a href="/rosters.php?year='.$array["year"].'&week='.$array["week_number"].'&manager='.$managerName.'">'.
                                                        $array['man1score'].' - '.$array['man2score'].'</a></td>';
                                                    if ($array['winning_manager_id'] == $versus) {
                                                        echo '<td><span class="badge badge-primary">' . $versusName.'</span></td>';
                                                    } else {
                                                        echo '<td><span class="badge badge-secondary">' . $versusName.'</span></td>';
                                                    }
                                                    echo '<td>'.round(abs($array['man1score'] - $array['man2score']), 2).'</td>
                                                </tr>';
                                            } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-sm-12 col-lg-6 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4>High/Low Foes</h4>
                        </div>
                        <div class="card-body" style="direction: ltr;">
                            <div class="row">
                                <div class="col-sm-12">
                                    <table class="table table-responsive table-striped nowrap">
                                        <tr>
                                            <td>Reg. Season Matchups</td>
                                            <td><?php echo $foes['reg_season_matchups']['manager']; ?></td>
                                            <td><?php echo $foes['reg_season_matchups']['value']; ?></td>
                                        </tr>
                                        <tr>
                                            <td>Reg. Season Wins</td>
                                            <td><?php echo $foes['reg_season_wins']['manager']; ?></td>
                                            <td><?php echo $foes['reg_season_wins']['value']; ?></td>
                                        </tr>
                                        <tr>
                                            <td>Reg. Season Losses</td>
                                            <td><?php echo $foes['reg_season_losses']['manager']; ?></td>
                                            <td><?php echo $foes['reg_season_losses']['value']; ?></td>
                                        </tr>
                                        <tr>
                                            <td>Postseason Matchups</td>
                                            <td><?php echo $foes['postseason_matchups']['manager']; ?></td>
                                            <td><?php echo $foes['postseason_matchups']['value']; ?></td>
                                        </tr>
                                        <tr>
                                            <td>Postseason Wins</td>
                                            <td><?php echo $foes['postseason_wins']['manager']; ?></td>
                                            <td><?php echo $foes['postseason_wins']['value']; ?></td>
                                        </tr>
                                        <tr>
                                            <td>Postseason Losses</td>
                                            <td><?php echo $foes['postseason_losses']['manager']; ?></td>
                                            <td><?php echo $foes['postseason_losses']['value']; ?></td>
                                        </tr>
                                        <tr>
                                            <td>Overall Winning %</td>
                                            <td><?php echo $foes['overall_win_pct']['manager']; ?></td>
                                            <td><?php echo $foes['overall_win_pct']['value']; ?></td>
                                        </tr>
                                        <tr>
                                            <td>Most Total Points For</td>
                                            <td><?php echo $foes['total_pf']['manager']; ?></td>
                                            <td><?php echo $foes['total_pf']['value']; ?></td>
                                        </tr>
                                        <tr>
                                            <td>Most Total Points Against</td>
                                            <td><?php echo $foes['total_pa']['manager']; ?></td>
                                            <td><?php echo $foes['total_pa']['value']; ?></td>
                                        </tr>
                                        <tr>
                                            <td>Highest Average Points For</td>
                                            <td><?php echo $foes['average_pf']['manager']; ?></td>
                                            <td><?php echo $foes['average_pf']['value']; ?></td>
                                        </tr>
                                        <tr>
                                            <td>Highest Average Points Against</td>
                                            <td><?php echo $foes['average_pa']['manager']; ?></td>
                                            <td><?php echo $foes['average_pa']['value']; ?></td>
                                        </tr>
                                        <tr>
                                            <td>Biggest Win</td>
                                            <td><?php echo $foes['biggest_win']['manager']; ?></td>
                                            <td><?php echo $foes['biggest_win']['value']; ?></td>
                                        </tr>
                                        <tr>
                                            <td>Biggest Loss</td>
                                            <td><?php echo $foes['biggest_loss']['manager']; ?></td>
                                            <td><?php echo $foes['biggest_loss']['value']; ?></td>
                                        </tr>
                                        <tr>
                                            <td>Closest Win</td>
                                            <td><?php echo $foes['closest_win']['manager']; ?></td>
                                            <td><?php echo $foes['closest_win']['value']; ?></td>
                                        </tr>
                                        <tr>
                                            <td>Closest Loss</td>
                                            <td><?php echo $foes['closest_loss']['manager']; ?></td>
                                            <td><?php echo $foes['closest_loss']['value']; ?></td>
                                        </tr>

                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<script type="text/javascript">
    $(document).ready(function() {

        if ("<?php echo $versusSet; ?>" == true) {
            document.getElementById('versus').scrollIntoView(true);
        }

        let managerName = "<?php echo $managerName; ?>";
        let baseUrl = "<?php echo $BASE_URL; ?>";
        $('#versus-select').change(function() {
            window.location = baseUrl+'profile.php?id='+managerName+'&versus='+$('#versus-select').val();
        });

        $('#oppRecordSelector').change(function() {
            if ($('#oppRecordSelector').val() == 'reg') {
                $('#datatable-regSeason').show();
                $('#datatable-postseason').hide();
                $('#postseasonWinsChart').hide();
                $('#winsChart').show();
            } else {
                $('#datatable-regSeason').hide();
                $('#datatable-postseason').show();
                $('#postseasonWinsChart').show();
                $('#winsChart').hide();
            }
        });

        $('#datatable-regSeason').DataTable({
            searching: false,
            paging: false,
            info: false,
            order: [
                [3, "desc"]
            ]
        });

        $('#datatable-postseason').DataTable({
            searching: false,
            paging: false,
            info: false,
            order: [
                [3, "desc"]
            ]
        });

        $('#datatable-seasons').DataTable({
            searching: false,
            paging: false,
            info: false,
            order: [
                [0, "desc"]
            ]
        });

        $('#datatable-teamNames').DataTable({
            searching: false,
            paging: false,
            info: false,
            order: [
                [0, "desc"]
            ]
        });

        $('#datatable-drafts').DataTable({
            searching: false,
            paging: false,
            info: false,
            order: [
                [0, "desc"]
            ]
        });

        $('#datatable-topPlayers').DataTable({
            searching: false,
            paging: false,
            info: false,
            order: [
                [1, "desc"]
            ]
        });

        $('#datatable-versus').DataTable({
            searching: false,
            paging: false,
            info: false,
            order: [
                [0, "desc"]
            ]
        });

        var ctx = $('#finishesChart');
        var years = <?php echo json_encode($finishesChart['years']); ?>;
        var yearLabels = years.split(",");
        var finishes = <?php echo json_encode($finishesChart['finishes']); ?>;
        var finishData = finishes.split(",");
        var regSeason = <?php echo json_encode($finishesChart['regSeasons']); ?>;
        var regSeasonData = regSeason.split(",");

        var line = new Chart(ctx, {
            type: 'line',
            data: {
                labels: yearLabels,
                datasets: [{
                    label: 'Overall Finish',
                    data: finishData,
                    // borderColor: '#2eff37',
                    borderColor: '#2eb82e',
                    yAxisID: 'y',
                },{
                    label: 'Reg. Season Finish',
                    data: regSeasonData,
                    // borderColor: '#2eff37',
                    borderColor: '#297eff',
                    yAxisID: 'y',
                }]
            },
            options: {
                scales: {
                    y: {
                        reverse: true,
                        min: 1,
                        max: 10
                    }
                },
                plugins: {
                    quadrants: {
                        topLeft: "rgb(172, 240, 172)",
                        topRight: "rgb(172, 240, 172)",
                        bottomRight: "#bdbdbd",
                        bottomLeft: "#bdbdbd",
                    },
                }
            },
            plugins: [{
                id: 'quadrants',
                beforeDraw(chart, args, options) {
                    const {ctx, chartArea: {left, top, right, bottom}, scales: {x, y}} = chart;
                    const midX = x.getPixelForValue(6);
                    const midY = y.getPixelForValue(6);
                    ctx.save();
                    ctx.fillStyle = options.topLeft;
                    ctx.fillRect(left, top, midX - left, midY - top);
                    ctx.fillStyle = options.topRight;
                    ctx.fillRect(midX, top, right - midX, midY - top);
                    ctx.fillStyle = options.bottomRight;
                    ctx.fillRect(midX, midY, right - midX, bottom - midY);
                    ctx.fillStyle = options.bottomLeft;
                    ctx.fillRect(left, midY, midX - left, bottom - midY);
                    ctx.restore();
                }
            }]
        });
        
        var ctx = $('#winsChart');
        var managers = <?php echo json_encode($winsChart['managers']); ?>;
        var wins = <?php echo json_encode($winsChart['wins']); ?>;
        let colors = ["#a6c6fa","#3cf06e","#f33c47","#c0f6e6","#def89f","#dca130","#ff7f2c","#ecb2b6"," #f87598"];
        
        let obj = {};
        obj.label = 'Wins';
        obj.data = wins;
        obj.backgroundColor = colors;
        obj.datalabels = {
            align: 'end'
        };

        var winsChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: managers,
                datasets: [obj]
            },
            options: {
                plugins: {
                    legend: {
                        display: false,
                    },
                    datalabels: {
                        formatter: function(value, context) {
                            return context.chart.data.labels[context.dataIndex]+': '+value;
                        },
                        color: 'black',
                        font: {
                            weight: 'bold'
                        }
                    }
                }
            },
            plugins: [ChartDataLabels]
        });
        
        var ctx = $('#postseasonWinsChart');
        var postManagers = <?php echo json_encode($postseasonWinsChart['managers']); ?>;
        var postseasonWins = <?php echo json_encode($postseasonWinsChart['wins']); ?>;

        obj = {};
        obj.label = 'Wins';
        obj.data = postseasonWins;
        obj.backgroundColor = colors;
        obj.datalabels = {
            align: 'end'
        };

        var postseasonWinsChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: postManagers,
                datasets: [obj]
            },
            options: {
                plugins: {
                    legend: {
                        display: false,
                    },
                    datalabels: {
                        formatter: function(value, context) {
                            return context.chart.data.labels[context.dataIndex]+': '+value;
                        },
                        color: 'black',
                        font: {
                            weight: 'bold'
                        }
                    }
                }
            },
            plugins: [ChartDataLabels]
        });

    });
</script>