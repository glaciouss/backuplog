<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Logs for <?= $selectedDate ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
        }
        h2 {
            color: #555;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        table th {
            background-color: #f4f4f9;
        }
        table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>
<div class="content">
        <table>
            <caption>Backup/Replication Logs</caption>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Status</th>
                    <th>Log Time</th>
                    <th>Last Successful Time</th>
                    <th>Log Type</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $combinedLogs = array_merge($logs['backupLogs'], $logs['replicationLogs']);
                foreach ($combinedLogs as $log): ?>
                    <tr>
                        <td><?= $log['name'] ?></td>
                        <td>
                            <?php if (isset($log['is_delayed']) && $log['is_delayed']): ?>
                                <span style="color: red;">Delayed</span> <!-- Red indicator -->
                            <?php else: ?>
                                <span style="color: green;">On Time</span>
                            <?php endif; ?>
                        </td>
                        <td><?= $log['log_time'] ?></td>
                        <td><?= $log['last_successful_time'] ?></td>
                        <td><?= $log['log_type'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <table>
            <caption>DB Backup Logs</caption>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Log Time (DC)</th>
                    <th>Backup Time (DC)</th>
                    <th>Log Time (DR)</th>
                    <th>Backup Time (DR)</th>
                    <th>Log Time (NDC)</th>
                    <th>Backup Time (NDC)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs['dbBackupLogs'] as $log): ?>
                    <tr>
                        <td><?= $log['name'] ?></td>
                        <td><?= $log['log_time_dc'] ?? 'N/A' ?></td>
                        <td><?= $log['backup_time_dc'] ?? 'N/A' ?></td>
                        <td><?= $log['log_time_dr'] ?? 'N/A' ?></td>
                        <td><?= $log['backup_time_dr'] ?? 'N/A' ?></td>
                        <td><?= $log['log_time_ndc'] ?? 'N/A' ?></td>
                        <td><?= $log['backup_time_ndc'] ?? 'N/A' ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <table>
            <caption>Storage Space Logs</caption>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Free Space</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs['storageSpaceLogs'] as $log): ?>
                    <tr>
                        <td><?= $log['name'] ?></td>
                        <td><?= $log['free_space'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="additional-info">
            <h3>Additional Information</h3>
            <?php foreach ($logs['additionalInfoLogs'] as $log): ?>
                <p>Submitted by: <?= $log['submitted_by'] ?></p>
                <p>Remarks: <?= $log['remarks'] ?></p>
            <?php endforeach; ?>
        </div>

    </div>
</body>
</html>
