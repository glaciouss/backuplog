<?php

namespace App\Controllers;

use App\Models\ServerModel;
use App\Models\BackupModel;
use App\Models\ReplicationModel;
use App\Models\DBBackupLogModel;
use App\Models\DBNamesModel;
use App\Models\StorageNamesModel;
use App\Models\StorageSpaceModel;
use App\Models\AdditionalInfoModel;
use App\Models\UserModel;
use DateTime;
use Dompdf\Dompdf;
use Dompdf\Options;


class LogController extends BaseController
{
    //Display logs dashboard//

    public function index()
    {
        //Initialize models
        $serverModel = new ServerModel();
        $backupModel = new BackupModel();
        $dbModel = new DBNamesModel();
        $replicationModel = new ReplicationModel();
        $databases = $dbModel->findAll();
        $storageNamesModel = new StorageNamesModel();
        $storageNames = $storageNamesModel->findAll();
        $userModel = new UserModel();
        $users = $userModel->findAll();
        // Fetch all servers
        $servers = $serverModel->findAll();
        $currentDate = date('Y-m-d'); // Declare $currentDate here


        // Fetch logs and merge with server names
        $backupLogs = [];
        foreach ($servers as $server) {
            $log = $backupModel->where('server_id', $server['id'])->where('created_date', $currentDate)->first();
            $backupLogs[] = [
                'server_id' => $server['id'], // Include server_id
                'server_name' => $server['server_name'],
                'reference_time' => $server['reference_time'],
                'backup_time' => $log['backup_time'] ?? null,
                'status' => $log ? 'Logged' : 'Pending',

            ];
        }
        // Fetch replication logs and add server info
        $replicationLogs = [];
        foreach ($servers as $server) {
            $log = $replicationModel->where('server_id', $server['id'])->where('created_date', $currentDate)->first();
            $replicationLogs[] = [
                'server_id' => $server['id'], // Include server_id
                'server_name' => $server['server_name'],
                'reference_time' => $server['reference_time'],
                'replication_time' => $log['replication_time'] ?? null,
                'status' => $log ? 'Logged' : 'Pending',
            ];
        }
        // Pass data to the view
        return view('logs', [
            'backupLogs' => $backupLogs,
            'replicationLogs' => $replicationLogs,
            'databases' => $databases,
            'storageNames' => $storageNames,
            'users' => $users
        ]);
    }
    //Add or update a backup log for a specific server
    public function addBackupLog()
    {
        $backupModel = new BackupModel();
        $serverId = $this->request->getPost('server_id');
        $backupTime = $this->request->getPost('backup_time');
        $currentDate = date('Y-m-d');

        // Check if an entry exists for the current day
        $existingLog = $backupModel->where('server_id', $serverId)
            ->where('created_date', $currentDate)
            ->first();

        if ($existingLog) {
            // Update the existing log
            $backupModel->update($existingLog['id'], [
                'backup_time' => $backupTime,
                'log_time' => date('H:i:s'), // Update log time
            ]);
        } else {
            // Insert a new log
            $backupModel->insert([
                'server_id' => $serverId,
                'backup_time' => $backupTime,
                'log_time' => date('H:i:s'), // Current time
                'created_date' => $currentDate,
            ]);
        }

        return redirect()->to('/logs');
    }
    //Add or update a replication log for a specific server
    public function addReplicationLog()
    {
        $replicationModel = new ReplicationModel();
        $serverId = $this->request->getPost('server_id');
        $replicationTime = $this->request->getPost('replication_time');
        $currentDate = date('Y-m-d');

        // Check if an entry exists for the current day
        $existingLog = $replicationModel->where('server_id', $serverId)
            ->where('created_date', $currentDate)
            ->first();

        if ($existingLog) {
            // Update the existing log
            $replicationModel->update($existingLog['id'], [
                'replication_time' => $replicationTime,
                'log_time' => date('H:i:s'), // Update log time
            ]);
        } else {
            // Insert a new log
            $replicationModel->insert([
                'server_id' => $serverId,
                'replication_time' => $replicationTime,
                'log_time' => date('H:i:s'), // Current time
                'created_date' => $currentDate,
            ]);
        }

        return redirect()->to('/logs');// Redirect to logs page
    }
    //Save or update a database backup log
    public function saveDBBackup()
    {
        $dbBackupLogModel = new DBBackupLogModel();  // Model for `db_backup_log` table
        // Retrieve inputs from form
        $db_name_id = $this->request->getPost('db_name_id');
        $backup_time_dc = $this->request->getPost('backup_time_dc');
        $backup_time_dr = $this->request->getPost('backup_time_dr');
        $backup_time_ndc = $this->request->getPost('backup_time_ndc');
        $currentDate = date('Y-m-d'); // Get the current date
        // Validation: Ensure at least one field is filled
        if (empty($backup_time_dc) && empty($backup_time_dr) && empty($backup_time_ndc)) {
            return redirect()->to(base_url('/'))
                ->with('error', 'At least one backup time field must be filled.');
        }
        // Check if a log already exists for this database and day
        $existingLog = $dbBackupLogModel->where('db_name_id', $db_name_id)
            ->where('DATE(log_time_dc)', $currentDate) // Ensure it's the same day
            ->first();

        // Prepare data for partial updates
        $data = [];
        if ($backup_time_dc) {
            $data['log_time_dc'] = date('Y-m-d H:i:s'); // Update log time for DC
            $data['backup_time_dc'] = $backup_time_dc;  // Update backup time for DC
        }
        if ($backup_time_dr) {
            $data['log_time_dr'] = date('Y-m-d H:i:s'); // Update log time for DR
            $data['backup_time_dr'] = $backup_time_dr;  // Update backup time for DR
        }
        if ($backup_time_ndc) {
            $data['log_time_ndc'] = date('Y-m-d H:i:s'); // Update log time for NDC
            $data['backup_time_ndc'] = $backup_time_ndc; // Update backup time for NDC
        }

        if ($existingLog) {
            // Update only the specified fields for the existing log
            $dbBackupLogModel->update($existingLog['id'], $data);

            return redirect()->to(base_url('/'))
                ->with('success', 'Database backup log updated successfully.');
        } else {
            // If no existing log, ensure all fields have default values to create a new row
            $newData = [
                'db_name_id' => $db_name_id,
                'log_time_dc' => isset($data['log_time_dc']) ? $data['log_time_dc'] : null,
                'backup_time_dc' => isset($data['backup_time_dc']) ? $data['backup_time_dc'] : null,
                'log_time_dr' => isset($data['log_time_dr']) ? $data['log_time_dr'] : null,
                'backup_time_dr' => isset($data['backup_time_dr']) ? $data['backup_time_dr'] : null,
                'log_time_ndc' => isset($data['log_time_ndc']) ? $data['log_time_ndc'] : null,
                'backup_time_ndc' => isset($data['backup_time_ndc']) ? $data['backup_time_ndc'] : null,
            ];
            $dbBackupLogModel->insert($newData);

            return redirect()->to(base_url('/'))
                ->with('success', 'Database backup log saved successfully.');
        }
    }
    public function saveStorageSpace()
    {
        $storageSpaceModel = new StorageSpaceModel(); // Model for `storage_space` table

        // Get the submitted values
        $storage_name_id = $this->request->getPost('storage_name_id');
        $free_space = $this->request->getPost('free_space');
        $unit = $this->request->getPost('unit');
        $submission_date = date('Y-m-d'); // Automatically set the submission date

        // Convert the free space to a standard unit (MB) based on the selected unit
        if ($unit == 'GB') {
            $free_space = $free_space * 1024; // Convert GB to MB
        } elseif ($unit == 'TB') {
            $free_space = $free_space * 1024 * 1024; // Convert TB to MB
        }

        // Check if there's already an entry for the storage name on the current day
        $existingEntry = $storageSpaceModel->where('storage_name_id', $storage_name_id)
            ->where('DATE(submission_date)', $submission_date)
            ->first();

        if ($existingEntry) {
            // If an existing entry is found, update it with the new free space
            $data = [
                'free_space' => $free_space,
                'submission_date' => $submission_date
            ];
            $storageSpaceModel->update($existingEntry['id'], $data);

            return redirect()->to(base_url('/'))->with('success', 'Storage space data updated successfully.');
        } else {
            // If no existing entry is found, create a new entry
            $data = [
                'storage_name_id' => $storage_name_id,
                'free_space' => $free_space,
                'submission_date' => $submission_date
            ];
            $storageSpaceModel->save($data);

            return redirect()->to(base_url('/'))->with('success', 'Storage space data saved successfully.');
        }
    }
    public function saveAdditionalInfo()
    {
        $additionalInfoModel = new AdditionalInfoModel(); // Model for `additional_info` table

        // Retrieve form data
        $user_id = $this->request->getPost('user_id');
        $remarks = $this->request->getPost('remarks');
        $current_date = date('Y-m-d'); // Current date

        // Validate input
        if (empty($user_id)) {
            return redirect()->back()->with('error', 'User required.');
        }

        // Check if an entry already exists for the user on the same date
        $existingEntry = $additionalInfoModel
            ->where('user_id', $user_id)
            ->where('creation_date', $current_date)
            ->first();

        if ($existingEntry) {
            // Update the existing entry
            $additionalInfoModel->update($existingEntry['id'], [
                'remarks' => $remarks,
            ]);
        } else {
            // Insert a new entry
            $additionalInfoModel->insert([
                'user_id' => $user_id,
                'remarks' => $remarks,
                'creation_date' => $current_date,
            ]);
        }

        return redirect()->to(base_url('/'))->with('success', 'Additional information saved successfully.');
    }
    public function viewLogs($returnData = false)
    {
        $logDate = $this->request->getGet('log_date');
        $selectedDate = date('Y-m-d', strtotime($logDate));

        $backupLogModel = new BackupModel();
        $replicationLogModel = new ReplicationModel();
        $dbBackupLogModel = new DBBackupLogModel();
        $storageSpaceModel = new StorageSpaceModel();
        $additionalInfoModel = new AdditionalInfoModel();
        $serverModel = new ServerModel();

        // Fetch logs for the selected date
        $backupLogs = $backupLogModel
            ->select('server_names.server_name AS name, backup_db.log_time, backup_db.backup_time, "backup" AS log_type')
            ->join('server_names', 'backup_db.server_id = server_names.id')
            ->where('created_date', $selectedDate)
            ->findAll();

        $replicationLogs = $replicationLogModel
            ->select('server_names.server_name AS name, replication_db.log_time, replication_db.replication_time, "replication" AS log_type')
            ->join('server_names', 'replication_db.server_id = server_names.id')
            ->where('created_date', $selectedDate)
            ->findAll();

        $dbBackupLogs = $dbBackupLogModel
            ->select('db_names.db_name AS name, db_backup_log.log_time_dc, db_backup_log.backup_time_dc, db_backup_log.log_time_dr, db_backup_log.backup_time_dr, db_backup_log.log_time_ndc, db_backup_log.backup_time_ndc')
            ->join('db_names', 'db_backup_log.db_name_id = db_names.id')
            ->groupStart()
            ->where('DATE(log_time_dc)', $selectedDate)
            ->orWhere('DATE(log_time_dr)', $selectedDate)
            ->orWhere('DATE(log_time_ndc)', $selectedDate)
            ->groupEnd()
            ->findAll();

        $storageSpaceLogs = $storageSpaceModel
            ->select('storage_names.storage_type AS name, storage_space.free_space')
            ->join('storage_names', 'storage_space.storage_name_id = storage_names.id')
            ->where('DATE(submission_date)', $selectedDate)
            ->findAll();

        $additionalInfoLogs = $additionalInfoModel
            ->select('users.name AS name, additional_info.remarks')
            ->join('users', 'additional_info.id = users.id')
            ->where('DATE(creation_date)', $selectedDate)
            ->findAll();

        // Normalize logs to a common structure
        $normalizedBackupLogs = array_map(function ($log) use ($serverModel) {
            // Retrieve the reference time for this server
            $server = $serverModel->getServerByName($log['name']);  // Fetch server info from the server_names table
            $reference_time_str = $server['reference_time_min'] ?? '0';  // Default if no reference_time exists

            // Convert log time and last successful time to DateTime objects
            $log_time = new DateTime($log['log_time']);
            $last_successful_time = new DateTime($log['backup_time']);

            // Calculate the time difference in minutes
            $time_diff = $log_time->diff($last_successful_time);
            $diff_minutes = $time_diff->h * 60 + $time_diff->i;  // Total difference in minutes

            // Determine if the log is delayed based on the reference time
            $is_delayed = $diff_minutes > $reference_time_str;
            return [
                'name' => $log['name'],
                'log_time' => $log['log_time'],
                'last_successful_time' => $log['backup_time'],
                'log_type' => $log['log_type'],
                'is_delayed' => $is_delayed,
            ];
        }, $backupLogs);

        $normalizedReplicationLogs = array_map(function ($log) use ($serverModel) {
            $server = $serverModel->getServerByName($log['name']);
            $reference_time_str = $server['reference_time_min'] ?? '0 min';

            $log_time = new DateTime($log['log_time']);
            $last_successful_time = new DateTime($log['replication_time']);
            $time_diff = $log_time->diff($last_successful_time);
            $diff_minutes = $time_diff->h * 60 + $time_diff->i;

            $is_delayed = $diff_minutes > $reference_time_str;
            return [
                'name' => $log['name'],
                'log_time' => $log['log_time'],
                'last_successful_time' => $log['replication_time'],
                'log_type' => $log['log_type'],
                'is_delayed' => $is_delayed,
            ];
        }, $replicationLogs);

        $normalizedDbBackupLogs = array_map(function ($log) {
            return [
                'name' => $log['name'],
                'log_time_dc' => $log['log_time_dc'] ? date('H:i:s', strtotime($log['log_time_dc'])) : null,
                'backup_time_dc' => $log['backup_time_dc'],
                'log_time_dr' => $log['log_time_dr'] ? date('H:i:s', strtotime($log['log_time_dr'])) : null,
                'backup_time_dr' => $log['backup_time_dr'],
                'log_time_ndc' => $log['log_time_ndc'] ? date('H:i:s', strtotime($log['log_time_ndc'])) : null,
                'backup_time_ndc' => $log['backup_time_ndc'],
            ];
        }, $dbBackupLogs);

        $normalizedStorageSpaceLogs = array_map(function ($log) {
            return [
                'name' => $log['name'],
                'free_space' => $log['free_space'],
            ];
        }, $storageSpaceLogs);

        $normalizedAdditionalInfoLogs = array_map(function ($log) {
            return [
                'submitted_by' => $log['name'],
                'remarks' => $log['remarks'],
            ];
        }, $additionalInfoLogs);

        // Combine all normalized logs into one array
        $logs = [
            'backupLogs' => $normalizedBackupLogs,
            'replicationLogs' => $normalizedReplicationLogs,
            'dbBackupLogs' => $normalizedDbBackupLogs,
            'storageSpaceLogs' => $normalizedStorageSpaceLogs,
            'additionalInfoLogs' => $normalizedAdditionalInfoLogs,
        ];

        if ($returnData) {
            return [
                'logs' => $logs,
                'selectedDate' => $selectedDate,
            ];
        }
        return view('view_logs', [
            'logs' => $logs,
            'selectedDate' => $selectedDate,
        ]);
    }

    public function downloadLogsPDF()
    {
        $logDate = $this->request->getGet('log_date');
        if (!$logDate) {
            return redirect()->back()->with('error', 'Date is required to download logs!');
        }
    
        // Reuse viewLogs to fetch data
        $viewLogsData = $this->viewLogs(true);
        $logs = $viewLogsData['logs'];
        $selectedDate = $viewLogsData['selectedDate'];
    
        // Combine logs into HTML for PDF
        $html = view('pdf_template', [
            'logs' => $logs,
            'selectedDate' => $selectedDate,
        ]);
    
        // Generate PDF
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
    
        // Download the PDF
        $dompdf->stream("Logs_{$selectedDate}.pdf", ['Attachment' => true]);
        exit();
    }    
}
