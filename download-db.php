<?php
include_once 'connections.php';

$token = $_GET['token'] ?? '';
if (empty($DB_SYNC_TOKEN) || $token !== $DB_SYNC_TOKEN) {
    http_response_code(403);
    exit('Forbidden');
}

$file = __DIR__ . '/database/ffb.sqlite';
if (!file_exists($file)) {
    http_response_code(404);
    exit('Database not found');
}

header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="ffb.sqlite"');
header('Content-Length: ' . filesize($file));
readfile($file);
