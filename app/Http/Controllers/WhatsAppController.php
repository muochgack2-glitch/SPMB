<?php

namespace App\Http\Controllers;

use App\Models\Pendaftar;
use App\Models\LogistikBayar;
use App\Models\Jurusan;
use App\Models\SettingSystem;
use App\Models\WhatsAppLog;
use App\Models\WhatsAppTemplate;
use App\Models\WhatsAppSetting;
use App\Models\UserActivityLog;
use App\Models\ExternalBroadcastBatch;
use App\Models\ExternalBroadcastRecipient;
use App\Services\WhatsAppService;
use App\Services\ExternalBroadcastService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class WhatsAppController extends Controller
{
    protected WhatsAppService $whatsappService;
    protected ExternalBroadcastService $externalBroadcastService;

    public function __construct(
        WhatsAppService $whatsappService,
        ExternalBroadcastService $externalBroadcastService
    ) {
        $this->whatsappService = $whatsappService;
        $this->externalBroadcastService = $externalBroadcastService;
    }

    /**
     * Dashboard WhatsApp Gateway
     */
    public function index()
    {
        $status = $this->whatsappService->getStatus();
        $statistics = $this->whatsappService->getStatistics();
        
        $recentLogs = WhatsAppLog::with(['pendaftar', 'template', 'sender'])
            ->latest()
            ->limit(10)
            ->get();

        // Get active server URL (with failover detection)
        $activeServerUrl = $this->whatsappService->getCurrentServerUrl();

        return view('whatsapp.index', compact('status', 'statistics', 'recentLogs', 'activeServerUrl'));
    }

    /**
     * Get server status (AJAX)
     */
    public function status()
    {
        $status = $this->whatsappService->getStatus();
        return response()->json($status);
    }

    /**
     * Get server health metrics (AJAX)
     */
    public function health()
    {
        $health = $this->whatsappService->getHealth();
        return response()->json($health);
    }

    /**
     * Get QR code (AJAX)
     */
    public function qrCode()
    {
        $qr = $this->whatsappService->getQRCode();
        return response()->json($qr);
    }

    /**
     * Send message page
     */
    public function sendPage()
    {
        $templates = WhatsAppTemplate::active()->get();
        return view('whatsapp.send', compact('templates'));
    }

    /**
     * Send single message
     */
    public function send(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $result = $this->whatsappService->send(
            $request->phone,
            $request->message,
            [
                'type' => 'manual',
                'sent_by' => auth()->id(),
            ]
        );

        return response()->json($result);
    }

    /**
     * Send message with template
     */
    public function sendWithTemplate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'template_id' => 'required|exists:whatsapp_templates,id',
            'data' => 'nullable|array', // Changed to nullable - template might not have variables
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $template = WhatsAppTemplate::findOrFail($request->template_id);
        
        $result = $this->whatsappService->sendWithTemplate(
            $request->phone,
            $template->name,
            $request->data,
            [
                'type' => 'manual',
                'sent_by' => auth()->id(),
            ]
        );

        return response()->json($result);
    }

    /**
     * Logs page
     */
    public function logs(Request $request)
    {
        $query = WhatsAppLog::with(['pendaftar', 'template', 'sender', 'externalBatch'])
            ->latest();

        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->has('type') && $request->type != '') {
            $query->where('type', $request->type);
        }

        // Filter by date
        if ($request->has('date') && $request->date != '') {
            $query->whereDate('created_at', $request->date);
        }

        // Search by phone
        if ($request->has('search') && $request->search != '') {
            $query->where('phone', 'like', '%' . $request->search . '%');
        }

        $perPage = $request->get('per_page', 20);
        $perPage = in_array($perPage, [10, 20, 50, 100]) ? $perPage : 20;
        
        $logs = $query->paginate($perPage)->appends($request->except('page'));

        return view('whatsapp.logs', compact('logs'));
    }

    /**
     * Templates page
     */
    public function templates()
    {
        $templates = WhatsAppTemplate::latest()->get();
        return view('whatsapp.templates', compact('templates'));
    }

    /**
     * Create template page
     */
    public function createTemplate()
    {
        return view('whatsapp.template-form');
    }

    /**
     * Store template
     */
    public function storeTemplate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:whatsapp_templates,name',
            'label' => 'required|string',
            'message' => 'required|string',
            'type' => 'required|in:registration,payment,reminder,notification,custom',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'auto_send' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        WhatsAppTemplate::create($request->all());

        return redirect()->route('whatsapp.templates')
            ->with('success', 'Template created successfully');
    }

    /**
     * Edit template page
     */
    public function editTemplate($id)
    {
        $template = WhatsAppTemplate::findOrFail($id);
        return view('whatsapp.template-form', compact('template'));
    }

    /**
     * Update template
     */
    public function updateTemplate(Request $request, $id)
    {
        $template = WhatsAppTemplate::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:whatsapp_templates,name,' . $id,
            'label' => 'required|string',
            'message' => 'required|string',
            'type' => 'required|in:registration,payment,reminder,notification,custom',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'auto_send' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $template->update($request->all());

        return redirect()->route('whatsapp.templates')
            ->with('success', 'Template updated successfully');
    }

    /**
     * Delete template
     */
    public function deleteTemplate($id)
    {
        $template = WhatsAppTemplate::findOrFail($id);
        $template->delete();

        return redirect()->route('whatsapp.templates')
            ->with('success', 'Template deleted successfully');
    }

    /**
     * Preview template
     */
    public function previewTemplate($id)
    {
        $template = WhatsAppTemplate::findOrFail($id);
        $preview = $template->getPreview();

        return response()->json([
            'success' => true,
            'preview' => $preview,
        ]);
    }

    /**
     * Settings page
     */
    public function settings()
    {
        $settings = WhatsAppSetting::orderBy('group')->get()->groupBy('group');
        return view('whatsapp.settings', compact('settings'));
    }

    /**
     * Update settings
     */
    public function updateSettings(Request $request)
    {
        foreach ($request->except('_token') as $key => $value) {
            WhatsAppSetting::set($key, $value);
        }

        // Clear cache
        WhatsAppSetting::clearCache();

        return redirect()->route('whatsapp.settings')
            ->with('success', 'Settings updated successfully');
    }

    /**
     * Broadcast page
     */
    public function broadcastPage()
    {
        $templates = WhatsAppTemplate::active()->get();
        
        // Get pendaftars with any valid phone number (siswa, ortu, or wali)
        $pendaftars = Pendaftar::where(function($query) {
            $query->where(function($q) {
                $q->whereNotNull('no_telepon')
                  ->where('no_telepon', '!=', '')
                  ->where('no_telepon', '!=', '-');
            })
            ->orWhere(function($q) {
                $q->whereNotNull('no_hp_ortu')
                  ->where('no_hp_ortu', '!=', '')
                  ->where('no_hp_ortu', '!=', '-');
            })
            ->orWhere(function($q) {
                $q->whereNotNull('no_hp_wali')
                  ->where('no_hp_wali', '!=', '')
                  ->where('no_hp_wali', '!=', '-');
            });
        })->get();
        
        // Set primary phone for each pendaftar (priority: wali > ortu > siswa)
        $pendaftars->each(function($p) {
            if (!empty($p->no_hp_wali) && $p->no_hp_wali != '-') {
                $p->primary_phone = $p->no_hp_wali;
                $p->phone_type = 'Wali';
            } elseif (!empty($p->no_hp_ortu) && $p->no_hp_ortu != '-') {
                $p->primary_phone = $p->no_hp_ortu;
                $p->phone_type = 'Orang Tua';
            } elseif (!empty($p->no_telepon) && $p->no_telepon != '-') {
                $p->primary_phone = $p->no_telepon;
                $p->phone_type = 'Siswa';
            }
        });
        
        // Prepare recipients data for JavaScript (filter only those with phone)
        $recipientsData = $pendaftars->filter(function($p) {
            return !empty($p->primary_phone);
        })->map(function($p) {
            return [
                'phone' => $p->primary_phone,
                'id_pendaftar' => $p->id_pendaftar,
                'nama' => $p->nama_lengkap,
                'jurusan' => $p->jurusan
            ];
        })->values();
        
        return view('whatsapp.broadcast', compact('templates', 'pendaftars', 'recipientsData'));
    }

    /**
     * Send broadcast
     */
    public function sendBroadcast(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'recipients' => 'required|array',
            'recipients.*.phone' => 'required|string',
            'recipients.*.id_pendaftar' => 'nullable|integer',
            'recipients.*.nama' => 'nullable|string',
            'recipients.*.jurusan' => 'nullable|string',
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $messages = [];
        $successCount = 0;
        $failedCount = 0;
        $results = [];

        foreach ($request->recipients as $recipient) {
            $phone = $recipient['phone'];
            $pendaftarId = $recipient['id_pendaftar'] ?? null;
            
            // Find pendaftar by ID or phone number
            $pendaftar = null;
            if ($pendaftarId) {
                $pendaftar = Pendaftar::find($pendaftarId);
            } else {
                // Fallback: find by phone if id not provided
                $pendaftar = Pendaftar::where(function($query) use ($phone) {
                    $query->where('no_hp_wali', $phone)
                          ->orWhere('no_hp_ortu', $phone)
                          ->orWhere('no_telepon', $phone);
                })->first();
            }

            // Replace variables in message
            $personalizedMessage = $request->message;
            
            if ($pendaftar) {
                $personalizedMessage = $this->replaceMessageVariables($request->message, [
                    'name' => $pendaftar->nama_lengkap,
                    'no_reg' => $pendaftar->no_registrasi,
                    'jurusan' => $pendaftar->jurusan,
                    'nisn' => $pendaftar->nisn,
                    'asal_sekolah' => $pendaftar->asal_sekolah,
                ]);
            }

            $messages[] = [
                'phone' => $phone,
                'message' => $personalizedMessage,
                'pendaftar_id' => $pendaftar ? $pendaftar->id_pendaftar : null,
            ];
        }

        $result = $this->whatsappService->sendBulk($messages, [
            'type' => 'broadcast',
            'sent_by' => auth()->id(),
        ]);

        return response()->json($result);
    }

    /**
     * Logout from WhatsApp
     */
    public function logout()
    {
        $result = $this->whatsappService->logout();
        
        // Return JSON for AJAX request
        if (request()->expectsJson()) {
            return response()->json($result);
        }
        
        // Return redirect for regular request
        return redirect()->route('whatsapp.index')
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    /**
     * Restart WhatsApp server
     */
    public function restart()
    {
        // Check last restart time (cooldown 5 minutes)
        $lastRestart = cache()->get('wa_server_last_restart');
        if ($lastRestart && now()->diffInMinutes($lastRestart) < 5) {
            $remainingMinutes = 5 - now()->diffInMinutes($lastRestart);
            return response()->json([
                'success' => false,
                'message' => "Server baru saja restart. Tunggu {$remainingMinutes} menit lagi."
            ], 429);
        }

        // Log activity
        UserActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'wa_server_restart',
            'description' => 'User initiated WA Gateway server restart',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        // Set cooldown
        cache()->put('wa_server_last_restart', now(), 300); // 5 minutes

        // Restart server
        $result = $this->whatsappService->restart();
        
        return response()->json($result);
    }

    /**
     * Reset & Reconnect WhatsApp (Logout + Generate new QR)
     */
    public function reset()
    {
        // Check last reset time (cooldown 5 minutes)
        $lastReset = cache()->get('wa_server_last_reset');
        if ($lastReset && now()->diffInMinutes($lastReset) < 5) {
            $remainingMinutes = 5 - now()->diffInMinutes($lastReset);
            return response()->json([
                'success' => false,
                'message' => "Reset baru saja dilakukan. Tunggu {$remainingMinutes} menit lagi."
            ], 429);
        }

        // Log activity
        UserActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'wa_server_reset',
            'description' => 'User initiated WA Gateway reset & reconnect',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        // Set cooldown
        cache()->put('wa_server_last_reset', now(), 300); // 5 minutes

        // Reset connection (logout + generate new QR)
        $result = $this->whatsappService->logout();
        
        return response()->json($result);
    }

    /**
     * Phone list page - Rekap nomor HP pendaftar
     */
    public function phoneList(Request $request)
    {
        // Get active tab (default: not-sent for actionable view)
        $activeTab = $request->get('tab', 'not-sent');
        
        // Save active tab to session for persistence
        session(['phone_list_active_tab' => $activeTab]);
        
        // EXTERNAL TAB HANDLING (Task 5.5)
        if ($activeTab === 'external') {
            return $this->handleExternalTab($request);
        }
        
        $query = Pendaftar::with(['masterJurusan', 'whatsappLogs']);

        // TAB FILTERING
        switch ($activeTab) {
            case 'sent':
                // Has at least one successful message
                $query->whereHas('whatsappLogs', function($q) {
                    $q->where('status', 'sent');
                });
                break;
            case 'not-sent':
                // No messages sent yet BUT has phone number
                $query->whereDoesntHave('whatsappLogs')
                    ->where(function($q) {
                        // Harus punya minimal 1 nomor HP
                        $q->whereNotNull('no_hp_wali')
                          ->orWhereNotNull('no_hp_ortu')
                          ->orWhereNotNull('no_telepon');
                    })
                    ->where(function($q) {
                        // Dan nomor tidak boleh empty string semua
                        $q->where('no_hp_wali', '!=', '')
                          ->orWhere('no_hp_ortu', '!=', '')
                          ->orWhere('no_telepon', '!=', '');
                    });
                break;
            case 'failed':
                // Latest message failed
                $query->whereHas('whatsappLogs', function($q) {
                    $q->where('status', 'failed')
                      ->whereRaw('whatsapp_logs.id = (SELECT MAX(id) FROM whatsapp_logs AS wl WHERE wl.pendaftar_id = whatsapp_logs.pendaftar_id)');
                });
                break;
            case 'accepted':
                // Students with status_siswa = 'Diterima'
                $query->where('status_siswa', 'Diterima');
                break;
            case 'no-phone':
                // No phone number
                $query->where(function($q) {
                    $q->whereNull('no_hp_wali')
                      ->whereNull('no_hp_ortu')
                      ->whereNull('no_telepon');
                })->orWhere(function($q) {
                    $q->where('no_hp_wali', '')
                      ->where('no_hp_ortu', '')
                      ->where('no_telepon', '');
                });
                break;
            // 'all' - no additional filter
        }

        // Filter by jurusan
        if ($request->has('jurusan_id') && $request->jurusan_id != '') {
            $query->where('jurusan_id', $request->jurusan_id);
        }

        // Filter by gelombang
        if ($request->has('gelombang') && $request->gelombang != '') {
            $query->where('gelombang', $request->gelombang);
        }

        // Filter by status siswa
        if ($request->has('status_siswa') && $request->status_siswa != '') {
            $query->where('status_siswa', $request->status_siswa);
        }

        // Filter by phone type
        $phoneType = $request->get('phone_type', 'all');
        if ($phoneType != 'all') {
            switch ($phoneType) {
                case 'wali':
                    $query->whereNotNull('no_hp_wali')->where('no_hp_wali', '!=', '');
                    break;
                case 'ortu':
                    $query->whereNotNull('no_hp_ortu')->where('no_hp_ortu', '!=', '');
                    break;
                case 'siswa':
                    $query->whereNotNull('no_telepon')->where('no_telepon', '!=', '');
                    break;
            }
        }

        // Search by name or NISN
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%")
                  ->orWhere('no_registrasi', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sort = $request->get('sort', '');
        switch ($sort) {
            case 'has_phone':
                // Punya WA dulu (prioritas: wali > ortu > siswa)
                $query->orderByRaw("
                    CASE 
                        WHEN no_hp_wali IS NOT NULL AND no_hp_wali != '' THEN 1
                        WHEN no_hp_ortu IS NOT NULL AND no_hp_ortu != '' THEN 2
                        WHEN no_telepon IS NOT NULL AND no_telepon != '' THEN 3
                        ELSE 4
                    END
                ");
                break;
            case 'no_phone':
                // Tidak punya WA dulu
                $query->orderByRaw("
                    CASE 
                        WHEN (no_hp_wali IS NULL OR no_hp_wali = '') 
                         AND (no_hp_ortu IS NULL OR no_hp_ortu = '') 
                         AND (no_telepon IS NULL OR no_telepon = '') THEN 1
                        ELSE 2
                    END
                ");
                break;
            case 'name_asc':
                $query->orderBy('nama_lengkap', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('nama_lengkap', 'desc');
                break;
            case 'reg_newest':
                $query->orderBy('tgl_daftar', 'desc');
                break;
            case 'reg_oldest':
                $query->orderBy('tgl_daftar', 'asc');
                break;
            default:
                // Default: terbaru daftar
                $query->orderBy('tgl_daftar', 'desc');
        }

        $perPage = $request->get('per_page', 20);
        $perPage = in_array($perPage, [10, 20, 50, 100]) ? $perPage : 20;
        
        $pendaftars = $query->paginate($perPage)->appends($request->except('page'));

        // Add phone data and message status to each pendaftar
        $pendaftars->getCollection()->transform(function ($pendaftar) use ($phoneType) {
            $pendaftar->phone_data = $this->getPhoneDataForDisplay($pendaftar, $phoneType);
            $pendaftar->message_status = $this->getMessageStatus($pendaftar);
            return $pendaftar;
        });

        // Get statistics
        $statistics = $this->getPhoneStatistics();
        
        // Get message statistics
        $messageStats = $this->getMessageStatistics();

        // Get tab counts
        $tabCounts = $this->getTabCounts();

        // Get jurusans for filter
        $jurusans = Jurusan::where('aktif', true)->orderBy('nama')->get();

        // Get unique gelombangs
        $gelombangs = Pendaftar::select('gelombang')
            ->distinct()
            ->whereNotNull('gelombang')
            ->orderBy('gelombang')
            ->pluck('gelombang');

        // Get templates for broadcast
        $templates = WhatsAppTemplate::active()->get();

        return view('whatsapp.phone-list', compact(
            'pendaftars',
            'statistics',
            'messageStats',
            'tabCounts',
            'activeTab',
            'jurusans',
            'gelombangs',
            'templates'
        ));
    }

    /**
     * Handle external tab in phone list (Task 5.5)
     */
    private function handleExternalTab(Request $request)
    {
        $query = ExternalBroadcastRecipient::with(['batch', 'matchedPendaftar']);

        // Filter: show duplicates only
        if ($request->get('show_duplicates_only') === 'true') {
            $query->where('is_duplicate_spmb', true);
        }

        // Search by name or phone
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('phone_normalized', 'like', "%{$search}%");
            });
        }

        // Sort by created_at desc
        $query->orderBy('created_at', 'desc');

        $perPage = $request->get('per_page', 20);
        $perPage = in_array($perPage, [10, 20, 50, 100]) ? $perPage : 20;
        
        $externalRecipients = $query->paginate($perPage)->appends($request->except('page'));

        // Get statistics
        $statistics = $this->getPhoneStatistics();
        $messageStats = $this->getMessageStatistics();
        $tabCounts = $this->getTabCounts();

        $activeTab = 'external';
        $jurusans = Jurusan::where('aktif', true)->orderBy('nama')->get();
        $gelombangs = collect();
        $templates = WhatsAppTemplate::active()->get();
        $pendaftars = collect(); // Empty for external tab

        return view('whatsapp.phone-list', compact(
            'pendaftars',
            'externalRecipients',
            'statistics',
            'messageStats',
            'tabCounts',
            'activeTab',
            'jurusans',
            'gelombangs',
            'templates'
        ));
    }

    /**
     * Send bulk broadcast from phone list
     */
    public function sendBulkBroadcast(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phones' => 'required|array|min:1',
            'phones.*.phone' => 'required|string',
            'phones.*.name' => 'required|string',
            'phones.*.no_reg' => 'required|string',
            'phones.*.jurusan' => 'required|string',
            'message' => 'required|string',
            'template_id' => 'nullable|exists:whatsapp_templates,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $phones = $request->phones;
        $message = $request->message;
        $templateId = $request->template_id;

        $results = [];
        $successCount = 0;
        $failedCount = 0;

        foreach ($phones as $phoneData) {
            try {
                // Replace variables in message for each recipient
                $personalizedMessage = $this->replaceMessageVariables($message, $phoneData);

                // Send message
                $result = $this->whatsappService->send(
                    $phoneData['phone'],
                    $personalizedMessage,
                    [
                        'type' => 'broadcast',
                        'sent_by' => auth()->id(),
                        'template_id' => $templateId,
                        'pendaftar_id' => $phoneData['id'] ?? null,
                    ]
                );

                if ($result['success']) {
                    $successCount++;
                } else {
                    $failedCount++;
                }

                $results[] = [
                    'phone' => $phoneData['phone'],
                    'name' => $phoneData['name'],
                    'success' => $result['success'],
                    'message' => $result['message'] ?? null,
                ];

                // Delay between messages
                if (count($phones) > 1) {
                    sleep(1);
                }

            } catch (\Exception $e) {
                $failedCount++;
                $results[] = [
                    'phone' => $phoneData['phone'],
                    'name' => $phoneData['name'],
                    'success' => false,
                    'message' => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Broadcast selesai. Terkirim: {$successCount}, Gagal: {$failedCount}",
            'total' => count($phones),
            'success_count' => $successCount,
            'failed_count' => $failedCount,
            'results' => $results,
        ]);
    }

    /**
     * Get pendaftar messages history
     */
    public function getPendaftarMessages($id)
    {
        try {
            $pendaftar = Pendaftar::findOrFail($id);
            
            // Get all phone numbers from pendaftar
            $phones = collect([
                $pendaftar->no_hp_wali,
                $pendaftar->no_hp_ortu,
                $pendaftar->no_telepon
            ])->filter()->unique();

            // Normalize phone numbers
            $normalizedPhones = $phones->map(function($phone) {
                return WhatsAppLog::normalizePhone($phone);
            })->filter()->unique()->values()->toArray();

            // Get all messages (SPMB + External)
            $allMessages = WhatsAppLog::with(['template', 'sender', 'externalBatch'])
                ->where(function($query) use ($pendaftar, $normalizedPhones) {
                    $query->where('pendaftar_id', $pendaftar->id_pendaftar)
                          ->orWhereIn('phone_normalized', $normalizedPhones);
                })
                ->orderBy('created_at', 'desc')
                ->get();
            
            // Get phone data (priority: wali > ortu > siswa)
            $phone = null;
            if (!empty($pendaftar->no_hp_wali)) {
                $phone = $pendaftar->no_hp_wali;
            } elseif (!empty($pendaftar->no_hp_ortu)) {
                $phone = $pendaftar->no_hp_ortu;
            } elseif (!empty($pendaftar->no_telepon)) {
                $phone = $pendaftar->no_telepon;
            }
            
            // Format messages
            $messages = $allMessages->map(function($log) {
                $source = $log->external_batch_id ? 'external' : 'spmb';
                $sourceBadge = $source === 'external' ? '📤 Eksternal' : '🏫 SPMB';
                
                return [
                    'id' => $log->id,
                    'status' => $log->status,
                    'type' => $log->type,
                    'message' => $log->message,
                    'template' => $log->template->label ?? null,
                    'date' => $log->created_at->format('d M Y, H:i'),
                    'error_message' => $log->error_message,
                    'sent_by' => $log->sender->name ?? 'System',
                    'source' => $source,
                    'source_badge' => $sourceBadge,
                    'batch_name' => $log->externalBatch->batch_name ?? null
                ];
            });
            
            return response()->json([
                'success' => true,
                'pendaftar' => [
                    'id_pendaftar' => $pendaftar->id_pendaftar,
                    'nama_lengkap' => $pendaftar->nama_lengkap,
                    'no_registrasi' => $pendaftar->no_registrasi,
                    'nisn' => $pendaftar->nisn,
                    'jurusan' => $pendaftar->masterJurusan->nama_jurusan ?? $pendaftar->jurusan,
                    'phone' => $phone
                ],
                'messages' => $messages,
                'statistics' => [
                    'total' => $allMessages->count(),
                    'spmb' => $allMessages->whereNull('external_batch_id')->count(),
                    'external' => $allMessages->whereNotNull('external_batch_id')->count(),
                    'sent' => $allMessages->where('status', 'sent')->count(),
                    'failed' => $allMessages->where('status', 'failed')->count(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get phone data for display
     */
    private function getPhoneDataForDisplay(Pendaftar $pendaftar, string $phoneType = 'all'): array
    {
        $phone = null;
        $type = null;

        if ($phoneType == 'all') {
            // Priority: wali > ortu > siswa
            if (!empty($pendaftar->no_hp_wali)) {
                $phone = $pendaftar->no_hp_wali;
                $type = 'Wali';
            } elseif (!empty($pendaftar->no_hp_ortu)) {
                $phone = $pendaftar->no_hp_ortu;
                $type = 'Orang Tua';
            } elseif (!empty($pendaftar->no_telepon)) {
                $phone = $pendaftar->no_telepon;
                $type = 'Siswa';
            }
        } elseif ($phoneType == 'wali' && !empty($pendaftar->no_hp_wali)) {
            $phone = $pendaftar->no_hp_wali;
            $type = 'Wali';
        } elseif ($phoneType == 'ortu' && !empty($pendaftar->no_hp_ortu)) {
            $phone = $pendaftar->no_hp_ortu;
            $type = 'Orang Tua';
        } elseif ($phoneType == 'siswa' && !empty($pendaftar->no_telepon)) {
            $phone = $pendaftar->no_telepon;
            $type = 'Siswa';
        }

        // Format phone number
        $formatted = null;
        if ($phone) {
            $formatted = $this->formatPhoneForDisplay($phone);
        }

        return [
            'phone' => $phone,
            'formatted' => $formatted,
            'type' => $type,
        ];
    }

    /**
     * Format phone number for display
     */
    private function formatPhoneForDisplay(string $phone): string
    {
        // Remove non-numeric
        $cleaned = preg_replace('/\D/', '', $phone);

        // Convert to 62xxx format
        if (substr($cleaned, 0, 1) === '0') {
            $cleaned = '62' . substr($cleaned, 1);
        } elseif (!str_starts_with($cleaned, '62')) {
            $cleaned = '62' . $cleaned;
        }

        // Format: 0812-3456-7890
        $original = $phone;
        if (substr($original, 0, 1) === '0') {
            return substr($original, 0, 4) . '-' . substr($original, 4, 4) . '-' . substr($original, 8);
        }

        return $phone;
    }

    /**
     * Get phone statistics
     */
    private function getPhoneStatistics(): array
    {
        $totalPendaftar = Pendaftar::count();
        
        $withPhone = Pendaftar::where(function($query) {
            $query->whereNotNull('no_hp_wali')
                  ->orWhereNotNull('no_hp_ortu')
                  ->orWhereNotNull('no_telepon');
        })->where(function($query) {
            $query->where('no_hp_wali', '!=', '')
                  ->orWhere('no_hp_ortu', '!=', '')
                  ->orWhere('no_telepon', '!=', '');
        })->count();

        $withoutPhone = $totalPendaftar - $withPhone;
        $phonePercentage = $totalPendaftar > 0 ? round(($withPhone / $totalPendaftar) * 100, 1) : 0;

        return [
            'total_pendaftar' => $totalPendaftar,
            'with_phone' => $withPhone,
            'without_phone' => $withoutPhone,
            'phone_percentage' => $phonePercentage,
        ];
    }

    /**
     * Replace message variables with actual data
     */
    private function replaceMessageVariables(string $message, array $data): string
    {
        $settings = SettingSystem::instance()->toSettingsArray();
        
        $replacements = [
            '{nama}' => $data['name'] ?? '-',
            '{nama_lengkap}' => $data['name'] ?? '-',
            '{no_pendaftaran}' => $data['no_reg'] ?? '-',
            '{no_registrasi}' => $data['no_reg'] ?? '-',
            '{jurusan}' => $data['jurusan'] ?? '-',
            '{nisn}' => $data['nisn'] ?? '-',
            '{asal_sekolah}' => $data['asal_sekolah'] ?? '-',
            '{portal_url}' => url('/'),
            '{sekolah}' => $settings['school_name'] ?? 'SMK PGRI BLORA',
            '{tanggal}' => now()->format('d-m-Y'),
            '{tahun}' => now()->format('Y'),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $message);
    }

    /**
     * Get PM2 diagnostics
     */
    public function diagnostics()
    {
        try {
            // Get PM2 process list (using full path with sudo for root's PM2)
            $pm2Output = shell_exec('sudo -u root /usr/bin/pm2 jlist 2>&1');
            $pm2Processes = json_decode($pm2Output, true);
            
            // Find whatsapp-server process
            $process = null;
            if (is_array($pm2Processes)) {
                foreach ($pm2Processes as $p) {
                    if (isset($p['name']) && $p['name'] === 'whatsapp-server') {
                        $process = $p;
                        break;
                    }
                }
            }

            $issues = [];
            $recommendations = [];

            if (!$process) {
                $issues[] = [
                    'type' => 'error',
                    'code' => 'PROCESS_NOT_FOUND',
                    'title' => 'PM2 Process Not Found',
                    'description' => 'WhatsApp server process tidak ditemukan di PM2',
                    'auto_fixable' => true
                ];
            } else {
                $pm2 = $process['pm2_env'] ?? [];
                $monit = $process['monit'] ?? [];
                $restarts = $pm2['restart_time'] ?? 0;
                $status = $pm2['status'] ?? 'unknown';
                $memory = $monit['memory'] ?? 0;
                $memoryMB = round($memory / 1024 / 1024, 2);

                // Check crash loop (restarts > 10)
                if ($restarts > 10) {
                    $issues[] = [
                        'type' => 'warning',
                        'code' => 'CRASH_LOOP',
                        'title' => 'High Restart Count',
                        'description' => "Server telah restart {$restarts} kali. Mungkin ada masalah yang perlu diperbaiki.",
                        'auto_fixable' => true
                    ];
                }

                // Check memory usage (> 500 MB)
                if ($memoryMB > 500) {
                    $issues[] = [
                        'type' => 'warning',
                        'code' => 'HIGH_MEMORY',
                        'title' => 'High Memory Usage',
                        'description' => "Memory usage tinggi: {$memoryMB} MB. Pertimbangkan restart server.",
                        'auto_fixable' => false
                    ];
                }

                // Check if process is stopped
                if ($status === 'stopped' || $status === 'errored') {
                    $issues[] = [
                        'type' => 'error',
                        'code' => 'PROCESS_STOPPED',
                        'title' => 'Process Stopped',
                        'description' => "Server dalam status: {$status}",
                        'auto_fixable' => true
                    ];
                }

                // Check for import path errors in logs
                $errorLog = shell_exec('sudo -u root /usr/bin/pm2 logs whatsapp-server --err --lines 50 --nostream 2>&1');
                if ($errorLog && str_contains($errorLog, 'ERR_UNSUPPORTED_DIR_IMPORT')) {
                    $issues[] = [
                        'type' => 'error',
                        'code' => 'IMPORT_PATH_ERROR',
                        'title' => 'Import Path Error',
                        'description' => 'Ditemukan error import path di logs. Server perlu direstart dengan konfigurasi yang benar.',
                        'auto_fixable' => true
                    ];
                }
            }

            // Get fix history from cache
            $fixHistory = cache()->get('wa_diagnostics_fix_history', []);

            return response()->json([
                'success' => true,
                'data' => [
                    'process' => $process,
                    'issues' => $issues,
                    'recommendations' => $recommendations,
                    'fix_history' => array_slice($fixHistory, -10), // Last 10 fixes
                    'timestamp' => now()->toIso8601String()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get diagnostics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Auto-fix detected issues
     */
    public function autoFix()
    {
        try {
            // Rate limiting: Max 3 auto-fixes per hour
            $fixCount = cache()->get('wa_autofix_count', 0);
            $fixTimestamp = cache()->get('wa_autofix_timestamp');
            
            if ($fixCount >= 3 && $fixTimestamp && now()->diffInHours($fixTimestamp) < 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rate limit exceeded. Maximum 3 auto-fixes per hour. Please wait.'
                ], 429);
            }

            // Get diagnostics first
            $diagnosticsResponse = $this->diagnostics();
            $diagnostics = $diagnosticsResponse->getData(true);
            
            if (!$diagnostics['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to get diagnostics'
                ], 500);
            }

            $issues = $diagnostics['data']['issues'] ?? [];
            $fixedIssues = [];
            $failedIssues = [];

            foreach ($issues as $issue) {
                if (!$issue['auto_fixable']) {
                    continue;
                }

                $fixed = false;
                $fixMessage = '';

                switch ($issue['code']) {
                    case 'PROCESS_NOT_FOUND':
                        // Start new PM2 process
                        $output = shell_exec('cd ' . base_path('whatsapp-server') . ' && sudo -u root /usr/bin/pm2 start server.js --name whatsapp-server 2>&1');
                        $fixed = true;
                        $fixMessage = 'Started new PM2 process';
                        break;

                    case 'IMPORT_PATH_ERROR':
                        // Delete process and restart with correct path
                        shell_exec('sudo -u root /usr/bin/pm2 delete whatsapp-server 2>&1');
                        sleep(2);
                        $output = shell_exec('cd ' . base_path('whatsapp-server') . ' && sudo -u root /usr/bin/pm2 start server.js --name whatsapp-server 2>&1');
                        $fixed = true;
                        $fixMessage = 'Deleted and restarted process with correct configuration';
                        break;

                    case 'CRASH_LOOP':
                        // Flush logs and restart
                        shell_exec('sudo -u root /usr/bin/pm2 flush whatsapp-server 2>&1');
                        sleep(1);
                        shell_exec('sudo -u root /usr/bin/pm2 restart whatsapp-server 2>&1');
                        $fixed = true;
                        $fixMessage = 'Flushed logs and restarted process';
                        break;

                    case 'PROCESS_STOPPED':
                        // Restart the process
                        shell_exec('sudo -u root /usr/bin/pm2 restart whatsapp-server 2>&1');
                        $fixed = true;
                        $fixMessage = 'Restarted stopped process';
                        break;
                }

                if ($fixed) {
                    $fixedIssues[] = [
                        'code' => $issue['code'],
                        'title' => $issue['title'],
                        'fix' => $fixMessage
                    ];
                } else {
                    $failedIssues[] = [
                        'code' => $issue['code'],
                        'title' => $issue['title'],
                        'reason' => 'No auto-fix available'
                    ];
                }
            }

            // Update fix counter
            if (!empty($fixedIssues)) {
                cache()->put('wa_autofix_count', $fixCount + 1, 3600);
                cache()->put('wa_autofix_timestamp', now(), 3600);

                // Add to fix history
                $fixHistory = cache()->get('wa_diagnostics_fix_history', []);
                $fixHistory[] = [
                    'timestamp' => now()->toIso8601String(),
                    'user_id' => auth()->id(),
                    'user_name' => auth()->user()->name,
                    'fixed_issues' => $fixedIssues,
                    'failed_issues' => $failedIssues
                ];
                cache()->put('wa_diagnostics_fix_history', $fixHistory, 86400 * 7); // 7 days

                // Log activity
                UserActivityLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'wa_auto_fix',
                    'description' => 'Applied auto-fix for ' . count($fixedIssues) . ' issue(s)',
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => count($fixedIssues) > 0 
                    ? 'Auto-fix applied successfully. Fixed ' . count($fixedIssues) . ' issue(s).'
                    : 'No fixable issues found.',
                'data' => [
                    'fixed' => $fixedIssues,
                    'failed' => $failedIssues
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Auto-fix failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get error logs from PM2
     */
    public function getErrorLogs()
    {
        try {
            $errorLog = shell_exec('/usr/bin/pm2 logs whatsapp-server --err --lines 100 --nostream 2>&1');
            
            return response()->json([
                'success' => true,
                'data' => [
                    'logs' => $errorLog ?? 'No error logs found',
                    'timestamp' => now()->toIso8601String()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get error logs: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get message status for a pendaftar
     */
    private function getMessageStatus(Pendaftar $pendaftar): array
    {
        // Get all phone numbers from pendaftar
        $phones = collect([
            $pendaftar->no_hp_wali,
            $pendaftar->no_hp_ortu,
            $pendaftar->no_telepon
        ])->filter()->unique();

        // Normalize phone numbers
        $normalizedPhones = $phones->map(function($phone) {
            return WhatsAppLog::normalizePhone($phone);
        })->filter()->unique()->values()->toArray();

        // Get SPMB messages (from whatsapp_logs)
        $spmbMessages = WhatsAppLog::where('pendaftar_id', $pendaftar->id_pendaftar)
            ->orWhereIn('phone_normalized', $normalizedPhones)
            ->get();

        // Get External broadcast messages (from external_broadcast_recipients via whatsapp_logs)
        $externalMessages = collect();
        if (!empty($normalizedPhones)) {
            $externalMessages = WhatsAppLog::whereIn('phone_normalized', $normalizedPhones)
                ->whereNotNull('external_batch_id')
                ->get();
        }

        // Merge all messages
        $allMessages = $spmbMessages->merge($externalMessages)->unique('id')->sortByDesc('created_at');

        // Calculate statistics
        $totalMessages = $allMessages->count();
        $sentMessages = $allMessages->where('status', 'sent')->count();
        $failedMessages = $allMessages->where('status', 'failed')->count();
        $pendingMessages = $allMessages->where('status', 'pending')->count();
        
        // Count messages by source
        $spmbCount = $allMessages->whereNull('external_batch_id')->count();
        $externalCount = $allMessages->whereNotNull('external_batch_id')->count();
        
        // Get last message
        $lastMessage = $allMessages->first();
        
        // Determine status
        if ($totalMessages == 0) {
            $status = 'not-sent';
            $label = 'Belum Dikirim';
            $badge = 'secondary';
            $icon = '🔵';
        } elseif ($lastMessage && $lastMessage->status == 'failed') {
            $status = 'failed';
            $label = 'Gagal';
            $badge = 'danger';
            $icon = '❌';
        } elseif ($sentMessages > 0) {
            $status = 'sent';
            $label = "Terkirim ({$sentMessages}x)";
            $badge = 'success';
            $icon = '✅';
        } elseif ($pendingMessages > 0) {
            $status = 'pending';
            $label = "Pending ({$pendingMessages}x)";
            $badge = 'warning';
            $icon = '⏳';
        } else {
            $status = 'unknown';
            $label = 'Unknown';
            $badge = 'secondary';
            $icon = '❓';
        }
        
        return [
            'status' => $status,
            'label' => $label,
            'badge' => $badge,
            'icon' => $icon,
            'total' => $totalMessages,
            'sent' => $sentMessages,
            'failed' => $failedMessages,
            'pending' => $pendingMessages,
            'spmb_count' => $spmbCount,
            'external_count' => $externalCount,
            'last_message' => $lastMessage ? [
                'date' => $lastMessage->created_at->format('d M Y, H:i'),
                'template' => $lastMessage->template->label ?? 'Manual',
                'status' => $lastMessage->status,
                'source' => $lastMessage->external_batch_id ? 'external' : 'spmb'
            ] : null
        ];
    }
    
    /**
     * Get message statistics
     */
    private function getMessageStatistics(): array
    {
        $totalSent = WhatsAppLog::where('status', 'sent')->count();
        $totalFailed = WhatsAppLog::where('status', 'failed')->count();
        $totalPending = WhatsAppLog::where('status', 'pending')->count();
        $total = WhatsAppLog::count();
        
        $successRate = $total > 0 ? round(($totalSent / $total) * 100, 1) : 0;
        
        // Today's messages
        $todaySent = WhatsAppLog::where('status', 'sent')
            ->whereDate('created_at', today())
            ->count();
        
        return [
            'total_sent' => $totalSent,
            'total_failed' => $totalFailed,
            'total_pending' => $totalPending,
            'success_rate' => $successRate,
            'today_sent' => $todaySent
        ];
    }
    
    /**
     * Get tab counts
     */
    private function getTabCounts(): array
    {
        $total = Pendaftar::count();
        
        $sent = Pendaftar::whereHas('whatsappLogs', function($q) {
            $q->where('status', 'sent');
        })->count();
        
        // No phone: tidak punya nomor HP sama sekali
        $noPhone = Pendaftar::where(function($q) {
            $q->whereNull('no_hp_wali')
              ->whereNull('no_hp_ortu')
              ->whereNull('no_telepon');
        })->orWhere(function($q) {
            $q->where('no_hp_wali', '')
              ->where('no_hp_ortu', '')
              ->where('no_telepon', '');
        })->count();
        
        // Not sent: PUNYA nomor HP tapi belum pernah dikirim pesan
        $notSent = Pendaftar::whereDoesntHave('whatsappLogs')
            ->where(function($q) {
                // Harus punya minimal 1 nomor HP
                $q->whereNotNull('no_hp_wali')
                  ->orWhereNotNull('no_hp_ortu')
                  ->orWhereNotNull('no_telepon');
            })
            ->where(function($q) {
                // Dan nomor tidak boleh empty string semua
                $q->where('no_hp_wali', '!=', '')
                  ->orWhere('no_hp_ortu', '!=', '')
                  ->orWhere('no_telepon', '!=', '');
            })
            ->count();
        
        $failed = Pendaftar::whereHas('whatsappLogs', function($q) {
            $q->where('status', 'failed')
              ->whereRaw('whatsapp_logs.id = (SELECT MAX(id) FROM whatsapp_logs AS wl WHERE wl.pendaftar_id = whatsapp_logs.pendaftar_id)');
        })->count();
        
        // Accepted: siswa dengan status_siswa = 'Diterima'
        $accepted = Pendaftar::where('status_siswa', 'Diterima')->count();
        
        // External: total recipient eksternal
        $external = \App\Models\ExternalBroadcastRecipient::count();
        
        return [
            'all' => $total,
            'sent' => $sent,
            'not-sent' => $notSent,
            'failed' => $failed,
            'accepted' => $accepted,
            'no-phone' => $noPhone,
            'external' => $external
        ];
    }

    // ===== EXTERNAL BROADCAST METHODS =====

    /**
     * External broadcast page (Task 5.1)
     */
    public function externalBroadcastPage()
    {
        $templates = WhatsAppTemplate::active()->get();
        return view('whatsapp.external-broadcast', compact('templates'));
    }

    /**
     * Parse external recipients from CSV or manual input (Task 5.2)
     */
    public function parseExternalRecipients(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'batch_name' => 'required|string|max:255',
            'source_type' => 'required|in:csv,manual',
            'csv_file' => 'required_if:source_type,csv|file|mimes:csv,txt|max:2048',
            'manual_input' => 'required_if:source_type,manual|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Check for duplicate batch name in last 30 days
            $duplicateBatch = ExternalBroadcastBatch::where('batch_name', $request->batch_name)
                ->where('created_at', '>=', now()->subDays(30))
                ->exists();

            if ($duplicateBatch) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nama batch sudah digunakan dalam 30 hari terakhir. Gunakan nama yang berbeda.',
                ], 422);
            }

            // Parse recipients based on source type
            $recipients = [];
            if ($request->source_type === 'csv') {
                $recipients = $this->externalBroadcastService->parseCSV($request->file('csv_file'));
            } else {
                $recipients = $this->externalBroadcastService->parseManualInput($request->manual_input);
            }

            if (empty($recipients)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada recipient valid yang ditemukan.',
                ], 422);
            }

            // Detect duplicates with SPMB database
            $recipients = $this->externalBroadcastService->detectDuplicates($recipients);

            // Create batch and save recipients in transaction
            DB::beginTransaction();
            try {
                $batch = $this->externalBroadcastService->createBatch(
                    $request->batch_name,
                    $request->source_type,
                    auth()->id()
                );

                $this->externalBroadcastService->saveRecipients($batch->id, $recipients);

                // Update batch total count
                $batch->total_recipients = count($recipients);
                $batch->save();

                DB::commit();

                // Count duplicates
                $duplicatesCount = collect($recipients)->where('is_duplicate_spmb', true)->count();

                // Get preview (first 10 recipients)
                $preview = array_slice($recipients, 0, 10);

                return response()->json([
                    'success' => true,
                    'message' => 'Recipients berhasil diparse',
                    'data' => [
                        'batch_id' => $batch->id,
                        'total_count' => count($recipients),
                        'duplicates_count' => $duplicatesCount,
                        'preview' => $preview,
                    ],
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error parsing recipients: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send external broadcast (Task 5.3)
     */
    public function sendExternalBroadcast(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'batch_id' => 'required|exists:external_broadcast_batches,id',
            'message' => 'required|string',
            'template_id' => 'nullable|exists:whatsapp_templates,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Check WhatsApp Gateway status
            $status = $this->whatsappService->getStatus();
            
            // DEBUG: Log the actual status response
            \Log::info('External Broadcast - WhatsApp Status Check', [
                'status_response' => $status,
                'user_id' => auth()->id(),
                'batch_id' => $request->batch_id,
            ]);
            
            // Validate status structure and check connection
            $isConnected = false;
            if (isset($status['success']) && $status['success'] && isset($status['data']['status'])) {
                $isConnected = $status['data']['status'] === 'connected';
            }
            
            // DEBUG: Log connection check result
            \Log::info('External Broadcast - Connection Check', [
                'is_connected' => $isConnected,
                'status_value' => $status['data']['status'] ?? null,
                'has_success_key' => isset($status['success']),
                'success_value' => $status['success'] ?? null,
                'has_data_key' => isset($status['data']),
                'has_status_key' => isset($status['data']['status']),
            ]);
            
            if (!$isConnected) {
                return response()->json([
                    'success' => false,
                    'message' => 'WhatsApp Gateway tidak terhubung. Silakan scan QR code terlebih dahulu.',
                ], 422);
            }

            // Load batch and recipients with matched pendaftar data
            $batch = ExternalBroadcastBatch::with(['recipients.matchedPendaftar.masterJurusan'])->findOrFail($request->batch_id);

            // Mark batch as in progress
            $batch->markAsInProgress();

            $successCount = 0;
            $failedCount = 0;

            // Prepare common variables (date, time, school info)
            $now = now();
            $hariIndonesia = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            $bulanIndonesia = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            
            $commonVariables = [
                '{tanggal}' => $now->format('d-m-Y'),
                '{hari}' => $hariIndonesia[$now->dayOfWeek],
                '{bulan}' => $bulanIndonesia[(int)$now->format('n')],
                '{tahun}' => $now->format('Y'),
                '{waktu}' => $now->format('H:i'),
                '{sekolah}' => config('app.name', 'SMK PGRI Blora'),
                '{alamat_sekolah}' => env('SCHOOL_ADDRESS', 'Jl. Raya Blora'),
                '{telepon_sekolah}' => env('SCHOOL_PHONE', '(0296) 531xxx'),
                '{website}' => url('/'),
            ];

            foreach ($batch->recipients as $recipient) {
                // Prepare variables for replacement
                $variables = array_merge($commonVariables, [
                    '{nama}' => $recipient->name,
                    '{phone}' => $recipient->phone,
                    '{notes}' => $recipient->notes ?? '',
                ]);
                
                // Add SPMB data if recipient has matched pendaftar
                if ($recipient->matchedPendaftar) {
                    $pendaftar = $recipient->matchedPendaftar;
                    
                    $variables['{no_registrasi}'] = $pendaftar->no_registrasi ?? '';
                    $variables['{nisn}'] = $pendaftar->nisn ?? '';
                    $variables['{nik}'] = $pendaftar->nik ?? '';
                    $variables['{email}'] = $pendaftar->email ?? '';
                    $variables['{tempat_lahir}'] = $pendaftar->tempat_lahir ?? '';
                    $variables['{tanggal_lahir}'] = $pendaftar->tanggal_lahir ? $pendaftar->tanggal_lahir->format('d-m-Y') : '';
                    $variables['{jenis_kelamin}'] = $pendaftar->jenis_kelamin ?? '';
                    $variables['{agama}'] = $pendaftar->agama ?? '';
                    $variables['{asal_sekolah}'] = $pendaftar->asal_sekolah ?? '';
                    $variables['{tahun_lulus}'] = $pendaftar->tahun_lulus ?? '';
                    $variables['{alamat}'] = $pendaftar->alamat ?? '';
                    $variables['{nama_ayah}'] = $pendaftar->nama_ayah ?? '';
                    $variables['{nama_ibu}'] = $pendaftar->nama_ibu ?? '';
                    $variables['{no_hp_ortu}'] = $pendaftar->no_hp_ortu ?? '';
                    $variables['{nama_wali}'] = $pendaftar->nama_wali ?? '';
                    $variables['{no_hp_wali}'] = $pendaftar->no_hp_wali ?? '';
                    $variables['{jurusan}'] = $pendaftar->jurusan ?? '';
                    $variables['{nama_jaringan}'] = $pendaftar->nama_jaringan ?? '';
                    $variables['{gelombang}'] = $pendaftar->gelombang ?? '';
                    $variables['{tgl_daftar}'] = $pendaftar->tgl_daftar ? $pendaftar->tgl_daftar->format('d-m-Y') : '';
                    $variables['{status_siswa}'] = $pendaftar->status_siswa ?? '';
                }

                // Replace variables in message
                $personalizedMessage = str_replace(
                    array_keys($variables),
                    array_values($variables),
                    $request->message
                );

                // Send message
                $result = $this->whatsappService->send(
                    $recipient->phone_normalized,
                    $personalizedMessage,
                    [
                        'type' => 'external_broadcast',
                        'sent_by' => auth()->id(),
                        'template_id' => $request->template_id,
                        'external_batch_id' => $batch->id,
                    ]
                );

                // Validate result is an array with 'success' key
                if (is_array($result) && isset($result['success']) && $result['success']) {
                    $successCount++;
                    $batch->incrementSent();
                } else {
                    $failedCount++;
                    $batch->incrementFailed();
                }

                // Rate limiting: 1 second delay between messages
                if ($batch->recipients->count() > 1) {
                    sleep(1);
                }
            }

            // Mark batch as completed
            $batch->markAsCompleted();

            // Log activity
            UserActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'external_broadcast_sent',
                'description' => "Sent external broadcast '{$batch->batch_name}' to {$batch->total_recipients} recipients",
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return response()->json([
                'success' => true,
                'message' => "Broadcast selesai. Terkirim: {$successCount}, Gagal: {$failedCount}",
                'data' => [
                    'total' => $batch->total_recipients,
                    'success_count' => $successCount,
                    'failed_count' => $failedCount,
                    'batch_id' => $batch->id,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error sending broadcast: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get external recipient messages (Task 5.4)
     */
    public function getExternalMessages($id)
    {
        try {
            $recipient = ExternalBroadcastRecipient::with(['batch', 'matchedPendaftar'])->findOrFail($id);

            // Get messages by phone number and batch
            $messages = WhatsAppLog::with(['template', 'sender'])
                ->where('phone', $recipient->phone_normalized)
                ->where('external_batch_id', $recipient->batch_id)
                ->orderBy('created_at', 'desc')
                ->get();

            // Format messages
            $formattedMessages = $messages->map(function($log) {
                return [
                    'id' => $log->id,
                    'status' => $log->status,
                    'type' => $log->type,
                    'message' => $log->message,
                    'template' => $log->template->label ?? null,
                    'date' => $log->created_at->format('d M Y, H:i'),
                    'error_message' => $log->error_message,
                    'sent_by' => $log->sender->name ?? 'System',
                ];
            });

            return response()->json([
                'success' => true,
                'recipient' => [
                    'id' => $recipient->id,
                    'name' => $recipient->name,
                    'phone' => $recipient->phone,
                    'notes' => $recipient->notes,
                    'is_duplicate_spmb' => $recipient->is_duplicate_spmb,
                    'batch_name' => $recipient->batch->batch_name,
                    'matched_pendaftar' => $recipient->matchedPendaftar ? [
                        'id' => $recipient->matchedPendaftar->id_pendaftar,
                        'nama' => $recipient->matchedPendaftar->nama_lengkap,
                        'no_registrasi' => $recipient->matchedPendaftar->no_registrasi,
                    ] : null,
                ],
                'messages' => $formattedMessages,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data: ' . $e->getMessage(),
            ], 500);
        }
    }
}

