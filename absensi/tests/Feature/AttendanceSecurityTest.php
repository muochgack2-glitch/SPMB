<?php

namespace Tests\Feature;

use App\Models\AttendanceClass;
use App\Models\AttendanceRecord;
use App\Models\AttendanceStudent;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Task 17.12: Security Review
 * 
 * Tests for security concerns:
 * - Photo access control
 * - API rate limiting
 * - Input validation
 * - SQL injection prevention
 * - XSS prevention
 * - Path traversal prevention
 */
class AttendanceSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected AttendanceStudent $testStudent;
    protected AttendanceClass $testClass;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->artisan('db:seed', ['--class' => 'AttendanceSettingsSeeder']);
        
        $this->testClass = AttendanceClass::create([
            'nama_kelas' => '12 RPL',
            'tingkat' => 12,
            'jurusan' => 'RPL',
            'is_active' => true,
        ]);
        
        $this->testStudent = AttendanceStudent::create([
            'nis' => '24999',
            'nama' => 'Test Student',
            'kelas_id' => $this->testClass->id,
            'no_hp_ortu' => '628123456789',
            'is_active' => true,
        ]);
    }

    /**
     * Test: Photo files are not directly accessible via public URL
     * 
     * @test
     */
    public function test_photo_files_not_directly_accessible()
    {
        // Arrange: Create a test photo
        $photoPath = 'attendance/photos/24999/2026-06-14/check_in_123456.jpg';
        Storage::disk('local')->put($photoPath, 'fake photo content');
        
        // Act: Try to access via public URL (should fail)
        $publicUrl = '/storage/' . $photoPath;
        $response = $this->get($publicUrl);
        
        // Assert: Should not be accessible (404 expected)
        $response->assertStatus(404);
    }

    /**
     * Test: Photo access requires proper route with authentication
     * 
     * @test
     */
    public function test_photo_access_via_route_requires_auth()
    {
        // Arrange: Create attendance record with photo
        $photoPath = 'attendance/photos/24999/2026-06-14/check_in_123456.jpg';
        Storage::disk('local')->put($photoPath, 'fake photo content');
        
        $record = AttendanceRecord::create([
            'student_id' => $this->testStudent->id,
            'date' => Carbon::today(),
            'check_in_time' => '07:10:00',
            'check_in_photo' => $photoPath,
            'status' => 'hadir',
        ]);
        
        // Act: Try to access photo without auth
        $response = $this->get("/attendance/dashboard/photo/{$record->id}/check_in");
        
        // Assert: Should redirect to login or return 401/403
        $this->assertContains($response->status(), [302, 401, 403], 
            'Photo access should require authentication');
    }

    /**
     * Test: API scan endpoint validates required fields
     * 
     * @test
     */
    public function test_scan_api_validates_required_fields()
    {
        // Test missing NIS
        $response = $this->postJson('/api/attendance/scan', [
            'photo_base64' => 'data:image/jpeg;base64,fake',
            'action' => 'check_in',
        ]);
        $response->assertStatus(422);
        
        // Test missing photo
        $response = $this->postJson('/api/attendance/scan', [
            'nis' => '24999',
            'action' => 'check_in',
        ]);
        $response->assertStatus(422);
        
        // Test missing action
        $response = $this->postJson('/api/attendance/scan', [
            'nis' => '24999',
            'photo_base64' => 'data:image/jpeg;base64,fake',
        ]);
        $response->assertStatus(422);
    }

    /**
     * Test: API scan validates action enum
     * 
     * @test
     */
    public function test_scan_api_validates_action_enum()
    {
        $response = $this->postJson('/api/attendance/scan', [
            'nis' => '24999',
            'photo_base64' => 'data:image/jpeg;base64,fake',
            'action' => 'invalid_action', // Invalid action
        ]);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('action');
    }

    /**
     * Test: SQL injection prevention in NIS search
     * 
     * @test
     */
    public function test_sql_injection_prevention_in_nis()
    {
        $maliciousNIS = "24999' OR '1'='1";
        
        $response = $this->postJson('/api/attendance/scan', [
            'nis' => $maliciousNIS,
            'photo_base64' => 'data:image/jpeg;base64,fake',
            'action' => 'check_in',
        ]);
        
        // Should safely return "not found", not expose data
        $response->assertStatus(404);
        $response->assertJson([
            'success' => false,
        ]);
        
        // Should not create any records
        $this->assertEquals(0, AttendanceRecord::count());
    }

    /**
     * Test: XSS prevention in student name
     * 
     * @test
     */
    public function test_xss_prevention_in_student_name()
    {
        // Create student with potential XSS in name
        $xssStudent = AttendanceStudent::create([
            'nis' => '24888',
            'nama' => '<script>alert("XSS")</script>',
            'kelas_id' => $this->testClass->id,
            'no_hp_ortu' => '628999999999',
            'is_active' => true,
        ]);
        
        // Visit student list page
        $response = $this->get('/attendance/students');
        
        // Assert: Script tags should be escaped
        $content = $response->getContent();
        $this->assertStringNotContainsString('<script>alert', $content);
        $this->assertStringContainsString('&lt;script&gt;', $content);
    }

    /**
     * Test: Path traversal prevention in photo path
     * 
     * @test
     */
    public function test_path_traversal_prevention()
    {
        // Try to access file outside intended directory
        $maliciousPath = '../../../.env';
        
        $response = $this->get("/attendance/photos?path={$maliciousPath}");
        
        // Should return 404 or 403, not expose file content
        $this->assertContains($response->status(), [404, 403]);
    }

    /**
     * Test: Rate limiting on scan API (if implemented)
     * 
     * @test
     */
    public function test_api_rate_limiting()
    {
        // Note: This test assumes rate limiting is implemented
        // If not implemented, this will help identify the need
        
        $requests = 0;
        $blocked = false;
        
        // Try to make 100 rapid requests
        for ($i = 0; $i < 100; $i++) {
            $response = $this->postJson('/api/attendance/scan', [
                'nis' => '24999',
                'photo_base64' => 'data:image/jpeg;base64,fake',
                'action' => 'check_in',
            ]);
            
            $requests++;
            
            if ($response->status() === 429) {
                $blocked = true;
                break;
            }
        }
        
        // If rate limiting is not implemented, this will alert developers
        if (!$blocked && $requests >= 100) {
            $this->markTestIncomplete(
                'Rate limiting not implemented. Consider adding throttle middleware to scan API.'
            );
        }
    }

    /**
     * Test: Base64 photo validation
     * 
     * @test
     */
    public function test_photo_base64_validation()
    {
        // Test invalid base64 format
        $response = $this->postJson('/api/attendance/scan', [
            'nis' => $this->testStudent->nis,
            'photo_base64' => 'not-a-valid-base64',
            'action' => 'check_in',
        ]);
        
        $response->assertStatus(422);
    }

    /**
     * Test: Photo size limits
     * 
     * @test
     */
    public function test_photo_size_limits()
    {
        // Create a large fake photo (> 10MB)
        $largeData = str_repeat('A', 10 * 1024 * 1024); // 10MB
        $largePhoto = 'data:image/jpeg;base64,' . base64_encode($largeData);
        
        $response = $this->postJson('/api/attendance/scan', [
            'nis' => $this->testStudent->nis,
            'photo_base64' => $largePhoto,
            'action' => 'check_in',
        ]);
        
        // Should reject or handle gracefully
        $this->assertContains($response->status(), [413, 422], 
            'Large photos should be rejected');
    }

    /**
     * Test: QR Code path validation
     * 
     * @test
     */
    public function test_qr_code_path_validation()
    {
        // Try to download QR with path traversal
        $response = $this->get('/attendance/qr/../../../.env/download');
        
        // Should return 404, not expose sensitive files
        $response->assertStatus(404);
    }

    /**
     * Test: CSRF protection on forms
     * 
     * @test
     */
    public function test_csrf_protection_on_forms()
    {
        // All POST forms should have CSRF token
        $response = $this->get('/attendance/students/create');
        $response->assertStatus(200);
        $response->assertSee('csrf_token');
    }

    /**
     * Test: Prevent duplicate simultaneous check-ins
     * 
     * @test
     */
    public function test_prevent_duplicate_simultaneous_checkins()
    {
        Carbon::setTestNow(Carbon::today()->setTime(7, 10));
        
        $fakePhoto = $this->generateFakePhotoBase64();
        
        // Send two simultaneous requests
        $response1 = $this->postJson('/api/attendance/scan', [
            'nis' => $this->testStudent->nis,
            'photo_base64' => $fakePhoto,
            'action' => 'check_in',
        ]);
        
        $response2 = $this->postJson('/api/attendance/scan', [
            'nis' => $this->testStudent->nis,
            'photo_base64' => $fakePhoto,
            'action' => 'check_in',
        ]);
        
        // One should succeed, one should fail
        $statuses = [$response1->status(), $response2->status()];
        $this->assertContains(200, $statuses, 'One request should succeed');
        $this->assertContains(422, $statuses, 'One request should be rejected as duplicate');
        
        // Only one record should be created
        $this->assertEquals(1, AttendanceRecord::where('student_id', $this->testStudent->id)->count());
    }

    /**
     * Test: Phone number format validation
     * 
     * @test
     */
    public function test_phone_number_format_validation()
    {
        // Try to create student with invalid phone format
        $response = $this->post('/attendance/students', [
            'nis' => '24777',
            'nama' => 'Test Student',
            'kelas_id' => $this->testClass->id,
            'no_hp_ortu' => 'invalid-phone',
            '_token' => csrf_token(),
        ]);
        
        // Should validate phone number format
        $response->assertSessionHasErrors('no_hp_ortu');
    }

    // Helper Methods

    protected function generateFakePhotoBase64(): string
    {
        $img = imagecreate(100, 100);
        ob_start();
        imagejpeg($img, null, 85);
        $data = ob_get_clean();
        imagedestroy($img);
        
        return 'data:image/jpeg;base64,' . base64_encode($data);
    }
}
