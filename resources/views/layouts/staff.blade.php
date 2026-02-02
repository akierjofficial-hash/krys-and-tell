<!-- resources/views/layouts/staff.blade.php -->
<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Krys&Tell</title>

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>


    <style>
    /* ==========================================================
           Krys & Tell — Staff Layout (Dark mode FIXED FOR REAL)
           + Motion Pack (loader, modal confirm, toasts, micro-animations)
           ========================================================== */

    body { overflow-x: hidden; }

    /* ---------- Theme Tokens (LOCKED) ---------- */
    html {
        --kt-bg: #eef3fa !important;
        --kt-surface: rgba(255, 255, 255, .92) !important;
        --kt-surface-2: rgba(255, 255, 255, .98) !important;
        --kt-text: #0f172a !important;
        --kt-muted: rgba(15, 23, 42, .62) !important;
        --kt-border: rgba(15, 23, 42, .12) !important;
        --kt-shadow: 0 14px 36px rgba(15, 23, 42, .10) !important;
        --kt-primary: #0d6efd !important;
        --kt-primary-soft: rgba(13, 110, 253, .12) !important;

        --kt-input-bg: rgba(255, 255, 255, .92) !important;
        --kt-input-border: rgba(15, 23, 42, .16) !important;

        --kt-sidebar-bg: linear-gradient(180deg, #0d6efd 0%, #084298 100%) !important;
        --kt-sidebar-shadow: 0 12px 30px rgba(2, 117, 255, 0.25) !important;

        --kt-radius: 16px !important;
        --kt-radius-sm: 12px !important;

        /* Legacy vars used across blades */
        --text: var(--kt-text) !important;
        --muted: var(--kt-muted) !important;
        --card-border: 1px solid var(--kt-border) !important;
        --card-shadow: var(--kt-shadow) !important;
        --card-bg: var(--kt-surface) !important;

        /* Bootstrap bridge */
        --bs-body-bg: var(--kt-bg) !important;
        --bs-body-color: var(--kt-text) !important;
        --bs-border-color: var(--kt-border) !important;
        --bs-secondary-color: var(--kt-muted) !important;
        --bs-tertiary-color: var(--kt-muted) !important;
        --bs-secondary-bg: var(--kt-surface) !important;
        --bs-tertiary-bg: var(--kt-surface) !important;
    }

    html[data-theme="dark"] {
        color-scheme: dark;

        --kt-bg: #0b1220 !important;
        --kt-surface: rgba(17, 24, 39, .90) !important;
        --kt-surface-2: rgba(17, 24, 39, .96) !important;

        --kt-text: #f8fafc !important;
        --kt-muted: rgba(248, 250, 252, .74) !important;

        --kt-border: rgba(148, 163, 184, .18) !important;
        --kt-shadow: 0 18px 48px rgba(0, 0, 0, .55) !important;

        --kt-primary: #60a5fa !important;
        --kt-primary-soft: rgba(96, 165, 250, .14) !important;

        --kt-input-bg: rgba(17, 24, 39, .92) !important;
        --kt-input-border: rgba(148, 163, 184, .24) !important;

        --kt-sidebar-bg: linear-gradient(180deg, #0b1220 0%, #0b162d 100%) !important;
        --kt-sidebar-shadow: 0 18px 48px rgba(0, 0, 0, .55) !important;

        --text: var(--kt-text) !important;
        --muted: var(--kt-muted) !important;
        --card-border: 1px solid var(--kt-border) !important;
        --card-shadow: var(--kt-shadow) !important;
        --card-bg: var(--kt-surface) !important;

        --bs-body-bg: var(--kt-bg) !important;
        --bs-body-color: var(--kt-text) !important;
        --bs-border-color: var(--kt-border) !important;
        --bs-secondary-color: var(--kt-muted) !important;
        --bs-tertiary-color: var(--kt-muted) !important;
        --bs-secondary-bg: var(--kt-surface) !important;
        --bs-tertiary-bg: var(--kt-surface) !important;
    }

    html { --kt-sidebar-w: 245px !important; }

    /* ---------- Base ---------- */
    * { box-sizing: border-box; }

    body {
        font-family: 'Poppins', sans-serif;
        background: var(--kt-bg);
        color: var(--kt-text);
        margin: 0;
    }

    a { color: var(--kt-primary); }
    a:hover { opacity: .92; }

    .text-muted, small, .small { color: var(--kt-muted) !important; }

    hr {
        border-color: var(--kt-border) !important;
        opacity: 1;
    }

    html[data-theme="dark"] .content .text-dark,
    html[data-theme="dark"] .content .text-black,
    html[data-theme="dark"] .content .text-body {
        color: var(--kt-text) !important;
    }

    /* ---------- Layout ---------- */
    .layout {
        display: flex;
        min-height: 100vh;
        width: 100%;
    }

    /* ---------- Sidebar ---------- */
    .sidebar {
        width: 245px;
        color: #fff;
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
        display: block;
    }

    .brand h4 {
        margin: 0;
        font-weight: 800;
        font-size: 16px;
        line-height: 1.1;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .brand small {
        display: block;
        font-size: 12px;
        color: rgba(255, 255, 255, .75) !important;
        margin-top: 2px;
    }

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

    .sidebar-menu::-webkit-scrollbar { width: 8px; }
    .sidebar-menu::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, .18);
        border-radius: 999px;
    }
    .sidebar-menu::-webkit-scrollbar-track { background: rgba(255, 255, 255, .06); }

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
        position: relative; /* ✅ for badges */
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

    /* ✅ Messages unread badge (sidebar) */
    .kt-nav-badge{
        position:absolute;
        top: 8px;
        right: 10px;
        min-width: 20px;
        height: 18px;
        padding: 0 6px;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        font-size: 11px;
        font-weight: 950;
        border-radius: 999px;
        background: rgba(239, 68, 68, .22);
        border: 1px solid rgba(239, 68, 68, .35);
        color: #fff;
        box-shadow: 0 0 0 2px rgba(255,255,255,.12);
        line-height: 1;
    }

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
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 12px;
        transition: .18s ease;
    }

    .logout-btn:hover {
        background: rgba(220, 53, 69, 0.95);
        transform: translateY(-1px);
    }

    /* ---------- Content ---------- */
    .content {
        flex: 1;
        width: 100%;
        min-width: 0;
        overflow-x: hidden;
        padding: 16px;
        color: var(--kt-text);
        position: relative;
    }

    @media (min-width: 768px) { .content { padding: 22px; } }
    @media (min-width: 1200px) { .content { padding: 28px; } }

    html[data-theme="dark"] .content {
        background:
            radial-gradient(900px 420px at 15% 0%, rgba(96, 165, 250, .06), transparent 55%),
            radial-gradient(900px 420px at 95% 10%, rgba(167, 139, 250, .05), transparent 55%);
        border-radius: 18px;
    }

    /* ---------- Global Surfaces ---------- */
    .card, .kt-card, .section-box, .welcome, .stat-card, .list-item, .receipt-container,
    .modal-content, .dropdown-menu, .toast, .offcanvas, .list-group-item {
        background: var(--kt-surface);
        border: 1px solid var(--kt-border);
        box-shadow: var(--kt-shadow);
        color: var(--kt-text);
        border-radius: var(--kt-radius);
    }

    /* ---------- Tables ---------- */
    .table, table { color: var(--kt-text); }
    .table td, .table th { border-color: var(--kt-border) !important; }

    .table thead th {
        background: rgba(248, 250, 252, .95);
        color: var(--kt-muted) !important;
        font-weight: 800;
        letter-spacing: .02em;
        border-bottom: 1px solid var(--kt-border) !important;
        white-space: nowrap;
    }

    html[data-theme="dark"] .table thead th {
        background: rgba(2, 6, 23, .55) !important;
        color: var(--kt-muted) !important;
    }

    .table tbody td {
        color: var(--kt-text);
        vertical-align: middle;
    }

    html[data-theme="dark"] .table tbody td {
        border-color: rgba(148, 163, 184, .18) !important;
    }

    .table tbody tr:hover { background: rgba(13, 110, 253, .06) !important; }
    html[data-theme="dark"] .table tbody tr:hover { background: rgba(96, 165, 250, .08) !important; }

    .table-responsive {
        background: transparent !important;
        border: 0 !important;
        box-shadow: none !important;
    }

    /* ---------- Inputs ---------- */
    .form-control, .form-select, .input-group-text {
        background: var(--kt-input-bg) !important;
        color: var(--kt-text) !important;
        border-color: var(--kt-input-border) !important;
        border-radius: 12px !important;
    }

    .form-control:focus, .form-select:focus {
        border-color: rgba(96, 165, 250, .55) !important;
        box-shadow: 0 0 0 3px rgba(96, 165, 250, .18) !important;
    }

    html[data-theme="dark"] .form-control::placeholder,
    html[data-theme="dark"] textarea::placeholder {
        color: rgba(248, 250, 252, .58) !important;
    }

    /* ---------- Buttons (Bootstrap “light” variants) ---------- */
    html[data-theme="dark"] .btn-light,
    html[data-theme="dark"] .btn-secondary,
    html[data-theme="dark"] .btn-outline-secondary,
    html[data-theme="dark"] .btn-outline-dark {
        background: rgba(2, 6, 23, .62) !important;
        color: var(--kt-text) !important;
        border-color: var(--kt-border) !important;
    }

    html[data-theme="dark"] .btn-light:hover,
    html[data-theme="dark"] .btn-secondary:hover,
    html[data-theme="dark"] .btn-outline-secondary:hover,
    html[data-theme="dark"] .btn-outline-dark:hover {
        background: rgba(17, 24, 39, .92) !important;
    }

    /* ---------- Top bar icons + approval popover ---------- */
    .kt-top-icon {
        width: 42px;
        height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        border: 1px solid var(--kt-border);
        background: var(--kt-surface);
        color: var(--kt-text);
        transition: .15s ease;
    }

    .kt-top-icon:hover { background: var(--kt-surface-2); }

    .kt-dot {
        position: absolute;
        top: 9px;
        right: 9px;
        width: 10px;
        height: 10px;
        border-radius: 999px;
        background: #ef4444;
        box-shadow: 0 0 0 2px rgba(255, 255, 255, .95);
    }

    html[data-theme="dark"] .kt-dot {
        box-shadow: 0 0 0 2px rgba(2, 6, 23, .85);
    }

    .kt-popover {
        position: absolute;
        top: 54px;
        right: 0;
        width: 380px;
        max-width: calc(100vw - 24px);
        border-radius: 16px;
        background: var(--kt-surface-2);
        border: 1px solid var(--kt-border);
        box-shadow: var(--kt-shadow);
        z-index: 2500;
        overflow: hidden;

        opacity: 0;
        transform: translateY(-8px) scale(.98);
        pointer-events: none;
        visibility: hidden;
        transition: opacity 160ms ease, transform 180ms cubic-bezier(.2, .8, .2, 1), visibility 0s linear 180ms;
    }

    .kt-popover.show {
        opacity: 1;
        transform: translateY(0) scale(1);
        pointer-events: auto;
        visibility: visible;
        transition: opacity 160ms ease, transform 180ms cubic-bezier(.2, .8, .2, 1), visibility 0s;
    }

    .kt-popover .kt-pop-h {
        padding: 12px 14px;
        border-bottom: 1px solid var(--kt-border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }

    .kt-popover .kt-pop-title {
        font-weight: 900;
        font-size: 14px;
        margin: 0;
    }

    .kt-popover .kt-badge {
        font-size: 12px;
        padding: 4px 10px;
        border-radius: 999px;
        background: var(--kt-primary-soft);
        border: 1px solid rgba(37, 99, 235, .25);
        color: var(--kt-text);
    }

    .kt-popover .kt-pop-body {
        padding: 10px;
        max-height: 360px;
        overflow: auto;
    }

    .kt-popover .kt-item {
        border: 1px solid var(--kt-border);
        background: var(--kt-surface);
        border-radius: 14px;
        padding: 10px 12px;
        margin-bottom: 10px;
    }

    .kt-popover .kt-item:last-child { margin-bottom: 0; }

    .kt-popover .kt-item .top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
    }

    .kt-popover .kt-item .name {
        font-weight: 900;
        font-size: 13px;
        margin: 0;
        line-height: 1.2;
    }

    .kt-popover .kt-item .meta {
        font-size: 12px;
        opacity: .95;
        margin-top: 4px;
    }

    .kt-popover .kt-actions {
        display: flex;
        gap: 8px;
        margin-top: 10px;
    }

    .kt-popover .kt-actions form { margin: 0; }

    .kt-popover .btn-mini {
        padding: 6px 10px;
        border-radius: 10px;
        font-weight: 800;
        font-size: 12px;
    }

    .kt-popover .btn-approve {
        background: rgba(34, 197, 94, .15);
        border: 1px solid rgba(34, 197, 94, .25);
        color: #16a34a !important;
    }

    .kt-popover .btn-decline {
        background: rgba(239, 68, 68, .15);
        border: 1px solid rgba(239, 68, 68, .25);
        color: #ef4444 !important;
    }

    /* ---------- Mobile Sidebar Drawer ---------- */
    .side-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, .35);
        z-index: 1999;
        opacity: 0;
        pointer-events: none;
        transition: opacity .18s ease;
    }

    .side-overlay.show {
        opacity: 1;
        pointer-events: auto;
    }

    .menu-toggle {
        display: none;
        width: 42px;
        height: 42px;
        border-radius: 12px;
        border: 1px solid var(--kt-border);
        background: var(--kt-surface);
        box-shadow: var(--kt-shadow);
        place-items: center;
        cursor: pointer;
        color: var(--kt-text);
    }

    .menu-toggle:hover { background: var(--kt-surface-2); }

    @media (max-width: 900px) {
        .menu-toggle { display: grid; }

        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            height: 100dvh;
            z-index: 2000;
            transform: translateX(-105%);
            transition: transform .18s ease;
        }

        .sidebar.open { transform: translateX(0); }
        .content { padding: 14px; }
    }

    /* ==========================================================
           ✅ DARK MODE HAMMER
           ========================================================== */
    html[data-theme="dark"] body .content { color: var(--kt-text) !important; }

    html[data-theme="dark"] body .content :is(input:not([type="checkbox"]):not([type="radio"]):not([type="range"]):not([type="color"]),
        textarea, select) {
        background: var(--kt-input-bg) !important;
        color: var(--kt-text) !important;
        border-color: var(--kt-input-border) !important;
    }

    html[data-theme="dark"] body .content :is(input, textarea, select)::placeholder {
        color: rgba(248, 250, 252, .58) !important;
    }

    html[data-theme="dark"] body .content select,
    html[data-theme="dark"] body .content select option,
    html[data-theme="dark"] body .content datalist option {
        background-color: rgba(17, 24, 39, .98) !important;
        color: var(--kt-text) !important;
    }

    html[data-theme="dark"] body .content input[type="date"]::-webkit-calendar-picker-indicator,
    html[data-theme="dark"] body .content input[type="time"]::-webkit-calendar-picker-indicator {
        filter: invert(1);
        opacity: .85;
    }

    html[data-theme="dark"] body .content :is(label, legend, .form-label, .col-form-label, .form-check-label, .input-group-text) {
        color: var(--kt-text) !important;
    }

    html[data-theme="dark"] body .content :is(.form-text, .help-text, .hint, .subtitle, .muted, small, .small) {
        color: var(--kt-muted) !important;
    }

    html[data-theme="dark"] body .content .dropdown-menu {
        background: var(--kt-surface-2) !important;
        border-color: var(--kt-border) !important;
        box-shadow: var(--kt-shadow) !important;
    }

    html[data-theme="dark"] body .content .dropdown-item { color: var(--kt-text) !important; }
    html[data-theme="dark"] body .content .dropdown-item:hover { background: rgba(96, 165, 250, .10) !important; }

    html[data-theme="dark"] body .content :is(input[readonly], textarea[readonly]) {
        background: rgba(2, 6, 23, .55) !important;
        color: rgba(248, 250, 252, .86) !important;
    }

    /* ==========================================================
           ✅ MOTION PACK (Loader + Confirm Modal + Toasts + Micro)
           ========================================================== */
    @media (prefers-reduced-motion: reduce) {
        * {
            animation-duration: .001ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: .001ms !important;
            scroll-behavior: auto !important;
        }
    }

    :where(.kt-top-icon, .menu-toggle, .logout-btn, .sidebar-menu a, button, .btn) {
        transition: transform 140ms ease, opacity 140ms ease, background 140ms ease, box-shadow 140ms ease;
    }

    :where(.kt-top-icon, .menu-toggle, .sidebar-menu a):active { transform: scale(.98); }
    :where(.btn, button):active { transform: translateY(1px) scale(.99); }

    .kt-loader {
        position: fixed;
        top: 0; right: 0; bottom: 0;
        left: var(--kt-sidebar-w, 245px);
        display: grid;
        place-items: center;
        background: rgba(2, 6, 23, .35);
        backdrop-filter: blur(6px);
        opacity: 0;
        pointer-events: none;
        transition: opacity 160ms ease;
        z-index: 9999;
    }

    @media (max-width: 900px) { .kt-loader { left: 0; } }

    .kt-loader.is-active {
        opacity: 1;
        pointer-events: auto;
    }

    .kt-loader__card {
        min-width: 220px;
        padding: 14px 16px;
        border-radius: 16px;
        border: 1px solid var(--kt-border, rgba(148, 163, 184, .25));
        background: var(--kt-surface, rgba(15, 23, 42, .92));
        color: var(--kt-text, #e2e8f0);
        box-shadow: var(--kt-shadow, 0 10px 25px rgba(0, 0, 0, .18));
        display: flex;
        align-items: center;
        gap: 12px;
        transform: translateY(8px) scale(.98);
        transition: transform 160ms ease;
    }

    .kt-loader.is-active .kt-loader__card { transform: translateY(0) scale(1); }

    .kt-spinner {
        width: 22px;
        height: 22px;
        border-radius: 999px;
        border: 3px solid rgba(148, 163, 184, .35);
        border-top-color: rgba(255, 255, 255, .9);
        animation: ktSpin .8s linear infinite;
    }

    @keyframes ktSpin { to { transform: rotate(360deg); } }

    .kt-loader__text { font-size: 13px; opacity: .9; }

    .kt-modal {
        position: fixed;
        inset: 0;
        display: grid;
        place-items: center;
        opacity: 0;
        pointer-events: none;
        transition: opacity 160ms ease;
        z-index: 9998;
    }

    .kt-modal.is-open { opacity: 1; pointer-events: auto; }

    .kt-modal__backdrop {
        position: absolute;
        inset: 0;
        background: rgba(2, 6, 23, .45);
        backdrop-filter: blur(4px);
    }

    .kt-modal__panel {
        position: relative;
        width: min(92vw, 440px);
        border-radius: 18px;
        border: 1px solid var(--kt-border, rgba(148, 163, 184, .25));
        background: var(--kt-surface, rgba(15, 23, 42, .95));
        color: var(--kt-text, #e2e8f0);
        box-shadow: var(--kt-shadow, 0 18px 40px rgba(0, 0, 0, .25));
        padding: 16px;
        transform: translateY(14px) scale(.98);
        transition: transform 180ms cubic-bezier(.2, .8, .2, 1);
    }

    .kt-modal.is-open .kt-modal__panel { transform: translateY(0) scale(1); }

    .kt-modal__title { margin: 0 0 6px; font-size: 16px; font-weight: 900; }
    .kt-modal__msg { margin: 0 0 14px; font-size: 13px; opacity: .85; }

    .kt-modal__actions {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
        flex-wrap: wrap;
    }

    .kt-btn {
        border: 1px solid transparent;
        border-radius: 12px;
        padding: 10px 12px;
        font-size: 13px;
        font-weight: 800;
        cursor: pointer;
        transition: transform 120ms ease, opacity 120ms ease, background 120ms ease;
        user-select: none;
    }

    .kt-btn:active { transform: scale(.98); }

    .kt-btn--ghost {
        background: transparent;
        border-color: var(--kt-border, rgba(148, 163, 184, .25));
        color: var(--kt-text, #e2e8f0);
    }

    .kt-btn--danger {
        background: rgba(239, 68, 68, .16);
        border-color: rgba(239, 68, 68, .35);
        color: #fecaca;
    }

    .kt-toasts {
        position: fixed;
        right: 14px;
        bottom: 14px;
        display: flex;
        flex-direction: column;
        gap: 10px;
        z-index: 9997;
        max-width: min(92vw, 420px);
        pointer-events: none;
    }

    @media (max-width: 768px) {
        .kt-toasts {
            left: 50%;
            right: auto;
            transform: translateX(-50%);
            bottom: auto;
            top: 14px;
            width: min(92vw, 420px);
        }
    }

    .kt-toast {
        pointer-events: auto;
        border-radius: 16px;
        border: 1px solid var(--kt-border);
        background: var(--kt-surface-2);
        box-shadow: var(--kt-shadow);
        color: var(--kt-text);
        padding: 12px 12px;
        display: flex;
        align-items: flex-start;
        gap: 10px;

        opacity: 0;
        transform: translateY(10px) scale(.98);
        transition: opacity 160ms ease, transform 180ms cubic-bezier(.2, .8, .2, 1);
    }

    .kt-toast.show { opacity: 1; transform: translateY(0) scale(1); }

    .kt-toast .icon {
        width: 34px;
        height: 34px;
        border-radius: 12px;
        display: grid;
        place-items: center;
        flex: 0 0 auto;
    }

    .kt-toast .title { font-weight: 900; font-size: 13px; margin: 0; line-height: 1.1; }
    .kt-toast .msg { font-size: 12px; opacity: .9; margin: 4px 0 0 0; }

    .kt-toast .close {
        margin-left: auto;
        border: 0;
        background: transparent;
        color: var(--kt-muted);
        font-size: 16px;
        line-height: 1;
        padding: 4px 6px;
        border-radius: 10px;
        cursor: pointer;
    }

    .kt-toast .close:hover { background: rgba(148, 163, 184, .12); }

    .kt-toast.success .icon {
        background: rgba(34, 197, 94, .14);
        border: 1px solid rgba(34, 197, 94, .20);
        color: #16a34a;
    }

    .kt-toast.danger .icon {
        background: rgba(239, 68, 68, .14);
        border: 1px solid rgba(239, 68, 68, .20);
        color: #ef4444;
    }

    .kt-toast.info .icon {
        background: rgba(59, 130, 246, .14);
        border: 1px solid rgba(59, 130, 246, .20);
        color: #2563eb;
    }

    .kt-toast.warning .icon {
        background: rgba(245, 158, 11, .14);
        border: 1px solid rgba(245, 158, 11, .20);
        color: #d97706;
    }
    </style>
</head>

@php
$routeName = request()->route() ? request()->route()->getName() : '';
@endphp

<body data-page="{{ $routeName }}" data-kt-live-scope="@yield('kt_live_scope')" data-kt-live-snapshot-url="{{ route('staff.live.snapshot') }}" data-kt-live-interval="@yield('kt_live_interval', 8000)">
    <div class="layout">

        <!-- mobile overlay -->
        <div class="side-overlay" id="sideOverlay"></div>

        <!-- SIDEBAR -->
        @php
            // ✅ unread message badge for sidebar/top
            $unreadMessages = 0;
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('contact_messages')
                    && \Illuminate\Support\Facades\Schema::hasColumn('contact_messages', 'read_at')) {
                    $unreadMessages = \App\Models\ContactMessage::query()->whereNull('read_at')->count();
                }
            } catch (\Throwable $e) { $unreadMessages = 0; }
        @endphp

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
                <a href="{{ route('staff.dashboard') }}"
                    class="{{ request()->routeIs('staff.dashboard') ? 'active' : '' }}">
                    <i class="fa fa-chart-line"></i> Dashboard
                </a>

                <a href="{{ route('staff.patients.index') }}"
                    class="{{ request()->routeIs('staff.patients.*') ? 'active' : '' }}">
                    <i class="fa fa-users"></i> Patients
                </a>

                <a href="{{ route('staff.visits.index') }}"
                    class="{{ request()->routeIs('staff.visits.*') ? 'active' : '' }}">
                    <i class="fa fa-calendar-check"></i> Visits
                </a>

                <a href="{{ route('staff.payments.index') }}"
                    class="{{ request()->routeIs('staff.payments.*') ? 'active' : '' }}">
                    <i class="fa fa-money-bill"></i> Payments
                </a>

                <a href="{{ route('staff.appointments.index') }}"
                    class="{{ request()->routeIs('staff.appointments.*') ? 'active' : '' }}">
                    <i class="fa fa-calendar-days"></i> Appointments
                </a>

                <a href="{{ route('staff.services.index') }}"
                    class="{{ request()->routeIs('staff.services.*') ? 'active' : '' }}">
                    <i class="fa fa-gear"></i> Services
                </a>

                <a href="{{ route('staff.messages.index') }}"
                    class="{{ request()->routeIs('staff.messages.*') ? 'active' : '' }}">
                    <i class="fa fa-inbox"></i> Messages
                    <span id="msgNavBadge" class="kt-nav-badge {{ $unreadMessages > 0 ? '' : 'd-none' }}">{{ $unreadMessages }}</span>
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
            {{-- Top bar: menu (left) + icons (right) --}}
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

                {{-- Right side --}}
                <div class="ms-auto d-flex align-items-center gap-2 position-relative">
                    {{-- ✅ Messages icon (with dot) --}}
                    <a href="{{ route('staff.messages.index') }}"
                       class="kt-top-icon position-relative text-decoration-none"
                       title="Messages">
                        <i class="fa-solid fa-envelope"></i>
                        <span id="msgTopDot" class="kt-dot {{ $unreadMessages > 0 ? '' : 'd-none' }}"></span>
                    </a>

                    {{-- Bell button --}}
                    <button type="button" id="approvalBell" class="kt-top-icon position-relative border-0"
                        title="Approval Requests" aria-haspopup="true" aria-expanded="false">
                        <i class="fa-solid fa-bell"></i>
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

                        {{-- Flash message --}}
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
                                        $doctorName = $a->doctor->name ?? ($a->dentist_name ?? 'Doctor');

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
                                            <form class="approval-form" data-action="approve" method="POST"
                                                action="{{ route('staff.approvals.approve', $a->id) }}">
                                                @csrf
                                                <button class="btn btn-mini btn-approve" type="submit">
                                                    <i class="fa-solid fa-check me-1"></i> Approve
                                                </button>
                                            </form>

                                            <form class="approval-form" data-action="decline" method="POST"
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

            <!-- ✅ Global Loader (CONTENT ONLY) -->
            <div id="ktLoader" class="kt-loader" aria-hidden="true">
                <div class="kt-loader__card" role="status" aria-live="polite">
                    <div class="kt-spinner"></div>
                    <div class="kt-loader__text">Loading…</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ✅ Toast container -->
    <div class="kt-toasts" id="ktToasts" aria-live="polite" aria-atomic="true"></div>

    <!-- ✅ Confirm Modal -->
    <div id="ktConfirm" class="kt-modal" aria-hidden="true">
        <div class="kt-modal__backdrop" data-close></div>

        <div class="kt-modal__panel" role="dialog" aria-modal="true" aria-labelledby="ktConfirmTitle">
            <h3 id="ktConfirmTitle" class="kt-modal__title">Confirm action</h3>
            <p id="ktConfirmMsg" class="kt-modal__msg">Are you sure?</p>

            <div class="kt-modal__actions">
                <button type="button" class="kt-btn kt-btn--ghost" data-close>Cancel</button>
                <button type="button" class="kt-btn kt-btn--danger" id="ktConfirmYes">Continue</button>
            </div>
        </div>
    </div>

    <script>
    (function() {
        // =========================
        // Theme
        // =========================
        const html = document.documentElement;
        const btnTheme = document.getElementById('themeToggle');
        const icon = document.getElementById('themeIcon');

        function applyTheme(theme) {
            html.setAttribute('data-theme', theme);
            localStorage.setItem('theme', theme);

            if (icon) {
                icon.classList.remove('fa-moon', 'fa-sun');
                icon.classList.add(theme === 'dark' ? 'fa-sun' : 'fa-moon');
            }
        }

        const saved = localStorage.getItem('theme') || 'light';
        applyTheme(saved);

        btnTheme?.addEventListener('click', function() {
            const next = (html.getAttribute('data-theme') === 'dark') ? 'light' : 'dark';
            applyTheme(next);
        });

        // =========================
        // Sidebar drawer (mobile)
        // =========================
        const side = document.getElementById('staffSidebar');
        const overlay = document.getElementById('sideOverlay');
        const btnMenu = document.getElementById('menuToggle');

        function closeSidebar() {
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

        // =========================
        // Toast helper
        // =========================
        const toastWrap = document.getElementById('ktToasts');

        function toastIcon(type) {
            switch (type) {
                case 'success': return '<i class="fa-solid fa-check"></i>';
                case 'danger':  return '<i class="fa-solid fa-triangle-exclamation"></i>';
                case 'warning': return '<i class="fa-solid fa-circle-exclamation"></i>';
                default:        return '<i class="fa-solid fa-circle-info"></i>';
            }
        }

        function escapeHtml(str) {
            return (str ?? '').toString()
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function showToast(type, title, message, ms = 2600) {
            if (!toastWrap) return;

            const el = document.createElement('div');
            el.className = `kt-toast ${type || 'info'}`;
            el.innerHTML = `
                <div class="icon">${toastIcon(type)}</div>
                <div>
                    <p class="title">${escapeHtml(title || 'Notice')}</p>
                    <p class="msg">${escapeHtml(message || '')}</p>
                </div>
                <button class="close" type="button" aria-label="Close">&times;</button>
            `;

            toastWrap.appendChild(el);
            requestAnimationFrame(() => el.classList.add('show'));

            const close = () => {
                el.classList.remove('show');
                setTimeout(() => el.remove(), 200);
            };

            el.querySelector('.close')?.addEventListener('click', close);
            if (ms > 0) setTimeout(close, ms);

            return el;
        }

        window.KTToast = { show: showToast };

        // =========================
        // Global Loader (CONTENT ONLY)
        // =========================
        const loader = document.getElementById('ktLoader');
        const KTLoader = {
            show() {
                if (!loader) return;
                loader.classList.add('is-active');
                loader.setAttribute('aria-hidden', 'false');
            },
            hide() {
                if (!loader) return;
                loader.classList.remove('is-active');
                loader.setAttribute('aria-hidden', 'true');
            }
        };
        window.KTLoader = KTLoader;

        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => KTLoader.hide(), 80);
        });

        window.addEventListener('beforeunload', () => KTLoader.show());

        document.addEventListener('submit', (e) => {
            const form = e.target;
            if (!form || !form.matches('form')) return;

            if (form.hasAttribute('data-no-loader')) return;
            if (form.classList.contains('approval-form')) return;
            if (form.closest('#approvalPopover')) return;

            KTLoader.show();
        });

        // =========================
        // Confirm Modal (data-confirm)
        // =========================
        const modal = document.getElementById('ktConfirm');
        const msgEl = document.getElementById('ktConfirmMsg');
        const titleEl = document.getElementById('ktConfirmTitle');
        const yesBtn = document.getElementById('ktConfirmYes');

        let pendingAction = null;

        function openConfirm({ title = 'Confirm action', message = 'Are you sure?', onYes = null, yesText = 'Continue' } = {}) {
            if (!modal) return;

            pendingAction = onYes;
            if (titleEl) titleEl.textContent = title;
            if (msgEl) msgEl.textContent = message;
            if (yesBtn) yesBtn.textContent = yesText;

            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
            setTimeout(() => yesBtn?.focus(), 0);
        }

        function closeConfirm() {
            if (!modal) return;
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
            pendingAction = null;
        }

        modal?.addEventListener('click', (e) => {
            if (e.target && e.target.hasAttribute('data-close')) closeConfirm();
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modal?.classList.contains('is-open')) closeConfirm();
        });

        yesBtn?.addEventListener('click', () => {
            if (typeof pendingAction === 'function') pendingAction();
            closeConfirm();
        });

        document.addEventListener('click', (e) => {
            const el = e.target.closest('[data-confirm]');
            if (!el) return;

            const msg = el.getAttribute('data-confirm') || 'Are you sure?';
            const title = el.getAttribute('data-confirm-title') || 'Confirm action';
            const yesText = el.getAttribute('data-confirm-yes') || 'Continue';

            const sel = el.getAttribute('data-confirm-form');
            const form = sel ? document.querySelector(sel) : el.closest('form');

            e.preventDefault();

            openConfirm({
                title,
                message: msg,
                yesText,
                onYes: () => {
                    if (form) {
                        KTLoader.show();
                        form.submit();
                    }
                }
            });
        });

        // =========================
        // Approval popover (bell) + AJAX approve/decline + ✅ LIVE POLLING
        // =========================
        const bell = document.getElementById('approvalBell');
        const pop = document.getElementById('approvalPopover');

        const badgeEl = document.getElementById('approvalBadge');
        const dotEl = document.getElementById('approvalDot');
        const flashEl = document.getElementById('approvalFlash');
        const listEl = document.getElementById('approvalList');

        const csrf =
            document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
            document.querySelector('input[name="_token"]')?.value ||
            '';

        const widgetUrl = @json(route('staff.approvals.widget'));

        function closePopover() {
            if (!pop) return;
            pop.classList.remove('show');
            pop.setAttribute('aria-hidden', 'true');
            bell?.setAttribute('aria-expanded', 'false');
        }

        function togglePopover(e) {
            e?.stopPropagation();
            if (!pop) return;

            const isOpen = pop.classList.contains('show');
            if (isOpen) closePopover();
            else {
                pop.classList.add('show');
                pop.setAttribute('aria-hidden', 'false');
                bell?.setAttribute('aria-expanded', 'true');
            }
        }

        function setCount(n) {
            n = Number(n || 0);
            if (badgeEl) badgeEl.textContent = String(n);
            if (dotEl) dotEl.classList.toggle('d-none', n <= 0);
        }

        function showFlash(type, text) {
            if (!flashEl) return;

            flashEl.classList.remove('d-none');
            flashEl.innerHTML = `
                <div class="alert alert-${type} py-2 px-3 mb-0" style="font-size:13px;">
                    ${escapeHtml(text)}
                </div>
            `;

            window.clearTimeout(showFlash._t);
            showFlash._t = window.setTimeout(() => flashEl.classList.add('d-none'), 2500);

            window.KTToast?.show(type, type === 'danger' ? 'Error' : 'Success', text, 2400);
        }

        function ensureEmptyState() {
            if (!listEl) return;
            const anyItem = listEl.querySelector('.kt-item');
            if (!anyItem) {
                listEl.innerHTML = `
                    <div class="text-center py-3" id="approvalEmpty">
                        <div class="fw-bold">No pending requests</div>
                        <div class="small text-muted">You're all caught up.</div>
                    </div>
                `;
            }
        }

        async function postAction(form) {
            const item = form.closest('.kt-item');
            const action = form.dataset.action || 'approve';

            const btns = item ? item.querySelectorAll('button') : form.querySelectorAll('button');
            btns.forEach(b => b.disabled = true);

            try {
                const body = new URLSearchParams();
                body.set('_token', csrf);

                const res = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrf,
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body
                });

                const data = await res.json().catch(() => ({}));

                if (!res.ok || data.ok === false) {
                    showFlash('danger', data.message || 'Action failed. Please try again.');
                    btns.forEach(b => b.disabled = false);
                    return;
                }

                showFlash('success', data.message || (action === 'approve' ? 'Booking approved.' : 'Booking declined.'));
                if (item) item.remove();

                if (typeof data.pendingCount !== 'undefined') setCount(data.pendingCount);
                else {
                    const current = Number(badgeEl?.textContent || 0);
                    setCount(Math.max(0, current - 1));
                }

                ensureEmptyState();
            } catch (e) {
                showFlash('danger', 'Network error. Please try again.');
                btns.forEach(b => b.disabled = false);
            }
        }

        pop?.addEventListener('submit', function(e) {
            const form = e.target.closest('form.approval-form');
            if (!form) return;
            e.preventDefault();
            e.stopPropagation();
            postAction(form);
        });

        bell?.addEventListener('click', togglePopover);
        pop?.addEventListener('click', (e) => e.stopPropagation());

        document.addEventListener('click', closePopover);
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closePopover(); });

        // ---------- ✅ LIVE: Poll widget() every 5s ----------
        let lastPending = Number(badgeEl?.textContent || 0);
        let polling = false;

        function renderItems(items) {
            if (!listEl) return;

            if (!Array.isArray(items) || items.length === 0) {
                ensureEmptyState();
                return;
            }

            const htmlItems = items.map(i => {
                const id = Number(i.id || 0);
                const patient = escapeHtml(i.patient || 'N/A');
                const service = escapeHtml(i.service || 'N/A');
                const doctor  = escapeHtml(i.doctor  || '—');
                const date    = escapeHtml(i.date    || '—');
                const time    = escapeHtml(i.time    || '—');

                const approveUrl = escapeHtml(i.approve_url || '');
                const declineUrl = escapeHtml(i.decline_url || '');

                return `
                    <div class="kt-item" data-approval-id="${id}">
                        <div class="top">
                            <div>
                                <p class="name">${patient}</p>
                                <div class="meta">
                                    <div><b>${service}</b></div>
                                    <div>${date} • ${time}</div>
                                    <div class="small text-muted">Doctor: ${doctor}</div>
                                </div>
                            </div>
                            <div class="small text-muted text-end">Pending</div>
                        </div>

                        <div class="kt-actions">
                            <form class="approval-form" data-action="approve" method="POST" action="${approveUrl}">
                                <button class="btn btn-mini btn-approve" type="submit">
                                    <i class="fa-solid fa-check me-1"></i> Approve
                                </button>
                            </form>

                            <form class="approval-form" data-action="decline" method="POST" action="${declineUrl}">
                                <button class="btn btn-mini btn-decline" type="submit">
                                    <i class="fa-solid fa-xmark me-1"></i> Decline
                                </button>
                            </form>
                        </div>
                    </div>
                `;
            }).join('');

            listEl.innerHTML = htmlItems;
        }

        async function pollApprovals() {
            if (polling) return;
            if (document.hidden) return;

            polling = true;
            try {
                const res = await fetch(widgetUrl + '?limit=8', {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    cache: 'no-store'
                });

                if (!res.ok) throw new Error('poll failed');

                const data = await res.json();
                const pendingCount = Number(data.pendingCount || 0);
                setCount(pendingCount);

                renderItems(data.items || []);

                if (pendingCount > lastPending) {
                    window.KTToast?.show('info', 'New booking', 'A new approval request arrived.', 2200);

                    bell?.style.setProperty('box-shadow', '0 0 0 4px rgba(34,197,94,.18)');
                    setTimeout(() => bell?.style.removeProperty('box-shadow'), 600);
                }

                lastPending = pendingCount;
            } catch (e) {
                // silent
            } finally {
                polling = false;
            }
        }

        pollApprovals();
        setInterval(pollApprovals, 5000);

        // =========================
        // ✅ Messages realtime polling (AJAX) + badges
        // =========================
        // =========================
// ✅ Messages realtime polling (AJAX) + badges (widget endpoint)
// =========================
const msgWidgetUrl = @json(route('staff.messages.widget'));
const msgNavBadge = document.getElementById('msgNavBadge');
const msgTopDot = document.getElementById('msgTopDot');

let msgSince = localStorage.getItem('kt_msg_since') || '';
let msgPolling = false;
let msgLastUnread = Number(msgNavBadge?.textContent || 0);

function setUnreadMessages(n) {
    n = Number(n || 0);

    if (msgNavBadge) {
        msgNavBadge.textContent = String(n);
        msgNavBadge.classList.toggle('d-none', n <= 0);
    }

    if (msgTopDot) {
        msgTopDot.classList.toggle('d-none', n <= 0);
    }

    window.dispatchEvent(new CustomEvent('kt:messages:count', { detail: { unreadCount: n } }));
}

function normalizeMsg(m){
    return {
        id: m.id,
        name: m.name,
        email: m.email,
        message: m.message,
        created_human: m.created_at, // ✅ matches your index listener
        show_url: m.show_url
    };
}

async function pollMessages() {
    if (msgPolling) return;
    if (document.hidden) return;

    msgPolling = true;

    const prevSince = msgSince;

    try {
        const url = msgWidgetUrl + '?limit=20' + (msgSince ? ('&since=' + encodeURIComponent(msgSince)) : '');
        const res = await fetch(url, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            cache: 'no-store'
        });

        const data = await res.json().catch(() => ({}));
        if (!res.ok || !data.ok) throw new Error('messages poll failed');

        const unread = Number(data.unreadCount || 0);
        setUnreadMessages(unread);

        const latest = Array.isArray(data.latest) ? data.latest : [];
        if (latest[0]?.created_at_iso) {
            msgSince = latest[0].created_at_iso;
            localStorage.setItem('kt_msg_since', msgSince);
        }

        // compute "new messages" client-side from latest list
        let newMsgs = [];
        if (prevSince) {
            newMsgs = latest.filter(x => x.created_at_iso && x.created_at_iso > prevSince);
        } else if (Number(data.newCount || 0) > 0) {
            newMsgs = latest.slice(0, Math.min(5, latest.length));
        }

        if (newMsgs.length > 0) {
            window.KTToast?.show('info', 'New message', 'A new contact message arrived.', 2200);
            window.dispatchEvent(new CustomEvent('kt:messages:new', {
                detail: { messages: newMsgs.map(normalizeMsg) }
            }));
        }

        msgLastUnread = unread;
    } catch (e) {
        // silent
    } finally {
        msgPolling = false;
    }
}

pollMessages();
setInterval(pollMessages, 6000);


    })();
    </script>

<script src="{{ asset('js/kt-live.js') }}?v=1"></script>

</body>
</html>
