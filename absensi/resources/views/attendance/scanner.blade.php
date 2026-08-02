<x-app-layout>
    <x-slot name="title">QR Scanner</x-slot>
    <x-slot name="pageTitle">QR Scanner</x-slot>
    
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4" id="scanner-container">
        
        {{-- LEFT SIDEBAR: Logo + Stats Cards --}}
        <div class="lg:col-span-3 space-y-3">
            {{-- Logo & School Name --}}
            <div class="bg-gradient-to-br from-primary-600 to-purple-600 rounded-xl shadow-lg p-4 text-white text-center">
                <div class="inline-flex items-center justify-center w-12 h-12 bg-white/20 backdrop-blur-lg rounded-lg mb-2">
                    <i class="fas fa-graduation-cap text-2xl"></i>
                </div>
                <h2 class="text-lg font-black mb-1">SMK PGRI BLORA</h2>
                <p class="text-xs text-primary-100">Sistem Absensi QR Code</p>
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

        {{-- CENTER: Clock + Scanner Area --}}
        <div class="lg:col-span-6 space-y-4">

        {{-- Action Toggle + Clock - All in one row --}}
        <div class="flex justify-center items-center gap-4">
            <button 
                onclick="setAction('check_in')" 
                id="btnCheckIn"
                class="action-btn group relative px-8 py-3 rounded-xl font-bold text-base transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-2xl"
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
            
            {{-- Real-time Clock - Same size as buttons --}}
            <div class="bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white rounded-xl px-8 py-3 font-bold text-base shadow-lg flex items-center gap-3">
                <i class="fas fa-clock text-xl"></i>
                <div class="text-left">
                    <div id="currentTime" class="font-black leading-tight">00:00:00</div>
                    <div id="currentDate" class="text-xs opacity-90 leading-tight">Loading...</div>
                </div>
            </div>
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

        {{-- Old Error Card removed - using modal overlay system now --}}
        </div>

        {{-- RIGHT SIDEBAR: Recent Scans --}}
        <div class="lg:col-span-3 flex flex-col gap-4">
            {{-- Recent Scans Timeline --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-3 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-7 h-7 bg-gradient-to-br from-primary-500 to-purple-500 rounded-lg flex items-center justify-center text-white">
                        <i class="fas fa-history text-xs"></i>
                    </div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">Recent Scans</h3>
                </div>

                <div id="recentScansTimeline" class="space-y-2 overflow-y-auto" style="max-height: calc(100vh - 250px);">
                    {{-- Timeline items will be added here dynamically --}}
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

    {{-- Modal Overlay untuk Success/Error --}}
    <div id="modalOverlay" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" style="animation: fadeIn 0.2s ease-out;">
        <div id="modalContent" class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full transform" style="animation: scaleIn 0.3s ease-out;">
            <!-- Modal content will be injected here -->
        </div>
    </div>

    @push('styles')
    <style>
        /* Fix QR Scanner bounds - make all sides equal */
        #reader__scan_region {
            border: 3px solid rgba(59, 130, 246, 0.8) !important;
        }
        
        #reader__scan_region video {
            object-fit: cover !important;
        }
        
        /* Ensure scan box corners are symmetrical */
        #reader__dashboard_section_csr {
            padding: 0 !important;
        }

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

        /* Modal Overlay Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        @keyframes fadeOut {
            from {
                opacity: 1;
            }
            to {
                opacity: 0;
            }
        }

        @keyframes scaleIn {
            from {
                transform: scale(0.9);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        @keyframes scaleOut {
            from {
                transform: scale(1);
                opacity: 1;
            }
            to {
                transform: scale(0.9);
                opacity: 0;
            }
        }

        .modal-fade-out {
            animation: fadeOut 0.2s ease-out forwards;
        }

        .modal-scale-out {
            animation: scaleOut 0.2s ease-out forwards;
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
            
            // Update sidebar clock
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
                loadRecentScans(); // Load initial recent scans
                autoSetActionByTime(); // Auto-set Check In/Out based on current time
                // connectSSE(); // DISABLED: SSE causing 30s timeout and blocking server
            } else {
                console.log('Waiting for Html5Qrcode...');
                setTimeout(waitForHtml5Qrcode, 100);
            }
        }
        
        /**
         * Auto-set action (Check In/Out) based on current time
         * Morning = Check In, Afternoon = Check Out
         */
        function autoSetActionByTime() {
            const now = new Date();
            const currentHour = now.getHours();
            const currentMinute = now.getMinutes();
            const currentTime = currentHour * 60 + currentMinute; // Convert to minutes
            
            // Check-out start time (default: 15:00 = 900 minutes)
            // You can adjust this threshold based on school schedule
            const checkOutStartTime = 15 * 60; // 15:00 in minutes
            
            // Determine initial action based on time
            const initialAction = currentTime >= checkOutStartTime ? 'check_out' : 'check_in';
            currentAction = initialAction;
            
            // Wait for DOM to be fully ready, then set action
            setTimeout(() => {
                if (initialAction === 'check_out') {
                    setAction('check_out');
                    console.log('🌆 Auto-set to Check Out (afternoon mode)');
                } else {
                    setAction('check_in');
                    console.log('🌅 Auto-set to Check In (morning mode)');
                }
            }, 300); // Increased delay to ensure DOM is ready
            
            // Update every 5 minutes to keep in sync
            setInterval(() => {
                const now = new Date();
                const currentHour = now.getHours();
                const currentMinute = now.getMinutes();
                const currentTime = currentHour * 60 + currentMinute;
                
                if (currentTime >= checkOutStartTime && currentAction === 'check_in') {
                    setAction('check_out');
                    console.log('🌆 Auto-switched to Check Out');
                    showToast('info', '🌆 Mode berubah', 'Sekarang mode Check Out', 3000);
                } else if (currentTime < checkOutStartTime && currentAction === 'check_out') {
                    setAction('check_in');
                    console.log('🌅 Auto-switched to Check In');
                    showToast('info', '🌅 Mode berubah', 'Sekarang mode Check In', 3000);
                }
            }, 5 * 60 * 1000); // Check every 5 minutes
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
                fps: 30,                    // Aggressive 30 FPS for instant detection
                qrbox: { width: 300, height: 300 },  // Square scan area with equal dimensions
                aspectRatio: 1.0,
                disableFlip: false,         // Allow flipped QR codes
                rememberLastUsedCamera: true, // Remember camera selection
                videoConstraints: {
                    facingMode: "environment",
                    width: { ideal: 1280, max: 1920 },   // Higher resolution for better detection
                    height: { ideal: 720, max: 1080 }
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
            const now = Date.now();
            
            // IMMEDIATE LOCK: Block all scans if processing or within cooldown
            if (window.isProcessingScan) {
                return; // Silent block - no log spam
            }
            
            if (lastScannedNis === decodedText && window.lastScanTime && (now - window.lastScanTime) < 3000) {
                return; // Silent block - within cooldown
            }
            
            // LOCK IMMEDIATELY before any async operation
            window.isProcessingScan = true;
            lastScannedNis = decodedText;
            window.lastScanTime = now;
            
            console.log('✅ QR Code detected:', decodedText);
            
            // Process the scan (lock is already set)
            processScan(decodedText);
        }

        function onScanFailure(error) {
            // Silently ignore scan failures (expected during scanning)
        }

        async function processScan(nis) {
            try {
                // Capture photo from video (optional, bisa pakai dummy)
                const photoBase64 = await capturePhoto();

                console.log('Sending scan request to server...');
                
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

                console.log('Response status:', response.status, response.statusText);
                
                // Check if response is OK (200-299)
                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('Server error response:', errorText);
                    window.isProcessingScan = false;
                    
                    // Try to parse as JSON for better error message
                    try {
                        const errorJson = JSON.parse(errorText);
                        showError(errorJson.message || 'Server error: ' + response.status, errorJson.data);
                    } catch (e) {
                        showError('Server error: ' + response.status + ' - ' + errorText.substring(0, 100), null);
                    }
                    return;
                }

                const result = await response.json();
                console.log('Server response:', result);

                // Clear processing flag
                window.isProcessingScan = false;

                if (result.success) {
                    showSuccess(result);
                } else {
                    // Pass error data (including student info if duplicate)
                    showError(result.message || 'Gagal memproses absensi', result.data);
                }

                // Note: Scanner resume is handled by showSuccess/showError
                // No longer pause scanner here for faster throughput

            } catch (error) {
                console.error('Scan processing error:', error);
                window.isProcessingScan = false;
                showError('Terjadi kesalahan saat memproses scan', null);
            }
        }

        async function capturePhoto() {
            try {
                // Get video element from QR scanner
                const videoElement = document.querySelector('#reader video');
                
                if (!videoElement) {
                    console.warn('Video element not found, skipping photo capture');
                    return null;
                }
                
                // Create canvas to capture frame
                const canvas = document.createElement('canvas');
                canvas.width = videoElement.videoWidth || 640;
                canvas.height = videoElement.videoHeight || 480;
                
                const ctx = canvas.getContext('2d');
                
                // Draw current video frame to canvas
                ctx.drawImage(videoElement, 0, 0, canvas.width, canvas.height);
                
                // Convert to base64 (JPEG with 80% quality for smaller size)
                const photoBase64 = canvas.toDataURL('image/jpeg', 0.8);
                
                console.log('📸 Photo captured successfully:', photoBase64.substring(0, 50) + '...');
                
                return photoBase64;
            } catch (error) {
                console.error('Failed to capture photo:', error);
                // Return null if photo capture fails - backend will handle it
                return null;
            }
        }

        function showSuccess(result) {
            // Determine action type
            const isCheckIn = currentAction === 'check_in';
            const actionText = isCheckIn ? 'Datang' : 'Pulang';
            const actionIcon = isCheckIn ? '👋' : '🏃';
            const actionMessage = isCheckIn ? 'Selamat datang di sekolah!' : 'Hati-hati di jalan!';
            
            // 1. Show toast notification first (instant feedback)
            showToast(
                'success',
                `${actionIcon} ${actionText}!`,
                result.message || actionMessage
            );

            // 2. Show detailed modal overlay
            const modalOverlay = document.getElementById('modalOverlay');
            const modalContent = document.getElementById('modalContent');

            const statusColors = {
                'hadir': {
                    bg: 'from-green-400 to-emerald-500',
                    icon: 'fa-check-circle',
                    text: 'text-green-600'
                },
                'terlambat': {
                    bg: 'from-yellow-400 to-orange-500',
                    icon: 'fa-clock',
                    text: 'text-yellow-600'
                },
                'alpha': {
                    bg: 'from-gray-400 to-gray-500',
                    icon: 'fa-times-circle',
                    text: 'text-gray-600'
                }
            };

            const status = result.data?.status || 'hadir';
            const colors = statusColors[status] || statusColors['hadir'];

            // Icon based on action
            const modalIcon = isCheckIn ? 'fa-hand-wave' : 'fa-person-walking-arrow-right';
            const modalIconAlt = isCheckIn ? '👋' : '🚶‍♂️→';

            modalContent.innerHTML = `
                <div class="p-6 text-center">
                    <!-- Action Icon -->
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br ${colors.bg} rounded-full mb-4 shadow-lg">
                        <i class="fas ${modalIcon} text-4xl text-white"></i>
                    </div>

                    <!-- Action Title -->
                    <h3 class="text-2xl font-black mb-2" style="background: linear-gradient(135deg, ${isCheckIn ? '#10b981, #3b82f6' : '#f59e0b, #ef4444'}); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                        ${isCheckIn ? '🌅 SELAMAT DATANG!' : '🌆 SELAMAT JALAN!'}
                    </h3>

                    <!-- Student Info -->
                    <p class="text-xl font-bold text-gray-900 dark:text-white mb-1">
                        ${result.data?.nama || '-'}
                    </p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                        NIS: ${result.data?.nis || '-'}
                    </p>

                    <!-- Details Grid -->
                    <div class="grid grid-cols-3 gap-3 mb-4">
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Kelas</p>
                            <p class="text-sm font-bold text-gray-900 dark:text-white">${result.data?.kelas || '-'}</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Waktu</p>
                            <p class="text-sm font-bold text-gray-900 dark:text-white">${result.data?.time || '-'}</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Status</p>
                            <p class="text-sm font-bold ${colors.text}">${(result.data?.status || 'hadir').toUpperCase()}</p>
                        </div>
                    </div>

                    <!-- Message based on action -->
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                        ${isCheckIn ? '📚 Semangat belajar hari ini!' : '🎒 Sampai jumpa besok!'}
                    </p>

                    <!-- Auto close indicator -->
                    <p class="text-xs text-gray-400 dark:text-gray-500">
                        <i class="fas fa-circle-notch fa-spin mr-1"></i>
                        Auto-close dalam 2 detik...
                    </p>
                </div>
            `;

            // Show modal
            modalOverlay.classList.remove('hidden');

            // 3. Add to recent scans
            addToRecentScans(result.data);

            // 4. Update stats
            loadTodayStats();

            // 5. Play sound (if exists)
            playNotificationSound();

            // 6. Auto-close after 2 seconds
            setTimeout(() => {
                hideModal();
            }, 2000);

            // 7. Resume scanner after 2.5 seconds (give time for modal to close)
            setTimeout(() => {
                lastScannedNis = null;
                window.lastScanTime = null;
                console.log('Scanner cooldown cleared, ready for next scan');
                
                // Try to resume scanner if it was paused
                try {
                    if (html5QrCode && html5QrCode.resume) {
                        html5QrCode.resume();
                        console.log('Scanner resumed successfully');
                    }
                } catch (e) {
                    // Scanner already running or error, ignore
                    console.log('Scanner resume skipped:', e.message);
                }
            }, 2500);
        }

        function showError(message, errorData = null) {
            console.log('showError called:', { message, errorData });
            
            // 1. Show toast notification
            showToast(
                'warning',
                '⚠️ Gagal!',
                message || 'Terjadi kesalahan'
            );

            // 2. Show error modal overlay
            const modalOverlay = document.getElementById('modalOverlay');
            const modalContent = document.getElementById('modalContent');

            // Check if this is a duplicate scan with student data
            const isDuplicate = errorData && errorData.duplicate;
            console.log('isDuplicate:', isDuplicate, 'errorData:', errorData);
            
            if (isDuplicate && errorData.nama) {
                // Show detailed duplicate info (similar to success but with warning style)
                const isCheckIn = currentAction === 'check_in';
                
                modalContent.innerHTML = `
                    <div class="p-6 text-center">
                        <!-- Warning Icon -->
                        <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-orange-400 to-red-500 rounded-full mb-4 shadow-lg">
                            <i class="fas fa-exclamation-circle text-4xl text-white"></i>
                        </div>

                        <!-- Title -->
                        <h3 class="text-2xl font-black text-orange-800 dark:text-orange-300 mb-2">
                            ⚠️ SUDAH ABSEN!
                        </h3>

                        <!-- Student Info -->
                        <p class="text-xl font-bold text-gray-900 dark:text-white mb-1">
                            ${errorData.nama}
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                            NIS: ${errorData.nis}
                        </p>

                        <!-- Details Grid -->
                        <div class="grid grid-cols-3 gap-3 mb-4">
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Kelas</p>
                                <p class="text-sm font-bold text-gray-900 dark:text-white">${errorData.kelas}</p>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Waktu ${isCheckIn ? 'Datang' : 'Pulang'}</p>
                                <p class="text-sm font-bold text-gray-900 dark:text-white">${errorData.time}</p>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Status</p>
                                <p class="text-sm font-bold text-orange-600">${(errorData.status || 'hadir').toUpperCase()}</p>
                            </div>
                        </div>

                        <!-- Message -->
                        <p class="text-sm text-gray-700 dark:text-gray-300 mb-3">
                            ${message}
                        </p>

                        <!-- Auto close indicator -->
                        <p class="text-xs text-gray-400 dark:text-gray-500">
                            <i class="fas fa-circle-notch fa-spin mr-1"></i>
                            Auto-close dalam 3 detik...
                        </p>
                    </div>
                `;
            } else {
                // Generic error without student data
                modalContent.innerHTML = `
                    <div class="p-6 text-center">
                        <!-- Error Icon -->
                        <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-red-400 to-pink-500 rounded-full mb-4 shadow-lg">
                            <i class="fas fa-exclamation-triangle text-4xl text-white"></i>
                        </div>

                        <!-- Error Message -->
                        <h3 class="text-2xl font-black text-red-800 dark:text-red-300 mb-2">
                            Oops!
                        </h3>
                        <p class="text-base text-gray-700 dark:text-gray-300 mb-4">
                            ${message || 'Terjadi kesalahan'}
                        </p>

                        <!-- Auto close indicator -->
                        <p class="text-xs text-gray-400 dark:text-gray-500">
                            <i class="fas fa-circle-notch fa-spin mr-1"></i>
                            Auto-close dalam 3 detik...
                        </p>
                    </div>
                `;
            }

            // Show modal
            modalOverlay.classList.remove('hidden');

            // Auto-close after 3 seconds (longer for error so user can read)
            setTimeout(() => {
                hideModal();
            }, 3000);

            // Resume scanner after 3.5 seconds
            setTimeout(() => {
                lastScannedNis = null;
                window.lastScanTime = null;
                console.log('Scanner cooldown cleared after error, ready for next scan');
                
                // Try to resume scanner if it was paused
                try {
                    if (html5QrCode && html5QrCode.resume) {
                        html5QrCode.resume();
                        console.log('Scanner resumed successfully after error');
                    }
                } catch (e) {
                    // Scanner already running or error, ignore
                    console.log('Scanner resume skipped:', e.message);
                }
            }, 3500);
        }

        function hideModal() {
            const modalOverlay = document.getElementById('modalOverlay');
            const modalContent = document.getElementById('modalContent');

            // Add fade-out animation
            modalOverlay.classList.add('modal-fade-out');
            modalContent.classList.add('modal-scale-out');

            // Remove after animation
            setTimeout(() => {
                modalOverlay.classList.add('hidden');
                modalOverlay.classList.remove('modal-fade-out');
                modalContent.classList.remove('modal-scale-out');
            }, 200);
        }

        // Legacy functions (keep for compatibility but not used)
        function hideResult() {
            hideModal();
        }

        function hideError() {
            hideModal();
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
            
            timeline.innerHTML = recentScans.map(scan => {
                // Determine action type
                const isCheckIn = scan.action === 'check_in';
                const actionIcon = isCheckIn ? 'fa-sign-in-alt' : 'fa-sign-out-alt';
                const actionLabel = isCheckIn ? 'Datang' : 'Pulang';
                const actionColor = isCheckIn ? 'from-green-500 to-emerald-500' : 'from-blue-500 to-indigo-500';
                
                return `
                <div class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600 hover:shadow-md transition-shadow">
                    <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-br ${actionColor} rounded-lg flex items-center justify-center text-white text-sm">
                        <i class="fas ${actionIcon}"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">${scan.nama}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">${scan.kelas}</p>
                        <div class="flex items-center gap-2 mt-1 flex-wrap">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium ${
                                isCheckIn 
                                    ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' 
                                    : 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'
                            }">
                                <i class="fas ${actionIcon} text-[10px]"></i>
                                ${actionLabel}
                            </span>
                            <span class="text-xs text-gray-400 dark:text-gray-500">
                                <i class="far fa-clock"></i> ${scan.time}
                            </span>
                        </div>
                    </div>
                </div>
            `;
            }).join('');
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
            // Only try SSE if not already connected
            if (window.sseConnection && window.sseConnection.readyState !== EventSource.CLOSED) {
                console.log('SSE already connected');
                return;
            }

            try {
                // Connect to Server-Sent Events for real-time updates
                const eventSource = new EventSource('/api/attendance/sse');
                window.sseConnection = eventSource;
                
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
                
                eventSource.onopen = function() {
                    console.log('🔌 Connected to SSE for real-time updates');
                };
                
                eventSource.onerror = function(error) {
                    console.warn('SSE connection error (will auto-reconnect):', error.type);
                    
                    // Close the connection
                    if (eventSource.readyState === EventSource.CLOSED) {
                        console.log('SSE connection closed, will retry in 10 seconds...');
                        // Only retry after 10 seconds to avoid spam
                        setTimeout(() => {
                            window.sseConnection = null;
                            connectSSE();
                        }, 10000);
                    }
                };
            } catch (error) {
                console.error('Failed to establish SSE connection:', error);
                // Retry after 10 seconds
                setTimeout(() => {
                    window.sseConnection = null;
                    connectSSE();
                }, 10000);
            }
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


        // OLD showError, hideResult, hideError functions removed - using new modal-based functions above

        function setAction(action) {
            currentAction = action;
            
            // Update button styles
            const btnCheckIn = document.getElementById('btnCheckIn');
            const btnCheckOut = document.getElementById('btnCheckOut');
            
            if (btnCheckIn && btnCheckOut) {
                btnCheckIn.classList.toggle('active', action === 'check_in');
                btnCheckOut.classList.toggle('active', action === 'check_out');
            }
            
            // Update title with emoji
            const title = action === 'check_in' 
                ? '🌅 Scan QR Code untuk Check In' 
                : '🌆 Scan QR Code untuk Check Out';
            document.getElementById('scannerTitle').textContent = title;
            
            hideResult();
            hideError();
            lastScannedNis = null;
        }

        // ============================================================================
        // POLLING FOR REAL-TIME UPDATES (Replaces SSE for better performance)
        // ============================================================================
        
        let pollingInterval = null;
        let isPollingPaused = false;

        /**
         * Start polling for real-time stats and recent scans updates
         * Polls every 5 seconds when tab is active
         */
        function startPolling() {
            // Don't start if already polling
            if (pollingInterval) {
                console.log('⚠️ Polling already running');
                return;
            }

            // Initial load
            loadTodayStats();
            loadRecentScans();
            
            // Poll every 5 seconds
            pollingInterval = setInterval(() => {
                if (!isPollingPaused) {
                    loadTodayStats();
                    loadRecentScans();
                }
            }, 5000); // 5 seconds
            
            console.log('✅ Polling started (interval: 5s)');
        }

        /**
         * Stop polling completely
         */
        function stopPolling() {
            if (pollingInterval) {
                clearInterval(pollingInterval);
                pollingInterval = null;
                console.log('⏹️ Polling stopped');
            }
        }

        /**
         * Pause polling temporarily (don't clear interval)
         */
        function pausePolling() {
            isPollingPaused = true;
            console.log('⏸️ Polling paused');
        }

        /**
         * Resume polling
         */
        function resumePolling() {
            isPollingPaused = false;
            // Immediate update when resuming
            loadTodayStats();
            loadRecentScans();
            console.log('▶️ Polling resumed');
        }

        // ============================================================================
        // PAGE VISIBILITY API - Pause polling when tab is hidden (save resources)
        // ============================================================================
        
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                pausePolling();
                console.log('👁️ Tab hidden - polling paused to save resources');
            } else {
                resumePolling();
                console.log('👁️ Tab visible - polling resumed with immediate update');
            }
        });

        // ============================================================================
        // START POLLING ON PAGE LOAD
        // ============================================================================
        
        // Start polling after initial data load
        // Wait for waitForHtml5Qrcode() to finish loading initial data first
        setTimeout(() => {
            startPolling();
        }, 2000); // Wait 2 seconds for initial load to complete

        // Cleanup on page unload
        window.addEventListener('beforeunload', function() {
            stopPolling();
        });

        console.log('📊 Polling system initialized - will start after initial data load');
    </script>

    <style>
        /* Premium Action Button Styles */
        .action-btn {
            position: relative;
            background: linear-gradient(to bottom right, rgb(243, 244, 246), rgb(229, 231, 235));
            color: rgb(55, 65, 81);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }
        
        .dark .action-btn {
            background: linear-gradient(to bottom right, rgb(55, 65, 81), rgb(31, 41, 55));
            color: rgb(209, 213, 219);
        }
        
        .action-btn.active {
            background: linear-gradient(to bottom right, rgb(59, 130, 246), rgb(147, 51, 234)) !important;
            color: white !important;
            box-shadow: 0 20px 40px -10px rgba(59, 130, 246, 0.5) !important;
        }
        
        .action-btn:hover:not(.active) {
            transform: translateY(-4px);
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

