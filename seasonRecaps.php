<?php

$pageName = "Season Recap";
include 'header.php';
include 'sidebar.html';

if (isset($_GET['id'])) {
    $season = $_GET['id'];
} else {
    $result = mysqli_query($conn, "SELECT DISTINCT year FROM finishes ORDER BY year DESC LIMIT 1");
    while ($row = mysqli_fetch_array($result)) {
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
        <div class="content-header row"></div>

        <div class="content-body">
            <div class="row">
                <div class="col-xs-12">
                    <select id="year-select">
                        <?php
                        $result = mysqli_query($conn, "SELECT DISTINCT year FROM finishes ORDER BY year DESC");
                        while ($row = mysqli_fetch_array($result)) {
                            if ($row['year'] == $season) {
                                echo '<option selected value="'.$row['year'].'">'.$row['year'].'</option>';
                            } else {
                                echo '<option value="'.$row['year'].'">'.$row['year'].'</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
            <div>
            <!-- Statistics -->
            <div class="row">
                <div class="col-xl-3 col-lg-6 col-xs-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="media">
                                <div class="p-2 text-xs-center bg-green media-left media-middle">
                                    <i class="icon-checkmark2 font-large-2 white"></i>
                                </div>
                                <div class="p-2 bg-green white media-body">
                                    <h5>Most Points</h5>
                                    <h5 class="text-bold-400"><?php echo $topScorer; ?>&#x200E;</h5>
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
                                    <i class="icon-star-full font-large-2 white"></i>
                                </div>
                                <div class="p-2 bg-green white media-body">
                                    <h5>Regular Season Champion</h5>
                                    <h5 class="text-bold-400"><?php echo $regSeasonChamp; ?>&#x200E;</h5>
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
                                    <i class="icon-sad font-large-2 white"></i>
                                </div>
                                <div class="p-2 bg-green white media-body">
                                    <h5>Second Place</h5>
                                    <h5 class="text-bold-400"><?php echo $runnerUp; ?>&#x200E;</h5>
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
                                    <h5>Champion</h5>
                                    <h5 class="text-bold-400"><?php echo $champion; ?>&#x200E;</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 col-xs-12">
                    <div class="card">
                        <div class="card-header">
                            <h3>Standings</h3>
                        </div>
                        <div class="card-body">
                            <div class="card-block">
                                <table class="table table-responsive" id="datatable-standings">
                                    <thead>
                                        <th>Rank</th>
                                        <th>Seed</th>
                                        <th>Manager</th>
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
                                                <td><?php echo $standings['manager']; ?></td>
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
                <div class="col-md-6 col-xs-12">
                    <div class="card">
                        <div class="card-header">
                            <h3>Postseason</h3>
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
                                        <th>Champion</th>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="quarter top"><?php echo '<span class="seed">1</span>'.$bye1; ?></td>
                                            <td class="semi"></td>
                                            <td class="final"></td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td class="quarter bottom">Bye</td>
                                            <td class="semi top"><?php echo $s1['manager1disp']; ?><br />
                                                <?php echo explode(' - ',$s1['score'])[0]; ?>
                                            </td>
                                            <td class="final"></td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td class="quarter top"><?php echo '<span class="seed">' . $q1['m1seed'] . '</span>'.$q1['manager1disp']; ?><br />
                                                <?php echo explode(' - ',$q1['score'])[0]; ?>
                                            </td>
                                            <td class="semi bottom"><?php echo $s1['manager2disp']; ?><br />
                                                <?php echo explode(' - ',$s1['score'])[1]; ?>
                                            </td>
                                            <td class="final"></td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td class="quarter bottom"><?php echo '<span class="seed">' . $q1['m2seed'] . '</span>'.$q1['manager2disp']; ?><br />
                                                <?php echo explode(' - ',$q1['score'])[1]; ?>
                                            </td>
                                            <td class="semi"></td>
                                            <td class="final top"><?php echo $f['manager1disp']; ?><br />
                                                <?php echo explode(' - ',$f['score'])[0]; ?>
                                            </td>
                                            <td class="champ"><?php echo ($f['winner'] == 'm1') ? $f['manager1disp'] : $f['manager2disp']; ?></td>
                                        </tr>
                                        <tr>
                                            <td class="quarter top"><?php echo '<span class="seed">' . $q2['m1seed'] . '</span>'.$q2['manager1disp']; ?><br />
                                                <?php echo explode(' - ',$q2['score'])[0]; ?>
                                            </td>
                                            <td class="semi"></td>
                                            <td class="final bottom"><?php echo $f['manager2disp']; ?><br />
                                                <?php echo explode(' - ',$f['score'])[1]; ?>
                                            </td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td class="quarter bottom"><?php echo '<span class="seed">' . $q2['m2seed'] . '</span>'.$q2['manager2disp']; ?><br />
                                                <?php echo explode(' - ',$q2['score'])[1]; ?>
                                            </td>
                                            <td class="semi top"><?php echo $s2['manager1disp']; ?><br />
                                                <?php echo explode(' - ',$s2['score'])[0]; ?>
                                            </td>
                                            <td class="final"></td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td class="quarter top">Bye</td>
                                            <td class="semi bottom"><?php echo $s2['manager2disp']; ?><br />
                                                <?php echo explode(' - ',$s2['score'])[1]; ?>
                                            </td>
                                            <td class="final"></td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td class="quarter bottom"><?php echo '<span class="seed">2</span>'.$bye2; ?></td>
                                            <td class="semi"></td>
                                            <td class="final"></td>
                                            <td></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 col-xs-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">Draft Results</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <table class="table table-responsive" id="datatable-draft">
                                <thead>
                                    <th>Round</th>
                                    <th>Overall Pick</th>
                                    <th>Player</th>
                                    <th>Manager</th>
                                    <th>Position</th>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($draftResults as $draft) {
                                        if ($draft['year'] == $season) { ?>
                                        <tr>
                                            <td><?php echo $draft['round']; ?></td>
                                            <td><?php echo $draft['overall_pick']; ?></td>
                                            <td><?php echo $draft['player']; ?></td>
                                            <td><?php echo $draft['name']; ?></td>
                                            <td><?php echo $draft['position']; ?></td>
                                        </tr>

                                    <?php }
                                    } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xs-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">Regular Season Matchups</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <table class="table table-responsive" id="datatable-regSeason">
                                <thead>
                                    <th>Week</th>
                                    <th>Manager</th>
                                    <th>Opponent</th>
                                    <th>Score</th>
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
                                            <td><?php echo $matchup['score']; ?></td>
                                        </tr>

                                    <?php }
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

<style>

    span.seed {
        font-size: 14px;
        padding: 0px 5px;
        margin-right: 5px;
    }

</style>

<?php include 'footer.html'; ?>

<script type="text/javascript">
    $(document).ready(function() {

        let baseUrl = "<?php echo $BASE_URL; ?>";

        $('#year-select').change(function() {
            window.location = baseUrl+'seasonRecaps.php?id='+$('#year-select').val();
        });

        $('#datatable-regSeason').DataTable({
            "order": [
                [0, "asc"]
            ]
        });

        $('#datatable-postseason').DataTable({
            "columnDefs": [{
                "targets": [4],
                "visible": false,
            }],
            "searching": false,
            "paging": false,
            "info": false,
            "order": [
                [4, "desc"]
            ]
        });

        $('#datatable-standings').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [
                [0, "asc"]
            ]
        });

        $('#datatable-draft').DataTable({
            "order": [
                [1, "asc"]
            ]
        });
    });
</script>