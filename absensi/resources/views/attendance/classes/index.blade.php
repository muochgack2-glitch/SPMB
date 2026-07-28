<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Kelas - Sistem Absensi</title>
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
                        <a href="{{ route('attendance.students.index') }}" class="hover:bg-blue-700 px-3 py-2 rounded">Siswa</a>
                        <a href="{{ route('attendance.classes.index') }}" class="bg-blue-800 px-3 py-2 rounded">Kelas</a>
                        <a href="{{ route('attendance.reports.daily') }}" class="hover:bg-blue-700 px-3 py-2 rounded">Laporan</a>
                        <a href="{{ route('attendance.settings.index') }}" class="hover:bg-blue-700 px-3 py-2 rounded">Pengaturan</a>
                    </div>
                </div>
            </div>
        </nav>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-3xl font-bold text-gray-900">Manajemen Kelas</h2>
                    <p class="text-gray-600 mt-1">Kelola data kelas dan QR code absensi</p>
                </div>
                <a href="{{ route('attendance.classes.create') }}" 
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow flex items-center">
                    ➕ Tambah Kelas
                </a>
            </div>

            <!-- Classes Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($classes as $class)
                <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">{{ $class->nama_kelas }}</h3>
                            <p class="text-sm text-gray-600">Tingkat {{ $class->tingkat }} - {{ $class->jurusan }}</p>
                        </div>
                        @if($class->is_active)
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                            Aktif
                        </span>
                        @else
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                            Tidak Aktif
                        </span>
                        @endif
                    </div>

                    <div class="border-t pt-4 mb-4">
                        <div class="text-center">
                            <div class="text-3xl font-bold text-blue-600">
                                {{ $class->students_count ?? 0 }}
                            </div>
                            <div class="text-sm text-gray-600">Siswa</div>
                        </div>
                    </div>

                    <div class="flex space-x-2">
                        <a href="{{ route('attendance.classes.edit', $class->id) }}" 
                           class="flex-1 text-center px-3 py-2 bg-blue-100 hover:bg-blue-200 text-blue-700 rounded-lg text-sm">
                            ✏️ Edit
                        </a>
                        <form action="{{ route('attendance.classes.destroy', $class->id) }}" 
                              method="POST" 
                              onsubmit="return confirm('Yakin hapus kelas ini?')"
                              class="flex-1">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="w-full px-3 py-2 bg-red-100 hover:bg-red-200 text-red-700 rounded-lg text-sm">
                                🗑️ Hapus
                            </button>
                        </form>
                    </div>
                </div>
                @empty
                <div class="col-span-full bg-white rounded-lg shadow p-12 text-center">
                    <div class="text-6xl mb-4">📚</div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Belum Ada Kelas</h3>
                    <p class="text-gray-600 mb-4">Mulai dengan menambahkan kelas pertama</p>
                    <a href="{{ route('attendance.classes.create') }}" 
                       class="inline-block px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                        ➕ Tambah Kelas Pertama
                    </a>
                </div>
                @endforelse
            </div>

            @if($classes->hasPages())
            <div class="mt-6">
                {{ $classes->links() }}
            </div>
            @endif
        </div>
    </div>
</body>
</html>
