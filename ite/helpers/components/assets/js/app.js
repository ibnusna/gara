document.addEventListener("DOMContentLoaded", function () {
  const urlParams = new URLSearchParams(window.location.search);
  const status = urlParams.get("status");
  const msg = urlParams.get("msg");

  if (status === "success") {
    Swal.fire({
      icon: "success",
      title: "Berhasil!",
      text: "Data berhasil disimpan.",
      timer: 2000,
      showConfirmButton: false,
    });
  } else if (status === "error") {
    let errorMessage = "Terjadi kesalahan. Silakan coba lagi.";
    if (msg === "duplikat") {
      errorMessage =
        "Data untuk sesi ini sudah ada. Tidak dapat menyimpan data duplikat.";
    } else if (msg === "data_tidak_lengkap") {
      errorMessage =
        "Semua kolom wajib diisi. Mohon periksa kembali formulir Anda.";
    } else if (msg === "db_error") {
      errorMessage = "Terjadi kesalahan pada database.";
    }

    Swal.fire({
      icon: "error",
      title: "Gagal!",
      text: errorMessage,
    });
  }

  const newUrl = window.location.pathname;
  history.replaceState(null, null, newUrl);
});
