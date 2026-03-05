# Asttrolok LMS — Complete Feature & Role Matrix

> Last updated: 2026-03-05

---

## System Roles

| Role Key | Description |
|---|---|
| `admin` | Full system access, all permissions |
| `sub_admin` | Admin with limited configured permissions |
| `Support Role` | Support agent — can approve/reject tickets |
| `teacher` | Instructor who creates and sells courses |
| `organization` | Organization that manages multiple teachers |
| `teacher` + `consultant=1` | Consultant — teacher with student-like panel view |
| `user` | Student — purchases and consumes courses |

---

## 1. Payment Engine (UPE) — All Roles

### Payment Methods

| Feature | Student | Teacher/Org | Admin |
|---|---|---|---|
| Razorpay full payment (webinar) | ✅ | ✅ | — |
| Razorpay installment / EMI | ✅ | ✅ | — |
| Razorpay cart (multi-item) | ✅ | ✅ | — |
| Razorpay bundle purchase | ✅ | ✅ | — |
| Razorpay subscription | ✅ | ✅ | — |
| Wallet (full / partial payment) | ✅ | ✅ | — |
| Offline cash payment | — | — | ✅ via support |
| Quick pay | ✅ | ✅ | — |

### Access Engine (UPE Read Path)

| Access Type | Who Gets It |
|---|---|
| `paid` full purchase | Student (any paid checkout) |
| `installment` purchase | Student (via EMI) |
| `free` — free_course_grant | Admin-granted via support ticket |
| `free` — relative/friend access | Admin-granted via support ticket |
| `free` — mentor access | Admin-granted via support ticket |
| `temporary` — temporary access | Admin-granted via support ticket |
| `extension` — course extension | Admin-granted via support ticket |
| `subscription` | Student (active subscription) |

---

## 2. Support Ticket Scenarios — 10 Total

### Who Can Submit

- **Student** — submits for courses they own
- **Consultant / Teacher** — submits on behalf of student purchases on their courses

### Scenario Matrix

| # | Scenario Key | Student Submits | Admin Executes | Support Role Executes | Effect |
|---|---|---|---|---|---|
| 1 | `free_course_grant` | ✅ | ✅ Full auto-execute | ✅ Limited | Creates `UpeSale` (sale_type=free, pricing_mode=free) + legacy Sale for target course |
| 2 | `post_purchase_coupon` | ✅ | ✅ Auto-execute with coupon code | ✅ Validate then execute | Records discount in UPE ledger + Accounting credit |
| 3 | `wrong_course_correction` | ✅ | ✅ | ✅ | Revokes wrong `UpeSale` + grants correct course |
| 4 | `relatives_friends_access` | ✅ | ✅ | ✅ | Creates `UpeSale` (free) for relative/friend user |
| 5 | `mentor_access` | ✅ | ✅ | ✅ | Creates `UpeSale` (free) — mentor badge grant |
| 6 | `temporary_access` | ✅ | ✅ | ✅ | Creates `UpeSupportAction` with `expires_at` |
| 7 | `course_extension` | ✅ | ✅ | ✅ | Creates child `UpeSale` with extension days |
| 8 | `offline_cash_payment` | ✅ | ✅ Full (price breakdown, coupon, installment plan selector) | ✅ | Creates `UpeSale` + `UpeInstallmentPlan` + legacy dual-write |
| 9 | `installment_restructure` | ✅ | ✅ | ✅ | Splits `UpeInstallmentSchedule` via `InstallmentEngine::splitSchedule()` |
| 10 | `refund_payment` | ✅ | ✅ | ✅ | Creates `TYPE_REFUND` ledger entry, sets `UpeSale.status = refunded` |

### Refund Eligibility Rules

- ❌ Courses accessed via `free_course_grant` → **not refundable**
- ❌ Courses accessed via `mentor_access` → **not refundable**
- ❌ Courses accessed via `relatives_friends_access` → **not refundable**
- ❌ Courses accessed via `temporary_access` → **not refundable**
- ✅ Courses paid via checkout (full / installment / offline) → **refundable**

### Ticket Status Flow

```
pending → in_review → approved → verified → executed → completed
                    ↘ rejected
                    ↘ closed
```

| Status | Meaning | Dashboard Bucket |
|---|---|---|
| `pending` | Submitted, awaiting review | Open |
| `in_review` | Under review | Open |
| `approved` | Approved by support | Open |
| `verified` | Verified (new workflow) | Open |
| `executed` | Executed (new workflow) | Closed |
| `completed` | Completed (old workflow) | Closed |
| `rejected` | Rejected | Closed |
| `closed` | Manually closed | Closed |

---

## 3. Student Panel (`/panel`) — Role: `user` + `consultant`

| Section | URL | Feature |
|---|---|---|
| Dashboard | `/panel` | Support ticket counts (open/closed), installment summary, purchased course stats |
| My Courses | `/panel/webinars` | List all enrolled courses |
| My Purchases | `/panel/upe/purchases` | UPE-sourced paid purchases only (excludes free/mentor/relative grants) |
| **Refunded Courses** | `/panel/webinars/purchases/refunded` | All refunded courses (UPE + legacy merged) |
| EMI Plans | `/panel/upe/installments` | Active installment plans, schedules, overdue tracking |
| Financial Summary | `/panel/financial/summary` | All transactions (credit/debit) with direction badges |
| Support Tickets | `/panel/support/newsuportforasttrolok` | Submit/view own tickets; stats: total / pending / approved / completed / rejected |
| Certificates | `/panel/certificates` | Download earned certificates |
| Assignments | `/panel/assignments` | Submit and track assignments |
| Quiz | `/panel/quizzes` | Take quizzes, view results |
| Favorites | `/panel/webinars/favorites` | Bookmarked courses |
| Comments | `/panel/webinars/my-comments` | Course comments and reviews |
| Wallet | `/panel/financial/account` | Balance, top-up, use in checkout |
| Settings | `/panel/setting` | Profile, password, notification preferences |

---

## 4. Teacher / Organization Panel — Role: `teacher`, `organization`

All Student panel features **plus**:

| Section | URL | Feature |
|---|---|---|
| My Classes | `/panel/webinars` | Create, edit, publish, duplicate courses |
| Course Builder | `/panel/webinars/new` | Steps: info → chapters → sessions → files → quizzes → prerequisites → FAQs |
| Quiz Management | `/panel/quizzes` | Create quizzes, questions, view results |
| Assignments | `/panel/assignments` | Create assignments for students |
| Students Export | `/panel/webinars/{id}/export-students-list` | Export enrolled students list per course |
| Course Statistics | `/panel/webinars/statistics` | Views, sales, completion rates |
| Financial Sales Report | `/panel/financial/sales` | Sales revenue per course |
| Payout | `/panel/financial/payout` | Request payout to bank |
| Comments Management | `/panel/webinars/comments` | Reply and moderate course comments |
| Support Tickets | `/panel/support/newsuportforasttrolok` | View tickets raised by students on their courses |
| Meeting / Live Sessions | `/panel/meetings` | Schedule and host Agora / BigBlueButton live classes |
| Organization only | `/panel/manage/instructors` | Manage sub-instructors and students under org |

---

## 5. Admin Panel (`/admin`) — Role: `admin`, `sub_admin`

### Course & Content

| Feature | Notes |
|---|---|
| Courses CRUD | All statuses: pending / active / inactive |
| Bundles management | Group courses into bundles |
| Categories & sub-categories | Hierarchical structure |
| Chapters, Sessions, Files, Quizzes | Full course content management |
| Certificates | Templates + issuance |
| Course assignments | Create and grade |
| Upcoming courses | Pre-launch listings |
| Featured topics & webinars | Homepage highlights |

### Users & Roles

| Feature |
|---|
| Users list — create / edit / ban / delete / impersonate |
| Teachers management |
| Organizations management |
| Consultants management |
| Become instructor requests |
| Delete account requests |
| Groups |
| Badges |

### Payments & Finance

| Feature |
|---|
| Sales report (all transactions) |
| Installment plans management |
| Offline payment approvals |
| Payout management |
| Cashback rules & transactions |
| Discounts / Coupons CRUD |
| Accounting ledger |
| Gifts |

### Support System (Admin)

| Feature | Notes |
|---|---|
| View all support tickets | All users, all statuses |
| Execute all 10 scenarios | Full execution, no dependency on Support Role |
| Price breakdown for offline payment | Coupon validation + installment plan selector |
| Role-based execution | Admin = full auto-execute; Support Role = approve/reject flow |
| Refund scenario filtering | Excludes free / mentor / relative access courses |

### System & Configuration

| Feature |
|---|
| Settings (general, financial, notifications, features) |
| Theme / template management (default, default2) |
| Pages & blog |
| Navbar links |
| Home section settings |
| Advertising banners & modals |
| Floating bar |
| Forum management |
| Noticeboard |
| Newsletters |
| FAQ management |
| Filters |
| App update (PWA) |

### Integrations

| Integration | Usage |
|---|---|
| Razorpay | Course payments, installments, cart |
| Agora | Live video sessions |
| BigBlueButton | Live classes |
| Twilio SMS | Notifications |
| Wallet | Internal credit balance system |

---

## 6. My Purchased Courses — Filtering Rules

| Access Source | Shown in My Purchases | Refund Eligible | Shown in Refunded Tab |
|---|---|---|---|
| Paid checkout (Razorpay / offline) | ✅ | ✅ | ✅ if refunded |
| Free course grant (support) | ❌ excluded | ❌ | ❌ |
| Mentor access (support) | ❌ excluded | ❌ | ❌ |
| Relative / friend access (support) | ❌ excluded | ❌ | ❌ |
| Temporary access (support) | ❌ excluded | ❌ | ❌ |
| Installment / EMI purchase | ✅ in EMI Plans list | ✅ | ✅ if refunded |

---

## 7. UPE Sale Statuses

| Status | Meaning | Access Granted |
|---|---|---|
| `active` | Fully paid, active | ✅ |
| `partially_refunded` | Partially refunded, still has access | ✅ |
| `refunded` | Fully refunded | ❌ |
| `pending_payment` | EMI in progress (upfront paid) | ✅ if upfront covered |
| `cancelled` | Cancelled before payment | ❌ |
| `expired` | Access days elapsed | ❌ |

---

## 8. Key Services & Architecture

| Service / File | Responsibility |
|---|---|
| `app/Services/PaymentEngine/AccessEngine.php` | Sole access gatekeeper — computes whether a user can access a course |
| `app/Services/PaymentEngine/CheckoutService.php` | Handles all paid checkout flows (UPE + legacy dual-write) |
| `app/Services/PaymentEngine/InstallmentEngine.php` | EMI plan creation, schedule management, payment allocation |
| `app/Services/PaymentEngine/PaymentLedgerService.php` | Records all ledger entries (payment, refund, discount, wallet, adjustment) |
| `app/Services/SupportUpeBridge.php` | Bridges admin support actions → UPE records (all 10 scenarios) |
| `app/Services/SupportRequestService.php` | Student-facing support state machine (pending → completed) |
| `app/Http/Controllers/Admin/AdminSupportController.php` | Admin support ticket processing — role-based execution |
| `app/Http/Controllers/Panel/WebinarController.php` | Student purchases list, refunded purchases list |
| `app/Http/Controllers/Panel/InstallmentsController.php` | Student EMI plans list |
| `app/Http/Controllers/Panel/DashboardController.php` | Dashboard stats (support counts, installment counts, purchases) |

---

## 9. Summary Count

| Category | Count |
|---|---|
| System roles | 7 |
| Support scenarios | 10 |
| Razorpay payment types | 8 |
| UPE access types | 8 |
| Student panel sections | 14 |
| Teacher/Org extra sections | 8 |
| Admin panel feature groups | 5 |
| Total routes (panel) | ~295 |
| Total routes (admin) | ~779 |
| Key service files | 10 |
| **Total trackable features** | **~100+** |
