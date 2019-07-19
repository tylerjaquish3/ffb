
<?php 
$pageName = 'Dashboard';
include 'header.php'; 
include 'sidebar.html'; 

?>

<div class="app-content content container-fluid">
    <div class="content-wrapper">
        <div class="content-header row"></div>

        <div class="content-body">
            <!-- Statistics -->
            <div class="row">
                <div class="col-xl-3 col-lg-6 col-xs-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="media">
                                <div class="p-2 text-xs-center bg-green media-left media-middle">
                                    <i class="icon-star-full font-large-2 white"></i>
                                </div>
                                <div class="p-2 bg-green white media-body">
                                    <h5>Most Wins</h5>
                                    <h5 class="text-bold-400"><?php echo $dashboardNumbers['most_wins_manager'].' ('.$dashboardNumbers['most_wins_number'].')'; ?>&#x200E;</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-xs-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="media">
                                <div class="p-2 text-xs-center bg-green media-left media-middle">
                                    <i class="icon-trophy font-large-2 white"></i>
                                </div>
                                <div class="p-2 bg-green white media-body">
                                    <h5>Most Championships</h5>
                                    <h5 class="text-bold-400"><?php echo $dashboardNumbers['most_championships_manager'].' ('.$dashboardNumbers['most_championships_number'].')'; ?>&#x200E;</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-xs-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="media">
                                <div class="p-2 text-xs-center bg-green media-left media-middle">
                                    <i class="icon-user font-large-2 white"></i>
                                </div>
                                <div class="p-2 bg-green white media-body">
                                    <h5>Unique Champions</h5>
                                    <h5 class="text-bold-400"><?php echo $dashboardNumbers['unique_winners']; ?></h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-xs-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="media">
                                <div class="p-2 text-xs-center bg-green media-left media-middle">
                                    <i class="icon-calendar font-large-2 white"></i>
                                </div>
                                <div class="p-2 bg-green white media-body">
                                    <h5>Seasons</h5>
                                    <h5 class="text-bold-400"><?php echo $dashboardNumbers['seasons']; ?></h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xl-4 col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="position-relative">
                                <table class="table table-responsive" id="datatable-wins">
                                    <thead>
                                        <th>Manager</th>
                                        <th>Wins</th>
                                        <th>Losses</th>
                                        <th>Win %&#x200E;</th>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $result = mysqli_query($conn,"SELECT name, wins, losses, total, wins/total AS win_pct 
                                            FROM managers 
                                            JOIN (
                                                SELECT COUNT(manager1_id) AS wins, manager1_id FROM regular_season_matchups rsm 
                                                WHERE manager1_score > manager2_score GROUP BY manager1_id
                                            ) w ON w.manager1_id = managers.id

                                            JOIN (
                                                SELECT COUNT(manager1_id) AS losses, manager1_id FROM regular_season_matchups rsm 
                                                WHERE manager1_score < manager2_score GROUP BY manager1_id
                                            ) l ON l.manager1_id = managers.id

                                            JOIN (
                                                SELECT COUNT(manager1_id) AS total, manager1_id FROM regular_season_matchups rsm 
                                                GROUP BY manager1_id
                                            ) t ON t.manager1_id = managers.id"
                                        );
                                        while($row = mysqli_fetch_array($result)) 
                                        { ?>
                                            <tr>
                                                <td><?php echo $row['name']; ?></td>
                                                <td><?php echo $row['wins']; ?></td>
                                                <td><?php echo $row['losses']; ?></td>
                                                <td><?php echo $row['win_pct']; ?></td>
                                            </tr>

                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-8 col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="card-block">
                                <canvas id="postseasonChart" class="height-400"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Miscellaneous Stats</h4>
                        </div>
                        <div class="card-body down15">
                            <div class="col-xs-12 col-sm-2">
                                <a class="btn btn-primary" id="btnTable1" onclick="showTable(1);">Win/Lose Streaks</a>
                                <a class="btn btn-primary" id="btnTable2" onclick="showTable(2);">Total Points</a>
                                <a class="btn btn-primary" id="btnTable3" onclick="showTable(3);">Season Points</a>
                                <a class="btn btn-primary" id="btnTable4" onclick="showTable(4);">Best Start</a>
                                <a class="btn btn-primary" id="btnTable5" onclick="showTable(5);">Worst Start</a>
                                <a class="btn btn-primary" id="btnTable6" onclick="showTable(6);">Largest Victory</a>
                                <a class="btn btn-primary" id="btnTable7" onclick="showTable(7);">Smallest Victory</a>
                                <a class="btn btn-primary" id="btnTable8" onclick="showTable(8);">Weekly Points</a>
                                <a class="btn btn-primary" id="btnTable9" onclick="showTable(9);">Average Finish</a>
                            </div>
                            <div class="col-xs-12 col-sm-2">
                                <a class="btn btn-primary darkened" id="btnTable10" onclick="showTable(10);">First Round Byes</a>
                                <a class="btn btn-primary" id="btnTable11" onclick="showTable(11);">Consecutive Playoff App</a>
                                <a class="btn btn-primary" id="btnTable12" onclick="showTable(12);">Bottom Seed Wins</a>
                                <a class="btn btn-primary" id="btnTable13" onclick="showTable(13);">Top Seed Losses</a>
                                <a class="btn btn-primary" id="btnTable14" onclick="showTable(14);">Losses with Top Pts</a>
                                <a class="btn btn-primary" id="btnTable15" onclick="showTable(15);">Wins with Bottom Pts</a>
                                <a class="btn btn-primary" id="btnTable16" onclick="showTable(16);">Average PF/PA</a>
                                <a class="btn btn-primary" id="btnTable17" onclick="showTable(17);">Playoff Win Margin</a>
                                <a class="btn btn-primary" id="btnTable18" onclick="showTable(18);">Playoff Points</a>
                            </div>

                            <div class="col-xs-12 col-sm-8">
                                <table class="table" id="datatable-misc1" style="display:none;">
                                    <thead>
                                        <th>Manager</th>
                                        <th>Longest Win Streak</th>
                                        <th>Longest Lose Streak</th>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $managers = '';
                                        $result2 = mysqli_query($conn, "SELECT * FROM managers");
                                        while($manager = mysqli_fetch_array($result2)) 
                                        {
                                            
                                            $managerId = $manager['id'];

                                            $winStreak = $loseStreak = $longestWinStreak = $longestLoseStreak = 0;
                                            $result = mysqli_query($conn, "SELECT name, w.year, w.week_number, win, lose 
                                                FROM managers 
                                                JOIN regular_season_matchups rsm ON managers.id = rsm.manager1_id
                                                JOIN (
                                                    SELECT SUM(CASE
                                                    WHEN manager1_id = $managerId AND manager1_score > manager2_score THEN 1
                                                    ELSE 0
                                                    END) AS win, year, week_number 
                                                    FROM regular_season_matchups 
                                                    GROUP BY year, week_number
                                                ) w ON w.year = rsm.year AND w.week_number = rsm.week_number

                                                JOIN (
                                                    SELECT SUM(CASE
                                                    WHEN manager1_id = $managerId AND manager1_score < manager2_score THEN 1
                                                    ELSE 0
                                                    END) AS lose, year, week_number 
                                                    FROM regular_season_matchups 
                                                    GROUP BY year, week_number
                                                ) w2 ON w2.year = rsm.year AND w2.week_number = rsm.week_number
                                                WHERE name = '".$manager['name']."'");
                                            while($row = mysqli_fetch_array($result)) 
                                            {
                                                if ($row['win'] == 1) {
                                                    $winStreak++;
                                                } else {
                                                    $longestWinStreak = ($winStreak > $longestWinStreak) ? $winStreak : $longestWinStreak;
                                                    $winStreak = 0;
                                                }

                                                if ($row['lose'] == 1) {
                                                    $loseStreak++;
                                                } else {
                                                    $longestLoseStreak = ($loseStreak > $longestLoseStreak) ? $loseStreak : $longestLoseStreak;
                                                    $loseStreak = 0;
                                                }

                                                
                                            }

                                            $response[] = [
                                                'manager' => $manager['name'],
                                                'winStreak' => $longestWinStreak,
                                                'loseStreak' => $longestLoseStreak
                                            ];
                                        }
                                        
                                        foreach($response as $row) 
                                        { ?>
                                            <tr>
                                                <td><?php echo $row['manager']; ?></td>
                                                <td><?php echo $row['winStreak']; ?></td>
                                                <td><?php echo $row['loseStreak']; ?></td>
                                            </tr>

                                        <?php } ?>
                                    </tbody>
                                </table>
                                <table class="table" id="datatable-misc10">
                                    <thead>
                                        <th>Manager</th>
                                        <th>#1 Seed</th>
                                        <th>#2 Seed</th>
                                        <th>First Round Byes</th>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $result = mysqli_query($conn,"SELECT name, IFNULL(one_seeds, 0) as one_seeds, IFNULL(two_seeds, 0) as two_seeds, IFNULL(one_seeds, 0)+IFNULL(two_seeds, 0) as total
                                            FROM managers
                                            LEFT JOIN (SELECT COUNT(manager1_id) as one_seeds, manager1_id 
                                            FROM playoff_matchups pm 
                                            WHERE manager1_seed = 1 and round = 'Semifinal'
                                            GROUP BY manager1_id) one ON managers.id = one.manager1_id
                                            LEFT JOIN (SELECT COUNT(manager1_id) as two_seeds, manager1_id 
                                            FROM playoff_matchups pm 
                                            WHERE (manager1_seed = 2 OR manager2_seed = 2) AND round = 'Semifinal'
                                            GROUP BY manager1_id) two ON managers.id = two.manager1_id"
                                        );
                                        while($row = mysqli_fetch_array($result)) 
                                        { ?>
                                            <tr>
                                                <td><?php echo $row['name']; ?></td>
                                                <td><?php echo $row['one_seeds']; ?></td>
                                                <td><?php echo $row['two_seeds']; ?></td>
                                                <td><?php echo $row['total']; ?></td>
                                            </tr>

                                        <?php } ?>
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

<?php include 'footer.html'; ?>

<script type="text/javascript">

    $(document).ready(function(){
        
        $('#datatable-wins').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [[ 3, "desc" ]]
        });

        // Misc tables
        $('#datatable-misc1').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [[ 1, "desc" ]]
        });
        $('#datatable-misc2').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [[ 3, "desc" ]]
        });
        $('#datatable-misc3').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [[ 3, "desc" ]]
        });
        $('#datatable-misc4').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [[ 3, "desc" ]]
        });
        $('#datatable-misc5').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [[ 3, "desc" ]]
        });
        $('#datatable-misc6').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [[ 3, "desc" ]]
        });
        $('#datatable-misc7').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [[ 3, "desc" ]]
        });
        $('#datatable-misc8').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [[ 3, "desc" ]]
        });
        $('#datatable-misc9').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [[ 3, "desc" ]]
        });
        $('#datatable-misc10').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [[ 3, "desc" ]]
        });
        $('#datatable-misc11').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [[ 3, "desc" ]]
        });
        $('#datatable-misc12').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [[ 3, "desc" ]]
        });
        $('#datatable-misc13').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [[ 3, "desc" ]]
        });
        $('#datatable-misc14').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [[ 3, "desc" ]]
        });
        $('#datatable-misc15').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [[ 3, "desc" ]]
        });
        $('#datatable-misc16').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [[ 3, "desc" ]]
        });
        $('#datatable-misc17').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [[ 3, "desc" ]]
        });
        $('#datatable-misc18').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [[ 3, "desc" ]]
        });

        var ctx = $('#postseasonChart');

        var stackedBar = new Chart(ctx, {
            type: 'horizontalBar',
            data: {
                labels: <?php echo json_encode($postseasonChart['managers']); ?>,
                datasets: [
                    {
                        label: 'Playoff Appearances',
                        data: <?php echo json_encode($postseasonChart['appearances']); ?>,
                        backgroundColor: '#04015d'
                    },
                    {
                        label: 'Championship Appearances',
                        data: <?php echo json_encode($postseasonChart['shipAppearances']); ?>,
                    },
                    {
                        label: 'Championship Wins',
                        data: <?php echo json_encode($postseasonChart['ships']); ?>,
                        backgroundColor: '#2eff37'
                    }
                ]
            },
            options: {
                scales: {
                    xAxes: [{
                        stacked: true
                    }],
                    yAxes: [{
                        stacked: true
                    }]
                }
            }
        });
    });

    function showTable(tableId) {
        for (i = 1; i < 19; i++) {
            $('#datatable-misc'+i).hide();
            $('#btnTable'+i).removeClass('darkened');
        }
        $('#datatable-misc'+tableId).show();

        $('#btnTable'+tableId).addClass('darkened');
    }

</script>
