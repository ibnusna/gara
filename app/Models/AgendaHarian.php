<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgendaHarian extends Model
{
    protected $connection = 'mysql_apps';
    protected $table = 'agenda_harian';

    protected $fillable = [
        'mapel_id',
        'kelas_id',
        'tanggal',
        'jam',
        'rencana_kegiatan',
        'catatan_pelaksanaan',
        'siswa_tidak_hadir',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'siswa_tidak_hadir' => 'array',
    ];
}
