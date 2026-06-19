<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\WhatsAppController;
use App\Services\WhatsAppService;
use App\Services\ExternalBroadcastService;
use App\Models\ExternalBroadcastBatch;
use App\Models\ExternalBroadcastRecipient;
use App\Models\WhatsAppSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Mockery;

/**
 * Bug Condition Exploration Test
 * 
 * Property 1: Bug Condition - Sequential Confirmation Wait Violation
 * 
 * CRITICAL: This test MUST FAIL on unfixed code - failure confirms the bug exists
 * DO NOT attempt to fix the test or the code when it fails
 * NOTE: This test encodes the expected behavior - it will validate the fix when it passes after implementation
 * GOAL: Surface counterexamples that demonstrate the bug exists
 */
class ExternalBroadcastBugConditionTest extends TestCase
{
    use RefreshDatabase;

    protected $whatsappService;
    protected $externalBroadcastService;
    protected $controller;
    
    /**
     * Setup test environment
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Create mock services
        $this->whatsappService = Mockery::mock(WhatsAppService::class);
        $this->externalBroadcastService = Mockery::mock(ExternalBroadcastService::class);
        
        // Create controller with mocked services
        $this->controller = new WhatsAppController(
            $this->whatsappService,
            $this->externalBroadcastService
        );
        
        // Seed rate limiting settings
        $this->seedRateLimitingSettings();
        
        // Mock authenticated user
        $this->actingAs(\App\Models\User::factory()->create());
    }
    
    /**
     * Cleanup after test
     */
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
    
    /**
     * Seed database with rate limiting settings
     */
    private function seedRateLimitingSettings()
    {
        // Use updateOrCreate to avoid unique constraint violation
        WhatsAppSetting::updateOrCreate(
            ['key' => 'wa_external_broadcast_min_delay'],
            [
                'value' => '2',
                'type' => 'integer',
                'group' => 'rate_limiting',
                'label' => 'External Broadcast - Min Delay (detik)',
                'description' => 'Minimum delay between messages',
                'is_public' => false,
            ]
        );
        
        WhatsAppSetting::updateOrCreate(
            ['key' => 'wa_external_broadcast_max_delay'],
            [
                'value' => '4',
                'type' => 'integer',
                'group' => 'rate_limiting',
                'label' => 'External Broadcast - Max Delay (detik)',
                'description' => 'Maximum delay between messages',
                'is_public' => false,
            ]
        );
        
        WhatsAppSetting::updateOrCreate(
            ['key' => 'wa_external_broadcast_break_interval'],
            [
                'value' => '10',
                'type' => 'integer',
                'group' => 'rate_limiting',
                'label' => 'External Broadcast - Break Interval',
                'description' => 'Break every N messages',
                'is_public' => false,
            ]
        );
        
        WhatsAppSetting::updateOrCreate(
            ['key' => 'wa_external_broadcast_break_duration'],
            [
                'value' => '2',
                'type' => 'integer',
                'group' => 'rate_limiting',
                'label' => 'External Broadcast - Break Duration (detik)',
                'description' => 'Duration of break',
                'is_public' => false,
            ]
        );
    }
    
    /**
     * Test: Success Without MessageId Gets Delay
     * 
     * EXPECTED OUTCOME: This test SHOULD FAIL on unfixed code
     * - Confirms bug exists: no messageId verification before counting as success
     * 
     * Bug Condition: System counts success:true but missing messageId as successful
     * 
     * @test
     */
    public function test_success_without_message_id_should_fail()
    {
        // Create batch with 1 recipient
        $batch = ExternalBroadcastBatch::create([
            'batch_name' => 'Test Batch No MessageId',
            'status' => 'pending',
            'total_recipients' => 1,
            'sent_count' => 0,
            'failed_count' => 0,
            'created_by' => auth()->id(),
        ]);
        
        ExternalBroadcastRecipient::create([
            'batch_id' => $batch->id,
            'name' => 'Recipient 1',
            'phone' => '081234567890',
            'phone_normalized' => '6281234567890',
        ]);
        
        // Mock: Gateway status check
        $this->whatsappService->shouldReceive('getStatus')
            ->andReturn([
                'success' => true,
                'data' => [
                    'status' => 'connected',
                ],
            ]);
            
        $this->whatsappService->shouldReceive('send')
            ->once()
            ->andReturn([
                'success' => true,
                'message' => 'Message sent',
                'data' => [], // No messageId!
                'has_message_id' => false, // Flag indicates no messageId
            ]);
        
        $request = new Request([
            'batch_id' => $batch->id,
            'message' => 'Test message',
            'template_id' => null,
        ]);
        
        $result = $this->controller->sendExternalBroadcast($request);
        $resultData = $result->getData(true);
        
        $successCount = $resultData['data']['success_count'] ?? $resultData['success_count'] ?? 0;
        $failedCount = $resultData['data']['failed_count'] ?? $resultData['failed_count'] ?? 0;
        
        // ASSERTION: Message should be marked as FAILED due to missing messageId
        // Expected behavior (fixed): success_count = 0, failed_count = 1
        // **CONFIRMED BUG**: Current code returns success_count = 1, failed_count = 0
        //
        // This proves the bug exists: system counts success without verifying messageId
        
        $this->assertEquals(
            0,
            $successCount,
            'EXPECTED TO FAIL ON UNFIXED CODE: Success without messageId counted as success. ' .
            'Should be treated as failed due to missing proof of delivery. ' .
            "Current: success={$successCount}, failed={$failedCount}"
        );
        
        $this->assertEquals(
            1,
            $failedCount,
            'EXPECTED TO FAIL ON UNFIXED CODE: Success without messageId not counted as failed.'
        );
    }
}
