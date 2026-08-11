<?php
session_start();
$pageName = "2027 Draft Order";
require_once __DIR__ . '/lib.php';
include __DIR__ . '/../header.php';
include __DIR__ . '/../sidebar.php';

$year = DRAFT_ORDER_GAME_YEAR;

$loginError = '';
if (isset($_POST['draft_order_password'])) {
    if (hash_equals(DRAFT_ORDER_GAME_PASSWORD, $_POST['draft_order_password'])) {
        $_SESSION['draft_order_admin_auth'] = true;
    } else {
        $loginError = 'Incorrect password.';
    }
}
$isAdmin = !empty($_SESSION['draft_order_admin_auth']);

$pool = getPool($conn, $year);
$ineligible = getIneligiblePlayers($conn, $year);
$pickHistory = getPickHistory($conn, $year);
$picks = getCurrentPicks($pickHistory);
$managers = getManagers($conn);
$summary = getSummaryRows($conn, $year);
$defaultEffectiveWeek = getDefaultEffectiveWeek($conn, $year);
$manualPoints = getManualPoints($conn, $year);

$pickedPlayers = array_flip(array_filter($picks));

$availablePlayers = array_values(array_filter($pool, function ($p) use ($ineligible, $pickedPlayers) {
    return !isset($ineligible[$p['player']]) && !isset($pickedPlayers[$p['player']]);
}));

$ordinals = ['1st','2nd','3rd','4th','5th','6th','7th','8th','9th','10th'];
?>

<div class="app-content content container-fluid">
    <div class="content-wrapper">
        <div class="content-header row"></div>

        <div class="content-body">

            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-body" style="direction: ltr; padding: 24px;">
                            <h4 class="card-title">How It Works</h4>
                            <p>Before the <?php echo $year; ?> season, every manager picks one player from the top 100 picks of the real <?php echo $year; ?> draft — no two managers can have the same player. Whoever's player finishes the season <strong>closest to <?php echo (int)DRAFT_ORDER_GAME_TARGET; ?> fantasy points</strong> (over or under) gets the #1 pick in the <?php echo $year + 1; ?> draft, next-closest gets #2, and so on. That's an average of <?php echo number_format(DRAFT_ORDER_GAME_TARGET / DRAFT_ORDER_GAME_WEEKS, 2); ?> points per week to land right on target.</p>
                            <p>This runs the <strong>full <?php echo DRAFT_ORDER_GAME_WEEKS; ?>-week NFL regular season</strong> — not just the FFB league's own <?php echo $year; ?> schedule. Your player keeps racking up real stats through Week <?php echo DRAFT_ORDER_GAME_WEEKS; ?> no matter what, so even if your fantasy team doesn't make the FFB playoffs, you've still got something to play for all the way to the finish line.</p>
                            <p>Want to switch your player? Text the admin — only they can make the change. Switching doesn't rewrite history: points already scored by your old player stay locked in, and your new player only counts from the week the switch takes effect.</p>
                            <p class="mb-0"><strong>Tiebreakers</strong> (in order): 1) fewest pick changes during the season, 2) later overall draft pick of your current player.</p>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (empty($pool)): ?>
            <div class="row">
                <div class="col-sm-12">
                    <div class="alert alert-warning" style="direction: ltr;">
                        The <?php echo $year; ?> draft hasn't been imported yet, so there's no player pool to show. Check back after the draft.
                    </div>
                </div>
            </div>
            <?php else: ?>

            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Standings</h4>
                        </div>
                        <div class="card-body p-0" style="direction: ltr;">
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Rank</th>
                                        <th>Manager</th>
                                        <th>Player</th>
                                        <th>Pos</th>
                                        <th>Points</th>
                                        <th>Diff from <?php echo (int)DRAFT_ORDER_GAME_TARGET; ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($summary as $row): ?>
                                    <tr>
                                        <td><?php echo $row['rank'] ? $ordinals[$row['rank'] - 1] : '—'; ?></td>
                                        <td><strong><?php echo htmlspecialchars($row['manager']); ?></strong></td>
                                        <?php if ($row['player']): ?>
                                            <td>
                                                <?php echo htmlspecialchars($row['player']); ?>
                                                <?php if (count($row['history']) > 1): ?>
                                                    <div class="text-muted small">
                                                        <?php echo htmlspecialchars(implode(' → ', array_map(function ($a, $i) use ($row) {
                                                            $endWeek = ($i + 1 < count($row['history'])) ? $row['history'][$i + 1]['effective_week'] - 1 : DRAFT_ORDER_GAME_WEEKS;
                                                            return $a['player'] . ' (wk ' . $a['effective_week'] . '–' . $endWeek . ')';
                                                        }, $row['history'], array_keys($row['history'])))); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($row['position']); ?></td>
                                            <td><?php echo number_format($row['points'], 1); ?></td>
                                            <td><?php echo number_format($row['diff'], 1); ?></td>
                                        <?php else: ?>
                                            <td colspan="4" class="text-muted font-italic">No pick yet</td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Available Players</h4>
                        </div>
                        <div class="card-body p-0" style="direction: ltr; max-height:420px; overflow-y:auto;">
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Pick #</th>
                                        <th>Player</th>
                                        <th>Pos</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if (empty($availablePlayers)): ?>
                                    <tr><td colspan="3" class="text-muted font-italic">No players available.</td></tr>
                                <?php endif; ?>
                                <?php foreach ($availablePlayers as $p): ?>
                                    <tr>
                                        <td><?php echo (int)$p['overall_pick']; ?></td>
                                        <td><?php echo htmlspecialchars($p['player']); ?></td>
                                        <td><?php echo htmlspecialchars($p['position']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <?php endif; ?>

            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Admin</h4>
                        </div>
                        <div class="card-body" style="direction: ltr;">
                            <?php if (!$isAdmin): ?>
                                <?php if ($loginError): ?><div class="alert alert-danger" style="max-width:320px;"><?php echo htmlspecialchars($loginError); ?></div><?php endif; ?>
                                <form method="POST" style="max-width:320px;">
                                    <div class="form-group">
                                        <input type="password" name="draft_order_password" class="form-control" placeholder="Admin password" autofocus>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Sign in</button>
                                </form>
                            <?php elseif (empty($pool)): ?>
                                <p class="text-muted mb-0">Nothing to manage until the <?php echo $year; ?> draft is imported.</p>
                            <?php else: ?>

                                <h5 class="mb-2">Assign / Change Picks</h5>
                                <p class="text-muted small">Switching a manager's player doesn't rewrite history — points already scored by their old player stay locked in. "Effective from week" is when the new player starts counting; it defaults to the next week that hasn't been scored yet.</p>
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Manager</th>
                                            <th>Player</th>
                                            <th>Effective from week</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($managers as $manager):
                                        $currentPick = $picks[$manager['id']] ?? null;
                                        $options = $availablePlayers;
                                        if ($currentPick && !in_array($currentPick, array_column($options, 'player'), true)) {
                                            foreach ($pool as $p) {
                                                if ($p['player'] === $currentPick) { $options[] = $p; break; }
                                            }
                                        }
                                        usort($options, function ($a, $b) { return $a['overall_pick'] <=> $b['overall_pick']; });
                                    ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($manager['name']); ?></td>
                                            <td>
                                                <select class="form-control assign-select" data-manager-id="<?php echo $manager['id']; ?>" data-original-player="<?php echo htmlspecialchars($currentPick ?? ''); ?>" style="max-width:260px;">
                                                    <option value="">— No pick —</option>
                                                    <?php foreach ($options as $p): ?>
                                                        <option value="<?php echo htmlspecialchars($p['player']); ?>" <?php echo $currentPick === $p['player'] ? 'selected' : ''; ?>>
                                                            <?php echo htmlspecialchars($p['player'] . ' (' . $p['position'] . ')'); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="number" class="form-control assign-week" data-manager-id="<?php echo $manager['id']; ?>" min="1" max="<?php echo DRAFT_ORDER_GAME_WEEKS; ?>" value="<?php echo $defaultEffectiveWeek; ?>" style="max-width:90px;">
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <button type="button" id="save-all-picks-btn" class="btn btn-success">Save All Changes</button>

                                <h5 class="mb-2 mt-3">Player Eligibility</h5>
                                <div style="max-height:420px; overflow-y:auto;">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Pick #</th>
                                            <th>Player</th>
                                            <th>Pos</th>
                                            <th>Picked By</th>
                                            <th>Eligible</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($pool as $p):
                                        $pickedByManagerId = $pickedPlayers[$p['player']] ?? null;
                                        $pickedByName = '';
                                        if ($pickedByManagerId) {
                                            foreach ($managers as $m) {
                                                if ($m['id'] === $pickedByManagerId) $pickedByName = $m['name'];
                                            }
                                        }
                                    ?>
                                        <tr>
                                            <td><?php echo (int)$p['overall_pick']; ?></td>
                                            <td><?php echo htmlspecialchars($p['player']); ?></td>
                                            <td><?php echo htmlspecialchars($p['position']); ?></td>
                                            <td class="text-muted"><?php echo $pickedByName ? htmlspecialchars($pickedByName) : '—'; ?></td>
                                            <td>
                                                <input type="checkbox" class="eligibility-toggle" data-player="<?php echo htmlspecialchars($p['player']); ?>" <?php echo isset($ineligible[$p['player']]) ? '' : 'checked'; ?>>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                                </div>

                                <h5 class="mb-2 mt-3">Manual Point Overrides</h5>
                                <p class="text-muted small">Points only come from the rosters table, which only has data for players who were actually on a fantasy roster that week. If a manager's pick gets dropped/waived, weeks they sat on waivers score as 0 unless you fill them in here. An override replaces whatever's in rosters for that player/week — it doesn't add to it.</p>
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Player</th>
                                            <th>Week</th>
                                            <th>Points</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($pool as $p): foreach (($manualPoints[$p['player']] ?? []) as $week => $points): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($p['player']); ?></td>
                                            <td><?php echo (int)$week; ?></td>
                                            <td><?php echo number_format($points, 1); ?></td>
                                            <td><button type="button" class="btn btn-sm btn-outline-danger manual-points-delete-btn" data-player="<?php echo htmlspecialchars($p['player']); ?>" data-week="<?php echo (int)$week; ?>">Delete</button></td>
                                        </tr>
                                    <?php endforeach; endforeach; ?>
                                    <?php if (empty(array_filter($manualPoints))): ?>
                                        <tr><td colspan="4" class="text-muted font-italic">No overrides yet.</td></tr>
                                    <?php endif; ?>
                                    </tbody>
                                </table>
                                <div class="d-flex" style="gap:8px; align-items:flex-end; flex-wrap:wrap;">
                                    <div class="form-group mb-0">
                                        <label class="small text-muted mb-1">Player</label>
                                        <select id="manual-points-player" class="form-control" style="max-width:260px;">
                                            <?php foreach ($pool as $p): ?>
                                                <option value="<?php echo htmlspecialchars($p['player']); ?>"><?php echo htmlspecialchars($p['player'] . ' (' . $p['position'] . ')'); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="small text-muted mb-1">Week</label>
                                        <input type="number" id="manual-points-week" class="form-control" min="1" max="<?php echo DRAFT_ORDER_GAME_WEEKS; ?>" style="max-width:90px;">
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="small text-muted mb-1">Points</label>
                                        <input type="number" id="manual-points-value" class="form-control" step="0.1" style="max-width:100px;">
                                    </div>
                                    <button type="button" id="manual-points-save-btn" class="btn btn-success">Save Override</button>
                                </div>

                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<?php include __DIR__ . '/../footer.php'; ?>

<script>
var saveAllBtn = document.getElementById('save-all-picks-btn');
if (saveAllBtn) {
    saveAllBtn.addEventListener('click', function () {
        var changes = [];
        document.querySelectorAll('.assign-select').forEach(function (select) {
            var managerId = parseInt(select.dataset.managerId, 10);
            var player = select.value;
            if (player === select.dataset.originalPlayer) return; // unchanged, skip
            if (!player) return; // switching to "no pick" isn't supported, skip
            var weekInput = document.querySelector('.assign-week[data-manager-id="' + managerId + '"]');
            var effectiveWeek = parseInt(weekInput.value, 10);
            if (!effectiveWeek) {
                alert('Enter a valid effective week for ' + select.closest('tr').children[0].textContent + '.');
                return;
            }
            changes.push({ managerId: managerId, player: player, effectiveWeek: effectiveWeek });
        });

        if (!changes.length) {
            alert('No changes to save.');
            return;
        }

        saveAllBtn.disabled = true;
        saveAllBtn.textContent = 'Saving...';

        Promise.all(changes.map(function (change) {
            return fetch('/data/draftOrderGame.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'assign_pick', manager_id: change.managerId, player: change.player, effective_week: change.effectiveWeek })
            })
            .then(function (r) { return r.json(); })
            .then(function (data) { return { change: change, data: data }; });
        }))
        .then(function (results) {
            var failures = results.filter(function (r) { return !r.data.success; });
            if (failures.length) {
                var messages = failures.map(function (r) { return r.change.player + ': ' + (r.data.error || 'Something went wrong.'); });
                alert('Some changes failed:\n' + messages.join('\n'));
                saveAllBtn.disabled = false;
                saveAllBtn.textContent = 'Save All Changes';
                return;
            }
            location.reload();
        })
        .catch(function () {
            alert('Request failed.');
            saveAllBtn.disabled = false;
            saveAllBtn.textContent = 'Save All Changes';
        });
    });
}

document.querySelectorAll('.eligibility-toggle').forEach(function (checkbox) {
    checkbox.addEventListener('change', function () {
        fetch('/data/draftOrderGame.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'toggle_eligibility', player: checkbox.dataset.player, eligible: checkbox.checked })
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (!data.success) {
                alert(data.error || 'Something went wrong.');
                return;
            }
            location.reload();
        })
        .catch(function () { alert('Request failed.'); });
    });
});

var manualPointsSaveBtn = document.getElementById('manual-points-save-btn');
if (manualPointsSaveBtn) {
    manualPointsSaveBtn.addEventListener('click', function () {
        var player = document.getElementById('manual-points-player').value;
        var week = parseInt(document.getElementById('manual-points-week').value, 10);
        var pointsInput = document.getElementById('manual-points-value').value;
        if (!week) {
            alert('Enter a valid week.');
            return;
        }
        if (pointsInput === '') {
            alert('Enter a points value.');
            return;
        }
        fetch('/data/draftOrderGame.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'set_manual_points', player: player, week: week, points: parseFloat(pointsInput) })
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (!data.success) {
                alert(data.error || 'Something went wrong.');
                return;
            }
            location.reload();
        })
        .catch(function () { alert('Request failed.'); });
    });
}

document.querySelectorAll('.manual-points-delete-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
        fetch('/data/draftOrderGame.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'delete_manual_points', player: btn.dataset.player, week: parseInt(btn.dataset.week, 10) })
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (!data.success) {
                alert(data.error || 'Something went wrong.');
                return;
            }
            location.reload();
        })
        .catch(function () { alert('Request failed.'); });
    });
});
</script>
