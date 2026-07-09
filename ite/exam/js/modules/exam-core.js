











export const shuffleArray = (array) => {
    
    const newArray = [...array];
    for (let i = newArray.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [newArray[i], newArray[j]] = [newArray[j], newArray[i]];
    }
    return newArray;
};








export const prepareQuestions = (rawSoalList) => {
    if (!Array.isArray(rawSoalList)) return [];

    return rawSoalList.map((soal, index) => {
        const pilihan = ["a", "b", "c", "d", "e"]
            .filter(key => soal[key])
            .map((key, idx) => ({ 
                key: key.toUpperCase(),
                originalKey: key.toUpperCase(),
                originalIndex: idx, 
                text: soal[key]
            }));

        return {
            originalIndex: index,
            id_soal: soal.id_soal, 
            tipe: soal.tipe || "PG",
            pertanyaan: soal.soal,
            pilihan: pilihan,
            kunciJawaban: soal.kunci,
            assets: soal.assets || []
        };
    });
};





const isAnswerFilled = (val) => {
    
    if (val === null || val === undefined || val === "") return false;

    
    if (Array.isArray(val)) return val.length > 0;

    return true;
};








export const getExamStats = (jawaban, raguRagu) => {
    
    const safeJawaban = Array.isArray(jawaban) ? jawaban : [];
    const safeRagu = Array.isArray(raguRagu) ? raguRagu : [];

    const total = safeJawaban.length;

    
    const terisi = safeJawaban.filter(isAnswerFilled).length;

    const ragu = safeRagu.filter(r => r === true).length;
    const kosong = total - terisi;

    return { total, terisi, kosong, ragu };
};








export const formatSubmitData = (siswa, meta, jawaban, soalList) => {
    const cleanedJawaban = jawaban.map((val, idx) => {
        
        
        
        const soal = soalList.find(s => s.originalIndex === idx);

        if (!soal) return null; 

        let finalAns = val;

        if (val === null || val === undefined || val === "") {
            finalAns = "";
        }
        else if (soal.tipe === "BENAR_SALAH" && Array.isArray(val)) {
            
            const originalOrder = new Array(soal.pilihan.length).fill("");
            soal.pilihan.forEach((opt, visualIdx) => {
                const studentAnswer = val[visualIdx] || "";
                originalOrder[opt.originalIndex] = studentAnswer;
            });
            finalAns = originalOrder;
        }
        else if (Array.isArray(val)) {
            
            finalAns = val.map(subVal => {
                if (subVal === null || subVal === undefined || subVal === "") return "";
                const matchedOption = soal.pilihan.find(p => p.key === subVal);
                return matchedOption ? matchedOption.originalKey : subVal;
            });
        }
        else if (soal.tipe === "PG") {
            const matchedOption = soal.pilihan.find(p => p.key === val);
            finalAns = matchedOption ? matchedOption.originalKey : val;
        }

        
        return {
            id_soal: soal.id_soal,
            jawaban: finalAns
        };
    }).filter(ans => ans !== null);

    return {
        nis: siswa.NIS,
        nama: siswa["Nama Lengkap"],
        kelas: siswa.Kelas,
        topik: meta.topik,
        sheetSoal: meta.sheetSoal,
        jawaban: cleanedJawaban
    };
};