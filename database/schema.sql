CREATE DATABASE IF NOT EXISTS whos_that_pokemon CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE whos_that_pokemon;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(30) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    status ENUM('active', 'disabled') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_status (status)
);

CREATE TABLE IF NOT EXISTS scores (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    score INT NOT NULL DEFAULT 0,
    correct_guesses INT NOT NULL DEFAULT 0,
    total_guesses INT NOT NULL DEFAULT 0,
    difficulty ENUM('easy', 'normal', 'hard') NOT NULL DEFAULT 'normal',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_score (score),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- admin acc
INSERT INTO users (username, email, password_hash, role, status)
VALUES (
    'admin',
    'admin@test',
    '',
    'admin',
    'active'
) ;
