@php
    $pageTitle = 'Edit Data Siswa';
    $breadcrumbs = [
        ['label' => 'Data Siswa', 'url' => route('attendance.students.index')],
        ['label' => 'Edit Siswa']
    ];
@endphp

<x-app-layout>
    <div class="max-w-4xl mx-auto space-y-6">
        {{-- Page Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Data Siswa</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">{{ $student->nama }} ({{ $student->nis }})</p>
            </div>
            
            {{-- QR Code Quick Actions --}}
            @if($student->qr_code_path)
                <div class="flex gap-2">
                    <a
                        href="{{ route('attendance.qr.show', $student->nis) }}"
                        target="_blank"
                        class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200 bg-gradient-to-r from-green-500 to-emerald-600 text-white hover:from-green-600 hover:to-emerald-700 shadow-md hover:shadow-lg"
                    >
                        <i class="fas fa-qrcode mr-2"></i>
                        Lihat QR
                    </a>
                </div>
            @endif
        </div>

        {{-- Form Card --}}
        <x-card>
            <form method="POST" action="{{ route('attendance.students.update', $student->id) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="space-y-6">
                    {{-- Current Photo Display --}}
                    @if($student->foto_profil)
                        <div class="flex items-center space-x-4 p-4 bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900 rounded-xl">
                            <img
                                src="{{ asset('storage/' . $student->foto_profil) }}"
                                alt="{{ $student->nama }}"
                                class="w-20 h-20 rounded-xl object-cover ring-4 ring-white dark:ring-gray-700 shadow-lg"
                            />
                            <div>
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Foto Profil Saat Ini</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Upload foto baru untuk mengganti</p>
                            </div>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- NIS --}}
                        <x-input
                            type="text"
                            name="nis"
                            label="NIS"
                            :value="old('nis', $student->nis)"
                            placeholder="Contoh: 24001"
                            required
                            :error="$errors->first('nis')"
                        />

                        {{-- Nama --}}
                        <x-input
                            type="text"
                            name="nama"
                            label="Nama Lengkap"
                            :value="old('nama', $student->nama)"
                            placeholder="Contoh: Budi Santoso"
                            required
                            :error="$errors->first('nama')"
                        />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Kelas --}}
                        <x-select
                            name="kelas_id"
                            label="Kelas"
                            required
                            :error="$errors->first('kelas_id')"
                        >
                            <option value="">Pilih Kelas</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ old('kelas_id', $student->kelas_id) == $class->id ? 'selected' : '' }}>
                                    {{ $class->nama_kelas }}{{ $class->jurusan ? ' - ' . $class->jurusan : '' }}
                                </option>
                            @endforeach
                        </x-select>

                        {{-- No HP Orang Tua --}}
                        <x-input
                            type="text"
                            name="no_hp_ortu"
                            label="No HP Orang Tua"
                            :value="old('no_hp_ortu', $student->no_hp_ortu)"
                            placeholder="Contoh: 628123456789"
                            helper="Format: 628XXXXXXXXX"
                            :error="$errors->first('no_hp_ortu')"
                        />
                    </div>

                    {{-- Foto Profil --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Foto Profil Baru
                        </label>
                        <input
                            type="file"
                            name="foto_profil"
                            accept="image/*"
                            onchange="previewImage(event)"
                            class="block w-full text-sm text-gray-900 dark:text-gray-100 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl cursor-pointer bg-gray-50 dark:bg-gray-800 hover:border-primary-400 focus:outline-none transition-colors p-4"
                        />
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            Max 2MB, format: JPG, PNG, GIF. Kosongkan jika tidak ingin mengubah foto.
                        </p>
                        @error('foto_profil')
                            <p class="mt-2 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        
                        {{-- Image Preview --}}
                        <div id="imagePreview" class="mt-4 hidden">
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Preview Foto Baru:</p>
                            <img id="preview" src="" alt="Preview" class="w-32 h-32 rounded-xl object-cover ring-4 ring-primary-200 dark:ring-primary-800 shadow-lg">
                        </div>
                    </div>

                    {{-- Status Aktif --}}
                    <div class="flex items-center p-4 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/10 dark:to-indigo-900/10 rounded-xl">
                        <input
                            type="checkbox"
                            name="is_active"
                            id="is_active"
                            value="1"
                            {{ old('is_active', $student->is_active) ? 'checked' : '' }}
                            class="w-5 h-5 text-primary-600 border-gray-300 dark:border-gray-600 rounded focus:ring-primary-500"
                        />
                        <label for="is_active" class="ml-3">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Siswa Aktif</span>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Hanya siswa aktif yang bisa melakukan absensi</p>
                        </label>
                    </div>

                    {{-- QR Code Status --}}
                    @if($student->qr_code_path)
                        <div class="flex items-center justify-between p-4 bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/10 dark:to-emerald-900/10 border-l-4 border-green-500 rounded-xl">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-check text-white"></i>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-green-800 dark:text-green-300">QR Code Tersedia</p>
                                    <p class="text-xs text-green-600 dark:text-green-400">Siswa sudah memiliki QR Code</p>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <a
                                    href="{{ route('attendance.qr.show', $student->nis) }}"
                                    target="_blank"
                                    class="px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white text-xs font-medium rounded-lg transition"
                                >
                                    Lihat
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="flex items-center p-4 bg-gradient-to-br from-yellow-50 to-orange-50 dark:from-yellow-900/10 dark:to-orange-900/10 border-l-4 border-yellow-500 rounded-xl">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-triangle text-yellow-600 dark:text-yellow-400"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-yellow-800 dark:text-yellow-300">QR Code Belum Dibuat</p>
                                <p class="text-xs text-yellow-600 dark:text-yellow-400 mt-1">Generate QR Code setelah menyimpan data</p>
                            </div>
                        </div>
                    @endif

                    {{-- Action Buttons --}}
                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <a
                            href="{{ route('attendance.students.index') }}"
                            class="inline-flex items-center justify-center px-6 py-3 text-sm font-medium rounded-xl transition-all duration-200 border-2 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                        >
                            Batal
                        </a>
                        <button
                            type="submit"
                            class="inline-flex items-center justify-center px-6 py-3 text-sm font-semibold rounded-xl transition-all duration-200 bg-gradient-to-r from-primary-500 to-blue-600 text-white hover:from-primary-600 hover:to-blue-700 shadow-lg hover:shadow-xl hover:-translate-y-0.5"
                        >
                            <i class="fas fa-save mr-2"></i>
                            Update Data
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
