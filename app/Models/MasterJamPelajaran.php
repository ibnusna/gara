<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterJamPelajaran extends Model
{
    protected $connection = 'mysql_auth';
    protected $table = 'master_jam_pelajaran';

    protected $fillable = [
        'hari',
        'urutan_jam',
        'jenis_kegiatan',
        'jam_mulai',
        'jam_selesai'
    ];

    public $timestamps = false;
}
