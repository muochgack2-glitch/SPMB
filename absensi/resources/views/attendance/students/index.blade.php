<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Siswa - Sistem Absensi</title>
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
            <!-- Flash Messages -->
            @if(session('success'))
            <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded" role="alert">
                <p class="font-bold">✓ Berhasil</p>
                <p>{{ session('success') }}</p>
            </div>
            @endif

            @if(session('error'))
            <div class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded" role="alert">
                <p class="font-bold">✗ Error</p>
                <p>{{ session('error') }}</p>
            </div>
            @endif

            <!-- Page Header -->
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h2 class="text-3xl font-bold text-gray-900">Manajemen Siswa</h2>
                    <p class="text-gray-600 mt-1">Kelola data siswa dan QR Code absensi</p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('attendance.students.create') }}?import=true" 
                       class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg shadow flex items-center">
                        📥 Import Excel
                    </a>
                    <a href="{{ route('attendance.students.create') }}" 
                       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow flex items-center">
                        ➕ Tambah Siswa
                    </a>
                </div>
            </div>

            <!-- Search & Filter -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <form method="GET" action="{{ route('attendance.students.index') }}" class="flex flex-wrap gap-4">
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Cari Siswa</label>
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}"
                               placeholder="Nama atau NIS..."
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div class="w-48">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Filter Kelas</label>
                        <select name="kelas_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Semua Kelas</option>
                            @foreach(\App\Models\AttendanceClass::orderBy('tingkat')->orderBy('nama_kelas')->get() as $class)
                            <option value="{{ $class->id }}" {{ request('kelas_id') == $class->id ? 'selected' : '' }}>
                                {{ $class->tingkat }} {{ $class->nama_kelas }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-40">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">Semua</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                            🔍 Cari
                        </button>
                    </div>
                </form>
            </div>

            <!-- Students Table -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
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
                                No HP Ortu
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                QR Code
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($students as $student)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($student->foto_profil)
                                <img src="{{ Storage::url($student->foto_profil) }}" 
                                     alt="{{ $student->nama }}"
                                     class="w-12 h-12 rounded-full object-cover">
                                @else
                                <div class="w-12 h-12 rounded-full bg-gray-300 flex items-center justify-center text-gray-600 font-bold">
                                    {{ substr($student->nama, 0, 1) }}
                                </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $student->nis }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $student->nama }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $student->kelas->tingkat }} {{ $student->kelas->nama_kelas }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $student->no_hp_ortu ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($student->qr_code_path)
                                <a href="{{ route('attendance.qr.show', $student->nis) }}" 
                                   class="text-blue-600 hover:text-blue-800 inline-flex items-center">
                                    🔳 Lihat QR
                                </a>
                                @else
                                <span class="text-gray-400">Belum ada</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($student->is_active)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Aktif
                                </span>
                                @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    Tidak Aktif
                                </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <a href="{{ route('attendance.students.show', $student->id) }}" 
                                       class="text-blue-600 hover:text-blue-900" title="Lihat Detail">
                                        👁️
                                    </a>
                                    <a href="{{ route('attendance.students.edit', $student->id) }}" 
                                       class="text-yellow-600 hover:text-yellow-900" title="Edit">
                                        ✏️
                                    </a>
                                    @if($student->qr_code_path)
                                    <a href="{{ route('attendance.qr.download', $student->nis) }}" 
                                       class="text-green-600 hover:text-green-900" title="Download QR">
                                        💾
                                    </a>
                                    @endif
                                    <form method="POST" 
                                          action="{{ route('attendance.students.destroy', $student->id) }}" 
                                          onsubmit="return confirm('Yakin ingin menghapus siswa ini?')"
                                          class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="text-red-600 hover:text-red-900" title="Hapus">
                                            🗑️
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                <div class="text-4xl mb-2">📭</div>
                                <p class="text-lg">Belum ada data siswa</p>
                                <a href="{{ route('attendance.students.create') }}" 
                                   class="text-blue-600 hover:text-blue-800 mt-2 inline-block">
                                    Tambah siswa pertama
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                    {{ $students->links() }}
                </div>
            </div>

            <!-- Stats Summary -->
            <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="text-gray-500 text-sm mb-1">Total Siswa</div>
                    <div class="text-3xl font-bold text-blue-600">{{ \App\Models\AttendanceStudent::count() }}</div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="text-gray-500 text-sm mb-1">Siswa Aktif</div>
                    <div class="text-3xl font-bold text-green-600">{{ \App\Models\AttendanceStudent::where('is_active', true)->count() }}</div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="text-gray-500 text-sm mb-1">QR Code Dibuat</div>
                    <div class="text-3xl font-bold text-purple-600">{{ \App\Models\AttendanceStudent::whereNotNull('qr_code_path')->count() }}</div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
