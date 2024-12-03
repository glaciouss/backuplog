<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Logs</title>
    <link rel="stylesheet" href="/styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
            color: #333;
        }

        .content {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h2,
        h3 {
            margin-top: 0;
            color: #444;
        }

        form {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        form label {
            margin-right: 10px;
        }

        form input[type="date"] {
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-right: 10px;
        }

        form button {
            padding: 5px 10px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        form button:hover {
            background-color: #0056b3;
        }

        .button-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .button-container button {
            padding: 5px 26px;
            background-color: #28a745;
            color: white;
            font-size: medium;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .button-container button:hover {
            background-color: #218838;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table caption {
            margin-bottom: 10px;
            font-weight: bold;
            font-size: 1.2em;
            color: #555;
        }

        table th,
        table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        table th {
            background-color: #f4f4f9;
            color: #555;
        }

        table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        table tbody tr:hover {
            background-color: #f1f1f1;
        }

        .additional-info {
            padding: 15px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .additional-info p {
            margin: 5px 0;
        }

        form button[type="button"] {
            padding: 5px 10px;
            background-color: #ff5722;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-left: 10px;
        }

        form button[type="button"]:hover {
            background-color: #e64a19;
        }
    </style>
</head>
<script>
    function downloadPDF() {
        const logDate = document.querySelector('input[name="log_date"]').value;
        if (!logDate) {
            alert('Please select a date first!');
            return;
        }
        window.location.href = `<?= base_url('/downloadLogsPDF') ?>?log_date=${logDate}`;
    }
</script>

<body>

    <div class="content">
        <div class="button-container">
            <h2>View Logs</h2>
            <button onclick="window.location.href='/'">Home</button>
        </div>
        <form action="<?= base_url('/viewLogs') ?>" method="GET">
            <label for="date">Select Date:</label>
            <input type="date" name="log_date" required>
            <button type="submit">View</button>
            <button type="button" onclick="downloadPDF()">PDF</button>
        </form>


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
                                <span style="color: red;">&#x25CF; Delayed</span> <!-- Red indicator -->
                            <?php else: ?>
                                <span style="color: green;">&#x25CF; On Time</span>
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