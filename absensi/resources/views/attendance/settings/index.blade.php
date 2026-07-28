<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan - Sistem Absensi</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Navigation -->
        <nav class="bg-white shadow-lg">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <h1 class="text-xl font-bold text-gray-800">📊 Sistem Absensi QR</h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('attendance.dashboard') }}" class="text-gray-600 hover:text-gray-900">Dashboard</a>
                        <a href="{{ route('attendance.students.index') }}" class="text-gray-600 hover:text-gray-900">Siswa</a>
                        <a href="{{ route('attendance.reports.daily') }}" class="text-gray-600 hover:text-gray-900">Laporan</a>
                        <a href="{{ route('attendance.settings.index') }}" class="text-blue-600 font-semibold">Pengaturan</a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="max-w-5xl mx-auto py-6 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    <strong class="font-bold">✅ Berhasil!</strong>
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                    <strong class="font-bold">❌ Error!</strong>
                    <ul class="list-disc list-inside mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Header -->
            <div class="bg-white shadow-lg rounded-lg p-6 mb-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">⚙️ Pengaturan Sistem</h2>
                        <p class="text-gray-600 mt-1">Konfigurasi waktu absensi dan notifikasi</p>
                    </div>
                    <form action="{{ route('attendance.settings.reset') }}" method="POST" 
                          onsubmit="return confirm('Reset semua pengaturan ke default? Tindakan ini tidak dapat dibatalkan.')">
                        @csrf
                        <button type="submit" 
                                class="px-4 py-2 border border-red-300 rounded-lg text-red-700 hover:bg-red-50 transition">
                            🔄 Reset ke Default
                        </button>
                    </form>
                </div>
            </div>

            <form action="{{ route('attendance.settings.update') }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Schedule Settings -->
                <div class="bg-white shadow-lg rounded-lg p-6 mb-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                        <span class="text-2xl mr-2">🕐</span>
                        Pengaturan Waktu
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Check In Time -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Jam Masuk <span class="text-red-500">*</span>
                            </label>
                            <input type="time" 
                                   name="settings[check_in_time]" 
                                   value="{{ old('settings.check_in_time', $settings['schedule']['check_in_time'] ?? '07:00') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   required>
                            <p class="text-xs text-gray-500 mt-1">Jam mulai absensi masuk</p>
                        </div>

                        <!-- Check Out Time -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Jam Pulang <span class="text-red-500">*</span>
                            </label>
                            <input type="time" 
                                   name="settings[check_out_time]" 
                                   value="{{ old('settings.check_out_time', $settings['schedule']['check_out_time'] ?? '15:00') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   required>
                            <p class="text-xs text-gray-500 mt-1">Jam mulai absensi pulang</p>
                        </div>

                        <!-- Tolerance Minutes -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Toleransi Keterlambatan (menit) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" 
                                   name="settings[tolerance_minutes]" 
                                   value="{{ old('settings.tolerance_minutes', $settings['schedule']['tolerance_minutes'] ?? '15') }}"
                                   min="0" 
                                   max="60"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   required>
                            <p class="text-xs text-gray-500 mt-1">Siswa dianggap terlambat jika melewati toleransi ini</p>
                        </div>

                        <!-- Cutoff Time -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Batas Waktu Alpha <span class="text-red-500">*</span>
                            </label>
                            <input type="time" 
                                   name="settings[cutoff_time]" 
                                   value="{{ old('settings.cutoff_time', $settings['schedule']['cutoff_time'] ?? '09:00') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   required>
                            <p class="text-xs text-gray-500 mt-1">Siswa otomatis alpha jika belum absen sampai jam ini</p>
                        </div>
                    </div>

                    <!-- Example Timeline -->
                    <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <h4 class="font-semibold text-blue-900 mb-2">💡 Contoh Timeline:</h4>
                        <div class="text-sm text-blue-800 space-y-1">
                            <p>• <strong id="example-on-time">07:00 - 07:15</strong>: Siswa dianggap <span class="text-green-600 font-semibold">✅ Hadir</span></p>
                            <p>• <strong id="example-late">07:16 - 09:00</strong>: Siswa dianggap <span class="text-yellow-600 font-semibold">⏰ Terlambat</span></p>
                            <p>• <strong id="example-alpha">Setelah 09:00</strong>: Siswa otomatis <span class="text-red-600 font-semibold">❌ Alpha</span></p>
                        </div>
                    </div>
                </div>

                <!-- Notification Settings -->
                <div class="bg-white shadow-lg rounded-lg p-6 mb-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                        <span class="text-2xl mr-2">📱</span>
                        Pengaturan Notifikasi WhatsApp
                    </h3>

                    <div class="space-y-6">
                        <!-- Enable Parent Notification -->
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div>
                                <label class="text-sm font-medium text-gray-700">
                                    Kirim Notifikasi ke Orang Tua
                                </label>
                                <p class="text-xs text-gray-500 mt-1">Aktifkan notifikasi WhatsApp otomatis saat siswa absen</p>
                            </div>
                            <div class="flex items-center space-x-3">
                                <input type="hidden" name="settings[enable_parent_notification]" value="0">
                                <input type="checkbox" 
                                       id="enable_notification"
                                       name="settings[enable_parent_notification]" 
                                       value="1"
                                       {{ old('settings.enable_parent_notification', $settings['notification']['enable_parent_notification'] ?? '1') == '1' ? 'checked' : '' }}
                                       class="w-12 h-6 rounded-full appearance-none bg-gray-300 relative cursor-pointer transition-colors checked:bg-green-500">
                            </div>
                        </div>

                        <!-- Include Photo in Notification -->
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div>
                                <label class="text-sm font-medium text-gray-700">
                                    Sertakan Foto dalam Notifikasi
                                </label>
                                <p class="text-xs text-gray-500 mt-1">Kirim foto absensi bersama dengan pesan WhatsApp</p>
                            </div>
                            <div class="flex items-center space-x-3">
                                <input type="hidden" name="settings[include_photo_in_notification]" value="0">
                                <input type="checkbox" 
                                       id="include_photo"
                                       name="settings[include_photo_in_notification]" 
                                       value="1"
                                       {{ old('settings.include_photo_in_notification', $settings['notification']['include_photo_in_notification'] ?? '0') == '1' ? 'checked' : '' }}
                                       class="w-12 h-6 rounded-full appearance-none bg-gray-300 relative cursor-pointer transition-colors checked:bg-green-500">
                            </div>
                        </div>
                    </div>

                    <!-- Test Notification -->
                    <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <h4 class="font-semibold text-yellow-900 mb-3">🧪 Test Notifikasi</h4>
                        <form action="{{ route('attendance.settings.test-notification') }}" method="POST" class="flex gap-3">
                            @csrf
                            <input type="text" 
                                   name="phone" 
                                   placeholder="628123456789"
                                   pattern="^628[0-9]{9,12}$"
                                   class="flex-1 px-4 py-2 border border-yellow-300 rounded-lg focus:ring-2 focus:ring-yellow-500"
                                   required>
                            <button type="submit" 
                                    class="px-6 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition">
                                📤 Kirim Test
                            </button>
                        </form>
                        <p class="text-xs text-yellow-800 mt-2">
                            Pastikan WhatsApp Gateway sudah berjalan di http://localhost:3001
                        </p>
                    </div>
                </div>

                <!-- General Settings -->
                <div class="bg-white shadow-lg rounded-lg p-6 mb-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                        <span class="text-2xl mr-2">🏫</span>
                        Informasi Umum
                    </h3>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Nama Sekolah <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               name="settings[school_name]" 
                               value="{{ old('settings.school_name', $settings['general']['school_name'] ?? 'SMK Negeri 1') }}"
                               maxlength="100"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               required>
                        <p class="text-xs text-gray-500 mt-1">Nama ini akan muncul di notifikasi WhatsApp</p>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end space-x-4">
                    <a href="{{ route('attendance.dashboard') }}" 
                       class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                        Batal
                    </a>
                    <button type="submit" 
                            class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold">
                        💾 Simpan Pengaturan
                    </button>
                </div>
            </form>

            <!-- Info Box -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mt-6">
                <h3 class="font-semibold text-blue-900 mb-3">ℹ️ Informasi Penting</h3>
                <ul class="list-disc list-inside text-blue-800 space-y-2 text-sm">
                    <li><strong>Pengaturan waktu</strong> akan langsung berlaku untuk absensi hari berikutnya</li>
                    <li><strong>Notifikasi WhatsApp</strong> memerlukan WhatsApp Gateway yang berjalan</li>
                    <li><strong>Foto dalam notifikasi</strong> akan menambah ukuran pesan dan waktu pengiriman</li>
                    <li><strong>Reset ke default</strong> akan mengembalikan semua pengaturan seperti instalasi awal</li>
                </ul>
            </div>
        </div>
    </div>

    <style>
        /* Custom toggle switch */
        input[type="checkbox"] {
            position: relative;
        }
        input[type="checkbox"]::before {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            top: 50%;
            left: 3px;
            transform: translateY(-50%);
            background-color: white;
            transition: left 0.3s;
        }
        input[type="checkbox"]:checked::before {
            left: calc(100% - 23px);
        }
    </style>
</body>
</html>
