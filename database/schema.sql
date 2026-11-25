CREATE DATABASE IF NOT EXISTS db_pupmarket;

USE db_pupmarket;

CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    course_year VARCHAR(100) NULL,
    contact_info VARCHAR(100) NULL,
    bio VARCHAR(255) NULL,
    avatar_path VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Development-only accounts; remove these before deploying to production.
INSERT IGNORE INTO users (full_name, email, username, password) VALUES
    (
        'PUPMarket Test Student',
        'teststudent@iskolarngbayan.pup.edu.ph',
        'pupmarket_test_student',
        '$2y$10$mEqtG0bwX.FafuoV4YeVK.Le3Pvy932bPnOzmAhYqtMgxhAYOREUq'
    ),
    (
        'PUPMarket Test Seller',
        'testseller@iskolarngbayan.pup.edu.ph',
        'pupmarket_test_seller',
        '$2y$10$kQeIHJmA73qS00.nIxAVa.hFNcc4uMQJKqdTnZ1SyU4uhwZFHlkqy'
    );

CREATE TABLE IF NOT EXISTS email_otps (
    otp_id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL,
    otp_hash VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    attempts INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email_created (email, created_at)
);

CREATE TABLE IF NOT EXISTS password_resets (
    reset_id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL,
    otp_hash VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    attempts INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_reset_email_created (email, created_at)
);

CREATE TABLE IF NOT EXISTS remember_tokens (
    selector CHAR(32) NOT NULL PRIMARY KEY,
    token_hash CHAR(64) NOT NULL,
    user_id INT NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_remember_tokens_user (user_id),
    INDEX idx_remember_tokens_expiry (expires_at),
    CONSTRAINT fk_remember_tokens_user
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS inquiries (
    inquiry_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
