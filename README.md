# Enterprise Retail & Warehouse Management System

A Laravel-based Warehouse Management System (WMS) designed to manage products, multiple warehouses, inventory balances, and stock movements through a web dashboard and versioned REST API.

This project demonstrates senior-level backend engineering practices, including modular application design, database integrity, transactional inventory operations, concurrency control, API authentication, automated testing, and containerized deployment.

> **Project status:** Active development. The current implementation includes the Laravel application foundation, MySQL persistence, inventory domain models and services, Sanctum-protected API endpoints, Docker-based local infrastructure, and an initial Blade dashboard. Features listed as planned are not represented as completed.

---

## 1. Technology Stack

| Category              | Technology                   |
| --------------------- | ---------------------------- |
| Backend               | PHP 8.4, Laravel 12          |
| Database              | MySQL 8                      |
| ORM                   | Eloquent ORM                 |
| Authentication        | Laravel Sanctum              |
| Frontend              | Blade, HTML, CSS             |
| Web Server            | Nginx                        |
| PHP Runtime           | PHP-FPM                      |
| Containerization      | Docker, Docker Compose       |
| Dependency Management | Composer                     |
| Testing               | PHPUnit / Laravel Test Suite |
| Version Control       | Git, GitHub                  |

## 2. Core Business Capabilities

### Product Management

-   Maintain product records and SKU identifiers.
-   Retrieve and paginate product listings.
-   Create products through the REST API.

### Warehouse Management

-   Maintain multiple warehouse locations.
-   Track active warehouse records.
-   Retrieve warehouse-specific inventory.

### Inventory Management

-   Receive stock into a warehouse.
-   Transfer stock between warehouses.
-   Maintain warehouse-level stock balances.
-   Track inventory movement history.
-   Identify products requiring replenishment.

### Dashboard

-   Total product count.
-   Active warehouse count.
-   Aggregate stock quantity.
-   Low-stock item count.
-   Recent inventory movement history.

## 3. System Architecture

The application follows a **modular monolith architecture**.

This approach keeps the system within one Laravel deployment while organizing business responsibilities into distinct modules. It provides clear separation of concerns without introducing unnecessary microservice complexity.

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
              +--------------+--------------+
              |              |              |
           Catalog        Warehouse       Inventory
           Management     Management      Management
                             |
                             v
                       Eloquent ORM
                             |
                             v
                         MySQL 8
              +--------------+--------------+
              |              |              |
           Products      Warehouses      Inventory
                                          Stocks
                                          Movements
```

### Architectural Principles

-   Separation of HTTP responsibilities and business logic.
-   Centralized inventory business operations.
-   Database transactions for stock-changing operations.
-   Database constraints to protect data integrity.
-   Authentication and authorization boundaries.
-   Versioned REST API design.
-   Automated testing of business-critical workflows.

## 4. Project Structure

```text
retail-wms/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── DashboardController.php
│   │   │   └── API/
│   │   └── Requests/
│   ├── Models/
│   │   ├── Product.php
│   │   ├── Warehouse.php
│   │   ├── WarehouseStock.php
│   │   └── InventoryMovement.php
│   └── Services/
│       └── InventoryService.php
│
├── bootstrap/
├── config/
├── database/
│   ├── migrations/
│   └── seeders/
│
├── deploy/
├── docker/
│   └── nginx/
│
├── docs/
│   └── architecture/
│
├── public/
├── resources/
│   └── views/
│       └── dashboard/
│
├── routes/
│   ├── api.php
│   └── web.php
│
├── tests/
├── Dockerfile
├── docker-compose.yml
├── composer.json
├── composer.lock
└── README.md
```

The structure above documents the current application areas and recommended documentation location. Additional domain and application layers can be introduced incrementally as the codebase grows.

## 5. Database Design

The current database foundation includes the following entities:

| Table                  | Responsibility                         |
| ---------------------- | -------------------------------------- |
| users                  | Application users                      |
| products               | Product and SKU information            |
| warehouses             | Warehouse information                  |
| warehouse_stocks       | Current product quantity per warehouse |
| inventory_movements    | Inventory movement history             |
| personal_access_tokens | Sanctum API tokens                     |
| cache                  | Database cache storage                 |
| jobs                   | Database queue storage                 |

### Key Relationships

```text
Product
   |
   +------ WarehouseStock ------ Warehouse
   |
   +------ InventoryMovement --- Warehouse

User
   |
   +------ InventoryMovement (created_by)
```

### Data Integrity

Important database integrity considerations include:

-   Unique product SKU values.
-   Unique warehouse/product stock combinations.
-   Foreign key relationships between inventory records.
-   Indexed lookup fields for common inventory queries.
-   Transactional updates for stock-changing operations.

The database schema should remain the final authority for enforcing uniqueness and referential integrity.

## 6. Inventory Transaction Design

Inventory operations are business-critical because incorrect stock balances can affect purchasing, sales, and warehouse operations.

### Stock Receiving

```text
Receive Stock
     |
     v
Validate Request
     |
     v
Begin Transaction
     |
     v
Find or Create Stock Record
     |
     v
Update Warehouse Quantity
     |
     v
Create Inventory Movement
     |
     v
Commit Transaction
```

### Stock Transfer

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
Record Inventory Movements
       |
       v
Commit Transaction
```

### Concurrency and Consistency

The inventory implementation uses database transactions and row-level locking as core consistency mechanisms.

The intended guarantees are:

-   Prevent negative inventory balances.
-   Prevent partial stock transfers.
-   Keep balance updates and movement records in the same transaction.
-   Protect stock rows against conflicting concurrent updates.
-   Maintain an auditable inventory history.

Production hardening should additionally cover concurrent stock-row creation, deadlock retry behavior, idempotency, and reconciliation tests.

## 7. REST API

All application API routes use the `/api/v1` prefix.

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

The current API routes are protected using Laravel Sanctum authentication middleware.

Authentication is not the same as authorization. Warehouse-level policies, role assignment, and permission management remain important production enhancements.

Inspect the actual registered endpoints with:

```bash
php artisan route:list --path=api
```

## 8. Local Development with Docker

### Prerequisites

-   Docker Desktop
-   Docker Compose
-   Git

### Start Services

```bash
git clone https://github.com/MohdAdamSiThuThetNaing/retail-wms.git

cd retail-wms

docker compose up -d --build
```

Check containers:

```bash
docker compose ps
```

### Install Dependencies

```bash
docker compose exec app composer install
```

### Configure Environment

Create your environment file:

```bash
cp .env.example .env
```

Configure the Docker database connection:

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
DB_PASSWORD=retail_password
```

Use your actual local Compose credentials if they differ from these examples.

### Generate Application Key

```bash
docker compose exec app php artisan key:generate
```

### Run Database Migrations

```bash
docker compose exec app php artisan migrate
```

### Seed Demo Data

```bash
docker compose exec app php artisan db:seed
```

### Clear Laravel Cache

```bash
docker compose exec app php artisan optimize:clear
```

### Access Application

```text
http://localhost:8081
```

The URL assumes the Nginx container maps host port `8081` to container port `80`.

## 9. Testing

Run the Laravel test suite:

```bash
docker compose exec app php artisan test
```

Recommended critical test scenarios:

-   Product creation and SKU uniqueness.
-   Warehouse stock receiving.
-   Successful stock transfer.
-   Insufficient stock rejection.
-   Transaction rollback after failed operations.
-   Inventory movement creation.
-   Unauthenticated API access.
-   Invalid request validation.
-   Concurrent stock updates.

Testing concurrent inventory operations and database-level integrity should be prioritized before production deployment.

## 10. Security Considerations

-   Keep application secrets in `.env`.
-   Never commit production credentials or access tokens.
-   Use Sanctum for API authentication.
-   Validate all external request data.
-   Implement policies for warehouse-level access.
-   Use least-privilege database accounts.
-   Disable debug mode in production.
-   Enforce HTTPS.
-   Apply rate limiting to sensitive endpoints.
-   Monitor authentication failures and privileged operations.

## 11. Deployment Architecture

The intended production deployment uses:

```text
                Internet
                   |
                   v
              Domain / DNS
                   |
                   v
              HTTPS / TLS
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
                   v
                MySQL 8
```

Production deployment responsibilities include:

1. Ubuntu LTS server provisioning.
2. Secure SSH access and firewall configuration.
3. PHP-FPM and required PHP extensions.
4. Composer dependency installation.
5. Nginx configuration.
6. Environment and secret management.
7. Database migrations.
8. TLS certificate configuration.
9. Application logs and monitoring.
10. Database backups and recovery procedures.

A production deployment should use:

```bash
composer install --no-dev --optimize-autoloader

php artisan migrate --force

php artisan config:cache

php artisan route:cache

php artisan view:cache
```

These commands are deployment steps, not a claim that production infrastructure has already been configured.

## 12. Engineering Roadmap

| Priority | Enhancement                                  | Status      |
| -------- | -------------------------------------------- | ----------- |
| P1       | Laravel application and Docker foundation    | Implemented |
| P1       | Product and warehouse data models            | Implemented |
| P1       | Inventory receive and transfer foundation    | Implemented |
| P1       | Sanctum-protected API routes                 | Implemented |
| P1       | Initial Blade dashboard                      | Implemented |
| P2       | Complete product and warehouse CRUD UI       | Planned     |
| P2       | Role-based access control and policies       | Planned     |
| P2       | API Resources and consistent error contracts | Planned     |
| P2       | Comprehensive inventory feature tests        | Planned     |
| P3       | Idempotency keys for inventory operations    | Planned     |
| P3       | Purchase order and approval workflow         | Planned     |
| P3       | Stock reservation for sales orders           | Planned     |
| P3       | Redis queues and asynchronous notifications  | Planned     |
| P3       | Outbox pattern for reliable event delivery   | Planned     |
| P4       | CI/CD pipeline and automated deployment      | Planned     |
| P4       | Monitoring, alerting, and reconciliation     | Planned     |

## 13. Engineering Decisions

### Why a Modular Monolith?

The system currently benefits from a single deployment and a single relational transaction boundary. A modular monolith keeps inventory operations straightforward while allowing the codebase to develop clear domain boundaries.

### Why MySQL Transactions?

Stock receiving and transfers involve multiple related writes. Transactions ensure the balance changes and movement records either commit together or roll back together.

### Why Row-Level Locking?

Inventory operations can be requested concurrently. Row-level locking helps prevent conflicting updates from causing incorrect stock balances.

### Why an Inventory Movement History?

A current stock balance alone does not explain how inventory changed. Movement records provide an audit trail for receiving, transferring, and future adjustment workflows.

### Why API Versioning?

The `/api/v1` prefix provides a stable boundary for future API changes and external integrations.

---

## 14. Interview Demonstration

A recommended technical walkthrough:

1. Explain the warehouse management business domain.
2. Present the application architecture and database relationships.
3. Demonstrate product and warehouse data.
4. Receive inventory into a warehouse.
5. Transfer inventory between warehouses.
6. Demonstrate insufficient stock validation.
7. Explain transactions and row-level locking.
8. Inspect the inventory movement history.
9. Explain Sanctum authentication and API boundaries.
10. Discuss testing, deployment, security, and future improvements.

## 15. Project Philosophy

This project focuses on engineering correctness, maintainability, data integrity, and operational reliability.

The objective is not simply to build CRUD screens, but to demonstrate how a business-critical inventory application can be designed, implemented, tested, and prepared for production.

**Built with Laravel, PHP, MySQL, and Docker.**
