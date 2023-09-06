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
                                        } ?>
                                        <div class="col-sm-6 award good">
                                            <strong><?php echo $row['fact']; ?> </strong><br />
                                            <?php echo $value; ?> <br />
                                            <?php echo $row['note']; ?>
                                        </div>
                                    <?php } ?>
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
                                        } ?>
                                        <div class="col-sm-6 award bad">
                                            <strong><?php echo $row['fact']; ?> </strong><br />
                                            <?php echo $value; ?> <br />
                                            <?php echo $row['note']; ?>
                                        </div>
                                    <?php } ?>
                                </div>

                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12 col-md-3">
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
                                            </tr>

                                        <?php } ?>
                                    </tbody>
                                </table>

                                <table class="table table-responsive table-striped nowrap" id="datatable-postseason" style="display:none;">
                                    <thead>
                                        <th>Manager</th>
                                        <th>Wins</th>
                                        <th>Losses</th>
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
                                        while ($row = fetch_array($result)) { ?>
                                            <tr>
                                                <td><?php echo $row['name']; ?></td>
                                                <td><?php echo $row['totalWins']; ?></td>
                                                <td><?php echo $row['totalLosses']; ?></td>
                                            </tr>

                                        <?php } ?>
                                    </tbody>
                                </table>
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
                <div class="col-sm-12">
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
                                            (SELECT $playerPos FROM draft WHERE manager_id = $managerId AND round = 5 AND year = d.year) as r5_pick
                                            FROM draft d
                                            WHERE manager_id = $managerId AND round = 1"
                                        );
                                        while ($array = fetch_array($result)) { ?>
                                            <tr>
                                                <td><?php echo $array['year']; ?></td>
                                                <td><?php echo $array['position']; ?></td>
                                                <td><?php echo $array['r1_pick']; ?></td>
                                                <td><?php echo $array['r2_pick']; ?></td>
                                                <td><?php echo $array['r3_pick']; ?></td>
                                                <td><?php echo $array['r4_pick']; ?></td>
                                                <td><?php echo $array['r5_pick']; ?></td>
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
                <div class="col-sm-12 col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h4><a href="draft.php">Top Drafted Players</a></h4>
                        </div>
                        <div class="card-body">
                            <table class="table table-responsive table-striped nowrap" id="datatable-topPlayers">
                                <thead>
                                    <th>Player</th>
                                    <th>Times Drafted</th>
                                </thead>
                                <tbody>
                                    <?php
                                    $result = query(
                                        "SELECT COUNT(round) as times, player FROM draft
                                        WHERE manager_id = $managerId
                                        GROUP BY player
                                        HAVING times > 2
                                        ORDER BY times DESC"
                                    );
                                    while ($array = fetch_array($result)) { ?>
                                        <tr>
                                            <td><?php echo $array['player']; ?></td>
                                            <td><?php echo $array['times']; ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12 col-lg-8" id="versus">
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

                                            $pf += $array['manager1_score'];
                                            $pa += $array['manager2_score'];

                                            $margin = $array['manager1_score'] - $array['manager2_score'];
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
                                        <tr><td>Average Points For</td><td><?php echo round($pf/$total, 1); ?></td></tr>
                                        <tr><td>Average Points Against</td><td><?php echo round($pa/$total, 1); ?></td></tr>

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
                                            while ($array = fetch_array($result)) { ?>
                                                <tr class="highlight">
                                                    <td><?php echo $array['year']; ?></td>
                                                    <td><?php echo $array['week_number']; ?></td>
                                                    <?php if ($array['winning_manager_id'] == $managerId) {
                                                        echo '<td><span class="badge badge-primary">' . $managerName.'</span></td>';
                                                    } else {
                                                        echo '<td><span class="badge badge-secondary">' . $managerName.'</span></td>';
                                                    }?>
                                                    <td><?php echo $array['man1score'].' - '.$array['man2score']; ?></td>
                                                    <?php
                                                    if ($array['winning_manager_id'] == $versus) {
                                                        echo '<td><span class="badge badge-primary">' . $versusName.'</span></td>';
                                                    } else {
                                                        echo '<td><span class="badge badge-secondary">' . $versusName.'</span></td>';
                                                    } ?>
                                                    <td><?php echo round(abs($array['man1score'] - $array['man2score']), 2); ?></td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
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

<?php include 'footer.html'; ?>

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
            } else {
                $('#datatable-regSeason').hide();
                $('#datatable-postseason').show();
            }
        });

        $('#datatable-regSeason').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [
                [1, "desc"]
            ]
        });

        $('#datatable-postseason').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [
                [1, "desc"]
            ]
        });

        $('#datatable-seasons').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [
                [0, "desc"]
            ]
        });

        $('#datatable-teamNames').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [
                [0, "desc"]
            ]
        });

        $('#datatable-drafts').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [
                [0, "desc"]
            ]
        });

        $('#datatable-topPlayers').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [
                [1, "desc"]
            ]
        });

        $('#datatable-versus').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [
                [0, "desc"]
            ]
        });

        var ctx = $('#finishesChart');

        var years = <?php echo json_encode($finishesChart['years']); ?>;
        var yearLabels = years.split(",");
        var finishes = <?php echo json_encode($finishesChart['finishes']); ?>;
        var finishData = finishes.split(",");

        var line = new Chart(ctx, {
            type: 'line',
            data: {
                labels: yearLabels,
                datasets: [{
                    label: 'Finish',
                    data: finishData,
                    // borderColor: '#2eff37',
                    borderColor: '#2eb82e',
                }]
            },
            options: {
                scales: {
                    yAxes: [{
                        ticks: {
                            reverse: true,
                        }
                    }]
                }
            }
        });

    });
</script>