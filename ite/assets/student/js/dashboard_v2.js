






if ('serviceWorker' in navigator) {
    navigator.serviceWorker.getRegistrations().then(function (registrations) {
        for (let registration of registrations) {
            registration.unregister();
            console.log("Service Worker Unregistered (Force Native Mode)");
        }
    });
}


if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initDashboard);
} else {
    
    initDashboard();
}

function initDashboard() {
    loadJadwalSholat();
    loadTrivia();
    loadPengumuman(); 
}



async function loadJadwalSholat() {
    const container = document.getElementById('sholat-times-grid');
    const locElement = document.getElementById('sholat-location');

    if (!container) return; 

    
    let lat = -6.2088;
    let long = 106.8456;

    
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                lat = position.coords.latitude;
                long = position.coords.longitude;
                fetchSholatData(lat, long);
            },
            () => {
                
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
    
    const dateString = date.toISOString().split('T')[0];
    const apiUrl = `https://api.aladhan.com/v1/timings/${dateString}?latitude=${lat}&longitude=${long}&method=11`; 

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

    
    const locElement = document.getElementById('sholat-location');
    if (locElement) locElement.innerText = `Lokasi: Bogor `;

    
    const targetTimes = ['Fajr', 'Dhuhr', 'Asr', 'Maghrib', 'Isha'];
    const mapNames = { 'Fajr': 'Subuh', 'Dhuhr': 'Dzuhur', 'Asr': 'Ashar', 'Maghrib': 'Maghrib', 'Isha': 'Isya' };

    
    const now = new Date();
    const currentMinutes = now.getHours() * 60 + now.getMinutes();

    targetTimes.forEach(key => {
        const timeValue = timings[key];
        const displayValue = timeValue; 

        const el = document.getElementById(`time-${mapNames[key].toLowerCase()}`);
        if (el) {
            el.innerText = displayValue;

            
            
            
        }
    });
}




async function loadTrivia() {
    const questionEl = document.getElementById('trivia-question');
    const optionsEl = document.getElementById('trivia-options');

    if (!questionEl || !optionsEl) return; 

    
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

    
    const apiUrl = 'https://opentdb.com/api.php?amount=1&type=multiple';

    try {
        const response = await fetch(apiUrl);
        const data = await response.json();

        if (data.response_code === 0) {
            renderTrivia(data.results[0]);
        } else {
            renderFallbackTrivia(); 
        }
    } catch (error) {
        console.warn("Trivia API Error (Switching to Local):", error);
        renderFallbackTrivia();
    }
}


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
    
    const q = offlineQuestions[Math.floor(Math.random() * offlineQuestions.length)];
    renderTrivia(q);
}

function renderTrivia(questionData) {
    const questionEl = document.getElementById('trivia-question');
    const optionsEl = document.getElementById('trivia-options');

    
    const decodeHtml = (html) => {
        const txt = document.createElement("textarea");
        txt.innerHTML = html;
        return txt.value;
    };

    questionEl.innerText = decodeHtml(questionData.question);

    
    let answers = [...questionData.incorrect_answers, questionData.correct_answer];
    answers = shuffleArray(answers);

    answers.forEach(ans => {
        const btn = document.createElement('button');
        btn.className = 'btn-trivia';
        btn.innerText = decodeHtml(ans);

        btn.onclick = () => {
            
            const allBtns = optionsEl.querySelectorAll('button');
            allBtns.forEach(b => b.disabled = true);

            if (ans === questionData.correct_answer) {
                btn.classList.add('correct');
                btn.innerText += " ✅ (Benar!)";
                
            } else {
                btn.classList.add('wrong');
                btn.innerText += " ❌";
                
                allBtns.forEach(b => {
                    if (b.innerText === decodeHtml(questionData.correct_answer)) {
                        b.classList.add('correct');
                    }
                });
            }

            
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


function shuffleArray(array) {
    for (let i = array.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [array[i], array[j]] = [array[j], array[i]];
    }
    return array;
}




function loadPengumuman() {
    const container = document.getElementById('pengumuman-container');
    if (!container) return;

    
    fetch('/api/student/pengumuman?nocache=' + Date.now())
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
                
                container.innerHTML = '';
                container.style.display = 'none'; 
            }
        })
        .catch(error => {
            console.error("Gagal memuat pengumuman:", error);
            
            container.innerHTML = '';
            container.style.display = 'none';
        });
}


function escapeHtml(text) {
    if (!text) return "";
    return text
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}





let _sseSource = null; 

function initSSE() {
    
    if (!document.getElementById('pengumuman-container')) return;
    
    if (_sseSource && _sseSource.readyState !== EventSource.CLOSED) return;

    const sseUrl = '/edu/student/notifications/stream';

    try {
        _sseSource = new EventSource(sseUrl);

        _sseSource.addEventListener('notification', function (e) {
            try {
                const payload = JSON.parse(e.data);
                if (payload.notifications && payload.notifications.length > 0) {
                    
                    const notif = payload.notifications[0];
                    const notifId = 'notif_' + btoa(unescape(encodeURIComponent(notif.title + notif.message))).substring(0, 20);
                    if (!localStorage.getItem(notifId)) {
                        localStorage.setItem(notifId, 'shown');
                        showSSEToast(notif.icon, notif.title, notif.message);
                    }
                }
            } catch (err) {
                
            }
        });

        _sseSource.addEventListener('close', function () {
            
            _sseSource.close();
            setTimeout(initSSE, 3000);
        });

        _sseSource.onerror = function () {
            
            if (_sseSource) _sseSource.close();
            setTimeout(initSSE, 10000);
        };

    } catch (err) {
        
        console.warn('SSE not available:', err.message);
    }
}




function showSSEToast(icon, title, message) {
    
    const existing = document.getElementById('gara-sse-toast');
    if (existing) existing.remove();

    const toast = document.createElement('div');
    toast.id = 'gara-sse-toast';
    toast.innerHTML = `
        <div style="
            position: fixed;
            top: 20px;
            right: 16px;
            z-index: 99999;
            max-width: 320px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.15), 0 2px 8px rgba(37,99,235,0.12);
            padding: 14px 16px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            border-left: 4px solid hsl(220,90%,50%);
            animation: sseToastIn 0.4s cubic-bezier(0.34,1.56,0.64,1) forwards;
            font-family: 'Inter', sans-serif;
        ">
            <div style="font-size: 1.5rem; flex-shrink: 0; line-height: 1;">${icon}</div>
            <div style="flex: 1; min-width: 0;">
                <div style="font-weight: 700; font-size: 0.85rem; color: #1e293b; margin-bottom: 3px;">${escapeHtml(title)}</div>
                <div style="font-size: 0.8rem; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${escapeHtml(message)}</div>
            </div>
            <button onclick="document.getElementById('gara-sse-toast').remove()" style="
                background: none; border: none; cursor: pointer; color: #94a3b8;
                font-size: 1rem; padding: 0; line-height: 1; flex-shrink: 0;
            ">×</button>
        </div>
        <style>
            @keyframes sseToastIn {
                from { opacity: 0; transform: translateX(100px) scale(0.9); }
                to   { opacity: 1; transform: translateX(0) scale(1); }
            }
            @keyframes sseToastOut {
                from { opacity: 1; transform: translateX(0) scale(1); }
                to   { opacity: 0; transform: translateX(100px) scale(0.9); }
            }
        </style>
    `;
    document.body.appendChild(toast);

    
    setTimeout(() => {
        const el = document.getElementById('gara-sse-toast');
        if (el) {
            el.firstElementChild.style.animation = 'sseToastOut 0.3s ease forwards';
            setTimeout(() => el.remove(), 350);
        }
    }, 5000);
}


















function triggerConfetti() {
    const canvas = document.createElement('canvas');
    canvas.style.cssText = `
        position: fixed;
        top: 0; left: 0;
        width: 100vw; height: 100vh;
        pointer-events: none;
        z-index: 99998;
    `;
    document.body.appendChild(canvas);

    const ctx = canvas.getContext('2d');
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;

    
    const colors = [
        'hsl(220,90%,50%)', 'hsl(220,90%,65%)', 'hsl(220,90%,75%)',
        'hsl(35,95%,50%)',  'hsl(35,95%,65%)',
        '#22c55e', '#a855f7', '#ec4899'
    ];

    const particles = Array.from({ length: 80 }, () => ({
        x: canvas.width / 2 + (Math.random() - 0.5) * 200,
        y: canvas.height * 0.6,
        vx: (Math.random() - 0.5) * 12,
        vy: -(Math.random() * 12 + 4),
        size: Math.random() * 8 + 3,
        color: colors[Math.floor(Math.random() * colors.length)],
        rotation: Math.random() * 360,
        rotationSpeed: (Math.random() - 0.5) * 10,
        opacity: 1,
        shape: Math.random() > 0.5 ? 'rect' : 'circle'
    }));

    let frame = 0;
    const maxFrames = 90;

    function animateConfetti() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        particles.forEach(p => {
            p.x  += p.vx;
            p.y  += p.vy;
            p.vy += 0.4; 
            p.rotation += p.rotationSpeed;
            p.opacity = Math.max(0, 1 - frame / maxFrames);

            ctx.save();
            ctx.globalAlpha = p.opacity;
            ctx.translate(p.x, p.y);
            ctx.rotate((p.rotation * Math.PI) / 180);
            ctx.fillStyle = p.color;

            if (p.shape === 'rect') {
                ctx.fillRect(-p.size / 2, -p.size / 4, p.size, p.size / 2);
            } else {
                ctx.beginPath();
                ctx.arc(0, 0, p.size / 2, 0, Math.PI * 2);
                ctx.fill();
            }
            ctx.restore();
        });

        frame++;
        if (frame < maxFrames) {
            requestAnimationFrame(animateConfetti);
        } else {
            canvas.remove();
        }
    }

    animateConfetti();
}








const _originalRenderTrivia = typeof renderTrivia !== 'undefined' ? renderTrivia : null;

function renderTrivia(questionData) {
    const questionEl = document.getElementById('trivia-question');
    const optionsEl = document.getElementById('trivia-options');

    if (!questionEl || !optionsEl) return;

    const decodeHtml = (html) => {
        const txt = document.createElement('textarea');
        txt.innerHTML = html;
        return txt.value;
    };

    questionEl.innerText = decodeHtml(questionData.question);

    let answers = [...questionData.incorrect_answers, questionData.correct_answer];
    answers = shuffleArray(answers);

    
    optionsEl.innerHTML = '';

    answers.forEach(ans => {
        const btn = document.createElement('button');
        btn.className = 'btn-trivia';
        btn.innerText = decodeHtml(ans);

        btn.onclick = () => {
            const allBtns = optionsEl.querySelectorAll('button');
            allBtns.forEach(b => b.disabled = true);

            
            btn.style.transition = 'transform 0.25s ease, background 0.2s';
            btn.style.transform = 'rotateY(360deg)';
            setTimeout(() => { btn.style.transform = ''; }, 300);

            if (ans === questionData.correct_answer) {
                btn.classList.add('correct');
                btn.innerText += ' ✅ (Benar!)';

                
                setTimeout(triggerConfetti, 150);
            } else {
                btn.classList.add('wrong');
                btn.innerText += ' ❌';
                allBtns.forEach(b => {
                    if (b.innerText.startsWith(decodeHtml(questionData.correct_answer))) {
                        b.classList.add('correct');
                    }
                });
            }

            setTimeout(() => {
                const reloadBtn = document.createElement('button');
                reloadBtn.className = 'btn-trivia';
                reloadBtn.style.cssText = 'text-align:center; margin-top:10px; font-weight:bold;';
                reloadBtn.innerHTML = '<i class="fas fa-redo"></i> Soal Berikutnya';
                reloadBtn.onclick = () => loadTrivia();
                optionsEl.appendChild(reloadBtn);
            }, 1000);
        };

        optionsEl.appendChild(btn);
    });
}
