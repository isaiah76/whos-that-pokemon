<?php

require_once __DIR__ . '/../config/database.php';

class Score
{
    private PDO $db;

    public function __construct()
    {
        $this->db = getDB();
    }

    public function save(int $userId, int $score, int $correctGuesses, int $totalGuesses, string $difficulty = 'normal', int $bestStreak = 0, string $gens = ''): bool
    {
        if (!in_array($difficulty, ['easy', 'normal', 'hard'], true)) {
            $difficulty = 'normal';
        }
        $gens = preg_replace('/[^0-9,]/', '', $gens);

        $stmt = $this->db->prepare(
            'INSERT INTO scores (user_id, score, correct_guesses, total_guesses, difficulty, best_streak, gens)
             VALUES (:user_id, :score, :correct, :total, :difficulty, :best_streak, :gens)',
        );
        return $stmt->execute([
            ':user_id'     => $userId,
            ':score'       => $score,
            ':correct'     => max(0, $correctGuesses),
            ':total'       => max(0, $totalGuesses),
            ':difficulty'  => $difficulty,
            ':best_streak' => max(0, $bestStreak),
            ':gens'        => $gens,
        ]);
    }

    public function getLeaderboard(int $limit = 20, ?string $difficulty = null): array
    {
        $diffFilter = ($difficulty && in_array($difficulty, ['easy','normal','hard'], true))
            ? "AND s.difficulty = :difficulty" : '';

        $diffSubquery = $diffFilter
            ? "AND s3.difficulty = :difficulty2"
            : '';

        $sql = "SELECT
                    u.id                                                        AS user_id,
                    u.username,
                    u.avatar,
                    MAX(s.score)                                                AS highest_score,
                    COUNT(s.id)                                                 AS games_played,
                    SUM(s.correct_guesses)                                      AS total_correct,
                    SUM(s.total_guesses)                                        AS total_attempts,
                    ROUND(
                        CASE WHEN SUM(s.total_guesses) > 0
                             THEN (SUM(s.correct_guesses) / SUM(s.total_guesses)) * 100
                             ELSE 0
                        END, 1
                    )                                                           AS accuracy,
                    MAX(s.best_streak)                                          AS max_streak,
                    (SELECT s3.gens FROM scores s3
                    WHERE s3.user_id = u.id AND s3.gens != '' $diffSubquery
                    ORDER BY s3.score DESC LIMIT 1) AS top_gens,
                    (SELECT s2.difficulty FROM scores s2
                     WHERE s2.user_id = u.id
                     GROUP BY s2.difficulty ORDER BY COUNT(*) DESC LIMIT 1)    AS fav_difficulty
                FROM scores s
                JOIN users u ON u.id = s.user_id
                WHERE u.status = 'active' $diffFilter
                GROUP BY s.user_id, u.username, u.avatar
                ORDER BY highest_score DESC
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        if ($diffFilter) {
            $stmt->bindValue(':difficulty', $difficulty);
            $stmt->bindValue(':difficulty2', $difficulty);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getUserStats(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT MAX(score) AS best_score, COUNT(id) AS games_played,
                    SUM(correct_guesses) AS total_correct, SUM(total_guesses) AS total_attempts,
                    ROUND(CASE WHEN SUM(total_guesses) > 0
                          THEN (SUM(correct_guesses) / SUM(total_guesses)) * 100
                          ELSE 0 END, 1) AS accuracy,
                    MAX(best_streak) AS max_streak
             FROM scores WHERE user_id = :user_id',
        );
        $stmt->execute([':user_id' => $userId]);
        $base = $stmt->fetch() ?: [];

        $stmt2 = $this->db->prepare(
            'SELECT difficulty, COUNT(*) AS games, MAX(score) AS best,
                    ROUND(AVG(score), 0) AS avg_score,
                    ROUND(CASE WHEN SUM(total_guesses) > 0
                          THEN (SUM(correct_guesses) / SUM(total_guesses)) * 100
                          ELSE 0 END, 1) AS accuracy
             FROM scores WHERE user_id = :user_id GROUP BY difficulty',
        );
        $stmt2->execute([':user_id' => $userId]);
        $byDiff = [];
        $favDiff = null;
        $favCount = 0;
        foreach ($stmt2->fetchAll() as $r) {
            $byDiff[$r['difficulty']] = $r;
            if ((int) $r['games'] > $favCount) {
                $favCount = (int) $r['games'];
                $favDiff = $r['difficulty'];
            }
        }
        return array_merge($base, ['fav_difficulty' => $favDiff, 'by_difficulty' => $byDiff]);
    }

    public function getPersonalBest(int $userId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT score, correct_guesses, total_guesses, difficulty, created_at
             FROM scores WHERE user_id = :user_id ORDER BY score DESC LIMIT 1',
        );
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetch() ?: null;
    }

    public function getUserHistory(int $userId, int $limit = 10): array
    {
        $stmt = $this->db->prepare(
            'SELECT score, correct_guesses, total_guesses, difficulty, created_at
             FROM scores WHERE user_id = :user_id ORDER BY created_at DESC LIMIT :limit',
        );
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
