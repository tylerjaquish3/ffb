<?php
    $pageName = "Charts";
    include 'header.php';
    include 'sidebar.php';
    include 'data/charts.php';

    $careerRace      = getCareerPointsRaceData();
    $positionTreemap = getPositionTreemapData();
    $lineupAccuracy  = getLineupAccuracyData();
?>
<style>
    .charts-card-body { direction: ltr; }
    .race-wrapper {
        position: relative;
        background: #fff;
        border-radius: 8px;
        padding: 16px 18px 22px;
    }
    .race-header {
        display: flex;
        flex-wrap: wrap;
        align-items: baseline;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 6px;
    }
    .race-header h5 {
        margin: 0;
        color: #000;
        font-weight: 700;
        letter-spacing: 0.02em;
    }
    .race-ticker {
        font-size: 0.95rem;
        font-weight: 600;
        color: rgba(0,0,0,0.55);
    }
    .race-chart-wrap {
        position: relative;
        width: 100%;
        min-height: 540px;
    }
    .race-chart-wrap svg { width: 100%; height: auto; display: block; }
    .race-year-overlay {
        position: absolute;
        right: 14px;
        bottom: 8px;
        font-size: 4.5rem;
        font-weight: 800;
        color: rgba(0,0,0,0.08);
        line-height: 1;
        pointer-events: none;
        user-select: none;
        letter-spacing: 0.02em;
    }
    .race-controls {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 12px;
        margin-top: 14px;
    }
    .race-controls .race-btn {
        background: #000;
        color: #fff;
        border: none;
        border-radius: 999px;
        padding: 6px 18px;
        font-weight: 700;
        font-size: 0.85rem;
        letter-spacing: 0.04em;
        cursor: pointer;
        text-transform: uppercase;
        transition: opacity 0.15s ease, transform 0.15s ease;
    }
    .race-controls .race-btn:hover { transform: translateY(-1px); }
    .race-controls .race-btn:disabled { opacity: 0.4; cursor: default; transform: none; }
    .race-slider {
        flex: 1 1 220px;
        accent-color: #9c68d9;
        cursor: pointer;
    }
    .race-speed {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.78rem;
        color: rgba(0,0,0,0.65);
        font-weight: 600;
    }
    .race-speed select {
        border: 1px solid rgba(0,0,0,0.15);
        border-radius: 4px;
        padding: 2px 6px;
        font-size: 0.82rem;
        background: #fff;
    }
    .race-bar text.name-label {
        font-family: 'Barlow', sans-serif;
        font-weight: 700;
        font-size: 13px;
        fill: #000;
    }
    .race-bar text.value-label {
        font-family: 'Barlow', sans-serif;
        font-weight: 700;
        font-size: 13px;
        fill: rgba(0,0,0,0.85);
    }
    .race-axis text { fill: rgba(0,0,0,0.55); font-size: 11px; }
    .race-axis line, .race-axis path { stroke: rgba(0,0,0,0.1); }
    @media (max-width: 600px) {
        .race-year-overlay { font-size: 2.6rem; }
        .race-bar text.name-label { font-size: 11px; }
        .race-bar text.value-label { font-size: 11px; }
    }

    /* ── Position treemap ─────────────────────────────────────────────── */
    .tree-wrapper {
        position: relative;
        background: #fff;
        border-radius: 8px;
        padding: 16px 18px 22px;
    }
    .tree-header {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 12px;
    }
    .tree-header h5 {
        margin: 0;
        color: #000;
        font-weight: 700;
        letter-spacing: 0.02em;
    }
    .tree-year-ticker {
        font-size: 1.1rem;
        font-weight: 800;
        color: rgba(0,0,0,0.7);
        letter-spacing: 0.04em;
    }
    .tree-chart-wrap {
        position: relative;
        width: 100%;
        height: 620px;
    }
    .tree-chart-wrap svg { display: block; width: 100%; height: 100%; }
    .pos-header-bg { rx: 4; }
    .pos-header-text {
        font-family: 'Barlow', sans-serif;
        font-weight: 800;
        font-size: 12px;
        fill: #fff;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        pointer-events: none;
    }
    .mgr-tile rect { stroke: #fff; stroke-width: 1.5px; cursor: default; }
    .mgr-tile text.mgr-name {
        font-family: 'Barlow', sans-serif;
        font-weight: 700;
        font-size: 12px;
        fill: #fff;
        pointer-events: none;
    }
    .mgr-tile text.mgr-value {
        font-family: 'Barlow', sans-serif;
        font-weight: 600;
        font-size: 11px;
        fill: rgba(255,255,255,0.85);
        pointer-events: none;
    }
    .tree-tooltip {
        position: absolute;
        pointer-events: none;
        background: rgba(0,0,0,0.85);
        color: #fff;
        padding: 6px 10px;
        border-radius: 4px;
        font-size: 0.78rem;
        line-height: 1.35;
        opacity: 0;
        transition: opacity 0.12s ease;
        z-index: 20;
        white-space: nowrap;
    }
    .tree-tooltip strong { color: #fff; font-weight: 700; }
    @media (max-width: 600px) {
        .tree-chart-wrap { height: 720px; }
        .mgr-tile text.mgr-name  { font-size: 10px; }
        .mgr-tile text.mgr-value { font-size: 9.5px; }
    }

    /* ── Lineup Accuracy line chart ───────────────────────────────────── */
    .acc-wrapper {
        position: relative;
        background: #fff;
        border-radius: 8px;
        padding: 16px 18px 22px;
    }
    .acc-header {
        display: flex;
        flex-wrap: wrap;
        align-items: baseline;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 6px;
    }
    .acc-header h5 {
        margin: 0;
        color: #000;
        font-weight: 700;
        letter-spacing: 0.02em;
    }
    .acc-chart-wrap {
        position: relative;
        width: 100%;
    }
    .acc-chart-wrap svg { width: 100%; height: auto; display: block; }
    .acc-tooltip {
        position: absolute;
        pointer-events: none;
        background: rgba(0,0,0,0.85);
        color: #fff;
        padding: 8px 12px;
        border-radius: 4px;
        font-size: 0.78rem;
        line-height: 1.65;
        opacity: 0;
        transition: opacity 0.1s ease;
        z-index: 20;
        white-space: nowrap;
    }
    .acc-tooltip strong { color: #fff; font-weight: 700; }
    .acc-legend {
        display: flex;
        flex-wrap: wrap;
        gap: 6px 18px;
        margin-top: 14px;
    }
    .acc-legend-item {
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: 0.78rem;
        font-weight: 600;
        color: rgba(0,0,0,0.7);
    }
    .acc-legend-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        flex-shrink: 0;
    }
    .acc-axis text { fill: rgba(0,0,0,0.55); font-size: 11px; font-family: 'Barlow', sans-serif; }
    .acc-axis line, .acc-axis path { stroke: rgba(0,0,0,0.12); }
</style>
<div class="app-content content">
    <div class="content-wrapper">
        <div class="content-body">
            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Career Regular Season Points</h4>
                        </div>
                        <div class="card-body charts-card-body">
                            <div class="race-wrapper">
                                <div class="race-header">
                                    <h5>Cumulative career points by manager</h5>
                                    <div class="race-ticker" id="race-ticker">&nbsp;</div>
                                </div>
                                <div class="race-chart-wrap">
                                    <div id="race-chart"></div>
                                    <div class="race-year-overlay" id="race-year"></div>
                                </div>
                                <div class="race-controls">
                                    <button id="race-play" class="race-btn">Play</button>
                                    <button id="race-restart" class="race-btn" style="background:#9c68d9;">Restart</button>
                                    <input id="race-slider" class="race-slider" type="range" min="0" step="1" value="0" />
                                    <label class="race-speed">
                                        Speed
                                        <select id="race-speed">
                                            <option value="220">0.5×</option>
                                            <option value="140" selected>1×</option>
                                            <option value="80">2×</option>
                                            <option value="45">4×</option>
                                        </select>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Points by Position</h4>
                        </div>
                        <div class="card-body charts-card-body">
                            <div class="tree-wrapper">
                                <div class="tree-header">
                                    <h5>Manager share of total points within each position</h5>
                                    <div class="tree-year-ticker" id="tree-year-ticker">&nbsp;</div>
                                </div>
                                <div class="tree-chart-wrap">
                                    <div id="tree-chart"></div>
                                    <div class="tree-tooltip" id="tree-tooltip"></div>
                                </div>
                                <div class="race-controls" style="margin-top: 14px;">
                                    <button id="tree-play" class="race-btn">Play</button>
                                    <button id="tree-restart" class="race-btn" style="background:#9c68d9;">Restart</button>
                                    <input id="tree-slider" class="race-slider" type="range" min="0" step="1" value="0" />
                                    <label class="race-speed">
                                        Speed
                                        <select id="tree-speed">
                                            <option value="280">0.5×</option>
                                            <option value="140" selected>1×</option>
                                            <option value="80">2×</option>
                                            <option value="45">4×</option>
                                        </select>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Lineup Accuracy</h4>
                        </div>
                        <div class="card-body charts-card-body">
                            <div class="acc-wrapper">
                                <div class="acc-header">
                                    <h5>Actual vs. optimal points by season</h5>
                                </div>
                                <div class="acc-chart-wrap">
                                    <div id="acc-chart"></div>
                                    <div class="acc-tooltip" id="acc-tooltip"></div>
                                </div>
                                <div class="acc-legend" id="acc-legend"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<!-- D3 v7 (used by the bar chart race) -->
<script src="https://cdn.jsdelivr.net/npm/d3@7.9.0/dist/d3.min.js"></script>
<script>
(function () {
    const payload  = <?php echo json_encode($careerRace); ?>;
    const managers = payload.managers; // { mid: {name, color} }
    const frames   = payload.frames;   // [{year, week, label, values:{mid:total}}, ...]
    if (!frames.length) return;

    // ── Layout ────────────────────────────────────────────────────────────
    const numBars   = Object.keys(managers).length; // 10
    const margin    = { top: 18, right: 110, bottom: 28, left: 110 };
    const barHeight = 22;
    const barGap    = 8;
    const innerH    = numBars * (barHeight + barGap);
    const innerW    = 760;
    const fullW     = innerW + margin.left + margin.right;
    const fullH     = innerH + margin.top + margin.bottom;

    const container = d3.select('#race-chart');
    const svg = container.append('svg')
        .attr('viewBox', `0 0 ${fullW} ${fullH}`)
        .attr('preserveAspectRatio', 'xMidYMid meet')
        .style('width', '100%')
        .style('height', 'auto');

    const g = svg.append('g').attr('transform', `translate(${margin.left}, ${margin.top})`);

    const x = d3.scaleLinear().range([0, innerW]);
    const axisG = g.append('g')
        .attr('class', 'race-axis')
        .attr('transform', `translate(0, ${innerH})`);

    // Build per-frame ranked snapshots once so playback is cheap.
    function snapshotFor(frame) {
        const rows = [];
        for (const [midStr, val] of Object.entries(frame.values)) {
            if (val <= 0) continue;
            const mid = +midStr;
            rows.push({
                mid:   mid,
                value: val,
                name:  managers[mid].name,
                color: managers[mid].color,
            });
        }
        rows.sort((a, b) => b.value - a.value);
        rows.forEach((r, i) => { r.rank = i; });
        return rows;
    }
    const snapshots = frames.map(snapshotFor);

    const fmt = d3.format(',.0f');

    function rowY(rank) { return rank * (barHeight + barGap); }

    function render(idx, durationMs) {
        const frame = frames[idx];
        const rows  = snapshots[idx];
        const maxVal = rows.length ? rows[0].value : 1;
        x.domain([0, maxVal * 1.06]);

        const t = d3.transition()
            .duration(durationMs)
            .ease(d3.easeLinear);

        // ── X axis ────────────────────────────────────────────────────────
        axisG.transition(t).call(
            d3.axisBottom(x)
              .ticks(5)
              .tickFormat(v => v >= 1000 ? (v / 1000).toFixed(1).replace(/\.0$/, '') + 'k' : v)
              .tickSizeOuter(0)
        );

        // ── Bars ─────────────────────────────────────────────────────────
        const bars = g.selectAll('g.race-bar')
            .data(rows, d => d.mid);

        const barsEnter = bars.enter().append('g')
            .attr('class', 'race-bar')
            .attr('transform', `translate(0, ${innerH + barHeight})`); // enter from below

        barsEnter.append('rect')
            .attr('height', barHeight)
            .attr('rx', 5)
            .attr('width', 0)
            .attr('fill', d => d.color);

        barsEnter.append('text')
            .attr('class', 'name-label')
            .attr('text-anchor', 'end')
            .attr('x', -8)
            .attr('y', barHeight / 2)
            .attr('dy', '0.35em')
            .text(d => d.name);

        barsEnter.append('text')
            .attr('class', 'value-label')
            .attr('x', 4)
            .attr('y', barHeight / 2)
            .attr('dy', '0.35em')
            .text('0');

        const merged = barsEnter.merge(bars);

        merged.transition(t)
            .attr('transform', d => `translate(0, ${rowY(d.rank)})`);

        merged.select('rect')
            .transition(t)
            .attr('width', d => Math.max(1, x(d.value)));

        merged.select('text.name-label')
            .text(d => d.name);

        merged.select('text.value-label')
            .transition(t)
            .attr('x', d => Math.max(1, x(d.value)) + 8)
            .tween('text', function (d) {
                const prev = +String(this.textContent).replace(/[^0-9.\-]/g, '') || 0;
                const i = d3.interpolateNumber(prev, d.value);
                return tt => { this.textContent = fmt(i(tt)); };
            });

        bars.exit()
            .transition(t)
            .attr('transform', `translate(0, ${innerH + barHeight})`)
            .style('opacity', 0)
            .remove();

        // ── Overlays ─────────────────────────────────────────────────────
        d3.select('#race-year').text(frame.year);
        d3.select('#race-ticker').text(frame.label);
    }

    // ── Playback controls ────────────────────────────────────────────────
    const playBtn    = document.getElementById('race-play');
    const restartBtn = document.getElementById('race-restart');
    const slider     = document.getElementById('race-slider');
    const speedSel   = document.getElementById('race-speed');

    slider.max = frames.length - 1;

    let currentIdx = 0;
    let playing    = false;
    let timerId    = null;
    let stepMs     = parseInt(speedSel.value, 10);

    function setPlaying(on) {
        playing = on;
        playBtn.textContent = on ? 'Pause' : (currentIdx >= frames.length - 1 ? 'Replay' : 'Play');
        if (!on && timerId) { clearTimeout(timerId); timerId = null; }
    }

    function tick() {
        if (!playing) return;
        if (currentIdx >= frames.length - 1) {
            setPlaying(false);
            return;
        }
        currentIdx++;
        slider.value = currentIdx;
        render(currentIdx, stepMs);
        timerId = setTimeout(tick, stepMs);
    }

    playBtn.addEventListener('click', () => {
        if (!playing && currentIdx >= frames.length - 1) {
            currentIdx = 0;
            slider.value = 0;
            render(0, 0);
        }
        setPlaying(!playing);
        if (playing) tick();
    });

    restartBtn.addEventListener('click', () => {
        setPlaying(false);
        currentIdx = 0;
        slider.value = 0;
        render(0, 0);
        playBtn.textContent = 'Play';
    });

    slider.addEventListener('input', () => {
        if (playing) setPlaying(false);
        currentIdx = +slider.value;
        render(currentIdx, 0);
        playBtn.textContent = currentIdx >= frames.length - 1 ? 'Replay' : 'Play';
    });

    speedSel.addEventListener('change', () => {
        stepMs = parseInt(speedSel.value, 10);
        if (playing) {
            if (timerId) clearTimeout(timerId);
            timerId = setTimeout(tick, stepMs);
        }
    });

    // Initial frame
    render(0, 0);
})();
</script>

<script>
(function () {
    const payload        = <?php echo json_encode($positionTreemap); ?>;
    const positions      = payload.positions;           // ['QB','RB','WR','TE','K','DEF']
    const positionColors = payload.positionColors;      // {pos: '#hex'}
    const weeklyFrames   = payload.frames;              // [{year, week, key, weekly:{pos:{mgr:pts}}}, ...]
    if (!weeklyFrames.length) return;

    // Build cumulative-through-week frames so each playback step "grows"
    // the treemap by exactly that week's points.
    const frames = (function () {
        const accum = {}; // pos -> mgr -> running total
        const out   = [];
        for (const f of weeklyFrames) {
            for (const pos of positions) {
                if (!accum[pos]) accum[pos] = {};
                const mgrs = f.weekly[pos] || {};
                for (const [mgr, pts] of Object.entries(mgrs)) {
                    accum[pos][mgr] = (accum[pos][mgr] || 0) + (+pts);
                }
            }
            const snapshot = {};
            for (const pos of positions) {
                snapshot[pos] = Object.assign({}, accum[pos] || {});
            }
            out.push({ year: f.year, week: f.week, src: snapshot });
        }
        return out;
    })();

    // ── SVG setup ────────────────────────────────────────────────────────
    const wrap = document.getElementById('tree-chart');
    const svg  = d3.select(wrap).append('svg')
        .attr('width', '100%')
        .attr('height', '100%');

    function size() {
        const r = wrap.getBoundingClientRect();
        return { w: Math.max(320, r.width), h: Math.max(480, r.height) };
    }

    const fmt = d3.format(',.0f');
    const tooltip = d3.select('#tree-tooltip');

    function buildHierarchy(frameIdx) {
        const src = frames[frameIdx].src;
        const children = positions.map(pos => {
            const mgrs = src[pos] || {};
            const kids = Object.entries(mgrs)
                .map(([name, pts]) => ({ name, value: +pts }))
                .filter(d => d.value > 0);
            return { name: pos, color: positionColors[pos], children: kids };
        });
        return { name: 'root', children };
    }

    function layout(frameIdx) {
        const { w, h } = size();
        const root = d3.hierarchy(buildHierarchy(frameIdx))
            .sum(d => d.value || 0)
            .sort((a, b) => (b.value || 0) - (a.value || 0));

        d3.treemap()
            .tile(d3.treemapResquarify)
            .size([w, h])
            .paddingOuter(4)
            .paddingTop(22)
            .paddingInner(2)
            .round(true)(root);

        return { root, w, h };
    }

    function showTooltip(event, html) {
        const r = wrap.getBoundingClientRect();
        tooltip.html(html)
            .style('left', (event.clientX - r.left + 12) + 'px')
            .style('top',  (event.clientY - r.top  + 12) + 'px')
            .style('opacity', 1);
    }
    function hideTooltip() { tooltip.style('opacity', 0); }

    function render(frameIdx, durationMs) {
        const { root, w, h } = layout(frameIdx);
        svg.attr('viewBox', `0 0 ${w} ${h}`);

        const t = d3.transition().duration(durationMs).ease(d3.easeCubicInOut);

        // ── Position groups (parents) ────────────────────────────────────
        const posNodes = root.children || [];
        const groups = svg.selectAll('g.pos-group')
            .data(posNodes, d => d.data.name);

        const groupsEnter = groups.enter().append('g')
            .attr('class', 'pos-group')
            .attr('transform', d => `translate(${d.x0}, ${d.y0})`);

        groupsEnter.append('rect')
            .attr('class', 'pos-header-bg')
            .attr('x', 0).attr('y', 0)
            .attr('width', d => Math.max(0, d.x1 - d.x0))
            .attr('height', 20)
            .attr('rx', 4)
            .attr('fill', d => d.data.color);

        groupsEnter.append('text')
            .attr('class', 'pos-header-text')
            .attr('x', 8)
            .attr('y', 14)
            .text(d => d.data.name);

        const groupsMerged = groupsEnter.merge(groups);

        groupsMerged.transition(t)
            .attr('transform', d => `translate(${d.x0}, ${d.y0})`);
        groupsMerged.select('rect.pos-header-bg')
            .transition(t)
            .attr('width', d => Math.max(0, d.x1 - d.x0))
            .attr('fill', d => d.data.color);
        groupsMerged.select('text.pos-header-text')
            .text(d => d.data.name);

        groups.exit().transition(t).style('opacity', 0).remove();

        // ── Manager tiles (leaves) ───────────────────────────────────────
        const leaves = root.leaves().filter(d => (d.value || 0) > 0);
        const tiles = svg.selectAll('g.mgr-tile')
            .data(leaves, d => d.parent.data.name + '|' + d.data.name);

        const tilesEnter = tiles.enter().append('g')
            .attr('class', 'mgr-tile')
            .attr('transform', d => `translate(${d.x0}, ${d.y0})`)
            .style('opacity', 0);

        tilesEnter.append('rect')
            .attr('width',  d => Math.max(0, d.x1 - d.x0))
            .attr('height', d => Math.max(0, d.y1 - d.y0))
            .attr('fill', d => d.parent.data.color);

        tilesEnter.append('text')
            .attr('class', 'mgr-name')
            .attr('x', 6).attr('y', 16)
            .text(d => d.data.name);

        tilesEnter.append('text')
            .attr('class', 'mgr-value')
            .attr('x', 6).attr('y', 30)
            .text(d => fmt(d.value));

        const tilesMerged = tilesEnter.merge(tiles);

        tilesMerged
            .on('mousemove', (event, d) => {
                showTooltip(event,
                    `<strong>${d.parent.data.name} — ${d.data.name}</strong><br>` +
                    `${fmt(d.value)} pts`);
            })
            .on('mouseleave', hideTooltip);

        tilesMerged.transition(t)
            .attr('transform', d => `translate(${d.x0}, ${d.y0})`)
            .style('opacity', 1);

        tilesMerged.select('rect')
            .transition(t)
            .attr('width',  d => Math.max(0, d.x1 - d.x0))
            .attr('height', d => Math.max(0, d.y1 - d.y0))
            .attr('fill', d => d.parent.data.color);

        tilesMerged.select('text.mgr-name')
            .text(d => d.data.name)
            .style('display', d => ((d.x1 - d.x0) > 50 && (d.y1 - d.y0) > 22) ? null : 'none');

        tilesMerged.select('text.mgr-value')
            .style('display', d => ((d.x1 - d.x0) > 50 && (d.y1 - d.y0) > 36) ? null : 'none')
            .transition(t)
            .tween('text', function (d) {
                const prev = +String(this.textContent).replace(/[^0-9.\-]/g, '') || 0;
                const i = d3.interpolateNumber(prev, d.value);
                return tt => { this.textContent = fmt(i(tt)); };
            });

        tiles.exit()
            .transition(t)
            .style('opacity', 0)
            .remove();

        const fr = frames[frameIdx];
        d3.select('#tree-year-ticker').text('Through ' + fr.year + ' — Week ' + fr.week);
    }

    // ── Playback controls ────────────────────────────────────────────────
    const playBtn    = document.getElementById('tree-play');
    const restartBtn = document.getElementById('tree-restart');
    const slider     = document.getElementById('tree-slider');
    const speedSel   = document.getElementById('tree-speed');

    slider.max = frames.length - 1;

    let currentIdx = 0;
    let playing    = false;
    let timerId    = null;
    let stepMs     = parseInt(speedSel.value, 10);

    function setPlaying(on) {
        playing = on;
        playBtn.textContent = on ? 'Pause' : (currentIdx >= frames.length - 1 ? 'Replay' : 'Play');
        if (!on && timerId) { clearTimeout(timerId); timerId = null; }
    }

    function tick() {
        if (!playing) return;
        if (currentIdx >= frames.length - 1) {
            setPlaying(false);
            return;
        }
        currentIdx++;
        slider.value = currentIdx;
        render(currentIdx, stepMs);
        timerId = setTimeout(tick, stepMs);
    }

    playBtn.addEventListener('click', () => {
        if (!playing && currentIdx >= frames.length - 1) {
            currentIdx = 0;
            slider.value = 0;
            render(0, 0);
        }
        setPlaying(!playing);
        if (playing) tick();
    });

    restartBtn.addEventListener('click', () => {
        setPlaying(false);
        currentIdx = 0;
        slider.value = 0;
        render(0, 0);
        playBtn.textContent = 'Play';
    });

    slider.addEventListener('input', () => {
        if (playing) setPlaying(false);
        currentIdx = +slider.value;
        render(currentIdx, 0);
        playBtn.textContent = currentIdx >= frames.length - 1 ? 'Replay' : 'Play';
    });

    speedSel.addEventListener('change', () => {
        stepMs = parseInt(speedSel.value, 10);
        if (playing) {
            if (timerId) clearTimeout(timerId);
            timerId = setTimeout(tick, stepMs);
        }
    });

    window.addEventListener('resize', () => render(currentIdx, 0));

    render(0, 0);
})();
</script>

<script>
(function () {
    const payload = <?php echo json_encode($lineupAccuracy); ?>;
    const seasons = (payload.seasons || []).map(String);
    const series  = payload.series  || [];
    if (!seasons.length || !series.length) return;

    // Pre-build per-series point arrays using numeric year keys
    const sData = series.map(s => ({
        mid:    s.mid,
        name:   s.name,
        color:  s.color,
        byYear: s.byYear,
        pts:    seasons
            .filter(yr => s.byYear[+yr] !== undefined)
            .map(yr => ({ year: yr, acc: +s.byYear[+yr] }))
    })).filter(s => s.pts.length > 0);
    if (!sData.length) return;

    // Legend
    const legendEl = document.getElementById('acc-legend');
    sData.forEach(s => {
        const el = document.createElement('div');
        el.className = 'acc-legend-item';
        el.innerHTML = `<span class="acc-legend-dot" style="background:${s.color}"></span>${s.name}`;
        legendEl.appendChild(el);
    });

    // Layout
    const margin = { top: 14, right: 16, bottom: 36, left: 52 };
    const IW = 760, IH = 300;
    const FW = IW + margin.left + margin.right;
    const FH = IH + margin.top + margin.bottom;

    const wrap = document.getElementById('acc-chart');
    const svg = d3.select(wrap).append('svg')
        .attr('viewBox', `0 0 ${FW} ${FH}`)
        .attr('preserveAspectRatio', 'xMidYMid meet')
        .style('width', '100%').style('height', 'auto');
    const g = svg.append('g').attr('transform', `translate(${margin.left},${margin.top})`);

    // Scales
    const x = d3.scalePoint().domain(seasons).range([0, IW]).padding(0.1);

    const allAcc  = sData.flatMap(s => s.pts.map(p => p.acc));
    const yFloor  = Math.max(0, Math.floor((Math.min(...allAcc) - 2) / 5) * 5);
    const y = d3.scaleLinear().domain([yFloor, 100]).range([IH, 0]);

    // Horizontal grid
    g.selectAll('.acc-hg')
        .data(y.ticks(6)).enter()
        .append('line')
        .attr('x1', 0).attr('x2', IW)
        .attr('y1', d => y(d)).attr('y2', d => y(d))
        .attr('stroke', 'rgba(0,0,0,0.06)').attr('stroke-width', 1);

    // Axes
    g.append('g').attr('class', 'acc-axis')
        .attr('transform', `translate(0,${IH})`)
        .call(d3.axisBottom(x).tickSizeOuter(0));
    g.append('g').attr('class', 'acc-axis')
        .call(d3.axisLeft(y).ticks(6).tickFormat(d => d + '%').tickSizeOuter(0));

    // Line generator
    const lineGen = d3.line()
        .x(d => x(d.year)).y(d => y(d.acc))
        .curve(d3.curveMonotoneX)
        .defined(d => d.acc != null);

    // Lines and dots
    sData.forEach(s => {
        g.append('path')
            .datum(s.pts)
            .attr('fill', 'none').attr('stroke', s.color)
            .attr('stroke-width', 2).attr('stroke-linejoin', 'round')
            .attr('stroke-linecap', 'round')
            .attr('d', lineGen);
        g.selectAll(null).data(s.pts).enter()
            .append('circle')
            .attr('cx', d => x(d.year)).attr('cy', d => y(d.acc))
            .attr('r', 3).attr('fill', s.color)
            .attr('stroke', '#fff').attr('stroke-width', 1.5);
    });

    // Hover: vertical rule + tooltip
    const hLine = g.append('line')
        .attr('y1', 0).attr('y2', IH)
        .attr('stroke', 'rgba(0,0,0,0.25)').attr('stroke-width', 1)
        .attr('stroke-dasharray', '4,3').attr('pointer-events', 'none')
        .style('opacity', 0);

    const tip = d3.select('#acc-tooltip');

    svg.append('rect')
        .attr('transform', `translate(${margin.left},${margin.top})`)
        .attr('width', IW).attr('height', IH)
        .attr('fill', 'transparent')
        .on('mousemove', function (event) {
            const [mx] = d3.pointer(event);
            let best = seasons[0], bd = Infinity;
            seasons.forEach(yr => {
                const dist = Math.abs(x(yr) - mx);
                if (dist < bd) { bd = dist; best = yr; }
            });

            hLine.attr('x1', x(best)).attr('x2', x(best)).style('opacity', 1);

            const rows = sData
                .filter(s => s.byYear[+best] !== undefined)
                .map(s => ({ name: s.name, color: s.color, acc: s.byYear[+best] }))
                .sort((a, b) => b.acc - a.acc);

            const html = `<strong>${best}</strong><br>` + rows.map(r =>
                `<span style="display:inline-block;width:8px;height:8px;border-radius:50%;` +
                `background:${r.color};margin-right:5px;vertical-align:middle;"></span>` +
                `${r.name} <strong>${r.acc}%</strong>`
            ).join('<br>');

            const wr = wrap.getBoundingClientRect();
            tip.html(html)
                .style('left', (event.clientX - wr.left + 14) + 'px')
                .style('top',  (event.clientY - wr.top  + 14) + 'px')
                .style('opacity', 1);
        })
        .on('mouseleave', function () {
            hLine.style('opacity', 0);
            tip.style('opacity', 0);
        });
})();
</script>
