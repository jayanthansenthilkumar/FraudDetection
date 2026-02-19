<?php
include_once 'includes/auth.php';
checkUserAccess(false);
include_once 'db.php';

$userId = $_SESSION['user_id'];

// Customer stats
$totalBalance = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(balance),0) as b FROM accounts WHERE user_id = $userId AND status = 'active'"))['b'];
$myTxnCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM transactions t JOIN accounts a ON t.account_id = a.id WHERE a.user_id = $userId"))['c'];
$myAccounts = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM accounts WHERE user_id = $userId AND status = 'active'"))['c'];
$myAlertCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM fraud_alerts fa JOIN transactions t ON fa.transaction_id = t.id JOIN accounts a ON t.account_id = a.id WHERE a.user_id = $userId AND fa.status = 'new'"))['c'];

// My accounts
$accounts = mysqli_query($conn, "SELECT * FROM accounts WHERE user_id = $userId ORDER BY created_at DESC");

// Recent transactions
$recentTxns = mysqli_query($conn, "SELECT t.*, a.account_number FROM transactions t JOIN accounts a ON t.account_id = a.id WHERE a.user_id = $userId ORDER BY t.created_at DESC LIMIT 15");

// My alerts
$myAlerts = mysqli_query($conn, "SELECT fa.*, t.amount, t.type as txn_type, a.account_number 
    FROM fraud_alerts fa 
    JOIN transactions t ON fa.transaction_id = t.id 
    JOIN accounts a ON t.account_id = a.id 
    WHERE a.user_id = $userId 
    ORDER BY fa.created_at DESC LIMIT 5");

// Account options for transaction form
$accountOptions = mysqli_query($conn, "SELECT id, account_number, account_type, balance FROM accounts WHERE user_id = $userId AND status = 'active'");
$accList = [];
while ($a = mysqli_fetch_assoc($accountOptions)) { $accList[] = $a; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - FraudShield</title>
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
                <h1><i class="ri-dashboard-line"></i> My Dashboard</h1>
                <p>Welcome back, <?= htmlspecialchars($_SESSION['name']) ?></p>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card-dash">
                    <div class="stat-icon" style="background: rgba(16,185,129,0.15); color: #10B981;">
                        <i class="ri-money-dollar-circle-line"></i>
                    </div>
                    <div class="stat-details">
                        <span class="stat-value">$<?= number_format($totalBalance, 2) ?></span>
                        <span class="stat-label">Total Balance</span>
                    </div>
                </div>
                <div class="stat-card-dash">
                    <div class="stat-icon" style="background: rgba(59,130,246,0.15); color: #3B82F6;">
                        <i class="ri-exchange-funds-line"></i>
                    </div>
                    <div class="stat-details">
                        <span class="stat-value"><?= $myTxnCount ?></span>
                        <span class="stat-label">Transactions</span>
                    </div>
                </div>
                <div class="stat-card-dash">
                    <div class="stat-icon" style="background: rgba(217,119,6,0.15); color: #D97706;">
                        <i class="ri-bank-card-line"></i>
                    </div>
                    <div class="stat-details">
                        <span class="stat-value"><?= $myAccounts ?></span>
                        <span class="stat-label">Accounts</span>
                    </div>
                </div>
                <div class="stat-card-dash">
                    <div class="stat-icon" style="background: rgba(239,68,68,0.15); color: #EF4444;">
                        <i class="ri-alarm-warning-line"></i>
                    </div>
                    <div class="stat-details">
                        <span class="stat-value"><?= $myAlertCount ?></span>
                        <span class="stat-label">Active Alerts</span>
                    </div>
                </div>
            </div>

            <div class="dashboard-grid">
                <!-- My Accounts -->
                <div class="dash-card">
                    <div class="dash-card-header">
                        <h3><i class="ri-bank-card-line"></i> My Accounts</h3>
                    </div>
                    <div class="dash-card-body">
                        <div class="account-cards">
                            <?php
                            $accountsData = mysqli_query($conn, "SELECT * FROM accounts WHERE user_id = $userId ORDER BY created_at DESC");
                            while ($acc = mysqli_fetch_assoc($accountsData)):
                            ?>
                            <div class="account-card-item <?= $acc['status'] === 'frozen' ? 'frozen' : '' ?>">
                                <div class="acc-top">
                                    <span class="acc-type"><?= ucfirst($acc['account_type']) ?></span>
                                    <span class="acc-status status-<?= $acc['status'] ?>"><?= ucfirst($acc['status']) ?></span>
                                </div>
                                <div class="acc-number mono"><?= $acc['account_number'] ?></div>
                                <div class="acc-balance">$<?= number_format($acc['balance'], 2) ?></div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>

                <!-- New Transaction -->
                <div class="dash-card">
                    <div class="dash-card-header">
                        <h3><i class="ri-add-circle-line"></i> New Transaction</h3>
                    </div>
                    <div class="dash-card-body">
                        <form id="txnForm" class="dash-form">
                            <div class="form-group">
                                <label>Account</label>
                                <select name="account_id" required>
                                    <?php foreach ($accList as $a): ?>
                                    <option value="<?= $a['id'] ?>"><?= $a['account_number'] ?> (<?= ucfirst($a['account_type']) ?> - $<?= number_format($a['balance'], 2) ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Type</label>
                                    <select name="type" id="txnType" required onchange="toggleRecipient()">
                                        <option value="deposit">Deposit</option>
                                        <option value="withdrawal">Withdrawal</option>
                                        <option value="transfer">Transfer</option>
                                        <option value="payment">Payment</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Amount ($)</label>
                                    <input type="number" name="amount" step="0.01" min="0.01" placeholder="0.00" required>
                                </div>
                            </div>
                            <div class="form-group" id="recipientGroup" style="display:none;">
                                <label>Recipient Account</label>
                                <input type="text" name="recipient_account" placeholder="e.g. ACC0040001">
                            </div>
                            <div class="form-group">
                                <label>Description</label>
                                <input type="text" name="description" placeholder="Transaction description">
                            </div>
                            <div class="form-group">
                                <label>Location</label>
                                <input type="text" name="location" placeholder="City" value="New York">
                            </div>
                            <button type="submit" class="btn-primary-full"><i class="ri-send-plane-line"></i> Submit Transaction</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="dash-card full-width">
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
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th>Fraud Score</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($txn = mysqli_fetch_assoc($recentTxns)): ?>
                                <tr>
                                    <td class="mono"><?= $txn['account_number'] ?></td>
                                    <td><?= ucfirst($txn['type']) ?></td>
                                    <td class="<?= in_array($txn['type'], ['withdrawal','transfer','payment']) ? 'text-danger' : 'text-success' ?>">
                                        <?= in_array($txn['type'], ['withdrawal','transfer','payment']) ? '-' : '+' ?>$<?= number_format($txn['amount'], 2) ?>
                                    </td>
                                    <td><?= htmlspecialchars($txn['description'] ?? '') ?></td>
                                    <td><span class="badge status-<?= $txn['status'] ?>"><?= ucfirst($txn['status']) ?></span></td>
                                    <td>
                                        <?php if ($txn['fraud_score'] > 0): ?>
                                        <span class="fraud-score <?= $txn['fraud_score'] >= 50 ? 'high' : 'low' ?>"><?= $txn['fraud_score'] ?></span>
                                        <?php else: ?>
                                        <span class="fraud-score low">0</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('M d, H:i', strtotime($txn['created_at'])) ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Alerts -->
            <?php if (mysqli_num_rows($myAlerts) > 0): ?>
            <div class="dash-card full-width">
                <div class="dash-card-header">
                    <h3><i class="ri-alarm-warning-line"></i> Fraud Alerts on My Account</h3>
                </div>
                <div class="dash-card-body">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead><tr><th>Account</th><th>Amount</th><th>Type</th><th>Severity</th><th>Status</th><th>Description</th><th>Date</th></tr></thead>
                            <tbody>
                                <?php
                                $myAlertsData = mysqli_query($conn, "SELECT fa.*, t.amount, t.type as txn_type, a.account_number FROM fraud_alerts fa JOIN transactions t ON fa.transaction_id = t.id JOIN accounts a ON t.account_id = a.id WHERE a.user_id = $userId ORDER BY fa.created_at DESC LIMIT 5");
                                while ($al = mysqli_fetch_assoc($myAlertsData)):
                                ?>
                                <tr>
                                    <td class="mono"><?= $al['account_number'] ?></td>
                                    <td>$<?= number_format($al['amount'], 2) ?></td>
                                    <td><?= $al['alert_type'] ?></td>
                                    <td><span class="badge severity-<?= $al['severity'] ?>"><?= ucfirst($al['severity']) ?></span></td>
                                    <td><span class="badge status-<?= $al['status'] ?>"><?= ucfirst($al['status']) ?></span></td>
                                    <td><?= htmlspecialchars($al['description'] ?? '') ?></td>
                                    <td><?= date('M d, H:i', strtotime($al['created_at'])) ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="assets/js/script.js"></script>
    <script>
        function toggleRecipient() {
            const type = document.getElementById('txnType').value;
            document.getElementById('recipientGroup').style.display = (type === 'transfer') ? 'block' : 'none';
        }

        document.getElementById('txnForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'addTransaction');

            fetch('fraudBackend.php', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        let icon = data.status === 'flagged' ? 'warning' : 'success';
                        let title = data.status === 'flagged' ? 'Transaction Flagged' : 'Transaction Processed';
                        Swal.fire({
                            icon: icon,
                            title: title,
                            html: data.message + (data.fraud_score > 0 ? '<br>Fraud Score: <strong>' + data.fraud_score + '</strong>' : ''),
                            confirmButtonColor: '#D97706'
                        }).then(() => location.reload());
                    } else {
                        Swal.fire({icon: 'error', title: 'Error', text: data.message, confirmButtonColor: '#D97706'});
                    }
                })
                .catch(() => Swal.fire({icon: 'error', title: 'Error', text: 'Network error'}));
        });
    </script>
</body>
</html>
