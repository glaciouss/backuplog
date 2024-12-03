<?php

namespace App\Models;

use CodeIgniter\Model;

class ReplicationModel extends Model
{
    protected $table = 'replication_db'; // Database table name
    protected $primaryKey = 'id'; // Primary key column
    protected $allowedFields = ['server_id', 'replication_time', 'log_time', 'created_date']; // Columns allowed for mass assignment

    /**
     * Add a replication log.
     *
     * @param array $data
     * @return bool
     */
    public function addReplicationLog(array $data)
    {
        return $this->insert($data);
    }

    /**
     * Get the latest replication log for a server.
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
