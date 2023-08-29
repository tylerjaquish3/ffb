<?php

$pageName = "Postseason";
include 'header.php';
include 'sidebar.html';

?>

<div class="app-content content container-fluid">
    <div class="content-wrapper">
        <div class="content-header row"></div>

        <div class="content-body">
            <div class="row">
                <div class="col-xs-12 col-lg-6 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">Matchups</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <table class="table table-responsive" id="datatable-postseason">
                                <thead>
                                    <th>Year</th>
                                    <th>Round</th>
                                    <th>Manager</th>
                                    <th>Opponent</th>
                                    <th>Score</th>
                                    <th></th>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($postseasonMatchups as $matchup) { ?>
                                        <tr>
                                            <td><?php echo $matchup['year']; ?></td>
                                            <td><?php echo $matchup['round']; ?></td>

                                            <?php if ($matchup['winner'] == 'm1') {
                                                echo '<td><span class="badge badge-primary">' . $matchup['manager1'] . '<span class="seed">' . $matchup['m1seed'] . '</span></span></td>';
                                            } else {
                                                echo '<td><span class="badge badge-secondary">' . $matchup['manager1'] . '<span class="seed">' . $matchup['m1seed'] . '</span></span></td>';
                                            }
                                            if ($matchup['winner'] == 'm2') {
                                                echo '<td><span class="badge badge-primary">' . $matchup['manager2'] . '<span class="seed">' . $matchup['m2seed'] . '</span></span></td>';
                                            } else {
                                                echo '<td><span class="badge badge-secondary">' . $matchup['manager2'] . '<span class="seed">' . $matchup['m2seed'] . '</span></span></td>';
                                            } ?>
                                            <td><?php echo $matchup['score']; ?></td>
                                            <td><?php echo $matchup['sort']; ?></td>
                                        </tr>

                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-lg-6 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">Points</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <table class="table" id="datatable-misc25">
                                <thead>
                                    <th>Manager</th>
                                    <th>Points</th>
                                    <th># Matchups</th>
                                    <th>Average</th>
                                </thead>
                                <tbody>
                                    <?php
                                    $result = $conn->query(
                                        "SELECT name, ptsTop, ptsBottom, gamest, gamesb
                                        FROM managers
                                        LEFT JOIN (
                                        SELECT COUNT(id) as gamest, SUM(manager1_score) AS ptsTop, manager1_id FROM playoff_matchups rsm
                                        GROUP BY manager1_id
                                        ) w ON w.manager1_id = managers.id

                                        LEFT JOIN (
                                        SELECT COUNT(id) as gamesb, SUM(manager2_score) AS ptsBottom, manager2_id FROM playoff_matchups rsm
                                        GROUP BY manager2_id
                                        ) l ON l.manager2_id = managers.id"
                                    );
                                    while ($row = mysqli_fetch_array($result)) {

                                        $points = $row['ptsTop'] + $row['ptsBottom'];
                                        $games = $row['gamest'] + $row['gamesb'];
                                        $average = $points / $games;
                                        ?>
                                        <tr>
                                            <td><?php echo $row['name']; ?></td>
                                            <td><?php echo number_format($points, 2, '.', ','); ?></td>
                                            <td><?php echo $games; ?></td>
                                            <td><?php echo number_format($average, 2, '.', ','); ?></td>
                                        </tr>

                                    <?php } ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan=4>Points scored in postseason matchups</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12 col-lg-6 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">Records</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <table class="table table-responsive" id="datatable-records">
                                <thead>
                                    <th>Manager</th>
                                    <th>Quarter Wins</th>
                                    <th>Quarter Losses</th>
                                    <th>Semi Wins</th>
                                    <th>Semi Losses</th>
                                    <th>Final Wins</th>
                                    <th>Final Losses</th>
                                    <th>Overall Win %</th>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($postseasonRecord as $manager) { ?>
                                        <tr>
                                            <td><?php echo $manager['name']; ?></td>
                                            <td><?php echo $manager['quarter_wins']; ?></td>
                                            <td><?php echo $manager['quarter_losses']; ?></td>
                                            <td><?php echo $manager['semi_wins']; ?></td>
                                            <td><?php echo $manager['semi_losses']; ?></td>
                                            <td><?php echo $manager['final_wins']; ?></td>
                                            <td><?php echo $manager['final_losses']; ?></td>
                                            <td><?php echo round($manager['win_pct'], 1); ?></td>
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

<?php include 'footer.html'; ?>

<script type="text/javascript">
    $(document).ready(function() {

        $('#datatable-postseason').DataTable({
            "columnDefs": [{
                "targets": [5],
                "visible": false,
                "searchable": false
            }],
            "order": [
                [0, "desc"],
                [5, "desc"]
            ]
        });

        $('#datatable-misc25').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [
                [3, "desc"]
            ]
        });

        $('#datatable-records').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [
                [7, "desc"]
            ]
        });

    });
</script>