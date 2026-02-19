<?php
include_once 'includes/auth.php';
checkUserAccess(false);
include_once 'db.php';
$userId = $_SESSION['user_id'];
$role = $_SESSION['role'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accounts - FraudShield</title>
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
                <h1><i class="ri-bank-card-line"></i> <?= $role === 'customer' ? 'My Accounts' : 'Account Management' ?></h1>
                <p><?= $role === 'customer' ? 'View your account details' : 'Manage all customer accounts' ?></p>
            </div>
            <div class="dash-card full-width">
                <div class="dash-card-body">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Account #</th>
                                    <?php if ($role !== 'customer'): ?><th>Holder</th><?php endif; ?>
                                    <th>Type</th>
                                    <th>Balance</th>
                                    <th>Credit Limit</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <?php if ($role === 'admin'): ?><th>Actions</th><?php endif; ?>
                                </tr>
                            </thead>
                            <tbody id="accountsBody">
                                <tr><td colspan="8" class="loading-cell">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="assets/js/script.js"></script>
    <script>
        function loadAccounts() {
            fetch('fraudBackend.php?action=getAccounts')
                .then(r => r.json())
                .then(data => {
                    const role = '<?= $role ?>';
                    document.getElementById('accountsBody').innerHTML = data.data.map(a => `
                        <tr>
                            <td class="mono">${a.account_number}</td>
                            ${role !== 'customer' ? `<td>${escapeHtml(a.holder_name)}</td>` : ''}
                            <td>${capitalize(a.account_type)}</td>
                            <td class="text-success">$${parseFloat(a.balance).toFixed(2)}</td>
                            <td>$${parseFloat(a.credit_limit).toFixed(2)}</td>
                            <td><span class="badge status-${a.status}">${capitalize(a.status)}</span></td>
                            <td>${formatDate(a.created_at)}</td>
                            ${role === 'admin' ? `<td>
                                <div class="action-btns">
                                    <button onclick="toggleFreeze(${a.id}, '${a.status}')" class="btn-sm ${a.status === 'frozen' ? 'btn-success' : 'btn-danger'}" title="${a.status === 'frozen' ? 'Unfreeze' : 'Freeze'}">
                                        <i class="ri-${a.status === 'frozen' ? 'lock-unlock' : 'lock'}-line"></i>
                                    </button>
                                </div>
                            </td>` : ''}
                        </tr>
                    `).join('') || '<tr><td colspan="8" class="loading-cell">No accounts found</td></tr>';
                });
        }

        function toggleFreeze(id, currentStatus) {
            const action = currentStatus === 'frozen' ? 'unfreeze' : 'freeze';
            Swal.fire({title: `${capitalize(action)} Account?`, showCancelButton: true, confirmButtonColor: action === 'freeze' ? '#EF4444' : '#10B981', confirmButtonText: capitalize(action)})
                .then(r => {
                    if (r.isConfirmed) {
                        const fd = new FormData();
                        fd.append('action', 'freezeAccount');
                        fd.append('account_id', id);
                        fd.append('freeze_action', action);
                        fetch('fraudBackend.php', { method: 'POST', body: fd })
                            .then(r => r.json())
                            .then(data => {
                                Swal.fire({icon: data.success ? 'success' : 'error', title: data.message, timer: 1500, showConfirmButton: false})
                                    .then(() => { if (data.success) loadAccounts(); });
                            });
                    }
                });
        }

        loadAccounts();
    </script>
</body>
</html>
