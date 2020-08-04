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

    });
</script>

<style>
    .app-content.container-fluid {
        background: white;
        direction: ltr;
        font-size: 11px;
    }
    #sortable { list-style-type: none; margin: 0; padding: 0; width: 60%; }
    #sortable li { margin: 0 5px 5px 5px; padding: 5px; font-size: 1.2em; height: 1.5em; }
    html>body #sortable li { height: 1.5em; line-height: 1.2em; }
    .ui-state-highlight { height: 1.5em; line-height: 1.2em; }

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