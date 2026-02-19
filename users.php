<?php
include_once 'includes/auth.php';
checkUserAccess(false);
include_once 'db.php';
if ($_SESSION['role'] !== 'admin') { header("Location: adminDashboard.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - FraudShield</title>
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
                <h1><i class="ri-group-line"></i> User Management</h1>
                <p>Manage system users and their roles</p>
            </div>
            <div class="dash-card full-width">
                <div class="dash-card-body">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr><th>ID</th><th>Name</th><th>Username</th><th>Email</th><th>Role</th><th>Status</th><th>Joined</th><th>Actions</th></tr>
                            </thead>
                            <tbody id="usersBody">
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
        function loadUsers() {
            fetch('fraudBackend.php?action=getUsers')
                .then(r => r.json())
                .then(data => {
                    document.getElementById('usersBody').innerHTML = data.data.map(u => `
                        <tr>
                            <td>#${u.id}</td>
                            <td>${escapeHtml(u.name)}</td>
                            <td class="mono">${u.username}</td>
                            <td>${escapeHtml(u.email)}</td>
                            <td><span class="badge role-${u.role}">${capitalize(u.role)}</span></td>
                            <td><span class="badge status-${u.status}">${capitalize(u.status)}</span></td>
                            <td>${formatDate(u.created_at)}</td>
                            <td>
                                <div class="action-btns">
                                    <button onclick="editUser(${u.id}, '${escapeHtml(u.name)}', '${escapeHtml(u.email)}', '${u.role}', '${u.status}')" class="btn-sm btn-primary" title="Edit"><i class="ri-edit-line"></i></button>
                                </div>
                            </td>
                        </tr>
                    `).join('');
                });
        }

        function editUser(id, name, email, role, status) {
            Swal.fire({
                title: 'Edit User',
                html: `
                    <input id="swal-name" class="swal2-input" value="${name}" placeholder="Name">
                    <input id="swal-email" class="swal2-input" value="${email}" placeholder="Email">
                    <select id="swal-role" class="swal2-select">
                        <option value="admin" ${role==='admin'?'selected':''}>Admin</option>
                        <option value="analyst" ${role==='analyst'?'selected':''}>Analyst</option>
                        <option value="customer" ${role==='customer'?'selected':''}>Customer</option>
                    </select>
                    <select id="swal-status" class="swal2-select">
                        <option value="active" ${status==='active'?'selected':''}>Active</option>
                        <option value="inactive" ${status==='inactive'?'selected':''}>Inactive</option>
                    </select>`,
                showCancelButton: true,
                confirmButtonColor: '#D97706',
                confirmButtonText: 'Update',
                preConfirm: () => ({
                    name: document.getElementById('swal-name').value,
                    email: document.getElementById('swal-email').value,
                    role: document.getElementById('swal-role').value,
                    status: document.getElementById('swal-status').value
                })
            }).then(r => {
                if (r.isConfirmed) {
                    const fd = new FormData();
                    fd.append('action', 'updateUser');
                    fd.append('user_id', id);
                    Object.entries(r.value).forEach(([k,v]) => fd.append(k, v));
                    fetch('fraudBackend.php', {method:'POST', body:fd})
                        .then(r => r.json())
                        .then(data => {
                            Swal.fire({icon: data.success ? 'success' : 'error', title: data.message, timer:1500, showConfirmButton:false})
                                .then(() => { if (data.success) loadUsers(); });
                        });
                }
            });
        }

        loadUsers();
    </script>
</body>
</html>
