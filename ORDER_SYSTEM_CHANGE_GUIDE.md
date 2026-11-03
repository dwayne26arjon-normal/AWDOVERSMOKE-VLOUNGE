# Oversmoke Vlounge Order System

## What changed

Orders now live in the Laravel database instead of being saved only in the customer's browser. This lets a customer see their order history after signing in and lets an authorized admin manage the same orders.

### Customer checkout

- Checkout sends product IDs, quantities, color selections, delivery details, and payment method to the server.
- Product names and prices are read from the database. The browser cannot set the order price.
- The server checks that each product is active and has enough stock before accepting the order.
- A successful order is saved with a snapshot of its customer, product, price, and delivery information.
- Stock is deducted when the order is placed.
- The cart is cleared only after checkout succeeds. If checkout fails, the customer sees an error and can retry.
- The customer Orders page displays database-backed orders belonging to the signed-in customer only.

### Admin order management

Authorized admins can:

- View and search the order list.
- Open an order to see customer details, products, quantities, totals, and status.
- Change an order's status.
- Print an invoice or request that it be emailed to the customer.

When an order is cancelled, its product stock is restored. If the order is reopened, the system checks active inventory and reserves the stock again. The stock adjustment and status change are performed together to avoid a partial update.

### Payment and shipping

- The current payment method is **Cash on delivery**. The site does not process online payments.
- Shipping fee is **₱0 for now**, as requested. No flat delivery charge has been added.
- Shipping calculation by customer location remains future work.

## How to use it

### Customer

1. Sign in to the customer account.
2. Add products to the cart and continue to checkout.
3. Confirm the delivery name, phone number, and address.
4. Confirm the purchase. Successful orders are stored in the account and appear on the Orders page.

### Admin

1. Sign in at `/admin/login` with an admin account.
2. Open **Orders** from the admin navigation.
3. Select an order to review its details, update its status, print its invoice, or email the invoice.

Admin pages are restricted to accounts marked as administrators. A **403 Forbidden** at `/admin/orders` means the currently signed-in account is not recognized as an admin. Sign out and use the admin login with the admin account. The first admin account can be created at `/admin/register` if no admin exists yet; registration closes after that.

## Database and deployment notes

- The order schema is added by `2026_10_28_000004_create_orders_table.php`.
- Run `php artisan migrate` in the deployment environment to create the orders and order-items tables.
- Orders are tied to the signed-in customer, while customer and item details are also snapshotted for the order record.
- Old orders that existed only in browser local storage are not imported automatically. New orders placed after this change are saved in the database.
- Invoice email delivery depends on the application's mail configuration. A mail transport failure is reported; it is not shown as a successful send.
- For deployment, configure the production database, mail transport, application URL, and HTTPS/session settings using the hosting provider's environment configuration. Do not put mail passwords or other secrets in this guide or source control.
- Shipping rates should be implemented as a server-side calculation using a confirmed location/rate policy before the shop starts charging shipping.

## Main implementation files

- `app/Http/Controllers/User/OrderController.php` — checkout validation, order creation, customer order history.
- `app/Http/Controllers/Admin/AdminOrderController.php` — admin order list, details, status updates, invoice actions, and inventory adjustments.
- `app/Models/Order.php` and `app/Models/OrderItem.php` — order data and relationships.
- `database/migrations/2026_10_28_000004_create_orders_table.php` — orders and order items database tables.
- `public/js/checkout.js` — submits checkout to the server and handles success or errors.
- `resources/views/user/orders.blade.php` — customer order history.
- `resources/views/admin/orders/` — admin order list, details, and invoice templates.
- `routes/user.php` and `routes/admin.php` — customer and admin order routes.
- `tests/Feature/User/OrdersTest.php` and `tests/Feature/Admin/AdminOrderManagementTest.php` — order-flow and admin-order tests.

## Verification performed

- All 39 Laravel tests passed (226 assertions).
- Order routes were listed successfully with `php artisan route:list --path=orders`.
- Blade templates compiled successfully with `php artisan view:cache`.
- Checkout JavaScript passed `node --check public/js/checkout.js`.
- The order migration was applied successfully in the local environment.
