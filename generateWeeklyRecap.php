<?php

include_once 'connections.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request method.']);
    exit;
}

$notes = trim($_POST['notes'] ?? '');
$week  = isset($_POST['week']) ? (int)$_POST['week'] : null;

if (empty($notes)) {
    echo json_encode(['error' => 'No notes provided.']);
    exit;
}

$notesPlain = strip_tags($notes);
$weekLabel  = $week ? "Week $week" : "this week";

$prompt = "You are a veteran sports newspaper columnist covering the Suntown Fantasy Football League, "
    . "a 10-manager league between longtime friends. Write the $weekLabel recap. "
    . "Style: sharp, witty newspaper sports-column prose — think a beat writer with a mean streak, not a group chat text. "
    . "Use clever turns of phrase and pointed jabs/roasts at managers based on how they performed. "
    . "No emojis, no exclamation-point-heavy hype, and nothing that reads like a high schooler's writing. "
    . "Base it on the following data: " . $notesPlain;

$result = callGeminiApi($prompt, $GEMINI_API_KEY);

if (strpos($result, '[') === 0) {
    echo json_encode(['error' => $result]);
} else {
    echo json_encode(['text' => $result]);
}

function callGeminiApi($prompt, $apiKey) {
    if (empty($apiKey)) {
        return '[No API key set — add $GEMINI_API_KEY to connections.php]';
    }

    if (!function_exists('curl_init')) {
        return '[curl is not available on this server]';
    }

    $url  = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . urlencode($apiKey);
    $body = json_encode([
        'contents'         => [['parts' => [['text' => $prompt]]]],
        'generationConfig' => [
            'maxOutputTokens' => 1024,
            'temperature'     => 0.9,
            'thinkingConfig'  => ['thinkingBudget' => 0],
        ],
    ]);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $body,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT        => 30,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if (!$response || $httpCode !== 200) {
        $err    = json_decode($response, true);
        $errMsg = $err['error']['message'] ?? "HTTP $httpCode — raw: $response";
        return "[Gemini API error: $errMsg]";
    }

    $data = json_decode($response, true);
    return trim($data['candidates'][0]['content']['parts'][0]['text'] ?? '[No text in response]');
}
