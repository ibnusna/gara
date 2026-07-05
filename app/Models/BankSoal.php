<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankSoal extends Model
{
    use HasFactory;

    protected $connection = 'mysql_asesmen';
    protected $table = 'bank_soal';
    protected $primaryKey = 'id_soal';
    public $timestamps = false;

    protected $fillable = [
        'id_guru',
        'mapel',
        'kelas',
        'tipe_soal',
        'konten_soal',
        'opsi_a',
        'opsi_b',
        'opsi_c',
        'opsi_d',
        'opsi_e',
        'kunci_jawaban',
        'bobot',
        'status_soal',
        'created_at',
        'updated_at'
    ];

    public function soal_assets()
    {
        return $this->hasMany(SoalAsset::class, 'bank_soal_id', 'id_soal');
    }
}
