




document.addEventListener('DOMContentLoaded', () => {
    
    let quizData = null;
    let currentQIndex = 0;
    let currentSlideIndex = 0; 

    
    let masteryQueue = [];
    let masteryScore = 0;

    
    let isAnswerRevealed = false;
    let timerInterval = null;
    let isGameRunning = false;

    
    const el = {
        screens: {
            opening: document.getElementById('screen-opening'),
            quiz: document.getElementById('screen-quiz'),
            exam: document.getElementById('screen-exam'),
            finish: document.getElementById('screen-finish')
        },
        audio: {
            opening: document.getElementById('audio-opening'),
            mainLoop: document.getElementById('bg-music'),
            sfxCorrect: document.getElementById('sfx-correct'),
            sfxWrong: document.getElementById('sfx-wrong'),
            sfxTransisi: document.getElementById('sfx-transisi'),
            bgm: []
        },
        btnStart: document.getElementById('btn-start'),
        quiz: {
            num: document.getElementById('q-number'),
            total: document.getElementById('q-total'),
            text: document.getElementById('q-text'),
            options: document.getElementById('q-options-container'),
            revealBox: document.getElementById('q-answer-reveal'),
            correctText: document.getElementById('q-correct-text'),
            timerBar: {
                container: document.getElementById('timer-bar-container'),
                fill: document.getElementById('timer-bar-fill')
            },
            masteryScore: document.getElementById('mastery-score'),
            feedbackBadge: document.getElementById('feedback-badge')
        },
        exam: {
            grid: document.getElementById('exam-grid-container'),
            timer: document.getElementById('exam-timer'),
            btnPrev: document.getElementById('exam-prev'),
            btnNext: document.getElementById('exam-next')
        },
        controls: {
            panel: document.querySelector('.control-panel'),
            next: document.getElementById('btn-next')
        }
    };

    
    for (let i = 1; i <= 5; i++) {
        const audioEl = document.getElementById(`bg-music-${i}`);
        if (audioEl) el.audio.bgm.push(audioEl);
    }

    
    async function init() {
        try {
            console.log("Initializing Quiz Engine V4...");
            const response = await fetch(`actions/get_quiz_data.php?id=${CONFIG.id}`);
            const data = await response.json();

            if (data.error) throw new Error(data.error);
            if (!data.questions || data.questions.length === 0) throw new Error("Tidak ada soal dalam kuis ini.");

            quizData = data;

            if (CONFIG.mode === 'quiz_mastery') {
                masteryQueue = [...quizData.questions];
                setupMasteryBGMOnly();

                
                const overlay = document.querySelector('.mastery-overlay');
                if (overlay) {
                    overlay.style.setProperty('pointer-events', 'none', 'important');
                    overlay.style.zIndex = '0'; 
                }
            }

            el.btnStart.addEventListener('click', handleStartClick);

            if (el.controls.next) el.controls.next.addEventListener('click', nextStep);

            
            if (el.exam.btnNext) el.exam.btnNext.addEventListener('click', () => navigateExamSlide(1));
            if (el.exam.btnPrev) el.exam.btnPrev.addEventListener('click', () => navigateExamSlide(-1));

            document.addEventListener('keydown', (e) => {
                if (isGameRunning && CONFIG.mode !== 'quiz_mastery') {
                    if (e.key === ' ' || e.key === 'ArrowRight') nextStep();
                }
            });

            
            window.handleStandardClick = handleStandardClick;

        } catch (error) {
            console.error(error);
            Swal.fire("Gagal", error.message, "error");
            el.btnStart.disabled = true;
            el.btnStart.innerText = "Error Memuat Data";
        }
    }

    
    function setupMasteryBGMOnly() {
        el.audio.bgm.forEach((audio, index) => {
            audio.addEventListener('ended', () => playNextBgm(index));
        });
    }

    function playNextBgm(currentIndex) {
        if (!isGameRunning) return;
        let nextIndex = currentIndex + 1;
        if (nextIndex >= el.audio.bgm.length) nextIndex = 0;

        const nextAudio = el.audio.bgm[nextIndex];
        if (nextAudio) {
            nextAudio.currentTime = 0;
            nextAudio.play().catch(e => console.warn("Autoplay BGM blocked:", e));
        }
    }

    
    function handleStartClick() {
        if (isGameRunning) return;
        toggleFullscreen(true);

        const btn = el.btnStart;

        if (CONFIG.mode === 'quiz_mastery') {
            const introAudio = el.audio.opening;

            const proceedToGame = () => {
                if (isGameRunning) return;
                if (introAudio) {
                    introAudio.pause();
                    introAudio.onended = null;
                    introAudio.onerror = null;
                }
                enterGameplay();
            };

            if (introAudio) {
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memutar Intro...';
                btn.disabled = true;

                introAudio.onended = proceedToGame;
                introAudio.onerror = () => proceedToGame();

                introAudio.play().then(() => {
                    btn.style.display = 'none';
                    const duration = introAudio.duration;
                    const safeWait = (isFinite(duration) && duration > 0) ? (duration * 1000) + 500 : 5000;
                    setTimeout(proceedToGame, safeWait);
                }).catch(() => proceedToGame());

            } else {
                proceedToGame();
            }
        } else {
            if (el.audio.mainLoop) el.audio.mainLoop.play().catch(() => { });
            enterGameplay();
        }
    }

    function enterGameplay() {
        console.log("ENTERING GAMEPLAY - STARTING CLEANUP");
        isGameRunning = true;

        
        
        el.screens.opening.style.setProperty('pointer-events', 'none', 'important');
        el.screens.opening.style.opacity = '0';
        el.screens.opening.style.transition = 'opacity 0.5s ease';

        
        setTimeout(() => {
            el.screens.opening.style.display = 'none';

            if (CONFIG.mode === 'ujian_essay') startExamMode();
            else startQuizMode();

            if (CONFIG.mode === 'quiz_mastery' && el.audio.bgm.length > 0) {
                const firstBgm = el.audio.bgm[0];
                if (firstBgm) {
                    firstBgm.currentTime = 0;
                    firstBgm.play().catch(e => console.warn("BGM Start fail:", e));
                }
            }
        }, 500);

        
        
        const cleanerInterval = setInterval(() => {
            if (!isGameRunning) { clearInterval(cleanerInterval); return; }

            if (el.screens.opening.style.display !== 'none') {
                console.warn("Force hiding stuck opening screen!");
                el.screens.opening.style.display = 'none';
                el.screens.opening.style.setProperty('pointer-events', 'none', 'important');
            }

            
            const overlay = document.querySelector('.mastery-overlay');
            if (overlay && overlay.style.pointerEvents !== 'none') {
                overlay.style.setProperty('pointer-events', 'none', 'important');
            }
        }, 1000);
    }

    function toggleFullscreen(enable) {
        if (enable && document.documentElement.requestFullscreen) document.documentElement.requestFullscreen().catch(() => { });
        else if (!enable && document.exitFullscreen) document.exitFullscreen().catch(() => { });
    }

    
    function startQuizMode() {
        el.screens.quiz.style.display = 'block';
        el.screens.quiz.classList.add('animate__animated', 'animate__fadeIn');

        if (CONFIG.mode !== 'quiz_mastery') el.controls.panel.style.display = 'block';
        loadQuestion();
    }

    function loadQuestion() {
        stopTimer();
        isAnswerRevealed = false;
        el.quiz.options.innerHTML = '';
        if (el.quiz.revealBox) el.quiz.revealBox.style.display = 'none';
        if (el.quiz.feedbackBadge) el.quiz.feedbackBadge.style.display = 'none';
        if (el.controls.next) el.controls.next.style.display = 'none'; 

        let currentQ = null;
        let displayIndex = 0;
        let totalCount = quizData.questions.length;

        
        if (CONFIG.mode === 'quiz_mastery') {
            if (masteryQueue.length === 0) { finishPresentation(); return; }
            currentQ = masteryQueue[0];
            displayIndex = masteryScore + 1;
            if (el.quiz.masteryScore) el.quiz.masteryScore.innerText = masteryScore;
        } else {
            if (currentQIndex >= totalCount) { finishPresentation(); return; }
            currentQ = quizData.questions[currentQIndex];
            displayIndex = currentQIndex + 1;
        }

        if (el.quiz.num) el.quiz.num.innerText = displayIndex;
        if (el.quiz.total) el.quiz.total.innerText = totalCount;

        
        el.quiz.text.innerHTML = currentQ.question.replace(/\n/g, '<br>');

        
        if (currentQ.type === 'pg') {
            const opts = ['A', 'B', 'C', 'D'];
            const optData = currentQ.options || {};

            opts.forEach((label, idx) => {
                if (optData[label]) {
                    const div = document.createElement('div');

                    if (CONFIG.mode === 'quiz_mastery') {
                        div.className = `opt-glass animate__animated animate__fadeInUp`;

                        
                        div.style.setProperty('cursor', 'pointer', 'important');
                        div.style.setProperty('z-index', '1000000', 'important'); 
                        div.style.setProperty('position', 'relative', 'important');
                        div.style.setProperty('pointer-events', 'auto', 'important');

                        div.style.animationDelay = `${idx * 0.1}s`;

                        
                        div.addEventListener('click', (e) => {
                            e.preventDefault();
                            e.stopPropagation(); 
                            console.log("Opsi diklik:", label);
                            handleMasteryAnswer(label, currentQ.answer);
                        });

                        div.innerHTML = `
                            <div class="opt-label">${label}</div>
                            <div class="opt-content">${optData[label]}</div>
                        `;
                    } else {
                        
                        div.className = 'col-md-6 mb-3';
                        div.innerHTML = `
                             <div class="card h-100 option-card shadow-sm" onclick="handleStandardClick(this, '${label}', '${currentQ.answer}')">
                                <div class="card-body d-flex align-items-center">
                                    <span class="option-label">${label}</span>
                                    <h5 class="m-0 font-weight-bold" style="flex:1;">${optData[label]}</h5>
                                </div>
                            </div>
                        `;
                    }
                    el.quiz.options.appendChild(div);
                }
            });
        }

        if (CONFIG.mode === 'quiz_interaktif' && CONFIG.timerPerSoal > 0) startTimer(CONFIG.timerPerSoal);
    }

    

    
    function handleStandardClick(element, selected, correct) {
        if (isAnswerRevealed) return; 

        
        stopTimer();

        isAnswerRevealed = true;

        
        const allCards = document.querySelectorAll('.option-card');

        
        if (selected === correct) {
            element.classList.add('selected-correct');
            
            if (el.audio.sfxCorrect) { el.audio.sfxCorrect.currentTime = 0; el.audio.sfxCorrect.play().catch(() => { }); }
        } else {
            element.classList.add('selected-wrong');
            if (el.audio.sfxWrong) { el.audio.sfxWrong.currentTime = 0; el.audio.sfxWrong.play().catch(() => { }); }
        }

        
        if (CONFIG.showAnswer === 'ya') {
            
            
            if (el.quiz.revealBox) {
                const qText = quizData.questions[currentQIndex].options[correct];
                el.quiz.correctText.innerText = `${correct}. ${qText}`;
                el.quiz.revealBox.style.display = 'block';
                el.quiz.revealBox.classList.add('animate__animated', 'animate__fadeIn');
            }
        }

        
        if (el.controls.next) {
            el.controls.next.style.display = 'block';
            el.controls.next.classList.add('animate__animated', 'animate__bounceIn');
        }
    }

    function handleMasteryAnswer(selected, correct) {
        if (isAnswerRevealed) return;
        isAnswerRevealed = true;

        const options = document.querySelectorAll('.opt-glass');
        let selectedEl = null;
        let correctEl = null;

        options.forEach(opt => {
            const labelText = opt.querySelector('.opt-label').innerText;
            if (labelText === selected) selectedEl = opt;
            if (labelText === correct) correctEl = opt;
            if (labelText !== selected && labelText !== correct) opt.classList.add('dimmed');
        });

        if (selected === correct) {
            
            if (el.audio.sfxCorrect) { el.audio.sfxCorrect.currentTime = 0; el.audio.sfxCorrect.play().catch(() => { }); }
            selectedEl.classList.add('correct');
            showFeedback("BENAR!", "correct");
            masteryScore++;
            masteryQueue.shift();
            setTimeout(() => { playTransitionSound(); loadQuestion(); }, 2000);
        } else {
            
            if (el.audio.sfxWrong) { el.audio.sfxWrong.currentTime = 0; el.audio.sfxWrong.play().catch(() => { }); }
            selectedEl.classList.add('wrong');
            if (correctEl) correctEl.classList.add('correct');
            showFeedback("SALAH!", "wrong");
            const recycled = masteryQueue.shift();
            masteryQueue.push(recycled);
            setTimeout(() => { playTransitionSound(); loadQuestion(); }, 3500);
        }
    }

    function playTransitionSound() {
        if (el.audio.sfxTransisi) { el.audio.sfxTransisi.currentTime = 0; el.audio.sfxTransisi.play().catch(() => { }); }
    }

    function showFeedback(text, type) {
        if (el.quiz.feedbackBadge) {
            el.quiz.feedbackBadge.innerText = text;
            el.quiz.feedbackBadge.className = `feedback-badge fb-${type}`;
            el.quiz.feedbackBadge.style.display = 'block';
        }
    }

    function startTimer(seconds) {
        let timeLeft = seconds;
        if (el.quiz.timerBar.container) el.quiz.timerBar.container.style.display = 'block';
        if (el.quiz.timerBar.fill) el.quiz.timerBar.fill.style.width = '100%';
        timerInterval = setInterval(() => {
            timeLeft--;
            const pct = (timeLeft / seconds) * 100;
            if (el.quiz.timerBar.fill) el.quiz.timerBar.fill.style.width = `${pct}%`;
            if (timeLeft <= 0) {
                stopTimer();
                
                
                if (el.controls.next) {
                    el.controls.next.style.display = 'block';
                }
            }
        }, 1000);
    }

    function stopTimer() { if (timerInterval) clearInterval(timerInterval); }

    function nextStep() {
        if (CONFIG.mode === 'quiz_mastery') return;

        currentQIndex++;
        loadQuestion();
    }

    
    function startExamMode() {
        el.screens.exam.style.display = 'block';
        renderExamSlide();
    }

    function renderExamSlide() {
        console.log("Attempting to render exam slide...");
        if (!quizData || !quizData.questions) {
            console.error("No quizData or questions found!");
            return;
        }

        const itemsPerPage = CONFIG.examItemsPerPage || 5;
        const start = currentSlideIndex * itemsPerPage;
        const end = start + itemsPerPage;

        if (el.exam.grid) el.exam.grid.innerHTML = '';
        const sliceData = quizData.questions.slice(start, end);

        
        if (sliceData.length === 0 && currentSlideIndex > 0) { currentSlideIndex--; renderExamSlide(); return; }

        if (sliceData.length === 0) {
            console.warn("Slice is empty! Showing alert.");
            el.exam.grid.innerHTML = '<div class="col-12 text-center"><div class="alert alert-warning">Tidak ada soal untuk ditampilkan di halaman ini.</div></div>';
            return;
        }

        console.log(`Rendering ${sliceData.length} items.`);

        sliceData.forEach((q, idx) => {
            const realNum = start + idx + 1;
            const col = document.createElement('div');
            
            col.className = 'card h-100 shadow-sm animate__animated animate__fadeIn'; 
            
            const qContent = q.question ? q.question.replace(/\n/g, '<br>') : '[Soal Kosong]';
            col.innerHTML = `
                <div class="card-header"><h5 class="m-0 font-weight-bold">Soal No. ${realNum}</h5></div>
                <div class="card-body"><div style="font-size: 1.2rem; line-height: 1.6;">${qContent}</div></div>
            `;
            el.exam.grid.appendChild(col);
        });

        const totalSlides = Math.ceil(quizData.questions.length / itemsPerPage);
        if (el.exam.btnPrev) el.exam.btnPrev.disabled = (currentSlideIndex === 0);

        if (currentSlideIndex >= totalSlides - 1) {
            el.exam.btnNext.innerHTML = '<i class="fas fa-flag-checkered"></i> Selesai';
            el.exam.btnNext.classList.remove('btn-primary');
            el.exam.btnNext.classList.add('btn-success');
            el.exam.btnNext.onclick = finishPresentation;
        } else {
            el.exam.btnNext.innerHTML = 'Next <i class="fas fa-chevron-right"></i>';
            el.exam.btnNext.classList.add('btn-primary');
            el.exam.btnNext.classList.remove('btn-success');
            el.exam.btnNext.onclick = () => navigateExamSlide(1);
        }
    }

    function navigateExamSlide(direction) {
        const itemsPerPage = CONFIG.examItemsPerPage || 5;
        const totalSlides = Math.ceil(quizData.questions.length / itemsPerPage);
        const newIndex = currentSlideIndex + direction;

        
        if (newIndex >= 0 && newIndex < totalSlides) {
            currentSlideIndex = newIndex;
            renderExamSlide();
            window.scrollTo(0, 0);
        }
    }

    function finishPresentation() {
        isGameRunning = false;
        toggleFullscreen(false);
        el.screens.quiz.style.display = 'none';
        el.screens.exam.style.display = 'none';
        el.screens.finish.style.display = 'flex';

        el.audio.bgm.forEach(a => { a.pause(); a.currentTime = 0; });
        if (el.audio.mainLoop) el.audio.mainLoop.pause();
    }

    init();
});