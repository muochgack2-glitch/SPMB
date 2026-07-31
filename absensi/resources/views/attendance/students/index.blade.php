<x-app-layout>
    <x-slot name="title">Data Siswa</x-slot>
    <x-slot name="pageTitle">Manajemen Siswa</x-slot>

    <div class="space-y-6">
        {{-- Page Header --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Manajemen Siswa</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">Kelola data siswa dan QR Code absensi</p>
            </div>
            
            <div class="flex flex-wrap gap-3">
                <a
                    href="{{ route('attendance.students.export.template') }}"
                    class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200 bg-gradient-to-r from-green-500 to-green-600 text-white hover:from-green-600 hover:to-green-700 shadow-md hover:shadow-lg hover:-translate-y-0.5"
                >
                    <i class="fas fa-file-excel mr-2"></i>
                    Download Template
                </a>
                
                <a
                    href="{{ route('attendance.students.import.form') }}"
                    class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200 bg-gradient-to-r from-blue-500 to-blue-600 text-white hover:from-blue-600 hover:to-blue-700 shadow-md hover:shadow-lg hover:-translate-y-0.5"
                >
                    <i class="fas fa-file-import mr-2"></i>
                    Import Excel
                </a>
                
                <a
                    href="{{ route('attendance.students.create') }}"
                    class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200 bg-gradient-to-r from-primary-500 to-primary-600 text-white hover:from-primary-600 hover:to-primary-700 shadow-md hover:shadow-lg hover:-translate-y-0.5"
                >
                    <i class="fas fa-plus mr-2"></i>
                    Tambah Siswa
                </a>
            </div>
        </div>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <x-stat-card
                title="Total Siswa"
                :value="\App\Models\AttendanceStudent::count()"
                icon="fas fa-users"
                color="blue"
            />
            
            <x-stat-card
                title="Siswa Aktif"
                :value="\App\Models\AttendanceStudent::where('is_active', true)->count()"
                icon="fas fa-user-check"
                color="success"
            />
            
            <x-stat-card
                title="QR Code Dibuat"
                :value="\App\Models\AttendanceStudent::whereNotNull('qr_code_path')->count()"
                icon="fas fa-qrcode"
                color="purple"
            />
        </div>

        {{-- Search & Filter Card --}}
        <x-card>
            <form method="GET" action="{{ route('attendance.students.index') }}" class="space-y-4" id="filterForm">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    {{-- Search Input --}}
                    <div class="md:col-span-2">
                        <x-input
                            type="text"
                            name="search"
                            :value="request('search')"
                            placeholder="Cari nama atau NIS..."
                            icon="fa-search"
                            label="Pencarian"
                            id="searchInput"
                        />
                    </div>
                    
                    {{-- Class Filter --}}
                    <div>
                        <x-select
                            name="kelas_id"
                            label="Filter Kelas"
                            onchange="document.getElementById('filterForm').submit()"
                        >
                            <option value="">Semua Kelas</option>
                            @foreach(\App\Models\AttendanceClass::orderBy('tingkat')->orderBy('nama_kelas')->get() as $class)
                                <option value="{{ $class->id }}" {{ request('kelas_id') == $class->id ? 'selected' : '' }}>
                                    {{ $class->nama_kelas }}
                                </option>
                            @endforeach
                        </x-select>
                    </div>
                    
                    {{-- Status Filter --}}
                    <div>
                        <x-select
                            name="status"
                            label="Status"
                            onchange="document.getElementById('filterForm').submit()"
                        >
                            <option value="">Semua</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                        </x-select>
                    </div>
                </div>
                
                <div class="flex justify-end gap-2">
                    <a
                        href="{{ route('attendance.students.index') }}"
                        class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200 border-2 border-primary-500 text-primary-600 dark:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/20"
                    >
                        <i class="fas fa-redo mr-2"></i>
                        Reset Filter
                    </a>
                </div>
            </form>
        </x-card>

        @push('scripts')
        <script>
            // Debounce function untuk search input
            let searchTimeout;
            const searchInput = document.getElementById('searchInput');
            const filterForm = document.getElementById('filterForm');
            
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(function() {
                        filterForm.submit();
                    }, 500); // Submit setelah 500ms user berhenti mengetik
                });
            }
        </script>
        @endpush

        {{-- Students Table --}}
        <x-section-card title="Daftar Siswa">
            <x-table>
                <x-slot name="header">
                    <x-table.header>Foto</x-table.header>
                    <x-table.header>NIS</x-table.header>
                    <x-table.header>Nama</x-table.header>
                    <x-table.header>Kelas</x-table.header>
                    <x-table.header>No HP Ortu</x-table.header>
                    <x-table.header>QR Code</x-table.header>
                    <x-table.header>Status</x-table.header>
                    <x-table.header>Aksi</x-table.header>
                </x-slot>

                @forelse($students as $student)
                    <x-table.row>
                        {{-- Photo --}}
                        <x-table.cell>
                            @if($student->foto_profil)
                                <img 
                                    src="{{ asset('storage/' . $student->foto_profil) }}" 
                                    alt="{{ $student->nama }}"
                                    class="w-10 h-10 rounded-full object-cover ring-2 ring-gray-200 dark:ring-gray-600"
                                >
                            @else
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white text-sm font-semibold">
                                    {{ strtoupper(substr($student->nama, 0, 1)) }}
                                </div>
                            @endif
                        </x-table.cell>
                        
                        {{-- NIS --}}
                        <x-table.cell>
                            <span class="font-medium text-gray-900 dark:text-white">{{ $student->nis }}</span>
                        </x-table.cell>
                        
                        {{-- Name --}}
                        <x-table.cell>
                            <span class="text-gray-900 dark:text-white">{{ $student->nama }}</span>
                        </x-table.cell>
                        
                        {{-- Class --}}
                        <x-table.cell>
                            <span class="text-gray-600 dark:text-gray-400">
                                {{ $student->kelas->nama_kelas }}
                            </span>
                        </x-table.cell>
                        
                        {{-- Parent Phone --}}
                        <x-table.cell>
                            <span class="text-gray-600 dark:text-gray-400">
                                {{ $student->no_hp_ortu ?? '-' }}
                            </span>
                        </x-table.cell>
                        
                        {{-- QR Code --}}
                        <x-table.cell>
                            @if($student->qr_code_path)
                                <a 
                                    href="{{ route('attendance.qr.show', $student->nis) }}" 
                                    class="inline-flex items-center text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 text-sm font-medium"
                                    target="_blank"
                                >
                                    <i class="fas fa-qrcode mr-1"></i>
                                    Lihat
                                </a>
                            @else
                                <span class="text-gray-400 dark:text-gray-500 text-sm">Belum ada</span>
                            @endif
                        </x-table.cell>
                        
                        {{-- Status --}}
                        <x-table.cell>
                            @if($student->is_active)
                                <x-badge variant="success">Aktif</x-badge>
                            @else
                                <x-badge variant="danger">Tidak Aktif</x-badge>
                            @endif
                        </x-table.cell>
                        
                        {{-- Actions --}}
                        <x-table.cell>
                            <div class="flex items-center space-x-2">
                                {{-- View --}}
                                <a 
                                    href="{{ route('attendance.students.show', $student->id) }}" 
                                    class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300"
                                    title="Lihat Detail"
                                >
                                    <i class="fas fa-eye"></i>
                                </a>
                                
                                {{-- Edit --}}
                                <a 
                                    href="{{ route('attendance.students.edit', $student->id) }}" 
                                    class="text-yellow-600 dark:text-yellow-400 hover:text-yellow-700 dark:hover:text-yellow-300"
                                    title="Edit"
                                >
                                    <i class="fas fa-edit"></i>
                                </a>
                                
                                {{-- Delete --}}
                                <form 
                                    method="POST" 
                                    action="{{ route('attendance.students.destroy', $student->id) }}" 
                                    onsubmit="return confirm('Yakin ingin menghapus siswa {{ $student->nama }}?')"
                                    class="inline"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button 
                                        type="submit" 
                                        class="text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300"
                                        title="Hapus"
                                    >
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </x-table.cell>
                    </x-table.row>
                @empty
                    <x-table.row>
                        <x-table.cell colspan="8" class="text-center py-12">
                            <x-empty-state 
                                icon="fas fa-users"
                                message="Belum ada data siswa"
                            >
                                <x-slot name="action">
                                    <a
                                        href="{{ route('attendance.students.create') }}"
                                        class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200 bg-gradient-to-r from-primary-500 to-primary-600 text-white hover:from-primary-600 hover:to-primary-700 shadow-md hover:shadow-lg mt-4"
                                    >
                                        <i class="fas fa-plus mr-2"></i>
                                        Tambah Siswa Pertama
                                    </a>
                                </x-slot>
                            </x-empty-state>
                        </x-table.cell>
                    </x-table.row>
                @endforelse
            </x-table>
            
            {{-- Pagination --}}
            @if($students->hasPages())
                <div class="mt-6">
                    {{ $students->links() }}
                </div>
            @endif
        </x-section-card>
    </div>
</x-app-layout>
