-- Migration: add parent_id to categories and insert top-level + subcategories
-- Idempotent: safe to run multiple times using information_schema checks

DELIMITER $$
CREATE PROCEDURE __migrate_categories_with_parents__()
BEGIN
  DECLARE cnt INT DEFAULT 0;
  DECLARE v_home INT DEFAULT NULL;
  DECLARE v_fashion INT DEFAULT NULL;
  DECLARE v_electronics INT DEFAULT NULL;
  DECLARE v_appliances INT DEFAULT NULL;
  DECLARE v_kitchen INT DEFAULT NULL;
  DECLARE v_beauty INT DEFAULT NULL;
  DECLARE v_baby INT DEFAULT NULL;
  DECLARE v_tools INT DEFAULT NULL;
  DECLARE v_sports INT DEFAULT NULL;
  DECLARE v_pet INT DEFAULT NULL;
  DECLARE v_food INT DEFAULT NULL;
  DECLARE v_automotive INT DEFAULT NULL;
  DECLARE v_computers INT DEFAULT NULL;
  DECLARE v_gaming INT DEFAULT NULL;
  DECLARE v_books INT DEFAULT NULL;
  DECLARE vtmp INT DEFAULT NULL;

  -- Add parent_id column if missing
  SELECT COUNT(*) INTO cnt
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'categories' AND COLUMN_NAME = 'parent_id';
  IF cnt = 0 THEN
    ALTER TABLE categories ADD COLUMN parent_id INT NULL;
  END IF;

  -- Add FK for parent_id if missing
  SELECT COUNT(*) INTO cnt
    FROM information_schema.TABLE_CONSTRAINTS tc
    WHERE tc.CONSTRAINT_SCHEMA = DATABASE() AND tc.TABLE_NAME = 'categories' AND tc.CONSTRAINT_NAME = 'fk_categories_parent' AND tc.CONSTRAINT_TYPE = 'FOREIGN KEY';
  IF cnt = 0 THEN
    -- Ensure no dangling parent references
    UPDATE categories c LEFT JOIN categories p ON c.parent_id = p.category_id
      SET c.parent_id = NULL
      WHERE c.parent_id IS NOT NULL AND p.category_id IS NULL;
    ALTER TABLE categories ADD CONSTRAINT fk_categories_parent FOREIGN KEY (parent_id) REFERENCES categories(category_id) ON DELETE SET NULL;
  END IF;

  -- Helper: insert top-level category if missing and capture id
  SELECT category_id INTO v_home FROM categories WHERE name = 'Home & Living' LIMIT 1;
  IF v_home IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Home & Living', 'Home & Living', NULL);
    SET v_home = LAST_INSERT_ID();
  END IF;

  SELECT category_id INTO v_fashion FROM categories WHERE name = 'Fashion & Apparel' LIMIT 1;
  IF v_fashion IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Fashion & Apparel', 'Fashion & Apparel', NULL);
    SET v_fashion = LAST_INSERT_ID();
  END IF;

  SELECT category_id INTO v_electronics FROM categories WHERE name = 'Electronics & Gadgets' LIMIT 1;
  IF v_electronics IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Electronics & Gadgets', 'Electronics & Gadgets', NULL);
    SET v_electronics = LAST_INSERT_ID();
  END IF;

  SELECT category_id INTO v_appliances FROM categories WHERE name = 'Appliances' LIMIT 1;
  IF v_appliances IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Appliances', 'Appliances', NULL);
    SET v_appliances = LAST_INSERT_ID();
  END IF;

  SELECT category_id INTO v_kitchen FROM categories WHERE name = 'Kitchen & Dining' LIMIT 1;
  IF v_kitchen IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Kitchen & Dining', 'Kitchen & Dining', NULL);
    SET v_kitchen = LAST_INSERT_ID();
  END IF;

  SELECT category_id INTO v_beauty FROM categories WHERE name = 'Beauty & Personal Care' LIMIT 1;
  IF v_beauty IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Beauty & Personal Care', 'Beauty & Personal Care', NULL);
    SET v_beauty = LAST_INSERT_ID();
  END IF;

  SELECT category_id INTO v_baby FROM categories WHERE name = 'Baby, Kids & Toys' LIMIT 1;
  IF v_baby IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Baby, Kids & Toys', 'Baby, Kids & Toys', NULL);
    SET v_baby = LAST_INSERT_ID();
  END IF;

  SELECT category_id INTO v_tools FROM categories WHERE name = 'Tools, DIY & Hardware' LIMIT 1;
  IF v_tools IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Tools, DIY & Hardware', 'Tools, DIY & Hardware', NULL);
    SET v_tools = LAST_INSERT_ID();
  END IF;

  SELECT category_id INTO v_sports FROM categories WHERE name = 'Sports & Outdoors' LIMIT 1;
  IF v_sports IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Sports & Outdoors', 'Sports & Outdoors', NULL);
    SET v_sports = LAST_INSERT_ID();
  END IF;

  SELECT category_id INTO v_pet FROM categories WHERE name = 'Pet Supplies' LIMIT 1;
  IF v_pet IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Pet Supplies', 'Pet Supplies', NULL);
    SET v_pet = LAST_INSERT_ID();
  END IF;

  SELECT category_id INTO v_food FROM categories WHERE name = 'Food & Beverages' LIMIT 1;
  IF v_food IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Food & Beverages', 'Food & Beverages', NULL);
    SET v_food = LAST_INSERT_ID();
  END IF;

  SELECT category_id INTO v_automotive FROM categories WHERE name = 'Automotive' LIMIT 1;
  IF v_automotive IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Automotive', 'Automotive', NULL);
    SET v_automotive = LAST_INSERT_ID();
  END IF;

  SELECT category_id INTO v_computers FROM categories WHERE name = 'Computers & Accessories' LIMIT 1;
  IF v_computers IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Computers & Accessories', 'Computers & Accessories', NULL);
    SET v_computers = LAST_INSERT_ID();
  END IF;

  SELECT category_id INTO v_gaming FROM categories WHERE name = 'Gaming' LIMIT 1;
  IF v_gaming IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Gaming', 'Gaming', NULL);
    SET v_gaming = LAST_INSERT_ID();
  END IF;

  SELECT category_id INTO v_books FROM categories WHERE name = 'Books & Stationery' LIMIT 1;
  IF v_books IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Books & Stationery', 'Books & Stationery', NULL);
    SET v_books = LAST_INSERT_ID();
  END IF;

  -- Insert subcategories for Home & Living
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Furniture' AND parent_id = v_home LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Furniture', 'Furniture', v_home);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Bedding & Pillows' AND parent_id = v_home LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Bedding & Pillows', 'Bedding & Pillows', v_home);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Home Decor' AND parent_id = v_home LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Home Decor', 'Home Decor', v_home);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Kitchenware' AND parent_id = v_home LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Kitchenware', 'Kitchenware', v_home);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Dining Essentials' AND parent_id = v_home LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Dining Essentials', 'Dining Essentials', v_home);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Storage & Organization' AND parent_id = v_home LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Storage & Organization', 'Storage & Organization', v_home);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Lighting' AND parent_id = v_home LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Lighting', 'Lighting', v_home);
  END IF;

  -- Fashion & Apparel subcategories
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Men\'s Clothing' AND parent_id = v_fashion LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Men\'s Clothing', 'Men\'s Clothing', v_fashion);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Women\'s Clothing' AND parent_id = v_fashion LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Women\'s Clothing', 'Women\'s Clothing', v_fashion);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Kids & Baby Clothing' AND parent_id = v_fashion LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Kids & Baby Clothing', 'Kids & Baby Clothing', v_fashion);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Shoes' AND parent_id = v_fashion LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Shoes', 'Shoes', v_fashion);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Bags' AND parent_id = v_fashion LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Bags', 'Bags', v_fashion);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Accessories' AND parent_id = v_fashion LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Accessories', 'Accessories', v_fashion);
  END IF;

  -- Electronics & Gadgets subcategories
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Mobile Phones' AND parent_id = v_electronics LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Mobile Phones', 'Mobile Phones', v_electronics);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Tablets' AND parent_id = v_electronics LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Tablets', 'Tablets', v_electronics);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Laptops' AND parent_id = v_electronics LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Laptops', 'Laptops', v_electronics);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Cameras' AND parent_id = v_electronics LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Cameras', 'Cameras', v_electronics);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Audio Devices' AND parent_id = v_electronics LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Audio Devices', 'Audio Devices', v_electronics);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Smartwatches' AND parent_id = v_electronics LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Smartwatches', 'Smartwatches', v_electronics);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Accessories & Cables' AND parent_id = v_electronics LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Accessories & Cables', 'Accessories & Cables', v_electronics);
  END IF;

  -- Appliances subcategories
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Small Kitchen Appliances' AND parent_id = v_appliances LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Small Kitchen Appliances', 'Small Kitchen Appliances', v_appliances);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Refrigerators' AND parent_id = v_appliances LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Refrigerators', 'Refrigerators', v_appliances);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Air Conditioners' AND parent_id = v_appliances LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Air Conditioners', 'Air Conditioners', v_appliances);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Washing Machines' AND parent_id = v_appliances LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Washing Machines', 'Washing Machines', v_appliances);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Vacuum Cleaners' AND parent_id = v_appliances LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Vacuum Cleaners', 'Vacuum Cleaners', v_appliances);
  END IF;

  -- Kitchen & Dining subcategories
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Cookware' AND parent_id = v_kitchen LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Cookware', 'Cookware', v_kitchen);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Bakeware' AND parent_id = v_kitchen LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Bakeware', 'Bakeware', v_kitchen);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Utensils' AND parent_id = v_kitchen LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Utensils', 'Utensils', v_kitchen);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Kitchen Storage' AND parent_id = v_kitchen LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Kitchen Storage', 'Kitchen Storage', v_kitchen);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Drinkware' AND parent_id = v_kitchen LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Drinkware', 'Drinkware', v_kitchen);
  END IF;

  -- Beauty & Personal Care subcategories
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Skin Care' AND parent_id = v_beauty LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Skin Care', 'Skin Care', v_beauty);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Hair Care' AND parent_id = v_beauty LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Hair Care', 'Hair Care', v_beauty);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Makeup' AND parent_id = v_beauty LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Makeup', 'Makeup', v_beauty);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Fragrances' AND parent_id = v_beauty LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Fragrances', 'Fragrances', v_beauty);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Health & Wellness' AND parent_id = v_beauty LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Health & Wellness', 'Health & Wellness', v_beauty);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Bath & Body' AND parent_id = v_beauty LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Bath & Body', 'Bath & Body', v_beauty);
  END IF;

  -- Baby, Kids & Toys subcategories
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Toys' AND parent_id = v_baby LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Toys', 'Toys', v_baby);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Baby Essentials' AND parent_id = v_baby LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Baby Essentials', 'Baby Essentials', v_baby);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Educational Toys' AND parent_id = v_baby LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Educational Toys', 'Educational Toys', v_baby);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Strollers & Car Seats' AND parent_id = v_baby LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Strollers & Car Seats', 'Strollers & Car Seats', v_baby);
  END IF;

  -- Tools, DIY & Hardware subcategories
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Power Tools' AND parent_id = v_tools LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Power Tools', 'Power Tools', v_tools);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Hand Tools' AND parent_id = v_tools LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Hand Tools', 'Hand Tools', v_tools);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Electrical' AND parent_id = v_tools LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Electrical', 'Electrical', v_tools);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Plumbing' AND parent_id = v_tools LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Plumbing', 'Plumbing', v_tools);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Safety Gear' AND parent_id = v_tools LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Safety Gear', 'Safety Gear', v_tools);
  END IF;

  -- Sports & Outdoors subcategories
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Fitness Equipment' AND parent_id = v_sports LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Fitness Equipment', 'Fitness Equipment', v_sports);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Outdoor Gear' AND parent_id = v_sports LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Outdoor Gear', 'Outdoor Gear', v_sports);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Bicycles' AND parent_id = v_sports LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Bicycles', 'Bicycles', v_sports);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Sports Apparel' AND parent_id = v_sports LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Sports Apparel', 'Sports Apparel', v_sports);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Camping Gear' AND parent_id = v_sports LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Camping Gear', 'Camping Gear', v_sports);
  END IF;

  -- Pet Supplies subcategories
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Dog Supplies' AND parent_id = v_pet LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Dog Supplies', 'Dog Supplies', v_pet);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Cat Supplies' AND parent_id = v_pet LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Cat Supplies', 'Cat Supplies', v_pet);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Food & Treats' AND parent_id = v_pet LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Food & Treats', 'Food & Treats', v_pet);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Pet Grooming' AND parent_id = v_pet LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Pet Grooming', 'Pet Grooming', v_pet);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Aquariums' AND parent_id = v_pet LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Aquariums', 'Aquariums', v_pet);
  END IF;

  -- Food & Beverages subcategories
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Snacks' AND parent_id = v_food LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Snacks', 'Snacks', v_food);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Packaged Goods' AND parent_id = v_food LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Packaged Goods', 'Packaged Goods', v_food);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Drinks' AND parent_id = v_food LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Drinks', 'Drinks', v_food);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Coffee & Tea' AND parent_id = v_food LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Coffee & Tea', 'Coffee & Tea', v_food);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Organic Products' AND parent_id = v_food LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Organic Products', 'Organic Products', v_food);
  END IF;

  -- Automotive subcategories
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Car Accessories' AND parent_id = v_automotive LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Car Accessories', 'Car Accessories', v_automotive);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Motorcycle Accessories' AND parent_id = v_automotive LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Motorcycle Accessories', 'Motorcycle Accessories', v_automotive);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Tools & Maintenance' AND parent_id = v_automotive LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Tools & Maintenance', 'Tools & Maintenance', v_automotive);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Lubricants' AND parent_id = v_automotive LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Lubricants', 'Lubricants', v_automotive);
  END IF;

  -- Computers & Accessories subcategories
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Components' AND parent_id = v_computers LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Components', 'Components', v_computers);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Keyboards & Mice' AND parent_id = v_computers LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Keyboards & Mice', 'Keyboards & Mice', v_computers);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Monitors' AND parent_id = v_computers LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Monitors', 'Monitors', v_computers);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Storage Devices' AND parent_id = v_computers LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Storage Devices', 'Storage Devices', v_computers);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Networking' AND parent_id = v_computers LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Networking', 'Networking', v_computers);
  END IF;

  -- Gaming subcategories
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Consoles' AND parent_id = v_gaming LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Consoles', 'Consoles', v_gaming);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Games' AND parent_id = v_gaming LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Games', 'Games', v_gaming);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Gaming Accessories' AND parent_id = v_gaming LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Gaming Accessories', 'Gaming Accessories', v_gaming);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'VR Gear' AND parent_id = v_gaming LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('VR Gear', 'VR Gear', v_gaming);
  END IF;

  -- Books & Stationery subcategories
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Educational Books' AND parent_id = v_books LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Educational Books', 'Educational Books', v_books);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Novels' AND parent_id = v_books LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Novels', 'Novels', v_books);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'School Supplies' AND parent_id = v_books LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('School Supplies', 'School Supplies', v_books);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Office Tools' AND parent_id = v_books LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Office Tools', 'Office Tools', v_books);
  END IF;

  -- Recommended Default Categories (Simplified Version)
  -- Insert a cleaner starting list of top-level categories (idempotent)
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Electronics' LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Electronics', 'Electronics', NULL);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Mobile & Gadgets' LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Mobile & Gadgets', 'Mobile & Gadgets', NULL);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Home & Living' LIMIT 1;
  -- Home & Living already handled earlier; the check is kept for idempotence
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Home & Living', 'Home & Living', NULL);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Furniture' LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Furniture', 'Furniture', NULL);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Appliances' LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Appliances', 'Appliances', NULL);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Fashion (Men)' LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Fashion (Men)', 'Fashion (Men)', NULL);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Fashion (Women)' LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Fashion (Women)', 'Fashion (Women)', NULL);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Beauty & Personal Care' LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Beauty & Personal Care', 'Beauty & Personal Care', NULL);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Sports & Outdoors' LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Sports & Outdoors', 'Sports & Outdoors', NULL);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Pet Supplies' LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Pet Supplies', 'Pet Supplies', NULL);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Kids & Baby' LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Kids & Baby', 'Kids & Baby', NULL);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Gaming' LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Gaming', 'Gaming', NULL);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Tools & Hardware' LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Tools & Hardware', 'Tools & Hardware', NULL);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Automotive' LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Automotive', 'Automotive', NULL);
  END IF;
  SELECT category_id INTO vtmp FROM categories WHERE name = 'Books & Stationery' LIMIT 1;
  IF vtmp IS NULL THEN
    INSERT INTO categories (name, description, parent_id) VALUES ('Books & Stationery', 'Books & Stationery', NULL);
  END IF;

END$$
DELIMITER ;

CALL __migrate_categories_with_parents__();
DROP PROCEDURE IF EXISTS __migrate_categories_with_parents__;
