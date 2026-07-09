









// AI API Keys: dibaca dari server (app_settings), bukan hardcoded
// Gunakan fungsi getGeminiKey() / getOpenRouterKey() untuk mengakses key
let _cachedGeminiKey = null;
let _cachedOpenRouterKey = null;
let _keysFetchPromise = null;

async function _fetchAiKeys() {
  if (_keysFetchPromise) return _keysFetchPromise;
  _keysFetchPromise = (async () => {
    try {
      const apiUrl = window.__EXAM_API_URL__ || '/api/exam/student';
      const fd = new FormData();
      fd.append('action', 'get_ai_keys');
      const r = await fetch(apiUrl, { method: 'POST', body: fd });
      const json = await r.json();
      if (json.success && json.data) {
        _cachedGeminiKey     = json.data.gemini_key     || '';
        _cachedOpenRouterKey = json.data.openrouter_key || '';
      }
    } catch (e) {
      console.warn('[AI Config] Gagal mengambil AI keys dari server:', e);
      _cachedGeminiKey     = '';
      _cachedOpenRouterKey = '';
    }
  })();
  return _keysFetchPromise;
}

async function getGeminiKey() {
  if (_cachedGeminiKey === null) await _fetchAiKeys();
  return _cachedGeminiKey || '';
}

async function getOpenRouterKey() {
  if (_cachedOpenRouterKey === null) await _fetchAiKeys();
  return _cachedOpenRouterKey || '';
}

const GEMINI_URL_BASE = `https://generativelanguage.googleapis.com/v1beta/models/gemini-3-flash-preview:generateContent?key=`;


const OPENROUTER_MODELS = [
  'google/gemini-2.5-flash-lite',          
  'nvidia/nemotron-3-nano-30b-a3b:free', 
  'deepseek/deepseek-r1-0528:free',       
  'xiaomi/mimo-v2-flash:free',         
  'mistralai/mistral-7b-instruct:free'         
];

const OPENROUTER_URL = 'https://openrouter.ai/api/v1/chat/completions';









export async function generateExplanation(soal, jawabanSiswa, kunci, onRetry) {
  const prompt = `Soal: "${soal}"
Jawaban Siswa: "${jawabanSiswa}"
Kunci Jawaban: "${kunci}"

Tugas: Jelaskan kenapa jawaban siswa salah (jika salah dan ini juga prioritas utama) atau benarkan konsepnya (jika benar). Berikan penjelasan jawaban yang benar.
Syarat:
1. Penjelasan harus sangat singkat, padat, dan jelas.
2. Minimal 1-2 kalimat.
3. Langsung ke inti permasalahannya.
4. Gaya bahasa santai gen z tapi edukatif (seperti guru privat).`;

  
  try {
    const result = await tryGemini(prompt, onRetry);
    return result;
  } catch (geminiError) {
    console.warn("[FAILOVER] Gemini Failed, switching to OpenRouter...", geminiError);
  }

  
  try {
    if (onRetry) onRetry(1, "Failover: OpenRouter"); 
    const result = await tryOpenRouter(prompt);
    return result;
  } catch (orError) {
    console.warn("[FAILOVER] OpenRouter Failed, switching to Static Fallback...", orError);
  }

  
  return getStaticFallback(jawabanSiswa, kunci);
}


async function tryGemini(prompt, onRetry) {
  const payload = {
    contents: [{
      parts: [{ text: prompt }]
    }]
  };

  
  const maxRetries = 0;

  
  const geminiKey = await getGeminiKey();
  if (!geminiKey) throw new Error('Gemini key belum dikonfigurasi');
  const GEMINI_URL = GEMINI_URL_BASE + geminiKey;
  console.log(`[Gemini API] Requesting...`);

  const response = await fetch(GEMINI_URL, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(payload)
  });

  if (!response.ok) {
    const text = await response.text();
    throw new Error(`Gemini HTT P${response.status}: ${text}`);
  }

  const data = await response.json();
  if (data.candidates && data.candidates.length > 0 && data.candidates[0].content) {
    return data.candidates[0].content.parts[0].text;
  } else {
    throw new Error("Gemini Empty Response");
  }
}


async function tryOpenRouter(prompt) {
  let lastError = null;

  for (const modelName of OPENROUTER_MODELS) {
    try {
      console.log(`[OpenRouter] Trying Model: ${modelName}...`);

      const orKey = await getOpenRouterKey();
      if (!orKey) throw new Error('OpenRouter key belum dikonfigurasi');
      const response = await fetch(OPENROUTER_URL, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${orKey}`,
          'Content-Type': 'application/json',
          'HTTP-Referer': 'https://garudaakademi.id',
          'X-Title': 'Garuda Akademi'
        },
        body: JSON.stringify({
          model: modelName,
          messages: [
            {
              role: "system",
              content: "Kamu adalah AI asisten guru yang gaul dan singkat."
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
        console.warn(`[OpenRouter] Model ${modelName} error:`, result.error.message);
        lastError = result.error;
        continue;
      }

      if (result.choices && result.choices.length > 0) {
        let content = result.choices[0].message.content;
        
        return content;
      }

    } catch (error) {
      console.warn(`[OpenRouter] Network error with ${modelName}:`, error);
      lastError = error;
    }
  }

  throw lastError || new Error("All OpenRouter models failed");
}


function getStaticFallback(jawabanSiswa, kunci) {
  
  const normSiswa = String(jawabanSiswa).trim().toUpperCase();
  const normKunci = String(kunci).trim().toUpperCase();

  
  
  

  
  

  
  let isCorrect = normSiswa === normKunci;
  if (normKunci.includes(',')) { 
    
  }

  if (isCorrect) {
    return `Wow Jawaban kamu **${jawabanSiswa}** sudah tepat. Pertahankan!`;
  } else {
    return `Waduh, jawaban kamu untuk soal ini kurang tepat alias salah, jawaban yang benar adalah **${kunci}**, sedangkan kamu menjawab **${jawabanSiswa}**.`;
  }
}