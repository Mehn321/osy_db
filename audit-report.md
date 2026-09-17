# OSY DB — Full Codebase Audit & Remediation Report

**Repo:** github.com/Mehn321/osy_db · **Commit audited:** `82c824a` · **Date:** 2026-09-17
**Stack (verified):** PHP 8.4 (vanilla OOP, no framework, no Composer) + MySQL 8/Aiven Cloud + Apache (`php:8.4-apache-bookworm`) + Tailwind CSS 4 (npm build) + vanilla JS. Deployed on Render.com via the repo's own Dockerfile.
**Method:** Single-session systematic review across 6 domains (not literally parallel sub-agents — this environment doesn't expose that — but each domain was worked end-to-end and cross-checked against actual code, the real npm registry, real CVE databases, and the real Docker/Render deployment config before being reported). Every finding below was verified by reading the actual source, not inferred from naming or patterns alone. Two findings that looked like bugs during investigation (an unused `Location` class, missing indexes) were disproven on closer inspection and are **not** included, per the "no false positives" instruction.

---

## Executive Summary

The application is a small, single-developer PHP system with genuinely solid fundamentals in places — parameterized SQL queries almost everywhere, bcrypt password hashing, DB-persisted OTP throttling, real server-side file-upload validation, and a centrally-enforced CSRF layer are all correctly built. However, the repository has **live production database credentials and a live API key committed to git history since July 2026**, and roughly **twenty unauthenticated scripts sitting in the public web root that directly rewrite user data** in the `users` table with no login check — several were clearly meant to be one-off local utilities but were never removed. There is **no automated test suite** (the two "test" files in the repo are a manual QA checklist and a superficial smoke-test script, neither of which would have caught any of the authorization bugs found and fixed earlier in this engagement). Performance is currently fine only because the dataset is tiny (~16 seed profiles); the matching engine and three duplicated dashboard widgets will not scale past a few hundred real youth records without rework. **Overall health: C- / Needs Urgent Attention** — the architecture is salvageable and the team clearly has security awareness in places (CSRF, upload validation, security headers), but the secret exposure and unauthenticated scripts need same-day action.

**Critical findings: 2 · High: 5 · Medium: 6 · Low: 5** (18 total, all independently verified)

---

## CRITICAL

### C-1. Live database credentials and API key committed to git
- **Category:** Security · **Confidence: HIGH**
- **File:** `.env.bak` (root), tracked since commit `a83612f` (2026-07-01)
- **Description:** Contains a real Aiven MySQL hostname, username, password, and a real Gemini API key. `.gitignore` excludes `.env`, `.env.local`, etc., but not `.env.bak` — the wildcard gap is exactly how this leaked. This has been sitting in a public GitHub repo for over two months.
- **Fix:** (1) Rotate the Aiven DB password and the Gemini API key immediately — treat both as burned regardless of any code fix. (2) `git rm .env.bak`, add `*.bak` and `.env.*` (already partly covered) to `.gitignore`. (3) Purge the file from git history with `git filter-repo` or BFG Repo-Cleaner, then force-push and have any other clone re-fetch. (4) Redeploy with the new credentials via Render's env var UI (already the intended mechanism — `render.yaml` marks these as `sync: false`, i.e., meant to be set out-of-band, so this file was never even supposed to exist).
- **Effort:** ~2 hours (rotation + history rewrite + redeploy verification)

### C-2. ~20 unauthenticated scripts in the web root that mutate real user data
- **Category:** Security · **Confidence: HIGH**
- **Files:** `get_user_credentials.php`, `fix_admin_email.php`, `update_emails_final.php`, `update_user_emails_demo.php`, `change_lydo_email.php`, `debug_session.php`, plus ~15 more debug/patch/check scripts at root
- **Description:** Every one of these runs unconditional SQL against the live `users` table with zero `requireLogin()`/`requireRole()` check the moment its URL is hit — e.g. `fix_admin_email.php` unconditionally runs `UPDATE users SET email = ... WHERE username = 'admin1'`. I checked whether `.htaccess` protects them: it doesn't. The root `.htaccess` blocklist only covers specific filenames/extensions (`migrate.*\.php`, `test_.*\.php`, `sms_.*\.php`, `.sql`, `.log`, etc.), and I confirmed by regex that none of the above match — `test-modal.php` isn't caught by `test_.*\.php` (hyphen vs. underscore), and files like `get_user_credentials.php` or `debug_session.php` were simply never added to the list at all. I also confirmed `.htaccess` *is* actually honored in production (the official `php:8.4-apache-bookworm` image sets `AllowOverride All` by default, and `render.yaml` deploys the same Dockerfile) — so this isn't a theoretical gap, it's a real one specific to these filenames.
- **Fix:** Delete these files outright — they have zero live callers (verified via grep across `pages/`, `api/`, `Classes/`, `includes/`). If any are still needed for ad-hoc admin use, move them out of the web root entirely and gate with `requireRole(['lydo'])`.
- **Effort:** ~20 minutes to delete the highest-risk ones (see Quick Wins), ~1 hour to clean up all 43 stale root files from C-2/L-5 together.

---

## HIGH

### H-1. Tracked log files leak real PII despite `.gitignore`
- **Category:** Security · **Confidence: HIGH**
- **Files:** `email_log.txt`, `sms_log.txt` (root)
- **Description:** Both are listed in `.gitignore`, but `git ls-files` confirms both are still tracked — they were committed before being added to `.gitignore`, which doesn't retroactively untrack them. Contents include real email addresses and real Philippine mobile numbers from actual signup/login flows.
- **Fix:** `git rm --cached email_log.txt sms_log.txt`, purge from history alongside C-1, verify the app writes these to a path outside the repo (or at minimum confirm the untracked local file still gets created correctly at runtime).
- **Effort:** 30 minutes (plus shared history-rewrite cost with C-1)

### H-2. Login brute-force lockout is bypassed by dropping cookies
- **Category:** Security · **Confidence: HIGH**
- **File:** `Classes/User.php:203` (`login()`)
- **Description:** Failed-attempt counting and the 15-minute lockout are stored entirely in `$_SESSION['login_attempts']`/`$_SESSION['login_lockout_until']`. Since this is the pre-authentication login endpoint, an attacker doing automated credential stuffing simply omits the session cookie on each request (or clears it) and gets a fresh, empty attempt counter every time — the 5-attempt threshold never triggers. I verified the OTP-attempt equivalent (`user_2fa_codes.attempts`) is correctly DB-persisted and doesn't have this problem, so this is an isolated gap in the password path specifically.
- **Fix:** Move attempt tracking to a DB table (or Redis/APCu if available) keyed by `IP + normalized username`, independent of session state.
- **Effort:** 2–3 hours

### H-3. Live applicant-review page is missing rate limiting a sibling file already implements
- **Category:** Code Quality / Security · **Confidence: HIGH**
- **Files:** `pages/opportunity-applications.php` (165 lines, **live/routed**) vs. root-level `opportunity-applications.php` (399 lines, orphaned) vs. `patch_opportunity_applications.php` (405 lines, also orphaned)
- **Description:** There are three diverging copies of this page. The root copy `require_once`s `includes/rate_limit.php` and calls `checkRateLimit(30)` before processing POST status-change actions, and also grants `sk_chairman` access. I confirmed via grep that **`checkRateLimit()` has zero callers anywhere in the actually-routed codebase** — the fully-working rate-limiting helper (`includes/rate_limit.php`) is entirely orphaned. The live `pages/` version that real users hit has neither the rate limit nor `sk_chairman` access.
- **Fix:** Port the rate-limiting call (and decide deliberately whether `sk_chairman` should have access) from the root copy into `pages/opportunity-applications.php`, then delete both orphaned root copies.
- **Effort:** ~1 hour

### H-4. Matching engine issues up to 4 DB queries per youth profile on a live request
- **Category:** Performance · **Confidence: HIGH**
- **File:** `Classes/Matching.php:563` (`generateMatches()`)
- **Description:** Fetches every `Verified` youth profile, then per profile: one existence-check query, then (if new) `calculateHybridScore()` issues two more queries (profile fetch + opportunity fetch) before a final insert. That's up to 4 synchronous queries × N youth, triggered directly from an HTTP request (an SK/LYDO/provider clicking "Generate Matches"). Currently invisible because the seed dataset only has ~16 youth profiles; this will visibly slow down or start timing out once the system holds a realistic municipal-scale population (hundreds+).
- **Fix:** Rewrite as a set-based operation — one query to find youth IDs *without* an existing match for the opportunity (`LEFT JOIN ... WHERE m.id IS NULL`), compute scores in PHP from a single batched profile+opportunity fetch, then bulk-insert.
- **Effort:** 3–4 hours

### H-5. Zero automated test coverage
- **Category:** Test Coverage · **Confidence: HIGH**
- **Files:** No `composer.json`/PHPUnit anywhere. `TEST_SUITE.php` and `FEATURE_TEST_RUNNER.php` (root)
- **Description:** `TEST_SUITE.php` is a PHP array of human-readable manual QA steps (e.g. "1. Go to public registration page... 2. Fill out youth signup form...") with zero `assert()` calls — it's a checklist, not a test. `FEATURE_TEST_RUNNER.php` is a real runnable script (`php FEATURE_TEST_RUNNER.php`) but only does structural smoke checks — "does this class exist," "does this method exist," "is the DB reachable," "is the uploads directory writable." None of the authorization bugs found and fixed earlier in this engagement (training providers locked out of their own applicant list, the youth "return for correction" login lockout, three API endpoints missing ownership checks) would have been caught by either file — they test structure, not behavior.
- **Fix:** Introduce PHPUnit via Composer. Prioritize tests for the highest-risk surface first: `User::login()`, role-based access on every `requireRole()` call, and the matching/application authorization paths.
- **Effort:** Multi-day initial investment; ongoing per feature after that.

---

## MEDIUM

### M-1. TLS certificate verification disabled for the production database connection
- **Category:** Security · **Confidence: HIGH**
- **File:** `render.yaml` (`DB_SSL_VERIFY_SERVER_CERT: "false"`, committed directly), also defaulted in `config/database.php`
- **Description:** The production DB connection to Aiven explicitly skips certificate validation. A CA cert (`config/ca.pem`) already exists in the repo, suggesting the correct fix was half-implemented and then disabled rather than debugged.
- **Fix:** Set `DB_SSL_VERIFY_SERVER_CERT=true` and confirm `config/ca.pem` is the correct, current Aiven CA chain; test the connection before deploying.
- **Effort:** 1–2 hours

### M-2. Identical N+1 count query copy-pasted across three pages
- **Category:** Performance · **Confidence: HIGH**
- **Files:** `pages/my-job-openings.php:72`, `pages/dashboard.php:412`, `pages/my-training-programs.php:70`
- **Description:** All three run `SELECT COUNT(*) FROM osy_matches WHERE opportunity_id = ?` once per row inside a loop, to show an applicant count next to each opportunity.
- **Fix:** Replace with a single `SELECT opportunity_id, COUNT(*) FROM osy_matches WHERE opportunity_id IN (...) GROUP BY opportunity_id` before the loop, then look up counts from an in-memory map.
- **Effort:** ~45 minutes for all three (see Quick Wins)

### M-3. Schema migration checks run on every single HTTP request
- **Category:** Architecture / Performance · **Confidence: HIGH**
- **File:** `migrate.php` (required unconditionally from `init.php:69-71`)
- **Description:** 13+ `SHOW COLUMNS`/`SHOW TABLES` round-trips plus several `CREATE TABLE IF NOT EXISTS` statements run on every page load — the file's own header comment says "safe to run every request," which is true but not "free." Since the DB is a remote Aiven host (not localhost), each of these is a real network round-trip, not a cheap local call.
- **Fix:** Gate behind a version check — store the last-applied migration identifier in `system_settings` (which already exists) and short-circuit the whole file if it matches current, or move schema migration to a deploy-time step entirely.
- **Effort:** 2–3 hours

### M-4. Hardcoded API token committed in a probe script
- **Category:** Security · **Confidence: HIGH**
- **File:** `sms_probe.sh` (root)
- **Description:** Contains a live-looking Traccar API bearer token and an internal LAN IP, committed to git.
- **Fix:** Delete the file (it has no live callers) or move the token to an env var; rotate the token regardless.
- **Effort:** 15 minutes (see Quick Wins) + rotation time

### M-5. Overly permissive Content-Security-Policy
- **Category:** Security · **Confidence: MEDIUM**
- **File:** `config/database.php` (CSP header definition)
- **Description:** `'unsafe-inline' 'unsafe-eval'` in `default-src` substantially weakens what CSP is for — it still blocks loading scripts from unlisted external hosts, but does nothing against inline/injected script execution, which is the more common XSS vector.
- **Fix:** Incrementally move inline `<script>`/`<style>` blocks to external files or nonce-based CSP; this is a larger refactor given how many pages likely have inline JS.
- **Effort:** Multi-day, lower urgency than the items above

### M-6. Fire-and-forget background jobs depend on `shell_exec` being enabled
- **Category:** Architecture · **Confidence: MEDIUM**
- **File:** `Classes/OSYProfile.php:155`, `:384`
- **Description:** AI match-score recalculation is dispatched via `shell_exec()` spawning a new backgrounded PHP CLI process per profile save. This correctly avoids blocking the request (verified the shell command is properly backgrounded with `&` and output redirected), but it silently no-ops if the host's `disable_functions` includes `shell_exec` (common on shared/managed hosting), has no retry or dead-letter handling if the spawn fails, and spawns a full new PHP process per save under concurrent load.
- **Fix:** If staying on this architecture, add a fallback/log when `shell_exec` is disabled so failures aren't silent; longer-term, a real job queue (even a simple DB-backed one) would be more robust.
- **Effort:** 2–4 hours for the fallback/logging; a full queue is a larger project

---

## LOW

### L-1. Inconsistent SQL-building pattern in Report.php
- **Category:** Code Quality · **Confidence: HIGH**
- **File:** `Classes/Report.php:196` (`buildProfileFilterSql()`)
- **Description:** Uses string concatenation with `$this->db->escape()` (verified this correctly wraps `mysqli_real_escape_string`, and the connection charset is `utf8mb4`, so this is **not currently exploitable** — the audit rules require flagging this as low/verified-safe rather than a false "SQL injection" claim). It's still inconsistent with the parameterized (`?`) pattern used everywhere else, which makes it a risk if someone copies this pattern elsewhere without also copying the `escape()` call.
- **Fix:** Convert to bound parameters for consistency.
- **Effort:** 1–2 hours

### L-2. 43 stale/dead root-level files with zero live references
- **Category:** Code Quality / Dead Code · **Confidence: HIGH**
- **Files:** All `debug_*`, `test-*`/`test_*` (outside the `.htaccess`-covered ones), `patch_*`, one-off `migrate_*.php`, `check_*`, `analyze_*`, `diagnosis_*`, `simple_debug.php`, `inspect_page.html`, `view_source.html` at root
- **Description:** Confirmed via grep that none of these are `require`'d or linked from anywhere in `pages/`, `api/`, `Classes/`, `includes/`, or `init.php`. This overlaps with C-2 for the ones that are also dangerous; the rest are simply clutter that makes the real codebase harder to navigate and increases the attack surface for future denylist gaps like C-2.
- **Fix:** Delete all of them (git history preserves them if ever needed). If any are genuinely still useful for local dev, move to a `/dev-tools` directory and add that to `.dockerignore`.
- **Effort:** 30 minutes

### L-3. No PHP dependency manager
- **Category:** Dependencies · **Confidence: HIGH**
- **Description:** No `composer.json`. PHPMailer is manually vendored at `libs/PHPMailer/` (verified version 7.0.2 via `const VERSION`; verified no CVEs currently affect this version). Works today, but there's no mechanism to know when a future PHPMailer CVE drops, and no lockfile for reproducible installs.
- **Fix:** Adopt Composer for at least PHPMailer; consider it for a formal autoloader too (ties into L-4).
- **Effort:** Half a day

### L-4. No autoloader — manual require list in `init.php`
- **Category:** Architecture · **Confidence: HIGH**
- **File:** `init.php:13-23`
- **Description:** All 11 `Classes/*.php` files are wired in via an explicit, manually-maintained `require_once` list. Adding a new class means remembering to add it here; forgetting causes a fatal error elsewhere, potentially not caught until that code path is hit in production.
- **Fix:** Adopt Composer's PSR-4 autoloader (natural pairing with L-3), or at minimum a simple `spl_autoload_register`.
- **Effort:** 2–3 hours alongside L-3

### L-5. Direct database access leaking into the presentation layer
- **Category:** Architecture · **Confidence: MEDIUM**
- **Description:** 19 of 44 files in `pages/` (43%) contain direct `$database->fetch*/execute` calls rather than going exclusively through the `Classes/` model layer. Not broken, but inconsistent — makes logic harder to reuse/test and increases the chance of a query being written safely in one place and unsafely in another (as nearly happened with the SQL-escaping pattern in L-1).
- **Fix:** No single fix — gradually migrate direct queries into appropriate model methods as those pages are touched for other reasons.
- **Effort:** Ongoing

---

## Quick Wins (each under 30 minutes)

| # | Fix | Ref |
|---|---|---|
| 1 | Delete `get_user_credentials.php`, `fix_admin_email.php`, `update_emails_final.php`, `update_user_emails_demo.php`, `change_lydo_email.php`, `debug_session.php` | C-2 |
| 2 | Add `*.bak` to `.gitignore` (prevents recurrence of C-1's root cause, doesn't undo existing exposure) | C-1 |
| 3 | Delete `sms_probe.sh` and rotate its hardcoded Traccar token | M-4 |
| 4 | `git rm --cached email_log.txt sms_log.txt` | H-1 |
| 5 | `ADD INDEX idx_osy_verification (verification_status)` on `osy_profiles` — cheap now, saves a headache later | (noted under M-2/H-4 context) |
| 6 | Delete the two orphaned `opportunity-applications.php` variants at root and `patch_matching.php`, `patch_export_applications.php` | H-3, L-2 |

Note: none of these quick wins substitute for the credential rotation and git-history purge in C-1/H-1, which take longer than 30 minutes but are the actual highest-priority action.

---

## What's already solid (verified, not just assumed)

- Parameterized queries used consistently outside the one flagged exception (L-1)
- Passwords and OTP codes both bcrypt-hashed; OTP attempt throttling correctly DB-persisted
- File upload validation is genuinely good: extension allowlist + server-side `finfo` MIME sniffing (not trusting client `Content-Type`) + size cap
- CSRF protection is centrally enforced in `init.php` for all POST requests outside an explicit public-page list, not left to individual pages to remember
- Security headers present (HSTS, X-Frame-Options, X-Content-Type-Options, Referrer-Policy)
- `npm audit` on the real registry: 0 vulnerabilities across all 3 npm dependencies, all at latest version
- No circular dependencies between `Classes/*.php` files

---

*Report generated via manual systematic review with tool-verified evidence (grep, npm audit, git history inspection, live Docker/Render config cross-reference, and targeted web searches for CVE data). No finding above is based on assumption from file naming alone.*
