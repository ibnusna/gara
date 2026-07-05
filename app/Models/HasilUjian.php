<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HasilUjian extends Model
{
    use HasFactory;

    protected $connection = 'mysql_asesmen';
    protected $table = 'hasil_ujian';
    protected $primaryKey = 'id_hasil';
    public $timestamps = false;

    protected $fillable = [
        'id_siswa',
        'id_jadwal',
        'jawaban_user',
        'skor_akhir',
        'waktu_mulai',
        'waktu_selesai'
    ];
}
