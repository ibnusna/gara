/**
 * File: js/dashboard_v2.js
 * Logic utama untuk Dashboard Siswa (Loading, Widget, dll)
 */

// 0. SERVICE WORKER REGISTRATION
// 0. SERVICE WORKER REMOVAL (Force Native Mode)
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.getRegistrations().then(function (registrations) {
        for (let registration of registrations) {
            registration.unregister();
            console.log("Service Worker Unregistered (Force Native Mode)");
        }
    });
}

// 1. Init Logic (Support Lazy Load / AJAX)
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initDashboard);
} else {
    // Jika script di-load via AJAX (document sudah ready), langsung jalankan
    initDashboard();
}

function initDashboard() {
    loadJadwalSholat();
    loadTrivia();
    loadPengumuman(); // New Async Fetch
}

// 1. LOGIKA JADWAL SHOLAT (API: Aladhan)
// ==========================================
async function loadJadwalSholat() {
    const container = document.getElementById('sholat-times-grid');
    const locElement = document.getElementById('sholat-location');

    if (!container) return; // Guard clause if widget not present

    // Default fallback (Jakarta) jika lokasi tidak diizinkan
    let lat = -6.2088;
    let long = 106.8456;

    // Coba ambil lokasi user
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                lat = position.coords.latitude;
                long = position.coords.longitude;
                fetchSholatData(lat, long);
            },
            () => {
                // Jika ditolak/error, pakai default Jakarta
                if (locElement) locElement.innerText = "Lokasi: Bogor (Default)";
                fetchSholatData(lat, long);
            }
        );
    } else {
        fetchSholatData(lat, long);
    }
}

async function fetchSholatData(lat, long) {
    const date = new Date();
    // Format YYYY-MM-DD
    const dateString = date.toISOString().split('T')[0];
    const apiUrl = `https://api.aladhan.com/v1/timings/${dateString}?latitude=${lat}&longitude=${long}&method=11`; // Method 11 = Majlis Ugama Islam Singapura (Standard ASEAN)

    try {
        const response = await fetch(apiUrl);
        const data = await response.json();

        if (data.code === 200) {
            updateSholatUI(data.data);
        }
    } catch (error) {
        console.error("Gagal memuat jadwal sholat:", error);
    }
}

function updateSholatUI(data) {
    const timings = data.timings;
    const meta = data.meta;

    // Update Lokasi Text (Simulasi dari Timezone)
    const locElement = document.getElementById('sholat-location');
    if (locElement) locElement.innerText = `Lokasi: Bogor `;

    // List Waktu yang mau ditampilkan
    const targetTimes = ['Fajr', 'Dhuhr', 'Asr', 'Maghrib', 'Isha'];
    const mapNames = { 'Fajr': 'Subuh', 'Dhuhr': 'Dzuhur', 'Asr': 'Ashar', 'Maghrib': 'Maghrib', 'Isha': 'Isya' };

    // Cari jam sekarang untuk highlight
    const now = new Date();
    const currentMinutes = now.getHours() * 60 + now.getMinutes();

    targetTimes.forEach(key => {
        const timeValue = timings[key];
        const displayValue = timeValue; // Contoh: "04:30"

        const el = document.getElementById(`time-${mapNames[key].toLowerCase()}`);
        if (el) {
            el.innerText = displayValue;

            // Logika Highlight Sederhana
            // (Mengecek jika waktu sholat < waktu sekarang, ini hanya pendekatan visual)
            // Untuk presisi butuh parsing jam menit lebih detail.
        }
    });
}

// ==========================================
// 2. LOGIKA TRIVIA QUIZ (API: OpenTDB)
// ==========================================
async function loadTrivia() {
    const questionEl = document.getElementById('trivia-question');
    const optionsEl = document.getElementById('trivia-options');

    if (!questionEl || !optionsEl) return; // Guard clause

    // Tampilkan Skeleton (Reset State)
    questionEl.innerHTML = `
        <div class="skeleton sk-text sk-text-lg mb-2"></div>
        <div class="skeleton sk-text sk-text-sm mb-4" style="width: 60%"></div>
    `;
    optionsEl.innerHTML = `
        <div class="d-grid gap-2">
            <div class="skeleton sk-btn"></div>
            <div class="skeleton sk-btn"></div>
            <div class="skeleton sk-btn"></div>
            <div class="skeleton sk-btn"></div>
        </div>
    `;

    // API: Science & Computers (Category 18 & 17), Medium/Easy
    const apiUrl = 'https://opentdb.com/api.php?amount=1&type=multiple';

    try {
        const response = await fetch(apiUrl);
        const data = await response.json();

        if (data.response_code === 0) {
            renderTrivia(data.results[0]);
        } else {
            renderFallbackTrivia(); // Fallback jika API habis quota/error
        }
    } catch (error) {
        console.warn("Trivia API Error (Switching to Local):", error);
        renderFallbackTrivia();
    }
}

// Fallback / Offline Mode
function renderFallbackTrivia() {
    const offlineQuestions = [
        {
            question: "Komponen komputer yang berfungsi sebagai otak pemroses data adalah?",
            correct_answer: "CPU",
            incorrect_answers: ["RAM", "Hard Disk", "VGA"]
        },
        {
            question: "Shortcut umum untuk 'Copy' pada Windows adalah?",
            correct_answer: "Ctrl + C",
            incorrect_answers: ["Ctrl + V", "Ctrl + X", "Alt + F4"]
        },
        {
            question: "Manakah yang BUKAN merupakan bahasa pemrograman?",
            correct_answer: "HTML",
            incorrect_answers: ["Python", "Java", "C++"]
        }
    ];
    // Pilih 1 acak
    const q = offlineQuestions[Math.floor(Math.random() * offlineQuestions.length)];
    renderTrivia(q);
}

function renderTrivia(questionData) {
    const questionEl = document.getElementById('trivia-question');
    const optionsEl = document.getElementById('trivia-options');

    // Decode HTML Entities (karena API return format HTML encoded)
    const decodeHtml = (html) => {
        const txt = document.createElement("textarea");
        txt.innerHTML = html;
        return txt.value;
    };

    questionEl.innerText = decodeHtml(questionData.question);

    // Gabung Jawaban Benar & Salah lalu acak
    let answers = [...questionData.incorrect_answers, questionData.correct_answer];
    answers = shuffleArray(answers);

    answers.forEach(ans => {
        const btn = document.createElement('button');
        btn.className = 'btn-trivia';
        btn.innerText = decodeHtml(ans);

        btn.onclick = () => {
            // Disable semua tombol setelah klik
            const allBtns = optionsEl.querySelectorAll('button');
            allBtns.forEach(b => b.disabled = true);

            if (ans === questionData.correct_answer) {
                btn.classList.add('correct');
                btn.innerText += " ✅ (Benar!)";
                // Bisa tambahkan logika poin jika mau (nanti disimpan ke localStorage sementara)
            } else {
                btn.classList.add('wrong');
                btn.innerText += " ❌";
                // Cari jawaban yang benar untuk dikasih tau
                allBtns.forEach(b => {
                    if (b.innerText === decodeHtml(questionData.correct_answer)) {
                        b.classList.add('correct');
                    }
                });
            }

            // Tombol Main Lagi
            setTimeout(() => {
                const reloadBtn = document.createElement('button');
                reloadBtn.className = 'btn-trivia';
                reloadBtn.style.textAlign = 'center';
                reloadBtn.style.marginTop = '10px';
                reloadBtn.style.fontWeight = 'bold';
                reloadBtn.innerHTML = '<i class="fas fa-redo"></i> Soal Berikutnya';
                reloadBtn.onclick = () => loadTrivia();
                optionsEl.appendChild(reloadBtn);
            }, 1000);
        };

        optionsEl.appendChild(btn);
    });
}

// Utility: Shuffle Array (Fisher-Yates)
function shuffleArray(array) {
    for (let i = array.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [array[i], array[j]] = [array[j], array[i]];
    }
    return array;
}

// ==========================================
// 3. LOGIKA PENGUMUMAN (Async Skeleton)
// ==========================================
function loadPengumuman() {
    const container = document.getElementById('pengumuman-container');
    if (!container) return;

    // Direct Fetch (No GaraLoader dependency)
    fetch('modules/api_pengumuman.php?nocache=' + Date.now())
        .then(response => response.json())
        .then(response => {
            if (response.found && response.data) {
                const p = response.data;
                const html = `
                <div class="mb-3">
                    <div class="section-label mt-0 mb-2">PENGUMUMAN KELAS</div>
                    <div class="widget-card p-3 border-0 shadow-sm" style="background: #fff; border-left: 4px solid var(--color-primary);">
                        <div class="d-flex align-items-start">
                            <div class="me-3 mt-1 text-primary">
                                <i class="fas fa-bullhorn fa-lg"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">${escapeHtml(p.judul)}</h6>
                                <p class="mb-0 text-muted small" style="line-height: 1.6;">${escapeHtml(p.isi)}</p>
                            </div>
                        </div>
                    </div>
                </div>`;
                container.innerHTML = html;
            } else {
                // WAJIB DI HILANGKAN jika tidak ada data
                container.innerHTML = '';
                container.style.display = 'none'; // Ensure it takes no space
            }
        })
        .catch(error => {
            console.error("Gagal memuat pengumuman:", error);
            // Panic fallback: hide everything
            container.innerHTML = '';
            container.style.display = 'none';
        });
}

// Helper: Prevent XSS
function escapeHtml(text) {
    if (!text) return "";
    return text
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}