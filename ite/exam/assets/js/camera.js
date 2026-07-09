









const CameraModule = (() => {
    
    const CONFIG = {
        TIMEOUT_MS: 60000, 
        MIN_WIDTH: 160,
        MIN_HEIGHT: 120,
        DEFAULT_POS: { bottom: '20px', right: '20px' }
    };

    
    const state = {
        isActive: false,
        stream: null,
        isDragging: false,
        isResizing: false,
        dragOffset: { x: 0, y: 0 },
        timeoutId: null
    };

    
    let els = {};

    


    function init() {
        
        if (!isDesktop()) {
            console.log('CameraModule: Mobile device detected. Module disabled.');
            return;
        }

        
        bindElements();

        
        setupInteractions();

        
        startCameraSequence();
    }

    



    function isDesktop() {
        const ua = navigator.userAgent;
        const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(ua);
        const isWide = window.innerWidth > 768; 
        return !isMobile && isWide;
    }

    


    function bindElements() {
        els.module = document.getElementById('cameraModule');
        els.header = document.getElementById('cameraHeader');
        els.video = document.getElementById('userCamera');
        els.status = document.getElementById('cameraStatus');
        els.statusText = els.status ? els.status.querySelector('p') : null;
        els.btnPermission = document.getElementById('btnRequestPermission');
        els.resizer = document.querySelector('.camera-resizer');
        els.btnMinimize = document.querySelector('.btn-cam-minimize'); 
        els.btnFloat = document.getElementById('btnShowCameraFloat');

        
        if (!els.module || !els.video) {
            console.error('CameraModule: DOM elements not found.');
        }
    }

    


    async function startCameraSequence() {
        
        els.module.classList.remove('hidden');

        
        state.timeoutId = setTimeout(() => {
            if (!state.stream) {
                console.warn('CameraModule: Timeout reached. Camera not found/allowed.');
                
                shutdownModule();
            }
        }, CONFIG.TIMEOUT_MS);

        requestCamera();
    }

    async function requestCamera() {
        if (els.statusText) els.statusText.innerText = "Memuat Kamera...";
        if (els.btnPermission) els.btnPermission.classList.add('hidden');

        try {
            
            const stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    width: { ideal: 640 },
                    height: { ideal: 480 },
                    facingMode: "user"
                },
                audio: false
            });

            handleStreamSuccess(stream);

        } catch (err) {
            handleStreamError(err);
        }
    }

    function handleStreamSuccess(stream) {
        
        clearTimeout(state.timeoutId);

        state.stream = stream;
        state.isActive = true;

        
        els.module.classList.remove('hidden');

        
        els.video.srcObject = stream;
        els.status.style.display = 'none'; 

        console.log('CameraModule: Active.');
    }

    function handleStreamError(err) {
        console.error('CameraModule: Access denied or error.', err);

        
        if (els.statusText) els.statusText.innerText = "Kamera tidak aktif.";
        if (els.btnPermission) {
            els.btnPermission.classList.remove('hidden');
            els.btnPermission.onclick = () => {
                requestCamera();
            };
        }

        
    }

    function shutdownModule() {
        if (els.module) els.module.classList.add('hidden');
        if (els.btnFloat) els.btnFloat.classList.add('hidden');
        state.isActive = false;
    }

    


    function setupInteractions() {
        if (!els.module) return;

        
        els.header.addEventListener('mousedown', startDrag);

        
        els.resizer.addEventListener('mousedown', startResize);

        
        document.addEventListener('mousemove', (e) => {
            if (state.isDragging) drag(e);
            if (state.isResizing) resize(e);
        });

        document.addEventListener('mouseup', () => {
            state.isDragging = false;
            state.isResizing = false;
            document.body.style.cursor = 'default';
        });

        
        if (els.btnMinimize) {
            els.btnMinimize.addEventListener('click', (e) => {
                e.stopPropagation(); 
                minimize();
            });
        }

        if (els.btnFloat) {
            els.btnFloat.addEventListener('click', restore);
        }
    }

    
    function startDrag(e) {
        
        if (e.target.closest('button')) return;

        state.isDragging = true;
        
        const rect = els.module.getBoundingClientRect();
        state.dragOffset.x = e.clientX - rect.left;
        state.dragOffset.y = e.clientY - rect.top;
    }

    function drag(e) {
        e.preventDefault();

        
        let newX = e.clientX - state.dragOffset.x;
        let newY = e.clientY - state.dragOffset.y;

        
        const winWidth = window.innerWidth;
        const winHeight = window.innerHeight;
        const rect = els.module.getBoundingClientRect();

        
        
        
        
        

        
        

        
        els.module.style.bottom = 'auto';
        els.module.style.right = 'auto';
        els.module.style.left = `${newX}px`;
        els.module.style.top = `${newY}px`;
    }

    
    function startResize(e) {
        state.isResizing = true;
        e.preventDefault();
        e.stopPropagation();
    }

    function resize(e) {
        const rect = els.module.getBoundingClientRect();

        
        
        
        let newWidth = e.clientX - rect.left;
        let newHeight = e.clientY - rect.top;

        
        if (newWidth > CONFIG.MIN_WIDTH) els.module.style.width = `${newWidth}px`;
        if (newHeight > CONFIG.MIN_HEIGHT) els.module.style.height = `${newHeight}px`;
    }

    
    function minimize() {
        els.module.classList.add('hidden');
        els.btnFloat.classList.remove('hidden');
    }

    function restore() {
        els.module.classList.remove('hidden');
        els.btnFloat.classList.add('hidden');
    }

    
    return {
        init: init
    };

})();


document.addEventListener('DOMContentLoaded', () => {
    CameraModule.init();
});
