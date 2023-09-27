<?php


$pageName = "Current Season";
include 'header.php';
include 'sidebar.html';

?>

<div class="app-content content container-fluid">
    <div class="content-wrapper">
        <div class="content-header row"></div>

        <div class="content-body">
            <div class="row" style="direction: ltr;">
                <div class="col-sm-12 d-md-none">
                    <h5 style="margin-top: 5px; color: #fff;">Choose Season</h5>
                </div>
                <div class="col-sm-12 col-md-4">
                    <select id="year-select" class="form-control">
                        <?php
                        $result = query("SELECT DISTINCT year FROM rosters WHERE year > 2019 ORDER BY year DESC");
                        while ($row = fetch_array($result)) {
                            if ($row['year'] == $selectedSeason) {
                                echo '<option selected value="'.$row['year'].'">'.$row['year'].'</option>';
                            } else {
                                echo '<option value="'.$row['year'].'">'.$row['year'].'</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="row">

                <div class="col-sm-12 col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="media">
                                <div class="p-2 text-xs-center bg-green-ffb media-left media-middle">
                                    <i class="icon-coin-dollar font-large-2 white"></i>
                                </div>
                                <div class="p-2 bg-green-ffb media-body">
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
                                <div class="p-2 text-xs-center bg-green-ffb media-left media-middle">
                                    <i class="icon-clipboard font-large-2 white"></i>
                                </div>
                                <div class="p-2 bg-green-ffb media-body">
                                    <h5>Best Draft Pick</h5>
                                    <h5 class="text-bold-400"><?php echo $topPerformers['bestDraftPick']['manager']; ?><br />
                                        <?php echo $topPerformers['bestDraftPick']['player'].' - '.$topPerformers['bestDraftPick']['points'].' points'; ?>
                                    </h5>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <div class="media">
                                <div class="p-2 text-xs-center bg-green-ffb media-left media-middle">
                                    <i class="icon-flag font-large-2 white"></i>
                                </div>
                                <div class="p-2 bg-green-ffb media-body">
                                    <h5>Most Total TDs (incl. BN)</h5>
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
                                <div class="p-2 text-xs-center bg-green-ffb media-left media-middle">
                                    <i class="icon-earth font-large-2 white"></i>
                                </div>
                                <div class="p-2 bg-green-ffb media-body">
                                    <h5>Most Total Yards (incl. BN)</h5>
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
                                <div class="p-2 text-xs-center bg-green-ffb media-left media-middle">
                                    <i class="icon-power-cord font-large-2 white"></i>
                                </div>
                                <div class="p-2 bg-green-ffb media-body">
                                    <h5>Best Bench</h5>
                                    <h5 class="text-bold-400"><?php echo $topPerformers['bestBench']['manager']; ?><br />
                                        <?php echo $topPerformers['bestBench']['points']; ?>
                                    </h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-sm-12 col-lg-8 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">Points</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <table class="table table-striped nowrap row-border order-column full-width" id="datatable-currentPoints">
                                <thead>
                                    <tr>
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
                                    </tr>
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
                                                <strong><?php echo $values['QB']['points']; ?></strong><br />
                                                <i><?php echo $values['QB']['projected']; ?></i>
                                            </td>
                                            <td data-order="<?php echo $values['RB']['points']; ?>">
                                                <strong><?php echo $values['RB']['points']; ?></strong><br />
                                                <i><?php echo $values['RB']['projected']; ?></i>
                                            </td>
                                            <td data-order="<?php echo $values['WR']['points']; ?>">
                                                <strong><?php echo $values['WR']['points']; ?></strong><br />
                                                <i><?php echo $values['WR']['projected']; ?></i>
                                            </td>
                                            <td data-order="<?php echo $values['TE']['points']; ?>">
                                                <strong><?php echo $values['TE']['points']; ?></strong><br />
                                                <i><?php echo $values['TE']['projected']; ?></i>
                                            </td>
                                            <td data-order="<?php echo $values['W/R/T']['points']; ?>">
                                                <strong><?php echo $values['W/R/T']['points']; ?></strong><br />
                                                <i><?php echo $values['W/R/T']['projected']; ?></i>
                                            </td>
                                            <?php if (isset($values['Q/W/R/T'])) { ?>
                                                <td data-order="<?php echo $values['Q/W/R/T']['points']; ?>">
                                                    <strong><?php echo $values['Q/W/R/T']['points']; ?></strong><br />
                                                    <i><?php echo $values['Q/W/R/T']['projected']; ?></i>
                                                </td>
                                            <?php } else {
                                                echo '<td></td>';
                                            } ?>
                                            <td data-order="<?php echo $values['K']['points']; ?>">
                                                <strong><?php echo $values['K']['points']; ?></strong><br />
                                                <i><?php echo $values['K']['projected']; ?></i>
                                            </td>
                                            <td data-order="<?php echo $values['DEF']['points']; ?>">
                                                <strong><?php echo $values['DEF']['points']; ?></strong><br />
                                                <i><?php echo $values['DEF']['projected']; ?></i>
                                            </td>
                                            <td data-order="<?php echo $values['BN']['points']; ?>">
                                                <strong><?php echo $values['BN']['points']; ?></strong><br />
                                                <i><?php echo $values['BN']['projected']; ?></i>
                                            </td>
                                            <td data-order="<?php echo $totalPoints; ?>">
                                                <strong><?php echo $totalPoints; ?></strong><br />
                                                <i><?php echo $totalProjected; ?></i>
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
                <div class="col-sm-12 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">Top Weekly Performers</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <table class="stripe nowrap row-border order-column full-width" id="datatable-bestWeek">
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

            </div>
            <div class="row">
                <div class="col-sm-12 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">Stats</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <table class="stripe nowrap row-border order-column" id="datatable-currentStats">
                                <thead>
                                    <th>Manager</th>
                                    <th>Total Yds</th>
                                    <th>Total TDs</th>
                                    <th>Pass Yds</th>
                                    <th>Pass TDs</th>
                                    <th>Ints</th>
                                    <th>Rush Yds</th>
                                    <th>Rush TDs</th>
                                    <th>Rec</th>
                                    <th>Rec Yds</th>
                                    <th>Rec TDs</th>
                                </thead>
                                <tbody>
                                    <?php
                                    while ($row = fetch_array($stats)) { ?>
                                        <tr>
                                            <td><?php echo $row['manager']; ?></td>
                                            <td><?php echo $row['pass_yds'] + $row['rush_yds'] + $row['rec_yds']; ?></td>
                                            <td><?php echo $row['pass_tds'] + $row['rush_tds'] + $row['rec_tds']; ?></td>
                                            <td><?php echo $row['pass_yds']; ?></td>
                                            <td><?php echo $row['pass_tds']; ?></td>
                                            <td><?php echo $row['ints']; ?></td>
                                            <td><?php echo $row['rush_yds']; ?></td>
                                            <td><?php echo $row['rush_tds']; ?></td>
                                            <td><?php echo $row['rec']; ?></td>
                                            <td><?php echo $row['rec_yds']; ?></td>
                                            <td><?php echo $row['rec_tds']; ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">Stats Against</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <table class="stripe nowrap row-border order-column" id="datatable-statsAgainst">
                                <thead>
                                    <th>Manager</th>
                                    <th>Total Yds</th>
                                    <th>Total TDs</th>
                                    <th>Pass Yds</th>
                                    <th>Pass TDs</th>
                                    <th>Ints</th>
                                    <th>Rush Yds</th>
                                    <th>Rush TDs</th>
                                    <th>Rec</th>
                                    <th>Rec Yds</th>
                                    <th>Rec TDs</th>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($statsAgainst as $manager => $row) { ?>
                                        <tr>
                                            <td><?php echo $manager; ?></td>
                                            <td><?php echo $row['pass_yds'] + $row['rush_yds'] + $row['rec_yds']; ?></td>
                                            <td><?php echo $row['pass_tds'] + $row['rush_tds'] + $row['rec_tds']; ?></td>
                                            <td><?php echo $row['pass_yds']; ?></td>
                                            <td><?php echo $row['pass_tds']; ?></td>
                                            <td><?php echo $row['ints']; ?></td>
                                            <td><?php echo $row['rush_yds']; ?></td>
                                            <td><?php echo $row['rush_tds']; ?></td>
                                            <td><?php echo $row['receptions']; ?></td>
                                            <td><?php echo $row['rec_yds']; ?></td>
                                            <td><?php echo $row['rec_tds']; ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">Optimal Lineups</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <table class="stripe nowrap row-border order-column" id="datatable-optimal">
                                <thead>
                                    <th>Manager</th>
                                    <th>Opponent</th>
                                    <th>Week</th>
                                    <th>Actual Points</th>
                                    <th>Opponent Score</th>
                                    <th>Result</th>
                                    <th>Projected</th>
                                    <th>Opponent Projected</th>
                                    <th>Optimal Points</th>
                                    <th>Opponent Optimal</th>
                                    <th>Actual Margin</th>
                                    <th>Optimal Margin</th>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($optimal as $row) { ?>
                                        <tr>
                                            <td><?php echo $row['manager']; ?></td>
                                            <td><?php echo $row['opponent']; ?></td>
                                            <td><?php echo $row['week']; ?></td>
                                            <td><?php echo $row['points']; ?></td>
                                            <td><?php echo $row['oppPoints']; ?></td>
                                            <td><?php echo $row['result']; ?></td>
                                            <td><?php echo $row['projected']; ?></td>
                                            <td><?php echo $row['oppProjected']; ?></td>
                                            <td><?php echo $row['optimal']; ?></td>
                                            <td><?php echo $row['oppOptimal']; ?></td>
                                            <td><?php echo abs(round($row['points'] - $row['oppPoints'], 2)); ?></td>
                                            <td><?php echo abs(round($row['optimal'] - $row['oppOptimal'], 2)); ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12 col-md-6 col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">Worst Draft Picks</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <table class="table table-responsive table-striped nowrap" id="datatable-worstDraft">
                                <thead>
                                    <th>Manager</th>
                                    <th>Player</th>
                                    <th>Pick</th>
                                    <th>Points</th>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($worstDraft as $row) {
                                    ?>
                                        <tr>
                                            <td><?php echo $row['manager']; ?></td>
                                            <td><?php echo $row['player']; ?></td>
                                            <td><?php echo $row['overall_pick']; ?></td>
                                            <td><?php echo round($row['points'], 1); ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-sm-12 col-md-6 col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">Best Draft Picks</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <table class="table table-responsive table-striped nowrap" id="datatable-bestDraft">
                                <thead>
                                    <th>Manager</th>
                                    <th>Player</th>
                                    <th>Pick</th>
                                    <th>Points</th>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($bestDraft as $row) {
                                    ?>
                                        <tr>
                                            <td><?php echo $row['manager']; ?></td>
                                            <td><?php echo $row['player']; ?></td>
                                            <td><?php echo $row['overall_pick']; ?></td>
                                            <td><?php echo round($row['points'], 1); ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-sm-12 col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">Record Against Everyone</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <table class="table table-responsive table-striped nowrap" id="datatable-everyone">
                                <thead>
                                    <th>Manager</th>
                                    <th>Wins</th>
                                    <th>Losses</th>
                                    <th>Win %</th>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($everyoneRecord as $manager => $array) { ?>
                                        <tr>
                                            <td><?php echo $manager; ?></td>
                                            <td><?php echo $array['wins']; ?></td>
                                            <td><?php echo $array['losses']; ?></td>
                                            <td><?php echo round(($array['wins'] / ($array['wins'] + $array['losses'])) * 100, 1) . ' %'; ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">

                <div class="col-sm-12 col-lg-7">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">Points From Draft</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <table class="stripe nowrap row-border order-column" id="datatable-drafted">
                                <thead>
                                    <th>Manager</th>
                                    <th>All Drafted</th>
                                    <th>Drafted 1-5</th>
                                    <th>Drafted 10-17</th>
                                    <th>Undrafted</th>
                                    <th>Players Retained</th>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($draftedPoints as $row) {
                                    ?>
                                        <tr>
                                            <td><?php echo $row['manager']; ?></td>
                                            <td><?php echo round($row['drafted_points'], 1); ?></td>
                                            <td><?php echo round($row['early_round'], 1); ?></td>
                                            <td><?php echo round($row['late_round'], 1); ?></td>
                                            <td><?php echo round($row['undrafted_points'], 1); ?></td>
                                            <td><?php echo round($row['retained'], 1); ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="col-sm-12 col-lg-5">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">Draft Performance</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <table class="table table-responsive table-striped nowrap" id="datatable-draftPerformance">
                                <thead>
                                    <th>Manager</th>
                                    <th>Pick #</th>
                                    <th>Player</th>
                                    <th>GP in lineup</th>
                                    <th>Points</th>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($draftPerformance as $row) { ?>
                                        <tr>
                                            <td><?php echo $row['manager']; ?></td>
                                            <td><?php echo $row['overall_pick']; ?></td>
                                            <td><?php echo $row['player']; ?></td>
                                            <td><?php echo $row['GP']; ?></td>
                                            <td><?php echo round($row['points'], 1); ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>

            <div class="row">
                <div class="col-sm-12 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">Points For and Against</h4>
                        </div>
                        <div class="card-body" style="direction: ltr;">
                            <div class="card-block">
                                <canvas id="scatterChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<script src="/assets/dataTables-fixedColumns.min.js" type="text/javascript"></script>

<script type="text/javascript">
    $(document).ready(function() {

        let baseUrl = "<?php echo $BASE_URL; ?>";

        $('#year-select').change(function() {
            window.location = baseUrl+'currentSeason.php?id='+$('#year-select').val();
        });

        $('#datatable-currentPoints').DataTable({
            searching: false,
            paging: false,
            info: false,
            scrollX: "100%",
            scrollCollapse: true,
            fixedColumns:   {
                leftColumns: 1
            },
            order: [
                [10, "desc"]
            ],
            initComplete: function() {
                var api = this.api();
                
                api.columns(':not(:first)').every(function() {
                    var col = this.index();
                    var array = [];
                    api.cells(null, col).every(function() {
                        var cell = this.node();
                        var record_id = $(cell).attr("data-order");
                        array.push(record_id)
                    })

                    last = array.length-1;
                    array.sort(function(a, b){return b-a});

                    api.cells(null, col).every( function() {
                        var cell = this.node();
                        var record_id = $( cell ).attr( "data-order" );
                        if (record_id === array[0]) {
                            $(this.node()).css('background-color', 'rgb(172, 240, 172)')
                        } else if (record_id === array[last]) {
                            $(this.node()).css('background-color', 'rgba(255, 85, 85, 0.32)')
                        }
                    });
                });
            }
        });

        $('#datatable-currentStats').DataTable({
            searching: false,
            paging: false,
            info: false,
            scrollX: "100%",
            scrollCollapse: true,
            fixedColumns:   {
                left: 1
            },
            order: [
                [2, "desc"]
            ],
            initComplete: function() {
                var api = this.api();
                api.columns(':not(:first)').every(function() {
                    var col = this.index();
                    var data = this.data().unique().map(function(value) {
                        return parseInt(value);
                    }).toArray().sort(function(a, b){return b-a});

                    last = data.length-1;
                    api.cells(null, col).every(function() {
                        var cell = parseInt(this.data());
                        if (cell === data[0]) {
                            $(this.node()).css('background-color', 'rgb(172, 240, 172)')
                        } else if (cell === data[last]) {
                            $(this.node()).css('background-color', 'rgba(255, 85, 85, 0.32)')
                        }
                    });
                });
            }
        });

        $('#datatable-bestWeek').DataTable({
            searching: false,
            paging: false,
            info: false,
            scrollX: "100%",
            scrollCollapse: true,
            fixedColumns:   {
                left: 1
            },
            order: [
                [0, "desc"]
            ]
        });

        $('#datatable-statsAgainst').DataTable({
            searching: false,
            paging: false,
            info: false,
            scrollX: "100%",
            scrollCollapse: true,
            fixedColumns:   {
                left: 1
            },
            order: [
                [2, "desc"]
            ],
            initComplete: function() {
                var api = this.api();
                api.columns(':not(:first)').every(function() {
                    var col = this.index();
                    var data = this.data().unique().map(function(value) {
                        return parseInt(value);
                    }).toArray().sort(function(a, b){return b-a});

                    last = data.length-1;
                    api.cells(null, col).every(function() {
                        var cell = parseInt(this.data());
                        if (cell === data[0]) {
                            $(this.node()).css('background-color', 'rgb(172, 240, 172)')
                        } else if (cell === data[last]) {
                            $(this.node()).css('background-color', 'rgba(255, 85, 85, 0.32)')
                        }
                    });
                });
            }
        });

        $('#datatable-bestTeamWeek').DataTable({
            searching: false,
            info: false,
            scrollX: "100%",
            scrollCollapse: true,
            fixedColumns:   {
                left: 1
            },
            order: [
                [0, "desc"]
            ]
        });

        $('#datatable-optimal').DataTable({
            scrollX: "100%",
            scrollCollapse: true,
            fixedColumns:   {
                leftColumns: 2
            },
            order: [
                [3, "desc"]
            ],
            initComplete: function() {
                var api = this.api();
                api.columns(':not(:first)').every(function() {
                    var col = this.index();
                    var data = this.data().unique().map(function(value) {
                        return parseInt(value);
                    }).toArray().sort(function(a, b){return b-a});

                    last = data.length-1;
                    api.cells(null, col).every(function() {
                        var cell = parseInt(this.data());
                        if (cell === data[0]) {
                            $(this.node()).css('background-color', 'rgb(172, 240, 172)')
                        } else if (cell === data[last]) {
                            $(this.node()).css('background-color', 'rgba(255, 85, 85, 0.32)')
                        }
                    });
                });
            }
        });

        $('#datatable-drafted').DataTable({
            searching: false,
            paging: false,
            info: false,
            scrollX: "100%",
            scrollCollapse: true,
            fixedColumns:   {
                left: 1
            },
            "order": [
                [1, "desc"]
            ],
            initComplete: function() {
                var api = this.api();
                api.columns(':not(:first)').every(function() {
                    var col = this.index();
                    var data = this.data().unique().map(function(value) {
                        return parseInt(value);
                    }).toArray().sort(function(a, b){return b-a});

                    last = data.length-1;
                    api.cells(null, col).every(function() {
                        var cell = parseInt(this.data());
                        if (cell === data[0]) {
                            $(this.node()).css('background-color', 'rgb(172, 240, 172)')
                        } else if (cell === data[last]) {
                            $(this.node()).css('background-color', 'rgba(255, 85, 85, 0.32)')
                        }
                    });
                });
            }
        });

        $('#datatable-bestDraft').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [
                [3, "desc"]
            ]
        });

        $('#datatable-worstDraft').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [
                [3, "asc"]
            ]
        });

        $('#datatable-everyone').DataTable({
            "searching": false,
            "paging": false,
            "info": false,
            "order": [
                [3, "desc"]
            ]
        });

        $('#datatable-draftPerformance').DataTable({
            "order": [
                [1, "asc"]
            ]
        });

        const avg = <?php echo json_encode($scatterChart['average']); ?>;
        const quadrants = {
            id: 'quadrants',
            beforeDraw(chart, args, options) {
                const {ctx, chartArea: {left, top, right, bottom}, scales: {x, y}} = chart;
                const midX = x.getPixelForValue(avg);
                const midY = y.getPixelForValue(avg);
                ctx.save();
                ctx.fillStyle = options.topLeft;
                ctx.fillRect(left, top, midX - left, midY - top);
                ctx.fillStyle = options.topRight;
                ctx.fillRect(midX, top, right - midX, midY - top);
                ctx.fillStyle = options.bottomRight;
                ctx.fillRect(midX, midY, right - midX, bottom - midY);
                ctx.fillStyle = options.bottomLeft;
                ctx.fillRect(left, midY, midX - left, bottom - midY);
                ctx.restore();
            }
        };

        // Chart for scatter of pf
        var ctx = $("#scatterChart");
        var points = <?php echo json_encode($scatterChart['chart']); ?>;
        let pointColor = '#000';
        let dataset = [];
        let labels = [];
        for (const [key, value] of Object.entries(points)) {

            let obj = {};
            obj.label = key;
            obj.data = value;
            obj.datalabels = {
                align: 'bottom'
            };
            labels.push(key);
            dataset.push(obj);
        }

        let scatterChart = new Chart(ctx, {
            type: 'scatter',
            data: {
                labels: labels,
                datasets: dataset
            },
            options: {
                scales: {
                    y: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Points Against',
                            font: {
                                size: 15
                            }
                        }
                    },
                    x: {
                        display: true,
                        title: {
                            display: true,
                            text: 'Points For',
                            font: {
                                size: 15
                            }
                        }
                    },
                },
                plugins: {
                    quadrants: {
                        topLeft: "rgba(255, 85, 85, 0.32)",
                        topRight: "#bdbdbd",
                        bottomRight: "rgb(172, 240, 172)",
                        bottomLeft: "#bdbdbd",
                    },
                    legend: {
                        display: false,
                    },
                    datalabels: {
                        formatter: function(value, context) {
                            return context.chart.data.labels[context.datasetIndex];
                        },
                        anchor: 'top',
                        padding: 10
                    },
                    tooltip: {
                        callbacks: {
                            title: function() {
                                return '';
                            },
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                return label+'(' + context.parsed.x + ' PF, ' + context.parsed.y + ' PA)';
                            }
                        }
                    }
                }
            },
            plugins: [quadrants,ChartDataLabels]
        });

    });
</script>

<style>
    #datatable-currentStats_wrapper, #datatable-statsAgainst_wrapper {
        max-width: 1100px;
    }
    #datatable-drafted_wrapper {
        max-width: 800px;
    }
    #datatable-optimal_wrapper {
        max-width: 1620px;
    }
</style>