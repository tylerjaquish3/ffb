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
                <div class="col-xs-12 table-padding">
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
            </div>
        </div>
    </div>
</div>

<?php include 'footer.html'; ?>

<script type="text/javascript">
    $(document).ready(function() {

        $('#datatable-draft').DataTable({
            "order": [
                [0, "asc"],
                [3, "asc"]
            ]
        });

    });
</script>