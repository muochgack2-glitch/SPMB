<div class="attendance-dashboard" wire:poll.30s="refresh">
    {{-- Filters --}}
    <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- Date Filter --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal</label>
                <input 
                    type="date" 
                    wire:model.live="selectedDate"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
            </div>

            {{-- Class Filter --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Kelas</label>
                <select 
                    wire:model.live="selectedClass"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                    <option value="">Semua Kelas</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->nama_kelas }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        {{-- Hadir --}}
        <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Hadir</p>
                    <p class="text-2xl font-bold text-green-600">{{ $stats['hadir'] ?? 0 }}</p>
                </div>
                <div class="text-3xl">✅</div>
            </div>
        </div>

        {{-- Terlambat --}}
        <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-yellow-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Terlambat</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ $stats['terlambat'] ?? 0 }}</p>
                </div>
                <div class="text-3xl">⏰</div>
            </div>
        </div>

        {{-- Sakit --}}
        <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Sakit</p>
                    <p class="text-2xl font-bold text-blue-600">{{ $stats['sakit'] ?? 0 }}</p>
                </div>
                <div class="text-3xl">🤒</div>
            </div>
        </div>

        {{-- Izin --}}
        <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Izin</p>
                    <p class="text-2xl font-bold text-purple-600">{{ $stats['izin'] ?? 0 }}</p>
                </div>
                <div class="text-3xl">📝</div>
            </div>
        </div>

        {{-- Alpha --}}
        <div class="bg-white rounded-lg shadow-sm p-4 border-l-4 border-red-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Alpha</p>
                    <p class="text-2xl font-bold text-red-600">{{ $stats['alpha'] ?? 0 }}</p>
                </div>
                <div class="text-3xl">❌</div>
            </div>
        </div>
    </div>

    {{-- Attendance Records Table --}}
    <div class="bg-white rounded-lg shadow-sm mb-6 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Data Absensi Hari Ini</h2>
            <p class="text-sm text-gray-600">Total: {{ $attendanceRecords->count() }} siswa</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Foto
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            NIS
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Nama
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Kelas
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Jam Masuk
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Jam Pulang
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($attendanceRecords as $record)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex gap-2">
                                @if($record->check_in_photo)
                                <button 
                                    wire:click="viewPhoto('{{ $record->check_in_photo }}', 'check_in')"
                                    class="photo-thumbnail w-12 h-12 rounded-lg overflow-hidden border-2 border-green-300 hover:border-green-500 transition cursor-pointer"
                                    title="Lihat foto check in"
                                >
                                    <img 
                                        src="{{ $record->checkInPhotoUrl }}" 
                                        alt="Check In"
                                        class="w-full h-full object-cover"
                                    >
                                </button>
                                @endif

                                @if($record->check_out_photo)
                                <button 
                                    wire:click="viewPhoto('{{ $record->check_out_photo }}', 'check_out')"
                                    class="photo-thumbnail w-12 h-12 rounded-lg overflow-hidden border-2 border-blue-300 hover:border-blue-500 transition cursor-pointer"
                                    title="Lihat foto check out"
                                >
                                    <img 
                                        src="{{ $record->checkOutPhotoUrl }}" 
                                        alt="Check Out"
                                        class="w-full h-full object-cover"
                                    >
                                </button>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $record->student->nis }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $record->student->nama }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $record->student->kelas->nama_kelas ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $record->check_in_time ? $record->check_in_time->format('H:i') : '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $record->check_out_time ? $record->check_out_time->format('H:i') : '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="status-badge status-{{ $record->status }} px-3 py-1 rounded-full text-xs font-semibold">
                                {{ $record->statusLabel }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            <div class="text-4xl mb-2">📭</div>
                            <p>Belum ada data absensi</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Absent Students --}}
    @if($absentStudents->count() > 0)
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-red-50">
            <h2 class="text-lg font-semibold text-red-800">Belum Absen</h2>
            <p class="text-sm text-red-600">{{ $absentStudents->count() }} siswa belum melakukan absensi</p>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($absentStudents as $student)
                <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                    @if($student->foto_profil)
                    <img 
                        src="{{ Storage::url($student->foto_profil) }}" 
                        alt="{{ $student->nama }}"
                        class="w-10 h-10 rounded-full object-cover"
                    >
                    @else
                    <div class="w-10 h-10 rounded-full bg-gray-300 flex items-center justify-center text-gray-600">
                        {{ substr($student->nama, 0, 1) }}
                    </div>
                    @endif
                    
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ $student->nama }}</p>
                        <p class="text-xs text-gray-500">{{ $student->kelas->nama_kelas ?? '-' }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- Photo Lightbox Modal --}}
    @if($showPhotoModal && $selectedPhoto)
    <div 
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-75"
        wire:click="closePhotoModal"
    >
        <div class="relative max-w-4xl max-h-screen p-4">
            <button 
                wire:click="closePhotoModal"
                class="absolute top-6 right-6 text-white text-3xl hover:text-gray-300 transition z-10"
            >
                ✕
            </button>
            
            <img 
                src="{{ Storage::url($selectedPhoto['path']) }}" 
                alt="Foto Absensi"
                class="max-w-full max-h-screen rounded-lg shadow-2xl"
                @click.stop
            >
            
            <div class="absolute bottom-6 left-6 bg-black bg-opacity-70 text-white px-4 py-2 rounded-lg">
                {{ $selectedPhoto['type'] === 'check_in' ? '📥 Check In' : '📤 Check Out' }}
            </div>
        </div>
    </div>
    @endif

    {{-- Auto-refresh indicator --}}
    <div 
        class="fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded-full shadow-lg text-sm"
        wire:loading
    >
        🔄 Memuat data...
    </div>

    <style>
        .status-badge {
            display: inline-block;
        }

        .status-hadir {
            background-color: #d1fae5;
            color: #065f46;
        }

        .status-terlambat {
            background-color: #fef3c7;
            color: #92400e;
        }

        .status-sakit {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .status-izin {
            background-color: #e9d5ff;
            color: #6b21a8;
        }

        .status-alpha {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .photo-thumbnail {
            transition: all 0.2s ease;
        }

        .photo-thumbnail:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .photo-thumbnail img {
            transition: opacity 0.2s ease;
        }

        .photo-thumbnail:hover img {
            opacity: 0.9;
        }
    </style>
</div>
