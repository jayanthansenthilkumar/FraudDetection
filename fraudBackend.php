<?php
session_start();
include_once 'db.php';

// Handle GET actions
if (isset($_GET['action'])) {
    $action = $_GET['action'];

    switch ($action) {
        case 'logout':
            // Audit log
            if (isset($_SESSION['user_id'])) {
                $uid = $_SESSION['user_id'];
                $ip = $_SERVER['REMOTE_ADDR'] ?? '';
                mysqli_query($conn, "INSERT INTO audit_log (user_id, action, details, ip_address) VALUES ($uid, 'logout', 'User logged out', '$ip')");
            }
            session_unset();
            session_destroy();
            header("Location: login.php");
            exit();

        case 'getTransactions':
            header('Content-Type: application/json');
            $userId = $_SESSION['user_id'] ?? 0;
            $role = $_SESSION['role'] ?? '';
            $page = intval($_GET['page'] ?? 1);
            $limit = intval($_GET['limit'] ?? 20);
            $offset = ($page - 1) * $limit;
            $search = mysqli_real_escape_string($conn, $_GET['search'] ?? '');
            $filter = mysqli_real_escape_string($conn, $_GET['filter'] ?? '');

            $where = "1=1";
            if ($role === 'customer') {
                $where .= " AND a.user_id = $userId";
            }
            if ($search) {
                $where .= " AND (t.description LIKE '%$search%' OR t.location LIKE '%$search%' OR a.account_number LIKE '%$search%')";
            }
            if ($filter && $filter !== 'all') {
                $where .= " AND t.status = '$filter'";
            }

            $countQ = mysqli_query($conn, "SELECT COUNT(*) as total FROM transactions t JOIN accounts a ON t.account_id = a.id WHERE $where");
            $total = mysqli_fetch_assoc($countQ)['total'];

            $q = mysqli_query($conn, "SELECT t.*, a.account_number, u.name as account_holder 
                FROM transactions t 
                JOIN accounts a ON t.account_id = a.id 
                JOIN users u ON a.user_id = u.id 
                WHERE $where 
                ORDER BY t.created_at DESC 
                LIMIT $limit OFFSET $offset");
            
            $transactions = [];
            while ($row = mysqli_fetch_assoc($q)) {
                $transactions[] = $row;
            }
            echo json_encode(['success' => true, 'data' => $transactions, 'total' => $total, 'page' => $page]);
            exit();

        case 'getAlerts':
            header('Content-Type: application/json');
            $userId = $_SESSION['user_id'] ?? 0;
            $role = $_SESSION['role'] ?? '';
            $status = mysqli_real_escape_string($conn, $_GET['status'] ?? '');

            $where = "1=1";
            if ($role === 'customer') {
                $where .= " AND fa.user_id = $userId";
            } elseif ($role === 'analyst') {
                $where .= " AND (fa.assigned_to = $userId OR fa.assigned_to IS NULL)";
            }
            if ($status && $status !== 'all') {
                $where .= " AND fa.status = '$status'";
            }

            $q = mysqli_query($conn, "SELECT fa.*, t.amount, t.type as txn_type, t.location, a.account_number, u.name as user_name
                FROM fraud_alerts fa
                LEFT JOIN transactions t ON fa.transaction_id = t.id
                LEFT JOIN accounts a ON t.account_id = a.id
                LEFT JOIN users u ON fa.user_id = u.id
                WHERE $where
                ORDER BY fa.created_at DESC LIMIT 100");
            
            $alerts = [];
            while ($row = mysqli_fetch_assoc($q)) {
                $alerts[] = $row;
            }
            echo json_encode(['success' => true, 'data' => $alerts]);
            exit();

        case 'getDashboardStats':
            header('Content-Type: application/json');
            $role = $_SESSION['role'] ?? '';
            $userId = $_SESSION['user_id'] ?? 0;
            $stats = [];

            if ($role === 'admin') {
                $r = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM users"));
                $stats['totalUsers'] = $r['c'];
                $r = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM transactions"));
                $stats['totalTransactions'] = $r['c'];
                $r = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM fraud_alerts WHERE status = 'new'"));
                $stats['newAlerts'] = $r['c'];
                $r = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM transactions WHERE is_fraud = 1"));
                $stats['fraudulentTxns'] = $r['c'];
                $r = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(amount),0) as s FROM transactions WHERE status = 'approved'"));
                $stats['totalVolume'] = $r['s'];
                $r = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM accounts"));
                $stats['totalAccounts'] = $r['c'];
            } elseif ($role === 'analyst') {
                $r = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM fraud_alerts WHERE status IN ('new','investigating') AND (assigned_to = $userId OR assigned_to IS NULL)"));
                $stats['activeAlerts'] = $r['c'];
                $r = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM fraud_alerts WHERE resolved_by = $userId"));
                $stats['resolvedByMe'] = $r['c'];
                $r = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM transactions WHERE is_fraud = 1 AND DATE(created_at) = CURDATE()"));
                $stats['todayFraud'] = $r['c'];
                $r = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM transactions WHERE status = 'flagged'"));
                $stats['flaggedTxns'] = $r['c'];
            } elseif ($role === 'customer') {
                $r = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(balance),0) as b FROM accounts WHERE user_id = $userId AND status = 'active'"));
                $stats['totalBalance'] = $r['b'];
                $r = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM transactions t JOIN accounts a ON t.account_id = a.id WHERE a.user_id = $userId"));
                $stats['myTransactions'] = $r['c'];
                $r = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM accounts WHERE user_id = $userId AND status = 'active'"));
                $stats['myAccounts'] = $r['c'];
                $r = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM fraud_alerts fa JOIN transactions t ON fa.transaction_id = t.id JOIN accounts a ON t.account_id = a.id WHERE a.user_id = $userId AND fa.status = 'new'"));
                $stats['myAlerts'] = $r['c'];
            }

            echo json_encode(['success' => true, 'data' => $stats]);
            exit();

        case 'getUsers':
            header('Content-Type: application/json');
            if (($_SESSION['role'] ?? '') !== 'admin') {
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                exit();
            }
            $q = mysqli_query($conn, "SELECT id, name, username, email, role, phone, status, created_at FROM users ORDER BY created_at DESC");
            $users = [];
            while ($row = mysqli_fetch_assoc($q)) { $users[] = $row; }
            echo json_encode(['success' => true, 'data' => $users]);
            exit();

        case 'getAccounts':
            header('Content-Type: application/json');
            $userId = $_SESSION['user_id'] ?? 0;
            $role = $_SESSION['role'] ?? '';
            $where = ($role === 'customer') ? "WHERE a.user_id = $userId" : "";
            $q = mysqli_query($conn, "SELECT a.*, u.name as holder_name, u.username FROM accounts a JOIN users u ON a.user_id = u.id $where ORDER BY a.created_at DESC");
            $accounts = [];
            while ($row = mysqli_fetch_assoc($q)) { $accounts[] = $row; }
            echo json_encode(['success' => true, 'data' => $accounts]);
            exit();

        case 'getFraudRules':
            header('Content-Type: application/json');
            $q = mysqli_query($conn, "SELECT * FROM fraud_rules ORDER BY created_at DESC");
            $rules = [];
            while ($row = mysqli_fetch_assoc($q)) { $rules[] = $row; }
            echo json_encode(['success' => true, 'data' => $rules]);
            exit();

        case 'getAuditLog':
            header('Content-Type: application/json');
            if (($_SESSION['role'] ?? '') !== 'admin') {
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                exit();
            }
            $limit = intval($_GET['limit'] ?? 50);
            $q = mysqli_query($conn, "SELECT al.*, u.name, u.username FROM audit_log al LEFT JOIN users u ON al.user_id = u.id ORDER BY al.created_at DESC LIMIT $limit");
            $logs = [];
            while ($row = mysqli_fetch_assoc($q)) { $logs[] = $row; }
            echo json_encode(['success' => true, 'data' => $logs]);
            exit();

        case 'getMessages':
            header('Content-Type: application/json');
            $userId = $_SESSION['user_id'] ?? 0;
            $q = mysqli_query($conn, "SELECT m.*, s.name as sender_name, r.name as recipient_name 
                FROM messages m 
                JOIN users s ON m.sender_id = s.id 
                JOIN users r ON m.recipient_id = r.id 
                WHERE m.recipient_id = $userId OR m.sender_id = $userId 
                ORDER BY m.created_at DESC LIMIT 50");
            $messages = [];
            while ($row = mysqli_fetch_assoc($q)) { $messages[] = $row; }
            echo json_encode(['success' => true, 'data' => $messages]);
            exit();

        case 'getAnalytics':
            header('Content-Type: application/json');
            $stats = [];
            // Transactions by type
            $q = mysqli_query($conn, "SELECT type, COUNT(*) as count, SUM(amount) as total FROM transactions GROUP BY type");
            $byType = [];
            while ($row = mysqli_fetch_assoc($q)) { $byType[] = $row; }
            $stats['byType'] = $byType;

            // Daily transaction volume (last 30 days)
            $q = mysqli_query($conn, "SELECT DATE(created_at) as date, COUNT(*) as count, SUM(amount) as total FROM transactions WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) GROUP BY DATE(created_at) ORDER BY date");
            $daily = [];
            while ($row = mysqli_fetch_assoc($q)) { $daily[] = $row; }
            $stats['daily'] = $daily;

            // Fraud by severity
            $q = mysqli_query($conn, "SELECT severity, COUNT(*) as count FROM fraud_alerts GROUP BY severity");
            $bySeverity = [];
            while ($row = mysqli_fetch_assoc($q)) { $bySeverity[] = $row; }
            $stats['bySeverity'] = $bySeverity;

            // Top flagged accounts
            $q = mysqli_query($conn, "SELECT a.account_number, u.name, COUNT(*) as fraud_count 
                FROM transactions t 
                JOIN accounts a ON t.account_id = a.id 
                JOIN users u ON a.user_id = u.id 
                WHERE t.is_fraud = 1 
                GROUP BY a.id ORDER BY fraud_count DESC LIMIT 10");
            $topFlagged = [];
            while ($row = mysqli_fetch_assoc($q)) { $topFlagged[] = $row; }
            $stats['topFlagged'] = $topFlagged;

            echo json_encode(['success' => true, 'data' => $stats]);
            exit();
    }
}

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'login':
            $username = mysqli_real_escape_string($conn, $_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($username) || empty($password)) {
                header("Location: login.php?error=" . urlencode("All fields are required"));
                exit();
            }

            $q = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username' AND status = 'active'");
            if ($q && $user = mysqli_fetch_assoc($q)) {
                // Plain text comparison (matching Spark pattern - no hashing)
                if ($password === $user['password']) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['userid'] = $user['id']; // Spark compatible
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['name'] = $user['name'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['last_activity'] = time();

                    // Audit log
                    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
                    mysqli_query($conn, "INSERT INTO audit_log (user_id, action, details, ip_address) VALUES ({$user['id']}, 'login', 'User logged in', '$ip')");

                    $dashboards = [
                        'admin' => 'adminDashboard.php',
                        'analyst' => 'analystDashboard.php',
                        'customer' => 'customerDashboard.php'
                    ];
                    $redirect = $dashboards[$user['role']] ?? 'login.php';
                    header("Location: $redirect");
                    exit();
                }
            }

            header("Location: login.php?error=" . urlencode("Invalid credentials"));
            exit();

        case 'register':
            $name = mysqli_real_escape_string($conn, $_POST['name'] ?? '');
            $username = mysqli_real_escape_string($conn, $_POST['username'] ?? '');
            $email = mysqli_real_escape_string($conn, $_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $phone = mysqli_real_escape_string($conn, $_POST['phone'] ?? '');

            if (empty($name) || empty($username) || empty($email) || empty($password)) {
                header("Location: register.php?error=" . urlencode("All fields are required"));
                exit();
            }

            // Check if username/email exists
            $check = mysqli_query($conn, "SELECT id FROM users WHERE username = '$username' OR email = '$email'");
            if (mysqli_num_rows($check) > 0) {
                header("Location: register.php?error=" . urlencode("Username or email already exists"));
                exit();
            }

            // Registration open check
            $regSetting = mysqli_fetch_assoc(mysqli_query($conn, "SELECT setting_value FROM settings WHERE setting_key = 'registration_open'"));
            if ($regSetting && $regSetting['setting_value'] === '0') {
                header("Location: register.php?error=" . urlencode("Registration is currently closed"));
                exit();
            }

            $q = mysqli_query($conn, "INSERT INTO users (name, username, email, password, role, phone) VALUES ('$name', '$username', '$email', '$password', 'customer', '$phone')");
            if ($q) {
                $newUserId = mysqli_insert_id($conn);
                // Auto-create savings account
                $accNum = 'ACC' . str_pad($newUserId, 3, '0', STR_PAD_LEFT) . '0001';
                mysqli_query($conn, "INSERT INTO accounts (user_id, account_number, account_type, balance) VALUES ($newUserId, '$accNum', 'savings', 0.00)");

                // Audit log
                $ip = $_SERVER['REMOTE_ADDR'] ?? '';
                mysqli_query($conn, "INSERT INTO audit_log (user_id, action, entity_type, entity_id, details, ip_address) VALUES ($newUserId, 'register', 'user', $newUserId, 'New customer registered', '$ip')");

                header("Location: login.php?success=" . urlencode("Registration successful! Please login."));
                exit();
            }

            header("Location: register.php?error=" . urlencode("Registration failed. Please try again."));
            exit();

        case 'addTransaction':
            header('Content-Type: application/json');
            $userId = $_SESSION['user_id'] ?? 0;
            $accountId = intval($_POST['account_id'] ?? 0);
            $amount = floatval($_POST['amount'] ?? 0);
            $type = mysqli_real_escape_string($conn, $_POST['type'] ?? '');
            $recipient = mysqli_real_escape_string($conn, $_POST['recipient_account'] ?? '');
            $description = mysqli_real_escape_string($conn, $_POST['description'] ?? '');
            $location = mysqli_real_escape_string($conn, $_POST['location'] ?? '');
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
            $device = mysqli_real_escape_string($conn, $_SERVER['HTTP_USER_AGENT'] ?? '');

            // Validate account ownership for customers
            $role = $_SESSION['role'] ?? '';
            if ($role === 'customer') {
                $accCheck = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM accounts WHERE id = $accountId AND user_id = $userId AND status = 'active'"));
                if (!$accCheck) {
                    echo json_encode(['success' => false, 'message' => 'Invalid account']);
                    exit();
                }
            }

            // Calculate fraud score
            $fraudScore = 0;
            $isFraud = 0;
            $alertReasons = [];

            // Rule 1: High value check
            $highValRule = mysqli_fetch_assoc(mysqli_query($conn, "SELECT threshold_value, score_weight FROM fraud_rules WHERE rule_name = 'High Value Transaction' AND is_active = 1"));
            if ($highValRule && $amount > $highValRule['threshold_value']) {
                $fraudScore += $highValRule['score_weight'];
                $alertReasons[] = "High value transaction: \$$amount exceeds \${$highValRule['threshold_value']} threshold";
            }

            // Rule 2: Rapid transactions
            $rapidRule = mysqli_fetch_assoc(mysqli_query($conn, "SELECT threshold_value, score_weight FROM fraud_rules WHERE rule_name = 'Rapid Transactions' AND is_active = 1"));
            if ($rapidRule) {
                $recentQ = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM transactions WHERE account_id = $accountId AND created_at >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)"));
                if ($recentQ['c'] >= $rapidRule['threshold_value']) {
                    $fraudScore += $rapidRule['score_weight'];
                    $alertReasons[] = "Rapid transactions: {$recentQ['c']} transactions in 5 minutes";
                }
            }

            // Rule 3: Location mismatch
            $locRule = mysqli_fetch_assoc(mysqli_query($conn, "SELECT score_weight FROM fraud_rules WHERE rule_name = 'Location Mismatch' AND is_active = 1"));
            if ($locRule && $location) {
                $lastLoc = mysqli_fetch_assoc(mysqli_query($conn, "SELECT location FROM transactions WHERE account_id = $accountId AND location != '' ORDER BY created_at DESC LIMIT 1"));
                if ($lastLoc && $lastLoc['location'] !== $location) {
                    $fraudScore += $locRule['score_weight'];
                    $alertReasons[] = "Location mismatch: Transaction from '$location', last was '{$lastLoc['location']}'";
                }
            }

            // Rule 4: Large transfer
            $lgRule = mysqli_fetch_assoc(mysqli_query($conn, "SELECT threshold_value, score_weight FROM fraud_rules WHERE rule_name = 'Large Transfer Pattern' AND is_active = 1"));
            if ($lgRule && $type === 'transfer' && $amount > $lgRule['threshold_value']) {
                $fraudScore += $lgRule['score_weight'];
                $alertReasons[] = "Large transfer of \$$amount to $recipient";
            }

            // Threshold check
            $thresholdSetting = mysqli_fetch_assoc(mysqli_query($conn, "SELECT setting_value FROM settings WHERE setting_key = 'alert_threshold_score'"));
            $threshold = $thresholdSetting ? intval($thresholdSetting['setting_value']) : 50;

            $status = 'approved';
            if ($fraudScore >= $threshold) {
                $isFraud = 1;
                $status = 'flagged';
            }

            $q = mysqli_query($conn, "INSERT INTO transactions (account_id, amount, type, recipient_account, description, status, location, ip_address, device_info, is_fraud, fraud_score) 
                VALUES ($accountId, $amount, '$type', '$recipient', '$description', '$status', '$location', '$ip', '$device', $isFraud, $fraudScore)");

            if ($q) {
                $txnId = mysqli_insert_id($conn);

                // Update balance
                if ($status === 'approved') {
                    if ($type === 'deposit' || $type === 'refund') {
                        mysqli_query($conn, "UPDATE accounts SET balance = balance + $amount WHERE id = $accountId");
                    } elseif (in_array($type, ['withdrawal', 'transfer', 'payment'])) {
                        mysqli_query($conn, "UPDATE accounts SET balance = balance - $amount WHERE id = $accountId");
                    }
                }

                // Create fraud alerts
                if ($isFraud && count($alertReasons) > 0) {
                    $accOwner = mysqli_fetch_assoc(mysqli_query($conn, "SELECT user_id FROM accounts WHERE id = $accountId"));
                    $ownerId = $accOwner['user_id'];
                    $severity = $fraudScore >= 80 ? 'critical' : ($fraudScore >= 60 ? 'high' : 'medium');
                    $desc = mysqli_real_escape_string($conn, implode('; ', $alertReasons));
                    
                    foreach ($alertReasons as $reason) {
                        $alertType = 'suspicious_activity';
                        if (strpos($reason, 'High value') !== false) $alertType = 'high_value';
                        if (strpos($reason, 'Rapid') !== false) $alertType = 'rapid_activity';
                        if (strpos($reason, 'Location') !== false) $alertType = 'location_anomaly';
                        if (strpos($reason, 'Large transfer') !== false) $alertType = 'large_transfer';
                        $reasonEsc = mysqli_real_escape_string($conn, $reason);
                        mysqli_query($conn, "INSERT INTO fraud_alerts (transaction_id, user_id, alert_type, severity, description) VALUES ($txnId, $ownerId, '$alertType', '$severity', '$reasonEsc')");
                    }
                }

                // Audit log
                mysqli_query($conn, "INSERT INTO audit_log (user_id, action, entity_type, entity_id, details, ip_address) VALUES ($userId, 'create_transaction', 'transaction', $txnId, 'Amount: $amount, Type: $type, Status: $status, Fraud Score: $fraudScore', '$ip')");

                echo json_encode(['success' => true, 'message' => $isFraud ? 'Transaction flagged for review' : 'Transaction processed', 'fraud_score' => $fraudScore, 'status' => $status]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to process transaction']);
            }
            exit();

        case 'updateAlertStatus':
            header('Content-Type: application/json');
            $alertId = intval($_POST['alert_id'] ?? 0);
            $newStatus = mysqli_real_escape_string($conn, $_POST['status'] ?? '');
            $notes = mysqli_real_escape_string($conn, $_POST['notes'] ?? '');
            $userId = $_SESSION['user_id'] ?? 0;

            $updates = "status = '$newStatus'";
            if ($newStatus === 'investigating') {
                $updates .= ", assigned_to = $userId";
            }
            if ($newStatus === 'resolved' || $newStatus === 'dismissed') {
                $updates .= ", resolved_by = $userId, resolved_at = NOW(), resolution_notes = '$notes'";
            }

            $q = mysqli_query($conn, "UPDATE fraud_alerts SET $updates WHERE id = $alertId");
            if ($q) {
                // If resolved/dismissed, also update transaction
                if ($newStatus === 'resolved') {
                    $alert = mysqli_fetch_assoc(mysqli_query($conn, "SELECT transaction_id FROM fraud_alerts WHERE id = $alertId"));
                    if ($alert && $alert['transaction_id']) {
                        mysqli_query($conn, "UPDATE transactions SET status = 'approved', reviewed_by = $userId, reviewed_at = NOW(), review_notes = '$notes' WHERE id = {$alert['transaction_id']}");
                    }
                } elseif ($newStatus === 'dismissed') {
                    $alert = mysqli_fetch_assoc(mysqli_query($conn, "SELECT transaction_id FROM fraud_alerts WHERE id = $alertId"));
                    if ($alert && $alert['transaction_id']) {
                        mysqli_query($conn, "UPDATE transactions SET is_fraud = 0, reviewed_by = $userId, reviewed_at = NOW(), review_notes = '$notes' WHERE id = {$alert['transaction_id']}");
                    }
                }

                $ip = $_SERVER['REMOTE_ADDR'] ?? '';
                mysqli_query($conn, "INSERT INTO audit_log (user_id, action, entity_type, entity_id, details, ip_address) VALUES ($userId, 'update_alert', 'fraud_alert', $alertId, 'Status changed to: $newStatus', '$ip')");

                echo json_encode(['success' => true, 'message' => 'Alert updated']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Update failed']);
            }
            exit();

        case 'updateUser':
            header('Content-Type: application/json');
            if (($_SESSION['role'] ?? '') !== 'admin') {
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                exit();
            }
            $targetId = intval($_POST['user_id'] ?? 0);
            $name = mysqli_real_escape_string($conn, $_POST['name'] ?? '');
            $email = mysqli_real_escape_string($conn, $_POST['email'] ?? '');
            $role = mysqli_real_escape_string($conn, $_POST['role'] ?? '');
            $status = mysqli_real_escape_string($conn, $_POST['status'] ?? '');

            $q = mysqli_query($conn, "UPDATE users SET name='$name', email='$email', role='$role', status='$status' WHERE id=$targetId");
            if ($q) {
                $ip = $_SERVER['REMOTE_ADDR'] ?? '';
                $uid = $_SESSION['user_id'];
                mysqli_query($conn, "INSERT INTO audit_log (user_id, action, entity_type, entity_id, details, ip_address) VALUES ($uid, 'update_user', 'user', $targetId, 'Updated role=$role, status=$status', '$ip')");
                echo json_encode(['success' => true, 'message' => 'User updated']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Update failed']);
            }
            exit();

        case 'updateProfile':
            header('Content-Type: application/json');
            $userId = $_SESSION['user_id'] ?? 0;
            $name = mysqli_real_escape_string($conn, $_POST['name'] ?? '');
            $email = mysqli_real_escape_string($conn, $_POST['email'] ?? '');
            $phone = mysqli_real_escape_string($conn, $_POST['phone'] ?? '');
            $address = mysqli_real_escape_string($conn, $_POST['address'] ?? '');

            $q = mysqli_query($conn, "UPDATE users SET name='$name', email='$email', phone='$phone', address='$address' WHERE id=$userId");
            if ($q) {
                $_SESSION['name'] = $name;
                $_SESSION['email'] = $email;
                echo json_encode(['success' => true, 'message' => 'Profile updated']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Update failed']);
            }
            exit();

        case 'changePassword':
            header('Content-Type: application/json');
            $userId = $_SESSION['user_id'] ?? 0;
            $current = $_POST['current_password'] ?? '';
            $newPass = $_POST['new_password'] ?? '';

            $user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT password FROM users WHERE id = $userId"));
            if ($user && $current === $user['password']) {
                $newPassEsc = mysqli_real_escape_string($conn, $newPass);
                mysqli_query($conn, "UPDATE users SET password = '$newPassEsc' WHERE id = $userId");
                echo json_encode(['success' => true, 'message' => 'Password changed']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
            }
            exit();

        case 'sendMessage':
            header('Content-Type: application/json');
            $senderId = $_SESSION['user_id'] ?? 0;
            $recipientId = intval($_POST['recipient_id'] ?? 0);
            $subject = mysqli_real_escape_string($conn, $_POST['subject'] ?? '');
            $message = mysqli_real_escape_string($conn, $_POST['message'] ?? '');

            if ($recipientId && $subject && $message) {
                $q = mysqli_query($conn, "INSERT INTO messages (sender_id, recipient_id, subject, message) VALUES ($senderId, $recipientId, '$subject', '$message')");
                echo json_encode(['success' => $q ? true : false, 'message' => $q ? 'Message sent' : 'Failed']);
            } else {
                echo json_encode(['success' => false, 'message' => 'All fields required']);
            }
            exit();

        case 'markMessageRead':
            header('Content-Type: application/json');
            $msgId = intval($_POST['message_id'] ?? 0);
            $userId = $_SESSION['user_id'] ?? 0;
            mysqli_query($conn, "UPDATE messages SET is_read = 1 WHERE id = $msgId AND recipient_id = $userId");
            echo json_encode(['success' => true]);
            exit();

        case 'updateFraudRule':
            header('Content-Type: application/json');
            $ruleId = intval($_POST['rule_id'] ?? 0);
            $threshold = floatval($_POST['threshold_value'] ?? 0);
            $weight = intval($_POST['score_weight'] ?? 0);
            $isActive = intval($_POST['is_active'] ?? 1);

            $q = mysqli_query($conn, "UPDATE fraud_rules SET threshold_value=$threshold, score_weight=$weight, is_active=$isActive WHERE id=$ruleId");
            echo json_encode(['success' => $q ? true : false, 'message' => $q ? 'Rule updated' : 'Update failed']);
            exit();

        case 'updateSettings':
            header('Content-Type: application/json');
            if (($_SESSION['role'] ?? '') !== 'admin') {
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                exit();
            }
            $settings = $_POST['settings'] ?? [];
            foreach ($settings as $key => $value) {
                $k = mysqli_real_escape_string($conn, $key);
                $v = mysqli_real_escape_string($conn, $value);
                mysqli_query($conn, "INSERT INTO settings (setting_key, setting_value) VALUES ('$k', '$v') ON DUPLICATE KEY UPDATE setting_value = '$v'");
            }
            echo json_encode(['success' => true, 'message' => 'Settings saved']);
            exit();

        case 'freezeAccount':
            header('Content-Type: application/json');
            $accountId = intval($_POST['account_id'] ?? 0);
            $action_type = mysqli_real_escape_string($conn, $_POST['freeze_action'] ?? 'freeze');
            $newStatus = ($action_type === 'freeze') ? 'frozen' : 'active';
            $q = mysqli_query($conn, "UPDATE accounts SET status = '$newStatus' WHERE id = $accountId");
            if ($q) {
                $uid = $_SESSION['user_id'];
                $ip = $_SERVER['REMOTE_ADDR'] ?? '';
                mysqli_query($conn, "INSERT INTO audit_log (user_id, action, entity_type, entity_id, details, ip_address) VALUES ($uid, '{$action_type}_account', 'account', $accountId, 'Account $action_type', '$ip')");
                echo json_encode(['success' => true, 'message' => "Account {$action_type}d"]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Action failed']);
            }
            exit();
    }
}
?>
