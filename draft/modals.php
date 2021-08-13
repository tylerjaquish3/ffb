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
                        for($round = 1; $round <= 22; $round++) {
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
                    <div class="col-xs-3" id="TE-tiers">
                    </div>
                    <div class="col-xs-3" id="WR-tiers">
                    </div>
                    <div class="col-xs-3" id="RB-tiers">
                    </div>
                    <div class="col-xs-3" id="QB-tiers">
                    </div>
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
                            <tbody>
                            </tbody>
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
                for ($rd = 1; $rd < 18; $rd++) {
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
        for (x = 1; x < 18; x++) {
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

    // Section for cheat sheet modal **********************************
    $("#cheat-sheet").on('shown.bs.modal', function(){
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
            }
        });
    });

</script>