@php
    $pageTitle = 'Import Data Siswa';
    $breadcrumbs = [
        ['label' => 'Data Siswa', 'url' => route('attendance.students.index')],
        ['label' => 'Import Excel']
    ];
@endphp

<x-app-layout>
    <div class="max-w-5xl mx-auto space-y-6">
        {{-- Page Header --}}
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Import Data Siswa dari Excel</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Upload file Excel untuk menambahkan banyak siswa sekaligus</p>
        </div>

        {{-- Instructions Card --}}
        <x-section-card title="📋 Petunjuk Import">
            <ol class="list-decimal list-inside space-y-3 text-gray-700 dark:text-gray-300">
                <li class="pl-2">
                    <strong>Download template Excel</strong> dengan klik tombol di bawah
                </li>
                <li class="pl-2">
                    <strong>Isi data siswa</strong> sesuai format template:
                    <ul class="list-disc list-inside ml-6 mt-2 space-y-1 text-sm text-gray-600 dark:text-gray-400">
                        <li><strong>NIS:</strong> Nomor Induk Siswa (wajib, unik)</li>
                        <li><strong>Nama:</strong> Nama lengkap siswa (wajib)</li>
                        <li><strong>Kelas ID:</strong> ID kelas dari database (wajib)</li>
                        <li><strong>No HP Ortu:</strong> Format 628XXXXXXXXX (opsional)</li>
                    </ul>
                </li>
                <li class="pl-2"><strong>Simpan file Excel</strong> Anda</li>
                <li class="pl-2"><strong>Upload file</strong> melalui form di bawah ini</li>
                <li class="pl-2"><strong>QR Code akan otomatis</strong> di-generate untuk semua siswa</li>
            </ol>
        </x-section-card>

        {{-- Download Template Card --}}
        <x-card class="bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/10 dark:to-emerald-900/10 border-2 border-green-200 dark:border-green-800">
            <div class="flex items-center justify-between">
                <div class="flex items-start space-x-4">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center shadow-lg">
                            <i class="fas fa-file-excel text-white text-xl"></i>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">
                            1. Download Template Excel
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Template sudah berisi contoh data dan format yang benar
                        </p>
                    </div>
                </div>
                <a
                    href="{{ route('attendance.students.export.template') }}"
                    class="inline-flex items-center justify-center px-6 py-3 text-sm font-semibold rounded-xl transition-all duration-200 bg-gradient-to-r from-green-500 to-emerald-600 text-white hover:from-green-600 hover:to-emerald-700 shadow-lg hover:shadow-xl hover:-translate-y-0.5"
                >
                    <i class="fas fa-download mr-2"></i>
                    Download Template
                </a>
            </div>
        </x-card>

        {{-- Daftar Kelas Reference --}}
        <x-section-card title="📚 Referensi ID Kelas" subtitle="Gunakan ID kelas ini saat mengisi kolom 'Kelas ID' di Excel">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach(\App\Models\AttendanceClass::orderBy('tingkat')->orderBy('nama_kelas')->get() as $class)
                    <div class="flex items-center justify-between p-4 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/10 dark:to-indigo-900/10 border border-blue-200 dark:border-blue-800 rounded-xl hover:shadow-md transition-all duration-200">
                        <div>
                            <p class="font-semibold text-gray-900 dark:text-white">
                                {{ $class->nama_kelas }}
                            </p>
                            @if($class->jurusan)
                                <p class="text-xs text-gray-600 dark:text-gray-400">{{ $class->jurusan }}</p>
                            @endif
                        </div>
                        <span class="inline-flex items-center justify-center px-3 py-1.5 bg-blue-600 text-white text-sm font-mono font-bold rounded-lg shadow-sm">
                            {{ $class->id }}
                        </span>
                    </div>
                @endforeach
            </div>
        </x-section-card>

        {{-- Upload Form Card --}}
        <x-card class="bg-gradient-to-br from-primary-50 to-blue-50 dark:from-primary-900/10 dark:to-blue-900/10 border-2 border-primary-200 dark:border-primary-800">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6 flex items-center">
                <span class="w-8 h-8 bg-gradient-to-br from-primary-500 to-blue-600 rounded-lg flex items-center justify-center text-white font-bold text-sm mr-3">2</span>
                Upload File Excel
            </h3>
            
            <form method="POST" action="{{ route('attendance.students.import') }}" enctype="multipart/form-data" id="importForm">
                @csrf

                <div class="space-y-6">
                    {{-- File Input --}}
                    <div>
                        <label for="file" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Pilih File Excel <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input
                                type="file"
                                id="file"
                                name="file"
                                accept=".xlsx,.xls,.csv"
                                required
                                onchange="displayFileName()"
                                class="block w-full text-sm text-gray-900 dark:text-gray-100 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl cursor-pointer bg-white dark:bg-gray-800 hover:border-primary-400 focus:outline-none focus:border-primary-500 transition-colors p-4"
                            />
                        </div>
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            <i class="fas fa-info-circle mr-1"></i>
                            Format: .xlsx, .xls, .csv (Max 5MB)
                        </p>
                        @error('file')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        
                        <div id="fileNameDisplay" class="mt-3 hidden">
                            <div class="flex items-center p-3 bg-green-100 dark:bg-green-900/30 border-l-4 border-green-500 rounded">
                                <i class="fas fa-check-circle text-green-600 dark:text-green-400 mr-2"></i>
                                <p class="text-sm text-green-700 dark:text-green-300">
                                    File dipilih: <span id="fileName" class="font-semibold"></span>
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Warning Box --}}
                    <div class="bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-500 p-4 rounded-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-triangle text-yellow-600 dark:text-yellow-400"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-semibold text-yellow-800 dark:text-yellow-300 mb-2">
                                    ⚠️ Perhatian: Proses import akan
                                </p>
                                <ul class="list-disc list-inside text-sm text-yellow-700 dark:text-yellow-400 space-y-1">
                                    <li>Memvalidasi semua data sebelum disimpan</li>
                                    <li>Skip baris dengan NIS yang sudah ada</li>
                                    <li>Generate QR Code untuk setiap siswa baru</li>
                                    <li>Proses mungkin memakan waktu untuk data besar</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex justify-end gap-3 pt-4">
                        <a
                            href="{{ route('attendance.students.index') }}"
                            class="inline-flex items-center justify-center px-6 py-3 text-sm font-medium rounded-xl transition-all duration-200 border-2 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                        >
                            Batal
                        </a>
                        <button
                            type="submit"
                            id="submitBtn"
                            class="inline-flex items-center justify-center px-6 py-3 text-sm font-semibold rounded-xl transition-all duration-200 bg-gradient-to-r from-primary-500 to-blue-600 text-white hover:from-primary-600 hover:to-blue-700 shadow-lg hover:shadow-xl hover:-translate-y-0.5"
                        >
                            <i class="fas fa-upload mr-2"></i>
                            Mulai Import
                        </button>
                    </div>
                </div>
            </form>
        </x-card>

        {{-- Tips Card --}}
        <x-card class="bg-gradient-to-br from-purple-50 to-pink-50 dark:from-purple-900/10 dark:to-pink-900/10 border border-purple-200 dark:border-purple-800">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                <i class="fas fa-lightbulb text-yellow-500 mr-2"></i>
                Tips Import Excel
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex items-start space-x-3">
                    <i class="fas fa-check-circle text-green-500 mt-0.5"></i>
                    <span class="text-sm text-gray-700 dark:text-gray-300">Pastikan NIS unik dan tidak ada yang duplikat</span>
                </div>
                <div class="flex items-start space-x-3">
                    <i class="fas fa-check-circle text-green-500 mt-0.5"></i>
                    <span class="text-sm text-gray-700 dark:text-gray-300">Gunakan ID kelas yang valid (lihat tabel di atas)</span>
                </div>
                <div class="flex items-start space-x-3">
                    <i class="fas fa-check-circle text-green-500 mt-0.5"></i>
                    <span class="text-sm text-gray-700 dark:text-gray-300">Format No HP: 628XXXXXXXXX (tanpa +, -, atau spasi)</span>
                </div>
                <div class="flex items-start space-x-3">
                    <i class="fas fa-check-circle text-green-500 mt-0.5"></i>
                    <span class="text-sm text-gray-700 dark:text-gray-300">Hapus baris contoh dari template sebelum import</span>
                </div>
                <div class="flex items-start space-x-3">
                    <i class="fas fa-check-circle text-green-500 mt-0.5"></i>
                    <span class="text-sm text-gray-700 dark:text-gray-300">Import besar (100+ siswa), lakukan per batch 50 siswa</span>
                </div>
            </div>
        </x-card>
    </div>

    @push('scripts')
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
            submitBtn.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Sedang memproses...';
            submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
        });
    </script>
    @endpush
</x-app-layout>
