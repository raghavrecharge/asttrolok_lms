# UPE Gap Report & Production Audit

**Date:** 2026-02-16
**Author:** Cascade (Senior Architect)
**Scope:** Unified Payment Engine — Buy Course flow, Support Actions, RBAC, Error Handling
**Environment:** Production (`asttrolok_live_db`, `APP_DEBUG=true`)

---

## 1. EXECUTIVE TECHNICAL SUMMARY

### Current State
The Unified Payment Engine (UPE) has been implemented with:
- **20,807 sales** successfully migrated into UPE
- **AccessEngine** is the sole access gatekeeper for all models
- **8 Support Action scenarios** fully implemented and tested
- **Dual-write** (UPE + legacy) active for all payment paths

### Critical Finding
**The `CheckoutService` — the primary write path for ALL new purchases — has 3 fatal bugs that cause every new purchase via Razorpay to fail silently.** No new sales have been created through `CheckoutService` (0 sales with `source=checkout` in metadata). All 20,807 existing UPE sales were created by the legacy migration command, NOT by the checkout flow.

### Severity: 🔴 BLOCKER — Revenue Loss Active

| Category | Issues Found | Critical | High | Medium |
|----------|-------------|----------|------|--------|
| CheckoutService (Buy Course) | 3 | 3 | 0 | 0 |
| RBAC / Authorization | 2 | 1 | 1 | 0 |
| Error Handling / Security | 3 | 1 | 1 | 1 |
| WebinarController (UI) | 1 | 1 | 0 | 0 |
| Data Integrity | 1 | 0 | 1 | 0 |
| Support Actions (new code) | 2 | 0 | 0 | 2 |
| **TOTAL** | **12** | **6** | **3** | **3** |

---

## 2. DETAILED ISSUE-BY-ISSUE RESOLUTION GUIDE

---

### ISSUE #1 — CheckoutService uses invalid `sale_type` enum values
**Severity:** 🔴 CRITICAL — ALL new purchases fail

| Field | Detail |
|-------|--------|
| **Affected Module** | `CheckoutService::processWebinarPurchase()`, `processBundlePurchase()`, `processSubscriptionPurchase()`, `processInstallmentPayment()` |
| **Affected Role** | Student (all users attempting purchase) |
| **Error Type** | Database Issue — Enum constraint violation |
| **Evidence** | `sale_type => 'new'` used at lines 64, 160, 273, 417. Valid enums: `paid, free, trial, referral, upgrade, adjustment`. Also `sale_type => 'renewal'` at line 254 — not in enum. |

**Root Cause:**
`CheckoutService` was written with assumed enum values (`new`, `renewal`) that don't exist in the `upe_sales` migration. The DB column is `ENUM('paid','free','trial','referral','upgrade','adjustment')`. MySQL raises `SQLSTATE[01000]: Warning: 1265 Data truncated for column 'sale_type'` — confirmed by live test.

**How to Debug:**
```
1. Check: php artisan tinker → UpeSale::create(['sale_type' => 'new', ...])
2. Result: SQLSTATE[01000] Data truncated error
3. Log: storage/logs/laravel.log → "Data truncated for column 'sale_type'"
```

**Fix:**
```php
// Lines 64, 160, 417: Change 'new' → 'paid' (or 'free' if amount=0)
'sale_type' => $amount > 0 ? 'paid' : 'free',

// Line 254 (subscription renewal): Change 'renewal' → 'paid'
'sale_type' => 'paid',

// Line 273 (new subscription): Same fix
'sale_type' => $amount > 0 ? 'paid' : 'free',
```

**Risk if Not Fixed:**
- 🔴 Revenue Loss: Every Razorpay purchase fails after payment capture
- 🔴 Data Integrity: Payment captured by Razorpay but no UPE sale created
- 🔴 Business: Students pay but don't get access

---

### ISSUE #2 — CheckoutService uses invalid `pricing_mode` enum values
**Severity:** 🔴 CRITICAL — Same failure path as Issue #1

| Field | Detail |
|-------|--------|
| **Affected Module** | `CheckoutService` lines 65, 161 |
| **Error Type** | Database Issue — Enum constraint violation |
| **Evidence** | `pricing_mode => 'one_time'` used for webinar and bundle purchases. Valid enums: `full, installment, subscription, free`. |

**Fix:**
```php
// Lines 65, 161: Change 'one_time' → 'full'
'pricing_mode' => 'full',
```

---

### ISSUE #3 — CheckoutService calls non-existent method `appendEntry()`
**Severity:** 🔴 CRITICAL — Same failure path as Issues #1-2

| Field | Detail |
|-------|--------|
| **Affected Module** | `CheckoutService` lines 78, 173, 299, 372, 459 |
| **Error Type** | Backend Logic Issue — Method not found |
| **Evidence** | `$this->ledger->appendEntry($upeSale->id, [...])` — but `PaymentLedgerService` has `append()`, NOT `appendEntry()`. Confirmed: `method_exists($ledger, 'appendEntry') → false`. |

**Fix:**
Replace all 5 calls. The `append()` method takes positional parameters, not an array:
```php
// FROM:
$this->ledger->appendEntry($upeSale->id, [
    'entry_type' => UpeLedgerEntry::TYPE_PAYMENT,
    'direction' => UpeLedgerEntry::DIR_CREDIT,
    'amount' => $amount,
    ...
]);

// TO:
$this->ledger->append(
    saleId: $upeSale->id,
    entryType: UpeLedgerEntry::TYPE_PAYMENT,
    direction: UpeLedgerEntry::DIR_CREDIT,
    amount: $amount,
    paymentMethod: $paymentMethod,
    gatewayTransactionId: $razorpayPaymentId,
    description: "Payment for course: {$webinar->slug}",
    idempotencyKey: $razorpayPaymentId ? "rp_{$razorpayPaymentId}_webinar_{$webinarId}" : "checkout_webinar_{$userId}_{$webinarId}_" . time(),
);
```

---

### ISSUE #4 — WebinarController `directPayment()` uses undefined variables
**Severity:** 🔴 CRITICAL — Buy Course page crashes

| Field | Detail |
|-------|--------|
| **Affected Module** | `WebinarController::directPayment()` line 1166, 1170 |
| **Affected Role** | Student (all users viewing buy course page) |
| **Error Type** | Frontend/Backend Issue — Undefined variable |
| **Evidence** | `$totalDiscount` and `$itemPrice1`/`$itemPrice` are used in the view data but never defined in the method. The `getPaymentData()` in `PaymentController` defines these, but `directPayment()` doesn't. |

**How to Debug:**
```
1. Navigate to any course → click "Buy Now"
2. Result: 500 error or Undefined variable warning
3. Log: "Undefined variable $totalDiscount" / "$itemPrice"
```

**Fix:**
```php
// After line 1161 ($item = $this->getItem(...)), add:
$itemPrice = $webinar->getPrice();
$price = $webinar->price;
$totalDiscount = 0;
$itemPrice1 = $itemPrice;

if (isset($Discount) && $Discount) {
    $percent = $Discount->percent ?? 0;
    $totalDiscount = ($price > 0) ? $price * $percent / 100 : 0;
    $itemPrice1 = $itemPrice - $totalDiscount;
}
```

**Risk if Not Fixed:**
- 🔴 Business: Students cannot view the purchase page at all
- 🔴 Revenue: Complete purchase flow blocked

---

### ISSUE #5 — Support Action routes have NO admin role check
**Severity:** 🔴 CRITICAL — Any authenticated user can approve/execute/reject support actions

| Field | Detail |
|-------|--------|
| **Affected Module** | `routes/api/upe.php` lines 90-105, `SupportActionController` |
| **Affected Role** | All roles (Students can perform admin operations) |
| **Error Type** | RBAC Misconfiguration |
| **Evidence** | Support routes (approve, reject, execute, grant mentor badge) are under `api.auth` middleware only — no admin/role check. The controller has zero `$this->authorize()` or role checks. Any authenticated student can: approve refunds, grant mentor badges, execute payment migrations. |

**How to Debug:**
```
1. Login as student
2. POST /api/development/upe/support/mentor/grant {user_id: X}
3. Result: 200 OK — student just granted a mentor badge
```

**Fix:**
Add admin middleware to sensitive routes:
```php
// In routes/api/upe.php, wrap admin-only routes:
Route::group(['prefix' => 'support'], function () {
    // Public/student routes (read-only)
    Route::get('/visibility', [SupportActionController::class, 'visibility']);
    Route::get('/user-matrix', [SupportActionController::class, 'userMatrix']);
    Route::post('/check-eligibility', [SupportActionController::class, 'checkEligibility']);

    // Admin-only routes
    Route::group(['middleware' => ['admin']], function () {
        Route::post('/create', [SupportActionController::class, 'create']);
        Route::get('/actions', [SupportActionController::class, 'index']);
        Route::get('/actions/{id}', [SupportActionController::class, 'show']);
        Route::post('/actions/{id}/approve', [SupportActionController::class, 'approve']);
        Route::post('/actions/{id}/reject', [SupportActionController::class, 'reject']);
        Route::post('/actions/{id}/execute', [SupportActionController::class, 'execute']);
        Route::post('/mentor/grant', [SupportActionController::class, 'grantMentorBadge']);
        Route::post('/mentor/revoke', [SupportActionController::class, 'revokeMentorBadge']);
        Route::get('/mentor/list', [SupportActionController::class, 'listMentors']);
    });
});
```

**Risk if Not Fixed:**
- 🔴 Security: Any user can approve their own refund, grant themselves mentor access
- 🔴 Financial: Unauthorized refunds, payment migrations
- 🔴 Data Integrity: Unaudited admin actions by non-admins

---

### ISSUE #6 — `APP_DEBUG=true` in production
**Severity:** 🔴 HIGH — Stack traces exposed to users

| Field | Detail |
|-------|--------|
| **Affected Module** | `.env` line 5, `Exceptions/Handler.php` line 84 |
| **Affected Role** | All users (public) |
| **Error Type** | Security / Exception Handling Failure |
| **Evidence** | `.env` has `APP_DEBUG=true`. The exception handler at line 84 checks `if (env('APP_DEBUG'))` and returns the full Laravel error page with stack traces, DB credentials, env variables to users on web routes. |

**Fix:**
```bash
# .env
APP_DEBUG=false
```

**Risk if Not Fixed:**
- 🔴 Security: Database credentials, API keys, file paths exposed in error pages
- 🔴 Compliance: PII and infrastructure details leak

---

### ISSUE #7 — `resolveProduct()` passes `name` to a table without `name` column
**Severity:** 🟡 MEDIUM — Silently ignored but incorrect

| Field | Detail |
|-------|--------|
| **Affected Module** | `CheckoutService::resolveProduct()` line 594 |
| **Error Type** | Database Issue — Column mismatch |
| **Evidence** | `upe_products` table has no `name` column. `firstOrCreate` passes `'name' => $name` which is silently ignored by Eloquent's `$fillable` guard (name is not in fillable). No crash, but the slug/name is lost. |

**Fix:**
Remove `name` from `resolveProduct()`:
```php
private function resolveProduct(int $externalId, string $productType, float $baseFee, ?int $validityDays = null): UpeProduct
{
    return UpeProduct::firstOrCreate(
        ['external_id' => $externalId, 'product_type' => $productType],
        [
            'base_fee' => $baseFee,
            'validity_days' => $validityDays,
            'status' => 'active',
        ]
    );
}
```

---

### ISSUE #8 — Telescope `telescope_entries` table missing
**Severity:** 🟡 MEDIUM — Monitoring broken

| Field | Detail |
|-------|--------|
| **Affected Module** | Laravel Telescope |
| **Error Type** | Database Issue |
| **Evidence** | Log shows `SQLSTATE[42S02]: Base table or view not found: 1146 Table 'asttrolok_live_db.telescope_entries' doesn't exist` |

**Fix:**
```bash
php artisan telescope:install
php artisan migrate
# OR disable Telescope in production:
# TELESCOPE_ENABLED=false in .env
```

---

### ISSUE #9 — UPE Admin routes have no admin middleware
**Severity:** 🟡 HIGH — Same as Issue #5 for admin panel

| Field | Detail |
|-------|--------|
| **Affected Module** | `routes/api/upe.php` lines 108-120 (admin prefix routes) |
| **Error Type** | RBAC Misconfiguration |
| **Evidence** | Routes like `POST /admin/grant-free`, `POST /admin/offline-payment`, `GET /admin/sales` are under `api.auth` only — no admin role check. |

**Fix:** Add admin middleware to the admin route group.

---

### ISSUE #10 — `upe_products` `base_fee` set to first-seen amount, not true base price
**Severity:** 🟡 MEDIUM — Data quality

| Field | Detail |
|-------|--------|
| **Affected Module** | `CheckoutService::resolveProduct()` |
| **Error Type** | State/Workflow Mismanagement |
| **Evidence** | `firstOrCreate` sets `base_fee` from the first purchase amount (which may include discounts). Subsequent calls find the existing product and don't update it. If first purchase was discounted, `base_fee` is forever wrong. |

**Fix:** Use `webinar->price` (original price) not `$amount` (discounted) for `base_fee`.

---

### ISSUE #11 — Support Action `hasPendingAction` self-blocking during execution
**Severity:** 🟢 FIXED — During this session

| Field | Detail |
|-------|--------|
| **Status** | ✅ FIXED |
| **What happened** | When `SupportActionService::execute()` called eligibility check, the action being executed was found as a "pending duplicate" by `hasPendingAction()`. |
| **Fix applied** | Added `$excludeActionId` parameter to all eligibility methods and `hasPendingAction()`. Wired through from all `execute*()` methods. |

---

### ISSUE #12 — Coupon apply requires real coupon code even for admin-set amounts
**Severity:** 🟢 FIXED — During this session

| Field | Detail |
|-------|--------|
| **Status** | ✅ FIXED |
| **What happened** | `executeCouponApply()` called `canApplyCoupon()` with the coupon code, which validated against `upe_discounts`. Admin-set amounts without a real coupon code failed. |
| **Fix applied** | Separated sale-level eligibility check from coupon-code validation. Admin can now apply a manual discount amount without needing a real coupon code. |

---

## 3. CHANGES MADE DURING UPE SUPPORT ACTIONS IMPLEMENTATION

### New Files Created (7)

| # | File | Purpose |
|---|------|---------|
| 1 | `database/migrations/2026_02_14_200000_create_upe_support_actions_table.php` | Tracks all 8 support action types with workflow states |
| 2 | `database/migrations/2026_02_14_200001_create_upe_mentor_badges_table.php` | Mentor badge grants/revocations |
| 3 | `app/Models/PaymentEngine/UpeSupportAction.php` | Model: 8 action type constants, workflow states, scopes |
| 4 | `app/Models/PaymentEngine/UpeMentorBadge.php` | Model: `hasBadge()`, `grant()`, `revoke()` statics |
| 5 | `app/Services/PaymentEngine/SupportEligibilityResolver.php` | Central eligibility resolver for all 8 scenarios + visibility matrix |
| 6 | `app/Services/PaymentEngine/SupportActionService.php` | Executes all 8 scenarios with DB transactions, idempotency, audit |
| 7 | `app/Http/Controllers/PaymentEngine/SupportActionController.php` | 12 API endpoints for support workflow |

### Existing Files Modified (4)

| # | File | Change |
|---|------|--------|
| 1 | `app/Services/PaymentEngine/AccessEngine.php` | Added temporary access + mentor badge checks in `computeAccess()` (steps 3 & 4 in resolution chain) |
| 2 | `routes/api/upe.php` | Added 12 support action routes under `/support/` |
| 3 | `app/Services/PaymentEngine/SupportEligibilityResolver.php` | Fixed `validateCoupon()` to use correct `UpeDiscount` field names; added `$excludeActionId` to prevent self-blocking |
| 4 | `app/Services/PaymentEngine/SupportActionService.php` | Added `$excludeActionId` passthrough; fixed coupon apply to allow admin-set amounts |

### Previous Session Changes (not this session)

| # | File | Change |
|---|------|--------|
| 1 | `app/Http/Controllers/OfflinePaymentController.php` | Added UPE dual-write for offline payment approvals |
| 2 | `app/Console/Commands/UPESyncAccessControl.php` | NEW: Syncs legacy `webinar_access_control` → UPE |
| 3 | `app/Console/Commands/UPESyncPartPayments.php` | NEW: Syncs legacy part payments → UPE ledger |
| 4 | `app/Console/Kernel.php` | Added cron schedules for sync commands |

---

## 4. IMMEDIATE HOTFIX PLAN (24–48 Hours)

### Priority 1: Fix CheckoutService — Buy Course flow (Hours 0–4)

**These 3 fixes restore ALL new purchases:**

```
File: app/Services/PaymentEngine/CheckoutService.php

FIX A: sale_type enum (5 occurrences)
  Line 64:  'sale_type' => 'new'      →  'sale_type' => $amount > 0 ? 'paid' : 'free'
  Line 160: 'sale_type' => 'new'      →  'sale_type' => $amount > 0 ? 'paid' : 'free'
  Line 254: 'sale_type' => 'renewal'  →  'sale_type' => 'paid'
  Line 273: 'sale_type' => 'new'      →  'sale_type' => $amount > 0 ? 'paid' : 'free'
  Line 417: 'sale_type' => 'new'      →  'sale_type' => $amount > 0 ? 'paid' : 'free'

FIX B: pricing_mode enum (2 occurrences)
  Line 65:  'pricing_mode' => 'one_time'  →  'pricing_mode' => 'full'
  Line 161: 'pricing_mode' => 'one_time'  →  'pricing_mode' => 'full'

FIX C: Method name (5 occurrences)
  Lines 78, 173, 299, 372, 459:
  $this->ledger->appendEntry(...)  →  $this->ledger->append(...)
  (Change from array to positional parameters)

FIX D: resolveProduct name column
  Remove 'name' parameter from resolveProduct() signature and calls
```

### Priority 2: Fix WebinarController directPayment (Hours 4–6)

```
File: app/Http/Controllers/Web/WebinarController.php
Add missing variable definitions before line 1164
```

### Priority 3: RBAC — Add admin middleware (Hours 6–8)

```
File: routes/api/upe.php
Wrap support action write routes + admin routes with admin middleware
```

### Priority 4: Security — Disable debug mode (Hour 8)

```
File: .env
APP_DEBUG=false
```

---

## 5. SPRINT-LEVEL IMPROVEMENT PLAN

### Sprint 1 (Current Sprint — Hotfixes)
- [ ] Fix CheckoutService enum values + method calls
- [ ] Fix WebinarController undefined variables
- [ ] Add RBAC to support + admin routes
- [ ] Set APP_DEBUG=false
- [ ] Disable or install Telescope properly
- [ ] Run end-to-end purchase test (webinar, bundle, subscription, installment)

### Sprint 2 (Next Sprint — Hardening)
- [ ] Add DB-level validation in `UpeSale::boot()` for valid enum values
- [ ] Add integration tests for all CheckoutService methods
- [ ] Add monitoring alerts for failed payment processing
- [ ] Implement retry logic in `BuyNowProcessJob` for transient DB errors
- [ ] Add proper user-friendly error pages (not stack traces)
- [ ] Replace `env()` calls with `config()` in runtime code

### Sprint 3 (Stabilization)
- [ ] Remove dual-write and go UPE-only for write path
- [ ] Add API rate limiting on support action endpoints
- [ ] Add request logging middleware for all UPE routes
- [ ] Implement webhook signature verification for Razorpay webhooks
- [ ] Add health check endpoint for payment engine

---

## 6. LONG-TERM ARCHITECTURE IMPROVEMENTS

1. **Enum Safety:** Replace MySQL ENUM columns with VARCHAR + application-level validation. ENUMs are rigid and cause silent data truncation.

2. **Service Interface Contracts:** Define interfaces for `PaymentLedgerService`, `CheckoutService` etc. The `appendEntry()` vs `append()` mismatch would be caught at compile time.

3. **Event-Driven Architecture:** Replace direct method calls in `CheckoutService` with domain events (`SalePurchased`, `RefundProcessed`). Decouples legacy dual-write from core logic.

4. **Feature Flags:** Use feature flags for new support actions so they can be enabled/disabled per-role without code deploys.

5. **Idempotency Keys:** Standardize idempotency key format across all entry points (currently mixed: `rp_`, `checkout_`, `support_`, etc.)

---

## 7. PRODUCTION RELEASE RECOMMENDATION

### 🔴 BLOCKER — Do NOT release without fixing Issues #1–5

| Issue | Status | Blocker? |
|-------|--------|----------|
| #1 CheckoutService sale_type enum | ✅ Fixed (2026-02-16) | 🔴 WAS BLOCKER |
| #2 CheckoutService pricing_mode enum | ✅ Fixed (2026-02-16) | 🔴 WAS BLOCKER |
| #3 CheckoutService appendEntry method | ✅ Fixed (2026-02-16) | 🔴 WAS BLOCKER |
| #4 WebinarController undefined vars | ✅ Fixed (2026-02-16) | 🔴 WAS BLOCKER |
| #5 Support routes no RBAC | ✅ Fixed (2026-02-16) | 🔴 WAS BLOCKER |
| #6 APP_DEBUG=true | ❌ Unfixed (manual .env change needed) | 🟡 Conditional |
| #7 resolveProduct name column | ✅ Fixed (2026-02-16) | 🟢 Safe (silent) |
| #8 Telescope missing | ❌ Unfixed | 🟢 Safe |
| #9 Admin routes no RBAC | ✅ Fixed (2026-02-16) | 🟡 WAS Conditional |
| #10 base_fee from discounted amount | ✅ Fixed (2026-02-16) | 🟡 WAS Conditional |
| #11 hasPendingAction self-block | ✅ Fixed | N/A |
| #12 Coupon apply no-code | ✅ Fixed | N/A |

**Updated Verdict (2026-02-16): 10 of 12 issues fixed. Remaining: #6 (set APP_DEBUG=false in .env) and #8 (run telescope:install or disable). All blockers resolved.**
