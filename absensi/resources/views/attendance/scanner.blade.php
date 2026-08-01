<x-app-layout>
    <x-slot name="title">QR Scanner</x-slot>
    <x-slot name="pageTitle">QR Scanner</x-slot>

    <div class="max-w-5xl mx-auto space-y-8" id="scanner-container">
        
        {{-- Header Section with Gradient --}}
        <div class="relative overflow-hidden bg-gradient-to-br from-primary-600 via-primary-500 to-purple-600 rounded-3xl shadow-2xl p-8 text-white">
            <div class="absolute top-0 right-0 -mt-12 -mr-12 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 left-0 -mb-12 -ml-12 w-64 h-64 bg-purple-500/20 rounded-full blur-3xl"></div>
            
            <div class="relative z-10 text-center">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-white/20 backdrop-blur-lg rounded-2xl mb-4 shadow-xl">
                    <i class="fas fa-qrcode text-4xl"></i>
                </div>
                <h1 class="text-4xl font-black mb-2">QR Scanner Premium</h1>
                <p class="text-primary-100 text-lg">Scan untuk absensi real-time dengan teknologi AI</p>
            </div>
        </div>

        {{-- Action Toggle with Modern Design --}}
        <div class="flex justify-center gap-6">
            <button 
                onclick="setAction('check_in')" 
                id="btnCheckIn"
                class="action-btn active group relative px-10 py-5 rounded-2xl font-bold text-lg transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-2xl"
            >
                <div class="absolute inset-0 bg-gradient-to-r from-green-400 to-emerald-500 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                <div class="relative flex items-center gap-3">
                    <i class="fas fa-sign-in-alt text-2xl"></i>
                    <span>Check In</span>
                </div>
            </button>
            <button 
                onclick="setAction('check_out')" 
                id="btnCheckOut"
                class="action-btn group relative px-10 py-5 rounded-2xl font-bold text-lg transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-2xl"
            >
                <div class="absolute inset-0 bg-gradient-to-r from-red-400 to-pink-500 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                <div class="relative flex items-center gap-3">
                    <i class="fas fa-sign-out-alt text-2xl"></i>
                    <span>Check Out</span>
                </div>
            </button>
        </div>

        {{-- Scanner Card with Premium Design --}}
        <div class="relative">
            <div class="absolute inset-0 bg-gradient-to-br from-primary-100 to-purple-100 dark:from-primary-900/20 dark:to-purple-900/20 rounded-3xl blur-2xl transform scale-95"></div>
            <x-card class="relative backdrop-blur-sm bg-white/80 dark:bg-gray-800/80 border-2 border-primary-200 dark:border-primary-800/50">
                <div class="text-center space-y-6">
                    <div class="flex items-center justify-center gap-4">
                        <div class="w-1 h-8 bg-gradient-to-b from-primary-500 to-purple-500 rounded-full"></div>
                        <h2 class="text-3xl font-black text-gray-900 dark:text-white">
                            <span id="scannerTitle">Scan QR Code untuk Check In</span>
                        </h2>
                        <div class="w-1 h-8 bg-gradient-to-b from-purple-500 to-primary-500 rounded-full"></div>
                    </div>

                    {{-- QR Scanner Video with Frame --}}
                    <div class="relative inline-block">
                        <div class="absolute -inset-4 bg-gradient-to-r from-primary-500 via-purple-500 to-pink-500 rounded-3xl opacity-30 blur-xl animate-pulse"></div>
                        <div class="relative bg-gray-900 rounded-2xl p-2 shadow-2xl">
                            <div id="reader" class="mx-auto rounded-xl overflow-hidden" style="max-width: 400px;"></div>
                            
                            {{-- Scanning Animation Overlay --}}
                            <div id="scanOverlay" class="absolute inset-0 pointer-events-none">
                                <div class="scan-line"></div>
                            </div>
                        </div>
                    </div>

                    {{-- Modern Instructions --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 max-w-3xl mx-auto">
                        <div class="flex items-start gap-3 p-4 bg-primary-50 dark:bg-primary-900/20 rounded-xl">
                            <div class="flex-shrink-0 w-10 h-10 bg-primary-500 text-white rounded-lg flex items-center justify-center">
                                <i class="fas fa-mobile-alt"></i>
                            </div>
                            <div class="text-left">
                                <p class="font-semibold text-gray-900 dark:text-white text-sm">Posisi QR Code</p>
                                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Di tengah frame kamera</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start gap-3 p-4 bg-purple-50 dark:bg-purple-900/20 rounded-xl">
                            <div class="flex-shrink-0 w-10 h-10 bg-purple-500 text-white rounded-lg flex items-center justify-center">
                                <i class="fas fa-sun"></i>
                            </div>
                            <div class="text-left">
                                <p class="font-semibold text-gray-900 dark:text-white text-sm">Pencahayaan</p>
                                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Pastikan cukup terang</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start gap-3 p-4 bg-green-50 dark:bg-green-900/20 rounded-xl">
                            <div class="flex-shrink-0 w-10 h-10 bg-green-500 text-white rounded-lg flex items-center justify-center">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="text-left">
                                <p class="font-semibold text-gray-900 dark:text-white text-sm">Auto Scan</p>
                                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Deteksi otomatis</p>
                            </div>
                        </div>
                    </div>
                </div>
            </x-card>
        </div>

        {{-- Result Card with Premium Success Animation --}}
        <div id="resultCard" class="hidden transform transition-all duration-500 scale-95 opacity-0">
            <div class="relative">
                <div class="absolute inset-0 bg-gradient-to-br from-green-100 to-emerald-100 dark:from-green-900/20 dark:to-emerald-900/20 rounded-3xl blur-2xl transform scale-95"></div>
                <x-card class="relative backdrop-blur-sm bg-white/90 dark:bg-gray-800/90 border-2 border-green-200 dark:border-green-800/50">
                    <div class="text-center space-y-6">
                        <div id="resultIcon" class="relative inline-block">
                            <div class="absolute inset-0 bg-green-400 rounded-full animate-ping opacity-30"></div>
                            <div class="relative text-8xl animate-bounce">✅</div>
                        </div>
                        
                        <div>
                            <h3 id="resultTitle" class="text-3xl font-black text-gray-900 dark:text-white mb-2"></h3>
                            <p id="resultMessage" class="text-lg text-gray-600 dark:text-gray-400"></p>
                        </div>
                        
                        <div id="resultDetails" class="max-w-md mx-auto bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900 rounded-2xl p-6 border-2 border-gray-200 dark:border-gray-700 shadow-xl"></div>

                        <div class="flex justify-center gap-4">
                            <button 
                                onclick="hideResult()" 
                                class="group relative px-8 py-4 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white rounded-xl font-bold transition-all transform hover:scale-105 shadow-lg hover:shadow-2xl"
                            >
                                <i class="fas fa-check mr-2"></i>
                                Selesai
                            </button>
                        </div>
                    </div>
                </x-card>
            </div>
        </div>

        {{-- Error Card with Premium Design --}}
        <div id="errorCard" class="hidden transform transition-all duration-500 scale-95 opacity-0">
            <div class="relative">
                <div class="absolute inset-0 bg-gradient-to-br from-red-100 to-pink-100 dark:from-red-900/20 dark:to-pink-900/20 rounded-3xl blur-2xl transform scale-95"></div>
                <x-card class="relative backdrop-blur-sm bg-white/90 dark:bg-gray-800/90 border-2 border-red-200 dark:border-red-800/50">
                    <div class="text-center space-y-6">
                        <div class="relative inline-block">
                            <div class="absolute inset-0 bg-red-400 rounded-full animate-ping opacity-30"></div>
                            <i class="relative fas fa-exclamation-triangle text-8xl text-red-600 dark:text-red-400"></i>
                        </div>
                        
                        <div>
                            <h3 class="text-3xl font-black text-red-800 dark:text-red-300 mb-2">Oops!</h3>
                            <p id="errorMessage" class="text-lg text-red-700 dark:text-red-400"></p>
                        </div>
                        
                        <button 
                            onclick="hideError()" 
                            class="group relative px-8 py-4 bg-gradient-to-r from-red-500 to-pink-600 hover:from-red-600 hover:to-pink-700 text-white rounded-xl font-bold transition-all transform hover:scale-105 shadow-lg hover:shadow-2xl"
                        >
                            <i class="fas fa-times mr-2"></i>
                            Tutup
                        </button>
                    </div>
                </x-card>
            </div>
        </div>
    </div>

    @push('scripts')
    <script type="module">
        // Import html5-qrcode from npm instead of CDN
        import { Html5Qrcode } from '/node_modules/html5-qrcode/html5-qrcode.min.js';
        
        window.Html5Qrcode = Html5Qrcode; // Make it globally available
        
        let currentAction = 'check_in';
        let html5QrCode = null;
        let lastScannedNis = null;

        // Initialize scanner on page load
        document.addEventListener('DOMContentLoaded', function() {
            initScanner();
        });

        function initScanner() {
            html5QrCode = new Html5Qrcode("reader");
            
            const config = {
                fps: 10,
                qrbox: { width: 250, height: 250 },
                aspectRatio: 1.0
            };

            html5QrCode.start(
                { facingMode: "environment" },
                config,
                onScanSuccess,
                onScanFailure
            ).catch(err => {
                console.error('Failed to start scanner:', err);
                showError('Gagal membuka kamera. Pastikan browser memiliki akses ke kamera.');
            });
        }

        function onScanSuccess(decodedText, decodedResult) {
            // Prevent duplicate scans
            if (lastScannedNis === decodedText) {
                return;
            }
            
            lastScannedNis = decodedText;
            
            // Stop scanner temporarily
            html5QrCode.pause(true);
            
            // Process the scan
            processScan(decodedText);
        }

        function onScanFailure(error) {
            // Silently ignore scan failures (expected during scanning)
        }

        async function processScan(nis) {
            try {
                // Capture photo from video (optional, bisa pakai dummy)
                const photoBase64 = await capturePhoto();

                const response = await fetch('/api/attendance/scan', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        nis: nis,
                        action: currentAction,
                        photo_base64: photoBase64
                    })
                });

                const result = await response.json();

                if (result.success) {
                    showSuccess(result);
                } else {
                    showError(result.message || 'Gagal memproses absensi');
                }

                // Resume scanning after 3 seconds
                setTimeout(() => {
                    lastScannedNis = null;
                    html5QrCode.resume();
                }, 3000);

            } catch (error) {
                console.error('Scan processing error:', error);
                showError('Terjadi kesalahan saat memproses scan');
                
                setTimeout(() => {
                    lastScannedNis = null;
                    html5QrCode.resume();
                }, 3000);
            }
        }

        async function capturePhoto() {
            // Simplified - return dummy base64 or capture from video
            // For production, implement proper camera capture
            return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';
        }

        function showSuccess(result) {
            const resultCard = document.getElementById('resultCard');
            const resultIcon = document.getElementById('resultIcon');
            const resultTitle = document.getElementById('resultTitle');
            const resultMessage = document.getElementById('resultMessage');
            const resultDetails = document.getElementById('resultDetails');

            resultTitle.textContent = currentAction === 'check_in' ? '✨ Check In Berhasil!' : '👋 Check Out Berhasil!';
            resultMessage.textContent = result.message || 'Absensi berhasil direkam ke sistem';
            
            resultDetails.innerHTML = `
                <div class="space-y-4">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 bg-gradient-to-br from-primary-500 to-purple-500 rounded-xl flex items-center justify-center text-white text-2xl font-bold shadow-lg">
                            ${result.data?.nis?.substring(0, 2) || 'ID'}
                        </div>
                        <div class="text-left flex-1">
                            <p class="text-lg font-bold text-gray-900 dark:text-white">${result.data?.nama || '-'}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">NIS: ${result.data?.nis || '-'}</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div class="bg-white dark:bg-gray-700 rounded-lg p-3">
                            <p class="text-gray-500 dark:text-gray-400 text-xs mb-1">Kelas</p>
                            <p class="font-semibold text-gray-900 dark:text-white">${result.data?.kelas || '-'}</p>
                        </div>
                        <div class="bg-white dark:bg-gray-700 rounded-lg p-3">
                            <p class="text-gray-500 dark:text-gray-400 text-xs mb-1">Waktu</p>
                            <p class="font-semibold text-gray-900 dark:text-white">${result.data?.time || '-'}</p>
                        </div>
                    </div>
                    
                    <div class="text-center pt-2">
                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-bold ${
                            result.data?.status === 'hadir' ? 'bg-gradient-to-r from-green-400 to-emerald-500 text-white shadow-lg' : 
                            result.data?.status === 'terlambat' ? 'bg-gradient-to-r from-yellow-400 to-orange-500 text-white shadow-lg' : 
                            'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300'
                        }">
                            <i class="fas ${result.data?.status === 'hadir' ? 'fa-check-circle' : result.data?.status === 'terlambat' ? 'fa-clock' : 'fa-times-circle'}"></i>
                            ${result.data?.status?.toUpperCase() || '-'}
                        </span>
                    </div>
                </div>
            `;
            
            resultCard.classList.remove('hidden', 'scale-95', 'opacity-0');
            resultCard.classList.add('scale-100', 'opacity-100');
            hideError();
            
            // Confetti effect (optional)
            if (window.confetti) {
                confetti({
                    particleCount: 100,
                    spread: 70,
                    origin: { y: 0.6 }
                });
            }
        }

        function showError(message) {
            const errorCard = document.getElementById('errorCard');
            document.getElementById('errorMessage').textContent = message;
            errorCard.classList.remove('hidden', 'scale-95', 'opacity-0');
            errorCard.classList.add('scale-100', 'opacity-100');
            hideResult();
        }

        function hideResult() {
            const resultCard = document.getElementById('resultCard');
            resultCard.classList.add('scale-95', 'opacity-0');
            setTimeout(() => resultCard.classList.add('hidden'), 300);
        }

        function hideError() {
            const errorCard = document.getElementById('errorCard');
            errorCard.classList.add('scale-95', 'opacity-0');
            setTimeout(() => errorCard.classList.add('hidden'), 300);
        }

        function setAction(action) {
            currentAction = action;
            
            // Update button styles
            document.getElementById('btnCheckIn').classList.toggle('active', action === 'check_in');
            document.getElementById('btnCheckOut').classList.toggle('active', action === 'check_out');
            
            // Update title
            const title = action === 'check_in' ? 'Scan QR Code untuk Check In' : 'Scan QR Code untuk Check Out';
            document.getElementById('scannerTitle').textContent = title;
            
            hideResult();
            hideError();
            lastScannedNis = null;
        }
    </script>
    </script>

    <style>
        /* Premium Action Button Styles */
        .action-btn {
            @apply relative bg-gradient-to-br from-gray-100 to-gray-200 text-gray-700 dark:from-gray-700 dark:to-gray-800 dark:text-gray-300 shadow-xl;
            transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }
        
        .action-btn.active {
            @apply bg-gradient-to-br from-primary-500 to-purple-600 text-white shadow-2xl;
            box-shadow: 0 20px 40px -10px rgba(59, 130, 246, 0.5);
        }
        
        .action-btn:hover:not(.active) {
            @apply transform -translate-y-1;
            box-shadow: 0 15px 30px -5px rgba(0, 0, 0, 0.2);
        }
        
        /* Scanning Line Animation */
        #scanOverlay {
            background: linear-gradient(
                180deg,
                rgba(59, 130, 246, 0) 0%,
                rgba(59, 130, 246, 0.8) 50%,
                rgba(59, 130, 246, 0) 100%
            );
            height: 4px;
            animation: scan 2s linear infinite;
        }
        
        @keyframes scan {
            0% {
                transform: translateY(-100%);
                opacity: 0;
            }
            10% {
                opacity: 1;
            }
            90% {
                opacity: 1;
            }
            100% {
                transform: translateY(400px);
                opacity: 0;
            }
        }
        
        /* Pulse Animation for Success */
        @keyframes pulse-scale {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }
        
        /* Shimmer Effect */
        @keyframes shimmer {
            0% {
                background-position: -1000px 0;
            }
            100% {
                background-position: 1000px 0;
            }
        }
        
        .shimmer {
            background: linear-gradient(
                90deg,
                rgba(255, 255, 255, 0) 0%,
                rgba(255, 255, 255, 0.2) 50%,
                rgba(255, 255, 255, 0) 100%
            );
            background-size: 1000px 100%;
            animation: shimmer 2s infinite;
        }
        
        /* QR Reader Container Enhancement */
        #reader {
            border: 4px solid transparent;
            background: linear-gradient(#000, #000) padding-box,
                        linear-gradient(135deg, #3b82f6, #8b5cf6, #ec4899) border-box;
            transition: all 0.3s ease;
        }
        
        #reader:hover {
            box-shadow: 0 0 30px rgba(59, 130, 246, 0.4);
        }
        
        /* Card Entrance Animation */
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        #scanner-container > * {
            animation: slideUp 0.6s ease-out backwards;
        }
        
        #scanner-container > *:nth-child(1) {
            animation-delay: 0.1s;
        }
        
        #scanner-container > *:nth-child(2) {
            animation-delay: 0.2s;
        }
        
        #scanner-container > *:nth-child(3) {
            animation-delay: 0.3s;
        }
    </style>
    @endpush
</x-app-layout>

