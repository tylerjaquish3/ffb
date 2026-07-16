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
        $state = ['results' => (object)[]];
        if (!empty($body['seeds']) && is_array($body['seeds'])) {
            // New seeds provided (randomizer ran) — save them
            $state['seeds'] = $body['seeds'];
        } elseif (empty($body['clearSeeds']) && file_exists($stateFile)) {
            // Plain bracket reset — keep existing seeds
            $existing = json_decode(file_get_contents($stateFile), true);
            if (!empty($existing['seeds'])) {
                $state['seeds'] = $existing['seeds'];
            }
        }
        file_put_contents($stateFile, json_encode($state, JSON_PRETTY_PRINT));
        echo json_encode($state);
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
    if (!empty($body['question'])) {
        if (!isset($state['questions'])) $state['questions'] = [];
        $state['questions'][$matchId] = $body['question'];
    }

    file_put_contents($stateFile, json_encode($state, JSON_PRETTY_PRINT));
    echo json_encode($state);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
