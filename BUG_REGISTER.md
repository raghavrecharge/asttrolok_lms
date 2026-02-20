# Bug Register — 19-02-26 Asttrolok Testing Doc

**All 48 images extracted and analyzed.**

## Image Coverage Map

| IMG | Source File | Screen | Bug ID |
|-----|-----------|--------|--------|
| IMG-01 | image1.png | Post-purchase coupon "palmistry101" applied success | LMS-012 |
| IMG-02 | image10.png | Order summary: Astrology Level 2, coupon "numerology10", discount ₹22,999 on ₹29,999 | LMS-020 |
| IMG-03 | image11.png | Razorpay receipt ₹4,999 | LMS-006 |
| IMG-04 | image12.png | Installment list: Upfront ₹16,500 Paid, 2nd ₹16,500-0 Unpaid | LMS-002, LMS-004 |
| IMG-05 | image13.png | Invoice: Yoga and Dasha ₹14,999 full paid, discount 0 | LMS-009 |
| IMG-06 | image14.png | Coupon list: "new discount" 25%, course shows null | LMS-007 |
| IMG-07 | image15.png | Coupon creation form: "extra discount" 10%, code "extradiscount" | LMS-030 |
| IMG-08 | image16.png | Course listing: Palmistry English ₹14,999 | LMS-026 |
| IMG-09 | image17.png | Razorpay checkout ₹12,999 (wrong for 10% off 14999) | LMS-031 |
| IMG-10 | image18.png | Coupon list: "new discount" 10%, usable 1, Remained:1 | LMS-028 |
| IMG-11 | image19.png | Purchase detail: Actions (refund, upgrade, extension, coupon) | LMS-011, LMS-015 |
| IMG-12 | image20.png | Admin support ticket list: 8 tickets various statuses | LMS-005, LMS-024 |
| IMG-13 | image21.png | SQLSTATE error: request_type "post_purchase_coupon" truncated | LMS-017 |
| IMG-14 | image22.png | SQLSTATE error: request_type "course_extension" truncated | LMS-014 |
| IMG-15 | image23.png | Error toast "Failed to update" | LMS-025 |
| IMG-16 | image24.png | Error toast "Failed to update" with "Never Purchased" visible | LMS-025 |
| IMG-17 | image25.png | Razorpay checkout ₹12,999 | LMS-031 |
| IMG-18 | image26.png | Error toast "Failed to update" | LMS-031 |
| IMG-19 | image27.png | Relative/Friend access dropdown: shows only purchased courses | LMS-008 |
| IMG-20 | image28.png | 404 error page | LMS-013 |
| IMG-21 | image29.png | Installment list: Upfront ₹5,940-0 Unpaid despite payment date shown | LMS-001 |
| IMG-22 | image30.png | Coupon admin list: 3 coupons (nadiastrology10, allusers, palmistry101) | LMS-007 |
| IMG-23 | image31.png | Mentor access ticket: Executed, "Ticket Successfully Resolved & Closed" | LMS-005 |
| IMG-24 | image32.png | Checkout ₹32,500: coupon invalid | LMS-019 |
| IMG-25 | image33.png | Admin ticket list: Installment Restructure Completed | LMS-022 |
| IMG-26 | image34.png | Error toast "Failed to update" | LMS-031 |
| IMG-27 | image35.png | Order summary: Astrology Level 2 coupon "allusers" 20%, discount ₹14,000, total ₹15,999 | LMS-020 |
| IMG-28 | image36.png | Razorpay receipt ₹50,000.03 | LMS-004 |
| IMG-29 | image37.png | Installment overview: 2 Total Parts, 2 Remained, ₹10,049 Remained | LMS-001 |
| IMG-30 | image38.png | Checkout ₹4,999: coupon "extradiscount" invalid | LMS-030 |
| IMG-31 | image39.png | Financial summary: "No financial document!" | LMS-003 |
| IMG-32 | image40.png | Checkout page with spinner/buffering after cancel | LMS-029 |
| IMG-33 | image41.png | Payment successful ₹50,000.03 | LMS-004 |
| IMG-34 | image42.png | Subscription page: Start Free Trial button, ₹2,100/month | LMS-027 |
| IMG-35 | image43.png | Checkout: Total Amount ₹12,500 (should be ₹14,999) | LMS-026 |
| IMG-36 | image44.png | Installment list: Upfront Unpaid despite Payment Date present | LMS-001 |
| IMG-37 | image45.png | Course listing page showing prices | LMS-026 |
| IMG-38 | image46.png | Admin support ticket: Post-Purchase Coupon Apply "Pending Processing" | LMS-024 |
| IMG-39 | image47.png | Support ticket details: Installment Restructure, "Never purchased" | LMS-022 |
| IMG-40 | image48.png | Admin ticket: Post-Purchase Coupon approved, "Pending Processing" | LMS-024 |
| IMG-41 | image49.png | Palmistry English course card ₹14,999 | LMS-026 |
| IMG-42 | image50.png | Razorpay checkout ₹9,999 (wrong amount) | LMS-020 |
| IMG-43 | image51.png | Admin support: Mentor Access ticket "Failed to update" error | LMS-025 |
| IMG-44 | image52.png | My Requests: 4 requests all "Numerology English" (wrong course) | LMS-018 |
| IMG-45 | image53.png | Invoice: Palmistry ₹4,999 (partial offline but full invoice) | LMS-009 |
| IMG-46 | image54.png | Financial summary: "No financial document!" | LMS-003 |
| IMG-47 | image55.png | Total Amount ₹12,500 (wrong) | LMS-026 |
| IMG-48 | image56.png | Installment list: Upfront ₹16,500 Paid, others Unpaid | LMS-001 |

## Bug Register

| ID | Title | Severity | Status | Root Cause | Fix Applied |
|----|-------|----------|--------|------------|-------------|
| LMS-001 | Upfront installment shows "Unpaid" when already paid | P0 | ✅ FIXED | Upfront `InstallmentOrderPayment` status not reconciled after payment | `InstallmentsController::show()` reconciles upfront to 'paid' when actual payments cover upfront amount |
| LMS-002 | "Pay Upcoming Part" shows full course amount | P0 | ✅ FIXED | `/register-course/{slug}` link lacks step amount param | Blade links now pass `?amount=` with correct step/upfront/sub-step amount |
| LMS-003 | Financial summary empty despite 4 purchases | P1 | ✅ FIXED | `AccountingController::index()` only queries legacy tables, misses UPE sales | Added UPE sales aggregation to financial summary |
| LMS-004 | After paying 2nd installment, shows all "Unpaid" | P0 | ✅ FIXED | Same root as LMS-001 — upfront status not reconciled | Fixed by LMS-001 reconciliation logic |
| LMS-005 | Mentor access ticket closed after "executed" | P1 | ✅ FIXED | Part of LMS-025 validation issue preventing status transitions | Fixed by LMS-025 validation fix |
| LMS-006 | Coupon 50% discount math wrong | P0 | ✅ FIXED | `directPayment()` didn't use session coupon, wrong calc | `WebinarController::directPayment()` now uses session coupon, handles fixed_amount + max_amount cap |
| LMS-007 | Coupon shows null course in discount list | P2 | ℹ️ NO-OP | Already guarded in blade with `@if(!empty($discountCourse->course))` | Verified: 0 orphaned `discount_courses` records exist |
| LMS-008 | Relative/Friend dropdown shows only purchased courses | P2 | ℹ️ BY DESIGN | Dropdown intentionally shows only user's purchased courses for sharing | Correct behavior — user can only share courses they own |
| LMS-009 | Offline ₹8,000 payment gives full invoice + full access | P1 | ✅ FIXED | `offlineCashPayment()` always called `purchaseCourseDirectly()` regardless of amount | Now checks `cashAmount >= coursePrice` before granting full access; partial → `WebinarPartPayment` only |
| LMS-010 | Installment payment not working (coupon invalid) | P0 | ✅ FIXED | Same as LMS-030: `source='all'` coupon rejected | Fixed by LMS-019/LMS-030 coupon scope fix |
| LMS-011 | Refund request page refreshes without error | P0 | ✅ FIXED | Blade template missing `$errors` display | Added `@if($errors->any())` block to `purchase_detail.blade.php` |
| LMS-012 | Post-purchase coupon: same coupon reapplied | P1 | ✅ FIXED | Part of LMS-028 — usage not counted across all purchase paths | Fixed by LMS-028 usable-times counting fix |
| LMS-013 | Wrong course: 404 error | P2 | ⏳ DEFERRED | Route/data issue — needs specific reproduction steps | Low-priority data investigation |
| LMS-014 | SQLSTATE enum truncation "course_extension" | P0 | ✅ FIXED | `upe_payment_requests.request_type` enum missing values | Migration adds `course_extension`, `post_purchase_coupon`, `installment_restructure` |
| LMS-017 | SQLSTATE enum truncation "post_purchase_coupon" | P0 | ✅ FIXED | Same as LMS-014 | Same migration fix |
| LMS-018 | Wrong course in refund request list | P0 | ⏳ DEFERRED | Request `sale_id` linkage — form passes correct sale_id from purchase detail | Likely stale data; needs data investigation |
| LMS-019 | Coupon "extradiscount" invalid for all courses | P0 | ✅ FIXED | `checkValidDiscount()` doesn't handle `source='all'` | Both `checkValidDiscount()` and `checkValidDiscount1()` now return 'ok' for `source='all'` |
| LMS-020 | 20% discount gives wrong price | P0 | ✅ FIXED | `directPayment()` used wrong discount logic | Fixed by LMS-006 directPayment coupon math fix |
| LMS-022 | Installment restructure shows "Never Purchased" | P1 | ✅ FIXED | `getPurchaseInfo()` only checks `getSaleItem()`, misses installment orders | Now also checks `InstallmentOrder` records |
| LMS-024 | Post-purchase coupon stuck "Pending Processing" | P1 | ✅ FIXED | Admin approval doesn't auto-execute coupon | Auto-executes `ApplyCouponCode()` on approval, sets status to 'completed' |
| LMS-025 | Mentor access "Failed to update" for Admin | P1 | ✅ FIXED | `support_remarks` required for admin on all status changes | Admin now has `nullable` support_remarks for completed/executed/closed/rejected |
| LMS-026 | Course price shows ₹12,500 instead of ₹14,999 | P2 | ℹ️ DATA | `activeSpecialOffer()` applies discount — price correct per active offer | Admin should verify/update special offer settings |
| LMS-027 | Free trial button error | P2 | ✅ FIXED | Button links to non-existent route `/subscriptions/direct-payment-enroll/{slug}` | Fixed all occurrences in both themes to `/subscriptions/direct-payment/{slug}` |
| LMS-028 | Coupon usable times=1 but can use twice | P0 | ✅ FIXED | `discountRemain()` only counts `OrderItem` usage | Now also counts `Sale` records and UPE ledger entries |
| LMS-029 | Payment cancel causes infinite buffering | P2 | ✅ FIXED | `hideLoader()` only hides `#loader`, not `#paymentLoader` overlay | `hideLoader()` now also hides `#paymentLoader` |
| LMS-030 | "extradiscount" coupon for all courses shows invalid | P0 | ✅ FIXED | Same as LMS-019 | Same fix |
| LMS-031 | 10% discount: 14999→12999 instead of 13500 | P0 | ✅ FIXED | Same as LMS-006 — directPayment coupon math | Same fix |

## Verification Checklist

### P0 Fixes (Must verify before deploy)
- [ ] **LMS-014/017 Enum**: Run `php artisan migrate` — confirm no SQLSTATE 1265 errors on course_extension / post_purchase_coupon requests
- [ ] **LMS-019/030 Coupon Scope**: Apply a coupon with `source='all'` on checkout — should validate successfully
- [ ] **LMS-006/020/031 Coupon Math**: Apply 10% coupon on ₹14,999 course → should see ₹13,499.10 (not ₹12,999)
- [ ] **LMS-028 Coupon Usage**: Use a coupon with `usable_times=1`, purchase → second use should be rejected
- [ ] **LMS-001/004 Upfront Status**: View installment detail after upfront payment → should show "Paid"
- [ ] **LMS-002 Pay Upcoming Part**: Click "Pay Upcoming Part" → payment page should show step amount, not full price
- [ ] **LMS-011 Refund Form**: Submit refund form with invalid data → should show validation errors

### P1 Fixes
- [ ] **LMS-003 Financial Summary**: View Panel > Financial for user with UPE purchases → should show records
- [ ] **LMS-025 Mentor Access**: Admin marks mentor access ticket as "completed" → should succeed
- [ ] **LMS-024 Post-Purchase Coupon**: Admin approves post-purchase coupon ticket → auto-executes, shows "Completed"
- [ ] **LMS-022 Installment Restructure**: Create restructure ticket for user with installment → shows "Active"
- [ ] **LMS-009 Offline Payment**: Admin completes offline cash with partial amount → should NOT grant full access

### P2 Fixes
- [ ] **LMS-029 Cancel Spinner**: Open Razorpay checkout, cancel → spinner disappears
- [ ] **LMS-027 Free Trial**: Click "Start Free Trial" → navigates to payment page (not 404)
- [ ] **LMS-026 Course Price**: Verify special offer settings for affected course

## Files Modified

| File | Bugs Fixed |
|------|-----------|
| `database/migrations/2026_02_19_180000_fix_upe_payment_requests_enum.php` | LMS-014, LMS-017 |
| `app/Models/Discount.php` | LMS-019, LMS-030, LMS-028 |
| `app/Http/Controllers/Web/WebinarController.php` | LMS-006, LMS-020, LMS-031 |
| `app/Http/Controllers/Panel/InstallmentsController.php` | LMS-001, LMS-004 |
| `app/Http/Controllers/Panel/AccountingController.php` | LMS-003 |
| `app/Http/Controllers/Admin/AdminSupportController.php` | LMS-025, LMS-024, LMS-009 |
| `app/Http/Controllers/Panel/NewSupportForAsttrolokController.php` | LMS-022 |
| `public/assets/design_1/js/unified-payment.js` | LMS-029 |
| `resources/views/web/default2/panel/upe/purchase_detail.blade.php` | LMS-011 |
| `resources/views/web/default2/panel/financial/installments/details.blade.php` | LMS-002 |
| `resources/views/web/default2/subscription/index.blade.php` | LMS-027 |
| `resources/views/web/default/subscription/index.blade.php` | LMS-027 |
