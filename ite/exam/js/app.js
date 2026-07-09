











function checkEnvironment() {
    
    const isElectronApp = window.isRuangUjian === true;

    
    const isUserAgentMatch = navigator.userAgent.includes('RuangUjian');

    
    const isAppMode = isElectronApp || isUserAgentMatch;

    const htmlEl = document.documentElement;

    if (isAppMode) {
        
        htmlEl.classList.add('is-ruang-ujian');
        console.log("%c[Ruang Ujian] Mode Aplikasi Terdeteksi: Navigasi eksternal dimatikan.", "color: green; font-weight: bold;");
    } else {
        
        htmlEl.classList.add('is-standard-browser');
        console.log("%c[Ruang Ujian] Mode Browser Standar.", "color: blue;");
    }
}



if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', checkEnvironment);
} else {
    checkEnvironment();
}
