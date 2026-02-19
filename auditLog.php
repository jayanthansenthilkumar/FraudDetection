<?php
include_once 'includes/auth.php';
checkUserAccess(false);
if ($_SESSION['role'] !== 'admin') { header("Location: adminDashboard.php"); exit(); }
include_once 'db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Log - FraudShield</title>
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
                <h1><i class="ri-file-list-3-line"></i> Audit Log</h1>
                <p>Complete system activity trail</p>
            </div>
            <div class="dash-card full-width">
                <div class="dash-card-body">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr><th>ID</th><th>User</th><th>Action</th><th>Entity</th><th>Details</th><th>IP Address</th><th>Time</th></tr>
                            </thead>
                            <tbody id="auditBody">
                                <tr><td colspan="7" class="loading-cell">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="assets/js/script.js"></script>
    <script>
        fetch('fraudBackend.php?action=getAuditLog&limit=100')
            .then(r => r.json())
            .then(data => {
                document.getElementById('auditBody').innerHTML = data.data.map(l => `
                    <tr>
                        <td>#${l.id}</td>
                        <td>${escapeHtml(l.name || 'System')} <small class="mono">(${l.username || ''})</small></td>
                        <td><span class="badge badge-outline">${l.action}</span></td>
                        <td>${l.entity_type ? capitalize(l.entity_type) + ' #' + l.entity_id : '-'}</td>
                        <td class="desc-cell">${escapeHtml(l.details || '')}</td>
                        <td class="mono">${l.ip_address || '-'}</td>
                        <td>${formatDate(l.created_at)}</td>
                    </tr>
                `).join('') || '<tr><td colspan="7" class="loading-cell">No audit entries</td></tr>';
            });
    </script>
</body>
</html>
