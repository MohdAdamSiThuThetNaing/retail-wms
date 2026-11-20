# Enterprise Retail & Warehouse Management System (Laravel)

A senior-level portfolio starter for demonstrating Laravel MVC, MySQL design, inventory consistency,
authorization, REST APIs, automated tests, and Linux VPS deployment.

## Stack

-   Laravel 12 / PHP 8.3+
-   MySQL 8
-   Blade + Tailwind (use Laravel's starter kit if you want a ready-made UI)
-   Laravel Sanctum (API authentication)
-   Redis (optional cache/queue backend)
-   PHPUnit / Pest

## Important scope note

This is a **Laravel application starter/implementation pack**, not a pre-installed Laravel vendor directory.
Install Laravel first, then copy these files into the fresh application. This keeps dependencies reproducible
and avoids shipping a large, machine-specific `vendor/` folder.

## 1. Create the Laravel app

```bash
composer create-project laravel/laravel retail-wms "^12.0"
cd retail-wms
```

Copy the contents of this package into the Laravel project root, preserving directories.

## 2. Configure MySQL

Create a database and update `.env`:

```dotenv
APP_NAME="Retail WMS"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=retail_wms
DB_USERNAME=root
DB_PASSWORD=your_password

CACHE_STORE=database
QUEUE_CONNECTION=database
SESSION_DRIVER=database
```

Then run:

```bash
php artisan key:generate
php artisan migrate
php artisan serve
```

## 3. Seed demo data

```bash
php artisan db:seed
```

The seeder creates two warehouses and sample products. For production, replace demo credentials and
add a proper user onboarding flow. The example seeder intentionally does not create a hard-coded admin password.

## 4. Main API endpoints

All routes are prefixed with `/api/v1`.

| Method | Endpoint                               | Purpose                |
| ------ | -------------------------------------- | ---------------------- |
| GET    | `/api/v1/products`                     | Paginated product list |
| POST   | `/api/v1/products`                     | Create product         |
| GET    | `/api/v1/warehouses`                   | Warehouse list         |
| GET    | `/api/v1/warehouses/{warehouse}/stock` | Stock by warehouse     |
| POST   | `/api/v1/inventory/receive`            | Receive stock          |
| POST   | `/api/v1/inventory/transfer`           | Transfer stock         |
| GET    | `/api/v1/inventory/movements`          | Movement audit trail   |

Protect the routes with Sanctum middleware after installing/configuring Sanctum:

```bash
php artisan install:api
```

The supplied route file shows the intended protected boundary. For a quick local walkthrough,
temporarily remove `auth:sanctum` only in a local development environment; do not do this in a demo
that is exposed to the internet.

## 5. Architecture decisions to explain in the interview

-   **Thin controllers:** HTTP validation and response formatting live in controllers/Form Requests.
-   **Service layer:** inventory business rules are centralized in `InventoryService`.
-   **Database transactions:** a stock transfer debits and credits stock in one transaction.
-   **Row-level locking:** `lockForUpdate()` prevents concurrent requests from overselling the same stock.
-   **Auditability:** every inventory change creates an immutable movement record.
-   **Indexes:** unique SKU and composite warehouse/product indexes support common lookups.
-   **Pagination:** product and movement lists are paginated instead of loading all records.
-   **Validation:** Form Requests validate inputs before business logic runs.
-   **Authorization:** use policies/roles for warehouse-level access; the starter leaves role assignment as an extension.

## 6. Production deployment outline (Ubuntu VPS)

1. Provision Ubuntu LTS, create a non-root deploy user, configure SSH keys and firewall.
2. Install PHP 8.3 FPM and required extensions, Composer, Nginx, MySQL, and Git.
3. Deploy the code into `/var/www/retail-wms`.
4. Configure `.env` with production secrets; never commit it.
5. Run `composer install --no-dev --optimize-autoloader`.
6. Run `php artisan migrate --force`.
7. Set `storage` and `bootstrap/cache` ownership/permissions for the PHP-FPM user.
8. Configure Nginx to serve the `public/` directory and pass PHP requests to PHP-FPM.
9. Issue TLS with Certbot / Let's Encrypt.
10. Configure queue workers with Supervisor or systemd.
11. Run `php artisan config:cache`, `route:cache`, and `view:cache`.
12. Add backups, log rotation, monitoring, and a rollback procedure.

See `deploy/nginx-retail-wms.conf` and `deploy/DEPLOYMENT.md`.

## 7. Interview demo flow (8–10 minutes)

1. Explain the domain: multiple warehouses, SKUs, stock movements.
2. Show the schema and explain the unique/index constraints.
3. Create a product and receive stock.
4. Transfer stock between warehouses.
5. Explain the DB transaction and row lock in `InventoryService`.
6. Show the movement audit trail.
7. Explain API authentication, authorization, validation, and tests.
8. Walk through the Nginx/PHP-FPM/SSL deployment architecture.

## 8. Suggested next enhancements

-   Role/permission management using policies or a permission package.
-   Barcode scanning UI.
-   Purchase order approval workflow.
-   Reservation/commitment of stock for sales orders.
-   Idempotency keys for retry-safe inventory APIs.
-   Outbox pattern for reliable domain events.
-   Redis-backed queues and cache invalidation.
-   Feature tests for concurrent transfers and insufficient stock.
-   CI pipeline and zero-downtime deployment.

Be transparent in the interview about which features you personally implemented and which are planned extensions.
