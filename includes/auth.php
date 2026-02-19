<?php
session_start();

function checkUserAccess($isPublic = false) {
    $timeout = 1800; // 30 minutes

    if (!$isPublic) {
        if (!isset($_SESSION['user_id'])) {
            header("Location: login.php");
            exit();
        }

        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
            session_unset();
            session_destroy();
            header("Location: login.php?timeout=1");
            exit();
        }

        $_SESSION['last_activity'] = time();

        // Page access control
        $currentPage = basename($_SERVER['PHP_SELF']);
        $role = $_SESSION['role'] ?? '';

        $accessMap = [
            'admin' => [
                'adminDashboard.php', 'users.php', 'accounts.php', 'transactions.php',
                'alerts.php', 'analytics.php', 'fraudRules.php', 'auditLog.php',
                'messages.php', 'profile.php', 'settings.php', 'database.php'
            ],
            'analyst' => [
                'analystDashboard.php', 'transactions.php', 'alerts.php',
                'analytics.php', 'fraudRules.php', 'messages.php', 'profile.php'
            ],
            'customer' => [
                'customerDashboard.php', 'transactions.php', 'accounts.php',
                'messages.php', 'profile.php'
            ]
        ];

        $allowedPages = $accessMap[$role] ?? [];

        if (!in_array($currentPage, $allowedPages)) {
            $dashboards = [
                'admin' => 'adminDashboard.php',
                'analyst' => 'analystDashboard.php',
                'customer' => 'customerDashboard.php'
            ];
            $redirect = $dashboards[$role] ?? 'login.php';
            header("Location: $redirect");
            exit();
        }
    }

    // Anti-cache headers
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
}
?>
