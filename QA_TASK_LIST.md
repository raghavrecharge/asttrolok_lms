# Asttrolok LMS — QA Task List
> Generated: 2026-03-05 | Tester: __________ | Review Date: __________

---

## How to Use This List

| Symbol | Meaning |
|---|---|
| `[ ]` | Not tested yet |
| `[✅]` | Passed |
| `[❌]` | Bug found |
| `[⚠️]` | Partial / needs more testing |

Fill in the **Result** column for each task. Add bug notes in the **Notes** column.

---

---

# MODULE 1 — UPE Payment Engine

## 1.1 Razorpay Full Payment (Webinar / Course)

| # | Task | Expected Result | Result | Notes |
|---|---|---|---|---|
| 1.1.1 | Student purchases a course using Razorpay (full price, no coupon) | Payment completes, `UpeSale` created with `status=active`, `sale_type=paid`, `pricing_mode=full`. Student gets course access immediately. | | |
| 1.1.2 | Student purchases a course and then checks `/panel/upe/purchases` | Course appears in My Purchases list | | |
| 1.1.3 | Student purchases a course and checks the UPE ledger | `TYPE_PAYMENT` ledger entry exists with correct amount | | |
| 1.1.4 | Student tries to purchase same course twice | Second purchase is blocked or handled gracefully | | |
| 1.1.5 | After successful purchase, student visits course learning page | Access is granted (no paywall shown) | | |

## 1.2 Razorpay Bundle Purchase

| # | Task | Expected Result | Result | Notes |
|---|---|---|---|---|
| 1.2.1 | Student purchases a bundle | `UpeSale` created for bundle product, access to all courses in bundle | | |
| 1.2.2 | Bundle purchase shows in My Purchases list | Bundle entry visible in `/panel/upe/purchases` | | |

## 1.3 Razorpay Cart (Multi-item)

| # | Task | Expected Result | Result | Notes |
|---|---|---|---|---|
| 1.3.1 | Student adds 2 courses to cart and checks out | 2 separate `UpeSale` records created, both courses accessible | | |
| 1.3.2 | Cart checkout with coupon applied to one item | Discount applied to correct course only | | |

## 1.4 Razorpay Installment / EMI

| # | Task | Expected Result | Result | Notes |
|---|---|---|---|---|
| 1.4.1 | Student selects installment plan and pays upfront | `UpeSale` with `pricing_mode=installment` created, `UpeInstallmentPlan` + schedules created, access granted if upfront covers it | | |
| 1.4.2 | Student pays next EMI step | Schedule status updates from `due` → `paid`, remaining amount decreases | | |
| 1.4.3 | Student visits `/panel/upe/installments` | Active EMI plan shows with correct remaining steps, due dates, overdue flags | | |
| 1.4.4 | EMI step becomes overdue (past due date) | Schedule status = `overdue`, dashboard shows overdue count | | |
| 1.4.5 | Final EMI step paid | Plan status = `completed`, sale status = `active` | | |

## 1.5 Razorpay Subscription

| # | Task | Expected Result | Result | Notes |
|---|---|---|---|---|
| 1.5.1 | Student purchases subscription | `UpeSubscription` created, subscribed courses accessible | | |
| 1.5.2 | Subscription expires | Access revoked for subscribed courses | | |

## 1.6 Quick Pay

| # | Task | Expected Result | Result | Notes |
|---|---|---|---|---|
| 1.6.1 | Student uses quick pay for a course | `UpeSale` created, access granted | | |

## 1.7 Coupon / Discount on Checkout

| # | Task | Expected Result | Result | Notes |
|---|---|---|---|---|
| 1.7.1 | Student applies a valid percentage coupon at checkout | Discount calculated correctly (e.g. 20% off), correct final price charged | | |
| 1.7.2 | Student applies a fixed-amount coupon | Correct fixed discount applied | | |
| 1.7.3 | Student applies an expired coupon | Error: coupon expired | | |
| 1.7.4 | Student applies coupon exceeding `max_uses` | Error: coupon limit reached | | |
| 1.7.5 | Coupon applied to course not in coupon's allowed list | Error: coupon not valid for this course | | |

## 1.8 Access Engine Verification

| # | Task | Expected Result | Result | Notes |
|---|---|---|---|---|
| 1.8.1 | Student with active paid sale visits course | `AccessEngine::computeAccess()` returns `hasAccess=true`, `accessType=paid` | | |
| 1.8.2 | Student with refunded sale tries to access course | `hasAccess=false`, paywall shown | | |
| 1.8.3 | Student with temporary access (before expiry) | `hasAccess=true`, `accessType=temporary` | | |
| 1.8.4 | Student with temporary access (after expiry) | `hasAccess=false` | | |
| 1.8.5 | Student with mentor access visits course | `hasAccess=true`, `accessType=mentor` | | |
| 1.8.6 | Student with no purchase visits a paid course | `hasAccess=false`, paywall shown | | |

---

---

# MODULE 2 — Wallet System

## 2.1 Wallet Top-up

| # | Task | Expected Result | Result | Notes |
|---|---|---|---|---|
| 2.1.1 | Student tops up wallet via Razorpay | Wallet balance increases, `WalletTransaction` credit entry created | | |
| 2.1.2 | Top-up reflects on dashboard wallet balance immediately | Correct balance shown | | |

## 2.2 Full Wallet Payment (course purchase)

| # | Task | Expected Result | Result | Notes |
|---|---|---|---|---|
| 2.2.1 | Student has sufficient wallet balance and pays 100% via wallet | No Razorpay popup, course access granted, wallet balance deducted, `UpeSale` created | | |
| 2.2.2 | `TransactionsHistoryRazorpay` record created with `payment_method=wallet` | Record exists with correct amount | | |
| 2.2.3 | UPE ledger entry `TYPE_WALLET_PAYMENT` created | Ledger entry with correct amount exists | | |
| 2.2.4 | Student has INSUFFICIENT wallet balance — tries full wallet pay | Error shown, payment blocked | | |

## 2.3 Partial Wallet Payment (wallet + Razorpay)

| # | Task | Expected Result | Result | Notes |
|---|---|---|---|---|
| 2.3.1 | Student uses partial wallet (e.g. ₹500 wallet + ₹300 Razorpay for ₹800 course) | Razorpay order created for ₹300, wallet ₹500 deducted after payment success | | |
| 2.3.2 | Wallet deduction stored in `order.payment_data` (not session) | `wallet_deduction` key in payment_data JSON | | |
| 2.3.3 | Idempotency — webhook fires twice for same payment | Wallet deducted only ONCE (checked via `wallet_deduction_processed` flag) | | |
| 2.3.4 | `TYPE_WALLET_PAYMENT` ledger entry created for wallet portion | Ledger entry with wallet amount exists | | |

## 2.4 Wallet with Installment Purchase

| # | Task | Expected Result | Result | Notes |
|---|---|---|---|---|
| 2.4.1 | Student pays EMI upfront using partial wallet | Wallet deducted for wallet portion, Razorpay charged for remainder | | |
| 2.4.2 | Wallet balance shown correctly after installment payment | Balance correctly reduced | | |

## 2.5 Wallet Edge Cases

| # | Task | Expected Result | Result | Notes |
|---|---|---|---|---|
| 2.5.1 | Student disables wallet toggle at checkout | Full amount charged via Razorpay, wallet untouched | | |
| 2.5.2 | Wallet balance = 0 | Wallet option disabled or shows ₹0 | | |
| 2.5.3 | Razorpay payment fails after wallet deduction (partial) | Wallet amount refunded back to balance | | |

---

---

# MODULE 3 — Support Ticket System

## 3.1 Student Submitting a Ticket

| # | Task | Expected Result | Result | Notes |
|---|---|---|---|---|
| 3.1.1 | Student submits a support ticket for a purchased course | Ticket created with `status=pending`, ticket number generated | | |
| 3.1.2 | Student views `/panel/support/newsuportforasttrolok` | Own tickets listed with correct counts (total / pending / approved / completed / rejected) | | |
| 3.1.3 | Dashboard `/panel` shows matching support ticket counts | Open/closed counts match support page exactly | | |
| 3.1.4 | Student cannot submit ticket for a free/mentor/relative-access course | Only paid courses appear in ticket course dropdown | | |

## 3.2 Scenario — Free Course Grant

| # | Task | Expected Result | Result | Notes |
|---|---|---|---|---|
| 3.2.1 | Admin approves a `free_course_grant` ticket | `UpeSale` (sale_type=free) created for student, legacy Sale created | | |
| 3.2.2 | Student gets access to granted course | `AccessEngine` returns `hasAccess=true` | | |
| 3.2.3 | Granted course does NOT appear in student's My Purchases | `/panel/upe/purchases` excludes it | | |
| 3.2.4 | Granted course does NOT appear in refund scenario dropdown | Support ticket course list excludes it | | |

## 3.3 Scenario — Post-Purchase Coupon

| # | Task | Expected Result | Result | Notes |
|---|---|---|---|---|
| 3.3.1 | Admin provides a coupon code and approves | `TYPE_DISCOUNT` ledger entry created, `Accounting` credit entry created | | |
| 3.3.2 | Support Role validates coupon, submits for approval | Ticket moves to `verified` status | | |
| 3.3.3 | Expired coupon code rejected | Error shown, ticket not completed | | |
| 3.3.4 | Coupon discount amount is correct (percentage and fixed) | Discount matches coupon configuration | | |

## 3.4 Scenario — Wrong Course Correction

| # | Task | Expected Result | Result | Notes |
|---|---|---|---|---|
| 3.4.1 | Admin executes wrong course correction | Wrong course `UpeSale` revoked, correct course `UpeSale` created | | |
| 3.4.2 | Student loses access to wrong course after correction | `AccessEngine` returns `hasAccess=false` for old course | | |
| 3.4.3 | Student gains access to correct course | `AccessEngine` returns `hasAccess=true` for new course | | |

## 3.5 Scenario — Relatives / Friends Access

| # | Task | Expected Result | Result | Notes |
|---|---|---|---|---|
| 3.5.1 | Admin grants relative access to another user | `UpeSale` (free) created for relative's account | | |
| 3.5.2 | Relative's account can access the course | Access verified via `AccessEngine` | | |
| 3.5.3 | Relative's granted course excluded from their My Purchases | Not shown in `/panel/upe/purchases` | | |
| 3.5.4 | Relative's granted course excluded from refund scenarios | Not refundable | | |

## 3.6 Scenario — Mentor Access

| # | Task | Expected Result | Result | Notes |
|---|---|---|---|---|
| 3.6.1 | Admin grants mentor access | `UpeSale` (free) + mentor badge created | | |
| 3.6.2 | Mentor can access the course | Access granted | | |
| 3.6.3 | Mentor access course excluded from My Purchases | Not shown in purchases list | | |
| 3.6.4 | Mentor access course excluded from refund scenarios | Not refundable | | |

## 3.7 Scenario — Temporary Access

| # | Task | Expected Result | Result | Notes |
|---|---|---|---|---|
| 3.7.1 | Admin grants temporary access with expiry date | `UpeSupportAction` created with `expires_at` set | | |
| 3.7.2 | Student accesses course before expiry | Access granted | | |
| 3.7.3 | Student tries to access course after expiry | Access denied | | |
| 3.7.4 | Temporary access course excluded from My Purchases | Not shown in purchases list | | |

## 3.8 Scenario — Course Extension

| # | Task | Expected Result | Result | Notes |
|---|---|---|---|---|
| 3.8.1 | Admin extends an expired course | Child `UpeSale` created with new `valid_until` date | | |
| 3.8.2 | Student can access extended course again | Access re-granted | | |
| 3.8.3 | Extension shown in student dashboard under extended courses | Visible in dashboard extended accesses section | | |

## 3.9 Scenario — Offline Cash Payment

| # | Task | Expected Result | Result | Notes |
|---|---|---|---|---|
| 3.9.1 | Admin processes full offline cash payment (no installment) | `UpeSale` (status=active) created, legacy Sale + Accounting created, access granted | | |
| 3.9.2 | Underpayment (cash < required amount) is blocked | Error shown, ticket not completed | | |
| 3.9.3 | Coupon applied during offline payment | Discount deducted from payable amount, validated correctly | | |
| 3.9.4 | Admin processes offline installment payment | `UpeSale` + `UpeInstallmentPlan` + schedules created | | |
| 3.9.5 | Offline installment — upfront covered → access granted | Student gets course access | | |
| 3.9.6 | Offline installment — upfront NOT covered → access denied | Student cannot access course yet | | |

## 3.10 Scenario — Installment Restructure

| # | Task | Expected Result | Result | Notes |
|---|---|---|---|---|
| 3.10.1 | Admin restructures an active installment plan | `UpeInstallmentSchedule` split correctly via `InstallmentEngine::splitSchedule()` | | |
| 3.10.2 | New schedule has correct due dates and amounts | Amounts sum to remaining balance | | |
| 3.10.3 | Student sees updated schedule in `/panel/upe/installments` | New split visible | | |

## 3.11 Scenario — Refund Payment

| # | Task | Expected Result | Result | Notes |
|---|---|---|---|---|
| 3.11.1 | Admin processes refund for a paid course | `TYPE_REFUND` ledger entry created, `UpeSale.status = refunded` | | |
| 3.11.2 | Student loses access after refund | `AccessEngine` returns `hasAccess=false` | | |
| 3.11.3 | Refunded course appears in student's Refunded Courses tab | Visible at `/panel/webinars/purchases/refunded` | | |
| 3.11.4 | Refunded course does NOT appear in My Purchases | Excluded from active purchases list | | |
| 3.11.5 | Free/mentor/relative access course does NOT appear in refund dropdown | Admin support show page hides non-paid access courses | | |

## 3.12 Support Role vs Admin Role

| # | Task | Expected Result | Result | Notes |
|---|---|---|---|---|
| 3.12.1 | Support Role tries to directly execute a scenario | Can only approve/reject, cannot directly complete | | |
| 3.12.2 | Admin directly approves and executes a scenario | Executes immediately, ticket status = `completed` | | |
| 3.12.3 | Support Role approves `free_course_grant` ticket | Status moves to `approved`, awaiting admin final action | | |
| 3.12.4 | Admin with support scenario — no dependency on Support Role | Admin can complete all 10 scenarios without Support Role intervention | | |

---

---

# MODULE 4 — Today's Changes & New Implementations (2026-03-05)

## 4.1 My Purchased Courses — Free/Mentor/Relative Exclusion

| # | Task | Expected Result | Result | Notes |
|---|---|---|---|---|
| 4.1.1 | Student granted free access (free_course_grant) views My Purchases | Free-granted course is NOT shown in list | | |
| 4.1.2 | Student with mentor access views My Purchases | Mentor-access course is NOT shown | | |
| 4.1.3 | Student with relative access views My Purchases | Relative-access course is NOT shown | | |
| 4.1.4 | Student with a paid course views My Purchases | Paid course IS shown correctly | | |
| 4.1.5 | Student with both paid and free-granted courses | Only paid course shows; free-granted excluded | | |

## 4.2 My EMI/Installment List — Free Access Exclusion

| # | Task | Expected Result | Result | Notes |
|---|---|---|---|---|
| 4.2.1 | Student with free-granted course views EMI Plans | Free-granted course NOT in EMI list (already filtered by `pricing_mode=installment`) | | |
| 4.2.2 | Student with active EMI plan views EMI Plans | Correct plan shown with remaining steps | | |

## 4.3 Refunded Courses Tab (NEW)

| # | Task | Expected Result | Result | Notes |
|---|---|---|---|---|
| 4.3.1 | Student with no refunded courses visits `/panel/webinars/purchases/refunded` | Empty state shown: "No Refunded Courses" | | |
| 4.3.2 | Student with one refunded course visits the page | Course card shown with refund date, instructor, original amount | | |
| 4.3.3 | Refunded course shows correct original amount paid | Amount matches `UpeSale.base_fee_snapshot` | | |
| 4.3.4 | Refunded course shows correct refund date | Date matches `UpeSale.updated_at` after refund | | |
| 4.3.5 | Legacy refunded course (not in UPE) also appears | Merged from legacy `Sale.refund_at` | | |
| 4.3.6 | Same course not duplicated (UPE primary, legacy fallback) | Each course appears only once | | |

## 4.4 Sidebar Navigation — Refunded Courses Link

| # | Task | Expected Result | Result | Notes |
|---|---|---|---|---|
| 4.4.1 | Student role — sidebar shows "Refunded Courses" link | Link visible between My Purchases and EMI Plans | | |
| 4.4.2 | Consultant role — sidebar shows "Refunded Courses" link | Link visible in Courses section | | |
| 4.4.3 | Teacher role — sidebar shows "Refunded Courses" link | Link visible | | |
| 4.4.4 | Clicking "Refunded Courses" navigates to correct page | Opens `/panel/webinars/purchases/refunded` | | |
| 4.4.5 | "Refunded" tab highlighted (active) when on the page | Active CSS class applied to sidebar item | | |

## 4.5 Purchases Page — Tab Navigation

| # | Task | Expected Result | Result | Notes |
|---|---|---|---|---|
| 4.5.1 | My Purchases page shows "My Purchases" and "Refunded" tab buttons | Both buttons visible at top-right | | |
| 4.5.2 | "My Purchases" button is active/highlighted on purchases page | Primary button style applied | | |
| 4.5.3 | "Refunded" button is active/highlighted on refunded page | Primary button style applied | | |
| 4.5.4 | Clicking "Refunded" tab from purchases page navigates correctly | Navigates to `/panel/webinars/purchases/refunded` | | |

## 4.6 Support Ticket Count Mismatch Fix

| # | Task | Expected Result | Result | Notes |
|---|---|---|---|---|
| 4.6.1 | Student dashboard shows same total support count as support page | Counts match exactly | | |
| 4.6.2 | Student dashboard "open" count matches support page pending+approved | Counts match | | |
| 4.6.3 | Student dashboard "closed" count matches support page completed+rejected | Counts match | | |
| 4.6.4 | Student's dashboard does NOT include other students' tickets | Count shows only own tickets | | |
| 4.6.5 | Teacher dashboard shows tickets for their own courses only | Counts based on `$user->webinars` (courses they teach) | | |

---

---

# MODULE 5 — Other / General Features

## 5.1 Student Dashboard

| # | Task | Expected Result | Result | Notes |
|---|---|---|---|---|
| 5.1.1 | Dashboard shows correct purchased course count | Matches actual paid purchases | | |
| 5.1.2 | Dashboard shows correct open installment count | Active `UpeInstallmentPlan` count | | |
| 5.1.3 | Dashboard shows correct overdue installment count | Plans with overdue schedules | | |
| 5.1.4 | Dashboard support ticket section shows correct open/closed counts | Matches support page | | |
| 5.1.5 | Extended/temporary access courses shown in dashboard | Visible under extended accesses section | | |

## 5.2 Financial Summary

| # | Task | Expected Result | Result | Notes |
|---|---|---|---|---|
| 5.2.1 | Student visits `/panel/financial/summary` | Transactions listed with amount and type | | |
| 5.2.2 | Credit transactions (payments) show green/positive direction badge | Badge color correct | | |
| 5.2.3 | Debit transactions (refunds) show red/negative direction badge | Badge color correct | | |
| 5.2.4 | Page works even when no legacy Accounting entries exist | UPE-based fallback shown | | |

## 5.3 Part Payment (Installment Steps)

| # | Task | Expected Result | Result | Notes |
|---|---|---|---|---|
| 5.3.1 | Student pays next installment step | Correct step amount pre-filled (server-calculated, not editable) | | |
| 5.3.2 | Amount field on installment card is readonly | Cannot manually change amount | | |
| 5.3.3 | Fractional paise rounded to whole INR | No fractional amount sent to Razorpay | | |

## 5.4 Invoice / Receipt

| # | Task | Expected Result | Result | Notes |
|---|---|---|---|---|
| 5.4.1 | Student downloads invoice for a paid course | Invoice PDF/page loads correctly | | |
| 5.4.2 | Invoice not available for free-granted courses | Invoice link hidden for free-granted courses | | |

## 5.5 Role-Based Panel Visibility

| # | Task | Expected Result | Result | Notes |
|---|---|---|---|---|
| 5.5.1 | Student panel does NOT show course creation tools | New course, quiz creation etc. hidden | | |
| 5.5.2 | Teacher panel shows both teaching tools AND student purchase section | Both sections visible | | |
| 5.5.3 | Consultant panel shows student-like view | Same as student (EMI, purchases, support) | | |
| 5.5.4 | Admin panel accessible only to admin/sub_admin | Other roles get 403 | | |

## 5.6 Notifications

| # | Task | Expected Result | Result | Notes |
|---|---|---|---|---|
| 5.6.1 | Notification sent after course purchase | Student receives purchase confirmation | | |
| 5.6.2 | Notification sent after support ticket status change | Student notified of approval/rejection | | |
| 5.6.3 | Notification sent after refund | Student notified of refund | | |

---

---

# Summary Tracker

| Module | Total Tasks | Passed ✅ | Failed ❌ | Partial ⚠️ | Not Tested |
|---|---|---|---|---|---|
| 1. UPE Payment Engine | 33 | | | | |
| 2. Wallet System | 13 | | | | |
| 3. Support Ticket Scenarios | 48 | | | | |
| 4. Today's Changes (2026-03-05) | 25 | | | | |
| 5. Other / General | 17 | | | | |
| **TOTAL** | **136** | | | | |

---

## Bug Log

| Bug # | Module | Task # | Description | Severity (P0/P1/P2) | Status |
|---|---|---|---|---|---|
| B-001 | | | | | |
| B-002 | | | | | |
| B-003 | | | | | |

> **Severity Guide:** P0 = Critical (blocks feature), P1 = High (wrong behavior), P2 = Low (cosmetic/minor)

---

*Tester Signature: __________________ Date: __________________*
