# Learning Page — Complete Logic & Conditions (All Courses)

**Page URL:** `/course/learning/{slug}`  
**Controller:** `app/Http/Controllers/Web/LearningPageController.php` → `index()`  
**Blade:** `resources/views/web/default/course/learningPage/components/content_tab/chapter.blade.php`  
**Last Updated:** 2026-02-25

---

## STEP 1: Data Loading

| What | Source | How |
|------|--------|-----|
| Course data + `hasBought` | `WebinarController::course($slug)` | Loads course, chapters, user. `hasBought` uses `AccessEngine` internally |
| UPE Product | `UpeProduct` where `external_id = course.id` | Maps course to UPE |
| UPE Sale | `UpeSale` where `user_id + product_id` | Best sale (active > completed > partially_refunded > pending_payment) |
| Installment Plan + Schedules | `$upeSale->installmentPlan->schedules` | Only if `pricing_mode = installment` |
| Access Result | `AccessEngine::computeAccess(userId, productId)` | Checks sale, subscription, temporary access, mentor badge |

---

## STEP 2: ACCESS GATE — Can User Enter the Page?

**Condition:** If ALL of these are `false` → **abort(403)**

| # | Check | What it covers |
|---|-------|----------------|
| 1 | `$accessResult->hasAccess == true` | User has active UPE sale, subscription, temporary access, or mentor badge |
| 2 | `$upeSale->pricing_mode === 'installment'` exists | User has an installment sale (even if `pending_payment` — not fully paid) |
| 3 | `$data['hasBought'] == true` | Legacy fallback — `Webinar::checkUserHasBought()` (also uses AccessEngine) |
| 4 | `$data['directAccess'] == 1` | Set by special access types (see Step 3) |
| 5 | User is `course creator`, `teacher`, or `admin` | Always allowed |

**If ANY one is true → page loads. If ALL false → 403 Forbidden.**

---

## STEP 3: THREE VARIABLES Control Content Visibility

### Variable A: `$limit` (Chapter-level gate)

**Purpose:** How many chapters (counted from top) are accessible.

**Logic:**

```
IF no installment plan (full payment / free / subscription / etc.)
    → contentPercent = 100%

IF has installment plan:
    → contentPercent = min(100, round(sum(schedules.amount_paid) / plan.total_amount * 100))

OVERRIDE to 100% IF:
    - AccessEngine says hasAccess AND no installment plan
    - AccessEngine says hasAccess AND plan status = 'completed'
    - AccessEngine says hasAccess AND accessType is 'temporary', 'mentor', or 'free'

FINAL: $limit = round(contentPercent / 100 * total_chapter_count)
```

| Purchase Type | `contentPercent` | `$limit` example (22 chapters) |
|---------------|-----------------|-------------------------------|
| Full payment (paid in full) | 100% | 22 |
| Free access | 100% | 22 |
| Temporary access (support) | 100% | 22 |
| Mentor badge | 100% | 22 |
| Installment — 100% paid | 100% | 22 |
| Installment — 75% paid | 75% | 17 |
| Installment — 50% paid | 50% | 11 |
| Installment — 34% paid (upfront only) | 34% | 7 |
| Installment — 3% paid | 3% | 1 |
| Installment — 0% paid | 0% | 0 |

---

### Variable B: `$directAccess` (Bypass flag)

**Purpose:** If `1`, ALL content items within open chapters are visible regardless of `$duedate`.

| Condition | `$directAccess` |
|-----------|----------------|
| No installment plan + has access | **1** |
| Installment plan completed + has access | **1** |
| Access type = `temporary` | **1** |
| Access type = `mentor` | **1** |
| Access type = `free` | **1** |
| Installment plan active (partially paid) | **0** |
| No access at all | **0** |

---

### Variable C: `$duedate` (Item-level date filter)

**Purpose:** Within open chapters, controls which individual items (videos, files, lessons) are playable vs locked. Only matters when `$directAccess = 0`.

**Logic:**

```
DEFAULT: $duedate = time()  (current timestamp — all existing items visible)

IF has installment plan:
    Find first schedule where:
        - status IN ('due', 'partial', 'overdue')
        - due_date is in the past
    
    IF found (overdue schedule exists):
        $duedate = overdue_schedule.due_date timestamp
    ELSE:
        $duedate = time()  (no overdue — all items visible)
```

| Installment State | `$duedate` | Effect |
|-------------------|-----------|--------|
| No installment | `now` | All items visible |
| All schedules paid or upcoming | `now` | All items visible |
| Schedule overdue (due Feb 15) | `Feb 15 timestamp` | Only items created before Feb 15 visible |
| Multiple overdue (first = Jan 10) | `Jan 10 timestamp` | Only items created before Jan 10 visible |

---

## STEP 4: BLADE RENDERING (chapter.blade.php)

```
FOR each chapter ($ci = 1, 2, 3...):

  ┌─ IF $limit >= $ci  →  CHAPTER OPEN
  │
  │   FOR each chapterItem:
  │   │
  │   ├─ IF ($chapterItem->created_at < $duedate) OR ($directAccess == 1)
  │   │     → Render "content" (FULL ACCESS: playable video/lesson)
  │   │     → Supports: session, file, textLesson, assignment, quiz
  │   │
  │   └─ ELSE
  │         → Render "content1" (LOCKED: grayed out, not playable)
  │         → Supports: session, file, textLesson only (no assignment/quiz)
  │
  └─ ELSE ($limit < $ci)  →  CHAPTER LOCKED
  
      FOR each chapterItem:
        → ALL items render as "content1" (LOCKED)
        → Supports: session, file, textLesson only
```

---

## STEP 5: OTHER CONDITIONS (Non-content)

| # | Condition | What happens |
|---|-----------|-------------|
| 1 | **Unread noticeboards** — user is not creator/teacher/admin AND has unread course noticeboards | Redirected to noticeboard page first (must read before accessing content) |
| 2 | **Assignment request** — URL has `?type=assignment&item=X` | Loads assignment-specific data alongside content |
| 3 | **Certificate** — course has certificate enabled | Loads user's certificate if earned |
| 4 | **Recommended courses** — bottom of page | Shows up to 5 active, non-private courses the user hasn't bought |

---

## SUMMARY: Decision Tree

```
User visits /course/learning/{slug}
│
├─ Course not found? → redirect back
│
├─ 403 CHECK: hasAccess OR installmentSale OR hasBought OR directAccess OR creator/teacher/admin?
│   ├─ NO → 403 Forbidden
│   └─ YES ↓
│
├─ Noticeboard redirect? (unread notices for non-admin)
│   ├─ YES → redirect to noticeboard
│   └─ NO ↓
│
├─ Calculate $limit, $directAccess, $duedate
│
└─ RENDER chapters:
    ├─ Chapter index <= $limit → OPEN
    │   ├─ Item created_at < $duedate OR directAccess=1 → PLAYABLE
    │   └─ Else → LOCKED (grayed)
    └─ Chapter index > $limit → LOCKED (all items grayed)
```

---

## KEY TAKEAWAY

Content visibility has **two layers**:

1. **Chapter-level** — controlled by payment percentage via `$limit`
2. **Item-level** (within open chapters) — controlled by overdue date via `$duedate`, bypassed by `$directAccess`

All access checks are powered by **UPE (Unified Payment Engine)** — no legacy tables are used.
