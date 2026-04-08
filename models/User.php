<?php

require_once __DIR__ . '/../config/database.php';

class User
{
    private PDO $db;

    private const AVATARS = [
        'eevee.jpg', 'bulbasaur.jpg', 'cubone.jpg', 'meowth.jpg', 'munchlax.jpg',
        'pikachu.jpg', 'piplup.jpg', 'snivy.jpg', 'togepi.jpg',
    ];

    public function __construct()
    {
        $this->db = getDB();
    }

    public function create(string $username, string $email, string $password): array
    {
        if (!$this->isValidUsername($username)) {
            return ['success' => false, 'message' => 'Username must be 3–30 alphanumeric characters.'];
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid email address.'];
        }
        if (strlen($password) < 8) {
            return ['success' => false, 'message' => 'Password must be at least 8 characters.'];
        }

        if ($this->findByUsername($username)) {
            return ['success' => false, 'message' => 'Username already taken.'];
        }
        if ($this->findByEmail($email)) {
            return ['success' => false, 'message' => 'Email already registered.'];
        }

        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

        $randomAvatar = self::AVATARS[array_rand(self::AVATARS)];

        $stmt = $this->db->prepare(
            'INSERT INTO users (username, email, password_hash, avatar) VALUES (:username, :email, :hash, :avatar)',
        );
        $stmt->execute([
            ':username' => $username,
            ':email'    => strtolower(trim($email)),
            ':hash'     => $hash,
            ':avatar'   => $randomAvatar,
        ]);

        return ['success' => true, 'message' => 'Account created.', 'id' => (int) $this->db->lastInsertId()];
    }

    public function authenticate(string $identifier, string $password): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM users WHERE username = :username OR email = :email LIMIT 1',
        );

        $stmt->execute([
            ':username' => $identifier,
            ':email'    => $identifier,
        ]);

        $user = $stmt->fetch();

        if (!$user) {
            return null;
        }
        if ($user['status'] === 'disabled') {
            return null;
        }
        if (!password_verify($password, $user['password_hash'])) {
            return null;
        }

        return $user;
    }

    public function findByUsername(string $username): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM users WHERE username = :username LIMIT 1',
        );
        $stmt->execute([':username' => $username]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM users WHERE email = :email LIMIT 1',
        );
        $stmt->execute([':email' => strtolower(trim($email))]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, username, email, role, status, avatar, created_at FROM users WHERE id = :id LIMIT 1',
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getAllUsers(): array
    {
        $stmt = $this->db->query(
            'SELECT id, username, email, role, status, avatar, created_at FROM users ORDER BY created_at DESC',
        );
        return $stmt->fetchAll();
    }

    public function setStatus(int $userId, string $status): bool
    {
        if (!in_array($status, ['active', 'disabled'], true)) {
            return false;
        }

        $stmt = $this->db->prepare(
            'UPDATE users SET status = :status WHERE id = :id AND role != "admin"',
        );
        $stmt->execute([':status' => $status, ':id' => $userId]);
        return $stmt->rowCount() > 0;
    }

    public function updateAvatar(int $userId, string $avatar): bool
    {
        if (!in_array($avatar, self::AVATARS, true)) {
            return false;
        }

        $stmt = $this->db->prepare(
            'UPDATE users SET avatar = :avatar WHERE id = :id',
        );

        return $stmt->execute([':avatar' => $avatar, ':id' => $userId]);
    }

    public function updateUsername(int $userId, string $username): bool
    {
        try {
            $stmt = $this->db->prepare(
                'UPDATE users SET username = :username WHERE id = :id',
            );
            return $stmt->execute([':username' => $username, ':id' => $userId]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function updatePassword(int $userId, string $newPassword): bool
    {
        $hash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = $this->db->prepare(
            'UPDATE users SET password_hash = :hash WHERE id = :id',
        );
        return $stmt->execute([':hash' => $hash, ':id' => $userId]);
    }

    public function updateEmail(int $userId, string $email): bool
    {
        $email = strtolower(trim($email));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        try {
            $stmt = $this->db->prepare(
                'UPDATE users SET email = :email WHERE id = :id',
            );
            return $stmt->execute([':email' => $email, ':id' => $userId]);
        } catch (PDOException $e) {
            return false;
        }
    }

    private function isValidUsername(string $username): bool
    {
        return preg_match('/^[a-zA-Z0-9_]{3,30}$/', $username) === 1;
    }
}
