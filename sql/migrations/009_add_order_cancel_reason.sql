-- 009_add_order_cancel_reason.sql
-- Adds cancel_reason column to orders to store seller-provided explanations when cancelling.

ALTER TABLE orders
  ADD COLUMN cancel_reason TEXT NULL AFTER status;
