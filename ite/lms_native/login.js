/**
 * GARUDA AKADEMI (GARA) - MOBILE PWA LOGIC (REAL BACKEND CONNECTED)
 * Fokus: Menghubungkan Form Login ke 'actions/login_process.php'
 */

document.addEventListener('DOMContentLoaded', () => {
    
    // --- ELEMENT SELECTION ---
    const welcomeScreen = document.getElementById('welcome-screen');
    const loginScreen = document.getElementById('login-screen');
    const btnContinue = document.getElementById('btn-continue');
    const btnLogin = document.getElementById('btnLogin');
    const loginForm = document.getElementById('loginForm');
    const togglePasswordBtn = document.querySelector('.toggle-password');
    const passwordInput = document.getElementById('password');

    // --- 1. WELCOME SCREEN TRANSITION (Tetap Sama) ---
    if (btnContinue) {
        btnContinue.addEventListener('click', function(e) {
            e.preventDefault();
            welcomeScreen.style.opacity = '0';
            setTimeout(() => {
                welcomeScreen.classList.remove('active');
                welcomeScreen.style.display = 'none';
                loginScreen.style.display = 'flex';
                requestAnimationFrame(() => {
                    loginScreen.classList.add('active');
                });
            }, 300);
        });
    }

    // --- 2. PASSWORD TOGGLE (Tetap Sama) ---
    if (togglePasswordBtn && passwordInput) {
        togglePasswordBtn.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            const icon = this.querySelector('i');
            if (type === 'text') {
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            } else {
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        });
    }

    // --- 3. REAL LOGIN PROCESS (KONEKSI KE PHP) ---
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault(); // Stop reload standar

            // Ambil data form
            const formData = new FormData(this);

            // Validasi Client Side (Visual saja)
            if (!formData.get('identifier') || !formData.get('password')) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Data Belum Lengkap',
                    text: 'Silakan isi username dan password!',
                    confirmButtonColor: '#0056b3',
                    heightAuto: false
                });
                return;
            }

            // UI Loading State
            const originalBtnText = btnLogin.innerHTML;
            btnLogin.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> MEMUAT...';
            btnLogin.disabled = true;

            // --- TEMBAK KE BACKEND ---
            fetch('actions/login_process.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                // Pastikan respon adalah JSON valid
                if (!response.ok) {
                    throw new Error('Terjadi kesalahan jaringan atau server.');
                }
                return response.json();
            })
            .then(data => {
                // --- LOGIKA RESPON DARI PHP ---
                
                if (data.status === 'success') {
                    // JIKA LOGIN SUKSES
                    Swal.fire({
                        icon: 'success',
                        title: 'Login Berhasil!',
                        text: 'Mengalihkan ke dashboard...',
                        showConfirmButton: false,
                        timer: 1500,
                        heightAuto: false
                    }).then(() => {
                        // Redirect ke URL yang dikasih oleh PHP (data.redirect)
                        // Contoh: 'admin/pilih_sesi.php' atau 'student/dashboard.php'
                        window.location.href = data.redirect;
                    });

                } else {
                    // JIKA LOGIN GAGAL (Password salah / User tidak ada)
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Masuk',
                        text: data.message || 'Username atau password salah.',
                        confirmButtonColor: '#d33',
                        heightAuto: false
                    });
                    
                    // Reset Tombol
                    btnLogin.innerHTML = originalBtnText;
                    btnLogin.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan Sistem',
                    text: 'Tidak dapat terhubung ke server. Coba lagi nanti.',
                    confirmButtonColor: '#d33',
                    heightAuto: false
                });
                
                // Reset Tombol
                btnLogin.innerHTML = originalBtnText;
                btnLogin.disabled = false;
            });
        });
    }
});