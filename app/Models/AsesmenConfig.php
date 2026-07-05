<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsesmenConfig extends Model
{
    use HasFactory;

    protected $connection = 'mysql_asesmen';
    protected $table = 'asesmen_config';
    protected $primaryKey = 'id_config';
    public $timestamps = false; 

    protected $fillable = [
        'jenis_asesmen',
        'status_pintu',
        'status_pintu_siswa',
        'opened_by',
        'updated_at'
    ];
}
