<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backup & Replication Logs</title>
    <link rel="stylesheet" href="/styles.css">
</head>
<script>
    // Save the scroll position in sessionStorage
    function saveScrollPosition() {
        sessionStorage.setItem('scrollPosition', window.scrollY);
    }

    // Restore the scroll position on page load
    document.addEventListener('DOMContentLoaded', () => {
        const scrollPosition = sessionStorage.getItem('scrollPosition');
        if (scrollPosition) {
            window.scrollTo(0, scrollPosition);
        }
    });
</script>

<body>
    <div class="container">
        <h1>Backup and Replication Logs</h1>

        <!-- Backup Log Table -->
        <h2>Backup Log</h2>
        <table class="excel-table">
            <thead>
                <tr>
                    <th>Server Name</th>
                    <th>Reference Time</th>
                    <th>Backup Time</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($backupLogs as $log): ?>
                    <tr>
                        <td><?= $log['server_name'] ?></td>
                        <td><?= $log['reference_time'] ?></td>
                        <td>
                            <form method="POST" action="/logs/addBackupLog" onsubmit="saveScrollPosition()">

                                <input type="hidden" name="server_id" value="<?= $log['server_id'] ?>">
                                <input type="time" name="backup_time" value="<?= $log['backup_time'] ?>" required>
                                <button type="submit">Submit</button>
                            </form>
                        </td>
                        <td class="<?= $log['status'] === 'Logged' ? 'status-logged' : 'status-pending' ?>">
                            <?= $log['status'] ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Replication Log Table -->
        <h2>Replication Log</h2>
        <table class="excel-table">
            <thead>
                <tr>
                    <th>Server Name</th>
                    <th>Reference Time</th>
                    <th>Replication Time</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($replicationLogs as $log): ?>
                    <tr>
                        <td><?= $log['server_name'] ?></td>
                        <td><?= $log['reference_time'] ?></td>
                        <td>
                            <form method="POST" action="/logs/addReplicationLog" onsubmit="saveScrollPosition()">
                                <input type="hidden" name="server_id" value="<?= $log['server_id'] ?>">
                                <input type="time" name="replication_time" value="<?= $log['replication_time'] ?>" required>
                                <button type="submit">Submit</button>
                            </form>
                        </td>
                        <td class="<?= $log['status'] === 'Logged' ? 'status-logged' : 'status-pending' ?>">
                            <?= $log['status'] ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <h2>Database Backup Log</h2>
        <!-- Alert Section for Error/Success Messages -->
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger">
                <?= session()->getFlashdata('error') ?>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success">
                <?= session()->getFlashdata('success') ?>
            </div>
        <?php endif; ?>
        <table class="excel-table">
            <thead>
                <tr>
                    <th>Database Name</th>
                    <th>DC:82</th>
                    <th>DR:126</th>
                    <th>NDC:6</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($databases as $db): ?>
                    <tr>
                        <form action="<?= base_url(relativePath: '/saveDbBackupLog') ?>" method="POST" onsubmit="saveScrollPosition()">
                            <input type="hidden" name="db_name_id" value="<?= $db['id'] ?>">

                            <td><?= esc($db['db_name']) ?></td>
                            <td><input type="time" name="backup_time_dc"></td>
                            <td><input type="time" name="backup_time_dr"></td>
                            <td><input type="time" name="backup_time_ndc"></td>
                            <td><button type="submit" class="action-btn">Submit</button></td>
                        </form>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <h2>Storage Free Space</h2>

        <!-- Table for Storage Free Space -->
        <table class="excel-table">
            <thead>
                <tr>
                    <th>Storage Name</th>
                    <th>Free Space</th>
                    <th>Unit</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($storageNames as $storage): ?>
                    <tr>
                        <form action="<?= base_url('/saveStorageSpace') ?>" method="POST" onsubmit="saveScrollPosition()">
                            <input type="hidden" name="storage_name_id" value="<?= $storage['id'] ?>">

                            <td><?= esc($storage['storage_type']) ?></td>
                            <td><input type="number" name="free_space"></td>
                            <td>
                                <select name="unit">
                                    <option value="MB">MB</option>
                                    <option value="GB">GB</option>
                                    <option value="TB">TB</option>
                                </select>
                            </td>
                            <td><button type="submit" class="action-btn">Submit</button></td>
                        </form>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h2>Additional Information</h2>

        <form action="<?= base_url('/saveAdditionalInfo') ?>" method="POST" onsubmit="saveScrollPosition()">
            <table class="excel-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Remarks</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <select name="user_id" required>
                                <option value="" disabled selected>Select User</option>
                                <?php foreach ($users as $user): ?>
                                    <option value="<?= $user['id'] ?>"><?= esc($user['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <textarea name="remarks" rows="3"></textarea>
                        </td>
                        <td>
                            <button type="submit" class="action-btn">Submit</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </form>
        <h1>Logs Management</h1>

        <!-- Add the button to navigate to the "View Logs" page -->
        <div>
            <a href="/viewLogs" class="view-logs-button">View Logs</a>
        </div>
        
    </div>
</body>

</html>