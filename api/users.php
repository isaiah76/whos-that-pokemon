<?php

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../models/User.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed.'], 405);
}

$userModel = new User();
$users = $userModel->getAllUsers();

jsonResponse(['success' => true, 'users' => $users]);
