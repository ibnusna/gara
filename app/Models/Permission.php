<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $connection = 'mysql_auth';
    protected $table = 'permissions';
    protected $fillable = ['name', 'description'];
    public $timestamps = false;

    




    public function getPermissionNameAttribute(): string
    {
        return $this->name ?? '';
    }
}
