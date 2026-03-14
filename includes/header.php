<?php if (session_status() === PHP_SESSION_NONE)
    session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VSMS — <?= htmlspecialchars($pageTitle ?? 'Dashboard')?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-light: #818cf8;
            --primary-dark: #3730a3;
            --accent: #7c3aed;
            --bg: #f1f5f9;
            --sidebar-bg: #1e1b4b;
            --sidebar-text: #c7d2fe;
            --sidebar-active: #818cf8;
            --card-bg: #ffffff;
            --text: #1e293b;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
        }

        /* ── Sidebar ─────────────────────────────── */
        .sidebar {
            width: 260px;
            min-height: 100vh;
            background: var(--sidebar-bg);
            padding: 1.5rem 0;
            position: fixed;
            top: 0; left: 0;
            z-index: 100;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s ease;
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0 1.25rem;
            margin-bottom: 2rem;
            text-decoration: none;
        }
        .sidebar-brand .brand-icon {
            width: 40px; height: 40px;
            background: linear-gradient(135deg, var(--primary-light), var(--accent));
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem;
        }
        .sidebar-brand h1 {
            font-size: 1.1rem;
            font-weight: 800;
            color: #fff;
            letter-spacing: 1px;
            margin: 0;
        }
        .sidebar-brand span {
            font-size: 0.65rem;
            color: var(--sidebar-text);
            opacity: 0.7;
            display: block;
            margin-top: 2px;
            letter-spacing: 0.5px;
        }

        .nav-section {
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: var(--sidebar-text);
            opacity: 0.5;
            padding: 0 1.25rem;
            margin: 1.25rem 0 0.5rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.6rem 1.25rem;
            color: var(--sidebar-text);
            text-decoration: none;
            font-size: 0.88rem;
            font-weight: 500;
            border-left: 3px solid transparent;
            transition: all 0.2s;
        }
        .nav-link:hover {
            background: rgba(255,255,255,0.06);
            color: #fff;
        }
        .nav-link.active {
            background: rgba(99,102,241,0.15);
            color: var(--sidebar-active);
            border-left-color: var(--sidebar-active);
            font-weight: 600;
        }
        .nav-link i { font-size: 1.1rem; width: 22px; text-align: center; }

        .sidebar-footer {
            margin-top: auto;
            padding: 1rem 1.25rem;
            border-top: 1px solid rgba(255,255,255,0.08);
        }
        .sidebar-footer .user-info {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            margin-bottom: 0.75rem;
        }
        .sidebar-footer .user-avatar {
            width: 34px; height: 34px;
            border-radius: 8px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            display: flex; align-items: center; justify-content: center;
            color: #fff;
            font-weight: 700;
            font-size: 0.85rem;
        }
        .sidebar-footer .user-name {
            font-weight: 600;
            font-size: 0.85rem;
            color: #fff;
        }
        .sidebar-footer .user-role {
            font-size: 0.7rem;
            color: var(--sidebar-text);
            opacity: 0.7;
        }
        .sidebar-footer .btn-logout {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            padding: 0.45rem;
            border-radius: 8px;
            background: rgba(239,68,68,0.12);
            color: #f87171;
            border: none;
            font-size: 0.82rem;
            font-weight: 600;
            text-decoration: none;
            transition: background 0.2s;
        }
        .sidebar-footer .btn-logout:hover { background: rgba(239,68,68,0.2); color: #fca5a5; }

        /* ── Mobile Hamburger ────────────────────── */
        .sidebar-toggle {
            display: none;
            position: fixed;
            top: 1rem; left: 1rem;
            z-index: 200;
            width: 40px; height: 40px;
            border-radius: 10px;
            background: var(--sidebar-bg);
            color: #fff;
            border: none;
            font-size: 1.2rem;
            cursor: pointer;
        }
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 99;
        }

        /* ── Main Content ────────────────────────── */
        .main-content {
            margin-left: 260px;
            flex: 1;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .top-bar {
            background: var(--card-bg);
            border-bottom: 1px solid var(--border);
            padding: 0.9rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 50;
        }
        .top-bar h2 {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--text);
        }
        .top-bar .breadcrumb {
            font-size: 0.78rem;
            color: var(--text-muted);
            margin: 0;
        }

        .page-body {
            padding: 1.5rem;
            flex: 1;
        }

        /* ── Cards ────────────────────────────────── */
        .card-vsms {
            background: var(--card-bg);
            border-radius: 14px;
            border: 1px solid var(--border);
            overflow: hidden;
            transition: box-shadow 0.2s;
        }
        .card-vsms:hover { box-shadow: 0 4px 20px rgba(0,0,0,0.06); }
        .card-vsms .card-header {
            background: transparent;
            border-bottom: 1px solid var(--border);
            padding: 0.85rem 1.15rem;
            font-weight: 700;
            font-size: 0.9rem;
            color: var(--text);
        }
        .card-vsms .card-body { padding: 1.15rem; }

        /* ── Stat Cards ──────────────────────────── */
        .stat-card {
            border-radius: 14px;
            padding: 1.25rem;
            position: relative;
            overflow: hidden;
        }
        .stat-card::after {
            content: '';
            position: absolute;
            top: -30%; right: -15%;
            width: 120px; height: 120px;
            border-radius: 50%;
            background: rgba(255,255,255,0.08);
        }
        .stat-icon {
            width: 42px; height: 42px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem;
            margin-bottom: 0.75rem;
        }
        .stat-val {
            font-size: 1.6rem;
            font-weight: 800;
            line-height: 1;
        }
        .stat-label {
            font-size: 0.78rem;
            opacity: 0.85;
            margin-top: 0.3rem;
            font-weight: 500;
        }

        /* ── Tables ──────────────────────────────── */
        .table-vsms {
            font-size: 0.85rem;
            margin-bottom: 0;
        }
        .table-vsms thead th {
            background: #f8fafc;
            border-bottom: 2px solid var(--border);
            font-weight: 700;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-muted);
            padding: 0.7rem 1rem;
            white-space: nowrap;
        }
        .table-vsms tbody td {
            padding: 0.65rem 1rem;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
        }
        .table-vsms tbody tr:hover { background: #fafbfe; }

        /* ── Badges ──────────────────────────────── */
        .badge-available { background: #dcfce7; color: #166534; font-weight: 600; }
        .badge-sold { background: #fef2f2; color: #991b1b; font-weight: 600; }
        .badge-cash { background: #dcfce7; color: #166534; }
        .badge-card { background: #dbeafe; color: #1e40af; }
        .badge-upi { background: #fef3c7; color: #92400e; }

        /* ── Buttons ─────────────────────────────── */
        .btn-primary-vsms {
            background: linear-gradient(135deg, var(--primary), var(--accent));
            border: none;
            color: #fff;
            font-weight: 600;
            font-size: 0.85rem;
            border-radius: 8px;
            padding: 0.55rem 1.15rem;
            transition: all 0.2s;
        }
        .btn-primary-vsms:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 14px rgba(79,70,229,0.3);
            color: #fff;
        }
        .btn-outline-vsms {
            border: 1.5px solid var(--border);
            color: var(--text-muted);
            font-weight: 600;
            font-size: 0.85rem;
            border-radius: 8px;
            padding: 0.55rem 1.15rem;
            background: transparent;
            transition: all 0.2s;
        }
        .btn-outline-vsms:hover { border-color: var(--primary); color: var(--primary); }

        /* ── Forms ────────────────────────────────── */
        .form-label {
            font-weight: 600;
            font-size: 0.78rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .form-control, .form-select {
            border-radius: 8px;
            border: 1.5px solid var(--border);
            font-size: 0.88rem;
            padding: 0.55rem 0.85rem;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79,70,229,0.1);
        }

        /* ── Empty State ─────────────────────────── */
        .empty-state {
            padding: 3rem;
            text-align: center;
            color: var(--text-muted);
        }
        .empty-state i { font-size: 2.5rem; opacity: 0.3; }
        .empty-state p { margin-top: 0.75rem; font-size: 0.88rem; }

        /* ── Alert ────────────────────────────────── */
        .alert-dismissible {
            border-radius: 10px;
            font-size: 0.85rem;
            border: none;
        }

        /* ── Responsive ──────────────────────────── */
        @media (max-width: 991px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .sidebar-toggle { display: flex; align-items: center; justify-content: center; }
            .sidebar-overlay.active { display: block; }
            .main-content { margin-left: 0; }
            .top-bar { padding-left: 3.5rem; }
        }
    </style>
</head>
<body>

<!-- Mobile Toggle -->
<button class="sidebar-toggle" onclick="toggleSidebar()" aria-label="Toggle sidebar">
    <i class="bi-list"></i>
</button>
<div class="sidebar-overlay" onclick="toggleSidebar()"></div>

<!-- Sidebar -->
<aside class="sidebar">
    <a href="<?= BASE_URL?>pages/dashboard.php" class="sidebar-brand">
        <div class="brand-icon">🚗</div>
        <div>
            <h1>VSMS</h1>
            <span>Showroom Manager</span>
        </div>
    </a>

    <div class="nav-section">Main</div>
    <a href="<?= BASE_URL?>pages/dashboard.php" class="nav-link <?=($pageTitle ?? '') === 'Dashboard' ? 'active' : ''?>">
        <i class="bi-grid-1x2-fill"></i> Dashboard
    </a>
    <a href="<?= BASE_URL?>pages/vehicles.php" class="nav-link <?=($pageTitle ?? '') === 'Vehicle Inventory' ? 'active' : ''?>">
        <i class="bi-car-front-fill"></i> Vehicles
    </a>
    <a href="<?= BASE_URL?>pages/sales.php" class="nav-link <?=($pageTitle ?? '') === 'Sales Management' ? 'active' : ''?>">
        <i class="bi-receipt"></i> Sales
    </a>

    <div class="nav-section">People</div>
    <a href="<?= BASE_URL?>pages/customers.php" class="nav-link <?=($pageTitle ?? '') === 'Customer Management' ? 'active' : ''?>">
        <i class="bi-people-fill"></i> Customers
    </a>
    <a href="<?= BASE_URL?>pages/staff.php" class="nav-link <?=($pageTitle ?? '') === 'Staff Management' ? 'active' : ''?>">
        <i class="bi-person-badge-fill"></i> Staff
    </a>
    <a href="<?= BASE_URL?>pages/suppliers.php" class="nav-link <?=($pageTitle ?? '') === 'Supplier Management' ? 'active' : ''?>">
        <i class="bi-truck"></i> Suppliers
    </a>

    <?php if (isAdmin()): ?>
    <div class="nav-section">Admin</div>
    <a href="<?= BASE_URL?>pages/users.php" class="nav-link <?=($pageTitle ?? '') === 'User Accounts' ? 'active' : ''?>">
        <i class="bi-shield-lock-fill"></i> Users
    </a>
    <?php
endif; ?>

    <div class="sidebar-footer">
        <div class="user-info">
            <div class="user-avatar"><?= strtoupper(substr($_SESSION['username'] ?? 'U', 0, 1))?></div>
            <div>
                <div class="user-name"><?= htmlspecialchars($_SESSION['username'] ?? 'User')?></div>
                <div class="user-role"><?= ucfirst($_SESSION['role'] ?? 'staff')?></div>
            </div>
        </div>
        <a href="<?= BASE_URL?>includes/logout.php" class="btn-logout">
            <i class="bi-box-arrow-left"></i> Sign Out
        </a>
    </div>
</aside>

<!-- Main Content -->
<div class="main-content">
    <div class="top-bar">
        <div>
            <h2><?= htmlspecialchars($pageTitle ?? 'Dashboard')?></h2>
        </div>
        <div class="breadcrumb">
            <span>VSMS</span> &nbsp;/&nbsp; <span><?= htmlspecialchars($pageTitle ?? 'Dashboard')?></span>
        </div>
    </div>
    <div class="page-body">

<script>
function toggleSidebar() {
    document.querySelector('.sidebar').classList.toggle('open');
    document.querySelector('.sidebar-overlay').classList.toggle('active');
}
</script>
