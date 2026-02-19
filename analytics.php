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
    <title>Analytics - FraudShield</title>
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
                <h1><i class="ri-bar-chart-box-line"></i> Analytics</h1>
                <p>Fraud detection analytics and trends</p>
            </div>

            <div class="dashboard-grid">
                <div class="dash-card">
                    <div class="dash-card-header"><h3><i class="ri-pie-chart-line"></i> Transactions by Type</h3></div>
                    <div class="dash-card-body" id="byTypeChart">
                        <div class="loading-cell">Loading analytics...</div>
                    </div>
                </div>

                <div class="dash-card">
                    <div class="dash-card-header"><h3><i class="ri-alarm-warning-line"></i> Alerts by Severity</h3></div>
                    <div class="dash-card-body" id="bySeverityChart">
                        <div class="loading-cell">Loading analytics...</div>
                    </div>
                </div>
            </div>

            <div class="dash-card full-width">
                <div class="dash-card-header"><h3><i class="ri-line-chart-line"></i> Daily Transaction Volume (Last 30 Days)</h3></div>
                <div class="dash-card-body" id="dailyChart">
                    <div class="loading-cell">Loading analytics...</div>
                </div>
            </div>

            <div class="dash-card full-width">
                <div class="dash-card-header"><h3><i class="ri-flag-line"></i> Top Flagged Accounts</h3></div>
                <div class="dash-card-body">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead><tr><th>Account</th><th>Holder</th><th>Fraud Count</th></tr></thead>
                            <tbody id="topFlaggedBody">
                                <tr><td colspan="3" class="loading-cell">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="assets/js/script.js"></script>
    <script>
        fetch('fraudBackend.php?action=getAnalytics')
            .then(r => r.json())
            .then(data => {
                if (!data.success) return;
                const d = data.data;

                // By Type
                const colors = {deposit:'#10B981', withdrawal:'#EF4444', transfer:'#3B82F6', payment:'#F59E0B', refund:'#8B5CF6'};
                document.getElementById('byTypeChart').innerHTML = '<div class="chart-bars">' +
                    d.byType.map(t => `
                        <div class="bar-item">
                            <div class="bar-label">${capitalize(t.type)}</div>
                            <div class="bar-track"><div class="bar-fill" style="width:${Math.min(t.count * 10, 100)}%; background:${colors[t.type]||'#D97706'};"></div></div>
                            <div class="bar-value">${t.count} ($${parseFloat(t.total).toLocaleString()})</div>
                        </div>
                    `).join('') + '</div>';

                // By Severity
                const sevColors = {low:'#10B981', medium:'#F59E0B', high:'#EF4444', critical:'#DC2626'};
                document.getElementById('bySeverityChart').innerHTML = '<div class="chart-bars">' +
                    d.bySeverity.map(s => `
                        <div class="bar-item">
                            <div class="bar-label"><span class="badge severity-${s.severity}">${capitalize(s.severity)}</span></div>
                            <div class="bar-track"><div class="bar-fill" style="width:${Math.min(s.count * 15, 100)}%; background:${sevColors[s.severity]||'#D97706'};"></div></div>
                            <div class="bar-value">${s.count}</div>
                        </div>
                    `).join('') + '</div>';

                // Daily
                if (d.daily.length > 0) {
                    const maxCount = Math.max(...d.daily.map(x => parseInt(x.count)));
                    document.getElementById('dailyChart').innerHTML = '<div class="chart-timeline">' +
                        d.daily.map(x => `
                            <div class="timeline-bar" title="${x.date}: ${x.count} txns, $${parseFloat(x.total).toLocaleString()}">
                                <div class="tbar-fill" style="height:${(x.count/maxCount*100)}%;"></div>
                                <span class="tbar-label">${x.date.slice(5)}</span>
                            </div>
                        `).join('') + '</div>';
                } else {
                    document.getElementById('dailyChart').innerHTML = '<div class="loading-cell">No data available</div>';
                }

                // Top Flagged
                document.getElementById('topFlaggedBody').innerHTML = d.topFlagged.length > 0 ?
                    d.topFlagged.map(f => `<tr><td class="mono">${f.account_number}</td><td>${escapeHtml(f.name)}</td><td><span class="badge severity-high">${f.fraud_count}</span></td></tr>`).join('')
                    : '<tr><td colspan="3" class="loading-cell">No flagged accounts</td></tr>';
            });
    </script>
</body>
</html>
