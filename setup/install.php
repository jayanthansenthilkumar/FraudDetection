<?php
$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = '';

$conn = new mysqli($dbHost, $dbUser, $dbPass);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>Financial Fraud Detection System - Database Setup</h2><hr>";

$sql = "CREATE DATABASE IF NOT EXISTS fraud";
if ($conn->query($sql) === TRUE) {
    echo "✅ Database 'fraud' created successfully<br>";
} else {
    die("❌ Error creating database: " . $conn->error);
}

$conn->select_db("fraud");

$tables = [
    "CREATE TABLE IF NOT EXISTS users (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'analyst', 'customer') DEFAULT 'customer',
        phone VARCHAR(20),
        address TEXT,
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB",

    "CREATE TABLE IF NOT EXISTS accounts (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        user_id INT(11) NOT NULL,
        account_number VARCHAR(20) NOT NULL UNIQUE,
        account_type ENUM('savings', 'checking', 'credit', 'business') DEFAULT 'savings',
        balance DECIMAL(15,2) DEFAULT 0.00,
        credit_limit DECIMAL(15,2) DEFAULT 0.00,
        status ENUM('active', 'frozen', 'closed') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB",

    "CREATE TABLE IF NOT EXISTS transactions (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        account_id INT(11) NOT NULL,
        amount DECIMAL(15,2) NOT NULL,
        type ENUM('deposit', 'withdrawal', 'transfer', 'payment', 'refund') NOT NULL,
        recipient_account VARCHAR(20),
        description TEXT,
        status ENUM('pending', 'approved', 'declined', 'flagged', 'reversed') DEFAULT 'pending',
        location VARCHAR(100),
        ip_address VARCHAR(45),
        device_info VARCHAR(255),
        is_fraud BOOLEAN DEFAULT 0,
        fraud_score DECIMAL(5,2) DEFAULT 0.00,
        reviewed_by INT(11),
        reviewed_at TIMESTAMP NULL,
        review_notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE,
        FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB",

    "CREATE TABLE IF NOT EXISTS fraud_alerts (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        transaction_id INT(11),
        user_id INT(11),
        alert_type VARCHAR(50) NOT NULL,
        severity ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
        status ENUM('new', 'investigating', 'resolved', 'dismissed') DEFAULT 'new',
        description TEXT,
        assigned_to INT(11),
        resolved_by INT(11),
        resolved_at TIMESTAMP NULL,
        resolution_notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (transaction_id) REFERENCES transactions(id) ON DELETE SET NULL,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
        FOREIGN KEY (resolved_by) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB",

    "CREATE TABLE IF NOT EXISTS fraud_rules (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        rule_name VARCHAR(100) NOT NULL,
        description TEXT,
        rule_type ENUM('amount', 'frequency', 'location', 'velocity', 'pattern') NOT NULL,
        threshold_value DECIMAL(15,2),
        score_weight INT DEFAULT 0,
        is_active BOOLEAN DEFAULT 1,
        created_by INT(11),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB",

    "CREATE TABLE IF NOT EXISTS audit_log (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        user_id INT(11),
        action VARCHAR(100) NOT NULL,
        entity_type VARCHAR(50),
        entity_id INT(11),
        details TEXT,
        ip_address VARCHAR(45),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB",

    "CREATE TABLE IF NOT EXISTS settings (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(100) NOT NULL UNIQUE,
        setting_value TEXT,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB",

    "CREATE TABLE IF NOT EXISTS messages (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        sender_id INT(11) NOT NULL,
        recipient_id INT(11) NOT NULL,
        subject VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        is_read BOOLEAN DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (recipient_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB"
];

foreach ($tables as $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "✅ Table created successfully<br>";
    } else {
        echo "❌ Error: " . $conn->error . "<br>";
    }
}

echo "<hr><h3>Inserting Default Data...</h3>";

// Default Settings
$defaultSettings = [
    ['system_name', 'FraudShield'],
    ['max_transaction_amount', '50000'],
    ['alert_threshold_score', '50'],
    ['session_timeout', '1800'],
    ['registration_open', '1'],
    ['high_value_threshold', '5000'],
    ['rapid_txn_window_minutes', '5'],
    ['rapid_txn_count', '3'],
    ['location_check_enabled', '1']
];
foreach ($defaultSettings as $s) {
    $stmt = $conn->prepare("INSERT IGNORE INTO settings (setting_key, setting_value) VALUES (?, ?)");
    $stmt->bind_param("ss", $s[0], $s[1]);
    $stmt->execute();
    $stmt->close();
}
echo "✅ Default settings inserted<br>";

// Default Fraud Rules
$defaultRules = [
    ['High Value Transaction', 'Flags transactions exceeding the threshold amount', 'amount', 5000, 60],
    ['Rapid Transactions', 'Detects multiple transactions within a short time window', 'frequency', 3, 40],
    ['Location Mismatch', 'Flags transactions from unusual locations', 'location', 0, 50],
    ['Velocity Check', 'Detects sudden increase in transaction frequency', 'velocity', 10, 35],
    ['Large Transfer Pattern', 'Flags large transfers to new recipients', 'pattern', 10000, 55]
];
foreach ($defaultRules as $r) {
    $stmt = $conn->prepare("INSERT IGNORE INTO fraud_rules (rule_name, description, rule_type, threshold_value, score_weight) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssdi", $r[0], $r[1], $r[2], $r[3], $r[4]);
    $stmt->execute();
    $stmt->close();
}
echo "✅ Default fraud rules inserted<br>";

// Admin user
$conn->query("INSERT IGNORE INTO users (name, username, email, password, role) VALUES ('System Admin', 'admin', 'admin@fraudshield.com', 'admin123', 'admin')");
echo "✅ Admin: admin / admin123<br>";

// Analyst user
$conn->query("INSERT IGNORE INTO users (name, username, email, password, role) VALUES ('Sarah Analyst', 'analyst', 'analyst@fraudshield.com', 'analyst123', 'analyst')");
echo "✅ Analyst: analyst / analyst123<br>";

// Customer user
$conn->query("INSERT IGNORE INTO users (name, username, email, password, role, phone, address) VALUES ('John Doe', 'johndoe', 'john@example.com', 'user123', 'customer', '+1-555-0123', '123 Main St, New York, NY')");

$custResult = $conn->query("SELECT id FROM users WHERE username = 'johndoe'");
$custRow = $custResult->fetch_assoc();
$custId = $custRow['id'];
echo "✅ Customer: johndoe / user123<br>";

// Customer accounts
$acc1 = 'ACC' . str_pad($custId, 3, '0', STR_PAD_LEFT) . '0001';
$acc2 = 'ACC' . str_pad($custId, 3, '0', STR_PAD_LEFT) . '0002';
$conn->query("INSERT IGNORE INTO accounts (user_id, account_number, account_type, balance) VALUES ($custId, '$acc1', 'savings', 15000.00)");
$conn->query("INSERT IGNORE INTO accounts (user_id, account_number, account_type, balance) VALUES ($custId, '$acc2', 'checking', 3500.00)");
echo "✅ Customer accounts created<br>";

// Sample transactions
$accResult = $conn->query("SELECT id FROM accounts WHERE user_id = $custId LIMIT 1");
$firstAcc = $accResult ? $accResult->fetch_assoc() : null;
if ($firstAcc) {
    $aid = $firstAcc['id'];
    $txns = [
        "INSERT INTO transactions (account_id, amount, type, description, status, location, is_fraud, fraud_score) VALUES ($aid, 2500.00, 'deposit', 'Salary deposit', 'approved', 'New York', 0, 0)",
        "INSERT INTO transactions (account_id, amount, type, description, status, location, is_fraud, fraud_score) VALUES ($aid, 75.50, 'withdrawal', 'ATM withdrawal', 'approved', 'New York', 0, 0)",
        "INSERT INTO transactions (account_id, amount, type, recipient_account, description, status, location, is_fraud, fraud_score) VALUES ($aid, 1200.00, 'transfer', 'ACC0040001', 'Rent payment', 'approved', 'New York', 0, 15)",
        "INSERT INTO transactions (account_id, amount, type, description, status, location, is_fraud, fraud_score) VALUES ($aid, 8500.00, 'withdrawal', 'Large cash withdrawal', 'flagged', 'Chicago', 1, 75)",
        "INSERT INTO transactions (account_id, amount, type, description, status, location, is_fraud, fraud_score) VALUES ($aid, 45.99, 'payment', 'Online purchase', 'approved', 'New York', 0, 0)"
    ];
    foreach ($txns as $t) {
        $conn->query($t);
    }

    $flagged = $conn->query("SELECT id FROM transactions WHERE account_id = $aid AND is_fraud = 1 LIMIT 1");
    if ($flagged && $fr = $flagged->fetch_assoc()) {
        $conn->query("INSERT INTO fraud_alerts (transaction_id, user_id, alert_type, severity, description) VALUES ({$fr['id']}, $custId, 'high_value', 'high', 'Large withdrawal of \$8,500 from unusual location (Chicago)')");
    }
    echo "✅ Sample transactions and alerts created<br>";
}

echo "<hr><h3>✅ Installation Complete!</h3>";
echo "<p><a href='../index.php'>Access the System</a></p>";
echo "<p><strong>Credentials:</strong></p><ul>";
echo "<li>Admin: admin / admin123</li>";
echo "<li>Analyst: analyst / analyst123</li>";
echo "<li>Customer: johndoe / user123</li></ul>";

$conn->close();
?>
