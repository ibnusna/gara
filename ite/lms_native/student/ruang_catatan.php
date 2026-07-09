<?php
require_once '../actions/auth_helper.php';

$page_title = 'Ruang Catatan | Garuda Akademi';
if (!is_ajax_request()) {
    require_once 'templates/header.php';
}
?>

<!-- Custom App CSS -->
<link rel="stylesheet" href="css/root.css">
<link rel="stylesheet" href="css/catatan.css">

<!-- Override Container Height for Navbar Compatibility -->
<style>
    /* Adjust app container to fit between header and footer/bottom of screen */
    .app-container {
        height: calc(100vh - 60px); /* Approx header height */
        margin-top: 0;
    }
    
    /* Ensure footer nav doesn't overlap content on mobile */
    @media (max-width: 768px) {
        .app-container {
            height: calc(100vh - 60px - 70px); /* Header - Bottom Nav */
        }
    }
</style>

<!-- Sidebar Overlay for Mobile -->
<div class="sidebar-overlay" onclick="toggleSidebar()"></div>

<div class="app-container">
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <!-- Header removed as requested -->
        <div class="sidebar-actions">
            <button class="btn btn-primary" onclick="createNewPage()">
                <i class="fas fa-plus"></i> Halaman Baru
            </button>
        </div>
        <div class="pages-list" id="pagesList">
            <!-- Pages will be rendered here -->
        </div>
        <div class="sidebar-footer">
            <button class="btn" onclick="showTemplates()" style="background: #f1f5f9; color: #475569; margin-bottom: 8px;">
                <i class="fas fa-layer-group"></i> Template
            </button>
            <button class="btn" onclick="exportData()" style="background: #f1f5f9; color: #475569;">
                <i class="fas fa-print"></i> Print / PDF
            </button>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="editor-header">
            <button class="toggle-sidebar" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            <input type="text" class="page-title-input" id="pageTitle" placeholder="Judul Halaman" disabled>
            <div class="editor-actions">
                <button class="icon-btn" onclick="searchNotes()" title="Cari">
                    <i class="fas fa-search"></i>
                </button>
                <button class="icon-btn" onclick="deletePage()" title="Hapus Halaman">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
        <div class="editor-body" id="editorBody">
            <div class="empty-state">
                <i class="fas fa-file-alt"></i>
                <h3>Selamat Datang di Ruang Catatan</h3>
                <p>Buat halaman baru atau pilih halaman yang ada untuk mulai mencatat</p>
                <button class="btn btn-primary" onclick="createNewPage()" style="display: inline-flex; width: auto;">
                    <i class="fas fa-plus"></i> Buat Halaman Pertama
                </button>
            </div>
        </div>
    </main>
</div>

<script>
    const STORAGE_KEY = 'garuda_akademi_ruang_catatan';
    let currentPageId = null;
    let pages = [];

    const defaultConfig = {
        app_title: "Garuda Akademi",
        workspace_title: "Ruang Catatan Saya",
        primary_color: "#2563eb",
        background_color: "#f8f9fa",
        sidebar_color: "#ffffff",
        text_color: "#2c3e50",
        accent_color: "#1e3c72"
    };

    const blockTypes = [
        { type: 'paragraph', icon: 'fas fa-paragraph', label: 'Paragraf' },
        { type: 'heading1', icon: 'fas fa-heading', label: 'Heading 1' },
        { type: 'heading2', icon: 'fas fa-heading', label: 'Heading 2' },
        { type: 'heading3', icon: 'fas fa-heading', label: 'Heading 3' },
        { type: 'bullet', icon: 'fas fa-list-ul', label: 'Bullet List' },
        { type: 'numbered', icon: 'fas fa-list-ol', label: 'Numbered List' },
        { type: 'checklist', icon: 'fas fa-check-square', label: 'Checklist' },
        { type: 'quote', icon: 'fas fa-quote-left', label: 'Quote' },
        { type: 'code', icon: 'fas fa-code', label: 'Code Block' },
        { type: 'divider', icon: 'fas fa-minus', label: 'Divider' }
    ];

    const templates = [
        {
            name: 'Catatan Mata Pelajaran',
            icon: 'fas fa-book',
            description: 'Template untuk mencatat materi pelajaran',
            blocks: [
                { type: 'heading1', content: 'Nama Mata Pelajaran' },
                { type: 'heading2', content: 'Topik: ' },
                { type: 'paragraph', content: 'Tanggal: ' },
                { type: 'divider', content: '' },
                { type: 'heading3', content: 'Poin Utama' },
                { type: 'bullet', content: 'Poin 1' },
                { type: 'bullet', content: 'Poin 2' },
                { type: 'heading3', content: 'Catatan Tambahan' },
                { type: 'paragraph', content: '' }
            ]
        },
        {
            name: 'Ringkasan Bab',
            icon: 'fas fa-bookmark',
            description: 'Ringkas isi bab dengan struktur',
            blocks: [
                { type: 'heading1', content: 'Ringkasan Bab' },
                { type: 'heading2', content: 'Bab: ' },
                { type: 'heading3', content: 'Konsep Utama' },
                { type: 'paragraph', content: '' },
                { type: 'heading3', content: 'Rumus / Definisi Penting' },
                { type: 'code', content: '' },
                { type: 'heading3', content: 'Kesimpulan' },
                { type: 'paragraph', content: '' }
            ]
        },
        {
            name: 'To-Do Tugas',
            icon: 'fas fa-tasks',
            description: 'Daftar tugas yang harus dikerjakan',
            blocks: [
                { type: 'heading1', content: 'Daftar Tugas' },
                { type: 'heading3', content: 'Tugas Prioritas' },
                { type: 'checklist', content: 'Tugas 1' },
                { type: 'checklist', content: 'Tugas 2' },
                { type: 'checklist', content: 'Tugas 3' },
                { type: 'heading3', content: 'Deadline' },
                { type: 'paragraph', content: '' }
            ]
        },
        {
            name: 'Catatan Guru',
            icon: 'fas fa-chalkboard-teacher',
            description: 'Catat penjelasan Guru',
            blocks: [
                { type: 'heading1', content: 'Catatan Pelajaran' },
                { type: 'paragraph', content: 'Guru: ' },
                { type: 'paragraph', content: 'Tanggal: ' },
                { type: 'divider', content: '' },
                { type: 'heading3', content: 'Isi Pelajaran' },
                { type: 'paragraph', content: '' },
                { type: 'quote', content: 'Kutipan penting dari Guru' }
            ]
        },
        {
            name: 'Jurnal Belajar',
            icon: 'fas fa-pen',
            description: 'Refleksi proses belajar',
            blocks: [
                { type: 'heading1', content: 'Jurnal Belajar' },
                { type: 'paragraph', content: 'Tanggal: ' },
                { type: 'heading3', content: 'Apa yang Dipelajari Hari Ini' },
                { type: 'paragraph', content: '' },
                { type: 'heading3', content: 'Kesulitan yang Dihadapi' },
                { type: 'paragraph', content: '' },
                { type: 'heading3', content: 'Rencana Selanjutnya' },
                { type: 'checklist', content: 'Rencana 1' }
            ]
        }
    ];

    function loadData() {
        const stored = localStorage.getItem(STORAGE_KEY);
        if (stored) {
            try {
                pages = JSON.parse(stored);
            } catch (e) {
                pages = [];
            }
        }
        renderPagesList();
    }

    function saveData() {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(pages));
    }

    function generateId() {
        return Date.now().toString(36) + Math.random().toString(36).substr(2);
    }

    function createNewPage() {
        const newPage = {
            id: generateId(),
            title: 'Halaman Tanpa Judul',
            blocks: [],
            created_at: new Date().toISOString(),
            updated_at: new Date().toISOString()
        };
        pages.push(newPage);
        saveData();
        renderPagesList();
        openPage(newPage.id);
    }

    function renderPagesList() {
        const container = document.getElementById('pagesList');
        if (pages.length === 0) {
            container.innerHTML = '<div style="padding: 20px; text-align: center; color: #94a3b8; font-size: 13px;">Belum ada halaman</div>';
            return;
        }

        container.innerHTML = pages.map(page => `
            <div class="page-item ${currentPageId === page.id ? 'active' : ''}" onclick="openPage('${page.id}')">
                <i class="fas fa-file-alt"></i>
                <span>${page.title}</span>
                <div class="page-actions">
                    <button class="icon-btn" onclick="event.stopPropagation(); renamePage('${page.id}')" title="Rename">
                        <i class="fas fa-pen"></i>
                    </button>
                    <button class="icon-btn" onclick="event.stopPropagation(); deletePageConfirm('${page.id}')" title="Hapus">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `).join('');
    }

    function openPage(pageId) {
        currentPageId = pageId;
        const page = pages.find(p => p.id === pageId);
        if (!page) return;

        document.getElementById('pageTitle').value = page.title;
        document.getElementById('pageTitle').disabled = false;
        renderPagesList();
        renderBlocks(page);
    }

    function renderBlocks(page) {
        const editorBody = document.getElementById('editorBody');
        
        if (page.blocks.length === 0) {
            editorBody.innerHTML = `
                <div style="padding: 40px 0;">
                    <button class="add-block-btn" onclick="showBlockTypeMenu(event)">
                        <i class="fas fa-plus"></i> Tambah Blok
                    </button>
                </div>
            `;
            return;
        }

        editorBody.innerHTML = page.blocks.map((block, index) => {
            if (block.type === 'divider') {
                return `
                    <div class="block" data-type="divider" data-index="${index}">
                        <div class="block-controls">
                            <button class="icon-btn" onclick="deleteBlock(${index})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        <hr>
                    </div>
                `;
            }
            
            if (block.type === 'checklist') {
                return `
                    <div class="block" data-type="checklist" data-index="${index}">
                        <div class="block-controls">
                            <button class="icon-btn" onclick="deleteBlock(${index})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        <input type="checkbox" ${block.checked ? 'checked' : ''} onchange="toggleCheck(${index})">
                        <div class="block-content" contenteditable="true" data-placeholder="Item checklist..." oninput="updateBlock(${index})">${block.content}</div>
                    </div>
                `;
            }

            return `
                <div class="block" data-type="${block.type}" data-index="${index}">
                    <div class="block-controls">
                        <button class="icon-btn" onclick="deleteBlock(${index})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    <div class="block-content" contenteditable="true" data-placeholder="Tulis sesuatu..." oninput="updateBlock(${index})">${block.content}</div>
                </div>
            `;
        }).join('') + `
            <button class="add-block-btn" onclick="showBlockTypeMenu(event)">
                <i class="fas fa-plus"></i> Tambah Blok
            </button>
        `;
    }

    function updateBlock(index) {
        if (!currentPageId) return;
        const page = pages.find(p => p.id === currentPageId);
        if (!page) return;

        const blockEl = document.querySelector(`.block[data-index="${index}"] .block-content`);
        page.blocks[index].content = blockEl.textContent;
        page.updated_at = new Date().toISOString();
        saveData();
    }

    function deleteBlock(index) {
        if (!currentPageId) return;
        const page = pages.find(p => p.id === currentPageId);
        if (!page) return;

        page.blocks.splice(index, 1);
        page.updated_at = new Date().toISOString();
        saveData();
        renderBlocks(page);
    }

    function toggleCheck(index) {
        if (!currentPageId) return;
        const page = pages.find(p => p.id === currentPageId);
        if (!page) return;

        page.blocks[index].checked = !page.blocks[index].checked;
        page.updated_at = new Date().toISOString();
        saveData();
    }

    function showBlockTypeMenu(event) {
        event.preventDefault();
        
        const menuHtml = `
            <div class="block-type-menu" id="blockTypeMenu">
                ${blockTypes.map(type => `
                    <div class="block-type-item" onclick="addBlock('${type.type}')">
                        <i class="${type.icon}"></i>
                        <span>${type.label}</span>
                    </div>
                `).join('')}
            </div>
        `;

        const existing = document.getElementById('blockTypeMenu');
        if (existing) existing.remove();

        const menu = document.createElement('div');
        menu.innerHTML = menuHtml;
        document.body.appendChild(menu.firstElementChild);

        const menuEl = document.getElementById('blockTypeMenu');
        const rect = event.target.getBoundingClientRect();
        menuEl.style.position = 'fixed';
        menuEl.style.left = rect.left + 'px';
        menuEl.style.top = (rect.top - menuEl.offsetHeight - 8) + 'px';

        setTimeout(() => {
            document.addEventListener('click', function closeMenu(e) {
                if (!menuEl.contains(e.target)) {
                    menuEl.remove();
                    document.removeEventListener('click', closeMenu);
                }
            });
        }, 0);
    }

    function addBlock(type) {
        if (!currentPageId) return;
        const page = pages.find(p => p.id === currentPageId);
        if (!page) return;

        const newBlock = {
            id: generateId(),
            type: type,
            content: '',
            checked: false
        };

        page.blocks.push(newBlock);
        page.updated_at = new Date().toISOString();
        saveData();
        renderBlocks(page);

        document.getElementById('blockTypeMenu')?.remove();
    }

    function renamePage(pageId) {
        const page = pages.find(p => p.id === pageId);
        if (!page) return;

        Swal.fire({
            title: 'Rename Halaman',
            input: 'text',
            inputValue: page.title,
            showCancelButton: true,
            confirmButtonText: 'Simpan',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#2563eb',
            inputValidator: (value) => {
                if (!value) {
                    return 'Judul tidak boleh kosong';
                }
            }
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                page.title = result.value;
                page.updated_at = new Date().toISOString();
                saveData();
                renderPagesList();
                if (currentPageId === pageId) {
                    document.getElementById('pageTitle').value = result.value;
                }
            }
        });
    }

    function deletePageConfirm(pageId) {
        Swal.fire({
            title: 'Hapus Halaman?',
            text: 'Halaman yang dihapus tidak dapat dikembalikan',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Hapus',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#64748b'
        }).then((result) => {
            if (result.isConfirmed) {
                pages = pages.filter(p => p.id !== pageId);
                saveData();
                if (currentPageId === pageId) {
                    currentPageId = null;
                    document.getElementById('editorBody').innerHTML = `
                        <div class="empty-state">
                            <i class="fas fa-file-alt"></i>
                            <h3>Pilih halaman untuk mulai mencatat</h3>
                        </div>
                    `;
                    document.getElementById('pageTitle').value = '';
                    document.getElementById('pageTitle').disabled = true;
                }
                renderPagesList();
                Swal.fire('Terhapus!', 'Halaman berhasil dihapus', 'success');
            }
        });
    }

    function deletePage() {
        if (!currentPageId) {
            Swal.fire('Peringatan', 'Tidak ada halaman yang dipilih', 'warning');
            return;
        }
        deletePageConfirm(currentPageId);
    }

    function showTemplates() {
        const templateHtml = `
            <div class="template-grid">
                ${templates.map((template, index) => `
                    <div class="template-card" onclick="useTemplate(${index})">
                        <i class="${template.icon}"></i>
                        <h4>${template.name}</h4>
                        <p>${template.description}</p>
                    </div>
                `).join('')}
            </div>
        `;

        Swal.fire({
            title: 'Pilih Template',
            html: templateHtml,
            width: '700px',
            showConfirmButton: false,
            showCloseButton: true
        });
    }

    function useTemplate(templateIndex) {
        const template = templates[templateIndex];
        const newPage = {
            id: generateId(),
            title: template.name,
            blocks: template.blocks.map(block => ({
                ...block,
                id: generateId(),
                checked: false
            })),
            created_at: new Date().toISOString(),
            updated_at: new Date().toISOString()
        };

        pages.push(newPage);
        saveData();
        renderPagesList();
        openPage(newPage.id);
        Swal.close();
    }

    function searchNotes() {
        Swal.fire({
            title: 'Cari Catatan',
            input: 'text',
            inputPlaceholder: 'Ketik kata kunci...',
            showCancelButton: true,
            confirmButtonText: 'Cari',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#2563eb'
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                const keyword = result.value.toLowerCase();
                const results = pages.filter(page => {
                    const titleMatch = page.title.toLowerCase().includes(keyword);
                    const contentMatch = page.blocks.some(block => 
                        block.content.toLowerCase().includes(keyword)
                    );
                    return titleMatch || contentMatch;
                });

                if (results.length === 0) {
                    Swal.fire('Tidak Ditemukan', 'Tidak ada catatan yang cocok', 'info');
                } else {
                    const resultHtml = results.map(page => `
                        <div style="padding: 12px; margin-bottom: 8px; background: #f8fafc; border-radius: 6px; cursor: pointer; text-align: left;" onclick="openPage('${page.id}'); Swal.close();">
                            <strong>${page.title}</strong><br>
                            <small style="color: #64748b;">${page.blocks.length} blok</small>
                        </div>
                    `).join('');

                    Swal.fire({
                        title: `Ditemukan ${results.length} hasil`,
                        html: resultHtml,
                        width: '500px',
                        showConfirmButton: false,
                        showCloseButton: true
                    });
                }
            }
        });
    }

    function exportData() {
        window.print();
    }

    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.querySelector('.sidebar-overlay');
        const isMobile = window.innerWidth <= 768;

        if (isMobile) {
            sidebar.classList.toggle('mobile-open');
            overlay.classList.toggle('active');
        } else {
            sidebar.classList.toggle('collapsed');
        }
    }

    document.getElementById('pageTitle').addEventListener('input', function() {
        if (!currentPageId) return;
        const page = pages.find(p => p.id === currentPageId);
        if (!page) return;

        page.title = this.value || 'Halaman Tanpa Judul';
        page.updated_at = new Date().toISOString();
        saveData();
        renderPagesList();
    });

    // Element SDK Config Handler (Optional, left for compatibility if needed)
    async function onConfigChange(config) {
        const appTitle = config.app_title || defaultConfig.app_title;
        const workspaceTitle = config.workspace_title || defaultConfig.workspace_title;
        const primaryColor = config.primary_color || defaultConfig.primary_color;
        const accentColor = config.accent_color || defaultConfig.accent_color;
        
        document.getElementById('appTitle').textContent = appTitle;
        document.getElementById('workspaceTitle').textContent = workspaceTitle;

        // Note: Inline styles are less ideal here but kept for SDK compatibility
        const style = document.createElement('style');
        style.textContent = `
            .btn-primary {
                background: ${primaryColor} !important;
            }
            .btn-primary:hover {
                background: ${accentColor} !important;
            }
            .page-item.active {
                background: ${primaryColor}1a !important;
                color: ${primaryColor} !important;
            }
            .sidebar-header {
                background: linear-gradient(135deg, ${accentColor} 0%, ${primaryColor} 100%) !important;
            }
        `;
        document.head.appendChild(style);
    }

    if (window.elementSdk) {
        window.elementSdk.init({
            defaultConfig: defaultConfig,
            onConfigChange: onConfigChange,
            mapToCapabilities: (config) => ({
                recolorables: [],
                borderables: [],
                fontEditable: undefined,
                fontSizeable: undefined
            }),
            mapToEditPanelValues: (config) => new Map([
                ["app_title", config.app_title || defaultConfig.app_title],
                ["workspace_title", config.workspace_title || defaultConfig.workspace_title]
            ])
        });
    }

    loadData();
</script>

<?php 
if (!is_ajax_request()) {
    require_once 'templates/footer.php';
}
?>