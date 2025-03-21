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
                        $result = query("SELECT DISTINCT year FROM rosters ORDER BY year DESC");
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
                            <table class="stripe nowrap row-border order-column full-width" id="datatable-currentPoints">
                                <thead>
                                    <tr>
                                        <th>Manager</th>
                                        <?php
                                        foreach ($points as $manager => $values) {
                                            $headers = array_keys($values);
                                            $currentPointsColCount = count($headers);
                                            foreach ($headers as $header) {
                                                echo '<th>'.$header.'</th>';
                                            }
                                            break;
                                        }
                                        ?>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($points as $manager => $values) {
                                        $totalPoints = 0;

                                        foreach ($values as $pos => $stuff) {
                                            $totalPoints += $stuff['points'];
                                        } ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo $manager; ?></strong><br />
                                            </td>
                                            <?php foreach ($values as $pos => $stuff) { ?>
                                                <td data-order="<?php echo $stuff['points']; ?>">
                                                    <?php echo $stuff['points']; ?><br />
                                                </td>
                                            <?php } ?>
                                        
                                            <td data-order="<?php echo $totalPoints; ?>">
                                                <strong><?php echo $totalPoints; ?></strong><br />
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
                                    <?php
                                    foreach ($bestWeek as $manager => $values) {
                                        $headers = array_keys($values);
                                        $currentPointsColCount = count($headers);
                                        foreach ($headers as $header) {
                                            echo '<th>Top '.$header.'</th>';
                                        }
                                        break;
                                    }
                                    ?>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($bestWeek as $week => $players) { ?>
                                        <tr>
                                            <td><?php echo $week; ?></td>
                                            <?php foreach ($players as $pos => $stuff) {
                                                if ($pos != 'qb') { ?>
                                                    <td data-order="<?php echo $stuff['points']; ?>">
                                                        <strong><?php echo $stuff['manager']; ?></strong><br />
                                                        <?php echo $stuff['player']; ?><br />
                                                        <i><?php echo $stuff['points']. ' points'; ?></i>
                                                    </td>
                                                <?php }
                                            } ?>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
            <div class="row">
                <div class="col-sm-12 col-lg-6 table-padding">
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
                <div class="col-sm-12 col-lg-6 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">Stats by Week</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <table class="stripe nowrap row-border order-column" id="datatable-currentWeekStats">
                                <thead>
                                    <th>Manager</th>
                                    <th>Week</th>
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
                                    while ($row = fetch_array($weekStats)) { ?>
                                        <tr>
                                            <td><?php echo $row['manager']; ?></td>
                                            <td><?php echo $row['week']; ?></td>
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
                <div class="col-sm-12 col-lg-6 table-padding">
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
                <div class="col-sm-12 col-lg-6 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">Stats Against by Week</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <table class="stripe nowrap row-border order-column" id="datatable-weekStatsAgainst">
                                <thead>
                                    <th>Manager</th>
                                    <th>Week</th>
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
                                    foreach ($weekStatsAgainst as $row) { ?>
                                        <tr>
                                            <td><?php echo $row['manager']; ?></td>
                                            <td><?php echo $row['week']; ?></td>
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
                            <table class="stripe nowrap row-border order-column full-width" id="datatable-optimal">
                                <thead>
                                    <th>Manager</th>
                                    <th>Opponent</th>
                                    <th></th>
                                    <th>Week</th>
                                    <th>Actual Points</th>
                                    <th>Opponent Score</th>
                                    <th>Result</th>
                                    <th>Optimal Points</th>
                                    <th>Opponent Optimal</th>
                                    <th>Actual Margin</th>
                                    <th>Optimal Margin</th>
                                    <th>Accuracy</th>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12 col-md-6 col-lg-4 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">Worst Draft Picks</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <table class="stripe nowrap row-border order-column full-width" id="datatable-worstDraft">
                                <thead>
                                    <th>Manager</th>
                                    <th>Player</th>
                                    <th>Pick</th>
                                    <th>Points</th>
                                    <th>Avg Pick</th>
                                    <th>Avg Points</th>
                                    <th>Score</th>
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
                                            <td><?php echo round($row['avg_pick'], 1); ?></td>
                                            <td><?php echo round($row['median'], 1); ?></td>
                                            <td><?php echo round($row['score'], 1); ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-sm-12 col-md-6 col-lg-4 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">Best Draft Picks</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <table class="stripe nowrap row-border order-column full-width" id="datatable-bestDraft">
                                <thead>
                                    <th>Manager</th>
                                    <th>Player</th>
                                    <th>Pick</th>
                                    <th>Points</th>
                                    <th>Avg Pick</th>
                                    <th>Avg Points</th>
                                    <th>Score</th>
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
                                            <td><?php echo round($row['avg_pick'], 1); ?></td>
                                            <td><?php echo round($row['median'], 1); ?></td>
                                            <td><?php echo round($row['score'], 1); ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-sm-12 col-lg-4 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">Record Against Everyone</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <table class="stripe nowrap row-border order-column full-width" id="datatable-everyone">
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

                <div class="col-sm-12 col-lg-4 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">Lineup Accuracy</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <table class="stripe nowrap row-border order-column full-width" id="datatable-lineupAccuracy">
                                <thead>
                                    <th>Manager</th>
                                    <th>Points</th>
                                    <th>Optimal Points</th>
                                    <th>Accuracy</th>
                                </thead>
                                <tbody> </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12 col-lg-4 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">Points From Draft</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <table class="stripe nowrap row-border order-column full-width" id="datatable-drafted">
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
                
                <div class="col-sm-12 col-lg-4 table-padding">
                    <div class="card">
                        <div class="card-header">
                            <h4 style="float: right">Draft Performance</h4>
                        </div>
                        <div class="card-body" style="background: #fff; direction: ltr">
                            <table class="stripe nowrap row-border order-column full-width" id="datatable-draftPerformance">
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
                                            <td><?php echo $row['points'] ? round($row['points'], 1) : 0; ?></td>
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
                        <div class="card-body chart-block" style="direction: ltr;">
                            <canvas id="scatterChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12 table-padding">
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

<script src="/assets/dataTables-fixedColumns.min.js" type="text/javascript"></script>

<script type="text/javascript">
    $(document).ready(function() {

        let baseUrl = "<?php echo $BASE_URL; ?>";
        
        $('#year-select').change(function() {
            window.location = baseUrl+'currentSeason.php?id='+$('#year-select').val();
        });
        
        let currentPointsColCount = parseInt("<?php echo $currentPointsColCount; ?>");
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
                [currentPointsColCount+1, "desc"]
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

        $('#datatable-currentWeekStats').DataTable({
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

        $('#datatable-weekStatsAgainst').DataTable({
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
            ajax: {
                url: 'dataLookup.php',
                data: function (d) {
                    d.dataType = 'optimal-lineups';
                    d.season = $('#year-select').val();
                }
            },
            columns: [
                { data: 'manager' },
                { data: 'opponent' },
                { data: 'roster_link', sortable: false },
                { data: 'week' },
                { data: 'points' },
                { data: 'oppPoints' },
                { data: 'result' },
                { data: 'optimal' },
                { data: 'oppOptimal' },
                { data: 'margin' },
                { data: 'optimalMargin' },
                { data: 'accuracy' }
            ],
            order: [
                [4, "desc"]
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

        $('#datatable-lineupAccuracy').DataTable({
            scrollX: "100%",
            scrollCollapse: true,
            fixedColumns:   {
                leftColumns: 1
            },
            ajax: {
                url: 'dataLookup.php',
                data: function (d) {
                    d.dataType = 'lineup-accuracy';
                    d.season = $('#year-select').val();
                }
            },
            columns: [
                { data: 'manager' },
                { data: 'points' },
                { data: 'optimal' },
                { data: 'accuracy' }
            ],
            order: [
                [3, "desc"]
            ]
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
            order: [
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
            scrollX: "100%",
            scrollCollapse: true,
            fixedColumns:   {
                leftColumns: 1
            },
            searching: false,
            paging: false,
            info: false,
            columnDefs: [{
                targets: [4,5,6],
                visible: false
            }],
            order: [
                [6, "desc"]
            ]
        });

        $('#datatable-worstDraft').DataTable({
            scrollX: "100%",
            scrollCollapse: true,
            fixedColumns:   {
                leftColumns: 1
            },
            searching: false,
            paging: false,
            info: false,
            columnDefs: [{
                targets: [4,5,6],
                visible: false
            }],
            order: [
                [6, "asc"]
            ]
        });

        $('#datatable-everyone').DataTable({
            scrollX: "100%",
            scrollCollapse: true,
            fixedColumns:   {
                leftColumns: 1
            },
            searching: false,
            paging: false,
            info: false,
            order: [
                [3, "desc"]
            ]
        });

        $('#datatable-draftPerformance').DataTable({
            scrollX: "100%",
            scrollCollapse: true,
            fixedColumns:   {
                left: 1
            },
            order: [
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

        let weeks = <?php echo json_encode($weekStandings['weeks']); ?>;
        let managers = <?php echo json_encode($weekStandings['managers']); ?>;
        
        var ctx = $('#standingsChart');
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
        max-width: 1465px;
    }
</style>