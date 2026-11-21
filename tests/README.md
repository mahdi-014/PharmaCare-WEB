# PharmaCare — Automated Verification & QA Test Suite

**Course:** CIS324 Web Engineering — Fall 2026  
**Target Runner:** `tests/run_tests.php`  
**Execution Environment:** CLI-only (`php tests/run_tests.php`)  
**Target Test Database:** `pharmacare_test` (Isolated from production `pharmacare`)  

---

## 1. Safety Guards & Execution Policy

The test suite enforces strict pre-flight environment checks to prevent accidental execution against production data:

1. **CLI Only:** Web browser invocations return `403 Forbidden` immediately.
2. **Database Name Guard:** The environment variable `DB_NAME` must be explicitly set to `pharmacare_test`. If missing, empty, or set to `pharmacare`, execution aborts immediately.
3. **Restricted Test User:** The environment variable `DB_USER` must be set to `pharmacare_test_user`. This user should hold permissions on `pharmacare_test.*` only.
4. **Local Host Binding:** `DB_HOST` must be `127.0.0.1` and `DB_PORT` must be `3306`.

---

## 2. Test Database Setup (One-Time Setup)

In MySQL/MariaDB CLI or phpMyAdmin:

```sql
-- 1. Create dedicated test database
CREATE DATABASE IF NOT EXISTS pharmacare_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 2. Create restricted test user
CREATE USER IF NOT EXISTS 'pharmacare_test_user'@'localhost' IDENTIFIED BY 'TestPass_2026!';

-- 3. Grant privileges ONLY on test database
GRANT SELECT, INSERT, UPDATE, DELETE ON pharmacare_test.* TO 'pharmacare_test_user'@'localhost';
FLUSH PRIVILEGES;
```

Import schema and seeds into `pharmacare_test`:
```bash
mysql -u pharmacare_test_user -p pharmacare_test < database/pharmacare.sql
```

---

## 3. How to Run the Tests

### Windows PowerShell:
```powershell
$env:DB_HOST="127.0.0.1"
$env:DB_PORT="3306"
$env:DB_NAME="pharmacare_test"
$env:DB_USER="pharmacare_test_user"
$env:DB_PASS="TestPass_2026!"
php tests/run_tests.php
```

### Linux / macOS Bash:
```bash
DB_HOST=127.0.0.1 DB_PORT=3306 DB_NAME=pharmacare_test DB_USER=pharmacare_test_user DB_PASS='TestPass_2026!' php tests/run_tests.php
```

---

## 4. Test Suite Inventory (11 Automated Test Cases)

| Test ID | Category | Component | Description |
| :--- | :--- | :--- | :--- |
| **`TC-1.1`** | Static Lint | PHP Source Files | Verifies syntax (`php -l`) across all application files. |
| **`TC-1.2`** | Hygiene | Root & Config | Checks that no temporary files or credential leaks exist in root. |
| **`SEC-01`** | Security | `e()` Helper | Verifies DOM XSS mitigation via strict HTML entity escaping. |
| **`SEC-02`** | Security | `api/router.php` | Verifies that API error envelopes do not disclose raw SQL or internal exceptions. |
| **`SEC-04`** | Security | `storage/prescriptions/` | Verifies prescription file deletion and rollback hygiene upon transaction failure. |
| **`SEC-05`** | Security | `order_items` Table | Verifies historical order price and batch snapshot immutability. |
| **`TC-3.1`** | Business Logic | `app/InventoryService.php` | Verifies automated FEFO (First-Expiry-First-Out) batch allocation. |
| **`TC-3.2`** | Business Logic | `app/InventoryService.php` | Verifies stock restoration idempotency upon order cancellation. |
| **`TC-3.3`** | State Machine | `app/Controller.php` | Validates order state machine transitions (`PENDING` -> `PROCESSING` -> `SHIPPED` -> `DELIVERED`). |
| **`TC-4.1`** | RBAC Guard | `pages/users.php` | Verifies `require_admin()` guard blocks unprivileged users (`STAFF`, `CUSTOMER`). |
| **`TC-4.2`** | IDOR Defense | `orderStatusApi()` | Verifies that customers cannot query orders belonging to other users. |
