# Bug Register — Asttrolok LMS Testing

## Document: 20-02-26 Asttrolok Frontend UI – Human Testing Checklist

**37 images extracted and analyzed from testing doc.**

---

## Image Coverage Map (20-02-26 Document)

| IMG | Source File | Screen Shown | Bug Illustrated | Failing Layer | Bug ID |
|-----|-----------|--------------|-----------------|---------------|--------|
| IMG-01 | image1.png | Course Extension dropdown | "No expired courses found" despite expired course on dashboard | Support/Access | LMS-040 |
| IMG-02 | image2.png | Internal Server Error page | SQLSTATE 1265: `request_type` truncated for `installment_restructure` | DB/Migration | LMS-032 |
| IMG-03 | image3.png | Internal Server Error page | SQLSTATE 1265: `request_type` truncated for `course_extension` | DB/Migration | LMS-032 |
| IMG-04 | image4.png | Error toast | "Failed to update" on Mentor access completion | Support/UPE | LMS-034 |
| IMG-05 | image5.png | EMI Plan Details | Ledger Summary Credits=₹1, Balance=₹1 (wrong) | Ledger | LMS-037 |
| IMG-06 | image6.png | Support ticket list | Installment Restructure ticket "Completed" but no effect | Installment | LMS-036 |
| IMG-07 | image7.png | Coupon admin list | `nadiastrology10` 50% max ₹32,000 applied on ₹55,000 course | Coupon | LMS-041 |
| IMG-08 | image8.png | Installment list | Upfront Paid, 2nd+3rd Unpaid after paying ₹50,000 | Installment/Ledger | LMS-037 |
| IMG-09 | image9.png | Course card | Numerology English ₹14,999 (₹20,000 crossed) | UI/Coupon | LMS-041 |
| IMG-10 | image10.png | Support ticket detail | Coupon ticket Approved but "Pending Processing" still showing | Support/State | LMS-039 |
| IMG-11 | image11.png | Installment list | Upfront ₹16,500 Paid, 2nd ₹16,500-0 Unpaid, 3rd ₹17,000 Unpaid | Installment/Ledger | LMS-037 |
| IMG-12 | image12.png | Create Support Request | SQL error: "Unknown column 'relative_description'" | DB/Migration | LMS-033 |
| IMG-13 | image13.png | Error toast | "Failed to update" on Refund completion | Support/UPE | LMS-034 |
| IMG-14 | image14.png | Purchase Details #20835 | Base ₹20,000, Credits ₹10,999 — 20% off ₹14,999 should give ₹3,000 discount not ₹10,999 | Coupon/Ledger | LMS-041 |
| IMG-15 | image15.png | Financial summary | "No financial document!" | Ledger/UI | LMS-038 |
| IMG-16 | image16.png | Purchase Details #20833 | Base ₹55,000, Credits ₹9,075 — coupon applied incorrectly | Coupon/Ledger | LMS-041 |
| IMG-17 | image17.png | Installment list | Upfront ₹4,290-0 with Payment Date but shows "Pay Upcoming Part" | Installment/Ledger | LMS-037 |
| IMG-18 | image18.png | Razorpay receipt | ₹50,000.03 — fractional paise | Rounding | LMS-035 |
| IMG-19 | image19.png | My Purchases page | Request Extension form showing, current validity expired | Support/UI | INFO |
| IMG-20 | image20.png | Error toast | "Failed to update" on Offline/Cash Payment | Support/UPE | LMS-034 |
| IMG-21 | image21.png | Razorpay checkout | ₹5,332.32 — fractional paise, coupon not reflected | Rounding/Coupon | LMS-035 |
| IMG-22 | image22.png | Relative/Friend dropdown | Only purchased courses shown | Support/UI | BY-DESIGN |
| IMG-23 | image23.png | Razorpay receipt | ₹12,999 paid instead of entered ₹5,000 | Installment/Payment | LMS-036 |
| IMG-24 | image24.png | Razorpay checkout | ₹12,999 showing instead of ₹5,000 entered | Installment/Payment | LMS-036 |
| IMG-25 | image25.png | Financial summary | "No financial document!" | Ledger/UI | LMS-038 |
| IMG-26 | image26.png | Installment payment page | ₹12,999 Total Payment, editable amount field (5000) | Installment/UI | LMS-036 |
| IMG-27 | image27.png | Checkout page | ₹5,332 instead of ₹21,999 course price | Checkout/Legacy | LMS-035 |
| IMG-28 | image28.png | Legacy installment page | 3 installments: Upfront Paid, 2nd+3rd Unpaid, overview wrong | Installment/Ledger | LMS-037 |
| IMG-29 | image29.png | Support ticket detail | Offline payment ticket Approved but "Pending Processing" | Support/State | LMS-039 |
| IMG-30 | image30.png | UPE Purchase Details #20848 | ₹18,150 balance, "Pending payment" status, EMI Plan Active | UPE/UI | LMS-042 |
| IMG-31 | image31.png | Installment overview | Upfront paid but all showing Unpaid, overview not updated | Installment/Ledger | LMS-037 |
| IMG-32 | image32.png | Admin support ticket | Offline/Cash Payment — "Error Failed to update" on Complete | Support/UPE | LMS-034 |
| IMG-33 | image33.png | Dashboard | My Purchases shown but no access extension/temp access info | Access/UI | LMS-043 |
| IMG-34 | image34.png | Installment payment page | ₹55,000 Total Payment, editable amount field (18150) | Installment/UI | LMS-036 |
| IMG-35 | image35.png | UPE EMI Plan Details | Only 2 installments shown, Paid=₹18,150 | Installment/UPE | LMS-042 |
| IMG-36 | image36.png | Legacy installment page | 3 installments shown vs UPE showing 2 | Installment/UPE | LMS-042 |
| IMG-37 | image37.png | Course detail page | ₹21,999 price shown | UI | INFO (correct) |

---

## Bug Register (20-02-26 Round)

| ID | Title | Severity | Layer | Root Cause | Status |
|----|-------|----------|-------|------------|--------|
| LMS-032 | SQLSTATE 1265: `course_extension` / `installment_restructure` truncated in `upe_payment_requests` | P0 | DB | Enum migration `2026_02_19_180000` exists but not yet run on DB | ⏳ MIGRATION PENDING — run `php artisan migrate` |
| LMS-033 | SQL error: Unknown column `relative_description` in `new_support_for_asttrolok` | P0 | DB | Column defined in model but missing ALTER migration | ✅ FIXED — migration `2026_02_21_100000` created |
| LMS-034 | Support completion "Failed to update" for Mentor/Offline/Refund scenarios | P0 | Support/UPE | Generic catch block hid real error; missing tables/columns caused crashes | ✅ FIXED — error toast now shows actual exception; detailed logging added |
| LMS-035 | Checkout wrong price + fractional paise (₹5,332.32 / ₹50,000.03) | P0 | Checkout/Rounding | Razorpay amount `(int)($amount*100)` produced float artifacts | ✅ FIXED — HALF-UP round to integer INR before ×100 in `createRazorpayOrder()` + `Channel::verify()` |
| LMS-036 | "Pay Upcoming Part" shows full course amount; Razorpay ignores entered amount | P0 | Installment/Payment | `case 'part'` used `getPrice()` (full price); blade amount field was editable | ✅ FIXED — server calculates next unpaid step amount; blade field is readonly with computed upfront |
| LMS-037 | Installment upfront paid but shows Unpaid / overview not updated | P0 | Installment/Ledger | `InstallmentOrderPayment` status not reconciled from actual payments | ✅ FIXED — `show()` reconciles upfront + all steps from UPE ledger balance + legacy payments |
| LMS-038 | Financial summary "No financial document!" | P1 | Ledger/UI | Blade only showed legacy `$accountings`; UPE-only purchases invisible | ✅ FIXED — blade now shows `$amount_paid` payment history section when available |
| LMS-039 | Support ticket stuck "Pending Processing" after admin approval | P1 | Support/State | Auto-execute only fired if `coupon_code` already persisted; admin couldn't set it at approval | ✅ FIXED — admin can supply `coupon_code` at approval time; auto-execute fires immediately |
| LMS-040 | Expired course not shown in extension dropdown | P1 | Support/Access | `getPurchasedCoursesIds()` filters out expired UPE sales | ✅ FIXED — separately queries expired UPE sales and merges into dropdown list |
| LMS-041 | Coupon discount calculation wrong (20% off ₹14,999→₹10,999) + max_amount not enforced | P0 | Coupon/Ledger | Used `$webinar->price` (base) not `getPrice()` (after offers); no HALF-UP rounding | ✅ FIXED — all branches use `getPrice()` + `(int) round(..., PHP_ROUND_HALF_UP)` |
| LMS-042 | UPE EMI shows 2 installments vs legacy showing 3 | P2 | Installment/UPE | UPE and legacy installment plans configured with different step counts | ℹ️ DATA CONFIG — admin must align UPE installment plan step count with legacy |
| LMS-043 | Dashboard missing access extension / temp access info | P2 | Access/UI | Dashboard only queried `WebinarAccessControl`, not `UpeSupportAction` | ✅ FIXED — dashboard now merges UPE temp access + extension data into `$extendedAccesses` |

---

## Previous Round (19-02-26) — Fixes Already Applied

| ID | Title | Status |
|----|-------|--------|
| LMS-001 | Upfront installment shows "Unpaid" when already paid | ✅ FIXED |
| LMS-002 | "Pay Upcoming Part" shows full course amount | ✅ FIXED (partial — still recurring per LMS-036) |
| LMS-003 | Financial summary empty | ✅ FIXED (partial — UPE fallback added but still empty per LMS-038) |
| LMS-006 | Coupon 50% discount math wrong | ✅ FIXED |
| LMS-014 | SQLSTATE enum truncation "course_extension" | ✅ FIXED (migration created, needs `php artisan migrate`) |
| LMS-019 | Coupon `source='all'` rejected | ✅ FIXED |
| LMS-024 | Post-purchase coupon stuck "Pending Processing" | ✅ FIXED (partial — still occurring per LMS-039) |
| LMS-025 | Mentor access "Failed to update" for Admin | ✅ FIXED (partial — still occurring per LMS-034) |
| LMS-028 | Coupon usable times=1 but can use twice | ✅ FIXED |

---

## Dual-Write Gap Analysis

| Write Path | Legacy | UPE | Ledger | Status |
|-----------|--------|-----|--------|--------|
| Cart checkout (Razorpay) | ✅ Sale | ✅ UpeSale (via hook) | ✅ Ledger | OK |
| Direct payment (PaymentController) | ✅ Sale | ✅ UpeSale (CheckoutService) | ✅ Ledger | OK |
| Part payment (PartPaymentController) | ✅ WebinarPartPayment | ⚠️ Only on step clearance | ⚠️ Only on step clearance | BRIDGED |
| Support: Relative/Mentor access | ✅ Sale | ✅ UpeSale (SupportUpeBridge) | ❌ No ledger (free) | OK (free=no ledger) |
| Support: Offline payment | ✅ Sale/PartPayment | ✅ UpeSale (SupportUpeBridge) | ✅ Ledger entry | OK |
| Support: Refund | ✅ Sale soft-revoke | ✅ UPE (SupportUpeBridge) | ✅ Refund ledger | OK |
| Support: Course extension | ✅ WebinarAccessControl | ✅ UpeSale (SupportUpeBridge) | ✅ Extension ledger | OK |
| Support: Temp access | ✅ WebinarAccessControl | ✅ UpeSupportAction | ❌ No ledger (temp) | OK |
| Installment upfront (Razorpay) | ✅ InstallmentOrderPayment | ✅ UpeSale (hook) | ✅ Ledger | VERIFY status reconcile |

## Files Modified (20-02-26 Round)

| File | Bugs Fixed |
|------|-----------|
| `database/migrations/2026_02_21_100000_add_relative_description_to_support_table.php` (NEW) | LMS-033 |
| `app/Http/Controllers/Admin/AdminSupportController.php` | LMS-034, LMS-039 |
| `app/Http/Controllers/Web/PartPaymentController.php` | LMS-035, LMS-036 |
| `app/PaymentChannels/Drivers/Razorpay/Channel.php` | LMS-035 |
| `app/Http/Controllers/Web/CartController.php` | LMS-041 |
| `resources/views/web/default2/installment/partPayment/card.blade.php` | LMS-036 |
| `resources/views/web/default/installment/partPayment/card2.blade.php` | LMS-036 |
| `app/Http/Controllers/Panel/InstallmentsController.php` | LMS-037 |
| `resources/views/web/default2/panel/financial/summary.blade.php` | LMS-038 |
| `app/Http/Controllers/Panel/NewSupportForAsttrolokController.php` | LMS-040 |
| `app/Http/Controllers/Panel/DashboardController.php` | LMS-043 |
| `BUG_REGISTER.md` | Documentation |

## Ledger Bypass Points (Status After Fixes)

1. **Installment display**: ~~Legacy status not reconciled~~ → ✅ FIXED — `InstallmentsController::show()` now reconciles from UPE ledger
2. **Financial summary**: ~~Legacy Accounting empty~~ → ✅ FIXED — blade shows `$amount_paid` section when legacy is empty
3. **Coupon discount**: `handleDiscountPrice()` calculates discount but doesn't create ledger entry at calculation time (only at payment) — BY DESIGN
4. **Fractional paise**: ~~`Channel::verify()` stores floats~~ → ✅ FIXED — HALF-UP rounded to integer INR

## Secure vs Legacy Path Usage

| UI Action | Currently Calls | Should Call |
|-----------|----------------|-------------|
| Admin ticket completion | `AdminSupportController@updateStatus` | Same (has SupportUpeBridge dual-write) |
| Support ticket verification | `AdminSupportController@updateStatusSecure` → `SupportRequestService@transition` | Correct |
| User creates support request | `NewSupportForAsttrolokController@store` | Same (creates `upe_payment_requests` for extension/restructure) |

## Reconciliation Plan

1. Run `php artisan migrate` to apply enum fix + missing columns
2. Run `php artisan upe:sync-part-payments` to sync legacy part payments
3. Run `php artisan upe:sync-access-control` to sync WebinarAccessControl
4. Verify installment status reconciliation from UPE ledger
5. Verify financial summary shows UPE data for all users
