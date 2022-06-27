<?php
    $pageName = "Schedule";
    include 'header.php';
?>

<style>
    table td {
        padding: 0rem 1rem !important;
    }
</style>
<body>

    <?php
    $currentYear = date('Y');
    $allPositions = ['QB','RB','RB','WR','WR','WR','TE','W/R/T','Q/W/R/T','K','DEF','BN','BN','BN','BN','BN','BN'];
    $manager = 1;
    if (isset($_GET['id'])) {
        $manager = $_GET['id'];
    }

    $myPoints = $oppPoints = [];
    $schedule = [];
    $result = mysqli_query($conn, "SELECT * FROM schedule where manager1_id = $manager OR manager2_id = $manager ORDER BY week ASC");
    while ($row = mysqli_fetch_array($result)) {
        if ($row['manager1_id'] == $manager) {
            $schedule[$row['week']]['id'] = $row['manager2_id'];
            $schedule[$row['week']]['name'] = getManagerName($row['manager2_id']);
        } else {
            $schedule[$row['week']]['id'] = $row['manager1_id'];
            $schedule[$row['week']]['name'] = getManagerName($row['manager1_id']);
        }
    }
    $myName = getManagerName($manager);
    
    // var_dump($schedule);die;
    ?>

    <div class="app-content container-fluid">
        <div class="content-wrapper">
            <div class="content-header row"></div>

            <div class="content-body">
                <div class="row">
                    <div class="col-xs-12 col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="position-relative">
                                    <div class="card-header" style="direction: ltr;">
                                        Manager
                                        <select id="manager-select">
                                            <?php
                                            $result = mysqli_query($conn, "SELECT * FROM managers ORDER BY name ASC");
                                            while ($row = mysqli_fetch_array($result)) {
                                                if ($row['id'] == $manager) {
                                                    echo '<option selected value="'.$row['id'].'">'.$row['name'].'</option>';
                                                } else {
                                                    echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';
                                                }
                                            } ?>
                                        </select>
                                        Projected record: <span id="record">10-4</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                <?php
                for ($x = 1; $x < 15; $x++) {
                    headToHead($x);
                }
                
                function headToHead($week) { 
                    global $allPositions, $conn, $manager, $schedule;
                    ?>
                    <div class="col-xs-12 col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="position-relative">
                                    <div class="card-header">
                                        Week <?php echo $week.' - vs. '.$schedule[$week]['name']; ?>
                                        &nbsp;|&nbsp;
                                        <span id="outcome<?php echo $week; ?>"></span>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <table class="table table-responsive" id="datatable-teamByPosition">
                                                <thead>
                                                    <th>Pos</th>
                                                    <th>Player</th>
                                                    <th>Bye</th>
                                                    <th>PPG</th>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $myRoster = [];
                                                    $wrt = ['RB','WR','TE'];
                                                    $qwrt = ['QB','RB','WR','TE'];
                                                    foreach ($allPositions as $pos) {
                                                        $myRoster[] = [$pos => null];
                                                    }
                                                    $result = mysqli_query($conn,
                                                        "SELECT * FROM preseason_rankings
                                                        JOIN draft_selections ON preseason_rankings.id = draft_selections.ranking_id
                                                        WHERE manager_id = $manager AND bye != $week ORDER BY pick_number ASC"
                                                    );
                                                    while ($row = mysqli_fetch_array($result)) {
                                                        foreach ($myRoster as $key => &$rosterPos) {
                                                            foreach ($rosterPos as $k => &$pos) {
                                                                $filled = false;
                                                                if ($pos == null && $k == $row['position']) {
                                                                    $myRoster[$key] = $row;
                                                                    $filled = true;
                                                                    break;
                                                                } elseif ($pos == null && $k == 'W/R/T' && in_array($row['position'], $wrt)) {
                                                                    $myRoster[$key] = $row;
                                                                    $filled = true;
                                                                    break;
                                                                } elseif ($pos == null && $k == 'Q/W/R/T' && in_array($row['position'], $qwrt)) {
                                                                    $myRoster[$key] = $row;
                                                                    $filled = true;
                                                                    break;
                                                                } elseif ($pos == null && $k == 'BN') {
                                                                    $myRoster[$key] = $row;
                                                                    $filled = true;
                                                                    break;
                                                                }
                                                            }
                                                            if ($filled) {
                                                                break;
                                                            }
                                                        }
                                                    }
                                                    $count = $myTotal = 0;
                                                    
                                                    foreach ($allPositions as $rosterSpot) {
                                                        $row = $myRoster[$count];
                                                        if ($row && isset($row['position'])) {
                                                            $pts = round($row['proj_points'] / 17, 1);
                                                            if ($rosterSpot != 'BN') {
                                                                $myTotal += $pts;
                                                            }
                                                        ?>
                                                            <tr class="color-<?php echo $row['position']; ?>">
                                                                <td><?php echo $rosterSpot; ?></td>
                                                                <td><?php echo $row['player']; ?></td>
                                                                <td><?php echo $row['bye']; ?></td>
                                                                <td><?php echo $pts; ?></td>
                                                            </tr>
                                                        <?php
                                                        } elseif ($rosterSpot != 'BN') { ?>
                                                            <tr>
                                                                <td><?php echo $rosterSpot; ?></td>
                                                                <td></td>
                                                                <td></td>
                                                                <td></td>
                                                            </tr>
                                                        <?php
                                                        }
                                                        $count++;
                                                    } ?>
                                                    <tr>
                                                        <td></td>
                                                        <td><strong>Total</strong></td>
                                                        <td></td>
                                                        <td><strong><?php echo $myTotal; ?></strong></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <table class="table table-responsive" id="datatable-teamByPosition">
                                                <thead>
                                                    <th>Pos</th>
                                                    <th>Player</th>
                                                    <th>Bye</th>
                                                    <th>PPG</th>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $myRoster = [];
                                                    $wrt = ['RB','WR','TE'];
                                                    $qwrt = ['QB','RB','WR','TE'];
                                                    foreach ($allPositions as $pos) {
                                                        $myRoster[] = [$pos => null];
                                                    }
                                                    $opp = (int)$schedule[$week]['id'];
                                                    $result = mysqli_query($conn,
                                                        "SELECT * FROM preseason_rankings
                                                        JOIN draft_selections ON preseason_rankings.id = draft_selections.ranking_id
                                                        WHERE manager_id = $opp AND bye != $week ORDER BY pick_number ASC"
                                                    );
                                                    while ($row = mysqli_fetch_array($result)) {
                                                        foreach ($myRoster as $key => &$rosterPos) {
                                                            foreach ($rosterPos as $k => &$pos) {
                                                                $filled = false;
                                                                if ($pos == null && $k == $row['position']) {
                                                                    $myRoster[$key] = $row;
                                                                    $filled = true;
                                                                    break;
                                                                } elseif ($pos == null && $k == 'W/R/T' && in_array($row['position'], $wrt)) {
                                                                    $myRoster[$key] = $row;
                                                                    $filled = true;
                                                                    break;
                                                                } elseif ($pos == null && $k == 'Q/W/R/T' && in_array($row['position'], $qwrt)) {
                                                                    $myRoster[$key] = $row;
                                                                    $filled = true;
                                                                    break;
                                                                } elseif ($pos == null && $k == 'BN') {
                                                                    $myRoster[$key] = $row;
                                                                    $filled = true;
                                                                    break;
                                                                }
                                                            }
                                                            if ($filled) {
                                                                break;
                                                            }
                                                        }
                                                    }
                                                    $count = $oppTotal = 0;
                                                    foreach ($allPositions as $rosterSpot) {
                                                        $row = $myRoster[$count];
                                                        if ($row && isset($row['position'])) {
                                                            $pts = round($row['proj_points'] / 17, 1);
                                                            if ($rosterSpot != 'BN') {
                                                                $oppTotal += $pts;
                                                            }
                                                        ?>
                                                            <tr class="color-<?php echo $row['position']; ?>">
                                                                <td><?php echo $rosterSpot; ?></td>
                                                                <td><?php echo $row['player']; ?></td>
                                                                <td><?php echo $row['bye']; ?></td>
                                                                <td><?php echo $pts; ?></td>
                                                            </tr>
                                                        <?php
                                                        } elseif ($rosterSpot != 'BN') { ?>
                                                            <tr>
                                                                <td><?php echo $rosterSpot; ?></td>
                                                                <td></td>
                                                                <td></td>
                                                                <td></td>
                                                            </tr>
                                                        <?php
                                                        }
                                                        $count++;
                                                    }?>
                                                    <tr>
                                                        <td></td>
                                                        <td><strong>Total</strong></td>
                                                        <td></td>
                                                        <td><strong><?php echo $oppTotal; ?></strong></td>
                                                    </tr>
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
    </div>

    <script type="text/javascript">
    $(document).ready(function() {

        let baseUrl = "<?php echo $BASE_URL; ?>";
        $('#manager-select').change(function() {
            window.location = baseUrl+'draft/schedule.php?id='+$('#manager-select').val();
        });

        $.ajax({
            url : '../dataLookup.php',
            method: 'POST',
            dataType: 'text',
            data: {
                dataType: "schedule",
                manager: "<?php echo $manager; ?>"
            },
            cache: false,
            success: function(response) {
                let data = JSON.parse(response);
                console.log(data);

                data.forEach(function (item) {

                    console.log(item);
                    let outcome = item.oppName+' wins '+item.opp+' - '+item.mine;
                    if (item.mine > item.opp) {
                        outcome = item.manName+' wins '+item.mine+' - '+item.opp;
                    }

                    $("#outcome"+item.week).html(outcome);
                });
            }
        });

    });
    </script>