import { Store } from './store.js';
import { generateExplanation } from '../api.js';
import { analyzeExamOverall } from '../api_analisis.js';







const getElements = () => ({
    nama: document.getElementById("hasilNama"),
    kelas: document.getElementById("hasilKelas"),
    mapel: document.getElementById("hasilMapel"),
    nilai: document.getElementById("nilaiAkhir"),
    benar: document.getElementById("jawabanBenar"),
    salah: document.getElementById("jawabanSalah"),
    btnDownload: document.getElementById("downloadBuktiBtn"),
    btnSelesai: document.getElementById("selesaiBtn"),
    kkmBadge: document.getElementById("kkmStatusBadge")
});



export const initHasilPage = async () => {
    
    const siswa = Store.getSiswa();
    const ujian = Store.getUjianMeta();
    const hasil = Store.getHasil();

    
    if (!siswa || !ujian || !hasil) {
        window.location.href = getExitUrl();
        return;
    }

    
    
    
    try {
        const token = localStorage.getItem('exam_token');
        const nis = siswa.NIS || Store.getSavedNIS() || '1';
        if (token && nis) {
            const freshRes = await window.apiCall("check_token", { token: token, nis: nis });
            if (freshRes && freshRes.ujian) {
                
                Object.assign(ujian, freshRes.ujian);
                Store.setUjianMeta(ujian); 
                console.log("[SUPER PATCH] Fetched fresh ujianMeta from server:", ujian);
            }
        }
    } catch (e) {
        console.warn("[SUPER PATCH] Failed to fetch fresh metadata, falling back to cache.", e);
    }
    

    
    
    
    
    if (hasil.score !== undefined) {
        hasil.nilai = hasil.score;
    }
    

    
    
    const rawNilai = parseFloat(hasil.score ?? hasil.nilai ?? 0);
    hasil.nilai = parseFloat(rawNilai.toFixed(1)); 

    hasil.benar = parseInt(hasil.benar || 0);
    hasil.salah = parseInt(hasil.salah || 0);

    console.log("Using Server Result:", hasil);

    
    Store.setHasil(hasil);
    

    renderResultData(siswa, ujian, hasil);

    
    setupDownloadButton(ujian);

    
    setupFinishButton(ujian.pengulangan);

    
    const showNilai = String(ujian.tampilkanNilai || ujian.tampilkan_nilai || 'YA').toUpperCase() === 'YA';
    if (!showNilai) {
        
        const hideIds = ['nilaiAkhir', 'kkmStatusBadge', 'jawabanBenar', 'jawabanSalah', 'downloadBuktiBtn', 'analysisCard'];
        hideIds.forEach(id => {
            const el = document.getElementById(id);
            if (el) el.closest('.score-box, .stats-grid, .stat-card, #analysisCard, [id]').style.display = 'none';
        });

        
        document.querySelectorAll('.score-box, .stats-grid').forEach(el => el.style.display = 'none');
        document.getElementById('analysisCard') && (document.getElementById('analysisCard').style.display = 'none');
        document.getElementById('downloadBuktiBtn') && (document.getElementById('downloadBuktiBtn').style.display = 'none');

        
        const resultHeader = document.querySelector('.result-header');
        if (resultHeader && !document.getElementById('nilaiHiddenMsg')) {
            const msg = document.createElement('div');
            msg.id = 'nilaiHiddenMsg';
            msg.style.cssText = 'margin:1.5rem auto; padding:1rem 1.5rem; background:#f0fdf4; border:1.5px solid #4ade80; border-radius:12px; color:#166534; text-align:center; max-width:380px;';
            msg.innerHTML = '<i class="fas fa-check-circle" style="font-size:1.5rem;margin-bottom:0.5rem;display:block;"></i><strong>Ujian Selesai!</strong><br><small>Jawaban Anda telah tersimpan. Nilai akan diumumkan oleh guru.</small>';
            resultHeader.insertAdjacentElement('afterend', msg);
        }

        
        setupFinishButton(ujian.pengulangan);
        return;
    }
    

    
    await setupAnalysisCard(ujian, hasil);

    
    const el = getElements();
    animateValue(el.nilai, 0, hasil.nilai, 1500);
};



const setupAnalysisCard = async (ujianMeta, hasil) => {
    console.log("[DEBUG ANALYSIS] Memulai setupAnalysisCard...");
    console.log("[DEBUG ANALYSIS] Data ujianMeta:", ujianMeta);

    
    const izinTampil = String(ujianMeta.tampilkanJawaban || "TIDAK").toUpperCase() === "YA";
    console.log(`[DEBUG ANALYSIS] Izin Tampil Jawaban: ${izinTampil} (Data asli: ${ujianMeta.tampilkanJawaban})`);

    if (!izinTampil) {
        console.warn("[DEBUG ANALYSIS] Dibatalkan karena izinTampil = false.");
        return;
    }

    
    const card = document.getElementById('analysisCard');
    const elOpening = document.getElementById('analysisOpening');
    const wrapperWeak = document.getElementById('wrapperWeaknesses');
    const listWeak = document.getElementById('listWeaknesses');
    const wrapperRec = document.getElementById('wrapperRecommendations');
    const listRec = document.getElementById('listRecommendations');
    const aiDeepContainer = document.getElementById('aiDeepAnalysis');

    
    const wrapperBenar = document.getElementById('wrapperBenar');
    const wrapperSalah = document.getElementById('wrapperSalah');

    if (!card) return;

    
    card.style.display = 'block';
    elOpening.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Sedang memuat analisis detail dari server...';

    
    if (wrapperBenar) wrapperBenar.style.display = 'none';
    if (wrapperSalah) wrapperSalah.style.display = 'none';
    if (wrapperWeak) wrapperWeak.style.display = 'none';

    
    const localSoal = Store.getSoalList();
    const jawabanSiswa = Store.getJawaban();

    if (!localSoal || !jawabanSiswa) {
        elOpening.textContent = "Data tidak tersedia untuk analisis.";
        return;
    }

    try {
        
        
        const soalReview = await getSoalDetailForReview(localSoal, jawabanSiswa, ujianMeta.sheetSoal);

        
        const jawabanSalah = soalReview.filter(s => !s.isBenar);
        const analysis = generateSmartAnalysis(jawabanSalah, soalReview.length);

        
        elOpening.textContent = analysis.opening;

        
        listWeak.innerHTML = '';
        if (analysis.weaknesses.length > 0) {
            wrapperWeak.style.display = 'block';
            analysis.weaknesses.forEach(w => {
                const li = document.createElement('li');
                li.textContent = w;
                listWeak.appendChild(li);
            });
        } else {
            wrapperWeak.style.display = 'none';
        }

        
        listRec.innerHTML = '';
        analysis.recommendations.forEach(r => {
            const li = document.createElement('li');
            li.textContent = r;
            listRec.appendChild(li);
        });

        
        if (aiDeepContainer) {
            aiDeepContainer.style.display = 'block';
            aiDeepContainer.innerHTML = `
                <div class="ai-skeleton-container">
                    <div class="skeleton-header-row">
                        <div class="skeleton-circle skeleton-shimmer"></div>
                        <div class="skeleton-title-bar skeleton-shimmer"></div>
                    </div>
                    <div class="skeleton-line skeleton-shimmer" style="width: 100%"></div>
                    <div class="skeleton-line skeleton-shimmer" style="width: 95%"></div>
                    <div class="skeleton-line skeleton-shimmer" style="width: 90%"></div>
                    <div class="skeleton-line skeleton-shimmer" style="width: 85%"></div>
                </div>
            `;

            
            const siswaData = Store.getSiswa();

            
            
            const detailedWrongAnswers = soalReview
                .filter(s => !s.isBenar)
                .map(s => {
                    
                    const resolveText = (val) => {
                        if (!val) return "-";
                        if (s.pilihanMap) {
                            
                            const codes = Array.isArray(val) ? val : String(val).split(',');
                            const texts = codes.map(code => {
                                const c = String(code).trim().toUpperCase();
                                return s.pilihanMap[c] ? `${c} (${s.pilihanMap[c]})` : c;
                            });
                            return texts.join(', ');
                        }
                        return val;
                    };

                    return {
                        soal: s.pertanyaan ? s.pertanyaan.replace(/<[^>]*>/g, '').substring(0, 200) : "Pertanyaan Gambar/Tidak dimuat",
                        jawabanSiswa: resolveText(s.jawabanSiswa),
                        kunci: resolveText(s.kunci)
                    };
                });

            const analysisData = {
                siswaName: siswaData ? siswaData["Nama Lengkap"] : "Siswa",
                mapel: ujianMeta.topik,
                nilai: hasil.nilai,
                benar: hasil.benar,
                salah: hasil.salah,
                detailSalah: detailedWrongAnswers 
            };

            
            analyzeExamOverall(analysisData).then(aiHtml => {
                aiDeepContainer.innerHTML = `
                    <div class="ai-result-box">
                        <div class="ai-header-small">
                            <i class="fas fa-robot text-blue-600"></i>
                            <span class="font-bold text-gray-700">Analisis Mendalam AI</span>
                        </div>
                        <div class="ai-content-body">
                            ${aiHtml}
                        </div>
                    </div>
                `;
                
                if (window.MathJax) {
                    window.MathJax.typesetPromise([aiDeepContainer]).catch(err => console.log('MathJax error:', err));
                }
            }).catch(err => {
                console.error("AI Error", err);
                aiDeepContainer.innerHTML = `<div class="text-sm text-red-500">Gagal memuat analisis AI.</div>`;
            });
        }

        
        const listBenar = document.getElementById('listSoalBenar');
        const listSalah = document.getElementById('listSoalSalah');

        if (wrapperBenar && listBenar && wrapperSalah && listSalah) {
            
            
            renderDetailedAnalysis(soalReview, listBenar, listSalah, wrapperBenar, wrapperSalah, izinTampil);
        }

    } catch (error) {
        console.error("Gagal memuat analisis:", error);
        elOpening.innerHTML = `<span class="text-red-600"><i class="fas fa-exclamation-triangle"></i> Gagal memuat analisis: ${error.message}</span>`;
    }
};

const generateSmartAnalysis = (jawabanSalah, totalSoal) => {
    const totalSalah = jawabanSalah.length;
    const persentaseBenar = Math.round(((totalSoal - totalSalah) / totalSoal) * 100);

    let opening = "";
    if (persentaseBenar >= 85) {
        opening = "Luar biasa! Kamu menunjukkan pemahaman yang sangat baik pada materi ini dengan pencapaian di atas 85%. Pertahankan konsistensi belajar ini.";
    } else if (persentaseBenar >= 75) {
        opening = "Kerja bagus! Kamu menunjukkan pemahaman yang cukup baik. Masih ada beberapa area kecil yang perlu diperkuat untuk mencapai hasil maksimal.";
    } else {
        opening = "Tetap semangat! Kamu perlu meningkatkan pemahaman pada beberapa konsep dasar. Disarankan untuk mempelajari ulang materi dengan lebih fokus.";
    }

    
    const typeCount = {
        PG: 0,
        PG_KOMPLEKS: 0,
        BENAR_SALAH: 0,
        ISIAN: 0,
        MENJODOHKAN: 0
    };

    jawabanSalah.forEach(item => {
        const tipe = item.tipe || 'PG';
        if (typeCount[tipe] !== undefined) {
            typeCount[tipe]++;
        }
    });

    const weaknesses = [];
    if (typeCount.PG > 0) weaknesses.push(`${typeCount.PG} soal Pilihan Ganda - Perkuat pemahaman konsep dan definisi`);
    if (typeCount.PG_KOMPLEKS > 0) weaknesses.push(`${typeCount.PG_KOMPLEKS} soal Pilihan Ganda Kompleks - Latih ketelitian analisis multi-jawaban`);
    if (typeCount.BENAR_SALAH > 0) weaknesses.push(`${typeCount.BENAR_SALAH} soal Benar/Salah - Tingkatkan ketelitian evaluasi pernyataan`);
    if (typeCount.ISIAN > 0) weaknesses.push(`${typeCount.ISIAN} soal Isian - Perkuat hafalan dan detail materi`);

    const recommendations = [];
    if (typeCount.PG > 0 || typeCount.PG_KOMPLEKS > 0) {
        recommendations.push("Baca kembali rangkuman materi utama.");
        recommendations.push("Latih soal-soal serupa untuk memperdalam pemahaman.");
    }
    if (typeCount.BENAR_SALAH > 0) {
        recommendations.push("Baca setiap pernyataan dengan perlahan dan hati-hati.");
    }
    if (typeCount.ISIAN > 0) {
        recommendations.push("Buat catatan kecil atau flashcard untuk mengingat istilah penting.");
    }

    if (recommendations.length === 0) {
        recommendations.push("Pertahankan metode belajarmu yang sudah efektif.");
        recommendations.push("Coba kerjakan soal-soal pengayaan untuk tantangan lebih.");
    }

    return { opening, weaknesses, recommendations };
};



const renderDetailedAnalysis = (soalReview, containerBenar, containerSalah, wrapBenar, wrapSalah, showKeyAllowed) => {
    containerBenar.innerHTML = '';
    containerSalah.innerHTML = '';

    let countBenar = 0;
    let countSalah = 0;

    soalReview.forEach(item => {
        
        const formatAns = (val, type) => {
            if (!val && val !== 0) return "(Tidak dijawab)";

            const renderSingle = (k) => {
                const kClean = String(k).trim().toUpperCase();
                
                if (item.pilihanMap && item.pilihanMap[kClean]) {
                    return `<strong>${kClean}</strong>. ${item.pilihanMap[kClean]}`;
                }
                return kClean;
            };

            if (type === 'PG_KOMPLEKS' || Array.isArray(val)) {
                
                const arr = Array.isArray(val) ? val : String(val).split(',');
                return arr.map(k => renderSingle(k.trim())).join('<br>');
            }

            return renderSingle(val);
        };

        
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

        const cleanedQuestion = cleanQuestionText(item.pertanyaan || 'Pertanyaan tidak dimuat');

        
        const itemDiv = document.createElement('div');
        itemDiv.className = `analysis-item ${item.isBenar ? 'correct' : 'wrong'}`;

        
        let html = `
            <div class="item-header">
                <span class="item-number">#${item.no}</span>
                <span class="item-question">${cleanedQuestion}</span>
            </div>
            <div class="item-details">
        `;

        
        if (item.tipe === 'BENAR_SALAH' && item.bsData && item.bsData.length > 0) {
            
            let rowsHtml = '';
            item.bsData.forEach((row, idx) => {
                const isRowCorrect = row.userVal === row.keyVal;
                
                const statusClass = isRowCorrect ? 'status-correct' : 'status-wrong';

                rowsHtml += `
                    <div class="bs-row ${statusClass}">
                        <div class="bs-statement">${row.text}</div>
                        <div class="bs-answers-wrapper">
                            <div class="bs-ans-group user">
                                <span class="bs-label">Jawabanmu</span>
                                <span class="bs-badge ${row.userVal === 'BENAR' ? 'badge-true' : 'badge-false'}">
                                    ${row.userVal || '-'}
                                </span>
                            </div>
                            ${!item.isBenar ? `
                            <div class="bs-ans-group key" id="key-container-${item.no}" style="display: ${showKeyAllowed ? 'flex' : 'none'};">
                                <span class="bs-label">Kunci</span>
                                <span class="bs-badge key-badge ${row.keyVal === 'BENAR' ? 'badge-true' : 'badge-false'}">
                                    ${row.keyVal}
                                </span>
                            </div>
                            ` : ''}
                        </div>
                    </div>
                 `;
            });

            html += `
                <div class="bs-container">
                    <div class="bs-header-row">
                        <div class="bs-h-statement">Pernyataan</div>
                        <div class="bs-h-ans">Respon Detail</div>
                    </div>
                    <div class="bs-body">
                        ${rowsHtml}
                    </div>
                </div>
                <!-- BUTTON CONTAINER (Shared for BS) -->
                <div class="explanation-action-area" id="action-area-${item.no}">
                    <!-- Button will be injected here if allowed -->
                </div>
                <div id="explanation-box-${item.no}" class="ai-explanation-box" style="display: none;">
                    <div class="ai-header">
                        <img src="/logo/FARA_BLACK (3).png" alt="AI Logo" class="ai-icon">
                        <span>Di Inisialisasi oleh AI</span>
                    </div>
                    <p class="ai-text" id="ai-text-${item.no}"></p>
                </div>
            `;
        }
        else {
            
            const displayUser = formatAns(item.jawabanSiswa, item.tipe);
            const displayKey = formatAns(item.kunci, item.tipe);

            html += `
                <div class="detail-row">
                    <span class="detail-label">Jawabanmu:</span>
                    <span class="detail-value ${item.isBenar ? 'is-correct' : 'is-wrong'}">${displayUser}</span>
                </div>
                
                <!-- KEY ROW (Visible if ShowKeyAllowed) -->
                <div class="detail-row" id="key-row-${item.no}" style="display: ${showKeyAllowed ? 'flex' : 'none'};">
                    <span class="detail-label">Kunci:</span>
                    <span class="detail-value is-key">${displayKey}</span>
                </div>

                <!-- ACTION BUTTON AREA -->
                <div class="explanation-action-area" id="action-area-${item.no}">
                    <!-- Button will be injected here if allowed -->
                </div>

                <!-- AI EXPLANATION BOX -->
                <div id="explanation-box-${item.no}" class="ai-explanation-box" style="display: none;">
                    <div class="ai-header">
                        <img src="/logo/FARA_BLACK (3).png" alt="AI Logo" class="ai-icon">
                        <span>Penjelasan AI</span>
                    </div>
                    <p class="ai-text" id="ai-text-${item.no}"></p>
                </div>
            `;
        }

        html += `</div>`; 
        itemDiv.innerHTML = html;

        
        const actionArea = itemDiv.querySelector(`#action-area-${item.no}`);
        if (actionArea && showKeyAllowed) {
            const btn = document.createElement('button');
            btn.className = 'btn-explain';
            btn.innerHTML = `<i class="fas fa-robot"></i> Penjelasan`;

            btn.onclick = async () => {
                
                btn.style.display = 'none'; 

                const box = itemDiv.querySelector(`#explanation-box-${item.no}`);
                const textEl = itemDiv.querySelector(`#ai-text-${item.no}`);

                if (box && textEl) {
                    box.style.display = 'block';
                    
                    textEl.innerHTML = `
                        <div class="ai-skeleton-container" style="padding: 0; animation: none;">
                            <div class="skeleton-line skeleton-shimmer" style="width: 100%"></div>
                            <div class="skeleton-line skeleton-shimmer" style="width: 95%"></div>
                            <div class="skeleton-line skeleton-shimmer" style="width: 90%"></div>
                        </div>
                    `;
                }

                
                try {
                    
                    const rawSoal = item.pertanyaan;
                    const rawJwb = formatAns(item.jawabanSiswa, item.tipe).replace(/<[^>]*>/g, '');
                    const rawKey = formatAns(item.kunci, item.tipe).replace(/<[^>]*>/g, '');

                    
                    const explanation = await generateExplanation(
                        rawSoal,
                        rawJwb,
                        rawKey,
                        (attempt, max) => {
                            
                        }
                    );

                    

                    const box = itemDiv.querySelector(`#explanation-box-${item.no}`);
                    const textEl = itemDiv.querySelector(`#ai-text-${item.no}`);

                    if (box && textEl) {

                        
                        const isError = explanation.includes("[") && explanation.includes("ERROR]");

                        if (isError) {
                            textEl.innerHTML = explanation; 
                            textEl.style.color = '#dc2626'; 
                            textEl.style.fontFamily = 'monospace';
                            textEl.style.whiteSpace = 'pre-wrap';
                            textEl.style.fontSize = '0.85rem';

                            btn.innerHTML = `<i class="fas fa-sync-alt"></i> Coba Antre Lagi`;
                            btn.disabled = false;
                        } else {
                            
                            textEl.innerHTML = parseMarkdown(explanation);

                            textEl.style.color = '#334155';
                            textEl.style.fontFamily = 'inherit';
                            textEl.style.whiteSpace = 'normal';
                            textEl.style.fontSize = '1rem';

                            
                            if (window.MathJax && window.MathJax.typesetPromise) {
                                window.MathJax.typesetPromise([textEl]).catch(err => console.log('MathJax error:', err));
                            }

                            box.style.display = 'block';
                            box.classList.add('slide-down');
                            btn.style.display = 'none'; 
                        }

                        
                        box.style.display = 'block';
                        box.classList.add('slide-down');
                    }

                } catch (err) {
                    console.error(err);
                    btn.innerHTML = `<i class="fas fa-exclamation-circle"></i> Error. Coba Lagi`;
                    btn.disabled = false;
                }
            };

            actionArea.appendChild(btn);
        }

        if (item.isBenar) {
            containerBenar.appendChild(itemDiv);
            countBenar++;
        } else {
            containerSalah.appendChild(itemDiv);
            countSalah++;
        }
    });

    
    wrapBenar.style.display = countBenar > 0 ? 'block' : 'none';
    wrapSalah.style.display = countSalah > 0 ? 'block' : 'none';
};



async function getSoalDetailForReview(shuffledSoal, jawabanSiswa, sheetSoal) {
    let serverSoal = [];

    
    const callApi = async (act, pl) => {
        
        if (typeof window.apiCall === 'function') return await window.apiCall(act, pl);
        if (typeof window.callApiInternal === 'function') return await window.callApiInternal(act, pl);

        
        console.warn("API Function not found, returning local data.");
        return { soal: shuffledSoal };
    };

    try {
        const res = await callApi("getSoalWithKey", { sheetSoal });
        if (res && res.soal) {
            serverSoal = res.soal;
        } else {
            serverSoal = shuffledSoal;
        }
    } catch (e) {
        console.warn("Gagal fetch kunci, fallback local data", e);
        serverSoal = shuffledSoal;
    }

    const serverMap = {};
    
    serverSoal.forEach(s => {
        if (s.id_soal) serverMap[s.id_soal] = s;
        else if (s.no) serverMap[s.no] = s;
    });

    return shuffledSoal.map((soalLocal, idx) => {
        
        const targetId = soalLocal.id_soal || (soalLocal.originalIndex + 1);
        const soalDB = serverMap[targetId] || {};

        
        const tipe = soalDB.tipe_soal || soalDB.tipe || soalLocal.tipe || "PG";
        const kunci = String(soalDB.kunci || soalDB.kunci_jawaban || "").toUpperCase(); 
        const userJwb = jawabanSiswa[soalLocal.originalIndex];

        let isBenar = false;
        let bsData = [];

        
        
        const pilihanMap = {};
        if (soalLocal.pilihan && Array.isArray(soalLocal.pilihan)) {
            soalLocal.pilihan.forEach(p => {
                
                if (p.key && p.text) {
                    pilihanMap[String(p.key).toUpperCase()] = p.text;
                }
            });
        }

        
        if (tipe === 'BENAR_SALAH') {
            const kunciArr = kunci.split(',').map(k => k.trim());
            
            let userAnswers = Array.isArray(userJwb) ? userJwb : String(userJwb || "").split(',');

            
            while (userAnswers.length < kunciArr.length) userAnswers.push("");

            const statementKeys = ["a", "b", "c", "d", "e"];
            isBenar = true; 

            
            bsData = statementKeys.map((k, i) => {
                
                if (i >= kunciArr.length) return null;

                const stmt = soalDB[k] || `Pernyataan ${i + 1}`;
                const kVal = String(kunciArr[i] || "").toUpperCase().trim();
                const uVal = String(userAnswers[i] || "").toUpperCase().trim();

                
                if (uVal !== kVal) isBenar = false;

                return { text: stmt, userVal: uVal, keyVal: kVal };
            }).filter(item => item !== null);

        } else {
            
            if (tipe === 'PG_KOMPLEKS') {
                const kSet = String(kunci).split(',').map(s => s.trim()).sort().join(',');
                const uSet = Array.isArray(userJwb) ? userJwb.sort().join(',') : String(userJwb || "").toUpperCase();
                isBenar = kSet === uSet;
            } else {
                const kClean = String(kunci).trim();
                const uClean = String(userJwb || "").toUpperCase().trim();
                isBenar = kClean === uClean;
            }
        }

        return {
            no: idx + 1,
            pertanyaan: soalLocal.pertanyaan,
            tipe: tipe,
            jawabanSiswa: userJwb,
            kunci: kunci,
            isBenar: isBenar,
            bsData: bsData,
            pilihanMap: pilihanMap
        };
    });
}




const renderResultData = (siswa, ujian, hasil) => {
    const el = getElements();
    if (el.nama) el.nama.textContent = siswa["Nama Lengkap"];
    if (el.kelas) el.kelas.textContent = siswa.Kelas;
    if (el.mapel) el.mapel.textContent = ujian.topik;

    if (el.benar) el.benar.textContent = hasil.benar;
    if (el.salah) el.salah.textContent = hasil.salah;

    
    if (el.nilai) el.nilai.textContent = "0";

    
    const kkm = 80; 
    const isLulus = hasil.nilai >= kkm;

    if (el.kkmBadge) {
        if (isLulus) {
            el.kkmBadge.innerHTML = `
                <div class="kkm-badge lulus">
                    <i class="fas fa-check-circle"></i>
                    <span>TUNTAS (KKM ${kkm})</span>
                </div>
            `;
        } else {
            el.kkmBadge.innerHTML = `
                <div class="kkm-badge tidak-lulus">
                    <i class="fas fa-times-circle"></i>
                    <span>BELUM TUNTAS (KKM ${kkm})</span>
                </div>
            `;
        }
    }
};



const setupDownloadButton = (ujianMeta) => {
    const el = getElements();
    if (!el.btnDownload) return;

    
    const izinDownload = String(ujianMeta.tampilkanJawaban || "TIDAK").toUpperCase() === "YA";

    if (izinDownload) {
        el.btnDownload.style.display = 'inline-flex';

        el.btnDownload.onclick = async () => {
            
            const originalText = el.btnDownload.innerHTML;
            el.btnDownload.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memuat...';
            el.btnDownload.disabled = true;

            try {
                
                if (typeof window.generateBuktiUjian !== 'function') {
                    console.warn("PDF Generator belum dimuat, mencoba load manual...");
                    await loadScript('js/bukti-ujian.js');
                }

                
                if (typeof window.generateBuktiUjian === 'function') {
                    await window.generateBuktiUjian();
                } else {
                    throw new Error("Gagal memuat modul PDF Generator.");
                }
            } catch (error) {
                console.error(error);
                safeSwalFire('Error', 'Gagal memproses PDF: ' + error.message, 'error');
            } finally {
                
                el.btnDownload.innerHTML = originalText;
                el.btnDownload.disabled = false;
            }
        };
    } else {
        el.btnDownload.style.display = 'none';
    }
};



const setupFinishButton = (izinPengulangan) => {
    const el = getElements();
    if (!el.btnSelesai) return;

    const isRemedialAllowed = String(izinPengulangan || "TIDAK").toUpperCase() === "YA";

    
    const executeLogoutWithFeedback = () => {
        
        el.btnSelesai.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Memproses...';
        
        el.btnSelesai.disabled = true;
        el.btnSelesai.style.opacity = '0.7';
        el.btnSelesai.style.cursor = 'not-allowed';

        
        setTimeout(() => {
            doLogout();
        }, 100);
    };

    if (isRemedialAllowed) {
        
        el.btnSelesai.innerHTML = '<i class="fas fa-redo mr-2"></i> Selesai / Ulangi';
        el.btnSelesai.classList.remove('btn-danger');
        el.btnSelesai.classList.add('btn-primary');

        el.btnSelesai.onclick = () => {
            safeSwalFire({
                title: 'Konfirmasi',
                text: "Apa langkah Anda selanjutnya?",
                icon: 'question',
                showDenyButton: true,
                showCancelButton: true,
                confirmButtonText: 'Ulangi Ujian',
                denyButtonText: 'Keluar (Logout)',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#3085d6', 
                denyButtonColor: '#d33'         
            }).then((result) => {
                if (result.isConfirmed) {
                    doRepeatExam();
                } else if (result.isDenied) {
                    executeLogoutWithFeedback();
                }
            });
        };
    } else {
        
        el.btnSelesai.innerHTML = '<i class="fas fa-sign-out-alt mr-2"></i> Logout / Selesai';
        el.btnSelesai.classList.add('btn-danger');

        el.btnSelesai.onclick = () => {
            safeSwalFire({
                title: 'Selesai Ujian?',
                text: "Anda akan keluar dari sesi ini.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Keluar',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#d33'
            }).then((result) => {
                if (result.isConfirmed) {
                    executeLogoutWithFeedback();
                }
            });
        };
    }
};



const doRepeatExam = () => {
    
    const siswa = Store.getSiswa() || {};
    const nis = siswa.NIS || Store.getSavedNIS() || '1';
    const token = localStorage.getItem('exam_token') || 'UHRHF'; 

    
    Store.clearAll();

    
    const retryBase = (window.__EXAM_URLS__ && window.__EXAM_URLS__.summary) ? window.__EXAM_URLS__.summary : 'summary.html';
    window.location.href = `${retryBase}?nis=${nis}&token=${token}`;
};

const doLogout = () => {
    
    
    const targetUrl = getExitUrl();

    
    Store.clearAll();

    
    
    window.location.replace(targetUrl);
};

const getExitUrl = () => {
    

    
    const origin = sessionStorage.getItem('exam_origin');

    
    
    const referrer = document.referrer || "";

    
    
    if (origin === 'lms' || referrer.includes('garudakademi.ct.ws')) {
        return 'http://garudakademi.ct.ws';
    }

    
    
    return (window.__EXAM_URLS__ && window.__EXAM_URLS__.exit) ? window.__EXAM_URLS__.exit : 'index.html';
};

const animateValue = (obj, start, end, duration) => {
    if (!obj) return;
    let startTimestamp = null;
    const isDecimal = !Number.isInteger(end);
    const step = (timestamp) => {
        if (!startTimestamp) startTimestamp = timestamp;
        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
        const current = progress * (end - start) + start;
        
        obj.innerHTML = isDecimal ? current.toFixed(1) : Math.floor(current);
        if (progress < 1) {
            window.requestAnimationFrame(step);
        } else {
            
            obj.innerHTML = end.toFixed(1);
        }
    };
    window.requestAnimationFrame(step);
};


const loadScript = (src) => {
    return new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.src = src;
        script.onload = () => resolve();
        script.onerror = () => reject(new Error(`Script load error for ${src}`));
        document.head.appendChild(script);
    });
};


const safeSwalFire = (options, text, icon) => {
    if (typeof Swal !== 'undefined') {
        
        if (typeof options === 'string') {
            return Swal.fire(options, text, icon);
        }
        return Swal.fire(options);
    } else {
        
        if (typeof options === 'object' && options.title) {
            const isConfirmed = confirm(`${options.title}\n${options.text}`);
            
            return Promise.resolve({
                isConfirmed: isConfirmed,
                isDenied: !isConfirmed && options.showDenyButton
            });
        }
        alert(options);
        return Promise.resolve({});
    }
};


const parseMarkdown = (text) => {
    if (!text) return "";

    
    let html = text
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;");

    
    html = html.replace(/\*\*(.*?)\*\*/g, '<b>$1</b>');

    
    html = html.replace(/\*(.*?)\*/g, '<i>$1</i>');

    
    html = html.replace(/^#\s+(.*$)/gm, '<h4 class="font-bold text-lg mt-2">$1</h4>');
    html = html.replace(/^##\s+(.*$)/gm, '<h5 class="font-bold text-md mt-2">$1</h5>');

    
    html = html.replace(/^\*\s+(.*$)/gm, '<li class="ml-4">$1</li>');

    
    
    html = html.replace(/\\\[(.*?)\\\]/gs, '<div class="math-block text-center my-2">$$$1$$</div>');
    
    html = html.replace(/\\\((.*?)\\\)/g, '$$$1$$');

    
    html = html.replace(/\n/g, '<br>');

    return html;
};