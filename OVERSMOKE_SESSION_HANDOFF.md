# Oversmoke Vlounge — Project and Session Handoff

Updated: November 21, 2026

This note summarizes the earlier work and the latest EthioDeploy/MySQL troubleshooting. No passwords, database URLs, or other secrets are included.

## GitHub and deployment guidance discussed

- Explained the basic Git flow: stage the intended files, commit them with a message, then push the branch to GitHub. Recent commits listed below were pushed with the user's approval.
- Explained that `php artisan migrate --force` must run in the environment whose database should be changed. Running it from VS Code normally targets the local configuration, not EthioDeploy.
- Explained that a local phpMyAdmin database and EthioDeploy's hosted database are separate; local database imports do not initialize the hosted add-on.
- Explained that hosted mail variables belong in EthioDeploy's settings and that a redeploy is needed for runtime configuration changes to take effect.

## Project work completed

### Admin, owner, and staff access

- Admin registration is intended for the first owner; it is not an unrestricted public staff-signup form.
- Added owner-managed staff accounts. Staff can access store work areas such as products, inventory, and orders, while staff management remains owner-only.
- Added email-code password recovery for admin/staff and customer accounts.
- Relevant changes were committed in `6cc7eac` and later account/auth commits.

### MySQL schema and migrations

- Created `database/mysql-schema.sql` as a structure-only MySQL schema; it does not copy data out of SQLite.
- The schema was imported into the user's local phpMyAdmin/XAMPP database. At that time, `php artisan migrate:status` showed 11 migrations as run, and `php artisan migrate` reported “Nothing to migrate.”
- That local database is separate from EthioDeploy's database. Local phpMyAdmin on `localhost` is not reachable by the hosted app.
- The EthioDeploy MySQL add-on was added later. Its new database is empty until initialized.

### Orders, live updates, and time display

- Added browser polling for customer order-status changes and admin order updates/new-order notices (5-second polling, not WebSockets).
- Set order timestamps to Manila time (`Asia/Manila`, with the MySQL session timezone configured for UTC+08:00).
- Order updates and timezone work was committed in `d599302`.

### Invoice email

- Added/troubleshot invoice email delivery and made mail transport failures visible instead of reporting success when mail is only logged locally.
- Local `.env` SMTP settings do not automatically configure EthioDeploy; hosted mail settings belong in EthioDeploy's environment settings and need a redeploy.
- The user later confirmed email was working after configuring the hosted mail settings. No SMTP passwords or addresses are reproduced here.

### Cart and phone layout

- Removed the word “Cart” from customer navigation while retaining the cart icon/count.
- Improved phone layouts across shop pages and fixed the mobile filter/sidebar stretching issue.
- Relevant work was committed in `e9e411a` and `4f0aaa0`.

### Product variants, stock, and option images

- For variant products such as juices and pod systems, the displayed overall stock is calculated from the flavor/color stock; the product form presents the total below the options.
- Added image selection for individual flavors/colors and customer-side image previews; selected option images are used for cart/order snapshots.
- Added the variant image database migration and updated the SQL schema.
- This work appears in commit `aef5f30`.

### Notifications

- Added persistent database notifications for customer order placement/status changes and admin/staff new orders.
- Added unread counts/read controls and placed the customer's notifications On/Off preference in the account menu.
- Admin notifications use database-backed data rather than only browser-local state.
- Related work was committed in `aef5f30` and `0438946`; focused notification tests passed during that work.

### Recent conservative cleanup

- Removed the unused `resources/views/welcome.blade.php` and the trivial `tests/Unit/ExampleTest.php` placeholder.
- Updated `tests/Feature/ExampleTest.php` to assert that `/` redirects to the shop home route (rather than incorrectly expecting HTTP 200).
- Committed as `b29b267`. The latest full test run passed: **74 tests, 572 assertions**.

## Recent EthioDeploy/MySQL work

- Commit `c73dbaa` changes `config/database.php` so Laravel uses MySQL when `DATABASE_URL` is present and passes that URL to the MySQL connection.
- This config change is pushed. The app deployment after it failed its health check.
- EthioDeploy's only error shared so far was: `New container failed its health check.` This is a platform-level summary, not the underlying Laravel/SQL error.
- The production MySQL add-on is new and no production migration has been run. A likely cause is that the app's home route queries product tables that do not exist yet, but this has not been confirmed by a detailed error log.
- The user chose to pause until tomorrow. Nothing needs to be done tonight.

## Next steps for tomorrow

1. In EthioDeploy, find out whether there is a shell/console or release-command option for the deployed app.
2. If available, run `php artisan migrate --force` in that deployed environment, where the add-on's `DATABASE_URL` is available. Do not run it in VS Code; that acts on the local environment.
3. If there is no app shell, inspect the EthioDeploy Database tab and its available options before importing SQL. Do **not** import `database/mysql-schema.sql` as-is: it contains `CREATE DATABASE` and `USE oversmoke_vlounge`, which may not match the add-on's assigned database name.
4. If the deployment still fails after the schema is initialized, retrieve the more detailed app/build logs around the failure. The generic health-check line alone is insufficient to identify the cause.

## Safety and workspace notes

- Do not paste `.env`, `DATABASE_URL`, mail passwords, or other credentials into chat.
- The local SQLite database was left untouched during the latest cleanup.
- A new production database starts empty: migrations create tables but do not copy users, products, or orders.
- `OVERSMOKE_SESSION_HANDOFF.md` is a local handoff file and has not been committed or pushed.
