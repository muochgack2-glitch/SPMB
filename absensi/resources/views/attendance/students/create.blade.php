@php
    $pageTitle = 'Tambah Siswa Baru';
    $breadcrumbs = [
        ['label' => 'Data Siswa', 'url' => route('attendance.students.index')],
        ['label' => 'Tambah Siswa']
    ];
@endphp

<x-app-layout>
    <div class="max-w-4xl mx-auto space-y-6">
        {{-- Page Header --}}
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Tambah Siswa Baru</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">QR Code akan otomatis di-generate setelah siswa disimpan</p>
        </div>

        {{-- Form Card --}}
        <x-card>
            <form method="POST" action="{{ route('attendance.students.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="space-y-6">
                    {{-- NIS --}}
                    <x-input
                        type="text"
                        name="nis"
                        label="NIS"
                        :value="old('nis')"
                        placeholder="Contoh: 24001"
                        required
                        :error="$errors->first('nis')"
                    />

                    {{-- Nama --}}
                    <x-input
                        type="text"
                        name="nama"
                        label="Nama Lengkap"
                        :value="old('nama')"
                        placeholder="Contoh: Budi Santoso"
                        required
                        :error="$errors->first('nama')"
                    />

                    {{-- Kelas --}}
                    <x-select
                        name="kelas_id"
                        label="Kelas"
                        required
                        :error="$errors->first('kelas_id')"
                    >
                        <option value="">Pilih Kelas</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ old('kelas_id') == $class->id ? 'selected' : '' }}>
                                {{ $class->nama_kelas }}{{ $class->jurusan ? ' - ' . $class->jurusan : '' }}
                            </option>
                        @endforeach
                    </x-select>

                    {{-- No HP Orang Tua --}}
                    <x-input
                        type="text"
                        name="no_hp_ortu"
                        label="No HP Orang Tua"
                        :value="old('no_hp_ortu')"
                        placeholder="Contoh: 628123456789"
                        helper="Format: 628XXXXXXXXX (untuk notifikasi WhatsApp)"
                        :error="$errors->first('no_hp_ortu')"
                    />

                    {{-- Foto Profil --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Foto Profil
                        </label>
                        <input
                            type="file"
                            name="foto_profil"
                            accept="image/*"
                            onchange="previewImage(event)"
                            class="block w-full text-sm text-gray-900 dark:text-gray-100 border border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer bg-gray-50 dark:bg-gray-800 focus:outline-none"
                        />
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Max 2MB, format: JPG, PNG, GIF</p>
                        @error('foto_profil')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        
                        {{-- Image Preview --}}
                        <div id="imagePreview" class="mt-4 hidden">
                            <img id="preview" src="" alt="Preview" class="w-32 h-32 rounded-lg object-cover border-2 border-gray-300 dark:border-gray-600">
                        </div>
                    </div>

                    {{-- Status Aktif --}}
                    <div>
                        <label class="flex items-center">
                            <input
                                type="checkbox"
                                name="is_active"
                                value="1"
                                {{ old('is_active', true) ? 'checked' : '' }}
                                class="w-5 h-5 text-primary-600 border-gray-300 dark:border-gray-600 rounded focus:ring-primary-500"
                            />
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Siswa Aktif</span>
                        </label>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Hanya siswa aktif yang bisa melakukan absensi</p>
                    </div>

                    {{-- Info Box --}}
                    <div class="bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500 p-4 rounded">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-info-circle text-blue-500"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-blue-700 dark:text-blue-300">
                                    <strong>Catatan:</strong> QR Code akan otomatis dibuat setelah data siswa disimpan. 
                                    QR Code dapat dilihat dan diunduh dari halaman daftar siswa.
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex justify-end gap-3 pt-4">
                        <a
                            href="{{ route('attendance.students.index') }}"
                            class="inline-flex items-center justify-center px-6 py-2 text-sm font-medium rounded-lg transition-all duration-200 border-2 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                        >
                            Batal
                        </a>
                        <button
                            type="submit"
                            class="inline-flex items-center justify-center px-6 py-2 text-sm font-medium rounded-lg transition-all duration-200 bg-gradient-to-r from-primary-500 to-primary-600 text-white hover:from-primary-600 hover:to-primary-700 shadow-md hover:shadow-lg"
                        >
                            <i class="fas fa-save mr-2"></i>
                            Simpan Siswa
                        </button>
                    </div>
                </div>
            </form>
        </x-card>
    </div>

    @push('scripts')
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
    @endpush
</x-app-layout>
