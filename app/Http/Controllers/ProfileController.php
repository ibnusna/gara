<?php

namespace App\Http\Controllers;

use App\Integrations\Google\GoogleOAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    


    protected array $dashboardRoutes = [
        'siswa'       => 'student.dashboard',
        'guru'        => 'guru.dashboard',
        'operator'    => 'operator.dashboard',
        'kepsek'      => 'kepsek.dashboard',
        'super_admin' => 'superadmin.dashboard',
    ];

    


    protected array $profileRoutes = [
        'guru'        => 'guru.profile.index',
        'operator'    => 'operator.profile.index',
        'super_admin' => 'superadmin.profile.index',
    ];

    


    public function guruIndex()
    {
        $user = Auth::user();
        $role = optional($user->role)->role_name;
        $defaultPasswords = ['guru' => 'guru123'];
        $isDefaultPassword = isset($defaultPasswords[$role])
            ? Hash::check($defaultPasswords[$role], $user->getAuthPassword())
            : false;

        $oauthService = new GoogleOAuthService();
        $googleToken = $oauthService->getConnectedStatus($user->id);
        $googleConnected = !is_null($googleToken);
        $googleEmail = $googleConnected ? $googleToken->google_email : null;

        return view('guru.profile.index', compact('user', 'isDefaultPassword', 'googleConnected', 'googleEmail'));
    }

    


    public function operatorIndex()
    {
        $user = Auth::user();
        $role = optional($user->role)->role_name;
        $defaultPasswords = ['operator' => 'garaop457'];
        $isDefaultPassword = isset($defaultPasswords[$role])
            ? Hash::check($defaultPasswords[$role], $user->getAuthPassword())
            : false;

        return view('operator.profile.index', compact('user', 'isDefaultPassword'));
    }

    


    public function superAdminIndex()
    {
        $user = Auth::user();
        $role = optional($user->role)->role_name;
        $defaultPasswords = ['super_admin' => 'admingara776'];
        $isDefaultPassword = isset($defaultPasswords[$role])
            ? Hash::check($defaultPasswords[$role], $user->getAuthPassword())
            : false;

        return view('super_admin.profile.index', compact('user', 'isDefaultPassword'));
    }

    


    public function updatePhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|file|mimes:jpg,jpeg,png|max:2048',
        ], [
            'photo.required' => 'Pilih foto terlebih dahulu.',
            'photo.mimes'    => 'Format foto harus JPG atau PNG.',
            'photo.max'      => 'Ukuran foto maksimal 2 MB.',
        ]);

        $user = Auth::user();

        
        if ($user->profile_photo) {
            Storage::disk('public')->delete('profile_photos/' . $user->profile_photo);
        }

        
        $ext      = $request->file('photo')->getClientOriginalExtension();
        $filename = 'profile_' . $user->id . '_' . time() . '.' . $ext;
        $request->file('photo')->storeAs('profile_photos', $filename, 'public');

        $user->profile_photo = $filename;
        $user->save();
        $user->refresh();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Foto profil berhasil diperbarui.',
                'photo_url' => $user->profile_photo_url
            ]);
        }

        return back()->with('success', 'Foto profil berhasil diperbarui.');
    }

    


    public function updatePassword(Request $request)
    {
        $request->validate([
            'pass_lama' => 'required',
            'pass_baru' => 'required|min:6',
            'pass_konf' => 'required|same:pass_baru',
        ], [
            'pass_lama.required' => 'Password lama wajib diisi.',
            'pass_baru.required' => 'Password baru wajib diisi.',
            'pass_baru.min'      => 'Password baru minimal 6 karakter.',
            'pass_konf.required' => 'Konfirmasi password wajib diisi.',
            'pass_konf.same'     => 'Konfirmasi password tidak cocok.',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->pass_lama, $user->getAuthPassword())) {
            return back()->withInput()->with('error', 'Password lama yang Anda masukkan salah.');
        }

        $user->update(['password_hash' => Hash::make($request->pass_baru)]);

        $role           = optional($user->role)->role_name;
        $dashboardRoute = $this->dashboardRoutes[$role] ?? '/';

        return redirect()->route($dashboardRoute)
            ->with('success_message', 'Password berhasil diperbarui.');
    }
}
