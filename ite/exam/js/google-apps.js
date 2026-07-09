
const WEB_APP_URL = (typeof window.__EXAM_API_URL__ !== 'undefined')
    ? window.__EXAM_API_URL__
    : "../actions/asesmen.php";







async function apiCall(action, payload = {}) {
  const formData = new FormData();
  formData.append('action', action);

  
  for (const key in payload) {
    if (typeof payload[key] === 'object') {
      formData.append(key, JSON.stringify(payload[key]));
    } else {
      formData.append(key, payload[key]);
    }
  }

  
  

  try {
    const response = await fetch(WEB_APP_URL, {
      method: "POST",
      body: formData
    });

    if (!response.ok) {
      
      const text = await response.text();
      console.error("Server Error:", text);
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    const result = await response.json();

    if (result.success) {
      return result.data;
    } else {
      throw new Error(result.message || "Terjadi kesalahan pada server.");
    }
  } catch (error) {
    console.error("API Call Error:", error.message);
    throw error;
  }
}



window.apiCall = apiCall; 


window.validateLogin = async (credentials) => {
  const res = await apiCall("check_token", { token: credentials.kodeAkses, nis: credentials.nis });
  

  
  localStorage.setItem('exam_token', credentials.kodeAkses);

  return {
    isValid: true,
    role: "siswa",
    siswa: {
      "Nama Lengkap": res.siswa.nama,
      "NIS": res.siswa.nis,
      "Kelas": res.siswa.kelas
    },
    ujian: {
      topik:            res.ujian.topik, 
      durasi:           res.ujian.durasi,
      sheetSoal:        "DYNAMIC",
      pengulangan:      res.ujian.pengulangan,      
      tampilkanJawaban: res.ujian.tampilkanJawaban, 
      tampilkanNilai:   res.ujian.tampilkanNilai   ?? 'YA',   
      modeSubmit:       res.ujian.modeSubmit       ?? 'MANDIRI' 
    },
    message: "Login berhasil."
  };
};


window.getDashboardData = () => apiCall("getDashboardData");


window.saveExamConfig = (config) => apiCall("saveExamConfig", config);


window.generateKodeAkses = (payload) => apiCall("generateKodeAkses", payload);


window.getSoalByKodeAkses = async (payload) => {
  const token = localStorage.getItem('exam_token');
  const res = await apiCall("get_exam_paper", { token: token });

  
  
  
  if (res.soal && Array.isArray(res.soal)) {
    const idMap = res.soal.map(q => q.id_soal);
    localStorage.setItem('exam_id_map', JSON.stringify(idMap));

    
    

    const mappedSoal = res.soal.map((q, idx) => ({
      no: idx + 1,
      id_soal: q.id_soal, 
      soal: q.soal,
      tipe: q.tipe_soal,
      kunci: "HIDDEN",
      assets: q.assets, 
      
      a: q.a, b: q.b, c: q.c, d: q.d, e: q.e
    }));

    
    return { soal: mappedSoal };
  }

  return { soal: [] };
};


window.submitJawaban = async (data) => {
  const token = localStorage.getItem('exam_token');

  
  const idMap = JSON.parse(localStorage.getItem('exam_id_map') || '[]');

  
  let formattedJawaban = [];

  if (data.jawaban && data.jawaban.length > 0 && typeof data.jawaban[0] === 'object' && 'id_soal' in data.jawaban[0]) {
    
    formattedJawaban = data.jawaban;
  } else {
    
    formattedJawaban = data.jawaban.map((val, idx) => {
      let qId = idMap[idx] ? idMap[idx] : null;
      return {
        id_soal: qId,
        jawaban: val
      };
    });
  }

  try {
    const res = await apiCall("submit_exam", { token: token, jawaban: formattedJawaban });

    return {
      nilai: res.score,
      benar: res.benar || 0, 
      salah: res.salah || 0, 
      percobaan: 1
    };
  } catch (e) {
    console.error("Submit Error:", e);
    throw e;
  }
};


window.getMonitoringData = async (payload) => {
  const res = await apiCall("get_rekap_nilai", {});
  return res.map(row => ({
    "NIS": row.nis,
    "Nama Lengkap": row.nama,
    "Nilai": row.skor_akhir
  }));
};


window.getRiwayatPengulangan = (payload) => apiCall("getRiwayatPengulangan", payload);


window.getSoalWithKey = (payload) => apiCall("getSoalWithKey", payload);