<x-public-layout>
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4" id="scanner-container">
        
        {{-- LEFT SIDEBAR: Stats Cards --}}
        <div class="lg:col-span-3 space-y-3">
            {{-- Header with Clock --}}
            <div class="bg-gradient-to-br from-primary-600 to-purple-600 rounded-xl shadow-lg p-4 text-white text-center">
                <div class="inline-flex items-center justify-center w-10 h-10 bg-white/20 backdrop-blur-lg rounded-lg mb-2">
                    <i class="fas fa-clock text-xl"></i>
                </div>
                <div id="currentTime" class="text-2xl font-black mb-1">00:00:00</div>
                <div id="currentDate" class="text-xs text-primary-100 mb-3">Loading...</div>
                
                {{-- Jam Masuk & Pulang Info --}}
                <div class="pt-3 border-t border-white/20 space-y-2">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-primary-100">⏰ Jam Masuk:</span>
                        <span class="font-bold">06:30 - 07:00</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-primary-100">🏠 Jam Pulang:</span>
                        <span class="font-bold">15:00 - 15:30</span>
                    </div>
                </div>
            </div>

            {{-- Pengumuman Card --}}
            <div class="bg-gradient-to-br from-orange-500 to-red-500 rounded-xl shadow-lg p-4 text-white">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0">
                        <i class="fas fa-bullhorn text-3xl"></i>
                    </div>
                    <div class="text-left">
                        <h3 class="text-base font-black mb-2">📢 PENGUMUMAN</h3>
                        <p class="text-sm leading-relaxed">"Siswa harap scan saat masuk gerbang sekolah"</p>
                    </div>
                </div>
            </div>

            {{-- Stats Cards --}}
            <div class="space-y-2">
                <div class="bg-gradient-to-br from-green-500 to-emerald-600 rounded-lg shadow-lg p-3 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-xl font-black" id="statHadir">0</div>
                            <div class="text-xs text-green-100">Hadir</div>
                        </div>
                        <i class="fas fa-check-circle text-2xl opacity-50"></i>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-yellow-500 to-orange-600 rounded-lg shadow-lg p-3 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-xl font-black" id="statTerlambat">0</div>
                            <div class="text-xs text-yellow-100">Terlambat</div>
                        </div>
                        <i class="fas fa-clock text-2xl opacity-50"></i>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-red-500 to-pink-600 rounded-lg shadow-lg p-3 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-xl font-black" id="statAlpha">0</div>
                            <div class="text-xs text-red-100">Alpha</div>
                        </div>
                        <i class="fas fa-times-circle text-2xl opacity-50"></i>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg shadow-lg p-3 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-xl font-black" id="statTotal">0</div>
                            <div class="text-xs text-blue-100">Total Siswa</div>
                        </div>
                        <i class="fas fa-users text-2xl opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- CENTER: Scanner Area --}}
        <div class="lg:col-span-6 space-y-4">

        {{-- Action Toggle with Modern Design + Login Button --}}
        <div class="flex justify-center items-center gap-4">
            <button 
                onclick="setAction('check_in')" 
                id="btnCheckIn"
                class="action-btn active group relative px-8 py-3 rounded-xl font-bold text-base transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-2xl"
            >
                <div class="absolute inset-0 bg-gradient-to-r from-green-400 to-emerald-500 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                <div class="relative flex items-center gap-2">
                    <i class="fas fa-sign-in-alt text-xl"></i>
                    <span>Check In</span>
                </div>
            </button>
            <button 
                onclick="setAction('check_out')" 
                id="btnCheckOut"
                class="action-btn group relative px-8 py-3 rounded-xl font-bold text-base transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-2xl"
            >
                <div class="absolute inset-0 bg-gradient-to-r from-red-400 to-pink-500 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                <div class="relative flex items-center gap-2">
                    <i class="fas fa-sign-out-alt text-xl"></i>
                    <span>Check Out</span>
                </div>
            </button>
            
            {{-- Login Admin Button --}}
            <a href="{{ route('login') }}" class="group relative px-8 py-3 bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white rounded-xl font-bold text-base transition-all transform hover:scale-105 shadow-lg hover:shadow-2xl">
                <i class="fas fa-user-shield text-xl mr-2"></i>
                Login Admin
            </a>
        </div>

        {{-- Scanner Card with Premium Design --}}
        <div class="relative">
            <div class="absolute inset-0 bg-gradient-to-br from-primary-100 to-purple-100 dark:from-primary-900/20 dark:to-purple-900/20 rounded-2xl blur-xl transform scale-95"></div>
            <x-card class="relative backdrop-blur-sm bg-white/80 dark:bg-gray-800/80 border-2 border-primary-200 dark:border-primary-800/50">
                <div class="text-center space-y-3">
                    <h2 class="text-xl font-black text-gray-900 dark:text-white">
                        <span id="scannerTitle">Scan QR Code untuk Check In</span>
                    </h2>

                    {{-- QR Scanner Video with Frame --}}
                    <div class="relative inline-block w-full max-w-lg mx-auto">
                        <div class="absolute -inset-3 bg-gradient-to-r from-primary-500 via-purple-500 to-pink-500 rounded-2xl opacity-30 blur-lg animate-pulse"></div>
                        <div class="relative bg-gray-900 rounded-xl p-3 shadow-xl">
                            <div id="reader" class="mx-auto rounded-lg overflow-hidden" style="width: 100%; max-width: 400px; min-height: 300px;"></div>
                            
                            {{-- Scanning Animation Overlay --}}
                            <div id="scanOverlay" class="absolute inset-3 pointer-events-none rounded-lg overflow-hidden">
                                <div class="scan-line"></div>
                            </div>
                        </div>
                    </div>

                    {{-- Modern Instructions --}}
                    <div class="grid grid-cols-3 gap-3 max-w-2xl mx-auto">
                        <div class="flex flex-col items-center gap-2 p-2 bg-primary-50 dark:bg-primary-900/20 rounded-lg">
                            <div class="w-8 h-8 bg-primary-500 text-white rounded-lg flex items-center justify-center">
                                <i class="fas fa-mobile-alt text-sm"></i>
                            </div>
                            <p class="font-semibold text-gray-900 dark:text-white text-xs">Posisi Tengah</p>
                        </div>
                        
                        <div class="flex flex-col items-center gap-2 p-2 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
                            <div class="w-8 h-8 bg-purple-500 text-white rounded-lg flex items-center justify-center">
                                <i class="fas fa-sun text-sm"></i>
                            </div>
                            <p class="font-semibold text-gray-900 dark:text-white text-xs">Cukup Terang</p>
                        </div>
                        
                        <div class="flex flex-col items-center gap-2 p-2 bg-green-50 dark:bg-green-900/20 rounded-lg">
                            <div class="w-8 h-8 bg-green-500 text-white rounded-lg flex items-center justify-center">
                                <i class="fas fa-check-circle text-sm"></i>
                            </div>
                            <p class="font-semibold text-gray-900 dark:text-white text-xs">Auto Scan</p>
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

        {{-- RIGHT SIDEBAR: Branding + Recent Scans --}}
        <div class="lg:col-span-3 flex flex-col gap-4">
            {{-- Logo & School Name --}}
            <div class="bg-gradient-to-br from-primary-600 to-purple-600 rounded-xl shadow-lg p-4 text-white text-center">
                <div class="inline-flex items-center justify-center w-12 h-12 bg-white/20 backdrop-blur-lg rounded-lg mb-2">
                    <i class="fas fa-graduation-cap text-2xl"></i>
                </div>
                <h2 class="text-lg font-black mb-1">SMK PGRI BLORA</h2>
                <p class="text-xs text-primary-100">Sistem Absensi QR Code</p>
            </div>

            {{-- Recent Scans Timeline --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-3 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-7 h-7 bg-gradient-to-br from-primary-500 to-purple-500 rounded-lg flex items-center justify-center text-white">
                        <i class="fas fa-history text-xs"></i>
                    </div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">Recent Scans</h3>
                </div>

                <div id="recentScansTimeline" class="space-y-2 overflow-y-auto" style="max-height: calc(5 * 88px);">
                    {{-- Timeline items will be added here dynamically --}}
                    {{-- Each item is approximately 88px tall (72px content + 16px gap) --}}
                    <div class="text-center text-gray-400 dark:text-gray-500 py-4">
                        <i class="fas fa-qrcode text-2xl mb-2"></i>
                        <p class="text-xs">Belum ada scan</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Toast Container --}}
    <div id="toast-container" class="fixed top-4 right-4 z-50 space-y-2"></div>

    @push('styles')
    <style>
        /* Toast Notification Styles */
        .toast {
            min-width: 300px;
            max-width: 400px;
            padding: 16px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideInRight 0.4s ease-out, pulse 0.5s ease-in-out 0.4s;
            position: relative;
            overflow: hidden;
        }

        .toast::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(180deg, #fff, transparent);
        }

        .toast-icon {
            font-size: 32px;
            flex-shrink: 0;
            animation: bounceIn 0.6s ease-out 0.2s backwards;
        }

        .toast-content {
            flex: 1;
        }

        .toast-title {
            font-weight: 700;
            font-size: 16px;
            margin-bottom: 4px;
        }

        .toast-message {
            font-size: 13px;
            opacity: 0.9;
        }

        .toast-close {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .toast-close:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: rotate(90deg);
        }

        /* Toast Variants */
        .toast.success {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }

        .toast.warning {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .toast.info {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        /* Animations */
        @keyframes slideInRight {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(400px);
                opacity: 0;
            }
        }

        @keyframes bounceIn {
            0% {
                transform: scale(0.3);
                opacity: 0;
            }
            50% {
                transform: scale(1.1);
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.02);
            }
        }

        .toast.removing {
            animation: slideOutRight 0.3s ease-out forwards;
        }
    </style>
    @endpush

    @push('scripts')

    <script>
        let currentAction = 'check_in';
        let html5QrCode = null;
        let lastScannedNis = null;
        let recentScans = [];

        // Real-time clock
        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            const dateString = now.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
            
            document.getElementById('currentTime').textContent = timeString;
            document.getElementById('currentDate').textContent = dateString;
        }

        // Update clock every second
        setInterval(updateClock, 1000);
        updateClock(); // Initial call

        // Wait for Html5Qrcode to be available (loaded by app.js via Vite)
        function waitForHtml5Qrcode() {
            if (typeof window.Html5Qrcode !== 'undefined') {
                console.log('Html5Qrcode loaded successfully');
                initScanner();
                loadTodayStats();
                loadSchoolHours();
                loadAnnouncement();
                loadRecentScans(); // Load initial recent scans
                connectSSE(); // Connect to SSE for real-time updates
            } else {
                console.log('Waiting for Html5Qrcode...');
                setTimeout(waitForHtml5Qrcode, 100);
            }
        }

        // Initialize scanner on page load
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Scanner page loaded, checking Html5Qrcode availability...');
            waitForHtml5Qrcode();
        });

        function initScanner() {
            const Html5Qrcode = window.Html5Qrcode;
            html5QrCode = new Html5Qrcode("reader");
            
            const config = {
                fps: 10,
                qrbox: 250,
                aspectRatio: 1.0,
                videoConstraints: {
                    facingMode: "environment",
                    width: { ideal: 640 },
                    height: { ideal: 480 }
                }
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
            
            // Add to recent scans timeline
            addToRecentScans(result.data);
            
            // Update stats
            loadTodayStats();
            
            // Confetti effect (optional)
            if (window.confetti) {
                confetti({
                    particleCount: 100,
                    spread: 70,
                    origin: { y: 0.6 }
                });
            }
        }

        function addToRecentScans(data) {
            if (!data) return;
            
            const timeNow = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
            
            recentScans.unshift({
                nama: data.nama || 'Unknown',
                nis: data.nis || '-',
                kelas: data.kelas || '-',
                status: data.status || 'hadir',
                time: timeNow
            });
            
            // Keep only last 5 scans (visible without scroll)
            if (recentScans.length > 5) {
                recentScans.pop();
            }
            
            updateRecentScansUI();
        }

        function updateRecentScansUI() {
            const timeline = document.getElementById('recentScansTimeline');
            
            if (recentScans.length === 0) {
                timeline.innerHTML = `
                    <div class="text-center text-gray-400 dark:text-gray-500 py-8">
                        <i class="fas fa-qrcode text-3xl mb-2"></i>
                        <p class="text-sm">Belum ada scan</p>
                    </div>
                `;
                return;
            }
            
            timeline.innerHTML = recentScans.map(scan => `
                <div class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600 hover:shadow-md transition-shadow">
                    <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-br from-primary-500 to-purple-500 rounded-lg flex items-center justify-center text-white text-sm font-bold">
                        ${scan.nis.substring(0, 2)}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">${scan.nama}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">${scan.kelas}</p>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium ${
                                scan.status === 'hadir' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' :
                                scan.status === 'terlambat' ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400' :
                                'bg-gray-100 text-gray-700 dark:bg-gray-900/30 dark:text-gray-400'
                            }">
                                <i class="fas ${scan.status === 'hadir' ? 'fa-check' : 'fa-clock'} text-[10px]"></i>
                                ${scan.status}
                            </span>
                            <span class="text-xs text-gray-400 dark:text-gray-500">
                                <i class="far fa-clock"></i> ${scan.time}
                            </span>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        async function loadTodayStats() {
            try {
                const response = await fetch('/api/attendance/stats/today', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                
                const result = await response.json();
                
                if (result.success) {
                    const stats = result.data;
                    
                    // Update left sidebar stats
                    document.getElementById('statHadir').textContent = stats.hadir;
                    document.getElementById('statTerlambat').textContent = stats.terlambat;
                    document.getElementById('statAlpha').textContent = stats.alpha;
                    document.getElementById('statTotal').textContent = stats.total;
                } else {
                    console.error('Failed to load stats:', result.message);
                }
            } catch (error) {
                console.error('Failed to load stats:', error);
            }
        }

        async function loadSchoolHours() {
            try {
                const response = await fetch('/api/attendance/school-hours', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                
                const result = await response.json();
                
                if (result.success) {
                    const hours = result.data;
                    
                    // Update jam masuk & pulang di left sidebar
                    const jamMasukEl = document.querySelector('.text-primary-100:nth-of-type(1)').nextElementSibling;
                    const jamPulangEl = document.querySelector('.text-primary-100:nth-of-type(2)').nextElementSibling;
                    
                    if (jamMasukEl) {
                        jamMasukEl.textContent = `${hours.check_in_start} - ${hours.check_in_end}`;
                    }
                    if (jamPulangEl) {
                        jamPulangEl.textContent = `${hours.check_out_start} - ${hours.check_out_end}`;
                    }
                } else {
                    console.error('Failed to load school hours:', result.message);
                }
            } catch (error) {
                console.error('Failed to load school hours:', error);
            }
        }

        async function loadAnnouncement() {
            try {
                const response = await fetch('/api/announcement/active', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                
                const result = await response.json();
                
                if (result.success) {
                    const announcement = result.data;
                    
                    // Update pengumuman text di right sidebar
                    const announcementEl = document.querySelector('.from-orange-500 p.text-sm');
                    if (announcementEl) {
                        announcementEl.textContent = `"${announcement.message}"`;
                    }
                } else {
                    console.error('Failed to load announcement:', result.message);
                }
            } catch (error) {
                console.error('Failed to load announcement:', error);
            }
        }

        async function loadRecentScans() {
            try {
                const response = await fetch('/api/attendance/recent-scans', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                
                const result = await response.json();
                
                if (result.success && result.data) {
                    // Load initial data from database
                    recentScans = result.data;
                    updateRecentScansUI();
                    console.log(`✅ Loaded ${recentScans.length} recent scans from database`);
                } else {
                    console.error('Failed to load recent scans:', result.message);
                }
            } catch (error) {
                console.error('Failed to load recent scans:', error);
            }
        }

        function connectSSE() {
            // Connect to Server-Sent Events for real-time updates
            const eventSource = new EventSource('/api/attendance/sse');
            
            eventSource.addEventListener('new-scan', function(event) {
                try {
                    const scanData = JSON.parse(event.data);
                    console.log('🔔 New scan received via SSE:', scanData);
                    
                    // Add to recent scans
                    addToRecentScans(scanData);
                    
                    // Update stats
                    loadTodayStats();
                    
                    // Show notification (optional)
                    showNotification(scanData);
                } catch (error) {
                    console.error('Error parsing SSE data:', error);
                }
            });
            
            eventSource.onerror = function(error) {
                console.error('SSE connection error:', error);
                // Reconnect after 5 seconds
                eventSource.close();
                setTimeout(connectSSE, 5000);
            };
            
            console.log('🔌 Connected to SSE for real-time updates');
        }

        // ============================================
        // TOAST NOTIFICATION SYSTEM
        // ============================================

        /**
         * Play notification sound using Web Audio API
         */
        function playNotificationSound() {
            try {
                const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                const oscillator = audioContext.createOscillator();
                const gainNode = audioContext.createGain();
                
                oscillator.connect(gainNode);
                gainNode.connect(audioContext.destination);
                
                // Create pleasant "ding" sound
                oscillator.frequency.value = 800; // Hz
                oscillator.type = 'sine';
                
                // Envelope for smooth sound
                gainNode.gain.setValueAtTime(0, audioContext.currentTime);
                gainNode.gain.linearRampToValueAtTime(0.3, audioContext.currentTime + 0.01);
                gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);
                
                oscillator.start(audioContext.currentTime);
                oscillator.stop(audioContext.currentTime + 0.5);
            } catch (error) {
                console.error('Failed to play sound:', error);
            }
        }

        /**
         * Show toast notification
         * @param {string} title - Toast title
         * @param {string} message - Toast message
         * @param {string} type - Toast type: success, warning, info
         * @param {number} duration - Auto-dismiss duration in ms (0 = no auto-dismiss)
         */
        function showToast(title, message, type = 'success', duration = 4000) {
            const container = document.getElementById('toast-container');
            
            // Create toast element
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            
            // Icon based on type
            const icons = {
                success: '✅',
                warning: '⚠️',
                info: 'ℹ️',
                error: '❌'
            };
            
            toast.innerHTML = `
                <div class="toast-icon">${icons[type] || icons.success}</div>
                <div class="toast-content">
                    <div class="toast-title">${title}</div>
                    <div class="toast-message">${message}</div>
                </div>
                <button class="toast-close" onclick="dismissToast(this)">
                    <i class="fas fa-times text-xs"></i>
                </button>
            `;
            
            // Add to container
            container.appendChild(toast);
            
            // Play sound
            playNotificationSound();
            
            // Auto-dismiss after duration
            if (duration > 0) {
                setTimeout(() => {
                    dismissToast(toast.querySelector('.toast-close'));
                }, duration);
            }
            
            return toast;
        }

        /**
         * Dismiss toast notification
         */
        function dismissToast(closeButton) {
            const toast = closeButton.closest('.toast');
            if (toast) {
                toast.classList.add('removing');
                setTimeout(() => toast.remove(), 300);
            }
        }

        /**
         * Show notification for new scan (enhanced)
         */
        function showNotification(scanData) {
            if (!scanData) return;
            
            // Determine notification type and message
            let type = 'success';
            let icon = '🎉';
            let action = 'Check In';
            
            if (scanData.status === 'terlambat') {
                type = 'warning';
                icon = '⏰';
            }
            
            // Show toast notification
            const title = `${icon} ${scanData.nama} baru scan!`;
            const message = `${scanData.kelas} • ${scanData.status.toUpperCase()} • ${scanData.time}`;
            
            showToast(title, message, type, 5000);
            
            // Log for debugging
            console.log(`📢 ${scanData.nama} (${scanData.nis}) - ${scanData.status} at ${scanData.time}`);
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
        
        /* Ensure video is visible and properly sized */
        #reader video {
            width: 100% !important;
            height: auto !important;
            display: block !important;
            border-radius: 0.75rem;
        }
        
        #reader canvas {
            display: none !important;
        }
        
        #reader__scan_region {
            min-height: 400px !important;
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

