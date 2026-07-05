@extends('layouts.siswa')

@section('title', 'Ruang Fokus | GARA')

@push('css')
    
    <link rel="stylesheet" href="{{ asset('assets/student/css/fokus.css') }}">
@endpush

@section('content')

    
    <div id="app-fokus-wrapper">

        
        <a href="{{ route('student.dashboard') }}" class="fokus-back-btn" hx-boost="false">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>

        
        <div id="app">

            
            <div class="fokus-brand">
                <h2>Garuda Akademi</h2>
            </div>

            
            <header class="fokus-header">
                <h1 id="app-title">Ruang Fokus</h1>
                <p>Bangun kebiasaan belajar dengan konsistensi</p>
            </header>

            
            <div class="fokus-stats-bar">
                <div class="fokus-stat">
                    <div class="fokus-stat-value">
                        <i class="fas fa-fire fire-icon"></i>
                        <span id="current-streak" class="fokus-stat-number">0</span>
                    </div>
                    <p id="streak-label" class="fokus-stat-label">Streak Hari</p>
                </div>
                <div class="fokus-stat">
                    <div class="fokus-stat-value">
                        <i class="fas fa-seedling plant-icon"></i>
                        <span id="plant-level" class="fokus-stat-number">1</span>
                    </div>
                    <p id="plant-level-label" class="fokus-stat-label">Level Tanaman</p>
                </div>
            </div>

            
            <div class="fokus-timer-container">
                <svg width="280" height="280" class="timer-ring">
                    <circle cx="140" cy="140" r="130" stroke="#bfdbfe" stroke-width="12" fill="none" />
                    <circle id="timer-progress" class="timer-progress" cx="140" cy="140" r="130" stroke="#2563eb"
                        stroke-width="12" fill="none" stroke-dasharray="817" stroke-dashoffset="817"
                        stroke-linecap="round" />
                </svg>
                <div class="fokus-timer-overlay">
                    <div id="timer-display">25:00</div>
                    <div id="timer-mode">Fokus</div>
                    <div id="session-count">Sesi: 0/4</div>
                </div>
            </div>

            
            <div class="fokus-controls">
                <button id="start-btn" class="btn-fokus btn-fokus-start">
                    <i class="fas fa-play"></i> <span>Mulai</span>
                </button>
                <button id="pause-btn" class="btn-fokus btn-fokus-pause fokus-hidden">
                    <i class="fas fa-pause"></i> <span>Jeda</span>
                </button>
                <button id="resume-btn" class="btn-fokus btn-fokus-resume fokus-hidden">
                    <i class="fas fa-play"></i> <span>Lanjut</span>
                </button>
                <button id="reset-btn" class="btn-fokus btn-fokus-reset">
                    <i class="fas fa-redo"></i> <span>Reset</span>
                </button>
                <button id="settings-btn" class="btn-fokus btn-fokus-settings">
                    <i class="fas fa-cog"></i>
                </button>
            </div>

            
            <div class="plant-container">
                <div id="plant-visual">
                    
                </div>
                <p id="plant-status">Tanaman Anda menunggu untuk tumbuh</p>
            </div>

            
            <div class="fokus-stats-card">
                <h3>Statistik</h3>
                <div>
                    <div class="fokus-stat-row"><span>Total Sesi:</span> <span id="total-sessions">0</span></div>
                    <div class="fokus-stat-row"><span>Total Menit Fokus:</span> <span id="total-minutes">0</span></div>
                    <div class="fokus-stat-row"><span>Streak Terpanjang:</span> <span id="longest-streak">0 hari</span></div>
                    <div class="fokus-stat-row"><span>Poin Air:</span> <span id="water-points">0</span></div>
                </div>
            </div>
        </div>
    </div>
    
@endsection

@push('js')
    <script>
        
        function initRuangFokus() {
            
            const defaultConfig = {
                app_title: "Ruang Fokus",
                streak_label: "Streak Hari",
                plant_level_label: "Level Tanaman",
                start_button_text: "Mulai",
                pause_button_text: "Jeda",
                resume_button_text: "Lanjut",
                reset_button_text: "Reset",
                background_color: "#dbeafe",
                surface_color: "#ffffff",
                text_color: "#1e3a8a",
                primary_action_color: "#2563eb",
                secondary_action_color: "#60a5fa"
            };

            
            const STORAGE_KEY = 'garuda_akademi_ruang_fokus';

            
            let timerState = {
                mode: 'focus', 
                timeRemaining: 25 * 60,
                isRunning: false,
                isPaused: false,
                startTime: null,
                sessionCount: 0,
                currentSession: 0
            };

            
            const plantLevels = {
                1: { name: 'Bibit', svg: '<svg viewBox="0 0 100 100" style="height:100%; width:100%" xmlns="http://www.w3.org/2000/svg"><circle cx="50" cy="80" r="15" fill="#8B4513"/><line x1="50" y1="65" x2="50" y2="50" stroke="#228B22" stroke-width="3"/><circle cx="50" cy="45" r="8" fill="#32CD32"/></svg>' },
                2: { name: 'Tunas', svg: '<svg viewBox="0 0 100 100" style="height:100%; width:100%" xmlns="http://www.w3.org/2000/svg"><circle cx="50" cy="85" r="12" fill="#8B4513"/><line x1="50" y1="73" x2="50" y2="40" stroke="#228B22" stroke-width="4"/><ellipse cx="45" cy="35" rx="8" ry="12" fill="#32CD32"/><ellipse cx="55" cy="35" rx="8" ry="12" fill="#32CD32"/></svg>' },
                3: { name: 'Tanaman Kecil', svg: '<svg viewBox="0 0 100 100" style="height:100%; width:100%" xmlns="http://www.w3.org/2000/svg"><ellipse cx="50" cy="90" rx="18" ry="8" fill="#8B4513"/><line x1="50" y1="82" x2="50" y2="30" stroke="#228B22" stroke-width="4"/><ellipse cx="35" cy="40" rx="12" ry="18" fill="#32CD32"/><ellipse cx="50" cy="25" rx="12" ry="18" fill="#32CD32"/><ellipse cx="65" cy="40" rx="12" ry="18" fill="#32CD32"/></svg>' },
                4: { name: 'Tanaman Dewasa', svg: '<svg viewBox="0 0 100 100" style="height:100%; width:100%" xmlns="http://www.w3.org/2000/svg"><ellipse cx="50" cy="92" rx="20" ry="6" fill="#8B4513"/><line x1="50" y1="86" x2="50" y2="25" stroke="#228B22" stroke-width="5"/><circle cx="30" cy="35" r="15" fill="#32CD32"/><circle cx="50" cy="20" r="15" fill="#228B22"/><circle cx="70" cy="35" r="15" fill="#32CD32"/><circle cx="25" cy="55" r="12" fill="#90EE90"/><circle cx="75" cy="55" r="12" fill="#90EE90"/></svg>' },
                5: { name: 'Tanaman Subur', svg: '<svg viewBox="0 0 100 100" style="height:100%; width:100%" xmlns="http://www.w3.org/2000/svg"><ellipse cx="50" cy="94" rx="22" ry="5" fill="#8B4513"/><line x1="50" y1="89" x2="50" y2="20" stroke="#228B22" stroke-width="6"/><circle cx="25" cy="30" r="16" fill="#32CD32"/><circle cx="50" cy="15" r="18" fill="#228B22"/><circle cx="75" cy="30" r="16" fill="#32CD32"/><circle cx="20" cy="50" r="14" fill="#90EE90"/><circle cx="80" cy="50" r="14" fill="#90EE90"/><circle cx="35" cy="65" r="12" fill="#98FB98"/><circle cx="65" cy="65" r="12" fill="#98FB98"/></svg>' }
            };

            
            function loadData() {
                const saved = localStorage.getItem(STORAGE_KEY);
                if (saved) {
                    return JSON.parse(saved);
                }
                return {
                    settings: {
                        focus_duration: 25,
                        short_break: 5,
                        long_break: 15
                    },
                    stats: {
                        total_sessions: 0,
                        total_focus_minutes: 0
                    },
                    streak: {
                        current: 0,
                        longest: 0,
                        last_focus_date: null
                    },
                    plant: {
                        level: 1,
                        water_points: 0
                    },
                    history: {}
                };
            }

            
            function saveData(data) {
                localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
            }

            
            function getTodayString() {
                return new Date().toISOString().split('T')[0];
            }

            
            function checkStreak(data) {
                const today = getTodayString();
                const lastDate = data.streak.last_focus_date;

                if (!lastDate) return;

                const lastDateObj = new Date(lastDate);
                const todayObj = new Date(today);
                const diffDays = Math.floor((todayObj - lastDateObj) / (1000 * 60 * 60 * 24));

                if (diffDays > 1) {
                    
                    Swal.fire({
                        title: 'Streak Terputus',
                        text: 'Tidak ada sesi fokus kemarin. Tanaman menurun satu tingkat.',
                        icon: 'warning',
                        confirmButtonColor: '#10b981'
                    });

                    data.streak.current = 0;
                    if (data.plant.level > 1) {
                        data.plant.level--;
                    }
                    saveData(data);
                    updateUI(data);
                }
            }

            
            function updatePlantLevel(data) {
                const points = data.plant.water_points;
                let newLevel = 1;

                if (points >= 40) newLevel = 5;
                else if (points >= 25) newLevel = 4;
                else if (points >= 15) newLevel = 3;
                else if (points >= 8) newLevel = 2;

                if (newLevel > data.plant.level) {
                    data.plant.level = newLevel;
                    data.plant.level = newLevel;
                    Swal.fire({
                        title: 'Tanaman Tumbuh!',
                        text: `Tanaman Anda naik ke level ${newLevel}: ${plantLevels[newLevel].name}`,
                        icon: 'success',
                        confirmButtonColor: '#10b981'
                    });
                }
            }

            
            function completeSession(data) {
                const today = getTodayString();

                
                data.stats.total_sessions++;
                data.stats.total_focus_minutes += data.settings.focus_duration;

                
                if (!data.history[today]) {
                    data.history[today] = 0;
                }
                data.history[today]++;

                
                data.plant.water_points++;

                
                if (data.streak.last_focus_date !== today) {
                    const yesterday = new Date();
                    yesterday.setDate(yesterday.getDate() - 1);
                    const yesterdayString = yesterday.toISOString().split('T')[0];

                    if (data.streak.last_focus_date === yesterdayString) {
                        data.streak.current++;
                    } else if (!data.streak.last_focus_date) {
                        data.streak.current = 1;
                    } else {
                        data.streak.current = 1;
                    }

                    data.streak.last_focus_date = today;

                    if (data.streak.current > data.streak.longest) {
                        data.streak.longest = data.streak.current;
                    }
                }

                
                if (data.history[today] >= 4 && data.history[today] % 4 === 0) {
                    Swal.fire({
                        title: 'Streak Harian Tercapai!',
                        text: 'Anda telah menyelesaikan 4 sesi hari ini.',
                        icon: 'success',
                        confirmButtonColor: '#10b981'
                    });
                }

                updatePlantLevel(data);
                saveData(data);

                Swal.fire({
                    title: 'Sesi Fokus Selesai',
                    text: 'Tanaman Anda bertumbuh.',
                    icon: 'success',
                    confirmButtonColor: '#10b981'
                });
            }

            
            function updateUI(data) {
                document.getElementById('current-streak').textContent = data.streak.current;
                document.getElementById('plant-level').textContent = data.plant.level;
                document.getElementById('total-sessions').textContent = data.stats.total_sessions;
                document.getElementById('total-minutes').textContent = data.stats.total_focus_minutes;
                document.getElementById('longest-streak').textContent = `${data.streak.longest} hari`;
                document.getElementById('water-points').textContent = data.plant.water_points;

                
                const plantVisual = document.getElementById('plant-visual');
                plantVisual.innerHTML = plantLevels[data.plant.level].svg;

                const plantStatus = document.getElementById('plant-status');
                plantStatus.textContent = `${plantLevels[data.plant.level].name} - Level ${data.plant.level}`;

                
                const today = getTodayString();
                const todaySessions = data.history[today] || 0;
                document.getElementById('session-count').textContent = `Sesi: ${todaySessions}/4`;
            }

            
            function formatTime(seconds) {
                const mins = Math.floor(seconds / 60);
                const secs = seconds % 60;
                return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
            }

            
            function updateTimerDisplay() {
                document.getElementById('timer-display').textContent = formatTime(timerState.timeRemaining);

                
                const data = loadData();
                let totalTime;
                if (timerState.mode === 'focus') {
                    totalTime = data.settings.focus_duration * 60;
                } else if (timerState.mode === 'shortBreak') {
                    totalTime = data.settings.short_break * 60;
                } else {
                    totalTime = data.settings.long_break * 60;
                }

                const progress = (totalTime - timerState.timeRemaining) / totalTime;
                const circumference = 2 * Math.PI * 130;
                const offset = circumference * (1 - progress);
                document.getElementById('timer-progress').style.strokeDashoffset = offset;

                
                let modeText = 'Fokus';
                if (timerState.mode === 'shortBreak') modeText = 'Istirahat Pendek';
                if (timerState.mode === 'longBreak') modeText = 'Istirahat Panjang';
                document.getElementById('timer-mode').textContent = modeText;
            }

            
            function tick() {
                
                if (window._garaFokusShouldStop) {
                    timerState.isRunning = false;
                    timerState.isPaused = true;
                    return;
                }

                if (!timerState.isRunning || timerState.isPaused) return;

                timerState.timeRemaining--;
                updateTimerDisplay();

                if (timerState.timeRemaining <= 0) {
                    timerComplete();
                } else {
                    setTimeout(tick, 1000);
                }
            }

            
            function timerComplete() {
                timerState.isRunning = false;

                const data = loadData();

                if (timerState.mode === 'focus') {
                    completeSession(data);
                    timerState.currentSession++;

                    
                    if (timerState.currentSession % 4 === 0) {
                        timerState.mode = 'longBreak';
                        timerState.timeRemaining = data.settings.long_break * 60;
                    } else {
                        timerState.mode = 'shortBreak';
                        timerState.timeRemaining = data.settings.short_break * 60;
                    }
                } else {
                    
                    timerState.mode = 'focus';
                    timerState.timeRemaining = data.settings.focus_duration * 60;

                    Swal.fire({
                        title: 'Istirahat Selesai',
                        text: 'Siap untuk sesi fokus berikutnya?',
                        icon: 'info',
                        confirmButtonColor: '#10b981'
                    });
                }

                updateTimerDisplay();
                updateUI(data);
                updateButtons();
            }

            
            function updateButtons() {
                const startBtn = document.getElementById('start-btn');
                const pauseBtn = document.getElementById('pause-btn');
                const resumeBtn = document.getElementById('resume-btn');

                if (!timerState.isRunning) {
                    startBtn.classList.remove('fokus-hidden');
                    pauseBtn.classList.add('fokus-hidden');
                    resumeBtn.classList.add('fokus-hidden');
                } else if (timerState.isPaused) {
                    startBtn.classList.add('fokus-hidden');
                    pauseBtn.classList.add('fokus-hidden');
                    resumeBtn.classList.remove('fokus-hidden');
                } else {
                    startBtn.classList.add('fokus-hidden');
                    pauseBtn.classList.remove('fokus-hidden');
                    resumeBtn.classList.add('fokus-hidden');
                }
            }

            
            function startTimer() {
                if (timerState.isRunning) return;

                timerState.isRunning = true;
                timerState.isPaused = false;
                timerState.startTime = Date.now();

                
                
                
                
                
                
                
                

                updateButtons();
                tick();
            }

            
            function pauseTimer() {
                timerState.isPaused = true;
                updateButtons();
            }

            
            function resumeTimer() {
                timerState.isPaused = false;
                updateButtons();
                tick();
            }

            
            async function resetTimer() {
                const result = await Swal.fire({
                    title: 'Reset Timer?',
                    text: 'Progres sesi ini akan hilang.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Ya, Reset',
                    cancelButtonText: 'Batal'
                });

                if (result.isConfirmed) {
                    timerState.isRunning = false;
                    timerState.isPaused = false;
                    timerState.mode = 'focus';

                    const data = loadData();
                    timerState.timeRemaining = data.settings.focus_duration * 60;

                    updateTimerDisplay();
                    updateButtons();
                }
            }

            
            async function showSettings() {
                const data = loadData();

                const { value: formValues } = await Swal.fire({
                    title: 'Pengaturan Timer',
                    html: `
                    <div style="text-align: left;">
                        <label style="display: block; margin-bottom: 10px;">
                        <strong>Durasi Fokus (menit):</strong>
                        <input id="focus-duration" type="number" value="${data.settings.focus_duration}" 
                                min="1" max="60" class="swal2-input" style="width: 100%;">
                        </label>
                        <label style="display: block; margin-bottom: 10px;">
                        <strong>Istirahat Pendek (menit):</strong>
                        <input id="short-break" type="number" value="${data.settings.short_break}" 
                                min="1" max="30" class="swal2-input" style="width: 100%;">
                        </label>
                        <label style="display: block; margin-bottom: 10px;">
                        <strong>Istirahat Panjang (menit):</strong>
                        <input id="long-break" type="number" value="${data.settings.long_break}" 
                                min="1" max="60" class="swal2-input" style="width: 100%;">
                        </label>
                    </div>
                    `,
                    focusConfirm: false,
                    showCancelButton: true,
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Simpan',
                    cancelButtonText: 'Batal',
                    preConfirm: () => {
                        return {
                            focus: parseInt(document.getElementById('focus-duration').value),
                            shortBreak: parseInt(document.getElementById('short-break').value),
                            longBreak: parseInt(document.getElementById('long-break').value)
                        };
                    }
                });

                if (formValues) {
                    data.settings.focus_duration = formValues.focus;
                    data.settings.short_break = formValues.shortBreak;
                    data.settings.long_break = formValues.longBreak;
                    saveData(data);

                    if (!timerState.isRunning) {
                        timerState.timeRemaining = data.settings.focus_duration * 60;
                        updateTimerDisplay();
                    }

                    Swal.fire({
                        title: 'Tersimpan',
                        text: 'Pengaturan berhasil diperbarui.',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            }

            
            document.getElementById('start-btn').addEventListener('click', startTimer);
            document.getElementById('pause-btn').addEventListener('click', pauseTimer);
            document.getElementById('resume-btn').addEventListener('click', resumeTimer);
            document.getElementById('reset-btn').addEventListener('click', resetTimer);
            document.getElementById('settings-btn').addEventListener('click', showSettings);

            
            function init() {
                const data = loadData();
                checkStreak(data);
                timerState.timeRemaining = data.settings.focus_duration * 60;
                updateTimerDisplay();
                updateUI(data);
                updateButtons();
            }

            
            init();
        }

        
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initRuangFokus);
        } else {
            initRuangFokus();
        }

        
        document.body.addEventListener('htmx:afterSwap', function (event) {
            if (document.getElementById('app-fokus-wrapper')) {
                initRuangFokus();
            }
        });

    </script>
@endpush