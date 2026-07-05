<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MataPelajaran extends Model
{
    use HasFactory;

    protected $connection = 'mysql_auth'; 
    protected $table = 'mata_pelajaran';

    protected $fillable = [
        'nama_mapel',
        'kategori'
    ];

    public $timestamps = false;
}
