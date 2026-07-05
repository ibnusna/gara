<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbsensiDetail extends Model
{
    protected $connection = 'mysql_apps';
    protected $table = 'absensi_detail';
    public $timestamps = false;

    protected $fillable = [
        'absensi_id',
        'siswa_id',
        'status',
        'keterangan',
    ];

    public function absensi()
    {
        return $this->belongsTo(Absensi::class, 'absensi_id');
    }
}
