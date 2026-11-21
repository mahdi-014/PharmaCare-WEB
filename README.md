# PharmaCare — Pharmacy Management & Online Medicine Ordering System

**Course:** CIS324 Web Engineering — Semester Assignment  
**Semester:** Fall 2026 | **Section:** Section A  
**Topic:** Topic 13 — Pharmacy Inventory and Online Medicine Ordering System  
**Student Name:** Md. Mahdi Hasan  
**Student ID:** `241-16-016`  
**Git Branch:** `main`  
**Git Repository:** [https://github.com/mahdi-014/PharmaCare-WEB.git](https://github.com/mahdi-014/PharmaCare-WEB.git)  
**Implementation Stack:** Core PHP 8.1+ / 8.3, MySQL 5.7+ / MariaDB 10.4+ (InnoDB), PDO, Bootstrap 5.3.3, Vanilla JavaScript (ES6+ Fetch API), Apache 2.4.  
**External Frameworks:** Strictly **Zero** — Native PHP and JavaScript without Laravel, Symfony, React, Vue, or Composer dependencies.  
**Architecture:** Ultra-Compact Modular MVC Architecture (~27 canonical files).

---

## 1. Executive Summary & System Overview

PharmaCare is a comprehensive, production-grade pharmacy web application engineered to bridge in-clinic dispensary workflows with an asynchronous digital medicine storefront. Developed as the semester assignment for **CIS324 Web Engineering**, the system addresses real-world pharmaceutical logistics including batch-level inventory tracking, First-Expiry-First-Out (FEFO) stock allocation, pessimistic concurrency control, doctor prescription verification, multi-channel sales reconciliation, and strict role-based access control (RBAC).

### Key Operational Modules
1. **Dispensary & Inventory Management (`pages/medicines.php`):**
   - Batch-level tracking: Batch Number, Expiry Date, Purchase Price, Selling Price, and Minimum Stock Threshold.
   - Dynamic classification: Available, Low Stock ($\le 10$ units), Expiring Soon ($< 30$ days), Expired, and Inactive.
   - Pessimistic row locking (`SELECT ... FOR UPDATE`) inside database transactions to eliminate race conditions and overselling.
2. **Customer Digital Storefront (`pages/catalog.php`, `pages/cart.php`):**
   - Real-time catalog filtering by therapeutic category and instant debounced search (Vanilla JS Fetch API).
   - Session-backed shopping cart with stock-clamping and automated prescription-flag detection (`requires_prescription = 1`).
   - Integrated single-page checkout supporting delivery coordinates, phone validation (`^01[3-9]\d{8}$`), Cash on Delivery (COD), and secure doctor prescription file upload.
3. **Staff Fulfillment & Prescription Verification (`pages/orders.php`, `pages/order_detail.php`):**
   - Multi-parameter fulfillment queue filtering by order status (`PENDING`, `PROCESSING`, `SHIPPED`, `DELIVERED`, `CANCELLED`), payment status, and keyword search.
   - Strict order state-machine lifecycle enforcement with atomic stock deduction and automated inventory restoration upon order cancellation.
   - Prescription verification console allowing licensed pharmacists to inspect, approve, or reject uploaded medical prescriptions.
   - Automated accounting bridge: Completed customer orders automatically synchronize into financial sales ledger records.
4. **Point of Sale (POS) Billing & Invoicing (`pages/sales.php`, `pages/invoice.php`):**
   - High-speed walk-in counter sales terminal with multi-item line entry, automated line-item totals, and dynamic discount calculations.
   - Atomic multi-batch stock deduction within an isolated database transaction.
   - Clean, professional, printable sales tax invoice generation with CSS `@media print` directives.
5. **Unified Analytical Dashboard (`pages/dashboard.php`):**
   - Executive KPIs: Today's sales, all-time gross revenue, and POS vs. Online revenue breakdown.
   - Inventory health warnings: Active batches, low stock warnings, expiring/expired alert counts.
   - Real-time operations queue: Pending online customer orders and unverified prescriptions awaiting pharmacist action.

---

## 2. System Architecture

PharmaCare implements an ultra-compact layered **Model-View-Controller (MVC)** architectural pattern built entirely in native PHP without third-party frameworks.

```
                              PharmaCare High-Level Architecture
 ┌────────────────────────────────────────────────────────────────────────────────────────┐
 │                                   CLIENT TIER (Browser)                                │
 │  Semantic HTML5  │  Bootstrap 5.3.3 CSS  │  Vanilla JS (ES6+ Fetch, Debounced Search)   │
 └───────────────────────────────────────────┬────────────────────────────────────────────┘
                                             │ HTTP / HTTPS Requests
                                             ▼
 ┌────────────────────────────────────────────────────────────────────────────────────────┐
 │                             WEB SERVER & ENTRY GATEWAY (Apache)                        │
 │  .htaccess (Options -Indexes, Security Headers, Protected Directory & File Denials)    │
 └──────────────────────┬─────────────────────────────────────────────────┬───────────────┘
                        │ GET Page Views                                  │ POST Actions & APIs
                        ▼                                                 ▼
 ┌──────────────────────────────────────────────┐   ┌─────────────────────────────────────┐
 │         FRONT CONTROLLER (`index.php`)       │   │    ACTION DISPATCHER (`actions.php`)│
 │   - Boots Environment (functions.php)        │   │    - Method Enforcement (POST-only) │
 │   - Session Lifecycle & RBAC (auth.php)      │   │    - Timing-safe CSRF Validation    │
 │   - Page Route Dispatcher                    │   │    - Exception Handled Transactions │
 └──────────────────────┬───────────────────────┘   └──────────────────┬──────────────────┘
                        │                                              │
                        ├───────────────────────┬──────────────────────┘
                        ▼                       ▼
 ┌────────────────────────────────────────────────────────────────────────────────────────┐
 │                                  DOMAIN CONTROLLER LAYER                               │
 │   app/Controller.php (Cart Mutations, State Machine, Checkout, POS Billing, Dashboard) │
 │   api/router.php     (RESTful JSON API Endpoints: Live Search, Async Cart, Polling)    │
 └──────────────────────┬──────────────────────────────────────────────┬──────────────────┘
                        │                                              │
                        ▼                                              ▼
 ┌──────────────────────────────────────────────┐   ┌─────────────────────────────────────┐
 │              DOMAIN SERVICE LAYER            │   │              MODEL LAYER            │
 │  app/InventoryService.php                    │   │  app/Model.php                      │
 │   - SELECT ... FOR UPDATE Row Locking        │   │   - User, Medicine, Order Data      │
 │   - Automated FEFO Batch Allocation          │   │   - Parameterized PDO Queries       │
 │   - Idempotent Stock Restoration             │   │   - Encapsulated Business Rules     │
 └──────────────────────┬───────────────────────┘   └──────────────────┬──────────────────┘
                        │                                              │
                        └───────────────────────┬──────────────────────┘
                                                ▼
 ┌────────────────────────────────────────────────────────────────────────────────────────┐
 │                                 DATA PERSISTENCE & STORAGE                             │
 │   config/database.php (PDO Connection Singleton, UTF-8, Native Emulated Prepares OFF)  │
 │   MariaDB / MySQL 3NF Database (10 Normalized InnoDB Tables, 14 Foreign Keys)          │
 │   storage/prescriptions/ (Protected Medical Document Vault, Authenticated Streaming)   │
 └────────────────────────────────────────────────────────────────────────────────────────┘
```

### Directory Layout & File Responsibility Map (~27 Canonical Files)
```text
PharmaCare-PHP/
├── config/
│   └── database.php           # PDO connection singleton & environment credentials (Q4, Q5)
├── includes/
│   ├── auth.php               # Session security, 30-min idle timeout, role guards (Q7)
│   ├── functions.php          # Central bootstrap, formatting helpers, CSRF engine, e() (Q1, Q5, Q7)
│   ├── header.php             # Responsive Bootstrap 5 navbar, badge counts, flash alerts (Q2)
│   └── footer.php             # Page footer & global script includes (Q2)
├── app/                       # Compact MVC Domain Layer (Q8)
│   ├── Model.php              # Merged User, Medicine, and Order data models
│   ├── Controller.php         # Unified MVC Action Controller & Domain Services
│   └── InventoryService.php   # Centralized row locking (SELECT ... FOR UPDATE) & FEFO logic
├── pages/                     # 11 Consolidated Core Views (Q2)
│   ├── login.php              # User authentication screen & attempt tracking
│   ├── register.php           # Customer registration form & validation
│   ├── dashboard.php          # Executive analytics dashboard & inventory health cards
│   ├── catalog.php            # Storefront medicine browsing & category filter
│   ├── cart.php               # Shopping cart + integrated checkout + prescription upload
│   ├── orders.php             # Customer order history + staff fulfillment queue
│   ├── order_detail.php       # Order lifecycle transition, audit timeline & prescription review
│   ├── medicines.php          # Inventory table + add/edit medicine + categories & suppliers tabs
│   ├── sales.php              # Interactive POS billing counter + sales ledger
│   ├── invoice.php            # Official printable sales tax receipt
│   └── users.php              # Administrator user management & role control (admin only)
├── api/
│   └── router.php             # Unified JSON Web API router (search, cart, live order status) (Q6)
├── assets/
│   ├── style.css              # Custom layout styling, status badges, print rules (Q2)
│   └── script.js              # Debounced search, async cart, live order polling, POS billing (Q3)
├── database/
│   └── pharmacare.sql         # Canonical 10-table relational schema & 15-medicine dataset (Q4)
├── storage/
│   └── prescriptions/         # Uploaded Rx files (blocked from direct web access via .htaccess) (Q9)
├── tests/
│   ├── run_tests.php          # 11-test automated QA regression suite (CLI-only, sandbox guarded)
│   └── README.md              # Test execution guidelines and database isolation rules
├── docs/
│   └── screenshots/           # High-resolution UI evidence screenshots (1440 × 900 px) (Q2, Q3)
├── submission/                # Standalone submission package (PDF, Reports, Architecture, ERDs)
├── actions.php                # Centralized POST form action processor with CSRF validation (Q1, Q5)
├── index.php                  # Central Front Controller & Route Dispatcher (Q1)
├── .gitignore                 # Centralized repository ignore rules (Q9)
├── .htaccess                  # Centralized Apache security, headers, & directory blocking (Q9)
├── LICENSE                    # MIT open-source license
└── README.md                  # Definitive project guide, setup instructions, & viva defense mapping
```

---

## 3. Technology Stack

| Component | Technology | Version | Justification & Architectural Role |
| :--- | :--- | :--- | :--- |
| **Backend Runtime** | PHP | 8.1+ / 8.3 | Native server-side script execution with strict type declarations (`declare(strict_types=1)`). |
| **Database Engine** | MariaDB / MySQL | 10.4+ / 8.0+ | ACID-compliant relational persistence using the InnoDB engine with row-level pessimistic locking. |
| **Database Driver** | PDO (PHP Data Objects) | Native | Secure database abstraction with parameterized prepared statements (`PDO::ATTR_EMULATE_PREPARES => false`). |
| **Web Server** | Apache HTTP Server | 2.4+ | Request dispatching, security headers, directory indexing prevention, and route protection. |
| **CSS Framework** | Bootstrap | 5.3.3 | Mobile-first responsive grid system, modern flexbox utilities, and accessible components. |
| **Icon Library** | Bootstrap Icons | 1.11.3 | Lightweight SVG icon system integrated via CDN. |
| **Client Scripting** | Vanilla JavaScript | ECMAScript 6+ | Asynchronous Fetch API, debounced input event listeners, and dynamic DOM mutation without jQuery. |
| **External Libraries** | **None (Zero)** | — | Pure custom implementation without composer packages, npm dependencies, or heavy frameworks. |

---

## 4. Relational Database Schema & Data Integrity

The database (`database/pharmacare.sql`) consists of **10 normalized InnoDB tables** structured in strict **Third Normal Form (3NF)** with **14 foreign key constraints** ensuring referential integrity:

```
                                  Entity-Relationship Overview
 ┌──────────────┐          ┌──────────────┐          ┌──────────────────────┐
 │  app_users   │1        *│    orders    │1        *│     order_items      │
 │  (id, role)  ├──────────►(id, user_id) ├──────────►(id, order_id, ... )   │
 └──────┬───────┘          └──────┬───────┘          └──────────┬───────────┘
        │1                        │1                            │*
        │                         │                             ▼
        │                         │1                 ┌──────────────────────┐
        │                         └─────────────────►│     prescriptions    │
        │                                            │ (id, order_id, file) │
        │*                                           └──────────────────────┘
 ┌──────┴───────┐          ┌──────────────┐                     ▲
 │    sales     │1        *│  sale_items  │                     │
 │  (id, user)  ├──────────► (id, sale_id)│                     │
 └──────────────┘          └──────┬───────┘                     │
                                  │*                            │
                                  ▼                             │
 ┌──────────────┐          ┌──────────────┐                     │
 │  categories  │1        *│  medicines   │*                   1│
 │  (id, name)  ├──────────► (id, batch,  ├─────────────────────┘
 └──────────────┘          │  cat, supp)  │
 ┌──────────────┐1        *│              │
 │  suppliers   ├──────────►              │
 │  (id, name)  │          └──────┬───────┘
 └──────────────┘                 │1
                                  │*
                           ┌──────┴──────────────┐
                           │ order_status_history│
                           │(id, order_id, actor)│
                           └─────────────────────┘
```

### Table Summary & Primary Features
1. **`app_users`:** User authentication records, contact details, and role assignments (`ADMIN`, `STAFF`, `CUSTOMER`). Passwords hashed with `PASSWORD_BCRYPT` (work factor $2^{12}$).
2. **`categories`:** Pharmacological classifications (e.g., Analgesic, Antibiotic, Antihistamine, Gastric Care).
3. **`suppliers`:** Pharmaceutical manufacturers (e.g., Square, Beximco, Incepta) and licensing details.
4. **`medicines`:** Batch-level pharmaceutical stock records tracking batch number, expiration date, cost price, unit selling price, physical quantity, and `requires_prescription` binary flag.
5. **`sales`:** Multi-channel sales ledger tracking POS counter transactions and bridged online orders.
6. **`sale_items`:** Itemized sales ledger lines capturing quantity, unit price, and subtotal.
7. **`orders`:** Online customer orders recording status state machine, delivery address, phone, and payment method.
8. **`order_items`:** Preserved historical snapshots of purchased medicine name, batch number, unit price, and quantity.
9. **`order_status_history`:** Strict audit log recording every order status transition, actor ID, and timestamp.
10. **`prescriptions`:** Uploaded medical prescription metadata, hashed storage filenames, and pharmacist verification status (`PENDING`, `APPROVED`, `REJECTED`).

---

## 5. Setup & Installation Guide

### Prerequisites
- **PHP 8.1 or higher** with `pdo_mysql`, `mbstring`, `fileinfo`, and `session` extensions enabled.
- **MySQL 5.7+** or **MariaDB 10.4+** running locally or remotely.
- **Apache Web Server 2.4+** with `mod_rewrite` and `mod_headers` enabled.

---

### Step-by-Step Installation (XAMPP on Windows)

#### Step 1: Deploy Files to Web Root
Clone or extract the repository directly into your web server document directory:
```bash
# Target path:
C:\xampp\htdocs\PharmaCare-PHP\
```

#### Step 2: Import the Relational Database
1. Open the XAMPP Control Panel and start **Apache** and **MySQL**.
2. Navigate to **phpMyAdmin** in your browser: `http://localhost/phpmyadmin/`.
3. Create a new database named `pharmacare` with collation `utf8mb4_unicode_ci`.
4. Click the **Import** tab, browse to:
   ```text
   C:\xampp\htdocs\PharmaCare-PHP\database\pharmacare.sql
   ```
5. Click **Import** (or **Go**). Verify that all 10 tables and 15 seeded medicines are created.

*Alternative CLI Import:*
```bash
mysql -u root -p pharmacare < database/pharmacare.sql
```

#### Step 3: Verify Database Credentials
Open `config/database.php`. The default credentials are pre-configured for standard local XAMPP:
```php
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', (int)(getenv('DB_PORT') ?: 3306));
define('DB_NAME', getenv('DB_NAME') ?: 'pharmacare');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
```

#### Step 4: Storage Directory Permissions
Ensure the prescription upload directory exists and is writable by the PHP web server process:
```text
D:\PharmaCare-PHP-Complete\PharmaCare-PHP\storage\prescriptions\
```
*(On Linux/macOS: `chmod 755 storage/prescriptions`)*

#### Step 5: Access the Application
Open your web browser and navigate to:
- **Application Front Page:** `http://localhost/PharmaCare-PHP/`
- **Customer Storefront:** `http://localhost/PharmaCare-PHP/index.php?page=catalog`
- **User Authentication:** `http://localhost/PharmaCare-PHP/index.php?page=login`

---

## 6. User Roles & Evaluation Credentials

The database is pre-seeded with three academic evaluation accounts to facilitate immediate grading and oral defense demonstrations:

| Role | Username | Password | Access Scope & System Permissions |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin` | `admin123` | **Full Administrative Access:** Executive Analytics Dashboard, Inventory CRUD, Categories & Suppliers Management, POS Billing Counter, Fulfillment Queue, Prescription Review, and User Account Administration (`pages/users.php`). |
| **Staff Pharmacist** | `staff` | `staff123` | **Operational Access:** Dashboard, Inventory Lookup, POS Counter Billing, Order Fulfillment Queue, and Prescription Verification Console. Administrative routes (User Management) are strictly blocked with `403 Forbidden`. |
| **Online Customer** | `customer` | `customer123` | **Customer Storefront:** Medicine Catalog, Debounced Search, Shopping Cart, Checkout, Prescription File Upload, Order Tracking, and Personal Order History. Administrative and staff consoles are blocked with `403 Forbidden`. |

*(New customer accounts can also be created dynamically via the public registration form at `index.php?page=register`).*

---

## 7. Automated Testing & Verification Guide

PharmaCare includes a comprehensive, CLI-only automated regression testing suite (`tests/run_tests.php`) designed to verify system integrity without affecting production data.

### Test Isolation & Safety Guards
The test runner is protected by **multi-tier safety assertions** to prevent accidental execution against production databases:
1. **PHP CLI Enforcement:** Execution via web browsers is blocked with `403 Forbidden`.
2. **Database Identity Guard:** Execution is strictly aborted unless `DB_NAME` is explicitly set to `pharmacare_test`.
3. **Restricted User Assertion:** Tests require execution via a dedicated restricted user (`pharmacare_test_user`) holding privileges on `pharmacare_test.*` only, preventing access to `pharmacare`.

### Setting Up the Isolated Test Database
In MySQL/MariaDB CLI or phpMyAdmin, create the test database and assign restricted permissions:
```sql
CREATE DATABASE IF NOT EXISTS pharmacare_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE USER IF NOT EXISTS 'pharmacare_test_user'@'localhost' IDENTIFIED BY 'TestPass_2026!';
GRANT SELECT, INSERT, UPDATE, DELETE ON pharmacare_test.* TO 'pharmacare_test_user'@'localhost';
FLUSH PRIVILEGES;
```
Import the schema into the test database:
```bash
mysql -u pharmacare_test_user -p pharmacare_test < database/pharmacare.sql
```

### Running the Test Suite
Execute the test runner from the project root using environment variables:
```bash
# Windows PowerShell:
$env:DB_HOST="127.0.0.1"
$env:DB_PORT="3306"
$env:DB_NAME="pharmacare_test"
$env:DB_USER="pharmacare_test_user"
$env:DB_PASS="TestPass_2026!"
php tests/run_tests.php

# Linux / macOS Bash:
DB_HOST=127.0.0.1 DB_PORT=3306 DB_NAME=pharmacare_test DB_USER=pharmacare_test_user DB_PASS='TestPass_2026!' php tests/run_tests.php
```

### Test Suite Inventory (11 Automated Test Cases)
| Test ID | Test Category | Target Component | Verifies |
| :--- | :--- | :--- | :--- |
| **`TC-1.1`** | Static Linting | All `.php` source files | Full PHP syntax check (`php -l`) across all application files with 0 errors. |
| **`TC-1.2`** | Repository Hygiene | Root directory & config | Absence of leftover scratch scripts, temporary files, credentials, or backup dumps. |
| **`SEC-01`** | Security Defense | HTML Escaping `e()` | Prevention of DOM XSS through strict `htmlspecialchars()` escaping of quotes and tags. |
| **`SEC-02`** | Security Defense | `api/router.php` | Prevention of sensitive database/server error disclosure in JSON error envelopes. |
| **`SEC-04`** | Security Defense | `storage/prescriptions/` | Prescription file lifecycle cleanup and physical unlinking upon database rollback. |
| **`SEC-05`** | Security Defense | `order_items` table | Integrity of historical price and batch snapshots independent of future catalog edits. |
| **`TC-3.1`** | Business Logic | `app/InventoryService.php`| Concurrency-safe First-Expiry-First-Out (FEFO) batch allocation algorithm. |
| **`TC-3.2`** | Business Logic | `app/InventoryService.php`| Idempotency of inventory stock restoration upon order cancellation. |
| **`TC-3.3`** | State Machine | `app/Controller.php` | Order status transition validation matrix (valid progressions vs. illegal regressions). |
| **`TC-4.1`** | RBAC Guard | `pages/users.php` | Strict enforcement of `require_admin()` blocking unauthorized staff and customers. |
| **`TC-4.2`** | IDOR Defense | `orderStatusApi()` | IDOR protection preventing customers from accessing orders belonging to other users. |

**Latest Execution Result:** **11 Executed, 11 Passed, 0 Failed (100% Pass Rate).**

---

## 8. Security Architecture & Threat Mitigations

PharmaCare incorporates defense-in-depth security mechanisms designed to mitigate the primary web security risks identified in the OWASP Top 10:

1. **SQL Injection (SQLi) Defense:**
   - Universal use of PDO parameterized prepared statements (`$stmt->execute([$param])`).
   - Native prepared statements enforced via `PDO::ATTR_EMULATE_PREPARES => false`.
   - Dynamic user parameters are strictly bound to query placeholders, preventing attackers from injecting SQL command structures.
2. **Cross-Site Scripting (XSS) Defense:**
   - All user-supplied output in HTML views is sanitized using the global presentation helper:
     ```php
     function e(?string $val): string {
         return htmlspecialchars((string)$val, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
     }
     ```
   - Client-side DOM inputs are sanitized in `assets/script.js` using `escapeHtml()`.
3. **Cross-Site Request Forgery (CSRF) Defense:**
   - Cryptographically secure 64-character hex tokens generated via `bin2hex(random_bytes(32))` and stored in `$_SESSION['_csrf']`.
   - All POST forms require `csrf_field()`.
   - `actions.php` verifies incoming tokens using timing-safe `hash_equals()`.
   - Asynchronous JSON requests require the `X-CSRF-Token` header.
4. **Insecure Direct Object Reference (IDOR) Defense:**
   - Order tracking and prescription downloads enforce tenancy checks verifying that `order.user_id === session.user_id` unless the authenticated user is an authorized `STAFF` or `ADMIN`.
5. **Secure Password Handling:**
   - Passwords hashed using native `password_hash($password, PASSWORD_BCRYPT, ['cost' => 12])` providing a work factor of $2^{12}$ iterations.
   - Authentication verified using timing-attack resistant `password_verify()`.
6. **Hardened Session Management:**
   - Session cookies configured with `HttpOnly` (mitigates JavaScript cookie access) and `SameSite=Lax` (reduces cross-site request leakage).
   - Inactivity timeout automatically invalidates sessions idle for $> 30$ minutes (1800 seconds).
   - Session identifiers regenerated (`session_regenerate_id(true)`) upon privilege transitions and login.
7. **Secure File Upload & Storage Vault:**
   - Uploaded prescriptions are checked for legitimate MIME types (`image/jpeg`, `image/png`, `application/pdf`) and size restrictions ($\le 5\text{ MB}$).
   - Files are stored using random MD5/SHA256 hex hashes in `storage/prescriptions/`.
   - Direct web access to `storage/` is blocked via root `.htaccess`. Files are streamed securely through an authenticated controller in `pages/order_detail.php?download=1`.

---

## 9. Documentation Index

All technical documents, audits, diagrams, and reports are consolidated in the [`submission/`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/submission/) directory:

| Document | Format | Description & Purpose |
| :--- | :---: | :--- |
| [`submission/241-16-016_A_CIS324_Assignment.pdf`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/submission/241-16-016_A_CIS324_Assignment.pdf) | PDF (5.98 MB) | **Official Submission Document:** Final compiled PDF report with cover page, TOC, diagrams, 14 screenshots, and APA 7th references. |
| [`submission/CIS324_Assignment_Report_Review.md`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/submission/CIS324_Assignment_Report_Review.md) | Markdown | **Working Review Source:** Complete editable report with verified source code citations, line numbers, and student action placeholders. |
| [`submission/CIS324_Assignment_Report.md`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/submission/CIS324_Assignment_Report.md) | Markdown | **Original Baseline Report:** Unmodified primary project report preserved for version comparison and academic integrity. |
| [`submission/PharmaCare_System_Architecture.md`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/submission/PharmaCare_System_Architecture.md) | Markdown | **Technical Architecture Specification:** Deep architectural analysis of MVC layers, request lifecycles, and concurrency controls. |
| [`submission/PharmaCare_ER_Diagram.pdf`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/submission/PharmaCare_ER_Diagram.pdf) | PDF (Vector) | **Entity-Relationship Diagram (Vector):** High-resolution A3 landscape relational schema diagram in Crow's Foot notation. |
| [`submission/PharmaCare_ER_Diagram.png`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/submission/PharmaCare_ER_Diagram.png) | PNG (Raster) | **Entity-Relationship Diagram (Image):** High-resolution raster rendering of the 10-table schema for visual embedding. |
| [`submission/PharmaCare_ER_Diagram.mmd`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/submission/PharmaCare_ER_Diagram.mmd) | Mermaid Source| **ER Diagram Source Code:** Valid Mermaid ER syntax representing all 10 tables and 14 foreign keys. |
| [`submission/ERD_Analysis_Report.md`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/submission/ERD_Analysis_Report.md) | Markdown | **Relational Schema Audit:** Comprehensive column-by-column data dictionary, key analysis, and normalization proof. |
| [`submission/FINAL_ASSIGNMENT_AUDIT.md`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/submission/FINAL_ASSIGNMENT_AUDIT.md) | Markdown | **Assignment Rubric Audit:** Item-by-item verification against the 35-mark criteria in `CIS324_Assignment_Fall_2026.pdf`. |
| [`submission/REPORT_REVIEW_CHECKLIST.md`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/submission/REPORT_REVIEW_CHECKLIST.md) | Markdown | **Report Verification Checklist:** 25-point quality assurance verification report confirming report accuracy. |
| [`docs/screenshots/SCREENSHOT_COLLECTION_REPORT.md`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/docs/screenshots/SCREENSHOT_COLLECTION_REPORT.md) | Markdown | **Screenshot Verification Report:** Verification metadata, viewports, and MD5 hashes for all 14 visual evidence files. |

---

## 10. Screenshot & Visual Evidence Index

All 14 UI screenshots adhere strictly to a standardized **1440 × 900 pixel** desktop viewport and contain genuine application data:

| Fig # | Screenshot Filename | Target Route / Context | Caption & Evidentiary Description |
| :---: | :--- | :--- | :--- |
| **Fig 1** | [`01_Login.png`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/docs/screenshots/01_Login.png) | `index.php?page=login` | Guest authentication interface featuring username, password, CSRF token, and registration redirect. |
| **Fig 2** | [`02_Register.png`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/docs/screenshots/02_Register.png) | `index.php?page=register` | Customer account registration screen with full name, email, phone regex validation, and address. |
| **Fig 3** | [`03_Dashboard.png`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/docs/screenshots/03_Dashboard.png) | `index.php?page=dashboard` | Executive analytics dashboard displaying sales revenue KPIs, inventory health alerts, and operations queue. |
| **Fig 4** | [`04_Medicine_Management.png`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/docs/screenshots/04_Medicine_Management.png) | `index.php?page=medicines` | Dispensary inventory table with batch numbers, unit prices, expiry status badges, and action buttons. |
| **Fig 5** | [`05_Category_Supplier.png`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/docs/screenshots/05_Category_Supplier.png) | `index.php?page=medicines&tab=categories`| Therapeutic categories management console and supplier relationship interface. |
| **Fig 6** | [`06_Medicine_Search.png`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/docs/screenshots/06_Medicine_Search.png) | `index.php?page=catalog&search=Napa` | Real-time debounced medicine search results displaying stock availability and category badges. |
| **Fig 7** | [`07_Cart.png`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/docs/screenshots/07_Cart.png) | `index.php?page=cart` | Session shopping cart with dynamic quantity adjustments, price summaries, and prescription alerts. |
| **Fig 8** | [`08_Checkout.png`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/docs/screenshots/08_Checkout.png) | `index.php?page=cart#checkout` | Single-page checkout terminal with delivery address, phone validation, and payment method selection. |
| **Fig 9** | [`09_Order_Management.png`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/docs/screenshots/09_Order_Management.png) | `index.php?page=orders` | Staff fulfillment queue with multi-factor filters, order status pipelines, and summary cards. |
| **Fig 10** | [`10_Prescription.png`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/docs/screenshots/10_Prescription.png) | `index.php?page=cart#rx` | Doctor prescription upload interface displaying file format notices and regulatory warnings. |
| **Fig 11** | [`11_User_Management.png`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/docs/screenshots/11_User_Management.png) | `index.php?page=users` | Administrator user administration interface with role filtering, status toggles, and account forms. |
| **Fig 12** | [`12_Database_Structure.png`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/docs/screenshots/12_Database_Structure.png) | `phpmyadmin` | phpMyAdmin structural overview showing all 10 normalized InnoDB tables, collation, and row counts. |
| **Fig 13** | [`13_ER_Diagram.png`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/docs/screenshots/13_ER_Diagram.png) | `submission/PharmaCare_ER_Diagram.pdf` | High-resolution render of the complete 10-table A3 landscape Entity-Relationship Diagram. |
| **Fig 14** | [`14_Test_Results.png`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/docs/screenshots/14_Test_Results.png) | `tests/run_tests.php` | Terminal screenshot showing 11 automated test cases passing with 100% success rate in 4.096 seconds. |
| **Fig 15** | [`PharmaCare_ER_Diagram.png`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/submission/PharmaCare_ER_Diagram.png) | Standalone ER Diagram | Standalone high-resolution ER diagram render for vector print and digital display. |

---

## 11. CIS324 Assignment Question-to-Implementation Mapping

This matrix maps questions **Q1 through Q9** in the official assignment specification (`CIS324_Assignment_Fall_2026.pdf`) directly to the implementation files and report sections:

| Assignment Task & Question | Marks | Key Academic Concept | Concrete Source Code Implementation | Report Section |
| :--- | :---: | :--- | :--- | :--- |
| **Task 1 — Q1** | 3 | Client-Server Interaction & HTTP Lifecycle | `index.php` (Front Controller), `actions.php` (POST Dispatcher), `includes/functions.php`. | Section 1.1–1.3 |
| **Task 1 — Q2** | 2 | Web Development Life Cycle (WDLC 7 Phases) | Structured across 7 phases: Planning, Analysis, Design, Development, Testing, Deployment, Maintenance. | Section 2.1–2.2 |
| **Task 2 — Q3** | 4 | Semantic UI, State Management & Views | Semantic HTML5, Bootstrap 5.3.3, 11 core views in `pages/`, `assets/style.css`, `assets/script.js`. | Section 3.1–3.4 |
| **Task 2 — Q4** | 5 | Relational DB Design, 3NF & CRUD Operations | `database/pharmacare.sql` (10 tables, 14 FKs), `config/database.php` (PDO Singleton), `actions.php`. | Section 4.1–4.5 |
| **Task 3 — Q5** | 6 | Web Security Vulnerabilities & Mitigations | Defense against SQLi, XSS, Weak Passwords, IDOR, CSRF in `functions.php`, `auth.php`, `actions.php`. | Section 5.1–5.4 |
| **Task 3 — Q6** | 4 | Asynchronous JSON APIs & Client Scripting | `api/router.php` (Endpoints: search, cart_add, cart_update, status), `assets/script.js` (Fetch API). | Section 6.1–6.4 |
| **Task 4 — Q7** | 4 | State Management, Sessions & RBAC | Session security flags, 30-min idle timeout (`auth.php`), role guards (`require_admin()`, etc.). | Section 7.1–7.4 |
| **Task 4 — Q8** | 4 | MVC Architecture & Code Organization | Layered MVC in `app/Model.php`, `app/Controller.php`, `app/InventoryService.php`, and `pages/`. | Section 8.1–8.4 |
| **Task 4 — Q9** | 3 | Deployment Architecture & Future Scope | Apache configuration (`.htaccess`), prescription file streaming, system limitations, and future roadmap. | Section 9.1–9.4 |
| **Annex A** | — | Academic Integrity & AI-Use Disclosure | University AMBER Tier declaration detailing AI assistance, verification steps, and student authorship. | Annex A |

---

## 12. Submission Package Directory Guide

A consolidated standalone submission folder is prepared at [`submission/`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/submission/) containing all essential deliverables for evaluation:

```text
submission/
├── 241-16-016_A_CIS324_Assignment.pdf   # Complete final assignment PDF report (5.98 MB)
├── CIS324_Assignment_Report_Review.md   # Editable report Markdown source with code references
├── PharmaCare_ER_Diagram.pdf            # High-resolution vector ER Diagram (A3 Landscape)
├── PharmaCare_ER_Diagram.png            # High-resolution raster ER Diagram image
├── PharmaCare_System_Architecture.md    # In-depth system architecture specification
├── REPORT_REVIEW_CHECKLIST.md           # 25-point report quality assurance checklist
├── FINAL_ASSIGNMENT_AUDIT.md            # Comprehensive 35-mark assignment audit report
├── ERD_Analysis_Report.md               # 10-table relational schema audit and data dictionary
├── FINAL_SUBMISSION_README.md           # Submission package summary and instructions
└── screenshots/                         # All 14 visual evidence verification screenshots
```

---

## 13. Git Repository & Version Control

- **Branch:** `main`
- **Remote URL:** `https://github.com/mahdi-014/PharmaCare-WEB.git`
- **Status:** Complete, fully documented, verified against test suite, and tracked cleanly under Git.

---

## 14. License & Academic Integrity Notice

This project is developed for the **CIS324 Web Engineering** curriculum at the Department of Computer Science & Engineering. It is released under the **MIT License**.

### Statement of Academic Authorship
All architectural code, database schemas, test cases, and technical implementations represent authentic coursework engineering. In compliance with the university's academic regulations and AI usage policy (AMBER Tier), AI assistance was utilized exclusively for syntax verification, test generation, and document organization as documented in Annex A. All conceptual analyses, business logic justifications, and oral defense responses remain the intellectual work of the student author.