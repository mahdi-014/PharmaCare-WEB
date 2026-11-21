# PharmaCare — Automated Browser Testing & Screenshot Evidence Collection Report

**Project:** PharmaCare — Pharmacy Management & Online Ordering System  
**Course Context:** CIS324 Web Engineering Semester Project  
**Date of Execution:** November 20, 2026  
**Execution Environment:** PHP 8.3.36 Built-in Server (`http://127.0.0.1:8000`), MariaDB 10.4.32 (`127.0.0.1:3306`), Microsoft Edge Headless (Chromium Engine, CDP Protocol)  
**Database Target:** `pharmacare_test` (Isolated Test Database)  
**Database Account:** `pharmacare_test_user` (Restricted `SELECT, INSERT, UPDATE` privileges)  
**Output Directory:** [`docs/screenshots/`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/docs/screenshots/)  

---

## 1. Executive Summary & Collection Metrics

All **14 required evidence screenshots** have been successfully captured using automated browser testing driven by the Chrome DevTools Protocol (CDP). Every screenshot adheres strictly to the target viewport of **1440 × 900 pixels**, captures genuine application states, contains zero mocked or synthetic elements, and maintains 100% data privacy.

| Metric | Value | Verification Status |
| :--- | :---: | :--- |
| **Total Required Evidence Items** | **14** | Complete scope as specified |
| **Successfully Captured Screenshots** | **14** | Verified on disk (`.png`, non-zero, valid image headers) |
| **Missing / Failed Screenshots** | **0** | None (100% completion rate) |
| **Viewport Resolution Consistency** | **1440 × 900** | 100% identical dimensions across all 14 images |
| **Pages Requiring Manual Intervention** | **0** | Fully automated via isolated academic test accounts |
| **Blocked / Unavailable Routes** | **0** | All target pages, phpMyAdmin, and tools accessible |
| **Database Mutations Executed** | **0** | Zero DDL/schema changes; zero destructive operations |
| **Orders / Payments Submitted** | **0** | Zero mock orders placed; checkout viewed safely |
| **Prescriptions Uploaded** | **0** | Zero private patient files transmitted |

---

## 2. Complete Evidence Inventory & Verification Table

| # | Screenshot Filename | Source URL / Target Route | Dimensions | File Size | MD5 Checksum | Description & Authenticity Verification |
| :-: | :--- | :--- | :-: | :-: | :---: | :--- |
| **01** | [`01_Login.png`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/docs/screenshots/01_Login.png) | `index.php?page=login` | 1440 × 900 | 457,715 B | `165644b010e0a95234a2028a80207e2b` | Clean guest login card with username, password, CSRF protection, and register link. |
| **02** | [`02_Register.png`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/docs/screenshots/02_Register.png) | `index.php?page=register` | 1440 × 900 | 474,227 B | `e2db863bed4f3bf9300858c70c641e65` | Patient registration view with full name, email, phone, address, and credentials. |
| **03** | [`03_Dashboard.png`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/docs/screenshots/03_Dashboard.png) | `index.php?page=dashboard` | 1440 × 900 | 276,837 B | `1a572e8e6db2c6c4ee8bb514256cd3b9` | Administrator dashboard showing total revenue, inventory counts, and low-stock alerts. |
| **04** | [`04_Medicine_Management.png`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/docs/screenshots/04_Medicine_Management.png) | `index.php?page=medicines` | 1440 × 900 | 284,302 B | `79ed1fef4af2a5a5262167f19ebb4d80` | Dispensary medicine inventory table with batch numbers, dosage types, and stock levels. |
| **05** | [`05_Category_Supplier.png`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/docs/screenshots/05_Category_Supplier.png) | `index.php?page=medicines&tab=categories` | 1440 × 900 | 270,738 B | `dffe2068afdad8a22ed6241010e0d3b4` | Therapeutic categories management interface and supplier navigation tab. |
| **06** | [`06_Medicine_Search.png`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/docs/screenshots/06_Medicine_Search.png) | `index.php?page=catalog&search=Napa` | 1440 × 900 | 274,958 B | `8986c850843b2b7596d7751f531bcf0e` | Live medicine search results filtered by "Napa", displaying stock and pricing. |
| **07** | [`07_Cart.png`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/docs/screenshots/07_Cart.png) | `index.php?page=cart` | 1440 × 900 | 233,606 B | `f1131ab1b1965ee400fb719aa31644cd` | Customer shopping cart containing Ace Plus and Torax 10 with prescription notice. |
| **08** | [`08_Checkout.png`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/docs/screenshots/08_Checkout.png) | `index.php?page=cart#checkout` | 1440 × 900 | 233,955 B | `9c855b50e1bc24552dcb907053581177` | Shipping & Order Placement checkout form with delivery address and COD selection. |
| **09** | [`09_Order_Management.png`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/docs/screenshots/09_Order_Management.png) | `index.php?page=orders` | 1440 × 900 | 216,706 B | `92e43cb9c25cd084bd96da7ef822ca8c` | Staff/Admin Order Fulfillment Queue with status filters and metric summary cards. |
| **10** | [`10_Prescription.png`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/docs/screenshots/10_Prescription.png) | `index.php?page=cart#rx` | 1440 × 900 | 228,311 B | `89927ed9dc4b4095df4e178a5c64c34f` | Prescription upload and compliance interface highlighting doctor's script notice. |
| **11** | [`11_User_Management.png`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/docs/screenshots/11_User_Management.png) | `index.php?page=users` | 1440 × 900 | 266,328 B | `d0e2cd1fcf80775a3ce95d5cd794c7bd` | Administrator user management table displaying account roles and status controls. |
| **12** | [`12_Database_Structure.png`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/docs/screenshots/12_Database_Structure.png) | `http://127.0.0.1/phpmyadmin/` | 1440 × 900 | 235,941 B | `b0a15275ae7cf70118eb3fe2c38237ca` | phpMyAdmin database structure interface showing all 10 normalized InnoDB tables. |
| **13** | [`13_ER_Diagram.png`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/docs/screenshots/13_ER_Diagram.png) | `docs/PharmaCare_ER_Diagram.pdf` | 1440 × 900 | 234,614 B | `d7e863b2524c326bbf5845d573253aba` | High-resolution render of the complete A3 Landscape Entity-Relationship Diagram. |
| **14** | [`14_Test_Results.png`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/docs/screenshots/14_Test_Results.png) | `tests/run_tests.php` | 1440 × 900 | 100,892 B | `13a0157e5a23ecd96f53c7ecbc4e7d54` | Terminal output of the QA Regression Suite executed against `pharmacare_test`. |

---

## 3. Safety, Security & Compliance Audit

1. **Database Isolation & Restricted Privilege Enforcement:**
   - The PHP development server was launched with explicit environment variables:
     `DB_HOST=127.0.0.1`, `DB_PORT=3306`, `DB_NAME=pharmacare_test`, `DB_USER=pharmacare_test_user`.
   - The restricted `pharmacare_test_user` account holds only `SELECT, INSERT, UPDATE` on `pharmacare_test`.* with **zero privileges on the production database** and zero global administrative permissions.
   - Database passwords were kept strictly within secure local scratch memory and never printed, logged, or exposed in output.

2. **Source Code & Schema Immutability:**
   - Zero files in the application source tree ([`app/`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/app/), [`config/`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/config/), [`includes/`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/includes/), [`pages/`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/pages/)) were modified.
   - The database schema in [`database/pharmacare.sql`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/database/pharmacare.sql) was unmodified.
   - The Git working directory remains clean with zero unstaged changes to tracked files.

3. **Confidentiality & Non-Destructive Operation:**
   - No real patient prescriptions or PII were uploaded. The prescription upload interface was captured in its authentic pre-submission state.
   - No mock orders were finalized into the database; cart items were maintained solely in transient session memory.
   - All screenshots are genuine browser captures rendered at native 1440 × 900 resolution without mockups or synthetic overlays.
