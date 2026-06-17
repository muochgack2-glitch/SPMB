<?php

namespace App\Http\Controllers;

use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WhatsAppDiagnosticController extends Controller
{
    protected WhatsAppService $whatsappService;

    public function __construct(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    /**
     * Test send message with full diagnostic
     */
    public function testSend(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'message' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $phone = $request->phone;
        $message = $request->message ?? 'Test message dari WhatsApp Diagnostic Tool - ' . now()->format('Y-m-d H:i:s');

        // Get server status first
        $statusCheck = $this->whatsappService->getStatus();

        // Attempt to send
        $sendResult = $this->whatsappService->send($phone, $message, [
            'type' => 'diagnostic_test',
            'sent_by' => auth()->id(),
        ]);

        // Get current server URL
        $currentServer = $this->whatsappService->getCurrentServerUrl();

        return response()->json([
            'success' => true,
            'diagnostic' => [
                'test_phone' => $phone,
                'test_message' => $message,
                'timestamp' => now()->toIso8601String(),
                'user' => auth()->user()->name,
                
                'gateway_status' => [
                    'server_url' => $currentServer,
                    'status_check' => $statusCheck,
                ],
                
                'send_result' => $sendResult,
                
                'analysis' => [
                    'gateway_connected' => $statusCheck['success'] ?? false,
                    'send_api_success' => $sendResult['success'] ?? false,
                    'has_message_id' => $sendResult['has_message_id'] ?? false,
                    'has_error' => isset($sendResult['error']) || isset($sendResult['data']['error']),
                    'log_id' => $sendResult['log_id'] ?? null,
                ],
                
                'recommendations' => $this->getRecommendations($statusCheck, $sendResult),
            ]
        ]);
    }

    /**
     * Get recommendations based on test results
     */
    private function getRecommendations(array $statusCheck, array $sendResult): array
    {
        $recommendations = [];

        // Check 1: Gateway connection
        if (!($statusCheck['success'] ?? false)) {
            $recommendations[] = [
                'issue' => 'Gateway tidak terhubung',
                'severity' => 'critical',
                'action' => 'Pastikan WhatsApp Gateway (PM2) berjalan dan scan QR code',
            ];
        }

        // Check 2: Send result
        if (!($sendResult['success'] ?? false)) {
            $recommendations[] = [
                'issue' => 'Pesan gagal terkirim',
                'severity' => 'high',
                'action' => 'Cek error message: ' . ($sendResult['message'] ?? 'Unknown error'),
            ];
        }

        // Check 3: No messageId (false positive)
        if (($sendResult['success'] ?? false) && !($sendResult['has_message_id'] ?? false)) {
            $recommendations[] = [
                'issue' => 'Tidak ada messageId dari gateway',
                'severity' => 'medium',
                'action' => 'Gateway return success tapi tidak ada proof (messageId). Pesan mungkin tidak benar-benar terkirim. Cek HP penerima untuk konfirmasi.',
                'warning' => 'FALSE POSITIVE - Database log "sent" tapi mungkin tidak sampai ke HP',
            ];
        }

        // Check 4: Phone format
        $phone = $sendResult['data']['phone'] ?? '';
        if (!empty($phone) && !preg_match('/^62\d{9,13}$/', $phone)) {
            $recommendations[] = [
                'issue' => 'Format nomor HP mungkin salah',
                'severity' => 'medium',
                'action' => 'Nomor harus format 62xxx (contoh: 628123456789)',
                'current_format' => $phone,
            ];
        }

        if (empty($recommendations)) {
            $recommendations[] = [
                'issue' => 'Tidak ada masalah terdeteksi',
                'severity' => 'info',
                'action' => 'Cek HP penerima untuk konfirmasi pesan diterima',
            ];
        }

        return $recommendations;
    }

    /**
     * Get gateway response format documentation
     */
    public function getGatewayDocs()
    {
        return response()->json([
            'success' => true,
            'documentation' => [
                'expected_success_response' => [
                    'success' => true,
                    'messageId' => 'BAE5XXXXXXXXXXXXX',
                    'message' => 'Message sent successfully',
                ],
                'expected_failure_response' => [
                    'success' => false,
                    'error' => 'Phone not registered on WhatsApp',
                    'message' => 'Failed to send message',
                ],
                'validation_rules' => [
                    'messageId_required' => 'Response harus punya messageId sebagai proof pengiriman',
                    'success_key_required' => 'Response harus punya key "success" (boolean)',
                    'error_handling' => 'Kalau success=false, harus ada error message',
                ],
                'known_issues' => [
                    'false_positive' => 'Gateway return success=true tapi tidak ada messageId',
                    'phone_not_registered' => 'Nomor tidak terdaftar di WhatsApp',
                    'rate_limiting' => 'Terlalu banyak pesan dalam waktu singkat',
                    'connection_lost' => 'Gateway kehilangan koneksi ke WhatsApp',
                ],
            ]
        ]);
    }
}
