-- Seed 10 gaming products per subcategory for seller dcurtivo@gmail.com
START TRANSACTION;

SET @seller_id = (
    SELECT user_id
    FROM users
    WHERE email = 'dcurtivo@gmail.com'
    LIMIT 1
);
SET @cat_consoles = (
    SELECT category_id
    FROM categories
    WHERE name = 'Consoles'
    LIMIT 1
);
SET @cat_games = (
    SELECT category_id
    FROM categories
    WHERE name = 'Games'
    LIMIT 1
);
SET @cat_accessories = (
    SELECT category_id
    FROM categories
    WHERE name = 'Gaming Accessories'
    LIMIT 1
);
SET @cat_vr = (
    SELECT category_id
    FROM categories
    WHERE name = 'VR Gear'
    LIMIT 1
);

INSERT INTO products (
    seller_id,
    category_id,
    name,
    description,
    price,
    stock,
    image,
    status,
    created_at
)
SELECT
    @seller_id,
    data.category_id,
    data.name,
    data.description,
    data.price,
    data.stock,
    data.image,
    'active',
    NOW()
FROM (
    SELECT @cat_consoles AS category_id, 'PlayStation 5 Slim Disc Edition' AS name, 'Latest PS5 slim chassis with 1TB SSD and DualSense controller.' AS description, 589.00 AS price, 20 AS stock, 'https://m.media-amazon.com/images/I/51OBa7nZsoL._SL1500_.jpg' AS image
    UNION ALL SELECT @cat_consoles, 'Xbox Series X 1TB Performance Bundle', '4K native console with 1TB NVMe storage and Velocity Architecture.', 549.00, 18, 'https://m.media-amazon.com/images/I/71NBQ2a52CL._SL1500_.jpg'
    UNION ALL SELECT @cat_consoles, 'Xbox Series S Carbon Black 1TB', 'Compact next-gen console with 1TB storage and high frame-rate output.', 349.00, 22, 'https://m.media-amazon.com/images/I/71NBAS0Qe2L._SL1500_.jpg'
    UNION ALL SELECT @cat_consoles, 'Nintendo Switch OLED White Joy-Con', 'Vibrant 7-inch OLED Switch with improved audio and docked LAN.', 349.00, 25, 'https://m.media-amazon.com/images/I/61-PblYntsL._SL1500_.jpg'
    UNION ALL SELECT @cat_consoles, 'Nintendo Switch Lite Turquoise', 'Lightweight handheld-only Switch ideal for travel gaming.', 199.00, 30, 'https://m.media-amazon.com/images/I/71I0lQ%2Bv2cL._SL1500_.jpg'
    UNION ALL SELECT @cat_consoles, 'ASUS ROG Ally X Ryzen Z1 Extreme', 'Windows 11 handheld with upgraded cooling and 24GB LPDDR5X RAM.', 799.00, 12, 'https://dlcdnimgs.asus.com/files/media/9C62B2FE-7834-4D2A-9433-3BADF9FE0611.jpg'
    UNION ALL SELECT @cat_consoles, 'Steam Deck OLED 1TB Limited', 'Valve handheld with HDR OLED panel and rapid charging.', 649.00, 14, 'https://cdn.cloudflare.steamstatic.com/steamdeck/images/oled/steam-deck-oled.png'
    UNION ALL SELECT @cat_consoles, 'AYANEO Air 1S Pro 32GB', 'Ultra-portable handheld PC with Ryzen 7 7840U and AMOLED display.', 979.00, 6, 'https://cdn.shopify.com/s/files/1/0605/0373/6645/products/ayaneo-air-1s-pro.jpg?v=1693325400'
    UNION ALL SELECT @cat_consoles, 'Logitech G Cloud Gaming Handheld', 'Cloud-focused Android handheld with 12-hour battery life.', 299.00, 16, 'https://resource.logitechg.com/content/dam/gaming/en/products/g-cloud/g-cloud-gallery-1.png'
    UNION ALL SELECT @cat_consoles, 'Analogue Pocket Black Edition', 'FPGA handheld that plays original Game Boy, GBA and GG carts.', 249.00, 10, 'https://cdn.shopify.com/s/files/1/0545/1285/9836/products/analogue-pocket-black.jpg?v=1667866615'

    UNION ALL SELECT @cat_games, 'Elden Ring Shadow of the Erdtree Edition', 'Base game plus expansion, ready for PS5/PC cross-save.', 79.99, 40, 'https://m.media-amazon.com/images/I/81+KMzZMfjL.__AC_SX300_SY300_QL70_FMwebp_.jpg'
    UNION ALL SELECT @cat_games, 'Final Fantasy VII Rebirth Deluxe', 'Two-disc adventure continuing the Remake saga on PS5.', 89.99, 28, 'https://m.media-amazon.com/images/I/81gOuVhYJ3L._SL1500_.jpg'
    UNION ALL SELECT @cat_games, 'The Legend of Zelda: Tears of the Kingdom', 'Expansive open-world sequel with sky islands and Ultrahand.', 69.99, 35, 'https://m.media-amazon.com/images/I/71YKZ61AqCL._SL1500_.jpg'
    UNION ALL SELECT @cat_games, 'Marvel''s Spider-Man 2 Launch Edition', 'Swing across an expanded New York with Peter and Miles.', 69.99, 32, 'https://m.media-amazon.com/images/I/81H9E6spZTL._SL1500_.jpg'
    UNION ALL SELECT @cat_games, 'Starfield Constellation Edition Code', 'Bethesda space RPG with chronomark watch digital extras.', 299.99, 5, 'https://m.media-amazon.com/images/I/71oQgmO1NCL._SL1500_.jpg'
    UNION ALL SELECT @cat_games, 'Baldur''s Gate 3 Deluxe Physical', 'Collector pack with art cards, stickers and cloth map.', 79.99, 24, 'https://m.media-amazon.com/images/I/81XrOahJ0mL._SL1500_.jpg'
    UNION ALL SELECT @cat_games, 'Call of Duty: Modern Warfare III Cross-Gen', 'Includes both PS4 and PS5 versions plus open beta access.', 69.99, 38, 'https://m.media-amazon.com/images/I/71uZSv8n7iL._SL1500_.jpg'
    UNION ALL SELECT @cat_games, 'Hogwarts Legacy Deluxe Switch', 'Includes Dark Arts pack and exclusive mounts on Switch.', 69.99, 26, 'https://m.media-amazon.com/images/I/81xTbGZccBL._SL1500_.jpg'
    UNION ALL SELECT @cat_games, 'Persona 5 Tactica Phantom Thieves Edition', 'Strategy spin-off with artbook, soundtrack and DLC.', 99.99, 15, 'https://m.media-amazon.com/images/I/81RcmFB1KQL._SL1500_.jpg'
    UNION ALL SELECT @cat_games, 'Helldivers 2 Super Citizen Upgrade', 'Comes with DP-53 armor, SMG-32 and Stratagem hero cape.', 39.99, 45, 'https://m.media-amazon.com/images/I/81aPoPFpXDL._SL1500_.jpg'

    UNION ALL SELECT @cat_accessories, 'DualSense Edge Wireless Controller', 'Pro-grade PS5 controller with replaceable stick modules.', 199.99, 20, 'https://m.media-amazon.com/images/I/61lV6S1MeXL._SL1500_.jpg'
    UNION ALL SELECT @cat_accessories, 'Xbox Elite Wireless Controller Series 2 Core', 'Adjustable-tension thumbsticks and wrap-around rubber grip.', 129.99, 22, 'https://m.media-amazon.com/images/I/61qfNRN9dKL._SL1500_.jpg'
    UNION ALL SELECT @cat_accessories, 'Razer Huntsman V2 TKL Optical Keyboard', 'Linear optical switches with dampening foam and PBT keycaps.', 159.99, 18, 'https://m.media-amazon.com/images/I/71hu4OK4Q1L._SL1500_.jpg'
    UNION ALL SELECT @cat_accessories, 'Logitech G Pro X Superlight 2 Mouse', '54-gram wireless mouse with HERO 32K sensor and USB-C.', 159.99, 25, 'https://m.media-amazon.com/images/I/61p0XsyiCJL._SL1500_.jpg'
    UNION ALL SELECT @cat_accessories, 'SteelSeries Arctis Nova Pro Wireless Headset', 'ANC gaming headset with dual hot-swappable batteries.', 349.99, 16, 'https://m.media-amazon.com/images/I/71sa9Zk1n8L._SL1500_.jpg'
    UNION ALL SELECT @cat_accessories, 'Elgato Stream Deck MK.2 White', '15 customizable LCD keys for macros, scenes and automation.', 179.99, 14, 'https://m.media-amazon.com/images/I/71W+na43EJL._SL1500_.jpg'
    UNION ALL SELECT @cat_accessories, 'Razer Wolverine V2 Pro Controller', 'Low-latency wireless controller with Mecha-Tactile buttons.', 249.99, 12, 'https://m.media-amazon.com/images/I/71kcZq8ptqL._SL1500_.jpg'
    UNION ALL SELECT @cat_accessories, 'Secretlab Titan Evo 2024 XL Gaming Chair', 'Magnetic head pillow and 4-way L-ADAPT lumbar system.', 629.00, 8, 'https://cdn.shopify.com/s/files/1/0159/4211/products/secretlab-titan-evo-2024.jpg?v=1701327852'
    UNION ALL SELECT @cat_accessories, 'NZXT Capsule Mini USB Microphone', 'Cardioid USB mic tuned for crisp team chat and streaming.', 69.99, 30, 'https://m.media-amazon.com/images/I/61R4kmHG65L._SL1500_.jpg'
    UNION ALL SELECT @cat_accessories, 'HyperX ChargePlay Duo for DualSense', 'Adds weighted base and LED indicators for two PS5 controllers.', 39.99, 40, 'https://m.media-amazon.com/images/I/71IsDPKY0tL._SL1500_.jpg'

    UNION ALL SELECT @cat_vr, 'Meta Quest 3 512GB Advanced All-In-One', 'Mixed reality headset with Snapdragon XR2 Gen 2 and Touch Plus controllers.', 649.99, 20, 'https://m.media-amazon.com/images/I/71YgN0TUz6L._SL1500_.jpg'
    UNION ALL SELECT @cat_vr, 'PlayStation VR2 Horizon Bundle', 'Includes Horizon Call of the Mountain voucher and Sense controllers.', 599.99, 15, 'https://m.media-amazon.com/images/I/81YbO6OP0hL._SL1500_.jpg'
    UNION ALL SELECT @cat_vr, 'HTC Vive XR Elite Headset Set', 'Convertible MR headset with removable battery cradle and diopter dials.', 1099.99, 6, 'https://m.media-amazon.com/images/I/716zW0fvkQL._SL1500_.jpg'
    UNION ALL SELECT @cat_vr, 'Valve Index Full VR Kit', 'Includes headset, controllers and 2.0 base stations for SteamVR tracking.', 999.00, 5, 'https://m.media-amazon.com/images/I/61n6CB5YHeL._SL1500_.jpg'
    UNION ALL SELECT @cat_vr, 'PICO 4 Pro 512GB Standalone', 'Balanced pancake optics with eye and face tracking.', 799.00, 7, 'https://m.media-amazon.com/images/I/61ZmtewweYL._SL1500_.jpg'
    UNION ALL SELECT @cat_vr, 'HP Reverb G2 Omnicept Edition', 'Enterprise-grade VR with eye, heart-rate and facial expression sensors.', 1249.00, 4, 'https://m.media-amazon.com/images/I/81Y2ZP3cu6L._SL1500_.jpg'
    UNION ALL SELECT @cat_vr, 'Varjo Aero Professional Headset', 'Mini-LED dual displays with 2880x2720 per eye resolution.', 1990.00, 3, 'https://m.media-amazon.com/images/I/61dVUkA4FBL._SL1500_.jpg'
    UNION ALL SELECT @cat_vr, 'Meta Quest Pro Enterprise Kit', 'Color passthrough MR headset with eye tracking and pro controllers.', 999.99, 6, 'https://m.media-amazon.com/images/I/61xM3U6ZC-L._SL1500_.jpg'
    UNION ALL SELECT @cat_vr, 'HTC Vive Tracker 3.0 Three-Pack', 'Track feet, props or full-body rigs with extended battery life.', 349.00, 18, 'https://m.media-amazon.com/images/I/51uXQHxPPkL._SL1000_.jpg'
    UNION ALL SELECT @cat_vr, 'bHaptics TactSuit X40 Haptic Vest', '40 point haptic feedback suit compatible with leading VR titles.', 499.00, 9, 'https://m.media-amazon.com/images/I/81w35vj0aZL._SL1500_.jpg'
) AS data
WHERE @seller_id IS NOT NULL
  AND data.category_id IS NOT NULL
  AND NOT EXISTS (
      SELECT 1
      FROM products p
      WHERE p.seller_id = @seller_id
        AND p.name = data.name
  );

COMMIT;
