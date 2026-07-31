<x-app-layout>
    <x-slot name="title">Data Kelas</x-slot>
    <x-slot name="pageTitle">Manajemen Kelas</x-slot>

    <div class="space-y-6">
        {{-- Page Header --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Manajemen Kelas</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">Kelola data kelas dan organisasi siswa</p>
            </div>
            
            <a
                href="{{ route('attendance.classes.create') }}"
                class="inline-flex items-center justify-center px-6 py-3 text-sm font-semibold rounded-xl transition-all duration-200 bg-gradient-to-r from-primary-500 to-blue-600 text-white hover:from-primary-600 hover:to-blue-700 shadow-lg hover:shadow-xl hover:-translate-y-0.5"
            >
                <i class="fas fa-plus mr-2"></i>
                Tambah Kelas
            </a>
        </div>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <x-stat-card
                title="Total Kelas"
                :value="\App\Models\AttendanceClass::count()"
                icon="fas fa-school"
                color="blue"
            />
            
            <x-stat-card
                title="Kelas Aktif"
                :value="\App\Models\AttendanceClass::where('is_active', true)->count()"
                icon="fas fa-check-circle"
                color="success"
            />
            
            <x-stat-card
                title="Total Siswa"
                :value="\App\Models\AttendanceStudent::count()"
                icon="fas fa-users"
                color="purple"
            />
            
            <x-stat-card
                title="Rata-rata Siswa/Kelas"
                :value="round(\App\Models\AttendanceStudent::count() / max(\App\Models\AttendanceClass::count(), 1))"
                icon="fas fa-chart-line"
                color="info"
            />
        </div>

        {{-- Classes Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($classes as $class)
                <x-card class="group hover:shadow-2xl transition-all duration-300 hover:-translate-y-1 bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-900">
                    <div class="space-y-4">
                        {{-- Header --}}
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center space-x-2 mb-2">
                                    <div class="w-10 h-10 bg-gradient-to-br from-primary-500 to-blue-600 rounded-xl flex items-center justify-center text-white font-bold shadow-lg">
                                        {{ $class->tingkat }}
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                                            {{ $class->nama_kelas }}
                                        </h3>
                                        @if($class->jurusan)
                                            <p class="text-xs text-gray-600 dark:text-gray-400">{{ $class->jurusan }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            @if($class->is_active)
                                <x-badge variant="success">Aktif</x-badge>
                            @else
                                <x-badge variant="danger">Non-Aktif</x-badge>
                            @endif
                        </div>

                        {{-- Wali Kelas --}}
                        @if($class->wali_kelas)
                            <div class="flex items-center space-x-2 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                <i class="fas fa-user-tie text-blue-600 dark:text-blue-400"></i>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Wali Kelas</p>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $class->wali_kelas }}</p>
                                </div>
                            </div>
                        @endif

                        {{-- Stats --}}
                        <div class="flex items-center justify-center p-4 bg-gradient-to-br from-primary-50 to-blue-50 dark:from-primary-900/10 dark:to-blue-900/10 rounded-xl">
                            <div class="text-center">
                                <div class="text-3xl font-bold bg-gradient-to-r from-primary-600 to-blue-600 bg-clip-text text-transparent">
                                    {{ $class->students_count ?? 0 }}
                                </div>
                                <div class="text-sm text-gray-600 dark:text-gray-400 font-medium">Siswa Terdaftar</div>
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="flex gap-2 pt-2 border-t border-gray-200 dark:border-gray-700">
                            <a
                                href="{{ route('attendance.classes.edit', $class->id) }}"
                                class="flex-1 inline-flex items-center justify-center px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200 bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400 hover:bg-yellow-200 dark:hover:bg-yellow-900/50"
                            >
                                <i class="fas fa-edit mr-2"></i>
                                Edit
                            </a>
                            
                            <form
                                action="{{ route('attendance.classes.destroy', $class->id) }}"
                                method="POST"
                                onsubmit="return confirm('Yakin ingin menghapus kelas {{ $class->nama_kelas }}?')"
                                class="flex-1"
                            >
                                @csrf
                                @method('DELETE')
                                <button
                                    type="submit"
                                    class="w-full inline-flex items-center justify-center px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 hover:bg-red-200 dark:hover:bg-red-900/50"
                                >
                                    <i class="fas fa-trash mr-2"></i>
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                </x-card>
            @empty
                <div class="col-span-full">
                    <x-card class="text-center py-12">
                        <x-empty-state
                            icon="fas fa-school"
                            message="Belum ada data kelas"
                        >
                            <x-slot name="action">
                                <a
                                    href="{{ route('attendance.classes.create') }}"
                                    class="inline-flex items-center justify-center px-6 py-3 text-sm font-semibold rounded-xl transition-all duration-200 bg-gradient-to-r from-primary-500 to-blue-600 text-white hover:from-primary-600 hover:to-blue-700 shadow-lg hover:shadow-xl mt-4"
                                >
                                    <i class="fas fa-plus mr-2"></i>
                                    Tambah Kelas Pertama
                                </a>
                            </x-slot>
                        </x-empty-state>
                    </x-card>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($classes->hasPages())
            <div class="flex justify-center">
                {{ $classes->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
