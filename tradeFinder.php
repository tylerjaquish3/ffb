<?php

$pageName = "Trade Finder";
include 'header.php';
include 'sidebar.html';

?>

<div class="app-content content container-fluid">
    <div class="content-wrapper">
        <div class="content-header row"></div>

        <div class="content-body">
            <div class="row">

                <!-- <div class="col-xs-12 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4>Trade Finder</h4>
                            <span id="count"></span>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <table class="stripe row-border order-column" id="datatable-teamPosPts">
                                <thead>
                                    <th>Owner</th>
                                    <th>Pos</th>
                                    <th>Proj</th>
                                    <th>Points</th>
                                </thead>
                                <tbody>
                                    
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div> -->

                <div class="col-xs-12 col-md-6 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4>Players to Target</h4>
                            <span id="count"></span>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <table class="stripe row-border order-column" id="datatable-results">
                                <thead>
                                    <th>Player</th>
                                    <th>Projected</th>
                                    <th>Points</th>
                                    <th>Diff</th>
                                    <th>Owner</th>
                                </thead>
                                <tbody>
                                    <?php
                                    // Look up how many weeks of data there are
                                    $weeks = 0;
                                    $result = mysqli_query($conn, "SELECT distinct week FROM rosters 
                                        WHERE YEAR = $season");
                                    while ($row = mysqli_fetch_array($result)) {
                                        $weeks++;
                                    }

                                    // Look up all the actual points
                                    $playerPts = [];
                                    $result = mysqli_query($conn, "SELECT player, SUM(points) as pts FROM rosters 
                                        WHERE YEAR = $season
                                        GROUP BY player");
                                    while ($row = mysqli_fetch_array($result)) {
                                        $name = substr($row['player'], 0, strrpos($row['player'], ' '));
                                        $playerPts[$name] = round($row['pts'], 1);
                                    }

                                    $result = mysqli_query($conn, "SELECT player, proj_points, name FROM preseason_rankings pr JOIN draft_selections ds ON ds.ranking_id = pr.id
                                        JOIN managers m ON m.id = ds.manager_id
                                        WHERE is_mine = 0");
                                    while ($row = mysqli_fetch_array($result)) {

                                        $pts = 0;
                                        if (isset($playerPts[$row['player']])) {
                                            $pts = $playerPts[$row['player']];
                                        }
                                        $proj = round(($row['proj_points']/14) * $weeks, 1);
                                        if ($proj - $pts > 10) {
                                            echo '<tr><td>'.$row['player'].'</td><td>'.$proj.'</td><td>'.$pts.'</td><td>'.($proj-$pts).'</td><td>'.$row['name'].'</td></tr>';
                                        }
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-xs-12 col-md-6 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4>My Trade Candidates</h4>
                            <span id="count"></span>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <table class="stripe row-border order-column" id="datatable-mine">
                                <thead>
                                    <th>Player</th>
                                    <th>Projected</th>
                                    <th>Points</th>
                                    <th>Diff</th>
                                    <th>Bye</th>
                                </thead>
                                <tbody>
                                    <?php
                                    // Look up how many weeks of data there are
                                    $weeks = 0;
                                    $result = mysqli_query($conn, "SELECT distinct week FROM rosters 
                                        WHERE YEAR = $season");
                                    while ($row = mysqli_fetch_array($result)) {
                                        $weeks++;
                                    }

                                    // Look up all the actual points
                                    $playerPts = [];
                                    $result = mysqli_query($conn, "SELECT player, SUM(points) as pts FROM rosters 
                                        WHERE YEAR = $season
                                        GROUP BY player");
                                    while ($row = mysqli_fetch_array($result)) {
                                        $name = substr($row['player'], 0, strrpos($row['player'], ' '));
                                        $playerPts[$name] = round($row['pts'], 1);
                                    }

                                    $result = mysqli_query($conn, "SELECT player, proj_points, name FROM preseason_rankings pr 
                                        JOIN draft_selections ds ON ds.ranking_id = pr.id
                                        JOIN schedule s ON s.manager1_id = 1 AND pr.bye = s.week
                                        JOIN managers m ON m.id = s.manager2_id
                                        WHERE is_mine = 1");
                                    while ($row = mysqli_fetch_array($result)) {

                                        $pts = 0;
                                        if (isset($playerPts[$row['player']])) {
                                            $pts = $playerPts[$row['player']];
                                        }
                                        $proj = round(($row['proj_points']/14) * $weeks, 1);

                                        echo '<tr><td>'.$row['player'].'</td><td>'.$proj.'</td><td>'.$pts.'</td><td>'.($proj-$pts).'</td><td>'.$row['name'].'</td></tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-xs-12 col-md-6 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4>Points by Position</h4>
                            <span id="count"></span>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <table class="stripe row-border order-column" id="datatable-teamPosPts">
                                <thead>
                                    <th>Owner</th>
                                    <th>QB</th>
                                    <th>RB</th>
                                    <th>WR</th>
                                    <th>TE</th>
                                    <th>K</th>
                                    <th>DEF</th>
                                </thead>
                                <tbody>
                                    <?php
                                    // Look up how many weeks of data there are
                                    $weeks = 0;
                                    $result = mysqli_query($conn, "SELECT distinct week FROM rosters 
                                        WHERE YEAR = $season");
                                    while ($row = mysqli_fetch_array($result)) {
                                        $weeks++;
                                    }

                                    // Look up all the actual points
                                    $posPts = [];
                                    $result = mysqli_query($conn, "SELECT position, manager, SUM(points) as pts 
                                        FROM rosters 
                                        WHERE YEAR = $season
                                        GROUP BY manager, position");
                                    while ($row = mysqli_fetch_array($result)) {
                                        $posPts[$row['manager']][$row['position']] = round($row['pts'], 1);
                                    }

                                    foreach ($posPts as $man => $pts) { 
                                        echo '<tr><td>'.$man.'</td><td>'.$pts['QB'].'</td><td>'.$pts['RB'].'</td><td>'.$pts['WR'].'</td><td>'.$pts['TE'].'</td><td>'.$pts['K'].'</td><td>'.$pts['DEF'].'</td></tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php include 'footer.html'; ?>

<script type="text/javascript">

    $('#datatable-results').DataTable({
        "searching": false,
        "paging": false,
        "info": false,
        "order": [
            [3, "desc"],
        ]
    });

    $('#datatable-mine').DataTable({
        "searching": false,
        "paging": false,
        "info": false,
        "order": [
            [3, "asc"],
        ]
    });

    $('#datatable-teamPosPts').DataTable({
        "searching": false,
        "paging": false,
        "info": false,
        "order": [
            [1, "asc"],
        ]
    });

</script>

<style>
   
</style>