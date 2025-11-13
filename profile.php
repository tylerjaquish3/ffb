<?php

if (!isset($_GET['id'])) {
    header('Location: /404.php');
    exit();
}

$pageName = $_GET['id'] . "'s Profile";
include 'header.php';
include 'sidebar.php';

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

<div class="app-content content profile-page">
    <div class="content-wrapper">
        <div class="content-header row"></div>

        <div class="content-body">

            <!-- Tabs Navigation -->
            <div class="row mb-1">
                <div class="col-sm-12">
                    <div class="tab-buttons-container">
                        <button class="tab-button active" id="overview-tab" onclick="showCard('overview')">
                            Overview
                        </button>
                        <button class="tab-button" id="awards-tab" onclick="showCard('awards')">
                            Awards
                        </button>
                        <button class="tab-button" id="record-vs-opponent-tab" onclick="showCard('record-vs-opponent')">
                            Record vs. Opponent
                        </button>
                        <button class="tab-button" id="head-to-head-tab" onclick="showCard('head-to-head')">
                            Head to Head
                        </button>
                        <button class="tab-button" id="points-by-week-tab" onclick="showCard('points-by-week')">
                            Points Charts
                        </button>
                        <button class="tab-button" id="drafts-tab" onclick="showCard('drafts')">
                            Drafts
                        </button>
                        <button class="tab-button" id="draft-analysis-tab" onclick="showCard('draft-analysis')">
                            Draft Analysis
                        </button>
                    </div>
                </div>
            </div>

            <!-- Overview Tab -->
            <div class="row card-section" id="overview">
                <!-- Headline Statistics -->
                <div class="col-sm-12">
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
                </div>
                <!-- Seasons Card -->
                <div class="col-sm-12 table-padding">
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

            <!-- Awards Tab -->
            <div class="row card-section" id="awards" style="display: none;">
                <div class="col-sm-12 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title"><a href="awards.php">Awards</a></h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr;">
                            <?php
                            // Collect awards for this manager
                            $manager_awards = [];
                            
                            // Get positive awards
                            $query = "SELECT * FROM manager_fun_facts mff
                                JOIN fun_facts ff ON mff.fun_fact_id = ff.id
                                JOIN managers ON managers.id = mff.manager_id
                                WHERE is_positive = 1 AND manager_id = $managerId
                                ORDER BY ff.sort_order";
                            
                            $result = query($query);
                            while ($row = fetch_array($result)) {
                                $value = $row['value'];
                                if (isfloat($row['value']) && isDecimal($row['value'])) {
                                    $value = number_format($row['value'], 2, '.', ',');
                                }
                                
                                $manager_awards[] = [
                                    'fact' => $row['fact'],
                                    'value' => $value,
                                    'note' => $row['note'],
                                    'new_leader' => $row['new_leader'],
                                    'is_positive' => true
                                ];
                            }
                            
                            // Get negative awards
                            $query = "SELECT * FROM manager_fun_facts mff
                                JOIN fun_facts ff ON mff.fun_fact_id = ff.id
                                JOIN managers ON managers.id = mff.manager_id
                                WHERE is_positive = 0 AND manager_id = $managerId
                                ORDER BY ff.sort_order";
                            
                            $result = query($query);
                            while ($row = fetch_array($result)) { 
                                $value = $row['value'];
                                if (isfloat($row['value']) && isDecimal($row['value'])) {
                                    $value = number_format($row['value'], 2, '.', ',');
                                }
                                
                                $manager_awards[] = [
                                    'fact' => $row['fact'],
                                    'value' => $value,
                                    'note' => $row['note'],
                                    'new_leader' => $row['new_leader'],
                                    'is_positive' => false
                                ];
                            }
                            
                            // Display awards in the new grid format
                            if (!empty($manager_awards)) {
                                echo '<div class="awards-grid">';
                                foreach ($manager_awards as $award) {
                                    $award_class = $award['is_positive'] ? 'award-badge positive' : 'award-badge negative';
                                    $new_leader_icon = $award['new_leader'] ? '<i class="icon-warning award-new-icon" title="New Leader"></i>' : '';
                                    
                                    echo '<div class="' . $award_class . '">';
                                    echo '<div class="award-header-badge">';
                                    echo '</div>';
                                    echo '<div class="award-title">' . htmlspecialchars($award['fact']) . '</div>';
                                    echo '<div class="award-value">' . htmlspecialchars($award['value']) . ' ' . $new_leader_icon . '</div>';
                                    if (!empty($award['note'])) {
                                        echo '<div class="award-note">' . htmlspecialchars($award['note']) . '</div>';
                                    }
                                    echo '</div>';
                                }
                                echo '</div>';
                            } else {
                                echo '<p class="text-center">No awards found for this manager.</p>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Record vs. Opponent Tab -->
            <div class="row card-section" id="record-vs-opponent" style="display: none;">
                <div class="col-sm-12 col-md-4">
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
                                                <td><?php echo ($row['wins'] + $row['losses']) > 0 ? round(($row['wins'] * 100) / ($row['wins'] + $row['losses']), 1) : 'N/A'; ?></td>
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
                </div>
                <div class="col-sm-12 col-md-4 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">Wins by Opponent</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <div class="card-block">
                                <canvas id="winsChart" height="200px;"></canvas>
                                <canvas id="postseasonWinsChart" height="200px;" style="display: none;"></canvas>
                            </div>
                        </div>
                    </div>
                        </div>
                <div class="col-sm-12 col-md-4 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">Finishes</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <div class="card-block">
                                <canvas id="finishPieChart" height="200px;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>            
            
            <!-- Drafts Tab -->
            <div class="row card-section" id="drafts" style="display: none;">
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

            <!-- Draft Analysis Tab -->
            <div class="row card-section" id="draft-analysis" style="display: none;">
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
                <div class="col-sm-12 col-lg-8 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">Positions Drafted</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <div class="card-block">
                                <canvas id="posByRoundChart" height="500px;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Head to Head Tab -->
            <div class="row card-section" id="head-to-head" style="display: none;">
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
                                        <thead>
                                            <th>Statistic</th>
                                            <th>Value</th>
                                        </thead>
                                    <?php
                                        $wins = $losses = $total = $pf = $pa = $ptsAvg = $bigWin = $bigLoss = $postTotal = $postWins = $postLosses = 0;
                                        $closeLoss = -9999;
                                        $closeWin = 9999;
                                        $marginsArr = $combinedArr = [];
                                        $result = query(
                                            "SELECT year, week_number, manager1_id, manager2_id, manager1_score, manager2_score, winning_manager_id
                                            FROM regular_season_matchups
                                            WHERE manager1_id = $managerId
                                            AND manager2_id = $versus
                                            UNION
                                            SELECT year, round, manager1_id, manager2_id, manager1_score, manager2_score, CASE WHEN manager1_score > manager2_score THEN manager1_id ELSE manager2_id END
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
                                            $marginsArr[] = $margin;
                                            $combinedArr[] = $manager1score + $manager2score;
                                        } ?>
                                        <tr><td>Reg. Season Matchups</td><td><?php echo $total; ?></td></tr>
                                        <tr><td>Reg. Season Wins</td><td><?php echo $wins; ?></td></tr>
                                        <tr><td>Reg. Season Losses</td><td> <?php echo $losses; ?></td></tr>

                                        <tr><td>Postseason Matchups</td><td><?php echo $postTotal; ?></td></tr>
                                        <tr><td>Postseason Wins</td><td><?php echo $postWins; ?></td></tr>
                                        <tr><td>Postseason Losses</td><td> <?php echo $postLosses; ?></td></tr>

                                        <tr><td>Overall Winning %</td><td> <?php echo ($total + $postTotal) > 0 ? round(($wins + $postWins) * 100/ ($total + $postTotal), 1).' %' : 'N/A'; ?></td></tr>

                                        <tr><td>Total Points For</td><td><?php echo $pf; ?></td></tr>
                                        <tr><td>Total Points Against</td><td><?php echo $pa; ?></td></tr>
                                        <tr><td>Average Points For</td><td><?php echo ($total+$postTotal) > 0 ? round($pf/($total+$postTotal), 1) : 'N/A'; ?></td></tr>
                                        <tr><td>Average Points Against</td><td><?php echo ($total+$postTotal) > 0 ? round($pa/($total+$postTotal), 1) : 'N/A'; ?></td></tr>

                                        <tr><td>Biggest Win</td><td><?php echo round($bigWin, 2); ?></td></tr>
                                        <tr><td>Biggest Loss</td><td><?php echo round(abs($bigLoss), 2); ?></td></tr>
                                        <tr><td>Closest Win</td><td><?php echo round($closeWin, 2); ?></td></tr>
                                        <tr><td>Closest Loss</td><td><?php echo round(abs($closeLoss), 2); ?></td></tr>
                                        <tr><td>Average Margin</td><td><?php echo (count($marginsArr) > 0) ? round(array_sum(array_map('abs', $marginsArr))/count($marginsArr), 2) : 'N/A'; ?></td></tr>
                                        <tr><td>Average Combined</td><td><?php echo (count($combinedArr) > 0) ? round(array_sum($combinedArr)/count($combinedArr), 2) : 'N/A'; ?></td></tr>

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
                                            <th>Combined</th>
                                            <th>Records/Seeds</th>
                                        </thead>
                                        <tbody>
                                            <?php
                                            // Function to calculate records up to a specific week
                                            function getRecordBeforeWeek($manager_id, $year, $week) {
                                                // Don't show record for week 1
                                                if ($week <= 1) {
                                                    return '-';
                                                }
                                                
                                                $wins = 0;
                                                $losses = 0;
                                                
                                                $result = query("SELECT winning_manager_id, losing_manager_id FROM regular_season_matchups 
                                                    WHERE year = $year AND week_number < $week 
                                                    AND (manager1_id = $manager_id OR manager2_id = $manager_id)
                                                    AND manager1_id < manager2_id");
                                                
                                                while ($row = fetch_array($result)) {
                                                    if ($row['winning_manager_id'] == $manager_id) {
                                                        $wins++;
                                                    } else if ($row['losing_manager_id'] == $manager_id) {
                                                        $losses++;
                                                    }
                                                }
                                                
                                                return "$wins-$losses";
                                            }

                                            // Function to get rank before a specific week
                                            function getRankBeforeWeek($manager_id, $year, $week) {
                                                // Don't show rank for week 1
                                                if ($week <= 1) {
                                                    return '-';
                                                }
                                                
                                                // Get rank from standings table for the previous week
                                                $prevWeek = $week - 1;
                                                $result = query("SELECT rank FROM standings 
                                                    WHERE manager_id = $manager_id AND year = $year AND week = $prevWeek");
                                                $row = fetch_array($result);
                                                
                                                return $row ? $row['rank'] : '-';
                                            }

                                            // Get regular season matchups with records (avoiding duplicates)
                                            $regularSeasonResult = query(
                                                "SELECT year, week_number, manager1_id, manager2_id,
                                                manager1_score, manager2_score, winning_manager_id,
                                                'regular' as matchup_type
                                                FROM regular_season_matchups
                                                WHERE ((manager1_id = $managerId AND manager2_id = $versus)
                                                   OR (manager1_id = $versus AND manager2_id = $managerId))
                                                   AND manager1_id < manager2_id
                                                ORDER BY year DESC, week_number DESC"
                                            );

                                            // Get playoff matchups with seeds
                                            $playoffResult = query(
                                                "SELECT year, round as week_number, manager1_id AS man1, manager2_id AS man2,
                                                manager1_score AS man1score, manager2_score AS man2score, 
                                                CASE WHEN manager1_score > manager2_score THEN manager1_id ELSE manager2_id END as winning_manager_id,
                                                manager1_seed, manager2_seed, 'playoff' as matchup_type
                                                FROM playoff_matchups
                                                WHERE (manager1_id = $managerId AND manager2_id = $versus)
                                                   OR (manager1_id = $versus AND manager2_id = $managerId)
                                                ORDER BY year DESC, 
                                                CASE round 
                                                    WHEN 'Final' THEN 3
                                                    WHEN 'Semifinal' THEN 2  
                                                    WHEN 'Quarterfinal' THEN 1
                                                    ELSE 0 
                                                END DESC"
                                            );

                                            $allMatchups = array();
                                            
                                            // Process regular season matchups (avoiding duplicates)
                                            while ($array = fetch_array($regularSeasonResult)) {
                                                // Convert to consistent format with man1 and man2score
                                                $array['man1'] = $array['manager1_id'];
                                                $array['man2'] = $array['manager2_id'];
                                                $array['man1score'] = $array['manager1_score'];
                                                $array['man2score'] = $array['manager2_score'];
                                                $allMatchups[] = $array;
                                            }
                                            
                                            // Process playoff matchups
                                            while ($array = fetch_array($playoffResult)) {
                                                $allMatchups[] = $array;
                                            }
                                            
                                            // Sort all matchups by year desc, then week desc
                                            usort($allMatchups, function($a, $b) {
                                                if ($a['year'] != $b['year']) {
                                                    return $b['year'] - $a['year'];
                                                }
                                                
                                                if ($a['matchup_type'] == 'playoff' && $b['matchup_type'] == 'regular') {
                                                    return -1; // Playoff comes after regular season
                                                }
                                                if ($a['matchup_type'] == 'regular' && $b['matchup_type'] == 'playoff') {
                                                    return 1;
                                                }
                                                
                                                if ($a['matchup_type'] == 'regular' && $b['matchup_type'] == 'regular') {
                                                    return $b['week_number'] - $a['week_number'];
                                                }
                                                
                                                // For playoff matchups, Final > Semifinal > Quarterfinal
                                                $aRoundValue = ($a['week_number'] == 'Final') ? 3 : (($a['week_number'] == 'Semifinal') ? 2 : 1);
                                                $bRoundValue = ($b['week_number'] == 'Final') ? 3 : (($b['week_number'] == 'Semifinal') ? 2 : 1);
                                                return $bRoundValue - $aRoundValue;
                                            });

                                            foreach ($allMatchups as $array) {
                                                // Determine which manager is which in the display
                                                $isManagerFirst = ($array['man1'] == $managerId);
                                                $managerScore = $isManagerFirst ? $array['man1score'] : $array['man2score'];
                                                $opponentScore = $isManagerFirst ? $array['man2score'] : $array['man1score'];
                                                
                                                echo '<tr class="highlight">
                                                    <td>'.$array["year"].'</td>
                                                    <td>'.($array["week_number"] == '0' || !is_numeric($array["week_number"]) ? $array["week_number"] : (int)$array["week_number"]).'</td>';
                                                    
                                                if ($array['winning_manager_id'] == $managerId) {
                                                    echo '<td><span class="badge badge-primary">'.$managerName.'</span></td>';
                                                } else {
                                                    echo '<td><span class="badge badge-secondary">'.$managerName.'</span></td>';
                                                }
                                                
                                                echo '<td><a href="/rosters.php?year='.$array["year"].'&week='.$array["week_number"].'&manager='.$managerName.'">'.
                                                    $managerScore.' - '.$opponentScore.'</a></td>';
                                                    
                                                if ($array['winning_manager_id'] == $versus) {
                                                    echo '<td><span class="badge badge-primary">' . $versusName.'</span></td>';
                                                } else {
                                                    echo '<td><span class="badge badge-secondary">' . $versusName.'</span></td>';
                                                }
                                                
                                                echo '<td>'.round(abs($managerScore - $opponentScore), 2).'</td>';
                                                echo '<td>'.round($managerScore + $opponentScore, 2).'</td>';
                                                
                                                // Records/Seeds column - different for regular season vs playoff
                                                if ($array['matchup_type'] == 'regular') {
                                                    $managerRecord = getRecordBeforeWeek($managerId, $array['year'], $array['week_number']);
                                                    $opponentRecord = getRecordBeforeWeek($versus, $array['year'], $array['week_number']);
                                                    
                                                    if ($managerRecord == '-') {
                                                        echo '<td><small>0-0</small></td>';
                                                    } else {
                                                        $managerRank = getRankBeforeWeek($managerId, $array['year'], $array['week_number']);
                                                        $opponentRank = getRankBeforeWeek($versus, $array['year'], $array['week_number']);
                                                        echo '<td><small>'.$managerRecord.' (#'.$managerRank.') vs '.$opponentRecord.' (#'.$opponentRank.')</small></td>';
                                                    }
                                                } else {
                                                    // Playoff - show seeds
                                                    $managerSeed = $isManagerFirst ? $array['manager1_seed'] : $array['manager2_seed'];
                                                    $opponentSeed = $isManagerFirst ? $array['manager2_seed'] : $array['manager1_seed'];

                                                    echo '<td><small>'.$managerSeed.' Seed vs '.$opponentSeed.' Seed</small></td>';
                                                }
                                                
                                                echo '</tr>';
                                            } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            
                <div class="col-sm-12 col-md-4 table-padding">
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
                                            <td><?php echo abs($foes['biggest_loss']['value']); ?></td>
                                        </tr>
                                        <tr>
                                            <td>Closest Win</td>
                                            <td><?php echo $foes['closest_win']['manager']; ?></td>
                                            <td><?php echo $foes['closest_win']['value']; ?></td>
                                        </tr>
                                        <tr>
                                            <td>Closest Loss</td>
                                            <td><?php echo $foes['closest_loss']['manager']; ?></td>
                                            <td><?php echo abs($foes['closest_loss']['value']); ?></td>
                                        </tr>

                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Points Charts Tab -->
            <div class="row card-section" id="points-by-week" style="display: none;">
                <div class="col-sm-12 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">Points By Week</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <div class="card-block">
                                <div class="row">
                                    <div class="col-sm-12" style="display: flex">
                                        <button class="btn btn-primary" id="allSeasons">All Seasons</button>&nbsp;&nbsp;
                                        <button class="btn btn-primary" id="currentSeason">Current Season</button>&nbsp;&nbsp;
                                        <button class="btn btn-primary" id="lastSeason">Last Season</button>&nbsp;&nbsp;
                                        <button class="btn btn-primary" id="lastFiveSeasons">Last 5 Seasons</button>&nbsp;&nbsp;
                                        <h4>Start</h4>
                                        &nbsp;&nbsp;
                                        <select id="startWeek" class="dropdown form-control">
                                            <?php
                                            foreach ($allWeeks as $week) {
                                                echo '<option value="'.$week['week_id'].'">'.$week['week_display'].'</option>';
                                            }
                                            ?>
                                        </select>
                                        &nbsp;&nbsp;
                                        <h4>End</h4>
                                        &nbsp;&nbsp;
                                        <select id="endWeek" class="dropdown form-control">
                                            <?php
                                            foreach ($allWeeks as $week) {
                                                // if last, select it
                                                if ($week['week_id'] == $allWeeks[count($allWeeks)-1]['week_id']) {
                                                    echo '<option selected value="'.$week['week_id'].'">'.$week['week_display'].'</option>';
                                                } else {
                                                    echo '<option value="'.$week['week_id'].'">'.$week['week_display'].'</option>';
                                                }
                                            }
                                            ?>
                                        </select>
                                        &nbsp;&nbsp;
                                        <h4>Week</h4>
                                        &nbsp;&nbsp;
                                        <select id="onlyWeek" class="dropdown form-control">
                                            <option value="0">All Weeks</option>
                                            <?php
                                            for ($i = 1; $i <= 14; $i++) {
                                                echo '<option value="'.$i.'">Week '.$i.'</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-sm-12" style="height: 600px;">
                                        <canvas id="pointsByWeekChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 table-padding">
                        <div class="card">
                            <div class="card-header">
                                <h4 style="float: right">Points By Season</h4>
                            </div>
                            <div class="card-body" style="background: #fff; direction: ltr;">
                                <div class="row">
                                    <div class="col-sm-12" style="height: 600px;">
                                        <canvas id="pointsBySeasonChart"></canvas>
                                    </div>
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

        // Initialize the page with the correct tab active - delay to ensure showCard function is loaded
        setTimeout(function() {
            if (typeof showCard === 'function') {
                // Check if a hash is present in the URL
                if (window.location.hash) {
                    // Remove the # from the hash
                    const tabName = window.location.hash.substring(1);
                    showCard(tabName);
                } else {
                    // Default to overview tab
                    showCard('overview');
                }
            }
        }, 100);

        if ("<?php echo $versusSet; ?>" == true) {
            document.getElementById('versus').scrollIntoView(true);
        }

        let managerName = "<?php echo $managerName; ?>";
        let baseUrl = "<?php echo $BASE_URL; ?>";
        $('#versus-select').change(function() {
            window.location = baseUrl+'profile.php?id='+managerName+'&versus='+$('#versus-select').val()+'#head-to-head';
        });

        $('#oppRecordSelector').change(function() {
            
            if ($('#oppRecordSelector').val() == 'reg') {
                $('#datatable-regSeason').show();
                $('#datatable-postseason').hide();
                
                // Adjust DataTable columns after showing
                setTimeout(function() {
                    $('#datatable-regSeason').DataTable().columns.adjust().draw();
                }, 10);
                
                // Only hide/show charts if they exist
                if (typeof postseasonWinsChart !== 'undefined' && postseasonWinsChart) {
                    $('#postseasonWinsChart').hide();
                }
                if (typeof winsChart !== 'undefined' && winsChart) {
                    $('#winsChart').show();
                }
            } else {
                $('#datatable-regSeason').hide();
                $('#datatable-postseason').show();
                
                // Adjust DataTable columns after showing
                setTimeout(function() {
                    $('#datatable-postseason').DataTable().columns.adjust().draw();
                }, 10);
                
                // Only hide/show charts if they exist
                if (typeof winsChart !== 'undefined' && winsChart) {
                    $('#winsChart').hide();
                }
                if (typeof postseasonWinsChart !== 'undefined' && postseasonWinsChart) {
                    $('#postseasonWinsChart').show();
                }
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

        // Custom sorting function for week column (handles both numeric weeks and playoff rounds)
        $.fn.dataTable.ext.type.order['week-pre'] = function (data) {
            // Remove any HTML tags and get the text content
            var week = data.replace(/<[^>]*>/g, '');
            
            // Handle playoff rounds with high numeric values to sort them after regular weeks
            if (week === 'Quarterfinal') return 20;
            if (week === 'Semifinal') return 21;
            if (week === 'Final') return 22;
            
            // For numeric weeks, convert to integer
            var num = parseInt(week);
            return isNaN(num) ? 0 : num;
        };

        $('#datatable-versus').DataTable({
            searching: false,
            paging: false,
            info: false,
            order: [
                [0, "desc"],
                [1, "desc"]
            ],
            columnDefs: [{
                type: 'week',
                targets: 1  // Week column
            }]
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
        let colors = ["#9c68d9","#a6c6fa","#3cf06e","#f33c47","#c0f6e6","#def89f","#dca130","#ff7f2c","#ecb2b6"," #f87598"];
        
        let obj = {};
        obj.label = 'Wins';
        obj.data = wins;
        obj.backgroundColor = colors;
        obj.datalabels = {
            align: 'end'
        };

        winsChart = new Chart(ctx, {
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

        postseasonWinsChart = new Chart(ctx, {
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

    $.ajax({
        url: 'dataLookup.php',
        data:  {
            dataType: 'positions-drafted',
            manager: <?php echo $managerId; ?>
        },
        error: function() {
            console.log('Error');
        },
        success: function(response) {
            data = JSON.parse(response);
        
            var ctx = $('#posByRoundChart');
            positionsDraftedChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [{
                            label: "QB",
                            data: data.QB,
                            backgroundColor: '#9c68d9'
                        },{
                            label: "RB",
                            data: data.RB,
                            backgroundColor: '#a6c6fa'
                        },{
                            label: "WR",
                            data: data.WR,
                            backgroundColor: '#3cf06e'
                        },{
                            label: "TE",
                            data: data.TE,
                            backgroundColor: '#f33c47'
                        },{
                            label: "K",
                            data: data.K,
                            backgroundColor: '#f87598'
                        },{
                            label: "DEF",
                            data: data.DEF,
                            backgroundColor: '#ff7f2c'
                        },{
                            label: "IDP",
                            data: data.IDP,
                            backgroundColor: '#c0f6e6'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            stacked: true,
                            display: true,
                            title: {
                                display: true,
                                text: 'Draft Round',
                                font: {
                                    size: 20
                                }
                            }
                        },
                        y: {
                            stacked: true,
                            display: true,
                            title: {
                                display: true,
                                text: 'Selections',
                                font: {
                                    size: 20
                                }
                            }
                        }
                    },
                    plugins: {
                        datalabels: {
                            align: 'center',
                            anchor: 'center',
                            color: 'white',
                            font: {
                                weight: 'bold'
                            }
                        }
                    }
                },
                plugins: [ChartDataLabels]
            });
        }
    });

    function updatePointsChart() {
        $.ajax({
            url: 'dataLookup.php',
            data:  {
                dataType: 'points-by-week',
                manager: <?php echo $managerId; ?>,
                startWeek: $('#startWeek').val(),
                endWeek: $('#endWeek').val(),
                onlyWeek: $('#onlyWeek').val()
            },
            error: function() {
                console.log('Error');
            },
            success: function(response) {
                data = JSON.parse(response);
                var ctx = $('#pointsByWeekChart');
                pointsByWeekChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.weeks,
                        datasets: [{
                            label: 'Points',
                            data: data.points,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                stacked: true,
                                display: true,
                                title: {
                                    display: true,
                                    text: 'Points',
                                    font: {
                                        size: 20
                                    }
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            datalabels: {
                                align: 'top',
                                anchor: 'top',
                                font: {
                                    weight: 'bold'
                                }
                            }
                        }
                    },
                    plugins: [ChartDataLabels]
                });
            }
        });
    }

    updatePointsChart();

    // Points by Season Chart
    function renderPointsBySeasonChart() {
        managerName = "<?php echo $managerName; ?>";

        $.ajax({
            url: 'dataLookup.php',
            data: {
                dataType: 'points-by-season',
                manager: <?php echo $managerId; ?>
            },
            error: function() {
                console.log('Error loading points by season');
            },
            success: function(response) {
                var data = JSON.parse(response);
                var ctx = document.getElementById('pointsBySeasonChart').getContext('2d');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.seasons,
                        datasets: [
                            {
                                label: managerName+"'s Points",
                                data: data.managerPoints,
                                borderColor: '#297eff',
                                backgroundColor: 'rgba(46,184,46,0.1)',
                                fill: false,
                                tension: 0.2
                            },
                            {
                                label: 'League Average',
                                data: data.leagueAverages,
                                borderColor: '#656567',
                                backgroundColor: 'rgba(41,126,255,0.1)',
                                fill: false,
                                tension: 0.2
                            },
                            {
                                label: 'League High',
                                data: data.leagueHighs,
                                borderColor: '#2eb82e',
                                backgroundColor: 'rgba(243,60,71,0.1)',
                                fill: false,
                                tension: 0.2
                            },
                            {
                                label: 'League Low',
                                data: data.leagueLows,
                                borderColor: '#f33c47',
                                backgroundColor: 'rgba(189,189,189,0.1)',
                                fill: false,
                                tension: 0.2
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true
                            },
                            datalabels: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                title: {
                                    display: true,
                                    text: 'Total Points'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Season'
                                }
                            }
                        }
                    }
                });
            }
        });
    }

    renderPointsBySeasonChart();
    let season = <?php echo $season; ?>;

    $('#startWeek').change(function() {
        pointsByWeekChart.destroy();
        updatePointsChart();
    });
    $('#endWeek').change(function() {
        pointsByWeekChart.destroy();
        updatePointsChart();
    });
    $('#onlyWeek').change(function() {
        pointsByWeekChart.destroy();
        updatePointsChart();
    });

    let allWeeks = <?php echo json_encode($allWeeks); ?>;
    $('#allSeasons').click(function() {
        // change startWeek to first week of first season
        $('#startWeek').val('1_2006');
        // get the very last item in the array
        $('#endWeek').val(allWeeks[allWeeks.length-1]['week_id']);
        pointsByWeekChart.destroy();
        updatePointsChart();
    })

    $('#currentSeason').click(function() {
        // change startWeek to first week of current season
        $('#startWeek').val('1_'+season);
        $('#endWeek').val(allWeeks[allWeeks.length-1]['week_id']);
        pointsByWeekChart.destroy();
        updatePointsChart();
    });

    $('#lastSeason').click(function() {
        // change startWeek to first week of last season
        $('#startWeek').val('1_'+(season-1));
        $('#endWeek').val('14_'+(season-1));
        pointsByWeekChart.destroy();
        updatePointsChart();
    });

    $('#lastFiveSeasons').click(function() {
        // change startWeek to first week of last season
        $('#startWeek').val('1_'+(season-5));
        $('#endWeek').val(allWeeks[allWeeks.length-1]['week_id']);
        pointsByWeekChart.destroy();
        updatePointsChart();
    });

    // Finishes Chart
    var seasonNumbers = <?php echo json_encode($seasonNumbers); ?>;
    var finishesData = { '1-3': 0, '4-7': 0, '8-10': 0 };
    
    // Process season numbers to group finishes
    for (var year in seasonNumbers) {
        var finish = parseInt(seasonNumbers[year].finish);
        if (finish >= 1 && finish <= 3) {
            finishesData['1-3']++;
        } else if (finish >= 4 && finish <= 7) {
            finishesData['4-7']++;
        } else if (finish >= 8 && finish <= 10) {
            finishesData['8-10']++;
        }
    }
    
    var finishesCtx = $('#finishPieChart');
    var finishesLabels = ['1st-3rd Place', '4th-7th Place', '8th-10th Place'];
    var finishesValues = [finishesData['1-3'], finishesData['4-7'], finishesData['8-10']];
    var finishesColors = ["#3cf06e", "#ff7f2c", "#f33c47"]; // Green for top, orange for middle, red for bottom
    
    let finishesObj = {};
    finishesObj.label = 'Finishes';
    finishesObj.data = finishesValues;
    finishesObj.backgroundColor = finishesColors;
    finishesObj.datalabels = {
        align: 'end'
    };

    finishesChart = new Chart(finishesCtx, {
        type: 'pie',
        data: {
            labels: finishesLabels,
            datasets: [finishesObj]
        },
        options: {
            plugins: {
                legend: {
                    display: false,
                },
                datalabels: {
                    formatter: function(value, context) {
                        if (value === 0) return ''; // Don't show label for 0 values
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

</script>