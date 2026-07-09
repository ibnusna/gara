





(function () {
    'use strict';

    const btnReload = document.getElementById('reloadBtn');
    const icon = document.getElementById('btnIcon');
    const text = document.getElementById('btnText');
    const mainWindow = document.getElementById('main-window');
    const DEFAULT_PAGE = 'index.html';

    

    function getRedirectTarget() {
        const lastPage = sessionStorage.getItem('last_active_page');
        
        if (lastPage && lastPage.indexOf('eror.html') === -1) {
            return lastPage;
        }
        return DEFAULT_PAGE;
    }

    function setUIState(state) {
        if (!btnReload || !icon || !text || !mainWindow) return;

        if (state === 'checking') {
            btnReload.disabled = true;
            icon.classList.add('spin-anim');
            text.innerText = "Memeriksa...";
            mainWindow.classList.remove('shake');
        } else if (state === 'online') {
            text.innerText = "Terhubung!";
            icon.classList.remove('spin-anim');
            btnReload.disabled = false;
        } else if (state === 'offline') {
            text.innerText = "Coba Lagi";
            icon.classList.remove('spin-anim');
            btnReload.disabled = false;

            
            mainWindow.classList.add('shake');
            setTimeout(() => mainWindow.classList.remove('shake'), 500);
        }
    }

    

    async function attemptRecovery() {
        setUIState('checking');

        
        
        await new Promise(r => setTimeout(r, 1000));

        
        if (navigator.onLine) {
            
            setUIState('online');

            console.log('[Recovery] Connection restored. Redirecting...');
            const target = getRedirectTarget();

            setTimeout(() => {
                window.location.replace(target);
            }, 500);

        } else {
            
            setUIState('offline');
            console.warn('[Recovery] Still offline.');
        }
    }

    

    
    if (btnReload) {
        btnReload.onclick = (e) => {
            e.preventDefault(); 
            attemptRecovery();
        };
    }

    
    window.addEventListener('online', () => {
        console.log('[Recovery] Online event detected!');

        
        const title = document.querySelector('.error-title');
        const desc = document.querySelector('.error-desc');

        if (title) title.innerText = "Koneksi Pulih";
        if (desc) desc.innerText = "Internet Anda sudah kembali. Mengalihkan...";

        
        attemptRecovery();
    });

    
    setInterval(() => {
        if (navigator.onLine) {
            
            if (text && text.innerText !== "Terhubung!") {
                
                
                
            }
        }
    }, 5000);

})();
