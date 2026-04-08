<?php

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../models/User.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed.'], 405);
}

$user = requireAuth();
$body = getRequestBody();

$action = $body['action'] ?? '';
$userModel = new User();

$VALID_AVATARS = [
    'eevee.jpg', 'bulbasaur.jpg', 'cubone.jpg', 'meowth.jpg', 'munchlax.jpg',
    'pikachu.jpg', 'piplup.jpg', 'snivy.jpg', 'togepi.jpg',
];

switch ($action) {

    case 'avatar':
        $avatar = $body['avatar'] ?? '';
        if (!in_array($avatar, $VALID_AVATARS, true)) {
            jsonResponse(['success' => false, 'message' => 'Invalid avatar.'], 400);
        }
        $ok = $userModel->updateAvatar((int) $user['id'], $avatar);
        if ($ok) {
            $_SESSION['user']['avatar'] = $avatar;
            jsonResponse(['success' => true, 'message' => 'Avatar updated.']);
        }
        jsonResponse(['success' => false, 'message' => 'Failed to update avatar.'], 500);

        // no break
    case 'username':
        $username = sanitize($body['username'] ?? '');
        if (strlen($username) < 2 || strlen($username) > 32) {
            jsonResponse(['success' => false, 'message' => 'Username must be 2–32 characters.'], 400);
        }
        if (!preg_match('/^[a-zA-Z0-9_\-]+$/', $username)) {
            jsonResponse(['success' => false, 'message' => 'Username can only contain letters, numbers, _ and -.'], 400);
        }
        $ok = $userModel->updateUsername((int) $user['id'], $username);
        if ($ok) {
            $_SESSION['user']['username'] = $username;
            jsonResponse(['success' => true, 'message' => 'Username updated.']);
        }
        jsonResponse(['success' => false, 'message' => 'Username already taken or update failed.'], 409);

        // no break
    case 'password':
        $currentPassword = $body['current_password'] ?? '';
        $newPassword     = $body['new_password']     ?? '';

        if (empty($currentPassword) || empty($newPassword)) {
            jsonResponse(['success' => false, 'message' => 'All fields required.'], 400);
        }
        if (strlen($newPassword) < 8) {
            jsonResponse(['success' => false, 'message' => 'Password must be at least 8 characters.'], 400);
        }

        $verified = $userModel->authenticate($user['username'], $currentPassword);
        if (!$verified) {
            jsonResponse(['success' => false, 'message' => 'Current password is incorrect.'], 401);
        }

        $ok = $userModel->updatePassword((int) $user['id'], $newPassword);
        if ($ok) {
            jsonResponse(['success' => true, 'message' => 'Password updated.']);
        }
        jsonResponse(['success' => false, 'message' => 'Failed to update password.'], 500);

        // no break
    case 'email':
        $email = strtolower(trim($body['email'] ?? ''));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            jsonResponse(['success' => false, 'message' => 'Invalid email address.'], 400);
        }

        $ok = $userModel->updateEmail((int) $user['id'], $email);
        if ($ok) {
            $_SESSION['user']['email'] = $email;
            jsonResponse(['success' => true, 'message' => 'Email updated.']);
        }
        jsonResponse(['success' => false, 'message' => 'Email already taken or update failed.'], 409);

        // no break
    default:
        jsonResponse(['success' => false, 'message' => 'Unknown action.'], 400);
}
