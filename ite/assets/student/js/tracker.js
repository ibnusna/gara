




(function () {
    
    const ENDPOINT = 'ajax/update_recap.php'; 
    
    
    
    
    

    const ACTIVITY_INTERVAL = 60000; 
    const STORAGE_KEY = 'gara_last_activity_hit';

    function sendActivity() {
        const now = Date.now();
        const lastHit = localStorage.getItem(STORAGE_KEY);

        
        if (lastHit && (now - lastHit) < ACTIVITY_INTERVAL) {
            return;
        }

        
        
        
        
        
        
        

        let basePath = '';
        const scripts = document.getElementsByTagName('script');
        for (let script of scripts) {
            if (script.src.includes('tracker.js')) {
                
                
                basePath = script.src.replace('js/tracker.js', 'ajax/update_recap.php');
                break;
            }
        }

        if (!basePath) {
            
            basePath = 'ajax/update_recap.php';
        }

        const currentPage = window.location.pathname.split('/').pop() || 'index.php'; 

        fetch(basePath, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `page=${encodeURIComponent(currentPage)}`
        })
            .then(response => {
                if (response.ok) {
                    localStorage.setItem(STORAGE_KEY, now);
                    
                }
            })
            .catch(err => {
                
            });
    }

    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', sendActivity);
    } else {
        sendActivity();
    }

    
    
    
    
    
    
    
    
})();
