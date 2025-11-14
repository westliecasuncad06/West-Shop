-- Create store social tables: follows and reviews

-- Store follows: buyers follow sellers (stores)
CREATE TABLE IF NOT EXISTS store_follows (
  follow_id INT AUTO_INCREMENT PRIMARY KEY,
  buyer_id INT NOT NULL,
  seller_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_follow (buyer_id, seller_id),
  INDEX (seller_id),
  CONSTRAINT fk_follow_buyer FOREIGN KEY (buyer_id) REFERENCES users(user_id) ON DELETE CASCADE,
  CONSTRAINT fk_follow_seller FOREIGN KEY (seller_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Store reviews/ratings: one per buyer per seller
CREATE TABLE IF NOT EXISTS store_reviews (
  review_id INT AUTO_INCREMENT PRIMARY KEY,
  seller_id INT NOT NULL,
  buyer_id INT NOT NULL,
  rating INT NOT NULL,
  comment TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT chk_store_rating CHECK (rating BETWEEN 1 AND 5),
  UNIQUE KEY uniq_store_review (seller_id, buyer_id),
  INDEX (seller_id),
  INDEX (buyer_id),
  CONSTRAINT fk_store_reviews_seller FOREIGN KEY (seller_id) REFERENCES users(user_id) ON DELETE CASCADE,
  CONSTRAINT fk_store_reviews_buyer FOREIGN KEY (buyer_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;