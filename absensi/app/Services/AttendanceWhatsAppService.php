<?php

namespace App\Services;

use App\Models\WhatsAppMessage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AttendanceWhatsAppService
{
    /**
     * WhatsApp Gateway base URL.
     */
    private const GATEWAY_URL = 'http://localhost:3002';

    /**
     * Send parent notification via WhatsApp.
     *
     * @param string $phone Phone number (628xxx format)
     * @param string $message Message text
     * @param string|null $photoPath Optional photo path for media message
     * @param int|null $studentId Optional student ID for logging
     * @param string $type Message type (manual, auto_checkin, auto_checkout, auto_alpha)
     * @return array Response with success status
     */
    public function sendParentNotification(
        string $phone, 
        string $message, 
        ?string $photoPath = null,
        ?int $studentId = null,
        string $type = 'manual'
    ): array
    {
        // Normalize phone number
        $normalizedPhone = $this->normalizePhone($phone);

        // Create message log (pending)
        $messageLog = WhatsAppMessage::create([
            'student_id' => $studentId,
            'phone' => $phone,
            'phone_normalized' => $normalizedPhone,
            'message' => $message,
            'type' => $type,
            'status' => 'pending',
        ]);

        try {
            // Gateway lama hanya support text message via /send endpoint
            // Photo sending tidak supported di gateway baileys ini
            $endpoint = '/send';
            $url = self::GATEWAY_URL . $endpoint;

            // Prepare request data
            $data = [
                'phone' => $normalizedPhone,
                'message' => $message,
            ];

            // Note: Gateway whatsapp-server-absensi (Baileys) tidak support media sending
            // Jika ada photo, akan dikirim text only
            if ($photoPath) {
                Log::info("Photo path provided but gateway doesn't support media: {$photoPath}");
                // Append note to message
                $data['message'] .= "\n\n[Foto check-in tersimpan di sistem]";
            }

            // Send request to gateway
            $response = Http::timeout(10)
                ->post($url, $data);

            if ($response->successful()) {
                // Mark as sent
                $messageLog->markAsSent($response->json());

                return [
                    'success' => true,
                    'message' => 'Notification sent successfully',
                    'response' => $response->json(),
                    'log_id' => $messageLog->id,
                ];
            }

            // Failed response
            Log::error('WhatsApp notification failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            $messageLog->markAsFailed('Gateway returned error: ' . $response->status(), $response->json());

            return [
                'success' => false,
                'message' => 'Failed to send notification',
                'error' => $response->body(),
                'log_id' => $messageLog->id,
            ];

        } catch (\Exception $e) {
            Log::error('WhatsApp notification exception: ' . $e->getMessage());

            $messageLog->markAsFailed($e->getMessage());

            return [
                'success' => false,
                'message' => 'Exception occurred',
                'error' => $e->getMessage(),
                'log_id' => $messageLog->id,
            ];
        }
    }

    /**
     * Normalize phone number to WhatsApp format.
     * Converts 08xxx to 628xxx, removes spaces and dashes.
     *
     * @param string $phone Phone number
     * @return string Normalized phone number
     */
    public function normalizePhone(string $phone): string
    {
        // Remove spaces, dashes, parentheses
        $phone = preg_replace('/[\s\-\(\)]/', '', $phone);

        // Convert 08xxx to 628xxx
        if (str_starts_with($phone, '08')) {
            $phone = '62' . substr($phone, 1);
        }

        // Remove leading + if present
        if (str_starts_with($phone, '+')) {
            $phone = substr($phone, 1);
        }

        // Ensure it starts with 62
        if (!str_starts_with($phone, '62')) {
            $phone = '62' . $phone;
        }

        return $phone;
    }

    /**
     * Get WhatsApp Gateway connection status.
     *
     * @return array Status information
     */
    public function getGatewayStatus(): array
    {
        try {
            $response = Http::timeout(5)
                ->get(self::GATEWAY_URL . '/status');

            if ($response->successful()) {
                $data = $response->json();
                
                // Check both 'connected' field and 'status' field for compatibility
                $isConnected = ($data['connected'] ?? false) || ($data['status'] ?? '') === 'connected';
                
                return [
                    'connected' => $isConnected,
                    'message' => $isConnected ? 'Gateway is connected' : 'Gateway is disconnected',
                    'data' => $data,
                ];
            }

            return [
                'connected' => false,
                'message' => 'Gateway returned error',
                'error' => $response->body(),
            ];

        } catch (\Exception $e) {
            return [
                'connected' => false,
                'message' => 'Cannot connect to gateway',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Validate phone number format.
     *
     * @param string $phone Phone number
     * @return bool True if valid Indonesian mobile number
     */
    public function validatePhoneNumber(string $phone): bool
    {
        $normalized = $this->normalizePhone($phone);
        
        // Check if it's a valid Indonesian mobile number
        // Indonesian mobile: 628xxx (10-13 digits total)
        return preg_match('/^628\d{8,11}$/', $normalized) === 1;
    }

    /**
     * Send test message to verify gateway connection.
     *
     * @param string $phone Test phone number
     * @return array Test result
     */
    public function sendTestMessage(string $phone): array
    {
        $message = "🧪 Test message from Attendance System\n" .
                   "Waktu: " . now()->format('Y-m-d H:i:s') . "\n" .
                   "Jika Anda menerima pesan ini, gateway WhatsApp berfungsi dengan baik.";

        return $this->sendParentNotification($phone, $message, null, null, 'manual');
    }
}
