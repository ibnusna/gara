<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    use HasFactory;

    protected $connection = 'mysql_auth'; 
    protected $table = 'kelas';

    protected $fillable = [
        'nama_kelas',
        'tingkat'
    ];

    public $timestamps = false;

    public static function getSortedClasses()
    {
        return self::all()->sort(function ($a, $b) {
            // Coba parsing tingkat sebagai romawi, jika gagal coba parse sebagai integer
            $tingkatA = self::romanToInt($a->tingkat);
            if ($tingkatA === 0) $tingkatA = (int)$a->tingkat;

            $tingkatB = self::romanToInt($b->tingkat);
            if ($tingkatB === 0) $tingkatB = (int)$b->tingkat;

            if ($tingkatA === $tingkatB) {
                return strnatcasecmp($a->nama_kelas, $b->nama_kelas);
            }

            return $tingkatA <=> $tingkatB;
        })->values();
    }

    public static function romanToInt($roman)
    {
        if (empty($roman)) return 0;
        $romans = [
            'M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400,
            'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40,
            'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1,
        ];
        $result = 0;
        $roman = strtoupper((string)$roman);
        foreach ($romans as $key => $value) {
            while (strpos($roman, $key) === 0) {
                $result += $value;
                $roman = substr($roman, strlen($key));
            }
        }
        return $result;
    }
}
