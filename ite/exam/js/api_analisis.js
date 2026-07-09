





// OpenRouter API Key: dibaca dari server (app_settings), bukan hardcoded
const API_URL = 'https://openrouter.ai/api/v1/chat/completions';

let _analisisCachedKey = null;
let _analisisFetchPromise = null;

async function _getAnalisisKey() {
    if (_analisisCachedKey !== null) return _analisisCachedKey;
    if (_analisisFetchPromise) return _analisisFetchPromise;
    _analisisFetchPromise = (async () => {
        try {
            const apiUrl = window.__EXAM_API_URL__ || '/api/exam/student';
            const fd = new FormData();
            fd.append('action', 'get_ai_keys');
            const r = await fetch(apiUrl, { method: 'POST', body: fd });
            const json = await r.json();
            _analisisCachedKey = (json.success && json.data && json.data.openrouter_key) ? json.data.openrouter_key : '';
        } catch (e) {
            console.warn('[AI Analisis] Gagal mengambil key:', e);
            _analisisCachedKey = '';
        }
        return _analisisCachedKey;
    })();
    return _analisisFetchPromise;
}



const MODELS = [
    'google/gemini-2.5-flash-lite',
    'nvidia/nemotron-3-nano-30b-a3b:free',
    'deepseek/deepseek-r1-0528:free',
    'xiaomi/mimo-v2-flash:free',
    'mistralai/mistral-7b-instruct:free'
];

export async function analyzeExamOverall(data) {
    const {
        siswaName,
        mapel,
        nilai,
        benar,
        salah,
        detailSalah 
    } = data;

    
    let mistakesContext = "Tidak ada jawaban salah (Nilai Sempurna!). Berikan tips pengayaan.";
    if (detailSalah && detailSalah.length > 0) {
        mistakesContext = detailSalah.map((item, idx) => {
            return `[Soal #${idx + 1}]
   Pertanyaan: "${item.soal}"
   Jawaban Siswa (SALAH): ${item.jawabanSiswa}
   Kunci Benar: ${item.kunci}`;
        }).join('\n\n');
    }

    
    const prompt = `
Peran: Senior Guru IPA/Informatika tingkat profesional.
Siswa: "${siswaName}"
Mapel: "${mapel}"
Skor: ${nilai}/100 (Benar: ${benar}, Salah: ${salah})

DAFTAR KESALAHAN SISWA (Wajib Dianalisis):
---------------------------------------------------
${mistakesContext}
---------------------------------------------------

TUGAS:
Buat laporan evaluasi diri untuk siswa dalam format HTML (div/p/ul/strong) yang rapi. Jangan gunakan Markdown.

INSTRUKSI KHUSUS:
1. JANGAN HALUSINASI. Gunakan data teks jawaban yang sudah disediakan di atas (misal "A (Kucing)"). Jangan mengarang opsi sendiri jika tidak ada di data.
2. JANGAN BASA-BASI. Langsung ke inti analisis per-soal yang salah.
3. FOKUS UTAMA: Jelaskan mengapa opsi jawaban siswa salah dibandingkan kunci yang benar.
4. Gunakan bahasa Indonesia yang asik dan ala gen-z dan ada sedikit kata sundanya, menyemangati, tapi korektif.
5. Bila ada rumus matematika/fisika/kimia, gunakan format LaTeX yang valid:
   - Inline: \\( rumus \\)
   - Block: $$ rumus $$

STRUKTUR HTML OUTPUT (Isi konten di dalamnya):

<div class="ai-analysis-content">
  <h3> Evaluasi</h3>
  <p>...(Komentar ringkas tentang skor)...</p>

  <h3>Bedah Kesalahan</h3>
  <p>...(Ambil 2-3 contoh kesalahan paling fatal dari daftar di atas. Tulis: "Di soal tentang [Topik], kamu menjawab [Jawaban Siswa] yang kurang tepat. Seharusnya [Kunci] karena [Alasan Singkat]" ...)</p>

  <h3>Tips Perbaikan</h3>
  <ul class="list-disc pl-5">
    <li>...(Tips 1)...</li>
    <li>...(Tips 2)...</li>
  </ul>
</div>
`;

    
    let lastError = null;

    for (const modelName of MODELS) {
        try {
            console.log(`Trying AI Model: ${modelName}...`);

            const apiKey = await _getAnalisisKey();
            if (!apiKey) throw new Error('OpenRouter key belum dikonfigurasi oleh operator');
            const response = await fetch(API_URL, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${apiKey}`,
                    'Content-Type': 'application/json',
                    'HTTP-Referer': 'https://garudaakademi.id',
                    'X-Title': 'Garuda Akademi'
                },
                body: JSON.stringify({
                    model: modelName,
                    messages: [
                        {
                            role: "system",
                            content: "Kamu adalah AI Analis Pendidikan yang memberikan feedback terstruktur dan mendalam format HTML."
                        },
                        {
                            role: "user",
                            content: prompt
                        }
                    ]
                })
            });

            const result = await response.json();

            
            if (result.error) {
                console.warn(`Model ${modelName} failed:`, result.error.message);
                lastError = result.error;
                continue; 
            }

            if (result.choices && result.choices.length > 0) {
                let content = result.choices[0].message.content;
                
                content = content.replace(/```html/g, '').replace(/```/g, '');
                return content; 
            }

        } catch (error) {
            console.warn(`Network error with ${modelName}:`, error);
            lastError = error;
        }
    }

    
    console.error('All AI Models Failed. Last Error:', lastError);

    let msg = "Terjadi kesalahan pada semua server AI.";
    if (lastError && lastError.message) msg = lastError.message;
    else if (lastError) msg = JSON.stringify(lastError);

    return `<div class="alert alert-danger" style="color: #b91c1c; background: #fef2f2; padding: 1rem; border-radius: 8px; border: 1px solid #fecaca;">
        <strong>Gagal memuat analisis</strong><br>
        <span style="font-family: monospace; font-size: 0.85em;">${msg}</span><br>
        <small>Gara sedang sibuk. Silakan coba lagi nanti.</small>
    </div>`;
}