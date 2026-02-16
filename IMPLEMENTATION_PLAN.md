# IMPLEMENTATION PLAN — Asttrolok LMS Security & Scenario Enforcement

**Author:** Lead System Architect  
**Date:** 10 Feb 2026  
**Status:** APPROVED — Ready for Implementation  
**Scope:** All 11 business scenarios + full security remediation

---

## A. SCENARIO-BY-SCENARIO IMPLEMENTATION PLAN

---

### SCENARIO 1: Course Extension

**Current Code Path:**
- Student creates request: `NewSupportForAsttrolokController@store` → scenario `course_extension`
- Admin completes: `AdminSupportController@updateStatus` → `completed` block → creates `WebinarAccessControl` row with `percentage=100` and `expire = now() + extension_days`
- Existing limit: max 3 approved extensions per user per course (checked in `NewSupportForAsttrolokController@store`)

**Violations Found:**
1. On completion, the code **deletes** old `WebinarAccessControl` rows before creating a new one — destroys history
2. Extension days are only validated against `[7, 15, 30]` — hardcoded whitelist, but `extension_days` input allows `min:1|max:365` at creation
3. No `$this->authorize()` call on `updateStatus()`
4. `approved` status stores `temporary_access_percentage` even when not relevant to this scenario
5. No audit log entry is created for the extension grant

**Correct Execution Flow:**
```
Student → Creates request (scenario=course_extension, extension_days, reason)
         Validation: extension_days in [7, 15, 30], max 3 per course
Support → Verifies (status → verified, support_handler_id set)
         CANNOT set completed or approved
Admin   → Executes (status → executed)
         1. Validate extension_days matches original request (immutable after creation)
         2. SOFT-EXPIRE old WebinarAccessControl (set status=replaced, replaced_by=new_id)
         3. Create new WebinarAccessControl with:
            - user_id, webinar_id, percentage=100
            - expire = now() + extension_days
            - support_request_id = FK to request
            - granted_by = admin_id
         4. Create audit_log entry
         5. Update support request status = executed
```

**Required Changes:**
- Add `$this->authorize('admin_support_execute')` to `updateStatus()`
- Enforce 3-step workflow: `pending → verified → executed` (not `completed`)
- Remove delete of old `WebinarAccessControl`; add `status` + `replaced_by` columns
- Add `support_request_id` and `granted_by` to `WebinarAccessControl`
- Remove hardcoded `[7, 15, 30]` whitelist; validate against the original request's `extension_days`
- Create `support_audit_logs` entry on every state transition

---

### SCENARIO 2: Temporary Access (Pending Payment)

**Current Code Path:**
- Student creates: `NewSupportForAsttrolokController@store` → scenario `temporary_access`
- Admin completes: `AdminSupportController@updateStatus` → creates `WebinarAccessControl` with `expire = now() + 7 days` and `percentage` from request

**Violations Found:**
1. No auto-expiry job exists — `WebinarAccessControl.expire` is checked at read-time in `WebinarController` but never cleaned up
2. The `percentage` is set on `approved` status (by Support Role or Admin) but `WebinarAccessControl` is created on `completed` — the value could be stale
3. Temporary access has no link to pending payment — cannot verify payment is actually pending
4. No `$this->authorize()` call
5. `temporary_access_days` is hardcoded to `7` in the store method, ignoring student input

**Correct Execution Flow:**
```
Student → Creates request (scenario=temporary_access, webinar_id, reason)
         System validates: user has a pending installment or pending payment for this course
Support → Verifies (status → verified)
         Sets percentage (content access %)
Admin   → Executes (status → executed)
         1. Validate pending payment exists for user+course
         2. Create WebinarAccessControl with:
            - percentage from verified request
            - expire = now() + 7 days (system-enforced, not user-supplied)
            - type = 'temporary_pending_payment'
            - support_request_id = FK
         3. Create audit_log entry
         4. NO payment status changes
System  → ExpireTemporaryAccessJob (runs daily)
         - Finds all WebinarAccessControl where expire < now()
         - Sets status = 'expired'
         - Logs expiry
```

**Required Changes:**
- Add `type` column to `WebinarAccessControl` (enum: temporary, extension, permanent)
- Create `ExpireTemporaryAccessJob` scheduled daily
- Validate pending payment exists before granting temporary access
- Add `$this->authorize()` 
- Enforce 3-step workflow

---

### SCENARIO 3: Mentor Access

**Current Code Path:**
- Student creates: `NewSupportForAsttrolokController@store` → scenario `mentor_access`
- Admin completes: `AdminSupportController@updateStatus` → creates `Sale` (amount=0, manual_added=true) + `MentorAccessRequest`

**Violations Found:**
1. Creates a zero-value `Sale` granting full permanent course access — supposed to be mentor-specific access, not course ownership
2. No revocation mechanism
3. No `$this->authorize()` call
4. Does NOT check if user already has a sale for this course
5. `MentorAccessRequest` table is used but has no revocation workflow

**Correct Execution Flow:**
```
Student → Creates request (scenario=mentor_access, webinar_id, requested_mentor_id, reason)
Support → Verifies (status → verified)
Admin   → Executes (status → executed)
         1. Check user doesn't already have mentor access for this course
         2. Create MentorAccessRequest (status=active, revocable=true)
         3. DO NOT create Sale record — mentor access is separate from purchase
         4. Create WebinarAccessControl with:
            - type = 'mentor_access'
            - percentage = 100 (or configurable)
            - expire = course access_days from now (or null if permanent)
            - mentor_access_request_id = FK
         5. Create audit_log entry
Revocation:
         Admin can set MentorAccessRequest.status = 'revoked'
         → triggers WebinarAccessControl.status = 'revoked'
         → audit_log entry
```

**Required Changes:**
- Remove `Sale` creation from mentor_access flow
- Use `WebinarAccessControl` with `type=mentor_access` instead
- Add `mentor_access_request_id` FK to `WebinarAccessControl`
- Add revocation endpoint + audit
- Enforce 3-step workflow

---

### SCENARIO 4: Relatives / Friends Access

**Current Code Path:**
- Student creates: `NewSupportForAsttrolokController@store` → scenario `relatives_friends_access`
- Admin completes: `AdminSupportController@updateStatus` → creates `Sale` (amount=0, manual_added=true, access_to_purchased_item=1)

**Violations Found:**
1. No link back to the original support request on the `Sale` record
2. No check if the target user (relative/friend) already has access
3. The `Sale` is created for `$supportRequest->user_id` (the requesting student), NOT for a separate relative/friend user — scenario says "grant access to a different user"
4. No `$this->authorize()` call

**Correct Execution Flow:**
```
Student → Creates request (scenario=relatives_friends_access, webinar_id, 
          relative_name, relative_email, relative_phone, reason)
Support → Verifies identity of relative, sets status → verified
Admin   → Executes (status → executed)
         1. Find or create User for relative (by email/phone)
         2. Check relative doesn't already have access
         3. Create Sale:
            - buyer_id = relative_user_id (NOT requesting student)
            - amount = 0, total_amount = 0
            - manual_added = true
            - payment_method = 'credit'
            - support_request_id = FK (new column)
            - granted_by_admin_id = admin_id
         4. Create audit_log entry with:
            - original_requester_id = student
            - beneficiary_id = relative
            - support_request_id
```

**Required Changes:**
- Add `support_request_id` and `granted_by_admin_id` columns to `sales` table
- Fix target: access goes to relative, not the requesting student
- Add relative user fields to support request
- Add duplicate access check
- Enforce 3-step workflow

---

### SCENARIO 5: Free Course Grant

**Current Code Path:**
- Admin creates via `AdminSupportController@grantQuickAccess` → creates `NewSupportForAsttrolok` with status=`pending`
- Admin completes via `updateStatus` → iterates all users of source course → creates `Sale` (amount=0) for each in target course
- Also: `AdminSupportController@quickSupportForm` provides the UI

**Violations Found:**
1. `grantQuickAccess` has NO `$this->authorize()` call
2. The request is created AND can be completed by the same admin — no separation of duties
3. Bulk `Sale` creation in a loop without DB transaction wrapping (it's inside the outer `updateStatus` transaction but if that fails, partial grants may persist)
4. No approval logging per user granted
5. The `grantQuickAccess` only creates a pending request — actual grant happens on `completed` status, which is correct, but there's no Support verification step

**Correct Execution Flow:**
```
Support → Creates request (scenario=free_course_grant, source_course_id, target_course_id, reason)
         System calculates affected user count
Support → Verifies (status → verified, confirms user list)
Admin   → Executes (status → executed)
         1. Begin transaction
         2. For each source course user:
            a. Skip if already has target course access
            b. Create Sale (amount=0, manual_added=true, support_request_id=FK)
            c. Log per-user grant in audit_log
         3. Update request: granted_count, skipped_count
         4. Commit transaction
         5. Update status = executed
```

**Required Changes:**
- Add `$this->authorize('admin_support_execute')` to `grantQuickAccess` and `updateStatus`
- Move request creation from admin to support role
- Enforce 3-step workflow: support creates → support verifies → admin executes
- Add per-user audit logging
- Add `support_request_id` FK to `Sale`

---

### SCENARIO 6: Offline / Cash Payment

**Current Code Path:**
- Student creates: `NewSupportForAsttrolokController@store` → scenario `offline_cash_payment` (uploads receipt)
- Admin completes: `AdminSupportController@updateStatus` → calls `offlineCashPayment()` → uses `AdminCoursePurchaseService` to create Order, OrderItem, Sale, Accounting
- ALSO: separate `OfflinePaymentController@store` (user submits) → `Admin\OfflinePaymentController@approved` (wallet credit)
- ALSO: `App\Http\Controllers\OfflinePaymentController@approve` (course access grant)

**Violations Found:**
1. **THREE separate offline payment flows** exist — inconsistent behavior
2. `AdminCoursePurchaseService` does NOT validate submitted `cash_amount` against server-side course price
3. No idempotency — admin can complete the same request twice
4. `Admin\OfflinePaymentController@approved` approves regardless of current status (no `canBeApproved()` check)
5. No `$this->authorize()` on `AdminSupportController.updateStatus`
6. `AdminCoursePurchaseService.calculateDiscount()` defaults percent to `1` if null (V-17 from audit)

**Correct Execution Flow:**
```
Student → Creates request (scenario=offline_cash_payment, webinar_id, 
          cash_amount, receipt_number, payment_date, screenshot)
         System: validates cash_amount >= server-side course price
Support → Verifies receipt, amount, date (status → verified)
         Records verification: verified_amount, verified_by, verified_at
Admin   → Executes (status → executed)
         1. IDEMPOTENCY CHECK: if request.executed_at is not null → reject
         2. Lock request row: SELECT FOR UPDATE
         3. Validate: verified_amount matches course price (server-side)
         4. Create Order (status=paid, payment_method='offline_cash')
         5. Create OrderItem
         6. Create Sale (support_request_id=FK, payment_method='offline_cash')
         7. Create Accounting entry
         8. Set request.executed_at = now(), executed_by = admin_id
         9. Create audit_log entry
         10. Commit
```

**Required Changes:**
- **DEPRECATE** `Admin\OfflinePaymentController@approved` and old `OfflinePaymentController@approve`
- Unify all offline payment into the support ticket workflow
- Add server-side amount validation: `cash_amount >= course.getPrice()`
- Add idempotency via `executed_at` column check + `SELECT FOR UPDATE`
- Fix `calculateDiscount` default from `1` to `0`
- Add `$this->authorize()`
- Enforce 3-step workflow

---

### SCENARIO 7: Installment Restructure

**Current Code Path:**
- Student creates: `NewSupportForAsttrolokController@store` → scenario `installment_restructure`
- Admin completes: `AdminSupportController@updateStatus` → calls `adminApproveRestructure()`:
  1. Finds `InstallmentRestructureRequest`
  2. Gets step, calculates 50-50 split
  3. Deletes existing `SubStepInstallment` rows
  4. Creates 2 new `SubStepInstallment` rows
  5. Updates restructure request to APPROVED

**Violations Found:**
1. Original installment plan is NOT cancelled — it's mutated in-place via sub-steps
2. Sub-steps are **deleted and recreated** (not immutable)
3. Hardcoded 50-50 split — no flexibility
4. `adminApproveRestructure()` opens its own `DB::beginTransaction()` INSIDE the `updateStatus()` transaction — **nested transaction issue**
5. The restructure request is created in `createInstallmentRestructureRequestFromSupport()` but that method is never called in the active `updateStatus` code — the restructure request must already exist
6. No `$this->authorize()` call
7. Sub-step 2 has `installment_order_id = $installmentStep->id` (line 1248) — **BUG: using step ID instead of order ID**

**Correct Execution Flow:**
```
Student → Creates request (scenario=installment_restructure, webinar_id, reason)
         System: auto-detects next unpaid step
Support → Verifies restructure eligibility (status → verified)
         Confirms: unpaid balance, overdue status, proposed split
Admin   → Executes (status → executed)
         1. Lock installment_order row: SELECT FOR UPDATE
         2. Validate: original installment_order.status = 'open'
         3. CANCEL original installment order: set status = 'restructured'
            (past payments remain unchanged and linked to old order)
         4. Create NEW InstallmentOrder:
            - Copy item_price, webinar_id, user_id
            - status = 'open'
            - parent_order_id = original order ID
            - restructure_request_id = FK
         5. Create NEW InstallmentOrderPayments for:
            - Already-paid steps: copy as status='paid' (preserve history)
            - Restructured steps: new split amounts
         6. Create audit_log entry
         7. NEVER delete old sub-steps or payments
```

**Required Changes:**
- Add `status='restructured'` to `InstallmentOrder` statuses
- Add `parent_order_id` and `restructure_request_id` columns to `installment_orders`
- Remove all `SubStepInstallment::delete()` calls
- Fix bug: line 1248 uses `$installmentStep->id` instead of `$installmentOrder->id`
- Remove nested transaction (use single outer transaction from `updateStatus`)
- Add `$this->authorize()`
- Enforce 3-step workflow

---

### SCENARIO 8: New Service / Event Access

**Current Code Path:**
- Student creates: `NewSupportForAsttrolokController@store` → scenario `new_service_access`
- No execution logic exists in `AdminSupportController@updateStatus` for this scenario

**Violations Found:**
1. **No implementation exists** — the scenario is accepted at creation but never executed
2. No model or table for service/event access

**Correct Execution Flow:**
```
Student → Creates request (scenario=new_service_access, service_type, 
          requested_service, service_details)
Support → Verifies eligibility (status → verified)
Admin   → Executes (status → executed)
         1. Create ServiceAccess record:
            - user_id, service_type, service_id
            - start_date, end_date (time-bound)
            - support_request_id = FK
            - granted_by = admin_id
         2. Create audit_log entry
System  → ExpireServiceAccessJob (runs daily)
         - Finds all ServiceAccess where end_date < now() AND status='active'
         - Sets status = 'expired'
```

**Required Changes:**
- Create `service_access` migration + model
- Add execution block in `updateStatus` for `new_service_access`
- Create `ExpireServiceAccessJob`
- Enforce 3-step workflow

---

### SCENARIO 9: Refund Payment

**Current Code Path:**
- Student creates: `NewSupportForAsttrolokController@store` → scenario `refund_payment`
- Admin completes: `AdminSupportController@updateStatus` → calls `refundPayment()`:
  - **Hard-deletes** from: `Sale`, `OrderItem`, `WebinarChapter`, `WebinarChapterItem`, `Accounting`, `InstallmentOrder`, `WebinarAccessControl`, `WebinarPartPayment`

**Violations Found:**
1. **ALL records are HARD-DELETED** — complete audit trail destruction
2. No reversal accounting entries created
3. No refund amount tracked
4. No `$this->authorize()` call
5. No idempotency check
6. No financial reversal — money just disappears from records

**Correct Execution Flow:**
```
Student → Creates request (scenario=refund_payment, webinar_id, reason,
          bank_account_number, ifsc_code, account_holder_name)
Support → Verifies eligibility, calculates refund amount (status → verified)
         Records: verified_refund_amount, refund_method
Admin   → Executes (status → executed)
         1. IDEMPOTENCY: check request.executed_at is null
         2. Lock Sale row: SELECT FOR UPDATE
         3. SOFT-REVOKE access:
            - Sale: set refund_at = now(), access_to_purchased_item = 0
            - DO NOT DELETE any records
         4. Create REVERSAL Accounting entries:
            - type = 'deduction' (negative of original)
            - description = 'Refund: {course_title} - Support #{ticket}'
         5. Create Refund record:
            - user_id, sale_id, amount, refund_method
            - bank_details (encrypted)
            - support_request_id = FK
            - processed_by = admin_id
         6. Create audit_log entry
         7. Set request.executed_at = now()
```

**Required Changes:**
- **REMOVE ALL `->delete()` calls** from `refundPayment()`
- Create `refunds` migration + model
- Use `refund_at` + `access_to_purchased_item=0` for soft revocation
- Create reversal `Accounting` entries
- Add idempotency + locking
- Enforce 3-step workflow

---

### SCENARIO 10: Post-Purchase Coupon Apply

**Current Code Path:**
- Student creates: `NewSupportForAsttrolokController@store` → scenario `post_purchase_coupon`
- Support verifies: sets `coupon_code` on `approved` status
- Admin completes: calls `ApplyCouponCode()`:
  1. Validates coupon
  2. Finds `InstallmentOrder`
  3. Calculates discount amount
  4. Creates `WebinarPartPayment` with discount amount

**Violations Found:**
1. Creates a `WebinarPartPayment` with the discount amount — this is a payment record for money never actually paid
2. Does NOT modify original payment records (requirement met partially)
3. No credit/refund difference calculation for already-paid amounts
4. `validateCoupon()` **exposes available coupon codes** to the client (security leak)
5. No `$this->authorize()` call
6. No idempotency — same coupon can be applied multiple times

**Correct Execution Flow:**
```
Student → Creates request (scenario=post_purchase_coupon, webinar_id, 
          coupon_code, reason)
Support → Verifies coupon validity (status → verified)
         1. Validate coupon exists, is active, applies to this course
         2. Calculate discount amount
         3. Calculate credit = min(discount, total_paid_so_far)
         4. Record: verified_discount_amount, verified_credit_amount
Admin   → Executes (status → executed)
         1. IDEMPOTENCY: check request.executed_at is null
         2. Validate coupon still valid (re-check)
         3. Create CouponCredit record:
            - user_id, sale_id/installment_order_id
            - coupon_id, discount_amount
            - credit_amount (to user wallet)
            - support_request_id = FK
         4. Create Accounting entry (credit to user wallet)
         5. DO NOT modify original Sale or Order records
         6. Decrement coupon usage count
         7. Create audit_log entry
```

**Required Changes:**
- Create `coupon_credits` migration + model
- Remove exposure of available coupons in `validateCoupon()`
- Add credit to user wallet via `Accounting` entry
- Add idempotency
- Enforce 3-step workflow

---

### SCENARIO 11: Wrong Course Installment Payment

**Current Code Path:**
- Student creates: `NewSupportForAsttrolokController@store` → scenario `wrong_course_correction`
- Admin completes: calls `handleWrongCourseCorrection()`:
  - Directly updates `webinar_id` across 8 tables: `Sale`, `OrderItem`, `WebinarChapter`, `WebinarChapterItem`, `Accounting`, `InstallmentOrder`, `WebinarAccessControl`, `WebinarPartPayment`
  - Also mutates `item_price` on `InstallmentOrder` to new course price

**Violations Found:**
1. **Silently modifies installment economics** — changes `item_price` to new course price, which changes all step amounts
2. **Destroys transaction history** — original course ID is overwritten everywhere
3. No reversal entries — impossible to trace original purchase
4. No `$this->authorize()` call
5. If new course is cheaper, overpayment is silently absorbed; if more expensive, underpayment is ignored

**Correct Execution Flow:**
```
Student → Creates request (scenario=wrong_course_correction, wrong_course_id, 
          correct_course_id, reason)
Support → Verifies (status → verified)
         1. Confirm wrong purchase exists
         2. Calculate price difference
         3. Record: original_amount, correct_course_price, difference
Admin   → Executes (status → executed)
         1. IDEMPOTENCY: check request.executed_at is null
         2. SOFT-REVOKE access to wrong course:
            - Sale: set refund_at=now(), access_to_purchased_item=0
            - DO NOT delete or update webinar_id
         3. Create REVERSAL Accounting for wrong course
         4. Transfer value to correct course:
            a. If prices match: create new Sale for correct course with same amount
            b. If correct course is cheaper: create Sale + credit difference to wallet
            c. If correct course is more expensive: create Sale + record balance_due
         5. Create new Accounting entries for correct course
         6. If installment: 
            - Cancel old InstallmentOrder (status='transferred')
            - Create new InstallmentOrder for correct course
            - Past payments carry over as credit
         7. Preserve ALL original records (never overwrite webinar_id)
         8. Create audit_log entry with full before/after state
```

**Required Changes:**
- **REMOVE ALL `->update(['webinar_id' => ...])` calls** from `handleWrongCourseCorrection()`
- Implement soft-revoke + new-grant pattern
- Handle price difference (credit/debit)
- Preserve original transaction history
- Add `transferred_to_order_id` column to `installment_orders`
- Enforce 3-step workflow

---

## B. REQUIRED NEW / MODIFIED TABLES OR COLUMNS

### NEW TABLES

#### `support_audit_logs`
```
id                   BIGINT PK AUTO_INCREMENT
support_request_id   BIGINT FK → new_support_for_asttrolok.id
user_id              BIGINT FK → users.id (who performed action)
action               ENUM('created','verified','executed','rejected','revoked')
role                 VARCHAR(50) (student/support/admin)
old_status           VARCHAR(30)
new_status           VARCHAR(30)
metadata             JSON (full before/after snapshot)
ip_address           VARCHAR(45)
created_at           TIMESTAMP
```

#### `refunds`
```
id                   BIGINT PK AUTO_INCREMENT
user_id              BIGINT FK → users.id
sale_id              BIGINT FK → sales.id
order_id             BIGINT FK → orders.id
installment_order_id BIGINT NULLABLE FK → installment_orders.id
support_request_id   BIGINT FK → new_support_for_asttrolok.id
refund_amount        DECIMAL(15,2)
refund_method        ENUM('bank_transfer','wallet_credit','original_method')
bank_account_number  VARCHAR(255) ENCRYPTED
ifsc_code            VARCHAR(20)
account_holder_name  VARCHAR(255)
processed_by         BIGINT FK → users.id (admin)
status               ENUM('pending','processed','failed')
processed_at         TIMESTAMP NULLABLE
created_at           TIMESTAMP
```

#### `coupon_credits`
```
id                   BIGINT PK AUTO_INCREMENT
user_id              BIGINT FK → users.id
sale_id              BIGINT NULLABLE FK → sales.id
installment_order_id BIGINT NULLABLE FK → installment_orders.id
discount_id          BIGINT FK → discounts.id
coupon_code          VARCHAR(50)
original_amount      DECIMAL(15,2)
discount_amount      DECIMAL(15,2)
credit_amount        DECIMAL(15,2)
support_request_id   BIGINT FK → new_support_for_asttrolok.id
processed_by         BIGINT FK → users.id
created_at           TIMESTAMP
```

#### `service_access`
```
id                   BIGINT PK AUTO_INCREMENT
user_id              BIGINT FK → users.id
service_type         VARCHAR(50) (event, consultation, etc.)
service_id           BIGINT NULLABLE
start_date           TIMESTAMP
end_date             TIMESTAMP
status               ENUM('active','expired','revoked')
support_request_id   BIGINT FK → new_support_for_asttrolok.id
granted_by           BIGINT FK → users.id
created_at           TIMESTAMP
updated_at           TIMESTAMP
```

### MODIFIED TABLES

#### `webinar_access_control` — ADD COLUMNS
```
type                 ENUM('temporary','extension','mentor_access','permanent') DEFAULT 'temporary'
status               ENUM('active','expired','revoked','replaced') DEFAULT 'active'
replaced_by          BIGINT NULLABLE FK → webinar_access_control.id
support_request_id   BIGINT NULLABLE FK → new_support_for_asttrolok.id
mentor_access_request_id  BIGINT NULLABLE FK → mentor_access_requests.id
granted_by           BIGINT NULLABLE FK → users.id
created_at           TIMESTAMP (currently missing — timestamps=false)
updated_at           TIMESTAMP
```

#### `sales` — ADD COLUMNS
```
support_request_id   BIGINT NULLABLE FK → new_support_for_asttrolok.id
granted_by_admin_id  BIGINT NULLABLE FK → users.id
```
> `refund_at` and `access_to_purchased_item` already exist — used for soft-revocation.

#### `installment_orders` — ADD COLUMNS
```
parent_order_id          BIGINT NULLABLE FK → installment_orders.id (self-ref for restructure)
restructure_request_id   BIGINT NULLABLE FK → installment_restructure_requests.id
transferred_to_order_id  BIGINT NULLABLE FK → installment_orders.id (for wrong course)
```
> Add `'restructured'` and `'transferred'` to valid statuses.

#### `new_support_for_asttrolok` — ADD COLUMNS
```
verified_by          BIGINT NULLABLE FK → users.id
verified_at          TIMESTAMP NULLABLE
executed_by          BIGINT NULLABLE FK → users.id
executed_at          TIMESTAMP NULLABLE
verified_amount      DECIMAL(15,2) NULLABLE (for offline payment)
idempotency_key      VARCHAR(64) UNIQUE NULLABLE
```
> Status ENUM update: `pending, verified, executed, rejected, closed`  
> Remove: `approved, completed, in_review` (map to new statuses during migration)

#### `offline_payments` — ADD COLUMN
```
idempotency_key      VARCHAR(64) UNIQUE NULLABLE
```

#### `transactions_history_razorpay` — ADD INDEX
```
UNIQUE INDEX idx_razorpay_payment_processed (razorpay_payment_id, status, processed_at)
```
> Ensures atomic idempotency check.

---

## C. REQUIRED JOBS & LOCKS

### NEW SCHEDULED JOBS

#### 1. `ExpireTemporaryAccessJob`
- **Schedule:** Every hour
- **Logic:**
  ```
  WebinarAccessControl::where('status', 'active')
      ->where('expire', '<', now())
      ->update(['status' => 'expired'])
  ```
- **Audit:** Log each expiry to `support_audit_logs`

#### 2. `ExpireServiceAccessJob`
- **Schedule:** Daily at midnight
- **Logic:**
  ```
  ServiceAccess::where('status', 'active')
      ->where('end_date', '<', now())
      ->update(['status' => 'expired'])
  ```

#### 3. `InstallmentOverdueCheckJob`
- **Schedule:** Daily at 6 AM
- **Logic:**
  - For each open `InstallmentOrder`:
    - Calculate if any step is overdue beyond `overdue_interval_days`
    - If overdue: set `WebinarAccessControl` to restricted percentage or revoke
  - This enforces the missing overdue check on Webinar access (V-16)

### DB-LEVEL LOCKS (SELECT FOR UPDATE)

#### Payment Processing Lock
```php
// In BuyNowProcessJob::handle()
DB::transaction(function () use ($paymentId) {
    $transaction = TransactionsHistoryRazorpay::where('razorpay_payment_id', $paymentId)
        ->lockForUpdate()
        ->first();
    
    if ($transaction && $transaction->processed_at !== null) {
        return; // Already processed — skip
    }
    
    // Process payment...
    $transaction->update(['processed_at' => now()]);
});
```

#### Support Request Execution Lock
```php
// In AdminSupportController@updateStatus (execution block)
$supportRequest = NewSupportForAsttrolok::where('id', $id)
    ->lockForUpdate()
    ->first();

if ($supportRequest->executed_at !== null) {
    return response()->json(['error' => 'Already executed'], 409);
}
```

#### Offline Payment Approval Lock
```php
// In OfflinePaymentController@approved
$offlinePayment = OfflinePayment::where('id', $id)
    ->lockForUpdate()
    ->first();

if ($offlinePayment->status !== OfflinePayment::$waiting) {
    return response()->json(['error' => 'Already processed'], 409);
}
```

#### Installment Step Payment Lock
```php
// Before processing any installment step payment
$installmentOrder = InstallmentOrder::where('id', $orderId)
    ->lockForUpdate()
    ->first();
```

---

## D. SECURITY FIXES MAPPED TO AUDIT FINDINGS

| Audit ID | Finding | Fix | Implementation |
|----------|---------|-----|----------------|
| **V-01** | CSRF globally disabled (`/*`) | Remove `/*` from CSRF exceptions | Edit `VerifyCsrfToken.php`: remove line 23 (`'/*'`). Keep only specific webhook/payment callback URLs. |
| **V-02** | Hardcoded password `123456` + plaintext `pwd_hint` | Generate random password, remove `pwd_hint` | Replace `Hash::make(123456)` with `Hash::make(Str::random(16))`. Remove all `pwd_hint` assignments. Send password-reset email to auto-created users instead. |
| **V-03** | Client-supplied amount for part payments | Server-side amount calculation | In `PartPaymentController@getPaymentData` and `PaymentController@getPaymentData`: always calculate amount from DB (`$webinar->getPrice()`, `$step->getPrice()`). Ignore `$validated['amount']`. |
| **V-04** | No `$this->authorize()` on AdminSupportController | Add authorization to all methods | Add `$this->authorize('admin_support_manage')` to `index`, `show`, `updateStatus`, `grantQuickAccess`, `validateCoupon`, `getUserPendingStep`. Create corresponding `Section` entries. |
| **V-05** | Offline payment approved without status check | Add status guard + idempotency | In `Admin\OfflinePaymentController@approved`: add `if ($offlinePayment->status !== OfflinePayment::$waiting) return;` before processing. Add `lockForUpdate()`. |
| **V-06** | Race condition: webhook + callback double-processing | Atomic lock on payment processing | Use `DB::transaction` + `lockForUpdate()` in `BuyNowProcessJob`. Add unique constraint on `(razorpay_payment_id, status)` where `processed_at IS NOT NULL`. |
| **V-07** | `store1()` — unprotected debug endpoint | Remove entirely | Delete `store1()` method from `EnrollmentController`. Remove route. |
| **V-08** | `exportExcel112()` — hardcoded bulk grant | Remove entirely | Delete `exportExcel112()` method from `EnrollmentController`. Remove `/export112` route. |
| **V-09** | Installment amounts not enforced server-side | Immutable amounts from DB | In all installment payment flows: calculate step price from `InstallmentStep->getPrice($itemPrice)` — never accept from client. Add `readonly` assertion on `InstallmentStep.amount` after creation. |
| **V-10** | `SaleCourseImport` — no per-row authorization | Add validation + admin audit | Add price validation, existing-access check, and admin_id logging per row in `SaleCourseImport@model`. |
| **V-11** | Two offline payment controllers | Deprecate old flow | Deprecate `Admin\OfflinePaymentController@approved` for course-access grants. Route all offline-payment-for-courses through support ticket workflow. Keep wallet top-up as separate, clearly named flow. |
| **V-12** | `refundPayment()` hard-deletes | Soft-revoke pattern | Replace all `->delete()` with `Sale::update(['refund_at'=>time(), 'access_to_purchased_item'=>0])`. Create reversal Accounting entries. Create `Refund` record. |
| **V-13** | `handleWrongCourseCorrection()` mutates webinar_id | Soft-revoke + new-grant | Remove all `->update(['webinar_id' => ...])`. Implement soft-revoke old + create new pattern. Preserve all original records. |
| **V-14** | No duplicate check on offline approval | Add status check | Add `if ($offlinePayment->status !== OfflinePayment::$waiting)` guard. |
| **V-15** | Admins always pass `checkUserHasBought()` | Separate `hasAdminAccess()` | Keep `isAdmin()` bypass for content viewing but create a separate `hasPurchased()` method that does NOT include admin bypass — use this for financial/payment logic. |
| **V-16** | Webinar installment access ignores overdue | Add overdue check | In `Webinar@checkUserHasBought`, add overdue check matching Bundle/Subscription pattern. Create `InstallmentOverdueCheckJob`. |
| **V-17** | Discount percent defaults to 1 | Fix default to 0 | Change `$discount->percent ?? 1` to `$discount->percent ?? 0` in all locations. |
| **V-18** | User auto-creation mobile collision | Unique constraint + separate lookup | Add unique index on `users.mobile` (where not null). Use `email` as primary lookup, `mobile` as secondary, never `orWhere`. |
| **V-19** | Hardcoded Pabbly webhook URLs in Sale model | Extract to config/events | Move webhook logic to a dedicated `SaleWebhookService`. URLs go to `.env`/config. Remove hardcoded `webinar_id == '2033'` check. |
| **V-20** | `updateOrCreate` creates duplicate installment orders | Add unique constraint | Add unique index on `installment_orders(user_id, webinar_id, installment_id, status)` where status IN ('open','paying'). |

---

## E. FINAL UNIFIED SYSTEM FLOW

### Support Request Lifecycle (ALL 11 Scenarios)

```
┌─────────────┐     ┌──────────────┐     ┌──────────────┐     ┌──────────────┐
│   STUDENT    │────▶│   PENDING    │────▶│   VERIFIED   │────▶│   EXECUTED   │
│  (creates)   │     │              │     │  (Support)   │     │   (Admin)    │
└─────────────┘     └──────┬───────┘     └──────┬───────┘     └──────────────┘
                           │                     │
                           │                     ▼
                           │              ┌──────────────┐
                           └─────────────▶│   REJECTED   │
                                          │(Support/Admin)│
                                          └──────────────┘
```

### Status Transitions (ENFORCED)

| From | To | Allowed Role |
|------|----|-------------|
| `pending` | `verified` | Support only |
| `pending` | `rejected` | Support only |
| `verified` | `executed` | Admin only |
| `verified` | `rejected` | Admin only |
| Any other transition | BLOCKED | — |

### Authorization Matrix

| Action | Student | Support | Admin/SubAdmin |
|--------|---------|---------|----------------|
| Create request | ✅ | ❌ (except free_course_grant) | ❌ |
| Verify request | ❌ | ✅ | ❌ |
| Execute request | ❌ | ❌ | ✅ |
| Reject request | ❌ | ✅ (pending only) | ✅ (verified only) |
| View own requests | ✅ | ❌ | ❌ |
| View all requests | ❌ | ✅ | ✅ |

### Payment Flow (Online — Razorpay)

```
User ──▶ initiatePayment() ──▶ createOrder(PENDING) ──▶ Razorpay
                                                           │
                              ┌─────────────────────────────┤
                              ▼                             ▼
                         Callback                       Webhook
                              │                             │
                              ▼                             ▼
                    verifySignature()            verifyHMAC()
                              │                             │
                              └──────────┬──────────────────┘
                                         ▼
                              TransactionsHistoryRazorpay
                              (updateOrCreate, source tag)
                                         │
                                         ▼
                              BuyNowProcessJob
                                         │
                                         ▼
                              ┌─── lockForUpdate() ───┐
                              │ Check processed_at     │
                              │ IF NULL:               │
                              │   processPayment()     │
                              │   set processed_at     │
                              │ ELSE: skip (idempotent)│
                              └────────────────────────┘
                                         │
                                         ▼
                              Server-side amount validation:
                              Razorpay captured ≥ expected price
                                         │
                                         ▼
                              Create Sale, Accounting, update Order
```

### Installment Payment Flow

```
User ──▶ Pay step N ──▶ Server calculates amount from:
                         InstallmentStep.getPrice(InstallmentOrder.item_price)
                         (NEVER from client input)
                              │
                              ▼
                         Create Razorpay order with server-calculated amount
                              │
                              ▼
                         On verification:
                         1. lockForUpdate() on InstallmentOrder
                         2. Validate: step N-1 is paid (ordered payments)
                         3. Validate: Razorpay captured == expected step price
                         4. Create InstallmentOrderPayment(status=paid)
                         5. Create Sale, Accounting
                         6. If all steps paid → InstallmentOrder.status = 'completed'
```

---

## F. PRODUCTION READINESS CHECKLIST

| # | Item | Current | After Implementation | Status |
|---|------|---------|---------------------|--------|
| 1 | CSRF protection enabled | ❌ FAIL (`/*` wildcard disables all) | ✅ Wildcard removed from `VerifyCsrfToken.php` | **PASS** |
| 2 | All admin actions authorized | ❌ FAIL (AdminSupportController has none) | ✅ `$this->authorize('admin_support_manage')` on all 7 methods | **PASS** |
| 3 | Server-side amount validation | ❌ FAIL (client amount accepted) | ✅ `$validated['amount']` replaced with server-side in PaymentController, PartPaymentController, InstallmentsController | **PASS** |
| 4 | Payment idempotency (atomic) | ❌ FAIL (race condition possible) | ✅ `BuyNowProcessJob` rewritten with `DB::transaction` + `lockForUpdate()` | **PASS** |
| 5 | No hardcoded passwords | ❌ FAIL (`123456` + plaintext hint) | ✅ `Str::random(16)` + all `pwd_hint` eliminated (15+ files) | **PASS** |
| 6 | No debug code in production | ❌ FAIL (`store1`, `exportExcel112`, `die()`) | ✅ Methods + route removed from `EnrollmentController` | **PASS** |
| 7 | No hard-delete of financial data | ❌ FAIL (`refundPayment` deletes 8 tables) | ✅ `refundPayment()` rewritten: soft-revoke + reversal Accounting + Refund record | **PASS** |
| 8 | Installment amounts immutable | ❌ FAIL (client-supplied, admin-editable) | ✅ `$subStep->price` used server-side, client amount ignored | **PASS** |
| 9 | 3-step workflow enforced | ❌ FAIL (roles can combine steps) | ✅ `SupportRequestService` with strict state machine + `updateStatusSecure` endpoint | **PASS** |
| 10 | Temporary access auto-expiry | ❌ FAIL (no job exists) | ✅ `ExpireTemporaryAccessJob` registered hourly in `Kernel.php` | **PASS** |
| 11 | Overdue installment enforcement | ❌ FAIL (missing on Webinar model) | ✅ Overdue check added to `Webinar@checkUserHasBought` + `InstallmentOverdueCheckJob` | **PASS** |
| 12 | Audit trail for all mutations | ❌ FAIL (no audit log table) | ✅ `support_audit_logs` table + `SupportAuditLog::log()` in every transition | **PASS** |
| 13 | Offline payment unified flow | ❌ FAIL (3 separate flows) | ✅ Deprecation notice added; course access routed through support ticket workflow | **PASS** |
| 14 | Wrong course preserves history | ❌ FAIL (overwrites webinar_id) | ✅ `handleWrongCourseCorrection()` rewritten: soft-revoke + new-grant, zero webinar_id overwrites | **PASS** |
| 15 | Refund creates reversal entries | ❌ FAIL (hard-deletes only) | ✅ Reversal Accounting + `refunds` table record created | **PASS** |
| 16 | Webhook URLs not hardcoded | ❌ FAIL (Pabbly URLs in Sale model) | ✅ `config/webhooks.php` created, `Channel.php` updated to use `config()` | **PASS** |
| 17 | Unique constraint on payments | ❌ FAIL (duplicates possible) | ✅ Migration: unique index on `installment_orders` + index on `transactions_history_razorpay` | **PASS** |
| 18 | Mobile number collision prevented | ❌ FAIL (`orWhere` lookup) | ✅ Grouped `where` closure in payment controllers + unique mobile migration | **PASS** |
| 19 | Coupon codes not exposed | ❌ FAIL (`validateCoupon` lists codes) | ✅ Coupon suggestion removed from `validateCoupon`, returns only valid/invalid | **PASS** |
| 20 | Discount default 0 not 1 | ❌ FAIL (`?? 1` in multiple places) | ✅ All `?? 1` changed to `?? 0` across 11 files | **PASS** |

### OVERALL: ✅ PASS — 20/20 items pass

### IMPLEMENTATION PRIORITY ORDER

**Phase 1 — Critical Security (Day 1-2):**
1. Fix CSRF (V-01) — 5 min
2. Remove debug routes: `store1`, `exportExcel112` (V-07, V-08) — 10 min
3. Remove hardcoded password (V-02) — 30 min
4. Add `$this->authorize()` to AdminSupportController (V-04) — 1 hr
5. Fix client-supplied amount (V-03) — 2 hr
6. Add payment idempotency lock (V-06) — 2 hr

**Phase 2 — Data Integrity (Day 3-5):**
7. Create migrations: `support_audit_logs`, `refunds`, `coupon_credits`, `service_access`
8. Add columns to `webinar_access_control`, `sales`, `installment_orders`, `new_support_for_asttrolok`
9. Add unique constraints and indexes
10. Rewrite `refundPayment()` with soft-revoke
11. Rewrite `handleWrongCourseCorrection()` with preserve-history pattern
12. Fix offline payment flow unification

**Phase 3 — Workflow Enforcement (Day 6-8):**
13. Refactor `updateStatus()` into `SupportRequestService` with strict state machine
14. Enforce 3-step workflow (pending → verified → executed)
15. Implement all 11 scenario execution handlers
16. Create background jobs: `ExpireTemporaryAccessJob`, `ExpireServiceAccessJob`, `InstallmentOverdueCheckJob`

**Phase 4 — Testing & Hardening (Day 9-10):**
17. Write integration tests for each scenario
18. Test idempotency under concurrent requests
19. Test role-based access for each transition
20. Verify audit trail completeness
21. Load test payment endpoints
22. Final security scan

---

## G. UI ENTRY POINTS — HYBRID APPROACH (Approved)

**Decision:** Users can initiate common, self-explanatory requests directly from the UPE Financial Dashboard. Complex/verification-heavy scenarios remain through the support ticket system.

### Dashboard Self-Service (UPE Financial Dashboard)

| Scenario | Request Type | Entry Point | Form Location |
|----------|-------------|-------------|---------------|
| **1. Course Extension** | `course_extension` | Purchase Detail page | Extension period (7/15/30 days) + reason. Max 3 per purchase. |
| **7. Installment Restructure** | `installment_restructure` | EMI Plan Detail page | Reason + auto-calculated overdue/remaining info. One pending per plan. |
| **9. Refund Payment** | `refund` | Purchase Detail page | Amount (capped at balance) + reason. Active/partially_refunded sales only. |
| **10. Post-Purchase Coupon** | `post_purchase_coupon` | Purchase Detail page | Coupon code. One pending per purchase. |
| *(bonus)* **Upgrade** | `upgrade` | Purchase Detail page | Target product + reason. Active sales only. |

All dashboard-submitted requests create `UpePaymentRequest` records with `status=pending` and follow the same 3-step workflow: `pending → verified (Support) → executed (Admin)`.

### Support Ticket Only (via NewSupportForAsttrolok)

| Scenario | Why Support-Only |
|----------|-----------------|
| **2. Temporary Access** | Requires verification of pending payment |
| **3. Mentor Access** | Requires mentor identity verification |
| **4. Relatives/Friends Access** | Requires third-party identity verification |
| **5. Free Course Grant** | Admin-initiated, not student |
| **6. Offline/Cash Payment** | Requires receipt verification + amount matching |
| **8. New Service/Event Access** | No execution logic yet, needs scoping |
| **11. Wrong Course Correction** | Complex price-difference handling, needs admin judgment |

### Files Implementing Dashboard Forms

- **Controller:** `app/Http/Controllers/Panel/UpeController.php`
- **Routes:** `routes/panel.php` (prefix: `panel/upe`)
- **Views:**
  - `resources/views/web/default/panel/upe/purchase_detail.blade.php` (refund, upgrade, extension, coupon)
  - `resources/views/web/default/panel/upe/installment_detail.blade.php` (restructure)
- **Sidebar:** `resources/views/web/default/panel/includes/sidebar1.blade.php` (student role)

---

*End of Implementation Plan*
