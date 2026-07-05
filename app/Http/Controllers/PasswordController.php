<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PasswordController extends Controller
{
    



    protected array $defaultPasswords = [
        'siswa'       => 'siswa123',
        'guru'        => 'guru123',
        'operator'    => 'garaop457',
        'kepsek'      => 'admingara776',
        'super_admin' => 'admingara776',
    ];

    


    protected array $layoutMap = [
        'siswa'       => 'layouts.siswa',
        'guru'        => 'layouts.guru',
        'operator'    => 'layouts.operator',
        'kepsek'      => 'layouts.kepsek',
        'super_admin' => 'layouts.super_admin',
    ];

    


    protected array $dashboardRoutes = [
        'siswa'       => 'student.dashboard',
        'guru'        => 'guru.dashboard',
        'operator'    => 'operator.dashboard',
        'kepsek'      => 'kepsek.dashboard',
        'super_admin' => 'superadmin.dashboard',
    ];

    


    public function showForm()
    {
        $user = Auth::user();
        $role = optional($user->role)->role_name;

        $defaultPassword = $this->defaultPasswords[$role] ?? null;
        $isDefaultPassword = $defaultPassword
            ? Hash::check($defaultPassword, $user->getAuthPassword())
            : false;

        $layout = $this->layoutMap[$role] ?? 'layouts.siswa';

        return view('shared.ubah_password', compact('isDefaultPassword', 'layout', 'role'));
    }

    



    public function update(Request $request)
    {
        $user = Auth::user();
        $role = optional($user->role)->role_name;

        $defaultPassword = $this->defaultPasswords[$role] ?? null;
        $isDefaultPassword = $defaultPassword
            ? Hash::check($defaultPassword, $user->getAuthPassword())
            : false;

        
        $rules = [
            'pass_baru' => 'required|min:6',
            'pass_konf' => 'required|same:pass_baru',
        ];

        
        if (!$isDefaultPassword) {
            $rules['pass_lama'] = 'required';
        }

        $request->validate($rules, [
            'pass_baru.required'  => 'Password baru wajib diisi.',
            'pass_baru.min'       => 'Password baru minimal 6 karakter.',
            'pass_konf.required'  => 'Konfirmasi password wajib diisi.',
            'pass_konf.same'      => 'Konfirmasi password tidak cocok dengan password baru.',
            'pass_lama.required'  => 'Password lama wajib diisi.',
        ]);

        
        if (!$isDefaultPassword) {
            if (!Hash::check($request->pass_lama, $user->getAuthPassword())) {
                return back()
                    ->withInput()
                    ->with('error_ubah_password', 'Password lama yang Anda masukkan salah.');
            }
        }

        
        $user->update([
            'password_hash' => Hash::make($request->pass_baru),
        ]);

        
        $dashboardRoute = $this->dashboardRoutes[$role] ?? '/';

        return redirect()
            ->route($dashboardRoute)
            ->with('success_message', 'Password berhasil diperbarui. Silakan gunakan password baru Anda untuk login berikutnya.');
    }
}
