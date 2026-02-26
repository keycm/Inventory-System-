<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/auth.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header">
        <!-- Logo Icon -->
        <i class="fas fa-seedling" style="font-size: 2rem; color: #689f38; margin-right: 10px;"></i>
        <!-- Title broken into lines if needed or flex -->
        <div style="display: flex; flex-direction: column;">
            <span style="font-size: 0.7rem; font-weight: 800; color: #2e7d32;">BIODIVERSITY-FRIENDLY</span>
            <span style="font-size: 0.7rem; font-weight: 800; color: #2e7d32;">ENTERPRISE (BDFE)</span>
        </div>
    </div>

    <div class="sidebar-menu-label">MENU</div>

    <div class="sidebar-nav">
        <a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fas fa-th-large"></i> Dashboard
        </a>
        <a href="inventory.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'inventory.php' ? 'active' : ''; ?>">
            <i class="fas fa-boxes"></i> Inventory
        </a>
        <a href="sales_report.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'sales_report.php' ? 'active' : ''; ?>">
            <i class="fas fa-coins"></i> Sales
        </a>
        <a href="capital_recovery.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'capital_recovery.php' ? 'active' : ''; ?>">
            <i class="fas fa-chart-pie"></i> Finance
        </a>

        <!-- Gap -->
        <div style="height: 20px;"></div>

        <a href="#" class="<?php echo basename($_SERVER['PHP_SELF']) == 'user_account.php' ? 'active' : ''; ?>">
            <i class="fas fa-user"></i> User Account
        </a>
        <a href="sales_report.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'data_reports.php' ? 'active' : ''; ?>">
            <i class="fas fa-chart-bar"></i> Data Reports
        </a>
        <a href="#" class="<?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
            <i class="fas fa-cog"></i> Settings
        </a>

        <!-- Separator Line -->
        <div class="sidebar-separator"></div>

        <!-- POS Button -->
        <a href="pos.php" class="btn-pos-sidebar">POS</a>
    </div>
</div>

<div class="main-content">
    <div class="top-bar">
        <!-- Search Bar (Visual Only) -->
        <div class="search-bar" style="display: flex; align-items: center; background: #fff; padding: 8px 15px; border-radius: 20px; border: 1px solid #ddd; width: 300px;">
            <i class="fas fa-search" style="color: #999; margin-right: 10px;"></i>
            <input type="text" placeholder="Search" style="border: none; outline: none; font-size: 0.9rem; color: #666; width: 100%;">
        </div>

        <div class="top-icons">
            <i class="fas fa-sun"></i>
            <i class="fas fa-bell"></i>
        </div>

        <div class="user-profile">
            <div class="user-avatar">
                <!-- Use an icon or image -->
                <img src="https://ui-avatars.com/api/?name=Vincent+Paul&background=8bc34a&color=fff" alt="User">
            </div>
            <div class="user-info">
                <span class="user-name"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Vincent Paul Pena'); ?></span>
                <span class="user-role">Store Owner</span>
            </div>
            <i class="fas fa-chevron-down" style="font-size: 0.8rem; color: #666; margin-left: 5px;"></i>
            <a href="logout.php" style="color: #666; font-size: 1rem; margin-left: 10px;" title="Logout"><i class="fas fa-sign-out-alt"></i></a>
        </div>
    </div>
