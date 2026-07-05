<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $connection = 'mysql_auth';
    protected $table = 'audit_logs';
    protected $fillable = ['user_id', 'user_type', 'action', 'module', 'url', 'ip_address'];
    public $timestamps = false;
}
