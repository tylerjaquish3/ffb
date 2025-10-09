<?php
$pageName = 'Dashboard';
include 'header.php';
include 'sidebar.php';

?>

<div class="app-content content container-fluid">
    <div class="content-wrapper">
        <div class="content-header row"></div>

        <div class="content-body">
            <!-- Statistics -->
            <div class="row">
                <div class="col-xl-3 col-lg-6 col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="media">
                                <div class="p-2 text-xs-center bg-green-ffb media-left media-middle">
                                    <i class="icon-star-full font-large-2 white"></i>
                                </div>
                                <div class="p-2 bg-green-ffb media-body">
                                    <h5>Most Wins</h5>
                                    <h5 class="text-bold-400" id="most-wins">Loading...</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="media">
                                <div class="p-2 text-xs-center bg-green-ffb media-left media-middle">
                                    <i class="icon-trophy font-large-2 white"></i>
                                </div>
                                <div class="p-2 bg-green-ffb media-body">
                                    <h5>Most Championships</h5>
                                    <h5 class="text-bold-400" id="most-championships">Loading...</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="media">
                                <div class="p-2 text-xs-center bg-green-ffb media-left media-middle">
                                    <i class="icon-user font-large-2 white"></i>
                                </div>
                                <div class="p-2 bg-green-ffb media-body">
                                    <h5>Defending Champion</h5>
                                    <h5 class="text-bold-400" id="defending-champ">Loading...</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="media">
                                <div class="p-2 text-xs-center bg-green-ffb media-left media-middle">
                                    <i class="icon-calendar font-large-2 white"></i>
                                </div>
                                <div class="p-2 bg-green-ffb media-body">
                                    <h5>Seasons</h5>
                                    <h5 class="text-bold-400" id="seasons">Loading...</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title"><a href="trophy.php">Trophy</a></h4>
                        </div>
                        <div class="card-body" style="direction: ltr;">
                            <div class="row">
                                    <?php
                                    $result = query("SELECT f.year, m.name FROM finishes f JOIN managers m ON f.manager_id = m.id WHERE f.finish = 1 ORDER BY f.year");
                                    while ($row = fetch_array($result)) {
                                        $year = $row['year'];
                                        $name = strtoupper($row['name']);
                                        echo '<div class="col-md-3 col-xs-6"><div class="plaque"><a href="seasonRecaps.php?id=' . $year . '">' . $year . ' CHAMPION<br />' . $name . '</a></div></div>';
                                    }
                                    ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xl-4 col-sm-12 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title"><a href="regularSeason.php">Regular Season Record</a></h4>
                        </div>
                        <div class="card-body">
                            <div class="position-relative">
                                <table class="table table-responsive table-striped nowrap" id="datatable-wins">
                                    <thead>
                                        <th>Manager</th>
                                        <th>Wins</th>
                                        <th>Losses</th>
                                        <th>Win %&#x200E;</th>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $result = query(
                                            "SELECT name, wins, losses, total, 100.0*wins/total AS win_pct
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
                                        while ($row = fetch_array($result)) { ?>
                                            <tr>
                                                <td><a href="profile.php?id=<?php echo $row['name'];?>"><?php echo $row['name']; ?></a></td>
                                                <td><?php echo $row['wins']; ?></td>
                                                <td><?php echo $row['losses']; ?></td>
                                                <td><?php echo number_format($row['win_pct'], 1) . ' %'; ?></td>
                                            </tr>

                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-8 col-sm-12 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title"><a href="postseason.php">Postseason</a></h4>
                        </div>
                        <div class="card-body">
                            <div class="card-block">
                                <canvas id="postseasonChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12 col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h4><a href="postseason.php">Postseason Stats</a></h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-sm-12">
                                    <select id="postMiscStats" class="dropdown form-control">
                                        <option value="20" selected>Average Finish</option>
                                        <option value="21">First Round Byes</option>
                                        <option value="22">Appearances</option>
                                        <option value="23">Underdog Wins</option>
                                        <option value="24">Top Seed Losses</option>
                                        <option value="25">Playoff Points</option>
                                        <option value="26">Win/Loss Margin</option>
                                    </select>
                                    <?php include 'postMiscStats.php'; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12 col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h4><a href="regularSeason.php">Regular Season Stats</a></h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-sm-12">
                                    <select id="regMiscStats" class="dropdown form-control">
                                        <option value="3" selected>Season Points</option>
                                        <option value="1">Win/Lose Streaks</option>
                                        <option value="2">Total Points</option>
                                        <option value="4">Average PF/PA</option>
                                        <option value="5">Start Streaks</option>
                                        <option value="6">Win/Loss Margin</option>
                                        <option value="7">Weekly Points</option>
                                        <option value="8">Losses with Top 3 Pts</option>
                                        <option value="9">Wins with Bottom 3 Pts</option>
                                        <option value="10">Record Against Everyone</option>
                                        <option value="11">Draft Positions</option>
                                        <option value="12">Moves/Trades</option>
                                        <option value="13">Lineup Accuracy</option>
                                        <option value="14">Points by Position</option>
                                        <option value="15">Points by Position & Season</option>
                                        <option value="16">Points by Position & Week</option>
                                        <option value="17">Scoring by Week/Season</option>
                                    </select>
                                    <div style="direction: ltr;">
                                        <?php include 'regMiscStats.php'; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12 col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h4>League Stats</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-sm-12">
                                    <select id="leagueMiscStats" class="dropdown form-control">
                                        <option value="30" selected>Weekly Points</option>
                                        <option value="31">Season Points</option>
                                        <option value="32">Points by Position</option>
                                        <!-- <option value="33">Playoff Points</option> -->
                                    </select>
                                    <div style="direction: ltr;">
                                        <?php include 'leagueMiscStats.php'; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<script type="text/javascript">

    $('#regMiscStats').change(function() {
        showRegTable($(this).val());
    });

    $('#postMiscStats').change(function() {
        showPostTable($(this).val());
    });
    
    $('#leagueMiscStats').change(function() {
        showLeagueTable($(this).val());
    });

    $('#datatable-wins').DataTable({
        searching: false,
        paging: false,
        info: false,
        order: [
            [3, "desc"]
        ]
    });

    $(function() {
        // Fetch dashboard and chart data via AJAX
        fetch('data/index.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.statusText);
            }
            return response.json();
        })
        .then(data => {
            // Dashboard cards
            document.getElementById('most-wins').textContent = data.dashboardNumbers.most_wins_manager + ' (' + data.dashboardNumbers.most_wins_number + ')';
            document.getElementById('most-championships').textContent = data.dashboardNumbers.most_championships_manager + ' (' + data.dashboardNumbers.most_championships_number + ')';
            document.getElementById('defending-champ').textContent = data.dashboardNumbers.defending_champ;
            document.getElementById('seasons').textContent = data.dashboardNumbers.seasons;

            // Postseason chart
            var ctx = document.getElementById('postseasonChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.postseasonChart.managers,
                    datasets: [
                        {
                            label: 'Playoff Appearances',
                            data: data.postseasonChart.appearances,
                            backgroundColor: '#297eff'
                        },
                        {
                            label: 'Championship Appearances',
                            data: data.postseasonChart.shipAppearances,
                        },
                        {
                            label: 'Championship Wins',
                            data: data.postseasonChart.ships,
                            backgroundColor: '#2eb82e'
                        }
                    ]
                },
                options: {
                    indexAxis: 'y',
                    scales: {
                        x: { stacked: true },
                        y: { stacked: true }
                    }
                }
            });
        })
        .catch(error => {
            
            console.error('Error fetching dashboard data:', error);
            document.getElementById('most-wins').textContent = 'Error loading data';
            document.getElementById('most-championships').textContent = 'Error loading data';
            document.getElementById('defending-champ').textContent = 'Error loading data';
            document.getElementById('seasons').textContent = 'Error loading data';
        });

    });

    setTimeout(showRegTable(3), 1000);
    setTimeout(showLeagueTable(30), 1000);

</script>