<?php

$pageName = "Pre-Draft Rankings";
include 'header.php';

?>
<div class="app-content container-fluid">
    <div class="content-wrapper">
        <div class="content-header row"></div>

        <div class="content-body">
            <div class="row">
                <div class="col-xs-12 col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="position-relative">
                                <ul id="sortable">
                                    <?php
                                    $result = mysqli_query(
                                        $conn,
                                        "SELECT * FROM preseason_rankings ORDER BY my_rank ASC"
                                    );
                                    while ($row = mysqli_fetch_array($result)) {
                                        ?>
                                        <li class="ui-state-default" id="item-<?php echo $row['id']; ?>">
                                            <i class="icon-menu2"></i>&nbsp;&nbsp;&nbsp;<span class="color-<?php echo $row['position']; ?>"><?php echo $row['player']; ?></span>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="position-relative">
                                <ul class="tiers-list">
                                    <?php
                                    $result = mysqli_query($conn, "SELECT * FROM preseason_rankings WHERE position = 'WR' ORDER BY my_rank ASC");
                                    while ($row = mysqli_fetch_array($result)) {
                                        $tier = $row['tier'];
                                        ?>
                                        <li class="ui-state-default" id="item-<?php echo $row['id']; ?>">
                                            <select class="tier-selector" data-tier-id="<?php echo $row['id']; ?>">
                                                <option>Select Tier</option>
                                                <option value="1" <?php if ($tier == 1) { echo 'selected'; }?>>Tier 1</option>
                                                <option value="2" <?php if ($tier == 2) { echo 'selected'; }?>>Tier 2</option>
                                                <option value="3" <?php if ($tier == 3) { echo 'selected'; }?>>Tier 3</option>
                                                <option value="4" <?php if ($tier == 4) { echo 'selected'; }?>>Tier 4</option>
                                                <option value="5" <?php if ($tier == 5) { echo 'selected'; }?>>Tier 5</option>
                                                <option value="6" <?php if ($tier == 6) { echo 'selected'; }?>>Tier 6</option>
                                            </select>
                                            <span class="color-<?php echo $row['position']; ?>"><?php echo $row['player']; ?></span>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="position-relative">
                                <ul class="tiers-list">
                                    <?php
                                    $result = mysqli_query($conn, "SELECT * FROM preseason_rankings WHERE position = 'RB' ORDER BY my_rank ASC");
                                    while ($row = mysqli_fetch_array($result)) {
                                        $tier = $row['tier'];
                                        ?>
                                        <li class="ui-state-default" id="item-<?php echo $row['id']; ?>">
                                            <select class="tier-selector" data-tier-id="<?php echo $row['id']; ?>">
                                                <option>Select Tier</option>
                                                <option value="1" <?php if ($tier == 1) { echo 'selected'; }?>>Tier 1</option>
                                                <option value="2" <?php if ($tier == 2) { echo 'selected'; }?>>Tier 2</option>
                                                <option value="3" <?php if ($tier == 3) { echo 'selected'; }?>>Tier 3</option>
                                                <option value="4" <?php if ($tier == 4) { echo 'selected'; }?>>Tier 4</option>
                                                <option value="5" <?php if ($tier == 5) { echo 'selected'; }?>>Tier 5</option>
                                                <option value="6" <?php if ($tier == 6) { echo 'selected'; }?>>Tier 6</option>
                                            </select>
                                            <span class="color-<?php echo $row['position']; ?>"><?php echo $row['player']; ?></span>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="position-relative">
                                <ul class="tiers-list">
                                    <?php
                                    $result = mysqli_query($conn, "SELECT * FROM preseason_rankings WHERE position = 'QB' ORDER BY my_rank ASC");
                                    while ($row = mysqli_fetch_array($result)) {
                                        $tier = $row['tier'];
                                        ?>
                                        <li class="ui-state-default" id="item-<?php echo $row['id']; ?>">
                                            <select class="tier-selector" data-tier-id="<?php echo $row['id']; ?>">
                                                <option>Select Tier</option>
                                                <option value="1" <?php if ($tier == 1) { echo 'selected'; }?>>Tier 1</option>
                                                <option value="2" <?php if ($tier == 2) { echo 'selected'; }?>>Tier 2</option>
                                                <option value="3" <?php if ($tier == 3) { echo 'selected'; }?>>Tier 3</option>
                                                <option value="4" <?php if ($tier == 4) { echo 'selected'; }?>>Tier 4</option>
                                                <option value="5" <?php if ($tier == 5) { echo 'selected'; }?>>Tier 5</option>
                                                <option value="6" <?php if ($tier == 6) { echo 'selected'; }?>>Tier 6</option>
                                            </select>
                                            <span class="color-<?php echo $row['position']; ?>"><?php echo $row['player']; ?></span>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php include 'footer.html'; ?>

<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

<script type="text/javascript">
    $(document).ready(function() {
        $("#sortable").sortable({
            placeholder: "ui-state-highlight",
            update: function (event, ui) {
                var data = $(this).sortable('serialize');

                $.ajax({
                    data: data,
                    type: 'POST',
                    url: 'updateRankings.php'
                });
            }
        });
        $("#sortable").disableSelection();

        $('.tier-selector').change(function () {
            console.log($(this).val());
            console.log($(this).data('tier-id'));
            $.ajax({
                data: {
                    tier: $(this).val(),
                    playerId: $(this).data('tier-id')
                },
                type: 'POST',
                url: 'updateRankings.php'
            });
        });
    });
</script>

<style>
    .app-content.container-fluid {
        background: white;
        direction: ltr;
        font-size: 11px;
    }
    ul { list-style-type: none; margin: 0; padding: 0; width: 60%; }
    #sortable li { margin: 0 5px 5px 5px; padding: 5px; font-size: 1.2em; height: 1.5em; }
    html>body #sortable li { height: 1.5em; line-height: 1.2em; }
    .ui-state-highlight { height: 1.5em; line-height: 1.2em; }

    .tiers-list li {
        line-height: 2.2;
        font-size: 16px;
    }

    .color-QB {
        background-color: aquamarine;
    }

    .color-RB {
        background-color:burlywood;
    }

    .color-WR {
        background-color: #fa9cff;
    }

    .color-TE {
        background-color: #69cfff;
    }

    .color-DEF {
        background-color: #dffcde;
    }

    .color-K {
        background-color: #f7cbcc;
    }

    .color-IDP {
        background-color: #fcf8b3;
    }
</style>