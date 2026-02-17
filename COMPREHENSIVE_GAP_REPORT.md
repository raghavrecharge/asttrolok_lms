# COMPREHENSIVE GAP REPORT — Asttrolok LMS
# Support Ticket System + Course Purchase Module

**Date:** 2026-02-17
**Prepared by:** Senior Backend Architect
**Database:** `asttrolok_live_db` (MySQL)
**Application:** Laravel-based Astrology Course LMS
**Scope:** All QA issues across 3 debugging sessions

---

## TABLE OF CONTENTS

1. [Executive Summary](#1-executive-summary)
2. [All Issues — Master Tracker](#2-all-issues--master-tracker)
3. [All Changes Made — File-Level Detail](#3-all-changes-made--file-level-detail)
4. [All Database Changes — SQL-Level Detail](#4-all-database-changes--sql-level-detail)
5. [Issue-by-Issue Deep Analysis](#5-issue-by-issue-deep-analysis)
6. [Current System State](#6-current-system-state)
7. [Remaining Manual Actions](#7-remaining-manual-actions)
8. [Production Deployment Checklist](#8-production-deployment-checklist)

---

## 1. EXECUTIVE SUMMARY

Across 3 debugging sessions, **10 critical issues** were identified and resolved in the Asttrolok LMS platform. The issues span database schema gaps, infrastructure misconfiguration, authorization failures, null-safety crashes, enum mismatches, and data integrity problems.

### Root Cause Distribution

| Category | Count | Issues |
|----------|-------|--------|
| Missing DB Columns | 5 | #1, #2, #6, #7, #8 |
| Missing DB Tables | 1 | #3 |
| Infrastructure (Telescope) | 1 | #9 |
| Permission / Authorization | 1 | #10 |
| Code Logic Bugs | 1 | #4 |
| Data Integrity (Duplicates) | 1 | #5 |

### Impact Summary

- **Support Ticket System:** Completely non-functional — tables missing, columns missing, permissions missing
- **Course Purchase Flow:** Broken — 3 fatal bugs in CheckoutService meant zero purchases through the new payment engine
- **Admin Panel:** Login blocked for ALL users due to Telescope crash
- **My Purchases Page:** Showed duplicate course entries (2,149 affected user-product pairs)

---

## 2. ALL ISSUES — MASTER TRACKER

| # | Issue | Severity | Root Cause | Status |
|---|-------|----------|-----------|--------|
| 1 | Support ticket pages show 500 error | 🔴 CRITICAL | `new_support_for_asttrolok` + `new_support_for_asttrolok_logs` + `support_categories` tables never created (migrations not run) | ✅ FIXED |
| 2 | Null crashes on ticket detail page | 🔴 CRITICAL | `show.blade.php` uses `$supportRequest->webinar->title` without null-safe operator; webinar or creator can be null | ✅ FIXED |
| 3 | Support audit logging crashes | 🟡 HIGH | `support_audit_logs`, `refunds`, `coupon_credits` tables missing (migrations not run) | ✅ FIXED |
| 4 | Buy Course failure (Razorpay flow) | 🔴 CRITICAL | 3 fatal bugs in `CheckoutService`: invalid enum values (`new`/`one_time`), non-existent method (`appendEntry`), undefined variables in `WebinarController` | ✅ FIXED |
| 5 | Duplicate courses in My Purchases | 🟡 HIGH | Legacy migration created 2,466 excess `upe_sales` rows; display query had no dedup | ✅ FIXED |
| 6 | Temporary Request SQL error | 🔴 CRITICAL | `temporary_access_days` + `temporary_access_reason` columns missing from `new_support_for_asttrolok` | ✅ FIXED |
| 7 | Relative Request SQL error | 🔴 CRITICAL | `relative_description` column missing from `new_support_for_asttrolok` | ✅ FIXED |
| 8 | Status lifecycle mismatch | 🟡 HIGH | Old statuses (`in_review`/`approved`) vs new (`verified`/`executed`) coexist; model badge class missing `verified`; stats don't merge workflows | ✅ FIXED |
| 9 | Admin Login failure | 🔴 CRITICAL | `telescope_entries` table missing + Telescope enabled — crashes EVERY HTTP request | ✅ FIXED |
| 10 | Access Denied on Support Tickets | 🔴 CRITICAL | `admin_support_manage` permission missing from `sections` table; migration had bug (`key` vs `name` column); permission never granted to roles | ✅ FIXED |

---

## 3. ALL CHANGES MADE — FILE-LEVEL DETAIL

### 3.1 Configuration Files

| # | File | Change | Issue |
|---|------|--------|-------|
| 1 | `.env` | Added `TELESCOPE_ENABLED=false` (line 7) | #9 |

### 3.2 Backend — Controllers

| # | File | Change | Issue |
|---|------|--------|-------|
| 2 | `app/Http/Controllers/Panel/UpeController.php` | Rewrote `myPurchases()` method — added per-product dedup query that picks best sale by status priority (`active > partially_refunded > pending_payment`) then newest id | #5 |
| 3 | `app/Http/Controllers/Panel/NewSupportForAsttrolokController.php` | Rewrote `index()` stats computation — uses fresh query + merges old statuses (`in_review`→`verified`, `approved`→`executed`) for backward compatibility | #8 |
| 4 | `app/Services/PaymentEngine/CheckoutService.php` | Fixed `sale_type` enum: `'new'`→`'paid'`/`'free'` (5 locations), `'renewal'`→`'paid'` (1 location) | #4 |
| 5 | `app/Services/PaymentEngine/CheckoutService.php` | Fixed `pricing_mode` enum: `'one_time'`→`'full'` (2 locations) | #4 |
| 6 | `app/Services/PaymentEngine/CheckoutService.php` | Fixed method call: `appendEntry()`→`append()` with positional params (5 locations) | #4 |
| 7 | `app/Services/PaymentEngine/CheckoutService.php` | Fixed `resolveProduct()`: removed `name` param, use `webinar->price` for `base_fee` | #4 |
| 8 | `app/Http/Controllers/Web/WebinarController.php` | Fixed `directPayment()`: added missing `$itemPrice`, `$totalDiscount`, `$itemPrice1` variable definitions | #4 |

### 3.3 Backend — Models

| # | File | Change | Issue |
|---|------|--------|-------|
| 9 | `app/Models/NewSupportForAsttrolok.php` | Added `scopeVerified()` method for the new 3-step workflow | #8 |
| 10 | `app/Models/NewSupportForAsttrolok.php` | Added `'verified' => 'info'` to `getStatusBadgeClass()` method | #8 |

### 3.4 Frontend — Blade Views

| # | File | Change | Issue |
|---|------|--------|-------|
| 11 | `resources/views/web/default/panel/support/show.blade.php` | Line ~31: `$supportRequest->webinar->title` → `$supportRequest->webinar?->title` | #2 |
| 12 | `resources/views/web/default/panel/support/show.blade.php` | Line ~40: `route('webinar', $supportRequest->webinar->id)` → null-guarded with `@if($supportRequest->webinar)` | #2 |
| 13 | `resources/views/web/default/panel/support/show.blade.php` | Line ~79: `$supportRequest->webinar?->creator->full_name` → `$supportRequest->webinar?->creator?->full_name` | #2 |

### 3.5 Database — Migration Files

| # | File | Change | Issue |
|---|------|--------|-------|
| 14 | `database/migrations/2026_02_10_140007_add_workflow_columns_to_support_requests_table.php` | Added `Schema::hasColumn()` guards + replaced Doctrine `getDoctrineSchemaManager()` with raw `SHOW INDEX` query (Doctrine not available in Laravel 11) | #3, #8 |
| 15 | `database/migrations/2026_02_10_140009_add_admin_support_manage_permission.php` | Fixed `'key'`→`'name'`, `'title'`→`'caption'` (matching actual `sections` table schema); added auto-grant to admin role | #10 |

---

## 4. ALL DATABASE CHANGES — SQL-LEVEL DETAIL

### 4.1 Tables Created (Migrations Run)

```sql
-- Session 1: Support system core tables
CREATE TABLE new_support_for_asttrolok (...);          -- 69 columns
CREATE TABLE new_support_for_asttrolok_logs (...);     -- log entries
CREATE TABLE support_categories (...);                  -- category lookup

-- Session 2: Support workflow + financial tables
CREATE TABLE support_audit_logs (...);                  -- audit trail for status transitions
CREATE TABLE refunds (...);                             -- refund records
CREATE TABLE coupon_credits (...);                      -- coupon credit tracking
CREATE TABLE refund_records (...);                      -- detailed refund records
CREATE TABLE installment_restructure_requests (...);    -- installment restructuring
CREATE TABLE sub_step_installments (...);               -- sub-step tracking
```

### 4.2 Columns Added

```sql
-- Issue #6: Temporary access scenario fields
ALTER TABLE new_support_for_asttrolok
    ADD COLUMN temporary_access_days INT NULL AFTER extension_reason;
ALTER TABLE new_support_for_asttrolok
    ADD COLUMN temporary_access_reason TEXT NULL AFTER temporary_access_days;

-- Issue #7: Relative scenario field
ALTER TABLE new_support_for_asttrolok
    ADD COLUMN relative_description TEXT NULL AFTER relative_relation;

-- Issue #8: Workflow columns (via migration)
ALTER TABLE new_support_for_asttrolok
    ADD COLUMN verified_by BIGINT UNSIGNED NULL AFTER status;
ALTER TABLE new_support_for_asttrolok
    ADD COLUMN verified_at TIMESTAMP NULL AFTER verified_by;
ALTER TABLE new_support_for_asttrolok
    ADD COLUMN executed_by BIGINT UNSIGNED NULL AFTER verified_at;
ALTER TABLE new_support_for_asttrolok
    ADD COLUMN verified_amount DECIMAL(15,2) NULL AFTER executed_at;
ALTER TABLE new_support_for_asttrolok
    ADD COLUMN idempotency_key VARCHAR(64) NULL UNIQUE AFTER verified_amount;
```

### 4.3 Columns Modified (Made Nullable)

```sql
-- Issue #1: Controller sends NULL for non-course scenarios
ALTER TABLE new_support_for_asttrolok MODIFY webinar_id BIGINT UNSIGNED NULL;
ALTER TABLE new_support_for_asttrolok MODIFY description TEXT NULL;

-- Secondary: Blocked ALL inserts without explicit values
ALTER TABLE new_support_for_asttrolok MODIFY flow_type VARCHAR(255) NULL DEFAULT NULL;
ALTER TABLE new_support_for_asttrolok MODIFY purchase_status VARCHAR(255) NULL DEFAULT NULL;
```

### 4.4 Columns Added to Other Tables (via migrations)

```sql
-- Support columns on upe_sales
ALTER TABLE upe_sales ADD COLUMN support_ticket_id BIGINT UNSIGNED NULL;
-- (+ other support-related columns from migration 2026_02_10_180003)

-- Support table workflow column (add_purchase_to_refund_to_support)
ALTER TABLE new_support_for_asttrolok ADD COLUMN purchase_to_refund VARCHAR(255) NULL;
```

### 4.5 Permission Data Inserted

```sql
-- Issue #10: Permission section
INSERT INTO sections (name, section_group_id, caption)
VALUES ('admin_support_manage', NULL, 'Admin Support Manage');
-- Inserted as id=3053

-- Issue #10: Grant to Admin role
INSERT INTO permissions (role_id, section_id, allow) VALUES (2, 3053, 1);

-- Issue #10: Grant to Support Role
INSERT INTO permissions (role_id, section_id, allow) VALUES (9, 3053, 1);
```

---

## 5. ISSUE-BY-ISSUE DEEP ANALYSIS

### ISSUE #1 — Support Ticket Pages Show 500 Error

**Error:** `SQLSTATE[42S02]: Base table or view not found: 1146 Table 'asttrolok_live_db.new_support_for_asttrolok' doesn't exist`

**Root Cause:** Three migration files existed but were never executed:
- `2026_01_16_114513_create_new_support_for_asttrolok_table.php`
- `2026_01_16_114513_create_new_support_for_asttrolok_logs_table.php`
- `2026_01_16_114643_create_support_categories_table.php`

**Failing Layer:** Database → every controller method querying these tables throws SQLSTATE error

**Fix:** Ran all 3 migrations via `php artisan migrate --path=...`

**Risk if Unfixed:** 🔴 Entire support ticket system non-functional for all users + stack traces exposed

---

### ISSUE #2 — Null Crashes on Ticket Detail Page

**Error:** `Trying to get property 'title' of non-object` / `Call to a member function on null`

**Root Cause:** `show.blade.php` accesses `$supportRequest->webinar->title`, `$supportRequest->webinar->id`, and `$supportRequest->webinar->creator->full_name` without null-safe operators. Some support scenarios (e.g., general inquiry, offline payment) have `webinar_id = NULL`.

**Failing Layer:** Frontend (Blade view) — 3 locations

**Fix:** Added `?->` null-safe operators at all 3 locations + `@if` guards for route generation

---

### ISSUE #3 — Support Audit Logging Crashes

**Error:** `SQLSTATE[42S02]: Base table or view not found: 1146 Table 'asttrolok_live_db.support_audit_logs' doesn't exist`

**Root Cause:** `SupportRequestService::transition()` calls `SupportAuditLog::log()` which writes to the `support_audit_logs` table. This table's migration was never run. Similarly, `refunds`, `coupon_credits`, and `service_accesses` tables were missing.

**Failing Layer:** Database → Service layer (`SupportRequestService`) crashes during status transitions

**Fix:** Ran migrations for `support_audit_logs`, `refunds`, `coupon_credits`, `refund_records`, `installment_restructure_requests`, `sub_step_installments`. Also fixed migration file to use `Schema::hasColumn()` guards instead of Doctrine (not available in Laravel 11).

---

### ISSUE #4 — Buy Course Failure (Razorpay Flow)

**Errors:**
1. `SQLSTATE[01000]: Data truncated for column 'sale_type'` — enum value `'new'` doesn't exist
2. `BadMethodCallException: Method appendEntry does not exist` — wrong method name
3. `Undefined variable $itemPrice` — in WebinarController::directPayment()

**Root Cause (3 bugs):**

| Bug | Location | Wrong | Correct |
|-----|----------|-------|---------|
| A | CheckoutService (5 places) | `sale_type => 'new'` | `$amount > 0 ? 'paid' : 'free'` |
| B | CheckoutService (2 places) | `pricing_mode => 'one_time'` | `'full'` |
| C | CheckoutService (5 places) | `$this->ledger->appendEntry([...])` | `$this->ledger->append($saleId, $type, $amount, ...)` |
| D | WebinarController line ~1166 | `$itemPrice` undefined | Added computation before view data |

**Evidence:** 0 of 20,807 upe_sales records came through the checkout path — all from legacy migration.

**Failing Layer:** Service layer (CheckoutService) + Controller (WebinarController)

**Fix:** Corrected all enum values, method calls, and undefined variables across both files.

---

### ISSUE #5 — Duplicate Courses in My Purchases

**Root Cause:** Legacy migration (`UPEMigrateLegacy`) imported ALL historical `Sale` records into `upe_sales`. Many users had multiple Sale records for the same course (payment retries, admin corrections, partial payments). Result: **2,149 user-product pairs** with **2,466 excess rows**.

The display method `UpeController::myPurchases()` simply did `UpeSale::where('user_id', ...)->paginate(15)` — no grouping or dedup.

**Failing Layer:** Controller query logic + data integrity

**Fix:** Rewrote `myPurchases()` with priority-based dedup:
```
For each product → pick single best sale:
  Priority: active(4) > partially_refunded(3) > pending_payment(2) > others(1)
  Tiebreaker: newest id
```

---

### ISSUE #6 — Temporary Request SQL Error

**Error:** `Column not found: 1054 Unknown column 'temporary_access_days' in 'field list'`

**Root Cause:** Controller at line 497 sets `$data['temporary_access_days'] = 7` and `$data['temporary_access_reason'] = ...`, validation rules require both fields, and `$fillable` includes them — but the original migration **never created these columns**.

**Failing Layer:** Database schema — columns missing despite being referenced by controller, model, and validation

**Fix:** `ALTER TABLE new_support_for_asttrolok ADD COLUMN temporary_access_days INT NULL` and `ADD COLUMN temporary_access_reason TEXT NULL`

---

### ISSUE #7 — Relative Request SQL Error

**Error:** `Column not found: 1054 Unknown column 'relative_description' in 'field list'`

**Root Cause:** Same pattern as #6. The migration created 4 of 5 relative fields (`relative_name`, `relative_email`, `relative_phone`, `relative_relation`) but omitted `relative_description`. Controller at line 513 sets `$data['relative_description']`, validation requires it at line 362.

**Failing Layer:** Database schema

**Fix:** `ALTER TABLE new_support_for_asttrolok ADD COLUMN relative_description TEXT NULL AFTER relative_relation`

---

### ISSUE #8 — Status Lifecycle Mismatch

**Root Cause:** Two coexisting workflow patterns:

| Flow | Route | Statuses |
|------|-------|----------|
| Legacy | `PUT /support/{id}/status` → `updateStatus()` | `pending → in_review → approved → executed` |
| New Secure | `PUT /support/{id}/status-secure` → `updateStatusSecure()` | `pending → verified → executed` |

Problems:
- `getStatusBadgeClass()` had no entry for `verified` — rendered as gray
- `index()` stats showed only `in_review`/`approved` counts — `verified`/`executed` tickets invisible
- `scopeCompleted()` used capital `'Completed'` — inconsistent

**Failing Layer:** Model + Controller + View

**Fix:**
- Added `'verified' => 'info'` to badge class map
- Added `scopeVerified()`
- Rewrote stats to merge: `in_review` count includes `verified`; `approved` count includes `executed`

---

### ISSUE #9 — Admin Login Failure

**Error:** `SQLSTATE[42S02]: Base table or view not found: 1146 Table 'asttrolok_live_db.telescope_entries' doesn't exist`

**Root Cause:** Laravel Telescope is installed, registered in `config/app.php` (line 180), and enabled by default (`'enabled' => env('TELESCOPE_ENABLED', true)`). The `telescope_entries` table migration was never run. Telescope's watchers attempt to log EVERY HTTP request to this table. When the INSERT fails, it corrupts the entire response lifecycle.

**This is NOT an auth bug** — it's an infrastructure bug that kills ALL HTTP responses, making login appear to fail.

**Failing Layer:** Infrastructure (Telescope ServiceProvider)

**Fix:** Added `TELESCOPE_ENABLED=false` to `.env` + cleared config cache

---

### ISSUE #10 — Access Denied on Support Tickets

**Error:** HTTP 403 Forbidden on `/admin/supports/newsuportforasttrolok/support`

**Root Cause:** `AdminSupportController::index()` calls `$this->authorize('admin_support_manage')`. This permission:
1. Did NOT exist in the `sections` table
2. The migration that should create it (`2026_02_10_140009`) had a **bug** — used `where('key', ...)` but the column is `name`
3. Even if it existed, it was never granted to any role in the `permissions` table

**Failing Layer:** Authorization Gate → `sections` table → `permissions` table

**Fix:**
- Inserted `admin_support_manage` into `sections` (id=3053)
- Granted to Admin role (role_id=2) and Support Role (role_id=9)
- Fixed migration file: `'key'` → `'name'`, `'title'` → `'caption'`, added auto-grant logic

---

## 6. CURRENT SYSTEM STATE

### Verified Working ✅

| Component | Verification |
|-----------|-------------|
| Support ticket table exists | `SHOW TABLES LIKE 'new_support%'` → 2 tables |
| Support categories table | `SELECT COUNT(*) FROM support_categories` → works |
| Audit log table | `SELECT COUNT(*) FROM support_audit_logs` → works |
| `temporary_access_days` column | `SHOW COLUMNS ... LIKE 'temporary_access_days'` → EXISTS |
| `temporary_access_reason` column | `SHOW COLUMNS ... LIKE 'temporary_access_reason'` → EXISTS |
| `relative_description` column | `SHOW COLUMNS ... LIKE 'relative_description'` → EXISTS |
| `webinar_id` nullable | IS_NULLABLE = YES |
| `description` nullable | IS_NULLABLE = YES |
| `flow_type` nullable | IS_NULLABLE = YES |
| `purchase_status` nullable | IS_NULLABLE = YES |
| Workflow columns | `verified_by`, `verified_at`, `executed_by`, `verified_amount`, `idempotency_key` all exist |
| Telescope disabled | `config('telescope.enabled')` = false |
| `admin_support_manage` permission | section id=3053, granted to admin(2) + support(9) |
| Dedup query | User#1: 24 total sales → 10 unique products (14 dupes removed) |
| Status badge | `getStatusBadgeClass('verified')` = `'info'` |
| CheckoutService enum | Insert test with `sale_type='paid'`, `pricing_mode='full'` → ✅ |
| `PaymentLedgerService::append()` | Method exists ✅ |

### Known Remaining Items ⚠️

| Item | Status | Action Needed |
|------|--------|---------------|
| `APP_DEBUG=true` | ❌ Not fixed | Set to `false` in `.env` for production |
| `telescope_entries` table | Missing | Run `php artisan telescope:install` if Telescope is wanted |
| `service_accesses` table | Missing | Non-critical — only used by service access scenario |
| 2,466 duplicate upe_sales rows | Display fixed, data untouched | Optional: run cleanup script in maintenance window |
| ~20 pending migrations | Not all run | Review and run remaining non-critical migrations |

---

## 7. REMAINING MANUAL ACTIONS

### Immediate (Before Next QA Cycle)

```bash
# 1. CRITICAL: Disable debug mode
# In .env, change:
APP_DEBUG=false

# 2. Clear all caches after .env change
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

### Recommended (Within 1 Sprint)

1. **Run remaining migrations** — review and test the ~20 pending migrations
2. **Create `service_accesses` table** — migration exists but hasn't run
3. **Deprecate legacy `updateStatus()` route** — migrate admin views to use `updateStatusSecure()` only
4. **Add schema-model sync CI test** — compare `$fillable` against actual DB columns
5. **Data cleanup** — deduplicate 2,466 excess upe_sales rows

---

## 8. PRODUCTION DEPLOYMENT CHECKLIST

### Pre-Deployment

- [ ] Set `APP_DEBUG=false` in production `.env`
- [ ] Set `TELESCOPE_ENABLED=false` in production `.env`
- [ ] Verify all SQL ALTER TABLE statements are captured in migration files
- [ ] Run `php artisan migrate --pretend` to check for failures
- [ ] Backup database before applying changes

### Deployment Steps (In Order)

```
Step 1: Apply .env changes (TELESCOPE_ENABLED=false, APP_DEBUG=false)
Step 2: Run migrations for missing tables
Step 3: Run ALTER TABLE for missing columns (temporary_access_days, etc.)
Step 4: Run ALTER TABLE for nullable columns (flow_type, purchase_status, etc.)
Step 5: Insert admin_support_manage permission + grants
Step 6: Clear all caches
Step 7: Restart PHP-FPM / web server
```

### Post-Deployment Verification

- [ ] Admin login → success
- [ ] Admin → All Support Tickets → loads without 403
- [ ] Create ticket: Temporary Access scenario → success
- [ ] Create ticket: Relatives/Friends Access scenario → success
- [ ] Create ticket: Course Extension scenario → success
- [ ] View ticket detail with null webinar → no crash
- [ ] Support Role user → verify ticket → status shows "Verified"
- [ ] Admin → execute ticket → status shows "Executed"
- [ ] User Panel → My Purchases → no duplicate courses
- [ ] User Panel → Buy Course → Razorpay payment completes
- [ ] Check `storage/logs/laravel.log` for 15 minutes — no new errors

### Rollback Plan

All changes are additive (new columns, new rows, env flags). Rollback:
1. Remove `TELESCOPE_ENABLED=false` only if telescope table is created
2. Added columns are all nullable — no data loss risk
3. Permission grants: `DELETE FROM permissions WHERE section_id = 3053`
4. Revert CheckoutService/UpeController from git if needed

---

## APPENDIX: COMPLETE FILE CHANGE LOG

### Files Modified by Cascade (Debugging Sessions)

| # | File Path | Lines Changed | Change Type |
|---|-----------|---------------|-------------|
| 1 | `.env` | +1 line | Config: TELESCOPE_ENABLED=false |
| 2 | `app/Http/Controllers/Panel/UpeController.php` | ~50 lines rewritten | Dedup query in myPurchases() |
| 3 | `app/Http/Controllers/Panel/NewSupportForAsttrolokController.php` | ~15 lines | Stats query merge old+new workflows |
| 4 | `app/Services/PaymentEngine/CheckoutService.php` | ~15 locations | Enum fixes + method call fix |
| 5 | `app/Http/Controllers/Web/WebinarController.php` | ~5 lines | Added undefined variable definitions |
| 6 | `app/Models/NewSupportForAsttrolok.php` | ~10 lines | Added scopeVerified + verified badge |
| 7 | `resources/views/web/default/panel/support/show.blade.php` | 3 lines | Null-safe operators |
| 8 | `database/migrations/2026_02_10_140007_*.php` | ~20 lines | hasColumn guards + removed Doctrine |
| 9 | `database/migrations/2026_02_10_140009_*.php` | Full rewrite | Fixed key→name, added auto-grant |

### Database Changes Applied via SQL (Not in Migration Files)

| # | SQL Statement | Reason |
|---|---------------|--------|
| 1 | `ALTER TABLE ... ADD temporary_access_days INT NULL` | Missing column for temp access scenario |
| 2 | `ALTER TABLE ... ADD temporary_access_reason TEXT NULL` | Missing column for temp access scenario |
| 3 | `ALTER TABLE ... ADD relative_description TEXT NULL` | Missing column for relative scenario |
| 4 | `ALTER TABLE ... MODIFY webinar_id BIGINT UNSIGNED NULL` | Controller sends NULL for non-course scenarios |
| 5 | `ALTER TABLE ... MODIFY description TEXT NULL` | Same reason |
| 6 | `ALTER TABLE ... MODIFY flow_type VARCHAR(255) NULL` | Blocked all inserts without explicit value |
| 7 | `ALTER TABLE ... MODIFY purchase_status VARCHAR(255) NULL` | Blocked all inserts without explicit value |
| 8 | `INSERT INTO sections (name, ...) VALUES ('admin_support_manage', ...)` | Missing permission |
| 9 | `INSERT INTO permissions (role_id=2, section_id=3053, allow=1)` | Grant to Admin |
| 10 | `INSERT INTO permissions (role_id=9, section_id=3053, allow=1)` | Grant to Support Role |

> **⚠️ IMPORTANT:** Items 1-7 above are direct SQL changes applied via `tinker`. They should be captured in a new migration file for deployment reproducibility.
