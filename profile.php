<?php
include_once 'includes/auth.php';
checkUserAccess(false);
include_once 'db.php';
$userId = $_SESSION['user_id'];
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id = $userId"));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - FraudShield</title>
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
                <h1><i class="ri-user-settings-line"></i> My Profile</h1>
                <p>Manage your account information</p>
            </div>

            <div class="dashboard-grid">
                <div class="dash-card">
                    <div class="dash-card-header"><h3><i class="ri-user-line"></i> Profile Information</h3></div>
                    <div class="dash-card-body">
                        <form id="profileForm" class="dash-form">
                            <div class="form-group">
                                <label>Full Name</label>
                                <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Phone</label>
                                <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label>Address</label>
                                <textarea name="address" rows="2"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                            </div>
                            <div class="form-group">
                                <label>Role</label>
                                <input type="text" value="<?= ucfirst($user['role']) ?>" disabled>
                            </div>
                            <div class="form-group">
                                <label>Username</label>
                                <input type="text" value="<?= htmlspecialchars($user['username']) ?>" disabled>
                            </div>
                            <button type="submit" class="btn-primary-full"><i class="ri-save-line"></i> Update Profile</button>
                        </form>
                    </div>
                </div>

                <div class="dash-card">
                    <div class="dash-card-header"><h3><i class="ri-lock-line"></i> Change Password</h3></div>
                    <div class="dash-card-body">
                        <form id="passwordForm" class="dash-form">
                            <div class="form-group">
                                <label>Current Password</label>
                                <input type="password" name="current_password" required>
                            </div>
                            <div class="form-group">
                                <label>New Password</label>
                                <input type="password" name="new_password" required minlength="6">
                            </div>
                            <div class="form-group">
                                <label>Confirm New Password</label>
                                <input type="password" id="confirm_password" required minlength="6">
                            </div>
                            <button type="submit" class="btn-primary-full"><i class="ri-lock-line"></i> Change Password</button>
                        </form>
                    </div>

                    <div class="dash-card-header" style="margin-top: 1rem;"><h3><i class="ri-information-line"></i> Account Details</h3></div>
                    <div class="dash-card-body">
                        <div class="info-list">
                            <div class="info-item"><span>User ID</span><strong>#<?= $user['id'] ?></strong></div>
                            <div class="info-item"><span>Status</span><strong class="text-success"><?= ucfirst($user['status']) ?></strong></div>
                            <div class="info-item"><span>Joined</span><strong><?= date('M d, Y', strtotime($user['created_at'])) ?></strong></div>
                            <div class="info-item"><span>Last Updated</span><strong><?= date('M d, Y H:i', strtotime($user['updated_at'])) ?></strong></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="assets/js/script.js"></script>
    <script>
        document.getElementById('profileForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const fd = new FormData(this);
            fd.append('action', 'updateProfile');
            fetch('fraudBackend.php', {method:'POST', body:fd})
                .then(r => r.json())
                .then(data => {
                    Swal.fire({icon: data.success ? 'success' : 'error', title: data.message, confirmButtonColor: '#D97706'})
                        .then(() => { if (data.success) location.reload(); });
                });
        });

        document.getElementById('passwordForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const newPass = this.new_password.value;
            const confirm = document.getElementById('confirm_password').value;
            if (newPass !== confirm) {
                Swal.fire({icon: 'error', title: 'Passwords do not match', confirmButtonColor: '#D97706'});
                return;
            }
            const fd = new FormData(this);
            fd.append('action', 'changePassword');
            fetch('fraudBackend.php', {method:'POST', body:fd})
                .then(r => r.json())
                .then(data => {
                    Swal.fire({icon: data.success ? 'success' : 'error', title: data.message, confirmButtonColor: '#D97706'})
                        .then(() => { if (data.success) this.reset(); });
                });
        });
    </script>
</body>
</html>
