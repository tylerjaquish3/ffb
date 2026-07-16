<?php
$pageName = "2026 Draft Order Bracket";
$version = "v5.7.1";

$conn = new SQLite3(__DIR__ . '/../database/ffb.sqlite');
$seedsResult = $conn->query(
    "SELECT m.name, f.finish FROM finishes f JOIN managers m ON m.id = f.manager_id WHERE f.year = 2025 ORDER BY f.finish ASC"
);
$seeds = [];
while ($row = $seedsResult->fetchArray(SQLITE3_ASSOC)) {
    $seeds[] = $row['name'];
}
// $seeds[0] = seed 1 (AJ), $seeds[9] = seed 10 (Gavin)
?>
<!DOCTYPE html>
<html lang="en" data-textdirection="rtl">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <title><?php echo $pageName; ?> | Suntown FFB</title>
    <link rel="icon" type="image/png" href="/images/football.ico">
    <link rel="stylesheet" href="/assets/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/app.min.css">
    <link rel="stylesheet" href="/assets/icomoon.css">
    <link rel="stylesheet" href="/assets/bootstrap-extended.min.css">
    <link rel="stylesheet" href="/assets/custom-rtl.min.css">
    <link rel="stylesheet" type="text/css" href="/assets/suntown.css?v=<?php echo $version; ?>">
    <link rel="stylesheet" type="text/css" href="/assets/responsive.css?v=<?php echo $version; ?>">
</head>
<body class="fixed-navbar" style="background:#1f1f1f;">

<!-- Intro overlay -->
<div id="intro-overlay">
    <div id="intro-card">

        <div id="intro-step-dots">
            <span class="intro-dot active" data-step="1"></span>
            <span class="intro-dot" data-step="2"></span>
        </div>

        <!-- Step 1: Instructions -->
        <div id="intro-step-1">
            <div id="intro-pickle">🥒</div>
            <h1 id="intro-title">The Mystic Pickle Decides</h1>
            <h2 id="intro-subtitle">2026 Draft Order Tournament</h2>
            <div id="intro-body">
                <p>Draft order is earned, not given. This year, all 10 managers compete in a <strong>double-elimination tournament</strong> — and the outcomes are in the hands of a higher power: <strong>the Mystic Pickle</strong>.</p>

                <div class="intro-rule-block">
                    <div class="intro-rule-label">How It Works</div>
                    <ol class="intro-rules">
                        <li><strong>Seeds are randomized.</strong> No one gets a free ride based on last year's finish — the randomizer scrambles the bracket seeds.</li>
                        <li><strong>The Pickle presides over every match.</strong> For each matchup, the Pickle is asked a random yes/no question. Yes means the first manager wins. No means the second.</li>
                        <li><strong>Only clear answers count.</strong> The Pickle speaks in riddles sometimes. If the answer is a "maybe," incoherent, or otherwise non-committal, the Pickle is consulted again.</li>
                        <li><strong>Draft order fills from the bottom up.</strong> The first manager eliminated gets pick #10. The last manager standing gets pick #1.</li>
                    </ol>
                </div>

                <p class="intro-footer-note">The Pickle has spoken before. The Pickle will speak again. Trust the Pickle.</p>
            </div>
            <button id="intro-next-btn">The Stakes &rarr;</button>
        </div>

        <!-- Step 2: Pre-draft notes -->
        <div id="intro-step-2" style="display:none;">
            <div id="intro-pickle">📊</div>
            <h1 id="intro-title">Why It Matters</h1>
            <h2 id="intro-subtitle">2026 Pre-Draft Notes</h2>
            <div id="intro-body">
                <ul class="intro-stats-list">
                    <li>
                        <span class="stat-highlight">0 for 5</span>
                        <span class="stat-desc">The last 5 champions all picked in the top 5 — but <strong>not one of them had the #1 pick</strong>.</span>
                    </li>
                    <li>
                        <span class="stat-highlight">#1 &amp; #2</span>
                        <span class="stat-desc">Cole and AJ both went <strong>11-3</strong> last year and landed the first two picks.</span>
                    </li>
                    <li>
                        <span class="stat-highlight">Never</span>
                        <span class="stat-desc"><strong>Cam, Ev, and Justin</strong> have never held the #1 overall pick.</span>
                    </li>
                    <li>
                        <span class="stat-highlight">14 years</span>
                        <span class="stat-desc">Justin has had just <strong>2 top-3 picks in league history</strong> — and hasn't seen one since 2012.</span>
                    </li>
                    <li>
                        <span class="stat-highlight">2011</span>
                        <span class="stat-desc">The <strong>only time the #1 pick won</strong> the league was Ben, back in 2011.</span>
                    </li>
                </ul>
            </div>
            <button id="intro-start-btn">Get Started</button>
        </div>

    </div>
</div>

    <nav class="header-navbar navbar navbar-with-menu navbar-fixed-top navbar-semi-dark navbar-shadow">
        <div class="navbar-wrapper">
            <div class="navbar-header">
                <ul class="nav navbar-nav">
                    <li class="nav-item">
                        <a href="/"><img src="/images/logo-cropped.png" alt="Suntown FFB" class="navbar-logo"></a>
                    </li>
                </ul>
            </div>
            <div class="navbar-container content container-fluid">
                <div id="navbar-mobile">
                    <h2 style="direction:ltr;"><?php echo $pageName; ?></h2>
                </div>
            </div>
        </div>
    </nav>

<div class="app-content content" style="margin-left:0;">
    <div class="content-wrapper">
        <div class="content-body">

            <div class="row mb-1">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0" style="direction:ltr;">Seed Randomizer</h4>
                        </div>
                        <div class="card-body" style="direction:ltr; padding:1.25rem;">
                            <div class="seed-grid" id="seed-grid">
                                <div class="seed-slot" data-idx="0"><span class="seed-num">1</span><span class="seed-name">—</span></div>
                                <div class="seed-slot" data-idx="1"><span class="seed-num">2</span><span class="seed-name">—</span></div>
                                <div class="seed-slot" data-idx="2"><span class="seed-num">3</span><span class="seed-name">—</span></div>
                                <div class="seed-slot" data-idx="3"><span class="seed-num">4</span><span class="seed-name">—</span></div>
                                <div class="seed-slot" data-idx="4"><span class="seed-num">5</span><span class="seed-name">—</span></div>
                                <div class="seed-slot" data-idx="5"><span class="seed-num">6</span><span class="seed-name">—</span></div>
                                <div class="seed-slot" data-idx="6"><span class="seed-num">7</span><span class="seed-name">—</span></div>
                                <div class="seed-slot" data-idx="7"><span class="seed-num">8</span><span class="seed-name">—</span></div>
                                <div class="seed-slot" data-idx="8"><span class="seed-num">9</span><span class="seed-name">—</span></div>
                                <div class="seed-slot" data-idx="9"><span class="seed-num">10</span><span class="seed-name">—</span></div>
                            </div>
                            <button id="randomize-btn" class="seed-randomize-btn">&#127922;&nbsp; Randomize Seeds</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h4 class="card-title mb-0">Double Elimination Bracket</h4>
                            <button id="reset-btn" class="btn btn-sm btn-outline-danger" style="direction:ltr;">Reset Bracket</button>
                        </div>
                        <div class="card-body p-0">
                            <div class="bracket-scroll-wrapper" style="overflow-x:auto; padding: 1.5rem; direction: ltr;">
                                <div id="bracket-container"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-1">
                
                <div class="col-12 col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Draft Order Results</h4>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm mb-0" style="direction:ltr;">
                                <thead>
                                    <tr>
                                        <th>Pick #</th>
                                        <th>Bracket Finish</th>
                                        <th>Manager</th>
                                    </tr>
                                </thead>
                                <tbody id="draft-order-table"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Matchups</h4>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm mb-0" style="direction:ltr;">
                                <thead>
                                    <tr>
                                        <th style="width:50px;">Match</th>
                                        <th style="width:110px; white-space:nowrap;">Round</th>
                                        <th>Result</th>
                                    </tr>
                                </thead>
                                <tbody id="matchups-table"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Match Modal -->
<div class="modal fade" id="matchModal" tabindex="-1" role="dialog" aria-labelledby="matchModalLabel" aria-hidden="true" style="direction: ltr;">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="matchModalLabel">Match <span id="modal-match-num"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <p class="mb-4" id="modal-question" style="font-size:1.1rem;"></p>
                <div class="d-flex justify-content-center" style="gap:1rem;">
                    <button id="modal-yes-btn" class="btn btn-lg btn-outline-success winner-pick-btn" style="min-width:100px;">Yes</button>
                    <button id="modal-no-btn" class="btn btn-lg btn-outline-danger winner-pick-btn" style="min-width:100px;">No</button>
                </div>
                <div id="modal-selection-display" class="mt-3" style="display:none;">
                    Winner: <strong id="modal-selected-winner"></strong>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="modal-submit-btn" disabled>Save Result</button>
            </div>
        </div>
    </div>
</div>

<style>
/* ── Intro Overlay ───────────────────────────────────────────────── */
#intro-overlay {
    position: fixed;
    inset: 0;
    background: rgba(10, 10, 10, 0.97);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    direction: ltr;
}
#intro-card {
    background: #1e1e2e;
    border: 1px solid #3a3a5a;
    border-radius: 16px;
    max-width: 560px;
    width: 100%;
    padding: 40px 36px;
    text-align: center;
    box-shadow: 0 24px 80px rgba(0,0,0,0.7), 0 0 0 1px rgba(115,103,240,0.15);
}
#intro-pickle {
    font-size: 4rem;
    line-height: 1;
    margin-bottom: 16px;
    filter: drop-shadow(0 0 18px rgba(115,103,240,0.6));
    animation: pickleFloat 3s ease-in-out infinite;
}
@keyframes pickleFloat {
    0%, 100% { transform: translateY(0); }
    50%       { transform: translateY(-8px); }
}
#intro-title {
    font-size: 1.75rem;
    font-weight: 900;
    color: #fff;
    margin: 0 0 4px;
    letter-spacing: -0.01em;
}
#intro-subtitle {
    font-size: 0.9rem;
    font-weight: 600;
    color: #7367f0;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    margin: 0 0 28px;
}
#intro-body {
    text-align: left;
    color: #bbb;
    font-size: 0.92rem;
    line-height: 1.65;
    margin-bottom: 28px;
}
#intro-body p {
    margin: 0 0 16px;
}
#intro-body strong {
    color: #e0e0e0;
}
.intro-rule-block {
    background: #16161f;
    border: 1px solid #2e2e44;
    border-radius: 10px;
    padding: 18px 20px;
    margin-bottom: 16px;
}
.intro-rule-label {
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: #7367f0;
    margin-bottom: 12px;
}
ol.intro-rules {
    margin: 0;
    padding-left: 20px;
}
ol.intro-rules li {
    margin-bottom: 10px;
    color: #bbb;
}
ol.intro-rules li:last-child {
    margin-bottom: 0;
}
.intro-footer-note {
    font-size: 0.82rem;
    color: #666;
    font-style: italic;
    text-align: center;
    margin: 0 !important;
}
#intro-start-btn {
    display: block;
    width: 100%;
    padding: 14px 20px;
    font-size: 1rem;
    font-weight: 800;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: #fff;
    background: linear-gradient(135deg, #7367f0 0%, #4a42b8 100%);
    border: none;
    border-radius: 10px;
    cursor: pointer;
    box-shadow: 0 4px 24px rgba(115,103,240,0.5);
    transition: box-shadow 0.2s, transform 0.1s;
}
#intro-start-btn:hover {
    box-shadow: 0 6px 32px rgba(115,103,240,0.7);
    transform: translateY(-1px);
}
#intro-start-btn:active {
    transform: translateY(1px);
    box-shadow: 0 2px 12px rgba(115,103,240,0.35);
}
/* Step dots */
#intro-step-dots {
    display: flex;
    justify-content: center;
    gap: 8px;
    margin-bottom: 24px;
}
.intro-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #3a3a5a;
    transition: background 0.3s;
}
.intro-dot.active {
    background: #7367f0;
}
/* Next button */
#intro-next-btn {
    display: block;
    width: 100%;
    padding: 14px 20px;
    font-size: 1rem;
    font-weight: 800;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: #fff;
    background: linear-gradient(135deg, #7367f0 0%, #4a42b8 100%);
    border: none;
    border-radius: 10px;
    cursor: pointer;
    box-shadow: 0 4px 24px rgba(115,103,240,0.5);
    transition: box-shadow 0.2s, transform 0.1s;
}
#intro-next-btn:hover {
    box-shadow: 0 6px 32px rgba(115,103,240,0.7);
    transform: translateY(-1px);
}
#intro-next-btn:active {
    transform: translateY(1px);
    box-shadow: 0 2px 12px rgba(115,103,240,0.35);
}
/* Stats list */
ul.intro-stats-list {
    list-style: none;
    padding: 0;
    margin: 0 0 16px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}
ul.intro-stats-list li {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    background: #16161f;
    border: 1px solid #2e2e44;
    border-radius: 10px;
    padding: 12px 14px;
}
.stat-highlight {
    font-size: 1rem;
    font-weight: 900;
    color: #7367f0;
    min-width: 52px;
    text-align: right;
    white-space: nowrap;
    flex-shrink: 0;
    line-height: 1.5;
}
.stat-desc {
    font-size: 0.88rem;
    color: #bbb;
    line-height: 1.55;
}

/* ── Seed Randomizer ─────────────────────────────────────────────── */
.seed-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 10px;
    margin-bottom: 16px;
}
@media (max-width: 700px) {
    .seed-grid { grid-template-columns: repeat(2, 1fr); }
}
.seed-slot {
    display: flex;
    align-items: center;
    gap: 10px;
    background: #222;
    border: 1px solid #3a3a3a;
    border-radius: 8px;
    padding: 10px 14px;
    min-height: 50px;
    transition: border-color 0.25s, box-shadow 0.25s;
}
.seed-slot .seed-num {
    font-size: 1.5rem;
    font-weight: 900;
    color: #7367f0;
    min-width: 28px;
    text-align: center;
    line-height: 1;
    font-variant-numeric: tabular-nums;
    flex-shrink: 0;
}
.seed-slot .seed-name {
    font-size: 0.95rem;
    font-weight: 600;
    color: #888;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    transition: color 0.15s, filter 0.1s;
}
.seed-slot.rolling {
    border-color: #f0963b;
    box-shadow: 0 0 14px rgba(240, 150, 59, 0.35);
}
.seed-slot.rolling .seed-name {
    color: #f0963b;
    filter: blur(0.7px);
}
.seed-slot.locked {
    border-color: #28a745;
    box-shadow: 0 0 12px rgba(40, 167, 69, 0.32);
}
.seed-slot.locked .seed-name {
    color: #28a745;
    font-weight: 700;
    filter: none;
}
@keyframes seedLockBurst {
    0%   { box-shadow: 0 0 30px rgba(40,167,69,0.9); transform: scale(1.04); }
    55%  { transform: scale(0.97); }
    100% { box-shadow: 0 0 12px rgba(40,167,69,0.32); transform: scale(1); }
}
.seed-slot.lock-anim {
    animation: seedLockBurst 0.45s ease-out forwards;
}
.seed-randomize-btn {
    width: 100%;
    padding: 13px 20px;
    font-size: 1rem;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: #fff;
    background: linear-gradient(135deg, #7367f0 0%, #4a42b8 100%);
    border: none;
    border-radius: 8px;
    cursor: pointer;
    box-shadow: 0 4px 18px rgba(115,103,240,0.45);
    transition: box-shadow 0.2s, transform 0.1s, opacity 0.2s;
}
.seed-randomize-btn:hover:not(:disabled) {
    box-shadow: 0 6px 26px rgba(115,103,240,0.65);
    transform: translateY(-1px);
}
.seed-randomize-btn:active:not(:disabled) {
    transform: translateY(1px);
    box-shadow: 0 2px 10px rgba(115,103,240,0.3);
}
.seed-randomize-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
}
.seed-randomize-btn.is-rolling {
    background: linear-gradient(135deg, #f0963b 0%, #c97420 100%);
    box-shadow: 0 4px 18px rgba(240,150,59,0.45);
    animation: btnRollPulse 0.65s ease infinite;
}
@keyframes btnRollPulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.8; }
}

/* ── Bracket layout ──────────────────────────────────────────────── */
.bracket-section-label {
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: #888;
    margin-bottom: 4px;
}
.bracket-col {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: space-around;
    min-width: 150px;
    padding: 0 6px;
}
.bracket-col-label {
    font-size: 0.65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .07em;
    color: #aaa;
    text-align: center;
    margin-bottom: 6px;
    height: 16px;
}
.bracket-row {
    display: flex;
    flex-direction: row;
    align-items: flex-start;
    gap: 0;
}
/* Match card */
.match-card {
    border: 1px solid #444;
    border-radius: 6px;
    background: #2a2a2a;
    width: 142px;
    font-size: 0.8rem;
    cursor: default;
    position: relative;
    margin: 4px 0;
    transition: box-shadow .15s;
}
.match-card.clickable {
    cursor: pointer;
}
.match-card.clickable:hover {
    box-shadow: 0 0 0 2px #7367f0;
}
.match-card.completed {
    border-color: #28a745;
}
.match-badge {
    position: absolute;
    top: -8px;
    left: 50%;
    transform: translateX(-50%);
    background: #7367f0;
    color: #fff;
    font-size: 0.6rem;
    font-weight: 700;
    border-radius: 10px;
    padding: 1px 7px;
    white-space: nowrap;
    z-index: 2;
}
.match-badge-label {
    font-weight: 400;
    opacity: 0.85;
}
.match-card.consolation .match-badge {
    background: #f0963b;
}
.match-card.grand-final .match-badge {
    background: #28a745;
}
.match-slot {
    padding: 5px 8px;
    border-bottom: 1px solid #3a3a3a;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 142px;
    color: #ccc;
    height: 28px;
    line-height: 18px;
}
.match-slot:last-child {
    border-bottom: none;
}
.match-slot.tbd {
    color: #999;
    font-style: italic;
}
.match-seed {
    display: inline-block;
    font-size: 0.6rem;
    font-weight: 700;
    color: #888;
    min-width: 16px;
    margin-right: 4px;
}
.match-slot.winner-slot {
    color: #28a745;
    font-weight: 700;
}
.match-slot.loser-slot {
    color: #888;
    text-decoration: line-through;
}
/* Connector lines between match cards */
.connector-wrap {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: space-around;
    width: 20px;
    align-self: stretch;
}
.connector-line {
    width: 20px;
    position: relative;
}
/* Divider between WB and LB */
.bracket-divider {
    border-top: 1px solid #3a3a3a;
    margin: 16px 0;
}
.bracket-area-label {
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .1em;
    color: #666;
    margin-bottom: 8px;
}
/* Winner pick buttons in modal */
.winner-pick-btn.selected {
    background: #7367f0;
    color: #fff;
    border-color: #7367f0;
}
/* Matchups list */
.match-badge-sm {
    display: inline-block;
    background: #7367f0;
    color: #fff;
    font-size: 0.65rem;
    font-weight: 700;
    border-radius: 10px;
    padding: 1px 7px;
    white-space: nowrap;
}
.match-badge-sm.consolation { background: #f0963b; }
.match-badge-sm.grand-final { background: #28a745; }
.matchup-row.clickable-row { cursor: pointer; }
.matchup-row.clickable-row:hover { background: #333; color: #eee; }
.matchup-row.clickable-row:hover td,
.matchup-row.clickable-row:hover .text-muted,
.matchup-row.clickable-row:hover .loser-name { color: #eee !important; }
.matchup-row .winner-name { color: #28a745; font-weight: 700; }
.matchup-row .loser-name  { color: #888; text-decoration: line-through; }
/* Match modal — force LTR layout against RTL theme overrides */
#matchModal .modal-content { direction: ltr; }
#matchModal .modal-header {
    display: flex !important;
    flex-direction: row !important;
    justify-content: space-between !important;
    align-items: center !important;
}
#matchModal .modal-header .close {
    margin: 0 0 0 auto !important;
    padding: 0 !important;
    float: none !important;
    order: 2;
}
#matchModal .modal-header .modal-title { order: 1; }
#matchModal .modal-footer {
    display: flex !important;
    flex-direction: row !important;
    justify-content: flex-end !important;
}
#matchModal .modal-footer > * { margin-left: .25rem !important; margin-right: 0 !important; }
</style>

<script>
// ── Seeds ────────────────────────────────────────────────────────────────────
// ALL_MANAGERS = every manager name (from DB, order irrelevant here)
// SEEDS = current seeding order, populated by the randomizer
const ALL_MANAGERS = <?php echo json_encode($seeds); ?>;
const SEEDS = [];  // starts empty; filled by randomizer
const SEED_MAP = {};

// ── Seed Randomizer ──────────────────────────────────────────────────────────
let isRandomizing = false;

function fisherYates(arr) {
    const a = [...arr];
    for (let i = a.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [a[i], a[j]] = [a[j], a[i]];
    }
    return a;
}

function syncSeedSlots() {
    document.querySelectorAll('#seed-grid .seed-slot').forEach((slot, i) => {
        const nameEl = slot.querySelector('.seed-name');
        nameEl.textContent = SEEDS[i] || '—';
        slot.classList.remove('rolling', 'lock-anim');
        if (SEEDS[i]) {
            slot.classList.add('locked');
        } else {
            slot.classList.remove('locked');
        }
    });
}

document.addEventListener('DOMContentLoaded', function () {
    syncSeedSlots();

    document.getElementById('randomize-btn').addEventListener('click', function () {
        if (isRandomizing) return;

        const hasResults = Object.keys(results).length > 0;
        if (hasResults && !confirm('Randomizing will reset the current bracket results. Continue?')) return;

        isRandomizing = true;
        const btn = this;
        btn.disabled = true;
        btn.classList.add('is-rolling');
        btn.textContent = '⏳  Randomizing…';

        const newOrder = fisherYates(ALL_MANAGERS);
        const slots = Array.from(document.querySelectorAll('#seed-grid .seed-slot'));
        const intervals = [];

        // All slots roll simultaneously with different starting offsets
        slots.forEach(function (slot, i) {
            slot.classList.remove('locked', 'lock-anim');
            slot.classList.add('rolling');
            const nameEl = slot.querySelector('.seed-name');
            let idx = Math.floor(Math.random() * ALL_MANAGERS.length);
            const iv = setInterval(function () {
                nameEl.textContent = ALL_MANAGERS[idx % ALL_MANAGERS.length];
                idx++;
            }, 60);
            intervals.push(iv);
        });

        // Lock slots one at a time
        const ROLL_DUR = 1500;
        const LOCK_GAP = 180;

        newOrder.forEach(function (name, i) {
            setTimeout(function () {
                clearInterval(intervals[i]);
                const slot = slots[i];
                slot.classList.remove('rolling');
                slot.querySelector('.seed-name').textContent = name;
                slot.classList.add('locked');
                slot.classList.remove('lock-anim');
                void slot.offsetWidth; // force reflow for animation restart
                slot.classList.add('lock-anim');
                setTimeout(function () { slot.classList.remove('lock-anim'); }, 460);

                if (i === newOrder.length - 1) {
                    setTimeout(function () {
                        // Update SEEDS and SEED_MAP in-place
                        SEEDS.length = 0;
                        newOrder.forEach(function (n) { SEEDS.push(n); });
                        Object.keys(SEED_MAP).forEach(function (k) { delete SEED_MAP[k]; });
                        SEEDS.forEach(function (n, idx) { SEED_MAP[n] = idx + 1; });

                        // Reset bracket (seeds changed = fresh start), persist seeds
                        results = {};
                        lastResultsHash = null;
                        Object.keys(matchQuestions).forEach(function (k) { delete matchQuestions[k]; });
                        fetch('/data/draftOrderBracket.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ reset: true, seeds: SEEDS.slice() })
                        }).catch(function () {});

                        renderBracket();
                        renderMatchupsList();
                        renderDraftTable();

                        isRandomizing = false;
                        btn.disabled = false;
                        btn.classList.remove('is-rolling');
                        btn.textContent = '🎲  Randomize Again';
                    }, 500);
                }
            }, ROLL_DUR + i * LOCK_GAP);
        });
    });
});

// ── Bracket definition ───────────────────────────────────────────────────────
// Each match: { id, label, type, p1src, p2src }
// src format: "seed:N" | "winner:M" | "loser:M" | "bye:M" (auto-advance)
// type: "wb" | "lb" | "consolation" | "grand-final"
const MATCH_DEFS = {
    // ── Play-in ───────────────────────────────────────────────────────────────
     1: { label:'Play-in',              type:'wb',          p1src:'seed:8',    p2src:'seed:9'    },
     2: { label:'Play-in',              type:'wb',          p1src:'seed:7',    p2src:'seed:10'   },
    // ── Winners Bracket R1 ────────────────────────────────────────────────────
     3: { label:'WB R1',                type:'wb',          p1src:'seed:1',    p2src:'winner:1'  },
     4: { label:'WB R1',                type:'wb',          p1src:'seed:2',    p2src:'winner:2'  },
     5: { label:'WB R1',                type:'wb',          p1src:'seed:3',    p2src:'seed:6'    },
     6: { label:'WB R1',                type:'wb',          p1src:'seed:4',    p2src:'seed:5'    },
    // ── Losers Bracket R1 ─────────────────────────────────────────────────────
     7: { label:'LB R1',                type:'lb',          p1src:'loser:1',   p2src:'loser:3'   },
     8: { label:'LB R1',                type:'lb',          p1src:'loser:2',   p2src:'loser:4'   },
     9: { label:'LB R1',                type:'lb',          p1src:'loser:5',   p2src:'loser:6'   },
    10: { label:'10th pick',            type:'consolation', p1src:'loser:7',   p2src:'loser:8'   },
    // ── 8th/9th — before WB R2 ────────────────────────────────────────────────
    11: { label:'8th/9th pick',         type:'consolation', p1src:'winner:10', p2src:'loser:9'   },
    // ── Winners Bracket R2 ────────────────────────────────────────────────────
    12: { label:'WB R2',                type:'wb',          p1src:'winner:3',  p2src:'winner:4'  },
    13: { label:'WB R2',                type:'wb',          p1src:'winner:5',  p2src:'winner:6'  },
    // ── Losers Bracket R2 ─────────────────────────────────────────────────────
    14: { label:'LB R2',                type:'lb',          p1src:'loser:12',  p2src:'winner:7'  },
    15: { label:'LB R2',                type:'lb',          p1src:'loser:13',  p2src:'winner:8'  },
    // ── 6th/7th & WB SF ───────────────────────────────────────────────────────
    16: { label:'6th/7th pick',         type:'consolation', p1src:'loser:14',  p2src:'loser:15'  },
    17: { label:'WB SF',                type:'wb',          p1src:'winner:12', p2src:'winner:13' },
    // ── LB R3 (needs WB SF loser + LB R2 winners) ─────────────────────────────
    18: { label:'LB R3',                type:'lb',          p1src:'loser:17',  p2src:'winner:9'  },
    19: { label:'LB R3',                type:'lb',          p1src:'winner:14', p2src:'winner:15' },
    // ── 4th/5th, LB Final, Grand Final ───────────────────────────────────────
    20: { label:'4th/5th pick',         type:'consolation', p1src:'loser:18',  p2src:'loser:19'  },
    21: { label:'3rd Pick',             type:'lb',          p1src:'winner:18', p2src:'winner:19' },
    22: { label:'Grand Final · 1st/2nd Pick', type:'grand-final', p1src:'winner:17', p2src:'winner:21' },
};

// Which match result determines each draft position
// position -> { match, slot } where slot is 'winner' or 'loser'
const POSITION_SOURCES = {
    1:  { match:22, slot:'winner' },
    2:  { match:22, slot:'loser'  },
    3:  { match:21, slot:'loser'  },
    4:  { match:20, slot:'winner' },
    5:  { match:20, slot:'loser'  },
    6:  { match:16, slot:'winner' },
    7:  { match:16, slot:'loser'  },
    8:  { match:11, slot:'winner' },
    9:  { match:11, slot:'loser'  },
    10: { match:10, slot:'loser'  },
};

// ── Question templates ──────────────────────────────────────────────────────
// Use {p1} and {p2} placeholders. Yes = p1 wins, No = p2 wins,
// so phrase each question so a "Yes" answer means p1 is victorious.
const QUESTION_TEMPLATES = [
    'Would {p1} survive longer than {p2} on a deserted island with only a fantasy football magazine?',
    'Would {p1} beat {p2} in a Costco free-sample eating contest?',
    'Is {p1} too much for {p2} to handle?',
    'Is {p1} better than {p2} at blaming the refs after a loss?',
    'Can {p1} pee farther than {p2}?',
    'Is {p1} better than {p2} at negotiating trades for players no one wants?',
    'Is {p1} more likely than {p2} to lose because they forgot to set their lineup?',
    'Does {p1} send more unnecessary messages in our group chat than {p2}?',
    'Would {p1} beat {p2} in a spelling bee after three beers?',
    'Is {p1} more likely than {p2} to draft a player from their favorite team way too early?',
    'Would {p1} defeat {p2} in a dad-joke competition?',
    'Would {p1} be more likely than {p2} to score a touchdown in PeeWee football?',
    'Is {p1} more likely than {p2} to yell at the TV during a preseason game?',
    'Is {p1} more likely than {p2} to accidentally draft a retired player?',
    'If {p1} has (or hypothetically has) a sister, would {p2} have a chance with her?',
    'Would {p1} make a better optometrist than {p2}?',
    'Can {p1} deliver more UPS packages in one day than {p2}?',
    "Is {p1}'s mom better looking than {p2}'s?",
    'Does {p1} deserve a higher pick than {p2}?',
    'Should {p1} diet and exercise more than {p2}?',
    'Does {p1} need a shower more than {p2}?',
    "Will {p1} finish ahead of {p2} this season?"
];

let questionQueue = [];
function nextQuestion() {
    if (questionQueue.length === 0) {
        questionQueue = [...QUESTION_TEMPLATES].sort(() => Math.random() - 0.5);
    }
    return questionQueue.pop();
}

// ── State ────────────────────────────────────────────────────────────────────
// results[matchId] = winner name
let results = {};
let lastResultsHash = null;
const matchQuestions = {}; // matchId -> question template string

function getPlayer(src) {
    if (!src) return null;
    const [type, val] = src.split(':');
    const id = parseInt(val);
    if (type === 'seed') return SEEDS[id - 1] || null;
    if (type === 'winner') return results[id] ? results[id].winner : null;
    if (type === 'loser')  return results[id] ? results[id].loser  : null;
    return null;
}

function resolveMatch(id) {
    const def = MATCH_DEFS[id];
    const p1 = getPlayer(def.p1src);
    const p2 = getPlayer(def.p2src);
    const res = results[id] || null;
    return { p1, p2, winner: res ? res.winner : null, loser: res ? res.loser : null };
}

// ── Bracket visual columns definition ────────────────────────────────────────
// We define the visual structure as an array of "columns", each with matches
// and vertical spacing info.

// WB columns (top band)
const WB_COLS = [
    { label:'Play-in',     matches:[1,2],    spacer:1 },
    { label:'WB Round 1',  matches:[3,4,5,6], spacer:0 },
    { label:'WB Round 2',  matches:[12,13],  spacer:1 },
    { label:'WB Semifinal',matches:[17],     spacer:3 },
];
// LB columns (bottom band)
const LB_COLS = [
    { label:'LB Round 1',  matches:[7,8,9],  spacer:0 },
    { label:'10th place',  matches:[10],     spacer:1 },
    { label:'8th/9th',     matches:[11],     spacer:1 },
    { label:'LB Round 2',  matches:[14,15],  spacer:0 },
    { label:'6th/7th',     matches:[16],     spacer:1 },
    { label:'LB Round 3',  matches:[18,19],  spacer:0 },
    { label:'4th/5th',     matches:[20],     spacer:1 },
    { label:'LB Final',    matches:[21],     spacer:3 },
];
const GF_COL = { label:'Grand Final', matches:[22] };

// ── Rendering ────────────────────────────────────────────────────────────────
function slotHtml(name, role) {
    let cls = 'match-slot';
    let display;
    if (!name) {
        cls += ' tbd';
        display = 'TBD';
    } else {
        const seed = SEED_MAP[name];
        display = seed ? `<span class="match-seed">${seed}</span>${name}` : name;
        if (role === 'winner') cls += ' winner-slot';
        else if (role === 'loser') cls += ' loser-slot';
    }
    return `<div class="${cls}">${display}</div>`;
}

function matchCardHtml(id) {
    const def = MATCH_DEFS[id];
    const { p1, p2, winner, loser } = resolveMatch(id);
    const completed = !!winner;
    const clickable = (p1 && p2) ? 'clickable' : '';
    const completedCls = completed ? 'completed' : '';
    const typeCls = def.type === 'consolation' ? 'consolation' : (def.type === 'grand-final' ? 'grand-final' : '');

    const p1role = completed ? (p1 === winner ? 'winner' : 'loser') : 'normal';
    const p2role = completed ? (p2 === winner ? 'winner' : 'loser') : 'normal';

    return `
<div class="match-card ${clickable} ${completedCls} ${typeCls}" data-match="${id}">
    <div class="match-badge">M${id}<span class="match-badge-label"> &bull; ${def.label}</span></div>
    ${slotHtml(p1, p1role)}
    ${slotHtml(p2, p2role)}
</div>`;
}

function colHtml(col) {
    const spacer = col.spacer || 0;
    const topPad = spacer * 36; // px
    const gaps = col.matches.length > 1 ? `gap:${Math.max(16, spacer * 18 + 24)}px` : '';
    const matchesHtml = col.matches.map(id => matchCardHtml(id)).join('');
    return `
<div class="bracket-col" style="padding-top:${topPad}px;${gaps ? 'justify-content:space-around;' : 'justify-content:center;'}">
    <div class="bracket-col-label">${col.label}</div>
    <div class="d-flex flex-column" style="${gaps}">${matchesHtml}</div>
</div>`;
}

function renderBracket() {
    const wbHtml = `
<div class="bracket-area-label">Winners Bracket</div>
<div class="bracket-row">${WB_COLS.map(colHtml).join('')}</div>`;

    const lbHtml = `
<div class="bracket-area-label mt-2">Losers Bracket</div>
<div class="bracket-row">${LB_COLS.map(colHtml).join('')}</div>`;

    const gfHtml = `
<div class="bracket-area-label mt-2">Grand Final</div>
<div class="bracket-row">${colHtml(GF_COL)}</div>`;

    document.getElementById('bracket-container').innerHTML = wbHtml + '<div class="bracket-divider"></div>' + lbHtml + '<div class="bracket-divider"></div>' + gfHtml;

    // Attach click handlers
    document.querySelectorAll('.match-card.clickable').forEach(card => {
        card.addEventListener('click', () => openModal(parseInt(card.dataset.match)));
    });
}

function renderMatchupsList() {
    let html = '';
    for (let id = 1; id <= 22; id++) {
        const def = MATCH_DEFS[id];
        const { p1, p2, winner, loser } = resolveMatch(id);
        const ready = !!(p1 && p2);
        const completed = !!winner;

        let badgeCls = 'match-badge-sm';
        if (def.type === 'consolation') badgeCls += ' consolation';
        else if (def.type === 'grand-final') badgeCls += ' grand-final';

        let resultHtml;
        if (completed) {
            resultHtml = `<span class="winner-name">${winner}</span> <span class="text-muted">def.</span> <span class="loser-name">${loser}</span>`;
        } else if (ready) {
            resultHtml = `${p1} <span class="text-muted">vs</span> ${p2}`;
        } else {
            const left = p1 || 'TBD';
            const right = p2 || 'TBD';
            resultHtml = `<span class="text-muted font-italic">${left} vs ${right}</span>`;
        }

        const rowCls = ready ? 'matchup-row clickable-row' : 'matchup-row';
        html += `<tr class="${rowCls}" data-match="${id}">
            <td><span class="${badgeCls}">M${id}</span></td>
            <td class="text-muted" style="white-space:nowrap;">${def.label}</td>
            <td>${resultHtml}</td>
        </tr>`;
    }
    document.getElementById('matchups-table').innerHTML = html;
    document.querySelectorAll('#matchups-table .clickable-row').forEach(row => {
        row.addEventListener('click', () => openModal(parseInt(row.dataset.match)));
    });
}

function renderDraftTable() {
    let html = '';
    for (let pos = 1; pos <= 10; pos++) {
        const src = POSITION_SOURCES[pos];
        const { winner, loser } = resolveMatch(src.match);
        const manager = src.slot === 'winner' ? winner : loser;
        const ordinals = ['1st','2nd','3rd','4th','5th','6th','7th','8th','9th','10th'];
        const finishLabel = ordinals[pos - 1];
        const managerHtml = manager
            ? `<strong>${manager}</strong>`
            : `<span class="text-muted">TBD</span>`;
        html += `<tr><td>${pos}</td><td>${finishLabel}</td><td>${managerHtml}</td></tr>`;
    }
    document.getElementById('draft-order-table').innerHTML = html;
}

// ── Modal ────────────────────────────────────────────────────────────────────
let activeMatchId = null;
let selectedWinner = null;

function openModal(id) {
    const { p1, p2, winner } = resolveMatch(id);
    if (!p1 || !p2) return;

    activeMatchId = id;
    selectedWinner = winner || null;

    document.getElementById('modal-match-num').textContent = id;
    if (!matchQuestions[id]) matchQuestions[id] = nextQuestion();
    const template = matchQuestions[id];
    document.getElementById('modal-question').innerHTML = template
        .replace('{p1}', `<strong>${p1}</strong>`)
        .replace('{p2}', `<strong>${p2}</strong>`);

    const yesBtn = document.getElementById('modal-yes-btn');
    const noBtn  = document.getElementById('modal-no-btn');
    yesBtn.dataset.p1 = p1;
    yesBtn.dataset.p2 = p2;
    noBtn.dataset.p1  = p1;
    noBtn.dataset.p2  = p2;

    // Yes = p1 wins, No = p2 wins
    yesBtn.classList.toggle('selected', winner === p1);
    noBtn.classList.toggle('selected',  winner === p2);

    const display = document.getElementById('modal-selection-display');
    const submitBtn = document.getElementById('modal-submit-btn');
    if (winner) {
        document.getElementById('modal-selected-winner').textContent = winner;
        display.style.display = '';
        submitBtn.disabled = false;
    } else {
        display.style.display = 'none';
        submitBtn.disabled = true;
    }

    $('#matchModal').modal('show');
}

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('modal-yes-btn').addEventListener('click', () => {
        document.querySelectorAll('.winner-pick-btn').forEach(b => b.classList.remove('selected'));
        document.getElementById('modal-yes-btn').classList.add('selected');
        selectedWinner = document.getElementById('modal-yes-btn').dataset.p1;
        document.getElementById('modal-selected-winner').textContent = selectedWinner;
        document.getElementById('modal-selection-display').style.display = '';
        document.getElementById('modal-submit-btn').disabled = false;
    });
    document.getElementById('modal-no-btn').addEventListener('click', () => {
        document.querySelectorAll('.winner-pick-btn').forEach(b => b.classList.remove('selected'));
        document.getElementById('modal-no-btn').classList.add('selected');
        selectedWinner = document.getElementById('modal-no-btn').dataset.p2;
        document.getElementById('modal-selected-winner').textContent = selectedWinner;
        document.getElementById('modal-selection-display').style.display = '';
        document.getElementById('modal-submit-btn').disabled = false;
    });

    document.getElementById('modal-submit-btn').addEventListener('click', () => {
        if (!activeMatchId || !selectedWinner) return;
        saveResult(activeMatchId, selectedWinner);
        $('#matchModal').modal('hide');
    });
});

// ── Save / Load ──────────────────────────────────────────────────────────────
function saveResult(matchId, winner) {
    fetch('/data/draftOrderBracket.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ matchId, winner, question: matchQuestions[matchId] || null })
    })
    .then(r => r.json())
    .then(data => {
        lastResultsHash = JSON.stringify(data.results || {});
        applyServerState(data.results || {});
    });
}

function applyServerState(rawResults) {
    // rawResults: { "1": "AJ", "2": "Matt", ... } — winner names keyed by matchId
    // We need to derive loser too
    results = {};
    // First pass: record winners
    const winnerMap = {};
    for (const [id, winner] of Object.entries(rawResults)) {
        winnerMap[parseInt(id)] = winner;
    }
    // Second pass: derive losers by checking the two participants
    for (const [idStr, winner] of Object.entries(winnerMap)) {
        const id = parseInt(idStr);
        // Temporarily set winner so getPlayer can resolve downstream deps
        results[id] = { winner, loser: null };
    }
    // Now resolve losers — need to iterate in match order since earlier matches feed later ones
    for (let id = 1; id <= 22; id++) {
        if (!winnerMap[id]) continue;
        const { p1, p2 } = resolveMatch(id);
        const winner = winnerMap[id];
        const loser = (p1 === winner) ? p2 : p1;
        results[id] = { winner, loser };
    }

    renderBracket();
    renderMatchupsList();
    renderDraftTable();
}

document.getElementById('reset-btn').addEventListener('click', () => {
    if (!confirm('Reset the entire bracket? This cannot be undone.')) return;
    fetch('/data/draftOrderBracket.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ reset: true, clearSeeds: true })
    })
    .then(r => r.json())
    .then(data => {
        lastResultsHash = JSON.stringify(data.results || {});
        Object.keys(matchQuestions).forEach(function (k) { delete matchQuestions[k]; });
        SEEDS.length = 0;
        Object.keys(SEED_MAP).forEach(function (k) { delete SEED_MAP[k]; });
        syncSeedSlots();
        applyServerState(data.results || {});
    });
});

// ── Seed restore helper ──────────────────────────────────────────────────────
function restoreSeedsFromData(data) {
    if (!data.seeds || !data.seeds.length) return;
    SEEDS.length = 0;
    data.seeds.forEach(function (n) { SEEDS.push(n); });
    Object.keys(SEED_MAP).forEach(function (k) { delete SEED_MAP[k]; });
    SEEDS.forEach(function (n, i) { SEED_MAP[n] = i + 1; });
    syncSeedSlots();
}

function restoreQuestionsFromData(data) {
    if (!data.questions) return;
    Object.entries(data.questions).forEach(function ([id, q]) {
        matchQuestions[parseInt(id)] = q;
    });
}

// ── Intro overlay ────────────────────────────────────────────────────────────
document.getElementById('intro-next-btn').addEventListener('click', function () {
    const s1 = document.getElementById('intro-step-1');
    const s2 = document.getElementById('intro-step-2');
    s1.style.transition = 'opacity 0.25s ease';
    s1.style.opacity = '0';
    setTimeout(function () {
        s1.style.display = 'none';
        s2.style.display = '';
        s2.style.opacity = '0';
        s2.style.transition = 'opacity 0.3s ease';
        requestAnimationFrame(function () {
            requestAnimationFrame(function () { s2.style.opacity = '1'; });
        });
    }, 250);
    document.querySelectorAll('.intro-dot').forEach(function (d, i) {
        d.classList.toggle('active', i === 1);
    });
});

document.getElementById('intro-start-btn').addEventListener('click', function () {
    const overlay = document.getElementById('intro-overlay');
    overlay.style.transition = 'opacity 0.4s ease';
    overlay.style.opacity = '0';
    setTimeout(function () { overlay.style.display = 'none'; }, 400);
});

// Initial load
fetch('/data/draftOrderBracket.json?t=' + Date.now())
    .then(r => r.ok ? r.json() : { results: {} })
    .then(data => {
        lastResultsHash = JSON.stringify(data.results || {});
        restoreSeedsFromData(data);
        restoreQuestionsFromData(data);
        applyServerState(data.results || {});
    })
    .catch(() => {
        applyServerState({});
    });
</script>

<div class="footer" style="direction: ltr;">
    Copyright <?php echo date("Y"); ?> &copy; Suntown FFB.
    &nbsp;|&nbsp;
    <a href="/">Home</a>
</div>

<script src="/assets/datatables.min.js"></script>
<script src="/assets/tether.min.js"></script>
<script src="/assets/bootstrap.min.js"></script>
</body>
</html>
