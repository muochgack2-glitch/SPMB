<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Bulanan - Sistem Absensi</title>
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
            <!-- Header & Filters -->
            <div class="bg-white shadow-lg rounded-lg p-6 mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl font-bold text-gray-800">📆 Laporan Bulanan</h2>
                    <a href="{{ route('attendance.reports.daily') }}" 
                       class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                        ← Kembali
                    </a>
                </div>
                
                <form method="GET" action="{{ route('attendance.reports.monthly') }}" class="flex flex-wrap gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Bulan</label>
                        <input type="month" 
                               name="month" 
                               value="{{ $month }}"
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

            <!-- Monthly Summary Table -->
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">
                        Rekapitulasi Absensi - {{ \Carbon\Carbon::parse($month)->format('F Y') }}
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase sticky left-0 bg-gray-50">No</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase sticky left-12 bg-gray-50">NIS</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kelas</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase bg-green-50">✅ Hadir</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase bg-yellow-50">⏰ Terlambat</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase bg-blue-50">🤒 Sakit</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase bg-purple-50">📝 Izin</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase bg-red-50">❌ Alpha</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Total</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">%</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @php
                                $totalHadir = 0;
                                $totalTerlambat = 0;
                                $totalSakit = 0;
                                $totalIzin = 0;
                                $totalAlpha = 0;
                                $totalRecords = 0;
                            @endphp
                            
                            @forelse($summary as $index => $item)
                                @php
                                    $totalHadir += $item['hadir'];
                                    $totalTerlambat += $item['terlambat'];
                                    $totalSakit += $item['sakit'];
                                    $totalIzin += $item['izin'];
                                    $totalAlpha += $item['alpha'];
                                    $totalRecords += $item['total'];
                                    
                                    $daysInMonth = \Carbon\Carbon::parse($month)->daysInMonth;
                                    $percentage = $daysInMonth > 0 ? round(($item['total'] / $daysInMonth) * 100, 1) : 0;
                                @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 sticky left-0 bg-white">{{ $index + 1 }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 sticky left-12 bg-white">
                                        {{ $item['student']->nis }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item['student']->nama }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item['student']->kelas->nama_kelas }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium text-green-700 bg-green-50">
                                        {{ $item['hadir'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium text-yellow-700 bg-yellow-50">
                                        {{ $item['terlambat'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium text-blue-700 bg-blue-50">
                                        {{ $item['sakit'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium text-purple-700 bg-purple-50">
                                        {{ $item['izin'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium text-red-700 bg-red-50">
                                        {{ $item['alpha'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-bold text-gray-900">
                                        {{ $item['total'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900">
                                        {{ $percentage }}%
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="px-6 py-8 text-center text-gray-500">
                                        <div class="text-4xl mb-2">📭</div>
                                        <p>Tidak ada data absensi untuk bulan ini.</p>
                                    </td>
                                </tr>
                            @endforelse
                            
                            @if($summary->count() > 0)
                                <tr class="bg-gray-100 font-bold">
                                    <td colspan="4" class="px-6 py-4 text-sm text-gray-900">TOTAL</td>
                                    <td class="px-6 py-4 text-center text-sm text-green-700 bg-green-100">{{ $totalHadir }}</td>
                                    <td class="px-6 py-4 text-center text-sm text-yellow-700 bg-yellow-100">{{ $totalTerlambat }}</td>
                                    <td class="px-6 py-4 text-center text-sm text-blue-700 bg-blue-100">{{ $totalSakit }}</td>
                                    <td class="px-6 py-4 text-center text-sm text-purple-700 bg-purple-100">{{ $totalIzin }}</td>
                                    <td class="px-6 py-4 text-center text-sm text-red-700 bg-red-100">{{ $totalAlpha }}</td>
                                    <td class="px-6 py-4 text-center text-sm text-gray-900">{{ $totalRecords }}</td>
                                    <td class="px-6 py-4"></td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            @if($summary->count() > 0)
                <div class="mt-4 text-sm text-gray-600 text-center">
                    Menampilkan data {{ $summary->count() }} siswa
                </div>
            @endif
        </div>
    </div>
</body>
</html>
