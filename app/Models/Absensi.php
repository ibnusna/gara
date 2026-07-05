<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    protected $connection = 'mysql_apps';
    protected $table = 'absensi';
    public $timestamps = false;

    protected $fillable = [
        'mapel_id',
        'kelas_id',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'pertemuan_ke',
        'pokok_bahasan',
        'rangkuman',
        'materi',
        'metode',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'created_at' => 'datetime',
    ];

    public function detail()
    {
        return $this->hasMany(AbsensiDetail::class, 'absensi_id');
    }

    public function agenda()
    {
        return $this->hasOne(AgendaHarian::class, 'absensi_id');
    }
}
