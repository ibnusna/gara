

const DB_NAME = 'LMS_Offline_DB';
const DB_VERSION = 1;
const STORE_SISWA = 'siswa_cache';
const STORE_NILAI_PENDING = 'nilai_pending';

let db;


const request = indexedDB.open(DB_NAME, DB_VERSION);

request.onupgradeneeded = function (event) {
    db = event.target.result;

    
    if (!db.objectStoreNames.contains(STORE_SISWA)) {
        db.createObjectStore(STORE_SISWA, { keyPath: 'id' });
    }

    
    if (!db.objectStoreNames.contains(STORE_NILAI_PENDING)) {
        db.createObjectStore(STORE_NILAI_PENDING, { autoIncrement: true });
    }
};

request.onsuccess = function (event) {
    db = event.target.result;
    console.log("IndexedDB Siap digunakan.");
    checkPendingData(); 
};

request.onerror = function (event) {
    console.error("IndexedDB Error:", event.target.errorCode);
};


function cacheDataSiswa(siswaList) {
    const transaction = db.transaction([STORE_SISWA], 'readwrite');
    const store = transaction.objectStore(STORE_SISWA);

    
    store.clear();

    siswaList.forEach(siswa => {
        store.add(siswa);
    });
    console.log("Data siswa berhasil dicache untuk offline.");
}


function simpanNilaiOffline(dataNilai) {
    return new Promise((resolve, reject) => {
        const transaction = db.transaction([STORE_NILAI_PENDING], 'readwrite');
        const store = transaction.objectStore(STORE_NILAI_PENDING);

        
        dataNilai.createdAt = new Date().getTime();
        dataNilai.synced = false;

        const request = store.add(dataNilai);

        request.onsuccess = () => {
            Swal.fire({
                icon: 'info',
                title: 'Mode Offline',
                text: 'Data disimpan di browser. Akan dikirim saat online.'
            });
            resolve(true);
        };

        request.onerror = () => {
            reject("Gagal menyimpan ke IndexedDB");
        };
    });
}


function syncDataToServer() {
    const transaction = db.transaction([STORE_NILAI_PENDING], 'readonly');
    const store = transaction.objectStore(STORE_NILAI_PENDING);
    const getAllRequest = store.getAll();

    getAllRequest.onsuccess = function () {
        const pendingData = getAllRequest.result;

        if (pendingData.length > 0 && navigator.onLine) {
            console.log(`Mengirim ${pendingData.length} data pending...`);

            
            $.ajax({
                url: 'actions/sync_offline_data.php', 
                method: 'POST',
                data: { payload: JSON.stringify(pendingData) },
                success: function (response) {
                    console.log("Sync Berhasil!");
                    
                    const tx = db.transaction([STORE_NILAI_PENDING], 'readwrite');
                    tx.objectStore(STORE_NILAI_PENDING).clear();

                    Swal.fire({
                        icon: 'success',
                        title: 'Online Kembali!',
                        text: 'Data offline berhasil disinkronkan ke server.'
                    });
                }
            });
        }
    };
}


window.addEventListener('online', syncDataToServer);
window.addEventListener('offline', () => {
    Swal.fire({
        icon: 'warning',
        title: 'Koneksi Terputus',
        text: 'Anda sekarang dalam Mode Offline. Data akan disimpan lokal.'
    });
});

function checkPendingData() {
    if (navigator.onLine) {
        syncDataToServer();
    }
}

