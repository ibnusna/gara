<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengumuman extends Model
{
    protected $connection = 'mysql_apps';
    protected $table = 'pengumuman';

    
    
    public $timestamps = false;

    protected $fillable = [
        'mapel_id',
        'kelas_id',
        'judul',
        'isi',
    ];
}
