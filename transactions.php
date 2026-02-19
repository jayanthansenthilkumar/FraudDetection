<?php
include_once 'includes/auth.php';
checkUserAccess(false);
include_once 'db.php';

$userId = $_SESSION['user_id'];
$role = $_SESSION['role'];
$filter = $_GET['filter'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transactions - FraudShield</title>
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
                <h1><i class="ri-exchange-funds-line"></i> Transactions</h1>
                <p>View and monitor all transaction records</p>
            </div>

            <div class="dash-card full-width">
                <div class="dash-card-header">
                    <h3>Transaction Records</h3>
                    <div class="header-controls">
                        <div class="filter-group">
                            <select id="statusFilter" onchange="loadTransactions()">
                                <option value="all" <?= $filter === '' ? 'selected' : '' ?>>All Status</option>
                                <option value="approved" <?= $filter === 'approved' ? 'selected' : '' ?>>Approved</option>
                                <option value="pending" <?= $filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="flagged" <?= $filter === 'flagged' ? 'selected' : '' ?>>Flagged</option>
                                <option value="declined" <?= $filter === 'declined' ? 'selected' : '' ?>>Declined</option>
                            </select>
                            <input type="text" id="searchBox" placeholder="Search..." onkeyup="loadTransactions()">
                        </div>
                    </div>
                </div>
                <div class="dash-card-body">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Account</th>
                                    <?php if ($role !== 'customer'): ?><th>Holder</th><?php endif; ?>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Description</th>
                                    <th>Location</th>
                                    <th>Status</th>
                                    <th>Fraud Score</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody id="txnTableBody">
                                <tr><td colspan="10" class="loading-cell">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="pagination" id="pagination"></div>
                </div>
            </div>
        </div>
    </div>
    <script src="assets/js/script.js"></script>
    <script>
        let currentPage = 1;
        function loadTransactions(page = 1) {
            currentPage = page;
            const filter = document.getElementById('statusFilter').value;
            const search = document.getElementById('searchBox').value;
            fetch(`fraudBackend.php?action=getTransactions&page=${page}&limit=20&filter=${filter}&search=${encodeURIComponent(search)}`)
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        const tbody = document.getElementById('txnTableBody');
                        const role = '<?= $role ?>';
                        tbody.innerHTML = data.data.map(t => `
                            <tr>
                                <td>#${t.id}</td>
                                <td class="mono">${t.account_number}</td>
                                ${role !== 'customer' ? `<td>${escapeHtml(t.account_holder)}</td>` : ''}
                                <td>${capitalize(t.type)}</td>
                                <td class="${['withdrawal','transfer','payment'].includes(t.type) ? 'text-danger' : 'text-success'}">
                                    ${['withdrawal','transfer','payment'].includes(t.type) ? '-' : '+'}$${parseFloat(t.amount).toFixed(2)}
                                </td>
                                <td>${escapeHtml(t.description || '')}</td>
                                <td>${t.location || '-'}</td>
                                <td><span class="badge status-${t.status}">${capitalize(t.status)}</span></td>
                                <td><span class="fraud-score ${t.fraud_score >= 50 ? 'high' : (t.fraud_score > 0 ? 'medium' : 'low')}">${t.fraud_score}</span></td>
                                <td>${formatDate(t.created_at)}</td>
                            </tr>
                        `).join('') || '<tr><td colspan="10" class="loading-cell">No transactions found</td></tr>';

                        // Pagination
                        const totalPages = Math.ceil(data.total / 20);
                        const pag = document.getElementById('pagination');
                        if (totalPages > 1) {
                            let html = '';
                            for (let i = 1; i <= totalPages; i++) {
                                html += `<button class="page-btn ${i === page ? 'active' : ''}" onclick="loadTransactions(${i})">${i}</button>`;
                            }
                            pag.innerHTML = html;
                        } else {
                            pag.innerHTML = '';
                        }
                    }
                });
        }
        loadTransactions(1);
    </script>
</body>
</html>
