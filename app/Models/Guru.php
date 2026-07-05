<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Guru extends Model
{
    protected $connection = 'mysql_auth';
    protected $table = 'guru';
    protected $fillable = ['user_id', 'nip', 'nama_lengkap'];
    public $timestamps = false;
}
