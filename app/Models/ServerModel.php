<?php

namespace App\Models;

use CodeIgniter\Model;

class ServerModel extends Model
{
    protected $table = 'server_names'; // Database table name
    protected $primaryKey = 'id'; // Primary key column
    protected $allowedFields = ['server_name', 'reference_time']; // Columns allowed for mass assignment

    /**
     * Get all servers.
     *
     * @return array
     */
    public function getAllServers()
    {
        return $this->orderBy('server_name', 'ASC')->findAll();
    }
    public function getServerByName($server_name)
    {
        return $this->db->table('server_names')
            ->where('server_name', $server_name)
            ->get()
            ->getRowArray();
    }
}
