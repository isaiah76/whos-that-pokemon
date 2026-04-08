<?php

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../models/Score.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed.'], 405);
}

$sessionUser = requireAuth();
$body = getRequestBody();

$score = (int) ($body['score'] ?? 0);
$correct = (int) ($body['correct'] ?? 0);
$total = (int) ($body['total'] ?? 0);
$difficulty = sanitize($body['difficulty'] ?? 'normal');
$bestStreak = (int) ($body['best_streak'] ?? 0);
$gens = sanitize($body['gens'] ?? '');

$scoreModel = new Score();
$saved = $scoreModel->save(
    $sessionUser['id'],
    $score,
    $correct,
    $total,
    $difficulty,
    $bestStreak,
    $gens,
);

if ($saved) {
    $personalBest = $scoreModel->getPersonalBest($sessionUser['id']);
    jsonResponse(['success' => true, 'message' => 'Score saved.', 'personal_best' => $personalBest]);
} else {
    jsonResponse(['success' => false, 'message' => 'Failed to save score.'], 500);
}
