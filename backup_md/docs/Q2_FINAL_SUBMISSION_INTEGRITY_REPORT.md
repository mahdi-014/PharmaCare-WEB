# PharmaCare — Q2 Final Submission Integrity Verification Report

**Audit Date:** November 25, 2026  
**Audited Repository:** `D:\PharmaCare-PHP-Complete\PharmaCare-PHP`  
**Subject:** Q2 Implementation, Canonical Report/PDF Consistency, Git Tracking, and Evidence Verification (`726b913`)  

---

## 1. Verified GitHub Remote & Repository State

| Verification Item | Observed Command Output | Status |
| :--- | :--- | :---: |
| **`origin` Fetch & Push URL** | `https://github.com/mahdi-014/PharmaCare-WEB.git` | **Verified (Matches Target)** |
| **Active Branch & Tracking** | `* main 726b913 [origin/main]` | **Verified** |
| **Latest Commit** | `726b913b3e45dac0e365f74945960d8885542393` — `docs(assignment): complete CIS324 Q2 WDLC and web standards` | **Verified** |
| **Push Status (`origin/main`)** | `Your branch is up to date with 'origin/main'.` (`570e6a0..726b913`) | **Verified Pushed** |
| **Working Tree State** | `nothing to commit, working tree clean` (prior to creating this integrity report) | **Verified Clean** |
| **Tracked Files in `726b913`** | `assets/style.css`, `includes/header.php`, `pages/dashboard.php`, `docs/Q2_WDLC_WEB_STANDARDS_REPORT.md`, `docs/screenshots/15_Responsive_Tablet_Dashboard_768px.png`, `docs/screenshots/16_Responsive_Mobile_Catalog_375px.png` | **Verified** |

---

## 2. Canonical Report & Consolidated PDF Consistency

Inspected files:
- [`submission/CIS324_Assignment_Report_Review.md`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/submission/CIS324_Assignment_Report_Review.md) (`155,262` bytes)
- [`submission/241-16-016_A_CIS324_Assignment.pdf`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/submission/241-16-016_A_CIS324_Assignment.pdf) (`6,754,077` bytes, `43` pages)
- [`backup_md/docs/Q2_WDLC_WEB_STANDARDS_REPORT.md`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/backup_md/docs/Q2_WDLC_WEB_STANDARDS_REPORT.md) *(archived from `docs/`)*

### Consistency Verification Findings
1. **Canonical Markdown Q2 Content:** Verified present in `submission/CIS324_Assignment_Report_Review.md` (Sections `2.1`, `2.2`, `2.3`), covering all 7 WDLC phases (Planning, Analysis, Design, Development, Testing, Deployment, Maintenance), WDLC Summary Table, 3 Web Standards/Accessibility evaluations, contrast table, Standards Verification Table, and Q2 conclusion.
2. **Consolidated PDF Q2 Content:** Directly inspected pages 1–43 of `submission/241-16-016_A_CIS324_Assignment.pdf`. Confirmed:
   - **Page 2 (TOC):** Reflects updated Q2 section headings (`2.1`, `2.2`, `2.3`).
   - **Pages 6–12:** Render all 7 WDLC phases, WDLC Summary Table, Semantic HTML5 evaluation, Responsive Web Design evaluation, **Figure 2.1** (Page 10, Tablet `768×1024`), **Figure 2.2** (Page 11, Mobile `375×812`), Contrast Verification Table (Pages 11–12), Standards Verification Table, and Q2 conclusion.
   - **Mirrored Package Integrity:** SHA-256 hashes of both `CIS324_Assignment_Report_Review.md` and `241-16-016_A_CIS324_Assignment.pdf` in `PharmaCare-PHP/submission/` match `D:\PharmaCare-PHP-Complete\submission\` identically.
3. **Agreement with Dedicated Q2 Report:** All WDLC claims, line references, contrast calculations, Edge CDP overflow measurements (`18/18`), and limitation boundaries in `docs/Q2_WDLC_WEB_STANDARDS_REPORT.md` match `submission/CIS324_Assignment_Report_Review.md`.
4. **Referenced Images & Links:**
   - All **16/16** `![...](...)` image links in `submission/CIS324_Assignment_Report_Review.md` (`01`–`16`) resolve to existing `.png` files in both `docs/screenshots/` and `submission/screenshots/`.
   - All **53/53** `file:///` links in `docs/Q2_WDLC_WEB_STANDARDS_REPORT.md` resolve to existing local files/directories (`0` missing).

### Observed Formatting & Report-Scope Notes
- **Minor PDF Rendering Artifacts in `241-16-016_A_CIS324_Assignment.pdf`:** Python `markdown` + Edge print does not render LaTeX delimiters (`$\ge 4.5:1$`, `$\rightarrow$`, `$2^{12}$` appear literally on Pages 11, 12, 23, 35, 38), and two Q2 code excerpts nested inside bullet lists (Pages 8–9) rendered inline rather than as fenced `<pre>` blocks.
- **Unmodified Non-Q2 Sections:** Cover page (`Final Release Commit: bfd0c51`, `30 Application Files`), Q4/Q5 `[STUDENT WRITE HERE]` placeholders, Figure `5.1` caption (`24 Test Cases` vs `11` shown in image), and Annex A date (`November 09, 2026`) remain untouched per Q2 change control.

---

## 3. Git Tracking vs. Local Submission Availability

### Tracked on GitHub (`origin/main` — 49 Files Before This Report)
- Application source code (`index.php`, `actions.php`, `api/router.php`, `app/`, `config/`, `includes/`, `pages/`, `assets/`, `database/pharmacare.sql`, `storage/prescriptions/.gitkeep`)
- Configuration & guides (`.htaccess`, `.gitignore`, `LICENSE`, `README.md`)
- Verification & Q2 artifacts (`tests/run_tests.php`, `tests/README.md`, `docs/Q2_WDLC_WEB_STANDARDS_REPORT.md`, `docs/screenshots/01_...png` through `16_...png`)

### Intentionally Git-Ignored Locally (`git status --ignored`)
- `submission/` (contains `241-16-016_A_CIS324_Assignment.pdf`, `CIS324_Assignment_Report_Review.md`, `CIS324_Assignment_Report.md`, `PharmaCare_System_Architecture.md`, `PharmaCare_ER_Diagram.{mmd,pdf,png}`, `ERD_Analysis_Report.md`, `FINAL_ASSIGNMENT_AUDIT.md`, `REPORT_REVIEW_CHECKLIST.md`, `FINAL_SUBMISSION_README.md`, `screenshots/`)
- `FINAL_SUBMISSION_README.md`
- `storage/prescriptions/1cdd13d72a9062c84ef8ec7a04f24479.png`

### Submission Requirement Analysis (Consequences & Boundaries)
- **Is the consolidated PDF on GitHub?** **No.** Neither `submission/241-16-016_A_CIS324_Assignment.pdf` nor `submission/CIS324_Assignment_Report_Review.md` exists in `origin/main` because `.gitignore` explicitly excludes `submission/` (configured after the earlier instruction to keep only code, `README.md`, and screenshots on GitHub).
- **Assignment Requirement Assessment:** Based on [`submission/REPORT_REVIEW_CHECKLIST.md`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/submission/REPORT_REVIEW_CHECKLIST.md#L138-L149) and [`submission/FINAL_ASSIGNMENT_AUDIT.md`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/submission/FINAL_ASSIGNMENT_AUDIT.md#L94-L95) (note: the original guideline file `CIS324_Assignment_Fall_2026.pdf` is not unpacked in `D:\PharmaCare-PHP-Complete`), the assignment specifies two submission artifacts: (1) a consolidated PDF report (`241-16-016_A_CIS324_Assignment.pdf`) and (2) a GitHub repository (`241-16-016_A_CIS324_Project`) containing complete source code, SQL export, and `README.md` with evaluation credentials.
- **Local Submission Package Completeness:** Both `PharmaCare-PHP\submission\` and `D:\PharmaCare-PHP-Complete\submission\` contain all required deliverables (`11` root files + `17` files inside `screenshots/`).
- **Unmodified Git Rules:** `.gitignore` was preserved untouched; no ignored files were force-added or duplicated.

---

## 4. Evidence Verification & Reproducibility Matrix

### A. Directly Verified / Executed in This Phase
| Evidence Claim | Reproducibility & Observed Proof | Source / Command Proof |
| :--- | :--- | :--- |
| **PHP Lint (`23/23`, `0` errors)** | Executed after final code edits (`C:\xampp\php\php.exe -l` across all 23 `.php` files). | `PHP_FILES=23 SYNTAX_ERRORS=0` |
| **Responsive Checks (`18/18`, `0px` overflow)** | Executed live via headless Edge CDP across `dashboard`, `catalog`, `medicines`, `orders`, `sales`, `users` at `1440×900`, `768×1024`, and `375×812`. | `overflowPx` ranged `-15` to `0` across all 18 runs |
| **Contrast Ratios & CSS Values** | Verified exact hex values in [`assets/style.css`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/assets/style.css) (`#0f766e`, `#115e59`, `#b45309`, `#16a394`), [`pages/dashboard.php`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/pages/dashboard.php#L98) (`btn-info text-dark`), and [`includes/header.php`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/includes/header.php#L88-L101) (`title`, `aria-label`); ratios verified mathematically (`17.85:1`, `5.47:1–7.58:1`, `10.72:1`, `4.51:1`, `3.13:1 / 5.70:1`). | CSS inspection + Python sRGB luminance calculation |
| **Local HTTP Checks** | Executed after final edits against local XAMPP: `http://localhost/PharmaCare-PHP/` (`200 OK`), `?page=catalog` (`200 OK`). `.htaccess` `403` blocks verified prior to CSS/HTML doc edits. | `HTTP_HOME=200 HTTP_CAT=200` |
| **Responsive Screenshots** | Saved and visually verified on disk and in `origin/main`. | `15_Responsive_Tablet_Dashboard_768px.png` (`142,859 B`), `16_Responsive_Mobile_Catalog_375px.png` (`64,117 B`) |

### B. Previously Captured Evidence (Not Re-Executed Here)
- [`docs/screenshots/14_Test_Results.png`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/docs/screenshots/14_Test_Results.png): Captured Nov 20, 2026 (`10 Passed / 1 Failed`, `90.9%`, `3.571s` due to 1 empty directory that has since been removed; current repo check shows `EMPTY_DIRS=0`).

### C. Unexecuted Tests (Explicitly Bounded)
- [`tests/run_tests.php`](file:///D:/PharmaCare-PHP-Complete/PharmaCare-PHP/tests/run_tests.php) (`11` test cases) was **not** executed live because `DB_PASS` for `pharmacare_test_user` is external to the workspace.
- Assistive-technology screen-reader testing (NVDA/JAWS), automated axe/Lighthouse accessibility scans, and live remote production server deployment were **not** executed.

---

## 5. Remaining Limitations

1. **GitHub vs. LMS PDF Visibility:** `submission/241-16-016_A_CIS324_Assignment.pdf` exists locally in `submission/` and `D:\PharmaCare-PHP-Complete\submission\` for portal upload, and is **not** hosted on `origin/main` due to `.gitignore`.
2. **Remaining Academic Sections Outside Q2:** `submission/CIS324_Assignment_Report_Review.md` retains `6` `[STUDENT WRITE HERE]` blocks in Q4 and Q5 (line 1134 in Q4; lines 1326, 1365, 1390, 1416, 1461 in Q5) plus older metadata/PDF LaTeX formatting quirks outside Q2.
3. **Repository Naming Format Note:** Remote repository is named `PharmaCare-WEB.git` rather than `241-16-016_A_CIS324_Project` as noted in `REPORT_REVIEW_CHECKLIST.md:146`.
4. **Open Non-Blocking UI/Source Quirks:** Unfixed heading-level skips (`h2` $\rightarrow$ `h4`/`h5`), missing `<label>` on `pages/orders.php:198`, `title`-only icon buttons in `pages/medicines.php`, `filter_rx` vs `prescription` mismatch (`pages/dashboard.php:62` vs `pages/orders.php:109`), and `order_detail.php:112` `redirect(url('orders'))` double-encoding remain untouched per Q2 scope.

---

## 6. Exact Next Action Required

1. **Proceed with Remaining Assignment Questions (Q3–Q9) & Student Authorship:** Keep Q2 untouched and complete the remaining rubric requirements (`[STUDENT WRITE HERE]` justifications in Q4 and Q5, plus optional cleanup of `CIS324_Assignment_Report_Review.md` markdown/LaTeX formatting before final PDF export).
2. **Final Submission Verification:** Upload the final regenerated `241-16-016_A_CIS324_Assignment.pdf` from `D:\PharmaCare-PHP-Complete\submission\` to the university submission portal (and confirm whether the instructor also requires the repository renamed to `241-16-016_A_CIS324_Project` or a copy of the PDF inside GitHub).
3. *(Optional)* Decide whether to commit and push `docs/Q2_FINAL_SUBMISSION_INTEGRITY_REPORT.md` to `origin/main` or keep it as a local verification record.
