<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $connection = 'mysql_auth';
    protected $table = 'users';

    protected $fillable = [
        'username',
        'nama_lengkap',
        'password_hash',
        'role_id',
        'status_aktif',
        'profile_photo',
    ];

    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    public function getAuthPasswordName()
    {
        return 'password_hash';
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    


    public function getProfilePhotoUrlAttribute(): string
    {
        if ($this->profile_photo && \Storage::disk('public')->exists('profile_photos/' . $this->profile_photo)) {
            return asset('storage/profile_photos/' . $this->profile_photo) . '?v=' . time();
        }
        $name = $this->nama_lengkap;
        if (empty($name)) {
            $siswa = \Illuminate\Support\Facades\DB::connection('mysql_auth')
                ->table('siswa')
                ->where('user_id', $this->id)
                ->first();
            if ($siswa) {
                $name = $siswa->nama;
            }
        }
        if (empty($name)) {
            $name = $this->username ?? 'User';
        }
        
        $colors = [
            '1abc9c', '2ecc71', '3498db', '9b59b6', '34495e',
            '16a085', '27ae60', '2980b9', '8e44ad', '2c3e50',
            'f1c40f', 'e67e22', 'e74c3c', '95a5a6', 'f39c12',
            'd35400', 'c0392b', '7f8c8d', '5c6bc0', 'ec407a'
        ];
        $hash = 0;
        $len = strlen($name);
        for ($i = 0; $i < $len; $i++) {
            $hash = ($hash + ord($name[$i])) % 100000;
        }
        $index = $hash % count($colors);
        $bg = $colors[$index];
        return "https://ui-avatars.com/api/?name=" . urlencode($name) . "&background=" . $bg . "&color=fff&size=128&bold=true";
    }

    public $timestamps = false;
}
