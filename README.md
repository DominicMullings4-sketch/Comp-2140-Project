# Comp-2140-Project
## Feature 7 — Confirm Delivery

For a fresh database, import `database/schema.sql` and the development seed as
described in CodingInstructions.md. If Track-It is already installed, import
`database/migrations/007_confirm_delivery.sql` once instead of reloading the schema.

Open `deliveries.php` as the business owner and select a dispatch date. Each
order receives its date when it becomes confirmed. The cutoff is 10:00:00 a.m.
Jamaica time, inclusive. Saving a bearer assignment leaves the order confirmed.
Recording handover moves it to Out for Delivery and saves the bearer and time.

Older databases did not record confirmation times. For previously confirmed
orders, restore the actual local confirmation time in `orders.confirmed_at` from
business records. The database then calculates the dispatch date. Order entry
and confirmation can happen on different days.
