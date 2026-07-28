<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code - {{ $student->nama }}</title>
    
    {{-- Tailwind CSS CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            .print-only {
                display: block !important;
            }
            body {
                background: white !important;
            }
        }
        
        .print-only {
            display: none;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto py-8 px-4">
        {{-- Header (no-print) --}}
        <div class="no-print mb-6">
            <a href="{{ route('attendance.students.show', $student) }}" class="text-blue-600 hover:text-blue-800 flex items-center">
                ← Kembali ke Detail Siswa
            </a>
        </div>

        {{-- QR Card --}}
        <div class="max-w-md mx-auto bg-white rounded-lg shadow-lg p-8">
            {{-- School Header --}}
            <div class="text-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800 mb-2">
                    {{ \App\Models\AttendanceSetting::get('school_name', 'SMK Negeri 1') }}
                </h1>
                <p class="text-gray-600">Sistem Absensi Siswa</p>
            </div>

            {{-- Divider --}}
            <div class="border-t-2 border-gray-200 my-6"></div>

            {{-- Student Info --}}
            <div class="text-center mb-6">
                @if($student->foto_profil)
                <div class="mb-4">
                    <img 
                        src="{{ Storage::url($student->foto_profil) }}" 
                        alt="{{ $student->nama }}"
                        class="w-24 h-24 rounded-full mx-auto object-cover border-4 border-gray-200"
                    >
                </div>
                @endif
                
                <h2 class="text-xl font-bold text-gray-800 mb-1">{{ $student->nama }}</h2>
                <p class="text-gray-600">NIS: {{ $student->nis }}</p>
                <p class="text-gray-600">Kelas: {{ $student->kelas->nama_kelas ?? '-' }}</p>
            </div>

            {{-- QR Code --}}
            <div class="bg-white p-6 rounded-lg border-4 border-gray-300">
                @if($student->qr_code_path && Storage::exists($student->qr_code_path))
                    <div class="flex justify-center">
                        <div class="bg-white p-4">
                            {!! Storage::get($student->qr_code_path) !!}
                        </div>
                    </div>
                @else
                    <div class="text-center py-12 text-gray-500">
                        <p class="text-4xl mb-2">⚠️</p>
                        <p>QR Code belum di-generate</p>
                    </div>
                @endif
            </div>

            {{-- Instructions --}}
            <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h3 class="font-bold text-blue-900 mb-2">📋 Cara Penggunaan:</h3>
                <ol class="list-decimal list-inside text-blue-800 text-sm space-y-1">
                    <li>Tunjukkan QR Code ini pada scanner saat absensi</li>
                    <li>Pastikan QR Code terlihat jelas dan tidak terlipat</li>
                    <li>Tunggu konfirmasi dari sistem setelah scan</li>
                    <li>Simpan QR Code ini dengan baik</li>
                </ol>
            </div>

            {{-- Print Date (print-only) --}}
            <div class="print-only mt-6 text-center text-xs text-gray-500">
                Dicetak pada: {{ now()->format('d/m/Y H:i') }}
            </div>

            {{-- Action Buttons (no-print) --}}
            <div class="no-print mt-6 flex gap-3">
                <a 
                    href="{{ route('attendance.qr.download', $student->nis) }}" 
                    class="flex-1 bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-4 rounded-lg text-center transition"
                >
                    📥 Download PNG
                </a>
                
                <button 
                    onclick="window.print()" 
                    class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition"
                >
                    🖨️ Print
                </button>
            </div>

            @can('regenerate-qr')
            <div class="no-print mt-3">
                <form 
                    action="{{ route('attendance.qr.regenerate', $student->nis) }}" 
                    method="POST"
                    onsubmit="return confirm('Yakin ingin regenerate QR Code? QR Code lama akan diganti.')"
                >
                    @csrf
                    <button 
                        type="submit"
                        class="w-full bg-yellow-600 hover:bg-yellow-700 text-white font-semibold py-3 px-4 rounded-lg transition"
                    >
                        🔄 Regenerate QR Code
                    </button>
                </form>
            </div>
            @endcan
        </div>

        {{-- Info Note (no-print) --}}
        <div class="no-print max-w-md mx-auto mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <p class="text-yellow-800 text-sm">
                💡 <strong>Tips:</strong> Simpan gambar QR Code ini di ponsel atau print untuk kemudahan absensi harian.
            </p>
        </div>
    </div>

    <script>
        // Auto-print functionality for mobile
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('auto_print') === '1') {
            window.onload = function() {
                setTimeout(() => {
                    window.print();
                }, 500);
            };
        }
    </script>
</body>
</html>
