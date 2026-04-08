<?php

require_once __DIR__ . '/../config/bootstrap.php';

$_SESSION = [];
session_destroy();

jsonResponse(['success' => true, 'message' => 'Logged out.']);
