# QA Security Audit Report — Support Request System
**Date:** 2026-02-10  
**Auditor:** Cascade (Senior Backend QA + Security Engineer)  
**Scope:** All 11 support-ticket scenarios across both code paths

---

## IMPORTANT: Two Active Code Paths

| Path | Route | Method | Currently Used by UI |
|------|-------|--------|---------------------|
| **LEGACY** | `PUT /support/{id}/status` | `AdminSupportController@updateStatus` | **YES** |
| **SECURE** | `PUT /support/{id}/status-secure` | `AdminSupportController@updateStatusSecure` → `SupportRequestService@transition` | **NO** (not yet wired) |

**Critical finding:** The LEGACY path is the one actively used. All scenario execution in production flows through `updateStatus()`. The SECURE path exists but the UI has not been switched to it yet.

---

## SCENARIO 1: course_extension

### SECURE PATH (`SupportRequestService::executeCourseExtension`, lines 220–256)
| Check | Result | Evidence |
|-------|--------|----------|
| Authorization | ✅ PASS | `updateStatusSecure` line 909: `$this->authorize('admin_support_manage')` |
| Transaction boundary | ✅ PASS | `transition()` line 51: `DB::transaction(function () ...` |
| Idempotency | ✅ PASS | line 60: `if ($newStatus === 'executed' && $lockedRequest->executed_at !== null)` |
| Immutability | ✅ PASS | Old access set to `status='replaced'`, new record created. No deletes. |
| Audit logging | ✅ PASS | line 78: `SupportAuditLog::log(...)` |
| **Verdict** | **✅ PASS** | |

### LEGACY PATH (`updateStatus`, lines 835–853)
| Check | Result | Evidence |
|-------|--------|----------|
| Authorization | ✅ PASS | line 525: `$this->authorize('admin_support_manage')` |
| Transaction boundary | ✅ PASS | line 527: `DB::beginTransaction()` |
| Idempotency | ❌ **FAIL** | No `executed_at` check. No `lockForUpdate()`. Same ticket can be completed multiple times. |
| Immutability | ❌ **CRITICAL FAIL** | **line 842–844**: `WebinarAccessControl::where(...)->delete()` — HARD DELETE of access control records |
| Audit logging | ❌ **FAIL** | No `SupportAuditLog::log()` call anywhere in legacy path |
| **Verdict** | **❌ FAIL** | Risk: **CRITICAL** |

**Fix for legacy line 842–844:**
```php
// REPLACE (line 842-844):
WebinarAccessControl::where('user_id', $supportRequest->user_id)
    ->where('webinar_id', $supportRequest->webinar_id)
    ->delete();

// WITH:
WebinarAccessControl::where('user_id', $supportRequest->user_id)
    ->where('webinar_id', $supportRequest->webinar_id)
    ->where('status', 'active')
    ->update(['status' => 'replaced']);
```

---

## SCENARIO 2: temporary_access

### SECURE PATH (`SupportRequestService::executeTemporaryAccess`, lines 261–285)
| Check | Result | Evidence |
|-------|--------|----------|
| Authorization | ✅ PASS | |
| Transaction | ✅ PASS | |
| Idempotency | ✅ PASS | |
| Immutability | ✅ PASS | Creates new record only, no mutations. |
| Audit logging | ✅ PASS | |
| **Verdict** | **✅ PASS** | |

### LEGACY PATH (`updateStatus`, lines 821–831)
| Check | Result | Evidence |
|-------|--------|----------|
| Authorization | ✅ PASS | |
| Transaction | ✅ PASS | |
| Idempotency | ❌ **FAIL** | No `executed_at` guard. Duplicate `WebinarAccessControl` rows created on re-execution. |
| Immutability | ✅ PASS | Creates new record. |
| Audit logging | ❌ **FAIL** | No audit log. |
| **Verdict** | **❌ FAIL** | Risk: **HIGH** |

---

## SCENARIO 3: mentor_access

### SECURE PATH (`SupportRequestService::executeMentorAccess`, lines 290–323)
| Check | Result | Evidence |
|-------|--------|----------|
| Authorization | ✅ PASS | |
| Transaction | ✅ PASS | |
| Idempotency | ✅ PASS | line 298: checks for existing active mentor access and throws if duplicate |
| Immutability | ✅ PASS | |
| Audit logging | ✅ PASS | |
| **Verdict** | **✅ PASS** | |

### LEGACY PATH (`updateStatus`, lines 706–749)
| Check | Result | Evidence |
|-------|--------|----------|
| Authorization | ✅ PASS | |
| Transaction | ✅ PASS | |
| Idempotency | ❌ **FAIL** | line 713: checks `$existingSale` but NOT `WebinarAccessControl`. No `executed_at` guard. Sale+MentorAccessRequest created every re-submit. |
| Immutability | ✅ PASS | |
| Audit logging | ❌ **FAIL** | No audit log. |
| **Verdict** | **❌ FAIL** | Risk: **HIGH** |

---

## SCENARIO 4: relatives_friends_access

### SECURE PATH (`SupportRequestService::executeRelativesFriendsAccess`, lines 328–363)
| Check | Result | Evidence |
|-------|--------|----------|
| Authorization | ✅ PASS | |
| Transaction | ✅ PASS | |
| Idempotency | ✅ PASS | line 333: checks existing active sale, throws if duplicate |
| Immutability | ✅ PASS | |
| Audit logging | ✅ PASS | |
| **Verdict** | **✅ PASS** | |

### LEGACY PATH (`updateStatus`, lines 681–703)
| Check | Result | Evidence |
|-------|--------|----------|
| Idempotency | ❌ **FAIL** | No duplicate check. Re-completing creates duplicate Sale rows for same user+course. |
| Audit logging | ❌ **FAIL** | |
| **Verdict** | **❌ FAIL** | Risk: **HIGH** |

---

## SCENARIO 5: free_course_grant

### SECURE PATH (`SupportRequestService::executeFreeCourseGrant`, lines 368–419)
| Check | Result | Evidence |
|-------|--------|----------|
| Authorization | ✅ PASS | |
| Transaction | ✅ PASS | |
| Idempotency | ✅ PASS | line 384: per-user duplicate check before creating Sale |
| Immutability | ✅ PASS | |
| Audit logging | ✅ PASS | |
| **Verdict** | **✅ PASS** | |

### LEGACY PATH (`updateStatus`, lines 752–819)
| Check | Result | Evidence |
|-------|--------|----------|
| Idempotency | ⚠️ PARTIAL | line 775: per-user duplicate check exists. But no `executed_at` guard on the ticket itself. Re-completing re-iterates all users. |
| Audit logging | ❌ **FAIL** | Log call commented out (lines 802–810). |
| **Verdict** | **❌ FAIL** | Risk: **MEDIUM** |

---

## SCENARIO 6: offline_cash_payment

### SECURE PATH (`SupportRequestService::executeOfflineCashPayment`, lines 424–452)
| Check | Result | Evidence |
|-------|--------|----------|
| Authorization | ✅ PASS | |
| Transaction | ✅ PASS | Outer `DB::transaction` from `transition()` |
| Idempotency | ✅ PASS (ticket-level) | `executed_at` guard at line 60 |
| Immutability | ✅ PASS | |
| Audit logging | ✅ PASS | |
| Missing import | ❌ **FAIL** | `AdminCoursePurchaseService` not imported at top of file. Line 426: `app(AdminCoursePurchaseService::class)` will fail at runtime. |
| Wrong method signature | ❌ **CRITICAL FAIL** | Line 435: `$purchaseService->purchaseCourseWithInstallment($purchaseData)` passes array. But `AdminCoursePurchaseService::purchaseCourseWithInstallment()` expects 5 individual params: `($courseId, $userId, $installmentId, $discountId, $adminId)`. **Will throw TypeError at runtime.** |
| Nested transaction | ❌ **FAIL** | `AdminCoursePurchaseService::purchaseCourseDirectly()` line 27 calls `DB::beginTransaction()` inside the already-open `DB::transaction` from `transition()`. MySQL nested transactions silently commit the outer. |
| **Verdict** | **❌ FAIL** | Risk: **CRITICAL** |

**Fix:** 
```php
// SupportRequestService.php — add at top:
use App\Services\AdminCoursePurchaseService;

// Lines 434-438 — fix method call signatures:
if (!empty($request->installment_id)) {
    $result = $purchaseService->purchaseCourseWithInstallment(
        $request->webinar_id,
        $request->user_id,
        $request->installment_id,
        null,
        $user->id
    );
} else {
    $result = $purchaseService->purchaseCourseDirectly(
        $request->webinar_id,
        $request->user_id,
        null,
        $user->id
    );
}

// Lines 440-444 — fix result key ('sale_id' not 'sale' object):
// AdminCoursePurchaseService returns 'sale_id', not 'sale' model.
// Need to fetch Sale by ID:
if (isset($result['sale_id'])) {
    Sale::where('id', $result['sale_id'])->update([
        'support_request_id' => $request->id,
        'granted_by_admin_id' => $user->id,
    ]);
}
```

**Nested transaction fix:** Remove `DB::beginTransaction()`/`DB::commit()`/`DB::rollBack()` from `AdminCoursePurchaseService` methods and let the caller manage the transaction. OR use `DB::transaction()` which supports savepoints.

### LEGACY PATH (`updateStatus` → `offlineCashPayment`, lines 859–860, 2087–2168)
| Check | Result | Evidence |
|-------|--------|----------|
| Nested transaction | ❌ **FAIL** | `offlineCashPayment()` calls `AdminCoursePurchaseService::purchaseCourseDirectly()` which opens `DB::beginTransaction()` (line 27 of service) inside the already-open transaction from `updateStatus` line 527. |
| Idempotency | ❌ **FAIL** | No `executed_at` guard. |
| Audit logging | ❌ **FAIL** | |
| **Verdict** | **❌ FAIL** | Risk: **CRITICAL** |

---

## SCENARIO 7: installment_restructure

### SECURE PATH (`SupportRequestService::executeInstallmentRestructure`, lines 457–482)
| Check | Result | Evidence |
|-------|--------|----------|
| Authorization | ✅ PASS | |
| Transaction | ✅ PASS | |
| lockForUpdate | ✅ PASS | line 462 |
| Idempotency | ✅ PASS | `executed_at` guard + lockForUpdate |
| Immutability | ✅ PASS | |
| Audit logging | ✅ PASS | |
| Incomplete execution | ⚠️ **WARN** | Only approves the `InstallmentRestructureRequest` record. Does NOT create sub-steps. The legacy `adminApproveRestructure` does the actual sub-step creation. This handler is a no-op if `restructureRequest` is null. |
| **Verdict** | **⚠️ PARTIAL PASS** | Risk: **MEDIUM** — handler is incomplete |

### LEGACY PATH (`updateStatus` → `adminApproveRestructure`, lines 671–672, 1085–1413)
| Check | Result | Evidence |
|-------|--------|----------|
| Nested transaction | ❌ **CRITICAL FAIL** | line 1094: `DB::beginTransaction()` inside the already-open `updateStatus` transaction (line 527). Creates nested transaction. |
| Immutability | ❌ **FAIL** | line 1296–1298: `SubStepInstallment::where(...)->delete()` — HARD DELETE of existing sub-steps |
| Status overwrite | ❌ **FAIL** | line 1367-1368: Overwrites `status` to `'close'` directly, bypassing the main `$updateData` flow at line 870. Two competing status updates. |
| Idempotency | ❌ **FAIL** | No `executed_at` guard. |
| Audit logging | ❌ **FAIL** | |
| **Verdict** | **❌ FAIL** | Risk: **CRITICAL** |

---

## SCENARIO 8: new_service_access

### SECURE PATH (`SupportRequestService::executeNewServiceAccess`, lines 487–507)
| Check | Result | Evidence |
|-------|--------|----------|
| Authorization | ✅ PASS | |
| Transaction | ✅ PASS | |
| Idempotency | ✅ PASS | `executed_at` guard |
| Immutability | ✅ PASS | Creates new record only |
| Audit logging | ✅ PASS | |
| **Verdict** | **✅ PASS** | |

### LEGACY PATH
| Check | Result | Evidence |
|-------|--------|----------|
| Not implemented | ❌ **FAIL** | No `new_service_access` handler exists in legacy `updateStatus`. This scenario is **silently ignored** — ticket status updates to "completed" but no `ServiceAccess` record is created. |
| **Verdict** | **❌ FAIL** | Risk: **HIGH** — data loss |

---

## SCENARIO 9: refund_payment

### SECURE PATH (`SupportRequestService::executeRefundPayment`, lines 512–569)
| Check | Result | Evidence |
|-------|--------|----------|
| Authorization | ✅ PASS | |
| Transaction | ✅ PASS | |
| lockForUpdate | ✅ PASS | line 518 |
| Idempotency | ✅ PASS | `executed_at` guard + lockForUpdate on Sale |
| Immutability | ✅ PASS | Soft-revoke: `refund_at=time(), access_to_purchased_item=0`. No `delete()`. |
| Reversal accounting | ✅ PASS | line 530: negative Accounting entry |
| Refund record | ✅ PASS | line 541: `Refund::create(...)` |
| Access control revoke | ✅ PASS | line 559-561: `update(['status' => 'revoked'])` |
| Audit logging | ✅ PASS | |
| **Verdict** | **✅ PASS** | |

### LEGACY PATH (`updateStatus` → `refundPayment`, lines 863–864, 2177–2268)
| Check | Result | Evidence |
|-------|--------|----------|
| Nested transaction | ❌ **CRITICAL FAIL** | line 2179: `DB::beginTransaction()` inside the already-open `updateStatus` transaction (line 527). |
| lockForUpdate | ❌ **FAIL** | No lock on Sale. Race condition possible. |
| Immutability | ✅ PASS | V-12 fix applied. Soft-revoke pattern. No `delete()`. |
| Idempotency | ❌ **FAIL** | No `executed_at` guard. Can be re-executed. |
| Audit logging | ❌ **FAIL** | |
| **Verdict** | **❌ FAIL** | Risk: **CRITICAL** |

---

## SCENARIO 10: post_purchase_coupon

### SECURE PATH (`SupportRequestService::executePostPurchaseCoupon`, lines 574–637)
| Check | Result | Evidence |
|-------|--------|----------|
| Authorization | ✅ PASS | |
| Transaction | ✅ PASS | |
| Idempotency | ✅ PASS | `executed_at` guard |
| Immutability | ✅ PASS | Creates `CouponCredit` + `Accounting` records. No mutations to Sale. |
| Coupon decrement | ❌ **FAIL** | Coupon `count` / usage is NOT decremented. The plan specifies "Decrement coupon usage count" but this is not done. Coupon can be applied unlimited times to different tickets. |
| Audit logging | ✅ PASS | |
| **Verdict** | **❌ FAIL** | Risk: **MEDIUM** |

**Fix (add after line 627):**
```php
// Decrement coupon usage
$discount->increment('used_count');
```

### LEGACY PATH (`updateStatus` → `ApplyCouponCode`, lines 855–856, 1963–2085)
| Check | Result | Evidence |
|-------|--------|----------|
| Idempotency | ❌ **FAIL** | No guard. Creates duplicate `WebinarPartPayment` records on re-execution. |
| No CouponCredit record | ❌ **FAIL** | Creates `WebinarPartPayment` (line 2057) instead of `CouponCredit`. No audit trail for the coupon application. |
| No Accounting entry | ❌ **FAIL** | No wallet credit created. |
| Audit logging | ❌ **FAIL** | |
| **Verdict** | **❌ FAIL** | Risk: **HIGH** |

---

## SCENARIO 11: wrong_course_correction

### SECURE PATH (`SupportRequestService::executeWrongCourseCorrection`, lines 642–751)
| Check | Result | Evidence |
|-------|--------|----------|
| Authorization | ✅ PASS | |
| Transaction | ✅ PASS | |
| lockForUpdate | ✅ PASS | line 657 on old Sale |
| Idempotency | ✅ PASS | `executed_at` guard + lockForUpdate |
| Immutability | ✅ PASS | Soft-revoke old sale (line 673-676). New sale created (line 687). No `webinar_id` overwrites anywhere. Old installment set to `transferred` (line 716-718). |
| webinar_id overwrite | ✅ PASS | Zero `->update(['webinar_id' => ...])` in this method |
| Reversal accounting | ✅ PASS | line 678 (negative) + line 701 (positive) |
| Installment transfer | ✅ PASS | line 721-731: new order with `parent_order_id` linkage |
| Access control revoke | ✅ PASS | line 739-741: `update(['status' => 'revoked'])` |
| Audit logging | ✅ PASS | |
| **Verdict** | **✅ PASS** | |

### LEGACY PATH (`updateStatus` → `handleWrongCourseCorrection`, lines 676–678, 1750–1865)
| Check | Result | Evidence |
|-------|--------|----------|
| lockForUpdate | ❌ **FAIL** | No lock on old Sale. Race condition possible. |
| Idempotency | ❌ **FAIL** | No `executed_at` guard. Can create duplicate new Sales on re-execution. |
| Immutability | ✅ PASS | V-13 fix applied. Soft-revoke + new-grant. No `webinar_id` overwrites. |
| Audit logging | ❌ **FAIL** | |
| **Verdict** | **❌ FAIL** | Risk: **HIGH** |

---

## CROSS-CUTTING FINDINGS

### F-01: LEGACY PATH HAS ZERO AUDIT LOGGING
- **File:** `AdminSupportController.php`
- **Lines:** 523–898 (entire `updateStatus` method)
- **Reason:** `SupportAuditLog::log()` is never called in the legacy flow. Only the secure `SupportRequestService::transition()` calls it (line 78).
- **Risk:** CRITICAL — no audit trail for any scenario executed via legacy path.

### F-02: NESTED DB::TRANSACTION IN LEGACY PATH
- **File:** `AdminSupportController.php`
- **Lines:** 527 (outer), 1094 (adminApproveRestructure inner), 2179 (refundPayment inner)
- **File:** `AdminCoursePurchaseService.php` lines 27, 145 (called from offlineCashPayment)
- **Reason:** `updateStatus()` opens `DB::beginTransaction()` at line 527. Then `adminApproveRestructure()` opens ANOTHER at line 1094. `refundPayment()` opens ANOTHER at line 2179. `offlineCashPayment()` calls `AdminCoursePurchaseService` which opens ANOTHER at lines 27/145. MySQL treats these as savepoints but `DB::commit()` inside the inner methods commits the OUTER transaction prematurely.
- **Risk:** CRITICAL — partial commits, data inconsistency.

**Fix for F-02 (refundPayment, line 2179):**
```php
// REMOVE lines 2179 and 2249 (the nested beginTransaction/commit)
// The outer updateStatus() transaction already wraps this.
// Just remove DB::beginTransaction(); and DB::commit(); and the try/catch rollback.
```

### F-03: SECURE PATH — Scenario 6 (offline_cash_payment) WILL CRASH AT RUNTIME
- **File:** `SupportRequestService.php`
- **Lines:** 426, 435, 437
- **Reason 1:** Missing `use App\Services\AdminCoursePurchaseService;` import
- **Reason 2:** Method called with array `($purchaseData)` but expects individual params `($courseId, $userId, ...)`
- **Reason 3:** Result accesses `$result['sale']` (model) but service returns `$result['sale_id']` (int)
- **Reason 4:** Nested transaction (service opens `DB::beginTransaction` inside `DB::transaction`)
- **Risk:** CRITICAL — **100% runtime failure** for this scenario via secure path.

### F-04: LEGACY course_extension HARD DELETES ACCESS CONTROL
- **File:** `AdminSupportController.php`
- **Lines:** 842–844
- **Reason:** `WebinarAccessControl::where(...)->delete()` destroys historical access records.
- **Risk:** CRITICAL — violates immutability rule.

### F-05: LEGACY installment_restructure HARD DELETES SUB-STEPS
- **File:** `AdminSupportController.php`
- **Lines:** 1296–1298
- **Reason:** `SubStepInstallment::where(...)->delete()` destroys existing sub-step records.
- **Risk:** HIGH — financial data loss.

---

## SUMMARY MATRIX (FINAL — ALL FIXES APPLIED)

| # | Scenario | Secure Path | Legacy Path |
|---|----------|-------------|-------------|
| 1 | course_extension | ✅ PASS | ✅ PASS (delete→soft-revoke, idempotency guard, audit log) |
| 2 | temporary_access | ✅ PASS | ⚠️ LOW (no per-scenario dedup, but idempotency guard blocks re-complete) |
| 3 | mentor_access | ✅ PASS | ⚠️ LOW (no per-scenario dedup, but idempotency guard blocks re-complete) |
| 4 | relatives_friends_access | ✅ PASS | ⚠️ LOW (no per-scenario dedup, but idempotency guard blocks re-complete) |
| 5 | free_course_grant | ✅ PASS | ✅ PASS (per-user dedup + idempotency guard + audit log) |
| 6 | offline_cash_payment | ✅ PASS | ⚠️ LOW (nested txn fixed, idempotency guard + audit added) |
| 7 | installment_restructure | ✅ PASS (FIXED: full sub-step creation) | ⚠️ LOW (nested txn fixed, delete→soft-cancel, idempotency guard + audit) |
| 8 | new_service_access | ✅ PASS | ❌ MEDIUM (not implemented in legacy — switch to secure path required) |
| 9 | refund_payment | ✅ PASS | ✅ PASS (nested txn fixed, lockForUpdate added, idempotency guard + audit) |
| 10 | post_purchase_coupon | ✅ PASS | ⚠️ LOW (wrong record type — switch to secure path required) |
| 11 | wrong_course_correction | ✅ PASS | ⚠️ LOW (no lockForUpdate, but idempotency guard blocks re-complete + audit) |

### Secure Path: 11/11 PASS ✅
### Legacy Path: 3 PASS, 6 LOW, 1 MEDIUM, 1 NOT IMPLEMENTED

---

## FIXES APPLIED IN THIS AUDIT SESSION

| Priority | Fix | File | Status |
|----------|-----|------|--------|
| **P0** | Fix `executeOfflineCashPayment` — import, method signature, result key | `SupportRequestService.php` | ✅ DONE |
| **P0** | Remove nested `DB::beginTransaction` from `refundPayment()` + add `lockForUpdate()` | `AdminSupportController.php` | ✅ DONE |
| **P0** | Remove nested `DB::beginTransaction` from `adminApproveRestructure()` | `AdminSupportController.php` | ✅ DONE |
| **P1** | Fix legacy `course_extension` delete → `update(['status' => 'replaced'])` | `AdminSupportController.php` | ✅ DONE |
| **P1** | Fix legacy `installment_restructure` delete → `update(['status' => 'cancelled'])` | `AdminSupportController.php` | ✅ DONE |
| **P1** | Add `$discount->increment('used_count')` to `executePostPurchaseCoupon` | `SupportRequestService.php` | ✅ DONE |
| **P2** | Convert `AdminCoursePurchaseService` to `DB::transaction()` closure (savepoint-safe) | `AdminCoursePurchaseService.php` | ✅ DONE |

## ADDITIONAL FIXES APPLIED (Round 2)

| Priority | Fix | File | Status |
|----------|-----|------|--------|
| **P3** | Complete `executeInstallmentRestructure` — full sub-step creation logic ported (was only approving, not creating sub-steps). Also fixed bug: legacy sub-step 2 had wrong `installment_order_id` | `SupportRequestService.php` | ✅ DONE |
| **P3** | Add `executed_at` idempotency guard to legacy `updateStatus` completed block | `AdminSupportController.php` | ✅ DONE |
| **P3** | Add `SupportAuditLog::log()` to legacy `updateStatus` (every transition is now logged) | `AdminSupportController.php` | ✅ DONE |
| **P3** | Add `use App\Models\SupportAuditLog` import to controller | `AdminSupportController.php` | ✅ DONE |
| **P3** | Add `use App\Models\InstallmentStep` + `SubStepInstallment` imports to service | `SupportRequestService.php` | ✅ DONE |

## REMAINING ACTIONS (Manual / UI)

| Priority | Action | Owner |
|----------|--------|-------|
| **P2** | Switch UI to use `/status-secure` endpoint | Frontend dev |
| **LOW** | Scenario 8 (`new_service_access`) has no handler in legacy path — only works via secure path | N/A until UI switch |

---

*End of QA Audit Report*
