<?php

namespace App\Http\Controllers;

use App\Services\AttendanceWhatsAppService;
use App\Models\WhatsAppMessage;
use App\Models\WhatsAppSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class WhatsAppController extends Controller
{
    public function __construct(
        private AttendanceWhatsAppService $whatsAppService
    ) {}

    /**
     * WhatsApp Gateway Dashboard
     */
    public function index()
    {
        $status = $this->whatsAppService->getGatewayStatus();
        
        return view('attendance.whatsapp.index', [
            'gatewayStatus' => $status
        ]);
    }

    /**
     * Get gateway status (AJAX)
     */
    public function getStatus()
    {
        $status = $this->whatsAppService->getGatewayStatus();
        return response()->json($status);
    }

    /**
     * Get QR Code for scanning
     */
    public function getQRCode()
    {
        try {
            $response = Http::timeout(10)
                ->get('http://localhost:3002/qr');

            if ($response->successful()) {
                $data = $response->json();
                
                return response()->json([
                    'success' => true,
                    'qr' => $data['qr'] ?? null,
                    'connectionState' => $data['connectionState'] ?? 'unknown',
                    'message' => $data['message'] ?? 'QR Code ready'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch QR code'
            ], 500);

        } catch (\Exception $e) {
            Log::error('Failed to get QR code: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gateway tidak dapat diakses'
            ], 500);
        }
    }

    /**
     * Manual send message page
     */
    public function sendPage()
    {
        return view('attendance.whatsapp.send');
    }

    /**
     * Send manual message
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'message' => 'required|string|max:1000'
        ]);

        $result = $this->whatsAppService->sendParentNotification(
            $request->phone,
            $request->message
        );

        if ($result['success']) {
            return back()->with('success', 'Pesan berhasil dikirim ke ' . $request->phone);
        }

        return back()->with('error', $result['message'] ?? 'Gagal mengirim pesan');
    }

    /**
     * Test notification
     */
    public function testNotification(Request $request)
    {
        $request->validate([
            'phone' => 'required|string'
        ]);

        $result = $this->whatsAppService->sendTestMessage($request->phone);

        if ($result['success']) {
            return back()->with('success', 'Test message berhasil dikirim!');
        }

        return back()->with('error', $result['message'] ?? 'Gagal mengirim test message');
    }

    /**
     * Logout/disconnect gateway
     */
    public function logout()
    {
        try {
            $response = Http::timeout(15)
                ->post('http://localhost:3002/logout');

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Logout berhasil. QR Code baru sedang digenerate...'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Gagal logout'
            ], 500);

        } catch (\Exception $e) {
            Log::error('Failed to logout: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gateway tidak dapat diakses'
            ], 500);
        }
    }

    /**
     * Restart gateway server
     */
    public function restart()
    {
        try {
            $response = Http::timeout(15)
                ->post('http://localhost:3002/restart');

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Gateway sedang direstart...'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Gagal restart'
            ], 500);

        } catch (\Exception $e) {
            Log::error('Failed to restart: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gateway tidak dapat diakses'
            ], 500);
        }
    }

    /**
     * Get gateway health metrics
     */
    public function health()
    {
        try {
            $response = Http::timeout(10)
                ->get('http://localhost:3002/health');

            if ($response->successful()) {
                return response()->json($response->json());
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch health metrics'
            ], 500);

        } catch (\Exception $e) {
            Log::error('Failed to get health: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gateway tidak dapat diakses'
            ], 500);
        }
    }

    /**
     * Message logs page
     */
    public function logs(Request $request)
    {
        $query = WhatsAppMessage::with('student.kelas')
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->has('type') && $request->type != '') {
            $query->where('type', $request->type);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->start_date != '') {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->has('end_date') && $request->end_date != '') {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Search by phone or student name
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('phone', 'like', '%' . $search . '%')
                  ->orWhereHas('student', function($sq) use ($search) {
                      $sq->where('nama', 'like', '%' . $search . '%');
                  });
            });
        }

        $messages = $query->paginate(20);

        // Calculate statistics
        $stats = [
            'total' => WhatsAppMessage::count(),
            'sent' => WhatsAppMessage::sent()->count(),
            'failed' => WhatsAppMessage::failed()->count(),
            'pending' => WhatsAppMessage::pending()->count(),
            'today' => WhatsAppMessage::today()->count(),
        ];

        return view('attendance.whatsapp.logs', [
            'messages' => $messages,
            'stats' => $stats,
            'filters' => $request->only(['status', 'type', 'start_date', 'end_date', 'search'])
        ]);
    }

    /**
     * Get student messages (AJAX)
     */
    public function getStudentMessages($studentId)
    {
        $messages = WhatsAppMessage::where('student_id', $studentId)
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'messages' => $messages
        ]);
    }

    /**
     * Settings page
     */
    public function settings()
    {
        $settings = WhatsAppSetting::getAllGrouped();
        
        return view('attendance.whatsapp.settings', [
            'settings' => $settings
        ]);
    }

    /**
     * Update settings
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'gateway_url' => 'required|url',
            'gateway_timeout' => 'required|integer|min:5|max:60',
            'retry_attempts' => 'required|integer|min:1|max:5',
            'messages_per_minute' => 'required|integer|min:1|max:60',
            'delay_between_messages' => 'required|integer|min:0|max:30',
        ]);

        try {
            // Update all settings
            WhatsAppSetting::updateMultiple([
                // Connection
                'gateway_url' => $request->gateway_url,
                'gateway_timeout' => $request->gateway_timeout,
                'retry_attempts' => $request->retry_attempts,
                
                // Rate Limiting
                'rate_limit_enabled' => $request->has('rate_limit_enabled'),
                'messages_per_minute' => $request->messages_per_minute,
                'delay_between_messages' => $request->delay_between_messages,
                
                // Features
                'auto_send_enabled' => $request->has('auto_send_enabled'),
                'send_on_checkin' => $request->has('send_on_checkin'),
                'send_on_checkout' => $request->has('send_on_checkout'),
                'send_on_alpha' => $request->has('send_on_alpha'),
                
                // Templates
                'checkin_message_template' => $request->checkin_message_template,
                'checkout_message_template' => $request->checkout_message_template,
                'alpha_message_template' => $request->alpha_message_template,
            ]);

            return back()->with('success', 'Pengaturan WhatsApp Gateway berhasil disimpan!');
        } catch (\Exception $e) {
            Log::error('Failed to update WhatsApp settings: ' . $e->getMessage());
            return back()->with('error', 'Gagal menyimpan pengaturan: ' . $e->getMessage());
        }
    }

    /**
     * Reset settings to default
     */
    public function resetSettings()
    {
        try {
            WhatsAppSetting::resetToDefaults();
            
            return back()->with('success', 'Pengaturan telah direset ke default!');
        } catch (\Exception $e) {
            Log::error('Failed to reset WhatsApp settings: ' . $e->getMessage());
            return back()->with('error', 'Gagal reset pengaturan: ' . $e->getMessage());
        }
    }

    /**
     * Start WhatsApp Gateway server
     */
    public function startGateway()
    {
        try {
            // Try production path first, then local path
            $gatewayPath = base_path('../whatsapp-server-absensi');
            $processName = 'whatsapp-gateway-absensi';
            
            if (!is_dir($gatewayPath)) {
                // Try local Windows path
                $gatewayPath = base_path('../whatsapp-server');
                $processName = 'whatsapp-gateway-local';
            }
            
            // Check if gateway directory exists
            if (!is_dir($gatewayPath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gateway directory not found. Tried: ' . $gatewayPath
                ], 404);
            }

            // Check if already running (online)
            $checkCommand = "pm2 jlist";
            $output = [];
            \exec($checkCommand, $output);
            $processList = json_decode(implode('', $output), true);
            
            if (is_array($processList)) {
                foreach ($processList as $process) {
                    if (isset($process['name']) && $process['name'] === $processName) {
                        // Check if process is online
                        if (isset($process['pm2_env']['status']) && $process['pm2_env']['status'] === 'online') {
                            return response()->json([
                                'success' => false,
                                'message' => 'Gateway sudah running!'
                            ]);
                        }
                        // If stopped, delete it first before starting new one
                        if (isset($process['pm2_env']['status']) && $process['pm2_env']['status'] === 'stopped') {
                            \exec("pm2 delete " . escapeshellarg($processName) . " 2>&1");
                        }
                        break;
                    }
                }
            }

            // Start with PM2 - use ecosystem file if exists, otherwise use direct start
            $ecosystemFile = $gatewayPath . '/ecosystem.config.js';
            
            if (file_exists($ecosystemFile)) {
                // Use ecosystem file (has PORT env configured)
                if (PHP_OS_FAMILY === 'Windows') {
                    chdir($gatewayPath);
                    $startCommand = "pm2 start ecosystem.config.js";
                } else {
                    $startCommand = "cd " . escapeshellarg($gatewayPath) . " && pm2 start ecosystem.config.js";
                }
            } else {
                // Fallback to direct start (may not have PORT set correctly)
                if (PHP_OS_FAMILY === 'Windows') {
                    chdir($gatewayPath);
                    $startCommand = "pm2 start server.js --name " . escapeshellarg($processName);
                } else {
                    $startCommand = "cd " . escapeshellarg($gatewayPath) . " && pm2 start server.js --name " . escapeshellarg($processName);
                }
            }
            
            \exec($startCommand . " 2>&1", $output, $returnCode);

            if ($returnCode === 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'Gateway berhasil distart! Tunggu 5 detik lalu refresh status.'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Gagal start gateway: ' . implode("\n", $output)
            ], 500);

        } catch (\Exception $e) {
            Log::error('Failed to start gateway: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Stop WhatsApp Gateway server
     */
    public function stopGateway()
    {
        try {
            // Check which process is running (production or local)
            $checkCommand = "pm2 jlist";
            $output = [];
            \exec($checkCommand, $output);
            $processList = implode('', $output);
            
            $processName = null;
            if (strpos($processList, 'whatsapp-gateway-absensi') !== false) {
                $processName = 'whatsapp-gateway-absensi';
            } elseif (strpos($processList, 'whatsapp-gateway-local') !== false) {
                $processName = 'whatsapp-gateway-local';
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Gateway tidak ditemukan dalam PM2 process list'
                ], 404);
            }
            
            // Stop with PM2
            $stopCommand = "pm2 stop " . escapeshellarg($processName);
            \exec($stopCommand . " 2>&1", $output, $returnCode);

            if ($returnCode === 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'Gateway berhasil distop!'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Gagal stop gateway: ' . implode("\n", $output)
            ], 500);

        } catch (\Exception $e) {
            Log::error('Failed to stop gateway: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get gateway process status from PM2
     */
    public function getGatewayProcessStatus()
    {
        try {
            // Check if PM2 is installed
            $pm2Check = \shell_exec('pm2 -v 2>&1');
            if (empty($pm2Check) || strpos($pm2Check, 'command not found') !== false) {
                return response()->json([
                    'success' => true,
                    'running' => false,
                    'status' => 'pm2_not_installed',
                    'message' => 'PM2 not installed. Install with: npm install -g pm2'
                ]);
            }

            // Get PM2 process list
            $output = \shell_exec('pm2 jlist 2>&1');
            
            if (empty($output)) {
                return response()->json([
                    'success' => true,
                    'running' => false,
                    'status' => 'no_processes',
                    'message' => 'No PM2 processes running'
                ]);
            }

            // Parse JSON
            $processList = json_decode($output, true);
            
            if (!is_array($processList)) {
                return response()->json([
                    'success' => true,
                    'running' => false,
                    'status' => 'parse_error',
                    'message' => 'Failed to parse PM2 list'
                ]);
            }

            // Find gateway process (check both names)
            $gatewayProcess = null;
            foreach ($processList as $process) {
                if (isset($process['name']) && 
                    ($process['name'] === 'whatsapp-gateway-absensi' || $process['name'] === 'whatsapp-gateway-local')) {
                    $gatewayProcess = $process;
                    break;
                }
            }

            if ($gatewayProcess) {
                $isOnline = isset($gatewayProcess['pm2_env']['status']) && 
                           $gatewayProcess['pm2_env']['status'] === 'online';
                
                return response()->json([
                    'success' => true,
                    'running' => $isOnline,
                    'status' => $gatewayProcess['pm2_env']['status'] ?? 'unknown',
                    'uptime' => $gatewayProcess['pm2_env']['pm_uptime'] ?? 0,
                    'memory' => isset($gatewayProcess['monit']['memory']) ? 
                               round($gatewayProcess['monit']['memory'] / 1024 / 1024, 2) : 0,
                    'cpu' => $gatewayProcess['monit']['cpu'] ?? 0,
                ]);
            }

            return response()->json([
                'success' => true,
                'running' => false,
                'status' => 'not_found',
                'message' => 'Gateway process not found in PM2'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get process status: ' . $e->getMessage());
            
            return response()->json([
                'success' => true,
                'running' => false,
                'status' => 'error',
                'message' => 'Error checking status'
            ]);
        }
    }
}
