<?php
    $pageName = "Text Stats";
    include 'header.php';
    include 'sidebar.php';
    include 'data/charts.php';
    include 'data/textStats.php';

    $textStats = getTextStatsData();
    $managers  = array_values($textStats['managers']);
    $mostTalkative = $managers[0] ?? null;
    $quietest      = end($managers) ?: null;
    $timeframe     = $textStats['timeframe'];
?>
<style>
    .ts-card-body { direction: ltr; }
    .ts-tiles {
        direction: ltr;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 14px;
        margin-bottom: 20px;
    }
    .ts-tile {
        background: #fff;
        border-radius: 8px;
        padding: 16px 18px;
        text-align: center;
    }
    .ts-tile-value {
        font-size: 1.9rem;
        font-weight: 800;
        color: #000;
        line-height: 1.15;
    }
    .ts-tile-value.ts-tile-value-sm {
        font-size: 1.2rem;
    }
    .ts-tile-sub {
        font-size: 0.78rem;
        font-weight: 600;
        color: rgba(0,0,0,0.4);
        margin-top: 2px;
    }
    .ts-tile-label {
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: rgba(0,0,0,0.5);
        margin-top: 4px;
    }
    @media (max-width: 500px) {
        .ts-tiles { grid-template-columns: repeat(2, 1fr); }
    }

    .ts-wrapper {
        position: relative;
        background: #fff;
        border-radius: 8px;
        padding: 16px 18px 22px;
    }
    .ts-header {
        display: flex;
        flex-wrap: wrap;
        align-items: baseline;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 10px;
    }
    .ts-header h5 {
        margin: 0;
        color: #000;
        font-weight: 700;
        letter-spacing: 0.02em;
    }
    .ts-chart-wrap {
        position: relative;
        width: 100%;
    }
    .ts-chart-wrap svg { width: 100%; height: auto; display: block; }
    .ts-bar text.ts-name-label {
        font-family: 'Barlow', sans-serif;
        font-weight: 700;
        font-size: 13px;
        fill: #000;
    }
    .ts-bar text.ts-value-label {
        font-family: 'Barlow', sans-serif;
        font-weight: 700;
        font-size: 13px;
        fill: rgba(0,0,0,0.85);
    }
    .ts-bar rect { cursor: pointer; }
    .ts-tooltip {
        position: absolute;
        pointer-events: none;
        background: rgba(0,0,0,0.85);
        color: #fff;
        padding: 8px 12px;
        border-radius: 4px;
        font-size: 0.78rem;
        line-height: 1.5;
        opacity: 0;
        transition: opacity 0.1s ease;
        z-index: 20;
        white-space: nowrap;
    }
    .ts-tooltip strong { color: #fff; font-weight: 700; }
    .ts-note {
        font-size: 0.78rem;
        color: rgba(0,0,0,0.45);
        margin-top: 14px;
        line-height: 1.5;
    }
</style>
<div class="app-content content">
    <div class="content-wrapper">
        <div class="content-body">
            <div class="row">
                <div class="col-sm-12">

                    <div class="ts-tiles">
                        <div class="ts-tile">
                            <div class="ts-tile-value ts-tile-value-sm"><?php echo htmlspecialchars($timeframe['label'] ?? '—'); ?></div>
                            <div class="ts-tile-label">Timeframe</div>
                            <?php if ($timeframe): ?>
                                <div class="ts-tile-sub"><?php echo (int) $timeframe['days']; ?> days</div>
                            <?php endif; ?>
                        </div>
                        <div class="ts-tile">
                            <div class="ts-tile-value"><?php echo number_format($textStats['totalMessages']); ?></div>
                            <div class="ts-tile-label">Total Messages</div>
                        </div>
                        <div class="ts-tile">
                            <div class="ts-tile-value"><?php echo number_format($textStats['totalWords']); ?></div>
                            <div class="ts-tile-label">Total Words</div>
                        </div>
                        <div class="ts-tile">
                            <div class="ts-tile-value" style="color: <?php echo htmlspecialchars($mostTalkative['color'] ?? '#000'); ?>">
                                <?php echo htmlspecialchars($mostTalkative['name'] ?? '—'); ?>
                            </div>
                            <div class="ts-tile-label">Most Talkative</div>
                            <?php if ($mostTalkative): ?>
                                <div class="ts-tile-sub"><?php echo $mostTalkative['messagePct']; ?>% of messages</div>
                            <?php endif; ?>
                        </div>
                        <div class="ts-tile">
                            <div class="ts-tile-value" style="color: <?php echo htmlspecialchars($quietest['color'] ?? '#000'); ?>">
                                <?php echo htmlspecialchars($quietest['name'] ?? '—'); ?>
                            </div>
                            <div class="ts-tile-label">Quietest</div>
                            <?php if ($quietest): ?>
                                <div class="ts-tile-sub"><?php echo $quietest['messagePct']; ?>% of messages</div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Messages Sent</h4>
                        </div>
                        <div class="card-body ts-card-body">
                            <div class="ts-wrapper">
                                <div class="ts-header">
                                    <h5>Who's in the group chat the most (and the least)</h5>
                                </div>
                                <div class="ts-chart-wrap">
                                    <div id="ts-messages-chart"></div>
                                    <div class="ts-tooltip" id="ts-messages-tooltip"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Words Written</h4>
                        </div>
                        <div class="card-body ts-card-body">
                            <div class="ts-wrapper">
                                <div class="ts-header">
                                    <h5>Total words sent — frequency and length aren't the same thing</h5>
                                </div>
                                <div class="ts-chart-wrap">
                                    <div id="ts-words-chart"></div>
                                    <div class="ts-tooltip" id="ts-words-tooltip"></div>
                                </div>
                                <div class="ts-note">
                                    Parsed from a copy/paste export of the group thread — sender attribution is
                                    best-effort where messages run back-to-back with no timestamp between them,
                                    so treat close counts as roughly tied rather than exact.
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

<script src="https://cdn.jsdelivr.net/npm/d3@7.9.0/dist/d3.min.js"></script>
<script>
(function () {
    const managers = <?php echo json_encode(array_values($textStats['managers'])); ?>;
    if (!managers.length) return;

    function renderBarChart(containerId, tooltipId, metricKey, pctKey, valueFmt) {
        const rows = managers.slice().sort((a, b) => b[metricKey] - a[metricKey]);

        const isMobile  = window.innerWidth <= 600;
        const margin    = isMobile
            ? { top: 6, right: 44, bottom: 6, left: 64 }
            : { top: 6, right: 60, bottom: 6, left: 96 };
        const barHeight = 26;
        const barGap    = 10;
        const innerH    = rows.length * (barHeight + barGap) - barGap;
        const innerW    = isMobile ? 260 : 640;
        const fullW     = innerW + margin.left + margin.right;
        const fullH     = innerH + margin.top + margin.bottom;

        const container = d3.select('#' + containerId);
        const svg = container.append('svg')
            .attr('viewBox', `0 0 ${fullW} ${fullH}`)
            .attr('preserveAspectRatio', 'xMidYMid meet')
            .style('width', '100%')
            .style('height', 'auto');

        const g = svg.append('g').attr('transform', `translate(${margin.left}, ${margin.top})`);

        const maxVal = rows[0][metricKey];
        const x = d3.scaleLinear().domain([0, maxVal * 1.08]).range([0, innerW]);

        const tooltip = d3.select('#' + tooltipId);
        function showTooltip(event, d) {
            const wr = container.node().getBoundingClientRect();
            const perMsg = d.messages ? (d.words / d.messages).toFixed(1) : '0.0';
            tooltip.html(
                `<strong>${d.name}</strong><br>` +
                `${d.messages.toLocaleString()} messages (${d.messagePct}%)<br>` +
                `${d.words.toLocaleString()} words (${d.wordPct}%)<br>` +
                `${perMsg} words/message`
            )
                .style('left', (event.clientX - wr.left + 14) + 'px')
                .style('top',  (event.clientY - wr.top  + 14) + 'px')
                .style('opacity', 1);
        }
        function hideTooltip() { tooltip.style('opacity', 0); }

        const bars = g.selectAll('g.ts-bar')
            .data(rows, d => d.name)
            .enter()
            .append('g')
            .attr('class', 'ts-bar')
            .attr('transform', (d, i) => `translate(0, ${i * (barHeight + barGap)})`);

        bars.append('text')
            .attr('class', 'ts-name-label')
            .attr('text-anchor', 'end')
            .attr('x', -10)
            .attr('y', barHeight / 2)
            .attr('dy', '0.35em')
            .text(d => d.name);

        bars.append('rect')
            .attr('height', barHeight)
            .attr('rx', 5)
            .attr('width', 0)
            .attr('fill', d => d.color)
            .on('mousemove', (event, d) => showTooltip(event, d))
            .on('mouseleave', hideTooltip)
            .transition()
            .duration(600)
            .delay((d, i) => i * 60)
            .attr('width', d => Math.max(2, x(d[metricKey])));

        bars.append('text')
            .attr('class', 'ts-value-label')
            .attr('x', d => Math.max(2, x(d[metricKey])) + 8)
            .attr('y', barHeight / 2)
            .attr('dy', '0.35em')
            .style('opacity', 0)
            .text(d => `${valueFmt(d[metricKey])} · ${d[pctKey]}%`)
            .transition()
            .duration(300)
            .delay((d, i) => i * 60 + 500)
            .style('opacity', 1);
    }

    const fmt = d3.format(',d');
    renderBarChart('ts-messages-chart', 'ts-messages-tooltip', 'messages', 'messagePct', fmt);
    renderBarChart('ts-words-chart', 'ts-words-tooltip', 'words', 'wordPct', fmt);
})();
</script>
