  <footer class="main-footer">
    <div class="container">
        <div class="float-right d-none d-sm-inline">
            GARA LMS v1.0
        </div>
        <strong>Copyright &copy; <?= date('Y') ?> Garuda Akademi.</strong>
    </div>
  </footer>
</div>
<!-- ./wrapper -->

<!-- REQUIRED SCRIPTS -->
<!-- jQuery -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php if(isset($use_datatables) && $use_datatables): ?>
    <!-- DataTables & Plugins -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<?php endif; ?>

<!-- Cek Pesan Notifikasi dari URL (misal: belum pilih sesi) -->
<script>
    const urlParams = new URLSearchParams(window.location.search);
    if(urlParams.get('msg') === 'pilih_sesi_dulu'){
        Swal.fire({
            icon: 'warning',
            title: 'Pilih Sesi',
            text: 'Anda harus memilih kelas terlebih dahulu sebelum mengakses menu tersebut.'
        });
    }

    // Session Flash Messages
    <?php if(isset($_SESSION['success_message'])): ?>
        Swal.fire({ icon: 'success', title: 'Berhasil', text: '<?= $_SESSION['success_message'] ?>', timer: 1500, showConfirmButton: false });
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>
    <?php if(isset($_SESSION['error_message'])): ?>
        Swal.fire({ icon: 'error', title: 'Gagal', text: '<?= $_SESSION['error_message'] ?>' });
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>
</script>
</body>
</html>
