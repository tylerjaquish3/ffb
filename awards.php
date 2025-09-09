<?php

$pageName = "Awards";
include 'header.php';
include 'sidebar.html';

$type = 'all';
if (isset($_GET['id'])) {
    $type = $_GET['id'];
} 
if (isset($_GET['new_leader'])) {
    $new_leader = $_GET['new_leader'];
} else {
    $new_leader = 0;
}
?>

<div class="app-content content">
    <div class="content-wrapper">

        <div class="content-body">
            <div class="row" style="direction: ltr;">
                <div class="col-sm-12 d-md-none">
                    <h5 style="margin-top: 5px; color: #fff;">Filter</h5>
                </div>
                <div class="col-sm-12 col-md-6" style="display: flex;">
                    &nbsp;&nbsp;
                    <h4>Season Type</h4>
                    &nbsp;&nbsp;
                    <select id="type-select">
                        <option value="all" <?php if ($type == 'all') { echo 'selected'; } ?>>All</option>
                        <option value="regular" <?php if ($type == 'regular') { echo 'selected'; } ?>>Regular Season</option>
                        <option value="post" <?php if ($type == 'post') { echo 'selected'; } ?>>Postseason</option>
                        <option value="current" <?php if ($type == 'current') { echo 'selected'; } ?>>Current Season</option>
                    </select>
                    &nbsp;&nbsp;
                    <h4>New Leader</h4>
                    &nbsp;&nbsp;
                    <input type="checkbox" id="new_leader" <?php echo $new_leader == 1 ? 'checked' : '' ?>>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Fun Facts</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr;">

                            <?php
                            for ($x = 1; $x < 11; $x++) {
                                $result = query("SELECT * FROM managers WHERE id = $x");
                                while ($row = fetch_array($result)) {
                                    echo '<div class="row">
                                            <div class="col-sm-12 text-center">
                                                <h4><a href="profile.php?id='.$row['name'].'">'.$row['name'].'</a></h4>
                                            </div>
                                        </div>
                                        <div class="row">';
                                }
                            ?>
                                <div class="col-lg-6 col-sm-12">
                                    <?php
                                        $query = "SELECT * FROM manager_fun_facts mff
                                            JOIN fun_facts ff ON mff.fun_fact_id = ff.id
                                            JOIN managers ON managers.id = mff.manager_id
                                            WHERE is_positive = 1 AND manager_id = $x";

                                        if ($new_leader) {
                                            $query .= " AND new_leader = 1";
                                        }
                                        if ($type != 'all') {
                                            $query .= " AND type = '$type'";
                                        }
                                        $result = query($query);
                                        while ($row = fetch_array($result)) {
                                            $value = $row['value'];
                                            if (isfloat($row['value']) && isDecimal($row['value'])) {
                                                $value = number_format($row['value'], 2, '.', ',');
                                            } 
                                            echo '<div class="col-sm-4"><div class="award good">';
                                            if ($row['new_leader']) {
                                                echo '<i class="icon-warning" style="font-size: 15px"></i>';
                                            }
                                            echo '<strong>'.$row['fact'].'</strong><br />'.$value.'<br />'.$row['note'];
                                            echo '</div></div>';
                                        }
                                    ?>
                                </div>
                                <div class="col-lg-6 col-sm-12">
                                    <?php
                                        $query = "SELECT * FROM manager_fun_facts mff
                                            JOIN fun_facts ff ON mff.fun_fact_id = ff.id
                                            JOIN managers ON managers.id = mff.manager_id
                                            WHERE is_positive = 0 AND manager_id = $x";
                                            
                                        if ($new_leader) {
                                            $query .= " AND new_leader = 1";
                                        }
                                        if ($type != 'all') {
                                            $query .= " AND type = '$type'";
                                        }
                                        $result = query($query);
                                        while ($row = fetch_array($result)) { 
                                            $value = $row['value'];
                                            if (isfloat($row['value']) && isDecimal($row['value'])) {
                                                $value = number_format($row['value'], 2, '.', ',');
                                            } 
                                            echo '<div class="col-sm-4"><div class="award bad">';
                                            if ($row['new_leader']) {
                                                echo '<i class="icon-warning" style="font-size: 15px"></i>';
                                            }
                                            echo '<strong>'.$row['fact'].'</strong><br />'.$value.'<br />'.$row['note'];
                                            echo '</div></div>';
                                     } ?>
                                </div>
                            </div>
                            <hr />
                            <?php } ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<script type="text/javascript">

    let baseUrl = "<?php echo $BASE_URL; ?>";

    $('#type-select').change(function() {
        newLeader = $('#new_leader').is(':checked') ? 1 : 0;
        window.location = baseUrl+'awards.php?id='+$('#type-select').val()+'&new_leader='+newLeader;
    });

    $('#new_leader').change(function() {
        newLeader = $('#new_leader').is(':checked') ? 1 : 0;
        window.location = baseUrl+'awards.php?id='+$('#type-select').val()+'&new_leader='+newLeader;
    });

</script>