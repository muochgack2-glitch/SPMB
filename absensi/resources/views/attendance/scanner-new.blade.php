<x-app-layout>
    <x-slot name="title">QR Scanner</x-slot>
    <x-slot name="pageTitle">QR Scanner</x-slot>

    <div class="max-w-4xl mx-auto space-y-6" id="scanner-container">
        {{-- Action Toggle --}}
        <div class="flex justify-center gap-4">
            <button 
                onclick="setAction('check_in')" 
                id="btnCheckIn"
                class="action-btn active px-6 py-3 rounded-lg font-semibold transition-all"
            >
                <i class="fas fa-sign-in-alt mr-2"></i>
                Check In
            </button>
            <button 
                onclick="setAction('check_out')" 
                id="btnCheckOut"
                class="action-btn px-6 py-3 rounded-lg font-semibold transition-all"
            >
                <i class="fas fa-sign-out-alt mr-2"></i>
                Check Out
            </button>
        </div>

        {{-- Scanner Card --}}
        <x-card>
            <div class="text-center">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
                    <i class="fas fa-qrcode mr-2 text-primary-600"></i>
                    <span id="scannerTitle">Scan QR Code untuk Check In</span>
                </h2>

                {{-- QR Scanner Video --}}
                <div id="reader" class="mx-auto max-w-md mb-6"></div>

                {{-- Instructions --}}
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    <p><i class="fas fa-info-circle mr-2"></i>Posisikan QR code di depan kamera</p>
                    <p class="mt-2">Pastikan pencahayaan cukup untuk hasil terbaik</p>
                </div>
            </div>
        </x-card>

        {{-- Result Card (Hidden by default) --}}
        <x-card id="resultCard" class="hidden">
            <div class="text-center">
                <div id="resultIcon" class="text-6xl mb-4"></div>
                <h3 id="resultTitle" class="text-2xl font-bold mb-2"></h3>
                <p id="resultMessage" class="text-gray-600 dark:text-gray-400 mb-4"></p>
                
                <div id="resultDetails" class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 mb-4 text-left"></div>

                <div class="flex justify-center gap-3">
                    <button 
                        onclick="hideResult()" 
                        class="px-6 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition"
                    >
                        <i class="fas fa-check mr-2"></i>
                        OK
                    </button>
                </div>
            </div>
        </x-card>

        {{-- Error Card (Hidden by default) --}}
        <x-card id="errorCard" class="hidden bg-red-50 dark:bg-red-900/20 border-2 border-red-300 dark:border-red-800">
            <div class="text-center">
                <i class="fas fa-exclamation-triangle text-6xl text-red-600 mb-4"></i>
                <h3 class="text-2xl font-bold text-red-800 dark:text-red-300 mb-2">Error</h3>
                <p id="errorMessage" class="text-red-700 dark:text-red-400 mb-4"></p>
                <button 
                    onclick="hideError()" 
                    class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition"
                >
                    Tutup
                </button>
            </div>
        </x-card>
    </div>

    @push('scripts')
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>
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
            document.getElementById('reader').classList.add('opacity-50');
            
            const resultCard = document.getElementById('resultCard');
            const resultIcon = document.getElementById('resultIcon');
            const resultTitle = document.getElementById('resultTitle');
            const resultMessage = document.getElementById('resultMessage');
            const resultDetails = document.getElementById('resultDetails');

            resultIcon.innerHTML = '✅';
            resultIcon.className = 'text-6xl mb-4 text-green-600';
            resultTitle.textContent = currentAction === 'check_in' ? 'Check In Berhasil!' : 'Check Out Berhasil!';
            resultMessage.textContent = result.message || 'Absensi berhasil direkam';
            
            resultDetails.innerHTML = `
                <div class="space-y-2">
                    <p><strong>NIS:</strong> ${result.data?.nis || '-'}</p>
                    <p><strong>Nama:</strong> ${result.data?.nama || '-'}</p>
                    <p><strong>Kelas:</strong> ${result.data?.kelas || '-'}</p>
                    <p><strong>Waktu:</strong> ${result.data?.time || '-'}</p>
                    <p><strong>Status:</strong> <span class="px-2 py-1 rounded-full text-xs ${
                        result.data?.status === 'hadir' ? 'bg-green-100 text-green-800' : 
                        result.data?.status === 'terlambat' ? 'bg-yellow-100 text-yellow-800' : 
                        'bg-gray-100 text-gray-800'
                    }">${result.data?.status || '-'}</span></p>
                </div>
            `;
            
            resultCard.classList.remove('hidden');
            hideError();
        }

        function showError(message) {
            document.getElementById('reader').classList.add('opacity-50');
            document.getElementById('errorMessage').textContent = message;
            document.getElementById('errorCard').classList.remove('hidden');
            hideResult();
        }

        function hideResult() {
            document.getElementById('resultCard').classList.add('hidden');
            document.getElementById('reader').classList.remove('opacity-50');
        }

        function hideError() {
            document.getElementById('errorCard').classList.add('hidden');
            document.getElementById('reader').classList.remove('opacity-50');
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

    <style>
        .action-btn {
            @apply bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300;
        }
        .action-btn.active {
            @apply bg-primary-600 text-white ring-4 ring-primary-300 dark:ring-primary-800;
        }
        .action-btn:hover {
            @apply bg-primary-700;
        }
    </style>
    @endpush
</x-app-layout>

