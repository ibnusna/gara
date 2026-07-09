
import { initSummaryPage } from './modules/page-summary.js?v=garav9';
import { initUjianPage } from './modules/page-ujian-v3.js?v=garav9';
import { initHasilPage } from './modules/page-hasil.js?v=garav9';
import { initLoginPage } from './modules/page-login.js?v=garav9';

document.addEventListener("DOMContentLoaded", () => {
    
    const path = window.location.pathname.split("/").pop();

    console.log(`[App] Current Page: ${path || 'root'}`);

    
    if (path === "summary.html" || path === "konfirmasi") {
        initSummaryPage();
    } else if (path === "ujian.html" || path === "arena") {
        initUjianPage();
    } else if (path === "hasil.html" || path === "hasil") {
        initHasilPage();
    } else {
        
        
        initLoginPage();
    }
});