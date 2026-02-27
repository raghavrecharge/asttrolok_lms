@extends(getTemplate() .'.panel.layouts.panel_layout')
@push('styles_top')
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/vendors/chartjs/chart.min.css"/>
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/vendors/apexcharts/apexcharts.css"/>
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/vendors/owl.carousel.min.css">
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/vendors/owl.theme.default.min.css">
    <link rel="stylesheet" href="{{ config('app.js_css_url') }}/assets/default/vendors/daterangepicker/daterangepicker.min.css">
@endpush
@section('content')

<style>
/* ═══════════════════════════════════════════════════════════════
   DASHBOARD V2 — Improvised for panel width
   2-column layout: Left 8/12 | Right 4/12
   Brand: var(--primary), var(--secondary)
   ═══════════════════════════════════════════════════════════════ */

/* --- Base Card (image: white, rounded-lg, soft shadow) --- */
.db-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 1px 8px rgba(0,0,0,0.06);
    padding: 22px 24px;
    margin-bottom: 20px;
}
.db-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 16px;
}
.db-card-title {
    font-size: 15px;
    font-weight: 700;
    color: #1a1a2e;
    margin: 0;
}
.db-card-link {
    font-size: 12px;
    font-weight: 600;
    color: var(--primary);
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 3px;
}
.db-card-link:hover { text-decoration: underline; }

/* --- Welcome Hero (image: blue gradient, character right, 4 stat pills bottom) --- */
.db-welcome {
    background: linear-gradient(135deg, var(--primary) 0%, #4a6cf7 60%, #6b8cff 100%);
    border-radius: 20px;
    padding: 30px 32px 26px;
    color: #fff;
    position: relative;
    overflow: hidden;
    margin-bottom: 20px;
    min-height: 200px;
}
.db-welcome::after {
    content: '';
    position: absolute;
    right: -30px; top: -30px;
    width: 180px; height: 180px;
    background: rgba(255,255,255,0.07);
    border-radius: 50%;
}
.db-welcome::before {
    content: '';
    position: absolute;
    right: 80px; bottom: -50px;
    width: 140px; height: 140px;
    background: rgba(255,255,255,0.05);
    border-radius: 50%;
}
.db-welcome h2 {
    font-size: 22px; font-weight: 700;
    margin-bottom: 6px; color: #fff;
}
.db-welcome .db-welcome-sub {
    font-size: 13px; opacity: 0.85;
    margin-bottom: 22px;
}
.db-welcome-stats {
    display: grid; grid-template-columns: 1fr 1fr; gap: 16px;
    margin-top: 4px;
}
.db-welcome-stat {
    background: rgba(255,255,255,0.12);
    backdrop-filter: blur(4px);
    border-radius: 16px;
    padding: 18px 20px;
    display: flex; align-items: center; gap: 14px;
}
.db-welcome-stat .stat-icon-box {
    width: 52px; height: 52px; border-radius: 50%;
    background: rgba(255,255,255,0.18);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.db-welcome-stat .stat-icon-box i { color: #fff; }
.db-welcome-stat .stat-num {
    font-size: 24px; font-weight: 700; line-height: 1.1;
}
.db-welcome-stat .stat-label {
    font-size: 13px; opacity: 0.85; font-weight: 500;
}

/* --- Continue Cards (image: 2 side-by-side, rounded, image top, progress, footer) --- */
.db-continue-card {
    background: #fff;
    border: 1.5px solid #eef0f6;
    border-radius: 14px;
    overflow: hidden;
    transition: box-shadow 0.2s, transform 0.15s;
    height: 100%;
    display: flex;
    flex-direction: column;
}
.db-continue-card:hover {
    box-shadow: 0 4px 16px rgba(0,0,0,0.08);
    transform: translateY(-2px);
}
.db-continue-card .card-img-top {
    height: 110px; object-fit: cover; width: 100%;
}
.db-continue-card .card-body {
    padding: 12px 14px 8px;
    flex: 1;
}
.db-continue-card .card-title {
    font-size: 13px; font-weight: 600; color: #1a1a2e;
    display: -webkit-box; -webkit-line-clamp: 1;
    -webkit-box-orient: vertical; overflow: hidden;
    margin-bottom: 2px; line-height: 1.4;
}
.db-continue-card .card-date {
    font-size: 11px; color: #9ca3af; margin-bottom: 4px;
}
.db-continue-card .card-expiry {
    font-size: 10px; margin-bottom: 6px; display: flex; align-items: center; gap: 4px;
}
.db-continue-card .card-expiry .expiry-active {
    color: #16a34a; font-weight: 500;
}
.db-continue-card .card-expiry .expiry-expired {
    color: #dc2626; font-weight: 600;
}
.db-continue-card .card-expiry .expiry-extended {
    color: #2563eb; font-weight: 500;
}
.db-continue-card .progress {
    height: 5px; border-radius: 3px; background: #e9ecef; margin-bottom: 4px;
}
.db-continue-card .progress-bar {
    background: var(--primary); border-radius: 3px;
}
.db-continue-card .progress-text {
    font-size: 11px; color: #6b7280;
}
.db-continue-card .card-footer-row {
    display: flex; align-items: center; justify-content: space-between;
    padding: 8px 14px 12px; border-top: 1px solid #f5f5fa;
    margin-top: auto;
}
.db-continue-card .card-footer-row .footer-stat {
    font-size: 11px; color: #6b7280; display: flex; align-items: center; gap: 4px;
}
.db-continue-card .card-footer-row .btn-continue {
    font-size: 12px; font-weight: 600; color: var(--primary);
    text-decoration: none; display: flex; align-items: center; gap: 3px;
}
.db-continue-card .card-footer-row .btn-continue:hover { text-decoration: underline; }

/* --- Course Overview (image: 3 circle stats row, then list with progress) --- */
.db-course-stats {
    display: flex; gap: 0; margin-bottom: 18px;
    justify-content: space-around;
}
.db-course-stat-circle {
    display: flex; flex-direction: column; align-items: center; text-align: center;
    flex: 1;
}
.db-course-stat-circle .circle-icon {
    width: 44px; height: 44px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    margin-bottom: 8px;
}
.db-course-stat-circle .circle-icon.blue { background: #e8edfb; }
.db-course-stat-circle .circle-icon.blue i { color: var(--primary); }
.db-course-stat-circle .circle-icon.green { background: #e3f5e8; }
.db-course-stat-circle .circle-icon.green i { color: #22c55e; }
.db-course-stat-circle .circle-icon.orange { background: #fff4e5; }
.db-course-stat-circle .circle-icon.orange i { color: #f59e0b; }
.db-course-stat-circle .circle-num {
    font-size: 22px; font-weight: 700; color: #1a1a2e; line-height: 1;
}
.db-course-stat-circle .circle-label {
    font-size: 11px; color: #9ca3af; margin-top: 2px;
}
.db-course-item {
    display: flex; align-items: center;
    padding: 10px 0; border-bottom: 1px solid #f3f4f6; gap: 10px;
}
.db-course-item:last-child { border-bottom: none; }
.db-course-item .course-thumb {
    width: 40px; height: 40px; border-radius: 50%;
    object-fit: cover; flex-shrink: 0;
}
.db-course-item .course-info { flex: 1; min-width: 0; }
.db-course-item .course-name {
    font-size: 13px; font-weight: 600; color: #1a1a2e;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    text-decoration: none; display: block;
}
.db-course-item .course-name:hover { color: var(--primary); }
.db-course-item .course-teacher { font-size: 11px; color: #9ca3af; }
.db-course-item .course-progress-col { width: 130px; flex-shrink: 0; }
.db-course-item .progress {
    height: 5px; border-radius: 3px; background: #e9ecef; margin-bottom: 2px;
}
.db-course-item .progress-bar { background: var(--primary); border-radius: 3px; }
.db-course-item .progress-pct { font-size: 10px; color: #6b7280; }
.db-course-item .course-rating {
    font-size: 11px; color: #9ca3af; flex-shrink: 0; display: flex; align-items: center; gap: 2px;
}

/* --- Assignment Card (scrollable row) --- */
.db-assignment-grid {
    display: flex; gap: 14px;
    overflow-x: auto; overflow-y: hidden;
    padding-bottom: 6px;
    scroll-snap-type: x mandatory;
}
.db-assignment-grid::-webkit-scrollbar { height: 3px; }
.db-assignment-grid::-webkit-scrollbar-track { background: transparent; }
.db-assignment-grid::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 2px; }
.db-assignment-card {
    background: #fff; border: 1.5px solid #eef0f6;
    border-radius: 14px; padding: 16px;
    transition: box-shadow 0.2s;
    min-width: 200px; max-width: 240px;
    flex-shrink: 0;
    scroll-snap-align: start;
}
.db-assignment-card:hover {
    box-shadow: 0 4px 16px rgba(0,0,0,0.08);
}
.db-assignment-card .assign-title {
    font-size: 13px; font-weight: 600; color: #1a1a2e;
    margin-bottom: 8px;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.db-assignment-card .assign-badges {
    display: flex; gap: 6px; margin-bottom: 12px; flex-wrap: wrap;
}
.db-assignment-card .assign-badge {
    font-size: 10px; font-weight: 600; padding: 3px 10px;
    border-radius: 6px; display: inline-block;
}
.db-assignment-card .assign-badge.pending { background: #fff3cd; color: #856404; }
.db-assignment-card .assign-badge.passed { background: #d4edda; color: #155724; }
.db-assignment-card .assign-badge.failed { background: #f8d7da; color: #721c24; }
.db-assignment-card .assign-badge.not-submitted { background: #e2e8f0; color: #64748b; }
.db-assignment-card .assign-user {
    display: flex; align-items: center; gap: 8px;
}
.db-assignment-card .assign-user img {
    width: 28px; height: 28px; border-radius: 50%; object-fit: cover;
}
.db-assignment-card .assign-user .assign-user-name { font-size: 12px; font-weight: 500; color: #374151; }
.db-assignment-card .assign-user .assign-user-date { font-size: 10px; color: #9ca3af; }

/* --- Learning Activity --- */
.db-chart-container { height: 220px; position: relative; }
.db-chart-summary {
    display: flex; gap: 20px; margin-bottom: 14px; padding: 0 4px;
}
.db-chart-summary-item {
    display: flex; align-items: center; gap: 10px;
}
.db-chart-summary-item .summary-dot {
    width: 36px; height: 36px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.db-chart-summary-item .summary-dot.blue { background: #e8edfb; }
.db-chart-summary-item .summary-dot.blue i { color: var(--primary); }
.db-chart-summary-item .summary-dot.green { background: #e3f5e8; }
.db-chart-summary-item .summary-dot.green i { color: #22c55e; }
.db-chart-summary-item .summary-num { font-size: 18px; font-weight: 700; color: #1a1a2e; line-height: 1.1; }
.db-chart-summary-item .summary-label { font-size: 11px; color: #9ca3af; font-weight: 500; }
.db-empty-state {
    display: flex; flex-direction: column; align-items: center;
    justify-content: center; padding: 40px 20px; text-align: center;
}
.db-empty-state .empty-icon {
    width: 56px; height: 56px; border-radius: 50%;
    background: #e8edfb; display: flex; align-items: center;
    justify-content: center; margin-bottom: 14px;
}
.db-empty-state .empty-icon i { color: var(--primary); }
.db-empty-state h4 { font-size: 14px; font-weight: 600; color: #1a1a2e; margin-bottom: 6px; }
.db-empty-state p { font-size: 12px; color: #9ca3af; margin-bottom: 16px; max-width: 280px; }
.db-empty-state .empty-actions {
    display: flex; gap: 10px;
}
.db-empty-state .empty-actions a {
    font-size: 12px; font-weight: 600; padding: 7px 18px;
    border-radius: 10px; text-decoration: none;
    display: flex; align-items: center; gap: 5px;
}
.db-empty-state .empty-actions .btn-outline {
    border: 1.5px solid #e5e7eb; color: #374151; background: #fff;
}
.db-empty-state .empty-actions .btn-outline:hover { border-color: var(--primary); color: var(--primary); }
.db-empty-state .empty-actions .btn-filled {
    background: transparent; color: #374151; border: none;
}

/* --- Current Balance (image: dark card, big $ number) --- */
.db-balance-card {
    background: linear-gradient(135deg, #1a1a2e 0%, #2d2b55 100%);
    border-radius: 16px; padding: 24px;
    color: #fff; margin-bottom: 20px; position: relative; overflow: hidden;
}
.db-balance-card::after {
    content: ''; position: absolute; right: -20px; top: -20px;
    width: 100px; height: 100px; background: rgba(255,255,255,0.04);
    border-radius: 50%;
}
.db-balance-card .balance-label {
    font-size: 11px; color: rgba(255,255,255,0.6); text-transform: uppercase;
    letter-spacing: 0.5px; margin-bottom: 4px;
}
.db-balance-card .balance-date {
    font-size: 10px; color: rgba(255,255,255,0.45); margin-bottom: 10px;
}
.db-balance-card .balance-amount {
    font-size: 32px; font-weight: 700; line-height: 1.1; margin-bottom: 12px;
}
.db-balance-card .balance-sub {
    font-size: 11px; color: rgba(255,255,255,0.5);
    background: rgba(255,255,255,0.08); border-radius: 8px;
    padding: 6px 12px; display: inline-block;
}
.db-wallet-link {
    display: flex; align-items: center; justify-content: space-between;
    padding: 14px 20px; background: #fff; border-radius: 14px;
    box-shadow: 0 1px 8px rgba(0,0,0,0.06); margin-bottom: 20px;
    text-decoration: none; color: #1a1a2e; transition: box-shadow 0.15s;
}
.db-wallet-link:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.1); color: #1a1a2e; }
.db-wallet-link .wallet-title { font-size: 14px; font-weight: 600; }
.db-wallet-link .wallet-sub { font-size: 11px; color: #9ca3af; }

/* --- Support Messages (image: stat row + message list) --- */
.db-support-stats {
    display: flex; gap: 16px; margin-bottom: 16px;
}
.db-support-stat-item {
    display: flex; align-items: center; gap: 8px;
}
.db-support-stat-item .stat-num {
    font-size: 18px; font-weight: 700; color: #1a1a2e;
}
.db-support-stat-item .stat-icon {
    width: 28px; height: 28px;
}
.db-support-stat-item .stat-label { font-size: 10px; color: #9ca3af; }
.db-support-item {
    display: flex; align-items: flex-start; gap: 10px;
    padding: 12px 0; border-bottom: 1px solid #f3f4f6;
}
.db-support-item:last-child { border-bottom: none; }
.db-support-item .support-avatar {
    width: 36px; height: 36px; border-radius: 50%;
    object-fit: cover; flex-shrink: 0;
}
.db-support-item .support-info { flex: 1; min-width: 0; }
.db-support-item .support-title-row {
    display: flex; align-items: center; gap: 6px; margin-bottom: 2px;
}
.db-support-item .support-name {
    font-size: 13px; font-weight: 600; color: #1a1a2e;
}
.db-support-item .support-sender {
    font-size: 11px; color: #9ca3af;
}
.db-support-item .support-date {
    font-size: 10px; color: #c4c4c4;
}
.db-support-item .support-msg {
    font-size: 12px; color: #6b7280; margin-top: 4px;
    display: -webkit-box; -webkit-line-clamp: 2;
    -webkit-box-orient: vertical; overflow: hidden;
}
.db-support-item .support-course-tag {
    font-size: 10px; color: var(--primary); background: #eef2ff;
    padding: 2px 8px; border-radius: 6px; margin-top: 6px;
    display: inline-block;
}

/* --- Quiz Card (image: stat row + quiz list with arrow) --- */
.db-quiz-item {
    display: flex; align-items: center; gap: 10px;
    padding: 12px 0; border-bottom: 1px solid #f3f4f6;
    text-decoration: none; color: inherit;
}
.db-quiz-item:last-child { border-bottom: none; }
.db-quiz-item .quiz-avatar {
    width: 40px; height: 40px; border-radius: 50%;
    object-fit: cover; flex-shrink: 0; background: #f0f0f5;
    display: flex; align-items: center; justify-content: center;
}
.db-quiz-item .quiz-info { flex: 1; min-width: 0; }
.db-quiz-item .quiz-title {
    font-size: 13px; font-weight: 600; color: #1a1a2e;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.db-quiz-item .quiz-sub { font-size: 11px; color: #9ca3af; }
.db-quiz-item .quiz-arrow { flex-shrink: 0; color: #d1d5db; }
.db-quiz-item:hover .quiz-arrow { color: var(--primary); }

/* --- Meetings (image: stat count top, avatar list with badges) --- */
.db-meeting-count-box {
    display: flex; align-items: center; gap: 12px;
    margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid #f3f4f6;
}
.db-meeting-count-icon {
    width: 44px; height: 44px; border-radius: 12px;
    background: #eef2ff; display: flex; align-items: center; justify-content: center;
}
.db-meeting-count-icon i { color: var(--primary); }
.db-meeting-count-num { font-size: 22px; font-weight: 700; color: #1a1a2e; line-height: 1; }
.db-meeting-count-label { font-size: 11px; color: #9ca3af; }
.db-meeting-item {
    display: flex; align-items: center; gap: 10px;
    padding: 10px 0; border-bottom: 1px solid #f3f4f6;
}
.db-meeting-item:last-child { border-bottom: none; }
.db-meeting-item .meeting-avatar {
    width: 40px; height: 40px; border-radius: 50%;
    object-fit: cover; flex-shrink: 0;
}
.db-meeting-item .meeting-info { flex: 1; min-width: 0; }
.db-meeting-item .meeting-name { font-size: 13px; font-weight: 600; color: #1a1a2e; }
.db-meeting-item .meeting-time { font-size: 11px; color: #9ca3af; }
.db-meeting-status {
    font-size: 10px; font-weight: 600; padding: 3px 10px;
    border-radius: 20px; flex-shrink: 0;
}
.db-meeting-status.open { background: #d4edda; color: #155724; }
.db-meeting-status.pending { background: #fff3cd; color: #856404; }
.db-meeting-badge-row { display: flex; gap: 4px; flex-shrink: 0; }
.db-meeting-badge-row .badge-circle {
    width: 24px; height: 24px; border-radius: 50%; font-size: 10px;
    display: flex; align-items: center; justify-content: center;
    font-weight: 600;
}

/* --- Installment & Finance --- */
.db-installment-stats { display: flex; gap: 10px; margin-bottom: 14px; flex-wrap: wrap; }
.db-installment-pill {
    flex: 1; min-width: 70px; text-align: center;
    padding: 10px 6px; border-radius: 10px; background: #f8f9fc;
}
.db-installment-pill .pill-num { font-size: 18px; font-weight: 700; color: #1a1a2e; display: block; }
.db-installment-pill .pill-label { font-size: 9px; color: #6b7280; text-transform: uppercase; }
.db-installment-pill.overdue { background: #fff5f5; }
.db-installment-pill.overdue .pill-num { color: #e53e3e; }

.db-finance-table { width: 100%; font-size: 12px; }
.db-finance-table th {
    font-weight: 600; color: #6b7280; font-size: 10px;
    text-transform: uppercase; letter-spacing: 0.5px;
    padding: 6px 0; border-bottom: 2px solid #f3f4f6;
}
.db-finance-table td { padding: 8px 0; border-bottom: 1px solid #f8f9fa; color: #374151; }
.db-finance-table .amount { font-weight: 700; color: var(--primary); }

/* --- Featured Courses --- */
.db-featured-card {
    background: #fff; border: 1.5px solid #eef0f6;
    border-radius: 14px; overflow: hidden;
    transition: box-shadow 0.2s, transform 0.15s; height: 100%;
}
.db-featured-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.08); transform: translateY(-2px); }
.db-featured-card .card-img-top { height: 110px; object-fit: cover; width: 100%; }
.db-featured-card .card-body { padding: 10px 12px; }
.db-featured-card .card-title {
    font-size: 12px; font-weight: 600; color: #1a1a2e;
    display: -webkit-box; -webkit-line-clamp: 2;
    -webkit-box-orient: vertical; overflow: hidden;
    line-height: 1.35; min-height: 32px; margin-bottom: 4px;
}
.db-featured-card .card-price { font-size: 13px; font-weight: 700; color: var(--primary); }
.db-featured-card .card-price .off {
    font-size: 11px; color: #9ca3af; text-decoration: line-through;
    font-weight: 400; margin-left: 4px;
}
.db-featured-card .card-meta {
    font-size: 10px; color: #9ca3af; display: flex;
    align-items: center; gap: 3px; margin-bottom: 3px;
}
.db-buy-btn {
    font-size: 11px; font-weight: 600; color: #fff;
    background: var(--primary); padding: 5px 14px;
    border-radius: 8px; text-decoration: none;
    transition: background 0.2s, transform 0.15s;
    display: inline-flex; align-items: center;
}
.db-buy-btn:hover { background: var(--secondary); transform: translateY(-1px); color: #fff; text-decoration: none; }

/* --- Side Banner & Scrollable --- */
.db-side-banner { border-radius: 16px; overflow: hidden; margin-bottom: 20px; }
.db-side-banner img { width: 100%; border-radius: 16px; display: block; }

.db-scroll-sm { max-height: 300px; overflow-y: auto; overflow-x: hidden; }
.db-scroll-sm::-webkit-scrollbar { width: 3px; }
.db-scroll-sm::-webkit-scrollbar-track { background: transparent; }
.db-scroll-sm::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 2px; }

.db-scroll-md { max-height: 360px; overflow-y: auto; overflow-x: hidden; }
.db-scroll-md::-webkit-scrollbar { width: 3px; }
.db-scroll-md::-webkit-scrollbar-track { background: transparent; }
.db-scroll-md::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 2px; }

/* --- Responsive --- */
@media (max-width: 1199px) {
    .db-course-item .course-progress-col { width: 100px; }
}
@media (max-width: 991px) {
    .db-welcome { padding: 20px; min-height: auto; }
    .db-welcome-stats { gap: 10px; }
    .db-welcome-stat { padding: 12px 14px; }
}
@media (max-width: 576px) {
    .db-card { padding: 16px; border-radius: 12px; }
    .db-welcome { padding: 16px; border-radius: 14px; }
    .db-welcome h2 { font-size: 18px; }
    .db-welcome-stats { grid-template-columns: 1fr 1fr; gap: 8px; }
    .db-welcome-stat { padding: 10px; gap: 8px; border-radius: 12px; }
    .db-welcome-stat .stat-icon-box { width: 40px; height: 40px; }
    .db-welcome-stat .stat-num { font-size: 18px; }
    .db-welcome-stat .stat-label { font-size: 11px; }

    .db-balance-card .balance-amount { font-size: 26px; }
    
    .db-course-stats { 
        display: grid; 
        grid-template-columns: 1fr 1fr; 
        gap: 12px; 
        margin-bottom: 20px; 
    }
    .db-course-stat-circle { border: 1px solid #f3f4f6; padding: 10px; border-radius: 12px; }
    .db-course-item .course-progress-col { width: 80px; }

    /* Hide specific columns in Financial table on mobile */
    .db-finance-table th:first-child, 
    .db-finance-table td:first-child,
    .db-finance-table th:nth-child(3),
    .db-finance-table td:nth-child(3) {
        display: none !important;
    }
}
</style>

<section class="dashboard">

    {{-- ═══════════════════════════════════════════════════════════
         IMAGE ROW 1: WELCOME HERO (Left Col, spans full width of left)
         Matches: Blue gradient card with name, subtitle, 4 stat pills
         Data: $authUser, $webinarsCount, $reserveMeetingsCount, $hours
    ═══════════════════════════════════════════════════════════ --}}

    <div class="row">
        {{-- ═══════ LEFT COLUMN (col-lg-8) ═══════ --}}
        <div class="col-12 col-lg-8">

            {{-- Welcome Header --}}
            <div class="db-welcome">
                <h2>Hello, {{ $authUser->full_name }} 👋</h2>
                <div class="db-welcome-sub">Welcome! Let's begin your learning journey.</div>
                <div class="db-welcome-stats">
                    <div class="db-welcome-stat">
                        <div class="stat-icon-box"><i data-feather="book-open" width="22" height="22"></i></div>
                        <div>
                            <div class="stat-num">{{ $webinarsCount ?? 0 }}</div>
                            <div class="stat-label">Courses</div>
                        </div>
                    </div>
                    <div class="db-welcome-stat">
                        <div class="stat-icon-box"><i data-feather="video" width="22" height="22"></i></div>
                        <div>
                            <div class="stat-num">{{ $reserveMeetingsCount ?? 0 }}</div>
                            <div class="stat-label">Meetings</div>
                        </div>
                    </div>
                    <div class="db-welcome-stat">
                        <div class="stat-icon-box"><i data-feather="award" width="22" height="22"></i></div>
                        <div>
                            <div class="stat-num">{{ $certificatesCount ?? 0 }}</div>
                            <div class="stat-label">Certificates</div>
                        </div>
                    </div>
                    <div class="db-welcome-stat">
                        <div class="stat-icon-box"><i data-feather="check-circle" width="22" height="22"></i></div>
                        <div>
                            <div class="stat-num">{{ $quizzesPassedCount ?? 0 }}</div>
                            <div class="stat-label">Quizzes Passed</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Continue (image: 2 side-by-side course cards with progress) --}}
            <div class="db-card">
                <div class="db-card-header">
                    <h3 class="db-card-title">Continue</h3>
                </div>

                @if((!empty($sales) and !$sales->isEmpty()) || (!empty($orders) and !$orders->isEmpty()))
                <div class="owl-carousel owl-theme slider" id="slider1">
                    @php $cardCount = 0; @endphp

                    @if(!empty($sales) and !$sales->isEmpty())
                        @foreach($sales as $sale)
                            @php
                                $item = !empty($sale->webinar) ? $sale->webinar : $sale->bundle;
                                if(empty($item) && !empty($sale->subscription)) $item = $sale->subscription;
                            @endphp

                            @if(!empty($item) && $cardCount < 6)
                                @php
                                    $cardCount++;
                                    $hasAccessDays = !empty($sale->webinar) && !empty($sale->webinar->access_days) && $sale->webinar->access_days > 0;
                                    $isSubscription = !empty($sale->subscription_id) && !empty($sale->subscription);
                                    $expiryTimestamp = null;
                                    $isExpired = false;
                                    $extensionExpiry = null;
                                    $hasActiveExtension = false;
                                    $subAccessTill = null;
                                    $subExpired = false;

                                    if ($hasAccessDays) {
                                        $expiryTimestamp = $sale->webinar->getExpiredAccessDays($sale->created_at, $sale->gift_id ?? null);
                                        $isExpired = $expiryTimestamp < time();
                                        $wId = $sale->webinar->id;
                                        if (!empty($extendedAccesses[$wId]) && $extendedAccesses[$wId] > $expiryTimestamp) {
                                            $extensionExpiry = $extendedAccesses[$wId];
                                            $hasActiveExtension = $extensionExpiry > time();
                                            if ($hasActiveExtension) $isExpired = false;
                                        }
                                    }

                                    if ($isSubscription && !empty($subscriptionAccess)) {
                                        $subAccess = $subscriptionAccess->firstWhere('subscription_id', $sale->subscription_id);
                                        if (!empty($subAccess) && !empty($subAccess->access_till_date)) {
                                            $subAccessTill = is_numeric($subAccess->access_till_date) ? (int)$subAccess->access_till_date : strtotime($subAccess->access_till_date);
                                            $subExpired = $subAccessTill && $subAccessTill < time();
                                        }
                                    }
                                @endphp
                                <div>
                                    <div class="db-continue-card mx-1">
                                        @php
                                            $imgSrc = '';
                                            try { $imgSrc = $item->getImage(); } catch (\Throwable $e) {}
                                            $imgFull = '';
                                            if (!empty($imgSrc)) {
                                                $imgFull = (str_starts_with($imgSrc, 'http') ? '' : config('app.img_dynamic_url')) . $imgSrc;
                                            }
                                        @endphp
                                        @if(!empty($imgFull) && !empty($item->thumbnail))
                                            <img loading="lazy" src="{{ $imgFull }}" class="card-img-top" alt="{{ $item->title }}">
                                        @else
                                            <div class="card-img-top" style="height:110px;background:linear-gradient(135deg,var(--primary),var(--secondary));display:flex;align-items:center;justify-content:center;">
                                                <i data-feather="{{ $isSubscription ? 'play-circle' : 'book-open' }}" width="32" height="32" style="color:rgba(255,255,255,0.7);"></i>
                                            </div>
                                        @endif
                                        <div class="card-body">
                                            <a href="{{ $item->getUrl() }}" class="text-decoration-none">
                                                <div class="card-title">{{ $item->title }}</div>
                                            </a>
                                            <div class="card-date">{{ dateTimeFormat($sale->created_at, 'j M Y') }}</div>

                                            @if($isSubscription && $subAccessTill)
                                                <div class="card-expiry">
                                                    @if($subExpired)
                                                        <span class="expiry-expired">⛔ Access Expired</span>
                                                    @else
                                                        <span class="expiry-active">✓ Access till: {{ date('j M Y', (int)$subAccessTill) }}</span>
                                                    @endif
                                                </div>
                                            @elseif($hasAccessDays)
                                                <div class="card-expiry">
                                                    @if($isExpired && !$hasActiveExtension)
                                                        <span class="expiry-expired">⛔ Access Expired</span>
                                                    @elseif($hasActiveExtension)
                                                        <span class="expiry-extended">🔄 Extended till {{ date('j M Y', (int)$extensionExpiry) }}</span>
                                                    @else
                                                        <span class="expiry-active">✓ Expires: {{ date('j M Y', (int)$expiryTimestamp) }}</span>
                                                    @endif
                                                </div>
                                            @endif

                                            @if(!empty($sale->webinar) && method_exists($item, 'checkShowProgress') && $item->checkShowProgress())
                                                <div class="progress">
                                                    <div class="progress-bar" style="width: {{ $item->getProgress() }}%"></div>
                                                </div>
                                                <div class="progress-text">{{ $item->getProgress() }}% Completed</div>
                                            @endif
                                        </div>
                                        <div class="card-footer-row">
                                            <span class="footer-stat"><i data-feather="layers" width="12" height="12"></i> {{ $item->files_count ?? 0 }} lessons</span>
                                            @if(($isExpired && !$hasActiveExtension) || ($isSubscription && $subExpired))
                                                <span style="font-size:11px;color:#dc2626;font-weight:600;">Expired</span>
                                            @elseif(!empty($sale->webinar))
                                                <a href="{{ $item->getLearningPageUrl() }}" target="_blank" class="btn-continue">Continue <i data-feather="arrow-right" width="13" height="13"></i></a>
                                            @elseif(!empty($sale->bundle))
                                                <a href="/panel/bundle/{{ $sale->bundle->id }}" target="_blank" class="btn-continue">View List <i data-feather="arrow-right" width="13" height="13"></i></a>
                                            @elseif($isSubscription)
                                                <a href="{{ $item->getLearningPageUrl() }}" target="_blank" class="btn-continue">Continue <i data-feather="arrow-right" width="13" height="13"></i></a>
                                            @else
                                                <a href="{{ $item->getUrl() }}" class="btn-continue">Continue <i data-feather="arrow-right" width="13" height="13"></i></a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    @endif

                    @if(!empty($orders) and !$orders->isEmpty())
                        @foreach($orders as $sale)
                            @php $item = !empty($sale->webinar) ? $sale->webinar : $sale->bundle; @endphp
                            @if(!empty($item) && $cardCount < 8)
                                @php
                                    $cardCount++;
                                    $hasAccessDays = !empty($sale->webinar) && !empty($sale->webinar->access_days) && $sale->webinar->access_days > 0;
                                    $expiryTimestamp = null;
                                    $isExpired = false;
                                    $extensionExpiry = null;
                                    $hasActiveExtension = false;

                                    if ($hasAccessDays) {
                                        $expiryTimestamp = $sale->webinar->getExpiredAccessDays($sale->created_at, null);
                                        $isExpired = $expiryTimestamp < time();
                                        $wId = $sale->webinar->id;
                                        if (!empty($extendedAccesses[$wId]) && $extendedAccesses[$wId] > $expiryTimestamp) {
                                            $extensionExpiry = $extendedAccesses[$wId];
                                            $hasActiveExtension = $extensionExpiry > time();
                                            if ($hasActiveExtension) $isExpired = false;
                                        }
                                    }
                                @endphp
                                <div>
                                    <div class="db-continue-card mx-1">
                                        <img loading="lazy" src="{{ config('app.img_dynamic_url') }}{{ $item->getImage() }}" class="card-img-top" alt="{{ $item->title }}">
                                        <div class="card-body">
                                            <a href="{{ $item->getUrl() }}" class="text-decoration-none">
                                                <div class="card-title">{{ $item->title }}</div>
                                            </a>
                                            <div class="card-date">{{ dateTimeFormat($sale->created_at, 'j M Y') }}</div>

                                            @if($hasAccessDays)
                                                <div class="card-expiry">
                                                    @if($isExpired && !$hasActiveExtension)
                                                        <span class="expiry-expired">⛔ Access Expired</span>
                                                    @elseif($hasActiveExtension)
                                                        <span class="expiry-extended">🔄 Extended till {{ date('j M Y', (int)$extensionExpiry) }}</span>
                                                    @else
                                                        <span class="expiry-active">✓ Expires: {{ date('j M Y', (int)$expiryTimestamp) }}</span>
                                                    @endif
                                                </div>
                                            @endif

                                            @if(!empty($sale->webinar) && $item->checkShowProgress())
                                                <div class="progress">
                                                    <div class="progress-bar" style="width: {{ $item->getProgress() }}%"></div>
                                                </div>
                                                <div class="progress-text">{{ $item->getProgress() }}% Completed</div>
                                            @endif
                                        </div>
                                        <div class="card-footer-row">
                                            <span class="footer-stat"><i data-feather="layers" width="12" height="12"></i> {{ $item->files_count ?? 0 }} lessons</span>
                                            @if($isExpired && !$hasActiveExtension)
                                                <span style="font-size:11px;color:#dc2626;font-weight:600;">Expired</span>
                                            @else
                                                <a href="{{ $item->getLearningPageUrl() }}" target="_blank" class="btn-continue">Continue <i data-feather="arrow-right" width="13" height="13"></i></a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    @endif
                </div>
                @else
                    @include(getTemplate() . '.includes.no-result',[
                        'file_name' => 'student.png',
                        'title' => trans('panel.no_result_purchases'),
                        'hint' => trans('panel.no_result_purchases_hint'),
                        'btn' => ['url' => '/classes?sort=newest','text' => trans('panel.start_learning')]
                    ])
                @endif
            </div>

            {{-- Course Overview (image: 3 circle stat icons + course list) --}}
            <div class="db-card">
                <div class="db-card-header">
                    <h3 class="db-card-title">Course Overview</h3>
                </div>

                <div class="db-course-stats">
                    <div class="db-course-stat-circle">
                        <div class="circle-icon blue"><i data-feather="book" width="20" height="20"></i></div>
                        <div class="circle-label">Total Courses</div>
                        <div class="circle-num">{{ $webinarsCount ?? 0 }}</div>
                    </div>
                    <div class="db-course-stat-circle">
                        <div class="circle-icon green"><i data-feather="check-circle" width="20" height="20"></i></div>
                        <div class="circle-label">Completed Courses</div>
                        <div class="circle-num">{{ $finishedInstallmentsCount ?? 0 }}</div>
                    </div>
                    <div class="db-course-stat-circle">
                        <div class="circle-icon orange"><i data-feather="star" width="20" height="20"></i></div>
                        <div class="circle-label">Open Courses</div>
                        <div class="circle-num">{{ $openInstallmentsCount ?? 0 }}</div>
                    </div>
                </div>

                <div class="db-scroll-sm">
                    @if(!empty($sales) and !$sales->isEmpty())
                        @foreach($sales->take(6) as $sale)
                            @php
                                $item = !empty($sale->webinar) ? $sale->webinar : $sale->bundle;
                                if(empty($item) && !empty($sale->subscription)) $item = $sale->subscription;
                            @endphp
                            @if(!empty($item))
                                <div class="db-course-item">
                                    <img loading="lazy" src="{{ config('app.img_dynamic_url') }}{{ $item->getImage() }}" class="course-thumb" alt="">
                                    <div class="course-info">
                                        <a href="{{ $item->getUrl() }}" class="course-name">{{ $item->title }}</a>
                                        <span class="course-teacher">By {{ $item->teacher->full_name ?? '' }}</span>
                                    </div>
                                    <div class="course-progress-col">
                                        @if(!empty($sale->webinar) && $item->checkShowProgress())
                                            @php $prog = $item->getProgress(); @endphp
                                            <div class="progress-pct" style="margin-bottom:2px;">{{ $prog }}% <span style="color:#c4c4c4;">Progress</span></div>
                                            <div class="progress">
                                                <div class="progress-bar" style="width: {{ $prog }}%"></div>
                                            </div>
                                        @else
                                            <span class="progress-pct">0% Progress</span>
                                        @endif
                                    </div>
                                    <!-- <div class="course-rating">
                                        <i data-feather="star" width="11" height="11" style="color: #f59e0b; fill: #f59e0b;"></i>
                                        {{ number_format($item->getRate() ?? 0, 1) }}
                                    </div> -->
                                </div>
                            @endif
                        @endforeach
                    @endif
                </div>
            </div>

            {{-- Suggested Courses carousel --}}
            @if(!empty($featureWebinars) && count($featureWebinars) > 0)
            <div class="db-card">
                <div class="db-card-header">
                    <h3 class="db-card-title">Suggested Courses</h3>
                    <a href="/classes" class="db-card-link">View All <i data-feather="arrow-right" width="13" height="13"></i></a>
                </div>
                <div class="owl-carousel owl-theme slider" id="slider2">
                    @foreach($featureWebinars->take(10) as $webinar)
                        <div>
                            <div class="db-featured-card mx-1">
                                <a href="{{ $webinar->getUrl() }}">
                                    <img loading="lazy" src="{{ config('app.img_dynamic_url') }}{{ $webinar->getImage() }}" class="card-img-top" alt="{{ $webinar->title }}">
                                </a>
                                <div class="card-body">
                                    <div class="card-meta">
                                        <i data-feather="user" width="10" height="10"></i>
                                        {{ $webinar->teacher->full_name }}
                                    </div>
                                    <a href="{{ $webinar->getUrl() }}" class="text-decoration-none">
                                        <div class="card-title">{{ clean($webinar->title, 'title') }}</div>
                                    </a>
                                    <div class="card-price">
                                        @if(!empty($webinar->price) and $webinar->price > 0)
                                            @if($webinar->bestTicket() < $webinar->price)
                                                {{ handlePrice($webinar->bestTicket(), true, true, false, null, true) }}
                                                <span class="off">{{ handlePrice($webinar->price, true, true, false, null, true) }}</span>
                                            @else
                                                {{ handlePrice($webinar->price, true, true, false, null, true) }}
                                            @endif
                                        @else
                                            <span style="color: #2e7d32;">{{ trans('public.free') }}</span>
                                        @endif
                                    </div>
                                    <div style="display:flex;align-items:center;justify-content:space-between;margin-top:8px;">
                                        <div class="card-meta mb-0">
                                            <i data-feather="clock" width="10" height="10"></i>
                                            {{ convertMinutesToHourAndMinute($webinar->duration) }}h
                                        </div>
                                        <a href="{{ $webinar->getUrl() }}" class="db-buy-btn">Buy Now</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif


        </div>

        {{-- ═══════ RIGHT COLUMN (col-lg-4) ═══════ --}}
        <div class="col-12 col-lg-4">

            {{-- Current Balance (image: dark card with big $ amount) --}}
            <div class="db-balance-card">
                <div class="balance-label">Current Balance</div>
                <div class="balance-date">{{ date('d M Y h:i A') }}</div>
                <div class="balance-amount">{{ handlePrice($authUser->getAccountingBalance()) }}</div>
                <div class="balance-sub">{{ handlePrice($authUser->getAccountingBalance()) }} Available to use</div>
            </div>

            {{-- Wallet (image: simple link row) --}}
            <a href="/panel/financial/account" class="db-wallet-link">
                <div>
                    <div class="wallet-title">Wallet</div>
                    <div class="wallet-sub">Manage your wallet</div>
                </div>
                <i data-feather="chevron-right" width="18" height="18" style="color: #9ca3af;"></i>
            </a>

            {{-- UPE Subscription Status --}}
            @if(!empty($upeSubscription))
            <div class="db-card" style="border-left: 3px solid var(--primary);">
                <div class="db-card-header">
                    <h3 class="db-card-title">Subscription</h3>
                    <span class="badge badge-success" style="font-size:10px;padding:3px 10px;border-radius:8px;">Active</span>
                </div>
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:8px;">
                    <div style="width:40px;height:40px;border-radius:10px;background:#e0f2fe;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i data-feather="refresh-cw" width="18" height="18" style="color:var(--primary);"></i>
                    </div>
                    <div>
                        <div style="font-size:13px;font-weight:600;color:#1a1a2e;">{{ $upeSubscription->product->name ?? 'Subscription' }}</div>
                        <div style="font-size:11px;color:#6b7280;">{{ handlePrice($upeSubscription->billing_amount) }} / {{ $upeSubscription->billing_interval }}</div>
                    </div>
                </div>
                @if($upeSubscription->current_period_end)
                    <div style="font-size:11px;color:#6b7280;padding-top:6px;border-top:1px solid #f3f4f6;">
                        Renews {{ $upeSubscription->current_period_end->format('j M Y') }}
                    </div>
                @endif
            </div>
            @endif

            {{-- Support Messages (image: stat row + conversation list) --}}
            <div class="db-card">
                <div class="db-card-header">
                    <h3 class="db-card-title">Support messages</h3>
                    <a href="/panel/support/newsuportforasttrolok" class="db-card-link"><i data-feather="send" width="14" height="14"></i></a>
                </div>

                <div class="db-support-stats">
                    <div class="db-support-stat-item">
                        <span class="stat-num">{{ $openSupportsCount ?? 0 }}</span>
                        <span style="font-size:16px;">🔓</span>
                        <div>
                            <div class="stat-label">Open Tickets</div>
                        </div>
                    </div>
                    <div class="db-support-stat-item">
                        <span class="stat-num">{{ $supportsCount ?? 0 }}</span>
                        <span style="font-size:16px;">📨</span>
                        <div>
                            <div class="stat-label">Total Tickets</div>
                        </div>
                    </div>
                </div>

                @if(!empty($supports) and !$supports->isEmpty())
                    <div class="db-scroll-sm">
                        @foreach($supports->take(4) as $support)
                            <a href="/panel/supports/{{ $support->id }}" class="db-support-item text-decoration-none">
                                <img loading="lazy" src="{{ config('app.img_dynamic_url') }}{{ (!empty($support->webinar) && $support->webinar->teacher_id != $authUser->id) ? $support->webinar->teacher->getAvatar() : $support->user->getAvatar() }}" class="support-avatar" alt="">
                                <div class="support-info">
                                    <div class="support-title-row">
                                        <span class="support-name">{{ $support->title }}</span>
                                    </div>
                                    <div class="support-sender">
                                        {{ (!empty($support->webinar) && $support->webinar->teacher_id != $authUser->id) ? $support->webinar->teacher->full_name : $support->user->full_name }}
                                        &middot;
                                        {{ (!empty($support->conversations) && count($support->conversations)) ? dateTimeFormat($support->conversations->first()->created_at, 'j M Y H:i') : dateTimeFormat($support->created_at, 'j M Y H:i') }}
                                    </div>
                                    @if(!empty($support->webinar))
                                        <span class="support-course-tag">{{ truncate($support->webinar->title, 30) }}</span>
                                    @endif
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="db-empty-state" style="padding: 20px;">
                        <div class="empty-icon"><i data-feather="message-circle" width="22" height="22"></i></div>
                        <p>No support messages yet</p>
                    </div>
                @endif
            </div>

            {{-- Meetings --}}
            <div class="db-card">
                <div class="db-card-header">
                    <h3 class="db-card-title">Meetings</h3>
                </div>

                @php $finishedMeetingsCount = ($totalReserveCount ?? 0) - ($openReserveCount ?? 0); @endphp
                <div style="display:flex;gap:12px;margin-bottom:14px;">
                    <div class="db-meeting-count-box" style="flex:1;margin-bottom:0;">
                        <div class="db-meeting-count-icon"><i data-feather="video" width="18" height="18"></i></div>
                        <div>
                            <div class="db-meeting-count-num">{{ $openReserveCount ?? 0 }}</div>
                            <div class="db-meeting-count-label">Open</div>
                        </div>
                    </div>
                    <div class="db-meeting-count-box" style="flex:1;margin-bottom:0;background:#f0fdf4;border-color:#bbf7d0;">
                        <div class="db-meeting-count-icon" style="background:#dcfce7;"><i data-feather="check-circle" width="18" height="18" style="color:#22c55e;"></i></div>
                        <div>
                            <div class="db-meeting-count-num">{{ $finishedMeetingsCount >= 0 ? $finishedMeetingsCount : 0 }}</div>
                            <div class="db-meeting-count-label">Finished</div>
                        </div>
                    </div>
                </div>

                @if(!empty($reserveMeetings) and !$reserveMeetings->isEmpty())
                    <div class="db-scroll-sm">
                        @foreach($reserveMeetings->take(5) as $reserveMeeting)
                            <div class="db-meeting-item">
                                @if(!empty($reserveMeeting->meeting) && !empty($reserveMeeting->meeting->creator))
                                    <img loading="lazy" src="{{ config('app.img_dynamic_url') }}{{ $reserveMeeting->meeting->creator->getAvatar() }}" class="meeting-avatar" alt="">
                                    <div class="meeting-info">
                                        <div class="meeting-name">{{ $reserveMeeting->meeting->creator->full_name }}</div>
                                        <div class="meeting-time">
                                            @if(!empty($reserveMeeting->meetingTime))
                                                {{ $reserveMeeting->meetingTime->day_label }} | {{ $reserveMeeting->meetingTime->time }}
                                            @endif
                                        </div>
                                    </div>
                                    <span class="db-meeting-status {{ $reserveMeeting->status }}">
                                        {{ ucfirst($reserveMeeting->status) }}
                                    </span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="db-empty-state" style="padding: 20px;">
                        <div class="empty-icon"><i data-feather="video" width="22" height="22"></i></div>
                        <p>No meetings yet</p>
                    </div>
                @endif
            </div>

            {{-- My Assignments (moved to right column) --}}
            <div class="db-card">
                <div class="db-card-header">
                    <h3 class="db-card-title">My Assignments</h3>
                    <a href="/panel/assignments/my-assignments" class="db-card-link">View All <i data-feather="arrow-right" width="13" height="13"></i></a>
                </div>

                @if(!empty($sales) and !$sales->isEmpty())
                    @php $assignmentCards = collect(); @endphp
                    @foreach($sales->take(4) as $sale)
                        @php
                            $aItem = !empty($sale->webinar) ? $sale->webinar : null;
                            if(!empty($aItem)) { $assignmentCards->push(['item' => $aItem, 'sale' => $sale]); }
                        @endphp
                    @endforeach

                    @if($assignmentCards->count() > 0)
                        <div class="db-scroll-sm">
                            @foreach($assignmentCards->take(4) as $ac)
                                <a href="/panel/assignments/my-assignments" class="db-course-item text-decoration-none" style="display:flex;">
                                    <div style="width:40px;height:40px;border-radius:10px;background:#fef3c7;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                        <i data-feather="clipboard" width="18" height="18" style="color:#d97706;"></i>
                                    </div>
                                    <div class="course-info">
                                        <span class="course-name">{{ $ac['item']->title }}</span>
                                        <span class="course-teacher">{{ $ac['item']->teacher->full_name }} · {{ dateTimeFormat($ac['sale']->created_at, 'j M Y') }}</span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="db-empty-state" style="padding: 20px;">
                            <div class="empty-icon"><i data-feather="clipboard" width="22" height="22"></i></div>
                            <p>Visit your assignments page to view all tasks.</p>
                        </div>
                    @endif
                @else
                    <div class="db-empty-state" style="padding: 20px;">
                        <div class="empty-icon"><i data-feather="clipboard" width="22" height="22"></i></div>
                        <p>No assignments yet.</p>
                    </div>
                @endif
            </div>

            {{-- UPE Installments Overview --}}
            @if(!empty($upeUpcomingSchedules) && $upeUpcomingSchedules->count() > 0)
            <div class="db-card">
                <div class="db-card-header">
                    <h3 class="db-card-title">Installments</h3>
                    <a href="/panel/upe/installments" class="db-card-link">Details</a>
                </div>

                <div class="db-installment-stats">
                    <div class="db-installment-pill">
                        <span class="pill-num">{{ $upeInstallmentPlans->count() }}</span>
                        <span class="pill-label">Active Plans</span>
                    </div>
                    <div class="db-installment-pill overdue">
                        <span class="pill-num">{{ $upeOverdueCount ?? 0 }}</span>
                        <span class="pill-label">Overdue</span>
                    </div>
                </div>

                <div class="db-scroll-sm">
                    @foreach($upeUpcomingSchedules->take(4) as $schedule)
                        <div class="db-course-item">
                            <div style="width:40px;height:40px;border-radius:10px;background:{{ $schedule->status === 'overdue' ? '#fee2e2' : '#e0f2fe' }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <i data-feather="{{ $schedule->status === 'overdue' ? 'alert-circle' : 'calendar' }}" width="18" height="18" style="color:{{ $schedule->status === 'overdue' ? '#dc2626' : 'var(--primary)' }};"></i>
                            </div>
                            <div class="course-info">
                                <span class="course-name" style="cursor:default;">
                                    {{ !empty($schedule->plan) && !empty($schedule->plan->sale) && !empty($schedule->plan->sale->product) ? $schedule->plan->sale->product->name : 'Installment #'.$schedule->plan_id }}
                                </span>
                                <span class="course-teacher">
                                    @if($schedule->status === 'overdue')
                                        <span class="text-danger font-weight-600">Overdue — {{ handlePrice($schedule->amount_due - $schedule->amount_paid, false) }}</span>
                                    @else
                                        Due {{ $schedule->due_date->format('j M Y') }} — {{ handlePrice($schedule->amount_due - $schedule->amount_paid, false) }}
                                    @endif
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @elseif(!empty($orders) and !$orders->isEmpty())
            <div class="db-card">
                <div class="db-card-header">
                    <h3 class="db-card-title">Installments</h3>
                    <a href="/panel/upe/installments" class="db-card-link">Details</a>
                </div>
                <div class="db-installment-stats">
                    <div class="db-installment-pill">
                        <span class="pill-num">{{ $openInstallmentsCount ?? 0 }}</span>
                        <span class="pill-label">Open</span>
                    </div>
                    <div class="db-installment-pill overdue">
                        <span class="pill-num">{{ $overdueInstallmentsCount ?? 0 }}</span>
                        <span class="pill-label">Overdue</span>
                    </div>
                </div>
                <div class="db-scroll-sm">
                    @foreach($orders->take(3) as $order)
                        @php $orderItem = !empty($order->webinar) ? $order->webinar : $order->bundle; @endphp
                        @if(!empty($orderItem))
                            <div class="db-course-item">
                                <img loading="lazy" src="{{ config('app.img_dynamic_url') }}{{ $orderItem->getImage() }}" class="course-thumb" alt="">
                                <div class="course-info">
                                    <a href="/panel/financial/installments/{{ $order->id }}/details" class="course-name">{{ $orderItem->title }}</a>
                                    <span class="course-teacher">
                                        @if($order->has_overdue)
                                            <span class="text-danger font-weight-600">Overdue ({{ $order->overdue_count }})</span>
                                        @else
                                            Remaining: {{ $order->remained_installments_count ?? 0 }}
                                        @endif
                                    </span>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Side Banner --}}
            @if(!empty($sidebanner['studentdashboard']['image']))
            <!-- <div class="db-side-banner">
                <a href="{{ $sidebanner['studentdashboard']['link'] }}">
                    <img loading="lazy" src="{{ config('app.img_dynamic_url') }}{{ $sidebanner['studentdashboard']['image'] }}" alt="Banner">
                </a>
            </div> -->
            @endif

        </div>

    </div>

    {{-- ═══════ FULL-WIDTH ROW: Financial Documents + UPE Ledger ═══════ --}}
    <div class="row">
        <div class="col-12">
            <div class="db-card">
                <div class="db-card-header">
                    <h3 class="db-card-title">{{ trans('financial.financial_documents') }}</h3>
                    <a href="/panel/financial/summary" class="db-card-link">View All</a>
                </div>

                @if(count($amount_paid ?? []) > 0)
                    <div class="db-scroll-md">
                        <table class="db-finance-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th class="text-left">{{ trans('public.title') }}</th>
                                    <th class="text-center">Type</th>
                                    <th class="text-center">{{ trans('panel.amount') }} ({{ $currency ?? '₹' }})</th>
                                    <th class="text-center">{{ trans('public.date') }}</th>
                                    <th class="text-center">{{ trans('admin/main.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(array_slice($amount_paid ?? [], 0, 10) as $row)
                                    <tr>
                                        <td><span>#{{ $row[3] ?? '' }}</span></td>
                                        <td><span>{{ $row[2] ?? 'N/A' }}</span></td>
                                        <td class="text-center">
                                            @if(($row[5] ?? '') == 'part')
                                                <span>Installment payment</span>
                                            @elseif(($row[5] ?? '') == 'course')
                                                @if(($row[6] ?? '') == 'installment_payment')
                                                    <span>Installment payment</span>
                                                @else
                                                    <span>Course</span>
                                                @endif
                                            @elseif(($row[5] ?? '') == 'meeting')
                                                <span>Meeting</span>
                                            @elseif(($row[5] ?? '') == 'subscription')
                                                <span>Subscription</span>
                                            @elseif(($row[5] ?? '') == 'bundle')
                                                <span>Bundle</span>
                                            @elseif(($row[5] ?? '') == 'product')
                                                <span>Product</span>
                                            @endif
                                        </td>
                                        <td class="text-center"><span class="font-weight-bold" style="color:var(--primary);">{{ handlePrice($row[0] ?? 0, false) }}</span></td>
                                        <td class="text-center">{{ dateTimeFormat($row[1], 'j M Y') }}</td>
                                        <td class="text-center">
                                            @if(($row[5] ?? '') == 'part')
                                                <a href="/panel/webinars/{{ $row[4] }}/part/{{ $row[3] }}/invoice" target="_blank" title="{{ trans('admin/main.invoice') }}"><img loading="lazy" src="https://storage.googleapis.com/astrolok/store/1/prints.png" width="25" alt="invoice"></a>
                                            @elseif(($row[5] ?? '') == 'course')
                                                <a href="/panel/webinars/{{ $row[4] }}/sale/{{ $row[3] }}/invoice" target="_blank" title="{{ trans('admin/main.invoice') }}"><img loading="lazy" src="https://storage.googleapis.com/astrolok/store/1/prints.png" width="25" alt="invoice"></a>
                                            @elseif(($row[5] ?? '') == 'meeting')
                                                <a href="/panel/webinars/{{ $row[4] }}/meeting/{{ $row[3] }}/invoice" target="_blank" title="{{ trans('admin/main.invoice') }}"><img loading="lazy" src="https://storage.googleapis.com/astrolok/store/1/prints.png" width="25" alt="invoice"></a>
                                            @elseif(($row[5] ?? '') == 'subscription')
                                                <a href="/panel/webinars/{{ $row[4] }}/subscription/{{ $row[3] }}/invoice" target="_blank" title="{{ trans('admin/main.invoice') }}"><img loading="lazy" src="https://storage.googleapis.com/astrolok/store/1/prints.png" width="25" alt="invoice"></a>
                                            @elseif(($row[5] ?? '') == 'bundle')
                                                <a href="/panel/webinars/{{ $row[4] }}/bundle/{{ $row[3] }}/invoice" target="_blank" title="{{ trans('admin/main.invoice') }}"><img loading="lazy" src="https://storage.googleapis.com/astrolok/store/1/prints.png" width="25" alt="invoice"></a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="db-empty-state" style="padding: 20px;">
                        <div class="empty-icon"><i data-feather="file-text" width="22" height="22"></i></div>
                        <p>No financial documents yet.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

</section>

{{-- Offline Modal (preserved from original) --}}
<div class="d-none" id="iNotAvailableModal">
    <div class="offline-modal">
        <h3 class="section-title after-line">{{ trans('panel.offline_title') }}</h3>
        <p class="mt-20 font-16 text-gray">{{ trans('panel.offline_hint') }}</p>
        <div class="form-group mt-15">
            <label>{{ trans('panel.offline_message') }}</label>
            <textarea name="message" rows="4" class="form-control">{{ $authUser->offline_message }}</textarea>
            <div class="invalid-feedback"></div>
        </div>
        <div class="mt-30 d-flex align-items-center justify-content-end">
            <button type="button" class="js-save-offline-toggle btn btn-primary btn-sm">{{ trans('public.save') }}</button>
            <button type="button" class="btn btn-danger ml-10 close-swl btn-sm">{{ trans('public.close') }}</button>
        </div>
    </div>
</div>

<div class="d-none" id="noticeboardMessageModal">
    <div class="text-center">
        <h3 class="modal-title font-20 font-weight-500 text-dark-blue"></h3>
        <span class="modal-time d-block font-12 text-gray mt-25"></span>
        <p class="modal-message font-weight-500 text-gray mt-4"></p>
    </div>
</div>

@endsection

@push('scripts_bottom')
    <script defer src="{{ config('app.js_css_url') }}/assets/default/vendors/apexcharts/apexcharts.min.js"></script>
    <script defer src="{{ config('app.js_css_url') }}/assets/default/vendors/chartjs/chart.min.js"></script>
    <script defer src="{{ config('app.js_css_url') }}/assets/default/vendors/owl.carousel.min.js"></script>
    <script defer src="{{ config('app.js_css_url') }}/assets/default/vendors/modules-slider.js"></script>

    <script defer>
        var offlineSuccess = '{{ trans('panel.offline_success') }}';
        var $chartDataMonths = @json($monthlyChart['months']);
        var $chartData = @json($monthlyChart['data']);
    </script>

    <script defer src="{{ config('app.js_css_url') }}/assets/default/js/panel/dashboard.min.js"></script>
@endpush

@push('scripts_bottom')
    <script defer>
        var instructor_contact_information_lang = '{{ trans('panel.instructor_contact_information') }}';
        var student_contact_information_lang = '{{ trans('panel.student_contact_information') }}';
        var email_lang = '{{ trans('public.email') }}';
        var phone_lang = '{{ trans('public.phone') }}';
        var location_lang = '{{ trans('update.location') }}';
        var close_lang = '{{ trans('public.close') }}';
        var finishReserveHint = '{{ trans('meeting.finish_reserve_modal_hint') }}';
        var finishReserveConfirm = '{{ trans('meeting.finish_reserve_modal_confirm') }}';
        var finishReserveCancel = '{{ trans('meeting.finish_reserve_modal_cancel') }}';
        var finishReserveTitle = '{{ trans('meeting.finish_reserve_modal_title') }}';
        var finishReserveSuccess = '{{ trans('meeting.finish_reserve_modal_success') }}';
        var finishReserveSuccessHint = '{{ trans('meeting.finish_reserve_modal_success_hint') }}';
        var finishReserveFail = '{{ trans('meeting.finish_reserve_modal_fail') }}';
        var finishReserveFailHint = '{{ trans('meeting.finish_reserve_modal_fail_hint') }}';
    </script>
    <script defer src="{{ config('app.js_css_url') }}/assets/default/vendors/daterangepicker/daterangepicker.min.js"></script>
    <script defer src="{{ config('app.js_css_url') }}/assets/default/js/panel/meeting/contact-info.min.js"></script>
    <script defer src="{{ config('app.js_css_url') }}/assets/default/js/panel/meeting/reserve_meeting.min.js"></script>
@endpush

@push('scripts_bottom')
    <script>
        // Monthly Learning Activity Chart — wait for deferred scripts to load
        window.addEventListener('load', function() {
            "use strict";
            if (typeof Chart === 'undefined') return;

            var ctx = document.getElementById('monthlyChart');
            if (!ctx) return;

            var gradient = ctx.getContext('2d').createLinearGradient(0, 0, 0, 220);
            gradient.addColorStop(0, 'rgba(74, 108, 247, 0.35)');
            gradient.addColorStop(1, 'rgba(74, 108, 247, 0.02)');

            new Chart(ctx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: $chartDataMonths,
                    datasets: [{
                        label: 'Courses Enrolled',
                        data: $chartData,
                        backgroundColor: gradient,
                        borderColor: 'rgba(74, 108, 247, 1)',
                        borderWidth: 2,
                        borderRadius: 10,
                        borderSkipped: false,
                        barThickness: 28,
                        hoverBackgroundColor: 'rgba(74, 108, 247, 0.45)',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#1a1a2e',
                            titleFont: { size: 12, weight: '600' },
                            bodyFont: { size: 12 },
                            cornerRadius: 10,
                            padding: 12,
                            displayColors: false,
                            callbacks: {
                                label: function(context) {
                                    var val = context.parsed.y;
                                    return val + ' course' + (val !== 1 ? 's' : '') + ' enrolled';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(0,0,0,0.04)', drawBorder: false },
                            ticks: {
                                font: { size: 11 }, color: '#9ca3af',
                                stepSize: 1,
                                callback: function(value) { if (Number.isInteger(value)) return value; }
                            }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { font: { size: 11 }, color: '#9ca3af' }
                        }
                    }
                }
            });
        });

        // Force Suggested Courses slider to 1 item on mobile and tablet
        window.addEventListener('load', function() {
            var $slider2 = $('#slider2');
            if ($slider2.length && typeof $.fn.owlCarousel !== 'undefined') {
                $slider2.owlCarousel('destroy'); 
                $slider2.owlCarousel({
                    items: 1,
                    nav: false,
                    dots: true,
                    loop: true,
                    autoplay: true,
                    responsive: {
                        0: { items: 1 },
                        992: { items: 3 }
                    }
                });
            }
        });
    </script>
@endpush

@if(!empty($giftModal))
    @push('scripts_bottom2')
        <script defer>
            (function () {
                "use strict";

                handleLimitedAccountModal('{!! $giftModal !!}', 40)
            })(jQuery)
        </script>
    @endpush
@endif
