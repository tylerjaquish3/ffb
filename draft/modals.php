<div class="modal fade" id="draft-board" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
                <h4 class="modal-title">Draft Board</h4>
            </div>
            <div class="modal-body">
                <table class="table table-responsive" id="datatable-board">
                    <thead>
                        <?php
                        foreach ($draftOrder as $man => $avatar) {
                            echo '<th>'.$man.'</th>';
                        }
                        ?>
                    </thead>
                    <tbody>
                        <?php
                        for ($round = 1; $round <= count($allPositions); $round++) {
                            $pickMin = ($round*10)-10;
                            $pickMax = $round*10;
                            $dir = 'asc';
                            // Even rounds go backwards
                            if ($round % 2 == 0) {
                                $dir = 'desc';
                            }
                            echo '<tr>';

                            $result = mysqli_query(
                                $conn,
                                "SELECT pick_number, player, position, adp FROM draft_selections ds
                                JOIN preseason_rankings pr ON pr.id = ds.ranking_id
                                WHERE pick_number <= $pickMax
                                AND pick_number > $pickMin
                                ORDER BY pick_number $dir"
                            );

                            $count = mysqli_num_rows($result);
                            if ($round % 2 == 0 && $count < 10) {
                                for ($x = 0; $x < (10-$count); $x++) {
                                    echo '<td></td>';
                                }
                            }

                            while ($row = mysqli_fetch_array($result)) {
                                $goodPick = $row['pick_number'] >= $row['adp'] ? 'good-pick' : 'bad-pick';
                                ?>
                                <td class="color-<?php echo $row['position']; ?>"><?php echo '<span class="sub '.$goodPick.'">'.$row['pick_number'].'</span>&nbsp;'.$row['player']; ?></td>
                        <?php }
                            echo '</tr>';
                        } ?>
                    </tbody>
                </table>

            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="cheat-sheet" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
                <h4 class="modal-title">Cheat Sheet</h4>
            </div>
            <div class="modal-body" style="direction: ltr">
                <div class="row">
                    <div class="col-xs-3" id="TE-tiers"></div>
                    <div class="col-xs-3" id="WR-tiers"></div>
                    <div class="col-xs-3" id="RB-tiers"></div>
                    <div class="col-xs-3" id="QB-tiers"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="proj-standings" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
                <h4 class="modal-title">Projected Standings</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-xs-12 col-md-5">
                        <canvas id="proj-points"></canvas>
                    </div>
                    <div class="col-xs-12 col-md-7">
                        <table class="table table-responsive" id="datatable-standings">
                            <thead>
                                <th>Manager</th>
                                <th>QB</th>
                                <th>RB</th>
                                <th>WR</th>
                                <th>TE</th>
                                <th>DEF</th>
                                <th>K</th>
                                <th>Start</th>
                                <th>BN</th>
                                <th>Total</th>
                                <th>Last Year</th>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
                <div class="row" style="text-align: center;">
                    <div class="col-xs-4">
                        <h4>Worst Byes</h4>
                    </div>
                    <div class="col-xs-4">
                        <h4>Worst Picks</h4>
                    </div>
                    <div class="col-xs-4">
                        <h4>Best Picks</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-4">
                        <table class="table table-responsive" id="datatable-worstByes">
                            <thead>
                                <th>Manager</th>
                                <th>Week</th>
                                <th>Byes</th>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <div class="col-xs-4">
                        <table class="table table-responsive" id="datatable-worstPicks">
                            <thead>
                                <th>Manager</th>
                                <th>Player</th>
                                <th>Pick</th>
                                <th>ADP</th>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <div class="col-xs-4">
                        <table class="table table-responsive" id="datatable-bestPicks">
                            <thead>
                                <th>Manager</th>
                                <th>Player</th>
                                <th>Pick</th>
                                <th>ADP</th>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="positions" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
                <h4 class="modal-title">Drafted Positions</h4>
            </div>
            <div class="modal-body">
                <div class="row draft-pos">
                    <div class="col-xs-2">
                        <strong>Since Inception</strong>
                    </div>
                    <div class="col-xs-2">
                        <strong><?php echo $currentYear-3; ?></strong>
                    </div>
                    <div class="col-xs-2">
                        <strong><?php echo $currentYear-2; ?></strong>
                    </div>
                    <div class="col-xs-2">
                        <strong><?php echo $currentYear-1; ?></strong>
                    </div>
                    <div class="col-xs-2">
                        <strong><?php echo $currentYear; ?></strong>
                    </div>
                    <div class="col-xs-2">
                    </div>
                </div>
                <?php
                for ($rd = 1; $rd <= count($allPositions); $rd++) {
                ?>
                    <div class="row">
                        <div class="col-xs-2">
                            <canvas id="inception-rd<?php echo $rd; ?>"></canvas>
                        </div>
                        <div class="col-xs-2">
                            <canvas id="minus3-rd<?php echo $rd; ?>"></canvas>
                        </div>
                        <div class="col-xs-2">
                            <canvas id="minus2-rd<?php echo $rd; ?>"></canvas>
                        </div>
                        <div class="col-xs-2">
                            <canvas id="minus1-rd<?php echo $rd; ?>"></canvas>
                        </div>
                        <div class="col-xs-2">
                            <canvas id="current-rd<?php echo $rd; ?>"></canvas>
                        </div>
                        <div class="col-xs-2">
                            <strong><?php echo 'Rd'.$rd; ?></strong>
                        </div>
                    </div>
                <?php } ?>
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

<div class="modal fade" id="depth-chart" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" style="direction: ltr">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
                <h4 class="modal-title">Depth Chart</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-xs-12">
                        <table class="table table-responsive" id="datatable-depth">
                            <thead>
                                <th>Team</th>
                                <th>QB 1</th>
                                <th>QB 2</th>
                                <th>RB 1</th>
                                <th>RB 2</th>
                                <th>WR 1</th>
                                <th>WR 2</th>
                                <th>WR 3</th>
                                <th>WR 4</th>
                                <th>TE</th>
                                <th>K</th>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="defenses" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" style="direction: ltr">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
                <h4 class="modal-title">Defenses</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-xs-12">
                        <table class="table table-responsive" id="datatable-defenses">
                            <thead>
                                <th>Team</th>
                                <th>My Rank</th>
                                <th>SoS</th>
                                <th>Rank</th>
                                <th>Wk 1-4</th>
                                <th>Wk 1</th>
                                <th>Wk 2</th>
                                <th>Wk 3</th>
                                <th>Wk 4</th>
                                <th>Wk 5</th>
                                <th>Wk 6</th>
                                <th>Wk 7</th>
                                <th>Wk 8</th>
                            </thead>
                            <tbody>
                                <tr><td>Den</td><td>8</td><td>1</td><td>13</td><td>16</td>  <td class="color-M">NYG</td><td class="color-M">Jax</td><td class="color-G">NYJ</td><td class="color-B">Bal</td><td class="color-M">Pit</td><td class="color-M">LV </td><td class="color-B">Cle</td><td class="color-M">Was</td></tr>
                                <tr><td>NE</td><td>6</td><td>2</td><td>8</td><td>26</td>    <td class="color-G">Mia</td><td class="color-G">NYJ</td><td class="color-M">NO </td><td class="color-B">TB </td><td class="color-G">Hou</td><td class="color-B">Dal</td><td class="color-G">NYJ</td><td class="color-M">LAC</td></tr>
                                <tr><td>Pit</td><td>1</td><td>3</td><td>3</td><td>8</td>    <td class="color-B">Buf</td><td class="color-M">LV </td><td class="color-M">Cin</td><td class="color-B">GB </td><td class="color-G">Den</td><td class="color-B">Sea</td><td class="color-B">Cle</td><td class="color-M">Chi</td></tr>
                                <tr><td>KC</td><td>11</td><td>4</td><td>14</td><td>18</td>  <td class="color-B">Cle</td><td class="color-B">Bal</td><td class="color-M">LAC</td><td class="color-G">Phi</td><td class="color-B">Buf</td><td class="color-M">Was</td><td class="color-B">Ten</td><td class="color-M">NYG</td></tr>
                                <tr><td>Ind</td><td>7</td><td>6</td><td>7</td><td>28</td>   <td class="color-B">Sea</td><td class="color-B">LAR</td><td class="color-B">Ten</td><td class="color-G">Mia</td><td class="color-B">Bal</td><td class="color-G">Hou</td><td class="color-M">SF </td><td class="color-B">Ten</td></tr>
                                <tr><td>Was</td><td>3</td><td>7</td><td>2</td><td>17</td>   <td class="color-M">LAC</td><td class="color-M">NYG</td><td class="color-B">Buf</td><td class="color-M">Atl</td><td class="color-M">NO </td><td class="color-B">KC </td><td class="color-B">GB </td><td class="color-G">Den</td></tr>
                                <tr><td>Ari</td><td>12</td><td>8</td><td>19</td><td>4</td>  <td class="color-B">Ten</td><td class="color-M">Min</td><td class="color-M">Jax</td><td class="color-B">LAR</td><td class="color-M">SF </td><td class="color-B">Cle</td><td class="color-G">Hou</td><td class="color-B">GB </td></tr>
                                <tr><td>LAC</td><td>10</td><td>9</td><td>12</td><td>14</td> <td class="color-M">Was</td><td class="color-B">Dal</td><td class="color-B">KC </td><td class="color-M">LV </td><td class="color-B">Cle</td><td class="color-B">Bal</td><td class="color-G">NE </td><td class="color-G">Phi</td></tr>
                                <tr><td>Mia</td><td>9</td><td>11</td><td>10</td><td>20</td> <td class="color-G">NE </td><td class="color-B">Buf</td><td class="color-M">LV </td><td class="color-M">Ind</td><td class="color-B">TB </td><td class="color-M">Jax</td><td class="color-M">Atl</td><td class="color-B">Buf</td></tr>
                                <tr><td>SF</td><td>4</td><td>13</td><td>5</td><td>5</td>    <td class="color-G">Det</td><td class="color-G">Phi</td><td class="color-B">GB </td><td class="color-B">Sea</td><td class="color-M">Ari</td><td class="color-M">Ind</td><td class="color-M">Chi</td><td class="color-M">Ari</td></tr>
                                <tr><td>Bal</td><td>5</td><td>17</td><td>4</td><td>1</td>   <td class="color-M">LV </td><td class="color-B">KC </td><td class="color-G">Det</td><td class="color-G">Den</td><td class="color-M">Ind</td><td class="color-M">LAC</td><td class="color-M">Cin</td><td class="color-M">Min</td></tr>
                                <tr><td>LAR</td><td>2</td><td>18</td><td>1</td><td>30</td>  <td class="color-M">Chi</td><td class="color-M">Ind</td><td class="color-B">TB </td><td class="color-M">Ari</td><td class="color-B">Sea</td><td class="color-M">NYG</td><td class="color-G">Det</td><td class="color-G">Hou</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>

    let draftOrder = <?php echo json_encode($draftOrder); ?>;

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

    // Section for projected standings modal **************************
    $("#proj-standings").on('shown.bs.modal', function() {
        if (!$.fn.DataTable.isDataTable('#datatable-standings')) {
            let standingsTable = $('#datatable-standings').DataTable({
                "processing": true,
                "ajax": {
                    "url": "modalData.php",
                    "type": "POST",
                    "dataType": "json",
                    "data": {
                        "standings": true,
                        "draftOrder": draftOrder
                    }
                },
                "columns": [
                    { "data": "manager" },
                    { "data": "qb" },
                    { "data": "rb" },
                    { "data": "wr" },
                    { "data": "te" },
                    { "data": "def" },
                    { "data": "k" },
                    { "data": "starting" },
                    { "data": "bn" },
                    { "data": "total" },
                    { "data": "last_year" },
                ],
                "searching": false,
                "paging": false,
                "info": false,
                "autoWidth": false,
                "order": [
                    [7, "desc"]
                ]
            });
        }

        if (!$.fn.DataTable.isDataTable('#datatable-bestPicks')) {
            $('#datatable-bestPicks').DataTable({
                "processing": true,
                "ajax": {
                    "url": "modalData.php",
                    "type": "POST",
                    "dataType": "json",
                    "data": {
                        "bestPicks": true
                    }
                },
                "columns": [
                    { "data": "manager" },
                    { "data": "player" },
                    { "data": "pick" },
                    { "data": "adp" }
                ],
                "searching": false,
                "paging": false,
                "info": false,
                "sort": false
            });
        }

        if (!$.fn.DataTable.isDataTable('#datatable-worstPicks')) {
            $('#datatable-worstPicks').DataTable({
                "processing": true,
                "ajax": {
                    "url": "modalData.php",
                    "type": "POST",
                    "dataType": "json",
                    "data": {
                        "worstPicks": true
                    }
                },
                "columns": [
                    { "data": "manager" },
                    { "data": "player" },
                    { "data": "pick" },
                    { "data": "adp" }
                ],
                "searching": false,
                "paging": false,
                "info": false,
                "sort": false
            });
        }

        if (!$.fn.DataTable.isDataTable('#datatable-worstByes')) {
            $('#datatable-worstByes').DataTable({
                "processing": true,
                "ajax": {
                    "url": "modalData.php",
                    "type": "POST",
                    "dataType": "json",
                    "data": {
                        "worstByes": true
                    }
                },
                "columns": [
                    { "data": "manager" },
                    { "data": "week" },
                    { "data": "byes" }
                ],
                "searching": false,
                "paging": false,
                "info": false,
                "sort": false
            });
        }

        $.ajax({
            url : 'modalData.php',
            method: 'POST',
            dataType: 'json',
            data: {
                projectedChart: true,
                draftOrder: draftOrder
            },
            cache: false,
            success: function(response) {
                ctx = document.getElementById('proj-points').getContext('2d');
                Chart.defaults.global.defaultFontColor = '#ffffff';
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: ["QB", "RB", "WR", "TE", "K", "DEF", "BN"],
                        datasets: [
                            {
                                label: "Me",
                                backgroundColor: "#8cfa84",
                                data: response.mine
                            },{
                                label: "League Avg",
                                backgroundColor: "#fa887f",
                                data: response.avg
                            },
                        ]
                    },
                    options: {
                        scales: {
                            yAxes: [{
                                ticks: {
                                    beginAtZero: true
                                }
                            }]
                        }
                    }
                });
            }
        });

    });

    // Section for drafted positions modal **************************
    let colors = [
        '#7FFFD4',
        '#DEB887',
        '#fa9cff',
        '#69cfff',
        '#f7cbcc',
        '#dffcde'
    ];

    var options = {
        legend: {
            display: false
        }
    };

    $("#positions").on('shown.bs.modal', function(){
        $.ajax({
            url : 'modalData.php',
            method: 'POST',
            dataType: 'json',
            data: {
                currentYear: "<?php echo $currentYear; ?>",
                positions: true
            },
            cache: false,
            success: function(response) {
                positionYearChart('current', response.current);
                positionYearChart('minus1', response.minus1);
                positionYearChart('minus2', response.minus2);
                positionYearChart('minus3', response.minus3);
                positionYearChart('inception', response.inception);
            }
        });
    });

    function positionYearChart(chart, roundPos)
    {
        let count = <?php echo count($allPositions); ?>;
        for (x = 1; x <= count; x++) {
            data = roundPos[x-1]['data'];
            ctx = document.getElementById(chart+'-rd'+x).getContext('2d');
            new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ['QB', 'RB', 'WR', 'TE', 'K', 'DEF'],
                    datasets: [{
                        data: Object.values(data),
                        backgroundColor: colors
                    }]
                },
                options: options
            });
        }
    }

    // Section for depth chart modal ************************************
    $("#depth-chart").on('shown.bs.modal', function() {
        if (!$.fn.DataTable.isDataTable('#datatable-depth')) {
            let depthTable = $('#datatable-depth').DataTable({
                "processing": true,
                "ajax": {
                    "url": "modalData.php",
                    "type": "POST",
                    "data": {
                        "depthChart": true
                    }
                },
                "columns": [
                    { "data": "team" },
                    { "data": "qb1", "createdCell": function (td, cellData, rowData, row, col) {
                        $(td).addClass(rowData.qb1class);
                    } },
                    { "data": "qb2", "createdCell": function (td, cellData, rowData, row, col) {
                        $(td).addClass(rowData.qb2class);
                    }  },
                    { "data": "rb1", "createdCell": function (td, cellData, rowData, row, col) {
                        $(td).addClass(rowData.rb1class);
                    }  },
                    { "data": "rb2", "createdCell": function (td, cellData, rowData, row, col) {
                        $(td).addClass(rowData.rb2class);
                    }  },
                    { "data": "wr1", "createdCell": function (td, cellData, rowData, row, col) {
                        $(td).addClass(rowData.wr1class);
                    }  },
                    { "data": "wr2", "createdCell": function (td, cellData, rowData, row, col) {
                        $(td).addClass(rowData.wr2class);
                    }  },
                    { "data": "wr3", "createdCell": function (td, cellData, rowData, row, col) {
                        $(td).addClass(rowData.wr3class);
                    }  },
                    { "data": "wr4", "createdCell": function (td, cellData, rowData, row, col) {
                        $(td).addClass(rowData.wr4class);
                    }  },
                    { "data": "te1", "createdCell": function (td, cellData, rowData, row, col) {
                        $(td).addClass(rowData.te1class);
                    }  },
                    { "data": "k1", "createdCell": function (td, cellData, rowData, row, col) {
                        $(td).addClass(rowData.k1class);
                    }  }
                ],
                "searching": false,
                "sort": false,
                "paging": false,
                "info": false,
                "autoWidth": false,
                "order": [
                    [0, "asc"]
                ]
            });
        }
    });

    var defensesTable = $('#datatable-defenses').DataTable({
        "searching": false,
        "paging": false,
        "info": false,
        "autoWidth": false,
        "columnDefs": [
            { "sortable": false, "targets": [5,6,7,8,9,10,11,12] }
        ],
        "order": [
            [1, "asc"]
        ]
    });

    // Section for cheat sheet modal **********************************
    $("#cheat-sheet").on('shown.bs.modal', function() {
        $.ajax({
            url : 'modalData.php',
            method: 'POST',
            dataType: 'json',
            data: {
                cheatSheet: true
            },
            cache: false,
            success: function(response) {
                let posCol = '';

                for (const pos in response) {
                    posCol = '';
                    for (const tier in response[pos]) {
                        posCol += '<strong>Tier '+tier+'</strong><br />';
                        response[pos][tier].forEach(function (item) {
                            posCol += '<span class="'+item.class+'">'+item.player+'</span><br />';
                        })
                    }

                    $('#'+pos+'-tiers').html(posCol);
                }

                if (scrambled) {
                    $('#QB-tiers span').each(function () {
                        let str = $(this).text();
                        arr = str.split(".");
                        rank = arr[0];
                        $(this).text(rank+'. '+scrambledNames[Math.floor(Math.random() * scrambledNames.length)]);
                    });
                    $('#RB-tiers span').each(function () {
                        let str = $(this).text();
                        arr = str.split(".");
                        rank = arr[0];
                        $(this).text(rank+'. '+scrambledNames[Math.floor(Math.random() * scrambledNames.length)]);
                    });
                    $('#WR-tiers span').each(function () {
                        let str = $(this).text();
                        arr = str.split(".");
                        rank = arr[0];
                        $(this).text(rank+'. '+scrambledNames[Math.floor(Math.random() * scrambledNames.length)]);
                    });
                    $('#TE-tiers span').each(function () {
                        let str = $(this).text();
                        arr = str.split(".");
                        rank = arr[0];
                        $(this).text(rank+'. '+scrambledNames[Math.floor(Math.random() * scrambledNames.length)]);
                    });
                }
            }
        });
    });

</script>