<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FraudShield - Financial Fraud Detection System</title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/landing.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="landing-nav">
        <div class="nav-container">
            <a href="index.php" class="nav-brand">
                <i class="ri-shield-flash-line"></i>
                <span>FraudShield</span>
            </a>
            <div class="nav-links" id="navLinks">
                <a href="#features">Features</a>
                <a href="#stats">Statistics</a>
                <a href="#how-it-works">How It Works</a>
                <a href="#faq">FAQ</a>
            </div>
            <div class="nav-actions">
                <a href="login.php" class="btn-nav-login">Login</a>
                <a href="register.php" class="btn-nav-register">Get Started</a>
            </div>
            <button class="nav-toggle" onclick="toggleNav()">
                <i class="ri-menu-line"></i>
            </button>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <canvas id="particleCanvas"></canvas>
        <div class="hero-content">
            <div class="hero-badge">
                <i class="ri-shield-check-line"></i>
                <span>Advanced Fraud Protection</span>
            </div>
            <h1>Detect. Prevent.<br><span class="gradient-text">Protect.</span></h1>
            <p class="hero-subtitle">Real-time financial fraud detection system powered by behavioral analysis, pattern recognition, and rule-based scoring to safeguard your transactions.</p>
            <div class="hero-buttons">
                <a href="register.php" class="btn-primary">
                    <i class="ri-shield-flash-line"></i> Start Protection
                </a>
                <a href="#features" class="btn-secondary">
                    <i class="ri-play-circle-line"></i> Learn More
                </a>
            </div>
            <div class="hero-metrics">
                <div class="metric">
                    <span class="metric-value" data-count="99.7">0</span><span class="metric-suffix">%</span>
                    <span class="metric-label">Detection Rate</span>
                </div>
                <div class="metric-divider"></div>
                <div class="metric">
                    <span class="metric-value" data-count="50">&lt;50</span><span class="metric-suffix">ms</span>
                    <span class="metric-label">Response Time</span>
                </div>
                <div class="metric-divider"></div>
                <div class="metric">
                    <span class="metric-value" data-count="24">24</span><span class="metric-suffix">/7</span>
                    <span class="metric-label">Monitoring</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Strip -->
    <section class="stats-strip" id="stats">
        <div class="stats-container">
            <div class="stat-card">
                <i class="ri-exchange-funds-line"></i>
                <div class="stat-info">
                    <span class="stat-number" data-count="1000000">0</span>
                    <span class="stat-label">Transactions Monitored</span>
                </div>
            </div>
            <div class="stat-card">
                <i class="ri-alarm-warning-line"></i>
                <div class="stat-info">
                    <span class="stat-number" data-count="15000">0</span>
                    <span class="stat-label">Fraud Cases Detected</span>
                </div>
            </div>
            <div class="stat-card">
                <i class="ri-money-dollar-circle-line"></i>
                <div class="stat-info">
                    <span class="stat-number" data-count="50">$0</span>
                    <span class="stat-label">Million Saved</span>
                </div>
            </div>
            <div class="stat-card">
                <i class="ri-user-heart-line"></i>
                <div class="stat-info">
                    <span class="stat-number" data-count="10000">0</span>
                    <span class="stat-label">Protected Users</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Features -->
    <section class="features" id="features">
        <div class="section-container">
            <div class="section-header">
                <span class="section-badge">Core Features</span>
                <h2>Comprehensive Fraud Protection</h2>
                <p>Our multi-layered detection system analyzes every transaction in real-time using advanced algorithms and pattern recognition.</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="ri-radar-line"></i>
                    </div>
                    <h3>Real-Time Monitoring</h3>
                    <p>Every transaction is analyzed in real-time against multiple fraud detection rules, ensuring instant identification of suspicious activity.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="ri-brain-line"></i>
                    </div>
                    <h3>Behavioral Analysis</h3>
                    <p>Track user behavior patterns including transaction frequency, amounts, and locations to detect anomalies and deviations from normal activity.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="ri-shield-star-line"></i>
                    </div>
                    <h3>Rule-Based Scoring</h3>
                    <p>Configurable fraud rules with weighted scoring system that automatically flags transactions exceeding risk thresholds.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="ri-map-pin-line"></i>
                    </div>
                    <h3>Location Intelligence</h3>
                    <p>Geographic analysis detects transactions from unexpected locations, identifying potential card theft or account compromise.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="ri-bar-chart-grouped-line"></i>
                    </div>
                    <h3>Advanced Analytics</h3>
                    <p>Comprehensive dashboards with visual analytics showing fraud trends, transaction volumes, and risk assessments.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="ri-lock-line"></i>
                    </div>
                    <h3>Secure Archives</h3>
                    <p>Complete audit trail of all transactions, alerts, and administrative actions with detailed logging and reporting.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="how-it-works" id="how-it-works">
        <div class="section-container">
            <div class="section-header">
                <span class="section-badge">Process</span>
                <h2>How FraudShield Works</h2>
                <p>Our detection pipeline processes each transaction through multiple security layers.</p>
            </div>
            <div class="process-timeline">
                <div class="process-step">
                    <div class="step-number">01</div>
                    <div class="step-content">
                        <h3>Transaction Initiated</h3>
                        <p>A customer initiates a deposit, withdrawal, transfer, or payment through their account.</p>
                    </div>
                </div>
                <div class="process-step">
                    <div class="step-number">02</div>
                    <div class="step-content">
                        <h3>Rule Engine Analysis</h3>
                        <p>The transaction is evaluated against active fraud rules — high value, rapid frequency, location anomaly, and velocity checks.</p>
                    </div>
                </div>
                <div class="process-step">
                    <div class="step-number">03</div>
                    <div class="step-content">
                        <h3>Risk Score Calculation</h3>
                        <p>Each triggered rule adds weighted points to a cumulative fraud score. Higher scores indicate greater risk.</p>
                    </div>
                </div>
                <div class="process-step">
                    <div class="step-number">04</div>
                    <div class="step-content">
                        <h3>Decision & Alert</h3>
                        <p>Transactions exceeding the threshold are flagged, alerts are generated, and analysts are notified for investigation.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Roles Section -->
    <section class="roles-section">
        <div class="section-container">
            <div class="section-header">
                <span class="section-badge">User Roles</span>
                <h2>Role-Based Access Control</h2>
                <p>Three distinct roles with tailored dashboards and permissions.</p>
            </div>
            <div class="roles-grid">
                <div class="role-card">
                    <div class="role-icon admin-icon">
                        <i class="ri-admin-line"></i>
                    </div>
                    <h3>Administrator</h3>
                    <p>Full system control: manage users, configure fraud rules, view analytics, access audit logs, and system settings.</p>
                    <ul>
                        <li><i class="ri-check-line"></i> User Management</li>
                        <li><i class="ri-check-line"></i> System Configuration</li>
                        <li><i class="ri-check-line"></i> Complete Analytics</li>
                        <li><i class="ri-check-line"></i> Audit Trail Access</li>
                    </ul>
                </div>
                <div class="role-card featured">
                    <div class="role-icon analyst-icon">
                        <i class="ri-search-eye-line"></i>
                    </div>
                    <h3>Fraud Analyst</h3>
                    <p>Investigate alerts, analyze patterns, review flagged transactions, and manage fraud cases.</p>
                    <ul>
                        <li><i class="ri-check-line"></i> Alert Investigation</li>
                        <li><i class="ri-check-line"></i> Pattern Analysis</li>
                        <li><i class="ri-check-line"></i> Transaction Review</li>
                        <li><i class="ri-check-line"></i> Case Management</li>
                    </ul>
                </div>
                <div class="role-card">
                    <div class="role-icon customer-icon">
                        <i class="ri-user-line"></i>
                    </div>
                    <h3>Customer</h3>
                    <p>View account balances, track transactions, receive fraud notifications, and manage personal profile.</p>
                    <ul>
                        <li><i class="ri-check-line"></i> Account Overview</li>
                        <li><i class="ri-check-line"></i> Transaction History</li>
                        <li><i class="ri-check-line"></i> Fraud Notifications</li>
                        <li><i class="ri-check-line"></i> Secure Messaging</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ -->
    <section class="faq-section" id="faq">
        <div class="section-container">
            <div class="section-header">
                <span class="section-badge">FAQ</span>
                <h2>Frequently Asked Questions</h2>
            </div>
            <div class="faq-grid">
                <div class="faq-item" onclick="toggleFaq(this)">
                    <div class="faq-question">
                        <span>How does the fraud scoring system work?</span>
                        <i class="ri-arrow-down-s-line"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Each transaction is evaluated against active fraud rules. Each rule has a score weight — when triggered, the weight is added to the transaction's fraud score. If the total score exceeds the configured threshold (default: 50), the transaction is flagged for review.</p>
                    </div>
                </div>
                <div class="faq-item" onclick="toggleFaq(this)">
                    <div class="faq-question">
                        <span>What types of fraud can the system detect?</span>
                        <i class="ri-arrow-down-s-line"></i>
                    </div>
                    <div class="faq-answer">
                        <p>The system detects high-value transactions, rapid transaction patterns (multiple transactions in a short window), location mismatches, velocity anomalies, and large transfer patterns to new recipients.</p>
                    </div>
                </div>
                <div class="faq-item" onclick="toggleFaq(this)">
                    <div class="faq-question">
                        <span>Can fraud rules be customized?</span>
                        <i class="ri-arrow-down-s-line"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Yes. Administrators and analysts can adjust rule thresholds, score weights, and enable/disable individual rules through the Fraud Rules management page.</p>
                    </div>
                </div>
                <div class="faq-item" onclick="toggleFaq(this)">
                    <div class="faq-question">
                        <span>What database system is used?</span>
                        <i class="ri-arrow-down-s-line"></i>
                    </div>
                    <div class="faq-answer">
                        <p>FraudShield uses MySQL with InnoDB storage engine, supporting foreign key constraints, transactions, and referential integrity across 8 interconnected tables.</p>
                    </div>
                </div>
                <div class="faq-item" onclick="toggleFaq(this)">
                    <div class="faq-question">
                        <span>Is the system suitable for DBMS coursework?</span>
                        <i class="ri-arrow-down-s-line"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Absolutely. The system demonstrates key DBMS concepts: normalization, foreign keys, joins, aggregate queries, stored procedures, audit trails, role-based access, and CRUD operations across related tables.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="cta-section">
        <div class="section-container">
            <div class="cta-content">
                <h2>Ready to Secure Your Transactions?</h2>
                <p>Start monitoring your financial activity with real-time fraud detection.</p>
                <div class="cta-buttons">
                    <a href="register.php" class="btn-primary"><i class="ri-shield-flash-line"></i> Create Account</a>
                    <a href="login.php" class="btn-secondary"><i class="ri-login-box-line"></i> Sign In</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="landing-footer">
        <div class="footer-container">
            <div class="footer-brand">
                <i class="ri-shield-flash-line"></i>
                <span>FraudShield</span>
                <p>Financial Fraud Detection System</p>
            </div>
            <div class="footer-links">
                <a href="#features">Features</a>
                <a href="#how-it-works">How It Works</a>
                <a href="#faq">FAQ</a>
                <a href="login.php">Login</a>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 FraudShield. DBMS Project - Financial Fraud Detection System.</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/landing.js"></script>
</body>
</html>
