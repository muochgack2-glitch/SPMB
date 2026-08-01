<x-app-layout>
    <x-slot name="title">Settings - WhatsApp Gateway</x-slot>
    <x-slot name="pageTitle">WA Settings</x-slot>

    <div class="max-w-5xl mx-auto space-y-6">
        {{-- Page Header --}}
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">⚙️ Pengaturan WhatsApp Gateway</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">Konfigurasi koneksi, rate limiting, dan notifikasi</p>
            </div>
            <a href="{{ route('whatsapp.index') }}" 
               class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white">
                <i class="fas fa-arrow-left mr-2"></i>
                Kembali ke Dashboard
            </a>
        </div>

        {{-- Success/Error Messages --}}
        @if(session('success'))
            <x-alert type="success" dismissible>
                {{ session('success') }}
            </x-alert>
        @endif

        @if(session('error'))
            <x-alert type="danger" dismissible>
                {{ session('error') }}
            </x-alert>
        @endif

        @if($errors->any())
            <x-alert type="danger" dismissible>
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-alert>
        @endif

        <form action="{{ route('whatsapp.settings.update') }}" method="POST" class="space-y-6">
            @csrf

            {{-- Connection Settings --}}
            <x-card>
                <div class="flex items-center mb-6">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white text-2xl mr-4">
                        <i class="fas fa-plug"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Koneksi Gateway</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Pengaturan koneksi ke WhatsApp Gateway server</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @php
                        $connectionSettings = $settings['connection'] ?? [];
                        $gatewayUrl = collect($connectionSettings)->firstWhere('key', 'gateway_url');
                        $timeout = collect($connectionSettings)->firstWhere('key', 'gateway_timeout');
                        $retryAttempts = collect($connectionSettings)->firstWhere('key', 'retry_attempts');
                    @endphp

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Gateway URL <span class="text-red-500">*</span>
                        </label>
                        <input type="url" name="gateway_url" 
                               value="{{ old('gateway_url', $gatewayUrl['value'] ?? 'http://localhost:3002') }}"
                               required
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $gatewayUrl['description'] ?? '' }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Timeout (detik) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="gateway_timeout" 
                               value="{{ old('gateway_timeout', $timeout['value'] ?? 10) }}"
                               min="5" max="60" required
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $timeout['description'] ?? '' }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Retry Attempts <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="retry_attempts" 
                               value="{{ old('retry_attempts', $retryAttempts['value'] ?? 3) }}"
                               min="1" max="5" required
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $retryAttempts['description'] ?? '' }}</p>
                    </div>
                </div>
            </x-card>

            {{-- Rate Limiting Settings --}}
            <x-card>
                <div class="flex items-center mb-6">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-yellow-500 to-yellow-600 flex items-center justify-center text-white text-2xl mr-4">
                        <i class="fas fa-tachometer-alt"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Rate Limiting</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Batasi kecepatan pengiriman pesan untuk mencegah spam</p>
                    </div>
                </div>

                <div class="space-y-6">
                    @php
                        $rateLimitSettings = $settings['rate_limiting'] ?? [];
                        $rateLimitEnabled = collect($rateLimitSettings)->firstWhere('key', 'rate_limit_enabled');
                        $messagesPerMinute = collect($rateLimitSettings)->firstWhere('key', 'messages_per_minute');
                        $delayBetween = collect($rateLimitSettings)->firstWhere('key', 'delay_between_messages');
                    @endphp

                    <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                        <div>
                            <label class="font-medium text-gray-900 dark:text-white">Enable Rate Limiting</label>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $rateLimitEnabled['description'] ?? '' }}</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="rate_limit_enabled" value="1" 
                                   {{ old('rate_limit_enabled', $rateLimitEnabled['value'] ?? true) ? 'checked' : '' }}
                                   class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                        </label>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Messages per Minute <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="messages_per_minute" 
                                   value="{{ old('messages_per_minute', $messagesPerMinute['value'] ?? 20) }}"
                                   min="1" max="60" required
                                   class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $messagesPerMinute['description'] ?? '' }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Delay Between Messages (detik) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="delay_between_messages" 
                                   value="{{ old('delay_between_messages', $delayBetween['value'] ?? 3) }}"
                                   min="0" max="30" required
                                   class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $delayBetween['description'] ?? '' }}</p>
                        </div>
                    </div>
                </div>
            </x-card>

            {{-- Feature Toggles --}}
            <x-card>
                <div class="flex items-center mb-6">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-green-500 to-green-600 flex items-center justify-center text-white text-2xl mr-4">
                        <i class="fas fa-toggle-on"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Fitur Notifikasi</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Aktifkan atau nonaktifkan notifikasi otomatis</p>
                    </div>
                </div>

                <div class="space-y-4">
                    @php
                        $featureSettings = $settings['features'] ?? [];
                        $autoSendEnabled = collect($featureSettings)->firstWhere('key', 'auto_send_enabled');
                        $sendOnCheckin = collect($featureSettings)->firstWhere('key', 'send_on_checkin');
                        $sendOnCheckout = collect($featureSettings)->firstWhere('key', 'send_on_checkout');
                        $sendOnAlpha = collect($featureSettings)->firstWhere('key', 'send_on_alpha');
                    @endphp

                    <div class="flex items-center justify-between p-4 bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 border-2 border-blue-200 dark:border-blue-700 rounded-lg">
                        <div>
                            <label class="font-medium text-blue-900 dark:text-blue-100 flex items-center">
                                <i class="fas fa-bolt mr-2"></i>
                                Auto Send Enabled
                            </label>
                            <p class="text-sm text-blue-800 dark:text-blue-200">{{ $autoSendEnabled['description'] ?? '' }}</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="auto_send_enabled" value="1" 
                                   {{ old('auto_send_enabled', $autoSendEnabled['value'] ?? true) ? 'checked' : '' }}
                                   class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                        </label>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                            <div>
                                <label class="font-medium text-gray-900 dark:text-white text-sm">Check-In</label>
                                <p class="text-xs text-gray-600 dark:text-gray-400">{{ $sendOnCheckin['description'] ?? '' }}</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="send_on_checkin" value="1" 
                                       {{ old('send_on_checkin', $sendOnCheckin['value'] ?? true) ? 'checked' : '' }}
                                       class="sr-only peer">
                                <div class="w-9 h-5 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 dark:peer-focus:ring-green-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-600 peer-checked:bg-green-600"></div>
                            </label>
                        </div>

                        <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                            <div>
                                <label class="font-medium text-gray-900 dark:text-white text-sm">Check-Out</label>
                                <p class="text-xs text-gray-600 dark:text-gray-400">{{ $sendOnCheckout['description'] ?? '' }}</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="send_on_checkout" value="1" 
                                       {{ old('send_on_checkout', $sendOnCheckout['value'] ?? true) ? 'checked' : '' }}
                                       class="sr-only peer">
                                <div class="w-9 h-5 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 dark:peer-focus:ring-green-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-600 peer-checked:bg-green-600"></div>
                            </label>
                        </div>

                        <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                            <div>
                                <label class="font-medium text-gray-900 dark:text-white text-sm">Alpha</label>
                                <p class="text-xs text-gray-600 dark:text-gray-400">{{ $sendOnAlpha['description'] ?? '' }}</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="send_on_alpha" value="1" 
                                       {{ old('send_on_alpha', $sendOnAlpha['value'] ?? true) ? 'checked' : '' }}
                                       class="sr-only peer">
                                <div class="w-9 h-5 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 dark:peer-focus:ring-green-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-600 peer-checked:bg-green-600"></div>
                            </label>
                        </div>
                    </div>
                </div>
            </x-card>

            {{-- Message Templates --}}
            <x-card>
                <div class="flex items-center mb-6">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple-500 to-purple-600 flex items-center justify-center text-white text-2xl mr-4">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Template Pesan</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Kustomisasi format pesan notifikasi otomatis</p>
                    </div>
                </div>

                <div class="space-y-4">
                    @php
                        $templateSettings = $settings['templates'] ?? [];
                        $checkinTemplate = collect($templateSettings)->firstWhere('key', 'checkin_message_template');
                        $checkoutTemplate = collect($templateSettings)->firstWhere('key', 'checkout_message_template');
                        $alphaTemplate = collect($templateSettings)->firstWhere('key', 'alpha_message_template');
                    @endphp

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Check-In Message Template
                        </label>
                        <textarea name="checkin_message_template" rows="3"
                                  class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">{{ old('checkin_message_template', $checkinTemplate['value'] ?? '') }}</textarea>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            Variables: {nama}, {nis}, {waktu}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Check-Out Message Template
                        </label>
                        <textarea name="checkout_message_template" rows="3"
                                  class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">{{ old('checkout_message_template', $checkoutTemplate['value'] ?? '') }}</textarea>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            Variables: {nama}, {nis}, {waktu}
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Alpha Message Template
                        </label>
                        <textarea name="alpha_message_template" rows="3"
                                  class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">{{ old('alpha_message_template', $alphaTemplate['value'] ?? '') }}</textarea>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            Variables: {nama}, {nis}, {tanggal}
                        </p>
                    </div>
                </div>
            </x-card>

            {{-- Action Buttons --}}
            <div class="flex justify-between items-center">
                <form action="{{ route('whatsapp.settings.reset') }}" method="POST" 
                      onsubmit="return confirm('Reset semua pengaturan ke default? Perubahan tidak dapat dibatalkan.')">
                    @csrf
                    <button type="submit" 
                            class="inline-flex items-center px-6 py-2 text-sm font-medium rounded-lg transition-all duration-200 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300">
                        <i class="fas fa-undo mr-2"></i>
                        Reset ke Default
                    </button>
                </form>

                <button type="submit" 
                        class="inline-flex items-center px-6 py-2 text-sm font-medium rounded-lg transition-all duration-200 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white shadow-md hover:shadow-lg">
                    <i class="fas fa-save mr-2"></i>
                    Simpan Pengaturan
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
