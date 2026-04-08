<?php

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../models/User.php';

$adminUser = requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed.'], 405);
}

$body   = getRequestBody();
$userId = (int) ($body['user_id'] ?? 0);
$status = sanitize($body['status'] ?? '');

if ($userId <= 0 || !in_array($status, ['active', 'disabled'], true)) {
    jsonResponse(['success' => false, 'message' => 'Invalid parameters.'], 400);
}

if ($userId === $adminUser['id']) {
    jsonResponse(['success' => false, 'message' => 'Cannot modify your own account status.'], 403);
}

$userModel = new User();
$target    = $userModel->findById($userId);

if (!$target) {
    jsonResponse(['success' => false, 'message' => 'User not found.'], 404);
}

if ($target['role'] === 'admin') {
    jsonResponse(['success' => false, 'message' => 'Cannot modify admin accounts.'], 403);
}

$updated = $userModel->setStatus($userId, $status);

if ($updated) {
    jsonResponse(['success' => true, 'message' => "User {$target['username']} set to {$status}."]);
} else {
    jsonResponse(['success' => false, 'message' => 'No changes made.'], 400);
}
