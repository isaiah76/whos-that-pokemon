<?php

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../models/Score.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed.'], 405);
}

$limit = min(100, max(1, (int) ($_GET['limit'] ?? 20)));
$difficulty = $_GET['difficulty'] ?? null;

if ($difficulty && !in_array($difficulty, ['easy', 'normal', 'hard'], true)) {
    $difficulty = null;
}

$scoreModel = new Score();
$scores = $scoreModel->getLeaderboard($limit, $difficulty);

jsonResponse(['success' => true, 'scores' => $scores]);
