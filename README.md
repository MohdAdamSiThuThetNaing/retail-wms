# Enterprise Retail & Warehouse Management System (Retail WMS)

A Laravel-based enterprise application for managing products, warehouses, inventory, suppliers, purchase orders and customers through a centralized web dashboard and REST API.

Built with Laravel 12, PHP 8.4, MySQL, Redis and Docker, the system follows a modular monolith architecture with an emphasis on maintainability, data integrity, secure access control and reliable inventory operations.

**Project Status:** Active Development

---

## 1. Core Features

### Product Management

-   Create, update and manage product records.
-   Maintain unique SKU and barcode information.
-   Manage product categories, brands and units.
-   Track product pricing and active status.
-   Search and filter product listings.

### Warehouse Management

-   Manage multiple warehouses.
-   Maintain warehouse information and contact details.
-   Organize warehouse locations.
-   Track warehouse-specific inventory.

### Inventory Management

-   Receive stock into warehouses.
-   Transfer inventory between warehouses.
-   Maintain warehouse-level stock balances.
-   Track inventory movement history.
-   Monitor reorder levels and low-stock items.
-   Maintain transactional stock updates.

### Supplier Management

-   Maintain supplier profiles and contact information.
-   Manage supplier codes and tax information.
-   Configure payment terms.
-   Track supplier account status.
-   Associate suppliers with purchase orders.

### Purchase Order Management

-   Create and manage purchase orders.
-   Add products and quantities to purchase orders.
-   Calculate order subtotal, discounts, tax and total.
-   Submit purchase orders for approval.
-   Approve or reject submitted orders.
-   Record approval history and comments.
-   Receive ordered quantities into warehouse inventory.
-   Track partially received and completed orders.

### Customer Management

-   Create and maintain customer profiles.
-   Support individual, retail, wholesale and corporate customer types.
-   Maintain customer contact and tax information.
-   Configure credit limits and payment terms.
-   Search and filter customer records.
-   View customer account details.
-   Activate and deactivate customer accounts.

_Sales orders, invoicing, customer purchase history and outstanding credit calculations are future extensions._

### Dashboard and Notifications

-   Centralized application dashboard.
-   Inventory and operational information.
-   Notification history and unread notification tracking.
-   Real-time event communication using Laravel Reverb.
-   Background job processing through Laravel Horizon.

### Authentication and Authorization

-   Secure application authentication.
-   Role-based access control using Spatie Laravel Permission.
-   Permission-based access to business modules.
-   Separate viewing and management permissions where configured.
-   Support for administrative and operational roles.

---

## 2. Technology Stack

| Category                | Technology                           |
| ----------------------- | ------------------------------------ |
| Backend                 | PHP 8.4                              |
| Framework               | Laravel 12                           |
| Database                | MySQL 8                              |
| ORM                     | Laravel Eloquent                     |
| Authentication          | Laravel Authentication / Sanctum API |
| Authorization           | Spatie Laravel Permission            |
| Frontend                | Blade, HTML, CSS, JavaScript         |
| Frontend Build          | Vite                                 |
| Web Server              | Nginx                                |
| PHP Runtime             | PHP-FPM                              |
| Cache / Queue           | Redis                                |
| Queue Management        | Laravel Horizon                      |
| Real-time Communication | Laravel Reverb                       |
| Containerization        | Docker, Docker Compose               |
| Dependency Management   | Composer, npm                        |
| Testing                 | PHPUnit / Laravel Test Suite         |
| Version Control         | Git and GitHub                       |

---

## 3. System Architecture

The application uses a **Modular Monolith Architecture**.

Business capabilities are organized into separate modules while sharing one Laravel application and database. This provides clear separation of responsibilities without unnecessary microservice complexity.

### High-Level Architecture

```text
                    Web Browser
                        |
                        v
                       Nginx
                        |
                        v
                 Laravel 12 Application
                        |
          +-------------+-------------+
          |             |             |
          v             v             v
      Web Layer      REST API    Authentication
       (Blade)       (JSON)       & RBAC
          |             |             |
          +-------------+-------------+
                        |
                        v
                 Business Modules
                        |
       +----------------+----------------+
       |        |        |       |        |
       v        v        v       v        v
    Products  Warehouses Inventory Suppliers Customers
                        |
                        v
                 Purchase Orders
                        |
                        v
                    MySQL 8
```

### Background and Real-time Services

```text
Laravel Application
       |
       +------ Redis
       |         |
       |         v
       |      Horizon
       |         |
       |         v
       |    Queue Workers
       |
       +------ Reverb
                 |
                 v
          Real-time Events
                 |
                 v
             Web Browser
```

### Architectural Principles

-   Separation of HTTP controllers and business responsibilities.
-   Reusable Eloquent models and relationships.
-   Transactional inventory operations.
-   Database constraints and foreign key relationships.
-   Permission-based authorization.
-   Centralized inventory business logic.
-   Asynchronous background processing.
-   Real-time event broadcasting.

---

## 4. Project Structure

```text
retail-wms/
│
├── app/
│   ├── Events/
│   ├── Http/
│   │   └── Controllers/
│   ├── Models/
│   ├── Services/
│   └── Providers/
│
├── bootstrap/
├── config/
│
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
│
├── docker/
│   └── nginx/
│
├── public/
│   ├── css/
│   └── images/
│
├── resources/
│   ├── js/
│   ├── css/
│   └── views/
│       ├── customers/
│       ├── dashboard/
│       ├── layouts/
│       ├── products/
│       ├── purchase-orders/
│       ├── suppliers/
│       └── warehouses/
│
├── routes/
│   ├── api.php
│   └── web.php
│
├── tests/
│
├── Dockerfile
├── docker-compose.yml
├── composer.json
├── package.json
└── README.md
```

---

## 5. Database Design

The database is built around relational data structures with foreign keys, indexes and transactional operations.

### Main Business Tables

| Table                    | Responsibility                             |
| ------------------------ | ------------------------------------------ |
| users                    | Application users                          |
| products                 | Product and SKU information                |
| product_categories       | Product classification                     |
| warehouses               | Warehouse information                      |
| warehouse_locations      | Warehouse location organization            |
| warehouse_stocks         | Current stock per warehouse and product    |
| inventory_movements      | Inventory transaction history              |
| suppliers                | Supplier profiles                          |
| purchase_orders          | Purchase order headers                     |
| purchase_order_items     | Products and quantities in purchase orders |
| purchase_order_approvals | Purchase order approval history            |
| customers                | Customer profiles                          |
| roles                    | Application roles                          |
| permissions              | Application permissions                    |
| notifications            | Application notifications                  |

Laravel and supporting packages also use framework tables for sessions, cache, jobs and authentication tokens as configured.

### Important Relationships

```text
Supplier
   |
   +------ Purchase Orders
                 |
                 +------ Purchase Order Items
                 |
                 +------ Purchase Order Approvals

Customer
   |
   +------ Future Sales Orders

Product
   |
   +------ Warehouse Stock
   |
   +------ Inventory Movements
   |
   +------ Purchase Order Items

Warehouse
   |
   +------ Warehouse Stock
   |
   +------ Inventory Movements
   |
   +------ Purchase Orders
```

---

## 6. Inventory and Purchase Order Workflows

### Stock Receiving

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
Update Warehouse Stock
     |
     v
Create Inventory Movement
     |
     v
Commit Transaction
     |
     v
Broadcast Inventory Update
```

### Stock Transfer

```text
Transfer Request
      |
      v
Validate Source and Destination
      |
      v
Begin Transaction
      |
      v
Lock Affected Stock Rows
      |
      v
Check Available Quantity
      |
      v
Decrease Source Stock
      |
      v
Increase Destination Stock
      |
      v
Record Inventory Movements
      |
      v
Commit Transaction
```

### Purchase Order Approval and Receiving

```text
Create Purchase Order
        |
        v
Add Products and Quantities
        |
        v
Submit for Approval
        |
        v
Approval Review
        |
        +------ Rejected
        |          |
        |          v
        |      Revise Order
        |
        +------ Approved
                   |
                   v
             Receive Goods
                   |
                   v
          Update Warehouse Stock
                   |
                   v
         Record Inventory Movements
                   |
                   v
          Update Receiving Status
```

Inventory-changing operations should preserve stock balances and movement history within database transactions.

---

## 7. API

The application includes versioned REST API routes under the `/api/v1` prefix.

The documented inventory and catalog endpoints include:

| Method | Endpoint                               | Description                  |
| ------ | -------------------------------------- | ---------------------------- |
| GET    | `/api/v1/products`                     | Retrieve products            |
| POST   | `/api/v1/products`                     | Create product               |
| GET    | `/api/v1/warehouses`                   | Retrieve warehouses          |
| GET    | `/api/v1/warehouses/{warehouse}/stock` | Retrieve warehouse stock     |
| POST   | `/api/v1/inventory/receive`            | Receive inventory            |
| POST   | `/api/v1/inventory/transfer`           | Transfer inventory           |
| GET    | `/api/v1/inventory/movements`          | Retrieve inventory movements |

API authentication uses Laravel Sanctum where configured.

To inspect the actual registered endpoints:

```bash
docker compose exec app php artisan route:list --path=api
```

---

## 8. Local Development Setup

### Prerequisites

-   macOS, Linux or Windows
-   Docker Desktop
-   Docker Compose
-   Git
-   Node.js and npm (for local frontend builds)

### Clone the Repository

```bash
git clone https://github.com/MohdAdamSiThuThetNaing/retail-wms.git

cd retail-wms
```

### Start Docker Services

```bash
docker compose up -d --build
```

Check the running containers:

```bash
docker compose ps
```

### Install PHP Dependencies

```bash
docker compose exec app composer install
```

### Configure Environment

Create the environment file:

```bash
cp .env.example .env
```

Configure the application and database settings using your local Docker Compose configuration.

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
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password
```

Use the actual credentials defined in your local environment. Never commit real credentials.

### Generate Application Key

```bash
docker compose exec app php artisan key:generate
```

### Run Database Migrations

```bash
docker compose exec app php artisan migrate
```

### Seed Application Data

```bash
docker compose exec app php artisan db:seed
```

Run seeders only when appropriate for your environment and data state.

### Build Frontend Assets

```bash
npm install
npm run build
```

### Clear Application Cache

```bash
docker compose exec app php artisan optimize:clear
```

### Access the Application

| Service         | Local Address         |
| --------------- | --------------------- |
| Web Application | http://localhost:8081 |
| MySQL           | localhost:3307        |
| Reverb          | localhost:8082        |

The ports above reflect the local development Docker configuration.

---

## 9. Testing and Quality Assurance

Run the Laravel test suite:

```bash
docker compose exec app php artisan test
```

Inspect registered routes:

```bash
docker compose exec app php artisan route:list
```

Check migration status:

```bash
docker compose exec app php artisan migrate:status
```

### Recommended Test Coverage

-   Product creation and SKU uniqueness.
-   Customer and supplier validation.
-   Purchase order creation and approval.
-   Purchase order rejection and revision.
-   Partial and complete purchase order receiving.
-   Stock receiving and stock transfers.
-   Insufficient stock rejection.
-   Transaction rollback.
-   Inventory movement history.
-   Role and permission enforcement.
-   Unauthenticated access restrictions.
-   Concurrent inventory updates.

---

## 10. Security and Data Integrity

Security and data consistency are essential for a retail and warehouse management platform.

Key considerations:

-   Store application secrets in environment variables.
-   Never commit `.env` files or production credentials.
-   Use authentication and permission-based authorization.
-   Validate all incoming request data.
-   Enforce foreign keys and database constraints.
-   Use transactions for inventory-changing operations.
-   Protect sensitive routes with appropriate permissions.
-   Use least-privilege database credentials.
-   Disable debug mode in production.
-   Enforce HTTPS in production.
-   Configure regular database backups.
-   Review access permissions before production deployment.

**Database safety:** Use incremental migrations and preserve existing inventory and transaction records. Avoid destructive database reset commands in environments containing business data.

---

## 11. Development Roadmap

| Area                                     | Status      |
| ---------------------------------------- | ----------- |
| Laravel and Docker foundation            | Implemented |
| Product Management                       | Implemented |
| Warehouse Management                     | Implemented |
| Inventory Management                     | Implemented |
| Stock Receiving and Transfers            | Implemented |
| Supplier Management                      | Implemented |
| Purchase Order Management                | Implemented |
| Purchase Order Approval Workflow         | Implemented |
| Customer Management                      | Implemented |
| Role-Based Access Control                | Implemented |
| Notification and Reverb Integration      | Implemented |
| Sales Order Management                   | Planned     |
| Customer Purchase History                | Planned     |
| Invoicing and Payment Management         | Planned     |
| Customer Outstanding Balance             | Planned     |
| Advanced Financial and Inventory Reports | Planned     |
| Export to Excel and PDF                  | Planned     |
| Automated CI/CD Pipeline                 | Planned     |
| Advanced Inventory Reconciliation        | Planned     |

_Implemented indicates that the corresponding application functionality has been developed; comprehensive automated test coverage and production readiness should be evaluated separately._

---

## 12. Engineering Direction

The long-term objective is to evolve Retail WMS into a comprehensive enterprise retail and warehouse platform.

The next development phases will focus on connecting procurement, inventory, sales, customer accounts and financial operations into a consistent business workflow.

The architecture is designed to support incremental expansion while maintaining data integrity, clear module boundaries and maintainable application code.

---

## License

This project is currently maintained as an actively developed application. License and distribution terms should be defined before public or commercial distribution.
