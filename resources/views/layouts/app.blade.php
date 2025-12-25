<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Krys & Tell</title>

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">

    <style>
        /* ==========================================================
           THEME TOKENS (GLOBAL) — + LEGACY VARIABLE OVERRIDES
           ========================================================== */
        html {
            /* NEW TOKENS (recommended) */
            --kt-bg: #eef3fa !important;
            --kt-surface: rgba(255, 255, 255, .90) !important;
            --kt-surface-2: rgba(255, 255, 255, .96) !important;
            --kt-text: #0f172a !important;
            --kt-muted: rgba(15, 23, 42, .62) !important;
            --kt-border: rgba(15, 23, 42, .10) !important;
            --kt-shadow: 0 14px 36px rgba(15, 23, 42, .10) !important;
            --kt-primary: #2563eb !important;

            --kt-input-bg: rgba(255, 255, 255, .92) !important;
            --kt-input-border: rgba(15, 23, 42, .14) !important;

            --kt-sidebar-bg: linear-gradient(180deg, #0d6efd 0%, #084298 100%) !important;
            --kt-sidebar-shadow: 0 12px 30px rgba(2, 117, 255, 0.25) !important;

            /* LEGACY TOKENS (your pages use these a lot) */
            --text: #0f172a !important;
            --muted: rgba(15, 23, 42, .58) !important;
            --card-border: 1px solid rgba(15, 23, 42, .08) !important;
            --card-shadow: 0 12px 28px rgba(15, 23, 42, .08) !important;
        }

        html[data-theme="dark"] {
            /* Dark theme (NO galaxy, just clean dark) */
            --kt-bg: #0b1220 !important;
            --kt-surface: rgba(17, 24, 39, .78) !important;
            --kt-surface-2: rgba(17, 24, 39, .92) !important;

            /* YOU REQUESTED: all text white */
            --kt-text: #f8fafc !important;
            --kt-muted: rgba(248, 250, 252, .72) !important;

            --kt-border: rgba(148, 163, 184, .18) !important;
            --kt-shadow: 0 18px 48px rgba(0, 0, 0, .55) !important;
            --kt-primary: #60a5fa !important;

            --kt-input-bg: rgba(17, 24, 39, .92) !important;
            --kt-input-border: rgba(148, 163, 184, .22) !important;

            --kt-sidebar-bg: linear-gradient(180deg, #0b1220 0%, #0b162d 100%) !important;
            --kt-sidebar-shadow: 0 18px 48px rgba(0, 0, 0, .55) !important;

            /* LEGACY OVERRIDES (THIS FIXES YOUR “BAD” LOOK) */
            --text: #f8fafc !important;
            --muted: rgba(248, 250, 252, .72) !important;
            --card-border: 1px solid rgba(148, 163, 184, .18) !important;
            --card-shadow: 0 18px 48px rgba(0, 0, 0, .55) !important;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--kt-bg) !important;
            color: var(--kt-text) !important;
            margin: 0;
        }

        /* Make all common “muted” text readable */
        .text-muted,
        small,
        .small {
            color: var(--kt-muted) !important;
        }

        /* MAIN FLEX WRAPPER */
        .layout {
            display: flex;
            min-height: 100vh;
            width: 100%;
        }

        /* SIDEBAR */
        .sidebar {
            width: 245px;
            color: white;
            padding: 22px 14px;
            display: flex;
            flex-direction: column;
            background: var(--kt-sidebar-bg);
            box-shadow: var(--kt-sidebar-shadow);
            flex-shrink: 0;

            position: sticky;
            top: 0;
            height: 100vh;
            overflow: hidden;
        }

        .brand {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 10px 10px 18px 10px;
            border-bottom: 1px solid rgba(255, 255, 255, .15);
            margin-bottom: 14px;
        }

        .brand-left {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }

        .brand .logo {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: rgba(255, 255, 255, .18);
            display: grid;
            place-items: center;
            overflow: hidden;
            flex: 0 0 auto;
        }

        .brand .logo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            display: block;
        }
        .kt-top-icon{
            width:42px;
            height:42px;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            border-radius:14px;

            /* LIGHT MODE defaults */
            border: 1px solid var(--kt-border) !important;
            background: var(--kt-surface) !important;
            color: var(--kt-text) !important;
        }
        .kt-top-icon:hover{
            background: var(--kt-surface-2) !important;
        }

        .kt-dot{
            position:absolute;
            top:9px;
            right:9px;
            width:10px;
            height:10px;
            border-radius:999px;
            background:#ef4444;
            box-shadow: 0 0 0 2px rgba(255,255,255,.95); /* light mode ring */
        }

        /* ===== Approval popover (bell dropdown card) ===== */
        .kt-popover{
            position:absolute;
            top:54px;           /* distance below the top bar */
            right:0;
            width:380px;
            max-width: calc(100vw - 24px);
            border-radius: 16px;
            background: var(--kt-surface-2);
            border: 1px solid var(--kt-border);
            box-shadow: var(--kt-shadow);
            display:none;
            z-index: 2500;
            overflow:hidden;
        }

        .kt-popover.show{ display:block; }

        .kt-popover .kt-pop-h{
            padding: 12px 14px;
            border-bottom: 1px solid var(--kt-border);
            display:flex;
            align-items:center;
            justify-content: space-between;
            gap: 10px;
        }

        .kt-popover .kt-pop-title{
            font-weight: 800;
            font-size: 14px;
            margin: 0;
        }

        .kt-popover .kt-badge{
            font-size: 12px;
            padding: 4px 10px;
            border-radius: 999px;
            background: rgba(37,99,235,.15);
            border: 1px solid rgba(37,99,235,.25);
        }

        .kt-popover .kt-pop-body{
            padding: 10px;
            max-height: 360px;
            overflow:auto;
        }

        .kt-popover .kt-item{
            border: 1px solid var(--kt-border);
            background: var(--kt-surface);
            border-radius: 14px;
            padding: 10px 12px;
            margin-bottom: 10px;
        }

        .kt-popover .kt-item:last-child{ margin-bottom: 0; }

        .kt-popover .kt-item .top{
            display:flex;
            align-items:flex-start;
            justify-content: space-between;
            gap: 10px;
        }

        .kt-popover .kt-item .name{
            font-weight: 800;
            font-size: 13px;
            margin: 0;
            line-height: 1.2;
        }

        .kt-popover .kt-item .meta{
            font-size: 12px;
            opacity: .9;
            margin-top: 4px;
        }

        .kt-popover .kt-actions{
            display:flex;
            gap: 8px;
            margin-top: 10px;
        }

        .kt-popover .kt-actions form{ margin:0; }

        .kt-popover .btn-mini{
            padding: 6px 10px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 12px;
        }

        .kt-popover .btn-approve{
            background: rgba(34,197,94,.15);
            border: 1px solid rgba(34,197,94,.25);
            color: #16a34a !important;
        }
        .kt-popover .btn-decline{
            background: rgba(239,68,68,.15);
            border: 1px solid rgba(239,68,68,.25);
            color: #ef4444 !important;
        }



        .brand h4 {
            margin: 0;
            font-weight: 700;
            font-size: 16px;
            line-height: 1.1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .brand small {
            display: block;
            font-size: 12px;
            color: rgba(255, 255, 255, .75);
            margin-top: 2px;
        }

        /* Theme toggle */
        .theme-toggle {
            border: 1px solid rgba(255, 255, 255, .18);
            background: rgba(255, 255, 255, .10);
            color: #fff;
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            cursor: pointer;
            transition: .18s ease;
            flex: 0 0 auto;
        }

        .theme-toggle:hover {
            background: rgba(255, 255, 255, .16);
            transform: translateY(-1px);
        }

        .sidebar-menu {
            margin-top: 12px;
            display: flex;
            flex-direction: column;
            gap: 4px;

            flex: 1;
            overflow-y: auto;
            padding-right: 6px;
        }

        .sidebar-menu::-webkit-scrollbar {
            width: 8px;
        }

        .sidebar-menu::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, .18);
            border-radius: 999px;
        }

        .sidebar-menu::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, .06);
        }

        .sidebar-menu a {
            padding: 12px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 12px;
            color: rgba(255, 255, 255, .9);
            text-decoration: none;
            border-radius: 12px;
            transition: .18s ease;
        }

        .sidebar-menu a:hover {
            background: rgba(255, 255, 255, .14);
            transform: translateX(2px);
            color: #fff;
        }

        .sidebar-menu a.active {
            background: rgba(255, 255, 255, .18);
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .18);
            color: #fff;
        }

        /* LOGOUT pinned bottom */
        .sidebar-footer {
            margin-top: auto;
            padding-top: 14px;
            border-top: 1px solid rgba(255, 255, 255, .15);
        }

        .logout-btn {
            width: 100%;
            border: none;
            padding: 12px;
            border-radius: 12px;
            background: rgba(255, 255, 255, .14);
            color: #fff;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: .18s ease;
        }

        .logout-btn:hover {
            background: rgba(220, 53, 69, 0.95);
            transform: translateY(-1px);
        }

        /* CONTENT */
        .content {
            flex: 1;
            width: 100%;
            padding: 16px;
            color: var(--kt-text);
        }

        @media (min-width: 768px) {
            .content {
                padding: 22px;
            }
        }

        @media (min-width: 1200px) {
            .content {
                padding: 28px;
            }
        }

        /* Clean dark content backdrop (still "just dark") */
        html[data-theme="dark"] .content {
            background:
                radial-gradient(900px 420px at 15% 0%, rgba(96, 165, 250, .06), transparent 55%),
                radial-gradient(900px 420px at 95% 10%, rgba(167, 139, 250, .05), transparent 55%);
            border-radius: 18px;
        }

        /* ==========================================================
           GLOBAL SURFACE FIXES (Dark cards + white text)
           ========================================================== */
        .card,
        .kt-card,
        .section-box,
        .welcome,
        .stat-card,
        .list-item,
        .receipt-container,
        .modal-content,
        .dropdown-menu,
        .table-responsive,
        .toast,
        .offcanvas,
        .list-group-item {
            background: var(--kt-surface) !important;
            border-color: var(--kt-border) !important;
            box-shadow: var(--kt-shadow) !important;
            color: var(--kt-text) !important;
        }

        /* Bootstrap helpers (a LOT of pages use these) */
        html[data-theme="dark"] .bg-white,
        html[data-theme="dark"] .bg-light,
        html[data-theme="dark"] .table-light {
            background: var(--kt-surface-2) !important;
            color: var(--kt-text) !important;
        }

        /* Tables – force readable */
        .table {
            color: var(--kt-text) !important;
        }

        .table td,
        .table th {
            border-color: var(--kt-border) !important;
        }

        html[data-theme="dark"] .table thead th {
            background: rgba(17, 24, 39, .92) !important;
            color: var(--kt-muted) !important;
            border-color: var(--kt-border) !important;
        }

        html[data-theme="dark"] .table tbody td {
            color: var(--kt-text) !important;
        }

        html[data-theme="dark"] .table tbody tr:hover {
            background: rgba(148, 163, 184, .08) !important;
        }

        /* Inputs */
        .form-control,
        .form-select,
        .input-group-text {
            background: var(--kt-input-bg) !important;
            color: var(--kt-text) !important;
            border-color: var(--kt-input-border) !important;
        }

        html[data-theme="dark"] .form-control::placeholder {
            color: rgba(248, 250, 252, .55) !important;
        }

        /* FullCalendar dark-safe */
        html[data-theme="dark"] .fc,
        html[data-theme="dark"] .fc .fc-scrollgrid,
        html[data-theme="dark"] .fc .fc-view-harness {
            background: transparent !important;
            color: var(--kt-text) !important;
            border-color: var(--kt-border) !important;
        }

        html[data-theme="dark"] .fc-theme-standard td,
        html[data-theme="dark"] .fc-theme-standard th {
            border-color: rgba(148, 163, 184, .18) !important;
        }

        html[data-theme="dark"] .fc .fc-toolbar-title {
            color: var(--kt-text) !important;
        }

        html[data-theme="dark"] .fc .fc-button {
            background: rgba(17, 24, 39, .92) !important;
            border-color: rgba(148, 163, 184, .22) !important;
            color: var(--kt-text) !important;
            border-radius: 12px !important;
            font-weight: 800 !important;
        }

        html[data-theme="dark"] .fc .fc-button:hover {
            background: rgba(96, 165, 250, .10) !important;
            border-color: rgba(96, 165, 250, .25) !important;
        }

        html[data-theme="dark"] .fc .fc-day-today {
            background: rgba(96, 165, 250, .10) !important;
        }

        /* ==========================================================
           BOOTSTRAP DARK MODE HARD OVERRIDES (FINAL FIX)
           ========================================================== */

        /* MODALS */
        html[data-theme="dark"] .modal-content {
            background: var(--kt-surface-2) !important;
            color: var(--kt-text) !important;
        }

        html[data-theme="dark"] .modal-header,
        html[data-theme="dark"] .modal-footer {
            border-color: var(--kt-border) !important;
            background: transparent !important;
        }

        /* TABLE VARIANTS */
        html[data-theme="dark"] .table-striped > tbody > tr:nth-of-type(odd) {
            background: rgba(148, 163, 184, .06) !important;
        }

        html[data-theme="dark"] .table-hover > tbody > tr:hover {
            background: rgba(148, 163, 184, .10) !important;
        }

        html[data-theme="dark"] .table-bordered td,
        html[data-theme="dark"] .table-bordered th {
            border-color: var(--kt-border) !important;
        }

        /* PAGINATION */
        html[data-theme="dark"] .page-link {
            background: rgba(17, 24, 39, .92) !important;
            border-color: var(--kt-border) !important;
            color: var(--kt-text) !important;
        }

        html[data-theme="dark"] .page-item.active .page-link {
            background: rgba(96, 165, 250, .25) !important;
            border-color: rgba(96, 165, 250, .35) !important;
            color: #fff !important;
        }

        html[data-theme="dark"] .page-link:hover {
            background: rgba(96, 165, 250, .15) !important;
        }

        /* ALERTS */
        html[data-theme="dark"] .alert {
            background: rgba(17, 24, 39, .92) !important;
            border-color: var(--kt-border) !important;
            color: var(--kt-text) !important;
        }

        /* DROPDOWNS */
        html[data-theme="dark"] .dropdown-item {
            color: var(--kt-text) !important;
        }

        html[data-theme="dark"] .dropdown-item:hover {
            background: rgba(96, 165, 250, .12) !important;
        }

        /* BUTTON FIXES */
        html[data-theme="dark"] .btn-light {
            background: rgba(17, 24, 39, .92) !important;
            color: var(--kt-text) !important;
            border-color: var(--kt-border) !important;
        }

        html[data-theme="dark"] .btn-outline-secondary {
            color: var(--kt-text) !important;
            border-color: var(--kt-border) !important;
        }

        /* BAD LEGACY TEXT FIX */
        html[data-theme="dark"] .text-dark {
            color: var(--kt-text) !important;
        }

        /* LIST GROUP */
        html[data-theme="dark"] .list-group-item {
            background: var(--kt-surface) !important;
            color: var(--kt-text) !important;
            border-color: var(--kt-border) !important;
        }

        /* ==========================================================
           FORCE DARK MODE FOR ALL CARDS (FINAL OVERRIDE)
           ========================================================== */

        /* Every possible card container */
        html[data-theme="dark"] .card,
        html[data-theme="dark"] .card-body,
        html[data-theme="dark"] .card-header,
        html[data-theme="dark"] .card-footer,
        html[data-theme="dark"] .stat-card,
        html[data-theme="dark"] .info-card,
        html[data-theme="dark"] .summary-card,
        html[data-theme="dark"] .welcome,
        html[data-theme="dark"] .section-box,
        html[data-theme="dark"] .dashboard-card,
        html[data-theme="dark"] [class*="card"] {
            background: var(--kt-surface) !important;
            color: var(--kt-text) !important;
            border-color: var(--kt-border) !important;
        }

        /* Kill Bootstrap bg utilities */
        html[data-theme="dark"] .bg-white,
        html[data-theme="dark"] .bg-light {
            background: var(--kt-surface) !important;
            color: var(--kt-text) !important;
        }

        /* Card titles & text */
        html[data-theme="dark"] .card-title,
        html[data-theme="dark"] .card h1,
        html[data-theme="dark"] .card h2,
        html[data-theme="dark"] .card h3,
        html[data-theme="dark"] .card h4,
        html[data-theme="dark"] .card h5,
        html[data-theme="dark"] .card h6,
        html[data-theme="dark"] .card p,
        html[data-theme="dark"] .card span {
            color: var(--kt-text) !important;
        }

        /* Card headers (Bootstrap default is light) */
        html[data-theme="dark"] .card-header {
            background: rgba(17, 24, 39, .92) !important;
            border-bottom: 1px solid var(--kt-border) !important;
        }

        /* Card footers */
        html[data-theme="dark"] .card-footer {
            background: rgba(17, 24, 39, .75) !important;
            border-top: 1px solid var(--kt-border) !important;
        }

        /* ==========================================================
           ABSOLUTE DARK MODE TEXT OVERRIDE (GLOBAL)
           ========================================================== */
        html[data-theme="dark"],
        html[data-theme="dark"] body,
        html[data-theme="dark"] * {
            color: #f8fafc !important;
        }

        /* Keep muted text readable but still white */
        html[data-theme="dark"] .text-muted,
        html[data-theme="dark"] small,
        html[data-theme="dark"] .small {
            color: rgba(248, 250, 252, .85) !important;
        }

        /* Headings must stay bright */
        html[data-theme="dark"] h1,
        html[data-theme="dark"] h2,
        html[data-theme="dark"] h3,
        html[data-theme="dark"] h4,
        html[data-theme="dark"] h5,
        html[data-theme="dark"] h6 {
            color: #ffffff !important;
        }

        /* Form labels */
        html[data-theme="dark"] label,
        html[data-theme="dark"] .form-label {
            color: #ffffff !important;
        }

        /* Inputs text */
        html[data-theme="dark"] input,
        html[data-theme="dark"] textarea,
        html[data-theme="dark"] select {
            color: #ffffff !important;
        }

        /* Placeholders */
        html[data-theme="dark"] ::placeholder {
            color: rgba(248, 250, 252, .65) !important;
        }

        /* Table text */
        html[data-theme="dark"] table,
        html[data-theme="dark"] th,
        html[data-theme="dark"] td {
            color: #ffffff !important;
        }

        /* Dropdowns & modals */
        html[data-theme="dark"] .dropdown-menu *,
        html[data-theme="dark"] .modal *,
        html[data-theme="dark"] .offcanvas * {
            color: #ffffff !important;
        }

        /* Validation & alerts */
        html[data-theme="dark"] .invalid-feedback,
        html[data-theme="dark"] .valid-feedback,
        html[data-theme="dark"] .alert {
            color: #ffffff !important;
        }

        /* Links */
        html[data-theme="dark"] a {
            color: #93c5fd !important;
        }

        html[data-theme="dark"] a:hover {
            color: #bfdbfe !important;
        }

        /* ==========================================================
           DARK MODE INPUTS (FIX WHITE INPUT ISSUE)
           ========================================================== */
        html[data-theme="dark"] input,
        html[data-theme="dark"] textarea,
        html[data-theme="dark"] select {
            background-color: #111827 !important; /* dark slate */
            color: #ffffff !important;
            border-color: rgba(148, 163, 184, .35) !important;
        }

        html[data-theme="dark"] input:focus,
        html[data-theme="dark"] textarea:focus,
        html[data-theme="dark"] select:focus {
            background-color: #111827 !important;
            color: #ffffff !important;
            border-color: #60a5fa !important;
            box-shadow: 0 0 0 2px rgba(96, 165, 250, .25) !important;
        }

        /* Placeholder text */
        html[data-theme="dark"] ::placeholder {
            color: rgba(255, 255, 255, .55) !important;
        }

        /* ==========================================================
           DARK MODE TABLE HEADERS
           ========================================================== */
        html[data-theme="dark"] thead,
        html[data-theme="dark"] thead th {
            background-color: #0f172a !important;
            color: #ffffff !important;
            border-color: rgba(148, 163, 184, .25) !important;
        }

        /* Table rows */
        html[data-theme="dark"] tbody tr {
            background-color: rgba(17, 24, 39, .75) !important;
        }

        html[data-theme="dark"] tbody tr:hover {
            background-color: rgba(148, 163, 184, .12) !important;
        }

        /* Table borders */
        html[data-theme="dark"] table,
        html[data-theme="dark"] th,
        html[data-theme="dark"] td {
            border-color: rgba(148, 163, 184, .25) !important;
        }

        /* ==========================================================
           FULLCALENDAR HEADER + TOGGLES (DARK MODE)
           ========================================================== */
        html[data-theme="dark"] .fc-toolbar,
        html[data-theme="dark"] .fc-header-toolbar {
            background: transparent !important;
            color: #ffffff !important;
        }

        /* Calendar title */
        html[data-theme="dark"] .fc-toolbar-title {
            color: #ffffff !important;
            font-weight: 700;
        }

        /* Calendar view toggle buttons */
        html[data-theme="dark"] .fc .fc-button {
            background-color: #111827 !important;
            color: #ffffff !important;
            border-color: rgba(148, 163, 184, .35) !important;
        }

        html[data-theme="dark"] .fc .fc-button:hover {
            background-color: rgba(96, 165, 250, .15) !important;
            border-color: #60a5fa !important;
        }

        html[data-theme="dark"] .fc .fc-button.fc-button-active {
            background-color: #1e40af !important;
            border-color: #60a5fa !important;
            color: #ffffff !important;
        }

        /* ==========================================================
           TOGGLES / SWITCHES / CHECKBOXES (DARK MODE)
           ========================================================== */
        html[data-theme="dark"] .form-check-input {
            background-color: #111827 !important;
            border-color: rgba(148, 163, 184, .45) !important;
        }

        html[data-theme="dark"] .form-check-input:checked {
            background-color: #2563eb !important;
            border-color: #2563eb !important;
        }

        html[data-theme="dark"] .form-check-label {
            color: #ffffff !important;
        }

        /* ==========================================================
           CANCEL / SECONDARY BUTTONS (DARK MODE)
           ========================================================== */
        html[data-theme="dark"] .btn-secondary,
        html[data-theme="dark"] .btn-light,
        html[data-theme="dark"] .btn-cancel {
            background-color: #020617 !important; /* near-black */
            color: #ffffff !important;
            border-color: rgba(148, 163, 184, .35) !important;
        }

        html[data-theme="dark"] .btn-secondary:hover,
        html[data-theme="dark"] .btn-light:hover,
        html[data-theme="dark"] .btn-cancel:hover {
            background-color: #111827 !important;
            color: #ffffff !important;
        }

        /* ==========================================================
           UNIVERSAL BOXES / STRIPS / FOOTERS (DARK MODE)
           ========================================================== */
        html[data-theme="dark"] .bg-white,
        html[data-theme="dark"] .bg-light,
        html[data-theme="dark"] .bg-body,
        html[data-theme="dark"] .rounded,
        html[data-theme="dark"] .rounded-lg,
        html[data-theme="dark"] .rounded-xl {
            background: var(--kt-surface) !important;
            color: var(--kt-text) !important;
        }

        /* Odontogram header / footer strip */
        html[data-theme="dark"] .odontogram-header,
        html[data-theme="dark"] .odontogram-footer,
        html[data-theme="dark"] .odontogram-container,
        html[data-theme="dark"] .tooth-chart,
        html[data-theme="dark"] .tooth-chart-header,
        html[data-theme="dark"] .tooth-chart-footer {
            background: var(--kt-surface-2) !important;
            color: #ffffff !important;
            border-color: var(--kt-border) !important;
        }

        html[data-theme="dark"] .table tbody tr td {
            background: transparent !important;
            color: #ffffff !important;
        }

        html[data-theme="dark"] .table .empty-state,
        html[data-theme="dark"] .table tbody tr.empty td {
            background: rgba(148, 163, 184, .08) !important;
            color: #ffffff !important;
        }

        /* Calendar view toggle cards */
        html[data-theme="dark"] .fc-toolbar-chunk .fc-button-group,
        html[data-theme="dark"] .fc-toolbar-chunk button {
            background: transparent !important;
        }

        html[data-theme="dark"] .fc .fc-button {
            background: #020617 !important;
            color: #ffffff !important;
            border: 1px solid rgba(148, 163, 184, .35) !important;
            border-radius: 12px !important;
        }

        html[data-theme="dark"] .fc .fc-button.fc-button-active {
            background: #1e40af !important;
            border-color: #60a5fa !important;
            color: #ffffff !important;
        }

        /* CANCEL BUTTON — GLOBAL */
        html[data-theme="dark"] .btn-cancel,
        html[data-theme="dark"] .btn-outline-secondary,
        html[data-theme="dark"] .btn-light {
            background: #020617 !important;
            color: #ffffff !important;
            border: 1px solid rgba(148, 163, 184, .35) !important;
        }

        html[data-theme="dark"] .btn-cancel:hover,
        html[data-theme="dark"] .btn-outline-secondary:hover,
        html[data-theme="dark"] .btn-light:hover {
            background: #111827 !important;
            color: #ffffff !important;
        }

        html[data-theme="dark"] [class*="card"],
        html[data-theme="dark"] [class*="panel"],
        html[data-theme="dark"] [class*="box"] {
            background: var(--kt-surface) !important;
            color: #ffffff !important;
        }

        /* ==========================================================
           MINI BUTTONS (Month / Week / Day) — DARK MODE
           ========================================================== */
        html[data-theme="dark"] .mini-btn {
            background-color: #020617 !important; /* dark */
            color: #ffffff !important;
            border: 1px solid rgba(148, 163, 184, .35) !important;
            border-radius: 12px;
            padding: 6px 14px;
            font-weight: 600;
            transition: all .15s ease;
        }

        /* Hover */
        html[data-theme="dark"] .mini-btn:hover {
            background-color: #111827 !important;
            color: #ffffff !important;
        }

        /* ACTIVE STATE */
        html[data-theme="dark"] .mini-btn.active {
            background-color: #1e40af !important; /* blue active */
            border-color: #60a5fa !important;
            color: #ffffff !important;
        }

        /* Ensure text never turns dark */
        html[data-theme="dark"] .mini-btn *,
        html[data-theme="dark"] .mini-btn span {
            color: #ffffff !important;
        }

        /* ==========================================================
           GLOBAL TEXT — FORCE WHITE IN DARK MODE
           ========================================================== */
        html[data-theme="dark"] {
            color-scheme: dark;
        }

        html[data-theme="dark"] *,
        html[data-theme="dark"] body {
            color: #f8fafc;
        }

        /* ==========================================================
           kt-card titles (e.g. "Today's Schedule")
           ========================================================== */
        html[data-theme="dark"] .kt-card-title {
            color: #ffffff !important;
        }

        /* ==========================================================
           ICONS: cancel, back, xmark, arrow-left, eraser, etc.
           ========================================================== */
        html[data-theme="dark"] .fa,
        html[data-theme="dark"] .fa-solid,
        html[data-theme="dark"] .fa-regular,
        html[data-theme="dark"] .fa-xmark,
        html[data-theme="dark"] .fa-arrow-left,
        html[data-theme="dark"] .fa-eraser {
            color: #ffffff !important;
        }

        /* ==========================================================
           TITLES / SUBTEXT (Tooth Chart / Odontogram)
           ========================================================== */
        html[data-theme="dark"] .ttl {
            color: #ffffff !important;
            font-weight: 700;
        }

        html[data-theme="dark"] .ttl .mutedx {
            color: rgba(248, 250, 252, .75) !important;
        }

        html[data-theme="dark"] .sub {
            color: rgba(248, 250, 252, .70) !important;
        }

        /* ==========================================================
           ODONTO CARD (Tooth Chart container)
           ========================================================== */
        html[data-theme="dark"] .odonto-card,
        html[data-theme="dark"] .odonto-top {
            background: rgba(17, 24, 39, .92) !important;
            border: 1px solid rgba(148, 163, 184, .18) !important;
            color: #ffffff !important;
        }

        /* ==========================================================
           ODONTO LEGEND (Has procedure / Selected / Clear button)
           ========================================================== */
        html[data-theme="dark"] .odonto-legend {
            color: #ffffff !important;
        }

        html[data-theme="dark"] .odonto-legend .lg {
            color: rgba(248, 250, 252, .85) !important;
        }

        html[data-theme="dark"] .odonto-legend .dot {
            box-shadow: 0 0 0 2px rgba(255, 255, 255, .15);
        }

        /* Clear selection button */
        html[data-theme="dark"] .btn-mini {
            background: #020617 !important;
            color: #ffffff !important;
            border: 1px solid rgba(148, 163, 184, .35) !important;
        }

        html[data-theme="dark"] .btn-mini:hover {
            background: #111827 !important;
        }

        /* ==========================================================
           STICKY INNER / MINI NOTE
           ========================================================== */
        html[data-theme="dark"] .sticky-inner,
        html[data-theme="dark"] .mini-note {
            background: rgba(17, 24, 39, .92) !important;
            color: #ffffff !important;
            border-color: rgba(148, 163, 184, .18) !important;
        }

        /* ==========================================================
           SEGMENTED TABS (Cash / Installments)
           ========================================================== */
        html[data-theme="dark"] .segmented {
            background: #020617 !important;
            border: 1px solid rgba(148, 163, 184, .22) !important;
            border-radius: 14px;
            padding: 4px;
        }

        /* Buttons */
        html[data-theme="dark"] .seg-btn {
            background: transparent !important;
            color: #ffffff !important;
            border: none !important;
            padding: 10px 16px;
            border-radius: 10px;
            font-weight: 600;
        }

        /* Hover */
        html[data-theme="dark"] .seg-btn:hover {
            background: rgba(148, 163, 184, .15) !important;
        }

        /* Active */
        html[data-theme="dark"] .seg-btn.active {
            background: #1e40af !important;
            color: #ffffff !important;
        }

        /* Icons inside segmented buttons */
        html[data-theme="dark"] .seg-btn i {
            color: #ffffff !important;
        }

        /* ==========================================================
           KT CARD HEADER (Today’s Schedule, Overdue Payments, etc.)
           ========================================================== */
        html[data-theme="dark"] .kt-card {
            background: rgba(17, 24, 39, .92) !important;
            border: 1px solid rgba(148, 163, 184, .18) !important;
            box-shadow: 0 18px 48px rgba(0, 0, 0, .55) !important;
        }

        /* Header row inside card */
        html[data-theme="dark"] .kt-card-h {
            background: rgba(2, 6, 23, .85) !important;
            border-bottom: 1px solid rgba(148, 163, 184, .18) !important;
        }

        /* Card title */
        html[data-theme="dark"] .kt-card-title {
            color: #ffffff !important;
            font-weight: 700;
        }

        /* Date pill / badge */
        html[data-theme="dark"] .pill {
            background: rgba(148, 163, 184, .18) !important;
            font-weight: 600;
        }

        /* Optional hover polish */
        html[data-theme="dark"] .pill:hover {
            background: rgba(96, 165, 250, .25) !important;
        }

        /* Odontogram legend: "Has procedure" — DARK MODE */
        html[data-theme="dark"] .lg.lg-has {
            background: rgba(17, 24, 39, .92) !important;
            color: #ffffff !important;
            border: 1px solid rgba(148, 163, 184, .18) !important;
            border-radius: 999px;
            padding: 6px 12px;
        }

        /* Odontogram legend: "Selected" — DARK MODE */
        html[data-theme="dark"] .lg.lg-sel {
            background: #1e40af !important; /* blue selected */
            color: #ffffff !important;
            border: 1px solid #60a5fa !important;
            border-radius: 999px;
            padding: 6px 12px;
        }

        /* Ghost button — DARK MODE */
        html[data-theme="dark"] .btn-ghostx {
            background: rgba(17, 24, 39, .75) !important;
            color: #ffffff !important;
            border: 1px solid rgba(148, 163, 184, .35) !important;
            border-radius: 12px;
        }

        /* Hover */
        html[data-theme="dark"] .btn-ghostx:hover {
            background: rgba(96, 165, 250, .15) !important;
            border-color: #60a5fa !important;
            color: #ffffff !important;
        }

        /* Icon inside */
        html[data-theme="dark"] .btn-ghostx i {
            color: #ffffff !important;
        }

        html[data-theme="dark"] .odonto-bottom {
            background: var(--kt-surface-2) !important;
            color: #ffffff !important;
            border-color: var(--kt-border) !important;
        }

        /* ==========================================================
           INFO GRID + INFO BOXES — DARK MODE FIX
           ========================================================== */

        /* Grid container */
        html[data-theme="dark"] .info-grid {
            background: transparent !important; /* grid itself has no card bg */
        }

        /* Each info box (THIS is the light part) */
        html[data-theme="dark"] .info-grid .info {
            background: var(--kt-surface) !important;
            border: 1px solid var(--kt-border) !important;
            box-shadow: var(--kt-shadow) !important;
            color: var(--kt-text) !important;
            border-radius: 14px;
        }

        /* Labels inside info box */
        html[data-theme="dark"] .info-grid .label {
            color: var(--kt-muted) !important;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: .04em;
        }

        /* Values inside info box */
        html[data-theme="dark"] .info-grid .value {
            color: var(--kt-text) !important;
            font-weight: 600;
        }

        /* ==========================================================
           CARD-B DETAILS GRID — DARK MODE
           ========================================================== */

        /* Main container */
        html[data-theme="dark"] .card-b {
            background: var(--kt-surface) !important;
            border: 1px solid var(--kt-border) !important;
            box-shadow: var(--kt-shadow) !important;
            color: var(--kt-text) !important;
            border-radius: 18px;
        }

        /* Grid wrapper (layout only) */
        html[data-theme="dark"] .card-b .grid {
            background: transparent !important;
        }

        /* Individual field cards */
        html[data-theme="dark"] .card-b .field {
            background: rgba(17, 24, 39, .92) !important;
            border: 1px solid rgba(148, 163, 184, .18) !important;
            border-radius: 14px;
            color: #ffffff !important;
        }

        /* Labels */
        html[data-theme="dark"] .card-b .label {
            color: rgba(248, 250, 252, .70) !important;
            font-weight: 600;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        /* Main value */
        html[data-theme="dark"] .card-b .value {
            color: #ffffff !important;
            font-weight: 700;
        }

        /* Subvalue (muted line) */
        html[data-theme="dark"] .card-b .subvalue {
            color: rgba(248, 250, 252, .65) !important;
        }

        /* SUMMARY GRID — DARK ONLY (NO SIZE CHANGE) */
        html[data-theme="dark"] .summary-grid .tile {
            background: rgba(17, 24, 39, .92) !important;
            border-color: rgba(148, 163, 184, .18) !important;
            color: #ffffff !important;
        }

        /* Text */
        html[data-theme="dark"] .summary-grid .k,
        html[data-theme="dark"] .summary-grid .v {
            color: #ffffff !important;
        }

        /* Icons */
        html[data-theme="dark"] .summary-grid .k i {
            color: #ffffff !important;
        }

        /* Balance highlight stays readable */
        html[data-theme="dark"] .summary-grid .v.balance {
            color: #f87171 !important;
        }

        /* ==========================================================
           INSTALLMENT RECEIPT — DARK MODE ONLY
           (NO SIZE / LAYOUT CHANGES)
           ========================================================== */

        /* Main receipt card */
        html[data-theme="dark"] .receipt-container {
            background: rgba(2, 6, 23, .92) !important;
            border-color: rgba(148, 163, 184, .18) !important;
            box-shadow: 0 18px 48px rgba(0, 0, 0, .6) !important;
            color: #ffffff !important;
        }

        /* Disable light blobs visually (keep structure) */
        html[data-theme="dark"] .receipt-container::before,
        html[data-theme="dark"] .receipt-container::after,
        html[data-theme="dark"] .kt-wave {
            opacity: .12 !important;
        }

        /* Header */
        html[data-theme="dark"] .receipt-header {
            border-color: rgba(148, 163, 184, .18) !important;
        }

        html[data-theme="dark"] .brand-title .clinic,
        html[data-theme="dark"] .brand-title .sub,
        html[data-theme="dark"] .header-right,
        html[data-theme="dark"] .header-right b {
            color: #ffffff !important;
        }

        /* Meta pills */
        html[data-theme="dark"] .meta-pill {
            background: rgba(17, 24, 39, .92) !important;
            border-color: rgba(148, 163, 184, .22) !important;
            color: #ffffff !important;
        }

        /* Section titles */
        html[data-theme="dark"] .section-title {
            background: rgba(17, 24, 39, .92) !important;
            border-color: rgba(148, 163, 184, .22) !important;
            color: #ffffff !important;
        }

        /* Info table */
        html[data-theme="dark"] .info-table {
            background: rgba(17, 24, 39, .92) !important;
            border-color: rgba(148, 163, 184, .18) !important;
        }

        html[data-theme="dark"] .info-table td {
            color: #ffffff !important;
            border-color: rgba(148, 163, 184, .18) !important;
        }

        html[data-theme="dark"] .info-table td:first-child {
            background: rgba(2, 6, 23, .85) !important;
            color: #ffffff !important;
        }

        /* Receipt table */
        html[data-theme="dark"] .receipt-table {
            background: rgba(17, 24, 39, .92) !important;
            border-color: rgba(148, 163, 184, .18) !important;
        }

        html[data-theme="dark"] .receipt-table thead th {
            background: rgba(2, 6, 23, .92) !important;
            color: #ffffff !important;
            border-color: rgba(148, 163, 184, .18) !important;
        }

        html[data-theme="dark"] .receipt-table td {
            color: #ffffff !important;
            border-color: rgba(148, 163, 184, .18) !important;
        }

        html[data-theme="dark"] .receipt-table tbody tr:nth-child(even) {
            background: rgba(148, 163, 184, .08) !important;
        }

        /* Totals boxes */
        html[data-theme="dark"] .left-details,
        html[data-theme="dark"] .right-details {
            background: rgba(17, 24, 39, .92) !important;
            border-color: rgba(148, 163, 184, .18) !important;
            color: #ffffff !important;
        }

        html[data-theme="dark"] .right-details .row {
            border-color: rgba(148, 163, 184, .18) !important;
        }

        html[data-theme="dark"] .right-details .row.total {
            background: rgba(30, 64, 175, .85) !important;
        }

        /* Footer */
        html[data-theme="dark"] .receipt-footer {
            border-color: rgba(148, 163, 184, .18) !important;
            color: #ffffff !important;
        }

        html[data-theme="dark"] .signature,
        html[data-theme="dark"] .signature-line {
            color: #ffffff !important;
            border-color: #ffffff !important;
        }

        /* Buttons (screen only, NOT print) */
        html[data-theme="dark"] .back-btn {
            background: #020617 !important;
            color: #ffffff !important;
            border-color: rgba(148, 163, 184, .35) !important;
        }

        html[data-theme="dark"] .print-btn {
            filter: brightness(1.1);
        }

        /* Icons */
        html[data-theme="dark"] .receipt-container i {
            color: #ffffff !important;
        }
        /* ===== Mobile Responsive Sidebar Drawer (Staff) ===== */
.side-overlay{
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.35);
    z-index: 1999;
    opacity: 0;
    pointer-events: none;
    transition: opacity .18s ease;
}
.side-overlay.show{
    opacity: 1;
    pointer-events: auto;
}

.menu-toggle{
    display:none;
    width: 42px;
    height: 42px;
    border-radius: 12px;
    border: 1px solid var(--kt-border);
    background: var(--kt-surface);
    box-shadow: var(--kt-shadow);
    place-items:center;
    cursor:pointer;
}

@media (max-width: 900px){
    .menu-toggle{ display:grid; }

    .sidebar{
        position: fixed;
        left: 0;
        top: 0;
        height: 100dvh;
        z-index: 2000;
        transform: translateX(-105%);
        transition: transform .18s ease;
    }
    .sidebar.open{ transform: translateX(0); }

    .content{ padding: 14px; }
}

    </style>
</head>

{{-- layouts/app.blade.php (BODY PART UPDATED) --}}
<body>
<div class="layout">

    <!-- mobile overlay -->
    <div class="side-overlay" id="sideOverlay"></div>

    <!-- SIDEBAR -->
    <div class="sidebar" id="staffSidebar">
        <div class="brand">
            <div class="brand-left">
                <div class="logo">
                    <img src="{{ asset('images/krysandtelllogo.jpg') }}" alt="Krys & Tell Logo">
                </div>

                <div>
                    <h4>Krys & Tell</h4>
                    <small>Clinic Management</small>
                </div>
            </div>

            <button class="theme-toggle" id="themeToggle" type="button" title="Toggle Dark Mode">
                <i class="fa-solid fa-moon" id="themeIcon"></i>
            </button>
        </div>

        <div class="sidebar-menu">
            <a href="{{ route('staff.dashboard') }}" class="{{ request()->routeIs('staff.dashboard') ? 'active' : '' }}">
                <i class="fa fa-chart-line"></i> Dashboard
            </a>

            <a href="{{ route('staff.patients.index') }}" class="{{ request()->routeIs('staff.patients.*') ? 'active' : '' }}">
                <i class="fa fa-users"></i> Patients
            </a>

            <a href="{{ route('staff.visits.index') }}" class="{{ request()->routeIs('staff.visits.*') ? 'active' : '' }}">
                <i class="fa fa-calendar-check"></i> Visits
            </a>

            <a href="{{ route('staff.payments.index') }}" class="{{ request()->routeIs('staff.payments.*') ? 'active' : '' }}">
                <i class="fa fa-money-bill"></i> Payments
            </a>

            <a href="{{ route('staff.appointments.index') }}" class="{{ request()->routeIs('staff.appointments.*') ? 'active' : '' }}">
                <i class="fa fa-calendar-days"></i> Appointments
            </a>

            <a href="{{ route('staff.services.index') }}" class="{{ request()->routeIs('staff.services.*') ? 'active' : '' }}">
                <i class="fa fa-gear"></i> Services
            </a>
        </div>

        <div class="sidebar-footer">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="logout-btn">
                    <i class="fa fa-right-from-bracket"></i>
                    Logout
                </button>
            </form>
        </div>
    </div>

    <!-- CONTENT -->
    <div class="content app-content">
        {{-- Top bar: menu (left) + bell dropdown (right) --}}
        @php
            $pendingApprovals = 0;
            $pendingItems = collect();

            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('appointments')
                    && \Illuminate\Support\Facades\Schema::hasColumn('appointments', 'status')) {

                    $pendingItems = \App\Models\Appointment::query()
                        ->with(['service','doctor','patient'])
                        ->where('status', 'pending')
                        ->orderByDesc('created_at')
                        ->take(8)
                        ->get();

                    $pendingApprovals = $pendingItems->count();
                }
            } catch (\Throwable $e) {
                $pendingApprovals = 0;
                $pendingItems = collect();
            }
        @endphp

        <div class="d-flex align-items-center mb-3">
            <button class="menu-toggle" id="menuToggle" type="button" title="Menu">
                <i class="fa fa-bars"></i>
            </button>

            {{-- ✅ Right side --}}
            <div class="ms-auto d-flex align-items-center gap-2 position-relative">
                {{-- Bell button (no page redirect) --}}
                <button type="button"
                        id="approvalBell"
                        class="kt-top-icon position-relative border-0"
                        title="Approval Requests"
                        aria-haspopup="true"
                        aria-expanded="false">
                    <i class="fa-solid fa-bell"></i>

                    {{-- ✅ dot always exists; JS toggles it --}}
                    <span id="approvalDot" class="kt-dot {{ $pendingApprovals > 0 ? '' : 'd-none' }}"></span>
                </button>

                {{-- Dropdown card --}}
                <div id="approvalPopover" class="kt-popover" aria-hidden="true">
                    <div class="kt-pop-h">
                        <p class="kt-pop-title mb-0">Approval Requests</p>
                        <span class="kt-badge">
                            <span id="approvalBadge">{{ $pendingApprovals }}</span> pending
                        </span>
                    </div>

                    {{-- ✅ Flash message INSIDE the dropdown card --}}
                    <div id="approvalFlash" class="px-3 pt-3 d-none"></div>

                    <div class="kt-pop-body" id="approvalList">
                        @if($pendingItems->isEmpty())
                            <div class="text-center py-3" id="approvalEmpty">
                                <div class="fw-bold">No pending requests</div>
                                <div class="small text-muted">You're all caught up.</div>
                            </div>
                        @else
                            @foreach($pendingItems as $a)
                                @php
                                    $displayName =
                                        $a->public_name
                                        ?? trim(($a->public_first_name ?? '').' '.($a->public_middle_name ?? '').' '.($a->public_last_name ?? ''))
                                        ?: ($a->patient->name ?? 'Patient');

                                    $serviceName = $a->service->name ?? 'Service';
                                    $doctorName  = $a->doctor->name ?? ($a->dentist_name ?? 'Doctor');

                                    $date = $a->appointment_date ?? null;
                                    $time = $a->appointment_time ?? null;
                                @endphp

                                <div class="kt-item" data-approval-id="{{ $a->id }}">
                                    <div class="top">
                                        <div>
                                            <p class="name">{{ $displayName }}</p>
                                            <div class="meta">
                                                <div><b>{{ $serviceName }}</b></div>
                                                <div>
                                                    {{ $date ? \Carbon\Carbon::parse($date)->format('M d, Y') : '—' }}
                                                    @if($time) • {{ \Carbon\Carbon::parse($time)->format('h:i A') }} @endif
                                                </div>
                                                <div class="small text-muted">Doctor: {{ $doctorName }}</div>
                                            </div>
                                        </div>

                                        <div class="small text-muted text-end">
                                            {{ optional($a->created_at)->diffForHumans() }}
                                        </div>
                                    </div>

                                    <div class="kt-actions">
                                        <form class="approval-form"
                                              data-action="approve"
                                              method="POST"
                                              action="{{ route('staff.approvals.approve', $a->id) }}">
                                            @csrf
                                            <button class="btn btn-mini btn-approve" type="submit">
                                                <i class="fa-solid fa-check me-1"></i> Approve
                                            </button>
                                        </form>

                                        <form class="approval-form"
                                              data-action="decline"
                                              method="POST"
                                              action="{{ route('staff.approvals.decline', $a->id) }}">
                                            @csrf
                                            <button class="btn btn-mini btn-decline" type="submit">
                                                <i class="fa-solid fa-xmark me-1"></i> Decline
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @yield('content')
    </div>
</div>

<script>
(function () {
    // theme
    const html = document.documentElement;
    const btnTheme  = document.getElementById('themeToggle');
    const icon = document.getElementById('themeIcon');

    function applyTheme(theme){
        html.setAttribute('data-theme', theme);
        localStorage.setItem('theme', theme);

        if (icon) {
            icon.classList.remove('fa-moon', 'fa-sun');
            icon.classList.add(theme === 'dark' ? 'fa-sun' : 'fa-moon');
        }
    }

    const saved = localStorage.getItem('theme') || 'light';
    applyTheme(saved);

    btnTheme?.addEventListener('click', function () {
        const next = (html.getAttribute('data-theme') === 'dark') ? 'light' : 'dark';
        applyTheme(next);
    });

    // sidebar drawer (mobile)
    const side = document.getElementById('staffSidebar');
    const overlay = document.getElementById('sideOverlay');
    const btnMenu = document.getElementById('menuToggle');

    function closeSidebar(){
        side?.classList.remove('open');
        overlay?.classList.remove('show');
    }

    btnMenu?.addEventListener('click', () => {
        side?.classList.toggle('open');
        overlay?.classList.toggle('show');
    });

    overlay?.addEventListener('click', closeSidebar);

    window.addEventListener('resize', () => {
        if (!window.matchMedia('(max-width: 900px)').matches) closeSidebar();
    });

    // ✅ approval popover (bell) + AJAX approve/decline
    const bell = document.getElementById('approvalBell');
    const pop  = document.getElementById('approvalPopover');

    const badgeEl = document.getElementById('approvalBadge');
    const dotEl   = document.getElementById('approvalDot');
    const flashEl = document.getElementById('approvalFlash');
    const listEl  = document.getElementById('approvalList');

    const csrf =
        document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        || document.querySelector('input[name="_token"]')?.value
        || '';

    function closePopover(){
        if(!pop) return;
        pop.classList.remove('show');
        pop.setAttribute('aria-hidden', 'true');
        bell?.setAttribute('aria-expanded', 'false');
    }

    function togglePopover(e){
        e?.stopPropagation();
        if(!pop) return;

        const isOpen = pop.classList.contains('show');
        if(isOpen){
            closePopover();
        }else{
            pop.classList.add('show');
            pop.setAttribute('aria-hidden', 'false');
            bell?.setAttribute('aria-expanded', 'true');
        }
    }

    function setCount(n){
        n = Number(n || 0);
        if (badgeEl) badgeEl.textContent = String(n);
        if (dotEl) dotEl.classList.toggle('d-none', n <= 0);
    }

    function showFlash(type, text){
        if (!flashEl) return;
        flashEl.classList.remove('d-none');
        flashEl.innerHTML = `
            <div class="alert alert-${type} py-2 px-3 mb-0" style="font-size:13px;">
                ${escapeHtml(text)}
            </div>
        `;
        window.clearTimeout(showFlash._t);
        showFlash._t = window.setTimeout(() => flashEl.classList.add('d-none'), 2500);
    }

    function ensureEmptyState(){
        if (!listEl) return;
        const anyItem = listEl.querySelector('.kt-item');
        const empty = listEl.querySelector('#approvalEmpty');

        if (!anyItem) {
            if (!empty) {
                listEl.innerHTML = `
                    <div class="text-center py-3" id="approvalEmpty">
                        <div class="fw-bold">No pending requests</div>
                        <div class="small text-muted">You're all caught up.</div>
                    </div>
                `;
            }
        }
    }

    async function postAction(form){
        const item = form.closest('.kt-item');
        const action = form.dataset.action || 'approve';

        // disable buttons for this item
        const btns = item ? item.querySelectorAll('button') : form.querySelectorAll('button');
        btns.forEach(b => b.disabled = true);

        try{
            const res = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrf,
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: '' // no payload
            });

            const ct = res.headers.get('content-type') || '';
            const data = ct.includes('application/json') ? await res.json() : null;

            if (!res.ok || (data && data.ok === false)) {
                showFlash('danger', (data && data.message) ? data.message : 'Action failed. Please try again.');
                btns.forEach(b => b.disabled = false);
                return;
            }

            showFlash('success', (data && data.message)
                ? data.message
                : (action === 'approve' ? 'Booking approved.' : 'Booking declined.')
            );

            // remove the item
            if (item) item.remove();

            // update count
            if (data && typeof data.pendingCount !== 'undefined') {
                setCount(data.pendingCount);
            } else {
                const current = Number(badgeEl?.textContent || 0);
                setCount(Math.max(0, current - 1));
            }

            ensureEmptyState();
        } catch (e){
            showFlash('danger', 'Network error. Please try again.');
            btns.forEach(b => b.disabled = false);
        }
    }

    // intercept approve/decline forms in the popover
    pop?.addEventListener('submit', function(e){
        const form = e.target.closest('form.approval-form');
        if (!form) return;
        e.preventDefault();
        e.stopPropagation();
        postAction(form);
    });

    bell?.addEventListener('click', togglePopover);
    pop?.addEventListener('click', (e) => e.stopPropagation());

    document.addEventListener('click', closePopover);
    document.addEventListener('keydown', (e) => {
        if(e.key === 'Escape') closePopover();
    });

    function escapeHtml(str){
        return (str ?? '').toString()
            .replaceAll('&','&amp;')
            .replaceAll('<','&lt;')
            .replaceAll('>','&gt;')
            .replaceAll('"','&quot;')
            .replaceAll("'",'&#039;');
    }

})();
</script>

</body>
</html>
