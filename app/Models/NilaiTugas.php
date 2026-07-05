<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NilaiTugas extends Model
{
    protected $connection = 'mysql_apps';
    protected $table = 'nilai_tugas';

    protected $fillable = [
        'tugas_id',
        'siswa_id',
        'nilai',
    ];

    protected $casts = [
        'nilai' => 'decimal:2',
    ];
}
