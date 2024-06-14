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
                <div class="col-sm-12 col-lg-6 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">Matchups</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <table class="table table-responsive table-striped nowrap full-width" id="datatable-postseason">
                                <thead>
                                    <th>Year</th>
                                    <th>Round</th>
                                    <th>Manager</th>
                                    <th>Opponent</th>
                                    <th>Score</th>
                                    <th>Margin</th>
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
                                            <td><?php echo $matchup['margin']; ?></td>
                                            <td><?php echo $matchup['sort']; ?></td>
                                        </tr>

                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12 col-lg-6 table-padding">
                    <div class="card-header" style="float: left">
                        <h4>Postseason</h4>
                    </div>
                    <div style="float: right">
                        <select id="postMiscStats" class="dropdown form-control">
                            <option value="20">Average Finish</option>
                            <option value="21">First Round Byes</option>
                            <option value="22">Appearances</option>
                            <option value="23">Underdog Wins</option>
                            <option value="24">Top Seed Losses</option>
                            <option value="25">Playoff Points</option>
                            <option value="26">Win/Loss Margin</option>
                        </select>
                    </div>
                    <?php include 'postMiscStats.php'; ?>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-sm-12 col-lg-8 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">Records</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <table class="table table-responsive table-striped nowrap" id="datatable-records">
                                <thead>
                                    <th>Manager</th>
                                    <th>Quarter Wins</th>
                                    <th>Quarter Losses</th>
                                    <th>Semi Wins</th>
                                    <th>Semi Losses</th>
                                    <th>Final Wins</th>
                                    <th>Final Losses</th>
                                    <th>Total Wins</th>
                                    <th>Total Losses</th>
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
                                            <td><?php echo $manager['wins']; ?></td>
                                            <td><?php echo $manager['losses']; ?></td>
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

<?php include 'footer.php'; ?>

<script type="text/javascript">
    $(document).ready(function() {

        $('#postMiscStats').change(function() {
            showPostTable($('#postMiscStats').val());
        });

        function showPostTable(tableId) {
            for (i = 20; i < 27; i++) {
                $('#datatable-misc' + i).hide();
            }
            $('#datatable-misc' + tableId).show();
        }

        let postseasonTable = $('#datatable-postseason').DataTable({
            "columnDefs": [{
                "targets": [6],
                "visible": false,
                "searchable": false
            }],
            "search": {
                "caseInsensitive": false
            },
            "order": [
                [0, "desc"],
                [6, "desc"]
            ]
        });

        $('#datatable-misc20').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [
                [1, "asc"]
            ]
        });
        $('#datatable-misc21').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [
                [3, "desc"]
            ]
        });
        $('#datatable-misc22').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [
                [1, "desc"]
            ]
        });
        $('#datatable-misc23').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [
                [4, "desc"]
            ]
        });
        $('#datatable-misc24').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [
                [4, "desc"]
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
        $('#datatable-misc26').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [
                [1, "desc"]
            ]
        });

        $('#datatable-records').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [
                [9, "desc"]
            ]
        });

    });
</script>