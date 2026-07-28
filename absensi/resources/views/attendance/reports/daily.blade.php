<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Harian - Sistem Absensi</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Navigation -->
        <nav class="bg-blue-600 text-white shadow-lg">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <div class="flex items-center">
                        <h1 class="text-xl font-bold">📚 Sistem Absensi QR Code</h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('attendance.dashboard') }}" class="hover:bg-blue-700 px-3 py-2 rounded">Dashboard</a>
                        <a href="{{ route('attendance.scanner') }}" class="hover:bg-blue-700 px-3 py-2 rounded">Scanner</a>
                        <a href="{{ route('attendance.students.index') }}" class="hover:bg-blue-700 px-3 py-2 rounded">Siswa</a>
                        <a href="{{ route('attendance.classes.index') }}" class="hover:bg-blue-700 px-3 py-2 rounded">Kelas</a>
                        <a href="{{ route('attendance.reports.daily') }}" class="bg-blue-800 px-3 py-2 rounded">Laporan</a>
                        <a href="{{ route('attendance.settings.index') }}" class="hover:bg-blue-700 px-3 py-2 rounded">Pengaturan</a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <!-- Header & Filters -->
            <div class="bg-white shadow-lg rounded-lg p-6 mb-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">📅 Laporan Absensi Harian</h2>
                
                <form method="GET" action="{{ route('attendance.reports.daily') }}" class="flex flex-wrap gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
                        <input type="date" 
                               name="date" 
                               value="{{ $date }}"
                               class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kelas</label>
                        <select name="class_id" 
                                class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Semua Kelas</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ $classId == $class->id ? 'selected' : '' }}>
                                    {{ $class->nama_kelas }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="flex items-end">
                        <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                            🔍 Filter
                        </button>
                    </div>
                </form>
            </div>

            <!-- Stats Summary -->
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
                @php
                    $stats = [
                        'hadir' => $records->where('status', 'hadir')->count(),
                        'terlambat' => $records->where('status', 'terlambat')->count(),
                        'sakit' => $records->where('status', 'sakit')->count(),
                        'izin' => $records->where('status', 'izin')->count(),
                        'alpha' => $records->where('status', 'alpha')->count(),
                    ];
                @endphp
                
                <div class="bg-green-50 p-4 rounded-lg shadow border border-green-200">
                    <div class="text-2xl font-bold text-green-700">{{ $stats['hadir'] }}</div>
                    <div class="text-sm text-green-600">✅ Hadir</div>
                </div>
                <div class="bg-yellow-50 p-4 rounded-lg shadow border border-yellow-200">
                    <div class="text-2xl font-bold text-yellow-700">{{ $stats['terlambat'] }}</div>
                    <div class="text-sm text-yellow-600">⏰ Terlambat</div>
                </div>
                <div class="bg-blue-50 p-4 rounded-lg shadow border border-blue-200">
                    <div class="text-2xl font-bold text-blue-700">{{ $stats['sakit'] }}</div>
                    <div class="text-sm text-blue-600">🤒 Sakit</div>
                </div>
                <div class="bg-purple-50 p-4 rounded-lg shadow border border-purple-200">
                    <div class="text-2xl font-bold text-purple-700">{{ $stats['izin'] }}</div>
                    <div class="text-sm text-purple-600">📝 Izin</div>
                </div>
                <div class="bg-red-50 p-4 rounded-lg shadow border border-red-200">
                    <div class="text-2xl font-bold text-red-700">{{ $stats['alpha'] }}</div>
                    <div class="text-sm text-red-600">❌ Alpha</div>
                </div>
            </div>

            <!-- Attendance Records -->
            <div class="bg-white shadow-lg rounded-lg overflow-hidden mb-6">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">Siswa yang Sudah Absen ({{ $records->count() }})</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Foto</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">NIS</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kelas</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jam Masuk</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jam Pulang</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($records as $index => $record)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $index + 1 }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($record->check_in_photo)
                                            <img src="{{ $record->check_in_photo_url }}" 
                                                 class="w-12 h-12 rounded-full object-cover border-2 border-green-500"
                                                 alt="Foto">
                                        @else
                                            <div class="w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center">
                                                <span class="text-gray-500 text-xs">No Photo</span>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $record->student->nis }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $record->student->nama }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $record->student->kelas->nama_kelas }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $record->check_in_time ? \Carbon\Carbon::parse($record->check_in_time)->format('H:i') : '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $record->check_out_time ? \Carbon\Carbon::parse($record->check_out_time)->format('H:i') : '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $statusColors = [
                                                'hadir' => 'bg-green-100 text-green-800',
                                                'terlambat' => 'bg-yellow-100 text-yellow-800',
                                                'sakit' => 'bg-blue-100 text-blue-800',
                                                'izin' => 'bg-purple-100 text-purple-800',
                                                'alpha' => 'bg-red-100 text-red-800',
                                            ];
                                            $statusIcons = [
                                                'hadir' => '✅',
                                                'terlambat' => '⏰',
                                                'sakit' => '🤒',
                                                'izin' => '📝',
                                                'alpha' => '❌',
                                            ];
                                        @endphp
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $statusColors[$record->status] ?? '' }}">
                                            {{ $statusIcons[$record->status] ?? '' }} {{ ucfirst($record->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                                        Belum ada siswa yang absen hari ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Absent Students -->
            @if($absentStudents->count() > 0)
                <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                    <div class="px-6 py-4 bg-red-50 border-b border-red-200">
                        <h3 class="text-lg font-semibold text-red-800">⚠️ Siswa Belum Absen ({{ $absentStudents->count() }})</h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($absentStudents as $student)
                                <div class="flex items-center space-x-3 p-3 bg-red-50 rounded-lg border border-red-200">
                                    <div class="flex-shrink-0">
                                        @if($student->foto_profil)
                                            <img src="{{ Storage::url($student->foto_profil) }}" 
                                                 class="w-10 h-10 rounded-full object-cover"
                                                 alt="Foto">
                                        @else
                                            <div class="w-10 h-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                <span class="text-gray-600 text-sm">{{ substr($student->nama, 0, 2) }}</span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ $student->nama }}</p>
                                        <p class="text-xs text-gray-500">{{ $student->nis }} - {{ $student->kelas->nama_kelas }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</body>
</html>
