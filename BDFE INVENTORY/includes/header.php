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
        <i class="fas fa-leaf" style="font-size: 2rem; color: #689f38; margin-right: 10px;"></i>
        <h2>BIODIVERSITY-FRIENDLY ENTERPRISE (BDFE)</h2>
    </div>

    <div class="sidebar-menu-label">MENU</div>

    <a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
        <i class="fas fa-tachometer-alt"></i> Dashboard
    </a>
    <a href="inventory.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'inventory.php' ? 'active' : ''; ?>">
        <i class="fas fa-boxes"></i> Inventory
    </a>
    <a href="sales_report.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'sales_report.php' ? 'active' : ''; ?>">
        <i class="fas fa-chart-line"></i> Sales
    </a>
    <a href="capital_recovery.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'capital_recovery.php' ? 'active' : ''; ?>">
        <i class="fas fa-coins"></i> Finance
    </a>
    <a href="#" class="<?php echo basename($_SERVER['PHP_SELF']) == 'user_account.php' ? 'active' : ''; ?>">
        <i class="fas fa-user"></i> User Account
    </a>
    <a href="sales_report.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'data_reports.php' ? 'active' : ''; ?>">
        <i class="fas fa-file-alt"></i> Data Reports
    </a>
    <a href="#" class="<?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
        <i class="fas fa-cog"></i> Settings
    </a>

    <div class="pos-btn-container">
        <a href="pos.php" class="btn-pos-sidebar">POS</a>
    </div>
</div>

<div class="main-content">
    <div class="top-bar">
        <!-- Search Bar (Visual Only) -->
        <div class="search-bar" style="display: flex; align-items: center; background: #fff; padding: 5px 15px; border-radius: 20px; border: 1px solid #ddd;">
            <i class="fas fa-search" style="color: #999; margin-right: 10px;"></i>
            <input type="text" placeholder="Search..." style="border: none; outline: none; font-size: 0.9rem; color: #666;">
        </div>

        <div class="top-icons">
            <i class="fas fa-sun"></i>
            <i class="fas fa-bell"></i>
        </div>

        <div class="user-profile">
            <div class="user-info">
                <span class="user-name"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Vincent Paul Pena'); ?></span>
                <span class="user-role">Store Owner</span>
            </div>
            <div class="user-avatar">
                <i class="fas fa-user"></i>
            </div>
            <a href="logout.php" style="color: #666; font-size: 1.2rem; margin-left: 10px;"><i class="fas fa-sign-out-alt"></i></a>
        </div>
    </div>
