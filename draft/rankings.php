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
                                            <i class="icon-menu2"></i>&nbsp;&nbsp;&nbsp;<span class="color-<?php echo $row['position']; ?>"><?php echo '<a data-toggle="modal" data-target="#player-data" onclick="showPlayerData('.(int)$row['id'].')">'.$row['player'].'('.$row['proj_points'].')'.desigIcon($row['designation'], $row['notes']).'</a>'; ?></span>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <?php 
                $positions = ['TE', 'WR', 'RB', 'QB'];

                foreach ($positions as $pos) {
                ?>
                <div class="col-xs-12 col-md-2">
                    <div class="card">
                        <div class="card-body">
                            <div class="position-relative">
                                <ul class="tiers-list">
                                    <?php
                                    $result = mysqli_query($conn, "SELECT * FROM preseason_rankings WHERE position = '{$pos}' ORDER BY my_rank ASC");
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
                                                <option value="7" <?php if ($tier == 7) { echo 'selected'; }?>>Tier 7</option>
                                                <option value="8" <?php if ($tier == 8) { echo 'selected'; }?>>Tier 8</option>
                                            </select>
                                            <span class="color-<?php echo $row['position']; ?>"><?php echo '<a data-toggle="modal" data-target="#player-data" onclick="showPlayerData('.(int)$row['id'].')">'.$row['player'].'('.$row['proj_points'].')</a>'; ?></span>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
                } ?>

            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="player-data" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" style="direction: ltr">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
                <h4 class="modal-title">Player Data</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-xs-12">
                        <input type="hidden" id="player-id">
                        <div id="player-header"></div>
                        <div id="fetched-data"></div>

                        <textarea id="player-notes" cols=150 rows=6></textarea>
                        <br /><a class="btn btn-secondary mine" id="save-note">Save</a><div id="confirm"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../footer.html'; ?>

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
            // console.log($(this).val());
            // console.log($(this).data('tier-id'));
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

    // Section for player data and note modal *****************************************
    function showPlayerData(id)
    {
        $.ajax({
            type : 'post',
            url : 'modalData.php',
            data :  {
                request: 'player_data',
                id: id
            },
            success : function(data){
                let table = '<table id="player-history"><thead>';
                table += '<th>Year</th><th>Team</th><th>GP</th>';
                table += '<th>Pass Att</th><th>Comp</th><th>Pass Yds</th><th>Pass TDs</th><th>Int</th>';
                table += '<th>Rush Att</th><th>Rush Yds</th><th>Rush TDs</th>';
                table += '<th>Tar</th><th>Rec</th><th>Rec Yds</th><th>Rec TDs</th><th>Fumbles</th>';
                table += '<th>Pts</th><th>Pts/Gm</th>';
                table += '</thead><tbody>';

                data = JSON.parse(data);
                // Loop through data to add rows
                data.forEach(function (item, index) {

                    if (index == 0) {
                        let header = '<h4>'+item.player+' ('+item.position + ' | ' + item.team+')</h4>';
                        $('#player-header').html(header);
                        $('#player-notes').val(item.notes);
                        $('#player-id').val(item.id);
                    } else {

                        let points = (item.pass_yards*.04)+(item.pass_touchdowns*4)+(item.rush_yards*.1)+(item.rush_touchdowns*6);
                        points += (item.rec_yards*.1)+(item.rec_touchdowns*6)+(item.rec_receptions*.5);
                        points -= (item.pass_interceptions*2)+(item.fumbles*3);
                        let ppg = (points/item.games_played).toFixed(1);
                        table += '<tr>'+
                            '<td>'+item.year+'</td>'+
                            '<td>'+item.team_abbr+'</td>'+
                            '<td>'+item.games_played+'</td>'+
                            '<td>'+item.pass_attempts+'</td>'+
                            '<td>'+item.pass_completions+'</td>'+
                            '<td>'+item.pass_yards+'</td>'+
                            '<td>'+item.pass_touchdowns+'</td>'+
                            '<td>'+item.pass_interceptions+'</td>'+
                            '<td>'+item.rush_attempts+'</td>'+
                            '<td>'+item.rush_yards+'</td>'+
                            '<td>'+item.rush_touchdowns+'</td>'+
                            '<td>'+item.rec_targets+'</td>'+
                            '<td>'+item.rec_receptions+'</td>'+
                            '<td>'+item.rec_yards+'</td>'+
                            '<td>'+item.rec_touchdowns+'</td>'+
                            '<td>'+item.fumbles+'</td>'+
                            '<td>'+points.toFixed(0)+'</td>'+
                            '<td>'+ppg+'</td>';
                    }
                });

                table += '</tbody></table>';
                $('#fetched-data').html(table);
            }
        });
    }

    $('#save-note').click(function() {
        $.ajax({
            type : 'post',
            url : 'modalData.php',
            data :  {
                request: 'notes',
                id: $('#player-id').val(),
                notes: $('#player-notes').val()
            },
            success : function(data){
                $('#confirm').html('Saved!');
                location.reload();
            }
        });
    });
</script>

<style>
    .app-content.container-fluid {
        background: white;
        direction: ltr;
        font-size: 11px;
    }
    ul { list-style-type: none; margin: 0; padding: 0; }
    #sortable li { margin: 0 5px 5px 5px; padding: 5px; font-size: 1.2em; height: 1.5em; }
    html>body #sortable li { height: 1.5em; line-height: 1.2em; }
    .ui-state-highlight { height: 1.5em; line-height: 1.2em; }

    .tiers-list li {
        line-height: 2.2;
        font-size: 16px;
    }

    a, a:link, a:visited {
        color: black;
        cursor: pointer;
    }

    table#player-history td, th {
        padding: 10px 15px;
    }

    table.dataTable tbody th, table.dataTable tbody td {
        padding: 2px 10px;
    }

</style>