<?php
// Admin-mutation endpoint for the 2027 Draft Order ("Closest to 200") game.
// GET is not supported here — index.php renders state server-side on load.
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../2027draft/lib.php';

$conn = new SQLite3(__DIR__ . '/../database/ffb.sqlite');
$conn->enableExceptions(true);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true);
$action = $body['action'] ?? null;

if ($action === 'login') {
    if (hash_equals(DRAFT_ORDER_GAME_PASSWORD, (string)($body['password'] ?? ''))) {
        $_SESSION['draft_order_admin_auth'] = true;
        echo json_encode(['success' => true]);
    } else {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Incorrect password']);
    }
    exit;
}

if (empty($_SESSION['draft_order_admin_auth'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

$year = DRAFT_ORDER_GAME_YEAR;

if ($action === 'assign_pick') {
    $managerId = (int)($body['manager_id'] ?? 0);
    $player = trim((string)($body['player'] ?? ''));
    $effectiveWeek = (int)($body['effective_week'] ?? 0);

    $managers = getManagers($conn);
    $managerIds = array_column($managers, 'id');
    if (!in_array($managerId, $managerIds, true)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Unknown manager']);
        exit;
    }

    if ($effectiveWeek < 1 || $effectiveWeek > DRAFT_ORDER_GAME_WEEKS) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid effective week']);
        exit;
    }

    $pool = getPool($conn, $year);
    $poolPlayers = array_column($pool, 'player');
    if (!in_array($player, $poolPlayers, true)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Player not in pool']);
        exit;
    }

    $pickHistory = getPickHistory($conn, $year);
    $currentPicks = getCurrentPicks($pickHistory);
    $currentPickForManager = $currentPicks[$managerId] ?? null;

    $ineligible = getIneligiblePlayers($conn, $year);
    if (isset($ineligible[$player]) && $player !== $currentPickForManager) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Player is not eligible']);
        exit;
    }

    foreach ($currentPicks as $otherManagerId => $otherPlayer) {
        if ($otherPlayer === $player && $otherManagerId !== $managerId) {
            $otherManagerName = '';
            foreach ($managers as $m) {
                if ($m['id'] === $otherManagerId) $otherManagerName = $m['name'];
            }
            http_response_code(409);
            echo json_encode(['success' => false, 'error' => "Already picked by {$otherManagerName}"]);
            exit;
        }
    }

    $stmt = $conn->prepare("
        INSERT INTO draft_order_pick_history (year, manager_id, player, effective_week)
        VALUES (:year, :manager_id, :player, :effective_week)
    ");
    $stmt->bindValue(':year', $year, SQLITE3_INTEGER);
    $stmt->bindValue(':manager_id', $managerId, SQLITE3_INTEGER);
    $stmt->bindValue(':player', $player, SQLITE3_TEXT);
    $stmt->bindValue(':effective_week', $effectiveWeek, SQLITE3_INTEGER);
    $stmt->execute();

    echo json_encode(['success' => true, 'manager_id' => $managerId, 'player' => $player, 'effective_week' => $effectiveWeek]);
    exit;
}

if ($action === 'toggle_eligibility') {
    $player = trim((string)($body['player'] ?? ''));
    $eligible = !empty($body['eligible']);

    $pool = getPool($conn, $year);
    $poolPlayers = array_column($pool, 'player');
    if (!in_array($player, $poolPlayers, true)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Player not in pool']);
        exit;
    }

    if ($eligible) {
        $stmt = $conn->prepare("DELETE FROM draft_order_ineligible_players WHERE year = :year AND player = :player");
    } else {
        $stmt = $conn->prepare("
            INSERT INTO draft_order_ineligible_players (year, player)
            VALUES (:year, :player)
            ON CONFLICT(year, player) DO UPDATE SET updated_at = CURRENT_TIMESTAMP
        ");
    }
    $stmt->bindValue(':year', $year, SQLITE3_INTEGER);
    $stmt->bindValue(':player', $player, SQLITE3_TEXT);
    $stmt->execute();

    echo json_encode(['success' => true, 'player' => $player, 'eligible' => $eligible]);
    exit;
}

if ($action === 'set_manual_points') {
    $player = trim((string)($body['player'] ?? ''));
    $week = (int)($body['week'] ?? 0);
    $points = isset($body['points']) ? (float)$body['points'] : null;

    if ($week < 1 || $week > DRAFT_ORDER_GAME_WEEKS) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid week']);
        exit;
    }

    if ($points === null) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Points required']);
        exit;
    }

    $pool = getPool($conn, $year);
    $poolPlayers = array_column($pool, 'player');
    if (!in_array($player, $poolPlayers, true)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Player not in pool']);
        exit;
    }

    $stmt = $conn->prepare("
        INSERT INTO draft_order_manual_points (year, player, week, points)
        VALUES (:year, :player, :week, :points)
        ON CONFLICT(year, player, week) DO UPDATE SET points = excluded.points
    ");
    $stmt->bindValue(':year', $year, SQLITE3_INTEGER);
    $stmt->bindValue(':player', $player, SQLITE3_TEXT);
    $stmt->bindValue(':week', $week, SQLITE3_INTEGER);
    $stmt->bindValue(':points', $points, SQLITE3_FLOAT);
    $stmt->execute();

    echo json_encode(['success' => true, 'player' => $player, 'week' => $week, 'points' => $points]);
    exit;
}

if ($action === 'delete_manual_points') {
    $player = trim((string)($body['player'] ?? ''));
    $week = (int)($body['week'] ?? 0);

    $stmt = $conn->prepare("DELETE FROM draft_order_manual_points WHERE year = :year AND player = :player AND week = :week");
    $stmt->bindValue(':year', $year, SQLITE3_INTEGER);
    $stmt->bindValue(':player', $player, SQLITE3_TEXT);
    $stmt->bindValue(':week', $week, SQLITE3_INTEGER);
    $stmt->execute();

    echo json_encode(['success' => true, 'player' => $player, 'week' => $week]);
    exit;
}

http_response_code(400);
echo json_encode(['success' => false, 'error' => 'Unknown action']);
