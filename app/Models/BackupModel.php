<?php

namespace App\Models;

use CodeIgniter\Model;

class BackupModel extends Model
{
    protected $table = 'backup_db'; // Database table name
    protected $primaryKey = 'id'; // Primary key column
    protected $allowedFields = ['server_id', 'backup_time', 'log_time', 'created_date']; // Columns allowed for mass assignment

    /**
     * Add a backup log.
     *
     * @param array $data
     * @return bool
     */
    public function addBackupLog(array $data)
    {
        return $this->insert($data);
    }

    /**
     * Get the latest backup log for a server.
     *
     * @param int $serverId
     * @return array|null
     */
    public function getLatestLogByServerId(int $serverId)
    {
        return $this->where('server_id', $serverId)
                    ->orderBy('log_time', 'DESC')
                    ->first();
    }
}
