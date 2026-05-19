<?php
header('Content-Type: application/json');
$stateFile = __DIR__ . '/draftOrderBracket.json';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!file_exists($stateFile)) {
        echo json_encode(['results' => (object)[]]);
        exit;
    }
    echo file_get_contents($stateFile);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true);
    if (!empty($body['reset'])) {
        file_put_contents($stateFile, json_encode(['results' => (object)[]], JSON_PRETTY_PRINT));
        echo json_encode(['results' => (object)[]]);
        exit;
    }
    if (!isset($body['matchId']) || !isset($body['winner'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing matchId or winner']);
        exit;
    }

    $state = file_exists($stateFile) ? json_decode(file_get_contents($stateFile), true) : ['results' => []];
    if (!isset($state['results'])) $state['results'] = [];

    $matchId = (string)$body['matchId'];
    $state['results'][$matchId] = $body['winner'];

    file_put_contents($stateFile, json_encode($state, JSON_PRETTY_PRINT));
    echo json_encode($state);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
