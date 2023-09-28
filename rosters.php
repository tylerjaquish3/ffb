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
$result = query("SELECT * FROM regular_season_matchups rsm
JOIN managers on managers.id = rsm.manager2_id
WHERE year = $year and week_number = $week and manager1_id = $managerId");
while ($row = fetch_array($result)) {
    $versus = $row['name'];
}

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
                                        Manager
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
                <div class="col-sm-12 col-lg-12" id="versus">
                    <div class="card">
                        <div class="card-header">
                            <h4>Roster</h4>
                        </div>
                        <div class="card-body" style="direction: ltr;">

                            <div class="row">
                                <div class="col-sm-12 col-md-6">
                                    <h2 class="text-center"><?php echo $managerName; ?></h2>
                                    <table class="table table-responsive table-striped nowrap">
                                        <thead>
                                            <th>Player</th>
                                            <th>Position</th>
                                            <th>Projected</th>
                                            <th>Points</th>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $result = query( "SELECT * FROM rosters WHERE year = $year and week = $week and manager = '$managerName'");
                                            while ($row = fetch_array($result)) {
                                                echo '<tr>';
                                                echo '<td>'.$row['player'].'</td>';
                                                echo '<td>'.$row['roster_spot'].'</td>';
                                                echo '<td>'.$row['projected'].'</td>';
                                                echo '<td>'.$row['points'].'</td>';
                                            } ?>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="col-sm-12 col-md-6">
                                <h2 class="text-center"><?php echo $versus; ?></h2>
                                    <table class="table table-responsive table-striped nowrap">
                                        <thead>
                                            <th>Player</th>
                                            <th>Position</th>
                                            <th>Projected</th>
                                            <th>Points</th>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $result = query( "SELECT * FROM rosters WHERE year = $year and week = $week and manager = '$versus'");
                                            while ($row = fetch_array($result)) {
                                                echo '<tr>';
                                                echo '<td>'.$row['player'].'</td>';
                                                echo '<td>'.$row['roster_spot'].'</td>';
                                                echo '<td>'.$row['projected'].'</td>';
                                                echo '<td>'.$row['points'].'</td>';
                                            } ?>
                                        </tbody>
                                    </table>
                                </div>
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

        $('#oppRecordSelector').change(function() {
            if ($('#oppRecordSelector').val() == 'reg') {
                $('#datatable-regSeason').show();
                $('#datatable-postseason').hide();
            } else {
                $('#datatable-regSeason').hide();
                $('#datatable-postseason').show();
            }
        });

        $('#datatable-regSeason').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [
                [1, "desc"]
            ]
        });


    });
</script>