<?php

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../models/User.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed.'], 405);
}

if (!empty($_SESSION['user'])) {
    jsonResponse([
        'success' => true,
        'message' => 'Already logged in.',
        'user' => $_SESSION['user'],
    ]);
}

$body = getRequestBody();

$username = sanitize($body['username'] ?? '');
$password = $body['password'] ?? '';

if (empty($username) || empty($password)) {
    jsonResponse(['success' => false, 'message' => 'Username and password required.'], 400);
}

$userModel = new User();
$user      = $userModel->authenticate($username, $password);

if (!$user) {
    jsonResponse(['success' => false, 'message' => 'Invalid credentials.'], 401);
}

session_regenerate_id(true);

$_SESSION['user'] = [
    'id' => (int) $user['id'],
    'username' => $user['username'],
    'email' => $user['email'],
    'role' => $user['role'],
    'avatar' => $user['avatar'] ?? null,
];

jsonResponse([
    'success' => true,
    'message' => 'Logged in successfully.',
    'user' => $_SESSION['user'],
]);
