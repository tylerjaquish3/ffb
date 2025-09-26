<?php

$pageName = "Season Recap";
include 'header.php';
include 'sidebar.php';

if (isset($_GET['id'])) {
    $season = $_GET['id'];
} else {
    $result = query("SELECT DISTINCT year FROM finishes ORDER BY year DESC LIMIT 1");
    while ($row = fetch_array($result)) {
        $season = $row['year'];
    }
}
$mostPoints = 0;
foreach ($seasonNumbers as $standings) {
    if ($standings['finish'] == 1) {
        $champion = $standings['manager'];
    }
    if ($standings['finish'] == 2) {
        $runnerUp = $standings['manager'];
    }
    if ($standings['pf'] > $mostPoints) {
        $mostPoints = $standings['pf'];
        $topScorer = $standings['manager'];
    }
    if ($standings['seed'] == 1) {
        $regSeasonChamp = $standings['manager'];
    }
}
?>

<div class="app-content content container-fluid">
    <div class="content-wrapper">

        <div class="content-body">
            <div class="row" style="direction: ltr;">
                <div class="col-sm-12 d-md-none">
                    <h5 style="margin-top: 5px; color: #fff;">Choose Season</h5>
                </div>
                <div class="col-sm-12 col-md-4">
                    <select id="year-select" class="form-control">
                        <?php
                        $result = query("SELECT DISTINCT year FROM finishes ORDER BY year DESC");
                        while ($row = fetch_array($result)) {
                            if ($row['year'] == $season) {
                                echo '<option selected value="'.$row['year'].'">'.$row['year'].'</option>';
                            } else {
                                echo '<option value="'.$row['year'].'">'.$row['year'].'</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>
            
            <!-- Tabs Navigation -->
            <div class="row mb-1">
                <div class="col-sm-12">
                    <div class="tab-buttons-container">
                        <button class="tab-button active" id="overview-tab" onclick="showCard('overview')">
                            Overview
                        </button>
                        <button class="tab-button" id="standings-tab" onclick="showCard('standings')">
                            Standings
                        </button>
                        <button class="tab-button" id="draft-results-tab" onclick="showCard('draft-results')">
                            Draft Results
                        </button>
                        <button class="tab-button" id="matchups-tab" onclick="showCard('matchups')">
                            Matchups
                        </button>
                        <button class="tab-button" id="trades-tab" onclick="showCard('trades')">
                            Trades
                        </button>
                        <button class="tab-button" id="weekly-standings-tab" onclick="showCard('weekly-standings')">
                            Weekly Standings
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Overview Tab -->
            <div class="row card-section" id="overview">
                <!-- Statistics -->
                <div class="col-xl-3 col-lg-6 col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="media">
                                <div class="p-2 text-xs-center bg-green-ffb media-left media-middle">
                                    <i class="icon-checkmark2 font-large-2 white"></i>
                                </div>
                                <div class="p-2 bg-green-ffb media-body">
                                    <h5>Most Points</h5>
                                    <h5 class="text-bold-400"><?php echo $topScorer; ?>&#x200E;</h5>
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
                                    <i class="icon-star-full font-large-2 white"></i>
                                </div>
                                <div class="p-2 bg-green-ffb media-body">
                                    <h5>Regular Season Champion</h5>
                                    <h5 class="text-bold-400"><?php echo $regSeasonChamp; ?>&#x200E;</h5>
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
                                    <i class="icon-sad font-large-2 white"></i>
                                </div>
                                <div class="p-2 bg-green-ffb media-body">
                                    <h5>Second Place</h5>
                                    <h5 class="text-bold-400"><?php echo $runnerUp; ?>&#x200E;</h5>
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
                                    <h5>Champion</h5>
                                    <h5 class="text-bold-400"><?php echo $champion; ?>&#x200E;</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 col-sm-12 table-padding">
                        <div class="card">
                            <div class="card-header">
                                <h3>Playoff Bracket</h3>
                            </div>
                            <div class="card-body">
                                <div class="card-block">
                                    <?php
                                    $firstQ = $firstS = true;
                                    $bye1 = $bye2 = '';
                                    foreach ($postseasonMatchups as $matchup) {
                                        if ($matchup['year'] == $season) {

                                            if ($matchup['winner'] == 'm1') {
                                                $matchup['manager1disp'] = '<span class="badge badge-primary">'.$matchup['manager1'].'</span>';
                                                $matchup['manager2disp'] = '<span class="badge badge-secondary">'.$matchup['manager2'].'</span>';
                                            } else {
                                                $matchup['manager1disp'] = '<span class="badge badge-secondary">'.$matchup['manager1'].'</span>';
                                                $matchup['manager2disp'] = '<span class="badge badge-primary">'.$matchup['manager2'].'</span>';
                                            }
                                            if ($matchup['round'] == 'Quarterfinal') {
                                                if ($firstQ) {
                                                    $q1 = $matchup;
                                                    $firstQ = false;
                                                } else {
                                                    $q2 = $matchup;
                                                }
                                            }
                                            if ($matchup['round'] == 'Semifinal') {

                                                if ($matchup['m1seed'] == '1') {
                                                    $bye1 = '<span class="badge badge-primary">'.$matchup['manager1'].'</span>';
                                                }
                                                if ($matchup['m1seed'] == '2') {
                                                    $bye2 = '<span class="badge badge-primary">'.$matchup['manager1'].'</span>';
                                                }
                                                if ($matchup['m2seed'] == '2') {
                                                    $bye2 = '<span class="badge badge-primary">'.$matchup['manager2'].'</span>';
                                                }

                                                if ($firstS) {
                                                    $s1 = $matchup;
                                                    $firstS = false;
                                                } else {
                                                            $s2 = $matchup;
                                                }
                                            }
                                            if ($matchup['round'] == 'Final') {
                                                $f = $matchup;
                                            }
                                        }
                                    }
                                    ?>

                                    <table id="bracket">
                                        <thead>
                                            <th>Quarterfinal</th>
                                            <th>Semifinal</th>
                                            <th>Final</th>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="quarter top"><?php echo '<span class="seed">1</span>'.$bye1; ?></td>
                                                <td class="semi"></td>
                                                <td class="final"></td>
                                            </tr>
                                            <tr>
                                                <td class="quarter bottom">Bye</td>
                                                <td class="semi top"><?php echo $s1['manager1disp']; ?><br />
                                                    <?php echo explode(' - ',$s1['score'])[0]; ?>
                                                </td>
                                                <td class="final"></td>
                                            </tr>
                                            <tr>
                                                <td class="quarter top"><?php echo '<span class="seed">' . $q1['m1seed'] . '</span>'.$q1['manager1disp']; ?><br />
                                                    <?php echo explode(' - ',$q1['score'])[0]; ?>
                                                </td>
                                                <td class="semi bottom"><?php echo $s1['manager2disp']; ?><br />
                                                    <?php echo explode(' - ',$s1['score'])[1]; ?>
                                                </td>
                                                <td class="final"></td>
                                            </tr>
                                            <tr>
                                                <td class="quarter bottom"><?php echo '<span class="seed">' . $q1['m2seed'] . '</span>'.$q1['manager2disp']; ?><br />
                                                    <?php echo explode(' - ',$q1['score'])[1]; ?>
                                                </td>
                                                <td class="semi"></td>
                                                <td class="final top"><?php echo $f['manager1disp']; ?><br />
                                                    <?php echo explode(' - ',$f['score'])[0]; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="quarter top"><?php echo '<span class="seed">' . $q2['m1seed'] . '</span>'.$q2['manager1disp']; ?><br />
                                                    <?php echo explode(' - ',$q2['score'])[0]; ?>
                                                </td>
                                                <td class="semi"></td>
                                                <td class="final bottom"><?php echo $f['manager2disp']; ?><br />
                                                    <?php echo explode(' - ',$f['score'])[1]; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="quarter bottom"><?php echo '<span class="seed">' . $q2['m2seed'] . '</span>'.$q2['manager2disp']; ?><br />
                                                    <?php echo explode(' - ',$q2['score'])[1]; ?>
                                                </td>
                                                <td class="semi top"><?php echo $s2['manager1disp']; ?><br />
                                                    <?php echo explode(' - ',$s2['score'])[0]; ?>
                                                </td>
                                                <td class="final"></td>
                                            </tr>
                                            <tr>
                                                <td class="quarter top">Bye</td>
                                                <td class="semi bottom"><?php echo $s2['manager2disp']; ?><br />
                                                    <?php echo explode(' - ',$s2['score'])[1]; ?>
                                                </td>
                                                <td class="final"></td>
                                            </tr>
                                            <tr>
                                                <td class="quarter bottom"><?php echo '<span class="seed">2</span>'.$bye2; ?></td>
                                                <td class="semi"></td>
                                                <td class="final"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Standing section -->
            <div class="row card-section" id="standings">
                <div class="col-sm-12 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h3>Standings</h3>
                        </div>
                        <div class="card-body">
                            <div class="card-block">
                                <table class="table table-responsive table-striped nowrap" id="datatable-standings">
                                    <thead>
                                        <th>Rank</th>
                                        <th>Seed</th>
                                        <th>Manager</th>
                                        <th></th>
                                        <th>Team Name</th>
                                        <th>Record</th>
                                        <th>PF</th>
                                        <th>PA</th>
                                        <th>Moves</th>
                                        <th>Trades</th>
                                    </thead>
                                    <tbody>
                                        <?php
                                        foreach ($seasonNumbers as $standings) {
                                            ?>
                                            <tr>
                                                <td><?php echo $standings['finish']; ?></td>
                                                <td><?php echo $standings['seed']; ?></td>
                                                <td><?php echo '<a href="/profile.php?id='.$standings['manager'].'">'.$standings['manager'].'</a>'; ?></td>
                                                <td><?php echo '<a href="/rosters.php?year='.$season.'&week=1&manager='.$standings['manager'].'"><i class="icon-clipboard"></i></a>'; ?></td>
                                                <td><?php echo $standings['team_name']; ?></td>
                                                <td><?php echo $standings['record']; ?></td>
                                                <td><?php echo $standings['pf']; ?></td>
                                                <td><?php echo $standings['pa']; ?></td>
                                                <td><?php echo $standings['moves']; ?></td>
                                                <td><?php echo $standings['trades']; ?></td>
                                            </tr>
                                        <?php
                                        } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Draft Results Tab -->
            <div class="row card-section" id="draft-results" style="display: none;">
                <div class="col-lg-12 col-sm-12 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">Draft Results</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <table class="table table-responsive table-striped nowrap" id="datatable-draft">
                                <thead>
                                    <th>Round</th>
                                    <th>Overall Pick</th>
                                    <th>Player</th>
                                    <th>Manager</th>
                                    <th>Position</th>
                                    <th>Points</th>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($draftResults as $draft) {
                                        if ($draft['year'] == $season) { ?>
                                        <tr>
                                            <td><?php echo $draft['round']; ?></td>
                                            <td><?php echo $draft['overall_pick']; ?></td>
                                            <td><?php echo '<a href="/players.php?player='.$draft['player'].'">'.$draft['player'].'</a>'; ?></td>
                                            <td><?php echo $draft['name']; ?></td>
                                            <td><?php echo $draft['position']; ?></td>
                                            <td><?php echo $draft['points'] ? round($draft['points'], 1) : 0; ?></td>
                                        </tr>
                                    <?php }
                                    } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Regular Season matchups Tab -->
            <div class="row card-section" id="matchups" style="display: none;">
                <div class="col-lg-12 col-sm-12 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">Regular Season Matchups</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <table class="table table-responsive table-striped nowrap" id="datatable-regSeason">
                                <thead>
                                    <th>Week</th>
                                    <th>Manager 1</th>
                                    <th>Manager 2</th>
                                    <th>Score 1</th>
                                    <th>Score 2</th>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($regSeasonMatchups as $matchup) {
                                        if ($matchup['year'] == $season) {
                                        ?>
                                        <tr>
                                            <td><?php echo $matchup['week']; ?></td>

                                            <?php if ($matchup['winner'] == 'm1') {
                                                echo '<td><span class="badge badge-primary">' . $matchup['manager1'] . '</span></td>';
                                            } else {
                                                echo '<td><span class="badge badge-secondary">' . $matchup['manager1'] . '</span></td>';
                                            }
                                            if ($matchup['winner'] == 'm2') {
                                                echo '<td><span class="badge badge-primary">' . $matchup['manager2'] . '</span></td>';
                                            } else {
                                                echo '<td><span class="badge badge-secondary">' . $matchup['manager2'] . '</span></td>';
                                            } ?>
                                            <td><?php echo '<a href="/rosters.php?year='.$matchup['year'].'&week='.$matchup['week'].'&manager='.$matchup['manager1'].'">'.$matchup['score1'].'</a>'; ?></td>
                                            <td><?php echo '<a href="/rosters.php?year='.$matchup['year'].'&week='.$matchup['week'].'&manager='.$matchup['manager2'].'">'.$matchup['score2'].'</a>'; ?></td>
                                        </tr>

                                    <?php }
                                    } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Trades Tab -->
            <div class="row card-section" id="trades" style="display: none;">
                <div class="col-lg-12 col-sm-12 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">Trades</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <table class="table table-responsive table-striped nowrap full-width" id="datatable-trades">
                                <thead>
                                    <th>From</th>
                                    <th>To</th>
                                    <th>Player</th>
                                    <th>Week</th>
                                    <th>Points Before</th>
                                    <th>Points After</th>
                                    <th></th>
                                </thead>
                                <tbody>
                                    <?php
                                    $lastId = null;
                                    foreach ($trades as $trade) {
                                        if ($trade['trade_identifier'] != $lastId) {
                                            echo "<tr class='black-row'><td></td><td></td><td></td><td></td><td></td><td></td><td>".$trade['trade_identifier']."</td></tr>";
                                        }
                                        ?>
                                        <tr>
                                            <td><?php echo $trade['m1']; ?></td>
                                            <td><?php echo $trade['m2']; ?></td>
                                            <td><?php echo $trade['player']; ?></td>
                                            <td><?php echo $trade['week']; ?></td>
                                            <td><?php echo round($trade['points_before'], 1); ?></td>
                                            <td><?php echo round($trade['points_after'], 1); ?></td>
                                            <td><?php echo $trade['trade_identifier']; ?></td>
                                        </tr>
                                    <?php 
                                        $lastId = $trade['trade_identifier'];
                                    } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Weekly Standings Tab -->
            <div class="row card-section" id="weekly-standings" style="display: none;">
                <div class="col-sm-12 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">Standings By Week</h4>
                        </div>
                        <div class="card-body chart-block" style="background: #fff; direction: ltr">
                            <canvas id="standingsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<?php include 'footer.php'; ?>

<style>
    span.seed {
        font-size: 14px;
        padding: 0px 5px;
        margin-right: 5px;
    }

    tr.black-row td {
        background-color: #bdbdbd;
    }
     
</style>

<script type="text/javascript">

    let baseUrl = "<?php echo $BASE_URL; ?>";

    $('#year-select').change(function() {
        window.location = baseUrl+'seasonRecaps.php?id='+$('#year-select').val();
    });

    // Initialize DataTables for each tab
    $('#datatable-regSeason').DataTable({
        "order": [
            [0, "asc"]
        ]
    });

    $('#datatable-standings').DataTable({
        searching: false,
        paging: false,
        info: false,
        order: [
            [0, "asc"]
        ]
    });

    $('#datatable-draft').DataTable({
        order: [
            [1, "asc"]
        ]
    });
    
    $('#datatable-trades').DataTable({
        columnDefs: [{
            targets: [6],
            visible: false,
        }],
        order: [
            [6, "desc"]
        ]
    });
    
    // Setup the weekly standings chart
    let weeks = <?php echo json_encode($weekStandings['weeks']); ?>;
    let managers = <?php echo json_encode($weekStandings['managers']); ?>;
    
    // Create the standings chart when the weekly-standings tab is clicked
    window.standingsChartInitialized = false;
    
    function initStandingsChart() {
        if (!window.standingsChartInitialized) {
            var ctx = $('#standingsChart');
            
            // Make sure the canvas is visible
            if (ctx.is(':visible')) {
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: weeks,
                        datasets: managers
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                display: true,
                                title: {
                                    display: true,
                                    text: 'Rank',
                                    font: {
                                        size: 20
                                    }
                                },
                                reverse: true
                            },
                            x: {
                                display: true,
                                title: {
                                    display: true,
                                    text: 'Week',
                                    font: {
                                        size: 20
                                    }
                                }
                            }
                        }
                    }
                });
                window.standingsChartInitialized = true;
            } else {
                console.log('Canvas not visible, cannot initialize chart');
            }
        } else {
            console.log('Standings chart already initialized');
        }
    }
    
    // Initialize the page with the Overview tab showing
    showCard('overview');
        
</script>