<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Kelas - Sistem Absensi</title>
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
                        <a href="{{ route('attendance.classes.index') }}" class="bg-blue-800 px-3 py-2 rounded">Kelas</a>
                    </div>
                </div>
            </div>
        </nav>

        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="mb-6">
                <a href="{{ route('attendance.classes.index') }}" 
                   class="text-blue-600 hover:text-blue-800 inline-flex items-center">
                    ← Kembali ke Daftar Kelas
                </a>
            </div>

            <div class="bg-white rounded-lg shadow-md p-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Edit Kelas</h2>

                <form method="POST" action="{{ route('attendance.classes.update', $class->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Kelas *</label>
                            <input type="text" 
                                   name="nama_kelas" 
                                   value="{{ old('nama_kelas', $class->nama_kelas) }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                   required>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tingkat *</label>
                                <select name="tingkat" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                                        required>
                                    <option value="10" {{ $class->tingkat == 10 ? 'selected' : '' }}>10</option>
                                    <option value="11" {{ $class->tingkat == 11 ? 'selected' : '' }}>11</option>
                                    <option value="12" {{ $class->tingkat == 12 ? 'selected' : '' }}>12</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Jurusan</label>
                                <input type="text" 
                                       name="jurusan" 
                                       value="{{ old('jurusan', $class->jurusan) }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            </div>
                        </div>

                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       name="is_active" 
                                       value="1"
                                       {{ $class->is_active ? 'checked' : '' }}
                                       class="w-4 h-4 text-blue-600 border-gray-300 rounded">
                                <span class="ml-2 text-sm text-gray-700">Kelas Aktif</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 mt-8">
                        <a href="{{ route('attendance.classes.index') }}" 
                           class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                            Batal
                        </a>
                        <button type="submit" 
                                class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                            Update Kelas
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
