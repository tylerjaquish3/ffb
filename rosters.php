<?php

$pageName = "Rosters";
include 'header.php';
include 'sidebar.html';

$managerName = 'Andy';
$versus = '';
$year = 2023;
$week = 1;
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

$posOrder = ['QB', 'RB', 'WR', 'TE', 'W/R/T', 'W/R', 'W/T', 'Q/W/R/T', 'K', 'DEF', 'D', 'DB', 'BN', 'IR'];

?>

<div class="app-content content container-fluid">
    <div class="content-wrapper">
        <div class="content-header row"></div>

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
                                            $result = query("SELECT distinct week_number FROM regular_season_matchups ORDER BY week_number ASC");
                                            while ($row = fetch_array($result)) {
                                                if ($row['week_number'] == $week) {
                                                    echo '<option selected value="'.$row['week_number'].'">'.$row['week_number'].'</option>';
                                                } else {
                                                    echo '<option value="'.$row['week_number'].'">'.$row['week_number'].'</option>';
                                                }
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

            <div class="row">
                <div class="col-sm-12 table-padding">
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
                                            <td><?php echo $recap['margin1']; ?></td>
                                            <td><?php echo $recap['margin2']; ?></td>
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
            </div>

            <div class="row">
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
                                            <th>Projected</th>
                                            <th>Points</th>
                                            <th>Drafted?</th>
                                            <th>Week Rk</th>
                                            <th>Week Pos Rk</th>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $result = query("SELECT r.player, r.*, round FROM rosters r
                                                LEFT JOIN draft on draft.player = r.player AND draft.year = r.year
                                                WHERE r.year = $year AND week = $week AND manager = '$managerName'");
                                            while ($row = fetch_array($result)) {
                                                $rank = getPlayerRank($row['player'], $row['year'], $row['week']);
                                                $posRank = getPlayerPositionRank($row['player'], $row['roster_spot'], $row['position'], $row['year'], $row['week']);
                                                $order = array_search($row['roster_spot'], $posOrder);
                                                echo '<tr>';
                                                echo '<td data-order='.$order.'>'.$row['roster_spot'].'</td>';
                                                echo '<td><a href="/players.php?player='.$row['player'].'">'.$row['player'].'</a></td>';
                                                echo '<td class="text-right"><i>'.
                                                    number_format($row['projected'], 2)
                                                    .'</i></td>';
                                                echo '<td class="text-right"><strong>'.
                                                    number_format($row['points'], 2)
                                                    .'</strong></td>';
                                                if ($row['round']) {
                                                    echo '<td><i class="icon-table"></i></td>';
                                                } else {
                                                    echo '<td></td>';
                                                }
                                                echo '<td>'.$rank.'</td>';
                                                echo '<td>'.$posRank.'</td>';
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
                                            <th>Projected</th>
                                            <th>Points</th>
                                            <th>Drafted?</th>
                                            <th>Week Rk</th>
                                            <th>Week Pos Rk</th>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $result = query("SELECT r.player, r.*, round FROM rosters r
                                                LEFT JOIN draft on draft.player = r.player AND draft.year = r.year
                                                WHERE r.year = $year AND week = $week AND manager = '$versus'");
                                            while ($row = fetch_array($result)) {
                                                $rank = getPlayerRank($row['player'], $row['year'], $row['week']);
                                                $posRank = getPlayerPositionRank($row['player'], $row['roster_spot'], $row['position'], $row['year'], $row['week']);
                                                $order = array_search($row['roster_spot'], $posOrder);
                                                echo '<tr>';
                                                echo '<td data-order='.$order.'>'.$row['roster_spot'].'</td>';
                                                echo '<td><a href="/players.php?player='.$row['player'].'">'.$row['player'].'</a></td>';
                                                echo '<td class="text-right"><i>'.
                                                    number_format($row['projected'], 2)
                                                    .'</i></td>';
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
                <div class="col-sm-12 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Points by Position</h4>
                        </div>
                        <div class="card-body">
                            <div class="card-block">
                                <canvas id="posPointsChart" style="direction: ltr;"></canvas>
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
            "searching": false,
            "paging": false,
            "info": false,
            "order": [
                [0, "asc"]
            ]
        });
        
        $('#datatable-versusRoster').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [
                [0, "asc"]
            ]
        });

        var ctx = $('#posPointsChart');
        var stackedBar = new Chart(ctx, {
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
    });
</script>