<?php
include_once 'includes/auth.php';
checkUserAccess(false);
include_once 'db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fraud Rules - FraudShield</title>
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
                <h1><i class="ri-shield-check-line"></i> Fraud Detection Rules</h1>
                <p>Configure fraud detection parameters and thresholds</p>
            </div>
            <div class="dash-card full-width">
                <div class="dash-card-body">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr><th>Rule Name</th><th>Description</th><th>Type</th><th>Threshold</th><th>Score Weight</th><th>Status</th><th>Actions</th></tr>
                            </thead>
                            <tbody id="rulesBody">
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
        function loadRules() {
            fetch('fraudBackend.php?action=getFraudRules')
                .then(r => r.json())
                .then(data => {
                    document.getElementById('rulesBody').innerHTML = data.data.map(r => `
                        <tr>
                            <td><strong>${escapeHtml(r.rule_name)}</strong></td>
                            <td class="desc-cell">${escapeHtml(r.description || '')}</td>
                            <td><span class="badge badge-outline">${capitalize(r.rule_type)}</span></td>
                            <td>${parseFloat(r.threshold_value).toLocaleString()}</td>
                            <td><span class="fraud-score ${r.score_weight >= 50 ? 'high' : 'medium'}">${r.score_weight}</span></td>
                            <td><span class="badge ${r.is_active == 1 ? 'status-active' : 'status-inactive'}">${r.is_active == 1 ? 'Active' : 'Disabled'}</span></td>
                            <td>
                                <div class="action-btns">
                                    <button onclick="editRule(${r.id}, ${r.threshold_value}, ${r.score_weight}, ${r.is_active})" class="btn-sm btn-primary" title="Edit"><i class="ri-edit-line"></i></button>
                                </div>
                            </td>
                        </tr>
                    `).join('');
                });
        }

        function editRule(id, threshold, weight, active) {
            Swal.fire({
                title: 'Edit Fraud Rule',
                html: `
                    <label>Threshold Value</label>
                    <input id="swal-threshold" class="swal2-input" type="number" step="0.01" value="${threshold}">
                    <label>Score Weight</label>
                    <input id="swal-weight" class="swal2-input" type="number" value="${weight}">
                    <label>Status</label>
                    <select id="swal-active" class="swal2-select">
                        <option value="1" ${active==1?'selected':''}>Active</option>
                        <option value="0" ${active==0?'selected':''}>Disabled</option>
                    </select>`,
                showCancelButton: true,
                confirmButtonColor: '#D97706',
                confirmButtonText: 'Update',
                preConfirm: () => ({
                    threshold_value: document.getElementById('swal-threshold').value,
                    score_weight: document.getElementById('swal-weight').value,
                    is_active: document.getElementById('swal-active').value
                })
            }).then(r => {
                if (r.isConfirmed) {
                    const fd = new FormData();
                    fd.append('action', 'updateFraudRule');
                    fd.append('rule_id', id);
                    Object.entries(r.value).forEach(([k,v]) => fd.append(k, v));
                    fetch('fraudBackend.php', {method:'POST', body:fd})
                        .then(r => r.json())
                        .then(data => {
                            Swal.fire({icon: data.success ? 'success' : 'error', title: data.message, timer:1500, showConfirmButton:false})
                                .then(() => { if (data.success) loadRules(); });
                        });
                }
            });
        }

        loadRules();
    </script>
</body>
</html>
