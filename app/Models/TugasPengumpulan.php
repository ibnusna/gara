<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TugasPengumpulan extends Model
{
    protected $connection = 'mysql_apps';
    protected $table = 'tugas_pengumpulan';
    public $timestamps = false;
}
