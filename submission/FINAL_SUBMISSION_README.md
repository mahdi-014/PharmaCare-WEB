# PharmaCare CIS324 Final Submission Package

**Course:** CIS324 Web Engineering — Semester Assignment (Fall 2026)  
**Topic 13:** Pharmacy Inventory and Online Medicine Ordering System  
**Student Name / ID:** Md. Mahdi Hasan (ID: 241-16-016)  
**Section:** Section A  
**Submission Package Directory:** `D:\PharmaCare-PHP-Complete\PharmaCare-PHP\submission`  

---

## 1. Package Contents Overview

This package consolidates all academic deliverables, technical documentation, database schemas, and visual evidence required for final grading and viva voce evaluation:

1. **`241-16-016_A_CIS324_Assignment.pdf`**  
   The official, submission-ready academic assignment report compiled from the verified technical review document. Includes formal cover page, table of contents, architecture flowcharts, ER diagrams, 14 high-resolution screenshot embeddings, data dictionaries, comparative tables, and APA 7th edition references.
2. **`CIS324_Assignment_Report_Review.md`**  
   The complete editable Markdown source of the report containing all verified technical code citations, schema references, and clearly marked student academic placeholders.
3. **`PharmaCare_ER_Diagram.pdf` & `PharmaCare_ER_Diagram.png`**  
   The complete 10-table, 14-foreign-key normalized Third Normal Form (3NF) Entity-Relationship Diagram in vector PDF and high-resolution raster formats.
4. **`PharmaCare_System_Architecture.md`**  
   Comprehensive technical architecture specification documenting the MVC structure, request-response lifecycles, security defenses, and pessimistic concurrency locking algorithms.
5. **`REPORT_REVIEW_CHECKLIST.md`**  
   Complete question-by-question (Q1–Q9) rubric compliance and evidence-mapping checklist.
6. **`FINAL_ASSIGNMENT_AUDIT.md`**  
   Line-by-line verification audit evaluating all 35-mark criteria against the official assignment question paper (`CIS324_Assignment_Fall_2026.pdf`).
7. **`ERD_Analysis_Report.md`**  
   Deep relational schema audit verifying all 10 tables, 104 columns, primary keys, foreign key cascade behaviors, and domain CHECK constraints.
8. **`screenshots/`**  
   Folder containing all 14 captured high-resolution verification screenshots (`01_Login.png` through `14_Test_Results.png`) and the screenshot collection report.

---

## 2. What Is Complete & Verified

- **100% Functional Application Codebase:** 11 consolidated views, Front Controller (`index.php`), POST Action Dispatcher (`actions.php`), RESTful JSON API Router (`api/router.php`), PDO connection singleton, and FEFO inventory service (`app/InventoryService.php`).
- **Relational Database:** 10 normalized 3NF tables with 14 foreign keys imported and active in MariaDB/MySQL.
- **Automated Test Suite:** 11 automated regression test cases (`tests/run_tests.php`) covering syntax linting, repo hygiene, XSS sanitization, API error masking, prescription rollback cleanup, historical snapshots, automated FEFO allocation, stock restoration idempotency, order state machines, RBAC guards, and IDOR protection (100% pass rate in isolated test environment).
- **Embedded Visual Evidence:** All 14 UI screenshots and system diagrams are properly embedded with captions.
- **Factual Integrity:** Cart free delivery (`$shippingFee = 0.00;`), 30-minute session timeout, and complete method inventories match the physical code.
- **Mandatory AI Disclosure:** Annex A preserved with all 5 mandatory declaration elements and student signature block.

---

## 3. What the Student Must Personally Finish

In accordance with the university's academic integrity and AMBER-tier AI policy, the student must personally write their own intellectual analysis, evaluation, and reflections for sections marked by `[STUDENT WRITE HERE]` in `CIS324_Assignment_Report_Review.md` before final grading:

1. **Task 1 — Question 1 (Request-Response Trace & Web Fundamentals):**  
   Personal narrative tracing the browser request lifecycle through DNS, TCP/IP, Apache, PHP runtime, and MySQL.
2. **Task 1 — Question 2 (WDLC 7 Phases & Web Standards):**  
   Personal milestone timeline under `1. Planning` and ongoing operational maintenance procedures under `7. Maintenance`.
3. **Task 2 — Question 3 (Core Views & State Management):**  
   Personal reflection explaining the design choices behind the 11 functional views and session state handling.
4. **Task 2 — Question 4 (Prepared Statements Academic Justification):**  
   Academic explanation justifying why parameterized queries prevent SQL injection, explaining MySQL execution plan compilation.
5. **Task 3 — Question 5 (Security Threat Analysis & Conceptual Comparisons):**  
   Personal conceptual analysis and comparative discussion for each of the 5 realistic web threats (SQLi, XSS, Weak Passwords, IDOR, CSRF).
6. **Task 3 — Question 6 (API Evaluation: Fetch vs. Traditional Reloads):**  
   Personal comparative analysis evaluating asynchronous DOM updates vs full server-side page rendering.
7. **Task 4 — Question 7 (Session Hardening & Usability Trade-Offs):**  
   Personal evaluative critique of the 30-minute idle inactivity timeout trade-off between security and user experience.
8. **Task 4 — Question 8 (MVC Architectural Evaluation):**  
   Personal comparative evaluation contrasting compact layered MVC against procedural "spaghetti" PHP.
9. **Task 4 — Question 9 (System Limitations & Future Roadmap):**  
   Personal appraisal of system limitations (e.g., lack of automated SMS OTP, online payment gateways) and proposed future roadmaps.
10. **Annex A (AI Use Disclosure):**  
    Personal review, date, and signature on the University AMBER Tier Statement of Academic Integrity.

---
*PharmaCare Final Submission Package — CIS324 Web Engineering Semester Project (Fall 2026)*
