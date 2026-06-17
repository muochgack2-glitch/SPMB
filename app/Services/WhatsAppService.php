<?php

namespace App\Services;

use App\Models\WhatsAppLog;
use App\Models\WhatsAppTemplate;
use App\Models\WhatsAppSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class WhatsAppService
{
    /**
     * WhatsApp server URL
     */
    protected string $serverUrl;

    /**
     * Connection timeout
     */
    protected int $timeout;

    /**
     * Retry attempts
     */
    protected int $retryAttempts;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->serverUrl = $this->getActiveServerUrl();
        $this->timeout = WhatsAppSetting::getTimeout();
        $this->retryAttempts = WhatsAppSetting::getRetryAttempts();
    }

    /**
     * Get active server URL with context-aware selection and failover support
     * 
     * DUAL FUNCTION BACKUP GATEWAY:
     * 1. Dedicated for External Broadcast (always use backup)
     * 2. Failover for SPMB Broadcast (use backup when primary down)
     * 
     * @param string|null $context Context type: 'external_broadcast', 'broadcast', etc.
     * @return string
     */
    protected function getActiveServerUrl(?string $context = null): string
    {
        $primary = WhatsAppSetting::get('wa_server_url', 'http://localhost:3000');
        $backup = WhatsAppSetting::get('wa_server_url_backup');
        $failoverEnabled = WhatsAppSetting::get('wa_failover_enabled', false);

        // FUNCTION 1: External Broadcast ALWAYS uses backup gateway (if configured)
        if ($context === 'external_broadcast' && !empty($backup)) {
            Log::info('Using backup gateway for external broadcast (dedicated)', [
                'backup' => $backup,
                'context' => $context,
            ]);
            return $backup;
        }

        // FUNCTION 2: SPMB Broadcast uses primary with failover to backup
        // If failover not enabled or no backup configured, always use primary
        if (!$failoverEnabled || !$backup) {
            return $primary;
        }

        // Check primary health
        if ($this->checkServerHealth($primary)) {
            return $primary;
        }

        // Primary unhealthy, failover to backup
        Log::warning('Primary WhatsApp gateway unhealthy, failover to backup (SPMB)', [
            'primary' => $primary,
            'backup' => $backup,
            'context' => $context ?? 'default',
        ]);

        return $backup;
    }

    /**
     * Get current active server URL (public method for UI display)
     * 
     * @return string
     */
    public function getCurrentServerUrl(): string
    {
        return $this->getActiveServerUrl();
    }

    /**
     * Check if server is healthy
     * 
     * @param string $url
     * @return bool
     */
    protected function checkServerHealth(string $url): bool
    {
        try {
            $timeout = WhatsAppSetting::get('wa_failover_timeout', 5);
            
            $response = Http::timeout($timeout)->get("{$url}/status");
            
            if (!$response->successful()) {
                return false;
            }

            $data = $response->json();
            return isset($data['status']) && $data['status'] === 'connected';
            
        } catch (Exception $e) {
            Log::debug('Server health check failed', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get WhatsApp server status
     * 
     * @return array
     */
    public function getStatus(): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->get("{$this->serverUrl}/status");

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to get status',
                'error' => $response->body(),
            ];
        } catch (Exception $e) {
            Log::error('WhatsApp status check failed', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Connection failed',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get server health metrics
     * 
     * @return array
     */
    public function getHealth(): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->get("{$this->serverUrl}/health");

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to get health metrics',
                'error' => $response->body(),
            ];
        } catch (Exception $e) {
            Log::error('WhatsApp health check failed', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Connection failed',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get QR code for WhatsApp authentication
     * 
     * @return array
     */
    public function getQRCode(): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->get("{$this->serverUrl}/qr");

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to get QR code',
                'error' => $response->body(),
            ];
        } catch (Exception $e) {
            Log::error('WhatsApp QR code fetch failed', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Connection failed',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send single WhatsApp message
     * 
     * @param string $phone Phone number
     * @param string $message Message content
     * @param array $options Additional options (pendaftar_id, template_id, sent_by, type, external_batch_id)
     * @return array
     */
    public function send(string $phone, string $message, array $options = []): array
    {
        // Determine context from options
        $context = $options['type'] ?? null;
        
        // Get appropriate gateway URL based on context
        $serverUrl = $this->getActiveServerUrl($context);
        
        // Create log entry
        $log = WhatsAppLog::create([
            'phone' => $phone,
            'message' => $message,
            'status' => 'pending',
            'type' => $options['type'] ?? 'manual',
            'pendaftar_id' => $options['pendaftar_id'] ?? null,
            'template_id' => $options['template_id'] ?? null,
            'sent_by' => $options['sent_by'] ?? auth()->id(),
            'external_batch_id' => $options['external_batch_id'] ?? null,
        ]);

        try {
            Log::info('Attempting to send WhatsApp message', [
                'phone' => $phone,
                'message_length' => strlen($message),
                'log_id' => $log->id,
                'server_url' => $serverUrl,
                'context' => $context,
            ]);

            $response = Http::timeout($this->timeout)
                ->retry($this->retryAttempts, 1000)
                ->post("{$serverUrl}/send", [
                    'phone' => $phone,
                    'message' => $message,
                ]);

            Log::info('WhatsApp server response', [
                'status_code' => $response->status(),
                'body' => $response->body(),
                'log_id' => $log->id,
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                
                // Enhanced logging untuk debug
                Log::info('WhatsApp gateway response detail', [
                    'phone' => $phone,
                    'log_id' => $log->id,
                    'response_data' => $responseData,
                    'has_success_key' => isset($responseData['success']),
                    'success_value' => $responseData['success'] ?? null,
                    'has_message_id' => isset($responseData['messageId']) || isset($responseData['message_id']),
                    'message_id' => $responseData['messageId'] ?? $responseData['message_id'] ?? null,
                    'server_url' => $serverUrl,
                ]);
                
                // Check if server actually sent the message
                if (isset($responseData['success']) && $responseData['success'] === false) {
                    // Server returned success HTTP code but message failed
                    $errorMessage = $responseData['message'] ?? $responseData['error'] ?? 'Message failed on WhatsApp server';
                    $log->markAsFailed($errorMessage, $responseData);

                    Log::warning('WhatsApp server returned success=false', [
                        'phone' => $phone,
                        'response' => $responseData,
                        'log_id' => $log->id,
                    ]);

                    return [
                        'success' => false,
                        'message' => $errorMessage,
                        'log_id' => $log->id,
                        'debug' => $responseData,
                    ];
                }
                
                // Additional validation: Check if messageId exists (proof of sending)
                $hasMessageId = isset($responseData['messageId']) || isset($responseData['message_id']) || isset($responseData['data']['messageId']);
                
                if (!$hasMessageId && isset($responseData['success']) && $responseData['success'] === true) {
                    // Gateway says success but no messageId - suspicious!
                    Log::warning('WhatsApp gateway returned success without messageId', [
                        'phone' => $phone,
                        'response' => $responseData,
                        'log_id' => $log->id,
                        'warning' => 'Message may not be actually sent - no messageId proof',
                    ]);
                }
                
                // Mark as sent
                $log->markAsSent($responseData);

                Log::info('WhatsApp message sent successfully', [
                    'phone' => $phone,
                    'log_id' => $log->id,
                    'response' => $responseData,
                    'has_message_id' => $hasMessageId,
                ]);

                return [
                    'success' => true,
                    'message' => 'Message sent successfully',
                    'data' => $responseData,
                    'log_id' => $log->id,
                    'has_message_id' => $hasMessageId,
                ];
            }

            // Mark as failed
            $errorMessage = $response->json()['message'] ?? 'Failed to send message';
            $log->markAsFailed($errorMessage, $response->json());

            Log::error('WhatsApp message send failed', [
                'phone' => $phone,
                'status_code' => $response->status(),
                'error' => $errorMessage,
                'response' => $response->json(),
                'log_id' => $log->id,
            ]);

            return [
                'success' => false,
                'message' => $errorMessage,
                'log_id' => $log->id,
            ];
        } catch (Exception $e) {
            // Mark as failed
            $log->markAsFailed($e->getMessage());

            Log::error('WhatsApp message send exception', [
                'phone' => $phone,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'log_id' => $log->id,
            ]);

            return [
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage(),
                'error' => $e->getMessage(),
                'log_id' => $log->id,
            ];
        }
    }

    /**
     * Send message using template
     * 
     * @param string $phone Phone number
     * @param string $templateName Template name
     * @param array $data Data for template variables
     * @param array $options Additional options
     * @return array
     */
    public function sendWithTemplate(string $phone, string $templateName, array $data, array $options = []): array
    {
        try {
            // Get template
            $template = WhatsAppTemplate::where('name', $templateName)
                ->where('is_active', true)
                ->firstOrFail();

            // Parse template with data
            $message = $template->parse($data);

            // Increment template usage
            $template->incrementUsage();

            // Send message
            $options['template_id'] = $template->id;
            return $this->send($phone, $message, $options);

        } catch (Exception $e) {
            Log::error('WhatsApp template send failed', [
                'phone' => $phone,
                'template' => $templateName,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Template not found or inactive',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send bulk messages
     * 
     * @param array $messages Array of ['phone' => '...', 'message' => '...']
     * @param array $options Additional options
     * @return array
     */
    public function sendBulk(array $messages, array $options = []): array
    {
        $results = [];
        $successCount = 0;
        $failedCount = 0;

        foreach ($messages as $item) {
            $phone = $item['phone'] ?? null;
            $message = $item['message'] ?? null;
            $pendaftarId = $item['pendaftar_id'] ?? null;

            if (!$phone || !$message) {
                $results[] = [
                    'phone' => $phone,
                    'success' => false,
                    'error' => 'Phone and message are required',
                ];
                $failedCount++;
                continue;
            }

            // Merge pendaftar_id into options for this specific message
            $messageOptions = array_merge($options, [
                'pendaftar_id' => $pendaftarId
            ]);

            $result = $this->send($phone, $message, $messageOptions);
            
            $results[] = [
                'phone' => $phone,
                'success' => $result['success'],
                'log_id' => $result['log_id'] ?? null,
                'error' => $result['error'] ?? null,
            ];

            if ($result['success']) {
                $successCount++;
            } else {
                $failedCount++;
            }

            // Delay between messages to avoid rate limiting
            if (count($messages) > 1) {
                sleep(1);
            }
        }

        return [
            'success' => true,
            'message' => "Sent {$successCount} messages, {$failedCount} failed",
            'total' => count($messages),
            'success_count' => $successCount,
            'failed_count' => $failedCount,
            'results' => $results,
        ];
    }

    /**
     * Send message to pendaftar using template
     * 
     * @param \App\Models\Pendaftar $pendaftar
     * @param string $templateName
     * @return array
     */
    public function sendToPendaftar($pendaftar, string $templateName): array
    {
        // Prepare data for template
        $data = [
            'nama' => $pendaftar->nama_lengkap,
            'no_pendaftaran' => $pendaftar->no_pendaftaran,
            'jurusan' => $pendaftar->jurusan->nama_jurusan ?? 'N/A',
            'portal_url' => url('/'),
            'sekolah' => config('app.name', 'SMK PGRI Blora'),
        ];

        return $this->sendWithTemplate(
            $pendaftar->no_hp_wali,
            $templateName,
            $data,
            [
                'pendaftar_id' => $pendaftar->id,
                'type' => 'auto_registration',
            ]
        );
    }

    /**
     * Check if WhatsApp server is connected
     * 
     * @return bool
     */
    public function isConnected(): bool
    {
        $status = $this->getStatus();
        
        if (!$status['success']) {
            return false;
        }

        return ($status['data']['status'] ?? '') === 'connected';
    }

    /**
     * Check if auto send is enabled
     * 
     * @return bool
     */
    public function isAutoSendEnabled(): bool
    {
        return WhatsAppSetting::isAutoSendEnabled();
    }

    /**
     * Logout from WhatsApp
     * 
     * @return array
     */
    public function logout(): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->serverUrl}/logout");

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Logged out successfully',
                    'data' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to logout',
                'error' => $response->body(),
            ];
        } catch (Exception $e) {
            Log::error('WhatsApp logout failed', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Connection failed',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Restart WhatsApp server
     * 
     * @return array
     */
    public function restart(): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->post("{$this->serverUrl}/restart");

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Server is restarting...',
                    'data' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to restart server',
                'error' => $response->body(),
            ];
        } catch (Exception $e) {
            Log::error('WhatsApp server restart failed', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Connection failed',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get statistics
     * 
     * @return array
     */
    public function getStatistics(): array
    {
        return [
            'total_sent' => WhatsAppLog::sent()->count(),
            'total_failed' => WhatsAppLog::failed()->count(),
            'total_pending' => WhatsAppLog::pending()->count(),
            'sent_today' => WhatsAppLog::sent()->today()->count(),
            'failed_today' => WhatsAppLog::failed()->today()->count(),
            'total_templates' => WhatsAppTemplate::count(),
            'active_templates' => WhatsAppTemplate::active()->count(),
        ];
    }
}
