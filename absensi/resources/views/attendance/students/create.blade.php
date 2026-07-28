<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Siswa - Sistem Absensi</title>
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

            <!-- Page Header -->
            <div class="mb-6">
                <h2 class="text-3xl font-bold text-gray-900">Tambah Siswa Baru</h2>
                <p class="text-gray-600 mt-1">QR Code akan otomatis di-generate setelah siswa disimpan</p>
            </div>

            <!-- Form Card -->
            <div class="bg-white rounded-lg shadow-md p-8">
                <form method="POST" action="{{ route('attendance.students.store') }}" enctype="multipart/form-data">
                    @csrf

                    <!-- NIS -->
                    <div class="mb-6">
                        <label for="nis" class="block text-sm font-medium text-gray-700 mb-2">
                            NIS <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="nis" 
                               name="nis" 
                               value="{{ old('nis') }}"
                               required
                               class="w-full px-4 py-2 border @error('nis') border-red-500 @else border-gray-300 @enderror rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Contoh: 24001">
                        @error('nis')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Nama -->
                    <div class="mb-6">
                        <label for="nama" class="block text-sm font-medium text-gray-700 mb-2">
                            Nama Lengkap <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="nama" 
                               name="nama" 
                               value="{{ old('nama') }}"
                               required
                               class="w-full px-4 py-2 border @error('nama') border-red-500 @else border-gray-300 @enderror rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Contoh: Budi Santoso">
                        @error('nama')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Kelas -->
                    <div class="mb-6">
                        <label for="kelas_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Kelas <span class="text-red-500">*</span>
                        </label>
                        <select id="kelas_id" 
                                name="kelas_id" 
                                required
                                class="w-full px-4 py-2 border @error('kelas_id') border-red-500 @else border-gray-300 @enderror rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Pilih Kelas</option>
                            @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ old('kelas_id') == $class->id ? 'selected' : '' }}>
                                {{ $class->tingkat }} {{ $class->nama_kelas }} {{ $class->jurusan ? '- ' . $class->jurusan : '' }}
                            </option>
                            @endforeach
                        </select>
                        @error('kelas_id')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- No HP Orang Tua -->
                    <div class="mb-6">
                        <label for="no_hp_ortu" class="block text-sm font-medium text-gray-700 mb-2">
                            No HP Orang Tua
                        </label>
                        <input type="text" 
                               id="no_hp_ortu" 
                               name="no_hp_ortu" 
                               value="{{ old('no_hp_ortu') }}"
                               class="w-full px-4 py-2 border @error('no_hp_ortu') border-red-500 @else border-gray-300 @enderror rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Contoh: 628123456789">
                        <p class="mt-1 text-xs text-gray-500">Format: 628XXXXXXXXX (untuk notifikasi WhatsApp)</p>
                        @error('no_hp_ortu')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Foto Profil -->
                    <div class="mb-6">
                        <label for="foto_profil" class="block text-sm font-medium text-gray-700 mb-2">
                            Foto Profil
                        </label>
                        <input type="file" 
                               id="foto_profil" 
                               name="foto_profil" 
                               accept="image/*"
                               onchange="previewImage(event)"
                               class="w-full px-4 py-2 border @error('foto_profil') border-red-500 @else border-gray-300 @enderror rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <p class="mt-1 text-xs text-gray-500">Max 2MB, format: JPG, PNG, GIF</p>
                        @error('foto_profil')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                        
                        <!-- Image Preview -->
                        <div id="imagePreview" class="mt-4 hidden">
                            <img id="preview" src="" alt="Preview" class="w-32 h-32 rounded-lg object-cover border-2 border-gray-300">
                        </div>
                    </div>

                    <!-- Status Aktif -->
                    <div class="mb-6">
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   name="is_active" 
                                   value="1" 
                                   {{ old('is_active', true) ? 'checked' : '' }}
                                   class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700">Siswa Aktif</span>
                        </label>
                        <p class="mt-1 text-xs text-gray-500">Hanya siswa aktif yang bisa melakukan absensi</p>
                    </div>

                    <!-- Info Box -->
                    <div class="mb-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                ℹ️
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-blue-700">
                                    <strong>Catatan:</strong> QR Code akan otomatis dibuat setelah data siswa disimpan. 
                                    QR Code dapat dilihat dan diunduh dari halaman daftar siswa.
                                </p>
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
                                class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow">
                            💾 Simpan Siswa
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function previewImage(event) {
            const preview = document.getElementById('preview');
            const previewContainer = document.getElementById('imagePreview');
            const file = event.target.files[0];
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    previewContainer.classList.remove('hidden');
                }
                reader.readAsDataURL(file);
            } else {
                previewContainer.classList.add('hidden');
            }
        }
    </script>
</body>
</html>
