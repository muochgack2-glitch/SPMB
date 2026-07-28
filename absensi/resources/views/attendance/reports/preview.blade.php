<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview Laporan - Sistem Absensi</title>
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
            <!-- Header -->
            <div class="bg-white shadow-lg rounded-lg p-6 mb-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">Preview Laporan Absensi</h2>
                        <p class="text-gray-600 mt-1">
                            Periode: {{ \Carbon\Carbon::parse($validated['start_date'])->format('d M Y') }} - 
                            {{ \Carbon\Carbon::parse($validated['end_date'])->format('d M Y') }}
                        </p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('attendance.reports.daily') }}" 
                           class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                            ← Kembali
                        </a>
                        <form action="{{ route('attendance.reports.generate') }}" method="POST" class="inline">
                            @csrf
                            <input type="hidden" name="start_date" value="{{ $validated['start_date'] }}">
                            <input type="hidden" name="end_date" value="{{ $validated['end_date'] }}">
                            <input type="hidden" name="class_id" value="{{ $validated['class_id'] ?? '' }}">
                            <input type="hidden" name="status" value="{{ $validated['status'] ?? '' }}">
                            <input type="hidden" name="format" value="excel">
                            <button type="submit" 
                                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                                📥 Export ke Excel
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Summary Stats -->
            <div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-6">
                <div class="bg-white p-4 rounded-lg shadow">
                    <div class="text-2xl font-bold text-gray-800">{{ $summary['total_records'] }}</div>
                    <div class="text-sm text-gray-600">Total Record</div>
                </div>
                <div class="bg-green-50 p-4 rounded-lg shadow border border-green-200">
                    <div class="text-2xl font-bold text-green-700">{{ $summary['hadir'] }}</div>
                    <div class="text-sm text-green-600">✅ Hadir</div>
                </div>
                <div class="bg-yellow-50 p-4 rounded-lg shadow border border-yellow-200">
                    <div class="text-2xl font-bold text-yellow-700">{{ $summary['terlambat'] }}</div>
                    <div class="text-sm text-yellow-600">⏰ Terlambat</div>
                </div>
                <div class="bg-blue-50 p-4 rounded-lg shadow border border-blue-200">
                    <div class="text-2xl font-bold text-blue-700">{{ $summary['sakit'] }}</div>
                    <div class="text-sm text-blue-600">🤒 Sakit</div>
                </div>
                <div class="bg-purple-50 p-4 rounded-lg shadow border border-purple-200">
                    <div class="text-2xl font-bold text-purple-700">{{ $summary['izin'] }}</div>
                    <div class="text-sm text-purple-600">📝 Izin</div>
                </div>
                <div class="bg-red-50 p-4 rounded-lg shadow border border-red-200">
                    <div class="text-2xl font-bold text-red-700">{{ $summary['alpha'] }}</div>
                    <div class="text-sm text-red-600">❌ Alpha</div>
                </div>
            </div>

            <!-- Data Table -->
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NIS</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kelas</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jam Masuk</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jam Pulang</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Foto</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($records as $index => $record)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $index + 1 }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ \Carbon\Carbon::parse($record->date)->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $record->student->nis }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $record->student->nama }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $record->student->kelas->nama_kelas }}
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
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusColors[$record->status] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ $statusIcons[$record->status] ?? '' }} {{ ucfirst($record->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($record->check_in_photo)
                                            <span class="text-green-600">✓ Masuk</span>
                                        @endif
                                        @if($record->check_out_photo)
                                            <span class="text-blue-600">✓ Pulang</span>
                                        @endif
                                        @if(!$record->check_in_photo && !$record->check_out_photo)
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-6 py-8 text-center text-gray-500">
                                        <div class="text-4xl mb-2">📭</div>
                                        <p>Tidak ada data absensi untuk filter yang dipilih.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($records->count() > 0)
                <div class="mt-4 text-sm text-gray-600 text-center">
                    Menampilkan {{ $records->count() }} record absensi
                </div>
            @endif
        </div>
    </div>
</body>
</html>
