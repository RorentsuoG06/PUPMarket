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
    is_admin TINYINT(1) NOT NULL DEFAULT 0,
    nexconn_user_id CHAR(32) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_users_nexconn_user_id (nexconn_user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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

CREATE TABLE IF NOT EXISTS marketplace_listings (
    listing_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    seller_user_id INT NOT NULL,
    name VARCHAR(120) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    category VARCHAR(50) NOT NULL,
    item_condition VARCHAR(20) NOT NULL,
    description TEXT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_marketplace_listings_created (created_at),
    INDEX idx_marketplace_listings_seller (seller_user_id),
    CONSTRAINT fk_marketplace_listings_seller
        FOREIGN KEY (seller_user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS marketplace_reports (
    report_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reporter_user_id INT NOT NULL,
    listing_id INT UNSIGNED NULL,
    reported_user_id INT NULL,
    target_type ENUM('listing', 'seller') NOT NULL,
    listing_name_snapshot VARCHAR(120) NOT NULL,
    reported_username_snapshot VARCHAR(50) NOT NULL,
    reason VARCHAR(50) NOT NULL,
    details TEXT NULL,
    status ENUM('pending', 'reviewed', 'dismissed', 'actioned') NOT NULL DEFAULT 'pending',
    moderator_user_id INT NULL,
    moderator_note TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    reviewed_at DATETIME NULL,
    UNIQUE KEY uq_marketplace_reporter_listing_target (reporter_user_id, listing_id, target_type),
    INDEX idx_marketplace_reports_status_created (status, created_at),
    INDEX idx_marketplace_reports_listing (listing_id),
    INDEX idx_marketplace_reports_user (reported_user_id),
    CONSTRAINT fk_marketplace_reports_reporter
        FOREIGN KEY (reporter_user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    CONSTRAINT fk_marketplace_reports_listing
        FOREIGN KEY (listing_id) REFERENCES marketplace_listings(listing_id) ON DELETE SET NULL,
    CONSTRAINT fk_marketplace_reports_reported_user
        FOREIGN KEY (reported_user_id) REFERENCES users(user_id) ON DELETE SET NULL,
    CONSTRAINT fk_marketplace_reports_moderator
        FOREIGN KEY (moderator_user_id) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO marketplace_listings (
    seller_user_id,
    name,
    price,
    category,
    item_condition,
    description,
    image_path
)
SELECT
    demo_seller.user_id,
    sample.name,
    sample.price,
    sample.category,
    sample.item_condition,
    sample.description,
    sample.image_path
FROM users AS demo_seller
CROSS JOIN (
    SELECT
        'Data Structures in Java Book' AS name,
        550.00 AS price,
        'Books' AS category,
        'Good' AS item_condition,
        'Programming book with minimal highlights.' AS description,
        'images/datastructuresinjava.jpg' AS image_path
    UNION ALL SELECT
        'Modern Database Management Book',
        780.00,
        'Books',
        'Good',
        'Database management textbook useful for studying database concepts and systems.',
        'images/mdm.jpg'
    UNION ALL SELECT
        'OOP Reviewer',
        50.00,
        'Books',
        'Like New',
        'Printed reviewer for 1st semester midterm exam.',
        'images/oopreviewer.jpg'
    UNION ALL SELECT
        'PUPBC Uniform Set - Women',
        800.00,
        'Uniforms',
        'Like New',
        'Large Size. Uniform set with pants. Contact me for the other details.',
        'images/unifset.jpg'
    UNION ALL SELECT
        'PE Uniform Top only',
        320.00,
        'Uniforms',
        'Good',
        'Size medium. All goods pa.',
        'images/peunif.jpg'
    UNION ALL SELECT
        'CASIO Scientific Calculator',
        700.00,
        'Electronics',
        'Good',
        'Scientific calculator in good working condition, suitable for school and mathematical use.',
        'images/calc.jpg'
    UNION ALL SELECT
        'Logitech Wireless Mouse',
        680.00,
        'Electronics',
        'Good',
        'Wireless mouse in good working condition. Suitable for laptops, computers, and everyday school use.',
        'images/mouse.jpg'
    UNION ALL SELECT
        '1 Ream A4 Hard Copy Bond paper',
        280.00,
        'School Supplies',
        'New',
        '1 ream of A4 bond paper containing 500 sheets. Suitable for printing school requirements and documents.',
        'images/bndpaper.jpg'
    UNION ALL SELECT
        'Mica Paper Set',
        280.00,
        'School Supplies',
        'New',
        'Brand new mica paper set suitable for school projects, covers, and other printing needs.',
        'images/mica.jpg'
    UNION ALL SELECT
        'Homemade Cookies',
        10.00,
        'Others',
        'New',
        'Freshly made homemade cookies available in Matcha, Choco, and Red Velvet flavors. PHP 10 per piece.',
        'images/cookie.jpg'
) AS sample
WHERE demo_seller.username = 'pupmarket_test_seller'
  AND NOT EXISTS (
      SELECT 1
      FROM marketplace_listings AS existing
      WHERE existing.seller_user_id = demo_seller.user_id
        AND existing.name = sample.name
        AND existing.image_path = sample.image_path
  );
