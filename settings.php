<?php
include_once 'includes/auth.php';
checkUserAccess(false);
if ($_SESSION['role'] !== 'admin') { header("Location: adminDashboard.php"); exit(); }
include_once 'db.php';

// Get current settings
$settingsQ = mysqli_query($conn, "SELECT * FROM settings");
$settings = [];
while ($s = mysqli_fetch_assoc($settingsQ)) { $settings[$s['setting_key']] = $s['setting_value']; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - FraudShield</title>
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
                <h1><i class="ri-settings-3-line"></i> System Settings</h1>
                <p>Configure system parameters</p>
            </div>

            <div class="dashboard-grid">
                <div class="dash-card">
                    <div class="dash-card-header"><h3><i class="ri-shield-check-line"></i> Fraud Detection Settings</h3></div>
                    <div class="dash-card-body">
                        <form id="settingsForm" class="dash-form">
                            <div class="form-group">
                                <label>Alert Threshold Score</label>
                                <input type="number" name="settings[alert_threshold_score]" value="<?= $settings['alert_threshold_score'] ?? 50 ?>" min="1" max="100">
                                <small>Transactions with fraud score above this are flagged</small>
                            </div>
                            <div class="form-group">
                                <label>High Value Threshold ($)</label>
                                <input type="number" name="settings[high_value_threshold]" value="<?= $settings['high_value_threshold'] ?? 5000 ?>" step="100">
                                <small>Transaction amount considered high value</small>
                            </div>
                            <div class="form-group">
                                <label>Max Transaction Amount ($)</label>
                                <input type="number" name="settings[max_transaction_amount]" value="<?= $settings['max_transaction_amount'] ?? 50000 ?>" step="1000">
                            </div>
                            <div class="form-group">
                                <label>Rapid Txn Window (minutes)</label>
                                <input type="number" name="settings[rapid_txn_window_minutes]" value="<?= $settings['rapid_txn_window_minutes'] ?? 5 ?>">
                            </div>
                            <div class="form-group">
                                <label>Rapid Txn Count Threshold</label>
                                <input type="number" name="settings[rapid_txn_count]" value="<?= $settings['rapid_txn_count'] ?? 3 ?>">
                            </div>
                            <button type="submit" class="btn-primary-full"><i class="ri-save-line"></i> Save Settings</button>
                        </form>
                    </div>
                </div>

                <div class="dash-card">
                    <div class="dash-card-header"><h3><i class="ri-settings-4-line"></i> General Settings</h3></div>
                    <div class="dash-card-body">
                        <form id="generalForm" class="dash-form">
                            <div class="form-group">
                                <label>System Name</label>
                                <input type="text" name="settings[system_name]" value="<?= htmlspecialchars($settings['system_name'] ?? 'FraudShield') ?>">
                            </div>
                            <div class="form-group">
                                <label>Session Timeout (seconds)</label>
                                <input type="number" name="settings[session_timeout]" value="<?= $settings['session_timeout'] ?? 1800 ?>">
                            </div>
                            <div class="form-group">
                                <label>Registration Open</label>
                                <select name="settings[registration_open]">
                                    <option value="1" <?= ($settings['registration_open'] ?? '1') === '1' ? 'selected' : '' ?>>Yes</option>
                                    <option value="0" <?= ($settings['registration_open'] ?? '1') === '0' ? 'selected' : '' ?>>No</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Location Check Enabled</label>
                                <select name="settings[location_check_enabled]">
                                    <option value="1" <?= ($settings['location_check_enabled'] ?? '1') === '1' ? 'selected' : '' ?>>Yes</option>
                                    <option value="0" <?= ($settings['location_check_enabled'] ?? '1') === '0' ? 'selected' : '' ?>>No</option>
                                </select>
                            </div>
                            <button type="submit" class="btn-primary-full"><i class="ri-save-line"></i> Save General Settings</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="assets/js/script.js"></script>
    <script>
        function submitSettings(formId) {
            document.getElementById(formId).addEventListener('submit', function(e) {
                e.preventDefault();
                const fd = new FormData(this);
                fd.append('action', 'updateSettings');
                fetch('fraudBackend.php', {method:'POST', body:fd})
                    .then(r => r.json())
                    .then(data => {
                        Swal.fire({icon: data.success ? 'success' : 'error', title: data.message, confirmButtonColor: '#D97706'});
                    });
            });
        }
        submitSettings('settingsForm');
        submitSettings('generalForm');
    </script>
</body>
</html>
