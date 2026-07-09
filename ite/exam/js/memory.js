const MemoryModule = (function () {
  const STORAGE_KEY_PREFIX = "ujian_progress_v2_";
  let studentId = "";

  




  const init = (namaSiswa, nis) => {
    
    const cleanNama = typeof namaSiswa === 'string' ? namaSiswa.trim() : "Peserta";
    const cleanNIS = typeof nis === 'string' ? nis.trim() : "000";

    
    
    const safeNama = cleanNama.replace(/[^a-zA-Z0-9]/g, "_");
    const safeNIS = cleanNIS.replace(/[^a-zA-Z0-9]/g, "");

    studentId = `${STORAGE_KEY_PREFIX}${safeNama}_${safeNIS}`;
  };


  




  const saveProgress = (originalSoal, jawabanSiswa) => {
    if (!studentId) return;
    try {
      
      const cleanJawaban = jawabanSiswa.map(ans => {
        if (Array.isArray(ans)) return ans;
        if (ans === undefined || ans === null) return null;
        return ans;
      });

      const progress = {
        jawabanSiswa: cleanJawaban,
        timestamp: new Date().getTime(),
      };

      const serialized = JSON.stringify(progress);
      localStorage.setItem(studentId, serialized);

      
      syncToServer(cleanJawaban);

    } catch (e) {
      console.error("Gagal menyimpan progres ujian (Quota/Format):", e);
    }
  };

  



  let syncTimeout = null;
  const syncToServer = (jawaban) => {
    
    clearTimeout(syncTimeout);
    syncTimeout = setTimeout(() => {
      const apiUrl = window.__EXAM_API_URL__;
      const token = sessionStorage.getItem('exam_token'); 

      if (!apiUrl || !token) return;

      const formData = new FormData();
      formData.append('action', 'save_draft');
      formData.append('token', token);
      formData.append('jawaban', JSON.stringify(jawaban));

      fetch(apiUrl, {
        method: 'POST',
        body: formData,
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          console.log("[GARA Sync] Progres berhasil disinkronkan ke server.");
        } else {
          console.warn("[GARA Sync] Gagal sinkron ke server:", data.message);
        }
      })
      .catch(err => {
        console.warn("[GARA Sync] Offline / Koneksi gagal. Progres hanya tersimpan lokal.");
      });
    }, 2000); 
  };

  



  const loadProgress = () => {
    if (!studentId) return null;
    try {
      const savedData = localStorage.getItem(studentId);
      if (!savedData) return null;

      const parsedData = JSON.parse(savedData);

      
      if (parsedData && Array.isArray(parsedData.jawabanSiswa)) {
        return parsedData;
      }
      return null;
    } catch (e) {
      console.error("Gagal memuat progres ujian (Corrupt Data):", e);
      
      localStorage.removeItem(studentId);
      return null;
    }
  };

  


  const clearProgress = () => {
    if (!studentId) return;
    localStorage.removeItem(studentId);
  };

  return {
    init,
    saveProgress,
    loadProgress,
    clearProgress,
  };
})();
