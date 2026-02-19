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
    <title>Fraud Alerts - FraudShield</title>
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
                <h1><i class="ri-alarm-warning-line"></i> Fraud Alerts</h1>
                <p>Monitor and investigate suspicious activities</p>
            </div>

            <div class="filter-bar">
                <button class="filter-btn active" onclick="filterAlerts('all', this)">All</button>
                <button class="filter-btn" onclick="filterAlerts('new', this)">New</button>
                <button class="filter-btn" onclick="filterAlerts('investigating', this)">Investigating</button>
                <button class="filter-btn" onclick="filterAlerts('resolved', this)">Resolved</button>
                <button class="filter-btn" onclick="filterAlerts('dismissed', this)">Dismissed</button>
            </div>

            <div class="dash-card full-width">
                <div class="dash-card-body">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>User</th>
                                    <th>Account</th>
                                    <th>Alert Type</th>
                                    <th>Amount</th>
                                    <th>Location</th>
                                    <th>Severity</th>
                                    <th>Status</th>
                                    <th>Description</th>
                                    <th>Created</th>
                                    <?php if ($role !== 'customer'): ?><th>Actions</th><?php endif; ?>
                                </tr>
                            </thead>
                            <tbody id="alertsBody">
                                <tr><td colspan="11" class="loading-cell">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="assets/js/script.js"></script>
    <script>
        function filterAlerts(status, btn) {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            if (btn) btn.classList.add('active');
            loadAlerts(status);
        }

        function loadAlerts(status = 'all') {
            fetch(`fraudBackend.php?action=getAlerts&status=${status}`)
                .then(r => r.json())
                .then(data => {
                    const role = '<?= $role ?>';
                    const tbody = document.getElementById('alertsBody');
                    tbody.innerHTML = data.data.map(a => `
                        <tr>
                            <td>#${a.id}</td>
                            <td>${escapeHtml(a.user_name || 'N/A')}</td>
                            <td class="mono">${a.account_number || '-'}</td>
                            <td><span class="badge badge-outline">${a.alert_type}</span></td>
                            <td>$${parseFloat(a.amount || 0).toFixed(2)}</td>
                            <td>${a.location || '-'}</td>
                            <td><span class="badge severity-${a.severity}">${capitalize(a.severity)}</span></td>
                            <td><span class="badge status-${a.status}">${capitalize(a.status)}</span></td>
                            <td class="desc-cell">${escapeHtml(a.description || '')}</td>
                            <td>${formatDate(a.created_at)}</td>
                            ${role !== 'customer' ? `<td>
                                <div class="action-btns">
                                    ${a.status === 'new' ? `<button onclick="updateAlert(${a.id}, 'investigating')" class="btn-sm btn-warning" title="Investigate"><i class="ri-search-eye-line"></i></button>` : ''}
                                    ${['new','investigating'].includes(a.status) ? `
                                        <button onclick="resolveAlertPrompt(${a.id})" class="btn-sm btn-success" title="Resolve"><i class="ri-check-line"></i></button>
                                        <button onclick="dismissAlertPrompt(${a.id})" class="btn-sm btn-secondary" title="Dismiss"><i class="ri-close-line"></i></button>
                                    ` : ''}
                                </div>
                            </td>` : ''}
                        </tr>
                    `).join('') || '<tr><td colspan="11" class="loading-cell">No alerts found</td></tr>';
                });
        }

        function updateAlert(id, status, notes = '') {
            const formData = new FormData();
            formData.append('action', 'updateAlertStatus');
            formData.append('alert_id', id);
            formData.append('status', status);
            formData.append('notes', notes);
            fetch('fraudBackend.php', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => {
                    Swal.fire({icon: data.success ? 'success' : 'error', title: data.message, timer: 1500, showConfirmButton: false})
                        .then(() => { if (data.success) loadAlerts(); });
                });
        }

        function resolveAlertPrompt(id) {
            Swal.fire({title: 'Resolve Alert', input: 'textarea', inputLabel: 'Resolution Notes', showCancelButton: true, confirmButtonColor: '#10B981', confirmButtonText: 'Resolve'})
                .then(r => { if (r.isConfirmed) updateAlert(id, 'resolved', r.value || 'Resolved'); });
        }

        function dismissAlertPrompt(id) {
            Swal.fire({title: 'Dismiss Alert', input: 'textarea', inputLabel: 'Reason', showCancelButton: true, confirmButtonColor: '#6B7280', confirmButtonText: 'Dismiss'})
                .then(r => { if (r.isConfirmed) updateAlert(id, 'dismissed', r.value || 'False positive'); });
        }

        loadAlerts();
    </script>
</body>
</html>
