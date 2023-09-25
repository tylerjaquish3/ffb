<?php

$pageName = "Awards";
include 'header.php';
include 'sidebar.html';

$type = 'all';
if (isset($_GET['id'])) {
    $type = $_GET['id'];
} 
?>

<div class="app-content content container-fluid">
    <div class="content-wrapper">
        <div class="content-header row"></div>

        <div class="content-body">
            <div class="row" style="direction: ltr;">
                <div class="col-sm-12 d-md-none">
                    <h5 style="margin-top: 5px; color: #fff;">Filter</h5>
                </div>
                <div class="col-sm-12 col-md-4">
                    <select id="type-select" class="form-control">
                        <option value="all" <?php if ($type == 'all') { echo 'selected'; } ?>>All</option>
                        <option value="regular" <?php if ($type == 'regular') { echo 'selected'; } ?>>Regular Season</option>
                        <option value="post" <?php if ($type == 'post') { echo 'selected'; } ?>>Postseason</option>
                        <option value="current" <?php if ($type == 'current') { echo 'selected'; } ?>>Current Season</option>
                    </select>
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

                                        if ($type != 'all') {
                                            $query .= " AND type = '$type'";
                                        }
                                        $result = query($query);
                                        while ($row = fetch_array($result)) {
                                            $value = $row['value'];
                                            if (isfloat($row['value']) && isDecimal($row['value'])) {
                                                $value = number_format($row['value'], 2, '.', ',');
                                            } ?>
                                            <div class="col-sm-6 award good">
                                                <strong><?php echo $row['fact']; ?> </strong><br />
                                                <?php echo $value; ?> <br />
                                                <?php echo $row['note']; ?>
                                            </div>
                                        <?php }

                                    ?>
                                </div>
                                <div class="col-lg-6 col-sm-12">
                                    <?php
                                        $query = "SELECT * FROM manager_fun_facts mff
                                            JOIN fun_facts ff ON mff.fun_fact_id = ff.id
                                            JOIN managers ON managers.id = mff.manager_id
                                            WHERE is_positive = 0 AND manager_id = $x";
                                            
                                        if ($type != 'all') {
                                            $query .= " AND type = '$type'";
                                        }
                                        $result = query($query);
                                        while ($row = fetch_array($result)) { 
                                            $value = $row['value'];
                                            if (isfloat($row['value']) && isDecimal($row['value'])) {
                                                $value = number_format($row['value'], 2, '.', ',');
                                            } ?>
                                            <div class="col-sm-6 award bad">
                                                <strong><?php echo $row['fact']; ?> </strong><br />
                                                <?php echo $value; ?> <br />
                                                <?php echo $row['note']; ?>
                                            </div>
                                    <?php } ?>
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
        window.location = baseUrl+'awards.php?id='+$('#type-select').val();
    });
</script>