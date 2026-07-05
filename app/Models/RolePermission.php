<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RolePermission extends Model
{
    protected $connection = 'mysql_auth';
    protected $table = 'role_permissions';
    protected $fillable = ['role_id', 'permission_id'];
    public $timestamps = false;
}
