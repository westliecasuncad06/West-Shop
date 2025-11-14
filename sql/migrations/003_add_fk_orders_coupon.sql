-- Migration: add fk_orders_coupon to `orders` (safe, idempotent)
-- Adds FOREIGN KEY (orders.coupon_id) -> coupons(coupon_id) if it doesn't exist.
-- Uses a stored procedure + DELIMITER so it can run as a single file in tools/migrate.php.

DELIMITER $$
CREATE PROCEDURE __add_fk_orders_coupon__()
BEGIN
  DECLARE cnt INT DEFAULT 0;
  SELECT COUNT(*) INTO cnt
    FROM information_schema.TABLE_CONSTRAINTS tc
    WHERE tc.CONSTRAINT_SCHEMA = DATABASE()
      AND tc.TABLE_NAME = 'orders'
      AND tc.CONSTRAINT_NAME = 'fk_orders_coupon'
      AND tc.CONSTRAINT_TYPE = 'FOREIGN KEY';

  IF cnt = 0 THEN
    -- Clear any dangling coupon references to avoid FK creation failure
    UPDATE orders o
      LEFT JOIN coupons c ON o.coupon_id = c.coupon_id
    SET o.coupon_id = NULL
    WHERE o.coupon_id IS NOT NULL AND c.coupon_id IS NULL;

    ALTER TABLE orders
      ADD CONSTRAINT fk_orders_coupon FOREIGN KEY (coupon_id) REFERENCES coupons(coupon_id) ON DELETE SET NULL;
  END IF;
END$$
DELIMITER ;

CALL __add_fk_orders_coupon__();
DROP PROCEDURE IF EXISTS __add_fk_orders_coupon__;
