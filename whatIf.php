<?php
    $pageName = "What If";
    include 'header.php';
    include 'sidebar.php';
    include 'data/whatIf.php';

    $managerColors = [
        1=>"#9c68d9", 2=>"#a6c6fa", 3=>"#3cf06e", 4=>"#f33c47",
        5=>"#c0f6e6", 6=>"#def89f", 7=>"#dca130", 8=>"#ff7f2c",
        9=>"#2dd4bf", 10=>"#f87598",
    ];

    $chartData        = getWhatIfChartData();
    $winLossData      = getWhatIfWinLoss();
    $winLossBySeason  = getWhatIfWinLossBySeason();
    $accuracyBySeason = getWhatIfChartDataBySeason();
    $scenarioData     = getPlayoffScenarios();
    $matchups      = $scenarioData['matchups'];
    $summary       = $scenarioData['summary'];

    // Build lookup for accuracy by year-manager
    $accuracyLookup = [];
    foreach ($accuracyBySeason as $data) {
        $accuracyLookup[$data['id'] . '-' . $data['year']] = $data['accuracy'];
    }

    // Sort accuracy chart data DESC
    $accuracyData = $chartData;
    usort($accuracyData, fn($a, $b) => $b['accuracy'] <=> $a['accuracy']);

    // Sort points-missed chart data DESC
    $missedData = $chartData;
    usort($missedData, fn($a, $b) => $b['points_missed'] <=> $a['points_missed']);

    // League averages for scatter quadrant lines
    $avgAccuracy = count($chartData) > 0
        ? array_sum(array_column($chartData, 'accuracy')) / count($chartData)
        : 92;
    $avgMissed = count($chartData) > 0
        ? array_sum(array_column($chartData, 'points_missed')) / count($chartData)
        : 0;

    // Summary sorted by total reversals DESC
    arsort($summary); // sorts by value — but value is array, need custom
    uasort($summary, fn($a, $b) => $b['total'] <=> $a['total']);

    $totalMatchups    = count($matchups);
    $reversalMatchups = count(array_filter($matchups, fn($m) => $m['any_reversal']));
?>
<style>
    #charts .card-body,
    #winloss .card-body {
        padding: 1.5rem 1.75rem;
    }
    .whatif-chart-container {
        position: relative;
        width: 100%;
    }
    .whatif-chart-container.bar-chart {
        height: 320px;
    }
    .whatif-chart-container.scatter-chart {
        height: 380px;
    }
    .scenario-summary-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 24px;
    }
    .scenario-manager-card {
        flex: 1 1 160px;
        max-width: 200px;
        background: #fff;
        border-radius: 6px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.08);
        overflow: hidden;
        border: 1px solid rgba(0,0,0,0.07);
    }
    .scenario-manager-card .card-mgr-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 8px 12px;
    }
    .scenario-manager-card .card-mgr-name {
        font-weight: 700;
        font-size: 0.88rem;
        color: #2c2c2c;
    }
    .scenario-manager-card .card-mgr-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 22px;
        height: 22px;
        border-radius: 999px;
        background: #e9ecef;
        color: #495057;
        font-size: 0.75rem;
        font-weight: 700;
        padding: 0 6px;
    }

    .scenario-manager-card .round-rows {
        padding: 6px 12px 10px;
    }
    .scenario-manager-card .round-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 3px 0;
        font-size: 0.8rem;
        border-bottom: 1px solid #f3f3f3;
        color: #555;
    }
    .scenario-manager-card .round-row:last-child {
        border-bottom: none;
    }
    .scenario-manager-card .round-label {
        font-weight: 500;
    }
    .scenario-manager-card .round-count {
        font-weight: 700;
        color: #2c2c2c;
    }
    .scenario-manager-card .round-count.count-zero {
        color: #adb5bd;
        font-weight: 400;
    }
    .flip-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: rgba(255,193,7,0.25);
        border: 1px solid rgba(255,193,7,0.5);
        color: #856404;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 2px 8px;
        border-radius: 4px;
        white-space: nowrap;
    }
    .reversal-row {
        background: rgba(255, 193, 7, 0.12) !important;
    }
    .reversal-row td {
        border-color: rgba(255,193,7,0.2) !important;
    }
    .table-scenario td, .table-scenario th {
        vertical-align: middle;
        font-size: 0.875rem;
    }
    .seed-badge {
        display: inline-block;
        width: 18px;
        height: 18px;
        border-radius: 3px;
        background: #e9ecef;
        color: #495057;
        font-size: 0.68rem;
        font-weight: 700;
        text-align: center;
        line-height: 18px;
        margin-left: 4px;
        vertical-align: middle;
    }
    .mgr-dot {
        display: inline-block;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        margin-right: 5px;
        vertical-align: middle;
        flex-shrink: 0;
    }
    .score-cell {
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }
    .optimal-val {
        color: #6c757d;
        font-size: 0.78rem;
    }
    .round-pill {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .round-pill.pill-final      { background: #fff3cd; color: #856404; }
    .round-pill.pill-semifinal  { background: #e8f4fd; color: #0c63e4; }
    .round-pill.pill-quarterfinal { background: #f0f0f0; color: #555; }
    .winner-name {
        font-weight: 600;
    }
    .scenario-note {
        font-size: 0.8rem;
        color: #6c757d;
        margin-top: 12px;
        padding: 8px 12px;
        background: #f8f9fa;
        border-left: 3px solid #ffc107;
        border-radius: 0 4px 4px 0;
    }
    .whatif-section-heading {
        font-size: 1rem;
        font-weight: 700;
        color: #3d3d3d;
        margin: 0 0 14px;
        padding-bottom: 8px;
        border-bottom: 2px solid #f0f0f0;
    }
    .avg-line-legend {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 0.78rem;
        color: #6c757d;
        margin-top: 6px;
    }
    .avg-line-legend .legend-dash {
        display: inline-block;
        width: 24px;
        height: 2px;
        border-top: 2px dashed #999;
    }
</style>

<div class="app-content content">
    <div class="content-wrapper">
        <div class="content-body">

            <div class="tab-buttons-container">
                <button class="tab-button active" id="winloss-tab" onclick="showCard('winloss')">Wins / Losses</button>
                <button class="tab-button" id="scenarios-tab" onclick="showCard('scenarios')">Playoff Scenarios</button>
                <button class="tab-button" id="charts-tab" onclick="showCard('charts')">Charts</button>
            </div>

            <!-- ===== CHARTS TAB ===== -->
            <div class="card-section" id="charts" style="display:none">
                <div class="row">

                    <!-- Lineup Accuracy -->
                    <div class="col-12 col-xl-6">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Lineup Accuracy</h4>
                            </div>
                            <div class="card-body">
                                <div class="whatif-chart-container bar-chart">
                                    <canvas id="accuracyChart"></canvas>
                                </div>
                                <div class="avg-line-legend">
                                    <span class="legend-dash"></span>
                                    League average: <?php echo round($avgAccuracy, 1); ?>%
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Points Missed -->
                    <div class="col-12 col-xl-6">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Points Missed</h4>
                            </div>
                            <div class="card-body">
                                <div class="whatif-chart-container bar-chart">
                                    <canvas id="missedChart"></canvas>
                                </div>
                                <div class="avg-line-legend">
                                    <span class="legend-dash"></span>
                                    League average: <?php echo number_format($avgMissed, 1); ?> pts
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Scatter -->
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-header" style="border-radius:12px 12px 0 0;">
                                <h4 class="card-title">Efficiency Correlation</h4>
                            </div>
                            <div class="card-body" style="border-radius:0 0 12px 12px;">
                                <div class="whatif-chart-container scatter-chart">
                                    <canvas id="scatterChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div><!-- /charts -->

            <!-- ===== SCENARIOS TAB ===== -->
            <div class="card-section" id="scenarios" style="display:none;" dir="ltr">

                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Playoff Matchups</h4>
                    </div>
                    <div class="card-body">

                        <p class="whatif-section-heading">How many times would an optimal lineup have changed their playoff result?</p>

                        <div class="scenario-summary-grid">
                            <?php foreach ($summary as $mgrName => $counts):
                                $mgrId = null;
                                foreach ($chartData as $m) {
                                    if ($m['name'] === $mgrName) { $mgrId = $m['id']; break; }
                                }
                                $color = $mgrId && isset($managerColors[$mgrId]) ? $managerColors[$mgrId] : '#ccc';
                            ?>
                            <div class="scenario-manager-card">
                                <div class="card-mgr-header">
                                    <span class="card-mgr-name"><?php echo htmlspecialchars($mgrName); ?></span>
                                    <span class="card-mgr-badge <?php echo $counts['total'] === 0 ? 'badge-zero' : ''; ?>">
                                        <?php echo $counts['total']; ?>
                                    </span>
                                </div>
                                <div class="round-rows">
                                    <?php foreach (['Quarterfinal' => 'QF', 'Semifinal' => 'SF', 'Final' => 'Final'] as $round => $label): ?>
                                    <div class="round-row">
                                        <span class="round-label"><?php echo $label; ?></span>
                                        <span class="round-count <?php echo $counts[$round] === 0 ? 'count-zero' : ''; ?>">
                                            <?php echo $counts[$round] > 0 ? $counts[$round] : '—'; ?>
                                        </span>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <p class="whatif-section-heading" style="margin-top:28px;">
                            Matchup Details
                            <span style="font-weight:400; font-size:0.85rem; color:#6c757d; margin-left:10px;">
                                <?php echo $reversalMatchups; ?> of <?php echo $totalMatchups; ?> matchups could have flipped
                            </span>
                        </p>

                        <div class="table-responsive">
                            <table class="table table-striped table-hover nowrap table-scenario">
                                <thead>
                                    <tr>
                                        <th>Year</th>
                                        <th>Round</th>
                                        <th>Manager 1</th>
                                        <th class="text-right">Score</th>
                                        <th class="text-right">Optimal</th>
                                        <th>Manager 2</th>
                                        <th class="text-right">Score</th>
                                        <th class="text-right">Optimal</th>
                                        <th>Would Flip</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($matchups as $m):
                                        if (!$m['any_reversal']) continue;
                                        $m1Won = $m['actual_winner'] === $m['manager1'];
                                        $flipManagers = [];
                                        if ($m['m1_reversal']) $flipManagers[] = htmlspecialchars($m['manager1']);
                                        if ($m['m2_reversal']) $flipManagers[] = htmlspecialchars($m['manager2']);
                                    ?>
                                    <tr class="reversal-row">
                                        <td><strong><?php echo $m['year']; ?></strong></td>
                                        <td><?php echo htmlspecialchars($m['round']); ?></td>
                                        <td>
                                            <span class="badge <?php echo $m1Won ? 'badge-primary' : 'badge-secondary'; ?>">
                                                <?php echo htmlspecialchars($m['manager1']); ?>
                                                <?php if ($m['m1_seed'] > 0): ?><span class="seed"><?php echo $m['m1_seed']; ?></span><?php endif; ?>
                                            </span>
                                        </td>
                                        <td class="text-right score-cell">
                                            <?php echo number_format($m['m1_score'], 2); ?>
                                        </td>
                                        <td class="text-right score-cell">
                                            <span class="optimal-val"><?php echo number_format($m['m1_optimal'], 2); ?></span>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo !$m1Won ? 'badge-primary' : 'badge-secondary'; ?>">
                                                <?php echo htmlspecialchars($m['manager2']); ?>
                                                <?php if ($m['m2_seed'] > 0): ?><span class="seed"><?php echo $m['m2_seed']; ?></span><?php endif; ?>
                                            </span>
                                        </td>
                                        <td class="text-right score-cell">
                                            <?php echo number_format($m['m2_score'], 2); ?>
                                        </td>
                                        <td class="text-right score-cell">
                                            <span class="optimal-val"><?php echo number_format($m['m2_optimal'], 2); ?></span>
                                        </td>
                                        <td>
                                            <span class="flip-badge">
                                                &#10003; <?php echo implode(' &amp; ', $flipManagers); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="scenario-note">
                            * A Quarterfinal flip is hypothetical — we don't know how that manager would have performed in later rounds since we don't have their roster data for weeks they didn't play.
                        </div>

                    </div>
                </div>
            </div><!-- /scenarios -->

            <!-- ===== WINS/LOSSES TAB ===== -->
            <div class="card-section" id="winloss" style="display:block;" dir="ltr">
                <div class="row">

                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Win / Loss Impact</h4>
                            </div>
                            <div class="card-body">
                                <p class="whatif-section-heading">Regular Season Record Under Each Scenario</p>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover nowrap table-scenario">
                                        <thead>
                                            <tr>
                                                <th>Manager</th>
                                                <th class="text-center">Actual<br><small class="text-muted">W&nbsp;–&nbsp;L</small></th>
                                                <th class="text-center">I Play Optimal<br><small class="text-muted">W&nbsp;–&nbsp;L&nbsp;&nbsp;Δ</small></th>
                                                <th class="text-center">Opp Plays Optimal<br><small class="text-muted">W&nbsp;–&nbsp;L&nbsp;&nbsp;Δ</small></th>
                                                <th class="text-center">Both Play Optimal<br><small class="text-muted">W&nbsp;–&nbsp;L&nbsp;&nbsp;Δ</small></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($winLossData as $m):
                                                $mgrColor = isset($managerColors[$m['id']]) ? $managerColors[$m['id']] : '#ccc';
                                                $fmtDelta = function(int $d): string {
                                                    if ($d > 0) return '<span style="color:#1a7a3a;font-weight:700">+' . $d . '</span>';
                                                    if ($d < 0) return '<span style="color:#c0392b;font-weight:700">' . $d . '</span>';
                                                    return '<span style="color:#adb5bd">0</span>';
                                                };
                                            ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($m['name']); ?></strong>
                                                </td>
                                                <td class="text-center score-cell">
                                                    <?php echo $m['actual_wins']; ?>&nbsp;–&nbsp;<?php echo $m['actual_losses']; ?>
                                                </td>
                                                <td class="text-center score-cell">
                                                    <?php echo $m['self_opt_wins']; ?>&nbsp;–&nbsp;<?php echo $m['self_opt_losses']; ?>
                                                    &nbsp;&nbsp;<?php echo $fmtDelta($m['self_opt_delta']); ?>
                                                </td>
                                                <td class="text-center score-cell">
                                                    <?php echo $m['opp_opt_wins']; ?>&nbsp;–&nbsp;<?php echo $m['opp_opt_losses']; ?>
                                                    &nbsp;&nbsp;<?php echo $fmtDelta($m['opp_opt_delta']); ?>
                                                </td>
                                                <td class="text-center score-cell">
                                                    <?php echo $m['both_opt_wins']; ?>&nbsp;–&nbsp;<?php echo $m['both_opt_losses']; ?>
                                                    &nbsp;&nbsp;<?php echo $fmtDelta($m['both_opt_delta']); ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="scenario-note" style="margin-top:10px;">
                                    "I Play Optimal" assumes you always set your best possible lineup while your opponent plays their actual lineup, and vice versa. "Both" means both sides always set optimal.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Win Delta by Scenario</h4>
                            </div>
                            <div class="card-body">
                                <div class="whatif-chart-container" style="height:340px;">
                                    <canvas id="winLossChart"></canvas>
                                </div>
                                <div style="display:flex;gap:18px;margin-top:8px;font-size:0.78rem;color:#6c757d;flex-wrap:wrap;">
                                    <span><span style="display:inline-block;width:12px;height:12px;background:#4caf50;border-radius:2px;margin-right:4px;vertical-align:middle;"></span>I Play Optimal</span>
                                    <span><span style="display:inline-block;width:12px;height:12px;background:#ef5350;border-radius:2px;margin-right:4px;vertical-align:middle;"></span>Opp Plays Optimal</span>
                                    <span><span style="display:inline-block;width:12px;height:12px;background:#5c6bc0;border-radius:2px;margin-right:4px;vertical-align:middle;"></span>Both Play Optimal</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Season-by-Season Breakdown</h4>
                            </div>
                            <div class="card-body" style="padding: 0;">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover nowrap table-scenario" id="seasonBreakdownTable">
                                        <thead>
                                            <tr>
                                                <th>Year</th>
                                                <th>Manager</th>
                                                <th class="text-center">Actual<br><small class="text-muted">W&nbsp;–&nbsp;L</small></th>
                                                <th class="text-center">I Play Optimal<br><small class="text-muted">W&nbsp;–&nbsp;L&nbsp;&nbsp;Δ</small></th>
                                                <th class="text-center">Opp Plays Optimal<br><small class="text-muted">W&nbsp;–&nbsp;L&nbsp;&nbsp;Δ</small></th>
                                                <th class="text-center">Both Play Optimal<br><small class="text-muted">W&nbsp;–&nbsp;L&nbsp;&nbsp;Δ</small></th>
                                                <th class="text-center">Lineup Accuracy<br><small class="text-muted">%</small></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($winLossBySeason as $m):
                                                $fmtDelta = function(int $d): string {
                                                    if ($d > 0) return '<span style="color:#1a7a3a;font-weight:700">+' . $d . '</span>';
                                                    if ($d < 0) return '<span style="color:#c0392b;font-weight:700">' . $d . '</span>';
                                                    return '<span style="color:#adb5bd">0</span>';
                                                };
                                                $accuracyKey = $m['id'] . '-' . $m['year'];
                                                $accuracy = $accuracyLookup[$accuracyKey] ?? 0;
                                            ?>
                                            <tr>
                                                <td><strong><?php echo $m['year']; ?></strong></td>
                                                <td><strong><?php echo htmlspecialchars($m['name']); ?></strong></td>
                                                <td class="text-center score-cell">
                                                    <?php echo $m['actual_wins']; ?>&nbsp;–&nbsp;<?php echo $m['actual_losses']; ?>
                                                </td>
                                                <td class="text-center score-cell" data-sort="<?php echo $m['self_opt_delta']; ?>">
                                                    <?php echo $m['self_opt_wins']; ?>&nbsp;–&nbsp;<?php echo $m['self_opt_losses']; ?>
                                                    &nbsp;&nbsp;<?php echo $fmtDelta($m['self_opt_delta']); ?>
                                                </td>
                                                <td class="text-center score-cell" data-sort="<?php echo $m['opp_opt_delta']; ?>">
                                                    <?php echo $m['opp_opt_wins']; ?>&nbsp;–&nbsp;<?php echo $m['opp_opt_losses']; ?>
                                                    &nbsp;&nbsp;<?php echo $fmtDelta($m['opp_opt_delta']); ?>
                                                </td>
                                                <td class="text-center score-cell" data-sort="<?php echo $m['both_opt_delta']; ?>">
                                                    <?php echo $m['both_opt_wins']; ?>&nbsp;–&nbsp;<?php echo $m['both_opt_losses']; ?>
                                                    &nbsp;&nbsp;<?php echo $fmtDelta($m['both_opt_delta']); ?>
                                                </td>
                                                <td class="text-center score-cell" data-sort="<?php echo $accuracy; ?>">
                                                    <?php echo number_format($accuracy, 1); ?>%
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div><!-- /winloss -->

        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<script>
(function () {
    var managerColors = <?php echo json_encode($managerColors); ?>;

    // ── Accuracy Chart ──────────────────────────────────────────────
    var accuracyLabels  = <?php echo json_encode(array_column($accuracyData, 'name')); ?>;
    var accuracyValues  = <?php echo json_encode(array_column($accuracyData, 'accuracy')); ?>;
    var accuracyIds     = <?php echo json_encode(array_column($accuracyData, 'id')); ?>;
    var accuracyColors  = accuracyIds.map(function(id) { return managerColors[id] || '#ccc'; });
    var avgAccuracy     = <?php echo round($avgAccuracy, 2); ?>;

    var avgLinePlugin = {
        id: 'avgLine',
        afterDraw: function(chart) {
            if (chart.canvas.id !== 'accuracyChart') return;
            var ctx   = chart.ctx;
            var xAxis = chart.scales.x;
            var xPx   = xAxis.getPixelForValue(avgAccuracy);
            var top   = chart.chartArea.top;
            var bottom= chart.chartArea.bottom;
            ctx.save();
            ctx.beginPath();
            ctx.setLineDash([5, 4]);
            ctx.strokeStyle = 'rgba(120,120,120,0.7)';
            ctx.lineWidth = 1.5;
            ctx.moveTo(xPx, top);
            ctx.lineTo(xPx, bottom);
            ctx.stroke();
            ctx.restore();
        }
    };

    new Chart(document.getElementById('accuracyChart'), {
        type: 'bar',
        plugins: [avgLinePlugin],
        data: {
            labels: accuracyLabels,
            datasets: [{
                data: accuracyValues,
                backgroundColor: accuracyColors,
                borderRadius: 3,
                barThickness: 32,
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            return ' ' + ctx.parsed.x.toFixed(1) + '%';
                        }
                    }
                }
            },
            scales: {
                x: {
                    min: 85,
                    max: 100,
                    ticks: {
                        callback: function(v) { return v + '%'; },
                        font: { size: 11 }
                    },
                    grid: { color: 'rgba(0,0,0,0.05)' }
                },
                y: {
                    ticks: { font: { size: 12, weight: '600' } },
                    grid: { display: false }
                }
            }
        }
    });

    // ── Points Missed Chart ─────────────────────────────────────────
    var missedLabels = <?php echo json_encode(array_column($missedData, 'name')); ?>;
    var missedValues = <?php echo json_encode(array_column($missedData, 'points_missed')); ?>;
    var missedIds    = <?php echo json_encode(array_column($missedData, 'id')); ?>;
    var missedColors = missedIds.map(function(id) { return managerColors[id] || '#ccc'; });
    var avgMissedVal = <?php echo round($avgMissed, 2); ?>;

    var avgMissedLinePlugin = {
        id: 'avgMissedLine',
        afterDraw: function(chart) {
            var ctx   = chart.ctx;
            var xAxis = chart.scales.x;
            var xPx   = xAxis.getPixelForValue(avgMissedVal);
            var top   = chart.chartArea.top;
            var bottom= chart.chartArea.bottom;
            ctx.save();
            ctx.beginPath();
            ctx.setLineDash([5, 4]);
            ctx.strokeStyle = 'rgba(120,120,120,0.7)';
            ctx.lineWidth = 1.5;
            ctx.moveTo(xPx, top);
            ctx.lineTo(xPx, bottom);
            ctx.stroke();
            ctx.restore();
        }
    };

    new Chart(document.getElementById('missedChart'), {
        type: 'bar',
        plugins: [avgMissedLinePlugin],
        data: {
            labels: missedLabels,
            datasets: [{
                data: missedValues,
                backgroundColor: missedColors,
                borderRadius: 3,
                barThickness: 32,
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            return ' ' + ctx.parsed.x.toLocaleString(undefined, {minimumFractionDigits:1, maximumFractionDigits:1}) + ' pts';
                        }
                    }
                }
            },
            scales: {
                x: {
                    ticks: {
                        callback: function(v) {
                            return v >= 1000 ? (v/1000).toFixed(0) + 'k' : v;
                        },
                        font: { size: 11 }
                    },
                    grid: { color: 'rgba(0,0,0,0.05)' }
                },
                y: {
                    ticks: { font: { size: 12, weight: '600' } },
                    grid: { display: false }
                }
            }
        }
    });

    // ── Scatter Chart ───────────────────────────────────────────────
    var scatterRaw  = <?php echo json_encode($chartData); ?>;
    var avgAcc      = <?php echo round($avgAccuracy, 2); ?>;
    var avgMissed   = <?php echo round($avgMissed, 2); ?>;

    var scatterDatasets = scatterRaw.map(function(m) {
        return {
            label: m.name,
            data: [{ x: m.accuracy, y: m.points_missed }],
            backgroundColor: managerColors[m.id] || '#ccc',
            borderColor: 'rgba(0,0,0,0.2)',
            borderWidth: 1,
            pointRadius: 10,
            pointHoverRadius: 13,
        };
    });

    var nameLabelPlugin = {
        id: 'nameLabels',
        afterDatasetsDraw: function(chart) {
            var ctx = chart.ctx;
            ctx.save();
            chart.data.datasets.forEach(function(ds, i) {
                var meta = chart.getDatasetMeta(i);
                if (!meta.visible) return;
                meta.data.forEach(function(pt) {
                    ctx.font = 'bold 11px sans-serif';
                    ctx.fillStyle = '#3d3d3d';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'bottom';
                    ctx.fillText(ds.label, pt.x, pt.y - 14);
                });
            });
            ctx.restore();
        }
    };

    var quadrantPlugin = {
        id: 'quadrants',
        beforeDraw: function(chart) {
            var ctx    = chart.ctx;
            var xAxis  = chart.scales.x;
            var yAxis  = chart.scales.y;
            var xPx    = xAxis.getPixelForValue(avgAcc);
            var yPx    = yAxis.getPixelForValue(avgMissed);
            var area   = chart.chartArea;

            ctx.save();
            ctx.setLineDash([4, 4]);
            ctx.strokeStyle = 'rgba(150,150,150,0.4)';
            ctx.lineWidth = 1;

            ctx.beginPath();
            ctx.moveTo(xPx, area.top);
            ctx.lineTo(xPx, area.bottom);
            ctx.stroke();

            ctx.beginPath();
            ctx.moveTo(area.left, yPx);
            ctx.lineTo(area.right, yPx);
            ctx.stroke();

            ctx.setLineDash([]);
            ctx.font = '10px sans-serif';
            ctx.fillStyle = 'rgba(150,150,150,0.6)';
            ctx.textAlign = 'left';
            ctx.fillText('More Missed', area.left + 4, area.top + 13);
            ctx.fillText('Less Missed', area.left + 4, area.bottom - 5);
            ctx.restore();
        }
    };

    new Chart(document.getElementById('scatterChart'), {
        type: 'scatter',
        plugins: [quadrantPlugin, nameLabelPlugin],
        data: { datasets: scatterDatasets },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            return ctx.dataset.label + ': ' + ctx.parsed.x.toFixed(1) + '% acc, ' + ctx.parsed.y.toLocaleString(undefined, {maximumFractionDigits:0}) + ' pts missed';
                        }
                    }
                }
            },
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Lineup Accuracy (%)',
                        font: { size: 11 }
                    },
                    ticks: {
                        callback: function(v) { return v + '%'; },
                        font: { size: 11 }
                    },
                    grid: { color: 'rgba(0,0,0,0.05)' }
                },
                y: {
                    suggestedMax: Math.max.apply(null, scatterRaw.map(function(m) { return m.points_missed; })) * 1.03,
                    title: {
                        display: true,
                        text: 'Total Points Missed',
                        font: { size: 11 }
                    },
                    ticks: {
                        callback: function(v) {
                            return v >= 1000 ? (v/1000).toFixed(1) + 'k' : v;
                        },
                        font: { size: 11 }
                    },
                    grid: { color: 'rgba(0,0,0,0.05)' }
                }
            }
        }
    });

    // ── Win/Loss Delta Chart ────────────────────────────────────────
    var winLossRaw = <?php echo json_encode($winLossData); ?>;
    // Sort by self_opt_delta DESC for the chart (same as table)
    winLossRaw.sort(function(a, b) { return b.self_opt_delta - a.self_opt_delta; });

    var wlLabels      = winLossRaw.map(function(m) { return m.name; });
    var wlSelfDeltas  = winLossRaw.map(function(m) { return m.self_opt_delta; });
    var wlOppDeltas   = winLossRaw.map(function(m) { return m.opp_opt_delta; });
    var wlBothDeltas  = winLossRaw.map(function(m) { return m.both_opt_delta; });

    new Chart(document.getElementById('winLossChart'), {
        type: 'bar',
        data: {
            labels: wlLabels,
            datasets: [
                {
                    label: 'I Play Optimal',
                    data: wlSelfDeltas,
                    backgroundColor: 'rgba(76,175,80,0.8)',
                    borderRadius: 3,
                },
                {
                    label: 'Opp Plays Optimal',
                    data: wlOppDeltas,
                    backgroundColor: 'rgba(239,83,80,0.8)',
                    borderRadius: 3,
                },
                {
                    label: 'Both Play Optimal',
                    data: wlBothDeltas,
                    backgroundColor: 'rgba(92,107,192,0.8)',
                    borderRadius: 3,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            var v = ctx.parsed.y;
                            return ' ' + ctx.dataset.label + ': ' + (v > 0 ? '+' : '') + v + ' wins';
                        }
                    }
                }
            },
            scales: {
                x: {
                    ticks: { font: { size: 11, weight: '600' } },
                    grid: { display: false }
                },
                y: {
                    title: { display: true, text: 'Change in Wins', font: { size: 11 } },
                    ticks: {
                        callback: function(v) { return (v > 0 ? '+' : '') + v; },
                        font: { size: 11 }
                    },
                    grid: { color: 'rgba(0,0,0,0.05)' }
                }
            }
        }
    });

    // ── Season Breakdown DataTable ──────────────────────────────────
    document.addEventListener('DOMContentLoaded', function() {
        if (!$.fn.dataTable.isDataTable('#seasonBreakdownTable')) {
            $('#seasonBreakdownTable').DataTable({
                paging: true,
                pageLength: 10,
                lengthChange: false,
                searching: false,
                ordering: true,
                info: true,
                autoWidth: false,
                dom: 'tp',
                order: [[3, 'desc']]
            });
        }
    });

    // ── URL hash tab restore ────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof activateTabFromUrlHash === 'function') {
            activateTabFromUrlHash();
        } else if (window.location.hash) {
            var tab = window.location.hash.replace('#', '');
            if (tab && document.getElementById(tab)) {
                showCard(tab, false);
            }
        }
    });
}());
</script>
