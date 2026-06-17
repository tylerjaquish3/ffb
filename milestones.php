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
    window.showMilestoneTab = function (tabId) {
        showCard(tabId);
        const charts = chartsByTab[tabId];
        if (charts && charts.length) setTimeout(() => charts.forEach(c => c.resize()), 50);
    };
})();
</script>
