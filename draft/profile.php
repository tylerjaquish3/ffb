<?php
    $pageName = "Profile";
    include 'header.php';
?>

<style>
    .mine {
        background-color: #8cfa84;
    }

    .yep2 {
        color: #fff !important;
    }
</style>

<body>

    <?php
    $currentYear = date('Y');

    $playerId = $_GET['id'];
    $result = mysqli_query($conn, "SELECT * FROM preseason_rankings WHERE id = $playerId");
    while ($row = mysqli_fetch_array($result)) {
        $base = $row;
    }
    ?>

    <div class="app-content container-fluid">
        <div class="content-wrapper">
            <div class="content-header row"></div>
            <div class="content-body">

                <div class="row">
                    <div class="col-xs-12">
                        <div class="card">
                            <div class="card-header">
                                <div class="col-xs-8">
                                    <a id="mark-bust" class="<?php echo $base['designation'] == 'bust' ? 'yep2' : ''; ?>"><i class="icon-aid-kit" title="Bust"></i></a>&nbsp;&nbsp;
                                    <a id="mark-value" class="<?php echo $base['designation'] == 'value' ? 'yep2' : ''; ?>"><i class="icon-price-tag" title="Value"></i></a>&nbsp;&nbsp;
                                    <a id="mark-sleeper" class="<?php echo $base['designation'] == 'sleeper' ? 'yep2' : ''; ?>"><i class="icon-sleepy2" title="Sleeper"></i></a>&nbsp;&nbsp;
                                    <a id="mark-breakout" class="<?php echo $base['designation'] == 'breakout' ? 'yep2' : ''; ?>"><i class="icon-star-full" title="Breakout"></i></a>
                                </div>
                                <div class="col-xs-4">
                                    <h3><?php echo $base['player']; ?></h3>
                                </div>
                            </div>
                            <div class="card-body" style="padding: 25px;">
                                <div class="row">

                                    <div class="col-xs-6"></div>
                                    <div class="col-xs-6">
                                        
                                        <form action="updateRankings.php" method="POST">
                                            
                                            <div class="row">
                                                <div class="col-xs-6">
                                                    <div class="form-group row">
                                                        <div class="col-sm-8">
                                                            <input type="text" class="form-control" name="my_rank" value="<?php echo $base['my_rank']; ?>">
                                                        </div>
                                                        <label for="my_rank" class="col-sm-4 col-form-label">My Rank</label>
                                                    </div>
                                                    <div class="form-group row">
                                                        <div class="col-sm-8">
                                                            <input type="text" class="form-control" name="adp" value="<?php echo $base['adp']; ?>">
                                                        </div>
                                                        <label for="adp" class="col-sm-4 col-form-label">ADP</label>
                                                    </div>
                                                    <div class="form-group row">
                                                        <div class="col-sm-8">
                                                            <input type="text" class="form-control" name="vols" disabled value="<?php echo $base['vols']; ?>">
                                                        </div>
                                                        <label for="vols" class="col-sm-4 col-form-label">VOLS</label>
                                                    </div>
                                                    <div class="form-group row">
                                                        <div class="col-sm-8">
                                                            <input type="text" class="form-control" name="sos" value="<?php echo $base['sos']; ?>">
                                                        </div>
                                                        <label for="sos" class="col-sm-4 col-form-label">SoS</label>
                                                    </div>
                                                    <div class="form-group row">
                                                        <div class="col-sm-8">
                                                            <input type="text" class="form-control" name="line" value="<?php echo $base['line']; ?>">
                                                        </div>
                                                        <label for="line" class="col-sm-4 col-form-label">O-Line</label>
                                                    </div>
                                                </div>
                                                <div class="col-xs-6">
                                                    <div class="form-group row">
                                                        <div class="col-sm-8">
                                                            <input type="text" class="form-control" name="team" value="<?php echo $base['team']; ?>">
                                                        </div>
                                                        <label for="team" class="col-sm-4 col-form-label">Team</label>
                                                    </div>
                                                    <div class="form-group row">
                                                        <div class="col-sm-8">
                                                            <input type="text" class="form-control" name="position" value="<?php echo $base['position']; ?>">
                                                        </div>
                                                        <label for="position" class="col-sm-4 col-form-label">Position</label>
                                                    </div>
                                                    <div class="form-group row">
                                                        <div class="col-sm-8">
                                                            <input type="text" class="form-control" name="bye" value="<?php echo $base['bye']; ?>">
                                                        </div>
                                                        <label for="bye" class="col-sm-4 col-form-label">Bye</label>
                                                    </div>
                                                    <div class="form-group row">
                                                        <div class="col-sm-8">
                                                            <input type="text" class="form-control" name="depth" value="<?php echo $base['depth']; ?>">
                                                        </div>
                                                        <label for="depth" class="col-sm-4 col-form-label">Depth</label>
                                                    </div>
                                                    <div class="form-group row">
                                                        <div class="col-sm-8">
                                                            <input type="text" class="form-control" name="tier" value="<?php echo $base['tier']; ?>">
                                                        </div>
                                                        <label for="tier" class="col-sm-4 col-form-label">Tier</label>
                                                    </div>
                                                    
                                                </div>
                                               
                                            </div>

                                            <div class="form-group row">
                                                <div class="col-sm-10">
                                                    <textarea class="form-control" name="notes" cols=150 rows=2><?php echo $base['notes']; ?></textarea>
                                                </div>
                                                <label for="notes" class="col-sm-2 col-form-label">Notes</label>
                                            </div>
    
                                            <input type="hidden" name="profile-update">
                                            <input type="hidden" name="id" value="<?php echo $playerId; ?>">
                                            <button type="submit" class="btn btn-secondary mine">Save</button>
                                            <button class="btn btn-secondary" id="sport_radar">Get History</button>
                                            <div id="confirm"></div>
                                        </form>
                                        
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mt-4">
                    <div class="col-xs-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-xs-12">
                                        <table class="table table-responsive" id="datatable-players" width="100%">
                                            <thead>
                                                <th>Year</th>
                                                <th>Team</th>
                                                <th>GP</th>
                                                <th>Pass Att</th>
                                                <th>Comp</th>
                                                <th>Pass Yds</th>
                                                <th>Pass TDs</th>
                                                <th>Int</th>
                                                <th>Rush Att</th>
                                                <th>Rush Yds</th>
                                                <th>Rush TDs</th>
                                                <th>Tar</th>
                                                <th>Rec</th>
                                                <th>Rec Yds</th>
                                                <th>Rec TDs</th>
                                                <th>Fumbles</th>
                                                <th>Pts</th>
                                                <th>Pts/Gm</th>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $result = mysqli_query($conn, "SELECT * FROM player_data where type = 'REG' AND preseason_ranking_id = $playerId");
                                                while ($row = mysqli_fetch_array($result)) {

                                                    $points = ($row['pass_yards']*.04)+($row['pass_touchdowns']*4)+($row['rush_yards']*.1)+($row['rush_touchdowns']*6);
                                                    $points += ($row['rec_yards']*.1)+($row['rec_touchdowns']*6)+($row['rec_receptions']*.5);
                                                    $points -= ($row['pass_interceptions']*2)+($row['fumbles']*3);
                                                    $gp = $row['games_played'];
                                                    if ($gp == 0) {
                                                        $gp = 1;
                                                    }
                                                    
                                                    echo '<tr>';
                                                    echo '<td>'.$row['year'].'</td>'.
                                                    '<td>'.$row['team_abbr'].'</td>'.
                                                    '<td>'.$row['games_played'].'</td>'.
                                                    '<td>'.$row['pass_attempts'].'</td>'.
                                                    '<td>'.$row['pass_completions'].'</td>'.
                                                    '<td>'.$row['pass_yards'].'</td>'.
                                                    '<td>'.$row['pass_touchdowns'].'</td>'.
                                                    '<td>'.$row['pass_interceptions'].'</td>'.
                                                    '<td>'.$row['rush_attempts'].'</td>'.
                                                    '<td>'.$row['rush_yards'].'</td>'.
                                                    '<td>'.$row['rush_touchdowns'].'</td>'.
                                                    '<td>'.$row['rec_targets'].'</td>'.
                                                    '<td>'.$row['rec_receptions'].'</td>'.
                                                    '<td>'.$row['rec_yards'].'</td>'.
                                                    '<td>'.$row['rec_touchdowns'].'</td>'.
                                                    '<td>'.$row['fumbles'].'</td>'.
                                                    '<td>'.round($points, 1).'</td>'.
                                                    '<td>'.round($points / $gp, 1).'</td>';
                                           
                                                    echo '</tr>';
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


<?php include '../footer.html'; ?>

<script type="text/javascript">

    $(document).ready(function() {

        var playersTable = $('#datatable-players').DataTable({
            pageLength: 25,
            searching: false,
            paging: false,
            info: false,
            order: [
                [0, "desc"]
            ]
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
                id: "<?php echo $playerId; ?>",
                designation: desig
            },
            success : function(data){
                $('#confirm').html('Saved!');
                location.reload();
            }
        });
    }

    $('#sport_radar').click(function () {
        $.ajax({
            type : 'post',
            url : 'updateRankings.php',
            data :  {
                request: 'player-history',
                id: $('#player-id').val(),
            },
            success : function(data){
                $('#confirm').html('Saved!');
                location.reload();
            }
        });
    });

</script>

<style>

    body {
        padding-top: 0;
    }

    .app-content.container-fluid {
        background: white;
        direction: ltr;
    }

    table#player-history td, th {
        padding: 10px 15px;
    }

    table.dataTable tbody th, table.dataTable tbody td {
        padding: 2px 10px;
    }

    a, a:link, a:visited {
        color: black;
        cursor: pointer;
    }

    .yep {
        color: #8cfa84 !important;
    }

</style>