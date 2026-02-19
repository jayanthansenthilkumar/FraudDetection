<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$role = $_SESSION['role'] ?? '';
$userName = $_SESSION['name'] ?? 'User';

// Notification counts
include_once __DIR__ . '/../db.php';
$userId = $_SESSION['user_id'] ?? 0;

$unreadMessages = 0;
$alertCount = 0;
$msgQuery = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM messages WHERE recipient_id = $userId AND is_read = 0");
if ($msgQuery && $row = mysqli_fetch_assoc($msgQuery)) {
    $unreadMessages = $row['cnt'];
}

if ($role === 'analyst') {
    $alertQuery = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM fraud_alerts WHERE status IN ('new', 'investigating') AND (assigned_to = $userId OR assigned_to IS NULL)");
    if ($alertQuery && $row = mysqli_fetch_assoc($alertQuery)) {
        $alertCount = $row['cnt'];
    }
} elseif ($role === 'admin') {
    $alertQuery = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM fraud_alerts WHERE status = 'new'");
    if ($alertQuery && $row = mysqli_fetch_assoc($alertQuery)) {
        $alertCount = $row['cnt'];
    }
} elseif ($role === 'customer') {
    $alertQuery = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM fraud_alerts fa JOIN transactions t ON fa.transaction_id = t.id JOIN accounts a ON t.account_id = a.id WHERE a.user_id = $userId AND fa.status = 'new'");
    if ($alertQuery && $row = mysqli_fetch_assoc($alertQuery)) {
        $alertCount = $row['cnt'];
    }
}

$totalNotifs = $unreadMessages + $alertCount;
?>

<div class="dashboard-header">
    <div class="header-left">
        <button class="sidebar-toggle" onclick="toggleSidebar()">
            <i class="ri-menu-line"></i>
        </button>
        <div class="header-search">
            <i class="ri-search-line"></i>
            <input type="text" placeholder="Search transactions, alerts..." id="globalSearch" autocomplete="off">
        </div>
    </div>
    <div class="header-right">
        <div class="header-notifications" onclick="toggleNotifDropdown()">
            <i class="ri-notification-3-line"></i>
            <?php if ($totalNotifs > 0): ?>
                <span class="notif-badge"><?= $totalNotifs ?></span>
            <?php endif; ?>
            <div class="notif-dropdown" id="notifDropdown">
                <div class="notif-dropdown-header">
                    <h4>Notifications</h4>
                    <span><?= $totalNotifs ?> new</span>
                </div>
                <div class="notif-dropdown-body">
                    <?php if ($alertCount > 0): ?>
                        <a href="alerts.php" class="notif-item">
                            <i class="ri-alarm-warning-line" style="color: var(--danger);"></i>
                            <div>
                                <strong><?= $alertCount ?> Fraud Alert<?= $alertCount > 1 ? 's' : '' ?></strong>
                                <small>Require attention</small>
                            </div>
                        </a>
                    <?php endif; ?>
                    <?php if ($unreadMessages > 0): ?>
                        <a href="messages.php" class="notif-item">
                            <i class="ri-mail-line" style="color: var(--primary);"></i>
                            <div>
                                <strong><?= $unreadMessages ?> Unread Message<?= $unreadMessages > 1 ? 's' : '' ?></strong>
                                <small>Click to view</small>
                            </div>
                        </a>
                    <?php endif; ?>
                    <?php if ($totalNotifs == 0): ?>
                        <div class="notif-item" style="justify-content: center; opacity: 0.6;">
                            <span>No new notifications</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="header-user" onclick="toggleUserDropdown()">
            <div class="user-avatar">
                <?= strtoupper(substr($userName, 0, 1)) ?>
            </div>
            <span class="user-name"><?= htmlspecialchars($userName) ?></span>
            <i class="ri-arrow-down-s-line"></i>
            <div class="user-dropdown" id="userDropdown">
                <div class="user-dropdown-header">
                    <strong><?= htmlspecialchars($userName) ?></strong>
                    <small><?= ucfirst($role) ?></small>
                </div>
                <a href="profile.php"><i class="ri-user-settings-line"></i> Profile</a>
                <?php if ($role === 'admin'): ?>
                    <a href="settings.php"><i class="ri-settings-3-line"></i> Settings</a>
                <?php endif; ?>
                <hr>
                <a href="fraudBackend.php?action=logout" class="logout-link"><i class="ri-logout-box-r-line"></i> Logout</a>
            </div>
        </div>
    </div>
</div>
