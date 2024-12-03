<?php
namespace App\Models;

use CodeIgniter\Model;

class DBBackupLogModel extends Model
{
    protected $table = 'db_backup_log';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'db_name_id',
        'log_time_dc',
        'backup_time_dc82',
        'log_time_dr',
        'backup_time_dr126',
        'log_time_ndc',
        'backup_time_ndc6'
    ];
}