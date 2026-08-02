<x-app-layout>
    <x-slot name="title">QR Scanner</x-slot>
    <x-slot name="pageTitle">QR Scanner</x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6" id="scanner-container">
        
        {{-- LEFT SIDEBAR: Stats Cards --}}
        <div class="lg:col-span-2 space-y-4">
            {{-- Header with Clock --}}
            <div class="bg-gradient-to-br from-primary-600 to-purple-600 rounded-2xl shadow-xl p-6 text-white text-center">
                <div class="inline-flex items-center justify-center w-12 h-12 bg-white/20 backdrop-blur-lg rounded-xl mb-3">
                    <i class="fas fa-clock text-2xl"></i>
                </div>
                <div id="currentTime" class="text-3xl font-black mb-1">00:00:00</div>
                <div id="currentDate" class="text-sm text-primary-100">Loading...</div>
            </div>

            {{-- Stats Cards --}}
            <div class="space-y-3">
                <div class="bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl shadow-lg p-4 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-2xl font-black" id="statHadir">0</div>
                            <div class="text-xs text-green-100">Hadir</div>
                        </div>
                        <i class="fas fa-check-circle text-3xl opacity-50"></i>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-yellow-500 to-orange-600 rounded-xl shadow-lg p-4 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-2xl font-black" id="statTerlambat">0</div>
                            <div class="text-xs text-yellow-100">Terlambat</div>
                        </div>
                        <i class="fas fa-clock text-3xl opacity-50"></i>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-red-500 to-pink-600 rounded-xl shadow-lg p-4 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-2xl font-black" id="statAlpha">0</div>
                            <div class="text-xs text-red-100">Alpha</div>
                        </div>
                        <i class="fas fa-times-circle text-3xl opacity-50"></i>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl shadow-lg p-4 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-2xl font-black" id="statTotal">0</div>
                            <div class="text-xs text-blue-100">Total Siswa</div>
                        </div>
                        <i class="fas fa-users text-3xl opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- CENTER: Scanner Area --}}
        <div class="lg:col-span-7 space-y-6">
        
        {{-- Header Section with Gradient --}}
        <div class="relative overflow-hidden bg-gradient-to-br from-primary-600 via-primary-500 to-purple-600 rounded-3xl shadow-2xl p-6 text-white">
            <div class="absolute top-0 right-0 -mt-12 -mr-12 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 left-0 -mb-12 -ml-12 w-64 h-64 bg-purple-500/20 rounded-full blur-3xl"></div>
            
            <div class="relative z-10 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-white/20 backdrop-blur-lg rounded-2xl mb-3 shadow-xl">
                    <i class="fas fa-qrcode text-3xl"></i>
                </div>
                <h1 class="text-3xl font-black mb-1">QR Scanner Premium</h1>
                <p class="text-primary-100">Scan untuk absensi real-time</p>
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
                    <div class="relative inline-block w-full max-w-xl mx-auto">
                        <div class="absolute -inset-4 bg-gradient-to-r from-primary-500 via-purple-500 to-pink-500 rounded-3xl opacity-30 blur-xl animate-pulse"></div>
                        <div class="relative bg-gray-900 rounded-2xl p-4 shadow-2xl">
                            <div id="reader" class="mx-auto rounded-xl overflow-hidden" style="width: 100%; max-width: 500px; min-height: 400px;"></div>
                            
                            {{-- Scanning Animation Overlay --}}
                            <div id="scanOverlay" class="absolute inset-4 pointer-events-none rounded-xl overflow-hidden">
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

        {{-- Result Card with Premium Success Animation - Fixed Overlay --}}
        <div id="resultCard" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm transform transition-all duration-500 scale-95 opacity-0">
            <div class="relative max-w-md w-full">
                <div class="absolute inset-0 bg-gradient-to-br from-green-100 to-emerald-100 dark:from-green-900/20 dark:to-emerald-900/20 rounded-2xl blur-xl transform scale-95"></div>
                <x-card class="relative backdrop-blur-sm bg-white/90 dark:bg-gray-800/90 border-2 border-green-200 dark:border-green-800/50">
                    <div class="text-center space-y-4">
                        <div id="resultIcon" class="relative inline-block">
                            <div class="absolute inset-0 bg-green-400 rounded-full animate-ping opacity-30"></div>
                            <div class="relative text-6xl animate-bounce">✅</div>
                        </div>
                        
                        <div>
                            <h3 id="resultTitle" class="text-2xl font-black text-gray-900 dark:text-white mb-1"></h3>
                            <p id="resultMessage" class="text-sm text-gray-600 dark:text-gray-400"></p>
                        </div>
                        
                        <div id="resultDetails" class="bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900 rounded-xl p-4 border border-gray-200 dark:border-gray-700 shadow-lg"></div>

                        <div class="flex justify-center gap-3 pt-2">
                            <button 
                                onclick="hideResult()" 
                                class="group relative px-6 py-3 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white rounded-lg font-bold transition-all transform hover:scale-105 shadow-lg text-sm"
                            >
                                <i class="fas fa-check mr-1"></i>
                                Selesai
                            </button>
                        </div>
                    </div>
                </x-card>
            </div>
        </div>

        {{-- Error Card with Premium Design - Fixed Overlay --}}
        <div id="errorCard" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm transform transition-all duration-500 scale-95 opacity-0">
            <div class="relative max-w-md w-full">
                <div class="absolute inset-0 bg-gradient-to-br from-red-100 to-pink-100 dark:from-red-900/20 dark:to-pink-900/20 rounded-2xl blur-xl transform scale-95"></div>
                <x-card class="relative backdrop-blur-sm bg-white/90 dark:bg-gray-800/90 border-2 border-red-200 dark:border-red-800/50">
                    <div class="text-center space-y-4">
                        <div class="relative inline-block">
                            <div class="absolute inset-0 bg-red-400 rounded-full animate-ping opacity-30"></div>
                            <i class="relative fas fa-exclamation-triangle text-6xl text-red-600 dark:text-red-400"></i>
                        </div>
                        
                        <div>
                            <h3 class="text-2xl font-black text-red-800 dark:text-red-300 mb-1">Oops!</h3>
                            <p id="errorMessage" class="text-sm text-red-700 dark:text-red-400"></p>
                        </div>
                        
                        <button 
                            onclick="hideError()" 
                            class="group relative px-6 py-3 bg-gradient-to-r from-red-500 to-pink-600 hover:from-red-600 hover:to-pink-700 text-white rounded-lg font-bold transition-all transform hover:scale-105 shadow-lg text-sm"
                        >
                            <i class="fas fa-times mr-1"></i>
                            Tutup
                        </button>
                    </div>
                </x-card>
            </div>
        </div>
        </div>

        {{-- RIGHT SIDEBAR: Recent Scans Timeline --}}
        <div class="lg:col-span-3 space-y-4">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-4 border-2 border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-8 h-8 bg-gradient-to-br from-primary-500 to-purple-500 rounded-lg flex items-center justify-center text-white">
                        <i class="fas fa-history text-sm"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Recent Scans</h3>
                </div>

                <div id="recentScansTimeline" class="space-y-3 max-h-[600px] overflow-y-auto">
                    {{-- Timeline items will be added here dynamically --}}
                    <div class="text-center text-gray-400 dark:text-gray-500 py-8">
                        <i class="fas fa-qrcode text-3xl mb-2"></i>
                        <p class="text-sm">Belum ada scan</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
                loadRecentScans(); // Load initial recent scans
                autoSetActionByTime(); // Auto-set Check In/Out based on current time
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
            }, 300);
            
            // Update every 5 minutes to keep in sync
            setInterval(() => {
                const now = new Date();
                const currentHour = now.getHours();
                const currentMinute = now.getMinutes();
                const currentTime = currentHour * 60 + currentMinute;
                
                if (currentTime >= checkOutStartTime && currentAction === 'check_in') {
                    setAction('check_out');
                    console.log('🌆 Auto-switched to Check Out');
                } else if (currentTime < checkOutStartTime && currentAction === 'check_out') {
                    setAction('check_in');
                    console.log('🌅 Auto-switched to Check In');
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
                    // Pass error data (including student info if duplicate)
                    showError(result.message || 'Gagal memproses absensi', result.data);
                }

                // Resume scanning after 3 seconds
                setTimeout(() => {
                    lastScannedNis = null;
                    html5QrCode.resume();
                }, 3000);

            } catch (error) {
                console.error('Scan processing error:', error);
                showError('Terjadi kesalahan saat memproses scan', null);
                
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
            
            // Auto-close after 3 seconds
            setTimeout(() => {
                hideResult();
            }, 3000);
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
            
            // Keep only last 10 scans
            if (recentScans.length > 10) {
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
                    
                    // Update scanner stats
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
                    // Replace local recent scans with server data
                    recentScans = result.data;
                    updateRecentScansUI();
                } else {
                    console.error('Failed to load recent scans:', result.message);
                }
            } catch (error) {
                console.error('Failed to load recent scans:', error);
            }
        }

        function showError(message, errorData = null) {
            console.log('showError called:', { message, errorData });
            
            const errorCard = document.getElementById('errorCard');
            
            if (!errorCard) {
                console.error('Error: errorCard element not found');
                return;
            }
            
            // Show card first before modifying (some browsers need element visible to querySelector)
            errorCard.classList.remove('hidden');
            
            // Find the content container inside x-card - try multiple selectors
            let contentContainer = errorCard.querySelector('.text-center.space-y-6');
            
            if (!contentContainer) {
                // Fallback: try to find any div with text-center class
                contentContainer = errorCard.querySelector('.text-center');
            }
            
            if (!contentContainer) {
                console.error('Error: .text-center container not found in errorCard');
                console.log('errorCard HTML:', errorCard.innerHTML.substring(0, 500));
                return;
            }
            
            console.log('Content container found:', contentContainer.className);
            
            // Check if this is a duplicate scan with student data
            const isDuplicate = errorData && errorData.duplicate;
            console.log('isDuplicate:', isDuplicate, 'errorData:', errorData);
            
            if (isDuplicate && errorData.nama) {
                // Show detailed duplicate info (similar to success but with warning style)
                const isCheckIn = currentAction === 'check_in';
                
                contentContainer.innerHTML = `
                    <!-- Warning Icon -->
                    <div class="relative inline-block">
                        <div class="absolute inset-0 bg-orange-400 rounded-full animate-ping opacity-30"></div>
                        <i class="relative fas fa-exclamation-circle text-6xl text-orange-600 dark:text-orange-400"></i>
                    </div>
                    
                    <!-- Title -->
                    <div>
                        <h3 class="text-2xl font-black text-orange-800 dark:text-orange-300 mb-1">
                            ⚠️ SUDAH ABSEN!
                        </h3>
                        <p class="text-sm text-orange-700 dark:text-orange-400 mb-3">${message}</p>
                    </div>
                    
                    <!-- Student Info -->
                    <div class="bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900 rounded-xl p-4 border border-gray-200 dark:border-gray-700 shadow-lg">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-red-500 rounded-lg flex items-center justify-center text-white text-lg font-bold shadow-lg">
                                ${errorData.nis.substring(0, 2)}
                            </div>
                            <div class="text-left flex-1">
                                <p class="text-base font-bold text-gray-900 dark:text-white">${errorData.nama}</p>
                                <p class="text-xs text-gray-600 dark:text-gray-400">NIS: ${errorData.nis}</p>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-3 gap-2 text-xs">
                            <div class="bg-white dark:bg-gray-700 rounded-lg p-2">
                                <p class="text-gray-500 dark:text-gray-400 text-[10px] mb-1">Kelas</p>
                                <p class="font-semibold text-gray-900 dark:text-white">${errorData.kelas}</p>
                            </div>
                            <div class="bg-white dark:bg-gray-700 rounded-lg p-2">
                                <p class="text-gray-500 dark:text-gray-400 text-[10px] mb-1">Waktu</p>
                                <p class="font-semibold text-gray-900 dark:text-white">${errorData.time}</p>
                            </div>
                            <div class="bg-white dark:bg-gray-700 rounded-lg p-2">
                                <p class="text-gray-500 dark:text-gray-400 text-[10px] mb-1">Status</p>
                                <p class="font-semibold text-orange-600">${(errorData.status || 'hadir').toUpperCase()}</p>
                            </div>
                        </div>
                    </div>
                    
                    <button 
                        onclick="hideError()" 
                        class="group relative px-6 py-3 bg-gradient-to-r from-orange-500 to-red-600 hover:from-orange-600 hover:to-red-700 text-white rounded-lg font-bold transition-all transform hover:scale-105 shadow-lg text-sm"
                    >
                        <i class="fas fa-times mr-1"></i>
                        Tutup
                    </button>
                `;
            } else {
                // Generic error without student data
                contentContainer.innerHTML = `
                    <div class="relative inline-block">
                        <div class="absolute inset-0 bg-red-400 rounded-full animate-ping opacity-30"></div>
                        <i class="relative fas fa-exclamation-triangle text-6xl text-red-600 dark:text-red-400"></i>
                    </div>
                    
                    <div>
                        <h3 class="text-2xl font-black text-red-800 dark:text-red-300 mb-1">Oops!</h3>
                        <p class="text-sm text-red-700 dark:text-red-400">${message || 'Terjadi kesalahan'}</p>
                    </div>
                    
                    <button 
                        onclick="hideError()" 
                        class="group relative px-6 py-3 bg-gradient-to-r from-red-500 to-pink-600 hover:from-red-600 hover:to-pink-700 text-white rounded-lg font-bold transition-all transform hover:scale-105 shadow-lg text-sm"
                    >
                        <i class="fas fa-times mr-1"></i>
                        Tutup
                    </button>
                `;
            }
            
            console.log('Content updated, showing error card with animation...');
            
            // Now animate in
            errorCard.classList.remove('scale-95', 'opacity-0');
            errorCard.classList.add('scale-100', 'opacity-100');
            hideResult();
            
            console.log('Error card visible with classes:', errorCard.className);
            
            // Resume scanner after 3 seconds
            setTimeout(() => {
                lastScannedNis = null;
                hideError();
            }, 3000);
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

        // ============================================================================
        // POLLING FOR REAL-TIME STATS UPDATES
        // ============================================================================
        
        let pollingInterval = null;
        let isPollingPaused = false;

        /**
         * Start polling for real-time stats updates
         * Polls every 5 seconds when tab is active
         */
        function startPolling() {
            if (pollingInterval) {
                console.log('⚠️ Polling already running');
                return;
            }

            // Initial load
            loadTodayStats();
            loadRecentScans(); // Also load recent scans
            
            // Poll every 5 seconds
            pollingInterval = setInterval(() => {
                if (!isPollingPaused) {
                    loadTodayStats();
                    loadRecentScans(); // Update recent scans from server
                }
            }, 5000);
            
            console.log('✅ Scanner: Polling started (interval: 5s)');
        }

        /**
         * Stop polling completely
         */
        function stopPolling() {
            if (pollingInterval) {
                clearInterval(pollingInterval);
                pollingInterval = null;
                console.log('⏹️ Scanner: Polling stopped');
            }
        }

        /**
         * Pause polling temporarily
         */
        function pausePolling() {
            isPollingPaused = true;
            console.log('⏸️ Scanner: Polling paused');
        }

        /**
         * Resume polling
         */
        function resumePolling() {
            isPollingPaused = false;
            loadTodayStats(); // Immediate update
            loadRecentScans(); // Immediate update for recent scans too
            console.log('▶️ Scanner: Polling resumed');
        }

        // ============================================================================
        // PAGE VISIBILITY API - Pause polling when tab is hidden
        // ============================================================================
        
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                pausePolling();
                console.log('👁️ Scanner tab hidden - polling paused');
            } else {
                resumePolling();
                console.log('👁️ Scanner tab visible - polling resumed');
            }
        });

        // ============================================================================
        // START POLLING ON PAGE LOAD
        // ============================================================================
        
        setTimeout(() => {
            startPolling();
        }, 2000); // Wait for initial data load

        window.addEventListener('beforeunload', function() {
            stopPolling();
        });

        console.log('📊 Scanner: Polling system initialized');
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

