<?php

$pageName = "Draft";
include 'header.php';
include 'sidebar.html';

?>

<div class="app-content content container-fluid">
    <div class="content-wrapper">
        <div class="content-header row"></div>

        <div class="content-body">
            <div class="row">
                <div class="col-xs-12 col-lg-7 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">Draft Results</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <table class="table table-responsive" id="datatable-draft">
                                <thead>
                                    <th>Year</th>
                                    <th>Round</th>
                                    <th>Round Pick</th>
                                    <th>Overall Pick</th>
                                    <th>Player</th>
                                    <th>Manager</th>
                                    <th>Position</th>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($draftResults as $draft) { ?>
                                        <tr>
                                            <td><?php echo $draft['year']; ?></td>
                                            <td><?php echo $draft['round']; ?></td>
                                            <td><?php echo $draft['round_pick']; ?></td>
                                            <td><?php echo $draft['overall_pick']; ?></td>
                                            <td><?php echo $draft['player']; ?></td>
                                            <td><?php echo $draft['name']; ?></td>
                                            <td><?php echo $draft['position']; ?></td>
                                        </tr>

                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-lg-5 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">Draft Positions</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <!-- Draft position -->
                            <table class="table" id="datatable-misc11">
                                <thead>
                                    <th>Manager</th>
                                    <th>#1 Picks</th>
                                    <th>#10 Picks</th>
                                    <th>Avg. Position</th>
                                </thead>
                                <tbody>
                                    <?php
                                    $result = $conn->query("SELECT name, IFNULL(pick1, 0) as pick1, IFNULL(pick10, 0) as pick10, adp
                                        FROM managers
                                        LEFT JOIN (
                                            SELECT COUNT(id) as pick1, manager_id FROM draft
                                            WHERE overall_pick = 1
                                        GROUP BY manager_id
                                        ) p1 ON p1.manager_id = managers.id

                                        LEFT JOIN (
                                        SELECT COUNT(id) as pick10, manager_id FROM draft
                                        WHERE overall_pick = 10
                                        GROUP BY manager_id
                                        ) p10 ON p10.manager_id = managers.id

                                        LEFT JOIN (
                                        SELECT AVG(overall_pick) as adp, manager_id FROM draft
                                        WHERE round = 1
                                        GROUP BY manager_id
                                        ) average ON average.manager_id = managers.id");
                                    while ($row = $result->fetchArray()) { ?>
                                        <tr>
                                            <td><?php echo $row['name']; ?></td>
                                            <td><?php echo $row['pick1']; ?></td>
                                            <td><?php echo $row['pick10']; ?></td>
                                            <td><?php echo round($row['adp'], 1); ?></td>
                                        </tr>

                                    <?php } ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan=4>Number of times with #1 or #10 pick and average draft position</td>
                                    </tr>
                                </tfoot>
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

        $('#datatable-draft').DataTable({
            "order": [
                [0, "desc"],
                [3, "asc"]
            ]
        });

        $('#datatable-misc11').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [
                [3, "asc"]
            ]
        });

    });
</script>