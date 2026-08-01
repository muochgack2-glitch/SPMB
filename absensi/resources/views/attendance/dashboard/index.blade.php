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
                                    @if($record->check_in_photo)
                                        <img 
                                            src="{{ Storage::url($record->check_in_photo) }}" 
                                            alt="Check In"
                                            class="w-10 h-10 rounded-lg object-cover cursor-pointer hover:ring-2 hover:ring-primary-500 transition-all"
                                            onclick="viewPhoto('{{ Storage::url($record->check_in_photo) }}', 'Check In')"
                                        >
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
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
    <div id="photoModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black bg-opacity-75" onclick="closePhotoModal()">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="inline-block align-middle bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full" onclick="event.stopPropagation()">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white" id="photoModalTitle">Foto Absensi</h3>
                        <button onclick="closePhotoModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                    <img id="photoModalImage" src="" alt="Foto" class="w-full rounded-lg">
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Auto-refresh every 30 seconds
        let autoRefreshInterval = setInterval(refreshDashboard, 30000);

        function refreshDashboard() {
            const url = new URL(window.location.href);
            const params = new URLSearchParams(url.search);
            
            fetch('{{ route("attendance.dashboard.refresh") }}?' + params.toString())
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Reload the page to update data
                        location.reload();
                    }
                })
                .catch(error => console.error('Refresh error:', error));
        }

        function viewPhoto(photoUrl, title) {
            document.getElementById('photoModalImage').src = photoUrl;
            document.getElementById('photoModalTitle').textContent = title;
            document.getElementById('photoModal').classList.remove('hidden');
        }

        function closePhotoModal() {
            document.getElementById('photoModal').classList.add('hidden');
        }

        // Keyboard shortcut: Alt+R to refresh
        document.addEventListener('keydown', function(e) {
            if (e.altKey && e.key === 'r') {
                e.preventDefault();
                refreshDashboard();
            }
        });
    </script>
    @endpush
</x-app-layout>

