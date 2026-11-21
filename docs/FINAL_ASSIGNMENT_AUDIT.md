# PharmaCare — Comprehensive Final Assignment Audit Report

**Academic Course:** CIS324 Web Engineering — Semester Assignment (Fall 2026)  
**Primary Authority:** `CIS324_Assignment_Fall_2026.pdf` (Daffodil International University, FSIT/CIS)  
**Audit Subject:** `docs/CIS324_Assignment_Report.md`  
**Audited Project Directory:** `D:\PharmaCare-PHP-Complete\PharmaCare-PHP`  
**Supporting Documentation & Evidence Audited:**
- `docs/PharmaCare_System_Architecture.md`
- `docs/PharmaCare_ER_Diagram.pdf` & `docs/PharmaCare_ER_Diagram.png`
- `docs/ERD_Analysis_Report.md`
- `docs/screenshots/SCREENSHOT_COLLECTION_REPORT.md` and 14 captured screenshots in `docs/screenshots/`
- `tests/run_tests.php` (Automated 24-test verification suite)
- `database/pharmacare.sql` (3NF relational schema, 10 tables, 14 foreign keys)
- Source code in `index.php`, `actions.php`, `api/router.php`, `app/`, `includes/`, `pages/`, `config/`

**Audit Completion Date:** November 21, 2026  
**Auditor:** Antigravity Engineering Agent  
**Audit Mode:** Read-Only Audit & Fact-Checking (No source code or report modifications made)

---

## 1. Executive Summary of Audit Findings

A rigorous, line-by-line audit was conducted comparing the existing assignment report ([docs/CIS324_Assignment_Report.md](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/docs/CIS324_Assignment_Report.md)) against the assignment guideline specification (`CIS324_Assignment_Fall_2026.pdf`) and the actual, verified PharmaCare codebase.

### Overall Assessment Verdict
The underlying software engineering and database implementation of PharmaCare is **exceptionally high quality, fully functional, and surpasses all 35-mark criteria** of CIS324:
- The project implements **11 consolidated functional core views** (exceeding the required 5).
- The database contains **10 normalized 3NF tables with 14 foreign keys** (exceeding the required 3).
- Concurrency control is enforced using **pessimistic row locking (`SELECT ... FOR UPDATE`)** with First-Expired-First-Out (**FEFO**) batch allocation.
- A dedicated **JSON Web API Router** (`api/router.php`) powers asynchronous live search and polling via Vanilla JS Fetch.
- Security defenses incorporate Bcrypt (cost 12), timing-safe CSRF tokens, strict session parameters, a 30-minute idle timeout, and quarantined medical prescription file streaming.

### Critical Gaps & Deficiencies in the Existing Written Report
However, the written report ([docs/CIS324_Assignment_Report.md](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/docs/CIS324_Assignment_Report.md)) exhibits **several major omissions, inaccurate claims, and non-compliances with the assignment rubric**:

1. **CRITICAL DEFICIENCY — Total Absence of Embedded Screenshots (Task 2: Q3):**
   - **Rubric Requirement:** Task 2 (Q3) mandates: *"Include screenshots and explain the key code segments."* Submission guidelines mandate: *"A single, consolidated PDF report (ER diagram, architecture diagram, screenshots, and analysis)"*.
   - **Current State:** The report has **ZERO** embedded screenshots (`![...](...)`). While 14 high-resolution screenshots exist on disk in `docs/screenshots/`, not a single image is embedded in the report body. This directly jeopardizes Q3 marks (5 marks).
2. **CRITICAL DEFICIENCY — Missing Complete CRUD on Second Entity (Task 2: Q4):**
   - **Rubric Requirement:** Task 2 (Q4) mandates: *"implement complete CRUD (Create, Read, Update, Delete) operations on at least two entities, using prepared statements."*
   - **Current State:** Section 4.4 only documents Entity 1 (`medicines`), and even for `medicines`, only Create, Read, and Update are shown (Delete is omitted). Entity 2 is completely absent from the CRUD code section, even though the application implements full CRUD on `categories`, `suppliers`, and `app_users`.
3. **MAJOR DEFICIENCY — Outdated & Abbreviated ER Diagram in Report (Task 2: Q4):**
   - **Rubric Requirement:** Task 2 (Q4) mandates: *"present it as an ER diagram with a brief justification."*
   - **Current State:** Section 4.3 presents an older, abbreviated Mermaid ER diagram with truncated fields and missing foreign keys (`prescriptions.customer_id`, `prescriptions.reviewed_by_id`, `sales.order_id`), and lacks any image link or reference to the generated vector PDF (`docs/PharmaCare_ER_Diagram.pdf`) and PNG (`docs/PharmaCare_ER_Diagram.png`).
4. **MAJOR DEFICIENCY — Threat Mitigations Lack Before/After Code & Test Evidence (Task 3: Q5):**
   - **Rubric Requirement:** Task 3 (Q5) mandates: *"show with evidence (before/after code or test cases) how your implementation mitigates each one."*
   - **Current State:** Section 5.3 describes the vulnerability mechanics and explains the mitigation in text, but fails to provide explicit before/after code blocks demonstrating the vulnerable code vs. the secure implementation, and does not cite the automated security test cases in `tests/run_tests.php` (`TC-SEC-01` through `TC-SEC-05`).
5. **FACTUAL INACCURACIES & CODE DISCREPANCIES:**
   - **Delivery Fee Inaccuracy (Line 365):** The report claims a *"Standard Delivery Fee (৳60, waived over ৳1000)"*. In reality, `app/Controller.php:654` sets `$shippingFee = 0.00;` and `database/pharmacare.sql` sets `shipping_fee DECIMAL(10,2) NOT NULL DEFAULT 0.00` (Free Delivery).
   - **Function Inventory Incompleteness (Section 8.2):** The report lists ~20 methods in `Controller.php`, 3 in `InventoryService`, and 14 in `Model.php`. In reality, `Controller.php` contains 30 methods, and `Model.php` contains 32 methods across `User`, `Medicine`, and `Order`.
   - **Architecture Diagram in Q1:** Uses an ASCII text box in Section 1.1 instead of a visual, multi-tiered Mermaid architecture diagram.
   - **Mandatory Submission Naming:** The report cover page and footer list generic repository links (`https://github.com/mahdi-014/PharmaCare-WEB.git`) rather than referencing the university's mandatory naming format `StudentID_Section_CIS324_Project` and `StudentID_Section_CIS324_Assignment.pdf`.

---

## 2. Requirement-by-Requirement Comprehensive Audit Table

The table below maps every explicit requirement from `CIS324_Assignment_Fall_2026.pdf` to the evidence in the project, evaluates its coverage in `docs/CIS324_Assignment_Report.md`, and specifies the exact necessary correction.

| # | Assignment Requirement (from Guideline PDF) | Evidence in Project Codebase | Report Section | Audit Status | Necessary Correction Required |
| :---: | :--- | :--- | :---: | :---: | :--- |
| **Q1.1** | Explain front-end (HTML, CSS, JS) and back-end (web server, PHP, MySQL) interaction. | `index.php`, `actions.php`, `api/router.php`, `assets/`, `pages/`. | Section 1.1 (lines 99–154) | **Compliant** | Content is thorough and accurate. |
| **Q1.2** | Trace a complete browser request and server response (Internet/WWW, web server, database). | HTTP request line, Apache `.htaccess`, PDO query execution, HTML/CSS/JS rendering. | Section 1.2 & 1.3 (lines 156–219) | **Compliant** | Step-by-step trace and sequence diagram are well written. |
| **Q1.3** | Support explanation with a labeled architecture diagram. | Multi-tier architecture documented in `docs/PharmaCare_System_Architecture.md`. | Section 1.1 (lines 102–141) | **Needs Improvement** | Replace/augment the ASCII box diagram with the visual multi-tier Mermaid architecture diagram from `docs/PharmaCare_System_Architecture.md`. |
| **Q2.1** | Describe how WDLC phases apply to the project (planning, analysis, design, development, testing, deployment, maintenance). | Requirements analysis, 3NF database schema, MVC code, `tests/run_tests.php`, `.htaccess`, XAMPP deployment. | Section 2.1 (lines 225–271) | **Needs Improvement** | Explicitly enumerate all 7 distinct phases named in the rubric (planning, analysis, design, development, testing, deployment, maintenance) rather than combining them into 5 rows. |
| **Q2.2** | Identify at least three web standards or accessibility considerations (semantic HTML, responsive design, color contrast). | Semantic HTML5 tags (`<header>`, `<nav>`, `<main>`), Bootstrap 5.3.3 fluid grid, WCAG 2.1 AA contrast ($\ge 4.5:1$), 44px touch targets. | Section 2.2 (lines 273–287) | **Compliant** | Thorough, cites WCAG criteria (1.4.1, 1.4.3, 2.5.5) and semantic HTML5. |
| **Q3.1** | Develop front-end and server-side logic using HTML, CSS, JavaScript, and PHP. | Semantic HTML5, Bootstrap 5.3.3, Vanilla JS Fetch, native PHP 8.1+ without third-party frameworks. | Section 3.1 & 3.2 (lines 297–434) | **Compliant** | Demonstrates pure native PHP/JS implementation. |
| **Q3.2** | Application must include at least five functional pages. | 11 consolidated functional core views in `pages/*.php`. | Section 3.1 & 3.2 (lines 297–434) | **Compliant** | Thoroughly documents all 11 core views (exceeding requirement). |
| **Q3.3** | Apply PHP operators, conditionals, loops, and arrays appropriately. | Null coalescing `??`, match expressions, spaceship operator `<=>`, array destructuring, `foreach` loops. | Section 3.3 (lines 436–482) | **Compliant** | Detailed inventory of modern PHP 8 operators and constructs. |
| **Q3.4** | Handle form data using superglobals (`$_GET` / `$_POST`). | `$_GET` for route/filter parsing; `$_POST` for state mutations; `$_FILES` for prescriptions; `$_SESSION` for auth/cart; `$_SERVER` for methods. | Section 3.3 (lines 439–445) | **Compliant** | Detailed breakdown of each superglobal and its exact usage. |
| **Q3.5** | Use sessions and cookies to maintain user state (e.g., login). | `$_SESSION['user']`, `$_SESSION['cart']`, `$_SESSION['_csrf']`, 30-min idle timeout, strict cookie flags. | Section 3.4 (lines 484–508) | **Compliant** | Complete coverage of session state and stock clamping. |
| **Q3.6** | **Include screenshots and explain key code segments.** | 14 verified screenshots exist in `docs/screenshots/` (`01_Login.png` to `14_Test_Results.png`). | Section 3.2 (lines 316–434) | ❌ **NON-COMPLIANT (CRITICAL)** | **ZERO screenshots embedded in the report!** Embed the 11 view screenshots directly into Section 3.2 with figure numbers, captions, and callouts. |
| **Q4.1** | Design a normalized relational database (min three related tables with primary and foreign keys). | 10 tables, 10 primary keys, 14 foreign keys in `database/pharmacare.sql`. | Section 4.1 & 4.2 (lines 513–673) | **Compliant** | Documents 1NF, 2NF, 3NF proofs and full 10-table data dictionary. |
| **Q4.2** | Present database design as an ER diagram with a brief justification. | Complete ER diagram in `docs/PharmaCare_ER_Diagram.mmd`, `.pdf`, `.png`, and `docs/ERD_Analysis_Report.md`. | Section 4.3 (lines 675–786) | ⚠️ **PARTIALLY COMPLIANT** | Current Mermaid ERD in report is abbreviated and missing foreign keys (`prescriptions.customer_id`, `prescriptions.reviewed_by_id`, `sales.order_id`). Upgrade to complete 10-table / 14-FK diagram and embed ERD image. |
| **Q4.3** | Connect PHP application to MySQL using MySQLi or PDO. | Thread-safe PDO singleton in `config/database.php` with `ATTR_EMULATE_PREPARES => false`. | Section 4.4 (lines 788–820) | **Compliant** | Accurately describes PDO singleton connection and settings. |
| **Q4.4** | **Implement complete CRUD (Create, Read, Update, Delete) operations on at least two entities, using prepared statements.** | Entity 1: `medicines` CRUD in `actions.php:282, 344` & `Model.php`. Entity 2: `categories` CRUD in `actions.php:221, 238` & `Model.php:182`. Entity 3: `suppliers` CRUD in `actions.php:248, 272`. Entity 4: `app_users` CRUD in `actions.php:355, 393, 412`. | Section 4.4 (lines 788–820) | ❌ **NON-COMPLIANT (CRITICAL)** | **Only ONE entity (`medicines`) is documented, and only C, R, U are shown (Delete omitted). Entity 2 is completely missing!** Add complete C, R, U, D snippets for Entity 1 (`medicines`) AND Entity 2 (`categories`). |
| **Q5.1** | Analyze server-side input validation and sanitization (using regex and string functions). | `preg_match` for mobile phone (`01[3-9]\d{8}`) and username (`[A-Za-z0-9_.-]{3,50}`), `filter_var` for email, `required_text()`, `positive_int()`, `price_value()`. | Section 5.1 (lines 876–918) | **Compliant** | Details defensive sanitization helpers and regex validation. |
| **Q5.2** | Explain how implementation protects the database. | Parameterized prepared statements, disabling PDO emulation, foreign key restrict rules, pessimistic locks. | Section 5.1 & 5.3 (lines 910–918, 986–988) | **Compliant** | Accurately documents database protection layers. |
| **Q5.3** | Identify at least three realistic threats (SQL injection, XSS, weak password storage). | Identifies 5 threats: SQLi, XSS, Weak Passwords, IDOR, and CSRF. | Section 5.3 (lines 933–1005) | **Compliant** | Exceeds the minimum of three threats. |
| **Q5.4** | **Show with evidence (before/after code or test cases) how implementation mitigates each threat.** | `tests/run_tests.php` implements `TC-SEC-01` (XSS), `TC-SEC-02` (API/Methods), `TC-SEC-03` (CSRF), `TC-SEC-04` (Prescription Upload), `TC-SEC-05` (Bcrypt). | Section 5.3 (lines 986–1005) | ⚠️ **PARTIALLY COMPLIANT** | Descriptions explain concepts, but lack side-by-side Before/After code blocks and do not cite the automated test IDs from `tests/run_tests.php`. Add explicit Before/After code and test case evidence. |
| **Q6.1** | Implement at least one Web API endpoint returning JSON. | `api/router.php` implements `action=search`, `cart_add`, `cart_update`, `status`. Standard JSON envelope. | Section 6.1 & 6.2 (lines 1011–1067) | **Compliant** | Accurately documents endpoints and JSON response structure. |
| **Q6.2** | Consume Web API from front-end using JavaScript fetch/AJAX. | `assets/script.js` implements debounced search and real-time status polling using native `fetch()`. | Section 6.3 (lines 1069–1122) | **Compliant** | Code excerpts show event listeners, debouncing, and DOM injection. |
| **Q6.3** | Compare API approach with traditional form submission & page reload (performance, UX, coupling, security). | Detailed comparative table analyzing bandwidth (95% reduction), UX latency ($<50\text{ms}$ vs $800\text{ms}$), decoupling, and CSRF header verification. | Section 6.4 (lines 1124–1132) | **Compliant** | High-quality critical analysis addressing all 4 required dimensions. |
| **Q7.1** | Evaluate state management & access control against risks (session hijacking, session fixation). | Fixation defense via `session_regenerate_id(true)`; hijacking defense via `HttpOnly`, `SameSite=Lax`, strict mode. | Section 7.2 (lines 1162–1185) | **Compliant** | Explains attack vectors and implementation defenses. |
| **Q7.2** | Recommend and implement at least two hardening measures (session ID regeneration, cookie flags, timeout, password hashing). | Implements ALL four: `session_regenerate_id(true)`, `HttpOnly`/`SameSite=Lax`, 30-min idle timeout, Bcrypt cost 12. | Section 7.2 (lines 1162–1203) | **Compliant** | Exceeds requirement by implementing four measures. |
| **Q7.3** | Justify trade-offs between security and performance/usability. | 30-minute idle timeout trade-off analysis comparing 5-min clinical disruption vs 24-hr exposure. | Section 7.4 (lines 1217–1226) | **Compliant** | Strong real-world clinical pharmacy workflow justification. |
| **Q8.1** | Evaluate MVC architecture by implementing application in MVC with PHP. | Layered MVC implementation in `app/Controller.php`, `app/Model.php`, `app/InventoryService.php`, `pages/*.php`. | Section 8.1 (lines 1232–1266) | **Compliant** | Accurately maps application structure to MVC. |
| **Q8.2** | Map files to Model, View, and Controller roles. | Models (`app/Model.php`), Views (`pages/*.php`, `includes/`), Controllers (`app/Controller.php`, `index.php`, `actions.php`). | Section 8.1 & 8.2 (lines 1232–1314) | ⚠️ **NEEDS MINOR FIX** | Update method inventories to include all 30 Controller methods and 32 Model methods. |
| **Q8.3** | Critically compare MVC with non-MVC (procedural) approach (maintainability, scalability, testability). | Detailed comparison table on maintainability, DRY reusability, testability, and team scalability. | Section 8.3 (lines 1317–1325) | **Compliant** | Detailed technical justification. |
| **Q9.1** | Concluding statement justifying architectural & tech choices (secure, maintainable, fit for organization). | Concluding summary highlighting native lightweight PHP footprint, pessimistic locking, defense-in-depth. | Section 9.1 (lines 1331–1339) | **Compliant** | Well-reasoned engineering conclusion. |
| **Q9.2** | State main limitation. | Absence of external digital payment gateway (COD and Store Pickup only). | Section 9.2 (lines 1342–1346) | **Compliant** | Honest and accurate reflection of current system boundaries. |
| **Q9.3** | State one improvement you would make. | Proposes SMS gateway integration, automated purchase order reordering, and PWA offline caching. | Section 9.3 (lines 1349–1356) | **Compliant** | Exceeds requirement by outlining three actionable improvements. |
| **Sub.1** | Consolidated PDF report (ER diagram, architecture diagram, screenshots, analysis). | Markdown source `docs/CIS324_Assignment_Report.md`. | Entire Report | ⚠️ **PARTIALLY COMPLIANT** | Must embed screenshots, visual ER diagram, and visual architecture diagram before final PDF compilation. |
| **Sub.2** | Repository requirements: name `StudentID_Section_CIS324_Project`, README with test login details. | Project has complete `README.md` with evaluation credentials (`admin`/`admin123`, `staff`/`staff123`, `customer`/`customer123`). | Cover Page (lines 20, 24-25) | ⚠️ **NEEDS ADVICE** | Note in report that for final submission, the repo should be cloned/named `241-16-016_A_CIS324_Project`. |
| **Sub.3** | Mandatory AI Use Disclosure Statement as Annex A. | Annex A included with 6 declared tasks, tool versions, prompts, and signed statement. | Annex A (lines 1359–1386) | **Compliant** | Complies with university AMBER Tier Policy format. |
| **Sub.4** | In-text citations and reference list in APA 7th edition. | Report contains inline citations and technology references. | Sections 1-9 & References | ⚠️ **NEEDS EXPANSION** | Formalize APA 7th reference list at the end of the report (PHP manual, OWASP Top 10, W3C WCAG 2.1, MySQL reference). |

---

## 3. In-Depth Technical Verification & Fact-Checking Log

Every technical claim in `docs/CIS324_Assignment_Report.md` was cross-referenced against the physical codebase:

### 3.1 Verification of Factual Claims vs. Physical Codebase

1. **Claim: 11 Consolidated Core Views**
   - *Report Line:* 298–313
   - *Physical Codebase Check:* Verified in `index.php:33-36` and `pages/`. The files `login.php`, `register.php`, `dashboard.php`, `catalog.php`, `cart.php`, `orders.php`, `order_detail.php`, `medicines.php`, `sales.php`, `invoice.php`, `users.php` exist and are actively routed.
   - *Verdict:* **100% Verified Accurate.**

2. **Claim: Delivery Fee Calculation in Cart**
   - *Report Line:* 365 (`Standard Delivery Fee (৳60, waived over ৳1000)`)
   - *Physical Codebase Check:* Inspected `app/Controller.php:654`:
     ```php
     $shippingFee = 0.00;
     $totalAmount = round($subtotal + $shippingFee, 2);
     ```
     Inspected `database/pharmacare.sql:98`:
     ```sql
     shipping_fee DECIMAL(10,2) NOT NULL DEFAULT 0.00,
     ```
   - *Verdict:* **Inaccurate Claim.** The application implements Free Delivery (`৳0.00`). The report must be corrected to state that shipping is free (`৳0.00`) to reflect the real code.

3. **Claim: 10 Relational Tables & Normalization**
   - *Report Line:* 530–671
   - *Physical Codebase Check:* Verified in `database/pharmacare.sql`. The 10 tables are `app_users`, `categories`, `suppliers`, `medicines`, `sales`, `sale_items`, `orders`, `order_items`, `order_status_history`, and `prescriptions`.
   - *Verdict:* **100% Verified Accurate.**

4. **Claim: Pessimistic Row Locking (`SELECT ... FOR UPDATE`)**
   - *Report Line:* 824–865
   - *Physical Codebase Check:* Verified in `app/InventoryService.php:34, 119` and `app/Controller.php:768, 812`. Row locking is genuinely implemented and active during stock deduction and status updates.
   - *Verdict:* **100% Verified Accurate.**

5. **Claim: 30-Minute Idle Session Inactivity Timeout**
   - *Report Line:* 1187–1202
   - *Physical Codebase Check:* Verified in `includes/auth.php:12-30`. `SESSION_IDLE_TIMEOUT = 1800` is enforced on every authenticated request with session cookie destruction.
   - *Verdict:* **100% Verified Accurate.**

6. **Claim: Authenticated Prescription Streaming**
   - *Report Line:* 1206–1214
   - *Physical Codebase Check:* Verified in `index.php:88-91`, `app/Controller.php:496-583`, and `pages/order_detail.php:4-72`. Direct URL access to `storage/prescriptions/` is denied by `.htaccess`, and streaming enforces IDOR ownership checks.
   - *Verdict:* **100% Verified Accurate.**

7. **Claim: CSRF Token Verification**
   - *Report Line:* 981, 1004
   - *Physical Codebase Check:* Verified in `includes/functions.php:99-118` (`csrf()`, `csrf_verify()`) using `hash_equals()`. Enforced on all POST actions in `actions.php:17`.
   - *Verdict:* **100% Verified Accurate.**

---

## 4. Screenshot & Visual Evidence Audit

The assignment guideline heavily stresses visual demonstration. A comparison between what exists on disk and what is represented in the report reveals a stark disparity:

| Screenshot Identifier & File | Description / Screen Captured | Exists in `docs/screenshots/`? | Embedded in `CIS324_Assignment_Report.md`? | Action Required |
| :--- | :--- | :---: | :---: | :--- |
| `01_Login.png` | Authentication Portal with Role Selection | **Yes (1440 × 900)** | ❌ No | Embed in Section 3.2 (Login View) |
| `02_Register.png` | Customer Self-Registration with Regex Hints | **Yes (1440 × 900)** | ❌ No | Embed in Section 3.2 (Register View) |
| `03_Dashboard.png` | Executive Analytics, KPI Cards & Charts | **Yes (1440 × 900)** | ❌ No | Embed in Section 3.2 (Dashboard View) |
| `04_Medicine_Management.png` | Master Inventory, Batch Health & Add Modal | **Yes (1440 × 900)** | ❌ No | Embed in Section 3.2 (Medicines View) |
| `05_Category_Supplier.png` | Category & Supplier Management Tabs | **Yes (1440 × 900)** | ❌ No | Embed in Section 3.2 (Medicines Tabs) |
| `06_Medicine_Search.png` | Debounced Live Search & Dosage Filter Pills | **Yes (1440 × 900)** | ❌ No | Embed in Section 3.2 & Section 6.3 |
| `07_Cart.png` | Shopping Cart Table & Stock Clamping | **Yes (1440 × 900)** | ❌ No | Embed in Section 3.2 (Cart View) |
| `08_Checkout.png` | Integrated Single-Page Checkout & Rx Upload | **Yes (1440 × 900)** | ❌ No | Embed in Section 3.2 (Checkout View) |
| `09_Order_Management.png` | Master Fulfillment Queue & Status Badges | **Yes (1440 × 900)** | ❌ No | Embed in Section 3.2 (Orders View) |
| `10_Prescription.png` | Order Detail, Timeline & Rx Inspection Modal | **Yes (1440 × 900)** | ❌ No | Embed in Section 3.2 (Order Detail View) |
| `11_User_Management.png` | Admin User Directory, KPIs & Add User Modal | **Yes (1440 × 900)** | ❌ No | Embed in Section 3.2 (Users View) |
| `12_Database_Structure.png` | phpMyAdmin 10-Table Schema Overview | **Yes (1440 × 900)** | ❌ No | Embed in Section 4.2 (Data Dictionary) |
| `13_ER_Diagram.png` | Complete Normalized Relational ERD | **Yes (1440 × 900)** | ❌ No | Embed in Section 4.3 (ER Diagram) |
| `14_Test_Results.png` | Automated Test Suite Terminal Output | **Yes (1440 × 900)** | ❌ No | Embed in Section 5.3 & Section 2.1 |

---

## 5. Prioritized Remediation Roadmap (Awaiting User Approval)

To transform `docs/CIS324_Assignment_Report.md` from its current state into a 100% compliant, 35/35 submission report, the following targeted edits should be executed upon user approval:

### Phase 1: Embedding Visual Evidence (Q1, Q3, Q4)
1. **Task 1 (Q1):** Insert the multi-tiered Mermaid Architecture Diagram into Section 1.1 alongside the text explanation.
2. **Task 2 (Q3):** Embed all 11 core view screenshots (`01_Login.png` through `11_User_Management.png`) directly into their respective sub-sections under Section 3.2 with figure numbers, captions, and code explanations.
3. **Task 2 (Q4):** Embed the visual ER Diagram (`13_ER_Diagram.png`) and phpMyAdmin structure (`12_Database_Structure.png`) into Sections 4.2 and 4.3, linking to `docs/PharmaCare_ER_Diagram.pdf`.

### Phase 2: Fulfilling Missing CRUD Requirements (Q4)
1. In Section 4.4, update Entity 1 (`medicines`) to show all four operations: Create, Read, Update, and Delete/Deactivate.
2. Add a complete, dedicated subsection in Section 4.4 for **Entity 2: `categories` (or `suppliers`)** demonstrating full CRUD with prepared statements (`INSERT`, `SELECT`, `UPDATE`, `DELETE`), fulfilling the mandatory two-entity requirement.

### Phase 3: Strengthening Security Threat Evidence (Q5)
1. For each of the five threats analyzed in Section 5.3 (SQLi, XSS, Weak Passwords, IDOR, CSRF), provide explicit **"Vulnerable Code (Before)" vs. "PharmaCare Secure Code (After)"** code blocks.
2. Explicitly map each threat to the automated test suite in `tests/run_tests.php` (`TC-SEC-01` through `TC-SEC-05`) and embed the test execution evidence screenshot (`14_Test_Results.png`).

### Phase 4: Fact-Checking & Text Adjustments
1. Correct the cart delivery fee statement in Section 3.2 (change from ৳60 to ৳0.00 / Free Shipping).
2. Expand the function inventory in Section 8.2 to list all 30 Controller methods and 32 Model methods.
3. Update metadata to note the university's repository naming convention (`241-16-016_A_CIS324_Project`) and PDF export name (`241-16-016_A_CIS324_Assignment.pdf`).
4. Append a formal APA 7th edition reference list at the conclusion of the report.

---

## 6. Audit Conclusion & Next Steps

This audit establishes that the **PharmaCare application codebase is already fully built, robust, and capable of achieving maximum marks**. The required remediations are strictly documentational—upgrading the existing written report to include the rich visual evidence, missing code snippets, and complete test citations that already exist within the repository.

**Current Document Status:**
- Existing assignment report ([docs/CIS324_Assignment_Report.md](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/docs/CIS324_Assignment_Report.md)) remains **100% intact and unedited**.
- Application source code and database schemas remain **completely untouched**.
- Audit findings are permanently recorded in this deliverable: [docs/FINAL_ASSIGNMENT_AUDIT.md](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/docs/FINAL_ASSIGNMENT_AUDIT.md).

*Standing by for your review and explicit approval before applying the targeted corrections to `docs/CIS324_Assignment_Report.md`.*
