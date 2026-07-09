






const KEYS = {
    SISWA: 'siswaData',
    UJIAN: 'ujianData',
    SOAL: 'shuffledSoal',
    JAWABAN: 'jawabanSiswa',
    RAGU_RAGU: 'raguRaguStatus',
    CURRENT_INDEX: 'currentSoalIndex',
    END_TIME: 'examEndTime',
    HASIL: 'hasilUjian',
    ROLE: 'role',
    SAVED_NIS: 'savedNIS'
};



const getSession = (key, defaultValue = null) => {
    try {
        const item = sessionStorage.getItem(key);
        return item ? JSON.parse(item) : defaultValue;
    } catch (e) {
        console.warn(`Gagal parsing session key: ${key}`, e);
        return defaultValue;
    }
};

const setSession = (key, value) => {
    try {
        sessionStorage.setItem(key, JSON.stringify(value));
    } catch (e) {
        console.error(`Gagal menyimpan session key: ${key}`, e);
    }
};



export const Store = {
    
    getSiswa: () => getSession(KEYS.SISWA),
    setSiswa: (data) => setSession(KEYS.SISWA, data),

    getUjianMeta: () => getSession(KEYS.UJIAN),
    setUjianMeta: (data) => setSession(KEYS.UJIAN, data),

    isAuthenticated: () => {
        const siswa = getSession(KEYS.SISWA);
        const ujian = getSession(KEYS.UJIAN);
        return !!(siswa && ujian);
    },

    
    getSoalList: () => getSession(KEYS.SOAL, []),
    setSoalList: (list) => setSession(KEYS.SOAL, list),

    getJawaban: () => getSession(KEYS.JAWABAN, []),
    saveJawaban: (index, answerKey) => {
        const currentAnswers = getSession(KEYS.JAWABAN, []);
        if (index >= currentAnswers.length) currentAnswers.length = index + 1;
        currentAnswers[index] = answerKey;
        setSession(KEYS.JAWABAN, currentAnswers);
    },
    initJawaban: (length) => {
        const emptyAnswers = new Array(length).fill(null);
        setSession(KEYS.JAWABAN, emptyAnswers);
    },

    
    getRaguRagu: () => getSession(KEYS.RAGU_RAGU, []),
    setRaguRagu: (index, status) => {
        const currentStatus = getSession(KEYS.RAGU_RAGU, []);
        if (index >= currentStatus.length) currentStatus.length = index + 1;
        currentStatus[index] = status;
        setSession(KEYS.RAGU_RAGU, currentStatus);
    },
    initRaguRagu: (length) => {
        const emptyStatus = new Array(length).fill(false);
        setSession(KEYS.RAGU_RAGU, emptyStatus);
    },

    
    getCurrentIndex: () => parseInt(sessionStorage.getItem(KEYS.CURRENT_INDEX) || '0', 10),
    setCurrentIndex: (index) => sessionStorage.setItem(KEYS.CURRENT_INDEX, index),

    getExamEndTime: () => sessionStorage.getItem(KEYS.END_TIME),
    setExamEndTime: (timestamp) => sessionStorage.setItem(KEYS.END_TIME, timestamp),

    
    getHasil: () => getSession(KEYS.HASIL),
    setHasil: (data) => setSession(KEYS.HASIL, data),

    
    getSavedNIS: () => localStorage.getItem(KEYS.SAVED_NIS),
    setSavedNIS: (nis) => localStorage.setItem(KEYS.SAVED_NIS, nis),

    



    cleanupAfterSubmit: () => {
        sessionStorage.removeItem(KEYS.END_TIME);
        
    },

    


    clearExamSession: () => {
        sessionStorage.removeItem(KEYS.SOAL);
        sessionStorage.removeItem(KEYS.JAWABAN);
        sessionStorage.removeItem(KEYS.RAGU_RAGU);
        sessionStorage.removeItem(KEYS.CURRENT_INDEX);
        sessionStorage.removeItem(KEYS.END_TIME);
        sessionStorage.removeItem(KEYS.HASIL);
    },

    
    clearAll: () => {
        sessionStorage.clear();
        
        localStorage.removeItem('exam_id_map');
        localStorage.removeItem('exam_token');
    }
};