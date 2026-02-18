# Support System Bug Fixes — Production Readiness Report

## Root Cause

The **AdminSupportController** and **SupportRequestService** completion flows created legacy `Sale` records but **never created UPE records**. Since `AccessEngine` (the sole access gatekeeper) only queries `upe_sales` and `upe_support_actions`, all support-granted access was **invisible** to the system. This single root cause cascaded into 20+ reported bugs.

---

## Files Created

| File | Purpose |
|------|---------|
| `app/Services/SupportUpeBridge.php` | Bridge service: creates UPE records alongside legacy Sale records during support completion |
| `app/Console/Commands/BackfillSupportUpeRecords.php` | Artisan command to retroactively create UPE records for historically completed tickets |
| `database/migrations/2026_02_18_120000_add_missing_columns_to_support_table.php` | Adds missing columns to `new_support_for_asttrolok` |

## Files Modified

| File | Changes |
|------|---------|
| `app/Http/Controllers/Admin/AdminSupportController.php` | Wired `SupportUpeBridge` into all 7 completion scenarios (relative, mentor, temp, extension, offline, refund, wrong course, free grant) |
| `resources/views/admin/supports/show.blade.php` | Removed duplicate status dropdown from temporary_access block; fixed percentage input value binding |
| `app/Services/SupportRequestService.php` | Wired `SupportUpeBridge` into all 8 `executeXxx()` scenario handlers |
| `app/Http/Controllers/Panel/NewSupportForAsttrolokController.php` | Fixed expired course detection, flow type detection, duplicate validation rules, coupon code leak |
| `app/Services/PaymentEngine/PaymentLedgerService.php` | Added `idempotencyKey` parameter to `recordPayment()` |

---

## Fixes by Issue Category

### P0 — Access Not Granted (Root Cause)

| Issue | Fix |
|-------|-----|
| Relative access granted but user can't access course | `SupportUpeBridge::grantRelativeAccess()` creates `upe_sales` record |
| Mentor access granted but no course access | `SupportUpeBridge::grantMentorAccess()` creates `upe_sales` record |
| Temporary access granted but videos don't play | `SupportUpeBridge::grantTemporaryAccess()` creates `upe_support_actions` with `expires_at` |
| Course extension granted but expired immediately | `SupportUpeBridge::grantCourseExtension()` creates child `upe_sales` with correct `valid_until` |
| Free course grant doesn't give access | `SupportUpeBridge::grantFreeCourseAccess()` creates `upe_sales` record per user |
| Wrong course correction — new course inaccessible | `SupportUpeBridge::handleWrongCourseCorrection()` revokes old + grants new in UPE |

### P1 — Financial Integrity

| Issue | Fix |
|-------|-----|
| Offline payment uses `base_fee` instead of user-entered cash amount | `SupportUpeBridge::recordOfflinePayment()` records the actual `$cashAmount` in ledger |
| Refund not reflected in UPE ledger | `SupportUpeBridge::recordRefund()` creates debit ledger entry + updates sale status |
| `recordPayment()` couldn't accept idempotency key from bridge | Added `?string $idempotencyKey` parameter to `PaymentLedgerService::recordPayment()` |

### P1 — Course Extension Dropdown Empty

| Issue | Fix |
|-------|-----|
| Extension dropdown shows no expired courses | Rewrote expired course detection in `NewSupportForAsttrolokController::create()` to use UPE sale's `valid_until` directly instead of recalculating from `access_days` |
| `determineFlowType()` misclassifies expired courses | Now queries `upe_sales` for any historical sale (not just active) |

### P2 — Validation / UX

| Issue | Fix |
|-------|-----|
| Rejecting temporary access requires `access_permission` field | `support_remarks` made nullable when `status=rejected` |
| `temporary_access_percentage` null crash | Added `?? 100` fallback in completion flow |
| Duplicate switch cases weaken validation | Removed duplicate `relatives_friends_access`, `free_course_grant`, `offline_cash_payment`, `temporary_access` cases that overrode stricter earlier rules |

### P2 — Security

| Issue | Fix |
|-------|-----|
| Invalid coupon response leaks available coupon codes | `validateCoupon()` now returns generic "Invalid or expired coupon code" message |

### P3 — Configuration (Not Code)

| Issue | Resolution |
|-------|------------|
| Installment page "connection refused" | `APP_URL=http://127.0.0.1:8001` in `.env` — must be set to production domain before deploy |

---

## Schema Changes (Migration)

Added to `new_support_for_asttrolok`:
- `temporary_access_percentage` (integer, nullable, default 100)
- `source_course_id` (unsignedInteger, nullable)
- `target_course_id` (unsignedInteger, nullable)
- `total_users_count` (unsignedInteger, nullable)
- `granted_users_count` (unsignedInteger, nullable)
- `already_had_access_count` (unsignedInteger, nullable)

**Run:** `php artisan migrate` (migration is idempotent — checks column existence before adding)

---

## SupportUpeBridge Design

- **Additive only** — never mutates existing UPE records
- **Idempotent** — each method checks for existing UPE records before creating (safe to call twice)
- **Dual-write** — legacy Sale + UPE records created together
- **Auto-resolves products** — `getOrCreateProduct()` maps `webinar_id` → `upe_products.external_id`
- **Cache invalidation** — calls `AccessEngine::invalidate()` after every grant

---

## Pre-Deploy Checklist

1. [x] Run `php artisan migrate` to add missing columns — **DONE**
2. [x] Run `php artisan support:backfill-upe` — **DONE** (1 ticket backfilled, idempotency verified)
3. [ ] Set `APP_URL` in `.env` to production domain
4. [ ] Test each scenario end-to-end:
   - Create support ticket → Admin completes → Verify user has access
   - Scenarios: relative, mentor, temporary, extension, offline, refund, wrong course, free grant
4. [ ] Verify existing support tickets that were "completed" before this fix:
   - Run a one-time sync to create UPE records for historically completed tickets
   - Query: `SELECT * FROM new_support_for_asttrolok WHERE status IN ('completed','executed') AND support_scenario IN ('relatives_friends_access','mentor_access','temporary_access','course_extension','offline_cash_payment','free_course_grant')`
6. [ ] Monitor `laravel.log` for `SupportUpeBridge:` log entries after deploy

---

## Backfill Command

```bash
# Dry run (show what would be done)
php artisan support:backfill-upe --dry-run

# Full backfill
php artisan support:backfill-upe

# Single ticket
php artisan support:backfill-upe --id=3

# Single scenario
php artisan support:backfill-upe --scenario=relatives_friends_access
```

---

## Final Audit Results

- **13/13** classes compile
- **10/10** bridge methods verified
- **6/6** schema columns confirmed
- **1/1** backfilled ticket verified (AccessEngine returns `hasAccess: YES`)
- **Idempotency confirmed** — re-running backfill creates no duplicates
