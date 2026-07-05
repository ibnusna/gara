<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    


    public function run(): void
    {
        
        $roles = [
            ['id' => 1, 'role_name' => 'super_admin'],
            ['id' => 2, 'role_name' => 'operator'],
            ['id' => 3, 'role_name' => 'kepsek'],
            ['id' => 4, 'role_name' => 'guru'],
            ['id' => 5, 'role_name' => 'siswa'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['id' => $role['id']], $role);
        }

        
        $kelas = Kelas::firstOrCreate(['id' => 1], ['nama_kelas' => 'VII']);
        $mapelList = [
            'Pendidikan Agama dan Budi Pekerti',
            'Pendidikan Pancasila',
            'Bahasa Indonesia',
            'Matematika',
            'Bahasa Inggris',
            'Pendidikan Jasmani, Olahraga, dan Kesehatan (PJOK)',
            'Sejarah',
            'Informatika',
            'Seni Musik',
            'Seni Rupa',
            'Seni Tari',
            'Seni Teater',
            'Prakarya dan Kewirausahaan (PKWU)',
            'Ilmu Pengetahuan Alam dan Sosial (IPAS)',
            'Ilmu Pengetahuan Alam (IPA)',
            'Ilmu Pengetahuan Sosial (IPS)',
            'Fisika',
            'Kimia',
            'Biologi',
            'Matematika Tingkat Lanjut',
            'Sosiologi',
            'Ekonomi',
            'Geografi',
            'Antropologi',
            'Bahasa & Sastra Indonesia',
            'Bahasa & Sastra Inggris',
            'Bahasa Asing (Pilihan)',
            'Bahasa Daerah / Muatan Lokal'
        ];

        foreach ($mapelList as $index => $nama_mapel) {
            MataPelajaran::firstOrCreate(['id' => $index + 1], ['nama_mapel' => $nama_mapel]);
        }

        
        $passwordAdmin = Hash::make('admin123');
        $passwordKepsek = Hash::make('admingara776');
        $passwordDefault = Hash::make('password123'); 

        
        $admin = User::firstOrCreate(
            ['username' => 'admin'],
            [
                'nama_lengkap' => 'Super Administrator',
                'password_hash' => $passwordAdmin,
                'role_id' => 1,
                'status_aktif' => 1,
            ]
        );

        
        $kepsek = User::firstOrCreate(
            ['username' => 'kepsek'],
            [
                'nama_lengkap' => 'Kepala Sekolah',
                'password_hash' => $passwordKepsek,
                'role_id' => 3,
                'status_aktif' => 1,
            ]
        );

        
        $operator = User::firstOrCreate(
            ['username' => 'operator'],
            [
                'nama_lengkap' => 'Operator Sekolah',
                'password_hash' => $passwordDefault,
                'role_id' => 2,
                'status_aktif' => 1,
            ]
        );

        
        $guruUser = User::firstOrCreate(
            ['username' => 'guru'],
            [
                'nama_lengkap' => 'Guru Pengampu',
                'password_hash' => $passwordDefault,
                'role_id' => 4,
                'status_aktif' => 1,
            ]
        );

        DB::connection('mysql_auth')->table('guru')->updateOrInsert(
            ['user_id' => $guruUser->id],
            [
                'nip' => '12345678',
                'nama_lengkap' => 'Guru Pengampu',
            ]
        );

        
        $siswaUser = User::firstOrCreate(
            ['username' => 'siswa'],
            [
                'nama_lengkap' => 'Siswa Teladan',
                'password_hash' => $passwordDefault,
                'role_id' => 5,
                'status_aktif' => 1,
            ]
        );

        DB::connection('mysql_auth')->table('siswa')->updateOrInsert(
            ['user_id' => $siswaUser->id],
            [
                'nis' => '1001',
                'nama' => 'Siswa Teladan',
                'kelas_id' => 1,
                'password' => $passwordDefault,
            ]
        );

        $this->command->info('Database precisely seeded with 1 Super Admin, 1 Kepsek, 1 Operator, 1 Guru, and 1 Siswa!');
    }
}
