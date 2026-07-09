








var API_URL = (typeof window.API_URL !== 'undefined') ? window.API_URL : '../actions/diskusi_api.php';
var currentPage = 1;
var isLoading = false;
var hasNextPage = true;
var currentThreadId = null; 
var lastThreadId = 0; 
var lastReplyId = 0; 
var pollingInterval;


var CURRENT_USER_ID = (typeof CURRENT_USER !== 'undefined') ? CURRENT_USER.id : 0;

function getConsistentAvatar(name) {
    if (!name) return `https://ui-avatars.com/api/?name=Siswa&background=7f8c8d&color=fff&size=128&bold=true`;
    const colors = [
        '1abc9c', '2ecc71', '3498db', '9b59b6', '34495e',
        '16a085', '27ae60', '2980b9', '8e44ad', '2c3e50',
        'f1c40f', 'e67e22', 'e74c3c', '95a5a6', 'f39c12',
        'd35400', 'c0392b', '7f8c8d', '5c6bc0', 'ec407a'
    ];
    let hash = 0;
    for (let i = 0; i < name.length; i++) {
        hash = (hash + name.charCodeAt(i)) % 100000;
    }
    const index = hash % colors.length;
    const bg = colors[index];
    return `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=${bg}&color=fff&size=128&bold=true`;
}

$(document).ready(function () {
    
    loadThreads(true);

    
    
    setupModalEvents();
    setupPostingEvents();
    setupReplyInputEvents();
    setupModalPullToRefresh();
    setupDynamicEvents();

    
    $(document).off('input', '.auto-textarea').on('input', '.auto-textarea', function () {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
        $('#char-count').text($(this).val().length + " karakter");
    });

    
    if (!window.PTR_BOUND) {
        setupPullToRefresh();
        window.PTR_BOUND = true;
    }

    
    if (!window.POPSTATE_BOUND) {
        window.addEventListener('popstate', function (e) {
            if (e.state && e.state.modal === 'detail') {
                
                if (e.state.threadId && document.getElementById('modal-detail')) {
                    if (!$('#modal-detail').hasClass('active')) {
                        openDetailThread(e.state.threadId, false);
                    }
                }
            } else {
                
                if ($('#modal-detail').hasClass('active')) {
                    $('#modal-detail').removeClass('active');
                    currentThreadId = null;
                }
            }
        });
        window.POPSTATE_BOUND = true;
    }

    
    $(document).off('click', '#btn-load-more').on('click', '#btn-load-more', function () {
        if (!isLoading && hasNextPage) {
            currentPage++;
            loadThreads(false);
        }
    });

    
    if (window.pollingInterval) clearInterval(window.pollingInterval);
    startPolling();
});






function loadThreads(reset = false, isPull = false) {
    if (isLoading) return;
    isLoading = true;

    if (reset) {
        currentPage = 1;
        if (!isPull) {
            $('#feed-container').html('<div style="padding:40px; text-align:center;"><i class="fas fa-circle-notch fa-spin text-primary"></i></div>');
            $('#load-more-area').hide();
        }
    } else {
        $('#btn-load-more').text('Memuat...').prop('disabled', true);
    }

    $.ajax({
        url: API_URL,
        type: 'GET',
        data: { action: 'get_threads', page: currentPage },
        dataType: 'json',
        success: function (res) {
            if (res.status === 'success') {
                if (reset) $('#feed-container').empty();

                if (res.data.length === 0 && reset) {
                    $('#feed-container').html(`
                        <div class="empty-feed-placeholder" style="text-align:center; padding:50px 20px; color:var(--text-secondary);">
                            <i class="fa-regular fa-comment-dots fa-3x mb-3"></i>
                            <p>Belum ada diskusi. Jadilah yang pertama!</p>
                        </div>
                    `);
                } else {
                    res.data.forEach(thread => {
                        if (thread.id > lastThreadId) lastThreadId = thread.id;
                        $('#feed-container').append(renderThreadHTML(thread));
                    });
                }

                hasNextPage = res.meta.has_next;
                if (hasNextPage) {
                    $('#load-more-area').show();
                    $('#btn-load-more').text('Muat lebih banyak').prop('disabled', false);
                } else {
                    $('#load-more-area').hide();
                }
            } else {
                console.error("API Error: ", res.message);
                if (reset) {
                    $('#feed-container').html(`
                        <div class="error-feed-placeholder" style="text-align:center; padding:50px 20px; color:var(--text-secondary);">
                            <i class="fa-solid fa-triangle-exclamation fa-3x mb-3 text-danger"></i>
                            <p>${res.message || 'Gagal memuat diskusi. Silakan coba lagi.'}</p>
                        </div>
                    `);
                }
            }
        },
        error: function (err) {
            console.error(err);
            if (reset) $('#feed-container').html('<p class="text-center text-danger p-4">Gagal memuat data.</p>');
        },
        complete: function () {
            isLoading = false;
            if (isPull && window.resetPullToRefresh) {
                window.resetPullToRefresh();
            }
        }
    });
}






function renderThreadHTML(t) {
    let nama, avatar, badge = '', handle, isGuru = t.is_guru;

    if (isGuru) {
        nama = t.nama_penulis || 'Guru Pengajar';
        avatar = t.avatar || getConsistentAvatar(nama);
        badge = '<i class="fa-solid fa-circle-check verified-badge ml-1" style="color:var(--color-primary); font-size:14px;" title="Terverifikasi"></i>';
        handle = '@guru_mapel';
    } else {
        nama = t.nama_penulis || 'Siswa Tidak Dikenal';
        avatar = t.avatar || getConsistentAvatar(nama);
        handle = t.nis_siswa ? '@' + t.nis_siswa : '@siswa';
    }


    let mediaHtml = '';
    if (t.media_path) {
        mediaHtml = `<div class="post-media"><img src="${window.BASE_URL}lms/${t.media_path}" loading="lazy" class="img-fluid"></div>`;
    }

    
    let menuHtml = '';
    if (t.is_me) {
        let safeContent = (t.isi_konten || '').replace(/"/g, '&quot;');
        menuHtml = `
            <div class="menu-dropdown-wrapper">
                <button class="post-menu-btn icon-btn-small btn-trigger-menu">
                    <i class="fas fa-ellipsis-h"></i>
                </button>
                <div class="dropdown-menu-custom">
                    <div class="dropdown-item-custom btn-edit-thread" data-id="${t.id}" data-content="${safeContent}">
                        <i class="fas fa-pen"></i> Edit Postingan
                    </div>
                    <div class="dropdown-item-custom text-danger btn-delete-thread" data-id="${t.id}">
                        <i class="fas fa-trash"></i> Hapus Postingan
                    </div>
                </div>
            </div>
        `;
    } else if (isGuru && t.is_pinned) {
        menuHtml = `<div class="pinned-indicator"><i class="fas fa-thumbtack" style="color:#f6c23e;"></i></div>`;
    }

    return `
    <article class="feed-item" data-id="${t.id}">
        <img src="${avatar}" class="avatar" style="flex-shrink:0">
        <div class="post-content">
            <div class="post-header-stacked">
                <div class="header-info">
                    <div class="top-row">
                        <span class="name" style="font-weight:700; color:var(--text-primary); font-size:15px;">${nama}</span>
                        ${badge}
                    </div>
                    <div class="bottom-row" style="font-size:13px; color:var(--text-secondary); margin-top:2px;">
                        <span class="username">${handle}</span>
                        <span class="separator">&bull;</span>
                        <span class="time">${t.waktu_relatif}</span>
                    </div>
                </div>
                ${menuHtml}
            </div>
            
            <div class="post-text" style="margin-top:4px;">${linkify(t.isi_konten)}</div>
            ${mediaHtml}

            <div class="post-actions">
                <button class="action-item blue btn-action-reply">
                    <div class="action-icon-wrapper"><i class="fa-regular fa-comment"></i></div>
                    <span>${t.jumlah_balasan > 0 ? t.jumlah_balasan : ''}</span>
                </button>
                <button class="action-item green btn-cosmetic">
                    <div class="action-icon-wrapper"><i class="fa-solid fa-retweet"></i></div>
                </button>
                <button class="action-item pink btn-cosmetic">
                    <div class="action-icon-wrapper"><i class="fa-regular fa-heart"></i></div>
                </button>
                <button class="action-item blue btn-action-share">
                    <div class="action-icon-wrapper"><i class="fa-solid fa-share-nodes"></i></div>
                </button>
            </div>
        </div>
    </article>
    `;
}






function setupDynamicEvents() {

    
    if ($('#custom-dropdown-style').length === 0) {
        $('head').append(`
            <style id="custom-dropdown-style">
                .post-header-stacked { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 4px; }
                .header-info { display: flex; flex-direction: column; line-height:1.3; }
                .top-row { display: flex; align-items: center; gap: 4px; }
                .bottom-row { font-size: 13px; color: var(--text-secondary); }
                .separator { margin: 0 4px; }
                
                .menu-dropdown-wrapper { position: relative; }
                .icon-btn-small { width: 30px; height: 30px; border-radius: 50%; border: none; background: transparent; color: var(--text-secondary); cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background 0.2s; }
                .icon-btn-small:hover { background: rgba(29, 161, 242, 0.1); color: var(--color-primary); }
                
                .dropdown-menu-custom { 
                    display: none; position: absolute; right: 0; top: 100%; 
                    background: white; border-radius: 12px; box-shadow: 0 0 15px rgba(0,0,0,0.15); 
                    min-width: 180px; z-index: 100; overflow: hidden; border: 1px solid #eee;
                }
                .dropdown-menu-custom.show { display: block; }
                .dropdown-item-custom { padding: 12px 16px; cursor: pointer; font-size: 14px; display: flex; align-items: center; gap: 10px; font-weight: 500; transition: background 0.2s; color:#0f1419; }
                .dropdown-item-custom:hover { background: #f7f9f9; }
                .dropdown-item-custom.text-danger { color: #e74a3b; }
            </style>
        `);
    }

    
    $(document).off('click', '.feed-item').on('click', '.feed-item', function (e) {
        if ($(e.target).closest('.menu-dropdown-wrapper, .post-actions').length === 0) {
            const id = $(this).data('id');
            openDetailThread(id);
        }
    });

    
    $(document).off('click', '.btn-trigger-menu').on('click', '.btn-trigger-menu', function (e) {
        e.stopPropagation();
        $('.dropdown-menu-custom').not($(this).next('.dropdown-menu-custom')).removeClass('show');
        $(this).next('.dropdown-menu-custom').toggleClass('show');
    });

    
    $(document).off('click', '.btn-edit-thread').on('click', '.btn-edit-thread', function (e) {
        e.stopPropagation();
        const id = $(this).data('id');
        const content = $(this).data('content');
        $('.dropdown-menu-custom').removeClass('show');
        openEditThread(id, content);
    });

    
    $(document).off('click', '.btn-delete-thread').on('click', '.btn-delete-thread', function (e) {
        e.stopPropagation();
        const id = $(this).data('id');
        $('.dropdown-menu-custom').removeClass('show');

        Swal.fire({
            title: 'Hapus Postingan?',
            text: "Postingan akan dihapus secara permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post(API_URL, { action: 'delete_thread', id: id }, function (res) {
                    if (res.status === 'success') {
                        Swal.fire(
                            'Terhapus!',
                            'Postingan berhasil dihapus.',
                            'success'
                        );
                        loadThreads(true);
                    } else {
                        Swal.fire(
                            'Gagal!',
                            res.message || 'Gagal menghapus postingan',
                            'error'
                        );
                    }
                }, 'json');
            }
        });
    });

    
    $(document).off('click', '.post-actions button').on('click', '.post-actions button', function (e) {
        e.stopPropagation();
    });

    
    $(document).off('click', '.btn-cosmetic').on('click', '.btn-cosmetic', function () {
        const btn = $(this);
        const icon = btn.find('i');
        if (btn.hasClass('active')) {
            btn.removeClass('active');
            if (btn.hasClass('pink')) {
                icon.removeClass('fa-solid').addClass('fa-regular').css('font-weight', '');
                btn.removeClass('liked');
            }
            if (btn.hasClass('green')) { btn.removeClass('reposted'); }
        } else {
            btn.addClass('active');
            if (btn.hasClass('pink')) {
                icon.removeClass('fa-regular').addClass('fa-solid').css('font-weight', '900');
                btn.addClass('liked');
                icon.css('transform', 'scale(1.2)');
                setTimeout(() => icon.css('transform', 'scale(1)'), 200);
            }
            if (btn.hasClass('green')) { btn.addClass('reposted'); }
        }
    });

    
    $(document).off('click', '.btn-action-share').on('click', '.btn-action-share', function () {
        
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true
        });
        Toast.fire({
            icon: 'success',
            title: 'Link disalin ke clipboard!'
        });
    });

    
    $(window).click(function () {
        $('.dropdown-menu-custom').removeClass('show');
        $('.reply-dropdown-menu').removeClass('show');
    });

    
    $(document).off('click', '.main-post-media img, .reply-item-media img').on('click', '.main-post-media img, .reply-item-media img', function(e) {
        e.stopPropagation();
        openFullscreenImage($(this).attr('src'));
    });
}

function openFullscreenImage(src) {
    if ($('#gara-fullscreen-modal').length === 0) {
        $('body').append(`
            <div id="gara-fullscreen-modal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; z-index:999999; background:rgba(0,0,0,0.7); backdrop-filter:blur(10px); -webkit-backdrop-filter:blur(10px); align-items:center; justify-content:center; cursor:pointer;">
                <button style="position:absolute; top:20px; right:20px; background:rgba(0,0,0,0.5); border:none; color:white; width:44px; height:44px; border-radius:50%; font-size:24px; cursor:pointer; display:flex; align-items:center; justify-content:center; z-index:1000000; transition:background 0.2s;">
                    <i class="fas fa-times"></i>
                </button>
                <img id="gara-fullscreen-image" src="" style="max-width:95vw; max-height:95vh; object-fit:contain; border-radius:8px; box-shadow:0 10px 30px rgba(0,0,0,0.5); transition:transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);">
            </div>
        `);
        
        $('#gara-fullscreen-modal').click(function() {
            $(this).fadeOut(200);
            $('#gara-fullscreen-image').css('transform', 'scale(0.95)');
        });
        
        $('#gara-fullscreen-modal button').hover(
            function() { $(this).css('background', 'rgba(255,255,255,0.2)'); },
            function() { $(this).css('background', 'rgba(0,0,0,0.5)'); }
        );
    }
    
    $('#gara-fullscreen-image').attr('src', src).css('transform', 'scale(0.95)');
    $('#gara-fullscreen-modal').css('display', 'flex').hide().fadeIn(200, function() {
        $('#gara-fullscreen-image').css('transform', 'scale(1)');
    });
}






function setupPullToRefresh() {
    let startY = 0;
    const ptrThreshold = 70; 
    const $ptr = $('#ptr-spinner');
    const $icon = $('#ptr-icon');
    const $loading = $('#ptr-loading');
    const $text = $('#ptr-text');
    let isPulling = false;

    
    document.addEventListener('touchstart', function (e) {
        
        if (window.scrollY === 0) {
            startY = e.touches[0].clientY;
            isPulling = false;
        }
    }, { passive: true });

    document.addEventListener('touchmove', function (e) {
        const currentY = e.touches[0].clientY;
        const diff = currentY - startY;

        
        if (window.scrollY === 0 && diff > 0) {
            isPulling = true;

            
            if (diff < 200) {
                const height = diff / 2.5; // Resistance factor
                $ptr.css('height', height + 'px');
                $ptr.css('border-bottom', '1px solid var(--border-color)');

                
                if (height > 50) {
                    $icon.css('transform', 'rotate(180deg)');
                    $text.text('Lepas untuk menyegarkan');
                } else {
                    $icon.css('transform', 'rotate(0deg)');
                    $text.text('Tarik untuk menyegarkan');
                }

                
                
                if (e.cancelable && diff > 10) {
                    e.preventDefault();
                }
            }
        }
    }, { passive: false }); 

    document.addEventListener('touchend', function (e) {
        if (!isPulling) return;

        const height = parseInt($ptr.css('height'));
        if (height > 50) {
            
            $ptr.css('height', '60px'); 
            $icon.hide();
            $loading.show();
            $text.text('Memuat...');

            
            loadThreads(true, true);
        } else {
            if (typeof window.resetPullToRefresh === 'function') {
                window.resetPullToRefresh();
            }
        }

        isPulling = false;
    });

    
    window.resetPullToRefresh = function () {
        setTimeout(() => {
            $ptr.css('height', '0px');
            $ptr.css('border-bottom', '1px solid transparent');
            setTimeout(() => {
                $icon.show();
                $loading.hide();
                $icon.css('transform', 'rotate(0deg)');
                $text.text('Tarik untuk menyegarkan');
            }, 300);
        }, 500); 
    }
}






function setupModalPullToRefresh() {
    let startY = 0;
    const $ptr = $('#ptr-spinner-modal');
    const $icon = $('#ptr-icon-modal');
    const $loading = $('#ptr-loading-modal');
    
    const $scrollContainer = $('#modal-scroll-content'); 
    let isPulling = false;

    
    const container = document.getElementById('modal-scroll-content');
    if (!container) return; 

    container.addEventListener('touchstart', function (e) {
        if (container.scrollTop === 0) {
            startY = e.touches[0].clientY;
            isPulling = false;
        }
    }, { passive: true });

    container.addEventListener('touchmove', function (e) {
        const currentY = e.touches[0].clientY;
        const diff = currentY - startY;

        if (container.scrollTop === 0 && diff > 0) {
            isPulling = true;
            if (diff < 150) {
                const height = diff / 2.5;
                $ptr.css('height', height + 'px');
                $ptr.css('border-bottom', '1px solid var(--border-color)');
                if (height > 40) {
                    $icon.css('transform', 'rotate(180deg)');
                } else {
                    $icon.css('transform', 'rotate(0deg)');
                }
                if (e.cancelable && diff > 10) e.preventDefault();
            }
        }
    }, { passive: false });

    container.addEventListener('touchend', function (e) {
        if (!isPulling) return;
        const height = parseInt($ptr.css('height'));
        if (height > 40) {
            
            $ptr.css('height', '50px');
            $icon.hide();
            $loading.show();

            
            if (currentThreadId) {
                
                openDetailThread(currentThreadId, false);
                
                
                
                
                
                
                
                
                
                

                setTimeout(() => {
                    resetModalPtr();
                }, 1500);
            } else {
                resetModalPtr();
            }

        } else {
            resetModalPtr();
        }
        isPulling = false;
    });

    function resetModalPtr() {
        $ptr.css('height', '0px');
        $ptr.css('border-bottom', '1px solid transparent');
        setTimeout(() => {
            $icon.show();
            $loading.hide();
            $icon.css('transform', 'rotate(0deg)');
        }, 300);
    }
}






function setupModalEvents() {
    
    $('#trigger-post-modal, #trigger-image-modal').on('click touchstart', function (e) {
        e.preventDefault(); 
        $('#modal-post').addClass('active');
        
        setTimeout(() => $('#post-textarea').focus(), 300);
    });

    $('#btn-close-modal').on('click touchstart', function (e) {
        e.preventDefault();
        $('#modal-post').removeClass('active');
    });

    $('#btn-close-detail').on('click touchstart', function (e) {
        e.preventDefault();
        if(window.history.state && window.history.state.modal === 'detail') {
            window.history.back();
        } else {
            $('#modal-detail').removeClass('active');
            currentThreadId = null;
        }
    });

    
    $(document).off('click touchstart', '.modal-overlay').on('click touchstart', '.modal-overlay', function (e) {
        if ($(e.target).hasClass('modal-overlay')) {
            $(this).removeClass('active');
        }
    });
}

function setupPostingEvents() {
    
    $('#file-input').change(function () {
        const file = this.files[0];
        if (!file) return;

        
        const allowedExts  = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        const allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        const ext = file.name.split('.').pop().toLowerCase();
        const mime = file.type.toLowerCase();

        if (!allowedExts.includes(ext) || !allowedMimes.includes(mime)) {
            
            $(this).val('');
            $('#preview-container').hide();
            $('#img-preview').attr('src', '');

            Swal.fire({
                icon: 'error',
                title: 'Format File Tidak Diizinkan',
                html: `<p>Hanya gambar yang boleh dilampirkan di diskusi.</p>
                       <p><strong>Format diterima:</strong> JPG, PNG, WEBP, GIF</p>
                       <p><strong>File Anda:</strong> <code>${file.name}</code> (${mime || 'tipe tidak dikenal'})</p>`,
                confirmButtonColor: '#2563eb'
            });
            return;
        }

        
        if (file.size > 5 * 1024 * 1024) {
            $(this).val('');
            Swal.fire({
                icon: 'warning',
                title: 'File Terlalu Besar',
                text: `Ukuran file maksimal 5 MB. File Anda: ${(file.size / (1024*1024)).toFixed(2)} MB`,
                confirmButtonColor: '#2563eb'
            });
            return;
        }

        
        let reader = new FileReader();
        reader.onload = function (e) {
            $('#img-preview').attr('src', e.target.result);
            $('#preview-container').show();
        };
        reader.readAsDataURL(file);
    });

    $('#btn-remove-img').click(function () {
        $('#file-input').val('');
        $('#preview-container').hide();
        $('#img-preview').attr('src', '');
    });

    $('#post-textarea').on('input', function () {
        let val = $(this).val().trim();
        if (val.length > 0) $('#btn-submit-post').addClass('active');
        else $('#btn-submit-post').removeClass('active');
    });

    
    $('#btn-submit-post').click(function () {
        const btn = $(this);
        const form = $('#form-create-thread')[0];
        const formData = new FormData(form);
        btn.removeClass('active').text('Mengirim...');

        $.ajax({
            url: API_URL,
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function (res) {
                if (res.status === 'success') {
                    form.reset();
                    $('#preview-container').hide();
                    $('#modal-post').removeClass('active');

                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true
                    });
                    Toast.fire({
                        icon: 'success',
                        title: 'Postingan berhasil dibuat!'
                    });

                    if (res.data) {
                        const newThread = res.data;
                        if (newThread.id > lastThreadId) lastThreadId = newThread.id;
                        if ($('#feed-container').find('.empty-feed-placeholder').length > 0) {
                            $('#feed-container').empty();
                        }
                        const html = renderThreadHTML(newThread);
                        $(html).hide().prependTo('#feed-container').fadeIn('slow');
                    } else {
                        loadThreads(true);
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Postingan Gagal',
                        text: res.message || 'Gagal membuat postingan'
                    });
                }
            },
            
            error: function (xhr) {
                let msg = 'Gagal mengirim data ke server.';
                try {
                    const errData = JSON.parse(xhr.responseText);
                    if (errData.message) msg = errData.message;
                } catch (e) {}
                Swal.fire({
                    icon: 'error',
                    title: 'Upload Ditolak',
                    text: msg,
                    confirmButtonColor: '#dc2626'
                });
                
                $('#file-input').val('');
                $('#preview-container').hide();
                $('#img-preview').attr('src', '');
            },
            complete: function () {
                btn.text('Posting');
            }
        });
    });
}






window.openEditThread = function (id, content) {
    $('#edit-type').val('thread');
    $('#edit-id').val(id);
    $('#edit-textarea').val(content);
    $('#modal-edit').addClass('active');
    setTimeout(() => $('#edit-textarea').focus(), 100);
}


$('#btn-save-edit').off('click').on('click', function () {
    const type = $('#edit-type').val();
    const id = $('#edit-id').val();
    const content = $('#edit-textarea').val().trim();
    const btn = $(this);

    if (!content) {
        if (!content) {
            Swal.fire({
                icon: 'warning',
                title: 'Konten Kosong',
                text: 'Konten tidak boleh kosong',
                timer: 2000,
                showConfirmButton: false
            });
            return;
        }
    }

    btn.text('Menyimpan...').prop('disabled', true);

    if (type === 'thread') {
        $.post(API_URL, {
            action: 'edit_thread',
            id: id,
            isi_konten: content
        }, function (res) {
            if (res.status === 'success') {
                $('#modal-edit').removeClass('active');
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Postingan berhasil diedit',
                    timer: 1500,
                    showConfirmButton: false
                });
                loadThreads(true);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: res.message || 'Gagal menyimpan perubahan'
                });
            }
        }, 'json')
            .always(function () {
                btn.text('Simpan').prop('disabled', false);
            });
    } else if (type === 'reply') {
        $.post(API_URL, {
            action: 'edit_reply',
            id: id,
            isi_balasan: content
        }, function (res) {
            if (res.status === 'success') {
                $('#modal-edit').removeClass('active');
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Balasan berhasil diedit',
                    timer: 1500,
                    showConfirmButton: false
                });
                
                if (currentThreadId) {
                    openDetailThread(currentThreadId);
                }
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: res.message || 'Gagal menyimpan perubahan'
                });
            }
        }, 'json')
            .always(function () {
                btn.text('Simpan').prop('disabled', false);
            });
    }
});






window.openDetailThread = function (threadId, pushState = true) {
    currentThreadId = threadId;

    if (pushState) {
        const state = { modal: 'detail', threadId: threadId };
        window.history.pushState(state, '', window.location.pathname + '#thread-' + threadId);
    }

    
    $('#modal-detail').css('display', '').addClass('active');

    
    $('.modal-scroll-content').scrollTop(0);

    $('#detail-main-post').html('<div class="loading-spinner"><i class="fas fa-circle-notch fa-spin"></i><p>Memuat...</p></div>');
    $('#detail-replies-list').html('<div class="loading-spinner"><i class="fas fa-circle-notch fa-spin"></i><p>Memuat...</p></div>');

    
    $('#input-reply-text').val('');
    $('#reply-file-input').val('');
    $('#reply-image-preview').hide();

    
    $.ajax({
        url: API_URL,
        data: { action: 'get_thread_detail', thread_id: threadId },
        success: function (res) {
            if (res.status === 'success') {
                renderMainPost(res.data);
            } else {
                $('#detail-main-post').html('<div class="empty-state"><i class="fa-solid fa-exclamation-triangle"></i><p>Postingan tidak ditemukan</p></div>');
            }
        },
        error: function () {
            $('#detail-main-post').html('<div class="empty-state"><i class="fa-solid fa-exclamation-triangle"></i><p>Gagal memuat postingan</p></div>');
        }
    });

    
    loadReplies(threadId);
}






function renderMainPost(thread) {
    let nama, avatar, badge = '', handle, isGuru = thread.is_guru;

    if (isGuru) {
        nama = thread.nama_penulis || 'Guru Pengajar';
        avatar = thread.avatar || getConsistentAvatar(nama);
        badge = '<i class="fa-solid fa-circle-check verified-badge" style="color:var(--color-primary); font-size:16px;"></i>';
        handle = '@guru_mapel';
    } else {
        nama = thread.nama_penulis || 'Siswa Tidak Dikenal';
        avatar = thread.avatar || getConsistentAvatar(nama);
        handle = thread.nis_siswa ? '@' + thread.nis_siswa : '@siswa';
    }

    let mediaHtml = '';
    if (thread.media_path) {
        mediaHtml = `<div class="main-post-media"><img src="${window.BASE_URL}lms/${thread.media_path}" alt="Media"></div>`;
    }

    
    let menuHtml = '';
    if (thread.is_me) {
        let safeContent = thread.isi_konten.replace(/"/g, '&quot;');
        menuHtml = `
            <button class="post-menu-btn" onclick="openEditThread(${thread.id}, '${safeContent}')">
                <i class="fas fa-ellipsis-h"></i>
            </button>
        `;
    }

    let html = `
        <div class="main-post-header">
            <img src="${avatar}" alt="${nama}" class="main-post-avatar">
            <div class="main-post-user-info">
                <div class="main-name-wrapper">
                    <div class="main-name-link">
                        <span class="main-name">${nama}</span>
                        ${badge}
                    </div>
                    ${menuHtml}
                </div>
                <span class="main-handle">${handle}</span>
            </div>
        </div>
        
        <div class="main-post-text">${linkify(thread.isi_konten)}</div>
        ${mediaHtml}
        
        <div class="main-post-meta">
            <span class="meta-time">${thread.waktu_lengkap || thread.waktu_relatif}</span>
        </div>
        
        <div class="main-post-stats">
            <div class="stat-item">
                <strong>${thread.jumlah_balasan || 0}</strong>
                <span>Balasan</span>
            </div>
        </div>
        
        <div class="main-post-actions">
            <button class="main-action-btn comment-main-btn" onclick="$('#input-reply-text').focus()">
                <i class="fa-regular fa-comment"></i>
            </button>
            <button class="main-action-btn repost-main-btn">
                <i class="fa-solid fa-retweet"></i>
            </button>
            <button class="main-action-btn like-main-btn">
                <i class="fa-regular fa-heart"></i>
            </button>
            <button class="main-action-btn bookmark-main-btn">
                <i class="fa-regular fa-bookmark"></i>
            </button>
            <button class="main-action-btn share-main-btn" onclick="let timerInterval; Swal.fire({title: 'Link disalin!', timer: 1000, showConfirmButton: false, icon: 'success'})">
                <i class="fa-solid fa-share"></i>
            </button>
        </div>
    `;

    $('#detail-main-post').html(html);

    
    setupMainPostInteractions();
}




function setupMainPostInteractions() {
    $('.like-main-btn').off('click').on('click', function () {
        const btn = $(this);
        const icon = btn.find('i');
        if (btn.hasClass('liked')) {
            btn.removeClass('liked');
            icon.removeClass('fa-solid').addClass('fa-regular');
        } else {
            btn.addClass('liked');
            icon.removeClass('fa-regular').addClass('fa-solid');
            icon.css('transform', 'scale(1.2)');
            setTimeout(() => icon.css('transform', 'scale(1)'), 200);
        }
    });

    $('.repost-main-btn').off('click').on('click', function () {
        const btn = $(this);
        if (btn.hasClass('reposted')) {
            btn.removeClass('reposted');
        } else {
            btn.addClass('reposted');
        }
    });

    $('.bookmark-main-btn').off('click').on('click', function () {
        const btn = $(this);
        const icon = btn.find('i');
        if (icon.hasClass('fa-regular')) {
            icon.removeClass('fa-regular').addClass('fa-solid');
        } else {
            icon.removeClass('fa-solid').addClass('fa-regular');
        }
    });
}






function loadReplies(threadId) {
    $.ajax({
        url: API_URL,
        data: { action: 'get_replies', thread_id: threadId },
        success: function (res) {
            if (res.status === 'success') {
                if (res.data.length === 0) {
                    $('#detail-replies-list').html('<div class="empty-state"><i class="fa-regular fa-comment-dots"></i><p>Belum ada balasan. Jadilah yang pertama!</p></div>');
                } else {
                    let html = '';
                    res.data.forEach(reply => {
                        if (reply.id > lastReplyId) lastReplyId = reply.id;
                        html += renderReplyHTML(reply, threadId);
                    });
                    $('#detail-replies-list').html(html);
                }
            } else {
                $('#detail-replies-list').html('<div class="empty-state"><i class="fa-solid fa-exclamation-triangle"></i><p>Gagal memuat balasan</p></div>');
            }
        },
        error: function () {
            $('#detail-replies-list').html('<div class="empty-state"><i class="fa-solid fa-exclamation-triangle"></i><p>Gagal memuat balasan</p></div>');
        }
    });
}






function renderReplyHTML(reply, threadId) {
    let nama, avatar, badge = '', handle, isGuru = reply.is_guru;

    if (isGuru) {
        nama = reply.nama_penulis || 'Guru Pengajar';
        avatar = reply.avatar || getConsistentAvatar(nama);
        badge = '<i class="fa-solid fa-circle-check verified-badge" style="color:var(--color-primary); font-size:14px;"></i>';
        handle = '@guru_mapel';
    } else {
        nama = reply.nama_penulis || 'Siswa';
        avatar = reply.avatar || getConsistentAvatar(nama);
        handle = reply.nis_siswa ? '@' + reply.nis_siswa : '@siswa';
    }

    let mediaHtml = '';
    if (reply.media_path) {
        mediaHtml = `<div class="reply-item-media"><img src="${window.BASE_URL}lms/${reply.media_path}" alt="Media"></div>`;
    }

    
    let menuHtml = '';
    if (reply.is_me) {
        let safeContent = (reply.isi_balasan || '').replace(/"/g, '&quot;');
        menuHtml = `
            <div class="reply-menu-wrapper">
                <button class="reply-menu-btn btn-trigger-reply-menu">
                    <i class="fas fa-ellipsis-h"></i>
                </button>
                <div class="reply-dropdown-menu">
                    <div class="reply-dropdown-item btn-edit-reply" data-id="${reply.id}" data-content="${safeContent}">
                        <i class="fas fa-pen"></i> Edit Balasan
                    </div>
                    <div class="reply-dropdown-item text-danger btn-delete-reply" data-id="${reply.id}" data-thread-id="${threadId}">
                        <i class="fas fa-trash"></i> Hapus Balasan
                    </div>
                </div>
            </div>
        `;
    }

    return `
        <div class="reply-item">
            <div class="reply-avatar-wrapper">
                <img src="${avatar}" alt="${nama}" class="reply-item-avatar">
            </div>
            <div class="reply-item-content">
                <div class="reply-item-header">
                    <div class="reply-user-info">
                        <div class="top-row">
                            <span class="reply-author">${nama}</span>
                            ${badge}
                        </div>
                        <div class="bottom-row" style="font-size:13px; color:var(--text-secondary); margin-top:2px;">
                            <span class="reply-handle">${handle}</span>
                            <span class="separator">&bull;</span>
                            <span class="reply-time">${reply.waktu_relatif}</span>
                        </div>
                    </div>
                    ${menuHtml}
                </div>
                <div class="reply-text">${linkify(reply.isi_balasan)}</div>
                ${mediaHtml}
            </div>
        </div>
    `;
}






function setupReplyInputEvents() {
    
    $('#btn-reply-upload-image').click(function () {
        $('#reply-file-input').click();
    });

    
    $('#reply-file-input').change(function () {
        const file = this.files[0];
        if (!file) return;

        
        const allowedExts  = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        const allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        const ext = file.name.split('.').pop().toLowerCase();
        const mime = file.type.toLowerCase();

        if (!allowedExts.includes(ext) || !allowedMimes.includes(mime)) {
            $(this).val('');
            $('#reply-image-preview').hide();
            $('#reply-img-preview').attr('src', '');

            Swal.fire({
                icon: 'error',
                title: 'Format File Tidak Diizinkan',
                html: `<p>Hanya gambar yang boleh dilampirkan pada balasan.</p>
                       <p><strong>Format diterima:</strong> JPG, PNG, WEBP, GIF</p>
                       <p><strong>File Anda:</strong> <code>${file.name}</code></p>`,
                confirmButtonColor: '#2563eb'
            });
            return;
        }

        
        if (file.size > 5 * 1024 * 1024) {
            $(this).val('');
            Swal.fire({
                icon: 'warning',
                title: 'File Terlalu Besar',
                text: `Ukuran file maksimal 5 MB. File Anda: ${(file.size / (1024*1024)).toFixed(2)} MB`,
                confirmButtonColor: '#2563eb'
            });
            return;
        }

        
        let reader = new FileReader();
        reader.onload = function (e) {
            $('#reply-img-preview').attr('src', e.target.result);
            $('#reply-image-preview').show();
        };
        reader.readAsDataURL(file);
    });

    $('#btn-remove-reply-img').click(function () {
        $('#reply-file-input').val('');
        $('#reply-image-preview').hide();
        $('#reply-img-preview').attr('src', '');
    });

    
    
    $('#btn-submit-reply').on('click touchstart', function (e) {
        e.preventDefault();
        submitReply();
    });

    
    $('#input-reply-text').keypress(function (e) {
        if (e.which === 13 && !e.shiftKey) {
            e.preventDefault();
            submitReply();
        }
    });

    
    $(document).off('click', '#btn-close-detail').on('click', '#btn-close-detail', function (e) {
        e.preventDefault();
        e.stopPropagation();

        
        $('#modal-detail').removeClass('active');
        currentThreadId = null;

        
        try {
            history.replaceState(null, '', window.location.pathname);
        } catch (err) { console.error(err); }
    });


}




function submitReply() {
    const text = $('#input-reply-text').val().trim();
    const file = $('#reply-file-input')[0].files[0];
    const btn = $('#btn-submit-reply');

    if (!text && !file) {
        Swal.fire({
            icon: 'warning',
            title: 'Oops...',
            text: 'Balasan tidak boleh kosong',
            timer: 2000,
            showConfirmButton: false
        });
        return;
    }

    if (!currentThreadId) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Thread ID tidak valid'
        });
        return;
    }

    btn.text('Mengirim...').prop('disabled', true);

    
    let formData = new FormData();
    formData.append('action', 'post_reply');
    formData.append('thread_id', currentThreadId);
    formData.append('isi_balasan', text);

    if (file) {
        formData.append('media', file);
    }

    $.ajax({
        url: API_URL,
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        dataType: 'json',
        success: function (res) {
            if (res.status === 'success') {
                
                $('#input-reply-text').val('');
                $('#reply-file-input').val('');
                $('#reply-image-preview').hide();

                
                if (res.data) {
                    const newReply = res.data;
                    if (newReply.id > lastReplyId) lastReplyId = newReply.id;

                    
                    if ($('#detail-replies-list').find('.empty-state').length > 0) {
                        $('#detail-replies-list').empty();
                    }

                    const html = renderReplyHTML(newReply, currentThreadId);
                    $(html).hide().appendTo('#detail-replies-list').fadeIn('slow');

                    
                    const scrollContainer = $('.modal-scroll-content');
                    scrollContainer.animate({ scrollTop: scrollContainer[0].scrollHeight }, 500);

                    
                    const badge = $(`.feed-item[data-id="${currentThreadId}"]`).find('.btn-action-reply span');
                    let currentCount = parseInt(badge.text()) || 0;
                    badge.text(currentCount + 1);

                    
                    const modalBadge = $('#detail-main-post .stat-item strong');
                    let modalCount = parseInt(modalBadge.text()) || 0;
                    modalBadge.text(modalCount + 1);

                } else {
                    loadReplies(currentThreadId);
                }

                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
                Toast.fire({
                    icon: 'success',
                    title: 'Balasan terkirim!'
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: res.message || 'Gagal mengirim balasan'
                });
            }
        },
        
        error: function (xhr) {
            let msg = 'Gagal mengirim balasan ke server.';
            try {
                const errData = JSON.parse(xhr.responseText);
                if (errData.message) msg = errData.message;
            } catch (e) {}
            Swal.fire({
                icon: 'error',
                title: 'Upload Ditolak',
                text: msg,
                confirmButtonColor: '#dc2626'
            });
            
            $('#reply-file-input').val('');
            $('#reply-image-preview').hide();
            $('#reply-img-preview').attr('src', '');
        },
        complete: function () {
            btn.text('Balas').prop('disabled', false);
        }
    });
}






$(document).ready(function () {
    
    $(document).on('click', '.btn-trigger-reply-menu', function (e) {
        e.stopPropagation();
        $('.reply-dropdown-menu').not($(this).next('.reply-dropdown-menu')).removeClass('show');
        $(this).next('.reply-dropdown-menu').toggleClass('show');
    });

    
    $(document).on('click', '.btn-edit-reply', function (e) {
        e.stopPropagation();
        const id = $(this).data('id');
        const content = $(this).data('content');
        $('.reply-dropdown-menu').removeClass('show');
        openEditReply(id, content);
    });

    
    $(document).on('click', '.btn-delete-reply', function (e) {
        e.stopPropagation();
        const id = $(this).data('id');
        const threadId = $(this).data('thread-id');
        $('.reply-dropdown-menu').removeClass('show');

        Swal.fire({
            title: 'Hapus Balasan?',
            text: "Balasan akan dihapus permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                deleteReply(id, threadId);
            }
        });
    });
});




function openEditReply(id, content) {
    $('#edit-type').val('reply');
    $('#edit-id').val(id);
    $('#edit-textarea').val(content);
    $('#modal-edit').addClass('active');
    setTimeout(() => $('#edit-textarea').focus(), 100);
}




function deleteReply(id, threadId) {
    $.post(API_URL, {
        action: 'delete_reply',
        id: id
    }, function (res) {
        if (res.status === 'success') {
            
            loadReplies(threadId);

            
            loadThreads(true);

            Swal.fire({
                icon: 'success',
                title: 'Terhapus!',
                text: 'Balasan berhasil dihapus.',
                timer: 1500,
                showConfirmButton: false
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: res.message || 'Gagal menghapus balasan'
            });
        }
    }, 'json')
        .fail(function () {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Gagal menghapus balasan'
            });
        });
}






function linkify(text) {
    if (!text) return '';
    var urlRegex = /(\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig;
    return text.replace(urlRegex, function (url) {
        return '<a href="' + url + '" target="_blank" style="color:var(--color-primary);text-decoration:none;">' + url + '</a>';
    });
}






function startPolling() {
    
    pollingInterval = setInterval(function () {
        $.ajax({
            url: API_URL,
            type: 'GET',
            data: {
                action: 'check_updates',
                last_thread_id: lastThreadId,
                active_thread_id: currentThreadId || 0,
                last_reply_id: lastReplyId
            },
            dataType: 'json',
            success: function (res) {
                if (res.status === 'success') {
                    
                    if (res.new_threads && res.new_threads.length > 0) {
                        res.new_threads.forEach(t => {
                            if (t.id > lastThreadId) lastThreadId = t.id;
                            const html = renderThreadHTML(t);
                            
                            
                            $(html).css('background-color', '#e8f4fd').hide().prependTo('#feed-container').fadeIn('slow');

                            
                            if ($('#feed-container').find('.empty-feed-placeholder').length > 0) {
                                $('#feed-container').empty();
                            }
                        });
                    }

                    
                    if (currentThreadId && res.new_replies && res.new_replies.length > 0) {
                        res.new_replies.forEach(r => {
                            if (r.id > lastReplyId) lastReplyId = r.id;
                            const html = renderReplyHTML(r, currentThreadId);
                            $(html).css('background-color', '#e8f4fd').hide().appendTo('#detail-replies-list').fadeIn('slow');

                            
                            if ($('#detail-replies-list').find('.empty-state').length > 0) {
                                $('#detail-replies-list').empty();
                            }
                        });

                        
                        const modalBadge = $('#detail-main-post .stat-item strong');
                        let currentCount = parseInt(modalBadge.text()) || 0;
                        modalBadge.text(currentCount + res.new_replies.length);

                        
                        
                    }
                }
            }
        });
    }, 5000);
}