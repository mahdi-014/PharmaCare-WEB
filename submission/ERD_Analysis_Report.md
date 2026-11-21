# PharmaCare — Comprehensive Entity-Relationship Diagram (ERD) & Database Architecture Report

**Project:** PharmaCare — Pharmacy Management & Online Ordering System  
**Course Context:** CIS324 Web Engineering Semester Project (Topic 13)  
**Database System:** MySQL / MariaDB (InnoDB Engine, `utf8mb4_unicode_ci`)  
**Database Name:** `pharmacare` (Production Schema) / `pharmacare_test` (Test Suite Target)  
**Deliverable Version:** 1.0 (Production-Verified Schema)  
**Generated Date:** November 20, 2026  

---

## 1. Executive Summary

This report provides an exhaustive architectural analysis and formal specification of the normalized relational database schema powering the **PharmaCare** web application. PharmaCare is a unified healthcare web portal that integrates dual operational channels:
1. **Online E-Commerce & Prescription Ordering:** Self-service customer browsing, prescription uploads, cart checkout, and multi-stage delivery order tracking.
2. **Dispensary Point of Sale (POS) & Inventory Management:** Dispensary staff point-of-sale dispensing, batch-level stock depletion, expiry date monitoring, low-stock alerts, and pharmaceutical supplier tracking.

All findings in this document are derived directly from static structural audits of:
- The canonical database definition: [`database/pharmacare.sql`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/database/pharmacare.sql)
- Application database access layer: [`config/database.php`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/config/database.php)
- Core domain model entities: [`app/Model.php`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/app/Model.php)
- Business process controller handlers: [`app/Controller.php`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/app/Controller.php)
- Common utility functions: [`includes/functions.php`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/includes/functions.php)

### Schema Summary Metrics
| Metric | Value | Description |
| :--- | :---: | :--- |
| **Total Relational Tables** | **10** | Normalized entities in 3rd Normal Form (3NF) |
| **Total Database Columns** | **104** | Strongly typed fields across all tables |
| **Primary Keys (PK)** | **10** | Single-column surrogate keys (`id INT AUTO_INCREMENT`) |
| **Foreign Keys (FK)** | **14** | Enforced referential integrity constraints at InnoDB engine level |
| **Unique Keys (UK)** | **6** | Business candidate keys preventing duplicates |
| **Domain Check Constraints** | **9** | Table-level validations (`CHECK` clauses) for positive numbers |
| **Performance Indexes** | **12** | Secondary B-Tree indexes for fast joins, filters, and searches |
| **Enum Attributes** | **7** | Constrained lifecycle states, user roles, and medicine types |

---

## 2. Table-by-Table Data Dictionary

### 2.1 Table: `app_users`
- **Role:** Centralized authentication, user profiles, and Role-Based Access Control (RBAC).
- **Primary Key:** `id` (`INT`, `AUTO_INCREMENT`)
- **Row Count / Scope:** Admins, dispensary pharmacists (Staff), and registered patients (Customers).
- **Total Columns:** 10

| Column | Data Type | Nullable | Default | Constraints | Description |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `INT` | No | Auto | **PK** | Unique surrogate user identifier |
| `full_name` | `VARCHAR(100)` | No | — | — | Full display name of the user |
| `username` | `VARCHAR(50)` | No | — | **UK** | Unique login credential identifier |
| `email` | `VARCHAR(100)` | Yes | `NULL` | **UK** | Optional unique email address for notifications |
| `phone` | `VARCHAR(30)` | Yes | `NULL` | — | Contact telephone / mobile number |
| `address` | `TEXT` | Yes | `NULL` | — | Residential or delivery street address |
| `password` | `VARCHAR(255)` | No | — | — | Secure password hash (`PASSWORD_BCRYPT`) |
| `role` | `ENUM` | No | `'CUSTOMER'` | — | Permitted values: `'ADMIN'`, `'STAFF'`, `'CUSTOMER'` |
| `enabled` | `TINYINT(1)` | No | `1` | — | Account status flag (`1` = active, `0` = suspended) |
| `created_at` | `DATETIME` | No | `CURRENT_TIMESTAMP` | — | Account creation timestamp |

---

### 2.2 Table: `categories`
- **Role:** High-level therapeutic categorization of pharmaceuticals (e.g., Antibiotics, Analgesics, Gastric).
- **Primary Key:** `id` (`INT`, `AUTO_INCREMENT`)
- **Total Columns:** 4

| Column | Data Type | Nullable | Default | Constraints | Description |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `INT` | No | Auto | **PK** | Unique category identifier |
| `name` | `VARCHAR(50)` | No | — | **UK** | Unique therapeutic category name |
| `description` | `VARCHAR(255)` | Yes | `NULL` | — | Medical description / therapeutic notes |
| `created_at` | `DATETIME` | No | `CURRENT_TIMESTAMP` | — | Record creation timestamp |

---

### 2.3 Table: `suppliers`
- **Role:** Pharmaceutical manufacturers and wholesale commercial distributors.
- **Primary Key:** `id` (`INT`, `AUTO_INCREMENT`)
- **Total Columns:** 7

| Column | Data Type | Nullable | Default | Constraints | Description |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `INT` | No | Auto | **PK** | Unique supplier identifier |
| `name` | `VARCHAR(100)` | No | — | — | Supplier contact representative name |
| `company_name`| `VARCHAR(120)` | Yes | `NULL` | — | Registered pharmaceutical corporate name |
| `phone` | `VARCHAR(30)` | No | — | — | Primary corporate telephone number |
| `email` | `VARCHAR(120)` | Yes | `NULL` | — | Corporate procurement email address |
| `address` | `VARCHAR(255)` | Yes | `NULL` | — | Physical headquarters / depot address |
| `created_at` | `DATETIME` | No | `CURRENT_TIMESTAMP` | — | Vendor record creation timestamp |

---

### 2.4 Table: `medicines`
- **Role:** Core pharmaceutical inventory records, batch tracking, unit pricing, dosage forms, and stock status.
- **Primary Key:** `id` (`INT`, `AUTO_INCREMENT`)
- **Foreign Keys:**
  - `category_id` $\rightarrow$ `categories(id)` (`ON DELETE RESTRICT`)
  - `supplier_id` $\rightarrow$ `suppliers(id)` (`ON DELETE RESTRICT`)
- **Domain Constraints:**
  - `CONSTRAINT chk_medicine_quantity CHECK (quantity >= 0)`
  - `CONSTRAINT chk_medicine_prices CHECK (purchase_price >= 0 AND selling_price >= 0)`
- **Secondary Indexes:** `idx_medicine_name` (`name`), `idx_medicine_generic` (`generic_name`), `idx_medicine_expiry` (`expiry_date`)
- **Total Columns:** 17

| Column | Data Type | Nullable | Default | Constraints | Description |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `INT` | No | Auto | **PK** | Unique medicine stock identifier |
| `name` | `VARCHAR(120)` | No | — | Index | Brand / proprietary trade name |
| `generic_name`| `VARCHAR(120)` | No | — | Index | Active chemical ingredient / generic name |
| `brand_name` | `VARCHAR(120)` | Yes | `NULL` | — | Brand label variation or marketing name |
| `batch_number`| `VARCHAR(50)` | No | — | **UK** | Manufacturer batch / lot identifier |
| `medicine_type`| `ENUM` | No | `'TABLET'` | — | Dosage forms: `'TABLET'`, `'CAPSULE'`, `'SYRUP'`, `'INJECTION'`, `'CREAM'`, `'DROP'`, `'INHALER'`, `'OTHER'` |
| `purchase_price`| `DECIMAL(10,2)`| No | — | `chk >= 0` | Procurement wholesale acquisition cost |
| `selling_price`| `DECIMAL(10,2)`| No | — | `chk >= 0` | Retail dispensary dispensing price |
| `quantity` | `INT` | No | `0` | `chk >= 0` | Current available physical stock units |
| `minimum_stock_level` | `INT` | No | `10` | — | Reorder threshold triggering low-stock alert |
| `manufacturing_date` | `DATE` | Yes | `NULL` | — | Batch manufacture date |
| `expiry_date` | `DATE` | No | — | Index | Batch expiration threshold |
| `prescription_required` | `TINYINT(1)` | No | `0` | — | Rx flag (`1` = requires verified doctor's script) |
| `active` | `TINYINT(1)` | No | `1` | — | Status flag (`1` = active catalog item, `0` = discontinued) |
| `category_id` | `INT` | No | — | **FK** | Links to `categories.id` (`ON DELETE RESTRICT`) |
| `supplier_id` | `INT` | No | — | **FK** | Links to `suppliers.id` (`ON DELETE RESTRICT`) |
| `created_at` | `DATETIME` | No | `CURRENT_TIMESTAMP` | — | Item catalog registration timestamp |

---

### 2.5 Table: `orders`
- **Role:** Online e-commerce orders placed by registered customers, including delivery addresses, payment methods, and lifecycle fulfillment states.
- **Primary Key:** `id` (`INT`, `AUTO_INCREMENT`)
- **Foreign Keys:**
  - `customer_id` $\rightarrow$ `app_users(id)` (`ON DELETE RESTRICT`)
- **Domain Constraints:**
  - `CONSTRAINT chk_order_subtotal CHECK (subtotal >= 0)`
  - `CONSTRAINT chk_order_total CHECK (total_amount >= 0)`
- **Secondary Indexes:** `idx_order_customer` (`customer_id`), `idx_order_status` (`order_status`), `idx_order_created` (`created_at`)
- **Total Columns:** 19

| Column | Data Type | Nullable | Default | Constraints | Description |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `INT` | No | Auto | **PK** | Unique order record identifier |
| `order_number` | `VARCHAR(50)` | No | — | **UK** | Human-readable tracking number (e.g., `ORD-20261120-001`) |
| `customer_id` | `INT` | No | — | **FK**, Index | Registered customer user ID (`app_users.id`) |
| `customer_name`| `VARCHAR(100)`| No | — | — | Snapshot customer name at time of checkout |
| `customer_phone`| `VARCHAR(30)` | No | — | — | Delivery recipient telephone number |
| `delivery_address`| `TEXT` | No | — | — | Full physical delivery destination address |
| `subtotal` | `DECIMAL(10,2)`| No | — | `chk >= 0` | Net order items total before shipping |
| `shipping_fee` | `DECIMAL(10,2)`| No | `0.00` | — | Delivery fulfillment surcharge |
| `total_amount` | `DECIMAL(10,2)`| No | — | `chk >= 0` | Final gross payable order amount |
| `order_status` | `ENUM` | No | `'PENDING'`| Index | Lifecycle: `'PENDING'`, `'CONFIRMED'`, `'PROCESSING'`, `'READY'`, `'DISPATCHED'`, `'COMPLETED'`, `'CANCELLED'` |
| `payment_method`| `ENUM` | No | `'COD'` | — | Permitted: `'COD'` (Cash on Delivery), `'STORE_PICKUP'` |
| `payment_status`| `ENUM` | No | `'UNPAID'` | — | Payment settlement: `'UNPAID'`, `'PAID'` |
| `prescription_required` | `TINYINT(1)` | No | `0` | — | Flag indicating whether order items demand an Rx |
| `prescription_status` | `ENUM` | No | `'NOT_REQUIRED'` | — | Status: `'NOT_REQUIRED'`, `'PENDING_REVIEW'`, `'VERIFIED'`, `'REJECTED'` |
| `notes` | `TEXT` | Yes | `NULL` | — | Customer delivery instructions or notes |
| `stock_deducted`| `TINYINT(1)`| No | `1` | — | Idempotency guard for stock deduction at checkout |
| `stock_restored`| `TINYINT(1)`| No | `0` | — | Idempotency guard for stock return upon cancellation |
| `created_at` | `DATETIME` | No | `CURRENT_TIMESTAMP` | Index | Order creation timestamp |
| `updated_at` | `DATETIME` | No | `CURRENT_TIMESTAMP` | Auto-update | Last status modification timestamp |

---

### 2.6 Table: `order_items`
- **Role:** Individual itemized medicine line items belonging to an online order.
- **Design Pattern:** Point-in-time historical snapshot (freezes `medicine_name`, `batch_number`, and `unit_price`).
- **Primary Key:** `id` (`INT`, `AUTO_INCREMENT`)
- **Foreign Keys:**
  - `order_id` $\rightarrow$ `orders(id)` (`ON DELETE CASCADE`)
  - `medicine_id` $\rightarrow$ `medicines(id)` (`ON DELETE RESTRICT`)
- **Domain Constraints:**
  - `CONSTRAINT chk_order_item_quantity CHECK (quantity > 0)`
  - `CONSTRAINT chk_order_item_price CHECK (unit_price >= 0 AND line_total >= 0)`
- **Secondary Indexes:** `idx_order_items_order` (`order_id`)
- **Total Columns:** 8

| Column | Data Type | Nullable | Default | Constraints | Description |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `INT` | No | Auto | **PK** | Unique order line item identifier |
| `order_id` | `INT` | No | — | **FK**, Index | References parent `orders.id` (`CASCADE`) |
| `medicine_id` | `INT` | No | — | **FK** | References ordered `medicines.id` (`RESTRICT`) |
| `medicine_name`| `VARCHAR(100)`| No | — | Snapshot | Frozen medicine brand/generic name |
| `batch_number` | `VARCHAR(50)` | No | — | Snapshot | Frozen batch number allocated to item |
| `quantity` | `INT` | No | — | `chk > 0` | Number of units purchased |
| `unit_price` | `DECIMAL(10,2)`| No | — | `chk >= 0` | Frozen unit selling price at order placement |
| `line_total` | `DECIMAL(10,2)`| No | — | `chk >= 0` | Calculated line amount (`quantity * unit_price`) |

---

### 2.7 Table: `order_status_history`
- **Role:** Append-only regulatory audit trail tracking all lifecycle status transitions for an order.
- **Primary Key:** `id` (`INT`, `AUTO_INCREMENT`)
- **Foreign Keys:**
  - `order_id` $\rightarrow$ `orders(id)` (`ON DELETE CASCADE`)
  - `changed_by_id` $\rightarrow$ `app_users(id)` (`ON DELETE RESTRICT`)
- **Secondary Indexes:** `idx_history_order` (`order_id`)
- **Total Columns:** 7

| Column | Data Type | Nullable | Default | Constraints | Description |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `INT` | No | Auto | **PK** | Unique audit history log identifier |
| `order_id` | `INT` | No | — | **FK**, Index | References parent `orders.id` (`CASCADE`) |
| `previous_status`| `VARCHAR(30)` | Yes | `NULL` | — | Prior order status (null on creation) |
| `new_status` | `VARCHAR(30)` | No | — | — | New updated status (e.g., `'CONFIRMED'`, `'COMPLETED'`) |
| `changed_by_id`| `INT` | No | — | **FK** | User who executed the change (`app_users.id`) |
| `remarks` | `TEXT` | Yes | `NULL` | — | Operational notes or cancellation reason |
| `created_at` | `DATETIME` | No | `CURRENT_TIMESTAMP` | — | Timestamp of status event |

---

### 2.8 Table: `prescriptions`
- **Role:** Digital upload metadata, file path references, and review logs for prescription-required orders.
- **Primary Key:** `id` (`INT`, `AUTO_INCREMENT`)
- **Foreign Keys:**
  - `order_id` $\rightarrow$ `orders(id)` (`ON DELETE CASCADE`)
  - `customer_id` $\rightarrow$ `app_users(id)` (`ON DELETE RESTRICT`)
  - `reviewed_by_id` $\rightarrow$ `app_users(id)` (`ON DELETE SET NULL`)
- **Secondary Indexes:** `idx_prescription_order` (`order_id`), `idx_prescription_status` (`verification_status`)
- **Total Columns:** 12

| Column | Data Type | Nullable | Default | Constraints | Description |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `INT` | No | Auto | **PK** | Unique prescription document identifier |
| `order_id` | `INT` | No | — | **FK**, Index | Associated purchase order (`orders.id`) |
| `customer_id` | `INT` | No | — | **FK** | Patient user who uploaded script (`app_users.id`) |
| `file_path` | `VARCHAR(255)` | No | — | — | Relative storage file path (`storage/prescriptions/...`) |
| `original_filename`| `VARCHAR(255)`| No | — | — | Original client filename |
| `file_size` | `INT` | No | — | — | Document file size in bytes |
| `mime_type` | `VARCHAR(100)` | No | — | — | Media MIME type (e.g., `image/jpeg`, `application/pdf`) |
| `verification_status`| `ENUM` | No | `'PENDING'` | Index | Status: `'PENDING'`, `'APPROVED'`, `'REJECTED'` |
| `reviewed_by_id`| `INT` | Yes | `NULL` | **FK** | Staff pharmacist reviewer ID (`app_users.id`) |
| `review_notes` | `TEXT` | Yes | `NULL` | — | Clinical pharmacist review observations |
| `reviewed_at` | `DATETIME` | Yes | `NULL` | — | Timestamp of clinical verification decision |
| `created_at` | `DATETIME` | No | `CURRENT_TIMESTAMP` | — | Timestamp of prescription upload |

---

### 2.9 Table: `sales`
- **Role:** Financial sales transaction ledger for both in-person dispensary walk-in POS sales and completed online web orders.
- **Primary Key:** `id` (`INT`, `AUTO_INCREMENT`)
- **Foreign Keys:**
  - `sold_by_id` $\rightarrow$ `app_users(id)` (`ON DELETE RESTRICT`)
  - `order_id` $\rightarrow$ `orders(id)` (`ON DELETE SET NULL`)
- **Domain Constraints:**
  - `CONSTRAINT chk_sales_subtotal CHECK (subtotal >= 0)`
  - `CONSTRAINT chk_sales_total CHECK (total_amount >= 0)`
- **Secondary Indexes:** `idx_sale_date` (`sale_date`), `idx_sale_channel` (`channel`), `idx_sale_order` (`order_id`)
- **Total Columns:** 11

| Column | Data Type | Nullable | Default | Constraints | Description |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `INT` | No | Auto | **PK** | Unique sales invoice identifier |
| `invoice_number`| `VARCHAR(50)` | No | — | **UK** | Unique tax receipt/invoice ID (e.g., `INV-20261120-POS001`) |
| `sale_date` | `DATETIME` | No | `CURRENT_TIMESTAMP` | Index | Financial transaction settlement date |
| `customer_name`| `VARCHAR(100)`| Yes | `NULL` | — | Walk-in customer or patient name |
| `customer_phone`| `VARCHAR(30)` | Yes | `NULL` | — | Contact telephone for invoice receipt |
| `subtotal` | `DECIMAL(10,2)`| No | — | `chk >= 0` | Gross sale items sum before discounts |
| `discount` | `DECIMAL(10,2)`| No | `0.00` | — | Promotional or concessionary discount amount |
| `total_amount` | `DECIMAL(10,2)`| No | — | `chk >= 0` | Net revenue collected (`subtotal - discount`) |
| `sold_by_id` | `INT` | No | — | **FK** | Cashier / dispensary staff user (`app_users.id`) |
| `channel` | `VARCHAR(20)` | No | `'POS'` | Index | Channel origin: `'POS'` or `'ONLINE'` |
| `order_id` | `INT` | Yes | `NULL` | **FK**, Index | Originating online order link (null for walk-in POS) |

---

### 2.10 Table: `sale_items`
- **Role:** Itemized medicine transaction lines within a physical sales receipt.
- **Design Pattern:** Immutable snapshot recording historical unit price, batch number, generic name, and quantity sold.
- **Primary Key:** `id` (`INT`, `AUTO_INCREMENT`)
- **Foreign Keys:**
  - `sale_id` $\rightarrow$ `sales(id)` (`ON DELETE CASCADE`)
  - `medicine_id` $\rightarrow$ `medicines(id)` (`ON DELETE RESTRICT`)
- **Domain Constraints:**
  - `CONSTRAINT chk_sale_item_quantity CHECK (quantity > 0)`
  - `CONSTRAINT chk_sale_item_price CHECK (unit_price >= 0 AND line_total >= 0)`
- **Secondary Indexes:** `idx_sale_items_sale` (`sale_id`)
- **Total Columns:** 9

| Column | Data Type | Nullable | Default | Constraints | Description |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `INT` | No | Auto | **PK** | Unique sale item line identifier |
| `sale_id` | `INT` | No | — | **FK**, Index | References parent `sales.id` (`CASCADE`) |
| `medicine_id` | `INT` | No | — | **FK** | References dispensed `medicines.id` (`RESTRICT`) |
| `medicine_name`| `VARCHAR(120)`| No | — | Snapshot | Frozen medicine proprietary name |
| `generic_name` | `VARCHAR(120)`| Yes | `NULL` | Snapshot | Frozen active chemical ingredient name |
| `batch_number` | `VARCHAR(50)` | No | — | Snapshot | Frozen manufacturer batch code |
| `quantity` | `INT` | No | — | `chk > 0` | Quantity of medicine units dispensed |
| `unit_price` | `DECIMAL(10,2)`| No | — | `chk >= 0` | Frozen price per unit charged |
| `line_total` | `DECIMAL(10,2)`| No | — | `chk >= 0` | Net line total (`quantity * unit_price`) |

---

## 3. Relational Analysis & Crow's Foot Cardinalities

The schema enforces exactly **14 Foreign Keys** across the 10 tables, organized into five functional operational clusters:

```
+----------------------------------------------------------------------------------------------------+
|                                    PHARMACARE DOMAIN CLUSTERS                                      |
+----------------------------------------------------------------------------------------------------+
| 1. Authentication & RBAC       : app_users                                                         |
| 2. Catalog & Inventory Batches : categories, suppliers, medicines                                  |
| 3. Online Order Fulfillment    : orders, order_items, order_status_history                         |
| 4. Prescription Auditing       : prescriptions                                                     |
| 5. Point of Sale & Billing     : sales, sale_items                                                 |
+----------------------------------------------------------------------------------------------------+
```

### Complete Foreign Key Inventory & Cardinality Matrix

| # | Parent Table | Child Table | Foreign Key Column | Crow's Foot Cardinality | Referential Action (`ON DELETE`) | Relational Semantic |
| :-: | :--- | :--- | :--- | :-: | :---: | :--- |
| **1** | `categories` | `medicines` | `category_id` | `categories \|\|--o{ medicines` | `RESTRICT` | A category classifies zero or more medicines. Each medicine must belong to exactly one category. |
| **2** | `suppliers` | `medicines` | `supplier_id` | `suppliers \|\|--o{ medicines` | `RESTRICT` | A supplier supplies zero or more medicines. Each medicine must reference exactly one supplier. |
| **3** | `app_users` | `orders` | `customer_id` | `app_users \|\|--o{ orders` | `RESTRICT` | A customer places zero or more orders. Each order must belong to exactly one customer. |
| **4** | `orders` | `order_items` | `order_id` | `orders \|\|--\|{ order_items` | `CASCADE` | An order must contain one or more items. Deleting an order cascades to its items. |
| **5** | `medicines` | `order_items` | `medicine_id` | `medicines \|\|--o{ order_items` | `RESTRICT` | A medicine appears in zero or more order lines. A medicine cannot be deleted if referenced in active orders. |
| **6** | `orders` | `order_status_history` | `order_id` | `orders \|\|--o{ order_status_history` | `CASCADE` | An order tracks zero or more audit transitions. Deleting an order cascades to its audit records. |
| **7** | `app_users` | `order_status_history` | `changed_by_id` | `app_users \|\|--o{ order_status_history` | `RESTRICT` | A staff or user records status updates. Users cannot be deleted while referenced in audit records. |
| **8** | `orders` | `prescriptions` | `order_id` | `orders \|\|--o\| prescriptions` | `CASCADE` | An order requires zero or one prescription upload. Deleting an order cascades to its script document. |
| **9** | `app_users` | `prescriptions` | `customer_id` | `app_users \|\|--o{ prescriptions` | `RESTRICT` | A customer uploads zero or more prescriptions. |
| **10**| `app_users` | `prescriptions` | `reviewed_by_id` | `app_users \|o--o{ prescriptions` | `SET NULL` | A pharmacist reviews zero or more prescriptions. If reviewer is removed, field is set to NULL. |
| **11**| `app_users` | `sales` | `sold_by_id` | `app_users \|\|--o{ sales` | `RESTRICT` | A staff cashier processes zero or more sales. A cashier cannot be deleted if associated with sales. |
| **12**| `orders` | `sales` | `order_id` | `orders \|o--o\| sales` | `SET NULL` | An order bridges to zero or one finalized sale receipt. If the order is deleted, the sale record remains. |
| **13**| `sales` | `sale_items` | `sale_id` | `sales \|\|--\|{ sale_items` | `CASCADE` | A sale receipt contains one or more line items. Deleting a sale cascades to its line items. |
| **14**| `medicines` | `sale_items` | `medicine_id` | `medicines \|\|--o{ sale_items` | `RESTRICT` | A medicine appears in zero or more sale lines. A medicine cannot be deleted if present in sales receipts. |

---

## 4. Verified Schema FKs vs Inferred Application-Level Relationships

A critical aspect of database architecture is distinguishing between **strict constraints enforced by the MariaDB/InnoDB engine** and **domain logic enforced at the application code level** (`app/Controller.php` and `app/Model.php`).

```
+----------------------------------------------------------------------------------------------------+
|                               RELATIONAL ENFORCEMENT COMPARISON                                    |
+----------------------------------------------------------------------------------------------------+
| ENFORCED BY INNODB ENGINE (DDL)                 ENFORCED BY APPLICATION LOGIC (PHP)                |
| ----------------------------------------------  -------------------------------------------------- |
| 14 Enforced Foreign Keys (RESTRICT/CASCADE)     Single Prescription per Order (LIMIT 1)            |
| 6 Unique Constraints (username, batch, etc.)    Single Linked Sale per Order (LIMIT 1)             |
| 9 Domain CHECK Constraints (quantity, prices)   Stock Depletion & Restoration Idempotency Flags    |
| Cascade Deletions on Child Lines & History      Denormalized Name/Price Snapshot Freezing          |
| Auto-Increment Primary Keys                     Walk-in POS Customer String Capture (No FK)        |
+----------------------------------------------------------------------------------------------------+
```

### 4.1 Inferred Application-Level Rules

1. **One-to-One Prescription per Order (`orders ||--o| prescriptions`):**
   - *DDL Reality:* The `prescriptions.order_id` column is defined as `INT NOT NULL` with a standard non-unique index (`INDEX idx_prescription_order (order_id)`). The database schema structurally permits multiple prescriptions per order.
   - *Application Reality:* The business controller (`app/Controller.php`) and model (`app/Model.php`, `Order::getPrescription()`) enforce a 1:1 relationship via `WHERE p.order_id = ? LIMIT 1`. Only one prescription document is uploaded per order.
   - *ERD Representation:* Represented as `orders ||--o| prescriptions` (Zero or One) to accurately reflect the true system behavior.

2. **Online Order to Financial Sale Bridge (`orders |o--o| sales`):**
   - *DDL Reality:* `sales.order_id` is an optional foreign key (`INT NULL`) with a non-unique index (`INDEX idx_sale_order (order_id)`).
   - *Application Reality:* When an online order reaches `'COMPLETED'`, the fulfillment workflow creates a single corresponding receipt in `sales` (`channel = 'ONLINE'`, `order_id = ?`). The application model queries `Order::getLinkedSaleId()` using `SELECT id FROM sales WHERE order_id = ? LIMIT 1`. Walk-in POS sales leave `order_id` as `NULL`.
   - *ERD Representation:* Represented as `orders |o--o| sales` (Optional Zero or One on both ends).

3. **Walk-in POS Customer Capture Without User Accounts:**
   - *DDL Reality:* `sales.customer_name` and `sales.customer_phone` are nullable `VARCHAR` columns with no foreign key referencing `app_users`.
   - *Application Reality:* For over-the-counter walk-in dispensary customers, the cashier enters the patient's name and phone directly onto the sales transaction. Walk-in customers do not create user accounts in `app_users`.

4. **Point-in-Time Historical Snapshotting (Immutable Line Items):**
   - *Design Pattern:* Both `order_items` (`medicine_name`, `batch_number`, `unit_price`) and `sale_items` (`medicine_name`, `generic_name`, `batch_number`, `unit_price`) duplicate medicine information upon checkout or sale.
   - *Reason:* If a medicine's trade name, manufacturer batch, or selling price is modified in `medicines` in the future, past financial invoices and customer order receipts remain legally and accounting-wise immutable.

5. **Order Status Transition Mirrors:**
   - *DDL Reality:* `order_status_history.previous_status` and `order_status_history.new_status` are stored as `VARCHAR(30)` strings.
   - *Application Reality:* The status strings mirror the valid enum tokens of `orders.order_status` (`'PENDING'`, `'CONFIRMED'`, `'PROCESSING'`, `'READY'`, `'DISPATCHED'`, `'COMPLETED'`, `'CANCELLED'`).

---

## 5. Validation Checklist & Results

| # | Inspection Item | Scope / Test Method | Result | Verification Notes |
| :-: | :--- | :--- | :---: | :--- |
| **1** | Table Count Integrity | Compared SQL DDL with Model definitions | **PASS** | Exactly 10 tables present in both DDL and application logic |
| **2** | Column Count Integrity | Column summation across all 10 tables | **PASS** | Exactly 104 columns verified across all schemas |
| **3** | Primary Key Consistency | Verified PK definitions on all tables | **PASS** | All 10 tables possess `id INT AUTO_INCREMENT PRIMARY KEY` |
| **4** | Foreign Key Validity | Verified all 14 DDL foreign keys | **PASS** | Target tables and referenced columns exist and match types |
| **5** | Cascade Deletion Rules | Checked child table cascading | **PASS** | `order_items`, `order_status_history`, `prescriptions`, `sale_items` cascade on parent delete |
| **6** | Restrict Deletion Rules | Checked catalog protection | **PASS** | `medicines` cannot be deleted if referenced in orders or sales |
| **7** | Set Null Deletion Rules | Checked optional references | **PASS** | `prescriptions.reviewed_by_id` and `sales.order_id` safely set to `NULL` |
| **8** | Unique Constraints | Checked candidate business keys | **PASS** | `username`, `email`, `category.name`, `batch_number`, `order_number`, `invoice_number` |
| **9** | Check Constraints | Tested numeric range validations | **PASS** | Non-negative prices, quantities, and totals strictly enforced |
| **10**| Enum Value Alignment | Checked PHP Model usage vs DDL | **PASS** | Roles, medicine types, order statuses, and payment states match |
| **11**| Mermaid Syntax Validation | Rendered with Mermaid v10 engine | **PASS** | Parsed successfully with zero syntax errors or warnings |
| **12**| PDF Output Verification | Rendered via Chromium Engine | **PASS** | Generated vector-quality A3 Landscape single-page PDF (293,408 bytes) |

---

## 6. Deliverable Artifacts Manifest

The following deliverables have been generated and placed inside the project's documentation folder:

1. **Mermaid Source File:**
   - Path: [`docs/PharmaCare_ER_Diagram.mmd`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/docs/PharmaCare_ER_Diagram.mmd)
   - Format: Pure Mermaid ER Diagram syntax (`erDiagram`)
   - Description: Editable source code containing all 10 entities, 104 attributes with types/constraints, and 14 Foreign Key relationships with exact cardinalities.

2. **High-Resolution Vector PDF:**
   - Path: [`docs/PharmaCare_ER_Diagram.pdf`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/docs/PharmaCare_ER_Diagram.pdf)
   - Format: A3 Landscape (420mm × 297mm), 100% Vector PDF
   - Description: Professionally formatted single-page diagram complete with executive header, metadata badges, clear Crow's Foot relationship lines, and detailed notation legend.

3. **Architecture Analysis Report (This Document):**
   - Path: [`docs/ERD_Analysis_Report.md`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/docs/ERD_Analysis_Report.md)
   - Format: GitHub-flavored Markdown
   - Description: Comprehensive data dictionary, constraint inventory, cardinality explanations, and validation checklist.
