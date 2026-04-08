<?php

require_once __DIR__ . '/../config/bootstrap.php';

if (!empty($_SESSION['user'])) {
    jsonResponse(['success' => true, 'user' => $_SESSION['user']]);
} else {
    jsonResponse(['success' => false, 'user' => null]);
}
