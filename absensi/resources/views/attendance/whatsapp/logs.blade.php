<x-app-layout>
    <x-slot name="title">Message Logs - WhatsApp Gateway</x-slot>
    <x-slot name="pageTitle">WA Message Logs</x-slot>

    <div class="max-w-7xl mx-auto space-y-6">
        {{-- Page Header --}}
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">📨 Message Logs</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">Riwayat pesan WhatsApp yang telah dikirim</p>
            </div>
            <a href="{{ route('whatsapp.index') }}" 
               class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white">
                <i class="fas fa-arrow-left mr-2"></i>
                Kembali ke Dashboard
            </a>
        </div>

        {{-- Statistics Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <x-card class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 border-2 border-blue-200 dark:border-blue-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-blue-800 dark:text-blue-200">Total</p>
                        <p class="text-2xl font-bold text-blue-900 dark:text-blue-100">{{ number_format($stats['total']) }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-blue-500 dark:bg-blue-600 flex items-center justify-center text-white text-xl">
                        <i class="fas fa-envelope"></i>
                    </div>
                </div>
            </x-card>

            <x-card class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 border-2 border-green-200 dark:border-green-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-green-800 dark:text-green-200">Terkirim</p>
                        <p class="text-2xl font-bold text-green-900 dark:text-green-100">{{ number_format($stats['sent']) }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-green-500 dark:bg-green-600 flex items-center justify-center text-white text-xl">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </x-card>

            <x-card class="bg-gradient-to-br from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-800/20 border-2 border-red-200 dark:border-red-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-red-800 dark:text-red-200">Gagal</p>
                        <p class="text-2xl font-bold text-red-900 dark:text-red-100">{{ number_format($stats['failed']) }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-red-500 dark:bg-red-600 flex items-center justify-center text-white text-xl">
                        <i class="fas fa-times-circle"></i>
                    </div>
                </div>
            </x-card>

            <x-card class="bg-gradient-to-br from-yellow-50 to-yellow-100 dark:from-yellow-900/20 dark:to-yellow-800/20 border-2 border-yellow-200 dark:border-yellow-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Pending</p>
                        <p class="text-2xl font-bold text-yellow-900 dark:text-yellow-100">{{ number_format($stats['pending']) }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-yellow-500 dark:bg-yellow-600 flex items-center justify-center text-white text-xl">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </x-card>

            <x-card class="bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 border-2 border-purple-200 dark:border-purple-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-purple-800 dark:text-purple-200">Hari Ini</p>
                        <p class="text-2xl font-bold text-purple-900 dark:text-purple-100">{{ number_format($stats['today']) }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-purple-500 dark:bg-purple-600 flex items-center justify-center text-white text-xl">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                </div>
            </x-card>
        </div>

        {{-- Filter Card --}}
        <x-card>
            <form method="GET" action="{{ route('whatsapp.logs') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                {{-- Search --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Cari</label>
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" 
                           placeholder="Nomor HP atau Nama Siswa"
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                </div>

                {{-- Status Filter --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                    <select name="status" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="">Semua Status</option>
                        <option value="sent" {{ ($filters['status'] ?? '') === 'sent' ? 'selected' : '' }}>Terkirim</option>
                        <option value="failed" {{ ($filters['status'] ?? '') === 'failed' ? 'selected' : '' }}>Gagal</option>
                        <option value="pending" {{ ($filters['status'] ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
                    </select>
                </div>

                {{-- Type Filter --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tipe</label>
                    <select name="type" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="">Semua Tipe</option>
                        <option value="manual" {{ ($filters['type'] ?? '') === 'manual' ? 'selected' : '' }}>Manual</option>
                        <option value="auto_checkin" {{ ($filters['type'] ?? '') === 'auto_checkin' ? 'selected' : '' }}>Auto Check-In</option>
                        <option value="auto_checkout" {{ ($filters['type'] ?? '') === 'auto_checkout' ? 'selected' : '' }}>Auto Check-Out</option>
                        <option value="auto_alpha" {{ ($filters['type'] ?? '') === 'auto_alpha' ? 'selected' : '' }}>Auto Alpha</option>
                        <option value="broadcast" {{ ($filters['type'] ?? '') === 'broadcast' ? 'selected' : '' }}>Broadcast</option>
                    </select>
                </div>

                {{-- Date Range --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tanggal</label>
                    <div class="flex gap-2">
                        <input type="date" name="start_date" value="{{ $filters['start_date'] ?? '' }}" 
                               class="w-full px-2 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white text-sm">
                        <input type="date" name="end_date" value="{{ $filters['end_date'] ?? '' }}" 
                               class="w-full px-2 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white text-sm">
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex items-end gap-2">
                    <button type="submit" 
                            class="flex-1 px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-lg transition-all duration-200 font-medium">
                        <i class="fas fa-filter mr-2"></i>
                        Filter
                    </button>
                    <a href="{{ route('whatsapp.logs') }}" 
                       class="px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg transition-all duration-200">
                        <i class="fas fa-redo"></i>
                    </a>
                </div>
            </form>
        </x-card>

        {{-- Messages Table --}}
        <x-card>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Waktu</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Siswa</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">No HP</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pesan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tipe</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($messages as $message)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ $message->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($message->student)
                                        <div>
                                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $message->student->nama }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $message->student->kelas->nama ?? '-' }}</p>
                                        </div>
                                    @else
                                        <span class="text-sm text-gray-500 dark:text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ $message->phone }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100 max-w-md">
                                    <div class="truncate" title="{{ $message->message }}">
                                        {{ Str::limit($message->message, 50) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                                        {{ $message->type_label }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($message->status === 'sent')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            Terkirim
                                        </span>
                                    @elseif($message->status === 'failed')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300">
                                            <i class="fas fa-times-circle mr-1"></i>
                                            Gagal
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300">
                                            <i class="fas fa-clock mr-1"></i>
                                            Pending
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <button onclick="showMessageDetail({{ json_encode($message) }})"
                                            class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300">
                                        <i class="fas fa-eye mr-1"></i>
                                        Detail
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center text-gray-500 dark:text-gray-400">
                                        <i class="fas fa-inbox text-6xl mb-4 opacity-20"></i>
                                        <p class="text-lg font-medium">Tidak ada data</p>
                                        <p class="text-sm">Belum ada pesan yang dikirim</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($messages->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                    {{ $messages->links() }}
                </div>
            @endif
        </x-card>
    </div>

    {{-- Message Detail Modal --}}
    <div id="messageDetailModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black opacity-50" onclick="closeMessageDetail()"></div>
            
            <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full p-6">
                <div class="flex justify-between items-start mb-4">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">Detail Pesan</h3>
                    <button onclick="closeMessageDetail()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <div id="messageDetailContent" class="space-y-4">
                    <!-- Content will be populated by JavaScript -->
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function showMessageDetail(message) {
            const modal = document.getElementById('messageDetailModal');
            const content = document.getElementById('messageDetailContent');
            
            let studentInfo = message.student 
                ? `<div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                       <p class="text-sm text-gray-600 dark:text-gray-400">Siswa</p>
                       <p class="font-medium text-gray-900 dark:text-white">${message.student.nama}</p>
                       <p class="text-sm text-gray-500 dark:text-gray-400">${message.student.kelas?.nama || '-'}</p>
                   </div>`
                : '<p class="text-gray-500 dark:text-gray-400 italic">Tidak terkait siswa</p>';

            let errorInfo = message.error_message
                ? `<div class="p-3 bg-red-50 dark:bg-red-900/20 border-2 border-red-200 dark:border-red-700 rounded-lg">
                       <p class="text-sm font-medium text-red-700 dark:text-red-300 mb-1">Error Message:</p>
                       <p class="text-sm text-red-600 dark:text-red-400">${message.error_message}</p>
                   </div>`
                : '';

            content.innerHTML = `
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Waktu Kirim</p>
                        <p class="font-medium text-gray-900 dark:text-white">${new Date(message.created_at).toLocaleString('id-ID')}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Status</p>
                        <p class="font-medium text-gray-900 dark:text-white">${message.status_label}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">No HP</p>
                        <p class="font-medium text-gray-900 dark:text-white">${message.phone}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Tipe</p>
                        <p class="font-medium text-gray-900 dark:text-white">${message.type_label}</p>
                    </div>
                </div>

                ${studentInfo}

                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Isi Pesan</p>
                    <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <p class="text-gray-900 dark:text-white whitespace-pre-wrap">${message.message}</p>
                    </div>
                </div>

                ${errorInfo}
            `;

            modal.classList.remove('hidden');
        }

        function closeMessageDetail() {
            document.getElementById('messageDetailModal').classList.add('hidden');
        }

        // Close modal on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeMessageDetail();
            }
        });
    </script>
    @endpush
</x-app-layout>
