import { Store } from './store.js';
import { showModal, closeModal } from './ui-modal.js';







const elements = {
    nis: document.getElementById("summaryNIS"),
    nama: document.getElementById("summaryNama"),
    kelas: document.getElementById("summaryKelas"),
    mapel: document.getElementById("summaryMapel"),
    waktu: document.getElementById("summaryWaktu"),
    btnStart: document.getElementById("startUjianBtn"),

    
    loadingOverlay: document.getElementById("loadingOverlay"),
    mainLayout: document.getElementById("mainLayout")
};



export const initSummaryPage = async () => {
    
    toggleLoading(true);

    
    detectAndSaveOrigin();

    
    const urlParams = new URLSearchParams(window.location.search);
    const paramNis = urlParams.get('nis');
    const paramToken = urlParams.get('token');

    try {
        
        if (paramNis && paramToken) {
            await handleLoginProcess(paramNis, paramToken);
        }

        
        
        if (!Store.isAuthenticated()) {
            throw new Error("Sesi tidak valid. Silakan login kembali.");
        }

        
        renderStudentData();

        
        if (elements.btnStart) {
            elements.btnStart.onclick = handleStartExam;
        }

        
        
        setTimeout(() => {
            toggleLoading(false);
        }, 500);

    } catch (error) {
        console.error("Init Error:", error);
        
        window.location.href = getExitUrl();
    }
};



const handleLoginProcess = async (nis, token) => {
    

    try {
        
        if (typeof validateLogin === 'undefined') {
            throw new Error("Koneksi server (google-apps.js) belum siap.");
        }

        
        const response = await validateLogin({ nis: nis, kodeAkses: token });

        if (response && response.isValid) {
            
            Store.setSiswa(response.siswa);
            Store.setUjianMeta(response.ujian);
            Store.setSavedNIS(nis);

            
            const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
            window.history.replaceState({ path: cleanUrl }, "", cleanUrl);

            
            return true;
        } else {
            throw new Error("Validasi ditolak oleh server. Token atau NIS salah.");
        }

    } catch (error) {
        
        

        
        toggleLoading(false);

        showModal({
            title: "Akses Ditolak",
            text: "Gagal masuk ujian: " + (error.message || "Data tidak valid."),
            icon: "error",
            confirmButtonText: "Kembali Login",
            onConfirm: () => {
                const exitUrl = getExitUrl();
                Store.clearAll();
                window.location.href = exitUrl;
            }
        });

        
        throw error;
    }
};

const renderStudentData = () => {
    const siswa = Store.getSiswa();
    const meta = Store.getUjianMeta();

    if (!siswa || !meta) return;

    if (elements.nama) elements.nama.textContent = siswa["Nama Lengkap"];
    if (elements.kelas) elements.kelas.textContent = siswa.Kelas;
    if (elements.nis) elements.nis.textContent = siswa.NIS;

    if (elements.mapel) elements.mapel.textContent = meta.topik;
    if (elements.waktu) elements.waktu.textContent = meta.durasi;
};

const handleStartExam = () => {
    
    toggleLoading(true);

    Store.clearExamSession();
    window.location.href = (window.__EXAM_URLS__ && window.__EXAM_URLS__.ujian) ? window.__EXAM_URLS__.ujian : 'ujian.html';
};


const getExitUrl = () => {
    
    const origin = sessionStorage.getItem('exam_origin');

    if (origin === 'lms') {
        return 'http://garaedu.gt.tc';
    }

    
    return (window.__EXAM_URLS__ && window.__EXAM_URLS__.exit) ? window.__EXAM_URLS__.exit : 'index.html';
};

const detectAndSaveOrigin = () => {
    
    
    
    
    
    if (sessionStorage.getItem('exam_origin') === 'lms') {
        return;
    }

    const hostname = window.location.hostname;
    const referrer = document.referrer || "";

    
    
    if (hostname.includes('garudakademi.ct.ws') || referrer.includes('garudakademi.ct.ws')) {
        sessionStorage.setItem('exam_origin', 'lms');
    } else {
        
        sessionStorage.setItem('exam_origin', 'hosting');
    }
};



const toggleLoading = (show) => {
    if (show) {
        if (elements.loadingOverlay) elements.loadingOverlay.style.display = 'flex';
        if (elements.mainLayout) elements.mainLayout.style.display = 'none';
    } else {
        
        if (elements.loadingOverlay) elements.loadingOverlay.style.display = 'none';
        if (elements.mainLayout) elements.mainLayout.style.display = 'flex';
    }
};