# PharmaCare — Pharmacy Management & Online Medicine Ordering System

**PharmaCare** is a native PHP and MySQL/MariaDB web application developed for **CIS324 Web Engineering (Topic 13, Fall 2026 — Section A)** by **Md. Mahdi Hasan (`241-16-016`)**.

---

## 1. Purpose

PharmaCare unifies internal dispensary operations and customer-facing e-commerce into one lightweight, framework-free system. It allows pharmacy administrators and staff to control batch-level medicine inventory, prevent expired dispensing, process Point of Sale (POS) counter invoices, and verify patient prescriptions, while allowing customers to search available stock, order medicines online, upload doctor prescriptions, and track fulfillment status.

---

## 2. Main Modules

### Inventory Management ([`pages/medicines.php`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/pages/medicines.php))
- Batch-level tracking (`batch_number`, `manufacturing_date`, `expiry_date`, `purchase_price`, `selling_price`, `quantity`, `minimum_stock_level`, `prescription_required`).
- Dynamic batch status classification via `medicine_status()` (`Available`, `Low stock`, `Out of stock`, `Expiring soon` within 30 days, `Expired`, `Inactive`).
- Master CRUD and dependency-guarded deletion for pharmacological `categories` and `suppliers` (`actions.php`), plus soft-deactivation (`medicine_toggle`).
- First-Expiry-First-Out (**FEFO**) batch allocation and pessimistic row locking (`SELECT ... FOR UPDATE`) in [`app/InventoryService.php`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/app/InventoryService.php).

### Online Ordering ([`pages/catalog.php`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/pages/catalog.php), [`pages/cart.php`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/pages/cart.php), [`pages/orders.php`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/pages/orders.php), [`pages/order_detail.php`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/pages/order_detail.php))
- Public and customer storefront with `300ms` debounced Vanilla JS `fetch()` live search and category filtering via [`api/router.php`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/api/router.php).
- Session-backed shopping cart with stock clamping, async AJAX additions (`action=cart_add`), free shipping (`৳0.00`), and checkout validation (`COD` or `STORE_PICKUP`).
- Mandatory JPG/PNG/PDF prescription upload (`<= 5 MB`) for `prescription_required` items, stored in [`storage/prescriptions/`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/storage/prescriptions/), blocked from direct HTTP access by [`.htaccess`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/.htaccess), and streamed only after authentication and ownership checks.
- Strict order state machine (`PENDING` $\rightarrow$ `CONFIRMED` $\rightarrow$ `PROCESSING` $\rightarrow$ `READY` $\rightarrow$ `DISPATCHED` $\rightarrow$ `COMPLETED`, plus `CANCELLED` with idempotent `stock_restored` restoration) and `10`-second live polling tracker.

### Point of Sale (POS) & Analytics ([`pages/sales.php`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/pages/sales.php), [`pages/invoice.php`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/pages/invoice.php), [`pages/dashboard.php`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/pages/dashboard.php))
- Multi-line walk-in POS counter (`1–40` medicine rows) with live browser subtotal/discount calculation, atomic FEFO stock deduction, and printable invoices (`@media print`).
- Automatic accounting bridge generating `ONLINE` channel invoices in `sales` and `sale_items` when an online order reaches `COMPLETED`.
- Executive KPI dashboard summarizing today's/total revenue, POS vs. Online channel split, inventory alerts, pending order/prescription queues, and top-selling items.

---

## 3. Actual Technologies Used

| Layer | Technology | Notes |
| :--- | :--- | :--- |
| **Server-Side** | PHP `8.1+` (verified locally on `PHP 8.5.12`) | Strict typing (`declare(strict_types=1)`), zero Composer dependencies |
| **Database** | MariaDB / MySQL (`10.11.16-MariaDB` verified) | InnoDB engine (`utf8mb4_unicode_ci`), native PDO (`ATTR_EMULATE_PREPARES => false`) |
| **Web Server** | Apache `2.4+` (`2.4.65` Win64 via XAMPP) | `mod_rewrite`, `mod_headers`, `.htaccess` security rules |
| **Frontend UI** | Semantic HTML5, CSS3, Bootstrap `5.3.3`, Bootstrap Icons `1.11.3` | Responsive custom breakpoints (`991px`, `575px`, `print`) in `assets/style.css` |
| **Client Scripting** | Vanilla JavaScript (ES6+ Fetch API) | Zero jQuery/React/Vue; implemented in `assets/script.js` |

---

## 4. Architecture Overview

PharmaCare implements a compact layered **MVC + Service** structure without external frameworks:

```text
Browser (HTML5 + Bootstrap 5.3.3 + assets/script.js)
   │
   ├── GET ?page=...         ──► index.php (Front Controller, Legacy Redirects, RBAC)
   │                                └── wraps pages/*.php with includes/header.php & footer.php
   │
   ├── POST actions.php      ──► actions.php (POST-only, CSRF verify, PRG redirects)
   │                                └── delegates to App\Controllers\Controller / SQL helpers
   │
   └── Fetch api/router.php  ──► api/router.php (JSON actions: search, cart_add, cart_update, status)
                                    └── delegates to App\Controllers\Controller
                                           │
                                           ├── app/Model.php (User, Medicine, Order — 32 methods)
                                           ├── app/InventoryService.php (FEFO + SELECT ... FOR UPDATE)
                                           └── config/database.php (db() PDO singleton) ──► MariaDB
```

---

## 5. User Roles & Evaluation Credentials

Pre-seeded accounts in [`database/pharmacare.sql`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/database/pharmacare.sql) (passwords hashed with Bcrypt):

| Role | Username | Password | Permissions |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin` | `admin123` | Full access: Dashboard, Inventory/Category/Supplier CRUD, POS & Sales Ledger, Order Fulfillment, Prescription Review, and User Administration ([`pages/users.php`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/pages/users.php)). |
| **Staff Pharmacist** | `staff` | `staff123` | Dashboard, Inventory lookup (read-only), POS billing, Order Fulfillment, and Prescription Verification. User management and cart checkout return `403`. |
| **Customer** | `customer` | `customer123` | Storefront catalog, shopping cart, checkout, prescription upload, personal order tracking, and invoices for own orders. Admin/staff routes return `403`. |

Public visitors can also browse `index.php?page=catalog` or register a new `CUSTOMER` account via `index.php?page=register`.

---

## 6. Project Directory Structure

```text
PharmaCare-PHP/
├── api/
│   └── router.php               # JSON API endpoints (search, cart_add, cart_update, status)
├── app/
│   ├── Controller.php           # Application controller, order/Rx services & dashboard KPIs
│   ├── InventoryService.php     # FEFO batch allocation & SELECT ... FOR UPDATE locking
│   └── Model.php                # User, Medicine, and Order PDO models
├── assets/
│   ├── script.js                # POS calculator, debounced Fetch search, async cart, polling
│   └── style.css                # Custom theme, WCAG-checked palette, responsive & print styles
├── backup_md/                   # Archived phase, integrity, and auxiliary Markdown docs
├── config/
│   └── database.php             # Environment-aware PDO singleton & Asia/Dhaka timezone
├── database/
│   └── pharmacare.sql           # 10-table 3NF InnoDB schema (14 FKs, 10 CHECKs) & seed data
├── docs/
│   └── screenshots/             # UI & responsive viewport evidence screenshots (01–16)
├── includes/
│   ├── auth.php                 # 30-min idle timeout & role guards (require_admin/staff/customer)
│   ├── footer.php               # Global footer & JS bundles
│   ├── functions.php            # Hardened sessions, PDO helpers, CSRF, e(), cart helpers
│   └── header.php               # Role-aware sidebar, topbar, CSRF meta tag & flash alerts
├── pages/                       # 11 consolidated UI views (login, register, dashboard, catalog,
│                                # cart, orders, order_detail, medicines, sales, invoice, users)
├── storage/
│   └── prescriptions/           # Quarantined prescription upload folder (.gitkeep tracked; HTTP 403)
├── submission/                  # Local academic submission package (git-ignored)
├── tests/
│   └── run_tests.php            # CLI-only QA test suite (11 tests guarded to pharmacare_test)
├── .gitignore
├── .htaccess                    # Options -Indexes, security headers, 403 rules on sensitive paths
├── actions.php                  # Centralized POST dispatcher with CSRF & RBAC guards
├── index.php                    # Single Front Controller & route dispatcher
├── LICENSE                      # MIT License
└── README.md                    # Primary project guide
```

---

## 7. System Requirements & Local XAMPP Setup

### Prerequisites
- **XAMPP** (or Apache `2.4+` with `mod_rewrite`/`mod_headers`, PHP `8.1+` with `pdo_mysql`, `mbstring`, `fileinfo`, `session`, and MySQL `5.7+` / MariaDB `10.4+`).

### Installation & Database Import
1. Clone or link the project into XAMPP's web root (`C:\xampp\htdocs\PharmaCare-PHP`):
   ```powershell
   git clone https://github.com/mahdi-014/PharmaCare-WEB.git PharmaCare-PHP
   ```
   *(Or create a directory junction from `C:\xampp\htdocs\PharmaCare-PHP` to your local workspace.)*
2. Start **Apache** and **MySQL** in the XAMPP Control Panel.
3. Import [`database/pharmacare.sql`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/database/pharmacare.sql) into MariaDB/MySQL (creates the `pharmacare` database, all 10 tables, and sample data):
   - **phpMyAdmin:** Open `http://localhost/phpmyadmin/` $\rightarrow$ **Import** $\rightarrow$ select `database/pharmacare.sql` $\rightarrow$ **Import**.
   - **CLI:**
     ```bash
     mysql -u root -p < database/pharmacare.sql
     ```

### Configuration ([`config/database.php`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/config/database.php))
Database settings read environment variables and default to standard local XAMPP values:
```php
DB_HOST = '127.0.0.1'
DB_PORT = 3306
DB_NAME = 'pharmacare'
DB_USER = 'root'
DB_PASS = ''
APP_TIMEZONE = 'Asia/Dhaka'
```
Ensure `storage/prescriptions/` is writable by the web server process.

### Starting & Accessing the Application
- **XAMPP Apache URL:** `http://localhost/PharmaCare-PHP/`
  - Storefront Catalog: `http://localhost/PharmaCare-PHP/index.php?page=catalog`
  - Sign In Portal: `http://localhost/PharmaCare-PHP/index.php?page=login`
- **Alternative PHP Built-In Server (without Apache `.htaccess`):**
  ```bash
  php -S 127.0.0.1:8000
  ```

---

## 8. Testing Instructions & Verified Results

### Automated Regression Suite ([`tests/run_tests.php`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/tests/run_tests.php))
The CLI-only test runner contains **11 tests** (`TC-1.1` lint, `TC-1.2` hygiene, `SEC-01`, `SEC-02`, `SEC-04`, `SEC-05`, `TC-3.1` FEFO, `TC-3.2` cancellation idempotency, `TC-3.3` state machine, `TC-4.1` RBAC, `TC-4.2` IDOR on `orderStatusApi()`) and **aborts immediately** unless connected to an isolated `pharmacare_test` schema via `pharmacare_test_user`:

```powershell
# PowerShell example (after importing database/pharmacare.sql into pharmacare_test)
$env:DB_HOST="127.0.0.1"
$env:DB_PORT="3306"
$env:DB_NAME="pharmacare_test"
$env:DB_USER="pharmacare_test_user"
$env:DB_PASS="<your_test_user_password>"
php tests/run_tests.php
```

### Directly Verified Checks
- **PHP Syntax Lint:** `php -l` passed across all **23 `.php` files** (`0` syntax errors on `PHP 8.5.12`).
- **XAMPP Runtime & Security Headers:** Verified `200 OK` on `/`, `login`, `catalog`, `api/router.php?action=search&keyword=Napa`, plus `403 Forbidden` on `config/database.php`, `database/pharmacare.sql`, and `storage/prescriptions/`.
- **Responsive Viewport Verification (Headless Edge CDP):** Verified `0px` horizontal overflow across `dashboard`, `catalog`, `medicines`, `orders`, `sales`, and `users` at `1440×900`, `768×1024`, and `375×812` (`18/18` checks; see `docs/screenshots/15_Responsive_Tablet_Dashboard_.png` and `16_Responsive_Mobile_Catalog.png`).
- **Test Runner Status:** `docs/screenshots/14_Test_Results.png` records an earlier Nov 20 execution (`10 Passed / 1 Failed`, `90.9%`, `TC-1.2` empty directory subsequently removed). `tests/run_tests.php` was not re-executed during cleanup because `DB_PASS` for `pharmacare_test_user` is stored outside the workspace.

---

## 9. GitHub Repository & Archived Documentation

- **Repository:** `https://github.com/mahdi-014/PharmaCare-WEB.git`
- **Branch:** `main`
- **Archived Markdown Docs:** Historical phase reports, integrity reports, and auxiliary Markdown notes are archived in [`backup_md/`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/backup_md/).
- **Local Assignment Package:** Academic report drafts and compiled PDFs remain locally in git-ignored `submission/`.

---

## 10. Known Limitations

1. **No Live Payment Gateway Integration:** Online checkout supports `COD` and `STORE_PICKUP` only; digital wallets/cards are not integrated.
2. **Local Deployment Only:** Verified on local XAMPP; no live production server deployment has been conducted or verified.
3. **Partial Accessibility & Semantic Gaps:** Verified color contrast ratios pass WCAG 2.2 AA on remediated pairs, but minor semantic heading-level skips (`h2` $\rightarrow$ `h4`/`h5`), an unlabeled search input (`pages/orders.php:198`), `title`-only icon buttons (`pages/medicines.php`), and screen-reader testing remain open.
4. **Minor Open Code Quirks:** Dashboard's `"Review Rx Queue"` link (`pages/dashboard.php:62`) passes `filter_rx=needs_review` instead of `prescription=needs_review` (`pages/orders.php:109`); unauthorized customer redirect in `pages/order_detail.php:112` calls `redirect(url('orders'))` instead of `redirect('orders')`; admin user creation/reset (`actions.php:388,423`) uses default Bcrypt cost `10` instead of `cost => 12` (`actions.php:93`).
5. **Automated Test Coverage Boundaries:** `tests/run_tests.php` does not include automated SQLi fuzzing, CSRF rejection tests, Bcrypt work-factor assertions, or `order_detail.php?view_rx=1` streaming IDOR tests.
