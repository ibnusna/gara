<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SoalAsset extends Model
{
    use HasFactory;

    protected $connection = 'mysql_asesmen';
    protected $table = 'soal_assets';
    
    
    protected $primaryKey = 'id';

    protected $fillable = [
        'bank_soal_id',
        'asset_type',
        'asset_source',
        'original_name',
    ];

    public function bank_soal()
    {
        return $this->belongsTo(BankSoal::class, 'bank_soal_id', 'id_soal');
    }
}
