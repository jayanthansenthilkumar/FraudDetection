<?php
session_start();
if (isset($_SESSION['user_id'])) {
    $dashboards = ['admin' => 'adminDashboard.php', 'analyst' => 'analystDashboard.php', 'customer' => 'customerDashboard.php'];
    header("Location: " . ($dashboards[$_SESSION['role']] ?? 'index.php'));
    exit();
}
$error = $_GET['error'] ?? '';
$success = $_GET['success'] ?? '';
$timeout = $_GET['timeout'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - FraudShield</title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/landing.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="auth-body">
    <div class="auth-container">
        <div class="auth-grid-split">
            <div class="auth-brand-side">
                <div class="auth-brand-content">
                    <a href="index.php" class="auth-logo">
                        <i class="ri-shield-flash-line"></i>
                        <span>FraudShield</span>
                    </a>
                    <h2>Secure Access Portal</h2>
                    <p>Monitor your financial transactions with real-time fraud detection and protection.</p>
                    <div class="auth-features">
                        <div class="auth-feature">
                            <i class="ri-shield-check-line"></i>
                            <span>Real-time fraud monitoring</span>
                        </div>
                        <div class="auth-feature">
                            <i class="ri-bar-chart-box-line"></i>
                            <span>Advanced analytics dashboard</span>
                        </div>
                        <div class="auth-feature">
                            <i class="ri-lock-line"></i>
                            <span>Session-secured access</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="auth-form-side">
                <div class="auth-form-container">
                    <h2>Welcome Back</h2>
                    <p class="auth-subtitle">Enter your credentials to access the system</p>

                    <form action="fraudBackend.php" method="POST" class="auth-form">
                        <input type="hidden" name="action" value="login">

                        <div class="form-group">
                            <label for="username"><i class="ri-user-line"></i> Username</label>
                            <input type="text" id="username" name="username" placeholder="Enter your username" required autocomplete="username">
                        </div>

                        <div class="form-group">
                            <label for="password"><i class="ri-lock-line"></i> Password</label>
                            <div class="password-wrapper">
                                <input type="password" id="password" name="password" placeholder="Enter your password" required autocomplete="current-password">
                                <button type="button" class="toggle-password" onclick="togglePassword()">
                                    <i class="ri-eye-off-line" id="toggleIcon"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="btn-auth-submit">
                            <i class="ri-login-box-line"></i> Sign In
                        </button>
                    </form>

                    <div class="auth-footer-link">
                        <p>Don't have an account? <a href="register.php">Register here</a></p>
                    </div>

                    <div class="auth-demo-credentials">
                        <p><strong>Demo Credentials:</strong></p>
                        <div class="demo-list">
                            <span onclick="fillCredentials('admin','admin123')"><i class="ri-admin-line"></i> Admin</span>
                            <span onclick="fillCredentials('analyst','analyst123')"><i class="ri-search-eye-line"></i> Analyst</span>
                            <span onclick="fillCredentials('johndoe','user123')"><i class="ri-user-line"></i> Customer</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('toggleIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'ri-eye-line';
            } else {
                input.type = 'password';
                icon.className = 'ri-eye-off-line';
            }
        }

        function fillCredentials(user, pass) {
            document.getElementById('username').value = user;
            document.getElementById('password').value = pass;
        }

        function toggleNav() {
            document.getElementById('navLinks')?.classList.toggle('active');
        }

        <?php if ($error): ?>
        Swal.fire({icon: 'error', title: 'Login Failed', text: '<?= addslashes($error) ?>', confirmButtonColor: '#D97706'});
        <?php endif; ?>

        <?php if ($success): ?>
        Swal.fire({icon: 'success', title: 'Success', text: '<?= addslashes($success) ?>', confirmButtonColor: '#D97706'});
        <?php endif; ?>

        <?php if ($timeout): ?>
        Swal.fire({icon: 'warning', title: 'Session Expired', text: 'Your session has expired. Please login again.', confirmButtonColor: '#D97706'});
        <?php endif; ?>
    </script>
</body>
</html>
