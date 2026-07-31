@php
    $pageTitle = 'Detail Siswa - ' . $student->nama;
    $breadcrumbs = [
        ['label' => 'Data Siswa', 'url' => route('attendance.students.index')],
        ['label' => $student->nama]
    ];
@endphp

<x-app-layout>
    <div class="space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Left Column: Student Info --}}
            <div class="lg:col-span-1 space-y-6">
                {{-- Profile Card --}}
                <x-card>
                    <div class="text-center">
                        @if($student->foto_profil)
                        <img src="{{ Storage::url($student->foto_profil) }}" 
                             alt="{{ $student->nama }}"
                             class="w-32 h-32 rounded-full object-cover mx-auto mb-4 border-4 border-primary-500 shadow-lg">
                        @else
                        <div class="w-32 h-32 rounded-full bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center text-white font-bold text-4xl mx-auto mb-4 shadow-lg">
                            {{ substr($student->nama, 0, 1) }}
                        </div>
                        @endif

                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">{{ $student->nama }}</h2>
                        <p class="text-gray-600 dark:text-gray-400 mb-3">NIS: {{ $student->nis }}</p>
                        
                        @if($student->is_active)
                        <x-badge variant="success" class="text-sm">
                            <i class="fas fa-check-circle mr-1"></i> Aktif
                        </x-badge>
                        @else
                        <x-badge variant="danger" class="text-sm">
                            <i class="fas fa-times-circle mr-1"></i> Tidak Aktif
                        </x-badge>
                        @endif
                    </div>

                    <div class="mt-6 space-y-4 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white mr-3">
                                <i class="fas fa-school"></i>
                            </div>
                            <div class="flex-1">
                                <div class="text-sm text-gray-500 dark:text-gray-400">Kelas</div>
                                <div class="font-semibold text-gray-900 dark:text-white">
                                    {{ $student->kelas->nama_kelas }}
                                </div>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-gradient-to-br from-purple-500 to-purple-600 flex items-center justify-center text-white mr-3">
                                <i class="fas fa-book"></i>
                            </div>
                            <div class="flex-1">
                                <div class="text-sm text-gray-500 dark:text-gray-400">Jurusan</div>
                                <div class="font-semibold text-gray-900 dark:text-white">
                                    {{ $student->kelas->jurusan ?? '-' }}
                                </div>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-gradient-to-br from-green-500 to-green-600 flex items-center justify-center text-white mr-3">
                                <i class="fab fa-whatsapp"></i>
                            </div>
                            <div class="flex-1">
                                <div class="text-sm text-gray-500 dark:text-gray-400">HP Orang Tua</div>
                                <div class="font-semibold text-gray-900 dark:text-white">
                                    {{ $student->no_hp_ortu ?? '-' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700 space-y-2">
                        <a href="{{ route('attendance.students.edit', $student->id) }}" 
                           class="block w-full text-center px-4 py-2.5 bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white rounded-lg transition-all duration-200 shadow-md hover:shadow-lg font-medium">
                            <i class="fas fa-edit mr-2"></i> Edit Data
                        </a>
                        @if($student->qr_code_path)
                        <a href="{{ route('attendance.qr.show', $student->nis) }}" 
                           class="block w-full text-center px-4 py-2.5 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white rounded-lg transition-all duration-200 shadow-md hover:shadow-lg font-medium"
                           target="_blank">
                            <i class="fas fa-qrcode mr-2"></i> Lihat QR Code
                        </a>
                        @endif
                    </div>
                </x-card>

                {{-- QR Code Card --}}
                @if($student->qr_code_path)
                <x-card>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                        <i class="fas fa-qrcode mr-2 text-primary-500"></i>
                        QR Code Absensi
                    </h3>
                    <div class="text-center">
                        <div class="bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900 p-4 rounded-lg inline-block">
                            <img src="{{ Storage::url($student->qr_code_path) }}" 
                                 alt="QR Code {{ $student->nis }}"
                                 class="w-48 h-48 mx-auto border-2 border-primary-500 rounded">
                        </div>
                        <a href="{{ route('attendance.qr.download', $student->nis) }}" 
                           class="mt-4 inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg text-sm transition-all duration-200 shadow hover:shadow-md">
                            <i class="fas fa-download mr-2"></i> Download QR
                        </a>
                    </div>
                </x-card>
                @endif
            </div>

            {{-- Right Column: Attendance History --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Stats Cards --}}
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <x-stat-card
                        title="Hadir"
                        :value="$student->attendanceRecords->where('status', 'hadir')->count()"
                        icon="fa-check-circle"
                        color="green"
                    />
                    <x-stat-card
                        title="Terlambat"
                        :value="$student->attendanceRecords->where('status', 'terlambat')->count()"
                        icon="fa-clock"
                        color="yellow"
                    />
                    <x-stat-card
                        title="Sakit/Izin"
                        :value="$student->attendanceRecords->whereIn('status', ['sakit', 'izin'])->count()"
                        icon="fa-notes-medical"
                        color="blue"
                    />
                    <x-stat-card
                        title="Alpha"
                        :value="$student->attendanceRecords->where('status', 'alpha')->count()"
                        icon="fa-times-circle"
                        color="red"
                    />
                </div>

                {{-- Attendance History --}}
                <x-card>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-6 flex items-center">
                        <i class="fas fa-history mr-2 text-primary-500"></i>
                        Riwayat Absensi Terakhir
                    </h3>
                    
                    @if($student->attendanceRecords->count() > 0)
                    <div class="overflow-x-auto">
                        <x-table>
                            <x-table.header>
                                <th>Tanggal</th>
                                <th>Jam Masuk</th>
                                <th>Jam Pulang</th>
                                <th>Status</th>
                                <th>Foto</th>
                            </x-table.header>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($student->attendanceRecords as $record)
                                <x-table.row>
                                    <x-table.cell>
                                        <div class="font-medium">{{ $record->date->format('d M Y') }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $record->date->format('l') }}</div>
                                    </x-table.cell>
                                    <x-table.cell>
                                        {{ $record->check_in_time ? substr($record->check_in_time, 0, 5) : '-' }}
                                    </x-table.cell>
                                    <x-table.cell>
                                        {{ $record->check_out_time ? substr($record->check_out_time, 0, 5) : '-' }}
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
                                    <x-table.cell>
                                        <div class="flex gap-2">
                                            @if($record->check_in_photo)
                                            <img src="{{ Storage::url($record->check_in_photo) }}" 
                                                 alt="Check In"
                                                 class="w-12 h-12 rounded-lg object-cover cursor-pointer border-2 border-green-300 dark:border-green-700 hover:scale-110 transition-transform"
                                                 data-photo-url="{{ Storage::url($record->check_in_photo) }}"
                                                 data-photo-title="Check In - {{ $record->date->format('d M Y') }}"
                                                 onclick="viewPhoto(this.dataset.photoUrl, this.dataset.photoTitle)">
                                            @endif
                                            @if($record->check_out_photo)
                                            <img src="{{ Storage::url($record->check_out_photo) }}" 
                                                 alt="Check Out"
                                                 class="w-12 h-12 rounded-lg object-cover cursor-pointer border-2 border-blue-300 dark:border-blue-700 hover:scale-110 transition-transform"
                                                 data-photo-url="{{ Storage::url($record->check_out_photo) }}"
                                                 data-photo-title="Check Out - {{ $record->date->format('d M Y') }}"
                                                 onclick="viewPhoto(this.dataset.photoUrl, this.dataset.photoTitle)">
                                            @endif
                                        </div>
                                    </x-table.cell>
                                </x-table.row>
                                @endforeach
                            </tbody>
                        </x-table>
                    </div>
                    @else
                    <x-empty-state
                        icon="fa-calendar-times"
                        title="Belum Ada Riwayat"
                        message="Siswa ini belum memiliki riwayat absensi"
                    />
                    @endif
                </x-card>
            </div>
        </div>
    </div>

    {{-- Photo Modal --}}
    <x-modal id="photoModal">
        <div class="p-6">
            <h3 id="photoTitle" class="text-lg font-bold text-gray-900 dark:text-white mb-4"></h3>
            <img id="photoImage" src="" alt="Photo" class="w-full rounded-lg">
        </div>
    </x-modal>

    @push('scripts')
    <script>
        function viewPhoto(url, title) {
            document.getElementById('photoImage').src = url;
            document.getElementById('photoTitle').textContent = title;
            document.getElementById('photoModal').classList.remove('hidden');
        }

        // Close modal on click outside
        document.getElementById('photoModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.add('hidden');
            }
        });

        // Close modal on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.getElementById('photoModal')?.classList.add('hidden');
            }
        });
    </script>
    @endpush
</x-app-layout>

