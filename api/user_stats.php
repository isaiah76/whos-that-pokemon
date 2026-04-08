<?php

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../models/Score.php';
require_once __DIR__ . '/../models/User.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['success' => false,'message' => 'Method not allowed.'], 405);
}

$userId = (int) ($_GET['user_id'] ?? 0);
if ($userId <= 0) {
    jsonResponse(['success' => false,'message' => 'Missing user_id.'], 400);
}

$userModel = new User();
$scoreModel = new Score();
$user = $userModel->findById($userId);
if (!$user || $user['status'] !== 'active') {
    jsonResponse(['success' => false,'message' => 'User not found.'], 404);
}

$stats = $scoreModel->getUserStats($userId);
$history = $scoreModel->getUserHistory($userId, 5);

jsonResponse(['success' => true,
    'user' => ['id' => (int) $user['id'],'username' => $user['username'],'avatar' => $user['avatar'],'role' => $user['role'],'created_at' => $user['created_at']],
    'stats' => $stats,'history' => $history]);
