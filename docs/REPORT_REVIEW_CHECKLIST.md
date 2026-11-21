# PharmaCare CIS324 Assignment Report — Final Review & Submission Readiness Checklist

**Course:** CIS324: Web Engineering (Level 3, Term 3)  
**Academic Department:** Department of Computing and Information System (CIS), FSIT, Daffodil International University  
**Primary Authority:** `CIS324_Assignment_Fall_2026.pdf` (Semester Assignment Guideline)  
**Working Review Copy:** [`docs/CIS324_Assignment_Report_Review.md`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/docs/CIS324_Assignment_Report_Review.md)  
**Original Report (Preserved):** [`docs/CIS324_Assignment_Report.md`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/docs/CIS324_Assignment_Report.md)  
**Audit Source Reference:** [`docs/FINAL_ASSIGNMENT_AUDIT.md`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/docs/FINAL_ASSIGNMENT_AUDIT.md)  
**Audit & Review Date:** November 21, 2026  
**Auditor / Engineering Reviewer:** Antigravity Engineering Agent  

---

## 1. Executive Submission Readiness Verdict

> [!WARNING]
> ### CURRENT STATUS: NOT YET SUBMISSION-READY (ACTION ITEMS PENDING BY STUDENT)
> While the **software codebase is 100% complete and fully verified**, and the proposed working report ([`docs/CIS324_Assignment_Report_Review.md`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/docs/CIS324_Assignment_Report_Review.md)) has been structurally organized with all 14 visual screenshots and multi-tier diagrams embedded, **the student must personally complete two mandatory technical sections** before exporting the final submission PDF:
> 1. **Complete CRUD Implementation for a Second Entity (Task 2: Q4)** — Provide personal code excerpts and explanation for `categories` or `suppliers`.
> 2. **Before/After Security Code Snippets & Automated Test Citations (Task 3: Q5)** — Insert brief side-by-side code blocks and cite `tests/run_tests.php` test IDs for the 5 analyzed threats.

---

## 2. Q1–Q9 Detailed Requirement Checklist

| Question | Bloom's Level & Marks | Assignment Rubric Requirement | Status in Working Review Copy | Action Required by Student |
| :---: | :---: | :--- | :---: | :--- |
| **Q1** | Understand (C2)<br>**3 Marks (CO1)** | - Explain front-end vs. back-end interaction.<br>- Trace a complete browser request and server response.<br>- Explain role of Internet/WWW, web server, PHP, MySQL.<br>- Support with a labeled architecture diagram. | ✅ **Complete & Verified** | None. Preserved student's text, integrated multi-tier Mermaid Architecture Diagram (*Figure 1.1*), and sequence diagram (*Section 1.2*). |
| **Q2** | Understand (C2)<br>**2 Marks (CO1)** | - Map the seven WDLC phases: planning, analysis, design, development, testing, deployment, maintenance.<br>- Identify at least three web standards or accessibility considerations (e.g., semantic HTML, responsive design, color contrast). | ⚠️ **Guidance Added** | Review Section 2.1 and ensure all 7 phases are explicitly named as individual sub-headings before final PDF export. |
| **Q3** | Apply (C3)<br>**5 Marks (CO1)** | - Minimum 5 functional pages.<br>- HTML, CSS, JavaScript, PHP.<br>- Operators, conditionals, loops, arrays.<br>- `$_GET` / `$_POST` superglobals.<br>- Sessions and cookies for user state.<br>- **Application screenshots.**<br>- Key code segments and explanations. | ✅ **Screenshots Integrated** | None. All 11 core view screenshots (*Figures 3.1 to 3.11*) have been embedded directly into Section 3.2. Factual correction on delivery fee applied. |
| **Q4** | Apply (C3)<br>**5 Marks (CO2)** | - Normalized relational database (min 3 tables).<br>- Primary and foreign keys.<br>- ER diagram with brief justification.<br>- PDO MySQL connection.<br>- **Complete CRUD on at least two entities.**<br>- Prepared statements. | ❌ **ACTION REQUIRED** | **Student must insert CRUD code for Entity 2** (see Section 4.4 in review copy). The complete 10-table / 14-FK ER diagram and phpMyAdmin screenshot are now integrated (*Figures 4.1 & 4.2*). |
| **Q5** | Analyze (C4)<br>**5 Marks (CO2)** | - Server-side input validation & sanitization (regex, string functions).<br>- Database protection.<br>- At least three realistic security threats.<br>- **Evidence through real code (before/after) or test cases.** | ❌ **ACTION REQUIRED** | **Student must insert Before/After code blocks** and cite test IDs (`TC-SEC-01` to `TC-SEC-05`) in Section 5.3. Automated test execution screenshot (*Figure 5.1*) is now embedded. |
| **Q6** | Analyze (C4)<br>**5 Marks (CO3)** | - At least one JSON API endpoint.<br>- Front-end Fetch/AJAX integration.<br>- Comparison with traditional form reload (performance, UX, coupling, security). | ✅ **Complete & Verified** | None. Documents `api/router.php`, JSON contract, debounced search fetch in `assets/script.js`, and comparative analysis table. |
| **Q7** | Evaluate (C5)<br>**5 Marks (CO2)** | - Cookies, sessions, auth, RBAC.<br>- Session hijacking and fixation risks.<br>- At least two hardening measures.<br>- Security vs. usability/performance trade-offs. | ✅ **Complete & Verified** | None. Documents 4 hardening measures (strict mode, `session_regenerate_id`, 30-min timeout, Bcrypt) and 30-minute clinical workflow trade-off justification. |
| **Q8** | Evaluate (C5)<br>**3 Marks (CO3)** | - MVC implementation.<br>- Map actual files to Model, View, Controller.<br>- Comparison with procedural PHP (maintainability, scalability, testability). | ✅ **Complete & Verified** | None. Component mapping diagram included; method inventory expanded to reflect all 30 Controller and 32 Model methods. |
| **Q9** | Evaluate (C5)<br>**2 Marks (CO3)** | - Concluding justification of architectural and technology choices.<br>- Security, maintainability, organizational fit.<br>- Main limitation.<br>- One realistic future improvement. | ✅ **Complete & Verified** | None. Identifies native zero-framework efficiency, offline payment boundary, and proposes SMS gateway integration. |
| **Annex A** | Mandatory Policy | - AI Use Disclosure Statement.<br>- 5 mandatory declaration elements.<br>- Signed confirmation. | ✅ **Preserved & Untouched** | Student must personally verify the declared tasks and confirm signatures in Annex A before final submission. |
| **Ref** | APA 7th Edition | - Standard academic reference list for technologies, standards, and security frameworks. | ✅ **References Added** | Formal APA 7th references added to Section 10 for PHP, MySQL, OWASP, MDN, W3C WCAG, and Bootstrap. |

---

## 3. Verified Evidence & Source-File Mapping

All claims in the report have been verified against the physical project files:

| Requirement Area | Verified Source File in Project | Key Functions / Physical Evidence |
| :--- | :--- | :--- |
| **Front Controller & Routing** | [`index.php`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/index.php) | Lines 33–36 (11 routes), lines 79–85 (RBAC guards), lines 88–91 (Rx streaming). |
| **Action Processor (POST / PRG)**| [`actions.php`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/actions.php) | Line 17 (`csrf_verify()`), lines 24–524 (17 action handlers), line 538 (PRG redirect). |
| **Asynchronous API Router** | [`api/router.php`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/api/router.php) | Lines 37–43 (`match($action)`), `searchApi()`, `cartAddApi()`, `orderStatusApi()`. |
| **Controller Facade** | [`app/Controller.php`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/app/Controller.php) | 30 methods, lines 589–699 (`createOrder()`), lines 757–850 (`updateOrderStatus()`). |
| **Inventory & Concurrency Service**| [`app/InventoryService.php`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/app/InventoryService.php)| Lines 22–64 (`deductBatchStock()`), lines 92–159 (`allocateFefoStock()` via `FOR UPDATE`). |
| **Domain Models** | [`app/Model.php`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/app/Model.php) | `User` (lines 17–103), `Medicine` (lines 105–191), `Order` (lines 193–425). |
| **Database Connection Singleton** | [`config/database.php`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/config/database.php) | `db()` PDO singleton, `ATTR_EMULATE_PREPARES => false`, timezone `+06:00`. |
| **Session & Global Helpers** | [`includes/functions.php`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/includes/functions.php)| Lines 10–18 (session hardening), lines 36–39 (`e()`), lines 99–118 (`csrf()`). |
| **Authentication & RBAC** | [`includes/auth.php`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/includes/auth.php) | Lines 12–30 (30-min idle timeout), lines 64–112 (`require_login`, `require_admin`). |
| **Relational Database DDL** | [`database/pharmacare.sql`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/database/pharmacare.sql)| 10 tables, 14 foreign keys, 9 check constraints, default seed accounts. |
| **Automated Test Runner** | [`tests/run_tests.php`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/tests/run_tests.php) | 24 automated tests (`TC-1.1` to `TC-SEC-05`) verifying RBAC, Cart, FEFO, and Security. |
| **Quarantined Storage** | `storage/prescriptions/` | Non-public local directory, streaming through `Controller::downloadPrescription()`. |

---

## 4. Itemized Student-Written Content Still Required

Before exporting `docs/CIS324_Assignment_Report_Review.md` to PDF, the student must personally address the following placeholder blocks:

### Action Item 1: Complete CRUD for Second Entity (Task 2: Q4)
- **Location in Review Copy:** Section 4.4 (lines ~985–1010).
- **What to do:** The assignment requires complete CRUD (Create, Read, Update, Delete) on **at least two entities**. Entity 1 (`medicines`) is already documented. The student must write out the CRUD prepared statements for **Entity 2** (`categories` or `suppliers`) using the real code from `actions.php` and `app/Model.php`:
  - `CREATE`: `actions.php:232` (`INSERT INTO categories (name, description) VALUES (?, ?)`)
  - `READ`: `actions.php:227` (`SELECT id FROM categories WHERE id = ?`) and `Model.php:182` (`SELECT * FROM categories ORDER BY name ASC`)
  - `UPDATE`: `actions.php:226` (`UPDATE categories SET name = ?, description = ? WHERE id = ?`)
  - `DELETE`: `actions.php:243` (`DELETE FROM categories WHERE id = ?`)
- **Student task:** Replace the `[STUDENT ACTION REQUIRED]` banner with the snippets and a 2-paragraph personal explanation of why prepared statements prevent injection during each operation.

### Action Item 2: Side-by-Side Threat Code & Test Citations (Task 3: Q5)
- **Location in Review Copy:** Section 5.3 (lines ~1200–1280).
- **What to do:** For each of the five threats (SQL Injection, XSS, Weak Passwords, IDOR, and CSRF), the student must provide:
  1. A short 2-3 line **"Vulnerable Code (Before)"** example demonstrating insecure code.
  2. A short 2-3 line **"PharmaCare Secure Code (After)"** example showing the actual prepared statement, `e()` escaping, Bcrypt hashing, or `csrf_verify()` check.
  3. A citation to the automated test case in `tests/run_tests.php`:
     - SQL Injection $ightarrow$ `TC-1.2`
     - Cross-Site Scripting $ightarrow$ `TC-SEC-01` (`tests/run_tests.php:355-385`)
     - Weak Passwords $ightarrow$ `TC-SEC-05` (`tests/run_tests.php:487-515`)
     - Insecure Direct Object References $ightarrow$ `TC-SEC-04` (`tests/run_tests.php:440-485`)
     - Cross-Site Request Forgery $ightarrow$ `TC-SEC-03` (`tests/run_tests.php:403-438`)

### Action Item 3: WDLC 7 Distinct Phases Alignment (Task 1: Q2)
- **Location in Review Copy:** Section 2.1 (lines ~225–270).
- **What to do:** The student-written text covers all concepts, but groups them into 5 phases. The student should ensure that the final headings or table explicitly name all 7 phases: **(1) Planning, (2) Analysis, (3) Design, (4) Development, (5) Testing, (6) Deployment, and (7) Maintenance**.

### Action Item 4: Personal Verification of AI Disclosure (Annex A)
- **Location in Review Copy:** Annex A (lines ~1680–1720).
- **What to do:** In accordance with the university's AMBER Tier policy, the student must review the 6 declared tasks, confirm the evaluation entries, and sign their name/ID.

---

## 5. Visual Evidence & Diagram Placement Verification

All visual evidence has been successfully organized and embedded into [`docs/CIS324_Assignment_Report_Review.md`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/docs/CIS324_Assignment_Report_Review.md):

| Figure Number | Image File Path | Target Report Section | Subject Matter | Verified? |
| :---: | :--- | :--- | :--- | :---: |
| **Figure 1.1** | Inline Mermaid Diagram | Section 1.1 (Q1) | Multi-Tier Web Architecture Diagram | ✅ Yes |
| **Figure 3.1** | `screenshots/01_Login.png` | Section 3.2 (Q3, View 1) | Login Portal with Evaluation Role Cards | ✅ Yes |
| **Figure 3.2** | `screenshots/02_Register.png` | Section 3.2 (Q3, View 2) | Customer Self-Registration & Regex Validation | ✅ Yes |
| **Figure 3.3** | `screenshots/03_Dashboard.png` | Section 3.2 (Q3, View 3) | Operations & Financial Analytics Dashboard | ✅ Yes |
| **Figure 3.4** | `screenshots/06_Medicine_Search.png` | Section 3.2 (Q3, View 4) | Storefront Medicine Catalog & Search | ✅ Yes |
| **Figure 3.5** | `screenshots/07_Cart.png` | Section 3.2 (Q3, View 5) | Shopping Cart & Stock Clamping Controls | ✅ Yes |
| **Figure 3.6** | `screenshots/08_Checkout.png` | Section 3.2 (Q3, View 5) | Checkout Form & Prescription Upload Field | ✅ Yes |
| **Figure 3.7** | `screenshots/09_Order_Management.png` | Section 3.2 (Q3, View 6) | Master Fulfillment Queue & Customer History | ✅ Yes |
| **Figure 3.8** | `screenshots/10_Prescription.png` | Section 3.2 (Q3, View 7) | Order Detail & Prescription Review Modal | ✅ Yes |
| **Figure 3.9** | `screenshots/04_Medicine_Management.png`| Section 3.2 (Q3, View 8) | Master Inventory & Batch Management Modal | ✅ Yes |
| **Figure 3.10**| `screenshots/05_Category_Supplier.png` | Section 3.2 (Q3, View 8) | Category & Supplier Management Tabs | ✅ Yes |
| **Figure 3.11**| `screenshots/11_User_Management.png` | Section 3.2 (Q3, View 11)| User Administration & Status Toggles | ✅ Yes |
| **Figure 4.1** | `screenshots/12_Database_Structure.png` | Section 4.2 (Q4) | Database Overview (10 Tables in phpMyAdmin) | ✅ Yes |
| **Figure 4.2** | `screenshots/13_ER_Diagram.png` | Section 4.3 (Q4) | Complete 10-Table / 14-FK Relational ERD | ✅ Yes |
| **Figure 5.1** | `screenshots/14_Test_Results.png` | Section 5.3 (Q5) | Automated Security Test Suite Execution Output | ✅ Yes |

---

## 6. Factual Corrections Applied

1. **Delivery Fee Calculation (Cart & Checkout):**
   - *Previous Inaccuracy:* The report claimed: *"Displays item Subtotal, Standard Delivery Fee (৳60, waived over ৳1000), and Net Grand Total."*
   - *Codebase Reality:* In `app/Controller.php:654` and `database/pharmacare.sql:98`, shipping is fixed at `0.00` (Free Shipping).
   - *Correction Applied:* Corrected text to Free Shipping (`৳0.00`) and added an explanatory note.
2. **Controller and Model Method Inventory:**
   - *Previous Incompleteness:* The report previously listed ~20 Controller methods and 14 Model methods.
   - *Codebase Reality:* `app/Controller.php` defines 30 distinct public and static methods; `app/Model.php` defines 32 methods across `User`, `Medicine`, and `Order`.
   - *Correction Applied:* Section 8.2 expanded to document all 30 Controller methods and all Model persistence methods.
3. **ER Diagram Completeness:**
   - *Previous Incompleteness:* The Mermaid ERD previously displayed an abbreviated 6-table subset missing several foreign keys.
   - *Correction Applied:* Upgraded to the complete 10-table / 14-FK Mermaid definition matching `docs/PharmaCare_ER_Diagram.mmd` and embedded the high-resolution diagram image.

---

## 7. Official Submission Guidelines Checklist

| Guideline Item | Official PDF Requirement | Current Project Compliance Status | Action Needed Before Final Submission |
| :--- | :--- | :---: | :--- |
| **Submission Deadline** | November 30, 2026 | ✅ Within timeline | Submit before the deadline. |
| **Submission Format (1)** | Single consolidated PDF report | ⚠️ Ready in Markdown | Convert `docs/CIS324_Assignment_Report_Review.md` to PDF once action items are completed. |
| **Submission Format (2)** | Complete source code, SQL export, README | ✅ Complete | Repository has source code, `database/pharmacare.sql`, and `README.md`. |
| **File Naming Format** | `StudentID_Section_CIS324_Assignment.pdf` | ⚠️ Note on Cover Page | Name final PDF: **`241-16-016_A_CIS324_Assignment.pdf`**. |
| **Repository Naming Format**| `StudentID_Section_CIS324_Project` | ⚠️ Note on Cover Page | Name repository: **`241-16-016_A_CIS324_Project`**. |
| **Test Login Credentials** | Must be included in README | ✅ Verified | `README.md` documents `admin`/`admin123`, `staff`/`staff123`, `customer`/`customer123`. |
| **Hardcoded Secrets** | No hardcoded passwords or API keys | ✅ Verified | Uses environment variable fallbacks in `config/database.php`. |
| **AI Disclosure Statement** | Attached as Annex A (Mandatory) | ✅ Verified | Annex A is included with all 5 mandatory elements. |

---

## 8. Summary of Files Created & Modified

| File Path | Nature of File | State in Project |
| :--- | :--- | :---: |
| [`docs/CIS324_Assignment_Report.md`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/docs/CIS324_Assignment_Report.md) | Original student assignment report | **Preserved 100% Untouched** |
| [`docs/CIS324_Assignment_Report_Review.md`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/docs/CIS324_Assignment_Report_Review.md) | Proposed organized working copy | **Created (1,699 lines, ~125 KB)** |
| [`docs/REPORT_REVIEW_CHECKLIST.md`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/docs/REPORT_REVIEW_CHECKLIST.md) | Final verification & submission readiness checklist | **Created** |
| [`docs/FINAL_ASSIGNMENT_AUDIT.md`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/docs/FINAL_ASSIGNMENT_AUDIT.md) | Line-by-line audit against official PDF | **Created** |
| Application Source Code & Database | PHP code, SQL schemas, automated tests | **Preserved 100% Untouched** |

*Use `docs/REPORT_REVIEW_CHECKLIST.md` as your guide to complete the marked student placeholders in `docs/CIS324_Assignment_Report_Review.md` before final PDF export.*
