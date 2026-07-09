    <!-- Sidebar -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="#" class="brand-link">
            <span class="brand-text font-weight-light pl-3"><b>Garuda </b>Akademi</span>
        </a>
        <div class="sidebar">
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image"><img src="../img/guru.webp" class="img-circle elevation-2" alt="User Image"></div>
                <div class="info"><a href="#" class="d-block"><?= isset($_SESSION['nama']) ? htmlspecialchars($_SESSION['nama']) : 'Guru' ?></a></div>
            </div>
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                    <li class="nav-item">
                        <a href="dashboard.php" class="nav-link <?= (isset($active_menu) && $active_menu == 'dashboard') ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-tachometer-alt"></i><p>Dashboard</p>
                        </a>
                    </li>
                    <li class="nav-header">MANAJEMEN KELAS</li>

                    <li class="nav-item">
                        <a href="ruang_diskusi.php" class="nav-link <?= (isset($active_menu) && $active_menu == 'diskusi') ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-comments"></i><p>Ruang Diskusi</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="ruang_tugas.php" class="nav-link <?= (isset($active_menu) && $active_menu == 'tugas') ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-tasks"></i><p>Ruang Tugas</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="https://garudakademi.netlify.app/" class="nav-link <?= (isset($active_menu) && $active_menu == 'ujian') ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-edit"></i><p>Ruang Ujian</p>
                        </a>
                    </li>
                                        <li class="nav-item">
                        <a href="ruang_materi.php" class="nav-link <?= (isset($active_menu) && $active_menu == 'materi') ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-book-open"></i><p>Ruang Materi</p>
                        </a>
                    </li>
                    <li class="nav-header">LAINNYA</li>
                    <li class="nav-item">
                        <a href="pilih_sesi.php" class="nav-link bg-secondary">
                            <i class="nav-icon fas fa-exchange-alt"></i><p>Ganti Kelas</p>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>
