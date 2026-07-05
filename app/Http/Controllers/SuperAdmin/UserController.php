<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('role')
            ->whereHas('role', function ($query) {
                $query->whereIn('role_name', ['super_admin', 'operator']);
            })->orderBy('id')->get();

        $roles = Role::whereIn('role_name', ['super_admin', 'operator'])->get();

        return view('super_admin.users.index', compact('users', 'roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_lengkap' => 'required|string',
            'username' => 'required|string|unique:mysql_auth.users,username',
            'role_id' => 'required|exists:mysql_auth.roles,id',
        ]);

        $role = Role::findOrFail($request->role_id);

        if (!in_array($role->role_name, ['super_admin', 'operator'])) {
            return redirect()->back()->with('error_message', 'Role tidak valid.');
        }

        $password = $role->role_name === 'super_admin' ? 'admingara776' : 'garaop457';

        User::create([
            'username' => $request->username,
            'nama_lengkap' => $request->nama_lengkap,
            'password_hash' => Hash::make($password),
            'role_id' => $role->id,
            'status_aktif' => 1,
        ]);

        return redirect()->back()->with('success_message', "Akun '{$request->nama_lengkap}' berhasil ditambahkan.");
    }

    public function destroy($id)
    {
        if ($id == 1) {
            return redirect()->back()->with('error_message', 'Tidak dapat menghapus Super Admin utama.');
        }

        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->back()->with('success_message', "Akun '{$user->username}' berhasil dihapus.");
    }

    public function updatePassword(Request $request, $id)
    {
        $request->validate([
            'new_password' => 'required|min:6'
        ]);

        $myId = session('user_id') ?? 1;

        if ($id == 1 && $myId != 1) {
            return redirect()->back()->with('error_message', 'Hanya super admin utama yang bisa mereset passwordnya sendiri.');
        }

        $user = User::findOrFail($id);
        $user->update([
            'password_hash' => Hash::make($request->new_password)
        ]);

        return redirect()->back()->with('success_message', 'Password berhasil di-reset.');
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

        
        $headers = str_getcsv($firstLine, $delimiter);
        if (count($headers) === 1 && strpos($headers[0], $delimiter) !== false) {
            $headers = str_getcsv($headers[0], $delimiter);
        }
        $headers = array_map(function ($h) {
            return strtolower(trim(str_replace(['"', "'"], '', $h)));
        }, $headers);

        
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

        
        $usernameIndex = $findHeader(['username', 'user'], $headers);
        $namaIndex = $findHeader(['nama', 'name', 'nama_lengkap'], $headers);
        $roleIndex = $findHeader(['role', 'peran'], $headers);

        if ($usernameIndex === false || $namaIndex === false || $roleIndex === false) {
            return redirect()->back()->with('error_message', 'Format header CSV tidak valid. Pastikan ada kolom: username, nama_lengkap, dan role.');
        }

        
        $roles = Role::whereIn('role_name', ['super_admin', 'operator'])->get()->keyBy('role_name');

        $successCount = 0;
        $skipCount = 0;

        
        array_shift($lines);

        foreach ($lines as $line) {
            if (empty(trim($line)))
                continue;

            $row = str_getcsv($line, $delimiter);
            if (count($row) === 1 && strpos($row[0], $delimiter) !== false) {
                $row = str_getcsv($row[0], $delimiter);
            }
            if (count($row) < count($headers))
                continue;

            $username = trim($row[$usernameIndex]);
            $namaLengkap = trim($row[$namaIndex]);
            $roleName = strtolower(trim($row[$roleIndex]));

            if (empty($username) || empty($namaLengkap) || empty($roleName)) {
                continue;
            }

            
            $exists = User::where('username', $username)->exists();
            if ($exists) {
                $skipCount++;
                continue;
            }

            
            if (!isset($roles[$roleName])) {
                continue;
            }
            $role = $roles[$roleName];

            $password = $roleName === 'super_admin' ? 'admingara776' : 'garaop457';

            User::create([
                'username' => $username,
                'nama_lengkap' => $namaLengkap,
                'password_hash' => Hash::make($password),
                'role_id' => $role->id,
                'status_aktif' => 1,
            ]);

            $successCount++;
        }

        return redirect()->back()->with('success_message', "Berhasil mengimpor {$successCount} user. ({$skipCount} dilewati karena username sudah terdaftar).");
    }
}
