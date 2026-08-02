<x-app-layout>
    <x-slot name="title">Dashboard Absensi</x-slot>
    <x-slot name="pageTitle">Dashboard</x-slot>

    <div class="space-y-6" id="dashboard-content">
        {{-- Filters Section --}}
        <x-card>
            <form method="GET" action="{{ route('attendance.dashboard') }}" id="filterForm" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-input
                    type="date"
                    name="date"
                    label="Tanggal"
                    :value="$selectedDate"
                    icon="fa-calendar"
                    onchange="document.getElementById('filterForm').submit()"
                />

                <x-select
                    name="class"
                    label="Kelas"
                    :value="$selectedClass ?? ''"
                    icon="fa-school"
                    onchange="document.getElementById('filterForm').submit()"
                >
                    <option value="">Semua Kelas</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ $selectedClass == $class->id ? 'selected' : '' }}>
                            {{ $class->nama_kelas }}
                        </option>
                    @endforeach
                </x-select>
            </form>
        </x-card>

        {{-- Statistics Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            {{-- Total Students --}}
            <x-stat-card
                title="Total Siswa"
                :value="$stats['total'] ?? 0"
                icon="fa-users"
                color="blue"
            />

            {{-- Present --}}
            <x-stat-card
                title="Hadir"
                :value="$stats['present'] ?? 0"
                icon="fa-check-circle"
                color="green"
            />

            {{-- Late --}}
            <x-stat-card
                title="Terlambat"
                :value="$stats['late'] ?? 0"
                icon="fa-clock"
                color="yellow"
            />

            {{-- Alpha --}}
            <x-stat-card
                title="Alpha"
                :value="$stats['alpha'] ?? 0"
                icon="fa-times-circle"
                color="red"
            />
        </div>

        {{-- Attendance Records Table --}}
        <x-card>
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                    <i class="fas fa-clipboard-list mr-2 text-primary-600"></i>
                    Data Absensi
                </h3>
                <button onclick="refreshDashboard()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                    <i class="fas fa-sync-alt mr-2"></i>
                    Refresh
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                NIS
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Nama
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Kelas
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Check In
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Check Out
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Foto
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($attendanceRecords as $record)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $record->student->nis }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ $record->student->nama }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $record->student->kelas->nama_kelas ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $record->check_in_time ? \Carbon\Carbon::parse($record->check_in_time)->format('H:i') : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $record->check_out_time ? \Carbon\Carbon::parse($record->check_out_time)->format('H:i') : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($record->status === 'hadir')
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                                            ✅ Hadir
                                        </span>
                                    @elseif($record->status === 'terlambat')
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300">
                                            ⏰ Terlambat
                                        </span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                            {{ ucfirst($record->status) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <div class="flex items-center gap-2">
                                        @if($record->check_in_photo)
                                            <button onclick="viewPhoto('{{ $record->check_in_photo_url }}', '{{ addslashes($record->student->nama) }}', 'Check In')"
                                                    class="inline-flex items-center justify-center w-8 h-8 bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 rounded hover:bg-green-200 dark:hover:bg-green-900/50 transition-colors"
                                                    title="Lihat foto check in">
                                                <i class="fas fa-sign-in-alt text-sm"></i>
                                            </button>
                                        @else
                                            <div class="inline-flex items-center justify-center w-8 h-8 bg-gray-100 dark:bg-gray-800 text-gray-400 rounded">
                                                <i class="fas fa-sign-in-alt text-sm"></i>
                                            </div>
                                        @endif
                                        
                                        @if($record->check_out_photo)
                                            <button onclick="viewPhoto('{{ $record->check_out_photo_url }}', '{{ addslashes($record->student->nama) }}', 'Check Out')"
                                                    class="inline-flex items-center justify-center w-8 h-8 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded hover:bg-blue-200 dark:hover:bg-blue-900/50 transition-colors"
                                                    title="Lihat foto check out">
                                                <i class="fas fa-sign-out-alt text-sm"></i>
                                            </button>
                                        @else
                                            <div class="inline-flex items-center justify-center w-8 h-8 bg-gray-100 dark:bg-gray-800 text-gray-400 rounded">
                                                <i class="fas fa-sign-out-alt text-sm"></i>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                    <i class="fas fa-inbox text-4xl mb-3 text-gray-300 dark:text-gray-600"></i>
                                    <p>Belum ada data absensi untuk tanggal ini</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>

        {{-- Absent Students (if any) --}}
        @if($absentStudents->count() > 0)
            <x-card>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-6">
                    <i class="fas fa-user-times mr-2 text-red-600"></i>
                    Siswa Belum Absen ({{ $absentStudents->count() }})
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($absentStudents as $student)
                        <div class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                            <p class="font-semibold text-gray-900 dark:text-white">{{ $student->nama }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $student->nis }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-500">{{ $student->kelas->nama_kelas ?? '-' }}</p>
                        </div>
                    @endforeach
                </div>
            </x-card>
        @endif
    </div>

    {{-- Photo Modal --}}
    <div id="photoModal" class="hidden fixed inset-0 bg-black bg-opacity-75 backdrop-blur-sm z-50 flex items-center justify-center p-4" onclick="closePhotoModal()">
        <div class="relative max-w-2xl w-full" onclick="event.stopPropagation()">
            {{-- Close Button --}}
            <button onclick="closePhotoModal()" 
                    class="absolute -top-8 right-0 text-white hover:text-gray-300 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
            
            {{-- Photo Container --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-2xl overflow-hidden">
                {{-- Header --}}
                <div class="bg-gradient-to-r from-primary-500 to-purple-600 px-4 py-2 text-white">
                    <h3 class="text-base font-bold" id="photoModalTitle">Foto Absensi</h3>
                    <p class="text-xs opacity-90" id="photoModalSubtitle"></p>
                </div>
                
                {{-- Photo --}}
                <div class="p-3 flex items-center justify-center bg-gray-50 dark:bg-gray-900">
                    <img id="photoModalImage" src="" alt="Foto" class="max-w-full max-h-[40vh] rounded-lg shadow-lg object-contain">
                </div>
                
                {{-- Footer --}}
                <div class="px-4 py-2 bg-gray-100 dark:bg-gray-700 flex justify-between items-center">
                    <div class="text-xs text-gray-600 dark:text-gray-400">
                        <i class="fas fa-info-circle mr-1"></i>
                        Klik di luar untuk menutup
                    </div>
                    <button onclick="downloadPhoto()" 
                            class="px-3 py-1.5 bg-primary-500 hover:bg-primary-600 text-white text-xs rounded-lg transition-colors flex items-center gap-1.5">
                        <i class="fas fa-download"></i>
                        Download
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        /**
         * Manual refresh dashboard data
         */
        function refreshDashboard() {
            const url = new URL(window.location.href);
            const params = new URLSearchParams(url.search);
            
            fetch('{{ route("attendance.dashboard.refresh") }}?' + params.toString())
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    }
                })
                .catch(error => console.error('Refresh error:', error));
        }

        // ============================================================================
        // PHOTO MODAL FUNCTIONS
        // ============================================================================

        function viewPhoto(photoUrl, studentName, type) {
            console.log('viewPhoto called:', { photoUrl, studentName, type });
            
            const modal = document.getElementById('photoModal');
            const image = document.getElementById('photoModalImage');
            const title = document.getElementById('photoModalTitle');
            const subtitle = document.getElementById('photoModalSubtitle');
            
            // Set image source and alt
            image.src = photoUrl;
            image.alt = `Foto ${type} - ${studentName}`;
            image.onerror = function() {
                console.error('Failed to load image:', photoUrl);
                this.src = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="400" height="300"%3E%3Crect fill="%23ddd" width="400" height="300"/%3E%3Ctext fill="%23999" x="50%25" y="50%25" text-anchor="middle" dominant-baseline="middle" font-family="sans-serif" font-size="18"%3EGagal memuat foto%3C/text%3E%3C/svg%3E';
            };
            
            title.textContent = `Foto ${type}`;
            subtitle.textContent = studentName;
            
            modal.classList.remove('hidden');
            
            // Add fade-in animation
            modal.style.animation = 'fadeIn 0.2s ease-out';
        }

        function closePhotoModal() {
            const modal = document.getElementById('photoModal');
            modal.style.animation = 'fadeOut 0.2s ease-out';
            
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 200);
        }
        
        function downloadPhoto() {
            const image = document.getElementById('photoModalImage');
            const link = document.createElement('a');
            link.href = image.src;
            link.download = 'foto-absensi-' + Date.now() + '.jpg';
            link.click();
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Alt+R to refresh
            if (e.altKey && e.key === 'r') {
                e.preventDefault();
                refreshDashboard();
            }
            // ESC to close modal
            if (e.key === 'Escape') {
                closePhotoModal();
            }
        });
    </script>
    
    <style>
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
        
        @keyframes fadeOut {
            from {
                opacity: 1;
            }
            to {
                opacity: 0;
            }
        }
    </style>
    @endpush
</x-app-layout>

