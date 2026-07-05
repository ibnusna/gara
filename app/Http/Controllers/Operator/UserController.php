<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Guru;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index()
    {
        
        $gurus = Guru::select('guru.*', 'users.username', 'users.status_aktif')
            ->join('users', 'guru.user_id', '=', 'users.id')
            ->orderBy('guru.nama_lengkap', 'asc')
            ->get();

        
        $siswas = Siswa::select('siswa.*', 'users.username', 'users.status_aktif', 'kelas.nama_kelas')
            ->join('users', 'siswa.user_id', '=', 'users.id')
            ->leftJoin('kelas', 'siswa.kelas_id', '=', 'kelas.id')
            ->orderBy('kelas.nama_kelas', 'asc')
            ->orderBy('siswa.nama', 'asc')
            ->get();

        
        $kelas_list = DB::connection('mysql_auth')->table('kelas')->orderBy('nama_kelas', 'asc')->get();

        
        $latestId = DB::connection('mysql_auth')->table('users')->max('id');
        $nextId = $latestId ? $latestId + 1 : 1;
        $autoGuruUsername = 'G' . date('y') . str_pad($nextId, 4, '0', STR_PAD_LEFT);
        $autoSiswaUsername = 'S' . date('y') . str_pad($nextId, 4, '0', STR_PAD_LEFT);

        return view('operator.users.index', compact('gurus', 'siswas', 'kelas_list', 'autoGuruUsername', 'autoSiswaUsername'));
    }

    public function storeGuru(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:50|unique:mysql_auth.users,username',
            'nama_lengkap' => 'required|string|max:100',
        ]);

        try {
            DB::connection('mysql_auth')->beginTransaction();

            $role = Role::where('role_name', 'guru')->firstOrFail();

            $user = User::create([
                'username' => $request->username,
                'nama_lengkap' => $request->nama_lengkap, 
                'password_hash' => Hash::make('guru123'),
                'role_id' => $role->id,
                'status_aktif' => 1,
            ]);

            Guru::create([
                'user_id' => $user->id,
                'nip' => $request->username, 
                'nama_lengkap' => $request->nama_lengkap,
            ]);

            DB::connection('mysql_auth')->commit();

            return redirect()->back()->with('success_message', 'Guru berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::connection('mysql_auth')->rollBack();
            return redirect()->back()->with('error_message', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function updateGuru(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:mysql_auth.users,id',
            'username' => 'required|string|max:50|unique:mysql_auth.users,username,' . $request->user_id,
            'nama_lengkap' => 'required|string|max:100',
        ]);

        try {
            DB::connection('mysql_auth')->beginTransaction();

            $user = User::findOrFail($request->user_id);
            $user->update(['username' => $request->username, 'nama_lengkap' => $request->nama_lengkap]);

            $guru = Guru::where('user_id', $request->user_id)->firstOrFail();
            $guru->update([
                'nip' => $request->username,
                'nama_lengkap' => $request->nama_lengkap,
            ]);

            DB::connection('mysql_auth')->commit();

            return redirect()->back()->with('success_message', 'Data Guru berhasil diupdate.');
        } catch (\Exception $e) {
            DB::connection('mysql_auth')->rollBack();
            return redirect()->back()->with('error_message', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function storeSiswa(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:50|unique:mysql_auth.users,username',
            'nama' => 'required|string|max:100',
            'kelas_id' => 'required|exists:mysql_auth.kelas,id',
        ]);

        try {
            DB::connection('mysql_auth')->beginTransaction();

            $role = Role::where('role_name', 'siswa')->firstOrFail();

            $user = User::create([
                'username' => $request->username,
                'nama_lengkap' => $request->nama,
                'password_hash' => Hash::make('siswa123'),
                'role_id' => $role->id,
                'status_aktif' => 1,
            ]);

            Siswa::create([
                'user_id' => $user->id,
                'nis' => $request->username,
                'nama' => $request->nama,
                'kelas_id' => $request->kelas_id,
            ]);

            DB::connection('mysql_auth')->commit();

            return redirect()->back()->with('success_message', 'Siswa berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::connection('mysql_auth')->rollBack();
            return redirect()->back()->with('error_message', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function updateSiswa(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:mysql_auth.users,id',
            'username' => 'required|string|max:50|unique:mysql_auth.users,username,' . $request->user_id,
            'nama' => 'required|string|max:100',
            'kelas_id' => 'required|exists:mysql_auth.kelas,id',
        ]);

        try {
            DB::connection('mysql_auth')->beginTransaction();

            $user = User::findOrFail($request->user_id);
            $user->update(['username' => $request->username, 'nama_lengkap' => $request->nama]);

            $siswa = Siswa::where('user_id', $request->user_id)->firstOrFail();
            $siswa->update([
                'nis' => $request->username,
                'nama' => $request->nama,
                'kelas_id' => $request->kelas_id,
            ]);

            DB::connection('mysql_auth')->commit();

            return redirect()->back()->with('success_message', 'Data Siswa berhasil diupdate.');
        } catch (\Exception $e) {
            DB::connection('mysql_auth')->rollBack();
            return redirect()->back()->with('error_message', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function resetPassword($id)
    {
        try {
            $user = User::findOrFail($id);
            $roleName = $user->role->role_name;

            if ($roleName === 'guru') {
                $user->password_hash = Hash::make('guru123');
            } elseif ($roleName === 'siswa') {
                $user->password_hash = Hash::make('siswa123');
            } else {
                return redirect()->back()->with('error_message', 'Role tidak valid untuk reset password.');
            }

            $user->save();

            return redirect()->back()->with('success_message', 'Password berhasil direset (Password Default: ' . $roleName . '123)');
        } catch (\Exception $e) {
            return redirect()->back()->with('error_message', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            DB::connection('mysql_auth')->beginTransaction();

            $user = User::findOrFail($id);

            
            if ($user->role->role_name === 'guru') {
                $guru = Guru::where('user_id', $user->id)->first();
                if ($guru) {
                    DB::connection('mysql_apps')->table('teaching_assignments')->where('teacher_id', $guru->id)->delete();
                    DB::connection('mysql_apps')->table('teacher_subjects')->where('teacher_id', $guru->id)->delete();
                    $guru->delete();
                }
            } elseif ($user->role->role_name === 'siswa') {
                Siswa::where('user_id', $user->id)->delete();
            }

            $user->delete();

            DB::connection('mysql_auth')->commit();

            return redirect()->back()->with('success_message', 'User berhasil dihapus.');
        } catch (\Exception $e) {
            DB::connection('mysql_auth')->rollBack();
            return redirect()->back()->with('error_message', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file'
        ]);

        $file = $request->file('csv_file');
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, ['csv', 'txt'])) {
            return redirect()->back()->with('error_message', 'Tipe file harus berupa .csv atau .txt');
        }

        $filePath = $file->getRealPath();
        $content = file_get_contents($filePath);
        if ($content === false) {
            return redirect()->back()->with('error_message', 'Gagal membaca file.');
        }

        
        $bom = pack('H*', 'EFBBBF');
        if (substr($content, 0, 3) === $bom) {
            $content = substr($content, 3);
        }

        
        $content = preg_replace('/[\x00-\x09\x0B-\x0C\x0E-\x1F\x7F-\x9F]/u', '', $content);

        
        $lines = preg_split('/\r\n|\r|\n/', trim($content));
        if (empty($lines) || empty(trim($lines[0]))) {
            return redirect()->back()->with('error_message', 'File CSV kosong.');
        }

        
        $firstLine = $lines[0];
        $delimiters = [',' => 0, ';' => 0, "\t" => 0];
        foreach ($delimiters as $delim => &$count) {
            $count = substr_count($firstLine, $delim);
        }
        arsort($delimiters);
        $delimiter = key($delimiters);
        if ($delimiters[$delimiter] === 0) {
            $delimiter = ',';
        }

        \Log::info('CSV Import - firstLine: ' . bin2hex($firstLine) . ' | text: ' . trim($firstLine));
        \Log::info('CSV Import - Delimiter: ' . $delimiter);

        
        $headers = str_getcsv($firstLine, $delimiter);
        if (count($headers) === 1 && strpos($headers[0], $delimiter) !== false) {
            $headers = str_getcsv($headers[0], $delimiter);
        }
        $headers = array_map(function ($h) {
            return strtolower(trim(str_replace(['"', "'"], '', $h)));
        }, $headers);

        \Log::info('CSV Import - Headers: ' . json_encode($headers));

        
        $findHeader = function ($patterns, $headers) {
            
            foreach ($headers as $index => $header) {
                $cleanHeader = preg_replace('/[^a-z0-9]/', '', strtolower($header));
                foreach ($patterns as $pattern) {
                    $cleanPattern = preg_replace('/[^a-z0-9]/', '', strtolower($pattern));
                    if ($cleanHeader === $cleanPattern) {
                        return $index;
                    }
                }
            }
            
            foreach ($headers as $index => $header) {
                $cleanHeader = preg_replace('/[^a-z0-9]/', '', strtolower($header));
                foreach ($patterns as $pattern) {
                    $cleanPattern = preg_replace('/[^a-z0-9]/', '', strtolower($pattern));
                    if ($cleanPattern === 'name' || $cleanPattern === 'nama') {
                        if ($cleanHeader === 'username') continue;
                    }
                    if (strpos($cleanHeader, $cleanPattern) !== false) {
                        return $index;
                    }
                }
            }
            return false;
        };

        
        $kelasIndex = $findHeader(['kelas'], $headers);
        $isSiswa = ($kelasIndex !== false);

        \Log::info('CSV Import - isSiswa: ' . ($isSiswa ? 'true' : 'false') . ' | kelasIndex: ' . ($kelasIndex !== false ? $kelasIndex : 'false'));

        $successCount = 0;
        $skipCount = 0;

        
        array_shift($lines);

        if ($isSiswa) {
            
            $usernameIndex = $findHeader(['username', 'nis', 'nip'], $headers);
            $namaIndex = $findHeader(['nama', 'name', 'nama_lengkap'], $headers);

            \Log::info('CSV Import - Siswa matched indexes: usernameIndex=' . $usernameIndex . ', namaIndex=' . $namaIndex . ', kelasIndex=' . $kelasIndex);

            if ($usernameIndex === false || $namaIndex === false || $kelasIndex === false) {
                return redirect()->back()->with('error_message', 'Format header CSV Siswa tidak valid. Harus ada kolom: username (atau nis), nama (atau nama_lengkap), dan kelas.');
            }

            
            $role = Role::where('role_name', 'siswa')->first();
            if (!$role) {
                return redirect()->back()->with('error_message', 'Role siswa tidak ditemukan.');
            }

            
            $kelasMap = DB::connection('mysql_auth')->table('kelas')->pluck('id', 'nama_kelas')->toArray();

            foreach ($lines as $line) {
                if (empty(trim($line)))
                    continue;

                $row = str_getcsv($line, $delimiter);
                if (count($row) === 1 && strpos($row[0], $delimiter) !== false) {
                    $row = str_getcsv($row[0], $delimiter);
                }
                \Log::info('CSV Import - Siswa data row: ' . json_encode($row));
                if (count($row) < count($headers)) {
                    \Log::info('CSV Import - Row skipped because count(' . count($row) . ') < count(' . count($headers) . ')');
                    continue;
                }

                $username = trim($row[$usernameIndex]);
                $nama = trim($row[$namaIndex]);
                $kelasNama = trim($row[$kelasIndex]);

                if (empty($username) || empty($nama)) {
                    continue;
                }

                
                $exists = User::where('username', $username)->exists();
                if ($exists) {
                    $skipCount++;
                    continue;
                }

                
                $kelasId = $kelasMap[$kelasNama] ?? null;
                if (!$kelasId) {
                    
                    foreach ($kelasMap as $kName => $kId) {
                        if (strcasecmp($kName, $kelasNama) === 0) {
                            $kelasId = $kId;
                            break;
                        }
                    }
                }

                try {
                    DB::connection('mysql_auth')->beginTransaction();

                    $user = User::create([
                        'username' => $username,
                        'nama_lengkap' => $nama,
                        'password_hash' => Hash::make('siswa123'),
                        'role_id' => $role->id,
                        'status_aktif' => 1,
                    ]);

                    Siswa::create([
                        'user_id' => $user->id,
                        'nis' => $username,
                        'nama' => $nama,
                        'kelas_id' => $kelasId,
                    ]);

                    DB::connection('mysql_auth')->commit();
                    $successCount++;
                } catch (\Exception $e) {
                    DB::connection('mysql_auth')->rollBack();
                }
            }
        } else {
            
            $usernameIndex = $findHeader(['username', 'nip', 'nis'], $headers);
            $namaIndex = $findHeader(['nama', 'name', 'nama_lengkap'], $headers);

            if ($usernameIndex === false || $namaIndex === false) {
                return redirect()->back()->with('error_message', 'Format header CSV Guru tidak valid. Harus ada kolom: username (atau nip) dan nama_lengkap (atau nama).');
            }

            
            $role = Role::where('role_name', 'guru')->first();
            if (!$role) {
                return redirect()->back()->with('error_message', 'Role guru tidak ditemukan.');
            }

            foreach ($lines as $line) {
                if (empty(trim($line)))
                    continue;

                $row = str_getcsv($line, $delimiter);
                if (count($row) === 1 && strpos($row[0], $delimiter) !== false) {
                    $row = str_getcsv($row[0], $delimiter);
                }
                \Log::info('CSV Import - Guru data row: ' . json_encode($row));
                if (count($row) < count($headers)) {
                    continue;
                }

                $username = trim($row[$usernameIndex]);
                $namaLengkap = trim($row[$namaIndex]);

                if (empty($username) || empty($namaLengkap)) {
                    continue;
                }

                
                $exists = User::where('username', $username)->exists();
                if ($exists) {
                    $skipCount++;
                    continue;
                }

                try {
                    DB::connection('mysql_auth')->beginTransaction();

                    $user = User::create([
                        'username' => $username,
                        'nama_lengkap' => $namaLengkap,
                        'password_hash' => Hash::make('guru123'),
                        'role_id' => $role->id,
                        'status_aktif' => 1,
                    ]);

                    Guru::create([
                        'user_id' => $user->id,
                        'nip' => $username,
                        'nama_lengkap' => $namaLengkap,
                    ]);

                    DB::connection('mysql_auth')->commit();
                    $successCount++;
                } catch (\Exception $e) {
                    DB::connection('mysql_auth')->rollBack();
                }
            }
        }

        $typeLabel = $isSiswa ? 'Siswa' : 'Guru';
        return redirect()->back()->with('success_message', "Berhasil mengimpor {$successCount} data {$typeLabel}. ({$skipCount} dilewati karena username sudah terdaftar).");
    }
}
