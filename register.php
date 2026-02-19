<?php
session_start();
if (isset($_SESSION['user_id'])) {
    $dashboards = ['admin' => 'adminDashboard.php', 'analyst' => 'analystDashboard.php', 'customer' => 'customerDashboard.php'];
    header("Location: " . ($dashboards[$_SESSION['role']] ?? 'index.php'));
    exit();
}
$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - FraudShield</title>
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
                    <h2>Join FraudShield</h2>
                    <p>Create your account and start monitoring your financial transactions with advanced fraud protection.</p>
                    <div class="auth-features">
                        <div class="auth-feature">
                            <i class="ri-bank-card-line"></i>
                            <span>Auto-created savings account</span>
                        </div>
                        <div class="auth-feature">
                            <i class="ri-notification-3-line"></i>
                            <span>Instant fraud alerts</span>
                        </div>
                        <div class="auth-feature">
                            <i class="ri-line-chart-line"></i>
                            <span>Transaction tracking</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="auth-form-side">
                <div class="auth-form-container">
                    <h2>Create Account</h2>
                    <p class="auth-subtitle">Fill in your details to register as a customer</p>

                    <form action="fraudBackend.php" method="POST" class="auth-form">
                        <input type="hidden" name="action" value="register">

                        <div class="form-group">
                            <label for="name"><i class="ri-user-line"></i> Full Name</label>
                            <input type="text" id="name" name="name" placeholder="Enter your full name" required>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="username"><i class="ri-at-line"></i> Username</label>
                                <input type="text" id="username" name="username" placeholder="Choose a username" required>
                            </div>
                            <div class="form-group">
                                <label for="phone"><i class="ri-phone-line"></i> Phone</label>
                                <input type="text" id="phone" name="phone" placeholder="Phone number">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="email"><i class="ri-mail-line"></i> Email</label>
                            <input type="email" id="email" name="email" placeholder="Enter your email" required>
                        </div>

                        <div class="form-group">
                            <label for="password"><i class="ri-lock-line"></i> Password</label>
                            <div class="password-wrapper">
                                <input type="password" id="password" name="password" placeholder="Create a password" required minlength="6">
                                <button type="button" class="toggle-password" onclick="togglePassword()">
                                    <i class="ri-eye-off-line" id="toggleIcon"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="btn-auth-submit">
                            <i class="ri-user-add-line"></i> Create Account
                        </button>
                    </form>

                    <div class="auth-footer-link">
                        <p>Already have an account? <a href="login.php">Sign in here</a></p>
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

        <?php if ($error): ?>
        Swal.fire({icon: 'error', title: 'Registration Failed', text: '<?= addslashes($error) ?>', confirmButtonColor: '#D97706'});
        <?php endif; ?>
    </script>
</body>
</html>
