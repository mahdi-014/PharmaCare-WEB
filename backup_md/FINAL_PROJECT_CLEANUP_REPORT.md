# PharmaCare — Final Project Cleanup & Repository Synchronization Report

**Cleanup Date:** November 25, 2026  
**Project Directory:** `D:\PharmaCare-PHP-Complete\PharmaCare-PHP`  
**GitHub Repository:** `https://github.com/mahdi-014/PharmaCare-WEB.git`  
**Target Branch:** `main`  
**Baseline Commit (Pre-Cleanup):** `726b913b3e45dac0e365f74945960d8885542393` (`726b913`)  

---

## 1. Original Project Structure Summary

Before cleanup, the workspace contained:
- **Tracked on `origin/main` (`49` files):** `23` PHP files (`index.php`, `actions.php`, `api/router.php`, `app/`, `config/`, `includes/`, `pages/`, `tests/run_tests.php`), `assets/` (`style.css`, `script.js`), `database/pharmacare.sql`, `storage/prescriptions/.gitkeep`, `.htaccess`, `.gitignore`, `LICENSE`, `README.md`, `tests/README.md`, `docs/Q2_WDLC_WEB_STANDARDS_REPORT.md`, and `16` screenshots (`01`–`14`, `15_Responsive_Tablet_Dashboard_768px.png`, `16_Responsive_Mobile_Catalog_375px.png`).
- **Uncommitted Local Workspace Items:** Two user-renamed screenshot files (`15_Responsive_Tablet_Dashboard_.png`, `16_Responsive_Mobile_Catalog.png`) replacing `_768px`/`_375px`, plus untracked `docs/Q2_FINAL_SUBMISSION_INTEGRITY_REPORT.md`.
- **Git-Ignored Local Items:** `submission/` (`11` files + `screenshots/`), duplicate `FINAL_SUBMISSION_README.md` at project root, and orphaned upload `storage/prescriptions/1cdd13d72a9062c84ef8ec7a04f24479.png`.

---

## 2. Final Project Structure

```text
PharmaCare-PHP/
├── api/
│   └── router.php
├── app/
│   ├── Controller.php
│   ├── InventoryService.php
│   └── Model.php
├── assets/
│   ├── script.js
│   └── style.css
├── backup_md/
│   ├── FINAL_PROJECT_CLEANUP_REPORT.md
│   ├── docs/
│   │   ├── Q2_FINAL_SUBMISSION_INTEGRITY_REPORT.md
│   │   └── Q2_WDLC_WEB_STANDARDS_REPORT.md
│   └── tests/
│       └── README.md
├── config/
│   └── database.php
├── database/
│   └── pharmacare.sql
├── docs/
│   └── screenshots/          # 01_Login.png .. 16_Responsive_Mobile_Catalog.png
├── includes/
│   ├── auth.php
│   ├── footer.php
│   ├── functions.php
│   └── header.php
├── pages/                    # 11 consolidated core views
├── storage/
│   └── prescriptions/.gitkeep
├── submission/               # Preserved locally & git-ignored
├── tests/
│   └── run_tests.php
├── .gitignore
├── .htaccess
├── actions.php
├── index.php
├── LICENSE
└── README.md
```

---

## 3. Files and Folders Retained

- **All Application Code (`22` PHP files + `2` assets + `1` SQL file):** Unmodified; preserves MVC (`app/`), routing (`index.php`, `actions.php`, `api/router.php`), auth/RBAC (`includes/`), all `11` UI views (`pages/`), DB connection (`config/database.php`), and 10-table 3NF schema (`database/pharmacare.sql`).
- **Security & Git Config:** `.htaccess`, `.gitignore`, `LICENSE`, `storage/prescriptions/.gitkeep`.
- **Automated Test Suite:** `tests/run_tests.php`.
- **Visual Evidence:** All `16` `.png` screenshots inside `docs/screenshots/`.
- **Local Submission Package (`submission/`):** 100% preserved outside Git tracking (`CIS324_Assignment_Report_Review.md`, `CIS324_Assignment_Report.md`, `241-16-016_A_CIS324_Assignment.pdf`, ER diagrams, audits, checklists, `FINAL_SUBMISSION_README.md`, and `submission/screenshots/`).

---

## 4. Files and Folders Removed (Verified Safe)

| Removed File | Original Location | Reason for Removal & Safety Check |
| :--- | :--- | :--- |
| `FINAL_SUBMISSION_README.md` | Project root (`5,773 B`, git-ignored) | Exact byte-for-byte duplicate of `submission/FINAL_SUBMISSION_README.md` (verified by SHA-256). Preserved in `submission/` and timestamped backup. |
| `1cdd13d72a9062c84ef8ec7a04f24479.png` | `storage/prescriptions/` (`47,130 B`, git-ignored) | Unreferenced test screenshot of an unrelated enquiry form containing personal data; `0` rows exist in `pharmacare.prescriptions` and `pharmacare_test.prescriptions`. Preserved in timestamped backup. |
| `15_Responsive_Tablet_Dashboard_768px.png`, `16_Responsive_Mobile_Catalog_375px.png` | `docs/screenshots/` (git deletions staged) | User-renamed in workspace to `15_Responsive_Tablet_Dashboard_.png` and `16_Responsive_Mobile_Catalog.png`. User's renamed files preserved and staged. |

---

## 5. Markdown Files Archived into `backup_md/`

- `docs/Q2_WDLC_WEB_STANDARDS_REPORT.md` $\rightarrow$ `backup_md/docs/Q2_WDLC_WEB_STANDARDS_REPORT.md` *(updated screenshot links to match renamed PNGs)*
- `docs/Q2_FINAL_SUBMISSION_INTEGRITY_REPORT.md` $\rightarrow$ `backup_md/docs/Q2_FINAL_SUBMISSION_INTEGRITY_REPORT.md` *(updated reference to archived Q2 report path)*
- `tests/README.md` $\rightarrow$ `backup_md/tests/README.md`
- `backup_md/FINAL_PROJECT_CLEANUP_REPORT.md` *(this cleanup report)*

---

## 6. `README.md` Consolidation Summary

Replaced the previous 473-line report-style `README.md` with a concise, accurate project guide covering: project purpose, the 3 main modules (Inventory, Online Ordering, POS), verified tech stack, compact MVC/routing architecture, user roles & evaluation credentials, clean directory tree, XAMPP & database import instructions, `config/database.php` settings, testing instructions & verified results, GitHub/archive notes, and known limitations—removing dead links and historical coursework bloat. Zero broken `file:///` links remain (`18/18` verified).

---

## 7. GitHub Synchronization Summary

- **Commit Message:** `chore(project): clean repository and archive documentation`
- **Target Branch:** `main` on `https://github.com/mahdi-014/PharmaCare-WEB.git`
- **Tracking Policy Maintained:** `backup_md/` and renamed `docs/screenshots/` are tracked on GitHub; `submission/` and local prescription uploads remain git-ignored.

---

## 8. PHP Validation Results

- Executed `C:\xampp\php\php.exe -l` (`PHP 8.5.12`) across all **23 `.php` files** in the project.
- **Result:** **23 / 23 passed, 0 syntax errors.**

---

## 9. Database Verification Results

- Verified `database/pharmacare.sql` remains completely unmodified (`git diff database/pharmacare.sql` empty).
- Verified live PDO connection via `config/database.php` against MariaDB `10.11.16` (`pharmacare`): `SELECT COUNT(*) FROM medicines` returned `15` without exposing credentials.

---

## 10. Application Smoke-Test Results (Local XAMPP)

Executed live read-only HTTP checks against `http://localhost/PharmaCare-PHP/`:
- **Guest & Asset Routes:** `/` (`200`), `?page=login` (`200`), `?page=register` (`200`), `?page=catalog` (`200`), `assets/style.css` (`200`), `assets/script.js` (`200`), `api/router.php?action=search&keyword=Napa` (`200`).
- **Apache `.htaccess` Protection:** `config/database.php` (`403`), `database/pharmacare.sql` (`403`).
- **Role-Based Access Control (RBAC) & PHP Error Scan (`Fatal`/`Parse`/`Warning`/`Notice` = `False` across all `200` routes):**
  - `admin`: `dashboard`, `medicines`, `medicines&tab=categories`, `medicines&tab=suppliers`, `orders`, `sales`, `sales&tab=pos`, `users`, `catalog` $\rightarrow$ all `200 OK` (`PHP_ERR=False`).
  - `staff`: `dashboard`, `medicines`, `orders`, `sales` $\rightarrow$ all `200 OK` (`PHP_ERR=False`); `users` $\rightarrow$ `403 Forbidden`.
  - `customer`: `catalog`, `cart`, `orders` $\rightarrow$ all `200 OK` (`PHP_ERR=False`); `dashboard`, `users` $\rightarrow$ `403 Forbidden`.

---

## 11. Tests Not Executed & Why

- **`tests/run_tests.php` (`11` automated test cases):** Not executed in this session because `DB_PASS` for the restricted `pharmacare_test_user` account is external to the workspace (runner aborts safely unless valid `pharmacare_test` credentials are provided). Existing screenshot `docs/screenshots/14_Test_Results.png` reflects an earlier Nov 20 run (`10/11`, `90.9%`).
- **Write-Mutating Checkout / POS Transactions & Screen-Reader Tests:** Not executed during cleanup smoke tests to avoid mutating the live `pharmacare` database.

---

## 12. Backup Location

- **Full Pre-Cleanup Backup (`PharmaCare-PHP` + `.git`):**  
  `D:\PharmaCare-PHP-Complete\backups\pharmacare_cleanup_2026-11-25_2230\`
- **Baseline Audit:**  
  `D:\PharmaCare-PHP-Complete\backups\pharmacare_cleanup_2026-11-25_2230\CLEANUP_BASELINE_REPORT.md`

---

## 13. Git Commit Hash & 14. Push Verification

- **Committed Subject:** `chore(project): clean repository and archive documentation` on branch `main` (child of baseline commit `726b913`).
- Exact post-commit SHA-1 hash and `git push origin main` output are verified immediately upon pushing and reported in the completion response (to preserve a 100% clean working tree after the final commit).

---

## 15. Remaining Known Issues

- Dashboard `"Review Rx Queue"` link (`pages/dashboard.php:62`) passes `filter_rx=needs_review` while `pages/orders.php:109` reads `prescription`.
- Customer IDOR redirect in `pages/order_detail.php:112` calls `redirect(url('orders'))` instead of `redirect('orders')`.
- Admin user creation/reset (`actions.php:388,423`) and `User::createCustomer()` (`app/Model.php:42`) omit `'cost' => 12` (using default Bcrypt cost `10`), while customer registration (`actions.php:93`) uses `cost => 12`.
- Minor WCAG semantic/accessibility gaps (`h2` $\rightarrow$ `h4`/`h5` skips, `orders.php:198` unlabeled search input, `medicines.php` `title`-only icon buttons) and `6` `[STUDENT WRITE HERE]` blocks in `submission/CIS324_Assignment_Report_Review.md` remain outside cleanup scope.
