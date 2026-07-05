<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IpBlock extends Model
{
    protected $connection = 'mysql_auth';
    protected $table = 'ip_blocks';
    protected $fillable = ['ip_address', 'reason'];
    public $timestamps = false;
}
