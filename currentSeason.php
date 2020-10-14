<?php

$pageName = "Current Season";
include 'header.php';
include 'sidebar.html';

?>

<div class="app-content content container-fluid">
    <div class="content-wrapper">
        <div class="content-header row"></div>

        <div class="content-body">
            <div class="row">
                <div class="col-xs-12 col-md-7 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">Stats</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <table class="table table-responsive" id="datatable-currentStats">
                                <thead>
                                    <th>Manager</th>
                                    <th>Pass Yds</th>
                                    <th>Pass TDs</th>
                                    <th>Ints</th>
                                    <th>Rush Yds</th>
                                    <th>Rush TDs</th>
                                    <th>Rec</th>
                                    <th>Rec Yds</th>
                                    <th>Rec TDs</th>
                                    <th>Fum</th>
                                    <th>Total Yds</th>
                                    <th>Total TDs</th>
                                </thead>
                                <tbody>
                                    <?php
                                    while ($row = mysqli_fetch_array($stats)) { ?>
                                        <tr>
                                            <td><?php echo $row['manager']; ?></td>
                                            <td><?php echo $row['pass_yds']; ?></td>
                                            <td><?php echo $row['pass_tds']; ?></td>
                                            <td><?php echo $row['ints']; ?></td>
                                            <td><?php echo $row['rush_yds']; ?></td>
                                            <td><?php echo $row['rush_tds']; ?></td>
                                            <td><?php echo $row['rec']; ?></td>
                                            <td><?php echo $row['rec_yds']; ?></td>
                                            <td><?php echo $row['rec_tds']; ?></td>
                                            <td><?php echo $row['fum']; ?></td>
                                            <td><?php echo $row['pass_yds'] + $row['rush_yds'] + $row['rec_yds']; ?></td>
                                            <td><?php echo $row['pass_tds'] + $row['rush_tds'] + $row['rec_tds']; ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-xs-12 col-md-5 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">Points</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <table class="table table-responsive" id="datatable-currentPoints">
                                <thead>
                                    <th>Manager</th>
                                    <th>QB</th>
                                    <th>RB</th>
                                    <th>WR</th>
                                    <th>TE</th>
                                    <th>W/R/T</th>
                                    <th>Q/W/R/T</th>
                                    <th>K</th>
                                    <th>DEF</th>
                                    <th>Bench</th>
                                    <th>Total</th>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($points as $manager => $values) {
                                        $totalPoints = 0;
                                        $totalProjected = 0;

                                        foreach ($values as $pos => $stuff) {
                                            $totalPoints += $stuff['points'];
                                            $totalProjected += $stuff['projected'];
                                        }

                                        ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo $manager; ?></strong><br />
                                                <i>Projected</i>
                                            </td>
                                            <td data-order="<?php echo $values['QB']['points']; ?>">
                                                <strong><?php echo round($values['QB']['points'], 1); ?></strong><br />
                                                <i><?php echo round($values['QB']['projected'], 1); ?></i>
                                            </td>
                                            <td data-order="<?php echo $values['RB']['points']; ?>">
                                                <strong><?php echo round($values['RB']['points'], 1); ?></strong><br />
                                                <i><?php echo round($values['RB']['projected'], 1); ?></i>
                                            </td>
                                            <td data-order="<?php echo $values['WR']['points']; ?>">
                                                <strong><?php echo round($values['WR']['points'], 1); ?></strong><br />
                                                <i><?php echo round($values['WR']['projected'], 1); ?></i>
                                            </td>
                                            <td data-order="<?php echo $values['TE']['points']; ?>">
                                                <strong><?php echo round($values['TE']['points'], 1); ?></strong><br />
                                                <i><?php echo round($values['TE']['projected'], 1); ?></i>
                                            </td>
                                            <td data-order="<?php echo $values['W/R/T']['points']; ?>">
                                                <strong><?php echo round($values['W/R/T']['points'], 1); ?></strong><br />
                                                <i><?php echo round($values['W/R/T']['projected'], 1); ?></i>
                                            </td>
                                            <td data-order="<?php echo $values['Q/W/R/T']['points']; ?>">
                                                <strong><?php echo round($values['Q/W/R/T']['points'], 1); ?></strong><br />
                                                <i><?php echo round($values['Q/W/R/T']['projected'], 1); ?></i>
                                            </td>
                                            <td data-order="<?php echo $values['K']['points']; ?>">
                                                <strong><?php echo round($values['K']['points'], 1); ?></strong><br />
                                                <i><?php echo round($values['K']['projected'], 1); ?></i>
                                            </td>
                                            <td data-order="<?php echo $values['DEF']['points']; ?>">
                                                <strong><?php echo round($values['DEF']['points'], 1); ?></strong><br />
                                                <i><?php echo round($values['DEF']['projected'], 1); ?></i>
                                            </td>
                                            <td data-order="<?php echo $values['BN']['points']; ?>">
                                                <strong><?php echo round($values['BN']['points'], 1); ?></strong><br />
                                                <i><?php echo round($values['BN']['projected'], 1); ?></i>
                                            </td>
                                            <td data-order="<?php echo $totalPoints; ?>">
                                                <strong><?php echo round($totalPoints, 1); ?></strong><br />
                                                <i><?php echo round($totalProjected, 1); ?></i>
                                            </td>
                                        </tr>

                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
            <div class="row">
                <div class="col-xs-12 col-md-8 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">Top Weekly Performers</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <table class="table table-responsive" id="datatable-bestWeek">
                                <thead>
                                    <th>Week</th>
                                    <th>Top QB</th>
                                    <th>Top RB</th>
                                    <th>Top WR</th>
                                    <th>Top TE</th>
                                    <th>Top W/R/T</th>
                                    <th>Top Q/W/R/T</th>
                                    <th>Top K</th>
                                    <th>Top DEF</th>
                                    <th>Top Bench</th>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($bestWeek as $week => $players) { ?>
                                        <tr>
                                            <td><?php echo $week; ?></td>
                                            <td data-order="<?php echo $players['qb']['points']; ?>">
                                                <strong><?php echo $players['qb']['manager']; ?></strong><br />
                                                <?php echo $players['qb']['player']; ?><br />
                                                <i><?php echo $players['qb']['points']. ' points'; ?></i>
                                            </td>
                                            <td data-order="<?php echo $players['rb']['points']; ?>">
                                                <strong><?php echo $players['rb']['manager']; ?></strong><br />
                                                <?php echo $players['rb']['player']; ?><br />
                                                <i><?php echo $players['rb']['points']. ' points'; ?></i>
                                            </td>
                                            <td data-order="<?php echo $players['wr']['points']; ?>">
                                                <strong><?php echo $players['wr']['manager']; ?></strong><br />
                                                <?php echo $players['wr']['player']; ?><br />
                                                <i><?php echo $players['wr']['points']. ' points'; ?></i>
                                            </td>
                                            <td data-order="<?php echo $players['te']['points']; ?>">
                                                <strong><?php echo $players['te']['manager']; ?></strong><br />
                                                <?php echo $players['te']['player']; ?><br />
                                                <i><?php echo $players['te']['points']. ' points'; ?></i>
                                            </td>
                                            <td data-order="<?php echo $players['wrt']['points']; ?>">
                                                <strong><?php echo $players['wrt']['manager']; ?></strong><br />
                                                <?php echo $players['wrt']['player']; ?><br />
                                                <i><?php echo $players['wrt']['points']. ' points'; ?></i>
                                            </td>
                                            <td data-order="<?php echo $players['qwrt']['points']; ?>">
                                                <strong><?php echo $players['qwrt']['manager']; ?></strong><br />
                                                <?php echo $players['qwrt']['player']; ?><br />
                                                <i><?php echo $players['qwrt']['points']. ' points'; ?></i>
                                            </td>
                                            <td data-order="<?php echo $players['k']['points']; ?>">
                                                <strong><?php echo $players['k']['manager']; ?></strong><br />
                                                <?php echo $players['k']['player']; ?><br />
                                                <i><?php echo $players['k']['points']. ' points'; ?></i>
                                            </td>
                                            <td data-order="<?php echo $players['def']['points']; ?>">
                                                <strong><?php echo $players['def']['manager']; ?></strong><br />
                                                <?php echo $players['def']['player']; ?><br />
                                                <i><?php echo $players['def']['points']. ' points'; ?></i>
                                            </td>
                                            <td data-order="<?php echo $players['bn']['points']; ?>">
                                                <strong><?php echo $players['bn']['manager']; ?></strong><br />
                                                <?php echo $players['bn']['player']; ?><br />
                                                <i><?php echo $players['bn']['points']. ' points'; ?></i>
                                            </td>
                                        </tr>

                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-xs-12 col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="media">
                                <div class="p-2 text-xs-center bg-green media-left media-middle">
                                    <i class="icon-star-full font-large-2 white"></i>
                                </div>
                                <div class="p-2 bg-green white media-body">
                                    <h5>Top Week Performance</h5>
                                    <h5 class="text-bold-400"><?php echo $topPerformers['topPerformer']['manager'].' - Week '.$topPerformers['topPerformer']['week']; ?><br />
                                        <?php echo $topPerformers['topPerformer']['player'].' - '.$topPerformers['topPerformer']['points'].' points'; ?>
                                    </h5>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <div class="media">
                                <div class="p-2 text-xs-center bg-green media-left media-middle">
                                    <i class="icon-star-full font-large-2 white"></i>
                                </div>
                                <div class="p-2 bg-green white media-body">
                                    <h5>Top Outperformed Projection</h5>
                                    <h5 class="text-bold-400"><?php echo $topPerformers['outperform']['manager'].' - Week '.$topPerformers['outperform']['week']; ?><br />
                                        <?php echo $topPerformers['outperform']['player'].' - '.$topPerformers['outperform']['points'].' points'; ?>
                                    </h5>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <div class="media">
                                <div class="p-2 text-xs-center bg-green media-left media-middle">
                                    <i class="icon-star-full font-large-2 white"></i>
                                </div>
                                <div class="p-2 bg-green white media-body">
                                    <h5>Most Total TDs</h5>
                                    <h5 class="text-bold-400"><?php echo $topPerformers['mostTds']['manager']; ?><br />
                                        <?php echo $topPerformers['mostTds']['points']; ?>
                                    </h5>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <div class="media">
                                <div class="p-2 text-xs-center bg-green media-left media-middle">
                                    <i class="icon-star-full font-large-2 white"></i>
                                </div>
                                <div class="p-2 bg-green white media-body">
                                    <h5>Most Total Yards</h5>
                                    <h5 class="text-bold-400"><?php echo $topPerformers['mostYds']['manager']; ?><br />
                                        <?php echo $topPerformers['mostYds']['points']; ?>
                                    </h5>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <div class="media">
                                <div class="p-2 text-xs-center bg-green media-left media-middle">
                                    <i class="icon-star-full font-large-2 white"></i>
                                </div>
                                <div class="p-2 bg-green white media-body">
                                    <h5>Best Bench</h5>
                                    <h5 class="text-bold-400"><?php echo $topPerformers['bestBench']['manager']; ?><br />
                                        <?php echo $topPerformers['bestBench']['points']; ?>
                                    </h5>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
            <div class="row">
                <div class="col-xs-12 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">Stats Against</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <table class="table table-responsive" id="datatable-statsAgainst">
                                <thead>
                                    <th>Manager</th>
                                    <th>Pass Yds</th>
                                    <th>Pass TDs</th>
                                    <th>Ints</th>
                                    <th>Rush Yds</th>
                                    <th>Rush TDs</th>
                                    <th>Rec</th>
                                    <th>Rec Yds</th>
                                    <th>Rec TDs</th>
                                    <th>Fum</th>
                                    <th>Total Yds</th>
                                    <th>Total TDs</th>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($statsAgainst as $manager => $row) { ?>
                                        <tr>
                                            <td><?php echo $manager; ?></td>
                                            <td><?php echo $row['pass_yds']; ?></td>
                                            <td><?php echo $row['pass_tds']; ?></td>
                                            <td><?php echo $row['ints']; ?></td>
                                            <td><?php echo $row['rush_yds']; ?></td>
                                            <td><?php echo $row['rush_tds']; ?></td>
                                            <td><?php echo $row['receptions']; ?></td>
                                            <td><?php echo $row['rec_yds']; ?></td>
                                            <td><?php echo $row['rec_tds']; ?></td>
                                            <td><?php echo $row['fumbles']; ?></td>
                                            <td><?php echo $row['pass_yds'] + $row['rush_yds'] + $row['rec_yds']; ?></td>
                                            <td><?php echo $row['pass_tds'] + $row['rush_tds'] + $row['rec_tds']; ?></td>
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

<?php include 'footer.html'; ?>

<script type="text/javascript">
    $(document).ready(function() {

        $('#datatable-currentPoints').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [
                [10, "desc"]
            ]
        });

        $('#datatable-currentStats').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [
                [0, "asc"]
            ]
        });

        $('#datatable-bestWeek').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [
                [0, "asc"]
            ]
        });

        $('#datatable-statsAgainst').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [
                [0, "asc"]
            ]
        });

    });
</script>