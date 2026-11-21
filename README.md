# Enterprise Retail & Warehouse Management System

A Laravel-based Enterprise Retail and Warehouse Management System (WMS) designed to manage products, product categories, multiple warehouses, inventory balances, stock movements, user permissions, and operational notifications through a web dashboard and versioned REST API.

The application follows a modular monolith architecture and demonstrates backend engineering practices including transactional inventory operations, database integrity, concurrency control, role-based access control (RBAC), API authentication, asynchronous job processing, real-time event broadcasting, and containerized infrastructure.

> **Project status:** Active development. The application currently includes product and category management, warehouse and warehouse-location management, inventory receiving and transfers, stock movement history, Sanctum-protected API endpoints, role-based permissions, Redis-backed queues, Laravel Horizon, Reverb broadcasting, and a notification center. Purchase orders, sales orders, and other advanced retail workflows remain under development.

---

## 1. Technology Stack

| Category                | Technology                              |
| ----------------------- | --------------------------------------- |
| Backend                 | PHP 8.4, Laravel 12                     |
| Database                | MySQL 8                                 |
| ORM                     | Eloquent ORM                            |
| Authentication          | Laravel Sanctum                         |
| Authorization           | Spatie Laravel Permission               |
| Frontend                | Blade, JavaScript, HTML, CSS            |
| Frontend Build          | Vite, Node.js, npm                      |
| Real-Time Communication | Laravel Reverb, Laravel Echo, Pusher JS |
| Queue Processing        | Laravel Horizon                         |
| Cache / Queue Backend   | Redis                                   |
| Web Server              | Nginx                                   |
| PHP Runtime             | PHP-FPM                                 |
| Containerization        | Docker, Docker Compose                  |
| Dependency Management   | Composer                                |
| Testing                 | PHPUnit / Laravel Test Suite            |
| Version Control         | Git, GitHub                             |

## 2. Core Business Capabilities

### 2.1 Product and Catalog Management

-   Create and maintain product records.
-   Manage SKU and barcode identifiers.
-   Maintain product descriptions, brands, units, and pricing.
-   Assign products to categories.
-   Support product image uploads.
-   Activate or deactivate products.
-   Retrieve and paginate product listings.

### 2.2 Warehouse Management

-   Manage multiple warehouses.
-   Maintain warehouse contact information.
-   Track warehouse active status.
-   Organize warehouse locations.
-   Support parent-child warehouse location relationships.
-   Retrieve warehouse-specific inventory.

### 2.3 Inventory Management

-   Receive stock into a warehouse.
-   Transfer stock between warehouses.
-   Maintain warehouse-level stock balances.
-   Record inventory movements.
-   Track receiving and transfer references.
-   Identify low-stock items.
-   Protect stock balances through database transactions and row-level locking.

### 2.4 Stock Movement History

-   View inventory movement records.
-   Filter movements by product.
-   Filter movements by warehouse.
-   Filter by movement type.
-   Filter by date range.
-   View movement references and responsible users.

### 2.5 Dashboard

-   Total product count.
-   Active warehouse count.
-   Aggregate inventory quantity.
-   Low-stock item count.
-   Recent inventory movement history.

### 2.6 Authentication and Access Control

-   User authentication.
-   Role-based access control using Spatie Laravel Permission.
-   Permission-protected web routes.
-   Role-specific sidebar navigation.
-   Separate permissions for viewing, creating, editing, and deleting resources.
-   Protected warehouse inventory operations.

### 2.7 Notification Center

-   Store notifications in the database.
-   Display unread notification counts.
-   Display notification details in a dashboard dropdown.
-   View notification history.
-   Mark individual notifications as read.
-   Mark all notifications as read.
-   Broadcast notifications through Laravel Reverb.
-   Process notification jobs asynchronously through Redis and Horizon.

---

## 3. System Architecture

The application follows a **modular monolith architecture**.

The system runs as one Laravel application while separating responsibilities into modules and services. This approach keeps business operations within a consistent deployment and database transaction boundary without introducing unnecessary microservice complexity.

### High-Level Architecture

```text
                  Web Browser / API Client
                            |
                            v
                          NGINX
                            |
                            v
                   Laravel Application
                            |
             +--------------+--------------+
             |              |              |
          Web Layer      API Layer     Authentication
          (Blade)        (REST API)      (Sanctum)
             |              |              |
             +--------------+--------------+
                            |
                            v
                   Application Services
                            |
       +--------------------+--------------------+
       |          |          |          |         |
     Catalog   Warehouse  Inventory   RBAC    Notification
     Module     Module     Module     Module    Module
                            |
                            v
                       Eloquent ORM
                            |
                            v
                         MySQL 8

       Background Processing and Real-Time Layer
                            |
              +-------------+-------------+
              |             |             |
            Redis        Horizon        Reverb
            Queue        Workers      WebSockets
              |             |             |
              +-------------+-------------+
                            |
                       Laravel Echo
                            |
                       Web Browser
```

### Architectural Principles

-   Separation of HTTP responsibilities and business logic.
-   Centralized inventory business operations.
-   Database transactions for stock-changing operations.
-   Database constraints for data integrity.
-   Authentication and authorization boundaries.
-   Versioned REST API design.
-   Asynchronous processing for queued notifications.
-   Real-time communication through private broadcast channels.
-   Permission-based access to application modules.
-   Incremental modular development.

---

## 4. Project Structure

The following structure represents the main application areas. Individual files and modules may evolve during development.

```text
retail-wms/
├── app/
│   ├── Events/
│   │   ├── InventoryUpdated.php
│   │   └── RealtimeTestNotification.php
│   │
│   ├── Http/
│   │   └── Controllers/
│   │       ├── Api/
│   │       ├── AuthController.php
│   │       ├── DashboardController.php
│   │       ├── NotificationController.php
│   │       ├── ProductController.php
│   │       ├── ProductCategoryController.php
│   │       ├── WarehouseController.php
│   │       ├── WarehouseLocationController.php
│   │       ├── StockReceivingController.php
│   │       ├── StockTransferController.php
│   │       └── StockMovementController.php
│   │
│   ├── Jobs/
│   ├── Models/
│   │   ├── User.php
│   │   ├── Product.php
│   │   ├── ProductCategory.php
│   │   ├── Warehouse.php
│   │   ├── WarehouseLocation.php
│   │   ├── WarehouseStock.php
│   │   └── InventoryMovement.php
│   │
│   ├── Notifications/
│   │   └── WmsNotification.php
│   │
│   ├── Services/
│   │   ├── InventoryService.php
│   │   └── NotificationService.php
│   │
│   └── Providers/
│
├── bootstrap/
├── config/
│   ├── horizon.php
│   └── permission.php
│
├── database/
│   ├── migrations/
│   └── seeders/
│       └── RolesAndPermissionsSeeder.php
│
├── deploy/
├── docker/
│   └── nginx/
│
├── docs/
│   └── architecture/
│
├── public/
│   ├── css/
│   └── build/
│
├── resources/
│   ├── js/
│   ├── css/
│   └── views/
│       ├── dashboard/
│       ├── products/
│       ├── categories/
│       ├── warehouses/
│       ├── inventory/
│       ├── stock-receiving/
│       ├── stock-transfers/
│       ├── stock-movements/
│       └── notifications/
│
├── routes/
│   ├── api.php
│   ├── web.php
│   └── channels.php
│
├── tests/
├── Dockerfile
├── docker-compose.yml
├── composer.json
├── composer.lock
└── README.md
```

---

## 5. Database Design

The database foundation includes the following application entities and supporting Laravel infrastructure tables.

| Table                  | Responsibility                                |
| ---------------------- | --------------------------------------------- |
| users                  | Application users                             |
| products               | Product and SKU information                   |
| product_categories     | Product classification                        |
| warehouses             | Warehouse information                         |
| warehouse_locations    | Warehouse location hierarchy                  |
| warehouse_stocks       | Current product quantity per warehouse        |
| inventory_movements    | Inventory movement history                    |
| notifications          | User notification records                     |
| roles                  | Application roles                             |
| permissions            | Application permissions                       |
| model_has_roles        | User-role assignments                         |
| model_has_permissions  | Direct model permission assignments           |
| role_has_permissions   | Role-permission assignments                   |
| personal_access_tokens | Sanctum API tokens                            |
| jobs                   | Queued jobs, depending on queue configuration |
| failed_jobs            | Failed queue job records                      |
| cache                  | Cache storage, depending on configuration     |

Redis is configured as the application's cache and queue backend in the current Docker environment.

### Key Relationships

```text
ProductCategory
      |
      +------ Products
                  |
                  +------ WarehouseStock ------ Warehouse
                  |
                  +------ InventoryMovement -- Warehouse

Warehouse
    |
    +------ WarehouseLocation
                |
                +------ Child Locations

User
    |
    +------ InventoryMovement (created_by)
    |
    +------ Notifications
    |
    +------ Roles / Permissions
```

### Data Integrity

Important database integrity considerations include:

-   Unique product SKU values.
-   Unique warehouse/product stock combinations.
-   Foreign key relationships between inventory records.
-   Indexed lookup fields for common inventory queries.
-   Transactional updates for stock-changing operations.
-   User ownership checks for notification operations.
-   Referential integrity for role and permission relationships.

The database schema remains the final authority for enforcing uniqueness and referential integrity.

---

## 6. Inventory Transaction Design

Inventory operations are business-critical because incorrect stock balances can affect purchasing, sales, and warehouse operations.

### 6.1 Stock Receiving

The `InventoryService` centralizes the stock receiving workflow.

```text
Receive Stock
     |
     v
Validate Request
     |
     v
Begin Database Transaction
     |
     v
Find or Create Stock Record
     |
     v
Lock Stock Record
     |
     v
Increase Warehouse Quantity
     |
     v
Create Inventory Movement
     |
     v
Commit Transaction
     |
     v
Dispatch Inventory Update Event
```

### 6.2 Stock Transfer

Stock transfers update two warehouse balances and record corresponding movements.

```text
Transfer Request
      |
      v
Validate Source and Destination
      |
      v
Begin Database Transaction
      |
      v
Lock Affected Stock Rows
      |
      v
Check Available Quantity
      |
      v
Debit Source Warehouse
      |
      v
Credit Destination Warehouse
      |
      v
Record Transfer Out / Transfer In
      |
      v
Commit Transaction
      |
      v
Dispatch Inventory Update Events
```

### 6.3 Concurrency and Consistency

The inventory implementation uses database transactions, row-level locking, and transaction retries as consistency mechanisms.

The intended guarantees include:

-   Prevent negative inventory balances.
-   Prevent partial stock transfers.
-   Keep balance updates and movement records in the same transaction.
-   Protect stock rows against conflicting concurrent updates.
-   Maintain an auditable inventory history.
-   Dispatch inventory update events after successful transaction completion.

Production hardening should additionally cover idempotency, concurrent stock-row creation edge cases, reconciliation, and comprehensive concurrency tests.

---

## 7. Authentication and Role-Based Access Control

The application uses Laravel authentication and Spatie Laravel Permission to implement role-based authorization.

### Implemented Roles

| Role              | Responsibility                            |
| ----------------- | ----------------------------------------- |
| Super Admin       | Full application access                   |
| Admin             | Administrative application access         |
| Warehouse Manager | Warehouse and inventory operations        |
| Warehouse Staff   | Assigned inventory operations             |
| Sales Manager     | Customer and sales management permissions |
| Sales Staff       | Sales-related access                      |
| Accountant        | Financial and reporting-related access    |

### Permission Examples

```text
dashboard.view

products.view
products.create
products.edit
products.delete

categories.view
categories.create
categories.edit
categories.delete

warehouses.view
warehouses.create
warehouses.edit
warehouses.delete

warehouse-locations.manage

inventory.view
inventory.receive
inventory.transfer
inventory.adjust

stock-movements.view

suppliers.view
suppliers.manage

purchase-orders.view
purchase-orders.create
purchase-orders.approve

customers.view
customers.manage

sales-orders.view
sales-orders.create
sales-orders.manage

reports.view
reports.export

users.view
users.create
users.edit
users.delete

roles.manage
settings.manage
```

The permission list provides the authorization foundation for current and future modules. Some permissions are intended for business workflows that have not yet been implemented.

### Authorization Approach

-   Protect web routes with permission middleware.
-   Use Laravel's authorization integration with Spatie permissions.
-   Control sidebar navigation based on user permissions.
-   Separate viewing and modifying permissions.
-   Protect inventory receiving and transfer operations.
-   Authenticate API requests using Sanctum.

Seed the initial roles and permissions using:

```bash
php artisan db:seed --class=RolesAndPermissionsSeeder
```

---

## 8. REST API

Application API routes use the `/api/v1` prefix.

### Current API Endpoints

| Method | Endpoint                               | Description               |
| ------ | -------------------------------------- | ------------------------- |
| GET    | `/api/v1/products`                     | Retrieve products         |
| POST   | `/api/v1/products`                     | Create product            |
| GET    | `/api/v1/warehouses`                   | Retrieve warehouses       |
| GET    | `/api/v1/warehouses/{warehouse}/stock` | Retrieve warehouse stock  |
| POST   | `/api/v1/inventory/receive`            | Receive inventory         |
| POST   | `/api/v1/inventory/transfer`           | Transfer inventory        |
| GET    | `/api/v1/inventory/movements`          | Retrieve movement history |

### API Security

The current API routes use Laravel Sanctum authentication middleware.

Authentication and authorization are separate concerns. Production API access should additionally enforce appropriate permission checks and resource-level policies.

Inspect registered endpoints:

```bash
php artisan route:list --path=api
```

---

## 9. Redis, Horizon, and Background Processing

The application uses Redis for queue processing and cache storage.

Laravel Horizon provides a dashboard for monitoring and managing queued jobs.

### Current Queue Architecture

```text
Laravel Application
        |
        v
     Redis Queue
        |
        v
   Laravel Horizon
        |
        v
   Queue Workers
        |
        v
 Background Jobs
        |
        +------ Database Notifications
        |
        +------ Broadcast Notifications
```

### Benefits

-   Asynchronous notification processing.
-   Background job execution.
-   Queue monitoring through Horizon.
-   Visibility into job execution and failures.
-   Separation of web requests from background processing.

The Docker queue service runs Horizon rather than a separate `queue:work` process.

### Horizon Dashboard

Local development URL:

```text
http://localhost:8081/horizon
```

Check Horizon status:

```bash
php artisan horizon:status
```

Monitor queue logs:

```bash
docker compose logs -f queue
```

---

## 10. Real-Time Communication and Notifications

The application uses Laravel Reverb and Laravel Echo to support real-time communication between the Laravel backend and browser clients.

### Technology

-   Laravel Reverb for WebSocket broadcasting.
-   Laravel Echo for client-side event subscriptions.
-   Pusher JS as the WebSocket client.
-   Private broadcast channels for authenticated users.
-   Redis and Horizon for queued notification processing.

### Notification Architecture

```text
Application Event / Notification
             |
             v
      Notification Service
             |
             v
       WmsNotification
             |
             v
       Notification Queue
             |
             v
           Redis
             |
             v
          Horizon
             |
        +----+----+
        |         |
        v         v
     Database   Broadcast
     Channel    Channel
        |         |
        v         v
   Notifications Reverb
        Table      |
                   v
              Laravel Echo
                   |
                   v
             Browser UI
```

### Notification Capabilities

-   Send notifications to individual users.
-   Send notifications to multiple users.
-   Target users by role.
-   Target users by permission.
-   Store notification payloads in the database.
-   Broadcast notifications to authenticated users.
-   Display unread notification counts.
-   Mark individual notifications as read.
-   Mark all notifications as read.
-   View notification history.

### Notification Categories

The notification infrastructure supports categories such as:

-   System notifications.
-   Inventory notifications.
-   Warehouse notifications.
-   Purchase order notifications.
-   Sales order notifications.

The infrastructure is in place; integration with future purchase order and sales order workflows will be implemented when those modules are developed.

### Private Broadcast Channels

Examples include:

```text
App.Models.User.{id}
warehouses.{warehouseId}.inventory
```

Private channel authorization is defined in:

```text
routes/channels.php
```

### Local Reverb Configuration

The local Docker environment exposes Reverb through:

```text
http://localhost:8082
```

Laravel communicates with the Reverb service through the internal Docker network.

Do not commit actual Reverb credentials or production secrets to Git.

---

## 11. Local Development with Docker

### Prerequisites

-   macOS, Linux, or Windows with a compatible Docker environment.
-   Docker Desktop or Docker Engine.
-   Docker Compose.
-   Git.
-   Node.js and npm for frontend asset development.

### Clone Repository

```bash
git clone https://github.com/MohdAdamSiThuThetNaing/retail-wms.git

cd retail-wms
```

### Configure Environment

Create the local environment file:

```bash
cp .env.example .env
```

Configure the database connection using the actual values from your Docker Compose configuration.

Example:

```dotenv
APP_NAME="Retail WMS"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8081

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=retail_wms
DB_USERNAME=retail_user
DB_PASSWORD=your_local_database_password

CACHE_STORE=redis
QUEUE_CONNECTION=redis
REDIS_CLIENT=phpredis
REDIS_HOST=redis
REDIS_PORT=6379

BROADCAST_CONNECTION=reverb
```

Configure the Reverb application credentials and Vite WebSocket settings according to your local environment. Keep secrets out of source control.

### Build and Start Services

```bash
docker compose up -d --build
```

Check running containers:

```bash
docker compose ps
```

### Install PHP Dependencies

```bash
docker compose exec app composer install
```

### Generate Application Key

```bash
docker compose exec app php artisan key:generate
```

### Run Database Migrations

```bash
docker compose exec app php artisan migrate
```

### Seed Initial Roles and Permissions

```bash
docker compose exec app php artisan db:seed --class=RolesAndPermissionsSeeder
```

Run other seeders only when their data is appropriate for your environment.

### Build Frontend Assets

```bash
npm install

npm run build
```

### Clear Laravel Cache

```bash
docker compose exec app php artisan optimize:clear
```

### Check Queue Processing

```bash
docker compose exec app php artisan horizon:status
```

### Access Local Services

| Service          | URL                           |
| ---------------- | ----------------------------- |
| WMS Application  | http://localhost:8081         |
| Laravel Horizon  | http://localhost:8081/horizon |
| Reverb WebSocket | http://localhost:8082         |

The URLs assume the host port mappings defined in the current Docker Compose configuration.

---

## 12. Testing

Run the Laravel test suite:

```bash
docker compose exec app php artisan test
```

Recommended critical test scenarios:

### Product and Warehouse

-   Product creation and SKU uniqueness.
-   Product category relationships.
-   Warehouse creation and validation.
-   Warehouse location hierarchy.

### Inventory

-   Warehouse stock receiving.
-   Successful stock transfer.
-   Insufficient stock rejection.
-   Transaction rollback after failed operations.
-   Inventory movement creation.
-   Concurrent stock updates.
-   Inventory balance reconciliation.

### Authentication and Authorization

-   Unauthenticated API access.
-   Authenticated API access.
-   Role and permission assignment.
-   Unauthorized web route access.
-   Inventory permission enforcement.
-   Private broadcast channel authorization.

### Notifications and Queue Processing

-   Database notification creation.
-   Notification broadcasting.
-   Unread notification count.
-   Mark individual notification as read.
-   Mark all notifications as read.
-   Queue job failure and retry behavior.

Comprehensive automated coverage of these scenarios remains an important development priority.

---

## 13. Security Considerations

-   Keep application secrets in `.env`.
-   Never commit production credentials or access tokens.
-   Use Sanctum for API authentication.
-   Apply role-based authorization to protected operations.
-   Validate external request data.
-   Enforce warehouse-level access policies.
-   Use least-privilege database accounts.
-   Disable debug mode in production.
-   Enforce HTTPS.
-   Apply rate limiting to sensitive endpoints.
-   Protect private WebSocket channels.
-   Monitor authentication failures and privileged operations.
-   Secure the Horizon dashboard.
-   Back up databases and test recovery procedures.

---

## 14. Deployment Architecture

The intended production deployment architecture:

```text
                 Internet
                    |
                    v
                Domain / DNS
                    |
                    v
                 HTTPS/TLS
                    |
                    v
                  Nginx
                    |
                    v
                 PHP-FPM
                    |
                    v
               Laravel 12
                    |
          +---------+---------+
          |         |         |
          v         v         v
        MySQL     Redis     Reverb
                    |
                    v
                  Horizon
                    |
                    v
               Queue Workers
```

Production deployment responsibilities include:

1. Ubuntu LTS server provisioning.
2. Secure SSH access and firewall configuration.
3. PHP-FPM and required PHP extensions.
4. Composer dependency installation.
5. Nginx configuration.
6. Environment and secret management.
7. Database migrations.
8. Redis and Horizon configuration.
9. Reverb and WebSocket configuration.
10. TLS certificate configuration.
11. Application logs and monitoring.
12. Database backups and recovery procedures.

Typical Laravel production commands:

```bash
composer install --no-dev --optimize-autoloader

php artisan migrate --force

php artisan config:cache

php artisan route:cache

php artisan view:cache
```

These are deployment instructions, not a claim that production infrastructure has already been configured.

---

## 15. Engineering Roadmap

| Priority | Enhancement                                             | Status      |
| -------- | ------------------------------------------------------- | ----------- |
| P1       | Laravel application and Docker foundation               | Implemented |
| P1       | Product and category management foundation              | Implemented |
| P1       | Warehouse and location management foundation            | Implemented |
| P1       | Inventory receiving and transfer foundation             | Implemented |
| P1       | Inventory movement history                              | Implemented |
| P1       | Sanctum-protected API routes                            | Implemented |
| P1       | Initial Blade dashboard                                 | Implemented |
| P1       | Role-based access control foundation                    | Implemented |
| P1       | Redis-backed queues and Laravel Horizon                 | Implemented |
| P1       | Reverb real-time broadcasting foundation                | Implemented |
| P1       | Database and broadcast notification infrastructure      | Implemented |
| P2       | Comprehensive inventory feature tests                   | Planned     |
| P2       | API Resources and consistent error contracts            | Planned     |
| P2       | Advanced warehouse-level authorization policies         | Planned     |
| P2       | Stock adjustment and inventory reconciliation workflows | Planned     |
| P2       | Supplier management                                     | Planned     |
| P3       | Purchase order and approval workflow                    | Planned     |
| P3       | Sales order management                                  | Planned     |
| P3       | Stock reservation for sales orders                      | Planned     |
| P3       | Idempotency keys for inventory operations               | Planned     |
| P3       | Outbox pattern for reliable event delivery              | Planned     |
| P4       | CI/CD pipeline and automated deployment                 | Planned     |
| P4       | Monitoring, alerting, and reconciliation                | Planned     |

---

## 16. Engineering Decisions

### Why a Modular Monolith?

A modular monolith offers a single deployment and a shared relational transaction boundary. This keeps inventory operations straightforward while allowing the codebase to develop clear domain boundaries.

### Why MySQL Transactions?

Stock receiving and transfers involve multiple related writes. Transactions ensure that balance changes and movement records either commit together or roll back together.

### Why Row-Level Locking?

Inventory operations can be requested concurrently. Row-level locking helps prevent conflicting updates from causing incorrect stock balances.

### Why an Inventory Movement History?

A current stock balance alone does not explain how inventory changed. Movement records provide an audit trail for receiving and transferring inventory.

### Why Redis and Horizon?

Redis provides a queue backend, while Horizon provides visibility into queued jobs and worker execution. This allows notification processing and other background tasks to run independently of the web request lifecycle.

### Why Laravel Reverb?

Reverb enables real-time communication through WebSocket connections. Combined with Laravel Echo, it allows the browser to receive notifications and inventory events without continuously polling the application.

### Why Role-Based Access Control?

Different business users require different levels of access. RBAC provides a structured foundation for separating administrative, warehouse, sales, and accounting responsibilities.

### Why API Versioning?

The `/api/v1` prefix establishes a stable API boundary for future changes and external integrations.

---

## 17. Interview Demonstration

A recommended technical walkthrough:

1. Explain the retail and warehouse management business domain.
2. Present the modular monolith architecture.
3. Explain database relationships and constraints.
4. Demonstrate product and category management.
5. Demonstrate warehouse and location management.
6. Receive inventory into a warehouse.
7. Transfer inventory between warehouses.
8. Demonstrate insufficient stock validation.
9. Inspect inventory movement history.
10. Explain transactions and row-level locking.
11. Demonstrate authentication and role-based permissions.
12. Explain Sanctum API authentication.
13. Demonstrate the notification center.
14. Explain Redis, Horizon, Reverb, and Laravel Echo.
15. Discuss automated testing, deployment, security, and future improvements.

---

## 18. Project Philosophy

This project focuses on engineering correctness, maintainability, data integrity, security, and operational reliability.

The objective is not simply to build CRUD screens, but to demonstrate how a business-critical retail and inventory application can be designed, implemented, tested, and prepared for production.

**Built with Laravel 12, PHP 8.4, MySQL, Redis, Laravel Horizon, Reverb, and Docker.**
