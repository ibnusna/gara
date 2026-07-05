







@php
    $formRoutes = [
        'siswa'       => 'student.password.update',
        'guru'        => 'guru.password.update',
        'operator'    => 'operator.password.update',
        'kepsek'      => 'kepsek.password.update',
        'super_admin' => 'superadmin.password.update',
    ];
    $formAction = route($formRoutes[$role] ?? 'login');
@endphp

<div class="pwd-card">

    <div class="section-label">Keamanan Akun</div>

    


    @if($isDefaultPassword)
        <div class="pwd-notice" style="background:#fff8e1; border-left:4px solid #ffb300; color:#795548;">
            <i class="fas fa-exclamation-circle mr-2" style="color:#ffb300;"></i>
            <strong>Perhatian:</strong> Anda masih menggunakan <strong>password bawaan sistem</strong>.
            Sangat disarankan untuk segera menggantinya demi keamanan akun Anda.
        </div>
    @endif

    
    @if(session('error_ubah_password'))
        <div class="pwd-notice" style="background:#fdecea; border-left:4px solid #d32f2f; color:#b71c1c;">
            <i class="fas fa-times-circle mr-2"></i>
            {{ session('error_ubah_password') }}
        </div>
    @endif

    
    @if($errors->any())
        <div class="pwd-notice" style="background:#fdecea; border-left:4px solid #d32f2f; color:#b71c1c;">
            <i class="fas fa-times-circle mr-2"></i>
            <ul class="mb-0 pl-3">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ $formAction }}" method="POST" id="form-ubah-password" autocomplete="off">
        @csrf

        



        @if(!$isDefaultPassword)
            <div class="mb-3">
                <label class="form-label" for="pwd_pass_lama">Password Lama</label>
                <div class="pwd-input-wrap">
                    <input
                        type="password"
                        id="pwd_pass_lama"
                        name="pass_lama"
                        class="form-control {{ $errors->has('pass_lama') ? 'is-invalid' : '' }}"
                        placeholder="Masukkan password lama..."
                        autocomplete="current-password"
                        required
                    >
                    <button type="button" class="pwd-toggle" data-target="pass_lama" tabindex="-1" aria-label="Tampilkan password">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
        @endif

        


        <div class="mb-3">
            <label class="form-label" for="pwd_pass_baru">Password Baru</label>
            <div class="pwd-input-wrap">
                <input
                    type="password"
                    id="pwd_pass_baru"
                    name="pass_baru"
                    class="form-control {{ $errors->has('pass_baru') ? 'is-invalid' : '' }}"
                    placeholder="Minimal 6 karakter"
                    autocomplete="new-password"
                    required
                >
                <button type="button" class="pwd-toggle" data-target="pass_baru" tabindex="-1" aria-label="Tampilkan password">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
        </div>

        


        <div class="mb-4">
            <label class="form-label" for="pwd_pass_konf">Konfirmasi Password Baru</label>
            <div class="pwd-input-wrap">
                <input
                    type="password"
                    id="pwd_pass_konf"
                    name="pass_konf"
                    class="form-control {{ $errors->has('pass_konf') ? 'is-invalid' : '' }}"
                    placeholder="Ketik ulang password baru"
                    autocomplete="new-password"
                    required
                >
                <button type="button" class="pwd-toggle" data-target="pass_konf" tabindex="-1" aria-label="Tampilkan password">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
        </div>

        <button type="submit" class="btn btn-primary pwd-submit" id="btn-simpan-password">
            <i class="fas fa-save mr-2"></i> Simpan Password Baru
        </button>
    </form>

</div>
