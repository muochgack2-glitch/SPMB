<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Absensi {{ $student->nama }} - Sistem Absensi</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Navigation -->
        <nav class="bg-white shadow-lg">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <h1 class="text-xl font-bold text-gray-800">📊 Sistem Absensi QR</h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('attendance.dashboard') }}" class="text-gray-600 hover:text-gray-900">Dashboard</a>
                        <a href="{{ route('attendance.students.index') }}" class="text-gray-600 hover:text-gray-900">Siswa</a>
                        <a href="{{ route('attendance.reports.daily') }}" class="text-blue-600 font-semibold">Laporan</a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <!-- Student Info Card -->
            <div class="bg-white shadow-lg rounded-lg p-6 mb-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        @if($student->foto_profil)
                            <img src="{{ Storage::url($student->foto_profil) }}" 
                                 class="w-20 h-20 rounded-full object-cover border-4 border-blue-500"
                                 alt="Foto Profil">
                        @else
                            <div class="w-20 h-20 rounded-full bg-blue-500 flex items-center justify-center text-white text-2xl font-bold">
                                {{ substr($student->nama, 0, 2) }}
                            </div>
                        @endif
                        
                        <div>
                            <h2 class="text-2xl font-bold text-gray-800">{{ $student->nama }}</h2>
                            <p class="text-gray-600">NIS: {{ $student->nis }}</p>
                            <p class="text-gray-600">Kelas: {{ $student->kelas->nama_kelas }}</p>
                        </div>
                    </div>
                    
                    <a href="{{ route('attendance.students.show', $student->id) }}" 
                       class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                        ← Kembali ke Profil
                    </a>
                </div>
            </div>

            <!-- Statistics -->
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
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

            <!-- Attendance History Table -->
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">📋 Riwayat Absensi</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Hari</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jam Masuk</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jam Pulang</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Foto</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($records as $index => $record)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ ($records->currentPage() - 1) * $records->perPage() + $index + 1 }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ \Carbon\Carbon::parse($record->date)->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ \Carbon\Carbon::parse($record->date)->locale('id')->isoFormat('dddd') }}
                                    </td>
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
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex space-x-2">
                                            @if($record->check_in_photo)
                                                <img src="{{ $record->check_in_photo_url }}" 
                                                     class="w-10 h-10 rounded object-cover border border-green-500 cursor-pointer hover:scale-110 transition"
                                                     alt="Check In"
                                                     title="Foto Check In">
                                            @endif
                                            @if($record->check_out_photo)
                                                <img src="{{ $record->check_out_photo_url }}" 
                                                     class="w-10 h-10 rounded object-cover border border-blue-500 cursor-pointer hover:scale-110 transition"
                                                     alt="Check Out"
                                                     title="Foto Check Out">
                                            @endif
                                            @if(!$record->check_in_photo && !$record->check_out_photo)
                                                <span class="text-gray-400 text-xs">-</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        {{ $record->notes ?? '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                                        <div class="text-4xl mb-2">📭</div>
                                        <p>Belum ada riwayat absensi.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($records->hasPages())
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                        {{ $records->links() }}
                    </div>
                @endif
            </div>

            @if($records->count() > 0)
                <div class="mt-4 text-sm text-gray-600 text-center">
                    Menampilkan {{ $records->count() }} dari {{ $records->total() }} riwayat absensi
                </div>
            @endif
        </div>
    </div>
</body>
</html>
