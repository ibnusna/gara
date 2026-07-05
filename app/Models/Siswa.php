<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    protected $connection = 'mysql_auth';
    protected $table = 'siswa';
    protected $fillable = ['user_id', 'nis', 'nama', 'kelas_id'];
    public $timestamps = false;
}
