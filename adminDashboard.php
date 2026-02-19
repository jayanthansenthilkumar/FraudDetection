<?php
include_once 'includes/auth.php';
checkUserAccess(false);
include_once 'db.php';

$userId = $_SESSION['user_id'];

// Stats
$totalUsers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM users"))['c'];
$totalTxns = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM transactions"))['c'];
$newAlerts = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM fraud_alerts WHERE status = 'new'"))['c'];
$fraudTxns = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM transactions WHERE is_fraud = 1"))['c'];
$totalVolume = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(amount),0) as s FROM transactions WHERE status = 'approved'"))['s'];
$totalAccounts = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM accounts"))['c'];
$activeUsers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM users WHERE status = 'active'"))['c'];
$frozenAccounts = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM accounts WHERE status = 'frozen'"))['c'];

// Recent alerts
$recentAlerts = mysqli_query($conn, "SELECT fa.*, t.amount, t.type as txn_type, u.name as user_name 
    FROM fraud_alerts fa 
    LEFT JOIN transactions t ON fa.transaction_id = t.id 
    LEFT JOIN users u ON fa.user_id = u.id 
    ORDER BY fa.created_at DESC LIMIT 10");

// Recent transactions
$recentTxns = mysqli_query($conn, "SELECT t.*, a.account_number, u.name as holder 
    FROM transactions t 
    JOIN accounts a ON t.account_id = a.id 
    JOIN users u ON a.user_id = u.id 
    ORDER BY t.created_at DESC LIMIT 10");

// User distribution
$userDist = mysqli_query($conn, "SELECT role, COUNT(*) as count FROM users GROUP BY role");
$roleData = [];
while ($row = mysqli_fetch_assoc($userDist)) { $roleData[$row['role']] = $row['count']; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - FraudShield</title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/loader.php'; ?>
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <?php include 'includes/dashboard_header.php'; ?>

        <div class="page-content">
            <div class="page-title">
                <h1><i class="ri-dashboard-line"></i> Admin Dashboard</h1>
                <p>System overview and fraud monitoring</p>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card-dash">
                    <div class="stat-icon" style="background: rgba(217,119,6,0.15); color: #D97706;">
                        <i class="ri-group-line"></i>
                    </div>
                    <div class="stat-details">
                        <span class="stat-value"><?= number_format($totalUsers) ?></span>
                        <span class="stat-label">Total Users</span>
                    </div>
                    <div class="stat-badge"><?= $activeUsers ?> active</div>
                </div>
                <div class="stat-card-dash">
                    <div class="stat-icon" style="background: rgba(59,130,246,0.15); color: #3B82F6;">
                        <i class="ri-exchange-funds-line"></i>
                    </div>
                    <div class="stat-details">
                        <span class="stat-value"><?= number_format($totalTxns) ?></span>
                        <span class="stat-label">Transactions</span>
                    </div>
                    <div class="stat-badge">$<?= number_format($totalVolume, 0) ?></div>
                </div>
                <div class="stat-card-dash">
                    <div class="stat-icon" style="background: rgba(239,68,68,0.15); color: #EF4444;">
                        <i class="ri-alarm-warning-line"></i>
                    </div>
                    <div class="stat-details">
                        <span class="stat-value"><?= number_format($newAlerts) ?></span>
                        <span class="stat-label">New Alerts</span>
                    </div>
                    <div class="stat-badge danger"><?= $fraudTxns ?> flagged</div>
                </div>
                <div class="stat-card-dash">
                    <div class="stat-icon" style="background: rgba(16,185,129,0.15); color: #10B981;">
                        <i class="ri-bank-card-line"></i>
                    </div>
                    <div class="stat-details">
                        <span class="stat-value"><?= number_format($totalAccounts) ?></span>
                        <span class="stat-label">Accounts</span>
                    </div>
                    <div class="stat-badge"><?= $frozenAccounts ?> frozen</div>
                </div>
            </div>

            <!-- Dashboard Grid -->
            <div class="dashboard-grid">
                <!-- Recent Alerts -->
                <div class="dash-card">
                    <div class="dash-card-header">
                        <h3><i class="ri-alarm-warning-line"></i> Recent Fraud Alerts</h3>
                        <a href="alerts.php" class="dash-card-link">View All <i class="ri-arrow-right-s-line"></i></a>
                    </div>
                    <div class="dash-card-body">
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Type</th>
                                        <th>Severity</th>
                                        <th>Status</th>
                                        <th>Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($alert = mysqli_fetch_assoc($recentAlerts)): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($alert['user_name'] ?? 'N/A') ?></td>
                                        <td><span class="badge badge-outline"><?= $alert['alert_type'] ?></span></td>
                                        <td><span class="badge severity-<?= $alert['severity'] ?>"><?= ucfirst($alert['severity']) ?></span></td>
                                        <td><span class="badge status-<?= $alert['status'] ?>"><?= ucfirst($alert['status']) ?></span></td>
                                        <td><?= date('M d, H:i', strtotime($alert['created_at'])) ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Recent Transactions -->
                <div class="dash-card">
                    <div class="dash-card-header">
                        <h3><i class="ri-exchange-funds-line"></i> Recent Transactions</h3>
                        <a href="transactions.php" class="dash-card-link">View All <i class="ri-arrow-right-s-line"></i></a>
                    </div>
                    <div class="dash-card-body">
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Account</th>
                                        <th>Holder</th>
                                        <th>Amount</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($txn = mysqli_fetch_assoc($recentTxns)): ?>
                                    <tr>
                                        <td class="mono"><?= $txn['account_number'] ?></td>
                                        <td><?= htmlspecialchars($txn['holder']) ?></td>
                                        <td class="<?= in_array($txn['type'], ['withdrawal','transfer','payment']) ? 'text-danger' : 'text-success' ?>">
                                            <?= in_array($txn['type'], ['withdrawal','transfer','payment']) ? '-' : '+' ?>$<?= number_format($txn['amount'], 2) ?>
                                        </td>
                                        <td><?= ucfirst($txn['type']) ?></td>
                                        <td><span class="badge status-<?= $txn['status'] ?>"><?= ucfirst($txn['status']) ?></span></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Stats Row -->
            <div class="dashboard-grid three-col">
                <div class="dash-card">
                    <div class="dash-card-header">
                        <h3><i class="ri-pie-chart-line"></i> User Distribution</h3>
                    </div>
                    <div class="dash-card-body">
                        <div class="distribution-list">
                            <div class="dist-item">
                                <span class="dist-dot" style="background: #D97706;"></span>
                                <span>Admins</span>
                                <strong><?= $roleData['admin'] ?? 0 ?></strong>
                            </div>
                            <div class="dist-item">
                                <span class="dist-dot" style="background: #3B82F6;"></span>
                                <span>Analysts</span>
                                <strong><?= $roleData['analyst'] ?? 0 ?></strong>
                            </div>
                            <div class="dist-item">
                                <span class="dist-dot" style="background: #10B981;"></span>
                                <span>Customers</span>
                                <strong><?= $roleData['customer'] ?? 0 ?></strong>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="dash-card">
                    <div class="dash-card-header">
                        <h3><i class="ri-links-line"></i> Quick Actions</h3>
                    </div>
                    <div class="dash-card-body">
                        <div class="quick-actions">
                            <a href="users.php" class="quick-action-btn"><i class="ri-user-add-line"></i> Manage Users</a>
                            <a href="alerts.php" class="quick-action-btn"><i class="ri-alarm-warning-line"></i> View Alerts</a>
                            <a href="fraudRules.php" class="quick-action-btn"><i class="ri-shield-check-line"></i> Fraud Rules</a>
                            <a href="analytics.php" class="quick-action-btn"><i class="ri-bar-chart-box-line"></i> Analytics</a>
                        </div>
                    </div>
                </div>
                <div class="dash-card">
                    <div class="dash-card-header">
                        <h3><i class="ri-information-line"></i> System Info</h3>
                    </div>
                    <div class="dash-card-body">
                        <div class="info-list">
                            <div class="info-item">
                                <span>System</span>
                                <strong>FraudShield v1.0</strong>
                            </div>
                            <div class="info-item">
                                <span>Database</span>
                                <strong>fraud_detection</strong>
                            </div>
                            <div class="info-item">
                                <span>Tables</span>
                                <strong>8</strong>
                            </div>
                            <div class="info-item">
                                <span>Fraud Rules</span>
                                <strong>5 active</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/script.js"></script>
</body>
</html>
