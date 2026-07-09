<?php
// GARA - Garuda Akademi
// File: student/ruang_fokus.php
// Konsep: Standalone App, tapi support partial render HTMX.

require_once '../actions/auth_helper.php';
requireSiswa();

// Logic Render:
// Jika AJAX request (HTMX) -> Render hanya konten #app-fokus
// Jika Direct Access -> Render Full HTML
?>

<?php if (!is_ajax_request()): ?>
<!doctype html>
<html lang="id" class="h-full">
 <head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ruang Fokus - Pomodoro Timer</title>
  <script src="/_sdk/element_sdk.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body { box-sizing: border-box; }
    .timer-ring { transform: rotate(-90deg); }
    .timer-progress { transition: stroke-dashoffset 1s linear; }
    .plant-container { transition: all 0.5s ease; }
    @view-transition { navigation: auto; }
  </style>
  <script src="/_sdk/data_sdk.js" type="text/javascript"></script>
 </head>
 <body class="h-full w-full overflow-auto">
<?php endif; ?>

  <!-- START CONTENT Partial -->
  <div id="app-fokus-wrapper" style="width: 100%; height: 100vh; overflow: auto; position: relative;">
      
      <!-- Back Button for SPA Experience -->
      <div style="position: absolute; top: 20px; left: 20px; z-index: 50;">
        <button class="bg-white/80 hover:bg-white text-blue-900 px-4 py-2 rounded-full shadow-sm font-semibold text-sm backdrop-blur-sm transition-all flex items-center gap-2"
           hx-get="dashboard.php" hx-target="#app-main" hx-push-url="true">
            <i class="fas fa-arrow-left"></i> Kembali
        </button> 
      </div>

      <!-- Main App Container -->
      <div id="app" class="min-h-full w-full bg-gradient-to-br from-blue-100 to-blue-300 flex flex-col items-center justify-center p-6">
       
       <!-- Brand -->
       <div class="text-center mb-4">
        <h2 class="text-2xl font-bold text-blue-900">Garuda Akademi</h2>
       </div>
       
       <!-- Header -->
       <header class="text-center mb-8">
        <h1 id="app-title" class="text-4xl font-bold text-blue-900 mb-2">Ruang Fokus</h1>
        <p class="text-blue-700">Bangun kebiasaan belajar dengan konsistensi</p>
       </header>
       
       <!-- Stats Bar -->
       <div class="flex gap-8 mb-8">
        <div class="text-center">
         <div class="flex items-center gap-2 text-orange-500"><i class="fas fa-fire text-2xl"></i> <span id="current-streak" class="text-3xl font-bold">0</span>
         </div>
         <p id="streak-label" class="text-sm text-blue-800 mt-1">Streak Hari</p>
        </div>
        <div class="text-center">
         <div class="flex items-center gap-2 text-green-500"><i class="fas fa-seedling text-2xl"></i> <span id="plant-level" class="text-3xl font-bold">1</span>
         </div>
         <p id="plant-level-label" class="text-sm text-blue-800 mt-1">Level Tanaman</p>
        </div>
       </div>
       
       <!-- Timer Circle -->
       <div class="relative mb-8">
        <svg width="280" height="280" class="timer-ring"><circle cx="140" cy="140" r="130" stroke="#bfdbfe" stroke-width="12" fill="none" /> <circle id="timer-progress" class="timer-progress" cx="140" cy="140" r="130" stroke="#2563eb" stroke-width="12" fill="none" stroke-dasharray="817" stroke-dashoffset="817" stroke-linecap="round" />
        </svg>
        <div class="absolute inset-0 flex flex-col items-center justify-center">
         <div id="timer-display" class="text-6xl font-bold text-blue-900 mb-2">
          25:00
         </div>
         <div id="timer-mode" class="text-lg text-blue-700">
          Fokus
         </div>
         <div id="session-count" class="text-sm text-blue-600 mt-2">
          Sesi: 0/4
         </div>
        </div>
       </div>
       
       <!-- Control Buttons -->
       <div class="flex gap-4 mb-8"><button id="start-btn" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg font-semibold flex items-center gap-2 transition-colors"> <i class="fas fa-play"></i> <span>Mulai</span> </button> <button id="pause-btn" class="bg-yellow-500 hover:bg-yellow-600 text-white px-8 py-3 rounded-lg font-semibold hidden items-center gap-2 transition-colors"> <i class="fas fa-pause"></i> <span>Jeda</span> </button> <button id="resume-btn" class="bg-blue-500 hover:bg-blue-600 text-white px-8 py-3 rounded-lg font-semibold hidden items-center gap-2 transition-colors"> <i class="fas fa-play"></i> <span>Lanjut</span> </button> <button id="reset-btn" class="bg-blue-400 hover:bg-blue-500 text-white px-6 py-3 rounded-lg font-semibold flex items-center gap-2 transition-colors"> <i class="fas fa-redo"></i> <span>Reset</span> </button> <button id="settings-btn" class="bg-blue-400 hover:bg-blue-500 text-white px-6 py-3 rounded-lg font-semibold flex items-center gap-2 transition-colors"> <i class="fas fa-cog"></i> </button>
       </div>
       
       <!-- Plant Visualization -->
       <div class="plant-container text-center">
        <div id="plant-visual" class="text-9xl mb-4"><!-- Plant SVG will be inserted here -->
        </div>
        <p id="plant-status" class="text-blue-700 font-medium">Tanaman Anda menunggu untuk tumbuh</p>
       </div>
       
       <!-- Stats Summary -->
       <div class="mt-8 bg-white rounded-lg shadow-md p-6 max-w-md w-full">
        <h3 class="text-lg font-bold text-blue-900 mb-4">Statistik</h3>
        <div class="space-y-2 text-sm">
         <div class="flex justify-between"><span class="text-blue-700">Total Sesi:</span> <span id="total-sessions" class="font-semibold text-blue-900">0</span>
         </div>
         <div class="flex justify-between"><span class="text-blue-700">Total Menit Fokus:</span> <span id="total-minutes" class="font-semibold text-blue-900">0</span>
         </div>
         <div class="flex justify-between"><span class="text-blue-700">Streak Terpanjang:</span> <span id="longest-streak" class="font-semibold text-blue-900">0 hari</span>
         </div>
         <div class="flex justify-between"><span class="text-blue-700">Poin Air:</span> <span id="water-points" class="font-semibold text-blue-900">0</span>
         </div>
        </div>
       </div>
      </div>

      <!-- INJECT DEPENDENCIES FOR AJAX MODE -->
      <!-- INJECT DEPENDENCIES FOR AJAX MODE -->
      <?php if(is_ajax_request()): ?>
        <!-- Re-inject Tailwind with timestamp to force re-execution -->
        <script src="https://cdn.tailwindcss.com?v=<?= time() ?>"></script>
        
        <!-- Inject SweetAlert2 (Missing previously) -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        
        <!-- Inject FontAwesome just in case header didn't load it or version mismatch -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

        <script src="/_sdk/element_sdk.js"></script>
        <script src="/_sdk/data_sdk.js"></script>
        
        <!-- Force Tailwind Re-scan (Workaround) -->
        <script>
            if (typeof tailwind !== 'undefined') {
                // Trigger a dummy config update to force re-scan if possible, 
                // or just rely on the new script tag above.
            }
        </script>
      <?php endif; ?>

      <script>
        // Init Logic (Wrapped in function to be calleable)
        function initRuangFokus() {
            // Check if dependencies loaded
            if (typeof Swal === 'undefined') {
                // simple loader if needed
            }

            // ... (Kode Javascript Asli di sini) ...
            
            // Default configuration
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

            // LocalStorage key
            const STORAGE_KEY = 'garuda_akademi_ruang_fokus';

            // Timer state
            let timerState = {
                mode: 'focus', // 'focus', 'shortBreak', 'longBreak'
                timeRemaining: 25 * 60,
                isRunning: false,
                isPaused: false,
                startTime: null,
                sessionCount: 0,
                currentSession: 0
            };

            // Plant levels with SVG representations
            const plantLevels = {
                1: { name: 'Bibit', svg: '<svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg"><circle cx="50" cy="80" r="15" fill="#8B4513"/><line x1="50" y1="65" x2="50" y2="50" stroke="#228B22" stroke-width="3"/><circle cx="50" cy="45" r="8" fill="#32CD32"/></svg>' },
                2: { name: 'Tunas', svg: '<svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg"><circle cx="50" cy="85" r="12" fill="#8B4513"/><line x1="50" y1="73" x2="50" y2="40" stroke="#228B22" stroke-width="4"/><ellipse cx="45" cy="35" rx="8" ry="12" fill="#32CD32"/><ellipse cx="55" cy="35" rx="8" ry="12" fill="#32CD32"/></svg>' },
                3: { name: 'Tanaman Kecil', svg: '<svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg"><ellipse cx="50" cy="90" rx="18" ry="8" fill="#8B4513"/><line x1="50" y1="82" x2="50" y2="30" stroke="#228B22" stroke-width="4"/><ellipse cx="35" cy="40" rx="12" ry="18" fill="#32CD32"/><ellipse cx="50" cy="25" rx="12" ry="18" fill="#32CD32"/><ellipse cx="65" cy="40" rx="12" ry="18" fill="#32CD32"/></svg>' },
                4: { name: 'Tanaman Dewasa', svg: '<svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg"><ellipse cx="50" cy="92" rx="20" ry="6" fill="#8B4513"/><line x1="50" y1="86" x2="50" y2="25" stroke="#228B22" stroke-width="5"/><circle cx="30" cy="35" r="15" fill="#32CD32"/><circle cx="50" cy="20" r="15" fill="#228B22"/><circle cx="70" cy="35" r="15" fill="#32CD32"/><circle cx="25" cy="55" r="12" fill="#90EE90"/><circle cx="75" cy="55" r="12" fill="#90EE90"/></svg>' },
                5: { name: 'Tanaman Subur', svg: '<svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg"><ellipse cx="50" cy="94" rx="22" ry="5" fill="#8B4513"/><line x1="50" y1="89" x2="50" y2="20" stroke="#228B22" stroke-width="6"/><circle cx="25" cy="30" r="16" fill="#32CD32"/><circle cx="50" cy="15" r="18" fill="#228B22"/><circle cx="75" cy="30" r="16" fill="#32CD32"/><circle cx="20" cy="50" r="14" fill="#90EE90"/><circle cx="80" cy="50" r="14" fill="#90EE90"/><circle cx="35" cy="65" r="12" fill="#98FB98"/><circle cx="65" cy="65" r="12" fill="#98FB98"/></svg>' }
            };

            // Load data from localStorage
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

            // Save data to localStorage
            function saveData(data) {
                localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
            }

            // Get today's date string
            function getTodayString() {
                return new Date().toISOString().split('T')[0];
            }

            // Check and update streak
            function checkStreak(data) {
                const today = getTodayString();
                const lastDate = data.streak.last_focus_date;
                
                if (!lastDate) return;
                
                const lastDateObj = new Date(lastDate);
                const todayObj = new Date(today);
                const diffDays = Math.floor((todayObj - lastDateObj) / (1000 * 60 * 60 * 24));
                
                if (diffDays > 1) {
                    // Streak broken
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

            // Update plant level based on water points
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

            // Complete a focus session
            function completeSession(data) {
                const today = getTodayString();
                
                // Update stats
                data.stats.total_sessions++;
                data.stats.total_focus_minutes += data.settings.focus_duration;
                
                // Update history
                if (!data.history[today]) {
                    data.history[today] = 0;
                }
                data.history[today]++;
                
                // Update water points
                data.plant.water_points++;
                
                // Update streak
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
                
                // Check if daily goal reached (4 sessions)
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

            // Update UI with current data
            function updateUI(data) {
                document.getElementById('current-streak').textContent = data.streak.current;
                document.getElementById('plant-level').textContent = data.plant.level;
                document.getElementById('total-sessions').textContent = data.stats.total_sessions;
                document.getElementById('total-minutes').textContent = data.stats.total_focus_minutes;
                document.getElementById('longest-streak').textContent = `${data.streak.longest} hari`;
                document.getElementById('water-points').textContent = data.plant.water_points;
                
                // Update plant visual
                const plantVisual = document.getElementById('plant-visual');
                plantVisual.innerHTML = plantLevels[data.plant.level].svg;
                
                const plantStatus = document.getElementById('plant-status');
                plantStatus.textContent = `${plantLevels[data.plant.level].name} - Level ${data.plant.level}`;
                
                // Update session count
                const today = getTodayString();
                const todaySessions = data.history[today] || 0;
                document.getElementById('session-count').textContent = `Sesi: ${todaySessions}/4`;
            }

            // Format time display
            function formatTime(seconds) {
                const mins = Math.floor(seconds / 60);
                const secs = seconds % 60;
                return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
            }

            // Update timer display
            function updateTimerDisplay() {
                document.getElementById('timer-display').textContent = formatTime(timerState.timeRemaining);
                
                // Update progress ring
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
                
                // Update mode display
                let modeText = 'Fokus';
                if (timerState.mode === 'shortBreak') modeText = 'Istirahat Pendek';
                if (timerState.mode === 'longBreak') modeText = 'Istirahat Panjang';
                document.getElementById('timer-mode').textContent = modeText;
            }

            // Timer tick
            function tick() {
                if (!timerState.isRunning || timerState.isPaused) return;
                
                timerState.timeRemaining--;
                updateTimerDisplay();
                
                if (timerState.timeRemaining <= 0) {
                    timerComplete();
                } else {
                    setTimeout(tick, 1000);
                }
            }

            // Timer complete
            function timerComplete() {
                timerState.isRunning = false;
                
                const data = loadData();
                
                if (timerState.mode === 'focus') {
                    completeSession(data);
                    timerState.currentSession++;
                    
                    // Switch to break
                    if (timerState.currentSession % 4 === 0) {
                    timerState.mode = 'longBreak';
                    timerState.timeRemaining = data.settings.long_break * 60;
                    } else {
                    timerState.mode = 'shortBreak';
                    timerState.timeRemaining = data.settings.short_break * 60;
                    }
                } else {
                    // Break complete, switch back to focus
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

            // Update button visibility
            function updateButtons() {
                const startBtn = document.getElementById('start-btn');
                const pauseBtn = document.getElementById('pause-btn');
                const resumeBtn = document.getElementById('resume-btn');
                
                if (!timerState.isRunning) {
                    startBtn.classList.remove('hidden');
                    startBtn.classList.add('flex');
                    pauseBtn.classList.add('hidden');
                    pauseBtn.classList.remove('flex');
                    resumeBtn.classList.add('hidden');
                    resumeBtn.classList.remove('flex');
                } else if (timerState.isPaused) {
                    startBtn.classList.add('hidden');
                    startBtn.classList.remove('flex');
                    pauseBtn.classList.add('hidden');
                    pauseBtn.classList.remove('flex');
                    resumeBtn.classList.remove('hidden');
                    resumeBtn.classList.add('flex');
                } else {
                    startBtn.classList.add('hidden');
                    startBtn.classList.remove('flex');
                    pauseBtn.classList.remove('hidden');
                    pauseBtn.classList.add('flex');
                    resumeBtn.classList.add('hidden');
                    resumeBtn.classList.remove('flex');
                }
            }

            // Start timer
            function startTimer() {
                if (timerState.isRunning) return;
                
                timerState.isRunning = true;
                timerState.isPaused = false;
                timerState.startTime = Date.now();
                
                Swal.fire({
                    title: 'Sesi Dimulai',
                    text: 'Fokus pada pekerjaan Anda.',
                    icon: 'info',
                    timer: 2000,
                    showConfirmButton: false
                });
                
                updateButtons();
                tick();
            }

            // Pause timer
            function pauseTimer() {
                timerState.isPaused = true;
                updateButtons();
            }

            // Resume timer
            function resumeTimer() {
                timerState.isPaused = false;
                updateButtons();
                tick();
            }

            // Reset timer
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

            // Show settings dialog
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

            // Event listeners
            document.getElementById('start-btn').addEventListener('click', startTimer);
            document.getElementById('pause-btn').addEventListener('click', pauseTimer);
            document.getElementById('resume-btn').addEventListener('click', resumeTimer);
            document.getElementById('reset-btn').addEventListener('click', resetTimer);
            document.getElementById('settings-btn').addEventListener('click', showSettings);

            // Initialize
            function init() {
                const data = loadData();
                checkStreak(data);
                timerState.timeRemaining = data.settings.focus_duration * 60;
                updateTimerDisplay();
                updateUI(data);
                updateButtons();
            }

            // Start the app
            init();
        }
        
        // Auto Start
        initRuangFokus();
      </script>
  </div>
  <!-- END CONTENT -->

<?php if (!is_ajax_request()): ?>
 </body>
</html>
<?php endif; ?>