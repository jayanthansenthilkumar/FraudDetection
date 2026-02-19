<?php
include_once 'includes/auth.php';
checkUserAccess(false);
if ($_SESSION['role'] !== 'admin') { header("Location: adminDashboard.php"); exit(); }
include_once 'db.php';

// Get table info
$tables = [];
$result = mysqli_query($conn, "SHOW TABLE STATUS FROM fraud_detection");
while ($row = mysqli_fetch_assoc($result)) {
    $tables[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database - FraudShield</title>
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
                <h1><i class="ri-database-2-line"></i> Database Overview</h1>
                <p>View database schema and table statistics</p>
            </div>

            <div class="dash-card full-width">
                <div class="dash-card-header">
                    <h3><i class="ri-table-line"></i> Tables in fraud_detection</h3>
                </div>
                <div class="dash-card-body">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr><th>Table Name</th><th>Engine</th><th>Rows</th><th>Data Size</th><th>Index Size</th><th>Auto Increment</th><th>Created</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tables as $t): ?>
                                <tr>
                                    <td class="mono"><strong><?= $t['Name'] ?></strong></td>
                                    <td><?= $t['Engine'] ?></td>
                                    <td><?= number_format($t['Rows']) ?></td>
                                    <td><?= round($t['Data_length'] / 1024, 2) ?> KB</td>
                                    <td><?= round($t['Index_length'] / 1024, 2) ?> KB</td>
                                    <td><?= $t['Auto_increment'] ?? '-' ?></td>
                                    <td><?= $t['Create_time'] ? date('M d, Y', strtotime($t['Create_time'])) : '-' ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Table Columns Detail -->
            <?php foreach ($tables as $t): ?>
            <div class="dash-card full-width" style="margin-top: 1rem;">
                <div class="dash-card-header">
                    <h3><i class="ri-layout-column-line"></i> <?= $t['Name'] ?> — Schema</h3>
                </div>
                <div class="dash-card-body">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead><tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr></thead>
                            <tbody>
                                <?php
                                $cols = mysqli_query($conn, "DESCRIBE `{$t['Name']}`");
                                while ($col = mysqli_fetch_assoc($cols)):
                                ?>
                                <tr>
                                    <td class="mono"><?= $col['Field'] ?></td>
                                    <td><?= $col['Type'] ?></td>
                                    <td><?= $col['Null'] ?></td>
                                    <td><?= $col['Key'] ? "<span class='badge badge-outline'>{$col['Key']}</span>" : '' ?></td>
                                    <td><?= $col['Default'] ?? 'NULL' ?></td>
                                    <td><?= $col['Extra'] ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <script src="assets/js/script.js"></script>
</body>
</html>
