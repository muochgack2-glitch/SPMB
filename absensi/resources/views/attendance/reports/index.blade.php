<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Laporan - Sistem Absensi</title>
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
                        <a href="{{ route('attendance.reports.daily') }}" class="text-blue-600 font-semibold">Laporan</a>
                        <a href="{{ route('attendance.settings.index') }}" class="text-gray-600 hover:text-gray-900">Pengaturan</a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Quick Links -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <a href="{{ route('attendance.reports.daily') }}" class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
                    <div class="text-3xl mb-2">📅</div>
                    <h3 class="font-semibold text-gray-800">Laporan Harian</h3>
                    <p class="text-sm text-gray-600 mt-1">Absensi hari ini</p>
                </a>

                <a href="{{ route('attendance.reports.monthly') }}" class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
                    <div class="text-3xl mb-2">📆</div>
                    <h3 class="font-semibold text-gray-800">Laporan Bulanan</h3>
                    <p class="text-sm text-gray-600 mt-1">Rekapitulasi per bulan</p>
                </a>

                <a href="#custom" class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
                    <div class="text-3xl mb-2">🔍</div>
                    <h3 class="font-semibold text-gray-800">Laporan Custom</h3>
                    <p class="text-sm text-gray-600 mt-1">Filter kustom</p>
                </a>

                <a href="{{ route('attendance.reports.export-summary') }}" class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition"
                   onclick="return confirm('Export ringkasan absensi bulan ini?')">
                    <div class="text-3xl mb-2">📥</div>
                    <h3 class="font-semibold text-gray-800">Export Excel</h3>
                    <p class="text-sm text-gray-600 mt-1">Download ringkasan</p>
                </a>
            </div>

            <!-- Generate Report Form -->
            <div class="bg-white shadow-lg rounded-lg p-6" id="custom">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">Generate Laporan Custom</h2>

                <form action="{{ route('attendance.reports.generate') }}" method="POST">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Start Date -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Tanggal Mulai <span class="text-red-500">*</span>
                            </label>
                            <input type="date" 
                                   name="start_date" 
                                   value="{{ old('start_date', date('Y-m-01')) }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   required>
                            @error('start_date')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- End Date -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Tanggal Akhir <span class="text-red-500">*</span>
                            </label>
                            <input type="date" 
                                   name="end_date" 
                                   value="{{ old('end_date', date('Y-m-d')) }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   required>
                            @error('end_date')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Class Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Filter Kelas
                            </label>
                            <select name="class_id" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">Semua Kelas</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                        {{ $class->nama_kelas }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Status Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Filter Status
                            </label>
                            <select name="status" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">Semua Status</option>
                                <option value="hadir" {{ old('status') == 'hadir' ? 'selected' : '' }}>✅ Hadir</option>
                                <option value="terlambat" {{ old('status') == 'terlambat' ? 'selected' : '' }}>⏰ Terlambat</option>
                                <option value="sakit" {{ old('status') == 'sakit' ? 'selected' : '' }}>🤒 Sakit</option>
                                <option value="izin" {{ old('status') == 'izin' ? 'selected' : '' }}>📝 Izin</option>
                                <option value="alpha" {{ old('status') == 'alpha' ? 'selected' : '' }}>❌ Alpha</option>
                            </select>
                        </div>

                        <!-- Format -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Format Output <span class="text-red-500">*</span>
                            </label>
                            <select name="format" 
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    required>
                                <option value="preview" {{ old('format') == 'preview' ? 'selected' : '' }}>👁️ Preview (di layar)</option>
                                <option value="excel" {{ old('format') == 'excel' ? 'selected' : '' }}>📥 Excel (.xlsx)</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-4">
                        <a href="{{ route('attendance.dashboard') }}" 
                           class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                            Batal
                        </a>
                        <button type="submit" 
                                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                            🔍 Generate Laporan
                        </button>
                    </div>
                </form>
            </div>

            <!-- Instructions -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mt-6">
                <h3 class="font-semibold text-blue-900 mb-3">💡 Panduan Penggunaan</h3>
                <ul class="list-disc list-inside text-blue-800 space-y-2">
                    <li><strong>Laporan Harian:</strong> Lihat absensi hari ini secara real-time</li>
                    <li><strong>Laporan Bulanan:</strong> Rekapitulasi absensi per siswa dalam 1 bulan</li>
                    <li><strong>Laporan Custom:</strong> Filter berdasarkan tanggal, kelas, dan status tertentu</li>
                    <li><strong>Export Excel:</strong> Download data untuk analisis lebih lanjut</li>
                    <li><strong>Preview:</strong> Tampilkan data di layar sebelum export</li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>
