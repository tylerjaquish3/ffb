<?php

$pageName = "User Activity";
include 'header.php';
include 'sidebar.html';

?>

<div class="app-content content container-fluid">
    <div class="content-wrapper">
        <div class="content-header row"></div>

        <div class="content-body">

            <div class="row">
                <div class="col-xs-12 col-lg-4 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Activity</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr;">
                            <table class="table table-responsive" id="datatable-activity">
                                <thead>
                                    <th>User</th>
                                    <th>Page</th>
                                    <th>Timestamp</th>
                                </thead>
                                <tbody>
                                    <?php
                                    $result = mysqli_query($conn, "SELECT * FROM user_activity left join managers on managers.id = user_activity.manager_id");
                                    while ($row = mysqli_fetch_array($result)) { ?>
                                        <tr>
                                            <td><?php echo $row['name']; ?></td>
                                            <td><?php echo $row['page']; ?></td>
                                            <td><?php echo $row['created_at']; ?></td>
                                        </tr>

                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-lg-4 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Top Users</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr;">
                            <table class="table table-responsive" id="datatable-users">
                                <thead>
                                    <th>User</th>
                                    <th>Visits</th>
                                </thead>
                                <tbody>
                                    <?php
                                    $result = mysqli_query($conn, "SELECT managers.name, count(ua.id) as visits FROM user_activity ua
                                        join managers on managers.id = ua.manager_id
                                        group by name");
                                    while ($row = mysqli_fetch_array($result)) { ?>
                                        <tr>
                                            <td><?php echo $row['name']; ?></td>
                                            <td><?php echo $row['visits']; ?></td>
                                        </tr>

                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-lg-4 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Top Pages</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr;">
                            <table class="table table-responsive" id="datatable-pages">
                                <thead>
                                    <th>Page</th>
                                    <th>Visits</th>
                                </thead>
                                <tbody>
                                    <?php
                                    $result = mysqli_query($conn, "SELECT page, count(ua.id) as visits FROM user_activity ua 
                                        join managers on managers.id = ua.manager_id
                                        group by page");
                                    while ($row = mysqli_fetch_array($result)) { ?>
                                        <tr>
                                            <td><?php echo $row['page']; ?></td>
                                            <td><?php echo $row['visits']; ?></td>
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
    $('#datatable-activity').DataTable({
        "order": [
            [2, "desc"]
        ]
    });
    $('#datatable-users').DataTable({
        "searching": false,
        "paging": false,
        "info": false,
        "order": [
            [1, "desc"]
        ]
    });
    $('#datatable-pages').DataTable({
        "searching": false,
        "paging": false,
        "info": false,
        "order": [
            [1, "desc"]
        ]
    });
</script>