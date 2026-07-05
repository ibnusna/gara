<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RuangKompetensi extends Model
{
    protected $connection = 'mysql_apps';
    protected $table = 'ruang_kompetensi';

    protected $fillable = [
        'guru_id',
        'judul',
        'link_evaluasi',
        'waktu_menit',
        'status',
    ];

    public function guru()
    {
        return $this->belongsTo(User::class, 'guru_id');
    }
}
