# Retail WMS — Enterprise Retail & Warehouse Management System

A modular, web-based Retail and Warehouse Management System built with **Laravel 12**, designed to manage products, inventory, warehouse operations, procurement, sales, customers, and user access within a centralized platform.

The system provides a structured foundation for multi-warehouse inventory control, operational traceability, purchasing and sales workflows, and role-based access control.

---

## 1. Project Overview

Retail WMS centralizes core retail and warehouse operations into a single application.

It is designed around several key principles:

-   **Modular architecture:** Business capabilities are separated into focused modules with clear responsibilities.
-   **Inventory integrity:** Stock changes are handled through controlled inventory operations rather than arbitrary quantity updates.
-   **Operational traceability:** Stock movements and business document status changes provide an auditable operational history.
-   **Access control:** Users receive permissions through role-based authorization.
-   **Transactional consistency:** Related business operations are grouped into database transactions where appropriate.
-   **Extensibility:** The application can evolve to support additional warehouses, integrations, reporting, and automation.

### Core capabilities

| Domain               | Capabilities                                                       |
| -------------------- | ------------------------------------------------------------------ |
| Dashboard            | Operational overview and business metrics                          |
| Product Management   | Products and product categories                                    |
| Warehouse Management | Warehouses and warehouse locations                                 |
| Inventory Management | Stock visibility, receiving, transfers, adjustments, and movements |
| Supplier Management  | Supplier records and management                                    |
| Procurement          | Purchase orders, approval, and receiving                           |
| Customer Management  | Customer records and account status                                |
| Sales Management     | Sales orders and order lifecycle                                   |
| User Management      | User accounts, roles, and activation status                        |
| Authorization        | Role-based access control and granular permissions                 |
| Notifications        | Application notifications and realtime communication               |

---

## 2. Technology Stack

| Layer                  | Technology                             |
| ---------------------- | -------------------------------------- |
| Backend Framework      | Laravel 12                             |
| Language               | PHP 8.4                                |
| Database               | MySQL 8                                |
| ORM                    | Laravel Eloquent                       |
| Authentication         | Laravel Authentication / Sanctum       |
| Authorization          | Spatie Laravel Permission              |
| Frontend               | Blade Templates, HTML, CSS, JavaScript |
| Frontend Build         | Vite                                   |
| Styling                | Custom CSS, Tailwind CSS               |
| Realtime Communication | Laravel Reverb                         |
| Queue Processing       | Redis and Laravel Horizon              |
| Web Server             | Nginx                                  |
| Application Runtime    | PHP-FPM                                |
| Containerization       | Docker and Docker Compose              |
| Version Control        | Git                                    |

---

## 3. High-Level Architecture

The application follows a layered Laravel architecture. HTTP requests are handled by routes and middleware, delegated to controllers, and processed through application models and domain services.

```text
                         CLIENT / BROWSER
                                |
                                v
                         NGINX WEB SERVER
                                |
                                v
                         LARAVEL APPLICATION
                                |
                +---------------+---------------+
                |               |               |
                v               v               v
             ROUTES         MIDDLEWARE      AUTHENTICATION
                |               |               |
                +---------------+---------------+
                                |
                                v
                           CONTROLLERS
                                |
                   +------------+------------+
                   |                         |
                   v                         v
              DOMAIN SERVICES              MODELS
                   |                         |
                   +------------+------------+
                                |
                                v
                          MYSQL DATABASE
                                |
              +-----------------+-----------------+
              |                 |                 |
              v                 v                 v
          REDIS QUEUES     EVENT BROADCASTING   APPLICATION DATA
              |                 |
              v                 v
          HORIZON             REVERB
```

### Architectural responsibilities

**Presentation Layer**

-   Blade views render the application interface.
-   Custom CSS provides module-specific styling.
-   JavaScript supports client-side interactions.
-   Vite manages frontend asset compilation.

**HTTP and Application Layer**

-   Routes define application endpoints.
-   Middleware handles authentication, authorization, and request-level controls.
-   Controllers coordinate incoming requests and application operations.
-   Form validation protects application inputs.

**Business and Domain Layer**

-   Models represent business entities and their relationships.
-   Domain services encapsulate reusable business operations.
-   Database transactions protect operations that involve multiple related records.

**Infrastructure Layer**

-   MySQL persists operational and transactional data.
-   Redis supports queue processing and related infrastructure needs.
-   Laravel Horizon supervises queue workers.
-   Laravel Reverb provides realtime broadcasting.
-   Docker Compose coordinates the application services.

---

## 4. Application Modules

### 4.1 Product Management

Responsible for maintaining the product catalog.

Capabilities:

-   Create, view, update, and delete products.
-   Organize products into categories.
-   Maintain product information used by inventory, purchasing, and sales workflows.

### 4.2 Warehouse Management

Manages warehouse-level organization.

Capabilities:

-   Create and manage warehouses.
-   Manage warehouse locations.
-   Associate inventory with warehouse records.

### 4.3 Inventory Management

Provides centralized visibility and control over stock.

Capabilities:

-   View inventory balances.
-   Receive stock.
-   Transfer stock between warehouses.
-   Record inventory adjustments.
-   Review stock movement history.

**Inventory integrity principle:** Stock changes should be performed through controlled inventory operations. Business workflows should not directly modify stock quantities without applying the corresponding inventory rules and recording the movement.

### 4.4 Supplier Management

Maintains supplier information used by procurement operations.

Capabilities:

-   Create and maintain supplier records.
-   Search and view supplier information.
-   Manage supplier account status.

### 4.5 Purchase Order Management

Supports purchasing workflows from order creation through receiving.

Capabilities:

-   Create and manage purchase orders.
-   Maintain purchase order items.
-   Support approval workflows.
-   Receive purchased goods.
-   Update inventory through the receiving process.

### 4.6 Customer Management

Maintains customer records for sales operations.

Capabilities:

-   Create and manage customer records.
-   Search and view customers.
-   Manage active and inactive customer accounts.

### 4.7 Sales Order Management

Manages customer sales orders and their lifecycle.

Capabilities:

-   Create sales orders.
-   Maintain order items and pricing information.
-   View and update order details.
-   Confirm and process orders.
-   Complete or cancel orders.
-   Record order status history.

Inventory is issued through the inventory service when an order is completed. The current implementation checks stock availability at completion rather than reserving stock at confirmation.

### 4.8 User Management

Provides administrative control over application accounts.

Capabilities:

-   Create and update user accounts.
-   Assign roles.
-   Activate and deactivate accounts.
-   View user details and effective permissions.

Inactive accounts are prevented from authenticating, and active-session middleware checks account status during web requests.

### 4.9 Notifications and Realtime Communication

The application includes a notification center and realtime infrastructure.

-   Laravel notifications provide application-level notification functionality.
-   Laravel Reverb supports WebSocket broadcasting.
-   Redis and Laravel Horizon support background queue processing.

---

## 5. Inventory and Transaction Design

Inventory is a central domain of the system. Stock balances and movement history must remain consistent.

### Core inventory concepts

-   **Product:** The item being tracked.
-   **Warehouse:** The physical or logical stock-holding facility.
-   **Warehouse Stock:** The current quantity of a product at a warehouse, together with its reorder threshold.
-   **Inventory Movement:** A record of a stock operation, such as receiving, transferring, or issuing stock.

### Inventory operation flow

```text
Business Operation
       |
       v
Inventory Service
       |
       v
Database Transaction
       |
       +---- Update Warehouse Stock
       |
       +---- Create Inventory Movement
       |
       +---- Commit Transaction
       |
       v
Broadcast / Notification
```

The inventory service is the controlled entry point for stock operations. Related stock and movement changes should be committed together so that an unsuccessful operation does not leave partially updated inventory records.

### Current operational behavior

-   Receiving increases stock.
-   Transfers move stock between warehouse balances.
-   Issuing decreases stock.
-   Inventory movement records provide an operational history.
-   Sales order completion invokes the inventory issuing workflow.

---

## 6. Authentication and Authorization

The application uses Laravel authentication and Spatie Laravel Permission for role-based access control (RBAC).

### Authorization model

```text
USER
 |
 +---- Assigned Roles
          |
          +---- Role Permissions
                     |
                     +---- Application Access
```

A role groups related permissions, while individual permissions control specific application actions.

Examples:

| Permission                | Description                  |
| ------------------------- | ---------------------------- |
| `dashboard.view`          | View the dashboard           |
| `products.view`           | View products                |
| `products.create`         | Create products              |
| `products.edit`           | Update products              |
| `inventory.view`          | View inventory               |
| `inventory.receive`       | Receive stock                |
| `inventory.transfer`      | Transfer stock               |
| `purchase-orders.view`    | View purchase orders         |
| `purchase-orders.approve` | Approve purchase orders      |
| `sales-orders.view`       | View sales orders            |
| `sales-orders.create`     | Create sales orders          |
| `users.view`              | View users                   |
| `users.create`            | Create users                 |
| `roles.manage`            | Manage roles and permissions |

### Seeded roles

The current authorization seeder defines seven roles:

-   Super Admin
-   Admin
-   Warehouse Manager
-   Warehouse Staff
-   Sales Manager
-   Sales Staff
-   Accountant

The role and permission definitions are maintained in `RolesAndPermissionsSeeder`.

**Security considerations:**

-   Route authorization should be enforced on the server, not only through hidden menu items.
-   Privileged role assignment should be restricted to authorized administrators.
-   The last active Super Admin account must be protected from accidental removal or deactivation.
-   Deactivated users must not retain access through existing web sessions.

The role and permission management interface is a separate development milestone; the underlying Spatie authorization foundation and seeded roles and permissions are already established.

---

## 7. Database and Data Management

MySQL is the primary persistence layer. Laravel Eloquent manages entity relationships and database interactions.

The schema is managed through Laravel migrations.

Key domain entities include:

-   Users
-   Roles and Permissions
-   Products
-   Categories
-   Warehouses
-   Warehouse Locations
-   Warehouse Stocks
-   Inventory Movements
-   Suppliers
-   Purchase Orders
-   Purchase Order Items
-   Customers
-   Sales Orders
-   Sales Order Items
-   Sales Order Status Histories

### Database design principles

-   Use foreign keys to maintain referential integrity.
-   Use indexes for frequently filtered and searched fields.
-   Use database transactions for multi-record business operations.
-   Keep historical operational records wherever required for traceability.
-   Avoid destructive schema changes that could compromise operational data.

Migrations should be applied incrementally using Laravel's migration commands.

---

## 8. Docker Infrastructure

The application runs in a Docker Compose environment.

| Service  | Responsibility                         |
| -------- | -------------------------------------- |
| `app`    | Laravel application running on PHP-FPM |
| `nginx`  | Web server and HTTP entry point        |
| `mysql`  | Relational database                    |
| `redis`  | Queue and cache infrastructure         |
| `queue`  | Laravel Horizon queue supervision      |
| `reverb` | Realtime WebSocket server              |

### Local service endpoints

| Service     | Local endpoint        |
| ----------- | --------------------- |
| Application | http://localhost:8081 |
| Reverb      | http://localhost:8082 |
| MySQL       | localhost:3307        |

The application connects to infrastructure services through the Docker Compose network. Internal service hostnames and ports should be used for container-to-container communication.

---

## 9. Installation and Local Development

### Prerequisites

-   macOS, Linux, or Windows with Docker support.
-   Docker Desktop or Docker Engine with Docker Compose.
-   Git.
-   Node.js and npm for local frontend asset compilation.

PHP and MySQL can run inside Docker; local PHP installation is not required for the documented Docker workflow.

### Step 1 — Clone the repository

```bash
git clone <repository-url>
cd retail-wms
```

### Step 2 — Configure environment

Create the environment file:

```bash
cp .env.example .env
```

Configure the required application and database variables. Use the service names defined in Docker Compose for internal database and Redis connections.

Do not commit `.env` or production credentials to Git.

### Step 3 — Build and start services

```bash
docker compose up -d --build
```

### Step 4 — Install backend dependencies

```bash
docker compose exec app composer install
```

### Step 5 — Generate application key

```bash
docker compose exec app php artisan key:generate
```

### Step 6 — Run database migrations

```bash
docker compose exec app php artisan migrate
```

### Step 7 — Seed roles and permissions

```bash
docker compose exec app php artisan db:seed --class=RolesAndPermissionsSeeder
```

Run this seeder according to the deployment's database initialization procedure. It should be safe to rerun only if its implementation remains idempotent.

### Step 8 — Install and build frontend assets

```bash
npm install
npm run build
```

### Step 9 — Clear application caches

```bash
docker compose exec app php artisan optimize:clear
```

### Step 10 — Access the application

Open:

http://localhost:8081

---

## 10. Common Development Commands

### Docker services

```bash
docker compose ps
docker compose logs -f
docker compose logs -f app
docker compose logs -f nginx
docker compose logs -f queue
docker compose logs -f reverb
```

Start and stop services:

```bash
docker compose up -d
docker compose stop
```

### Laravel commands

```bash
docker compose exec app php artisan about
docker compose exec app php artisan route:list
docker compose exec app php artisan migrate:status
docker compose exec app php artisan optimize:clear
```

### Queue management

```bash
docker compose exec app php artisan horizon:status
```

### Frontend

```bash
npm run build
```

### PHP syntax validation

```bash
docker compose exec app php -l app/Http/Controllers/ExampleController.php
```

### Git workflow

```bash
git status
git add <specific-files>
git diff --cached
git commit -m "feat: describe the change"
git push origin main
```

Stage specific files rather than committing unrelated changes.

---

## 11. Project Structure

The following is a representative Laravel application structure. Exact module files may evolve as development continues.

```text
retail-wms/
│
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   ├── Middleware/
│   │   └── Requests/
│   │
│   ├── Models/
│   ├── Services/
│   └── Providers/
│
├── bootstrap/
│   └── app.php
│
├── config/
│
├── database/
│   ├── migrations/
│   ├── factories/
│   └── seeders/
│
├── public/
│   ├── css/
│   └── js/
│
├── resources/
│   ├── css/
│   ├── js/
│   └── views/
│       ├── layouts/
│       ├── products/
│       ├── inventory/
│       ├── suppliers/
│       ├── purchase-orders/
│       ├── customers/
│       ├── sales-orders/
│       ├── users/
│       └── notifications/
│
├── routes/
│   ├── web.php
│   ├── api.php
│   ├── channels.php
│   └── console.php
│
├── tests/
│
├── docker-compose.yml
├── Dockerfile
├── package.json
├── composer.json
├── vite.config.js
└── README.md
```

---

## 12. Security and Operational Practices

The following practices should be followed during development and deployment:

1. Keep secrets and environment-specific credentials outside version control.
2. Disable debug mode in production.
3. Use HTTPS for production application traffic.
4. Enforce authorization on backend routes and operations.
5. Validate all user-supplied input.
6. Protect inventory operations with database transactions and appropriate concurrency controls.
7. Restrict access to privileged roles and administrative functions.
8. Monitor queue workers and realtime services.
9. Back up the database and test restoration procedures.
10. Apply migrations through controlled deployment processes.
11. Avoid destructive database commands against environments containing operational data.
12. Rotate credentials if they have been exposed.

---

## 13. Implementation Status and Roadmap

### Current implementation

-   Laravel 12 application and Docker infrastructure.
-   Authentication and account activation controls.
-   Product and category management.
-   Warehouse and location management.
-   Inventory operations and movement tracking.
-   Supplier management.
-   Purchase order workflows and receiving.
-   Customer management.
-   Sales order lifecycle.
-   User management and account status.
-   Spatie roles and permission foundation.
-   Notification and realtime infrastructure.

### Recommended next milestones

| Priority | Area                   | Objective                                                                       |
| -------- | ---------------------- | ------------------------------------------------------------------------------- |
| High     | Roles & Permissions UI | Role creation, editing, permission assignment, and protected system roles       |
| High     | Automated Testing      | Unit, feature, authorization, and inventory transaction tests                   |
| High     | Inventory Concurrency  | Prevent race conditions and overselling during simultaneous stock operations    |
| High     | Sales Order Controls   | Server-side pricing validation, stock reservation strategy, and credit controls |
| Medium   | Reporting              | Inventory valuation, stock movement, purchasing, and sales reports              |
| Medium   | Audit Logging          | Track sensitive account, permission, and business-operation changes             |
| Medium   | Notifications          | Expand event-driven operational notifications                                   |
| Future   | Integrations           | External accounting, ERP, e-commerce, and shipping integrations                 |

---

## 14. Engineering Principles

The project should continue to follow these engineering standards:

-   Keep controllers focused on HTTP coordination.
-   Move reusable business rules into services or dedicated domain components.
-   Use Form Requests or equivalent validation boundaries for complex input.
-   Use transactions for operations that modify related business records.
-   Protect stock balances against concurrent updates.
-   Keep authorization checks close to the protected operation.
-   Maintain backward-compatible migrations where practical.
-   Write automated tests for critical business rules.
-   Keep operational logs and audit history meaningful.
-   Prefer explicit, maintainable code over unnecessary abstraction.

---
