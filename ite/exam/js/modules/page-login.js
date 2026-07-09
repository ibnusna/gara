import { Store } from './store.js';
import { showModal } from './ui-modal.js';






const elements = {
    inputNIS: document.getElementById("inputNIS"),
    inputToken: document.getElementById("inputToken"),
    btnLogin: document.getElementById("btnLogin"),
    loginForm: document.getElementById("loginForm")
};

export const initLoginPage = () => {
    
    if (elements.btnLogin) {
        elements.btnLogin.onclick = handleLogin;
    }

    
    if (elements.inputToken) {
        elements.inputToken.addEventListener("keypress", (e) => {
            if (e.key === "Enter") handleLogin();
        });
    }
};

const handleLogin = async (e) => {
    if (e) e.preventDefault();

    const nis = elements.inputNIS.value.trim();
    const token = elements.inputToken.value.trim().toUpperCase();

    if (!nis || !token) {
        Swal.fire('Error', 'NIS dan Token wajib diisi!', 'warning');
        return;
    }

    
    Swal.fire({
        title: 'Memeriksa Token...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    try {
        if (typeof validateLogin === 'undefined') {
            throw new Error("Koneksi server belum siap.");
        }

        const response = await validateLogin({ nis: nis, kodeAkses: token });

        if (response && response.isValid) {
            
            Store.setSiswa(response.siswa);
            Store.setUjianMeta(response.ujian);
            Store.setSavedNIS(nis);

            
            const summaryUrl = (window.__EXAM_URLS__ && window.__EXAM_URLS__.summary)
                ? `${window.__EXAM_URLS__.summary}?nis=${nis}&token=${token}`
                : `summary.html?nis=${nis}&token=${token}`;
            window.location.href = summaryUrl;
            
            
            
            
            
            

            
            
        } else {
            throw new Error(response.message || "Login gagal.");
        }

    } catch (error) {
        const msg = error.message || 'Terjadi kesalahan.';

        
        if (msg.includes('Token tidak sesuai untuk kelas')) {
            
            const lines = msg.split('\n').filter(Boolean);
            const htmlMsg = lines.map(l => `<p style="margin:0.25rem 0;">${l.trim()}</p>`).join('');

            Swal.fire({
                icon: 'error',
                title: '🚫 Token Tidak Sesuai',
                html: `<div style="text-align:left;font-size:0.9rem;line-height:1.6;">${htmlMsg}</div>`,
                confirmButtonText: 'Tutup',
                confirmButtonColor: '#dc3545',
                footer: '<small class="text-muted">Hubungi pengawas untuk mendapatkan token yang benar.</small>'
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Gagal Masuk',
                text: msg,
                confirmButtonColor: '#0b57d0'
            });
        }
    }
};
