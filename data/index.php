<?php

header('Content-Type: application/json');
require_once '../functions.php';

$dashboardNumbers = getDashboardNumbers();
$postseasonChart = getPostseasonChartNumbers();

echo json_encode([
    'dashboardNumbers' => $dashboardNumbers,
    'postseasonChart' => $postseasonChart
]);
