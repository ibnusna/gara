



async function verifyExamHistory() {
  const namaSiswa = sessionStorage.getItem("siswaNama");
  const startBtn = document.getElementById("startFullscreenBtn");

  
  if (!namaSiswa || !startBtn) {
    console.error(
      "Nama siswa atau tombol mulai tidak ditemukan di halaman summary."
    );
    return;
  }

  try {
    
    startBtn.disabled = true;
    startBtn.innerHTML =
      '<i class="fas fa-spinner fa-spin"></i> Memuat Soal...';

    const result = await checkStudentHistory(namaSiswa);

    if (result.hasSubmitted) {
      
      startBtn.textContent = "Ujian Telah Selesai";
      

      Swal.fire({
        title: "Anda Sudah Mengerjakan Ujian",
        text: "Anda telah menyelesaikan ujian ini dan tidak dapat mengulanginya.",
        icon: "error",
        confirmButtonText: "Mengerti",
        allowOutsideClick: false,
      });
    } else {
      
      startBtn.disabled = false;
      startBtn.innerHTML = 'Mulai Ujian <i class="fas fa-play"></i>';
    }
  } catch (error) {
    
    console.error("Gagal memeriksa riwayat ujian:", error);
    startBtn.disabled = true; 
    startBtn.textContent = "Gagal Memeriksa";

    Swal.fire({
      title: "Gagal Memeriksa Riwayat",
      text: "Tidak dapat terhubung ke server untuk verifikasi. Silakan muat ulang halaman atau hubungi admin.",
      icon: "warning",
      confirmButtonText: "Tutup",
    });
  }
}
