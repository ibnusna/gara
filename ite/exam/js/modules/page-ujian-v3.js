import { Store } from './store.js?v=garav7';
import { prepareQuestions, shuffleArray, getExamStats, formatSubmitData } from './exam-core.js?v=garav7';







const elements = {
    
    loadingOverlay: document.getElementById('loadingOverlay'),
    gatewayOverlay: document.getElementById('gatewayOverlay'),
    mainContainer: document.getElementById('mainExamContainer'),

    
    btnEnterFullscreen: document.getElementById('btnEnterFullscreen'),

    
    mapelName: document.getElementById('mapelName'),
    timerDisplay: document.getElementById('timerDisplay'),
    userName: document.getElementById('userName'),

    
    displayNoSoal: document.getElementById('displayNoSoal'),
    questionImageContainer: document.getElementById('questionImageContainer'),
    questionText: document.getElementById('questionText'),
    optionsContainer: document.getElementById('optionsContainer'),

    
    btnPrev: document.getElementById('btnPrev'),
    btnNext: document.getElementById('btnNext'),
    btnSubmit: document.getElementById('btnSubmit'),
    checkRagu: document.getElementById('checkRagu'),

    
    btnOpenSoalMobile: document.getElementById('btnOpenSoalMobile'),
    modalDaftarSoal: document.getElementById('modalDaftarSoal'),
    btnCloseModalSoal: document.getElementById('btnCloseModalSoal'),

    
    sidebarNavGrid: document.getElementById('sidebarNavGrid'),
    mobileNavGrid: document.getElementById('mobileNavGrid'),

    
    fontButtons: document.querySelectorAll('.btn-font'),

    
    btnToggleSidebar: document.getElementById('btnToggleSidebar')
};

let isSubmitting = false;





export const initUjianPage = async () => {
    if (!Store.isAuthenticated()) {
        window.location.href = (window.__EXAM_URLS__ && window.__EXAM_URLS__.login) ? window.__EXAM_URLS__.login : 'index.html';
        return;
    }

    const siswa = Store.getSiswa();
    const meta = Store.getUjianMeta();

    if (elements.userName) elements.userName.textContent = siswa["Nama Lengkap"] || "Peserta";
    if (elements.mapelName) elements.mapelName.textContent = meta.topik;

    let soalList = Store.getSoalList();

    toggleLayer('loading');

    
    if (soalList.length > 0) {
        const sample = soalList[0];
        if (!sample.id_soal || sample.assets === undefined) {
            console.warn("[AUTO-FIX] Stale Question Data detected (Missing ID or Assets). Forcing reload...");
            Store.clearAll(); 
            window.location.reload(); 
            return;
        }
    }

    
    if (soalList.length === 0) {
        try {
            if (typeof getSoalByKodeAkses !== 'function') throw new Error("Koneksi server terputus.");

            
            await new Promise(r => setTimeout(r, 800));

            const rawData = await getSoalByKodeAkses({ sheetSoal: meta.sheetSoal });
            console.log("[DEBUG] Raw Data from API:", rawData);

            if (!rawData || !rawData.soal || rawData.soal.length === 0) {
                console.error("[DEBUG] API returned empty questions!");
            }

            const formatted = prepareQuestions(rawData.soal);
            console.log("[DEBUG] Formatted Questions:", formatted);

            

            
            let shuffledQuestions = shuffleArray(formatted);

            
            
            shuffledQuestions = shuffledQuestions.map(soal => {
                if (soal.tipe === 'ISIAN' || soal.tipe === 'BENAR_SALAH') {
                    
                    return soal;
                }
                return {
                    ...soal,
                    pilihan: shuffleArray([...soal.pilihan]) 
                };
            });

            
            soalList = shuffledQuestions;
            Store.setSoalList(soalList);

            Store.initJawaban(soalList.length);
            Store.initRaguRagu(soalList.length);
            Store.setCurrentIndex(0);

        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal Memuat Soal',
                text: error.message,
                confirmButtonText: 'Kembali',
                confirmButtonColor: '#d33'
            }).then(() => window.location.href = (window.__EXAM_URLS__ && window.__EXAM_URLS__.login) ? window.__EXAM_URLS__.login : 'index.html');
            return;
        }
    }

    toggleLayer('gateway');
    setupGatewayEvents(meta.durasi);
};

const setupGatewayEvents = (durasiMenit) => {
    elements.btnEnterFullscreen.onclick = () => {
        requestFullScreenAndStart(durasiMenit);
    };
};

const requestFullScreenAndStart = async (durasiMenit) => {
    try {
        const docEl = document.documentElement;
        if (docEl.requestFullscreen) await docEl.requestFullscreen();
        else if (docEl.webkitRequestFullscreen) await docEl.webkitRequestFullscreen();
        else if (docEl.msRequestFullscreen) await docEl.msRequestFullscreen();
    } catch (err) {
        console.warn("Fullscreen skipped/failed:", err);
    }

    toggleLayer('main');
    setupTimer(durasiMenit);

    
    try {
        const siswa = Store.getSiswa();
        if (typeof MemoryModule !== 'undefined' && siswa) {
            MemoryModule.init(siswa["Nama Lengkap"], siswa["NIS"] || "000");
            let savedProgress = MemoryModule.loadProgress();

            
            if (!savedProgress || !savedProgress.jawabanSiswa || savedProgress.jawabanSiswa.every(j => j === null)) {
                console.log("[GARA Sync] Lokal kosong, mencoba mengambil draft dari server...");
                const apiUrl = window.__EXAM_API_URL__;
                const token = sessionStorage.getItem('exam_token');
                
                if (apiUrl && token) {
                    const formData = new FormData();
                    formData.append('action', 'get_draft');
                    formData.append('token', token);

                    const response = await fetch(apiUrl, { method: 'POST', body: formData });
                    const data = await response.json();
                    
                    if (data.success && data.data.jawaban) {
                        const serverJawaban = JSON.parse(data.data.jawaban);
                        if (Array.isArray(serverJawaban) && serverJawaban.length > 0) {
                            savedProgress = { jawabanSiswa: serverJawaban };
                            console.log("[GARA Sync] Progres berhasil dipulihkan dari server.");
                        }
                    }
                }
            }

            if (savedProgress && savedProgress.jawabanSiswa) {
                savedProgress.jawabanSiswa.forEach((ans, idx) => {
                    if (ans !== null && ans !== undefined) {
                        Store.saveJawaban(idx, ans);
                    }
                });

                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                });
                Toast.fire({
                    icon: 'success',
                    title: 'Progres ujian dipulihkan'
                });
            }
        }
    } catch (e) {
        console.warn("Recovery failed:", e);
    }
    

    renderCurrentSoal();
    renderNavigationGrids();
    attachExamEventListeners();

    
    setupNetworkListeners();

    if (typeof SecurityModule !== 'undefined') {
        SecurityModule.init(() => forceSubmit("Waktu Habis / Pelanggaran"));
    }
};





const renderCurrentSoal = (animate = true) => {
    const currentIndex = Store.getCurrentIndex();
    const soalList = Store.getSoalList();
    const currentSoal = soalList[currentIndex];

    if (!currentSoal) {
        console.error("Render Error: currentSoal is undefined", currentIndex, soalList);
        return;
    }

    console.log("Rendering Soal:", currentSoal);
    console.log("Pertanyaan Raw:", currentSoal.pertanyaan);
    console.log("Pilihan:", currentSoal.pilihan);

    
    elements.displayNoSoal.textContent = currentIndex + 1;

    let tipeLabel = "";
    if (currentSoal.tipe === "PG_KOMPLEKS") tipeLabel = `<span class="badge-tipe" style="font-size:0.7em; background:#e0f2fe; color:#0284c7; padding:2px 8px; border-radius:4px; margin-right:8px;">Pilih Lebih Dari Satu</span>`;
    if (currentSoal.tipe === "BENAR_SALAH") tipeLabel = `<span class="badge-tipe" style="font-size:0.7em; background:#f0fdf4; color:#16a34a; padding:2px 8px; border-radius:4px; margin-right:8px;">Tentukan Benar/Salah</span>`;
    if (currentSoal.tipe === "ISIAN") tipeLabel = `<span class="badge-tipe" style="font-size:0.7em; background:#fefce8; color:#ca8a04; padding:2px 8px; border-radius:4px; margin-right:8px;">Isian Angka</span>`;

    
    
    const cleanQuestionText = (text) => {
        if (!text) return "";
        let cleaned = text;
        const patterns = [
            /\[\s*TIPE\s*:\s*.*?\]/gi,
            /&#91;\s*TIPE\s*:\s*.*?&#93;/gi,
            /\[\s*(PG KOMPLEKS|PG_KOMPLEKS|PG|BENAR_SALAH|BENAR-SALAH|ISIAN)\s*\]/gi,
            /&#91;\s*(PG KOMPLEKS|PG_KOMPLEKS|PG|BENAR_SALAH|BENAR-SALAH|ISIAN)\s*&#93;/gi,
            /\[\s*TIPE\s+.*?\]/gi,
            /&#91;\s*TIPE\s+.*?&#93;/gi,
            /\[?TIPE:\s*(PG KOMPLEKS|PG_KOMPLEKS|PG|BENAR_SALAH|BENAR-SALAH|ISIAN)\]?/gi,
            /\[?TIPE\s+.*?\]?/gi
        ];
        patterns.forEach(pat => {
            cleaned = cleaned.replace(pat, '');
        });
        return cleaned.trim();
    };

    let cleanPertanyaan = cleanQuestionText(currentSoal.pertanyaan || "");

    
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = cleanPertanyaan;

    
    const links = tempDiv.querySelectorAll('a[href*="ibb.co.com"]');
    const processedIds = new Set();

    
    elements.questionImageContainer.innerHTML = '';
    
    let hasMedia = false;

    if (links.length > 0) {
        hasMedia = true;

        links.forEach(link => {
            const href = link.getAttribute('href');
            
            
            const parts = href.split('/').filter(p => p.trim() !== '');
            const imageId = parts[parts.length - 1];

            if (imageId && !processedIds.has(imageId)) {
                processedIds.add(imageId);

                
                const directLink = `https://i.ibb.co.com/${imageId}/cover.png`;

                
                const imgWrapper = document.createElement('div');
                imgWrapper.className = 'q-image-wrapper';

                const img = document.createElement('img');
                img.src = directLink;
                img.alt = "Gambar Soal";
                img.style.cursor = "zoom-in";
                img.onclick = () => window.openImageZoom(directLink);

                img.onerror = function () {
                    console.log('Failed loading image:', directLink);
                };

                imgWrapper.appendChild(img);
                elements.questionImageContainer.appendChild(imgWrapper);
            }

            
            link.remove();
        });

        
        cleanPertanyaan = tempDiv.innerHTML;
    }

    
    if (currentSoal.assets && currentSoal.assets.length > 0) {
        hasMedia = true;
        currentSoal.assets.forEach(asset => {
            const assetWrapper = document.createElement('div');
            assetWrapper.className = 'soal-asset-card';
            
            if (asset.asset_type === 'local_image' || asset.asset_type === 'external_image') {
                let src = asset.asset_source;
                if (asset.asset_type === 'local_image') {
                    src = src.startsWith('exam_assets/') ? window.BASE_URL + 'storage/' + src : window.BASE_URL + 'storage/exam_assets/' + src;
                }
                assetWrapper.innerHTML = `<img src="${src}" alt="Gambar Soal" style="cursor: zoom-in;" onclick="window.openImageZoom('${src}')">`;
            } else if (asset.asset_type === 'youtube_link') {
                assetWrapper.classList.add('embed-asset');
                let ytId = asset.asset_source.match(/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))([a-zA-Z0-9_-]{11})/);
                ytId = ytId ? ytId[1] : '';
                if(ytId) assetWrapper.innerHTML = `<iframe src="https://www.youtube.com/embed/${ytId}" frameborder="0" allowfullscreen></iframe>`;
            } else if (asset.asset_type === 'audio_mp3') {
                assetWrapper.classList.add('embed-asset');
                let src = asset.asset_source.startsWith('exam_assets/') ? window.BASE_URL + 'storage/' + asset.asset_source : window.BASE_URL + 'storage/exam_assets/' + asset.asset_source;
                assetWrapper.innerHTML = `<audio controls><source src="${src}" type="audio/mpeg"></audio>`;
            } else if (asset.asset_type === 'google_drive') {
                assetWrapper.innerHTML = `<a href="${asset.asset_source}" target="_blank" style="display:inline-block; padding:10px 16px; background:#f1f5f9; border-radius:8px; font-weight:600; text-decoration:none; color:#0b57d0;"><i class="fab fa-google-drive"></i> Buka Aset Google Drive</a>`;
            }
            
            elements.questionImageContainer.appendChild(assetWrapper);
        });
    }

    if (hasMedia) {
        elements.questionImageContainer.classList.remove('hidden');
    } else {
        elements.questionImageContainer.classList.add('hidden');
    }

    

    


    const renderPGBiasa = (soal, jawabanUser) => {
        const visualLabels = ["A", "B", "C", "D", "E"];

        soal.pilihan.forEach((opt, idx) => {
            const isSelected = jawabanUser === opt.key;
            const btn = document.createElement('div');
            btn.className = `option-card ${isSelected ? 'selected' : ''}`;

            
            btn.onclick = () => handleAnswerSimple(soal.originalIndex, opt.key);

            const labelText = visualLabels[idx] || String.fromCharCode(65 + idx);
            btn.innerHTML = `
            <div class="opt-label">${labelText}</div>
            <div class="opt-text">${opt.text}</div>
        `;
            elements.optionsContainer.appendChild(btn);
        });
    };

    


    const renderPGKompleks = (soal, jawabanUser) => {
        
        const currentAnswers = Array.isArray(jawabanUser) ? jawabanUser : [];

        soal.pilihan.forEach((opt) => {
            const isSelected = currentAnswers.includes(opt.key);

            const btn = document.createElement('div');
            
            btn.className = `option-card ${isSelected ? 'selected' : ''}`;
            btn.style.cursor = "pointer";

            btn.onclick = () => handleAnswerMulti(soal.originalIndex, opt.key);

            
            const icon = isSelected ? '<i class="fas fa-check-square"></i>' : '<i class="far fa-square"></i>';

            btn.innerHTML = `
            <div class="opt-label" style="background:none; border:none; color:inherit; font-size:1.2rem;">${icon}</div>
            <div class="opt-text">${opt.text}</div>
        `;
            elements.optionsContainer.appendChild(btn);
        });
    };

    


    const renderBenarSalah = (soal, jawabanUser) => {
        
        const currentAnswers = Array.isArray(jawabanUser) ? jawabanUser : new Array(soal.pilihan.length).fill(null);

        const container = document.createElement('div');
        container.className = 'bs-container';
        container.style.cssText = "display: flex; flex-direction: column; gap: 16px;";

        elements.optionsContainer.appendChild(container);

        soal.pilihan.forEach((opt, idx) => {
            const val = currentAnswers[idx];
            const groupName = `bs_row_${soal.originalIndex}_${idx}`;

            const card = document.createElement('div');
            card.className = 'bs-card-item';
            card.style.cssText = `
            background: #ffffff;
            border: 1px solid var(--color-border);
            border-radius: 12px;
            padding: 16px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.03);
            transition: transform 0.2s;
        `;

            const statement = document.createElement('div');
            statement.innerHTML = `<strong style="color:var(--color-primary); margin-right:5px;">${idx + 1}.</strong> ${opt.text}`;
            statement.style.marginBottom = "12px";
            statement.style.fontSize = "0.95rem";
            statement.style.lineHeight = "1.5";
            statement.style.color = "var(--color-text-main)";

            const btnGroup = document.createElement('div');
            btnGroup.className = 'bs-options';
            btnGroup.style.cssText = "display: grid; grid-template-columns: 1fr 1fr; gap: 10px;";

            const createButton = (label, value) => {
                const isChecked = val === value;
                const labelEl = document.createElement('label');

                
                let bg = isChecked ? (value === 'BENAR' ? '#dcfce7' : '#fee2e2') : '#f8fafc';
                let border = isChecked ? (value === 'BENAR' ? '#16a34a' : '#dc2626') : '#e2e8f0';
                let text = isChecked ? (value === 'BENAR' ? '#15803d' : '#b91c1c') : '#64748b';
                let weight = isChecked ? '700' : '500';

                labelEl.style.cssText = `
                display: flex; align-items: center; justify-content: center; gap: 8px;
                padding: 10px; border: 2px solid ${border}; background: ${bg}; color: ${text};
                font-weight: ${weight}; border-radius: 8px; cursor: pointer;
                transition: all 0.2s; user-select: none;
            `;

                const icon = value === 'BENAR' ? '<i class="fas fa-check"></i>' : '<i class="fas fa-times"></i>';
                labelEl.innerHTML = `${icon} <span>${label}</span>`;

                const input = document.createElement('input');
                input.type = 'radio';
                input.name = groupName;
                input.value = value;
                input.checked = isChecked;
                input.style.display = 'none';

                input.onclick = () => {
                    const buttonsInGroup = btnGroup.querySelectorAll('label');
                    buttonsInGroup.forEach(lbl => {
                        lbl.style.background = '#f8fafc';
                        lbl.style.borderColor = '#e2e8f0';
                        lbl.style.color = '#64748b';
                        lbl.style.fontWeight = '500';
                    });
                    labelEl.style.background = value === 'BENAR' ? '#dcfce7' : '#fee2e2';
                    labelEl.style.borderColor = value === 'BENAR' ? '#16a34a' : '#dc2626';
                    labelEl.style.color = value === 'BENAR' ? '#15803d' : '#b91c1c';
                    labelEl.style.fontWeight = '700';

                    handleAnswerBenarSalah(soal.originalIndex, idx, value);
                };

                labelEl.appendChild(input);
                return labelEl;
            };

            btnGroup.appendChild(createButton("BENAR", "BENAR"));
            btnGroup.appendChild(createButton("SALAH", "SALAH"));

            card.appendChild(statement);
            card.appendChild(btnGroup);
            container.appendChild(card);
        });
    };

    


    const renderIsian = (soal, jawabanUser) => {
        const wrapper = document.createElement('div');
        wrapper.className = "isian-container";

        const label = document.createElement('label');
        label.className = "isian-label";
        label.innerText = "Jawaban Anda (Angka):";

        const input = document.createElement('input');
        input.type = "number"; 
        input.className = "isian-input";
        input.placeholder = "Ketik angka di sini...";
        input.value = jawabanUser || "";

        
        let timeout = null;
        input.oninput = (e) => {
            const val = e.target.value;
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                handleAnswerSimple(soal.originalIndex, val);
            }, 300);
        };

        wrapper.appendChild(label);
        wrapper.appendChild(input);
        elements.optionsContainer.appendChild(wrapper);
    };

    
    
    if (animate) {
        elements.questionText.classList.remove('animate-enter');
        elements.optionsContainer.classList.remove('animate-enter-delay-1');

        
        void elements.questionText.offsetWidth;
    }

    elements.questionText.innerHTML = tipeLabel + cleanPertanyaan;
    
    
    const inlineImages = elements.questionText.querySelectorAll('img');
    inlineImages.forEach(img => {
        img.style.cursor = "zoom-in";
        img.onclick = () => {
            if (window.openImageZoom) window.openImageZoom(img.src);
        };
    });

    elements.optionsContainer.innerHTML = '';

    const jawabanUser = Store.getJawaban()[currentSoal.originalIndex];

    switch (currentSoal.tipe) {
        case 'PG_KOMPLEKS': renderPGKompleks(currentSoal, jawabanUser); break;
        case 'BENAR_SALAH': renderBenarSalah(currentSoal, jawabanUser); break;
        case 'ISIAN': renderIsian(currentSoal, jawabanUser); break;
        default: renderPGBiasa(currentSoal, jawabanUser); break;
    }

    
    const optionImages = elements.optionsContainer.querySelectorAll('img');
    optionImages.forEach(img => {
        img.style.cursor = "zoom-in";
        img.onclick = (e) => {
            e.stopPropagation();
            if (window.openImageZoom) window.openImageZoom(img.src);
        };
    });

    const raguStatus = Store.getRaguRagu();
    elements.checkRagu.checked = !!raguStatus[currentSoal.originalIndex];
    updateNavButtons(currentIndex, soalList.length);
    highlightActiveGrid(currentIndex);

    
    if (animate) {
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                elements.questionText.classList.add('animate-enter');
                elements.optionsContainer.classList.add('animate-enter-delay-1');
            });
        });
    }
};





const handleAnswerSimple = (originalIndex, value) => {
    Store.saveJawaban(originalIndex, value);

    
    if (typeof MemoryModule !== 'undefined') {
        MemoryModule.saveProgress(Store.getSoalList(), Store.getJawaban());
    }

    
    const currentSoal = Store.getSoalList()[Store.getCurrentIndex()];
    if (currentSoal.tipe !== 'ISIAN') {
        renderCurrentSoal(false); 
    }
    renderNavigationGrids();
};


const handleAnswerMulti = (originalIndex, key) => {
    let current = Store.getJawaban()[originalIndex];
    if (!Array.isArray(current)) current = [];

    
    if (current.includes(key)) {
        current = current.filter(k => k !== key); 
    } else {
        current.push(key); 
    }

    Store.saveJawaban(originalIndex, current);

    
    if (typeof MemoryModule !== 'undefined') {
        MemoryModule.saveProgress(Store.getSoalList(), Store.getJawaban());
    }

    renderCurrentSoal();
    renderNavigationGrids();
};



const handleAnswerBenarSalah = (originalIndex, rowIdx, value) => {
    const allAnswers = Store.getJawaban();
    let currentData = allAnswers[originalIndex];

    
    const targetSoal = Store.getSoalList().find(s => s.originalIndex === originalIndex);
    const totalRows = targetSoal ? targetSoal.pilihan.length : 0;

    
    if (!Array.isArray(currentData) || currentData.length !== totalRows) {
        currentData = new Array(totalRows).fill(null);
    }

    const newArray = [...currentData];
    newArray[rowIdx] = value; 

    Store.saveJawaban(originalIndex, newArray);

    
    if (typeof MemoryModule !== 'undefined') {
        MemoryModule.saveProgress(Store.getSoalList(), Store.getJawaban());
    }

    
    renderNavigationGrids();

    
    const currentSoal = Store.getSoalList()[Store.getCurrentIndex()];
    if (currentSoal && currentSoal.originalIndex === originalIndex) {
        updateBenarSalahVisual(rowIdx, value);
    }
};

const toggleRagu = (e) => {
    const currentSoal = Store.getSoalList()[Store.getCurrentIndex()];
    Store.setRaguRagu(currentSoal.originalIndex, e.target.checked);
    renderNavigationGrids();
};

const handleNav = (direction) => {
    const current = Store.getCurrentIndex();
    const total = Store.getSoalList().length;
    let next = current + direction;
    if (next >= 0 && next < total) {
        Store.setCurrentIndex(next);
        renderCurrentSoal();
    }
};


const getModeSubmit = () => {
    try {
        const meta = Store.getUjianMeta();
        return (meta && meta.modeSubmit) ? String(meta.modeSubmit).toUpperCase() : 'MANDIRI';
    } catch { return 'MANDIRI'; }
};

const updateNavButtons = (currentIndex, total) => {
    
    elements.btnPrev.disabled = currentIndex === 0;
    if (currentIndex === 0) elements.btnPrev.classList.add('opacity-50', 'cursor-not-allowed');
    else elements.btnPrev.classList.remove('opacity-50', 'cursor-not-allowed');

    const modeSubmit = getModeSubmit();

    
    if (modeSubmit === 'SERENTAK') {
        if (currentIndex === total - 1) {
            elements.btnNext.classList.add('hidden');
            elements.btnNext.style.display = 'none';

            
            const wrapRagu = document.querySelector('.ragu-checkbox-wrapper');
            if(wrapRagu) wrapRagu.style.display = 'none';

            
            let btnWait = document.getElementById('btnWaitSerentak');
            if (!btnWait) {
                btnWait = document.createElement('button');
                btnWait.id = 'btnWaitSerentak';
                btnWait.className = 'nav-btn';
                btnWait.style.backgroundColor = '#f3f4f6';
                btnWait.style.color = '#6b7280';
                btnWait.style.border = '2px dashed #d1d5db';
                btnWait.style.cursor = 'not-allowed';
                btnWait.innerHTML = '<i class="fas fa-clock"></i> <span class="btn-label" style="display:block!important; font-size:0.75rem; font-weight:bold; margin-top:2px;">Tunggu Waktu Habis</span>';
                elements.btnSubmit.parentNode.insertBefore(btnWait, elements.btnNext);
            }
            btnWait.style.display = 'flex';
            
            
            elements.btnSubmit.parentNode.style.gridTemplateColumns = '1fr 2fr';

            
            const existingNotice = document.getElementById('serentakNotice');
            if (existingNotice) existingNotice.remove();
        } else {
            elements.btnNext.classList.remove('hidden');
            elements.btnNext.style.display = 'flex';
            
            const wrapRagu = document.querySelector('.ragu-checkbox-wrapper');
            if(wrapRagu) wrapRagu.style.display = ''; 

            const btnWait = document.getElementById('btnWaitSerentak');
            if (btnWait) btnWait.style.display = 'none';
            
            
            elements.btnSubmit.parentNode.style.gridTemplateColumns = '';
        }
        
        elements.btnSubmit.classList.add('hidden');
        elements.btnSubmit.style.display = 'none';
        return;
    }

    
    const stats = getExamStats(Store.getJawaban(), Store.getRaguRagu());
    const isLastQuestion = currentIndex === total - 1;
    const isAllAnswered = stats.kosong === 0;

    if (isLastQuestion) {
        elements.btnNext.style.display = 'none';
        if (isAllAnswered) {
            elements.btnSubmit.classList.remove('hidden');
            elements.btnSubmit.style.display = 'flex';
        } else {
            elements.btnSubmit.classList.add('hidden');
            elements.btnSubmit.style.display = 'none';
        }
    } else {
        elements.btnNext.style.display = 'flex';
        elements.btnSubmit.classList.add('hidden');
        elements.btnSubmit.style.display = 'none';
    }
};





const renderNavigationGrids = () => {
    const soalList = Store.getSoalList();
    const jawaban = Store.getJawaban();
    const ragu = Store.getRaguRagu();

    const createGridItem = (idx, originalIdx) => {
        
        const val = jawaban[originalIdx];
        let isAnswered = false;

        if (Array.isArray(val)) isAnswered = val.length > 0; 
        else isAnswered = (val !== null && val !== "");      

        const isRagu = ragu[originalIdx];

        const btn = document.createElement('div');
        btn.textContent = idx + 1;
        btn.dataset.idx = idx;

        let cls = "nav-item-box";
        if (isRagu) cls += " ragu";
        else if (isAnswered) cls += " done";

        btn.className = cls;
        btn.onclick = () => {
            Store.setCurrentIndex(idx);
            renderCurrentSoal();
            closeMobileModal();
        };
        return btn;
    };

    elements.sidebarNavGrid.innerHTML = '';
    elements.mobileNavGrid.innerHTML = '';

    soalList.forEach((soal, idx) => {
        elements.sidebarNavGrid.appendChild(createGridItem(idx, soal.originalIndex));
        elements.mobileNavGrid.appendChild(createGridItem(idx, soal.originalIndex));
    });
};

const highlightActiveGrid = (currentIndex) => {
    document.querySelectorAll('.nav-item-box.active').forEach(el => el.classList.remove('active'));
    const targets = document.querySelectorAll(`.nav-item-box[data-idx="${currentIndex}"]`);
    targets.forEach(el => el.classList.add('active'));
};

const openMobileModal = () => {
    elements.modalDaftarSoal.classList.remove('hidden');
};

const closeMobileModal = () => {
    elements.modalDaftarSoal.classList.add('hidden');
};

const handleFontResize = (sizeClass) => {
    
    elements.questionText.classList.remove('text-sm', 'text-base', 'text-lg', 'text-xl');

    
    let newSize = '1rem'; 
    if (sizeClass.contains('sm')) newSize = '0.875rem';
    if (sizeClass.contains('lg')) newSize = '1.25rem';

    
    elements.questionText.style.fontSize = newSize;
    elements.optionsContainer.style.fontSize = newSize;
};

const handleSidebarToggle = () => {
    elements.mainContainer.classList.toggle('sidebar-closed');
    
};


const confirmSubmit = () => {
    const stats = getExamStats(Store.getJawaban(), Store.getRaguRagu());

    if (stats.kosong > 0) {
        Swal.fire({
            icon: 'error',
            title: 'Belum Selesai!',
            text: `Masih ada ${stats.kosong} soal yang belum dijawab.`
        });
        return;
    }

    let title = 'Konfirmasi Selesai';
    let htmlMsg = 'Apakah Anda yakin ingin mengakhiri ujian ini?';
    let iconType = 'question';
    let confirmColor = '#3085d6';

    if (stats.ragu > 0) {
        title = 'Masih Ragu-ragu';
        htmlMsg = `Masih ada <b>${stats.ragu} soal</b> bertanda kuning (Ragu-ragu).<br>Yakin ingin mengumpulkan?`;
        iconType = 'warning';
        confirmColor = '#fbbd05';
    }

    Swal.fire({
        title: title,
        html: htmlMsg,
        icon: iconType,
        showCancelButton: true,
        confirmButtonColor: confirmColor,
        cancelButtonColor: '#aaa',
        confirmButtonText: 'Ya, Selesai',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            processSubmit();
        }
    });
};

const forceSubmit = (reason) => {
    Swal.fire({
        title: 'Waktu Habis!',
        text: `${reason}. Jawaban Anda akan dikirim otomatis.`,
        icon: 'info',
        timer: 3000,
        showConfirmButton: false,
        allowOutsideClick: false
    }).then(() => {
        processSubmit();
    });
};

const processSubmit = async () => {
    if (isSubmitting) return;
    isSubmitting = true;

    Swal.fire({
        title: 'Mengirim Jawaban',
        text: 'Aplikasinya jangan ditutup yak',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    if (typeof stopTimer === 'function') stopTimer();
    if (typeof SecurityModule !== 'undefined') SecurityModule.destroy();

    const siswa = Store.getSiswa();
    const meta = Store.getUjianMeta();
    const jawaban = Store.getJawaban();
    const soalList = Store.getSoalList(); 
    const payload = formatSubmitData(siswa, meta, jawaban, soalList); 

    try {
        if (typeof submitJawaban !== 'function') throw new Error("API submitJawaban not found");

        const result = await submitJawaban(payload);

        Store.setHasil(result);
        Store.cleanupAfterSubmit();

        
        if (typeof MemoryModule !== 'undefined') {
            MemoryModule.clearProgress();
        }

        Swal.fire({
            icon: 'success',
            title: 'Terkirim!',
            text: 'Ujian telah berhasil diselesaikan.',
            timer: 1500,
            showConfirmButton: false
        }).then(() => {
            window.location.href = (window.__EXAM_URLS__ && window.__EXAM_URLS__.hasil) ? window.__EXAM_URLS__.hasil : 'hasil.html';
        });

    } catch (err) {
        isSubmitting = false;
        Swal.fire({
            icon: 'error',
            title: 'Gagal Mengirim',
            text: `Terjadi kesalahan: ${err.message}`,
            confirmButtonText: 'Coba Lagi',
            showCancelButton: true,
            cancelButtonText: 'Batal'
        }).then((result) => {
            
            if (result.isConfirmed) {
                processSubmit(); 
            }
        });
    }
};

const setupTimer = (durasiMenit) => {
    if (typeof startTimer === 'function') {
        startTimer(durasiMenit, elements.timerDisplay, () => forceSubmit("Waktu Habis"));
    }
};

const toggleLayer = (layerName) => {
    elements.loadingOverlay.classList.add('hidden');
    elements.gatewayOverlay.classList.add('hidden');
    elements.mainContainer.classList.add('hidden');

    if (layerName === 'loading') elements.loadingOverlay.classList.remove('hidden');
    if (layerName === 'gateway') elements.gatewayOverlay.classList.remove('hidden');
    if (layerName === 'main') elements.mainContainer.classList.remove('hidden');
};




const updateBenarSalahVisual = (rowIdx, value) => {
    
    const container = elements.optionsContainer.querySelector('.bs-container');
    if (!container) return;

    const cards = container.querySelectorAll('.bs-card-item');
    if (!cards[rowIdx]) return;

    const btnGroup = cards[rowIdx].querySelector('.bs-options');
    if (!btnGroup) return;

    const labels = btnGroup.querySelectorAll('label');

    
    labels.forEach(lbl => {
        lbl.style.background = '#f8fafc';
        lbl.style.borderColor = '#e2e8f0';
        lbl.style.color = '#64748b';
        lbl.style.fontWeight = '500';

        const input = lbl.querySelector('input');
        if (input) input.checked = false;
    });

    
    labels.forEach(lbl => {
        const input = lbl.querySelector('input');
        if (input && input.value === value) {
            input.checked = true;
            lbl.style.background = value === 'BENAR' ? '#dcfce7' : '#fee2e2';
            lbl.style.borderColor = value === 'BENAR' ? '#16a34a' : '#dc2626';
            lbl.style.color = value === 'BENAR' ? '#15803d' : '#b91c1c';
            lbl.style.fontWeight = '700';
        }
    });
};

const attachExamEventListeners = () => {
    elements.btnNext.onclick = () => handleNav(1);
    elements.btnPrev.onclick = () => handleNav(-1);
    elements.checkRagu.onchange = toggleRagu;
    elements.btnSubmit.onclick = confirmSubmit;

    elements.btnOpenSoalMobile.onclick = openMobileModal;
    elements.btnCloseModalSoal.onclick = closeMobileModal;
    elements.modalDaftarSoal.onclick = (e) => {
        if (e.target === elements.modalDaftarSoal) closeMobileModal();
    };

    elements.fontButtons.forEach(btn => {
        btn.onclick = function () {
            handleFontResize(this.classList);
        };
    });

    if (elements.btnToggleSidebar) {
        elements.btnToggleSidebar.onclick = handleSidebarToggle;
    }

    
    const btnCloseZoom = document.getElementById('btnCloseImageZoom');
    if(btnCloseZoom) btnCloseZoom.onclick = () => document.getElementById('modalImageZoom').classList.add('hidden');
    const backdropZoom = document.getElementById('backdropImageZoom');
    if(backdropZoom) backdropZoom.onclick = () => document.getElementById('modalImageZoom').classList.add('hidden');
};


window.openImageZoom = (src) => {
    console.log("Zooming image:", src);
    const modal = document.getElementById('modalImageZoom');
    const zoomedImage = document.getElementById('zoomedImage');
    if(modal && zoomedImage) {
        zoomedImage.src = src;
        modal.classList.remove('hidden');
        modal.style.display = 'flex'; 
    } else {
        alert("Gagal memuat modal zoom gambar.");
    }
};




const setupNetworkListeners = () => {
    
    































    console.log("Legacy Network Listener Disabled. Using Global Monitor.");
};
