    <footer class="main-footer">
        <strong>Copyright &copy; <?= date('Y') ?> Garuda Akademi.</strong>
    </footer>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- DataTables Scripts (Optional) -->
<?php if(isset($use_datatables) && $use_datatables): ?>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
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
