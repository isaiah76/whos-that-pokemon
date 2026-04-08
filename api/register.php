<?php

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../models/User.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed.'], 405);
}

if (!empty($_SESSION['user'])) {
    jsonResponse(['success' => false, 'message' => 'Already authenticated.'], 400);
}

$body = getRequestBody();

$username = sanitize($body['username'] ?? '');
$email    = sanitize($body['email']    ?? '');
$password = $body['password'] ?? '';

if (empty($username) || empty($email) || empty($password)) {
    jsonResponse(['success' => false, 'message' => 'All fields are required.'], 400);
}

$userModel = new User();
$result    = $userModel->create($username, $email, $password);

$statusCode = $result['success'] ? 201 : 422;
jsonResponse($result, $statusCode);
