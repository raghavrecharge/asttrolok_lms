# Asttrolok Unified Payment Engine — Architecture

## Status: DESIGN PHASE
## Author: System Architect
## Date: 2026-02-10

---

## 1. HIGH-LEVEL ARCHITECTURE

```
┌─────────────────────────────────────────────────────────────────────┐
│                        API / CONTROLLER LAYER                       │
│  PurchaseController · RefundController · SubscriptionController     │
│  AdjustmentController · InstallmentController · AdminController     │
└──────────────────────────────┬──────────────────────────────────────┘
                               │
┌──────────────────────────────▼──────────────────────────────────────┐
│                    REQUEST / APPROVAL WORKFLOW                       │
│  PaymentRequest → verified → approved → executed                    │
│  (All financial mutations must pass through this gate)              │
└──────────────────────────────┬──────────────────────────────────────┘
                               │
┌──────────────────────────────▼──────────────────────────────────────┐
│                        SERVICE LAYER (ENGINES)                      │
│                                                                     │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────────────────┐  │
│  │ PurchaseEngine│  │ RefundEngine │  │ AdjustmentEngine         │  │
│  └──────┬───────┘  └──────┬───────┘  └──────────┬───────────────┘  │
│         │                 │                      │                  │
│  ┌──────▼───────┐  ┌──────▼───────┐  ┌──────────▼───────────────┐  │
│  │DiscountEngine│  │InstallmentEng│  │ SubscriptionEngine       │  │
│  └──────┬───────┘  └──────┬───────┘  └──────────┬───────────────┘  │
│         │                 │                      │                  │
│  ┌──────▼───────┐  ┌──────▼───────┐  ┌──────────▼───────────────┐  │
│  │ReferralEngine│  │ AccessEngine │  │ PaymentGatewayRouter     │  │
│  └──────────────┘  └──────────────┘  └──────────────────────────┘  │
└──────────────────────────────┬──────────────────────────────────────┘
                               │
┌──────────────────────────────▼──────────────────────────────────────┐
│                      IMMUTABLE LEDGER LAYER                         │
│                                                                     │
│  ┌─────────────────────────────────────────────────────────────┐   │
│  │              PaymentLedgerService                            │   │
│  │  append(sale_id, type, amount, method, metadata)            │   │
│  │  balance(sale_id) → net amount paid                         │   │
│  │  entries(sale_id) → full history                             │   │
│  │                                                              │   │
│  │  Types: PAYMENT | REFUND | DISCOUNT | ADJUSTMENT_IN |       │   │
│  │         ADJUSTMENT_OUT | REFERRAL_BONUS | INSTALLMENT_PAY | │   │
│  │         SUBSCRIPTION_CHARGE | PENALTY | WRITE_OFF           │   │
│  └─────────────────────────────────────────────────────────────┘   │
└──────────────────────────────┬──────────────────────────────────────┘
                               │
┌──────────────────────────────▼──────────────────────────────────────┐
│                         DATA LAYER                                  │
│  upe_products · upe_sales · upe_ledger_entries · upe_discounts     │
│  upe_installment_plans · upe_installment_schedules                 │
│  upe_subscriptions · upe_subscription_cycles                       │
│  upe_adjustments · upe_referrals · upe_payment_requests            │
│  upe_audit_log                                                     │
└─────────────────────────────────────────────────────────────────────┘
```

**Table prefix:** All new tables use `upe_` (Unified Payment Engine) to avoid collision with legacy tables.

**Coexistence strategy:** The new engine runs alongside existing `sales`, `orders`, `accounting` tables. Legacy code continues to work. New purchases route through the engine. Migration is incremental.

---

## 2. CORE DATA MODELS

### 2.1 upe_products (Product Catalog)

Wraps existing `webinars` table and extends to all product types.

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT PK | |
| product_type | ENUM | `course_live`, `course_video`, `webinar`, `subscription`, `event`, `bundle` |
| external_id | INT | FK to `webinars.id`, `bundles.id`, etc. |
| base_fee | DECIMAL(15,2) | **Immutable** base price at creation |
| currency | CHAR(3) | INR, USD, etc. |
| validity_days | INT NULL | Days of access after purchase |
| is_upgradeable | BOOL | Can be upgraded to another product |
| upgrade_policy_id | BIGINT NULL | FK to upgrade policy |
| adjustment_eligible | BOOL | Can payment be reused |
| adjustment_max_percent | DECIMAL(5,2) | Max % of payment transferable |
| status | ENUM | `active`, `archived`, `draft` |
| metadata | JSON NULL | Flexible product-specific config |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

**Immutability rule:** `base_fee` is set at creation and NEVER updated. Price changes create a new product version or use discount engine.

### 2.2 upe_sales (Commercial Contract)

The central entity. Every financial interaction starts with a Sale.

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT PK | |
| uuid | CHAR(36) UNIQUE | Idempotency key |
| user_id | BIGINT | Buyer |
| product_id | BIGINT | FK to upe_products |
| sale_type | ENUM | `paid`, `free`, `trial`, `referral`, `upgrade`, `adjustment` |
| pricing_mode | ENUM | `full`, `installment`, `subscription`, `free` |
| base_fee_snapshot | DECIMAL(15,2) | Product base_fee at time of sale (immutable copy) |
| currency | CHAR(3) | |
| status | ENUM | `pending_payment`, `active`, `completed`, `refunded`, `partially_refunded`, `expired`, `cancelled`, `suspended` |
| valid_from | TIMESTAMP NULL | Access start |
| valid_until | TIMESTAMP NULL | Access end (NULL = lifetime) |
| parent_sale_id | BIGINT NULL | For upgrades/adjustments: points to original sale |
| referral_id | BIGINT NULL | FK to upe_referrals |
| support_request_id | BIGINT NULL | FK to support ticket that created this |
| approved_by | BIGINT NULL | Admin who approved |
| executed_at | TIMESTAMP NULL | Idempotency: when system executed |
| metadata | JSON NULL | |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

**Lifecycle:**
```
pending_payment → active → completed (validity expired, access ended normally)
pending_payment → cancelled (payment timeout / user cancel)
active → refunded (full refund)
active → partially_refunded (partial refund, access may continue)
active → suspended (subscription failure, installment overdue)
active → expired (validity_until passed)
```

### 2.3 upe_ledger_entries (Immutable Payment Ledger)

**THE CORE.** Every money movement is a row here. Append-only. No UPDATE. No DELETE.

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT PK | |
| uuid | CHAR(36) UNIQUE | Idempotency key |
| sale_id | BIGINT | FK to upe_sales |
| entry_type | ENUM | See below |
| direction | ENUM | `credit` (money in), `debit` (money out) |
| amount | DECIMAL(15,2) | Always positive. Direction determines sign. |
| currency | CHAR(3) | |
| payment_method | ENUM NULL | `cash`, `bank_transfer`, `razorpay`, `paypal`, `stripe`, `payment_link`, `wallet`, `system` |
| gateway_transaction_id | VARCHAR(255) NULL | External payment reference |
| gateway_response | JSON NULL | Raw gateway response (for audit) |
| reference_type | VARCHAR(50) NULL | Polymorphic: `discount`, `installment_schedule`, `adjustment`, `refund_request`, etc. |
| reference_id | BIGINT NULL | FK to the referenced entity |
| description | TEXT NULL | Human-readable |
| processed_by | BIGINT NULL | Admin/system user who triggered |
| idempotency_key | VARCHAR(255) UNIQUE NULL | Prevents duplicate entries |
| created_at | TIMESTAMP | **Immutable** — no updated_at |

**Entry types:**
| Type | Direction | Description |
|------|-----------|-------------|
| `payment` | credit | Money received from user |
| `refund` | debit | Money returned to user |
| `discount` | credit | Virtual reduction (no real money) |
| `adjustment_in` | credit | Payment transferred FROM another sale |
| `adjustment_out` | debit | Payment transferred TO another sale |
| `referral_bonus` | credit | Bonus credited |
| `installment_payment` | credit | Installment EMI received |
| `subscription_charge` | credit | Recurring billing charge |
| `penalty` | credit | Late fee or penalty |
| `write_off` | debit | Admin write-off / forgiveness |

**Balance calculation:**
```sql
SELECT 
  SUM(CASE WHEN direction = 'credit' THEN amount ELSE 0 END) -
  SUM(CASE WHEN direction = 'debit' THEN amount ELSE 0 END)
  AS net_paid
FROM upe_ledger_entries
WHERE sale_id = ?
```

### 2.4 upe_discounts (Discount Engine)

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT PK | |
| code | VARCHAR(64) UNIQUE NULL | Coupon code (NULL for auto-applied) |
| discount_type | ENUM | `percentage`, `fixed` |
| value | DECIMAL(15,2) | Percent (0-100) or fixed amount |
| max_discount_amount | DECIMAL(15,2) NULL | Cap for percentage discounts |
| min_order_amount | DECIMAL(15,2) NULL | Minimum purchase for eligibility |
| scope | ENUM | `global`, `product`, `category`, `user` |
| scope_ids | JSON NULL | Array of product/category/user IDs |
| allowed_roles | JSON NULL | Which roles can create/apply this |
| max_uses_total | INT NULL | Total redemptions allowed |
| max_uses_per_user | INT NULL | Per-user cap |
| used_count | INT DEFAULT 0 | |
| stackable | BOOL DEFAULT FALSE | Can combine with other discounts |
| valid_from | TIMESTAMP NULL | |
| valid_until | TIMESTAMP NULL | |
| created_by | BIGINT | Admin who created |
| status | ENUM | `active`, `expired`, `disabled` |
| created_at | TIMESTAMP | |

**Rules enforced in DiscountEngine:**
- Percentage discounts capped by `max_discount_amount`
- Role-based `allowed_roles` checked at application time
- `stackable = false` means only ONE non-stackable discount per sale
- Time-bound discounts auto-expire (checked at application, not cron)
- Discount creates a `discount` ledger entry, never mutates price

### 2.5 upe_installment_plans (Installment Engine)

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT PK | |
| sale_id | BIGINT | FK to upe_sales |
| total_amount | DECIMAL(15,2) | Total to be paid via installments |
| num_installments | INT | Number of EMIs |
| plan_type | ENUM | `standard`, `flexible` |
| status | ENUM | `active`, `completed`, `defaulted`, `restructured` |
| restructured_from_id | BIGINT NULL | If this plan replaced another |
| approved_by | BIGINT NULL | For flexible plans |
| created_at | TIMESTAMP | |

### 2.6 upe_installment_schedules (Individual EMIs)

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT PK | |
| plan_id | BIGINT | FK to upe_installment_plans |
| sequence | INT | 1, 2, 3... |
| amount_due | DECIMAL(15,2) | |
| amount_paid | DECIMAL(15,2) DEFAULT 0 | Accumulated from ledger |
| due_date | DATE | |
| status | ENUM | `upcoming`, `due`, `paid`, `partial`, `overdue`, `waived` |
| paid_at | TIMESTAMP NULL | |
| ledger_entry_id | BIGINT NULL | FK to the ledger entry that completed it |
| created_at | TIMESTAMP | |

**Rules:**
- Cannot skip installments (must pay in sequence)
- Partial payments map to current installment bucket
- Restructuring: old plan → `restructured`, new plan created with `restructured_from_id`

### 2.7 upe_subscriptions (Subscription Engine)

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT PK | |
| sale_id | BIGINT | FK to upe_sales |
| user_id | BIGINT | |
| product_id | BIGINT | |
| billing_amount | DECIMAL(15,2) | Monthly charge |
| billing_interval | ENUM | `monthly`, `quarterly`, `yearly` |
| trial_ends_at | TIMESTAMP NULL | First month free |
| current_period_start | TIMESTAMP | |
| current_period_end | TIMESTAMP | |
| grace_period_days | INT DEFAULT 3 | |
| status | ENUM | `trial`, `active`, `past_due`, `grace`, `cancelled`, `expired` |
| cancelled_at | TIMESTAMP NULL | |
| gateway_subscription_id | VARCHAR(255) NULL | External billing reference |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

### 2.8 upe_subscription_cycles (Billing History)

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT PK | |
| subscription_id | BIGINT | |
| cycle_number | INT | 1, 2, 3... |
| period_start | TIMESTAMP | |
| period_end | TIMESTAMP | |
| amount | DECIMAL(15,2) | |
| status | ENUM | `pending`, `paid`, `failed`, `waived` |
| ledger_entry_id | BIGINT NULL | |
| attempts | INT DEFAULT 0 | Payment retry count |
| last_attempt_at | TIMESTAMP NULL | |
| created_at | TIMESTAMP | |

### 2.9 upe_adjustments (Adjustment Engine)

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT PK | |
| source_sale_id | BIGINT | Original sale being adjusted FROM |
| target_sale_id | BIGINT | New sale being adjusted TO |
| adjustment_type | ENUM | `upgrade`, `cross_course`, `wrong_course` |
| source_amount | DECIMAL(15,2) | Amount taken from source |
| target_amount | DECIMAL(15,2) | Amount applied to target |
| adjustment_percent | DECIMAL(5,2) | Policy: what % was transferred |
| policy_snapshot | JSON | Policy rules at time of adjustment |
| source_ledger_entry_id | BIGINT | Debit entry on source |
| target_ledger_entry_id | BIGINT | Credit entry on target |
| approved_by | BIGINT | |
| created_at | TIMESTAMP | |

**Rules:**
- Original payment NEVER touched
- `adjustment_out` ledger entry on source sale (debit)
- `adjustment_in` ledger entry on target sale (credit)
- Configurable % (e.g., 80% of original payment transferable)

### 2.10 upe_referrals (Referral Engine)

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT PK | |
| referrer_user_id | BIGINT | Student who shared |
| referral_code | VARCHAR(32) UNIQUE | |
| referred_user_id | BIGINT NULL | Who signed up |
| referred_sale_id | BIGINT NULL | The purchase made |
| bonus_type | ENUM | `wallet_credit`, `discount_credit` |
| bonus_amount | DECIMAL(15,2) | |
| bonus_status | ENUM | `pending`, `credited`, `expired`, `ineligible` |
| bonus_ledger_entry_id | BIGINT NULL | When credited |
| credited_at | TIMESTAMP NULL | |
| created_at | TIMESTAMP | |

**Rules:**
- Bonus credited ONLY after successful payment (not at sale creation)
- Default: wallet credit, not cash
- Self-referral blocked

### 2.11 upe_payment_requests (Request/Approval Workflow)

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT PK | |
| uuid | CHAR(36) UNIQUE | |
| request_type | ENUM | `offline_payment`, `refund`, `upgrade`, `adjustment`, `restructure`, `manual_discount`, `subscription_cancel` |
| user_id | BIGINT | Requester |
| sale_id | BIGINT NULL | Related sale |
| payload | JSON | Request-specific data |
| status | ENUM | `pending`, `verified`, `approved`, `executed`, `rejected` |
| verified_by | BIGINT NULL | Support |
| verified_at | TIMESTAMP NULL | |
| approved_by | BIGINT NULL | Admin |
| approved_at | TIMESTAMP NULL | |
| executed_at | TIMESTAMP NULL | Idempotency |
| execution_result | JSON NULL | |
| rejected_reason | TEXT NULL | |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

### 2.12 upe_audit_log (Audit Trail)

| Column | Type | Description |
|--------|------|-------------|
| id | BIGINT PK | |
| actor_id | BIGINT | Who did it |
| actor_role | VARCHAR(50) | |
| action | VARCHAR(100) | `sale.created`, `ledger.payment`, `refund.approved`, etc. |
| entity_type | VARCHAR(50) | `sale`, `ledger_entry`, `discount`, etc. |
| entity_id | BIGINT | |
| old_state | JSON NULL | Before |
| new_state | JSON NULL | After |
| ip_address | VARCHAR(45) NULL | |
| user_agent | TEXT NULL | |
| created_at | TIMESTAMP | |

---

## 3. SERVICE BOUNDARIES

### 3.1 PaymentLedgerService
- `append(sale_id, type, direction, amount, method, reference, ...)` → LedgerEntry
- `balance(sale_id)` → decimal (net paid)
- `entries(sale_id)` → Collection
- `hasPayment(sale_id)` → bool
- **NEVER exposes update/delete**

### 3.2 PurchaseEngine
- `createSale(user, product, pricingMode, discountCode?, referralCode?)` → Sale
- `processPayment(sale, method, gatewayData)` → LedgerEntry
- `completeSale(sale)` → void
- Orchestrates: DiscountEngine → LedgerService → AccessEngine

### 3.3 DiscountEngine
- `validate(code, user, product)` → DiscountResult
- `apply(sale, discount)` → LedgerEntry (type=discount)
- `checkStacking(sale, newDiscount)` → bool
- `checkRoleCaps(user, discount)` → bool
- Auto-expiry on `valid_until` check

### 3.4 RefundEngine
- `calculateRefund(sale, policy)` → RefundEstimate
- `processRefund(sale, amount, reason)` → LedgerEntry (type=refund)
- Triggers AccessEngine.revokeOrDowngrade(sale)
- Partial refunds: amount < balance → `partially_refunded`

### 3.5 InstallmentEngine
- `createPlan(sale, numInstallments, schedule)` → InstallmentPlan
- `recordPayment(schedule, amount, method)` → LedgerEntry
- `getNextDue(plan)` → InstallmentSchedule
- `restructure(oldPlan, newSchedule, approvedBy)` → new Plan (old → restructured)
- No skip rule enforced in `recordPayment`

### 3.6 SubscriptionEngine
- `create(user, product, trialDays?)` → Subscription + Sale
- `charge(subscription)` → LedgerEntry | FailureResult
- `enterGrace(subscription)` → void
- `cancel(subscription)` → void
- `revoke(subscription)` → triggers AccessEngine
- Billing job runs daily

### 3.7 AdjustmentEngine
- `calculate(sourceSale, targetProduct, policy)` → AdjustmentEstimate
- `execute(sourceSale, targetSale, amount, approvedBy)` → Adjustment + 2 LedgerEntries
- Never modifies source sale's ledger history
- Creates `adjustment_out` on source, `adjustment_in` on target

### 3.8 ReferralEngine
- `generateLink(user)` → referral code
- `trackSignup(referralCode, newUser)` → Referral
- `creditBonus(referral)` → LedgerEntry (only after payment confirmed)
- Self-referral prevention

### 3.9 AccessEngine (Derived Access)
- `hasAccess(user, product)` → AccessResult
- `getAccessType(user, product)` → `paid | free | temporary | subscription | none`
- **NEVER stores access as truth.** Computes from:
  1. Sale exists for (user, product)?
  2. Sale.status in (`active`, `partially_refunded`)?
  3. Ledger balance ≥ threshold (for installments)?
  4. Subscription active / in trial / in grace?
  5. Sale.valid_until not passed?
- Cache layer with invalidation on ledger/sale changes

### 3.10 PaymentGatewayRouter
- `route(method)` → GatewayDriver
- Drivers: Razorpay, PayPal, Stripe, BankTransfer, Cash, PaymentLink
- Each driver: `initiate(amount, metadata)`, `verify(transactionId)`, `webhook(payload)`
- Webhook handler creates ledger entries

---

## 4. FLOW DIAGRAMS

### 4.1 Purchase Flow (Full Payment)

```
User selects product
  │
  ▼
PurchaseEngine.createSale(user, product, 'full')
  │ → Creates upe_sales (status=pending_payment)
  │ → Snapshots base_fee into sale
  │
  ▼
DiscountEngine.apply(sale, couponCode?)  [optional]
  │ → Validates coupon (expiry, scope, caps, role, stacking)
  │ → Creates ledger entry (type=discount, direction=credit)
  │ → Returns effective_price = base_fee - discount
  │
  ▼
ReferralEngine.trackSignup(referralCode?)  [optional]
  │ → Links referral to sale (bonus_status=pending)
  │
  ▼
PaymentGatewayRouter.route(method).initiate(effective_price)
  │ → Returns payment_url / QR / instructions
  │
  ▼
[User pays externally]
  │
  ▼
Webhook / Admin confirmation
  │
  ▼
PurchaseEngine.processPayment(sale, method, gatewayData)
  │ → Idempotency check: if ledger entry with same gateway_txn exists, skip
  │ → PaymentLedgerService.append(sale, 'payment', 'credit', amount, method)
  │ → Sale.status → 'active'
  │ → Sale.valid_from = now()
  │ → Sale.valid_until = now() + product.validity_days
  │
  ▼
ReferralEngine.creditBonus(referral)
  │ → Only now: creates ledger entry for referrer
  │ → referral.bonus_status → 'credited'
  │
  ▼
AccessEngine cache invalidated
  │ → User now has access (derived from active sale + positive balance)
```

### 4.2 Refund Flow

```
User/Support creates PaymentRequest(type=refund, sale_id, reason)
  │
  ▼
Support verifies (request.status → verified)
  │ → Validates sale exists, is active, has positive balance
  │
  ▼
Admin approves (request.status → approved)
  │ → RefundEngine.calculateRefund(sale, policy)
  │ → Determines refund_amount based on policy %
  │
  ▼
System executes (request.status → executed)
  │ → Idempotency: check request.executed_at is null
  │
  ▼
RefundEngine.processRefund(sale, amount, reason)
  │ → PaymentLedgerService.append(sale, 'refund', 'debit', amount)
  │ → IF refund_amount == balance: sale.status → 'refunded'
  │ → IF refund_amount <  balance: sale.status → 'partially_refunded'
  │
  ▼
AccessEngine cache invalidated
  │ → IF fully refunded: access revoked
  │ → IF partial: access continues (balance still positive)
  │
  ▼
AuditLog entry created
```

### 4.3 Upgrade / Adjustment Flow

```
User requests upgrade: Video Course → Live Course
  │
  ▼
PaymentRequest(type=upgrade, source_sale_id, target_product_id)
  │
  ▼
Admin approves
  │
  ▼
AdjustmentEngine.calculate(sourceSale, targetProduct, policy)
  │ → source_balance = LedgerService.balance(sourceSale)
  │ → transferable = source_balance * policy.adjustment_percent (e.g., 80%)
  │ → remaining = targetProduct.base_fee - transferable
  │ → Returns { transferable, remaining, policy_snapshot }
  │
  ▼
AdjustmentEngine.execute(...)
  │ → Create target Sale (status=pending_payment, parent_sale_id=source)
  │ → LedgerService.append(source, 'adjustment_out', 'debit', transferable)
  │ → LedgerService.append(target, 'adjustment_in', 'credit', transferable)
  │ → Source sale status → 'completed' (or remains active if partial)
  │
  ▼
IF remaining > 0:
  │ → User must pay the difference via normal payment flow
  │
  ▼
AccessEngine: source access ends, target access begins
```

### 4.4 Subscription Billing Flow

```
SubscriptionBillingJob (runs daily)
  │
  ▼
For each subscription WHERE current_period_end <= now():
  │
  ▼
IF status == 'trial' AND trial_ends_at <= now():
  │ → First real charge
  │
  ▼
SubscriptionEngine.charge(subscription)
  │ → Create upe_subscription_cycles entry
  │ → PaymentGatewayRouter.charge(gateway_subscription_id, amount)
  │
  ▼
IF payment succeeds:
  │ → LedgerService.append(sale, 'subscription_charge', 'credit', amount)
  │ → cycle.status → 'paid'
  │ → subscription.current_period_start = now()
  │ → subscription.current_period_end = now() + interval
  │ → subscription.status → 'active'
  │
  ▼
IF payment fails:
  │ → cycle.attempts++
  │ → IF attempts < max_retries:
  │     → subscription.status → 'past_due'
  │     → Retry tomorrow
  │ → IF attempts >= max_retries:
  │     → IF within grace_period_days:
  │         → subscription.status → 'grace'
  │     → ELSE:
  │         → subscription.status → 'expired'
  │         → AccessEngine: access revoked
```

### 4.5 Installment Payment Flow

```
User makes installment payment
  │
  ▼
InstallmentEngine.getNextDue(plan)
  │ → Returns first schedule WHERE status IN ('due', 'partial', 'overdue')
  │ → Enforces sequential order (no skipping)
  │
  ▼
InstallmentEngine.recordPayment(schedule, amount, method)
  │ → IF amount >= schedule.amount_due - schedule.amount_paid:
  │     → schedule.status → 'paid', schedule.paid_at = now()
  │     → Overflow applied to next schedule (if any)
  │ → ELSE:
  │     → schedule.amount_paid += amount
  │     → schedule.status → 'partial'
  │
  ▼
LedgerService.append(sale, 'installment_payment', 'credit', amount)
  │
  ▼
IF all schedules paid:
  │ → plan.status → 'completed'
  │
  ▼
AccessEngine: access derived from sale.status + balance threshold
```

---

## 5. EDGE CASES & FAILURE HANDLING

### 5.1 Concurrency
- All financial operations use `SELECT ... FOR UPDATE` on the sale row
- Ledger entries use `idempotency_key` (UNIQUE) to prevent duplicates
- Gateway webhooks are idempotent by `gateway_transaction_id`

### 5.2 Partial Payment Race
- Two partial payments arrive simultaneously for same installment
- Lock: `upe_installment_schedules` row locked during payment
- Second payment sees updated `amount_paid`, applies correctly

### 5.3 Double Webhook
- Gateway sends webhook twice for same payment
- `idempotency_key = gateway_{method}_{transaction_id}` prevents duplicate ledger entry
- Returns 200 OK to gateway (idempotent)

### 5.4 Refund After Installment
- User paid 3/6 installments, requests refund
- Refund amount = LedgerService.balance(sale) * policy_percent
- Remaining installments marked `waived`
- Plan status → `defaulted`

### 5.5 Subscription During Grace → Payment Success
- User in grace period, payment finally succeeds
- Status: `grace` → `active`
- Access: reinstated immediately
- No gap in billing cycle (backfill the cycle)

### 5.6 Upgrade + Discount Stacking
- User has a discount on target product
- Adjustment credit from source + discount on target
- Total credit cannot exceed target base_fee
- Enforced: `sum(credits) <= base_fee_snapshot`

### 5.7 Wrong Course Correction
- Original sale never modified
- AdjustmentEngine creates `adjustment_out` on wrong sale
- Creates new sale + `adjustment_in` on correct sale
- Old sale.status → `completed` (not deleted)

---

## 6. SECURITY & AUDIT CONSIDERATIONS

### 6.1 Server-Side Price Calculation
- Client NEVER sends amounts
- All prices derived from: `product.base_fee - discounts`
- Ledger entries created by services, never by controllers directly

### 6.2 Role-Based Authorization
- `admin` → can approve/execute all requests
- `support` → can verify requests, cannot execute
- `user` → can create requests only
- Discount creation caps per role (e.g., support max 20%, admin max 100%)

### 6.3 Idempotent Operations
- Every ledger entry has `idempotency_key`
- Every request has `executed_at` guard
- Every sale has `uuid` for creation idempotency
- Gateway webhooks keyed by `gateway_transaction_id`

### 6.4 Audit Trail
- Every state change logged to `upe_audit_log`
- Includes: actor, role, action, entity, old/new state, IP
- Ledger entries are the audit trail for money (never modified)
- Retention: indefinite (financial records)

### 6.5 Concurrency Safety
- `DB::transaction()` with `lockForUpdate()` on sale row
- `UNIQUE` constraints on idempotency keys
- Optimistic locking not used (pessimistic is safer for money)

### 6.6 Time-Bound Auto-Expiry
- Discounts: checked at application time, not cron
- Subscriptions: billing job checks `current_period_end`
- Sales: `valid_until` checked by AccessEngine at query time
- No silent background deletions

---

## 7. ASSUMPTIONS

1. **Currency:** System is primarily INR. Multi-currency support is at the product level (one currency per sale).
2. **Tax:** Tax calculation is outside this engine. Tax is stored on the sale but computed by a separate TaxService.
3. **Gateway abstraction:** Each payment gateway implements a `PaymentGatewayDriver` interface. Gateway-specific logic is isolated.
4. **Existing data:** The engine coexists with legacy `sales`, `orders`, `accounting` tables. No migration of historical data in phase 1.
5. **Wallet:** The existing `accounting` table serves as the wallet. `wallet_credit` ledger entries bridge into the legacy accounting system.
6. **Events:** Product events (webinars, live events) are treated as products with specific validity rules.
7. **Bundles:** A bundle is a product containing multiple sub-products. Access to sub-products derived from bundle sale access.

---

## 8. FILE STRUCTURE

```
app/
  Services/
    PaymentEngine/
      PaymentLedgerService.php      # Immutable ledger
      PurchaseEngine.php            # Sale creation + payment
      DiscountEngine.php            # Coupon/discount logic
      RefundEngine.php              # Refund processing
      InstallmentEngine.php         # Installment plans + payments
      SubscriptionEngine.php        # Recurring billing
      AdjustmentEngine.php          # Upgrades/corrections
      ReferralEngine.php            # Referral tracking + bonuses
      AccessEngine.php              # Derived access computation
      PaymentGatewayRouter.php      # Gateway routing
      AuditService.php              # Audit logging
      Contracts/
        PaymentGatewayDriver.php    # Interface for gateways
        RefundPolicy.php            # Interface for refund policies
        AdjustmentPolicy.php        # Interface for adjustment policies
  Models/
    PaymentEngine/
      UpeProduct.php
      UpeSale.php
      UpeLedgerEntry.php
      UpeDiscount.php
      UpeInstallmentPlan.php
      UpeInstallmentSchedule.php
      UpeSubscription.php
      UpeSubscriptionCycle.php
      UpeAdjustment.php
      UpeReferral.php
      UpePaymentRequest.php
      UpeAuditLog.php
  Jobs/
    PaymentEngine/
      SubscriptionBillingJob.php
      InstallmentOverdueJob.php
      ExpireTrialsJob.php
  Http/
    Controllers/
      PaymentEngine/
        PurchaseController.php
        RefundController.php
        SubscriptionController.php
        AdjustmentController.php
        InstallmentController.php
        AdminPaymentController.php
database/
  migrations/
    2026_02_10_200000_create_upe_products_table.php
    2026_02_10_200001_create_upe_sales_table.php
    2026_02_10_200002_create_upe_ledger_entries_table.php
    2026_02_10_200003_create_upe_discounts_table.php
    2026_02_10_200004_create_upe_installment_plans_table.php
    2026_02_10_200005_create_upe_installment_schedules_table.php
    2026_02_10_200006_create_upe_subscriptions_table.php
    2026_02_10_200007_create_upe_subscription_cycles_table.php
    2026_02_10_200008_create_upe_adjustments_table.php
    2026_02_10_200009_create_upe_referrals_table.php
    2026_02_10_200010_create_upe_payment_requests_table.php
    2026_02_10_200011_create_upe_audit_log_table.php
```

---

*End of Architecture Document*
