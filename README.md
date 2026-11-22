# Retail WMS — Enterprise Retail & Warehouse Management System

A modular, web-based **Retail and Warehouse Management System** built with **Laravel 12**, designed to centralize product management, inventory control, warehouse operations, procurement, sales, customer management, and user access.

The system provides a foundation for multi-warehouse inventory management, operational traceability, role-based access control, and realtime notifications.

## 1. Project Overview

Retail WMS brings core retail and warehouse operations together in one platform.

### Core Features

| Module          | Features                                               |
| --------------- | ------------------------------------------------------ |
| Dashboard       | Operational overview and business metrics              |
| Products        | Product and category management                        |
| Warehouses      | Warehouse and location management                      |
| Inventory       | Stock receiving, transfers, adjustments, and movements |
| Suppliers       | Supplier management                                    |
| Purchase Orders | Order creation, approval, and receiving                |
| Customers       | Customer records and account management                |
| Sales Orders    | Sales order lifecycle management                       |
| Users           | User accounts, roles, and activation                   |
| Notifications   | Database notifications and realtime updates            |

## 2. Technology Stack

| Layer           | Technology                       |
| --------------- | -------------------------------- |
| Backend         | Laravel 12, PHP 8.4              |
| Database        | MySQL 8                          |
| ORM             | Laravel Eloquent                 |
| Authentication  | Laravel Authentication / Sanctum |
| Authorization   | Spatie Laravel Permission        |
| Frontend        | Blade, HTML, CSS, JavaScript     |
| Build Tool      | Vite                             |
| Realtime        | Laravel Reverb, Laravel Echo     |
| Queue           | Redis, Laravel Horizon           |
| Web Server      | Nginx                            |
| Runtime         | PHP-FPM                          |
| Infrastructure  | Docker, Docker Compose           |
| Version Control | Git                              |

## 3. System Architecture

The application follows a layered Laravel architecture.

```text
CLIENT / BROWSER
       |
       v
NGINX WEB SERVER
       |
       v
LARAVEL APPLICATION
       |
       +---- Routes & Middleware
       |
       +---- Controllers
       |
       +---- Models & Services
       |
       v
   MYSQL DATABASE
       |
       +---- Redis / Horizon
       |
       +---- Laravel Reverb
                    |
                    v
               Laravel Echo
                    |
                    v
               Browser UI
```

### Main Components

-   **Controllers:** Handle HTTP requests and coordinate application workflows.
-   **Models:** Represent business entities and database relationships.
-   **Services:** Encapsulate reusable business logic.
-   **Middleware:** Handles authentication and access control.
-   **MySQL:** Stores application and transactional data.
-   **Redis & Horizon:** Support background job processing.
-   **Reverb & Echo:** Provide realtime communication.

## 4. Main Business Modules

### Product and Warehouse Management

-   Manage products and categories.
-   Create and maintain warehouses.
-   Manage warehouse locations.

### Inventory Management

-   View warehouse stock balances.
-   Receive stock.
-   Transfer stock between warehouses.
-   Perform inventory adjustments.
-   Track inventory movements.

Stock changes are processed through controlled inventory operations to maintain consistency between stock balances and movement records.

### Procurement

-   Create and manage purchase orders.
-   Submit orders for approval.
-   Approve or reject purchase orders.
-   Receive purchased goods.
-   Update inventory through the receiving workflow.

### Sales Management

-   Manage customers and sales orders.
-   Confirm and process orders.
-   Complete or cancel orders.
-   Maintain order status history.

Sales order completion invokes the inventory issuing workflow. Stock is currently checked at completion rather than reserved at confirmation.

### User Management

-   Create and update user accounts.
-   Assign roles.
-   Activate and deactivate accounts.
-   View user details and permissions.
-   Protect privileged administrative accounts.

## 5. Authentication and Authorization

Retail WMS uses Laravel authentication and Spatie Laravel Permission for role-based access control (RBAC).

### Seeded Roles

1. Super Admin
2. Admin
3. Warehouse Manager
4. Warehouse Staff
5. Sales Manager
6. Sales Staff
7. Accountant

Permissions control access to individual application operations, including:

-   `products.view`
-   `products.create`
-   `inventory.view`
-   `inventory.receive`
-   `inventory.transfer`
-   `purchase-orders.approve`
-   `sales-orders.create`
-   `users.manage`

The authorization foundation and seeded roles are implemented. A dedicated role and permission management interface is a planned enhancement.

## 6. Notifications and Realtime Communication

The application uses Laravel Notifications, Laravel Reverb, Laravel Echo, Redis, and Laravel Horizon.

### Architecture

```text
Business Operation
       |
       v
NotificationService
       |
       v
WmsNotification
       |
       v
Redis / Horizon
       |
       +---- Database Notification
       |
       +---- Reverb Broadcasting
                    |
                    v
               Laravel Echo
                    |
                    v
                Browser UI
```

### Current Integrations

| Event                            | Recipients                           |
| -------------------------------- | ------------------------------------ |
| Purchase order submitted         | Users with approval permission       |
| Purchase order approved/rejected | Purchase order creator               |
| Purchase order received          | Creator and eligible inventory users |
| Stock received                   | Eligible inventory users             |
| Stock transferred                | Eligible inventory users             |

The notification center supports notification history, unread counts, and read-status management.

Private channels provide user-specific notifications and warehouse inventory updates.

## 7. Database Design

The application uses MySQL with Laravel Eloquent and database migrations.

Key entities include:

-   Users, Roles, and Permissions
-   Products and Categories
-   Warehouses and Warehouse Locations
-   Warehouse Stocks and Inventory Movements
-   Suppliers and Purchase Orders
-   Customers and Sales Orders
-   Order Items and Status Histories
-   Notifications

Database transactions are used for related business operations. Inventory operations should also use appropriate locking and concurrency controls.

## 8. Docker Infrastructure

The application runs using Docker Compose.

| Service  | Responsibility       |
| -------- | -------------------- |
| `app`    | Laravel / PHP-FPM    |
| `nginx`  | Web server           |
| `mysql`  | Database             |
| `redis`  | Queue infrastructure |
| `queue`  | Laravel Horizon      |
| `reverb` | WebSocket server     |

### Local Endpoints

| Service     | Address               |
| ----------- | --------------------- |
| Application | http://localhost:8081 |
| Reverb      | http://localhost:8082 |
| MySQL       | `localhost:3307`      |

## 9. Installation and Setup

### Prerequisites

-   Docker Desktop or Docker Engine
-   Docker Compose
-   Git
-   Node.js and npm

### Step 1 — Clone Repository

```bash
git clone <repository-url>
cd retail-wms
```

### Step 2 — Configure Environment

```bash
cp .env.example .env
```

Configure the application, database, Redis, queue, and broadcasting environment variables.

### Step 3 — Start Docker Services

```bash
docker compose up -d --build
```

### Step 4 — Install Dependencies

```bash
docker compose exec app composer install
npm install
```

### Step 5 — Initialize Application

```bash
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
```

### Step 6 — Seed Roles and Permissions

```bash
docker compose exec app php artisan db:seed --class=RolesAndPermissionsSeeder
```

Run the seeder according to your database initialization procedure.

### Step 7 — Build Frontend

```bash
npm run build
```

### Step 8 — Access Application

Open http://localhost:8081

## 10. Common Development Commands

**Docker**

```bash
docker compose ps
docker compose logs -f
docker compose stop
docker compose up -d
```

**Laravel**

```bash
docker compose exec app php artisan route:list
docker compose exec app php artisan migrate:status
docker compose exec app php artisan optimize:clear
docker compose exec app php artisan horizon:status
```

**Frontend**

```bash
npm run build
```

## 11. Project Structure

```text
retail-wms/
├── app/
│   ├── Events/
│   ├── Http/
│   │   ├── Controllers/
│   │   └── Middleware/
│   ├── Models/
│   ├── Notifications/
│   └── Services/
├── bootstrap/
├── config/
├── database/
│   ├── migrations/
│   └── seeders/
├── public/
├── resources/
│   ├── js/
│   ├── css/
│   └── views/
├── routes/
│   ├── web.php
│   ├── api.php
│   └── channels.php
├── tests/
├── docker-compose.yml
├── Dockerfile
├── composer.json
├── package.json
└── README.md
```

## 12. Security and Best Practices

-   Keep environment variables and credentials out of version control.
-   Disable debug mode in production.
-   Use HTTPS for production environments.
-   Enforce server-side authorization.
-   Validate user input.
-   Protect inventory operations with database transactions.
-   Restrict privileged role assignments.
-   Monitor queue workers and failed jobs.
-   Back up the database regularly.
-   Apply migrations through controlled deployment procedures.

## 13. Development Roadmap

| Priority | Feature                | Objective                                |
| -------- | ---------------------- | ---------------------------------------- |
| High     | Roles & Permissions UI | Manage roles and permissions             |
| High     | Automated Testing      | Unit, feature, and integration tests     |
| High     | Inventory Concurrency  | Prevent stock conflicts and overselling  |
| High     | Sales Order Controls   | Pricing validation and stock reservation |
| Medium   | Reporting              | Inventory, purchasing, and sales reports |
| Medium   | Audit Logging          | Track sensitive business operations      |
| Medium   | Notifications          | Sales events and low-stock alerts        |
| Future   | External Integrations  | Accounting, ERP, and e-commerce systems  |

## 14. Engineering Principles

Retail WMS follows these development principles:

-   Modular and maintainable application architecture.
-   Reusable business services.
-   Transactional data integrity.
-   Centralized inventory operations.
-   Server-side authorization.
-   Secure user and permission management.
-   Realtime operational communication.
-   Incremental database migrations.
-   Automated testing for critical business workflows.

---

**Retail WMS — A centralized platform for retail operations, warehouse management, and inventory control.**
