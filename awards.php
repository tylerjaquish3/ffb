<?php

$pageName = "Awards";
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
                            <h4 class="card-title">Fun Facts</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr;">

                            <?php
                            for ($x = 1; $x < 11; $x++) {
                                $result = query("SELECT * FROM managers WHERE id = $x");
                                while ($row = fetch_array($result)) {
                                    echo '<div class="row">
                                            <div class="col-xs-12 text-center">
                                                <h4>'.$row['name'].'</h4>
                                            </div>
                                        </div>
                                        <div class="row">';
                                }
                            ?>
                                <div class="col-lg-6 col-xs-12">
                                    <?php
                                        $result = query( 
                                            "SELECT * FROM manager_fun_facts mff
                                            JOIN fun_facts ff ON mff.fun_fact_id = ff.id
                                            JOIN managers ON managers.id = mff.manager_id
                                            WHERE is_positive = 1 and manager_id = $x"
                                        );
                                        while ($row = fetch_array($result)) {
                                            $value = $row['value'];
                                            if (isfloat($row['value']) && isDecimal($row['value'])) {
                                                $value = number_format($row['value'], 2, '.', ',');
                                            } ?>
                                            <div class="col-xs-6 award good">
                                                <strong><?php echo $row['fact']; ?> </strong><br />
                                                <?php echo $value; ?> <br />
                                                <?php echo $row['note']; ?>
                                            </div>
                                        <?php }

                                    ?>
                                </div>
                                <div class="col-lg-6 col-xs-12">
                                    <?php
                                        $result = query(
                                            "SELECT * FROM manager_fun_facts mff
                                            JOIN fun_facts ff ON mff.fun_fact_id = ff.id
                                            JOIN managers ON managers.id = mff.manager_id
                                            WHERE is_positive = 0 and manager_id = $x"
                                        );
                                        while ($row = fetch_array($result)) { 
                                            $value = $row['value'];
                                            if (isfloat($row['value']) && isDecimal($row['value'])) {
                                                $value = number_format($row['value'], 2, '.', ',');
                                            } ?>
                                            <div class="col-xs-6 award bad">
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

<?php include 'footer.html'; ?>

<script type="text/javascript">

</script>