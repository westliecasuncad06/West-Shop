# Ecommerce Database ERD Notes

This document highlights the core relationships that should appear on the entity relationship diagram for the platform. It complements the schema captured in `sql/ecommerce_db.sql`.

## Key Entities

- **users** `(user_id PK)` – Holds buyer, seller, and admin profiles and drives role-based behavior.
- **products** `(product_id PK, seller_id FK → users.user_id)` – Listings created by sellers.
- **orders** `(order_id PK, buyer_id FK → users.user_id, coupon_id FK → coupons.coupon_id)` – Buyer checkouts capturing payment, totals, discounts, and status lifecycle.
- **order_items** `(item_id PK, order_id FK → orders.order_id, product_id FK → products.product_id)` – Line items that link each order to specific products and quantities.
- **notifications** `(notification_id PK, user_id FK → users.user_id)` – In-app alerts delivered to buyers and sellers whenever important events occur (new order, status updates, etc.).
- **chat_messages** `(message_id PK, sender_id FK → users.user_id, receiver_id FK → users.user_id)` – Buyer↔seller conversation threads for post-purchase coordination.

Other supporting tables (categories, coupons, stores, store_reviews, etc.) should also be represented, but the focus above covers the order/communication flow requested.

## Relationship Highlights

1. **users ↔ orders** – One buyer can place many orders (`users.user_id = orders.buyer_id`). Sellers relate indirectly through `products` and `order_items`.
2. **orders ↔ order_items** – Mandatory one‑to‑many; each order has ≥1 item and `order_items.order_id` enforces cascade deletes.
3. **order_items ↔ products** – Each line references exactly one product, preserving price snapshots via `order_items.price`.
4. **products ↔ users (sellers)** – Every product belongs to exactly one seller; deleting a seller cascades to their products per schema definition.
5. **notifications ↔ users** – Notifications target a single user, enabling unread counts and audit history.
6. **chat_messages ↔ users** – Messages store both sender and receiver IDs, enabling bi-directional conversations tied to marketplace participants.

## ERD Guidance for Communication Tables

Include both `notifications` and `chat_messages` on the ERD. They provide a clear view of how operational events (order placement, status changes) and real-time discussions relate back to `users`, `orders`, and `products`. Capturing them makes it easier to reason about which components must fire when a buyer places an order or a seller confirms/shipment updates.

## Suggested Diagram Flow

```
users --< products --< order_items >-- orders >-- notifications
   ^                         ^                     ^
   |                         |                     |
chat_messages ---------------+---------------------+
```

- Arrows indicate "one to many" direction.
- The simplified ASCII view focuses on the scope requested; full diagrams should add remaining tables from the SQL dump (stores, store_reviews, coupons, etc.) as needed.

Document last updated: 2025-11-15
