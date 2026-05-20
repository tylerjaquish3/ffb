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
// ── Seeds from PHP ───────────────────────────────────────────────────────────
const SEEDS = <?php echo json_encode($seeds); ?>;
// SEEDS[0]=seed1(AJ), SEEDS[9]=seed10(Gavin)
const SEED_MAP = {};
SEEDS.forEach((name, i) => { SEED_MAP[name] = i + 1; });

// ── Bracket definition ───────────────────────────────────────────────────────
// Each match: { id, label, type, p1src, p2src }
// src format: "seed:N" | "winner:M" | "loser:M" | "bye:M" (auto-advance)
// type: "wb" | "lb" | "consolation" | "grand-final"
const MATCH_DEFS = {
    // ── Play-in (bottom 4 seeds) ──────────────────────────────────────────────
     1: { label:'Play-in',   type:'wb',           p1src:'seed:8',    p2src:'seed:9'   },
     2: { label:'Play-in',   type:'wb',           p1src:'seed:7',    p2src:'seed:10'  },
    // ── Winners Bracket R1 ────────────────────────────────────────────────────
     3: { label:'WB R1',     type:'wb',           p1src:'seed:1',    p2src:'winner:1' },
     4: { label:'WB R1',     type:'wb',           p1src:'seed:2',    p2src:'winner:2' },
     5: { label:'WB R1',     type:'wb',           p1src:'seed:3',    p2src:'seed:6'   },
     6: { label:'WB R1',     type:'wb',           p1src:'seed:4',    p2src:'seed:5'   },
    // ── Losers Bracket R1 (before WB R2) ─────────────────────────────────────
     7: { label:'LB R1',     type:'lb',           p1src:'loser:1',   p2src:'loser:3'  },
     8: { label:'LB R1',     type:'lb',           p1src:'loser:2',   p2src:'loser:4'  },
     9: { label:'LB R1',     type:'lb',           p1src:'loser:5',   p2src:'loser:6'  },
    10: { label:'10th place',type:'consolation',  p1src:'loser:7',   p2src:'loser:8'  },
    // ── Winners Bracket R2 & SF ───────────────────────────────────────────────
    11: { label:'WB R2',     type:'wb',           p1src:'winner:3',  p2src:'winner:4' },
    12: { label:'WB R2',     type:'wb',           p1src:'winner:5',  p2src:'winner:6' },
    // ── Losers Bracket continued (before WB SF) ──────────────────────────────
    13: { label:'8th/9th',   type:'consolation',  p1src:'winner:10', p2src:'loser:9'  },
    14: { label:'LB R2',     type:'lb',           p1src:'loser:11',  p2src:'winner:7' },
    15: { label:'LB R2',     type:'lb',           p1src:'loser:12',  p2src:'winner:8' },
    // ── Winners Bracket SF ────────────────────────────────────────────────────
    16: { label:'WB SF',     type:'wb',           p1src:'winner:11', p2src:'winner:12' },
    17: { label:'6th/7th',   type:'consolation',  p1src:'loser:14',  p2src:'loser:15' },
    18: { label:'LB R3',     type:'lb',           p1src:'loser:16',  p2src:'winner:9' },
    19: { label:'LB R3',     type:'lb',           p1src:'winner:14', p2src:'winner:15' },
    20: { label:'4th/5th',   type:'consolation',  p1src:'loser:18',  p2src:'loser:19' },
    21: { label:'LB Final',  type:'lb',           p1src:'winner:18', p2src:'winner:19' },
    22: { label:'Grand Final',type:'grand-final', p1src:'winner:16', p2src:'winner:21' },
};

// Which match result determines each draft position
// position -> { match, slot } where slot is 'winner' or 'loser'
const POSITION_SOURCES = {
    1:  { match:22, slot:'winner' },
    2:  { match:22, slot:'loser'  },
    3:  { match:21, slot:'loser'  },
    4:  { match:20, slot:'winner' },
    5:  { match:20, slot:'loser'  },
    6:  { match:17, slot:'winner' },
    7:  { match:17, slot:'loser'  },
    8:  { match:13, slot:'winner' },
    9:  { match:13, slot:'loser'  },
    10: { match:10, slot:'loser'  },
};

// ── Question templates ──────────────────────────────────────────────────────
// Use {p1} and {p2} placeholders. Yes = p1 wins, No = p2 wins,
// so phrase each question so a "Yes" answer means p1 is victorious.
const QUESTION_TEMPLATES = [
    'Would {p1} survive longer than {p2} on a deserted island with only a fantasy football magazine?',
    'Would {p1} beat {p2} in a Costco free-sample eating contest?',
    'Is {p1} too much for {p2} to handle?',
    'Is {p1} better than {p2} at blaming the refs?',
    'Would {p1} beat {p2} in a race through an airport terminal?',
    'Is {p1} better than {p2} at negotiating fake trades no one wants?',
    'Is {p1} more likely than {p2} to lose because they forgot to set their lineup?',
    'Would {p1} beat {p2} in a karaoke battle singing 2000s emo songs?',
    'Would {p1} beat {p2} in a spelling bee after three beers?',
    'Is {p1} more likely than {p2} to draft a player from their favorite team way too early?',
    'Would {p1} defeat {p2} in a dad-joke competition?',
    'Would {p1} be more likely than {p2} to score a touchdown in PeeWee football?',
    'Is {p1} more likely than {p2} to yell at the TV during a preseason game?',
    'Would {p1} beat {p2} in a race to assemble IKEA furniture?',
    'Is {p1} more likely than {p2} to get kicked out of a Vegas sportsbook?',
    'Would {p1} make a better optometrist than {p2}?',
    'Can {p1} deliver more UPS packages in one day than {p2}?',
    "Is {p1}'s mom better looking than {p2}'s?",
    'Does {p1} deserve a higher pick than {p2}?',
    'Should {p1} diet and exercise more than {p2}?',
    'Does {p1} need a shower more than {p2}?',
    "Is {p1}'s dad prouder of him than {p2}'s dad is of him?"
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
    { label:'Play-in',     matches:[1,2],            spacer:1 },
    { label:'WB Round 1',  matches:[3,4,5,6],        spacer:0 },
    { label:'WB Round 2',  matches:[11,12],           spacer:1 },
    { label:'WB Semifinal',matches:[16],             spacer:3 },
];
// LB columns (bottom band)
const LB_COLS = [
    { label:'LB Round 1',  matches:[7,8,9],          spacer:0 },
    { label:'10th place',  matches:[10],             spacer:1 },
    { label:'8th/9th',     matches:[13],             spacer:1 },
    { label:'LB Round 2',  matches:[14,15],          spacer:0 },
    { label:'6th/7th',     matches:[17],             spacer:1 },
    { label:'LB Round 3',  matches:[18,19],          spacer:0 },
    { label:'4th/5th',     matches:[20],             spacer:1 },
    { label:'LB Final',    matches:[21],             spacer:3 },
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
    <div class="match-badge">M${id}</div>
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
    const template = nextQuestion();
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
        body: JSON.stringify({ matchId, winner })
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
        body: JSON.stringify({ reset: true })
    })
    .then(r => r.json())
    .then(data => {
        lastResultsHash = JSON.stringify(data.results || {});
        applyServerState(data.results || {});
    });
});

// ── Polling for live updates ─────────────────────────────────────────────────
function pollForUpdates() {
    // Skip poll while modal is open to avoid disrupting an active selection
    if ($('#matchModal').hasClass('show')) return;

    fetch('/data/draftOrderBracket.json')
        .then(r => r.json())
        .then(data => {
            const hash = JSON.stringify(data.results || {});
            if (hash !== lastResultsHash) {
                lastResultsHash = hash;
                applyServerState(data.results || {});
            }
        })
        .catch(() => {}); // silently ignore network errors during poll
}

// Initial load
fetch('/data/draftOrderBracket.json')
    .then(r => r.ok ? r.json() : { results: {} })
    .then(data => {
        lastResultsHash = JSON.stringify(data.results || {});
        applyServerState(data.results || {});
        setInterval(pollForUpdates, 10000);
    })
    .catch(() => {
        applyServerState({});
        setInterval(pollForUpdates, 10000);
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
