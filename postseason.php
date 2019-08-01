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
                <div class="col-xs-12 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">Postseason Matchups</h4>
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
                [0, "asc"],
                [5, "asc"]
            ]
        });

    });
</script>