# JoyShop (PHP + MySQL)

A simple, joyful e-commerce site built with PHP (PDO), MySQL, and Bootstrap 5. Supports Buyer, Seller, and Admin roles with role-based dashboards, cart/checkout, seller approvals, categories, and chat (Buyer↔Seller, Seller↔Admin).

## Quick Start

1) Create schema

```bash
mysql -u root -p < sql/ecommerce_db.sql
```

2) Seed sample accounts

```bash
php tools/seed.php
```

3) Run PHP dev server (serve the project root)

```bash
php -S localhost:8000 -t .
```

Open http://localhost:8000 in your browser.

## Sample Accounts

- Admin: westragna@gmail.com / 123456789
- Seller: west@gmail.com / 123456789
- Buyer: dbaluyot@gmail.com / 123456789

## Key Paths

- `includes/` — config, db (PDO), helpers, auth
- `templates/` — header/footer with Bootstrap 5 + theme
- `index.php` — homepage (featured + categories)
- `public/` — product and category browsing
- `buyer/` — cart, checkout, orders, chat
- `seller/` — products, orders, chat_admin
- `admin/` — sellers (approve/reject), categories

## Notes

- Prepared statements (PDO) throughout; passwords hashed via `password_hash()` and verified with `password_verify()`.
- Sessions and access control via `require_login()` / `require_role()`.
- Theme: soft pastel blue + yellow, Poppins typography, rounded corners.
- Chat uses `chat_messages` with sender/receiver IDs and timestamps.

## Database

See `sql/ecommerce_db.sql` for schema (with foreign keys). Seeder creates Admin, Seller, Buyer.
