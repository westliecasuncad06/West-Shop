-- Migration: Rename/upgrade `shops` -> `stores` and update references
-- Safe to run multiple times.

DELIMITER //
CREATE PROCEDURE migrate_shop_to_store()
BEGIN
  -- 1) Ensure `stores` table exists (new schema name)
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

  -- 2) If legacy `shops` exists, copy data into `stores` (map `name` -> `store_name`)
  IF EXISTS (
    SELECT 1 FROM information_schema.tables 
    WHERE table_schema = DATABASE() AND table_name = 'shops'
  ) THEN
    INSERT INTO stores (store_id, seller_id, store_name, logo, banner, description, shipping_policy, return_policy, created_at)
    SELECT shop_id, seller_id, name, logo, banner, description, shipping_policy, return_policy, created_at
    FROM shops
    ON DUPLICATE KEY UPDATE
      store_name = VALUES(store_name),
      logo = VALUES(logo),
      banner = VALUES(banner),
      description = VALUES(description),
      shipping_policy = VALUES(shipping_policy),
      return_policy = VALUES(return_policy);
  END IF;

  -- 3) VOUCHERS: add `store_id`, copy from `shop_id`, update FK, drop old column
  IF NOT EXISTS (
    SELECT 1 FROM information_schema.columns
    WHERE table_schema = DATABASE() AND table_name = 'vouchers' AND column_name = 'store_id'
  ) THEN
    ALTER TABLE vouchers ADD COLUMN store_id INT NULL;
  END IF;

  IF EXISTS (
    SELECT 1 FROM information_schema.columns
    WHERE table_schema = DATABASE() AND table_name = 'vouchers' AND column_name = 'shop_id'
  ) THEN
    UPDATE vouchers SET store_id = COALESCE(store_id, shop_id);
  END IF;

  IF EXISTS (
    SELECT 1 FROM information_schema.table_constraints
    WHERE table_schema = DATABASE() AND table_name = 'vouchers' AND constraint_name = 'fk_vouchers_shop'
  ) THEN
    ALTER TABLE vouchers DROP FOREIGN KEY fk_vouchers_shop;
  END IF;

  IF NOT EXISTS (
    SELECT 1 FROM information_schema.table_constraints
    WHERE table_schema = DATABASE() AND table_name = 'vouchers' AND constraint_name = 'fk_vouchers_store'
  ) THEN
    ALTER TABLE vouchers
      ADD CONSTRAINT fk_vouchers_store FOREIGN KEY (store_id) REFERENCES stores(store_id) ON DELETE SET NULL;
  END IF;

  IF EXISTS (
    SELECT 1 FROM information_schema.columns
    WHERE table_schema = DATABASE() AND table_name = 'vouchers' AND column_name = 'shop_id'
  ) THEN
    ALTER TABLE vouchers DROP COLUMN shop_id;
  END IF;

  -- 4) COUPONS: add `store_id`, copy from `shop_id`, update FK, drop old column
  IF NOT EXISTS (
    SELECT 1 FROM information_schema.columns
    WHERE table_schema = DATABASE() AND table_name = 'coupons' AND column_name = 'store_id'
  ) THEN
    ALTER TABLE coupons ADD COLUMN store_id INT NULL;
  END IF;

  IF EXISTS (
    SELECT 1 FROM information_schema.columns
    WHERE table_schema = DATABASE() AND table_name = 'coupons' AND column_name = 'shop_id'
  ) THEN
    UPDATE coupons SET store_id = COALESCE(store_id, shop_id);
  END IF;

  IF EXISTS (
    SELECT 1 FROM information_schema.table_constraints
    WHERE table_schema = DATABASE() AND table_name = 'coupons' AND constraint_name = 'fk_coupons_shop'
  ) THEN
    ALTER TABLE coupons DROP FOREIGN KEY fk_coupons_shop;
  END IF;

  IF NOT EXISTS (
    SELECT 1 FROM information_schema.table_constraints
    WHERE table_schema = DATABASE() AND table_name = 'coupons' AND constraint_name = 'fk_coupons_store'
  ) THEN
    ALTER TABLE coupons
      ADD CONSTRAINT fk_coupons_store FOREIGN KEY (store_id) REFERENCES stores(store_id) ON DELETE SET NULL;
  END IF;

  IF EXISTS (
    SELECT 1 FROM information_schema.columns
    WHERE table_schema = DATABASE() AND table_name = 'coupons' AND column_name = 'shop_id'
  ) THEN
    ALTER TABLE coupons DROP COLUMN shop_id;
  END IF;

  -- 5) Optionally drop the legacy `shops` table once migrated
  IF EXISTS (
    SELECT 1 FROM information_schema.tables 
    WHERE table_schema = DATABASE() AND table_name = 'shops'
  ) THEN
    DROP TABLE shops;
  END IF;
END //
DELIMITER ;

CALL migrate_shop_to_store();
DROP PROCEDURE IF EXISTS migrate_shop_to_store;
