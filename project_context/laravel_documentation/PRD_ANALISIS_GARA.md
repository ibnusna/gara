Produk: GARA — Garuda Akademi
Modul: Exam Engine / Ruang Asesmen (Bank Soal)
Framework: Laravel 12 & Vanilla JS / Vue (Sesuai arsitektur frontend)
Database: MySQL — mysql_asesmen
Status: Bugfix & UX Enhancement
Prioritas: High (Critical for Exam Readability)
Target: Penyempurnaan format teks soal (Rich Text & Multi-line Support)
Role terdampak: guru (Creator), operator (Reviewer/Validator), siswa (Consumer)

1. LATAR BELAKANG

Sistem Ujian (CBT/Exam Engine) GARA saat ini mengalami masalah pada rendering (penampilan) teks soal. Berdasarkan keluhan pengguna:

Ketika guru menginput soal yang memiliki banyak baris, paragraf (seperti soal cerita/literasi), teks tersebut tergabung menjadi satu paragraf panjang dan terpotong/tidak terbaca dengan baik di sisi siswa dan operator. Garis baru (newline) tidak dieksekusi oleh sistem.

Setelah dilakukan audit arsitektur pada database mysql_asesmen, kolom konten_soal pada tabel bank_soal dan ujian_bank_soal sudah menggunakan tipe data LONGTEXT yang didesain untuk menyimpan struktur HTML. Masalah ini dipastikan terjadi pada siklus Input ➔ Controller ➔ Rendering (UI) yang masih memperlakukan input soal sebagai Plain Text.

Oleh karena itu, diperlukan integrasi Rich Text Editor dan penyempurnaan metode rendering HTML di seluruh modul yang terlibat.

2. TUJUAN PRODUK

Mengizinkan Guru menginput soal dengan format kaya (Rich Text): paragraf, cetak tebal, cetak miring, list (bullet/numbering), dan baris baru.

Memastikan soal ditampilkan dengan hierarki dan spasi yang persis sama saat direview oleh Operator dan saat dikerjakan oleh Siswa.

Mencegah kerentanan keamanan XSS (Cross-Site Scripting) saat sistem mengizinkan penyimpanan teks berformat HTML.

3. KONDISI SISTEM SAAT INI (AS-IS)

Form Guru: Kemungkinan besar masih menggunakan tag <textarea name="konten_soal"></textarea> standar. Saat ditekan 'Enter', ia mengirim karakter \n ke backend.

View Blade/JS: Saat dirender di halaman Siswa atau Operator, variabel dicetak menggunakan format escaping standar Laravel {{ $soal->konten_soal }} atau textContent pada JavaScript. Format ini mengabaikan karakter \n dan HTML, sehingga teks tampil bersambung tanpa break.

Database: Tabel bank_soal kolom konten_soal (Tipe: LONGTEXT) ➔ Sudah sesuai, tidak perlu modifikasi ALTER TABLE.

4. TARGET ARCHITECTURE & SCOPE PENGERJAAN

Pengerjaan dibagi ke dalam 3 zona/modul:

4.1 ZONA 1: Modul Guru (Input Soal)

Perubahan UI/UX: Mengganti <textarea> biasa di form tambah/edit soal menjadi WYSIWYG Rich Text Editor.

Rekomendasi Library: Quill.js (Ringan & modern), Summernote, atau CKEditor 5.

Implementasi:
Guru tidak lagi mengetik \n biasa, melainkan editor akan otomatis mengonversi Enter menjadi <p> atau <br>.

4.2 ZONA 2: Backend (Controller & Sanitasi)

Karena kita sekarang menerima input berupa tag HTML, kita harus mencegah guru (atau pihak nakal) memasukkan script jahat <script>alert('hack')</script>.

Perubahan Controller: BankSoalController atau API Endpoint terkait.

Implementasi HTML Purifier: Gunakan library seperti mews/purifier (Laravel) untuk membersihkan tag HTML yang masuk sebelum di-save() ke database.

// Contoh implementasi di Controller
$konten_kotor = $request->input('konten_soal');
$konten_bersih = clean($konten_kotor); // Membersihkan script berbahaya, menyisakan <p>, <br>, <b>, dll.

BankSoal::create([
    'konten_soal' => $konten_bersih,
    // ... atribut lainnya
]);


4.3 ZONA 3: Modul Operator & Siswa (Rendering / Display)

Ini adalah tahap paling krusial agar teks tampil rapi.

Jika menggunakan Blade (Server-side rendering):
Ubah kode {{ $soal->konten_soal }} menjadi {!! $soal->konten_soal !!}. Tanda seru ganda di Laravel memberitahu engine untuk mengeksekusi tag HTML di dalamnya.

Jika menggunakan JavaScript/AJAX/API (Exam Engine SPA):
Saat memuat soal dari API, jangan gunakan element.innerText atau element.textContent. Gunakan element.innerHTML = data.konten_soal.

Styling (CSS):
Tambahkan class CSS pembungkus (misal: .soal-content) yang memiliki properti:

.soal-content p {
    margin-bottom: 1rem; /* Memberi jarak antar paragraf */
}


5. KASUS UJI (TEST CASES)

TC-EXAM-RICH-001: Input Soal Multi-Paragraf

Aktor: Guru

Aksi: Menginput soal cerita dengan 3 paragraf berbeda menggunakan tombol "Enter" di editor.

Ekspektasi: Data tersimpan di database dengan dibungkus tag <p> atau dipisah <br>. (pastikan autosave juga ya 1 teks apapun yg ada di layer teks ketika di refresh ga ilang pakai mekanisme save localstorage dulu aja)

TC-EXAM-RICH-002: Rendering di Layar Siswa

Aktor: Siswa

Aksi: Membuka soal cerita (hasil dari TC-EXAM-RICH-001) di Arena Ujian.

Ekspektasi: Soal terbaca rapi, terbagi menjadi 3 paragraf, tidak overlap, dan bagian bawah soal (pertanyaan inti) tidak terpotong.

TC-EXAM-RICH-003: XSS Protection (Security)

Aktor: Guru / Penyerang

Aksi: Mencoba memasukkan payload <script>alert(1)</script> pada source HTML soal.

Ekspektasi: Backend membuang tag <script> tersebut, dan saat dirender di layar Siswa/Operator, tidak ada popup alert yang muncul (Aman).

6. DEFINITION OF DONE (DoD)

[ ] Library WYSIWYG Editor berhasil diimplementasikan di halaman Create/Edit soal Guru.

[ ] Middleware/Sanitizer HTML Purifier berjalan di BankSoalController.

[ ] Halaman Review soal di Operator merender HTML dengan benar {!! !!}.

[ ] UI Exam Engine di sisi Siswa berhasil merender paragraf HTML dengan jarak yang nyaman dibaca (innerHTML).

[ ] Tidak ada eksekusi SQL Migration yang diperlukan karena arsitektur DB sudah mendukung LONGTEXT.
