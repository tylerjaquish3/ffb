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
                        <div class="row">
                            <div class="col-xs-2">
                                <a class="btn btn-secondary mine" id="profile">Profile</a>
                            </div>
                            <div class="col-xs-4">
                                <div id="player-detail">Loading...</div>
                            </div>
                            <div class="col-xs-2">
                                <a id="mark-bust" class=""><i class="icon-aid-kit" title="Bust"></i></a>&nbsp;&nbsp;
                                <a id="mark-value" class=""><i class="icon-price-tag" title="Value"></i></a>&nbsp;&nbsp;
                                <a id="mark-sleeper" class=""><i class="icon-sleepy2" title="Sleeper"></i></a>&nbsp;&nbsp;
                                <a id="mark-breakout" class=""><i class="icon-star-full" title="Breakout"></i></a>&nbsp;&nbsp;
                                <a id="move-down"><i class="icon-arrow-down" title="Move Down"></i></a>
                            </div>
                            <div class="col-xs-4">
                                <div id="player-header">Loading...</div>
                            </div>
                        </div>
                        <div id="fetched-data">Loading...</div>

                        <textarea id="player-notes" cols=150 rows=2></textarea>

                        <br />
                        <br /><a class="btn btn-secondary mine" id="save-note">Save Note</a><div id="confirm"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .yep {
        color: #8cfa84 !important;
    }

    .mine {
        background-color: #8cfa84;
        color: #000 !important;
    }
</style>

<script>

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

                        $('#mark-bust').removeClass('yep');
                        $('#mark-value').removeClass('yep');
                        $('#mark-sleeper').removeClass('yep');
                        $('#mark-breakout').removeClass('yep');
                        if (item.designation) {
                            desig = item.designation;
                            $('#mark-'+desig).addClass('yep');
                        } 

                        let detail = 'ADP: '+item.adp+' | My Rank: '+item.my_rank+' | VOLS: '+item.vols+' | Depth: '+item.depth+' | Tier: '+item.tier;
                        $('#player-detail').html(detail);
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

    $('#profile').click(function () {
        let url = 'profile.php?id='+$('#player-id').val();
        window.open(url, '_blank');
    });

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

    $('#mark-bust').click(function () {
        updateDesignation('bust');
    });
    $('#mark-value').click(function () {
        updateDesignation('value');
    });
    $('#mark-sleeper').click(function () {
        updateDesignation('sleeper');
    });
    $('#mark-breakout').click(function () {
        updateDesignation('breakout');
    });

    function updateDesignation(desig)
    {
        $.ajax({
            type : 'post',
            url : 'modalData.php',
            data :  {
                request: 'designation',
                id: $('#player-id').val(),
                designation: desig
            },
            success : function(data){
                $('#confirm').html('Saved!');
                location.reload();
            }
        });
    }

    $('#move-down').click(function() {
        $.ajax({
            type : 'post',
            url : 'modalData.php',
            data :  {
                request: 'down',
                id: $('#player-id').val()
            },
            success : function(data){
                $('#confirm').html('Saved!');
                location.reload();
            }
        });
    });

    $('#player-data').on('hidden.bs.modal', function () {
        $('#player-header').html('Loading...');
        $('#player-detail').html('Loading...');
        $('#fetched-data').html('Loading...');
    });

</script>