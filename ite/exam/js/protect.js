












const isIOS = () => {
  const ua = navigator.userAgent || '';
  const platform = navigator.platform || '';

  
  
  const isAppleMobile = /iPhone|iPod/.test(ua) ||
    (/iPad/.test(ua)) ||
    (platform === 'MacIntel' && navigator.maxTouchPoints > 1);

  
  const isSafari = /WebKit/.test(ua) && !/Chrome/.test(ua);

  return isAppleMobile && isSafari;
};

const SecurityModule = (function () {
  
  let config = {
    violationCount: 3,
    onExamEnd: null,
    audioElement: null,
  };

  
  const getSafeTarget = (target) => {
    if (!target) return null;
    
    if (target.nodeType === 3) return target.parentElement;
    
    if (target.nodeType === 9) return null;
    return target;
  };

  
  const devToolsDetector = () => {
    const threshold = 160;
    if (
      window.outerWidth - window.innerWidth > threshold ||
      window.outerHeight - window.innerHeight > threshold
    ) {
      handleCheatingAttempt("Developer Tools Terdeteksi");
    }
  };

  
  const handleVisibilityChange = () => {
    if (document.visibilityState === "hidden") {
      handleCheatingAttempt("Meninggalkan Halaman Ujian");
    }
  };

  
  const handleKeyDown = (e) => {
    
    
    
    if (isIOS()) {
      
      if (e.ctrlKey && ['c', 'v', 'u'].includes(e.key.toLowerCase())) {
        e.preventDefault();
        handleCheatingAttempt('Copy-Paste Dinonaktifkan');
      }
      return; 
    }

    if (e.altKey || e.key === 'Tab' || e.metaKey) {
      e.preventDefault();
      handleCheatingAttempt('Tombol Terlarang Ditekan');
    }
    if (e.ctrlKey && ['c', 'v', 'u'].includes(e.key.toLowerCase())) {
      e.preventDefault();
      handleCheatingAttempt('Copy-Paste Dinonaktifkan');
    }
  };

  
  const preventCopy = (e) => {
    const target = getSafeTarget(e.target);
    
    if (target && typeof target.closest === 'function') {
      if (
        target.closest('.soal-pertanyaan') ||
        target.closest('.soal-pilihan')
      ) {
        e.preventDefault();
        handleCheatingAttempt("Mencoba Menyalin Soal");
      }
    }
  };

  
  const preventSelect = (e) => {
    const target = getSafeTarget(e.target);
    
    if (target && typeof target.closest === 'function') {
      if (
        target.closest('.soal-pertanyaan') ||
        target.closest('.soal-pilihan')
      ) {
        e.preventDefault();
      }
    }
  };

  
  const isFullscreenSupported = () => {
    const elem = document.documentElement;
    return !!(elem.requestFullscreen || elem.mozRequestFullScreen || elem.webkitRequestFullscreen || elem.msRequestFullscreen);
  };

  
  const enterFullscreen = () => {
    const elem = document.documentElement;
    const requestMethod = elem.requestFullscreen ||
      elem.mozRequestFullScreen ||
      elem.webkitRequestFullscreen ||
      elem.msRequestFullscreen;

    if (requestMethod) {
      try {
        const result = requestMethod.call(elem);
        
        if (result && typeof result.catch === 'function') {
          result.catch(err => {
            
            
            console.warn("Fullscreen ditolak browser (aman untuk diabaikan di preview):", err.message);
          });
        }
      } catch (err) {
        
        console.warn("Fullscreen error (Sync):", err.message);
      }
    }
  };

  
  const handleFullscreenChange = () => {
    const fullscreenElement = document.fullscreenElement ||
      document.mozFullScreenElement ||
      document.webkitFullscreenElement ||
      document.msFullscreenElement;
    if (!fullscreenElement) {
      handleCheatingAttempt("Keluar dari mode layar penuh");
    }
  };

  
  const handleCheatingAttempt = (reason) => {
    config.violationCount--;

    if (config.audioElement) {
      config.audioElement.play().catch(e => console.log("Audio play failed"));
    }

    if (config.violationCount <= 0) {
      
      if (typeof Swal !== 'undefined') {
        Swal.fire({
          icon: "error",
          title: "Batas Pelanggaran Tercapai!",
          text: `Anda telah melakukan pelanggaran berulang kali. Ujian Anda akan dihentikan.`,
          allowOutsideClick: false,
          allowEscapeKey: false,
        }).then(() => {
          if (config.onExamEnd) {
            config.onExamEnd();
          }
        });
      } else {
        alert("Batas pelanggaran tercapai. Ujian dihentikan.");
        if (config.onExamEnd) config.onExamEnd();
      }
      destroy();
    } else {
      if (typeof Swal !== 'undefined') {
        Swal.fire({
          icon: "warning",
          title: "Peringatan Keamanan!",
          html: `Alasan: <strong>${reason}</strong>.<br>Jangan meninggalkan halaman ujian.<br><br>Sisa kesempatan: <strong>${config.violationCount}</strong> kali.`,
          confirmButtonText: "Saya Mengerti",
          allowOutsideClick: false,
          allowEscapeKey: false,
        }).then(() => {
          if (config.audioElement) {
            config.audioElement.pause();
            config.audioElement.currentTime = 0;
          }
          if (isFullscreenSupported()) {
            enterFullscreen();
          }
        });
      }
    }
  };

  const init = (examEndCallback) => {
    config.onExamEnd = examEndCallback;
    config.audioElement = document.getElementById('violationAudio');

    
    
    
    if (!isIOS()) {
      window.addEventListener('resize', devToolsDetector);
    } else {
      console.log('🍎 iOS Detected: devToolsDetector (resize) bypassed to prevent false alarms.');
    }

    
    document.addEventListener('visibilitychange', handleVisibilityChange);
    document.addEventListener('keydown', handleKeyDown);
    document.addEventListener('copy', preventCopy);
    document.addEventListener('selectstart', preventSelect);
    document.body.setAttribute('oncontextmenu', 'return false;');

    
    
    if (isFullscreenSupported() && !isIOS()) {
      
      const initialModal = document.getElementById('fullscreenModal');
      const enterBtn = document.getElementById('enterFullscreenBtn');

      if (initialModal && enterBtn) {
        document.body.classList.add('modal-active');
        initialModal.style.display = 'flex';
        enterBtn.addEventListener('click', () => {
          initialModal.style.display = 'none';
          document.body.classList.remove('modal-active');
          enterFullscreen();
        });
      }

      
      document.addEventListener('fullscreenchange', handleFullscreenChange);
      document.addEventListener('mozfullscreenchange', handleFullscreenChange);
      document.addEventListener('webkitfullscreenchange', handleFullscreenChange);
      document.addEventListener('MSFullscreenChange', handleFullscreenChange);

    } else {
      
      if (typeof Swal !== 'undefined') {
        Swal.fire({
          icon: 'info',
          title: 'Pemberitahuan Keamanan',
          html: isIOS()
            ? 'Mode layar penuh tidak tersedia di Safari iOS.<br>Perpindahan tab dan aplikasi tetap dipantau.'
            : 'Mode layar penuh tidak didukung di perangkat Anda.<br>Untuk menjaga integritas ujian, <b>pengawasan terhadap perpindahan tab/aplikasi akan ditingkatkan.</b>',
          confirmButtonText: 'Saya Mengerti',
          allowOutsideClick: false,
          allowEscapeKey: false
        });
      }
    }
  };

  
  const destroy = () => {
    document.removeEventListener("visibilitychange", handleVisibilityChange);
    window.removeEventListener("resize", devToolsDetector);
    document.removeEventListener("keydown", handleKeyDown);
    document.removeEventListener("copy", preventCopy);
    document.removeEventListener("selectstart", preventSelect);
    document.removeEventListener("fullscreenchange", handleFullscreenChange);
    document.removeEventListener("mozfullscreenchange", handleFullscreenChange);
    document.removeEventListener("webkitfullscreenchange", handleFullscreenChange);
    document.removeEventListener("MSFullscreenChange", handleFullscreenChange);
    document.body.removeAttribute("oncontextmenu");
  };

  return {
    init,
    destroy
  };
})();


window.SecurityModule = SecurityModule;





(function () {
  "use strict";

  const blockEvent = (e) => {
    e.preventDefault();
    e.stopPropagation();
    return false;
  };

  
  document.addEventListener('contextmenu', blockEvent);

  
  document.addEventListener('selectstart', blockEvent);
  document.addEventListener('dragstart', blockEvent);

  
  document.addEventListener('copy', blockEvent);
  document.addEventListener('cut', blockEvent);
  document.addEventListener('paste', blockEvent);

  
  document.addEventListener('keydown', (e) => {
    
    const forbiddenKeys = ['u', 's', 'c', 'v', 'x', 'p', 'a', 'shift', 'i', 'j'];

    
    if (e.key === 'F12' || e.keyCode === 123) {
      blockEvent(e);
    }

    
    if (e.ctrlKey || e.metaKey) {
      if (forbiddenKeys.includes(e.key.toLowerCase())) {
        blockEvent(e);
      }
    }
  });

  console.log("🛡️ GARA Security Shield: Active");
})();