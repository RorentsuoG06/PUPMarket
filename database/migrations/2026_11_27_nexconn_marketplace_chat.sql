ALTER TABLE users
    ADD COLUMN nexconn_user_id CHAR(32) NULL,
    ADD UNIQUE KEY uq_users_nexconn_user_id (nexconn_user_id);

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
