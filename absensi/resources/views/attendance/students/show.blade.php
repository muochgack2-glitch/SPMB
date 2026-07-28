<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Siswa - Sistem Absensi</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <!-- Header -->
        <nav class="bg-blue-600 text-white shadow-lg">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <div class="flex items-center">
                        <h1 class="text-xl font-bold">📚 Sistem Absensi QR Code</h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('attendance.dashboard') }}" class="hover:bg-blue-700 px-3 py-2 rounded">Dashboard</a>
                        <a href="{{ route('attendance.scanner') }}" class="hover:bg-blue-700 px-3 py-2 rounded">Scanner</a>
                        <a href="{{ route('attendance.students.index') }}" class="bg-blue-800 px-3 py-2 rounded">Siswa</a>
                        <a href="{{ route('attendance.classes.index') }}" class="hover:bg-blue-700 px-3 py-2 rounded">Kelas</a>
                        <a href="{{ route('attendance.reports.daily') }}" class="hover:bg-blue-700 px-3 py-2 rounded">Laporan</a>
                        <a href="{{ route('attendance.settings.index') }}" class="hover:bg-blue-700 px-3 py-2 rounded">Pengaturan</a>
                    </div>
                </div>
            </div>
        </nav>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Back Button -->
            <div class="mb-6">
                <a href="{{ route('attendance.students.index') }}" 
                   class="text-blue-600 hover:text-blue-800 inline-flex items-center">
                    ← Kembali ke Daftar Siswa
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column: Student Info -->
                <div class="lg:col-span-1">
                    <!-- Profile Card -->
                    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                        <div class="text-center">
                            @if($student->foto_profil)
                            <img src="{{ Storage::url($student->foto_profil) }}" 
                                 alt="{{ $student->nama }}"
                                 class="w-32 h-32 rounded-full object-cover mx-auto mb-4 border-4 border-blue-100">
                            @else
                            <div class="w-32 h-32 rounded-full bg-gray-300 flex items-center justify-center text-gray-600 font-bold text-4xl mx-auto mb-4">
                                {{ substr($student->nama, 0, 1) }}
                            </div>
                            @endif

                            <h2 class="text-2xl font-bold text-gray-900 mb-1">{{ $student->nama }}</h2>
                            <p class="text-gray-600 mb-2">NIS: {{ $student->nis }}</p>
                            
                            @if($student->is_active)
                            <span class="px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800">
                                ✓ Aktif
                            </span>
                            @else
                            <span class="px-3 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800">
                                ✗ Tidak Aktif
                            </span>
                            @endif
                        </div>

                        <div class="mt-6 space-y-3 border-t pt-4">
                            <div class="flex items-start">
                                <div class="text-gray-500 w-32">Kelas:</div>
                                <div class="font-medium text-gray-900">
                                    {{ $student->kelas->tingkat }} {{ $student->kelas->nama_kelas }}
                                </div>
                            </div>
                            <div class="flex items-start">
                                <div class="text-gray-500 w-32">Jurusan:</div>
                                <div class="font-medium text-gray-900">
                                    {{ $student->kelas->jurusan ?? '-' }}
                                </div>
                            </div>
                            <div class="flex items-start">
                                <div class="text-gray-500 w-32">HP Ortu:</div>
                                <div class="font-medium text-gray-900">
                                    {{ $student->no_hp_ortu ?? '-' }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 pt-4 border-t">
                            <a href="{{ route('attendance.students.edit', $student->id) }}" 
                               class="block w-full text-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg mb-2">
                                ✏️ Edit Data
                            </a>
                            @if($student->qr_code_path)
                            <a href="{{ route('attendance.qr.show', $student->nis) }}" 
                               class="block w-full text-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg"
                               target="_blank">
                                🔳 Lihat QR Code
                            </a>
                            @endif
                        </div>
                    </div>

                    <!-- QR Code Card -->
                    @if($student->qr_code_path)
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">QR Code Absensi</h3>
                        <div class="text-center">
                            <img src="{{ Storage::url($student->qr_code_path) }}" 
                                 alt="QR Code {{ $student->nis }}"
                                 class="w-48 h-48 mx-auto border-2 border-gray-200 rounded">
                            <a href="{{ route('attendance.qr.download', $student->nis) }}" 
                               class="mt-4 inline-block px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm">
                                💾 Download QR
                            </a>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Right Column: Attendance History -->
                <div class="lg:col-span-2">
                    <!-- Stats Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                        <div class="bg-white rounded-lg shadow p-4">
                            <div class="text-sm text-gray-600">Hadir</div>
                            <div class="text-2xl font-bold text-green-600">
                                {{ $student->attendanceRecords->where('status', 'hadir')->count() }}
                            </div>
                        </div>
                        <div class="bg-white rounded-lg shadow p-4">
                            <div class="text-sm text-gray-600">Terlambat</div>
                            <div class="text-2xl font-bold text-yellow-600">
                                {{ $student->attendanceRecords->where('status', 'terlambat')->count() }}
                            </div>
                        </div>
                        <div class="bg-white rounded-lg shadow p-4">
                            <div class="text-sm text-gray-600">Sakit/Izin</div>
                            <div class="text-2xl font-bold text-blue-600">
                                {{ $student->attendanceRecords->whereIn('status', ['sakit', 'izin'])->count() }}
                            </div>
                        </div>
                        <div class="bg-white rounded-lg shadow p-4">
                            <div class="text-sm text-gray-600">Alpha</div>
                            <div class="text-2xl font-bold text-red-600">
                                {{ $student->attendanceRecords->where('status', 'alpha')->count() }}
                            </div>
                        </div>
                    </div>

                    <!-- Attendance History -->
                    <div class="bg-white rounded-lg shadow-md">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-xl font-bold text-gray-900">Riwayat Absensi Terakhir</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jam Masuk</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jam Pulang</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Foto</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($student->attendanceRecords as $record)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $record->date->format('d M Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $record->check_in_time ? $record->check_in_time->format('H:i') : '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $record->check_out_time ? $record->check_out_time->format('H:i') : '-' }}
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
                                                $color = $statusColors[$record->status] ?? 'bg-gray-100 text-gray-800';
                                            @endphp
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $color }}">
                                                {{ ucfirst($record->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <div class="flex space-x-2">
                                                @if($record->check_in_photo)
                                                <img src="{{ Storage::url($record->check_in_photo) }}" 
                                                     alt="Check In"
                                                     class="w-10 h-10 rounded object-cover cursor-pointer border-2 border-green-300"
                                                     onclick="viewPhoto('{{ Storage::url($record->check_in_photo) }}', 'Check In')">
                                                @endif
                                                @if($record->check_out_photo)
                                                <img src="{{ Storage::url($record->check_out_photo) }}" 
                                                     alt="Check Out"
                                                     class="w-10 h-10 rounded object-cover cursor-pointer border-2 border-blue-300"
                                                     onclick="viewPhoto('{{ Storage::url($record->check_out_photo) }}', 'Check Out')">
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                            <div class="text-4xl mb-2">📭</div>
                                            <p>Belum ada riwayat absensi</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Photo Modal -->
    <div id="photoModal" class="hidden fixed inset-0 bg-black bg-opacity-75 z-50 flex items-center justify-center p-4">
        <div class="relative max-w-4xl w-full">
            <button onclick="closePhotoModal()" 
                    class="absolute -top-10 right-0 text-white text-4xl hover:text-gray-300">
                ×
            </button>
            <div class="bg-white rounded-lg p-4">
                <h3 id="photoTitle" class="text-lg font-bold mb-4"></h3>
                <img id="photoImage" src="" alt="Photo" class="w-full rounded">
            </div>
        </div>
    </div>

    <script>
        function viewPhoto(url, type) {
            document.getElementById('photoImage').src = url;
            document.getElementById('photoTitle').textContent = 'Foto ' + type;
            document.getElementById('photoModal').classList.remove('hidden');
        }

        function closePhotoModal() {
            document.getElementById('photoModal').classList.add('hidden');
        }

        // Close modal on click outside
        document.getElementById('photoModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closePhotoModal();
            }
        });

        // Close modal on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closePhotoModal();
            }
        });
    </script>
</body>
</html>
