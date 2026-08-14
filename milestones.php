<?php
    $pageName = "Milestones";
    include 'header.php';
    include 'sidebar.php';
    include 'data/milestones.php';

    // Per-manager color palette, matches Current Season > Charts tab (functions.php getSeasonStandings).
    // Indexed by manager id (1..10).
    $managerColors = [
        1  => "#9c68d9",
        2  => "#a6c6fa",
        3  => "#3cf06e",
        4  => "#f33c47",
        5  => "#c0f6e6",
        6  => "#def89f",
        7  => "#dca130",
        8  => "#ff7f2c",
        9  => "#2dd4bf",
        10 => "#f87598",
    ];

    $milestoneTotals = getMilestoneTotals(); // [spec_id => ['spec' => …, 'top5' => …]]
    $alerts          = getCareerPointsAlerts();
    $scorigami       = getScorigamiData();

    // Scorigami grid: dense list of every integer score in range, and a
    // count-bucket (0-4) per cell for CSS shading.
    $sgValues  = range($scorigami['min'], $scorigami['max']);
    $sgCellMap = [];
    foreach ($scorigami['cells'] as $c) {
        $sgCellMap[$c['win_score'] . '-' . $c['lose_score']] = $c;
    }
    function _scorigamiBucket($count)
    {
        if ($count <= 0) return 0;
        if ($count >= 4) return 4;
        return $count;
    }

    // rosters.php resolves a matchup from any one of its two managers'
    // names, so either side of the pairing works here.
    function _scorigamiRosterLink($year, $week, $managerName)
    {
        return '/rosters.php?year=' . $year . '&week=' . $week . '&manager=' . rawurlencode($managerName);
    }

    // Group charts by tab, preserving spec order.
    $tabs = [
        'regular-season' => ['label' => 'Regular Season', 'charts' => []],
        'postseason'     => ['label' => 'Postseason',     'charts' => []],
    ];
    foreach ($milestoneTotals as $specId => $bundle) {
        $spec = $bundle['spec'];
        $tabs[$spec['tab']]['charts'][] = [
            'spec_id' => $specId,
            'chartId' => 'milestone-' . $specId,
            'title'   => $spec['title'],
            'unit'    => $spec['unit'],
            'tiers'   => $spec['tiers'],
            'rows'    => $bundle['top5'],
        ];
    }
?>
<style>
    .tier-legend {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.5rem;
        margin: 0 0 0.5rem;
    }
    .tier-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.15rem 0.7rem;
        border-radius: 999px;
        background: #fff;
        border: 1px solid rgba(0,0,0,0.12);
        font-size: 0.78rem;
        font-weight: 600;
        color: #000;
        letter-spacing: 0.02em;
        line-height: 1.4;
    }
    .tier-chip .tier-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
        background: rgba(0,0,0,0.55);
    }
    .tier-chip .tier-label { text-transform: uppercase; color: #000; }
    .tier-chip .tier-value { color: rgba(0,0,0,0.7); font-weight: 500; }
    .milestone-chart-wrapper {
        position: relative;
        width: 100%;
        height: 360px;
        background: #fff;
        border-radius: 6px;
        padding: 12px;
    }
    @media (max-width: 600px) {
        .milestone-chart-wrapper { height: 380px; }
    }
    .alert-list {
        display: flex;
        flex-direction: column;
        gap: 0.6rem;
    }
    .alert-item {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        padding: 0.85rem 1rem;
        background: #fff;
        border: 1px solid rgba(0,0,0,0.08);
        border-left-width: 5px;
        border-radius: 6px;
        color: #000;
    }
    .alert-badge {
        flex: 0 0 auto;
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        padding: 0.25rem 0.55rem;
        border-radius: 4px;
        text-transform: uppercase;
        white-space: nowrap;
        color: #000;
    }
    .alert-badge.recent       { background: #2eb82e; }
    .alert-badge.first        { background: #f59e0b; }
    .alert-badge.first-recent { background: #9c68d9; }
    .alert-body { flex: 1 1 auto; min-width: 0; }
    .alert-text {
        font-size: 1rem;
        font-weight: 600;
        line-height: 1.3;
        color: #000;
    }
    .alert-when {
        margin-top: 2px;
        font-size: 0.8rem;
        color: rgba(0,0,0,0.6);
    }
    .alert-place {
        flex: 0 0 auto;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        padding: 0.25rem 0.6rem;
        border-radius: 4px;
        text-transform: uppercase;
        white-space: nowrap;
        background: rgba(0,0,0,0.07);
        color: rgba(0,0,0,0.5);
    }
    .alert-place.place-first {
        background: #f59e0b;
        color: #000;
    }
    .alert-empty {
        padding: 1rem;
        background: #fff;
        border-radius: 6px;
        color: rgba(0,0,0,0.7);
        font-style: italic;
    }
    .manager-legend {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 0.4rem 0.5rem;
        margin: 0 0 1rem;
    }
    .manager-chip {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 1 auto;
        padding: 0.35rem 0.85rem;
        border-radius: 999px;
        font-size: 0.85rem;
        font-weight: 700;
        color: #000;
        border: 2px solid transparent;
        white-space: nowrap;
        line-height: 1.2;
        cursor: pointer;
        transition: opacity 0.15s ease, transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
    }
    .manager-chip:hover { transform: translateY(-1px); box-shadow: 0 2px 6px rgba(0,0,0,0.25); }
    .manager-chip.selected {
        border-color: #000;
        box-shadow: 0 0 0 2px rgba(255,255,255,0.85), 0 2px 8px rgba(0,0,0,0.35);
    }
    .manager-chip.faded { opacity: 0.35; }
    .manager-chip.faded:hover { opacity: 0.6; }
    @media (max-width: 900px) {
        .manager-chip { font-size: 0.78rem; padding: 0.3rem 0.7rem; }
    }
    @media (max-width: 600px) {
        .manager-chip { font-size: 0.72rem; padding: 0.25rem 0.6rem; }
    }
    .sg-intro {
        color: rgba(0,0,0,0.7);
        font-size: 0.9rem;
        margin: 0 0 0.75rem;
    }
    .sg-detail {
        background: #fff;
        border: 1px solid rgba(0,0,0,0.08);
        border-radius: 6px;
        padding: 0.75rem 1rem;
        margin: 0 0 0.75rem;
        color: rgba(0,0,0,0.6);
        font-size: 0.85rem;
    }
    .sg-detail-title { color: #000; font-weight: 700; margin-bottom: 0.35rem; }
    .sg-detail-list { margin: 0; padding-left: 1.1rem; color: #000; }
    .sg-detail-list li { margin: 0.15rem 0; }
    .sg-wrapper {
        overflow: auto;
        max-height: 65vh;
        border: 1px solid rgba(0,0,0,0.1);
        border-radius: 6px;
        background: #fff;
    }
    .sg-table { border-collapse: collapse; font-size: 0.62rem; }
    .sg-table th, .sg-table td {
        width: 20px; min-width: 20px; height: 20px;
        text-align: center; padding: 0;
        border: 1px solid rgba(0,0,0,0.06);
        white-space: nowrap;
    }
    .sg-corner, .sg-col-head, .sg-row-head {
        position: sticky;
        background: #fff;
        color: #000;
        font-weight: 700;
    }
    .sg-col-head { top: 0; z-index: 2; }
    .sg-row-head { left: 0; z-index: 2; padding: 0 4px; }
    .sg-corner { top: 0; left: 0; z-index: 3; font-size: 0.55rem; color: rgba(0,0,0,0.5); }
    .sg-cell { cursor: default; }
    .sg-cell[data-key] { cursor: pointer; }
    .sg-cell[data-key]:hover { outline: 2px solid #000; outline-offset: -2px; }
    .sg-cell.sg-c0 { background: #fff; }
    .sg-cell.sg-c1 { background: #b7d3f6; }
    .sg-cell.sg-c2 { background: #6da7ec; }
    .sg-cell.sg-c3 { background: #2a78d6; }
    .sg-cell.sg-c4 { background: #184f95; }
    .sg-invalid {
        background: repeating-linear-gradient(
            45deg, rgba(0,0,0,0.03), rgba(0,0,0,0.03) 4px,
            rgba(0,0,0,0.06) 4px, rgba(0,0,0,0.06) 8px
        );
    }
    .sg-swatch {
        width: 12px; height: 12px; border-radius: 3px;
        border: 1px solid rgba(0,0,0,0.15);
    }
    .sg-swatch.sg-c0 { background: #fff; }
    .sg-swatch.sg-c1 { background: #b7d3f6; }
    .sg-swatch.sg-c2 { background: #6da7ec; }
    .sg-swatch.sg-c3 { background: #2a78d6; }
    .sg-swatch.sg-c4 { background: #184f95; }
    .sg-outliers {
        margin-top: 0.85rem;
        padding: 0.75rem 1rem;
        background: #fff;
        border: 1px solid rgba(0,0,0,0.08);
        border-radius: 6px;
        color: #000;
    }
    .sg-outliers-title { font-weight: 700; margin-bottom: 0.5rem; }
    .sg-recent {
        margin-top: 0.85rem;
        padding: 0.75rem 1rem;
        background: #fff;
        border: 1px solid rgba(0,0,0,0.08);
        border-radius: 6px;
        color: #000;
    }
    .sg-recent-title { font-weight: 700; }
    .sg-recent-sub { margin: 0.15rem 0 0.6rem; font-size: 0.82rem; color: rgba(0,0,0,0.6); }
</style>
<div class="app-content content">
    <div class="content-wrapper">
        <div class="content-body">
            <div class="row">
                <div class="col-sm-12">

                    <div class="tab-buttons-container">
                        <button class="tab-button active" id="alerts-tab"
                                onclick="showMilestoneTab('alerts')">
                            Alerts
                        </button>
                        <?php foreach ($tabs as $tabId => $tab): ?>
                            <button class="tab-button"
                                    id="<?php echo $tabId; ?>-tab"
                                    onclick="showMilestoneTab('<?php echo $tabId; ?>')">
                                <?php echo htmlspecialchars($tab['label']); ?>
                            </button>
                        <?php endforeach; ?>
                        <button class="tab-button" id="scorigami-tab"
                                onclick="showMilestoneTab('scorigami')">
                            Scorigami
                        </button>
                    </div>

                    <div>
                        <!-- ── Alerts (default) ── -->
                        <div class="row card-section" id="alerts">
                            <div class="col-sm-12">
                                <div class="card milestone-card" style="border-top-left-radius: 0;">
                                    <div class="card-header">
                                        <h4 class="card-title">Milestone Alerts</h4>
                                    </div>
                                    <div class="card-body" style="direction: ltr;">
                                        <?php if (empty($alerts)): ?>
                                            <div class="alert-empty">No milestone crossings to report yet.</div>
                                        <?php else: ?>
                                            <div class="alert-list">
                                                <?php foreach ($alerts as $a):
                                                    $color  = $managerColors[$a['manager_id']] ?? '#9c68d9';
                                                    $label  = $a['type'] === 'recent' ? 'Recent'
                                                            : ($a['type'] === 'first-recent' ? 'First · Recent' : 'First');
                                                ?>
                                                <div class="alert-item" style="border-left-color: <?php echo $color; ?>;">
                                                    <span class="alert-badge <?php echo $a['type']; ?>"><?php echo $label; ?></span>
                                                    <div class="alert-body">
                                                        <div class="alert-text"><?php echo htmlspecialchars($a['text']); ?></div>
                                                        <div class="alert-when"><?php echo htmlspecialchars($a['when']); ?></div>
                                                    </div>
                                                    <?php if (!empty($a['place'])): ?>
                                                    <span class="alert-place<?php echo $a['place'] === 1 ? ' place-first' : ''; ?>">
                                                        <?php echo _milestoneOrdinal($a['place']); ?>
                                                    </span>
                                                    <?php endif; ?>
                                                </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ── Chart tabs ── -->
                        <?php foreach ($tabs as $tabId => $tab): ?>
                        <div class="row card-section" id="<?php echo $tabId; ?>" style="display: none;">
                            <div class="col-sm-12">
                                <div class="manager-legend">
                                    <?php foreach ($managerColors as $mid => $color):
                                        $name = getManagerName($mid);
                                    ?>
                                        <span class="manager-chip" data-mid="<?php echo $mid; ?>"
                                              style="background: <?php echo $color; ?>;">
                                            <?php echo htmlspecialchars($name); ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                                <?php foreach ($tab['charts'] as $c): ?>
                                <div class="card milestone-card" style="margin-bottom: 1.25rem;">
                                    <div class="card-header">
                                        <h4 class="card-title"><?php echo htmlspecialchars($c['title']); ?></h4>
                                    </div>
                                    <div class="card-body" style="direction: ltr;">
                                        <div class="tier-legend">
                                            <?php foreach ($c['tiers'] as $idx => $tv): ?>
                                                <span class="tier-chip">
                                                    <span class="tier-dot"></span>
                                                    <span class="tier-label">Tier <?php echo $idx + 1; ?></span>
                                                    <span class="tier-value"><?php echo number_format($tv); ?></span>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                        <div class="milestone-chart-wrapper">
                                            <canvas id="<?php echo $c['chartId']; ?>"></canvas>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>

                        <!-- ── Scorigami ── -->
                        <div class="row card-section" id="scorigami" style="display: none;">
                            <div class="col-sm-12">
                                <div class="card milestone-card">
                                    <div class="card-header">
                                        <h4 class="card-title">Scorigami</h4>
                                    </div>
                                    <div class="card-body" style="direction: ltr;">
                                        <p class="sg-intro">
                                            Every regular-season matchup, plotted by winning score (rows) vs.
                                            losing score (columns), rounded to the nearest point. Click a cell
                                            to see when it happened.
                                        </p>
                                        <div class="tier-legend">
                                            <span class="tier-chip"><span class="tier-dot sg-swatch sg-c0"></span><span class="tier-label">Never</span></span>
                                            <span class="tier-chip"><span class="tier-dot sg-swatch sg-c1"></span><span class="tier-label">1x</span></span>
                                            <span class="tier-chip"><span class="tier-dot sg-swatch sg-c2"></span><span class="tier-label">2x</span></span>
                                            <span class="tier-chip"><span class="tier-dot sg-swatch sg-c3"></span><span class="tier-label">3x</span></span>
                                            <span class="tier-chip"><span class="tier-dot sg-swatch sg-c4"></span><span class="tier-label">4+x</span></span>
                                        </div>
                                        <div id="sg-detail" class="sg-detail">Click a cell in the grid to see the matchups behind it.</div>
                                        <div class="sg-wrapper">
                                            <table class="sg-table">
                                                <thead>
                                                    <tr>
                                                        <th class="sg-corner">W \ L</th>
                                                        <?php foreach ($sgValues as $l): ?>
                                                            <th class="sg-col-head"><?php echo $l; ?></th>
                                                        <?php endforeach; ?>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php $sgN = count($sgValues); ?>
                                                    <?php for ($i = 0; $i < $sgN; $i++):
                                                        $w = $sgValues[$i];
                                                    ?>
                                                    <tr>
                                                        <th class="sg-row-head"><?php echo $w; ?></th>
                                                        <?php for ($j = 0; $j <= $i; $j++):
                                                            $l     = $sgValues[$j];
                                                            $key   = $w . '-' . $l;
                                                            $cell  = $sgCellMap[$key] ?? null;
                                                            $count = $cell['count'] ?? 0;
                                                            $bucket = _scorigamiBucket($count);
                                                            $title  = $w . '–' . $l . ' — ' . ($count > 0
                                                                ? $count . ($count === 1 ? ' time' : ' times')
                                                                : 'never happened');
                                                        ?>
                                                            <td class="sg-cell sg-c<?php echo $bucket; ?>"
                                                                <?php if ($count > 0): ?>data-key="<?php echo $key; ?>"<?php endif; ?>
                                                                title="<?php echo htmlspecialchars($title); ?>"></td>
                                                        <?php endfor; ?>
                                                        <?php $remaining = $sgN - 1 - $i; ?>
                                                        <?php if ($remaining > 0): ?>
                                                            <td class="sg-invalid" colspan="<?php echo $remaining; ?>"></td>
                                                        <?php endif; ?>
                                                    </tr>
                                                    <?php endfor; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <?php if (!empty($scorigami['outliers'])): ?>
                                        <div class="sg-outliers">
                                            <div class="sg-outliers-title">
                                                Outliers (excluded from grid above — the most extreme 1% of scores on each end)
                                            </div>
                                            <div class="table-responsive">
                                                <table class="table table-striped" id="scorigami-outliers-table">
                                                    <thead>
                                                        <tr>
                                                            <th>Year</th>
                                                            <th>Week</th>
                                                            <th>Score</th>
                                                            <th>Matchup</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($scorigami['outliers'] as $o): ?>
                                                            <tr>
                                                                <td><?php echo $o['year']; ?></td>
                                                                <td><?php echo $o['week']; ?></td>
                                                                <td><?php echo $o['win_score'] . '–' . $o['lose_score']; ?></td>
                                                                <td>
                                                                    <a href="<?php echo _scorigamiRosterLink($o['year'], $o['week'], $o['win_name']); ?>">
                                                                        <?php if ($o['tie']): ?>
                                                                            <?php echo htmlspecialchars($o['win_name'] . ' tied ' . $o['lose_name']); ?>
                                                                        <?php else: ?>
                                                                            <?php echo htmlspecialchars($o['win_name'] . ' over ' . $o['lose_name']); ?>
                                                                        <?php endif; ?>
                                                                    </a>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                        <?php if (!empty($scorigami['recent'])): ?>
                                        <div class="sg-recent">
                                            <div class="sg-recent-title">Scorigamis</div>
                                            <p class="sg-recent-sub">The first time each score pair happened, most recent first.</p>
                                            <div class="table-responsive">
                                                <table class="table table-striped" id="scorigami-recent-table">
                                                    <thead>
                                                        <tr>
                                                            <th>Year</th>
                                                            <th>Week</th>
                                                            <th>Score</th>
                                                            <th>Matchup</th>
                                                            <th>Times Since</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($scorigami['recent'] as $g): ?>
                                                            <tr>
                                                                <td><?php echo $g['year']; ?></td>
                                                                <td><?php echo $g['week']; ?></td>
                                                                <td><?php echo $g['win_score'] . '–' . $g['lose_score']; ?></td>
                                                                <td>
                                                                    <a href="<?php echo _scorigamiRosterLink($g['year'], $g['week'], $g['winner']); ?>">
                                                                        <?php if ($g['tie']): ?>
                                                                            <?php echo htmlspecialchars($g['winner'] . ' tied ' . $g['loser']); ?>
                                                                        <?php else: ?>
                                                                            <?php echo htmlspecialchars($g['winner'] . ' over ' . $g['loser']); ?>
                                                                        <?php endif; ?>
                                                                    </a>
                                                                </td>
                                                                <td><?php echo $g['count']; ?></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <?php endif; ?>
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

<script>
(function () {
    const managerColors = <?php echo json_encode($managerColors); ?>;
    const tabsData      = <?php echo json_encode(array_map(function ($tab) {
        return ['charts' => $tab['charts']];
    }, $tabs)); ?>;

    const chartsByTab    = {};   // tabId -> [chart, chart, ...]
    const chartInstances = [];   // [{ chart, mids, baseColors }]
    let   selectedMid    = null; // currently highlighted manager id (or null)

    function fadeHex(hex, alpha) {
        const m = /^#?([0-9a-f]{6})$/i.exec(hex.trim());
        if (!m) return hex;
        const n = parseInt(m[1], 16);
        return 'rgba(' + ((n >> 16) & 255) + ',' + ((n >> 8) & 255) + ',' + (n & 255) + ',' + alpha + ')';
    }

    function tierIndex(value, tiers) {
        let idx = 0;
        for (let i = 0; i < tiers.length; i++) {
            if (value >= tiers[i]) idx = i + 1;
        }
        return idx;
    }

    const tierLinesPlugin = {
        id: 'tierLines',
        afterDatasetsDraw(chart, args, opts) {
            const tiers = opts && opts.tiers;
            if (!tiers || !tiers.length) return;
            const { ctx, chartArea: { top, bottom }, scales: { x } } = chart;
            ctx.save();
            ctx.setLineDash([5, 5]);
            ctx.lineWidth = 1;
            ctx.strokeStyle = 'rgba(0,0,0,0.35)';
            ctx.fillStyle = '#000';
            ctx.font = '600 11px Barlow, sans-serif';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'top';
            tiers.forEach((t, i) => {
                const xPos = x.getPixelForValue(t);
                if (xPos < x.left || xPos > x.right) return;
                ctx.beginPath();
                ctx.moveTo(xPos, top);
                ctx.lineTo(xPos, bottom);
                ctx.stroke();
                ctx.fillText('T' + (i + 1), xPos, top + 2);
            });
            ctx.restore();
        }
    };

    function buildChart(chartCfg) {
        const ctx = document.getElementById(chartCfg.chartId);
        if (!ctx) return null;

        const sorted = chartCfg.rows.slice().sort((a, b) => b.points - a.points);
        const labels = sorted.map(r => r.manager_name);
        const data   = sorted.map(r => r.points);
        const mids   = sorted.map(r => r.manager_id);
        const colors = sorted.map(r => managerColors[r.manager_id] || '#9c68d9');

        const topTier = chartCfg.tiers[chartCfg.tiers.length - 1];
        const leader  = Math.max.apply(null, data);
        const xMax    = Math.max(leader, topTier) * 1.08;

        const unitLabel = chartCfg.unit === 'wins' ? 'Career Wins' : 'Career Points';
        const isWins    = chartCfg.unit === 'wins';
        const fmtValue  = v => isWins ? v.toLocaleString() : Math.round(v).toLocaleString();

        const chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: unitLabel,
                    data: data,
                    backgroundColor: colors,
                    borderColor: 'rgba(0,0,0,0.25)',
                    borderWidth: 1,
                    borderRadius: 4,
                    barPercentage: 0.78,
                    categoryPercentage: 0.85,
                    datalabels: {
                        anchor: 'end',
                        align: 'end',
                        clamp: true,
                        color: '#000',
                        font: { weight: '600', size: 12 },
                        formatter: fmtValue
                    }
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                layout: { padding: { right: 64, top: 18 } },
                scales: {
                    x: {
                        beginAtZero: true,
                        max: xMax,
                        ticks: { color: '#000', callback: v => v.toLocaleString() },
                        grid:  { color: 'rgba(0,0,0,0.08)' }
                    },
                    y: {
                        ticks: { color: '#000', font: { weight: '600', size: 13 } },
                        grid:  { color: 'rgba(0,0,0,0.05)' }
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (cx) {
                                const v = cx.parsed.x;
                                const tIdx = tierIndex(v, chartCfg.tiers);
                                const tierStr = tIdx === 0 ? 'Below Tier 1' : ('Tier ' + tIdx);
                                const lines = [
                                    unitLabel + ': ' + (isWins ? v.toLocaleString() : v.toLocaleString(undefined, { maximumFractionDigits: 2 })),
                                    'Current: ' + tierStr
                                ];
                                if (tIdx < chartCfg.tiers.length) {
                                    const next = chartCfg.tiers[tIdx];
                                    lines.push('To Tier ' + (tIdx + 1) + ': ' + Math.ceil(next - v).toLocaleString());
                                } else {
                                    lines.push('All tiers unlocked');
                                }
                                return lines;
                            }
                        }
                    },
                    tierLines: { tiers: chartCfg.tiers }
                }
            },
            plugins: [ChartDataLabels, tierLinesPlugin]
        });

        chartInstances.push({ chart, mids, baseColors: colors });
        return chart;
    }

    Object.keys(tabsData).forEach(tabId => {
        chartsByTab[tabId] = tabsData[tabId].charts.map(buildChart).filter(Boolean);
    });

    // ── Manager highlight on legend click ─────────────────────────────────
    function applyHighlight() {
        document.querySelectorAll('.manager-chip').forEach(chip => {
            const mid = parseInt(chip.dataset.mid, 10);
            chip.classList.toggle('selected', selectedMid === mid);
            chip.classList.toggle('faded',    selectedMid !== null && selectedMid !== mid);
        });

        chartInstances.forEach(({ chart, mids, baseColors }) => {
            const newColors = baseColors.map((c, i) => {
                if (selectedMid === null) return c;
                return mids[i] === selectedMid ? c : fadeHex(c, 0.18);
            });
            chart.data.datasets[0].backgroundColor = newColors;
            chart.update('none');
        });
    }

    document.querySelectorAll('.manager-chip').forEach(chip => {
        chip.addEventListener('click', () => {
            const mid = parseInt(chip.dataset.mid, 10);
            selectedMid = (selectedMid === mid) ? null : mid;
            applyHighlight();
        });
    });

    // Hidden-tab canvases render at 0 width; resize charts after activation.
    // DataTables has the same problem, so its tables init lazily on first view.
    let sgTablesInitialized = false;
    window.showMilestoneTab = function (tabId) {
        showCard(tabId);
        const charts = chartsByTab[tabId];
        if (charts && charts.length) setTimeout(() => charts.forEach(c => c.resize()), 50);

        if (tabId === 'scorigami' && !sgTablesInitialized && window.jQuery && jQuery.fn.DataTable) {
            sgTablesInitialized = true;
            jQuery('#scorigami-outliers-table').DataTable({ pageLength: 10, order: [[0, 'desc'], [1, 'desc']] });
            jQuery('#scorigami-recent-table').DataTable({ pageLength: 10, order: [[0, 'desc'], [1, 'desc']] });
        }
    };

    // ── Scorigami cell detail ──────────────────────────────────────────────
    const sgCellDetails = <?php echo json_encode($sgCellMap); ?>;
    const sgDetailEl    = document.getElementById('sg-detail');
    const sgTable       = document.querySelector('.sg-table');

    if (sgTable) {
        sgTable.addEventListener('click', function (e) {
            const td = e.target.closest('td[data-key]');
            if (!td) return;
            const cell = sgCellDetails[td.dataset.key];
            if (!cell || !cell.games.length) return;

            const items = cell.games.map(g => {
                const when = g.year + ' Wk ' + g.week;
                return g.tie
                    ? '<li>' + when + ': ' + g.winner + ' tied ' + g.loser + '</li>'
                    : '<li>' + when + ': ' + g.winner + ' over ' + g.loser + '</li>';
            }).join('');

            sgDetailEl.innerHTML =
                '<div class="sg-detail-title">' + cell.win_score + '–' + cell.lose_score +
                ' (' + cell.count + (cell.count === 1 ? ' time)' : ' times)') + '</div>' +
                '<ul class="sg-detail-list">' + items + '</ul>';
        });
    }
})();
</script>
