<?php

$pageName = "Trophy";
include 'header.php';
include 'sidebar.php';

?>
<style>
@import url('https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700;900&family=Cormorant+SC:wght@300;400;500;600;700&display=swap');

.trophy-wrapper {
    background: #060606;
    min-height: 85vh;
    padding: 40px 20px 60px;
    display: flex;
    flex-direction: column;
    align-items: center;
    font-family: 'Cinzel', serif;
    border-radius: 6px;
    overflow: hidden;
    position: relative;
    direction: ltr;
}


/* ===== TROPHY IMAGE ===== */
.trophy-top-wrap {
    width: 420px; /* match small-base width */
    overflow: visible;
    margin-bottom: -4px;
}

.trophy-top-img {
    width: 420px;
    display: block;
}

/* ===== ROTATING UNIT ===== */
.rotating-unit {
    width: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    perspective: 1200px;
}

.rotating-inner {
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 100%;
}

@keyframes rotOutRight {
    from { transform: perspective(1200px) rotateY(0deg); }
    to   { transform: perspective(1200px) rotateY(90deg); }
}
@keyframes rotInRight {
    from { transform: perspective(1200px) rotateY(-90deg); }
    to   { transform: perspective(1200px) rotateY(0deg); }
}
@keyframes rotOutLeft {
    from { transform: perspective(1200px) rotateY(0deg); }
    to   { transform: perspective(1200px) rotateY(-90deg); }
}
@keyframes rotInLeft {
    from { transform: perspective(1200px) rotateY(90deg); }
    to   { transform: perspective(1200px) rotateY(0deg); }
}

.rotating-inner.spin-out-r { animation: rotOutRight 0.22s ease-in  forwards; }
.rotating-inner.spin-in-r  { animation: rotInRight  0.22s ease-out forwards; }
.rotating-inner.spin-out-l { animation: rotOutLeft  0.22s ease-in  forwards; }
.rotating-inner.spin-in-l  { animation: rotInLeft   0.22s ease-out forwards; }

/* ===== WOODEN BASES ===== */
.wood-base {
    position: relative;
    border-radius: 5px 5px 3px 3px;
}

.small-base {
    width: 420px;
    background:
        repeating-linear-gradient(91deg, transparent 0, transparent 2px, rgba(0,0,0,0.025) 2px, rgba(0,0,0,0.025) 3px),
        linear-gradient(180deg,
            #4a2400 0%, #7a3b10 6%, #8b4513 18%,
            #7a3b10 32%, #8b4513 46%, #6b3210 60%,
            #8b4513 74%, #7a3b10 88%, #4a2400 100%
        );
    box-shadow:
        0 -3px 0 #9a5530,
        0 -5px 0 #7a3b10,
        5px 0 10px rgba(0,0,0,0.55),
        -5px 0 10px rgba(0,0,0,0.55),
        0 18px 35px rgba(0,0,0,0.75);
}

.large-base {
    width: 540px;
    margin-top: -2px;
    background:
        repeating-linear-gradient(91deg, transparent 0, transparent 2px, rgba(0,0,0,0.025) 2px, rgba(0,0,0,0.025) 3px),
        linear-gradient(180deg,
            #3d1e00 0%, #6b3210 5%, #8b4513 18%,
            #7a3b10 34%, #8b4513 50%, #6b3210 66%,
            #8b4513 80%, #7a3b10 92%, #3d1e00 100%
        );
    box-shadow:
        0 -2px 0 #9a5530,
        6px 0 14px rgba(0,0,0,0.65),
        -6px 0 14px rgba(0,0,0,0.65),
        0 28px 55px rgba(0,0,0,0.85);
}

.base-molding-top {
    height: 16px;
    background: linear-gradient(180deg, #a06030 0%, #7a4818 50%, #4a2400 100%);
    border-radius: 4px 4px 0 0;
    box-shadow: inset 0 3px 5px rgba(255,255,255,0.08), inset 0 -2px 4px rgba(0,0,0,0.4);
}

.base-molding-bot {
    height: 12px;
    background: linear-gradient(180deg, #3d1e00 0%, #6b3a18 55%, #9a5530 100%);
    border-radius: 0 0 4px 4px;
    box-shadow: 0 6px 16px rgba(0,0,0,0.65);
}

.plaques-area {
    padding: 14px 18px;
}

/* ===== PLAQUE GRIDS ===== */

/* Front of small base: title spans full width + 3 champions below */
.grid-small-front {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 8px;
}

/* Other small base sides: 2x2 */
.grid-small-regular {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
}

/* Large base: 3x3 */
.grid-large {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 7px;
}

/* ===== PLAQUES ===== */
.plaque {
    background: #0d0d0d;
    border: 2px solid #c9a227;
    border-top-color: #ffe060;
    border-left-color: #d4af37;
    border-radius: 3px;
    padding: 9px 8px;
    min-height: 72px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
    position: relative;
    box-shadow:
        inset 0 0 12px rgba(212,175,55,0.06),
        2px 3px 8px rgba(0,0,0,0.6),
        0 0 0 1px rgba(0,0,0,0.4);
}

/* Inner engraved border */
.plaque::before {
    content: '';
    position: absolute;
    inset: 4px;
    border: 1px solid rgba(212,175,55,0.3);
    border-radius: 1px;
    pointer-events: none;
}

.plaque-year {
    font-family: 'Cinzel', serif;
    font-size: 15px;
    font-weight: 900;
    color: #d4af37;
    letter-spacing: 3px;
    text-shadow: 0 0 8px rgba(212,175,55,0.4);
    line-height: 1;
    margin-bottom: 4px;
}

.plaque-label {
    font-family: 'Cinzel', serif;
    font-size: 7px;
    font-weight: 600;
    color: #8b6914;
    letter-spacing: 2.5px;
    margin-bottom: 3px;
}

.plaque-name {
    font-family: 'Cinzel', serif;
    font-size: 9px;
    font-weight: 700;
    color: #c9a227;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    text-shadow: 0 0 6px rgba(201,162,39,0.3);
    line-height: 1.3;
}

/* Title plaque — spans all columns */
.plaque-title-item {
    grid-column: 1 / -1;
    min-height: 110px;
}

.plaque-title-text {
    font-family: 'Cormorant SC', serif;
    font-size: 16px;
    font-weight: 600;
    color: #d4af37;
    letter-spacing: 3px;
    text-transform: uppercase;
    text-shadow: 0 0 14px rgba(212,175,55,0.5);
    line-height: 1.9;
}

/* Empty slot */
.plaque-empty {
    background: #080808;
    border-color: #3a2c0a;
    border-top-color: #4a3810;
    border-left-color: #3a2c0a;
    opacity: 0.4;
    min-height: 60px;
}

.plaque-empty::before {
    border-color: rgba(100,75,20,0.2);
}

/* ===== CONTROLS ===== */
.trophy-controls {
    display: flex;
    gap: 6px;
    justify-content: center;
    margin-top: 22px;
    flex-wrap: wrap;
}

.side-btn {
    font-family: 'Cinzel', serif;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 3px;
    padding: 9px 22px;
    background: transparent;
    border: 1px solid rgba(212,175,55,0.28);
    color: rgba(212,175,55,0.5);
    cursor: pointer;
    transition: all 0.22s ease;
    text-transform: uppercase;
    border-radius: 2px;
    outline: none;
}

.side-btn:hover {
    border-color: rgba(255,215,0,0.65);
    color: #ffd700;
    box-shadow: 0 0 14px rgba(255,215,0,0.12);
}

.side-btn.active {
    background: linear-gradient(135deg, #8b6914, #c9a227);
    border-color: #ffd700;
    color: #1a0d00;
    box-shadow: 0 0 22px rgba(255,215,0,0.22), 0 2px 8px rgba(0,0,0,0.5);
}

/* Glow beneath the base */
.base-glow {
    width: 440px;
    height: 45px;
    background: radial-gradient(ellipse, rgba(212,175,55,0.1) 0%, transparent 70%);
    margin-top: -8px;
    pointer-events: none;
}

/* Side indicator */
.side-indicator {
    font-family: 'Cinzel', serif;
    font-size: 9px;
    letter-spacing: 4px;
    color: rgba(212,175,55,0.35);
    text-transform: uppercase;
    margin: 10px 0 4px;
}

/* Responsive */
@media (max-width: 600px) {
    .trophy-top-wrap { width: 320px; }
    .trophy-top-img  { width: 320px; }
    .small-base { width: 320px; }
    .large-base { width: 340px; }
    .plaque-year { font-size: 12px; }
    .plaque-name { font-size: 8px; }
    .base-glow  { width: 340px; }
}
</style>

<div class="app-content content">
    <div class="content-wrapper">
        <div class="content-body">
            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-body" style="padding: 0; overflow: hidden; border-radius: 6px;">

                            <div class="trophy-wrapper">

                                <!-- ===== TROPHY CUP ===== -->
                                <div class="trophy-top-wrap">
                                    <img src="/images/trophy top.png" class="trophy-top-img" alt="Trophy">
                                </div>

                                <!-- ===== ROTATING BASES ===== -->
                                <div class="rotating-unit">
                                    <div class="rotating-inner" id="rotatingInner">

                                        <!-- Small base -->
                                        <div class="wood-base small-base">
                                            <div class="base-molding-top"></div>
                                            <div class="plaques-area" id="smallPlaques"></div>
                                            <div class="base-molding-bot"></div>
                                        </div>

                                        <!-- Large base -->
                                        <div class="wood-base large-base">
                                            <div class="base-molding-top"></div>
                                            <div class="plaques-area" id="largePlaques"></div>
                                            <div class="base-molding-bot"></div>
                                        </div>

                                    </div>
                                </div>

                                <div class="base-glow"></div>

                                <!-- Side indicator -->
                                <div class="side-indicator" id="sideLabel">— FRONT —</div>

                                <!-- Controls -->
                                <div class="trophy-controls">
                                    <button class="side-btn active" data-side="front">FRONT</button>
                                    <button class="side-btn" data-side="right">RIGHT</button>
                                    <button class="side-btn" data-side="back">BACK</button>
                                    <button class="side-btn" data-side="left">LEFT</button>
                                </div>

                            </div><!-- /trophy-wrapper -->

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var SIDES = ['front', 'right', 'back', 'left'];
    var current = 'front';
    var busy = false;

    /* Small base: 4 plaques per side */
    var small = {
        front: [
            { title: true, text: 'SUNTOWN\nFANTASY FOOTBALL\nLEAGUE' }
        ],
        right: [
            { year: '2006', name: 'AJ' },
            { year: '2007', name: 'JUSTIN' },
            { year: '2008', name: 'TYLER' },
            { year: '2009', name: 'MATT' }
        ],
        back: [
            { year: '2010', name: 'CAMERON' },
            { year: '2011', name: 'BEN' },
            { year: '2012', name: 'AJ' },
            { year: '2013', name: 'ANDY' }
        ],
        left: [
            { year: '2014', name: 'JUSTIN' },
            { year: '2015', name: 'JUSTIN' },
            { year: '2016', name: 'COLE' },
            { year: '2017', name: 'COLE' }
        ]
    };

    /* Large base: 9 plaques per side (3×3) */
    var E = { empty: true };
    var large = {
        front: [E, E, E, E, E, E, E, E, E],
        right: [
            { year: '2018', name: 'JUSTIN' },
            { year: '2019', name: 'CAMERON' },
            { year: '2020', name: 'MATT' },
            { year: '2021', name: 'JUSTIN' },
            { year: '2022', name: 'JUSTIN' },
            { year: '2023', name: 'COLE' },
            { year: '2024', name: 'CAMERON' },
            { year: '2025', name: 'AJ' },
            E
        ],
        back:  [E, E, E, E, E, E, E, E, E],
        left:  [E, E, E, E, E, E, E, E, E]
    };

    function makePlaque(p) {
        var d = document.createElement('div');
        if (p.title) {
            d.className = 'plaque plaque-title-item';
            d.innerHTML = '<div class="plaque-title-text">' +
                p.text.replace(/\n/g, '<br>') + '</div>';
        } else if (p.empty) {
            d.className = 'plaque plaque-empty';
        } else {
            d.className = 'plaque';
            d.innerHTML =
                '<div class="plaque-year">' + p.year + '</div>' +
                '<div class="plaque-label">CHAMPION</div>' +
                '<div class="plaque-name">' + p.name + '</div>';
        }
        return d;
    }

    function renderSmall(side) {
        var el = document.getElementById('smallPlaques');
        var data = small[side];
        var hasTitle = data[0] && data[0].title;
        var grid = document.createElement('div');
        grid.className = hasTitle ? 'grid-small-front' : 'grid-small-regular';
        data.forEach(function (p) { grid.appendChild(makePlaque(p)); });
        el.innerHTML = '';
        el.appendChild(grid);
    }

    function renderLarge(side) {
        var el = document.getElementById('largePlaques');
        var data = large[side];
        var grid = document.createElement('div');
        grid.className = 'grid-large';
        data.forEach(function (p) { grid.appendChild(makePlaque(p)); });
        el.innerHTML = '';
        el.appendChild(grid);
    }

    function direction(from, to) {
        var fi = SIDES.indexOf(from), ti = SIDES.indexOf(to);
        var diff = ti - fi;
        if (diff > 2)  diff -= 4;
        if (diff < -2) diff += 4;
        return diff >= 0 ? 1 : -1;
    }

    function rotateTo(side) {
        if (side === current || busy) return;
        busy = true;

        var inner = document.getElementById('rotatingInner');
        var dir = direction(current, side);
        var outCls = dir > 0 ? 'spin-out-r' : 'spin-out-l';
        var inCls  = dir > 0 ? 'spin-in-r'  : 'spin-in-l';

        inner.classList.add(outCls);

        inner.addEventListener('animationend', function onOut() {
            inner.removeEventListener('animationend', onOut);
            inner.classList.remove(outCls);

            current = side;
            renderSmall(side);
            renderLarge(side);

            document.getElementById('sideLabel').textContent = '— ' + side.toUpperCase() + ' —';
            document.querySelectorAll('.side-btn').forEach(function (b) {
                b.classList.toggle('active', b.dataset.side === side);
            });

            inner.classList.add(inCls);
            inner.addEventListener('animationend', function onIn() {
                inner.removeEventListener('animationend', onIn);
                inner.classList.remove(inCls);
                busy = false;
            });
        });
    }

    document.querySelectorAll('.side-btn').forEach(function (btn) {
        btn.addEventListener('click', function () { rotateTo(this.dataset.side); });
    });

    renderSmall('front');
    renderLarge('front');
}());
</script>

<?php include 'footer.php'; ?>
