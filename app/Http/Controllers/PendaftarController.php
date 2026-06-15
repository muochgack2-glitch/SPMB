<?php

namespace App\Http\Controllers;

use App\Models\Pendaftar;
use App\Models\LogistikBayar;
use App\Models\Jurusan;
use App\Models\SettingSystem;
use App\Services\TahunAjaranService;
use Illuminate\Http\Request;
use Twilio\Rest\Client;
use Illuminate\Support\Carbon;
use App\Exports\PendaftarExport;
use Maatwebsite\Excel\Facades\Excel;

class PendaftarController extends Controller
{
    protected $tahunAjaranService;
    protected $whatsappService;
    
    public function __construct(TahunAjaranService $tahunAjaranService, \App\Services\WhatsAppService $whatsappService)
    {
        $this->tahunAjaranService = $tahunAjaranService;
        $this->whatsappService = $whatsappService;
    }
    /**
     * Send WhatsApp message via Twilio.
     */
    protected function sendWhatsApp($to, $message)
    {
        $sid = env('TWILIO_SID');
        $token = env('TWILIO_AUTH_TOKEN');
        $from = env('TWILIO_WHATSAPP_FROM'); // e.g., whatsapp:+14155238886
        if (!$sid || !$token || !$from) {
            // Twilio credentials not set; skip sending.
            return false;
        }
        $client = new Client($sid, $token);
        try {
            $client->messages->create($to, ['from' => $from, 'body' => $message]);
            return true;
        } catch (\Exception $e) {
            // Log error if needed
            return false;
        }
    }



    /**
     * Generate unique registration number using TahunAjaranService
     */
    private function generateRegistrationNumber()
    {
        try {
            // Get active tahun ajaran
            $activeTahun = SettingSystem::get('active_tahun_ajaran', '2026/2027');
            
            // Generate using service
            return $this->tahunAjaranService->generateNomorRegistrasi($activeTahun);
        } catch (\Exception $e) {
            // Fallback to old format if service fails
            \Log::error('Failed to generate registration number', [
                'error' => $e->getMessage()
            ]);
            
            $tahun = Carbon::now()->year;
            $lastRegistration = Pendaftar::where('no_registrasi', 'like', 'SPMB-' . $tahun . '-%')
                ->orderByRaw('CAST(SUBSTRING(no_registrasi, -4) AS UNSIGNED) DESC')
                ->first();

            $nextNumber = 1;
            if ($lastRegistration) {
                $lastNumber = (int) substr($lastRegistration->no_registrasi, -4);
                $nextNumber = $lastNumber + 1;
            }

            return 'SPMB-' . $tahun . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        }
    }

    /**
     * Show the form for creating a new pendaftar (Admin)
     */
    public function create()
    {
        $jurusans = Jurusan::where('aktif', true)->orderBy('kode')->get();
        return view('pendaftar.create', compact('jurusans'));
    }

    /**
     * Store a newly created pendaftar in database (Admin manual entry)
     */
    public function store(Request $request)
    {
        // Send WhatsApp notification after successful registration
        $request->validate([
            'nisn' => 'required|string|unique:pendaftar',
            'nama_lengkap' => 'required|string',
            'no_telepon' => 'required|string|max:20',
            'asal_sekolah' => 'required|string',
            'alamat' => 'required|string',
            'jurusan_id' => 'required|exists:jurusan,id',
            'nama_jaringan' => 'nullable|string',
        ]);

        $setting = SettingSystem::first();
        $noRegistrasi = $this->generateRegistrationNumber();
        $activeTahun = SettingSystem::get('active_tahun_ajaran', '2026/2027');

        // Auto-fill nama_jaringan dengan "PANITIA" jika kosong
        $namaJaringan = $request->nama_jaringan;
        if (empty($namaJaringan)) {
            $namaJaringan = 'PANITIA';
        }

        // Create pendaftar
        $pendaftar = Pendaftar::create([
            'no_registrasi' => $noRegistrasi,
            'nisn' => $request->nisn,
            'nama_lengkap' => $request->nama_lengkap,
            'no_telepon' => $request->no_telepon,
            'asal_sekolah' => $request->asal_sekolah,
            'alamat' => $request->alamat,
            'jurusan_id' => (int) $request->jurusan_id,
            'jurusan' => Jurusan::find($request->jurusan_id)?->kode ?? '-',
            'nama_jaringan' => $namaJaringan,
            'gelombang' => $setting->gelombang_aktif,
            'status_siswa' => 'Belum Daftar Ulang',
            'status_data' => 'awal',
            'tahun_ajaran' => $activeTahun,
        ]);

        // Create logistik entry
        LogistikBayar::create([
            'id_pendaftar' => $pendaftar->id_pendaftar,
            'status_bayar' => 'Belum',
            'status_kain' => 'Belum',
            'status_kaos' => 'Belum',
        ]);

        // Notify admin via WhatsApp
        $this->sendWhatsApp(env('ADMIN_WHATSAPP'), "🆕 Registrasi baru: {$pendaftar->nama_lengkap} (No: {$noRegistrasi})");

        // Auto-send welcome message to student via WhatsApp
        if (!empty($pendaftar->no_telepon)) {
            try {
                $jurusanData = Jurusan::find($request->jurusan_id);
                
                // Prepare data for template
                $templateData = [
                    'nama' => $pendaftar->nama_lengkap,
                    'no_pendaftaran' => $noRegistrasi,
                    'jurusan' => $jurusanData ? "{$jurusanData->kode} - {$jurusanData->nama}" : $pendaftar->jurusan,
                    'portal_url' => url('/'),
                    'sekolah' => config('app.name', 'SMK PGRI Blora'),
                ];

                // Format phone number (add 62 if starts with 0)
                $phone = $pendaftar->no_telepon;
                if (substr($phone, 0, 1) === '0') {
                    $phone = '62' . substr($phone, 1);
                }

                // Send WhatsApp using template
                $result = $this->whatsappService->sendWithTemplate(
                    $phone,
                    'welcome_registration',
                    $templateData,
                    [
                        'pendaftar_id' => $pendaftar->id_pendaftar,
                        'type' => 'auto_registration',
                        'sent_by' => auth()->id(),
                    ]
                );

                // Add success/fail message to session
                if ($result['success']) {
                    session()->flash('wa_sent', true);
                    session()->flash('wa_phone', $pendaftar->no_telepon);
                } else {
                    session()->flash('wa_failed', true);
                    session()->flash('wa_error', $result['message'] ?? 'Gagal mengirim WhatsApp');
                }
            } catch (\Exception $e) {
                \Log::error('Failed to send welcome WhatsApp', [
                    'pendaftar_id' => $pendaftar->id_pendaftar,
                    'error' => $e->getMessage(),
                ]);
                session()->flash('wa_failed', true);
                session()->flash('wa_error', $e->getMessage());
            }
        }

        return redirect()->route('pendaftar.index')
            ->with('success', 'Pendaftar ' . $pendaftar->nama_lengkap . ' berhasil dibuat dengan nomor registrasi ' . $noRegistrasi . '.')
            ->with('created_pendaftar_id', $pendaftar->id_pendaftar)
            ->with('created_pendaftar_name', $pendaftar->nama_lengkap)
            ->with('created_pendaftar_no', $noRegistrasi);
    }

    /**
     * Show list of all pendaftar (Admin)
     */
    public function index(Request $request)
    {
        // Get tahun from URL parameter or default to active year
        $activeTahun = SettingSystem::get('active_tahun_ajaran', '2026/2027');
        $selectedTahun = $request->get('tahun', $activeTahun);
        
        // Get available tahun ajaran for dropdown (if needed in future)
        $availableTahun = \App\Models\TahunAjaran::orderBy('tahun', 'desc')->pluck('tahun');
        
        // FILTER BY SELECTED YEAR (default: active year)
        $query = Pendaftar::with('logistik')
            ->where('tahun_ajaran', $selectedTahun);
        
        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_lengkap', 'like', '%' . $search . '%')
                  ->orWhere('no_registrasi', 'like', '%' . $search . '%')
                  ->orWhere('nisn', 'like', '%' . $search . '%');
            });
        }
        
        // Jurusan filter
        if ($request->filled('jurusan')) {
            $query->where('jurusan', $request->jurusan);
        }
        
        // Gelombang filter
        if ($request->filled('gelombang')) {
            $query->where('gelombang', $request->gelombang);
        }
        
        // Status Siswa filter
        if ($request->filled('status_siswa')) {
            $query->where('status_siswa', $request->status_siswa);
        }
        
        // Status Data filter
        if ($request->filled('status_data')) {
            $query->where('status_data', $request->status_data);
        }
        
        // Jaringan filter
        if ($request->filled('jaringan')) {
            $query->where('nama_jaringan', $request->jaringan);
        }
        
        // Order by latest
        $query->orderBy('id_pendaftar', 'desc');
        
        // Get per_page value (default 20)
        $perPage = $request->get('per_page', 20);
        $perPage = in_array($perPage, [10, 20, 50, 100]) ? $perPage : 20;
        
        $pendaftars = $query->paginate($perPage)->appends($request->except('page'));
        
        return view('pendaftar.index', compact('pendaftars', 'activeTahun', 'selectedTahun', 'availableTahun'));
    }

    /**
     * Show list specifically for payment verification workflow.
     */
    public function verificationIndex(Request $request)
    {
        // Get active tahun ajaran
        $activeTahun = SettingSystem::get('active_tahun_ajaran', '2026/2027');
        
        // FILTER BY ACTIVE YEAR ONLY
        $query = Pendaftar::with(['logistik', 'masterJurusan'])
            ->where('tahun_ajaran', $activeTahun);
        
        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_lengkap', 'like', '%' . $search . '%')
                  ->orWhere('no_registrasi', 'like', '%' . $search . '%');
            });
        }
        
        // Status filter
        if ($request->filled('status')) {
            $query->where('status_siswa', $request->status);
        }
        
        // Jurusan filter
        if ($request->filled('jurusan')) {
            $query->where('jurusan', $request->jurusan);
        }
        
        // Order by latest
        $query->orderBy('id_pendaftar', 'desc');
        
        // Get per_page value (default 20)
        $perPage = $request->get('per_page', 20);
        $perPage = in_array($perPage, [10, 20, 50, 100]) ? $perPage : 20;
        
        $pendaftars = $query->paginate($perPage)->appends($request->except('page'));
        $jurusans = Jurusan::where('aktif', true)->orderBy('kode')->get();
        return view('pendaftar.verification-index', compact('pendaftars', 'jurusans'));
    }

    /**
     * Show the form for editing the specified pendaftar
     */
    public function edit(Pendaftar $pendaftar)
    {
        $jurusans = Jurusan::where('aktif', true)->orderBy('kode')->get();
        return view('pendaftar.edit', compact('pendaftar', 'jurusans'));
    }

    /**
     * Update the specified pendaftar in database
     */
    public function update(Request $request, Pendaftar $pendaftar)
    {
        // Store old phone number BEFORE validation
        $oldPhone = $pendaftar->no_telepon;
        
        $validated = $request->validate([
            'nisn' => 'required|string|unique:pendaftar,nisn,' . $pendaftar->id_pendaftar . ',id_pendaftar',
            'nik' => 'nullable|string|max:20',
            'no_kip' => 'nullable|string|max:40',
            'nama_lengkap' => 'required|string|max:100',
            'email' => 'nullable|email|max:100',
            'no_telepon' => 'nullable|string|max:20',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'nullable|in:L,P',
            'agama' => 'nullable|string|max:50',
            'asal_sekolah' => 'required|string|max:100',
            'tahun_lulus' => 'nullable|string|max:10',
            'alamat' => 'required|string',
            'alamat_jalan' => 'nullable|string|max:255',
            'alamat_dukuh' => 'nullable|string|max:120',
            'alamat_rt' => 'nullable|string|max:10',
            'alamat_rw' => 'nullable|string|max:10',
            'alamat_kelurahan' => 'nullable|string|max:120',
            'alamat_kecamatan' => 'nullable|string|max:120',
            'alamat_kabupaten' => 'nullable|string|max:120',
            'alamat_provinsi' => 'nullable|string|max:120',
            'nama_ayah' => 'nullable|string|max:100',
            'pekerjaan_ayah' => 'nullable|string|max:100',
            'alamat_ayah' => 'nullable|string|max:255',
            'nama_ibu' => 'nullable|string|max:100',
            'pekerjaan_ibu' => 'nullable|string|max:100',
            'alamat_ibu' => 'nullable|string|max:255',
            'no_hp_ortu' => 'nullable|string|max:20',
            'nama_wali' => 'nullable|string|max:100',
            'pekerjaan_wali' => 'nullable|string|max:100',
            'no_hp_wali' => 'nullable|string|max:20',
            'alamat_wali' => 'nullable|string|max:255',
            'jurusan_id' => 'required|exists:jurusan,id',
            'nama_jaringan' => 'nullable|string|max:100',
            'gelombang' => 'nullable|string|max:50',
            'status_data' => 'required|in:awal,lengkap,terverifikasi',
        ]);

        $jurusan = Jurusan::find((int) $validated['jurusan_id']);
        $validated['jurusan'] = $jurusan?->kode ?? $pendaftar->jurusan;

        // Auto-fill nama_jaringan dengan "PANITIA" jika kosong saat update
        if (empty($validated['nama_jaringan'])) {
            $validated['nama_jaringan'] = 'PANITIA';
        }

        $pendaftar->update($validated);

        // Auto-send welcome WhatsApp if phone number was just added
        $newPhone = $validated['no_telepon'] ?? null;
        $phoneWasAdded = empty($oldPhone) && !empty($newPhone);
        
        if ($phoneWasAdded) {
            try {
                $jurusanData = Jurusan::find($validated['jurusan_id']);
                
                // Prepare data for template
                $templateData = [
                    'nama' => $pendaftar->nama_lengkap,
                    'no_pendaftaran' => $pendaftar->no_registrasi,
                    'jurusan' => $jurusanData ? "{$jurusanData->kode} - {$jurusanData->nama}" : $pendaftar->jurusan,
                    'portal_url' => url('/'),
                    'sekolah' => config('app.name', 'SMK PGRI Blora'),
                ];

                // Format phone number (add 62 if starts with 0)
                $phone = $newPhone;
                if (substr($phone, 0, 1) === '0') {
                    $phone = '62' . substr($phone, 1);
                }

                // Send WhatsApp using template
                $result = $this->whatsappService->sendWithTemplate(
                    $phone,
                    'welcome_registration',
                    $templateData,
                    [
                        'pendaftar_id' => $pendaftar->id_pendaftar,
                        'type' => 'phone_added',
                        'sent_by' => auth()->id(),
                    ]
                );

                // Add success/fail message to session
                if ($result['success']) {
                    session()->flash('wa_sent', true);
                    session()->flash('wa_phone', $newPhone);
                } else {
                    session()->flash('wa_failed', true);
                    session()->flash('wa_error', $result['message'] ?? 'Gagal mengirim WhatsApp');
                }
            } catch (\Exception $e) {
                \Log::error('Failed to send welcome WhatsApp on update', [
                    'pendaftar_id' => $pendaftar->id_pendaftar,
                    'error' => $e->getMessage(),
                ]);
                session()->flash('wa_failed', true);
                session()->flash('wa_error', $e->getMessage());
            }
        }

        return redirect()->route('pendaftar.index')
            ->with('success', 'Data pendaftar berhasil diperbarui');
    }

    /**
     * Show daftar ulang verification form
     */
    public function showDaftarUlangVerification($id)
    {
        $pendaftar = Pendaftar::findOrFail($id);
        $logistik = $pendaftar->logistik;

        return view('pendaftar.daftar-ulang-verification', compact('pendaftar', 'logistik'));
    }

    /**
     * Process daftar ulang verification and t-shirt size selection
     */
    public function processDaftarUlang(Request $request, $id)
    {
        $request->validate([
            'ukuran_kaos' => 'required|in:S,M,L,XL,XXL,JUMBO',
        ]);

        $pendaftar = Pendaftar::findOrFail($id);
        $logistik = $pendaftar->logistik;

        // Update logistik data
        $logistik->update([
            'status_bayar' => 'Lunas',
            'ukuran_kaos' => $request->ukuran_kaos,
            'status_kain' => 'Sudah',
            'status_kaos' => 'Proses',
        ]);

        // Update status siswa ketika daftar ulang berhasil
        $pendaftar->update([
            'status_siswa' => 'Diterima',
            'status_data' => $pendaftar->status_data === 'awal' ? 'lengkap' : $pendaftar->status_data,
        ]);

        // Notify applicant via WhatsApp after daftar ulang verification
        $this->sendWhatsApp(env('ADMIN_WHATSAPP'), "✅ Daftar ulang berhasil untuk pendaftar ID: {$id}");

        return redirect()->route('pendaftar.daftar-ulang', $pendaftar->id_pendaftar)
            ->with('success', 'Daftar ulang berhasil diverifikasi untuk ' . $pendaftar->nama_lengkap . '.');
    }

    /**
     * Cancel daftar ulang verification (set back to not verified).
     */
    public function cancelDaftarUlang($id)
    {
        $pendaftar = Pendaftar::findOrFail($id);
        $logistik = $pendaftar->logistik;

        $logistik->update([
            'status_bayar' => 'Belum',
            'ukuran_kaos' => null,
            'status_kain' => 'Belum',
            'status_kaos' => 'Belum',
        ]);

        // Kembalikan status siswa saat daftar ulang dibatalkan
        $pendaftar->update([
            'status_siswa' => 'Belum Daftar Ulang',
        ]);

        $this->sendWhatsApp(env('ADMIN_WHATSAPP'), "↩️ Verifikasi daftar ulang dibatalkan untuk pendaftar ID: {$id}");

        return redirect()->route('pendaftar.verification-index')
            ->with('success', 'Status daftar ulang dikembalikan ke Belum Daftar Ulang untuk ' . $pendaftar->nama_lengkap . '.')
            ->with('rollback_success', true);
    }

    /**
     * Print Bukti Registrasi
     */
    public function printRegistrasi($id)
    {
        $pendaftar = Pendaftar::with(['logistik', 'masterJurusan'])->findOrFail($id);
        return view('pendaftar.print-registrasi', compact('pendaftar'));
    }

    /**
     * Print Formulir Lengkap
     */
    public function printFormulir($id)
    {
        $pendaftar = Pendaftar::with(['logistik', 'masterJurusan'])->findOrFail($id);
        return view('pendaftar.print-formulir', compact('pendaftar'));
    }

    /**
     * Print Bukti Ambil Barang
     */
    public function printAmbilBarang($id)
    {
        $pendaftar = Pendaftar::with(['logistik', 'masterJurusan'])->findOrFail($id);
        return view('pendaftar.print-ambil-barang', compact('pendaftar'));
    }

    /**
     * Bulk delete pendaftar
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:pendaftar,id_pendaftar'
        ]);

        try {
            $ids = $request->ids;
            
            // Delete related logistik records first
            LogistikBayar::whereIn('id_pendaftar', $ids)->delete();
            
            // Delete pendaftar records
            $count = Pendaftar::whereIn('id_pendaftar', $ids)->delete();

            return response()->json([
                'success' => true,
                'count' => $count,
                'message' => "Berhasil menghapus {$count} pendaftar"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export pendaftar to Excel
     */
    public function exportExcel()
    {
        $filename = 'Data_Pendaftar_' . date('Y-m-d_His') . '.xlsx';
        return Excel::download(new PendaftarExport, $filename);
    }

    /**
     * Export pendaftar to PDF
     */
    public function exportPdf()
    {
        // Get active tahun ajaran
        $activeTahun = SettingSystem::get('active_tahun_ajaran', '2026/2027');
        
        // FILTER BY ACTIVE YEAR ONLY
        $pendaftars = Pendaftar::with(['logistik', 'masterJurusan'])
            ->where('tahun_ajaran', $activeTahun)
            ->get();
            
        $settings = SettingSystem::first();
        
        return view('pendaftar.export-pdf', compact('pendaftars', 'settings'));
    }

    /**
     * Soft delete pendaftar (Admin only)
     */
    public function destroy(Request $request, $id)
    {
        try {
            $pendaftar = Pendaftar::findOrFail($id);
            
            // Store who deleted and reason
            $pendaftar->deleted_by = auth()->id();
            $pendaftar->deleted_reason = $request->input('reason', 'Dihapus oleh administrator');
            $pendaftar->save();
            
            // Soft delete
            $pendaftar->delete();
            
            return redirect()->route('pendaftar.index')
                ->with('success', 'Pendaftar ' . $pendaftar->nama_lengkap . ' (No. ' . $pendaftar->no_registrasi . ') berhasil dihapus');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menghapus pendaftar: ' . $e->getMessage());
        }
    }

    /**
     * Restore soft deleted pendaftar (Admin only)
     */
    public function restore($id)
    {
        try {
            $pendaftar = Pendaftar::withTrashed()->findOrFail($id);
            $pendaftar->restore();
            
            return redirect()->route('pendaftar.index')
                ->with('success', 'Pendaftar ' . $pendaftar->nama_lengkap . ' berhasil dipulihkan');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal memulihkan pendaftar: ' . $e->getMessage());
        }
    }

    /**
     * Show deleted pendaftars (Admin only)
     */
    public function trashed(Request $request)
    {
        // Get active tahun ajaran
        $activeTahun = SettingSystem::get('active_tahun_ajaran', '2026/2027');
        
        // FILTER BY ACTIVE YEAR ONLY
        $query = Pendaftar::onlyTrashed()
            ->with(['logistik', 'deletedBy'])
            ->where('tahun_ajaran', $activeTahun);
        
        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_lengkap', 'like', '%' . $search . '%')
                  ->orWhere('no_registrasi', 'like', '%' . $search . '%')
                  ->orWhere('nisn', 'like', '%' . $search . '%');
            });
        }
        
        $query->orderBy('deleted_at', 'desc');
        
        $perPage = $request->get('per_page', 20);
        $perPage = in_array($perPage, [10, 20, 50, 100]) ? $perPage : 20;
        
        $pendaftars = $query->paginate($perPage)->appends($request->except('page'));
        
        return view('pendaftar.trashed', compact('pendaftars'));
    }
}
