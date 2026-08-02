<x-app-layout>
    <x-slot name="title">Laporan Harian</x-slot>
    <x-slot name="pageTitle">Laporan Harian</x-slot>

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
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700" style="table-layout: auto;">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider" style="width: 60px;">No</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider" style="width: 100px;">NIS</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider" style="min-width: 180px;">Nama</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider" style="width: 130px;">Kelas</th>
                            <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider" style="width: 80px;">Masuk</th>
                            <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider" style="width: 80px;">Pulang</th>
                            <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider" style="width: 80px;">Foto</th>
                            <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider" style="width: 100px;">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($records as $index => $record)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <td class="px-4 py-3 text-center text-sm text-gray-900 dark:text-white">{{ $index + 1 }}</td>
                            
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white font-medium">{{ $record->student->nis }}</td>
                            
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white font-medium">{{ $record->student->nama }}</td>
                            
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $record->student->kelas->nama_kelas }}</td>
                            
                            <td class="px-4 py-3 text-center text-sm text-gray-900 dark:text-white font-mono">
                                {{ $record->check_in_time ? \Carbon\Carbon::parse($record->check_in_time)->format('H:i') : '-' }}
                            </td>
                            
                            <td class="px-4 py-3 text-center text-sm text-gray-900 dark:text-white font-mono">
                                {{ $record->check_out_time ? \Carbon\Carbon::parse($record->check_out_time)->format('H:i') : '-' }}
                            </td>
                            
                            {{-- Foto Icons --}}
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    @if($record->check_in_photo)
                                        <button onclick="showPhotoModal('{{ $record->check_in_photo_url }}', '{{ addslashes($record->student->nama) }}', 'Check In')"
                                                class="inline-flex items-center justify-center w-7 h-7 bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 rounded hover:bg-green-200 dark:hover:bg-green-900/50 transition-colors"
                                                title="Lihat foto check in">
                                            <i class="fas fa-sign-in-alt text-xs"></i>
                                        </button>
                                    @else
                                        <div class="inline-flex items-center justify-center w-7 h-7 bg-gray-100 dark:bg-gray-800 text-gray-400 rounded">
                                            <i class="fas fa-sign-in-alt text-xs"></i>
                                        </div>
                                    @endif
                                    
                                    @if($record->check_out_photo)
                                        <button onclick="showPhotoModal('{{ $record->check_out_photo_url }}', '{{ addslashes($record->student->nama) }}', 'Check Out')"
                                                class="inline-flex items-center justify-center w-7 h-7 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded hover:bg-blue-200 dark:hover:bg-blue-900/50 transition-colors"
                                                title="Lihat foto check out">
                                            <i class="fas fa-sign-out-alt text-xs"></i>
                                        </button>
                                    @else
                                        <div class="inline-flex items-center justify-center w-7 h-7 bg-gray-100 dark:bg-gray-800 text-gray-400 rounded">
                                            <i class="fas fa-sign-out-alt text-xs"></i>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            
                            <td class="px-4 py-3 text-center">
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
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
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

    {{-- Photo Modal --}}
    <div id="photoModal" class="hidden fixed inset-0 bg-black bg-opacity-75 backdrop-blur-sm z-50 flex items-center justify-center p-4" onclick="hidePhotoModal()">
        <div class="relative max-w-2xl w-full" onclick="event.stopPropagation()">
            {{-- Close Button --}}
            <button onclick="hidePhotoModal()" 
                    class="absolute -top-8 right-0 text-white hover:text-gray-300 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
            
            {{-- Photo Container --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-2xl overflow-hidden">
                {{-- Header --}}
                <div class="bg-gradient-to-r from-primary-500 to-purple-600 px-4 py-2 text-white">
                    <h3 class="text-base font-bold" id="photoModalTitle">Foto Absensi</h3>
                    <p class="text-xs opacity-90" id="photoModalSubtitle"></p>
                </div>
                
                {{-- Photo --}}
                <div class="p-3 flex items-center justify-center bg-gray-50 dark:bg-gray-900">
                    <img id="photoModalImage" src="" alt="Foto" class="max-w-full max-h-[40vh] rounded-lg shadow-lg object-contain">
                </div>
                
                {{-- Footer --}}
                <div class="px-4 py-2 bg-gray-100 dark:bg-gray-700 flex justify-between items-center">
                    <div class="text-xs text-gray-600 dark:text-gray-400">
                        <i class="fas fa-info-circle mr-1"></i>
                        Klik di luar untuk menutup
                    </div>
                    <button onclick="downloadPhoto()" 
                            class="px-3 py-1.5 bg-primary-500 hover:bg-primary-600 text-white text-xs rounded-lg transition-colors flex items-center gap-1.5">
                        <i class="fas fa-download"></i>
                        Download
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function showPhotoModal(photoUrl, studentName, type) {
            console.log('showPhotoModal called:', { photoUrl, studentName, type });
            
            const modal = document.getElementById('photoModal');
            const image = document.getElementById('photoModalImage');
            const title = document.getElementById('photoModalTitle');
            const subtitle = document.getElementById('photoModalSubtitle');
            
            // Set image source and alt
            image.src = photoUrl;
            image.alt = `Foto ${type} - ${studentName}`;
            image.onerror = function() {
                console.error('Failed to load image:', photoUrl);
                this.src = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="400" height="300"%3E%3Crect fill="%23ddd" width="400" height="300"/%3E%3Ctext fill="%23999" x="50%25" y="50%25" text-anchor="middle" dominant-baseline="middle" font-family="sans-serif" font-size="18"%3EGagal memuat foto%3C/text%3E%3C/svg%3E';
            };
            
            title.textContent = `Foto ${type}`;
            subtitle.textContent = studentName;
            
            modal.classList.remove('hidden');
            
            // Add fade-in animation
            modal.style.animation = 'fadeIn 0.2s ease-out';
        }
        
        function hidePhotoModal() {
            const modal = document.getElementById('photoModal');
            modal.style.animation = 'fadeOut 0.2s ease-out';
            
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 200);
        }
        
        function downloadPhoto() {
            const image = document.getElementById('photoModalImage');
            const link = document.createElement('a');
            link.href = image.src;
            link.download = 'foto-absensi-' + Date.now() + '.jpg';
            link.click();
        }
        
        // Close modal on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                hidePhotoModal();
            }
        });
    </script>
    
    <style>
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
        
        @keyframes fadeOut {
            from {
                opacity: 1;
            }
            to {
                opacity: 0;
            }
        }
    </style>
    @endpush
</x-app-layout>
