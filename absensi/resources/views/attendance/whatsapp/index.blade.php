<x-app-layout>
    <x-slot name="title">WhatsApp Gateway</x-slot>
    <x-slot name="pageTitle">WA Gateway</x-slot>

    <div class="max-w-7xl mx-auto space-y-6" x-data="whatsappGateway()">
        {{-- Page Header --}}
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">💬 WhatsApp Gateway</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">Monitor dan kelola notifikasi WhatsApp otomatis</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('whatsapp.settings') }}" 
                   class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200 bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white shadow-md hover:shadow-lg">
                    <i class="fas fa-cog mr-2"></i>
                    Settings
                </a>
                <a href="{{ route('whatsapp.logs') }}" 
                   class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200 bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white shadow-md hover:shadow-lg">
                    <i class="fas fa-history mr-2"></i>
                    Message Logs
                </a>
                <button @click="refreshStatus()" 
                        class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200 border-2 border-blue-300 dark:border-blue-700 text-blue-700 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20">
                    <i class="fas fa-sync-alt mr-2" :class="{ 'animate-spin': loading }"></i>
                    Refresh
                </button>
            </div>
        </div>

        {{-- Gateway Status Card --}}
        <x-card>
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-green-500 to-green-600 flex items-center justify-center text-white text-2xl mr-4">
                        <i class="fab fa-whatsapp"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Status Gateway</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Connection status dan health metrics</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2 px-4 py-2 rounded-lg" 
                         :class="status.connected ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300' : 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300'">
                        <span class="relative flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75"
                                  :class="status.connected ? 'bg-green-500' : 'bg-red-500'"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3"
                                  :class="status.connected ? 'bg-green-600' : 'bg-red-600'"></span>
                        </span>
                        <span class="font-semibold" x-text="status.connected ? 'Connected' : 'Disconnected'"></span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                {{-- Gateway URL --}}
                <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Gateway URL</span>
                        <i class="fas fa-server text-gray-400"></i>
                    </div>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white">localhost:3002</p>
                </div>

                {{-- Uptime --}}
                <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Uptime</span>
                        <i class="fas fa-clock text-gray-400"></i>
                    </div>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white" x-text="formatUptime(health.uptime)"></p>
                </div>

                {{-- Memory Usage --}}
                <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Memory Usage</span>
                        <i class="fas fa-memory text-gray-400"></i>
                    </div>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white">
                        <span x-text="health.memory?.heapUsed || 0"></span> MB / 
                        <span x-text="health.memory?.heapTotal || 0"></span> MB
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- QR Code Section --}}
                <div class="p-4 bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 border-2 border-green-200 dark:border-green-700 rounded-lg">
                    <h4 class="font-semibold text-green-900 dark:text-green-300 mb-3 flex items-center">
                        <i class="fas fa-qrcode mr-2"></i>
                        QR Code Login
                    </h4>
                    <template x-if="!status.connected">
                        <div class="space-y-3">
                            <p class="text-sm text-green-800 dark:text-green-200">Scan QR code ini dengan WhatsApp untuk menghubungkan gateway</p>
                            <button @click="getQRCode()" 
                                    :disabled="loadingQR"
                                    class="w-full px-4 py-2 bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white rounded-lg transition-all duration-200 font-medium">
                                <i class="fas" :class="loadingQR ? 'fa-spinner fa-spin' : 'fa-qrcode'"></i>
                                <span class="ml-2" x-text="loadingQR ? 'Loading...' : 'Lihat QR Code'"></span>
                            </button>
                        </div>
                    </template>
                    <template x-if="status.connected">
                        <div class="flex items-center text-green-800 dark:text-green-200">
                            <i class="fas fa-check-circle text-2xl mr-3"></i>
                            <span>WhatsApp sudah terhubung!</span>
                        </div>
                    </template>
                </div>

                {{-- Actions Section --}}
                <div class="p-4 bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 border-2 border-blue-200 dark:border-blue-700 rounded-lg">
                    <h4 class="font-semibold text-blue-900 dark:text-blue-300 mb-3 flex items-center">
                        <i class="fas fa-tools mr-2"></i>
                        Gateway Control
                    </h4>
                    <div class="space-y-2">
                        {{-- PM2 Start/Stop Section --}}
                        <div>
                            {{-- Loading State --}}
                            <template x-if="processStatus.checking">
                                <div class="w-full px-4 py-2 bg-gray-400 text-white rounded-lg text-center text-sm">
                                    <i class="fas fa-spinner fa-spin mr-2"></i>
                                    Checking PM2 status...
                                </div>
                            </template>

                            {{-- PM2 Not Available --}}
                            <template x-if="!processStatus.checking && !processStatus.pm2Available">
                                <div class="p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-300 dark:border-yellow-700 rounded-lg">
                                    <p class="text-sm text-yellow-800 dark:text-yellow-300 mb-2">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>
                                        PM2 not installed
                                    </p>
                                    <p class="text-xs text-yellow-700 dark:text-yellow-400 mb-2">
                                        Install PM2 for automatic start/stop: <code class="bg-yellow-200 dark:bg-yellow-800 px-1 rounded">npm install -g pm2</code>
                                    </p>
                                    <p class="text-xs text-yellow-700 dark:text-yellow-400">
                                        Or start manually: <code class="bg-yellow-200 dark:bg-yellow-800 px-1 rounded">cd ../whatsapp-server && node server.js</code>
                                    </p>
                                </div>
                            </template>
                            
                            {{-- PM2 Available - Show Start/Stop Buttons --}}
                            <template x-if="!processStatus.checking && processStatus.pm2Available">
                                <div>
                                    {{-- Start Button - show when PM2 not running --}}
                                    <button @click="startGateway()" 
                                    x-show="!processStatus.running"
                                    class="w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-all duration-200 font-medium text-sm">
                                        <i class="fas fa-play mr-2"></i>
                                        Start Gateway Server (PM2)
                                    </button>
                                    
                                    {{-- Stop Button - show when PM2 is running --}}
                                    <button @click="stopGateway()" 
                                    x-show="processStatus.running"
                                    class="w-full px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg transition-all duration-200 font-medium text-sm">
                                        <i class="fas fa-stop mr-2"></i>
                                        Stop Gateway Server (PM2)
                                    </button>
                                </div>
                            </template>
                        </div>

                        {{-- Separator --}}
                        <div class="border-t border-blue-300 dark:border-blue-700 my-3"></div>

                        {{-- Logout & Restart (always visible) --}}
                        <button @click="logout()" 
                                :disabled="!status.connected"
                                class="w-full px-4 py-2 bg-yellow-600 hover:bg-yellow-700 disabled:bg-gray-400 text-white rounded-lg transition-all duration-200 font-medium text-sm">
                            <i class="fas fa-sign-out-alt mr-2"></i>
                            Logout & Reset QR
                        </button>
                        <button @click="restart()" 
                                class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-all duration-200 font-medium text-sm">
                            <i class="fas fa-redo mr-2"></i>
                            Restart Gateway
                        </button>
                    </div>
                </div>
            </div>
        </x-card>

        {{-- Quick Send Message --}}
        <x-card>
            <div class="flex items-center mb-6">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white text-2xl mr-4">
                    <i class="fas fa-paper-plane"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">Kirim Pesan Manual</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Test pengiriman pesan WhatsApp</p>
                </div>
            </div>

            <form action="{{ route('whatsapp.send.submit') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input
                        type="text"
                        name="phone"
                        label="Nomor WhatsApp"
                        placeholder="628123456789"
                        pattern="^628[0-9]{9,12}$"
                        helper="Format: 628XXXXXXXXX"
                        required
                    />
                    <div></div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Pesan <span class="text-red-500">*</span>
                    </label>
                    <textarea 
                        name="message" 
                        rows="4" 
                        maxlength="1000"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
                        placeholder="Tulis pesan disini..."
                        required
                    ></textarea>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Maksimal 1000 karakter</p>
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                            :disabled="!status.connected"
                            class="inline-flex items-center px-6 py-2 text-sm font-medium rounded-lg transition-all duration-200 bg-gradient-to-r from-blue-500 to-blue-600 text-white hover:from-blue-600 hover:to-blue-700 disabled:from-gray-400 disabled:to-gray-500 shadow-md hover:shadow-lg">
                        <i class="fas fa-paper-plane mr-2"></i>
                        Kirim Pesan
                    </button>
                </div>
            </form>
        </x-card>

        {{-- Info Box --}}
        <x-card>
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple-500 to-purple-600 flex items-center justify-center text-white text-xl">
                        <i class="fas fa-info-circle"></i>
                    </div>
                </div>
                <div class="ml-4 flex-1">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">ℹ️ Informasi Gateway</h3>
                    <ul class="space-y-2 text-sm text-gray-700 dark:text-gray-300">
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-0.5 mr-3"></i>
                            <span><strong>Notifikasi otomatis</strong> akan dikirim ke orang tua saat siswa check-in/check-out</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-0.5 mr-3"></i>
                            <span><strong>Gateway harus running</strong> di http://localhost:3002 agar notifikasi berfungsi</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-0.5 mr-3"></i>
                            <span><strong>QR Code</strong> hanya perlu di-scan sekali, session akan tersimpan</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-0.5 mr-3"></i>
                            <span><strong>Logout & Reset</strong> akan menghapus session dan generate QR code baru</span>
                        </li>
                    </ul>
                </div>
            </div>
        </x-card>

        {{-- QR Code Modal --}}
        <div x-show="showQRModal" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto"
             style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-900 bg-opacity-75" @click="showQRModal = false"></div>

                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Scan QR Code</h3>
                            <button @click="showQRModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>

                        <div class="text-center">
                            <template x-if="qrCode">
                                <div>
                                    <img :src="qrCode" alt="QR Code" class="mx-auto rounded-lg shadow-lg" style="max-width: 300px;">
                                    <p class="mt-4 text-sm text-gray-600 dark:text-gray-400">
                                        Buka WhatsApp → Linked Devices → Link a Device
                                    </p>
                                </div>
                            </template>
                            <template x-if="!qrCode && qrError">
                                <div class="p-4 bg-red-50 dark:bg-red-900/20 rounded-lg">
                                    <i class="fas fa-exclamation-circle text-red-500 text-3xl mb-2"></i>
                                    <p class="text-red-700 dark:text-red-300" x-text="qrError"></p>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function whatsappGateway() {
            return {
                status: {
                    connected: false,
                    message: ''
                },
                health: {},
                processStatus: {
                    running: false,
                    checking: true,
                    pm2Available: true,
                    errorMsg: ''
                },
                loading: false,
                loadingQR: false,
                showQRModal: false,
                qrCode: null,
                qrError: null,

                init() {
                    this.refreshStatus();
                    // Auto refresh every 30 seconds
                    setInterval(() => this.refreshStatus(), 30000);
                },

                async refreshStatus() {
                    this.loading = true;
                    try {
                        const [statusRes, healthRes, processRes] = await Promise.all([
                            fetch('/whatsapp/status'),
                            fetch('/whatsapp/health'),
                            fetch('/whatsapp/gateway/process-status')
                        ]);

                        if (statusRes.ok) {
                            this.status = await statusRes.json();
                        }
                        if (healthRes.ok) {
                            this.health = await healthRes.json();
                        }
                        if (processRes.ok) {
                            const data = await processRes.json();
                            if (data.status === 'pm2_not_installed') {
                                this.processStatus.pm2Available = false;
                                this.processStatus.errorMsg = data.message;
                            } else {
                                this.processStatus.running = data.running || false;
                            }
                            this.processStatus.checking = false;
                        }
                    } catch (error) {
                        console.error('Failed to refresh status:', error);
                        this.processStatus.checking = false;
                    } finally {
                        this.loading = false;
                    }
                },

                async getQRCode() {
                    this.loadingQR = true;
                    this.qrError = null;
                    try {
                        const response = await fetch('/whatsapp/qr');
                        const data = await response.json();

                        if (data.success && data.qr) {
                            this.qrCode = data.qr;
                            this.showQRModal = true;
                        } else {
                            this.qrError = data.message || 'QR Code tidak tersedia';
                            this.showQRModal = true;
                        }
                    } catch (error) {
                        this.qrError = 'Gagal mengambil QR Code';
                        this.showQRModal = true;
                    } finally {
                        this.loadingQR = false;
                    }
                },

                async logout() {
                    if (!confirm('Logout akan menghapus session WhatsApp. Anda perlu scan QR code lagi. Lanjutkan?')) {
                        return;
                    }

                    try {
                        const response = await fetch('/whatsapp/logout', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });
                        const data = await response.json();

                        if (data.success) {
                            alert('Logout berhasil! QR Code baru sedang digenerate. Tunggu 5-10 detik lalu klik "Lihat QR Code".');
                            await this.refreshStatus();
                        } else {
                            alert('Gagal logout: ' + data.message);
                        }
                    } catch (error) {
                        alert('Error: ' + error.message);
                    }
                },

                async restart() {
                    if (!confirm('Restart gateway server? Ini akan memutus koneksi sementara.')) {
                        return;
                    }

                    try {
                        const response = await fetch('/whatsapp/restart', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });
                        const data = await response.json();

                        if (data.success) {
                            alert('Gateway sedang direstart... Tunggu 10 detik lalu refresh status.');
                        } else {
                            alert('Gagal restart: ' + data.message);
                        }
                    } catch (error) {
                        alert('Error: ' + error.message);
                    }
                },

                async startGateway() {
                    if (!confirm('Start WhatsApp Gateway server dengan PM2?')) {
                        return;
                    }

                    try {
                        const response = await fetch('/whatsapp/gateway/start', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        if (!response.ok) throw new Error('HTTP ' + response.status);
                        const data = await response.json();

                        alert(data.message);
                        if (data.success) {
                            this.processStatus.running = true;
                            setTimeout(() => this.refreshStatus(), 5000);
                        }
                    } catch (error) {
                        alert('Error: ' + error.message);
                    }
                },

                async stopGateway() {
                    if (!confirm('Stop WhatsApp Gateway server?')) {
                        return;
                    }

                    try {
                        const response = await fetch('/whatsapp/gateway/stop', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        if (!response.ok) throw new Error('HTTP ' + response.status);
                        const data = await response.json();

                        alert(data.message);
                        if (data.success) {
                            this.processStatus.running = false;
                            this.refreshStatus();
                        }
                    } catch (error) {
                        alert('Error: ' + error.message);
                    }
                },

                formatUptime(seconds) {
                    if (!seconds) return '0s';
                    const hours = Math.floor(seconds / 3600);
                    const minutes = Math.floor((seconds % 3600) / 60);
                    return `${hours}h ${minutes}m`;
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
