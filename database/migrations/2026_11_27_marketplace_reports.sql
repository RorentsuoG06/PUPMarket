ALTER TABLE users
    ADD COLUMN is_admin TINYINT(1) NOT NULL DEFAULT 0;

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
