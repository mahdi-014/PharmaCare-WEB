# PharmaCare — CIS324 Assignment Q2 Implementation & Verification Report

**Course:** CIS324 Web Engineering — Fall 2026 (Section A)  
**Topic:** Topic 13 — Pharmacy Inventory and Online Medicine Ordering System (`PharmaCare`)  
**Question Covered:** Task 1, Question 2 — Web Development Life Cycle (WDLC) & Web Standards (2 Marks, CO1)  
**Repository:** [https://github.com/mahdi-014/PharmaCare-WEB.git](https://github.com/mahdi-014/PharmaCare-WEB.git) (Branch: `main`)  
**Student:** Md. Mahdi Hasan (ID: `241-16-016`)  

---

## 1. Assignment Requirement

Question 2 of the CIS324 Fall 2026 assignment requires:
1. Explaining how **all seven phases** of the Web Development Life Cycle (WDLC) apply to the PharmaCare project: **Planning**, **Analysis**, **Design**, **Development**, **Testing**, **Deployment**, and **Maintenance**.
2. Identifying and evaluating **at least three relevant web standards or accessibility considerations** (Semantic HTML5, Responsive Web Design, and Color Contrast & Accessibility) using actual project code, contrast calculations, and verifiable evidence.
3. Updating the canonical assignment report with these evidence-backed Q2 sections without breaking the existing architecture, database schema, or unrelated sections (Q1, Q3–Q9).

---

## 2. Seven-Phase WDLC Mapping (PharmaCare Context)

| Phase | Web Engineering Definition | PharmaCare Application & Key Decisions | Source / Documentary Evidence | Phase Outcome |
| :--- | :--- | :--- | :--- | :--- |
| **1. Planning** | Defines scope, objectives, target users, constraints, and technology stack. | Scoped 3 modules (**Inventory Management**, **Online Medicine Ordering**, **POS Counter Billing**) for **Administrator**, **Staff Pharmacist**, and **Customer** roles using a zero-framework stack (PHP 8.1+, MySQL/MariaDB PDO, HTML5, CSS3, Bootstrap 5.3.3, Vanilla JS). | [`README.md`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/README.md), [`submission/PharmaCare_System_Architecture.md`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/submission/PharmaCare_System_Architecture.md) | Bounded project scope and dependency-free tech stack |
| **2. Analysis** | Captures functional requirements and non-functional quality/security constraints. | Modeled batch/expiry classification (`medicine_status()`), OTC vs. Prescription (`Rx`) segregation, registration/auth, POS & online order tracking, RBAC/IDOR rules, concurrent stock protection (`FOR UPDATE`), and immutable historical snapshots. | [`includes/functions.php`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/includes/functions.php#L225-L234), [`includes/auth.php`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/includes/auth.php), [`app/Controller.php`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/app/Controller.php#L22-L30), [`submission/ERD_Analysis_Report.md`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/submission/ERD_Analysis_Report.md) | Concrete functional spec, RBAC rules, and state invariants |
| **3. Design** | Specifies relational schema, MVC topology, routing flow, and responsive UI layouts. | Designed a 3NF schema (**10 InnoDB tables, 14 foreign keys, 10 `CHECK` constraints**); separated Front Controller (`index.php`), POST PRG dispatcher (`actions.php`), JSON API (`api/router.php`), Controller/Models/InventoryService (`app/`), and responsive sidebar/grid layout (`assets/style.css`). | [`database/pharmacare.sql`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/database/pharmacare.sql), [`submission/PharmaCare_ER_Diagram.pdf`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/submission/PharmaCare_ER_Diagram.pdf), [`assets/style.css`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/assets/style.css) | Verified 3NF schema, MVC blueprint, and mobile-first UI design |
| **4. Development** | Implements server-side logic, DB integration, UI markup, styling, and client scripts. | Authored strict PHP 8 (`declare(strict_types=1)`), PDO singleton (`ATTR_EMULATE_PREPARES => false`), FEFO batch locking (`allocateFefoStock()`), hardened sessions/CSRF, 11 views (`pages/*.php`), and Vanilla JS `fetch()` search/cart/polling (`assets/script.js`). | [`config/database.php`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/config/database.php), [`index.php`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/index.php), [`actions.php`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/actions.php), [`api/router.php`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/api/router.php), [`app/`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/app/), [`pages/`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/pages/), [`assets/script.js`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/assets/script.js) | Working full-stack application (`8,460` lines across 26 core files) |
| **5. Testing** | Evaluates syntax, logic, security, API behavior, and UI responsiveness while bounding untested areas. | Ran static `php -l` (23/23 PHP files, 0 errors), hygiene checks, live XAMPP HTTP/403 checks, and headless Edge viewport checks (`1440px`, `768px`, `375px`); automated `tests/run_tests.php` defines 11 test cases (`14_Test_Results.png` recorded `10/11` pre-cleanup; not re-run in this session without external `DB_PASS`). | [`tests/run_tests.php`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/tests/run_tests.php), [`docs/screenshots/14_Test_Results.png`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/docs/screenshots/14_Test_Results.png), [`15_Responsive_Tablet_Dashboard_.png`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/docs/screenshots/15_Responsive_Tablet_Dashboard_.png), [`16_Responsive_Mobile_Catalog.png`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/docs/screenshots/16_Responsive_Mobile_Catalog.png) | Verified static, HTTP, and responsive integrity; bounded untested flows |
| **6. Deployment** | Configures runtime stack, DB initialization, env config, and server security rules. | Verified **locally on XAMPP** (`Apache/2.4.65`, `PHP 8.5.12`, `MariaDB 10.11.16`, junctions in `C:\xampp\htdocs\`, `pharmacare.sql` imported, `.htaccess` blocking `config/`, `database/`, `storage/prescriptions/` with `403`). **Not deployed to a live production server.** | [`.htaccess`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/.htaccess), [`config/database.php`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/config/database.php), [`database/pharmacare.sql`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/database/pharmacare.sql) | Verified local XAMPP runtime & access protection |
| **7. Maintenance** | Supports backup/recovery, patching, log monitoring, data hygiene, and bug fixes. | **Existing:** Admin user enable/disable/reset (`pages/users.php`), medicine active toggle (`pages/medicines.php`), dashboard expiry/low-stock alerts (`pages/dashboard.php`), `error_log()`, external zip backup. **Recommended:** Scheduled `mysqldump` + Rx file backups, log rotation, PHP/MariaDB patches, fixing minor URL/cost quirks. | [`pages/users.php`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/pages/users.php), [`pages/medicines.php`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/pages/medicines.php), [`pages/dashboard.php`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/pages/dashboard.php) | Operational maintenance controls + concrete upkeep plan |

---

## 3. Web Standards & Accessibility Evaluation

### 3.1 Standard 1 — Semantic HTML5

**Findings in Source Code:**
- **Structural Landmarks:** [`includes/header.php`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/includes/header.php#L79-L140) and [`includes/footer.php`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/includes/footer.php#L2) establish `<aside class="app-sidebar">`, `<nav class="sidebar-nav">`, `<header class="app-topbar">`, `<main class="content">`, and `<footer>`. Standalone/receipt views use `<section>` ([`pages/login.php`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/pages/login.php#L14), [`pages/register.php`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/pages/register.php#L14), [`pages/invoice.php`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/pages/invoice.php#L75)). *(`<article>` is not used.)*
- **Form Labels & Input Constraints:** Forms across `login.php`, `register.php`, `cart.php`, `medicines.php`, `sales.php`, `order_detail.php`, and `users.php` bind `<label for="...">` to control `id`s and apply native HTML5 types (`tel` + BD regex `pattern="(01|\+8801)[3-9][0-9]{8}"`, `email`, `date`, `number`, `file` + `accept=".jpg,.jpeg,.png,.pdf"`).
- **Images & Native Controls:** The single `<img>` in the application ([`pages/order_detail.php`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/pages/order_detail.php#L555)) includes `alt="Doctor Prescription"`. Interactive controls use native `<button>` and `<a href>` elements.

**Actual Code Excerpt ([`pages/register.php`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/pages/register.php#L76-L82)):**
```html
<div class="col-md-6 mb-2">
    <label class="form-label" for="phone">Phone Number *</label>
    <div class="input-group">
        <span class="input-group-text bg-white border-end-0"><i class="bi bi-telephone"></i></span>
        <input class="form-control border-start-0" type="tel" name="phone" id="phone" maxlength="15" pattern="(01|\+8801)[3-9][0-9]{8}" placeholder="017XXXXXXXX" required>
    </div>
</div>
```

**Observed Semantic Gaps (Unmodified):**
- **Heading skips:** Topbar renders `<h1>` (`includes/header.php:123`), views start with `<h2>`, and cards/empty states/modals drop to `<h4>` or `<h5>` without intermediate `<h3>` (e.g., `pages/cart.php:34`, `pages/catalog.php:90`, `pages/users.php:200`).
- **Missing Search Label:** [`pages/orders.php`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/pages/orders.php#L198) uses `placeholder="Search Order #, customer, phone..."` without an explicit `<label>`.
- **Icon-Only Table Buttons:** Action controls in [`pages/medicines.php`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/pages/medicines.php#L246-L257) rely on `title` attributes rather than `aria-label`.

---

### 3.2 Standard 2 — Responsive Web Design

**CSS & Grid Architecture Inspected:**
- Bootstrap 5.3.3 grid (`row g-3`, `col-12 col-sm-6 col-xl`, `col-md-6 col-xl-4`, `col-lg-4`/`col-lg-8`) + `.table-responsive` wrappers around data tables ([`assets/style.css`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/assets/style.css#L205-L215), setting `.table { min-width: 720px; }` below `575px` while exempting `.invoice-sheet .table`).
- Custom media queries in [`assets/style.css`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/assets/style.css#L150-L202):
  - `@media (max-width: 991px)`: Collapses `--sidebar-width` to `88px`, hides nav link text (`display: none`), hides `.login-showcase`.
  - `@media (max-width: 575px)`: Shifts `.app-sidebar` (`280px`) off-canvas via `transform: translateX(-100%)`, removes `.app-main` left margin, reveals `.sidebar-backdrop` when `body.sidebar-open` is toggled by [`assets/script.js`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/assets/script.js#L54-L67).
  - `@media print`: Hides `.no-print`, `.app-sidebar`, and `.app-topbar` for invoice printing.

**Live Edge CDP Viewport Test Results (Admin Session, Local XAMPP):**

| Viewport | Breakpoint | Sidebar Width / X | `app-main` Left Margin | Pages Tested (`overflowPx = scrollWidth - innerWidth`) |
| :--- | :--- | :---: | :---: | :--- |
| **Desktop (`1440×900`)** | Default (`>= 992px`) | `280px` / `x=0` | `280px` | `dashboard` (`-15`), `catalog` (`-15`), `medicines` (`-15`), `orders` (`0`), `sales` (`0`), `users` (`0`) |
| **Tablet (`768×1024`)** | `@media (max-width: 991px)` | `88px` / `x=0` | `88px` | `dashboard` (`-15`), `catalog` (`-15`), `medicines` (`-15`), `orders` (`-7`), `sales` (`0`), `users` (`-15`) |
| **Mobile (`375×812`)** | `@media (max-width: 575px)` | `280px` / `x=-280` | `0px` | `dashboard` (`0`), `catalog` (`0`), `medicines` (`0`), `orders` (`0`), `sales` (`0`), `users` (`0`) |

All **18/18** viewport-page combinations exhibited **0px horizontal overflow**. Note that at `768px`, the Bootstrap `d-lg-none` toggle button ([`includes/header.php`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/includes/header.php#L119)) is visible even though the drawer slide-in rule activates only at `<= 575px`.

Visual evidence captured:
- [`docs/screenshots/15_Responsive_Tablet_Dashboard_.png`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/docs/screenshots/15_Responsive_Tablet_Dashboard_.png)
- [`docs/screenshots/16_Responsive_Mobile_Catalog.png`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/docs/screenshots/16_Responsive_Mobile_Catalog.png)

---

### 3.3 Standard 3 — Color Contrast and Accessibility (WCAG 2.2 AA)

**WCAG 2.2 AA Calculations & Minimal Fixes Applied:**
Using exact sRGB relative luminance on stylesheet and Bootstrap 5.3.3 hex values, four accessibility issues were discovered and minimally corrected:

| Location | Original Implementation | Measured Contrast (Before) | Minimal Fix Applied | Verified Contrast (After) |
| :--- | :--- | :---: | :--- | :---: |
| [`assets/style.css`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/assets/style.css#L2-L3) | `--brand: #0d9488`, `--brand-dark: #0f766e` (`#ffffff` text on `.btn-primary` / `.sidebar-nav a.active`; `#0d9488` on `#ffffff` `.btn-outline-primary`) | **3.74:1** (Fail $< 4.5:1$ normal text) | `--brand: #0f766e`, `--brand-dark: #115e59` | **5.47:1 – 7.58:1** (Pass AA/AAA) |
| [`assets/style.css`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/assets/style.css#L98) | `.icon-warning` `#d97706` on `#fef3c7` | **2.86:1** (Fail $< 3.0:1$ UI graphic) | Changed foreground to `#b45309` | **4.51:1** (Pass AA $\ge 3.0:1$ & $4.5:1$) |
| [`pages/dashboard.php`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/pages/dashboard.php#L98) | `btn-info text-white` (`#ffffff` on `#0dcaf0`) | **1.96:1** (Fail) | Changed `text-white` to `text-dark` (`#000000` on `#0dcaf0`) | **10.72:1** (Pass AAA) |
| [`includes/header.php`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/includes/header.php#L88-L101) | Sidebar `<a>` links & Logout `<button>` lacked `aria-label` when tablet CSS hid `<span>` (`display: none`) | No accessible name in icon-only tablet mode | Added `title="<?= e($label) ?>" aria-label="<?= e($label) ?>"` and `title="Logout" aria-label="Logout"` | Accessible name preserved at `<= 991px` |

**Additional Verified Contrast Ratios:**
- `--ink` (`#0f172a`) on `--surface` (`#ffffff`): **17.85:1**; on `--page` (`#f8fafc`): **17.06:1** (Pass).
- `--muted` (`#64748b`) on `#ffffff`: **4.76:1**; on `#f8fafc`: **4.55:1** (Pass).
- Sidebar nav (`#94a3b8` on `#0f172a`..`#1e293b`): **5.71:1–6.96:1** (Pass).
- `.icon-brand` (`#0f766e` on `#f0fdfa`): **5.25:1**; `.data-meta` (`#0f766e` on `#eaf9f5`): **5.05:1** (Pass).
- Bootstrap badges: `text-bg-success` (`#fff`/`#198754` = **4.53:1**), `text-bg-danger` (`#fff`/`#dc3545` = **4.53:1**), `text-bg-warning` (`#000`/`#ffc107` = **12.88:1**), `text-bg-info` with `text-dark` (`#000`/`#0dcaf0` = **10.72:1**).
- Focus indicator (`.btn:focus-visible, a:focus-visible` `3px solid #16a394` at `assets/style.css:214`): **3.13:1** on `#ffffff` and **5.70:1** on `#0f172a` (Pass WCAG 2.2 SC 1.4.11 $\ge 3.0:1$).

---

## 4. Testing Evidence, Limitations & Changes Made

### Verification Run Summary
- **PHP Static Lint:** Executed `C:\xampp\php\php.exe -l` (`PHP 8.5.12`) across all **23 `.php` files** — **0 syntax errors**.
- **Repository Hygiene:** Verified `0` empty directories, `0` zero-byte files, `1` `.htaccess`, `1` `.gitignore`.
- **XAMPP HTTP & Security Rules:** Verified `200 OK` on `/`, `?page=login`, `?page=catalog`, and `api/router.php?action=search&keyword=Napa`; verified `403 Forbidden` on `config/database.php`, `database/pharmacare.sql`, and `storage/prescriptions/`.
- **Edge Responsive Check:** Verified `18/18` live viewport/page combinations (`1440×900`, `768×1024`, `375×812`) with `0px` overflow.
- **Automated Test Suite (`tests/run_tests.php`):** Defines 11 tests (`TC-1.1`..`TC-4.2`); not re-executed in this session because `DB_PASS` for `pharmacare_test_user` is external to the workspace. Earlier screenshot `docs/screenshots/14_Test_Results.png` recorded `10/11` (`90.9%`, `TC-1.2` empty dir since resolved).

### Identified Limitations
1. Heading-hierarchy level skips (`h2` $\rightarrow$ `h4`/`h5`), missing `<label>` on `orders.php:198`, and `title`-only icon buttons in `medicines.php` remain open semantic improvements.
2. Live assistive-technology (NVDA/JAWS) screen-reader audit and keyboard tab-order walkthrough across modals were not executed.
3. No live internet production deployment exists; verification applies strictly to local XAMPP.

### Files Created or Modified for Q2
- **Modified (Minimal Accessibility Fixes):** [`assets/style.css`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/assets/style.css), [`pages/dashboard.php`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/pages/dashboard.php), [`includes/header.php`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/includes/header.php)
- **Created (Verification Evidence & Q2 Report):** [`backup_md/docs/Q2_WDLC_WEB_STANDARDS_REPORT.md`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/backup_md/docs/Q2_WDLC_WEB_STANDARDS_REPORT.md), [`docs/screenshots/15_Responsive_Tablet_Dashboard_.png`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/docs/screenshots/15_Responsive_Tablet_Dashboard_.png), [`docs/screenshots/16_Responsive_Mobile_Catalog.png`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/docs/screenshots/16_Responsive_Mobile_Catalog.png)
- **Updated Locally (Canonical Assignment Report & PDF, in git-ignored `submission/`):** [`submission/CIS324_Assignment_Report_Review.md`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/submission/CIS324_Assignment_Report_Review.md), [`submission/241-16-016_A_CIS324_Assignment.pdf`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/submission/241-16-016_A_CIS324_Assignment.pdf)

---

## 5. Final Q2 Compliance Status

**Status: Q2 Compliant (Documentation + Evidence Verified)**  
All seven WDLC phases and three web standards/accessibility areas are documented using real PharmaCare source code, contrast calculations, live XAMPP runtime checks, and browser viewport screenshots, with architectural, database, and authorization logic preserved untouched.
