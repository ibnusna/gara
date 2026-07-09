const SecurityCheckModule = (function () {
  const STORAGE_KEY_PREFIX = "ujian_selesai_";

  





  function generateStorageKey(nama, kelas) {
    const normalizedNama = nama.trim().toLowerCase();
    const normalizedKelas = kelas.trim().toLowerCase();
    return `${STORAGE_KEY_PREFIX}${normalizedNama}_${normalizedKelas}`;
  }

  





  function hasCompletedLocally(nama, kelas) {
    const key = generateStorageKey(nama, kelas);
    return localStorage.getItem(key) === "true";
  }

  




  function markAsCompleted(nama, kelas) {
    const key = generateStorageKey(nama, kelas);
    localStorage.setItem(key, "true");
  }

  return {
    hasCompletedLocally,
    markAsCompleted,
  };
})();
