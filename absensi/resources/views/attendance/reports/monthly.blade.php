<x-app-layout>
    <x-slot name="title">Laporan Bulanan</x-slot>
    <x-slot name="pageTitle">Laporan Bulanan</x-slot>

    <div class="space-y-6">
        {{-- Page Header with Filters --}}
        <x-card>
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple-500 to-purple-600 flex items-center justify-center text-white text-2xl mr-4">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">📆 Laporan Bulanan</h2>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Rekapitulasi absensi siswa per bulan</p>
                    </div>
                </div>
                <a href="{{ route('attendance.reports.index') }}" 
                   class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200 border-2 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali
                </a>
            </div>

            {{-- Filters --}}
            <form method="GET" action="{{ route('attendance.reports.monthly') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <x-input
                    type="month"
                    name="month"
                    label="Bulan"
                    :value="$month"
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

        {{-- Monthly Summary Table --}}
        <x-card>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6 flex items-center">
                <i class="fas fa-table mr-2 text-primary-500"></i>
                Rekapitulasi Absensi - {{ \Carbon\Carbon::parse($month)->format('F Y') }}
            </h3>
            
            @if($summary->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider sticky left-0 bg-gray-50 dark:bg-gray-800">No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">NIS</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kelas</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider bg-green-50 dark:bg-green-900/20">
                                <i class="fas fa-check-circle text-green-600 dark:text-green-400"></i> Hadir
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider bg-yellow-50 dark:bg-yellow-900/20">
                                <i class="fas fa-clock text-yellow-600 dark:text-yellow-400"></i> Terlambat
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider bg-blue-50 dark:bg-blue-900/20">
                                <i class="fas fa-notes-medical text-blue-600 dark:text-blue-400"></i> Sakit
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider bg-purple-50 dark:bg-purple-900/20">
                                <i class="fas fa-file-alt text-purple-600 dark:text-purple-400"></i> Izin
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider bg-red-50 dark:bg-red-900/20">
                                <i class="fas fa-times-circle text-red-600 dark:text-red-400"></i> Alpha
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">%</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                        @php
                            $totalHadir = 0;
                            $totalTerlambat = 0;
                            $totalSakit = 0;
                            $totalIzin = 0;
                            $totalAlpha = 0;
                            $totalRecords = 0;
                        @endphp
                        
                        @foreach($summary as $index => $item)
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
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 sticky left-0 bg-white dark:bg-gray-900">{{ $index + 1 }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-medium text-gray-900 dark:text-white font-mono">{{ $item['student']->nis }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $item['student']->nama }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $item['student']->kelas->nama_kelas }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-bold text-green-700 dark:text-green-400 bg-green-50 dark:bg-green-900/20">
                                    {{ $item['hadir'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-bold text-yellow-700 dark:text-yellow-400 bg-yellow-50 dark:bg-yellow-900/20">
                                    {{ $item['terlambat'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-bold text-blue-700 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20">
                                    {{ $item['sakit'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-bold text-purple-700 dark:text-purple-400 bg-purple-50 dark:bg-purple-900/20">
                                    {{ $item['izin'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-bold text-red-700 dark:text-red-400 bg-red-50 dark:bg-red-900/20">
                                    {{ $item['alpha'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-bold text-gray-900 dark:text-white">
                                    {{ $item['total'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900 dark:text-white">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $percentage >= 80 ? 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300' }}">
                                        {{ $percentage }}%
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                        
                        {{-- Total Row --}}
                        <tr class="bg-gradient-to-r from-gray-100 to-gray-200 dark:from-gray-800 dark:to-gray-700 font-bold">
                            <td colspan="4" class="px-6 py-4 text-sm text-gray-900 dark:text-white uppercase tracking-wider">TOTAL</td>
                            <td class="px-6 py-4 text-center text-sm text-green-700 dark:text-green-300 bg-green-100 dark:bg-green-900/30">{{ $totalHadir }}</td>
                            <td class="px-6 py-4 text-center text-sm text-yellow-700 dark:text-yellow-300 bg-yellow-100 dark:bg-yellow-900/30">{{ $totalTerlambat }}</td>
                            <td class="px-6 py-4 text-center text-sm text-blue-700 dark:text-blue-300 bg-blue-100 dark:bg-blue-900/30">{{ $totalSakit }}</td>
                            <td class="px-6 py-4 text-center text-sm text-purple-700 dark:text-purple-300 bg-purple-100 dark:bg-purple-900/30">{{ $totalIzin }}</td>
                            <td class="px-6 py-4 text-center text-sm text-red-700 dark:text-red-300 bg-red-100 dark:bg-red-900/30">{{ $totalAlpha }}</td>
                            <td class="px-6 py-4 text-center text-sm text-gray-900 dark:text-white">{{ $totalRecords }}</td>
                            <td class="px-6 py-4"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4 text-sm text-gray-600 dark:text-gray-400 text-center">
                Menampilkan data <span class="font-semibold">{{ $summary->count() }}</span> siswa
            </div>
            @else
            <x-empty-state
                icon="fa-calendar-times"
                title="Tidak Ada Data"
                message="Tidak ada data absensi untuk bulan ini"
            />
            @endif
        </x-card>
    </div>
</x-app-layout>
