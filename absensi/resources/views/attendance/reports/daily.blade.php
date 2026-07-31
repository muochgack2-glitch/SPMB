@php
    $pageTitle = 'Laporan Harian';
    $breadcrumbs = [
        ['label' => 'Laporan', 'url' => route('attendance.reports.index')],
        ['label' => 'Laporan Harian']
    ];
@endphp

<x-app-layout>
    <div class="space-y-6">
        {{-- Page Header with Filters --}}
        <x-card>
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white text-2xl mr-4">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">📅 Laporan Absensi Harian</h2>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Data real-time absensi siswa hari ini</p>
                    </div>
                </div>
            </div>

            {{-- Filters --}}
            <form method="GET" action="{{ route('attendance.reports.daily') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <x-input
                    type="date"
                    name="date"
                    label="Tanggal"
                    :value="$date"
                />
                
                <x-select
                    name="class_id"
                    label="Kelas"
                >
                    <option value="">Semua Kelas</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ $classId == $class->id ? 'selected' : '' }}>
                            {{ $class->nama_kelas }}
                        </option>
                    @endforeach
                </x-select>
                
                <div class="flex items-end">
                    <button type="submit" 
                            class="w-full inline-flex items-center justify-center px-6 py-2.5 text-sm font-medium rounded-lg transition-all duration-200 bg-gradient-to-r from-primary-500 to-primary-600 text-white hover:from-primary-600 hover:to-primary-700 shadow-md hover:shadow-lg">
                        <i class="fas fa-search mr-2"></i> Filter
                    </button>
                </div>
            </form>
        </x-card>

        {{-- Stats Summary --}}
        @php
            $stats = [
                ['status' => 'hadir', 'count' => $records->where('status', 'hadir')->count(), 'label' => 'Hadir', 'icon' => 'fa-check-circle', 'color' => 'green'],
                ['status' => 'terlambat', 'count' => $records->where('status', 'terlambat')->count(), 'label' => 'Terlambat', 'icon' => 'fa-clock', 'color' => 'yellow'],
                ['status' => 'sakit', 'count' => $records->where('status', 'sakit')->count(), 'label' => 'Sakit', 'icon' => 'fa-notes-medical', 'color' => 'blue'],
                ['status' => 'izin', 'count' => $records->where('status', 'izin')->count(), 'label' => 'Izin', 'icon' => 'fa-file-alt', 'color' => 'purple'],
                ['status' => 'alpha', 'count' => $records->where('status', 'alpha')->count(), 'label' => 'Alpha', 'icon' => 'fa-times-circle', 'color' => 'red'],
            ];
        @endphp
        
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            @foreach($stats as $stat)
            <x-stat-card
                :title="$stat['label']"
                :value="$stat['count']"
                :icon="$stat['icon']"
                :color="$stat['color']"
            />
            @endforeach
        </div>

        {{-- Attendance Records --}}
        <x-card>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6 flex items-center">
                <i class="fas fa-check-double mr-2 text-primary-500"></i>
                Siswa yang Sudah Absen ({{ $records->count() }})
            </h3>
            
            @if($records->count() > 0)
            <div class="overflow-x-auto">
                <x-table>
                    <x-table.header>
                        <th>No</th>
                        <th>Foto</th>
                        <th>NIS</th>
                        <th>Nama</th>
                        <th>Kelas</th>
                        <th>Jam Masuk</th>
                        <th>Jam Pulang</th>
                        <th>Status</th>
                    </x-table.header>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($records as $index => $record)
                        <x-table.row>
                            <x-table.cell>{{ $index + 1 }}</x-table.cell>
                            <x-table.cell>
                                @if($record->check_in_photo)
                                    <img src="{{ $record->check_in_photo_url }}" 
                                         class="w-12 h-12 rounded-full object-cover border-2 border-green-500 dark:border-green-700"
                                         alt="Foto">
                                @else
                                    <div class="w-12 h-12 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                        <i class="fas fa-user text-gray-400"></i>
                                    </div>
                                @endif
                            </x-table.cell>
                            <x-table.cell>
                                <span class="font-medium">{{ $record->student->nis }}</span>
                            </x-table.cell>
                            <x-table.cell>{{ $record->student->nama }}</x-table.cell>
                            <x-table.cell>{{ $record->student->kelas->nama_kelas }}</x-table.cell>
                            <x-table.cell>
                                <span class="font-mono">
                                    {{ $record->check_in_time ? \Carbon\Carbon::parse($record->check_in_time)->format('H:i') : '-' }}
                                </span>
                            </x-table.cell>
                            <x-table.cell>
                                <span class="font-mono">
                                    {{ $record->check_out_time ? \Carbon\Carbon::parse($record->check_out_time)->format('H:i') : '-' }}
                                </span>
                            </x-table.cell>
                            <x-table.cell>
                                @php
                                    $statusVariants = [
                                        'hadir' => 'success',
                                        'terlambat' => 'warning',
                                        'sakit' => 'info',
                                        'izin' => 'info',
                                        'alpha' => 'danger',
                                    ];
                                    $variant = $statusVariants[$record->status] ?? 'secondary';
                                @endphp
                                <x-badge :variant="$variant">
                                    {{ ucfirst($record->status) }}
                                </x-badge>
                            </x-table.cell>
                        </x-table.row>
                        @endforeach
                    </tbody>
                </x-table>
            </div>
            @else
            <x-empty-state
                icon="fa-calendar-check"
                title="Belum Ada Data"
                message="Belum ada siswa yang absen hari ini"
            />
            @endif
        </x-card>

        {{-- Absent Students --}}
        @if($absentStudents->count() > 0)
        <x-card class="border-l-4 border-red-500">
            <div class="flex items-center mb-6">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-red-500 to-red-600 flex items-center justify-center text-white text-2xl mr-4">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">⚠️ Siswa Belum Absen</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $absentStudents->count() }} siswa belum melakukan absensi</p>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($absentStudents as $student)
                <div class="flex items-center gap-3 p-4 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800 hover:shadow-md transition-all duration-200">
                    <div class="flex-shrink-0">
                        @if($student->foto_profil)
                            <img src="{{ Storage::url($student->foto_profil) }}" 
                                 class="w-12 h-12 rounded-full object-cover border-2 border-red-300 dark:border-red-700"
                                 alt="Foto">
                        @else
                            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-red-400 to-red-500 flex items-center justify-center text-white font-bold text-lg">
                                {{ substr($student->nama, 0, 1) }}
                            </div>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-gray-900 dark:text-white truncate">{{ $student->nama }}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $student->nis }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-500">{{ $student->kelas->nama_kelas }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </x-card>
        @endif
    </div>
</x-app-layout>
