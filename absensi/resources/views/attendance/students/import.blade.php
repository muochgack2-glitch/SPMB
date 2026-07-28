<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Siswa - Sistem Absensi</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <!-- Header -->
        <nav class="bg-blue-600 text-white shadow-lg">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <div class="flex items-center">
                        <h1 class="text-xl font-bold">📚 Sistem Absensi QR Code</h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('attendance.dashboard') }}" class="hover:bg-blue-700 px-3 py-2 rounded">Dashboard</a>
                        <a href="{{ route('attendance.scanner') }}" class="hover:bg-blue-700 px-3 py-2 rounded">Scanner</a>
                        <a href="{{ route('attendance.students.index') }}" class="bg-blue-800 px-3 py-2 rounded">Siswa</a>
                        <a href="{{ route('attendance.classes.index') }}" class="hover:bg-blue-700 px-3 py-2 rounded">Kelas</a>
                        <a href="{{ route('attendance.reports.daily') }}" class="hover:bg-blue-700 px-3 py-2 rounded">Laporan</a>
                        <a href="{{ route('attendance.settings.index') }}" class="hover:bg-blue-700 px-3 py-2 rounded">Pengaturan</a>
                    </div>
                </div>
            </div>
        </nav>

        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Back Button -->
            <div class="mb-6">
                <a href="{{ route('attendance.students.index') }}" 
                   class="text-blue-600 hover:text-blue-800 inline-flex items-center">
                    ← Kembali ke Daftar Siswa
                </a>
            </div>

            <!-- Flash Messages -->
            @if(session('success'))
            <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded" role="alert">
                <p class="font-bold">✓ Berhasil</p>
                <p>{{ session('success') }}</p>
            </div>
            @endif

            @if(session('error'))
            <div class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded" role="alert">
                <p class="font-bold">✗ Error</p>
                <p>{{ session('error') }}</p>
            </div>
            @endif

            <!-- Page Header -->
            <div class="mb-6">
                <h2 class="text-3xl font-bold text-gray-900">Import Data Siswa dari Excel</h2>
                <p class="text-gray-600 mt-1">Upload file Excel untuk menambahkan banyak siswa sekaligus</p>
            </div>

            <!-- Instructions Card -->
            <div class="bg-blue-50 border-l-4 border-blue-500 p-6 rounded-lg mb-6">
                <h3 class="text-lg font-bold text-blue-900 mb-3">📋 Petunjuk Import</h3>
                <ol class="list-decimal list-inside space-y-2 text-blue-800">
                    <li>Download template Excel dengan klik tombol "Download Template" di bawah</li>
                    <li>Isi data siswa sesuai format template:
                        <ul class="list-disc list-inside ml-6 mt-1 space-y-1 text-sm">
                            <li><strong>NIS:</strong> Nomor Induk Siswa (wajib, unik)</li>
                            <li><strong>Nama:</strong> Nama lengkap siswa (wajib)</li>
                            <li><strong>Kelas ID:</strong> ID kelas dari database (wajib)</li>
                            <li><strong>No HP Ortu:</strong> Format 628XXXXXXXXX (opsional)</li>
                        </ul>
                    </li>
                    <li>Simpan file Excel Anda</li>
                    <li>Upload file melalui form di bawah ini</li>
                    <li>QR Code akan otomatis di-generate untuk semua siswa</li>
                </ol>
            </div>

            <!-- Download Template Card -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-1">1. Download Template Excel</h3>
                        <p class="text-sm text-gray-600">Template sudah berisi contoh data dan format yang benar</p>
                    </div>
                    <a href="{{ route('attendance.students.template') }}" 
                       class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg shadow flex items-center">
                        📥 Download Template
                    </a>
                </div>
            </div>

            <!-- Daftar Kelas Reference -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">📚 Daftar ID Kelas</h3>
                <p class="text-sm text-gray-600 mb-4">Gunakan ID kelas ini saat mengisi kolom "Kelas ID" di Excel:</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach(\App\Models\AttendanceClass::orderBy('tingkat')->orderBy('nama_kelas')->get() as $class)
                    <div class="border border-gray-300 rounded p-3 flex justify-between items-center">
                        <div>
                            <span class="font-semibold">{{ $class->tingkat }} {{ $class->nama_kelas }}</span>
                            @if($class->jurusan)
                            <span class="text-gray-600">- {{ $class->jurusan }}</span>
                            @endif
                        </div>
                        <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded font-mono text-sm">
                            ID: {{ $class->id }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Upload Form Card -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">2. Upload File Excel</h3>
                
                <form method="POST" action="{{ route('attendance.students.import') }}" enctype="multipart/form-data" id="importForm">
                    @csrf

                    <div class="mb-6">
                        <label for="file" class="block text-sm font-medium text-gray-700 mb-2">
                            Pilih File Excel <span class="text-red-500">*</span>
                        </label>
                        <input type="file" 
                               id="file" 
                               name="file" 
                               accept=".xlsx,.xls,.csv"
                               required
                               onchange="displayFileName()"
                               class="w-full px-4 py-2 border @error('file') border-red-500 @else border-gray-300 @enderror rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <p class="mt-1 text-xs text-gray-500">Format: .xlsx, .xls, .csv (Max 5MB)</p>
                        @error('file')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                        
                        <div id="fileNameDisplay" class="mt-2 hidden">
                            <p class="text-sm text-green-600">
                                ✓ File dipilih: <span id="fileName" class="font-semibold"></span>
                            </p>
                        </div>
                    </div>

                    <!-- Warning Box -->
                    <div class="mb-6 bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                ⚠️
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700">
                                    <strong>Perhatian:</strong> Proses import akan:
                                </p>
                                <ul class="list-disc list-inside text-sm text-yellow-700 mt-2 space-y-1">
                                    <li>Memvalidasi semua data sebelum disimpan</li>
                                    <li>Skip baris dengan NIS yang sudah ada</li>
                                    <li>Generate QR Code untuk setiap siswa baru</li>
                                    <li>Proses mungkin memakan waktu untuk data besar</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex justify-end space-x-3">
                        <a href="{{ route('attendance.students.index') }}" 
                           class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                            Batal
                        </a>
                        <button type="submit" 
                                id="submitBtn"
                                class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow">
                            📤 Mulai Import
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tips Card -->
            <div class="mt-6 bg-gray-50 border border-gray-200 rounded-lg p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-3">💡 Tips Import Excel</h3>
                <ul class="space-y-2 text-gray-700 text-sm">
                    <li class="flex items-start">
                        <span class="mr-2">✓</span>
                        <span>Pastikan NIS unik dan tidak ada yang duplikat</span>
                    </li>
                    <li class="flex items-start">
                        <span class="mr-2">✓</span>
                        <span>Gunakan ID kelas yang valid (lihat tabel di atas)</span>
                    </li>
                    <li class="flex items-start">
                        <span class="mr-2">✓</span>
                        <span>Format No HP: 628XXXXXXXXX (tanpa tanda +, -, atau spasi)</span>
                    </li>
                    <li class="flex items-start">
                        <span class="mr-2">✓</span>
                        <span>Hapus baris contoh dari template sebelum import</span>
                    </li>
                    <li class="flex items-start">
                        <span class="mr-2">✓</span>
                        <span>Untuk import besar (100+ siswa), lakukan per batch 50 siswa</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        function displayFileName() {
            const fileInput = document.getElementById('file');
            const fileNameDisplay = document.getElementById('fileNameDisplay');
            const fileName = document.getElementById('fileName');
            
            if (fileInput.files.length > 0) {
                fileName.textContent = fileInput.files[0].name;
                fileNameDisplay.classList.remove('hidden');
            } else {
                fileNameDisplay.classList.add('hidden');
            }
        }

        // Show loading state on submit
        document.getElementById('importForm').addEventListener('submit', function() {
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '⏳ Sedang memproses...';
            submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
        });
    </script>
</body>
</html>
