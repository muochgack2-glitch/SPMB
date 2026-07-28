<?php

namespace Tests\Feature;

use App\Models\AttendanceClass;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSetting;
use App\Models\AttendanceStudent;
use App\Services\QRCodeService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Task 17: End-to-End Testing
 * 
 * Comprehensive E2E tests covering:
 * - Complete check-in flow (Tasks 17.1)
 * - Complete check-out flow (Tasks 17.2)
 * - All error scenarios (Tasks 17.3)
 * - Manual reject functionality (Tasks 17.4)
 * - Dashboard functionality (Tasks 17.5)
 * - Auto alpha marking (Tasks 17.6)
 * - Performance testing (Tasks 17.7)
 */
class AttendanceEndToEndTest extends TestCase
{
    use RefreshDatabase;

    protected AttendanceStudent $testStudent;
    protected AttendanceClass $testClass;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed settings
        $this->artisan('db:seed', ['--class' => 'AttendanceSettingsSeeder']);
        
        // Create test class
        $this->testClass = AttendanceClass::create([
            'nama_kelas' => '12 RPL A',
            'tingkat' => 12,
            'jurusan' => 'RPL',
            'is_active' => true,
        ]);
        
        // Create test student
        $this->testStudent = AttendanceStudent::create([
            'nis' => '24999',
            'nama' => 'Test Student',
            'kelas_id' => $this->testClass->id,
            'no_hp_ortu' => '628123456789',
            'is_active' => true,
        ]);
        
        // Generate QR Code
        $qrService = app(QRCodeService::class);
        $this->testStudent->qr_code_path = $qrService->generateQRCode($this->testStudent->nis);
        $this->testStudent->save();
    }

    /**
     * Task 17.1: Test complete check-in flow
     * 
     * @test
     */
    public function test_complete_check_in_flow()
    {
        // Arrange: Mock current time to 07:10 (within check-in window, hadir)
        Carbon::setTestNow(Carbon::today()->setTime(7, 10));
        
        // Create fake photo (base64 encoded)
        $fakePhoto = base64_encode(file_get_contents(__DIR__ . '/../fixtures/test-photo.jpg'));
        if (!file_exists(__DIR__ . '/../fixtures/test-photo.jpg')) {
            // Create dummy 1x1 pixel JPEG if fixture doesn't exist
            $img = imagecreate(1, 1);
            ob_start();
            imagejpeg($img, null, 85);
            $fakePhoto = base64_encode(ob_get_clean());
            imagedestroy($img);
        }
        
        // Act: Send scan request
        $response = $this->postJson('/api/attendance/scan', [
            'nis' => $this->testStudent->nis,
            'photo_base64' => 'data:image/jpeg;base64,' . $fakePhoto,
            'action' => 'check_in',
        ]);
        
        // Assert: Response successful
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'status' => 'hadir',
                    'student' => [
                        'nis' => $this->testStudent->nis,
                        'nama' => $this->testStudent->nama,
                    ],
                ],
            ]);
        
        // Assert: Attendance record created
        $this->assertDatabaseHas('attendance_records', [
            'student_id' => $this->testStudent->id,
            'date' => Carbon::today()->toDateString(),
            'status' => 'hadir',
        ]);
        
        $record = AttendanceRecord::where('student_id', $this->testStudent->id)
            ->where('date', Carbon::today())
            ->first();
        
        $this->assertNotNull($record);
        $this->assertNotNull($record->check_in_time);
        $this->assertNotNull($record->check_in_photo);
        $this->assertEquals('hadir', $record->status);
        
        // Assert: Log created
        $this->assertDatabaseHas('attendance_logs', [
            'student_id' => $this->testStudent->id,
            'action' => 'check_in',
            'status' => 'success',
        ]);
        
        // Assert: Photo file saved
        Storage::disk('local')->assertExists($record->check_in_photo);
    }

    /**
     * Task 17.1: Test check-in with late status
     * 
     * @test
     */
    public function test_check_in_late_status()
    {
        // Arrange: Set time to 07:20 (terlambat)
        Carbon::setTestNow(Carbon::today()->setTime(7, 20));
        
        $fakePhoto = $this->generateFakePhotoBase64();
        
        // Act
        $response = $this->postJson('/api/attendance/scan', [
            'nis' => $this->testStudent->nis,
            'photo_base64' => $fakePhoto,
            'action' => 'check_in',
        ]);
        
        // Assert
        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'terlambat');
        
        $this->assertDatabaseHas('attendance_records', [
            'student_id' => $this->testStudent->id,
            'status' => 'terlambat',
        ]);
    }

    /**
     * Task 17.2: Test complete check-out flow
     * 
     * @test
     */
    public function test_complete_check_out_flow()
    {
        // Arrange: Create existing check-in record
        Carbon::setTestNow(Carbon::today()->setTime(7, 10));
        $checkInPhoto = $this->saveTestPhoto('check_in');
        
        $checkInRecord = AttendanceRecord::create([
            'student_id' => $this->testStudent->id,
            'date' => Carbon::today(),
            'check_in_time' => Carbon::now()->toTimeString(),
            'check_in_photo' => $checkInPhoto,
            'status' => 'hadir',
        ]);
        
        // Set time to check-out time
        Carbon::setTestNow(Carbon::today()->setTime(15, 0));
        
        $fakePhoto = $this->generateFakePhotoBase64();
        
        // Act: Send check-out scan
        $response = $this->postJson('/api/attendance/scan', [
            'nis' => $this->testStudent->nis,
            'photo_base64' => $fakePhoto,
            'action' => 'check_out',
        ]);
        
        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'status' => 'hadir',
                ],
            ]);
        
        $checkInRecord->refresh();
        $this->assertNotNull($checkInRecord->check_out_time);
        $this->assertNotNull($checkInRecord->check_out_photo);
        
        // Assert: Log created
        $this->assertDatabaseHas('attendance_logs', [
            'student_id' => $this->testStudent->id,
            'action' => 'check_out',
            'status' => 'success',
        ]);
    }

    /**
     * Task 17.3: Test error scenario - Invalid NIS
     * 
     * @test
     */
    public function test_error_invalid_nis()
    {
        $fakePhoto = $this->generateFakePhotoBase64();
        
        $response = $this->postJson('/api/attendance/scan', [
            'nis' => '99999', // Non-existent NIS
            'photo_base64' => $fakePhoto,
            'action' => 'check_in',
        ]);
        
        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Siswa dengan NIS 99999 tidak ditemukan',
            ]);
    }

    /**
     * Task 17.3: Test error scenario - Inactive student
     * 
     * @test
     */
    public function test_error_inactive_student()
    {
        // Arrange: Deactivate student
        $this->testStudent->update(['is_active' => false]);
        
        $fakePhoto = $this->generateFakePhotoBase64();
        
        $response = $this->postJson('/api/attendance/scan', [
            'nis' => $this->testStudent->nis,
            'photo_base64' => $fakePhoto,
            'action' => 'check_in',
        ]);
        
        $response->assertStatus(422)
            ->assertJsonFragment([
                'success' => false,
            ]);
    }

    /**
     * Task 17.3: Test error scenario - Already checked in today
     * 
     * @test
     */
    public function test_error_already_checked_in()
    {
        // Arrange: Create existing check-in
        Carbon::setTestNow(Carbon::today()->setTime(7, 10));
        AttendanceRecord::create([
            'student_id' => $this->testStudent->id,
            'date' => Carbon::today(),
            'check_in_time' => Carbon::now()->toTimeString(),
            'check_in_photo' => 'photos/test.jpg',
            'status' => 'hadir',
        ]);
        
        $fakePhoto = $this->generateFakePhotoBase64();
        
        // Act: Try to check in again
        $response = $this->postJson('/api/attendance/scan', [
            'nis' => $this->testStudent->nis,
            'photo_base64' => $fakePhoto,
            'action' => 'check_in',
        ]);
        
        // Assert
        $response->assertStatus(422)
            ->assertJsonFragment([
                'success' => false,
            ]);
    }

    /**
     * Task 17.3: Test error scenario - Outside time window
     * 
     * @test
     */
    public function test_error_outside_time_window()
    {
        // Arrange: Set time to before check-in opens (06:00)
        Carbon::setTestNow(Carbon::today()->setTime(6, 0));
        
        $fakePhoto = $this->generateFakePhotoBase64();
        
        $response = $this->postJson('/api/attendance/scan', [
            'nis' => $this->testStudent->nis,
            'photo_base64' => $fakePhoto,
            'action' => 'check_in',
        ]);
        
        $response->assertStatus(422)
            ->assertJsonFragment([
                'success' => false,
            ]);
    }

    /**
     * Task 17.4: Test manual reject functionality
     * 
     * @test
     */
    public function test_manual_reject_functionality()
    {
        // Arrange: Create check-in record
        Carbon::setTestNow(Carbon::today()->setTime(7, 10));
        $record = AttendanceRecord::create([
            'student_id' => $this->testStudent->id,
            'date' => Carbon::today(),
            'check_in_time' => Carbon::now()->toTimeString(),
            'check_in_photo' => 'photos/test.jpg',
            'status' => 'hadir',
        ]);
        
        // Act: Send reject request
        $response = $this->postJson('/api/attendance/reject', [
            'nis' => $this->testStudent->nis,
            'reason' => 'Foto tidak jelas',
        ]);
        
        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
        
        // Assert: Record deleted or marked as rejected
        $this->assertDatabaseMissing('attendance_records', [
            'id' => $record->id,
            'student_id' => $this->testStudent->id,
            'date' => Carbon::today()->toDateString(),
        ]);
        
        // Assert: Log created
        $this->assertDatabaseHas('attendance_logs', [
            'student_id' => $this->testStudent->id,
            'action' => 'reject',
            'status' => 'success',
        ]);
    }

    /**
     * Task 17.5: Test dashboard displays attendance data
     * 
     * @test
     */
    public function test_dashboard_displays_attendance_data()
    {
        // Arrange: Create various attendance records
        Carbon::setTestNow(Carbon::today()->setTime(7, 10));
        
        // Student 1: Hadir
        $student1 = AttendanceStudent::create([
            'nis' => '24101',
            'nama' => 'Student Hadir',
            'kelas_id' => $this->testClass->id,
            'no_hp_ortu' => '628111111111',
            'is_active' => true,
        ]);
        AttendanceRecord::create([
            'student_id' => $student1->id,
            'date' => Carbon::today(),
            'check_in_time' => '07:10:00',
            'check_in_photo' => 'photos/student1.jpg',
            'status' => 'hadir',
        ]);
        
        // Student 2: Terlambat
        $student2 = AttendanceStudent::create([
            'nis' => '24102',
            'nama' => 'Student Terlambat',
            'kelas_id' => $this->testClass->id,
            'no_hp_ortu' => '628222222222',
            'is_active' => true,
        ]);
        AttendanceRecord::create([
            'student_id' => $student2->id,
            'date' => Carbon::today(),
            'check_in_time' => '07:30:00',
            'check_in_photo' => 'photos/student2.jpg',
            'status' => 'terlambat',
        ]);
        
        // Act: Visit dashboard
        $response = $this->get('/attendance/dashboard');
        
        // Assert: Page loads and displays data
        $response->assertStatus(200);
        $response->assertSee('Student Hadir');
        $response->assertSee('Student Terlambat');
        $response->assertSee('hadir');
        $response->assertSee('terlambat');
    }

    /**
     * Task 17.6: Test auto alpha marking (scheduled job)
     * 
     * @test
     */
    public function test_auto_alpha_marking()
    {
        // Arrange: Create students without check-in
        Carbon::setTestNow(Carbon::today()->setTime(9, 1)); // After cutoff
        
        $activeStudent = AttendanceStudent::create([
            'nis' => '24201',
            'nama' => 'Active Student',
            'kelas_id' => $this->testClass->id,
            'no_hp_ortu' => '628333333333',
            'is_active' => true,
        ]);
        
        $inactiveStudent = AttendanceStudent::create([
            'nis' => '24202',
            'nama' => 'Inactive Student',
            'kelas_id' => $this->testClass->id,
            'no_hp_ortu' => '628444444444',
            'is_active' => false,
        ]);
        
        // Act: Run mark absent command
        $this->artisan('attendance:mark-absent')
            ->assertExitCode(0);
        
        // Assert: Active student marked as alpha
        $this->assertDatabaseHas('attendance_records', [
            'student_id' => $activeStudent->id,
            'date' => Carbon::today()->toDateString(),
            'status' => 'alpha',
        ]);
        
        // Assert: Inactive student NOT marked
        $this->assertDatabaseMissing('attendance_records', [
            'student_id' => $inactiveStudent->id,
            'date' => Carbon::today()->toDateString(),
        ]);
    }

    /**
     * Task 17.7: Test performance with multiple rapid scans
     * 
     * @test
     */
    public function test_multiple_rapid_scans_performance()
    {
        // Arrange: Create 10 students
        Carbon::setTestNow(Carbon::today()->setTime(7, 10));
        $students = [];
        
        for ($i = 1; $i <= 10; $i++) {
            $students[] = AttendanceStudent::create([
                'nis' => sprintf('24%03d', $i),
                'nama' => "Student $i",
                'kelas_id' => $this->testClass->id,
                'no_hp_ortu' => sprintf('6281234567%02d', $i),
                'is_active' => true,
            ]);
        }
        
        $fakePhoto = $this->generateFakePhotoBase64();
        
        // Act: Rapid scan all students
        $startTime = microtime(true);
        
        foreach ($students as $student) {
            $response = $this->postJson('/api/attendance/scan', [
                'nis' => $student->nis,
                'photo_base64' => $fakePhoto,
                'action' => 'check_in',
            ]);
            
            $response->assertStatus(200);
        }
        
        $duration = microtime(true) - $startTime;
        
        // Assert: All completed in reasonable time (< 5 seconds for 10 scans)
        $this->assertLessThan(5, $duration, "Multiple scans took too long: {$duration}s");
        
        // Assert: All records created
        $this->assertEquals(10, AttendanceRecord::whereDate('date', Carbon::today())->count());
    }

    /**
     * Task 17.8-17.9: Test scanner interface page loads
     * 
     * @test
     */
    public function test_scanner_interface_loads()
    {
        $response = $this->get('/attendance/scanner');
        
        $response->assertStatus(200);
        $response->assertSee('QR Code Scanner');
        $response->assertSee('video'); // Video element for webcam
        $response->assertSee('jsQR'); // QR scanner library
    }

    /**
     * Task 17.10-17.11: Test views are responsive
     * 
     * @test
     */
    public function test_views_contain_responsive_classes()
    {
        $routes = [
            '/attendance/dashboard',
            '/attendance/scanner',
            '/attendance/students',
            '/attendance/reports',
            '/attendance/settings',
        ];
        
        foreach ($routes as $route) {
            $response = $this->get($route);
            $response->assertStatus(200);
            
            // Check for Tailwind responsive classes
            $content = $response->getContent();
            $this->assertStringContainsString('md:', $content, "Route $route missing responsive classes");
        }
    }

    // Helper Methods

    protected function generateFakePhotoBase64(): string
    {
        $img = imagecreate(100, 100);
        imagecolorallocate($img, 255, 255, 255);
        ob_start();
        imagejpeg($img, null, 85);
        $data = ob_get_clean();
        imagedestroy($img);
        
        return 'data:image/jpeg;base64,' . base64_encode($data);
    }

    protected function saveTestPhoto(string $type): string
    {
        $path = "attendance/photos/{$this->testStudent->nis}/" . Carbon::today()->format('Y-m-d') . "/{$type}_" . time() . '.jpg';
        
        $img = imagecreate(100, 100);
        ob_start();
        imagejpeg($img, null, 85);
        $data = ob_get_clean();
        imagedestroy($img);
        
        Storage::disk('local')->put($path, $data);
        
        return $path;
    }
}
