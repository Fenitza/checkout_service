# Design Decisions — Checkout and Rewards Service

## 1. Overview

This project implements an ecommerce checkout and rewards service using PHP, Laravel, and a relational database.

The main goals are to preserve inventory, order, and coupon consistency; handle repeated checkout requests predictably; retain purchase-time item details; and provide useful reporting.

The solution uses database transactions, row locking, unique constraints, integer-based money calculations, and automated feature tests.

## 2. System Invariants

The following invariants guide the implementation:

1. A cart can produce at most one successfully placed order.
2. Retrying checkout with the same cart and idempotency key must not create another order or deduct inventory again.
3. An idempotency key cannot be reused for a different cart.
4. Inventory must never be sold below zero.
5. A successful order records the product name, unit price, quantity, and line total at purchase time.
6. Gross amount equals the sum of order-item line totals.
7. Net amount equals gross amount minus the applied discount.
8. A coupon can be redeemed by at most one order.
9. A failed checkout must not leave a partially created order, inventory deduction, or coupon redemption.
10. At most one coupon can exist for a given reward milestone.
11. Reports must read existing data without changing business state.
12. Repeating reward generation must not create duplicate coupons for an already rewarded milestone.

These invariants are enforced through application logic, transactions, database constraints, and tests where implemented.

## 3. Decision: Use a Relational Database

**Context:** Orders, inventory, carts, and coupons must remain consistent when operations fail or overlap.

**Options considered:**

- In-memory storage.
- A relational database with transactional support.
- Separate services for orders, inventory, and rewards.

**Choice:** Laravel with a relational database, using MySQL for local application development, SQLite in memory for the existing automated feature tests and POSTMAN for routing checks.

**Why:** A relational database supports transactions, foreign keys, uniqueness constraints, and row-level locking in MySQL. It is appropriate for the consistency requirements and proportionate to the assignment's timebox.

**Consequences:** SQLite tests are fast and isolated, but they do not prove MySQL row-lock behavior. Database-specific concurrency behavior requires additional MySQL testing.

## 4. Decision: Use Integer Minor Units for Money

**Context:** Floating-point calculations can introduce rounding errors into prices, discounts, and revenue reports.

**Options considered:**

- Floating-point amounts.
- Decimal database columns.
- Integer minor units.

**Choice:** Store prices and totals as integer minor units, such as cents or paise, using fields such as `price_cents`, `gross_cents`, `discount_cents`, and `net_cents`.

**Why:** Integer arithmetic is deterministic for the implemented calculations and avoids floating-point representation errors.

**Rounding rule:** Percentage discounts use integer division, effectively rounding down to the nearest minor unit:

`discount = floor(gross × discount percentage / 100)`

The net amount is gross minus discount. The percentage is validated to remain between 0 and 100, so the discount cannot exceed the gross amount.

**Consequences:** API clients must understand that monetary values are minor units, not formatted currency values. Currency formatting belongs in the presentation layer.

## 5. Decision: Use Idempotency Keys for Checkout

**Context:** A client may retry checkout after a timeout without knowing whether the original request succeeded.

**Options considered:**

- Create a new order for every request.
- Infer retries only from cart status.
- Require a client-supplied idempotency key and persist it.

**Choice:** Require an idempotency key and enforce uniqueness in the database. An existing order for the same cart is returned only when the key matches. Conflicting keys return HTTP 409.

**Why:** This prevents retries from creating duplicate orders and makes conflicting requests explicit.

**Consequences:** Clients must retain and reuse the same key when retrying the same logical checkout. Reusing a key for another cart is rejected.

## 6. Decision: Use Transactions and Database Constraints

**Context:** Order creation, inventory deduction, cart state changes, and coupon redemption must succeed or fail together.

**Options considered:**

- Perform each write independently.
- Use application-level checks without transactions.
- Group related writes in a database transaction and enforce critical uniqueness rules in the database.

**Choice:** Checkout runs in a database transaction. The cart, coupon when supplied, and products are locked with `lockForUpdate()`. Products are processed in a consistent order. Unique constraints protect order-to-cart association, idempotency keys, coupon milestones, and coupon redemption associations.

**Why:** Application checks alone are vulnerable to race conditions. Transactions and database constraints provide stronger consistency guarantees.

**Consequences:** Lock contention and deadlocks are possible under load. MySQL behavior must be tested under real concurrent requests. The current SQLite test suite is not proof of concurrency safety.

## 7. Decision: Revalidate Price and Inventory at Checkout

**Context:** A product's price or stock may change after it is added to a cart.

**Options considered:**

- Reserve stock and price when an item is added.
- Use the price displayed when the cart was created.
- Recheck current product data at checkout.

**Choice:** The current product price and inventory are checked during checkout. The order uses the price current at checkout. Checkout is rejected when the requested quantity exceeds available stock.

**Why:** This avoids relying on stale cart data and prevents selling unavailable inventory.

**Consequences:** The amount shown in a cart can change before checkout. A production system could add price-change notifications, reservations, or explicit price confirmation before the customer places the order.

## 8. Decision: Store Immutable Order Item Snapshots

**Context:** Product names and prices can change after purchase, but historical orders must remain explainable.

**Options considered:**

- Calculate historical orders from current product records.
- Copy purchase-time product details into order items.

**Choice:** Store the product name, unit price, quantity, and line total in each order item.

**Why:** Historical order details and revenue calculations remain stable even if a product changes or is deleted.

**Consequences:** Snapshot fields intentionally duplicate product data. The order snapshot is authoritative for historical reporting.

## 9. Decision: Define Coupon Milestones and Redemption

**Context:** Coupon generation must be repeatable, and coupons must not be consumed by failed checkouts.

**Options considered:**

- Generate a new coupon every time an administrator requests generation.
- Generate coupons only for eligible milestones, with a unique record per milestone.
- Generate coupons asynchronously through a job queue.

**Choice:** The reward service reads configurable values for the order milestone interval and discount percentage. It creates missing eligible milestone coupons and relies on a unique milestone constraint to prevent duplicates. Coupon redemption occurs inside the checkout transaction.

**Selected semantics:**

- Only successfully placed orders count toward milestones.
- The milestone is based on the total number of successfully placed orders.
- A milestone can have at most one coupon.
- An available coupon can be applied to one successful checkout.
- An unavailable or already redeemed coupon causes checkout to fail.
- Failed checkout rolls back coupon redemption.
- Discount calculations use integer minor units and round down.

**Why:** This design makes reward generation idempotent and keeps redemption consistent with order creation.

**Consequences:** Reward generation currently runs after a successful checkout transaction, and an administrator can request generation for eligible milestones. Production deployments may need scheduled reconciliation or a durable job mechanism to recover from process failure between order commit and reward generation.

## 10. Decision: Keep Reporting Read-Only

**Context:** Repeated report requests must not alter orders or coupon state.

**Options considered:**

- Update reward state while building the report.
- Compute report values from persisted orders, order items, and coupons.

**Choice:** The report reads persisted data and calculates order totals, purchased quantities, revenue, discounts, and coupon counts without creating or redeeming coupons.

**Why:** Read-only reporting is easier to reason about and ensures repeated GET requests do not mutate state.

**Consequences:** Reports may require aggregation indexes or precomputed summaries if data volume grows. Any precomputed reporting mechanism must preserve reconciliation with source records.

## 11. Decision: Use Explicit HTTP Error Semantics

**Context:** API clients need to distinguish invalid input from conflicts with current business state.

**Choice:**

- `200 OK`: successful retrieval or an idempotent checkout retry.
- `201 Created`: a new order or resource was created.
- `404 Not Found`: the requested resource does not exist.
- `409 Conflict`: inventory, coupon, cart-state, or idempotency conflicts.
- `422 Unprocessable Content`: request validation or other invalid business input that is not represented as a conflict.

**Why:** Distinguishable errors allow clients to respond appropriately instead of treating all failures alike.

**Consequences:** Error messages and status codes should remain consistent across endpoints. A future version could introduce stable machine-readable error codes and a standardized error response schema.

## 12. Decision: Use a Lightweight Fake Checkout Outcome

**Context:** The assignment does not require a real payment provider.

**Choice:** A successful checkout represents successful payment. No external payment integration is required.

**Why:** This keeps the assignment focused on inventory, order consistency, idempotency, and rewards instead of introducing payment-provider credentials and external failure modes.

**Consequences:** This implementation does not demonstrate payment authorization, capture, refunds, webhooks, or reconciliation. A production system would need a payment abstraction and a deliberate strategy for coordinating payment outcomes with order state.

## 13. Automated Testing

The current feature tests cover:

- Reward generation before and at an eligible milestone.
- Idempotent reward generation.
- Successful coupon redemption and discount calculation.
- Failed checkout preserving a coupon.
- Reusing a redeemed coupon.
- Checkout idempotency and conflicting keys.
- Preventing two sequential checkouts from purchasing the last available unit.
- Order retrieval, item snapshots, and unknown order IDs.
- Reporting totals and ensuring GET requests do not mutate state.

**Concurrency limitation:** The sequential inventory test does not prove behavior under simultaneous requests. SQLite in-memory tests do not validate MySQL row-level locking. A real concurrent MySQL test remains an outstanding validation task.

## 14. Implemented Versus Deferred

**Implemented or covered by existing tests:**

- Product seed data and inventory fields.
- Cart creation, retrieval, item management, and totals.
- Checkout, order snapshots, and idempotency checks.
- Inventory validation and transactional updates.
- Optional coupon redemption.
- Milestone-based coupon generation.
- Read-only reporting.
- Order retrieval.
- Feature tests for the listed business rules and failure cases.

**Requires final verification or further work:**

- Genuine concurrent checkout and coupon-redemption tests against MySQL.
- Consistent API documentation for every route and response.
- Administrative access controls, which are outside the required implementation scope.
- Production authentication, authorization, rate limiting, and audit logging.
- Payment-provider integration.
- Durable reward reconciliation after process failure.
- Monitoring, backups, and deployment automation.

## 15. Evolution for Multiple Application Instances and Production Scale

The current database-backed approach can support multiple application instances if they use the same authoritative database and correct transactional constraints.

For production, I would:

1. Keep inventory and coupon decisions in database transactions.
2. Test lock ordering, deadlocks, and retry behavior on the production database engine.
3. Add authenticated administrative routes and customer authorization.
4. Add structured logs, request correlation IDs, metrics, and alerts.
5. Add a payment state machine and idempotent payment-provider integration.
6. Use a durable queue or transactional outbox for asynchronous reward generation and other post-checkout tasks.
7. Add database backups, recovery drills, migration controls, and deployment rollback procedures.
8. Review query plans and indexes using realistic data volumes.
9. Add load and concurrency testing before increasing traffic.

The database, rather than an individual application instance's memory, must remain the authority for inventory, order idempotency, and coupon redemption.

## 16. Use of AI Tools

AI tools were used to assist with implementation planning, controller and test drafting, reviewing edge cases, and documentation.

Generated suggestions were treated as proposals rather than proof of correctness. I ran the tests and used their results to refine the implementation.

One example was an inventory test that expected HTTP 409 but received HTTP 422. I reviewed the distinction between invalid input and a conflict with current inventory, then aligned the stock-conflict behavior and related test expectations with the chosen error contract. This reinforced the importance of validating generated code and tests against the intended API semantics.

AI-generated code was reviewed and adapted to the existing Laravel project, database schema, routes, and test setup. No private AI transcripts are included.

## 17. What I Would Do With Two More Hours

My priorities would be:

1. Implement and run genuine concurrent MySQL tests for two carts competing for the last unit and two checkouts competing for the same coupon.
2. Run the complete automated test suite and fix any regressions.
3. Verify every API route and document exact request, response, status, and error examples.
4. Review transaction boundaries, indexes, and database constraints.
5. Verify the setup instructions from a clean checkout of the repository.
6. Review Git history, secrets, and incomplete work before submission.
