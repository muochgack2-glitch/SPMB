<div class="qr-scanner-container">
    {{-- Action Selector --}}
    <div class="action-selector mb-4">
        <h2 class="text-center text-2xl font-bold mb-4 text-gray-900 dark:text-white">Sistem Absensi Siswa</h2>
        <div class="flex justify-center gap-4 mb-6">
            <button 
                type="button"
                id="btn-check-in"
                onclick="switchAction('check_in')"
                class="px-6 py-3 rounded-lg font-semibold transition-all bg-green-600 text-white"
            >
                📥 Check In (Masuk)
            </button>
            <button 
                type="button"
                id="btn-check-out"
                onclick="switchAction('check_out')"
                class="px-6 py-3 rounded-lg font-semibold transition-all bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600"
            >
                📤 Check Out (Pulang)
            </button>
        </div>
    </div>

    {{-- Scanner Area --}}
    <div class="scanner-area bg-gray-900 dark:bg-gray-950 rounded-lg p-6 mb-6">
        <div class="relative">
            {{-- Video Element for Webcam --}}
            <video id="qr-video" class="w-full rounded-lg" playsinline autoplay></video>
            
            {{-- Canvas for QR Detection (hidden) --}}
            <canvas id="qr-canvas" class="hidden"></canvas>
            
            {{-- Scanning Overlay --}}
            <div class="scanning-overlay absolute inset-0 flex items-center justify-center pointer-events-none">
                <div class="scan-frame border-4 border-green-400 w-64 h-64 rounded-lg animate-pulse"></div>
            </div>
            
            {{-- Status Indicator --}}
            <div id="scanner-status" class="absolute top-4 left-4 bg-black bg-opacity-70 text-white px-4 py-2 rounded-lg">
                <span class="status-text">Initializing camera...</span>
            </div>
        </div>
        
        <div class="text-center text-white mt-4">
            <p class="text-lg">Arahkan kamera ke QR Code siswa</p>
            <p class="text-sm text-gray-400">Mode: <span id="action-mode-text" class="font-bold">CHECK IN (Masuk)</span></p>
        </div>
    </div>

    {{-- Result Card --}}
    @if($showResult || $errorMessage)
    <div 
        id="result-card"
        class="result-card bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg p-6 mb-4"
        x-data="{ show: true }"
        x-show="show"
        x-transition
    >
        @if($errorMessage)
            {{-- Error Message --}}
            <div class="error-message bg-red-100 dark:bg-red-900/30 border-l-4 border-red-500 text-red-700 dark:text-red-300 p-4 mb-4">
                <div class="flex items-center">
                    <span class="text-2xl mr-3">❌</span>
                    <div>
                        <p class="font-bold">Error</p>
                        <p>{{ $errorMessage }}</p>
                    </div>
                </div>
            </div>
            <button 
                type="button"
                wire:click="hideResult"
                class="w-full px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition"
            >
                Tutup
            </button>
        @else
            {{-- Success/Loading State (will be updated by JavaScript) --}}
            <div id="scan-result-content" class="text-gray-900 dark:text-gray-100">
                <div class="flex items-center justify-center mb-4">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500"></div>
                </div>
                <p class="text-center text-gray-600 dark:text-gray-400">Memproses scan...</p>
            </div>
            
            {{-- Reject Button (hidden until scan succeeds) --}}
            <button 
                id="reject-button"
                class="hidden w-full px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition mt-4"
            >
                ❌ REJECT (Tolak Absensi)
            </button>
        @endif
    </div>
    @endif

    {{-- Instructions --}}
    <div class="instructions bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
        <h3 class="font-bold text-blue-900 dark:text-blue-200 mb-2">📋 Instruksi:</h3>
        <ul class="list-disc list-inside text-blue-800 dark:text-blue-300 text-sm space-y-1">
            <li>Pilih mode: Check In (masuk) atau Check Out (pulang)</li>
            <li>Pastikan kamera menghadap QR Code siswa</li>
            <li>Sistem akan otomatis mengambil foto saat QR terdeteksi</li>
            <li>Petugas dapat menekan tombol REJECT jika ada yang mencurigakan</li>
            <li>Hasil akan tampil selama 3 detik, lalu otomatis kembali ke mode scan</li>
        </ul>
    </div>

    {{-- Audio Elements for Feedback --}}
    <audio id="success-audio" preload="auto">
        <source src="data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBjCM0vLTgTAHGGS56+ihUxQJT6Pe77FYGAg+ltryxnMpBSh+zPLaizsIHGrE7+CWRAwYZ7rt66BVEQk9luHxuGkdBTGO0/LSgS8GJ3vH79uRPwsTXrTp7KhUFQlHouDwvmseBjCN0vLTgjAGKX7I8NqLOwgaaL/t559NEAxPqOPwtmMcBjiP1/PMeS0EJHfJ8N2RQAoTXbXq7KlUFQpGoN/wvmwhBjGM0fLTgjEGKH3H8NuQPwoaZ77s6qBUEglCm+DwtWMcBjiO1/PNejAFJHbI8N6SPwsUXLPp7KlUFQpFn97wv24gBjCM0fPUhTEGJnzH8N2SPwoaZrvt655NEQxPquDvtmMcBjiO1/PNezEFJHXH8N+TQAsUW7Lp7KpWFwpEnt3xwHEeBzCM0fPWhDIGJnvH8N+UPwsaZLft651MEwxOp97vtGMcBTiN1vLOfTQFI3TG8OCUQAwUWbDo7axYGApDndzxwnEeBzCL0fPWhTMGJXrG8OCVQAwZYrXs6lsTEwxNo9vvtGMcBTeL1fLPgDkFI3DG8OGXQw0UWK7n7a1aGgpBmtrxxXQhBzCK0PPYiDYGJHnF8OKZRg0ZXrHr6loUFAxModjvtWQdBTiJ1PLPgTsFJG/E8OKTQA4UWKzm7K1bGwpAl9jxxnYjBzCJ0PPZijkGI3fE8OKZSA8aXK7p6VoUFQxLodXvtWUdBjiH0vLPgj4FI27D8OKURA8UVqrm7K5dHApAldXyxnglBzCH0PPaizsGI3bD8OObSBEaWqvm6FoUFgxKn9TvtmYdBjiF0vLPgUAFI23C8OKTRRAUV6jl7K5fHgpAlNPzyHomBzCG0PPajDwGI3XC8OOcShIaWKvl6FsVFgxJnk/vt2geBjGF0vLQgUIFI2vC8OKVSBAUV6bk7K9gHwpAktPzyHwpBzCG0PPbpTwGI3TB8OOcShMbV6nk6FwWFwxJnNDvt2keBzGD0fLQgkMFI2vB8OOXTBEUV6Tj7LBhIApAkNHzyn8pBzCF0PPbpT4GI3LA8OOeTBMbVqjj6FwWGQxIms/vt2seBzGD0PLQg0QFI2rA8OOXTREUV6Li7LFjIgpAjtHzyoEqBzCE0PPcpkAGI3C/8OSfThQbVabj6F0XGQxImM7vt20fBzGC0PLQhEUFI2m/8OOYTREUV5/h7LJkIwpAjdD0zIEqBzCE0PPcqkMGI2+/8OSfTxUbVaPi6F0XGgxIl83vtnEfBzGA0PLRhEYFI2m+8OOZTxEUV57h7LJkJApAjM7yzIQrBzCE0PPbq0YGI26+8OSfURYbVKHi6V4XGgxHlc3vtnIfBzGA0PLRhUgFI2i+8OOaTxEUV53g7LNmJApAis30zIUrBzCE0PPbrEcGI2698OSfUhcbU5/h6V8YGwxHlMzutXMfBzF/0PLRh0oFI2i98OOaTxIUVpzg7LNmJQpAisz0zYctBzCE0PPbrEgGI268" type="audio/wav">
    </audio>
    
    <audio id="error-audio" preload="auto">
        <source src="data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBjCM0vLTgTAHGGS56+ihUxQJT6Pe77FYGAg+ltryxnMpBSh+zPLaizsIHGrE7+CWRAwYZ7rt66BVEQk9luHxuGkdBTGO0/LSgS8GJ3vH79uRPwsTXrTp7KhUFQlHouDwvmseBjCN0vLTgjAGKX7I8NqLOwgaaL/t559NEAxPqOPwtmMcBjiP1/PMeS0EJHfJ8N2RQAoTXbXq7KlUFQpGoN/wvmwhBjGM0fLTgjEGKH3H8NuQPwoaZ77s6qBUEglCm+DwtWMcBjiO1/PNejAFJHbI8N6SPwsUXLPp7KlUFQpFn97wv24gBjCM0fPUhTEGJnzH8N2SPwoaZrvt655NEQxPquDvtmMcBjiO1/PNezEFJHXH8N+TQAsUW7Lp7KpWFwpEnt3xwHEeBzCM0fPWhDIGJnvH8N+UPwsaZLft651MEwxOp97vtGMcBTiN1vLOfTQFI3TG8OCUQAwUWbDo7axYGApDndzxwnEeBzCL0fPWhTMGJXrG8OCVQAwZYrXs6lsTEwxNo9vvtGMcBTeL1fLPgDkFI3DG8OGXQw0UWK7n7a1aGgpBmtrxxXQhBzCK0PPYiDYGJHnF8OKZRg0ZXrHr6loUFAxModjvtWQdBTiJ1PLPgTsFJG/E8OKTQA4UWKzm7K1bGwpAl9jxxnYjBzCJ0PPZijkGI3fE8OKZSA8aXK7p6VoUFQxLodXvtWUdBjiH0vLPgj4FI27D8OKURA8UVqrm7K5dHApAldXyxnglBzCH0PPaizsGI3bD8OObSBEaWqvm6FoUFgxKn9TvtmYdBjiF0vLPgUAFI23C8OKTRRAUV6jl7K5fHgpAlNPzyHomBzCG0PPajDwGI3XC8OOcShIaWKvl6FsVFgxJnk/vt2geBjGF0vLQgUIFI2vC8OKVSBAUV6bk7K9gHwpAktPzyHwpBzCG0PPbpTwGI3TB8OOcShMbV6nk6FwWFwxJnNDvt2keBzGD0fLQgkMFI2vB8OOXTBEUV6Tj7LBhIApAkNHzyn8pBzCF0PPbpT4GI3LA8OOeTBMbVqjj6FwWGQxIms/vt2seBzGD0PLQg0QFI2rA8OOXTREUV6Li7LFjIgpAjtHzyoEqBzCE0PPcpkAGI3C/8OSfThQbVabj6F0XGQxImM7vt20fBzGC0PLQhEUFI2m/8OOYTREUV5/h7LJkIwpAjdD0zIEqBzCE0PPcqkMGI2+/8OSfTxUbVaPi6F0XGgxIl83vtnEfBzGA0PLRhEYFI2m+8OOZTxEUV57h7LJkJApAjM7yzIQrBzCE0PPbq0YGI26+8OSfURYbVKHi6V4XGgxHlc3vtnIfBzGA0PLRhUgFI2i+8OOaTxEUV53g7LNmJApAis30zIUrBzCE0PPbrEcGI2698OSfUhcbU5/h6V8YGwxHlMzutXMfBzF/0PLRh0oFI2i98OOaTxIUVpzg7LNmJQpAisz0zYctBzCE0PPbrEgGI268" type="audio/wav">
    </audio>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
    <script>
        let cameraInitialized = false; // Flag to prevent re-init
        let currentAction = 'check_in'; // Track current action in JavaScript
        
        document.addEventListener('DOMContentLoaded', function() {
            if (!cameraInitialized) {
                initQRScanner();
            }
        });

        // Switch action without Livewire (pure JavaScript)
        function switchAction(action) {
            currentAction = action;
            
            // Update button styles
            const btnCheckIn = document.getElementById('btn-check-in');
            const btnCheckOut = document.getElementById('btn-check-out');
            const modeText = document.getElementById('action-mode-text');
            
            if (action === 'check_in') {
                btnCheckIn.className = 'px-6 py-3 rounded-lg font-semibold transition-all bg-green-600 text-white';
                btnCheckOut.className = 'px-6 py-3 rounded-lg font-semibold transition-all bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600';
                modeText.textContent = 'CHECK IN (Masuk)';
            } else {
                btnCheckIn.className = 'px-6 py-3 rounded-lg font-semibold transition-all bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600';
                btnCheckOut.className = 'px-6 py-3 rounded-lg font-semibold transition-all bg-blue-600 text-white';
                modeText.textContent = 'CHECK OUT (Pulang)';
            }
            
            console.log('Action switched to:', action);
        }

        let video, canvas, ctx, scanning = false;
        let lastScannedCode = null;
        let lastScanTime = 0;
        const SCAN_COOLDOWN = 3000; // 3 seconds cooldown between scans

        function initQRScanner() {
            if (cameraInitialized) {
                console.log('Camera already initialized');
                return;
            }
            
            video = document.getElementById('qr-video');
            canvas = document.getElementById('qr-canvas');
            ctx = canvas.getContext('2d', { willReadFrequently: true }); // Optimize for frequent reads

            // Request camera access
            navigator.mediaDevices.getUserMedia({ 
                video: { facingMode: 'environment' } 
            })
            .then(function(stream) {
                video.srcObject = stream;
                video.setAttribute('playsinline', true);
                video.play();
                
                cameraInitialized = true; // Mark as initialized
                updateStatus('Camera ready. Scanning...', 'text-green-400');
                requestAnimationFrame(scanQRCode);
            })
            .catch(function(err) {
                console.error('Camera error:', err);
                updateStatus('Camera access denied', 'text-red-400');
                @this.set('errorMessage', 'Tidak dapat mengakses kamera. Pastikan izin kamera diaktifkan.');
            });
        }

        function scanQRCode() {
            if (video.readyState === video.HAVE_ENOUGH_DATA) {
                canvas.height = video.videoHeight;
                canvas.width = video.videoWidth;
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                
                const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                const code = jsQR(imageData.data, imageData.width, imageData.height, {
                    inversionAttempts: 'dontInvert',
                });

                if (code) {
                    handleQRDetected(code.data);
                }
            }
            
            requestAnimationFrame(scanQRCode);
        }

        function handleQRDetected(qrData) {
            const now = Date.now();
            
            // Prevent duplicate scans within cooldown period
            if (lastScannedCode === qrData && (now - lastScanTime) < SCAN_COOLDOWN) {
                return;
            }

            lastScannedCode = qrData;
            lastScanTime = now;

            // Capture photo from video
            const photoCanvas = document.createElement('canvas');
            photoCanvas.width = video.videoWidth;
            photoCanvas.height = video.videoHeight;
            const photoCtx = photoCanvas.getContext('2d');
            photoCtx.drawImage(video, 0, 0);
            const photoBase64 = photoCanvas.toDataURL('image/jpeg', 0.85);

            // Get current action from JavaScript variable
            const action = currentAction;

            // Show loading state
            @this.set('showResult', true);
            @this.set('errorMessage', null);

            // Send to API
            sendScanToAPI(qrData, photoBase64, action);
        }

        function sendScanToAPI(nis, photoBase64, action) {
            fetch('/api/attendance/scan', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    nis: nis,
                    photo_base64: photoBase64,
                    action: action
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displaySuccessResult(data);
                    playSound('success');
                    
                    // Auto hide after 3 seconds
                    setTimeout(() => {
                        @this.call('hideResult');
                    }, 3000);
                } else {
                    displayErrorResult(data);
                    playSound('error');
                }
            })
            .catch(error => {
                console.error('Scan error:', error);
                @this.set('errorMessage', 'Terjadi kesalahan saat memproses scan.');
                playSound('error');
            });
        }

        function displaySuccessResult(data) {
            const resultContent = document.getElementById('scan-result-content');
            const rejectButton = document.getElementById('reject-button');
            
            const student = data.data.student;
            const record = data.data.record;
            const actionText = record.action === 'check_in' ? 'Check In (Masuk)' : 'Check Out (Pulang)';
            const statusClass = getStatusClass(record.status);
            
            resultContent.innerHTML = `
                <div class="text-center">
                    <div class="text-6xl mb-4">✅</div>
                    <h3 class="text-2xl font-bold text-green-600 mb-2">Berhasil!</h3>
                    <div class="bg-gray-100 rounded-lg p-4 mb-4">
                        <p class="text-lg font-semibold">${student.nama}</p>
                        <p class="text-gray-600">NIS: ${student.nis}</p>
                        <p class="text-gray-600">Kelas: ${student.kelas.nama_kelas}</p>
                        <div class="mt-2 inline-block px-3 py-1 rounded-full text-sm font-semibold ${statusClass}">
                            ${record.status.toUpperCase()}
                        </div>
                    </div>
                    <p class="text-sm text-gray-500">${actionText} - ${record.time}</p>
                </div>
            `;
            
            // Show reject button
            if (rejectButton) {
                rejectButton.classList.remove('hidden');
                rejectButton.onclick = () => rejectScan(student.nis);
            }
        }

        function displayErrorResult(data) {
            @this.set('errorMessage', data.message || 'Scan gagal. Silakan coba lagi.');
        }

        function rejectScan(nis) {
            if (!confirm('Yakin ingin REJECT absensi ini?')) {
                return;
            }

            fetch('/api/attendance/reject', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    nis: nis,
                    reason: 'Manual rejection by petugas'
                })
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                @this.call('hideResult');
            });
        }

        function getStatusClass(status) {
            const classes = {
                'hadir': 'bg-green-100 text-green-800',
                'terlambat': 'bg-yellow-100 text-yellow-800',
                'sakit': 'bg-blue-100 text-blue-800',
                'izin': 'bg-purple-100 text-purple-800',
                'alpha': 'bg-red-100 text-red-800'
            };
            return classes[status] || 'bg-gray-100 text-gray-800';
        }

        function playSound(type) {
            const audio = document.getElementById(type + '-audio');
            if (audio) {
                audio.currentTime = 0;
                audio.play().catch(e => console.log('Audio play failed:', e));
            }
        }

        function updateStatus(text, colorClass) {
            const statusEl = document.querySelector('#scanner-status .status-text');
            if (statusEl) {
                statusEl.textContent = text;
                statusEl.className = 'status-text ' + colorClass;
            }
        }
    </script>
    @endpush
</div>
