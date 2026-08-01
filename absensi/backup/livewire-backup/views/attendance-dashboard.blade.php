<div wire:poll.30s="refresh" class="space-y-6">
    
    <!-- Filters Section -->
    <x-card>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Date Filter -->
            <x-input
                type="date"
                name="date"
                label="Tanggal"
                wire:model.live="selectedDate"
                icon="fa-calendar"
            />
            
            <!-- Class Filter -->
            <x-select
                name="class"
                label="Kelas"
                wire:model.live="selectedClass"
                icon="fa-school"
            >
                <option value="">Semua Kelas</option>
                @foreach($classes as $class)
                    <option value="{{ $class->id }}">{{ $class->nama_kelas }}</option>
                @endforeach
            </x-select>
        </div>
    </x-card>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <x-stat-card
            title="Total Siswa"
            :value="$stats['total'] ?? 0"
            icon="fa-users"
            color="primary"
        />
        
        <x-stat-card
            title="Hadir"
            :value="$stats['hadir'] ?? 0"
            icon="fa-user-check"
            color="success"
            :trend="$stats['hadir_trend'] ?? null"
            :trendUp="($stats['hadir_trend'] ?? 0) > 0"
        />
        
        <x-stat-card
            title="Terlambat"
            :value="$stats['terlambat'] ?? 0"
            icon="fa-clock"
            color="warning"
        />
        
        <x-stat-card
            title="Alpha"
            :value="$stats['alpha'] ?? 0"
            icon="fa-user-times"
            color="danger"
        />
    </div>

    <!-- Charts & Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Chart Area (2 columns) -->
        <div class="lg:col-span-2">
            <x-section-card title="Statistik Kehadiran" subtitle="7 hari terakhir">
                <div id="attendance-chart" class="h-80"></div>
            </x-section-card>
        </div>

        <!-- Recent Activity (1 column) -->
        <div>
            <x-section-card title="Aktivitas Terbaru" subtitle="Real-time updates">
                <div class="space-y-3 max-h-96 overflow-y-auto">
                    @forelse($attendanceRecords->take(10) as $record)
                        <div class="flex items-start space-x-3 p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                            <!-- Photo Thumbnail -->
                            <div class="flex-shrink-0">
                                @if($record->student->foto_profil)
                                    <img 
                                        src="{{ Storage::url($record->student->foto_profil) }}" 
                                        alt="{{ $record->student->nama }}"
                                        class="w-10 h-10 rounded-full object-cover border-2 border-gray-200 dark:border-gray-700"
                                    >
                                @else
                                    <div class="w-10 h-10 bg-gradient-to-br from-primary-400 to-primary-600 rounded-full flex items-center justify-center text-white font-semibold">
                                        {{ strtoupper(substr($record->student->nama, 0, 1)) }}
                                    </div>
                                @endif
                            </div>
                            
                            <!-- Activity Info -->
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                    {{ $record->student->nama }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $record->student->kelas->nama_kelas ?? '-' }}
                                </p>
                                <div class="flex items-center space-x-2 mt-1">
                                    <x-badge variant="{{ $record->status === 'hadir' ? 'success' : ($record->status === 'terlambat' ? 'warning' : 'danger') }}" size="sm" dot>
                                        {{ ucfirst($record->status) }}
                                    </x-badge>
                                    <span class="text-xs text-gray-400">
                                        {{ $record->check_in_time ? \Carbon\Carbon::parse($record->check_in_time)->format('H:i') : '-' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <x-empty-state
                            icon="fa-inbox"
                            title="Belum ada aktivitas"
                            message="Belum ada siswa yang melakukan absensi hari ini."
                        />
                    @endforelse
                </div>
            </x-section-card>
        </div>
    </div>

    <!-- Attendance Records Table -->
    <x-section-card title="Data Absensi Hari Ini" :subtitle="'Total: ' . $attendanceRecords->count() . ' siswa'">
        @if($attendanceRecords->count() > 0)
            <x-table>
                <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        <x-table.header>Foto</x-table.header>
                        <x-table.header>NIS</x-table.header>
                        <x-table.header>Nama</x-table.header>
                        <x-table.header>Kelas</x-table.header>
                        <x-table.header>Jam Masuk</x-table.header>
                        <x-table.header>Jam Pulang</x-table.header>
                        <x-table.header>Status</x-table.header>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($attendanceRecords as $record)
                        <x-table.row striped hover>
                            <x-table.cell>
                                @if($record->check_in_photo)
                                    <img 
                                        src="{{ Storage::url($record->check_in_photo) }}" 
                                        alt="Check In"
                                        class="w-10 h-10 rounded-lg object-cover cursor-pointer hover:ring-2 hover:ring-primary-500 transition-all"
                                        wire:click="viewPhoto('{{ $record->check_in_photo }}', 'check_in')"
                                    >
                                @else
                                    <div class="w-10 h-10 bg-gray-200 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-image text-gray-400 text-sm"></i>
                                    </div>
                                @endif
                            </x-table.cell>
                            <x-table.cell>
                                <span class="font-mono text-xs">{{ $record->student->nis }}</span>
                            </x-table.cell>
                            <x-table.cell>{{ $record->student->nama }}</x-table.cell>
                            <x-table.cell>{{ $record->student->kelas->nama_kelas ?? '-' }}</x-table.cell>
                            <x-table.cell>
                                {{ $record->check_in_time ? \Carbon\Carbon::parse($record->check_in_time)->format('H:i') : '-' }}
                            </x-table.cell>
                            <x-table.cell>
                                {{ $record->check_out_time ? \Carbon\Carbon::parse($record->check_out_time)->format('H:i') : '-' }}
                            </x-table.cell>
                            <x-table.cell>
                                <x-badge 
                                    variant="{{ $record->status === 'hadir' ? 'success' : ($record->status === 'terlambat' ? 'warning' : 'danger') }}"
                                    dot
                                >
                                    {{ ucfirst($record->status) }}
                                </x-badge>
                            </x-table.cell>
                        </x-table.row>
                    @endforeach
                </tbody>
            </x-table>
        @else
            <x-empty-state
                icon="fa-clipboard-list"
                title="Belum ada data absensi"
                message="Belum ada siswa yang melakukan absensi pada tanggal ini."
            />
        @endif
    </x-section-card>

    <!-- Photo Lightbox Modal -->
    @if($showPhotoModal && $selectedPhoto)
        <x-modal name="photo-modal" title="Foto Absensi" maxWidth="2xl">
            <div class="space-y-4">
                <img 
                    src="{{ Storage::url($selectedPhoto['path']) }}" 
                    alt="Attendance Photo"
                    class="w-full rounded-lg"
                >
                <div class="text-center">
                    <x-badge variant="primary">{{ ucfirst(str_replace('_', ' ', $selectedPhoto['type'])) }}</x-badge>
                </div>
            </div>
            
            <x-slot:footer>
                <x-button variant="secondary" @click="$wire.closePhotoModal()">
                    Tutup
                </x-button>
            </x-slot:footer>
        </x-modal>
    @endif

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    // Initialize chart when component loads
    document.addEventListener('livewire:init', () => {
        initAttendanceChart();
    });

    function initAttendanceChart() {
        const chartElement = document.querySelector('#attendance-chart');
        
        if (!chartElement) return;

        const options = {
            series: [{
                name: 'Hadir',
                data: [30, 40, 35, 50, 49, 60, 70]
            }, {
                name: 'Terlambat',
                data: [5, 8, 10, 7, 12, 9, 15]
            }, {
                name: 'Alpha',
                data: [3, 2, 5, 3, 4, 2, 5]
            }],
            chart: {
                type: 'area',
                height: 320,
                toolbar: {
                    show: false
                },
                zoom: {
                    enabled: false
                },
                background: 'transparent'
            },
            colors: ['#10b981', '#f59e0b', '#ef4444'],
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth',
                width: 2
            },
            fill: {
                type: 'gradient',
                gradient: {
                    opacityFrom: 0.6,
                    opacityTo: 0.1,
                }
            },
            grid: {
                borderColor: document.documentElement.classList.contains('dark') ? '#374151' : '#e5e7eb',
            },
            xaxis: {
                categories: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
                labels: {
                    style: {
                        colors: document.documentElement.classList.contains('dark') ? '#9ca3af' : '#6b7280'
                    }
                }
            },
            yaxis: {
                labels: {
                    style: {
                        colors: document.documentElement.classList.contains('dark') ? '#9ca3af' : '#6b7280'
                    }
                }
            },
            legend: {
                position: 'top',
                horizontalAlign: 'right',
                labels: {
                    colors: document.documentElement.classList.contains('dark') ? '#d1d5db' : '#374151'
                }
            },
            tooltip: {
                theme: document.documentElement.classList.contains('dark') ? 'dark' : 'light'
            }
        };

        const chart = new ApexCharts(chartElement, options);
        chart.render();
    }
</script>
@endpush
