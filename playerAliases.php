<?php

include_once 'connections.php';
include_once 'functions.php';
include_once 'data/playerAliases.php';

$message = '';
$searchedPlayer = trim($_GET['player'] ?? '');
$record = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $searchedPlayer = trim($_POST['player'] ?? '');
    if ($searchedPlayer !== '') {
        savePlayerAliases(
            $searchedPlayer,
            $_POST['alias_1'] ?? '',
            $_POST['alias_2'] ?? '',
            $_POST['alias_3'] ?? ''
        );
        $message = "Aliases saved for $searchedPlayer.";
    }
}

if ($searchedPlayer !== '') {
    $record = getPlayerAliasRecord($searchedPlayer);
}

$allPlayerNames = getAllPlayerNames();

?>

<div class="app-content content">
    <div class="content-wrapper">
        <div class="content-body">

            <!-- Player search -->
            <div class="row" style="direction: ltr;">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Find Player</h4>
                        </div>
                        <div class="card-body" style="background: #fff; padding: 20px 24px;">
                            <form method="GET" action="admin.php" style="direction: ltr;">
                                <input type="hidden" name="tab" value="player-aliases">
                                <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 12px;">
                                    <input
                                        type="text"
                                        name="player"
                                        list="player-names-list"
                                        class="form-control"
                                        style="width: 320px; height: 38px;"
                                        placeholder="Search by player name or alias"
                                        value="<?php echo htmlspecialchars($searchedPlayer); ?>"
                                        autocomplete="off"
                                    >
                                    <datalist id="player-names-list">
                                        <?php foreach ($allPlayerNames as $name): ?>
                                            <option value="<?php echo htmlspecialchars($name); ?>">
                                        <?php endforeach; ?>
                                    </datalist>
                                    <button type="submit" class="btn btn-primary" style="height: 38px; padding: 0 16px;">Find</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($message): ?>
            <div class="row" style="direction: ltr;">
                <div class="col-sm-12">
                    <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($searchedPlayer !== ''): ?>
            <!-- Alias editor -->
            <div class="row" style="direction: ltr;">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <h4><?php echo $record ? 'Edit Aliases' : 'Add Aliases'; ?> — <?php echo htmlspecialchars($record['player'] ?? $searchedPlayer); ?></h4>
                        </div>
                        <div class="card-body" style="background: #fff; padding: 20px 24px;">
                            <form method="POST" action="admin.php?tab=player-aliases" style="direction: ltr;">
                                <input type="hidden" name="player" value="<?php echo htmlspecialchars($record['player'] ?? $searchedPlayer); ?>">

                                <div class="form-group" style="margin-bottom: 14px;">
                                    <label style="font-weight: 600;">Player</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($record['player'] ?? $searchedPlayer); ?>" disabled>
                                </div>

                                <div class="form-group" style="margin-bottom: 14px;">
                                    <label for="alias_1" style="font-weight: 600;">Alias 1</label>
                                    <input type="text" id="alias_1" name="alias_1" class="form-control" value="<?php echo htmlspecialchars($record['alias_1'] ?? ''); ?>">
                                </div>

                                <div class="form-group" style="margin-bottom: 14px;">
                                    <label for="alias_2" style="font-weight: 600;">Alias 2</label>
                                    <input type="text" id="alias_2" name="alias_2" class="form-control" value="<?php echo htmlspecialchars($record['alias_2'] ?? ''); ?>">
                                </div>

                                <div class="form-group" style="margin-bottom: 14px;">
                                    <label for="alias_3" style="font-weight: 600;">Alias 3</label>
                                    <input type="text" id="alias_3" name="alias_3" class="form-control" value="<?php echo htmlspecialchars($record['alias_3'] ?? ''); ?>">
                                </div>

                                <button type="submit" name="save" value="1" class="btn btn-primary">
                                    <i class="icon-checkmark"></i> Save Aliases
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>
