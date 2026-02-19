<?php
include_once 'includes/auth.php';
checkUserAccess(false);
include_once 'db.php';

$userId = $_SESSION['user_id'];

// Stats
$activeAlerts = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM fraud_alerts WHERE status IN ('new','investigating') AND (assigned_to = $userId OR assigned_to IS NULL)"))['c'];
$resolvedByMe = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM fraud_alerts WHERE resolved_by = $userId"))['c'];
$todayFraud = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM transactions WHERE is_fraud = 1 AND DATE(created_at) = CURDATE()"))['c'];
$flaggedTxns = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM transactions WHERE status = 'flagged'"))['c'];
$criticalAlerts = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM fraud_alerts WHERE severity = 'critical' AND status = 'new'"))['c'];

// My assigned alerts
$myAlerts = mysqli_query($conn, "SELECT fa.*, t.amount, t.type as txn_type, t.location, t.fraud_score, a.account_number, u.name as user_name
    FROM fraud_alerts fa
    LEFT JOIN transactions t ON fa.transaction_id = t.id
    LEFT JOIN accounts a ON t.account_id = a.id
    LEFT JOIN users u ON fa.user_id = u.id
    WHERE fa.status IN ('new', 'investigating') AND (fa.assigned_to = $userId OR fa.assigned_to IS NULL)
    ORDER BY FIELD(fa.severity, 'critical', 'high', 'medium', 'low'), fa.created_at DESC
    LIMIT 15");

// Recent flagged transactions
$flagged = mysqli_query($conn, "SELECT t.*, a.account_number, u.name as holder
    FROM transactions t
    JOIN accounts a ON t.account_id = a.id
    JOIN users u ON a.user_id = u.id
    WHERE t.status = 'flagged'
    ORDER BY t.fraud_score DESC, t.created_at DESC
    LIMIT 10");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analyst Dashboard - FraudShield</title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <?php include 'includes/loader.php'; ?>
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <?php include 'includes/dashboard_header.php'; ?>

        <div class="page-content">
            <div class="page-title">
                <h1><i class="ri-search-eye-line"></i> Analyst Dashboard</h1>
                <p>Fraud investigation and alert monitoring</p>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card-dash">
                    <div class="stat-icon" style="background: rgba(239,68,68,0.15); color: #EF4444;">
                        <i class="ri-alarm-warning-line"></i>
                    </div>
                    <div class="stat-details">
                        <span class="stat-value"><?= $activeAlerts ?></span>
                        <span class="stat-label">Active Alerts</span>
                    </div>
                    <?php if ($criticalAlerts > 0): ?>
                    <div class="stat-badge danger"><?= $criticalAlerts ?> critical</div>
                    <?php endif; ?>
                </div>
                <div class="stat-card-dash">
                    <div class="stat-icon" style="background: rgba(16,185,129,0.15); color: #10B981;">
                        <i class="ri-check-double-line"></i>
                    </div>
                    <div class="stat-details">
                        <span class="stat-value"><?= $resolvedByMe ?></span>
                        <span class="stat-label">Resolved by Me</span>
                    </div>
                </div>
                <div class="stat-card-dash">
                    <div class="stat-icon" style="background: rgba(217,119,6,0.15); color: #D97706;">
                        <i class="ri-error-warning-line"></i>
                    </div>
                    <div class="stat-details">
                        <span class="stat-value"><?= $todayFraud ?></span>
                        <span class="stat-label">Today's Fraud</span>
                    </div>
                </div>
                <div class="stat-card-dash">
                    <div class="stat-icon" style="background: rgba(139,92,246,0.15); color: #8B5CF6;">
                        <i class="ri-flag-line"></i>
                    </div>
                    <div class="stat-details">
                        <span class="stat-value"><?= $flaggedTxns ?></span>
                        <span class="stat-label">Flagged Transactions</span>
                    </div>
                </div>
            </div>

            <!-- Dashboard Grid -->
            <div class="dashboard-grid">
                <!-- Active Alerts -->
                <div class="dash-card full-width">
                    <div class="dash-card-header">
                        <h3><i class="ri-alarm-warning-line"></i> Active Alerts - Awaiting Investigation</h3>
                        <a href="alerts.php" class="dash-card-link">View All <i class="ri-arrow-right-s-line"></i></a>
                    </div>
                    <div class="dash-card-body">
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>User</th>
                                        <th>Account</th>
                                        <th>Type</th>
                                        <th>Amount</th>
                                        <th>Score</th>
                                        <th>Severity</th>
                                        <th>Location</th>
                                        <th>Time</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($alert = mysqli_fetch_assoc($myAlerts)): ?>
                                    <tr>
                                        <td>#<?= $alert['id'] ?></td>
                                        <td><?= htmlspecialchars($alert['user_name'] ?? 'N/A') ?></td>
                                        <td class="mono"><?= $alert['account_number'] ?? '-' ?></td>
                                        <td><span class="badge badge-outline"><?= $alert['alert_type'] ?></span></td>
                                        <td>$<?= number_format($alert['amount'] ?? 0, 2) ?></td>
                                        <td>
                                            <span class="fraud-score <?= ($alert['fraud_score'] ?? 0) >= 70 ? 'high' : (($alert['fraud_score'] ?? 0) >= 40 ? 'medium' : 'low') ?>">
                                                <?= $alert['fraud_score'] ?? 0 ?>
                                            </span>
                                        </td>
                                        <td><span class="badge severity-<?= $alert['severity'] ?>"><?= ucfirst($alert['severity']) ?></span></td>
                                        <td><?= $alert['location'] ?? '-' ?></td>
                                        <td><?= date('M d, H:i', strtotime($alert['created_at'])) ?></td>
                                        <td>
                                            <div class="action-btns">
                                                <button onclick="investigateAlert(<?= $alert['id'] ?>)" class="btn-sm btn-warning" title="Investigate">
                                                    <i class="ri-search-eye-line"></i>
                                                </button>
                                                <button onclick="resolveAlert(<?= $alert['id'] ?>)" class="btn-sm btn-success" title="Resolve">
                                                    <i class="ri-check-line"></i>
                                                </button>
                                                <button onclick="dismissAlert(<?= $alert['id'] ?>)" class="btn-sm btn-secondary" title="Dismiss">
                                                    <i class="ri-close-line"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Flagged Transactions -->
                <div class="dash-card full-width">
                    <div class="dash-card-header">
                        <h3><i class="ri-flag-line"></i> Flagged Transactions (Highest Risk)</h3>
                        <a href="transactions.php?filter=flagged" class="dash-card-link">View All <i class="ri-arrow-right-s-line"></i></a>
                    </div>
                    <div class="dash-card-body">
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Account</th>
                                        <th>Holder</th>
                                        <th>Amount</th>
                                        <th>Type</th>
                                        <th>Fraud Score</th>
                                        <th>Location</th>
                                        <th>Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($txn = mysqli_fetch_assoc($flagged)): ?>
                                    <tr>
                                        <td>#<?= $txn['id'] ?></td>
                                        <td class="mono"><?= $txn['account_number'] ?></td>
                                        <td><?= htmlspecialchars($txn['holder']) ?></td>
                                        <td class="text-danger">$<?= number_format($txn['amount'], 2) ?></td>
                                        <td><?= ucfirst($txn['type']) ?></td>
                                        <td>
                                            <div class="score-bar">
                                                <div class="score-fill" style="width: <?= min($txn['fraud_score'], 100) ?>%; background: <?= $txn['fraud_score'] >= 70 ? '#EF4444' : ($txn['fraud_score'] >= 40 ? '#F59E0B' : '#10B981') ?>;"></div>
                                                <span><?= $txn['fraud_score'] ?></span>
                                            </div>
                                        </td>
                                        <td><?= $txn['location'] ?? '-' ?></td>
                                        <td><?= date('M d, H:i', strtotime($txn['created_at'])) ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/script.js"></script>
    <script>
        function investigateAlert(id) {
            updateAlertStatus(id, 'investigating', 'Started investigation');
        }

        function resolveAlert(id) {
            Swal.fire({
                title: 'Resolve Alert',
                input: 'textarea',
                inputLabel: 'Resolution Notes',
                inputPlaceholder: 'Enter resolution details...',
                showCancelButton: true,
                confirmButtonColor: '#10B981',
                confirmButtonText: 'Resolve'
            }).then((result) => {
                if (result.isConfirmed) {
                    updateAlertStatus(id, 'resolved', result.value || 'Resolved');
                }
            });
        }

        function dismissAlert(id) {
            Swal.fire({
                title: 'Dismiss Alert',
                text: 'Are you sure? This will mark the transaction as legitimate.',
                input: 'textarea',
                inputLabel: 'Reason',
                showCancelButton: true,
                confirmButtonColor: '#6B7280',
                confirmButtonText: 'Dismiss'
            }).then((result) => {
                if (result.isConfirmed) {
                    updateAlertStatus(id, 'dismissed', result.value || 'False positive');
                }
            });
        }

        function updateAlertStatus(id, status, notes) {
            const formData = new FormData();
            formData.append('action', 'updateAlertStatus');
            formData.append('alert_id', id);
            formData.append('status', status);
            formData.append('notes', notes);

            fetch('fraudBackend.php', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({icon: 'success', title: 'Updated', text: data.message, timer: 1500, showConfirmButton: false})
                            .then(() => location.reload());
                    } else {
                        Swal.fire({icon: 'error', title: 'Error', text: data.message});
                    }
                });
        }
    </script>
</body>
</html>
