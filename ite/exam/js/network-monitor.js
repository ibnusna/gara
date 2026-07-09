





(function () {
    'use strict';

    const ERROR_PAGE_URL = 'eror.html';

    function handleOffline() {
        console.warn('[Network] Connection lost! Redirecting to error page...');

        
        
        if (window.location.pathname.indexOf(ERROR_PAGE_URL) === -1) {
            sessionStorage.setItem('last_active_page', window.location.href);
        }

        
        window.location.replace(ERROR_PAGE_URL);
    }

    
    window.addEventListener('offline', handleOffline);

    
    if (!navigator.onLine) {
        handleOffline();
    }

    console.log('[Network] Monitor aktif. Menunggu sinyal offline...');
})();
