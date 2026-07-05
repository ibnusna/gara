<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalUjian extends Model
{
    use HasFactory;

    protected $connection = 'mysql_asesmen';
    protected $table = 'jadwal_ujian';
    protected $primaryKey = 'id_jadwal';
    public $timestamps = false;

    protected $fillable = [
        'mapel',
        'kelas',
        'tanggal_ujian',
        'hari',
        'jam_mulai',
        'jam_selesai',
        'durasi',
        'jenis_asesmen',
        'pengulangan',
        'tampilkan_jawaban',
        'tampilkan_nilai',
        'mode_submit',
        'token',
        'created_by',
        'created_at',
        'updated_at'
    ];
}
