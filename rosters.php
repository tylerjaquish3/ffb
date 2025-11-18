<?php

$pageName = "Rosters";
include 'header.php';
include 'sidebar.php';

$playoffRoster = false;
$managerName = 'Andy';
$versus = '';
$year = $season;
if (isset($_GET['manager'])) {
    $managerName = $_GET['manager'];
    if (isset($_GET['year'])) {
        $year = $_GET['year'];
    }
    if (isset($_GET['week'])) {
        $week = $_GET['week'];
    }
}
$result = query("SELECT * FROM managers WHERE name = '" . $managerName . "'");
while ($row = fetch_array($result)) {
    $managerId = $row['id'];
}
$managerPoints = $versusPoints = 0;

$result = query("SELECT * FROM regular_season_matchups rsm
    JOIN managers on managers.id = rsm.manager2_id
    WHERE year = $year and week_number = $week and manager1_id = $managerId");
while ($row = fetch_array($result)) {
    $versus = $row['name'];
    $managerPoints = $row['manager1_score'];
    $versusPoints = $row['manager2_score'];
}

if ($managerPoints == 0) {
    $playoffRoster = true;
    $result = query("SELECT distinct week_number FROM regular_season_matchups WHERE year = $year ORDER BY week_number ASC");
    $lastWeek = 0;
    while ($row = fetch_array($result)) {
        $lastWeek++;
    }

    // Find round based on week
    if ($week == $lastWeek+1) {
        $round = 'Quarterfinal';
    } else if ($week == $lastWeek+2) {
        $round = 'Semifinal';
    } else if ($week >= $lastWeek+3) {
        $round = 'Final';
    }


    $versus = null;
    // Has to be a playoff matchup, so look in playoff_matchups table
    $result = query("SELECT m.name as m1, l.name as m2, pm.manager1_id, pm.manager2_id, pm.year, pm.round, pm.manager1_seed, pm.manager2_seed, pm.manager1_score, pm.manager2_score
        FROM managers m
        JOIN playoff_matchups pm ON pm.manager1_id = m.id
        LEFT JOIN (
        SELECT name, manager2_id, year, round, manager2_score FROM playoff_matchups pm2
            JOIN managers ON managers.id = pm2.manager2_id
        ) l ON l.manager2_id = pm.manager2_id AND l.year = pm.year AND l.round = pm.round
        WHERE pm.year = $year and pm.round = '$round' and (m.name = '$managerName' OR l.name = '$managerName')");
    while ($row = fetch_array($result)) {

        if ($row['m1'] == $managerName) {
            $versus = $row['m2'];
            $versusId = $row['manager2_id'];
            $managerPoints = $row['manager1_score'];
            $versusPoints = $row['manager2_score'];
        } else {
            $versus = $row['m1'];
            $versusId = $row['manager1_id'];
            $managerPoints = $row['manager2_score'];
            $versusPoints = $row['manager1_score'];
        }
    }
}

$posOrder = ['QB', 'RB', 'WR', 'TE', 'W/R/T', 'W/R', 'W/T', 'Q/W/R/T', 'K', 'DEF', 'D', 'DL', 'LB', 'DB', 'BN', 'IR'];

function lookupGameTime(?int $id) {
    switch ($id) {
        case 1:
            return 'Thursday';
        case 2:
            return 'Friday';
        case 3:
            return 'Sunday Early';
        case 4:
            return 'Sunday Late';
        case 5:
            return 'Sunday Night';
        case 6:
            return 'Monday';
        case 7:
            return 'Tuesday';
        case 8:
            return 'Other';
        default:
            return '';
    }
}
?>

<div class="app-content content">
    <div class="content-wrapper">

        <div class="content-body">
            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Filters</h4>
                        </div>
                        <div class="card-body" style="direction: ltr;">
                            <div class="row">
                                <div class="col-sm-12 col-md-4">
                                    <h3 class="text-center">
                                        Manager<br />
                                        <select id="manager-select" class="form-control w-50">
                                            <?php
                                            $result = query("SELECT * FROM managers ORDER BY name ASC");
                                            while ($row = fetch_array($result)) {
                                                if ($row['id'] == $managerId) {
                                                    echo '<option selected value="'.$row['name'].'">'.$row['name'].'</option>';
                                                } else {
                                                    echo '<option value="'.$row['name'].'">'.$row['name'].'</option>';
                                                }
                                            }
                                            ?>
                                        </select>
                                    </h3>
                                </div>
                                <div class="col-sm-12 col-md-4">
                                    <h3 class="text-center">
                                        Week
                                        <select id="week-select" class="form-control w-50">
                                            <?php
                                            $result = query("SELECT distinct week_number FROM regular_season_matchups where year = $season ORDER BY week_number ASC");
                                            $lastWeek = 0;
                                            while ($row = fetch_array($result)) {
                                                $lastWeek++;
                                                if ($row['week_number'] == $week) {
                                                    echo '<option selected value="'.$row['week_number'].'">'.$row['week_number'].'</option>';
                                                } else {
                                                    echo '<option value="'.$row['week_number'].'">'.$row['week_number'].'</option>';
                                                }
                                            }
                                            if ($week == $lastWeek+1) {
                                                echo '<option selected value="'.($lastWeek+1).'">Quarterfinal</option>';
                                            } else {
                                                echo '<option value="'.($lastWeek+1).'">Quarterfinal</option>';
                                            }
                                            if ($week == $lastWeek+2) {
                                                echo '<option selected value="'.($lastWeek+2).'">Semifinal</option>';
                                            } else {
                                                echo '<option value="'.($lastWeek+2).'">Semifinal</option>';
                                            }
                                            if ($week == $lastWeek+3) {
                                                echo '<option selected value="'.($lastWeek+3).'">Final</option>';
                                            } else {
                                                echo '<option value="'.($lastWeek+3).'">Final</option>';
                                            }
                                            ?>
                                        </select>
                                    </h3>
                                </div>
                                <div class="col-sm-12 col-md-4">
                                    <h3 class="text-center">
                                        Year
                                        <select id="year-select" class="form-control w-50">
                                            <?php
                                            $result = query("SELECT distinct year FROM regular_season_matchups ORDER BY year DESC");
                                            while ($row = fetch_array($result)) {
                                                if ($row['year'] == $year) {
                                                    echo '<option selected value="'.$row['year'].'">'.$row['year'].'</option>';
                                                } else {
                                                    echo '<option value="'.$row['year'].'">'.$row['year'].'</option>';
                                                }
                                            }
                                            ?>
                                        </select>
                                    </h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php
            if (!$versus) {
                echo '<h1 class="text-center">No playoff matchup found for '.$managerName.' in '.$year.' '.$round.'</h1>';
            } else {
            ?>

            <!-- Tabs Navigation -->
            <div class="row mb-1" style="margin-bottom: 10px;">
                <div class="col-sm-12">
                    <div class="tab-buttons-container">
                        <button class="tab-button active" id="recap-tab" onclick="showCard('recap')">Recap</button>
                        <button class="tab-button" id="matchup-rosters-tab" onclick="showCard('matchup-rosters')">Matchup Rosters</button>
                        <button class="tab-button" id="player-stats-tab" onclick="showCard('player-stats')">Player Stats</button>
                        <button class="tab-button" id="points-by-position-tab" onclick="showCard('points-by-position')">Points by Position</button>
                        <button class="tab-button" id="full-season-roster-tab" onclick="showCard('full-season-roster')">Full Season Roster</button>
                    </div>
                </div>
            </div>

            <div class="row card-section" id="recap">
                <div class="col-md-6 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4>Recap</h4>
                        </div>
                        <div class="card-body" style="direction: ltr;">
                            <div class="row">
                                <div class="col-sm-12">
                                    <table class="table table-responsive table-striped nowrap">
                                        <tr>
                                            <td><strong>Managers</strong></td>
                                            <td><?php echo $recap['man1']; ?></td>
                                            <td><?php echo $recap['man2']; ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Total</strong></td>
                                            <td><?php echo $recap['points1']; ?></td>
                                            <td><?php echo $recap['points2']; ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Margin</strong></td>
                                            <td><?php echo round($recap['margin1'], 2); ?></td>
                                            <td><?php echo round($recap['margin2'], 2); ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Projected</strong></td>
                                            <td><?php echo $recap['projected1']; ?></td>
                                            <td><?php echo $recap['projected2']; ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Top Scorer</strong></td>
                                            <td><?php echo $recap['top_scorer1']; ?></td>
                                            <td><?php echo $recap['top_scorer2']; ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Bottom Scorer</strong></td>
                                            <td><?php echo $recap['bottom_scorer1']; ?></td>
                                            <td><?php echo $recap['bottom_scorer2']; ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Bench Points</strong></td>
                                            <td><?php echo $recap['bench1']; ?></td>
                                            <td><?php echo $recap['bench2']; ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Record Before</strong></td>
                                            <td><?php echo $recap['record1before']; ?></td>
                                            <td><?php echo $recap['record2before']; ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Record After</strong></td>
                                            <td><?php echo $recap['record1after']; ?></td>
                                            <td><?php echo $recap['record2after']; ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Points by Game Time</h4>
                        </div>
                        <div class="card-body">
                            <div class="card-block chart-block">
                                <canvas id="gameTimeChart" style="direction: ltr;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row card-section" id="matchup-rosters" style="display: none;">
                <div class="col-sm-12 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4>Matchup Rosters</h4>
                        </div>
                        <div class="card-body" style="direction: ltr;">

                            <div class="row">
                                <div class="col-sm-12 col-md-6">
                                    <h2 class="text-center">
                                        <?php echo $managerName; ?><br />
                                        <?php echo 'Total: '.$managerPoints; ?>
                                    </h2>
                                    <table class="table table-responsive table-striped nowrap" id="datatable-managerRoster">
                                        <thead>
                                            <th>Position</th>
                                            <th>Player</th>
                                            <th>Team</th>
                                            <th>Points</th>
                                            <th>Drafted?</th>
                                            <th>Week Rk</th>
                                            <th>Week Pos Rk</th>
                                            <th>Game Time</th>
                                        </thead>
                                        <tbody>
                                            <?php
                                            if ($playoffRoster) {
                                                $result = query("SELECT r.player, r.*, d.round FROM playoff_rosters r
                                                    JOIN managers m on m.name = r.manager
                                                    LEFT JOIN draft d on d.player = r.player AND d.year = r.year and d.manager_id = m.id
                                                    WHERE r.year = $year AND week = $week AND manager = '$managerName'");
                                            } else {
                                                $result = query("SELECT r.player, r.*, round FROM rosters r
                                                    JOIN managers m on m.name = r.manager
                                                    LEFT JOIN draft d on d.player = r.player AND d.year = r.year and d.manager_id = m.id
                                                    WHERE r.year = $year AND week = $week AND manager = '$managerName'");
                                            }
                                            while ($row = fetch_array($result)) {
                                                $rank = getPlayerRank($row['player'], $row['year'], $row['week']);
                                                $posRank = getPlayerPositionRank($row['player'], $row['roster_spot'], $row['position'], $row['year'], $row['week']);
                                                $order = array_search($row['roster_spot'], $posOrder);
                                                echo '<tr>';
                                                echo '<td data-order='.$order.'>'.$row['roster_spot'].'</td>';
                                                echo '<td><a href="/players.php?player='.$row['player'].'">'.$row['player'].'</a></td>';
                                                echo '<td>'.$row['team'].'</td>';
                                                echo '<td class="text-right"><strong>'.
                                                    number_format((float)$row['points'], 2)
                                                    .'</strong></td>';
                                                if ($row['round']) {
                                                    echo '<td><i class="icon-table"></i></td>';
                                                } else {
                                                    echo '<td></td>';
                                                }
                                                echo '<td>'.$rank.'</td>';
                                                echo '<td>'.$posRank.'</td>';
                                                echo '<td>'.lookupGameTime($row['game_slot']).'</td>';
                                            } ?>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="col-sm-12 col-md-6">
                                    <h2 class="text-center">
                                        <?php echo $versus; ?><br />
                                        <?php echo 'Total: '.$versusPoints; ?>
                                    </h2>
                                    <table class="table table-responsive table-striped nowrap" id="datatable-versusRoster">
                                        <thead>
                                            <th>Position</th>
                                            <th>Player</th>
                                            <th>Team</th>
                                            <th>Points</th>
                                            <th>Drafted?</th>
                                            <th>Week Rk</th>
                                            <th>Week Pos Rk</th>
                                            <th>Game Time</th>
                                        </thead>
                                        <tbody>
                                            <?php
                                            if ($playoffRoster) {
                                                $result = query("SELECT r.player, r.*, draft.round FROM playoff_rosters r
                                                    LEFT JOIN draft on draft.player = r.player AND draft.year = r.year
                                                    WHERE r.year = $year AND week = $week AND manager = '$versus'");
                                            } else {
                                                $result = query("SELECT r.player, r.*, round FROM rosters r
                                                    LEFT JOIN draft on draft.player = r.player AND draft.year = r.year
                                                    WHERE r.year = $year AND week = $week AND manager = '$versus'");
                                            }
                                            while ($row = fetch_array($result)) {
                                                $rank = getPlayerRank($row['player'], $row['year'], $row['week']);
                                                $posRank = getPlayerPositionRank($row['player'], $row['roster_spot'], $row['position'], $row['year'], $row['week']);
                                                $order = array_search($row['roster_spot'], $posOrder);
                                                echo '<tr>';
                                                echo '<td data-order='.$order.'>'.$row['roster_spot'].'</td>';
                                                echo '<td><a href="/players.php?player='.$row['player'].'">'.$row['player'].'</a></td>';
                                                echo '<td>'.$row['team'].'</td>';
                                                echo '<td class="text-right"><strong>'.
                                                    number_format($row['points'], 2)
                                                    .'</strong></td>';
                                                if ($row['round']) {
                                                    echo '<td><i class="icon-table" alt="Drafted"></i></td>';
                                                } else {
                                                    echo '<td></td>';
                                                }
                                                echo '<td>'.$rank.'</td>';
                                                echo '<td>'.$posRank.'</td>';
                                                echo '<td>'.lookupGameTime($row['game_slot']).'</td>';
                                            } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row card-section" id="player-stats" style="display: none;">
                <div class="col-sm-12 col-md-6 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4>Player Stats Comparison</h4>
                        </div>
                        <div class="card-body" style="direction: ltr;">
                            <?php
                            // Query stats for both managers in this matchup
                            $statsAvailable = false;
                            $managerStats = [];
                            $versusStats = [];
                            
                            // Check if stats exist for this year/week
                            $statsCheckQuery = "SELECT COUNT(*) as count FROM rosters r 
                                JOIN stats s ON s.roster_id = r.id 
                                WHERE r.year = $year AND r.week = $week";
                            $statsCheckResult = query($statsCheckQuery);
                            $statsCheckRow = fetch_array($statsCheckResult);
                            
                            if ($statsCheckRow['count'] > 0) {
                                $statsAvailable = true;
                                
                                // Get stats for the main manager
                                if ($playoffRoster) {
                                    $managerStatsQuery = "SELECT 
                                        SUM(s.pass_yds) AS pass_yds, SUM(s.pass_tds) AS pass_tds, SUM(s.ints) AS ints,
                                        SUM(s.rush_yds) AS rush_yds, SUM(s.rush_tds) AS rush_tds,
                                        SUM(s.receptions) AS receptions, SUM(s.rec_yds) AS rec_yds, SUM(s.rec_tds) AS rec_tds,
                                        SUM(s.fumbles) AS fumbles
                                        FROM playoff_rosters r
                                        JOIN stats s ON s.roster_id = r.id
                                        WHERE r.year = $year AND r.week = $week AND r.manager = '$managerName' AND r.roster_spot != 'BN' AND r.roster_spot != 'IR'";
                                } else {
                                    $managerStatsQuery = "SELECT 
                                        SUM(s.pass_yds) AS pass_yds, SUM(s.pass_tds) AS pass_tds, SUM(s.ints) AS ints,
                                        SUM(s.rush_yds) AS rush_yds, SUM(s.rush_tds) AS rush_tds,
                                        SUM(s.receptions) AS receptions, SUM(s.rec_yds) AS rec_yds, SUM(s.rec_tds) AS rec_tds,
                                        SUM(s.fumbles) AS fumbles
                                        FROM rosters r
                                        JOIN stats s ON s.roster_id = r.id
                                        WHERE r.year = $year AND r.week = $week AND r.manager = '$managerName' AND r.roster_spot != 'BN' AND r.roster_spot != 'IR'";
                                }
                                $result = query($managerStatsQuery);
                                $managerStats = fetch_array($result);
                                
                                // Get stats for the versus manager
                                if ($playoffRoster) {
                                    $versusStatsQuery = "SELECT 
                                        SUM(s.pass_yds) AS pass_yds, SUM(s.pass_tds) AS pass_tds, SUM(s.ints) AS ints,
                                        SUM(s.rush_yds) AS rush_yds, SUM(s.rush_tds) AS rush_tds,
                                        SUM(s.receptions) AS receptions, SUM(s.rec_yds) AS rec_yds, SUM(s.rec_tds) AS rec_tds,
                                        SUM(s.fumbles) AS fumbles
                                        FROM playoff_rosters r
                                        JOIN stats s ON s.roster_id = r.id
                                        WHERE r.year = $year AND r.week = $week AND r.manager = '$versus' AND r.roster_spot != 'BN' AND r.roster_spot != 'IR'";
                                } else {
                                    $versusStatsQuery = "SELECT 
                                        SUM(s.pass_yds) AS pass_yds, SUM(s.pass_tds) AS pass_tds, SUM(s.ints) AS ints,
                                        SUM(s.rush_yds) AS rush_yds, SUM(s.rush_tds) AS rush_tds,
                                        SUM(s.receptions) AS receptions, SUM(s.rec_yds) AS rec_yds, SUM(s.rec_tds) AS rec_tds,
                                        SUM(s.fumbles) AS fumbles
                                        FROM rosters r
                                        JOIN stats s ON s.roster_id = r.id
                                        WHERE r.year = $year AND r.week = $week AND r.manager = '$versus' AND r.roster_spot != 'BN' AND r.roster_spot != 'IR'";
                                }
                                $result = query($versusStatsQuery);
                                $versusStats = fetch_array($result);
                            }
                            ?>
                            
                            <?php if (!$statsAvailable): ?>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <h4 class="alert-heading">Data not available</h4>
                                        <p>Player statistics are not available for <?php echo $year; ?> Week <?php echo $week; ?>.</p>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <table class="table table-responsive table-striped nowrap">
                                            <thead>
                                                <th>Statistic</th>
                                                <th class="text-center"><?php echo $managerName; ?></th>
                                                <th class="text-center"><?php echo $versus; ?></th>
                                                <th class="text-center">Difference</th>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><strong>Total Yards</strong></td>
                                                    <td class="text-center"><?php echo number_format(($managerStats['pass_yds'] ?? 0) + ($managerStats['rush_yds'] ?? 0) + ($managerStats['rec_yds'] ?? 0)); ?></td>
                                                    <td class="text-center"><?php echo number_format(($versusStats['pass_yds'] ?? 0) + ($versusStats['rush_yds'] ?? 0) + ($versusStats['rec_yds'] ?? 0)); ?></td>
                                                    <td class="text-center"><?php 
                                                        $managerTotal = ($managerStats['pass_yds'] ?? 0) + ($managerStats['rush_yds'] ?? 0) + ($managerStats['rec_yds'] ?? 0);
                                                        $versusTotal = ($versusStats['pass_yds'] ?? 0) + ($versusStats['rush_yds'] ?? 0) + ($versusStats['rec_yds'] ?? 0);
                                                        $diff = $managerTotal - $versusTotal;
                                                        echo ($diff > 0 ? '+' : '') . number_format($diff);
                                                    ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Total TDs</strong></td>
                                                    <td class="text-center"><?php echo ($managerStats['pass_tds'] ?? 0) + ($managerStats['rush_tds'] ?? 0) + ($managerStats['rec_tds'] ?? 0); ?></td>
                                                    <td class="text-center"><?php echo ($versusStats['pass_tds'] ?? 0) + ($versusStats['rush_tds'] ?? 0) + ($versusStats['rec_tds'] ?? 0); ?></td>
                                                    <td class="text-center"><?php 
                                                        $managerTds = ($managerStats['pass_tds'] ?? 0) + ($managerStats['rush_tds'] ?? 0) + ($managerStats['rec_tds'] ?? 0);
                                                        $versusTds = ($versusStats['pass_tds'] ?? 0) + ($versusStats['rush_tds'] ?? 0) + ($versusStats['rec_tds'] ?? 0);
                                                        $diff = $managerTds - $versusTds;
                                                        echo ($diff > 0 ? '+' : '') . $diff;
                                                    ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Pass Yards</strong></td>
                                                    <td class="text-center"><?php echo number_format($managerStats['pass_yds'] ?? 0); ?></td>
                                                    <td class="text-center"><?php echo number_format($versusStats['pass_yds'] ?? 0); ?></td>
                                                    <td class="text-center"><?php 
                                                        $diff = ($managerStats['pass_yds'] ?? 0) - ($versusStats['pass_yds'] ?? 0);
                                                        echo ($diff > 0 ? '+' : '') . number_format($diff);
                                                    ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Pass TDs</strong></td>
                                                    <td class="text-center"><?php echo $managerStats['pass_tds'] ?? 0; ?></td>
                                                    <td class="text-center"><?php echo $versusStats['pass_tds'] ?? 0; ?></td>
                                                    <td class="text-center"><?php 
                                                        $diff = ($managerStats['pass_tds'] ?? 0) - ($versusStats['pass_tds'] ?? 0);
                                                        echo ($diff > 0 ? '+' : '') . $diff;
                                                    ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Interceptions</strong></td>
                                                    <td class="text-center"><?php echo $managerStats['ints'] ?? 0; ?></td>
                                                    <td class="text-center"><?php echo $versusStats['ints'] ?? 0; ?></td>
                                                    <td class="text-center"><?php 
                                                        $diff = ($managerStats['ints'] ?? 0) - ($versusStats['ints'] ?? 0);
                                                        echo ($diff > 0 ? '+' : '') . $diff;
                                                    ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Rush Yards</strong></td>
                                                    <td class="text-center"><?php echo number_format($managerStats['rush_yds'] ?? 0); ?></td>
                                                    <td class="text-center"><?php echo number_format($versusStats['rush_yds'] ?? 0); ?></td>
                                                    <td class="text-center"><?php 
                                                        $diff = ($managerStats['rush_yds'] ?? 0) - ($versusStats['rush_yds'] ?? 0);
                                                        echo ($diff > 0 ? '+' : '') . number_format($diff);
                                                    ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Rush TDs</strong></td>
                                                    <td class="text-center"><?php echo $managerStats['rush_tds'] ?? 0; ?></td>
                                                    <td class="text-center"><?php echo $versusStats['rush_tds'] ?? 0; ?></td>
                                                    <td class="text-center"><?php 
                                                        $diff = ($managerStats['rush_tds'] ?? 0) - ($versusStats['rush_tds'] ?? 0);
                                                        echo ($diff > 0 ? '+' : '') . $diff;
                                                    ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Receptions</strong></td>
                                                    <td class="text-center"><?php echo $managerStats['receptions'] ?? 0; ?></td>
                                                    <td class="text-center"><?php echo $versusStats['receptions'] ?? 0; ?></td>
                                                    <td class="text-center"><?php 
                                                        $diff = ($managerStats['receptions'] ?? 0) - ($versusStats['receptions'] ?? 0);
                                                        echo ($diff > 0 ? '+' : '') . $diff;
                                                    ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Receiving Yards</strong></td>
                                                    <td class="text-center"><?php echo number_format($managerStats['rec_yds'] ?? 0); ?></td>
                                                    <td class="text-center"><?php echo number_format($versusStats['rec_yds'] ?? 0); ?></td>
                                                    <td class="text-center"><?php 
                                                        $diff = ($managerStats['rec_yds'] ?? 0) - ($versusStats['rec_yds'] ?? 0);
                                                        echo ($diff > 0 ? '+' : '') . number_format($diff);
                                                    ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Receiving TDs</strong></td>
                                                    <td class="text-center"><?php echo $managerStats['rec_tds'] ?? 0; ?></td>
                                                    <td class="text-center"><?php echo $versusStats['rec_tds'] ?? 0; ?></td>
                                                    <td class="text-center"><?php 
                                                        $diff = ($managerStats['rec_tds'] ?? 0) - ($versusStats['rec_tds'] ?? 0);
                                                        echo ($diff > 0 ? '+' : '') . $diff;
                                                    ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Fumbles</strong></td>
                                                    <td class="text-center"><?php echo $managerStats['fumbles'] ?? 0; ?></td>
                                                    <td class="text-center"><?php echo $versusStats['fumbles'] ?? 0; ?></td>
                                                    <td class="text-center"><?php 
                                                        $diff = ($managerStats['fumbles'] ?? 0) - ($versusStats['fumbles'] ?? 0);
                                                        echo ($diff > 0 ? '+' : '') . $diff;
                                                    ?></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row card-section" id="points-by-position" style="display: none;">
                <div class="col-sm-12 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Points by Position</h4>
                        </div>
                        <div class="card-body">
                            <div class="card-block chart-block">
                                <canvas id="posPointsChart" style="direction: ltr;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row card-section" id="full-season-roster" style="display: none;">
                <div class="col-sm-12 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4><?php echo $managerName."'s Full Season Roster"; ?></h4>
                        </div>
                        <div class="card-body" style="direction: ltr;">
                            <?php
                            $grouped = [];
                            $result = query("SELECT week, player, points, roster_spot FROM rosters
                                WHERE year = $year AND manager = '$managerName'
                                AND roster_spot NOT IN ('IR','BN') ORDER BY week ASC");
                            while ($row = fetch_array($result)) {
                                // group results by roster_spot and week
                                $grouped[$row['week']][$row['roster_spot']][] = $row;
                            } 
                            // order grouped by roster_spot accoring to $posOrder
                            $ordered = [];
                            foreach ($grouped as $week => $positions) {
                                foreach ($posOrder as $pos) {
                                    if (isset($positions[$pos])) {
                                        $ordered[$week][$pos] = $positions[$pos];
                                    }
                                }
                            } ?>
                            <div class="row">
                                <div class="col-sm-12">
                                    <table class="table table-responsive table-striped nowrap" id="datatable-yearlyRosters">
                                        <thead>
                                            <th>Week</th>
                                            <?php 
                                            $positionCounts = [];
                                            foreach ($ordered as $week => $positions) {
                                                if ($week == 2) {
                                                    break;
                                                }
                                                foreach ($positions as $position => $players) {
                                                    $count = count($players);
                                                    foreach ($players as $player) {
                                                        echo '<th>'.$position.'</th>';
                                                    }
                                                    $positionCounts[$position] = $count;
                                                }
                                            } ?>
                                        </thead>
                                        <tbody>
                                            <?php
                                            foreach ($ordered as $week => $positions) {
                                                echo '<tr>';
                                                echo '<td>'.$week.'</td>';

                                                foreach ($positions as $position => $players) {
                                                    
                                                    foreach ($players as $player) {
                                                        echo '<td data-order="'.$player['points'].'">
                                                            <a href="/players.php?player='.$player['player'].'">'.$player['player'].'</a><br />'.
                                                            $player['points'].' pts</td>';
                                                    }
                                                    // if any positions were blank, put in an empty cell
                                                    if (count($players) <= $positionCounts[$position]) {
                                                        for ($i = 0; $i < $positionCounts[$position] - count($players); $i++) {
                                                            echo '<td data-order="0"></td>';
                                                        }
                                                    }
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
            </div>

            <?php } ?>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<style>
    /* Reduce spacing for tab navigation */
    .tab-buttons-container {
        padding: 10px 0 !important;
    }
</style>

<script type="text/javascript">
    $(document).ready(function() {

        let managerName = "<?php echo $managerName; ?>";
        let baseUrl = "<?php echo $BASE_URL; ?>";
        $('#year-select').change(function() {
            refreshPage();
        });
        $('#week-select').change(function() {
            refreshPage();
        });
        $('#manager-select').change(function() {
            refreshPage();
        });
        
        function refreshPage()
        {
            window.location = baseUrl+'rosters.php?manager='+$('#manager-select').val()+'&year='+$('#year-select').val()+'&week='+$('#week-select').val();
        }

        $('#datatable-managerRoster').DataTable({
            searching: false,
            paging: false,
            info: false,
            order: [
                [0, "asc"]
            ]
        });
        
        $('#datatable-versusRoster').DataTable({
            searching: false,
            paging: false,
            info: false,
            order: [
                [0, "asc"]
            ]
        });
        
        $('#datatable-yearlyRosters').DataTable({
            searching: false,
            paging: false,
            info: false,
            order: [
                [0, "asc"]
            ],
            initComplete: function() {
                var api = this.api();
                
                api.columns(':not(:first)').every(function() {
                    var col = this.index();
                    var array = [];
                    api.cells(null, col).every(function() {
                        var cell = this.node();
                        var record_id = $(cell).attr("data-order");
                        array.push(record_id)
                    })

                    last = array.length-1;
                    array.sort(function(a, b){return b-a});

                    api.cells(null, col).every( function() {
                        var cell = this.node();
                        var record_id = $( cell ).attr("data-order");
                        if (record_id === array[0]) {
                            $(this.node()).css('background-color', 'rgb(172, 240, 172)')
                        } else if (record_id === array[last]) {
                            $(this.node()).css('background-color', 'rgba(255, 85, 85, 0.32)')
                        }
                    });
                });
            }
        });

        var ctx = $('#posPointsChart');
        var posPointsChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($posPointsChart['labels']); ?>,
                datasets: [{
                        label: "<?php echo $managerName; ?>",
                        data: <?php echo isset($posPointsChart['points'][$managerName]) ? json_encode($posPointsChart['points'][$managerName]) : "0"; ?>,
                        // backgroundColor: '#04015d'
                        backgroundColor: '#297eff'
                    },
                    {
                        label: "<?php echo $versus; ?>",
                        data: <?php echo isset($posPointsChart['points'][$versus]) ? json_encode($posPointsChart['points'][$versus]) : "0"; ?>,
                        backgroundColor: '#2eb82e'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    datalabels: {
                        formatter: function(value, context) {
                            return Math.round(value * 10) / 10;
                        },
                        align: 'center',
                        anchor: 'center',
                        // color: 'white',
                        font: {
                            weight: 'bold'
                        }
                    }
                }
            },
            plugins: [ChartDataLabels]
        });

        var ctx = $('#gameTimeChart');
        var gameTimeChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($gameTimeChart['labels']); ?>,
                datasets: [{
                        label: "<?php echo $managerName; ?>",
                        data: <?php echo isset($gameTimeChart['points'][$managerName]) ? json_encode($gameTimeChart['points'][$managerName]) : "0"; ?>,
                        backgroundColor: '#297eff'
                    },
                    {
                        label: "<?php echo $versus; ?>",
                        data: <?php echo isset($gameTimeChart['points'][$versus]) ? json_encode($gameTimeChart['points'][$versus]) : "0"; ?>,
                        backgroundColor: '#2eb82e'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    datalabels: {
                        formatter: function(value, context) {
                            return Math.round(value * 10) / 10;
                        },
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

        // Initialize the page with Recap tab active
        showCard('recap');

    });
</script>