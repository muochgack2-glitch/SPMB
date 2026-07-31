@php
    $pageTitle = 'Edit Kelas';
    $breadcrumbs = [
        ['label' => 'Data Kelas', 'url' => route('attendance.classes.index')],
        ['label' => 'Edit Kelas']
    ];
@endphp

<x-app-layout>
    <div class="max-w-4xl mx-auto space-y-6">
        {{-- Page Header --}}
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Kelas</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Perbarui informasi kelas {{ $class->nama_kelas }}</p>
        </div>

        {{-- Form Card --}}
        <x-card>
            <form method="POST" action="{{ route('attendance.classes.update', $class->id) }}">
                @csrf
                @method('PUT')

                <div class="space-y-6">
                    {{-- Nama Kelas --}}
                    <x-input
                        type="text"
                        name="nama_kelas"
                        label="Nama Kelas"
                        :value="old('nama_kelas', $class->nama_kelas)"
                        placeholder="Contoh: RPL A"
                        required
                        :error="$errors->first('nama_kelas')"
                    />

                    {{-- Tingkat & Jurusan --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <x-select
                            name="tingkat"
                            label="Tingkat"
                            required
                            :error="$errors->first('tingkat')"
                        >
                            <option value="">Pilih Tingkat</option>
                            <option value="10" {{ old('tingkat', $class->tingkat) == 10 ? 'selected' : '' }}>Kelas 10</option>
                            <option value="11" {{ old('tingkat', $class->tingkat) == 11 ? 'selected' : '' }}>Kelas 11</option>
                            <option value="12" {{ old('tingkat', $class->tingkat) == 12 ? 'selected' : '' }}>Kelas 12</option>
                        </x-select>

                        <x-input
                            type="text"
                            name="jurusan"
                            label="Jurusan"
                            :value="old('jurusan', $class->jurusan)"
                            placeholder="Contoh: RPL, TKJ, MM"
                            :error="$errors->first('jurusan')"
                        />
                    </div>

                    {{-- Wali Kelas (optional) --}}
                    <x-input
                        type="text"
                        name="wali_kelas"
                        label="Wali Kelas"
                        :value="old('wali_kelas', $class->wali_kelas ?? '')"
                        placeholder="Nama wali kelas (opsional)"
                        :error="$errors->first('wali_kelas')"
                    />

                    {{-- Status Aktif --}}
                    <div>
                        <label class="flex items-center">
                            <input
                                type="checkbox"
                                name="is_active"
                                value="1"
                                {{ old('is_active', $class->is_active) ? 'checked' : '' }}
                                class="w-5 h-5 text-primary-600 border-gray-300 dark:border-gray-600 rounded focus:ring-primary-500"
                            />
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Kelas Aktif</span>
                        </label>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Hanya kelas aktif yang dapat digunakan untuk absensi</p>
                    </div>

                    {{-- Info Box - Student Count --}}
                    <div class="bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500 p-4 rounded">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-users text-blue-500"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-blue-700 dark:text-blue-300">
                                    <strong>Total Siswa:</strong> {{ $class->students->count() ?? 0 }} siswa terdaftar di kelas ini
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex justify-end gap-3 pt-4">
                        <a
                            href="{{ route('attendance.classes.index') }}"
                            class="inline-flex items-center justify-center px-6 py-2 text-sm font-medium rounded-lg transition-all duration-200 border-2 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                        >
                            Batal
                        </a>
                        <button
                            type="submit"
                            class="inline-flex items-center justify-center px-6 py-2 text-sm font-medium rounded-lg transition-all duration-200 bg-gradient-to-r from-primary-500 to-primary-600 text-white hover:from-primary-600 hover:to-primary-700 shadow-md hover:shadow-lg"
                        >
                            <i class="fas fa-save mr-2"></i>
                            Update Kelas
                        </button>
                    </div>
                </div>
            </form>
        </x-card>
    </div>
</x-app-layout>
