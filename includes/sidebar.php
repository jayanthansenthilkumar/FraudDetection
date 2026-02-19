<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$role = $_SESSION['role'] ?? '';

$menuItems = [
    'admin' => [
        'Overview' => [
            ['icon' => 'ri-dashboard-line', 'label' => 'Dashboard', 'page' => 'adminDashboard.php'],
            ['icon' => 'ri-bar-chart-box-line', 'label' => 'Analytics', 'page' => 'analytics.php'],
        ],
        'Management' => [
            ['icon' => 'ri-group-line', 'label' => 'Users', 'page' => 'users.php'],
            ['icon' => 'ri-bank-card-line', 'label' => 'Accounts', 'page' => 'accounts.php'],
            ['icon' => 'ri-exchange-funds-line', 'label' => 'Transactions', 'page' => 'transactions.php'],
            ['icon' => 'ri-alarm-warning-line', 'label' => 'Fraud Alerts', 'page' => 'alerts.php'],
        ],
        'System' => [
            ['icon' => 'ri-shield-check-line', 'label' => 'Fraud Rules', 'page' => 'fraudRules.php'],
            ['icon' => 'ri-file-list-3-line', 'label' => 'Audit Log', 'page' => 'auditLog.php'],
            ['icon' => 'ri-mail-line', 'label' => 'Messages', 'page' => 'messages.php'],
            ['icon' => 'ri-database-2-line', 'label' => 'Database', 'page' => 'database.php'],
            ['icon' => 'ri-settings-3-line', 'label' => 'Settings', 'page' => 'settings.php'],
        ],
    ],
    'analyst' => [
        'Analysis' => [
            ['icon' => 'ri-dashboard-line', 'label' => 'Dashboard', 'page' => 'analystDashboard.php'],
            ['icon' => 'ri-bar-chart-box-line', 'label' => 'Analytics', 'page' => 'analytics.php'],
            ['icon' => 'ri-exchange-funds-line', 'label' => 'Transactions', 'page' => 'transactions.php'],
        ],
        'Investigation' => [
            ['icon' => 'ri-alarm-warning-line', 'label' => 'Fraud Alerts', 'page' => 'alerts.php'],
            ['icon' => 'ri-shield-check-line', 'label' => 'Fraud Rules', 'page' => 'fraudRules.php'],
        ],
        'Communication' => [
            ['icon' => 'ri-mail-line', 'label' => 'Messages', 'page' => 'messages.php'],
            ['icon' => 'ri-user-settings-line', 'label' => 'Profile', 'page' => 'profile.php'],
        ],
    ],
    'customer' => [
        'Account' => [
            ['icon' => 'ri-dashboard-line', 'label' => 'Dashboard', 'page' => 'customerDashboard.php'],
            ['icon' => 'ri-bank-card-line', 'label' => 'My Accounts', 'page' => 'accounts.php'],
            ['icon' => 'ri-exchange-funds-line', 'label' => 'Transactions', 'page' => 'transactions.php'],
        ],
        'Activity' => [
            ['icon' => 'ri-mail-line', 'label' => 'Messages', 'page' => 'messages.php'],
            ['icon' => 'ri-user-settings-line', 'label' => 'Profile', 'page' => 'profile.php'],
        ],
    ]
];

$menu = $menuItems[$role] ?? [];
$dashboards = [
    'admin' => 'adminDashboard.php',
    'analyst' => 'analystDashboard.php',
    'customer' => 'customerDashboard.php'
];
$homeLink = $dashboards[$role] ?? 'login.php';
?>

<div class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <a href="<?= $homeLink ?>">
            <i class="ri-shield-flash-line"></i>
            <span>FraudShield</span>
        </a>
    </div>

    <nav class="sidebar-nav">
        <?php foreach ($menu as $section => $items): ?>
            <div class="nav-section">
                <span class="nav-section-title"><?= $section ?></span>
                <?php foreach ($items as $item): ?>
                    <a href="<?= $item['page'] ?>" class="nav-item <?= ($currentPage === $item['page']) ? 'active' : '' ?>">
                        <i class="<?= $item['icon'] ?>"></i>
                        <span><?= $item['label'] ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </nav>

    <div class="sidebar-footer">
        <a href="fraudBackend.php?action=logout" class="nav-item logout-item">
            <i class="ri-logout-box-r-line"></i>
            <span>Logout</span>
        </a>
    </div>
</div>
