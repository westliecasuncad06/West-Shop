-- Migration: add `store_id` relationships for social tables (reviews, follows)
-- Safe to run multiple times.

DELIMITER //
CREATE PROCEDURE migrate_store_relationships()
BEGIN
  -- 0) Ensure `stores` table exists (compatible with prior migrations)
  CREATE TABLE IF NOT EXISTS stores (
    store_id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT NOT NULL,
    store_name VARCHAR(120) NOT NULL,
    logo VARCHAR(255),
    banner VARCHAR(255),
    description TEXT,
    shipping_policy TEXT,
    return_policy TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_store_seller (seller_id),
    CONSTRAINT fk_stores_seller FOREIGN KEY (seller_id) REFERENCES users(user_id) ON DELETE CASCADE
  ) ENGINE=InnoDB;

  -- Ensure one store row per seller (create missing)
  INSERT INTO stores (seller_id, store_name)
  SELECT u.user_id, CONCAT(u.name, ' Store')
  FROM users u
  WHERE u.role = 'seller'
    AND NOT EXISTS (
      SELECT 1 FROM stores s WHERE s.seller_id = u.user_id
    );

  -- 1) store_reviews: add store_id, backfill, add FK
  IF NOT EXISTS (
    SELECT 1 FROM information_schema.columns
    WHERE table_schema = DATABASE() AND table_name = 'store_reviews' AND column_name = 'store_id'
  ) THEN
    ALTER TABLE store_reviews ADD COLUMN store_id INT NULL AFTER review_id;
  END IF;

  -- Backfill store_id via seller mapping
  UPDATE store_reviews sr
  JOIN stores s ON s.seller_id = sr.seller_id
  SET sr.store_id = s.store_id
  WHERE sr.store_id IS NULL;

  -- Index + FK for store_id
  IF NOT EXISTS (
    SELECT 1 FROM information_schema.statistics
    WHERE table_schema = DATABASE() AND table_name = 'store_reviews' AND index_name = 'idx_sr_store'
  ) THEN
    CREATE INDEX idx_sr_store ON store_reviews(store_id);
  END IF;

  IF NOT EXISTS (
    SELECT 1 FROM information_schema.table_constraints
    WHERE table_schema = DATABASE() AND table_name = 'store_reviews' AND constraint_name = 'fk_store_reviews_store'
  ) THEN
    ALTER TABLE store_reviews
      ADD CONSTRAINT fk_store_reviews_store FOREIGN KEY (store_id) REFERENCES stores(store_id) ON DELETE CASCADE;
  END IF;

  -- 2) store_follows: add store_id, backfill, add FK
  IF NOT EXISTS (
    SELECT 1 FROM information_schema.columns
    WHERE table_schema = DATABASE() AND table_name = 'store_follows' AND column_name = 'store_id'
  ) THEN
    ALTER TABLE store_follows ADD COLUMN store_id INT NULL AFTER follow_id;
  END IF;

  UPDATE store_follows sf
  JOIN stores s ON s.seller_id = sf.seller_id
  SET sf.store_id = s.store_id
  WHERE sf.store_id IS NULL;

  IF NOT EXISTS (
    SELECT 1 FROM information_schema.statistics
    WHERE table_schema = DATABASE() AND table_name = 'store_follows' AND index_name = 'idx_sf_store'
  ) THEN
    CREATE INDEX idx_sf_store ON store_follows(store_id);
  END IF;

  IF NOT EXISTS (
    SELECT 1 FROM information_schema.table_constraints
    WHERE table_schema = DATABASE() AND table_name = 'store_follows' AND constraint_name = 'fk_store_follows_store'
  ) THEN
    ALTER TABLE store_follows
      ADD CONSTRAINT fk_store_follows_store FOREIGN KEY (store_id) REFERENCES stores(store_id) ON DELETE CASCADE;
  END IF;

  -- Optionally, make store_id NOT NULL once populated
  -- (Guarded by checking if any rows still null)
  IF NOT EXISTS (
    SELECT 1 FROM store_reviews WHERE store_id IS NULL LIMIT 1
  ) THEN
    -- MySQL requires separate ALTERs; ignore if already NOT NULL
    SET @stmt := (SELECT IF(
      (SELECT IS_NULLABLE FROM information_schema.columns
        WHERE table_schema = DATABASE() AND table_name = 'store_reviews' AND column_name = 'store_id') = 'YES',
      'ALTER TABLE store_reviews MODIFY store_id INT NOT NULL',
      NULL));
    IF @stmt IS NOT NULL THEN PREPARE x FROM @stmt; EXECUTE x; DEALLOCATE PREPARE x; END IF;
  END IF;

  IF NOT EXISTS (
    SELECT 1 FROM store_follows WHERE store_id IS NULL LIMIT 1
  ) THEN
    SET @stmt2 := (SELECT IF(
      (SELECT IS_NULLABLE FROM information_schema.columns
        WHERE table_schema = DATABASE() AND table_name = 'store_follows' AND column_name = 'store_id') = 'YES',
      'ALTER TABLE store_follows MODIFY store_id INT NOT NULL',
      NULL));
    IF @stmt2 IS NOT NULL THEN PREPARE y FROM @stmt2; EXECUTE y; DEALLOCATE PREPARE y; END IF;
  END IF;
END //
DELIMITER ;

CALL migrate_store_relationships();
DROP PROCEDURE IF EXISTS migrate_store_relationships;
