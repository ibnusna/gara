



class GaraPicker {
    constructor(options) {
        this.developerKey = options.developerKey;
        this.appId = options.appId;
        this.mode = options.mode || 'all'; 
        this.onSelect = options.onSelect;
        this.onError = options.onError || console.error;
        this.tokenUrl = options.tokenUrl || '/guru/google/picker-token';
        this.token = null;
        this.pickerApiLoaded = false;
        
        this.init();
    }

    init() {
        
        if (typeof gapi === 'undefined') {
            const script = document.createElement('script');
            script.src = 'https://apis.google.com/js/api.js';
            script.onload = () => this.onApiLoad();
            document.body.appendChild(script);
        } else {
            this.onApiLoad();
        }
    }

    onApiLoad() {
        gapi.load('picker', { 'callback': () => { this.pickerApiLoaded = true; } });
    }

    open() {
        if (!this.pickerApiLoaded) {
            console.log('Google Picker API sedang dimuat...');
            setTimeout(() => this.open(), 500);
            return;
        }

        
        fetch(this.tokenUrl)
            .then(response => {
                if (!response.ok) throw new Error('Token tidak valid atau belum konek Google.');
                return response.json();
            })
            .then(data => {
                this.token = data.access_token;
                this.createPicker();
            })
            .catch(err => {
                Swal.fire('Error', err.message, 'error');
                this.onError(err);
            });
    }

    createPicker() {
        if (!this.token) return;

        let pickerBuilder = new google.picker.PickerBuilder()
            .setDeveloperKey(this.developerKey)
            .setOAuthToken(this.token)
            .setAppId(this.appId)
            .setOrigin(window.location.origin)
            .setCallback((data) => this.pickerCallback(data));

        if (this.mode === 'images') {
            let view = new google.picker.DocsView().setIncludeFolders(false);
            view.setMimeTypes('image/png,image/jpeg,image/jpg');
            pickerBuilder.addView(view);
        } else if (this.mode === 'docs_only') {
            let view = new google.picker.DocsView().setIncludeFolders(false);
            view.setMimeTypes('application/pdf,application/vnd.google-apps.document,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document');
            pickerBuilder.addView(view);
        } else if (this.mode === 'gdocs_only') {
            let view = new google.picker.DocsView().setIncludeFolders(false);
            view.setMimeTypes('application/vnd.google-apps.document');
            pickerBuilder.addView(view);
        } else if (this.mode === 'gforms_only') {
            let view = new google.picker.DocsView().setIncludeFolders(false);
            view.setMimeTypes('application/vnd.google-apps.form');
            pickerBuilder.addView(view);
        } else {
            let view = new google.picker.DocsView().setIncludeFolders(false);
            pickerBuilder.addView(view);
        }

        let picker = pickerBuilder.build();
        picker.setVisible(true);
        
        let attempts = 0;
        let zIndexInterval = setInterval(() => {
            let elements = document.getElementsByClassName('picker-dialog');
            let bgElements = document.getElementsByClassName('picker-dialog-bg');
            if (elements.length > 0) {
                for (let i = 0; i < elements.length; i++) {
                    elements[i].style.zIndex = '100000';
                }
                for (let i = 0; i < bgElements.length; i++) {
                    bgElements[i].style.zIndex = '99999';
                }
                clearInterval(zIndexInterval);
            }
            attempts++;
            if (attempts > 50) clearInterval(zIndexInterval); 
        }, 100);
    }

    pickerCallback(data) {
        if (data.action === google.picker.Action.PICKED) {
            const doc = data.docs[0];
            const result = {
                id: doc.id,
                name: doc.name,
                url: doc.url,
                embedUrl: doc.embedUrl,
                type: doc.type,
                mimeType: doc.mimeType,
                serviceId: doc.serviceId 
            };
            
            
            
            
            if (this.onSelect) {
                this.onSelect(result);
            }
        }
    }
}
