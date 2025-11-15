-- 011_seed_west_fashion_products.sql
-- Seeds "West Fashion" with at least five products per Fashion subcategory
-- (Accessories, Bags, Kids & Baby Clothing, Men's Clothing, Shoes, Women's Clothing).
-- Each insert uses INSERT ... SELECT with a NOT EXISTS guard so rerunning the script
-- will not duplicate listings for the same seller + product name combination.

START TRANSACTION;

SET @store_name := 'West Fashion';
SET @seller_id := (
  SELECT seller_id FROM stores WHERE store_name = @store_name ORDER BY store_id DESC LIMIT 1
);
SET @seller_id := COALESCE(
  @seller_id,
  (SELECT seller_id FROM seller_profiles WHERE shop_name = @store_name ORDER BY profile_id DESC LIMIT 1)
);
-- If the lookups above return NULL, manually set the seller id below before running the inserts.
-- SET @seller_id := 2;

SELECT CONCAT('Seeding products for West Fashion seller_id = ', COALESCE(CAST(@seller_id AS CHAR), 'NOT FOUND')) AS debug_message;

-- Accessories (category_id = 28)
INSERT INTO products (seller_id, category_id, name, description, price, stock, image, status)
SELECT @seller_id, 28, 'Aurora Layered Pendant Necklace',
       'Layered 18k gold-plated necklace with adjustable clasp and glass crystal accents.',
       749.00, 35,
       'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=900&q=80',
       'active'
WHERE @seller_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE seller_id = @seller_id AND name = 'Aurora Layered Pendant Necklace');

INSERT INTO products (seller_id, category_id, name, description, price, stock, image, status)
SELECT @seller_id, 28, 'Opal Glow Drop Earrings',
       'Ultra-light drop earrings with faux opal stones and nickel-free hooks for everyday wear.',
       549.00, 45,
       'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?auto=format&fit=crop&w=900&q=80',
       'active'
WHERE @seller_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE seller_id = @seller_id AND name = 'Opal Glow Drop Earrings');

INSERT INTO products (seller_id, category_id, name, description, price, stock, image, status)
SELECT @seller_id, 28, 'Velvet Luxe Hair Clip Set',
       'Set of three velvet hair clips lined with anti-slip grips to keep styles in place all day.',
       299.00, 60,
       'https://images.unsplash.com/photo-1514996937319-344454492b37?auto=format&fit=crop&w=900&q=80',
       'active'
WHERE @seller_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE seller_id = @seller_id AND name = 'Velvet Luxe Hair Clip Set');

INSERT INTO products (seller_id, category_id, name, description, price, stock, image, status)
SELECT @seller_id, 28, 'Quartz Stackable Bracelet Pack',
       'Set of four stretch bracelets with rose-quartz beads, matte gold spacers, and elastic cords.',
       459.00, 40,
       'https://images.unsplash.com/photo-1520962918287-7448c2878f65?auto=format&fit=crop&w=900&q=80',
       'active'
WHERE @seller_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE seller_id = @seller_id AND name = 'Quartz Stackable Bracelet Pack');

INSERT INTO products (seller_id, category_id, name, description, price, stock, image, status)
SELECT @seller_id, 28, 'Classic Leather Skinny Belt',
       'Full-grain leather belt with polished hardware designed to cinch dresses or layer over blazers.',
       899.00, 30,
       'https://images.unsplash.com/photo-1503602642458-232111445657?auto=format&fit=crop&w=900&q=80',
       'active'
WHERE @seller_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE seller_id = @seller_id AND name = 'Classic Leather Skinny Belt');

-- Bags (category_id = 27)
INSERT INTO products (seller_id, category_id, name, description, price, stock, image, status)
SELECT @seller_id, 27, 'Midnight City Crossbody',
       'Pebbled vegan leather crossbody with adjustable strap, zip pocket, and matte black fittings.',
       1890.00, 28,
       'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?auto=format&fit=crop&w=900&q=80',
       'active'
WHERE @seller_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE seller_id = @seller_id AND name = 'Midnight City Crossbody');

INSERT INTO products (seller_id, category_id, name, description, price, stock, image, status)
SELECT @seller_id, 27, 'Everyday Structured Tote',
       'Roomy saffiano tote with padded laptop sleeve, key leash, and reinforced base feet.',
       2190.00, 24,
       'https://images.unsplash.com/photo-1518544889280-54e5f79f31fc?auto=format&fit=crop&w=900&q=80',
       'active'
WHERE @seller_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE seller_id = @seller_id AND name = 'Everyday Structured Tote');

INSERT INTO products (seller_id, category_id, name, description, price, stock, image, status)
SELECT @seller_id, 27, 'Metro Sling Pack',
       'Slim anti-theft sling with hidden zipper panel, breathable back mesh, and USB pass-through.',
       1250.00, 36,
       'https://images.unsplash.com/photo-1507679799987-c73779587ccf?auto=format&fit=crop&w=900&q=80',
       'active'
WHERE @seller_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE seller_id = @seller_id AND name = 'Metro Sling Pack');

INSERT INTO products (seller_id, category_id, name, description, price, stock, image, status)
SELECT @seller_id, 27, 'Heritage Mini Backpack',
       'Compact daypack with twin front pockets, padded straps, and water-resistant twill shell.',
       1580.00, 32,
       'https://images.unsplash.com/photo-1475180098004-ca77a66827be?auto=format&fit=crop&w=900&q=80',
       'active'
WHERE @seller_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE seller_id = @seller_id AND name = 'Heritage Mini Backpack');

INSERT INTO products (seller_id, category_id, name, description, price, stock, image, status)
SELECT @seller_id, 27, 'Weekend Canvas Duffle',
       'Oversized canvas duffle with leather trims, trolley sleeve, and removable shoulder strap.',
       2450.00, 20,
       'https://images.unsplash.com/photo-1483985988355-763728e1935b?auto=format&fit=crop&w=900&q=80',
       'active'
WHERE @seller_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE seller_id = @seller_id AND name = 'Weekend Canvas Duffle');

-- Kids & Baby Clothing (category_id = 25)
INSERT INTO products (seller_id, category_id, name, description, price, stock, image, status)
SELECT @seller_id, 25, 'CloudSoft Baby Romper Set',
       'Two-piece bamboo romper set featuring fold-over mittens and nickel-free snaps.',
       690.00, 45,
       'https://images.unsplash.com/photo-1504151932400-72d4384f04b3?auto=format&fit=crop&w=900&q=80',
       'active'
WHERE @seller_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE seller_id = @seller_id AND name = 'CloudSoft Baby Romper Set');

INSERT INTO products (seller_id, category_id, name, description, price, stock, image, status)
SELECT @seller_id, 25, 'Playground Stretch Denim',
       'Soft stretch denim with knee reinforcements and adjustable waist for growing kids.',
       780.00, 38,
       'https://images.unsplash.com/photo-1470813740244-df37b8c1edcb?auto=format&fit=crop&w=900&q=80',
       'active'
WHERE @seller_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE seller_id = @seller_id AND name = 'Playground Stretch Denim');

INSERT INTO products (seller_id, category_id, name, description, price, stock, image, status)
SELECT @seller_id, 25, 'Sunbeam Graphic Tee Pack',
       'Three-piece cotton tee set with sunny graphics and tag-free collars for itch-free days.',
       650.00, 50,
       'https://images.unsplash.com/photo-1469474968028-56623f02e42e?auto=format&fit=crop&w=900&q=80',
       'active'
WHERE @seller_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE seller_id = @seller_id AND name = 'Sunbeam Graphic Tee Pack');

INSERT INTO products (seller_id, category_id, name, description, price, stock, image, status)
SELECT @seller_id, 25, 'Dreamy Knitted Cardigan',
       'Cozy knit cardigan with wooden buttons and cloud embroidery for cooler play dates.',
       890.00, 34,
       'https://images.unsplash.com/photo-1503341455253-b2e723bb3dbb?auto=format&fit=crop&w=900&q=80',
       'active'
WHERE @seller_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE seller_id = @seller_id AND name = 'Dreamy Knitted Cardigan');

INSERT INTO products (seller_id, category_id, name, description, price, stock, image, status)
SELECT @seller_id, 25, 'Galaxy Lights Sneakers',
       'Lightweight sneakers with galaxy print mesh uppers and glow-in-the-dark eyelets.',
       1190.00, 42,
       'https://images.unsplash.com/photo-1517486808906-6ca8b3f04846?auto=format&fit=crop&w=900&q=80',
       'active'
WHERE @seller_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE seller_id = @seller_id AND name = 'Galaxy Lights Sneakers');

-- Men's Clothing (category_id = 23)
INSERT INTO products (seller_id, category_id, name, description, price, stock, image, status)
SELECT @seller_id, 23, 'Heritage Oxford Shirt',
       'Tailored oxford shirt with button-down collar, locker loop, and easy-iron cotton.',
       1290.00, 40,
       'https://images.unsplash.com/photo-1503342250614-ca4407868a5b?auto=format&fit=crop&w=900&q=80',
       'active'
WHERE @seller_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE seller_id = @seller_id AND name = 'Heritage Oxford Shirt');

INSERT INTO products (seller_id, category_id, name, description, price, stock, image, status)
SELECT @seller_id, 23, 'Coastal Linen Camp Shirt',
       'Breathable linen-blend camp shirt with coconut buttons and relaxed sleeves for summer.',
       1490.00, 34,
       'https://images.unsplash.com/photo-1490111718993-d98654ce6cf7?auto=format&fit=crop&w=900&q=80',
       'active'
WHERE @seller_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE seller_id = @seller_id AND name = 'Coastal Linen Camp Shirt');

INSERT INTO products (seller_id, category_id, name, description, price, stock, image, status)
SELECT @seller_id, 23, 'CoreFlex Chino Pants',
       'Tapered chino pants with 4-way stretch, smart creases, and hidden zip pocket.',
       1350.00, 38,
       'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?auto=format&fit=crop&w=900&q=80',
       'active'
WHERE @seller_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE seller_id = @seller_id AND name = 'CoreFlex Chino Pants');

INSERT INTO products (seller_id, category_id, name, description, price, stock, image, status)
SELECT @seller_id, 23, 'Everyday Crew-Neck Tee Pack',
       'Bundle of three enzyme-washed tees with reinforced collars and curved hems.',
       990.00, 55,
       'https://images.unsplash.com/photo-1503602642458-232111445657?auto=format&fit=crop&w=900&q=80',
       'active'
WHERE @seller_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE seller_id = @seller_id AND name = 'Everyday Crew-Neck Tee Pack');

INSERT INTO products (seller_id, category_id, name, description, price, stock, image, status)
SELECT @seller_id, 23, 'AeroTech Training Hoodie',
       'Moisture-wicking hoodie with reflective piping, thumbholes, and media pocket.',
       1650.00, 28,
       'https://images.unsplash.com/photo-1489987707025-afc232f7ea0f?auto=format&fit=crop&w=900&q=80',
       'active'
WHERE @seller_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE seller_id = @seller_id AND name = 'AeroTech Training Hoodie');

-- Shoes (category_id = 26)
INSERT INTO products (seller_id, category_id, name, description, price, stock, image, status)
SELECT @seller_id, 26, 'Pulse Runner Knit Sneakers',
       'Knit upper runners with responsive foam midsole, wide toe box, and reflective heel tab.',
       2250.00, 30,
       'https://images.unsplash.com/photo-1514986888952-8cd320577b68?auto=format&fit=crop&w=900&q=80',
       'active'
WHERE @seller_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE seller_id = @seller_id AND name = 'Pulse Runner Knit Sneakers');

INSERT INTO products (seller_id, category_id, name, description, price, stock, image, status)
SELECT @seller_id, 26, 'Heritage Leather Brogues',
       'Hand-burnished leather brogues with Goodyear welt construction and cushioned insoles.',
       3150.00, 22,
       'https://images.unsplash.com/photo-1460353581641-37baddab0fa2?auto=format&fit=crop&w=900&q=80',
       'active'
WHERE @seller_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE seller_id = @seller_id AND name = 'Heritage Leather Brogues');

INSERT INTO products (seller_id, category_id, name, description, price, stock, image, status)
SELECT @seller_id, 26, 'Coastline Espadrille Flats',
       'Handwoven espadrilles with jute soles, cushioned footbeds, and color-pop canvas.',
       1750.00, 26,
       'https://images.unsplash.com/photo-1505685296765-3a2736de412f?auto=format&fit=crop&w=900&q=80',
       'active'
WHERE @seller_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE seller_id = @seller_id AND name = 'Coastline Espadrille Flats');

INSERT INTO products (seller_id, category_id, name, description, price, stock, image, status)
SELECT @seller_id, 26, 'Elevate Court High-Tops',
       'Padded high-top sneakers with contrast panels, rubber cup sole, and waxed laces.',
       2890.00, 24,
       'https://images.unsplash.com/photo-1529333166437-7750a6dd5a70?auto=format&fit=crop&w=900&q=80',
       'active'
WHERE @seller_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE seller_id = @seller_id AND name = 'Elevate Court High-Tops');

INSERT INTO products (seller_id, category_id, name, description, price, stock, image, status)
SELECT @seller_id, 26, 'Nimbus Cloud Slides',
       'Ultra-cushioned EVA slides with anti-slip grooves and contoured footbed for recovery days.',
       950.00, 45,
       'https://images.unsplash.com/photo-1504198458649-3128b932f49b?auto=format&fit=crop&w=900&q=80',
       'active'
WHERE @seller_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE seller_id = @seller_id AND name = 'Nimbus Cloud Slides');

-- Women's Clothing (category_id = 24)
INSERT INTO products (seller_id, category_id, name, description, price, stock, image, status)
SELECT @seller_id, 24, 'Muse Pleated Midi Dress',
       'Fluid pleated midi dress with removable sash, flutter sleeves, and hidden side zip.',
       1890.00, 32,
       'https://images.unsplash.com/photo-1487412720507-e7ab37603c6f?auto=format&fit=crop&w=900&q=80',
       'active'
WHERE @seller_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE seller_id = @seller_id AND name = 'Muse Pleated Midi Dress');

INSERT INTO products (seller_id, category_id, name, description, price, stock, image, status)
SELECT @seller_id, 24, 'Serene Wrap Blouse',
       'Soft crepe wrap blouse with shirred cuffs, interior snap, and waist tie for custom fit.',
       1290.00, 40,
       'https://images.unsplash.com/photo-1495129128850-29c27f9ad89c?auto=format&fit=crop&w=900&q=80',
       'active'
WHERE @seller_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE seller_id = @seller_id AND name = 'Serene Wrap Blouse');

INSERT INTO products (seller_id, category_id, name, description, price, stock, image, status)
SELECT @seller_id, 24, 'Aline High-Rise Culottes',
       'Structured culottes with pleated front, stretch waistband, and side seam pockets.',
       1490.00, 36,
       'https://images.unsplash.com/photo-1504593811423-6dd665756598?auto=format&fit=crop&w=900&q=80',
       'active'
WHERE @seller_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE seller_id = @seller_id AND name = 'Aline High-Rise Culottes');

INSERT INTO products (seller_id, category_id, name, description, price, stock, image, status)
SELECT @seller_id, 24, 'FeatherSoft Lounge Set',
       'Matching brushed-knit lounge top and jogger with rib cuffs and deep hand pockets.',
       1650.00, 28,
       'https://images.unsplash.com/photo-1503341455253-b2e723bb3dbb?auto=format&fit=crop&w=900&q=80',
       'active'
WHERE @seller_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE seller_id = @seller_id AND name = 'FeatherSoft Lounge Set');

INSERT INTO products (seller_id, category_id, name, description, price, stock, image, status)
SELECT @seller_id, 24, 'Luxe Essential Tank Trio',
       'Pack of three modal tanks with curved hem, bra-friendly straps, and raw edge finish.',
       990.00, 50,
       'https://images.unsplash.com/photo-1491553895911-0055eca6402d?auto=format&fit=crop&w=900&q=80',
       'active'
WHERE @seller_id IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM products WHERE seller_id = @seller_id AND name = 'Luxe Essential Tank Trio');

COMMIT;
