// QR Scanner with html5-qrcode
// This file will be lazy-loaded only on scanner page

export class QRScanner {
    constructor(elementId) {
        this.elementId = elementId;
        this.html5QrCode = null;
        this.onScanSuccess = null;
        this.onScanFailure = null;
    }
    
    async init(config = {}) {
        const { Html5Qrcode } = await import('html5-qrcode');
        
        const defaultConfig = {
            fps: 10,
            qrbox: { width: 250, height: 250 },
            aspectRatio: 1.0,
            ...config
        };
        
        this.html5QrCode = new Html5Qrcode(this.elementId);
        return defaultConfig;
    }
    
    async start(onSuccess, onFailure) {
        if (!this.html5QrCode) {
            throw new Error('QR Scanner not initialized. Call init() first.');
        }
        
        this.onScanSuccess = onSuccess;
        this.onScanFailure = onFailure;
        
        try {
            await this.html5QrCode.start(
                { facingMode: "environment" }, // Back camera
                {
                    fps: 10,
                    qrbox: { width: 250, height: 250 }
                },
                this.handleScanSuccess.bind(this),
                this.handleScanFailure.bind(this)
            );
        } catch (err) {
            console.error('Failed to start QR scanner:', err);
            throw err;
        }
    }
    
    async stop() {
        if (this.html5QrCode) {
            await this.html5QrCode.stop();
        }
    }
    
    handleScanSuccess(decodedText, decodedResult) {
        if (this.onScanSuccess) {
            this.onScanSuccess(decodedText, decodedResult);
        }
    }
    
    handleScanFailure(error) {
        // Silent failure - QR not detected yet
        if (this.onScanFailure) {
            this.onScanFailure(error);
        }
    }
}

// Photo capture utility
export async function capturePhotoFromVideo(videoElement) {
    const canvas = document.createElement('canvas');
    canvas.width = videoElement.videoWidth;
    canvas.height = videoElement.videoHeight;
    
    const context = canvas.getContext('2d');
    context.drawImage(videoElement, 0, 0, canvas.width, canvas.height);
    
    return canvas.toDataURL('image/jpeg', 0.8);
}
