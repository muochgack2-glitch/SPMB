<?php

namespace Tests\Unit;

use App\Models\AttendanceClass;
use App\Models\AttendanceStudent;
use App\Models\AttendanceRecord;
use App\Models\AttendanceLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AttendanceStudentTest extends TestCase
{
    use RefreshDatabase;

    protected AttendanceClass $class;
    protected AttendanceStudent $student;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test class
        $this->class = AttendanceClass::create([
            'nama_kelas' => '12 RPL A',
            'tingkat' => '12',
            'jurusan' => 'RPL',
            'is_active' => true,
        ]);

        // Create a test student
        $this->student = AttendanceStudent::create([
            'nis' => '12345',
            'nama' => 'Test Student',
            'kelas_id' => $this->class->id,
            'no_hp_ortu' => '628123456789',
            'qr_code_path' => 'attendance/qrcodes/12345.png',
            'foto_profil' => 'attendance/photos/12345/profile.jpg',
            'is_active' => true,
        ]);
    }

    #[Test]
    public function it_has_fillable_attributes()
    {
        $fillable = [
            'nis',
            'nama',
            'kelas_id',
            'no_hp_ortu',
            'qr_code_path',
            'foto_profil',
            'is_active',
        ];

        $this->assertEquals($fillable, $this->student->getFillable());
    }

    #[Test]
    public function it_casts_is_active_to_boolean()
    {
        $this->assertIsBool($this->student->is_active);
    }

    #[Test]
    public function it_belongs_to_a_class()
    {
        $this->assertInstanceOf(AttendanceClass::class, $this->student->kelas);
        $this->assertEquals($this->class->id, $this->student->kelas->id);
    }

    #[Test]
    public function it_has_many_attendance_records()
    {
        // Create attendance records
        AttendanceRecord::create([
            'student_id' => $this->student->id,
            'date' => Carbon::today(),
            'check_in_time' => '07:00:00',
            'status' => 'hadir',
        ]);

        AttendanceRecord::create([
            'student_id' => $this->student->id,
            'date' => Carbon::yesterday(),
            'check_in_time' => '07:15:00',
            'status' => 'terlambat',
        ]);

        $this->assertCount(2, $this->student->attendanceRecords);
        $this->assertInstanceOf(AttendanceRecord::class, $this->student->attendanceRecords->first());
    }

    #[Test]
    public function it_has_many_logs()
    {
        // Create logs
        AttendanceLog::create([
            'student_id' => $this->student->id,
            'action' => 'check_in',
            'message' => 'Test log',
            'status' => 'success',
        ]);

        AttendanceLog::create([
            'student_id' => $this->student->id,
            'action' => 'check_out',
            'message' => 'Test log 2',
            'status' => 'success',
        ]);

        $this->assertCount(2, $this->student->logs);
        $this->assertInstanceOf(AttendanceLog::class, $this->student->logs->first());
    }

    /** @test */
    public function it_returns_qr_code_url_when_path_exists()
    {
        Storage::fake('public');
        
        $student = AttendanceStudent::create([
            'nis' => '54321',
            'nama' => 'Test Student 2',
            'kelas_id' => $this->class->id,
            'qr_code_path' => 'attendance/qrcodes/54321.png',
            'is_active' => true,
        ]);

        $this->assertNotNull($student->qr_code_url);
        $this->assertStringContainsString('attendance/qrcodes/54321.png', $student->qr_code_url);
    }

    /** @test */
    public function it_returns_null_qr_code_url_when_path_is_null()
    {
        $student = AttendanceStudent::create([
            'nis' => '99999',
            'nama' => 'Test Student 3',
            'kelas_id' => $this->class->id,
            'qr_code_path' => null,
            'is_active' => true,
        ]);

        $this->assertNull($student->qr_code_url);
    }

    /** @test */
    public function it_gets_today_attendance_record()
    {
        // Create today's attendance
        $todayRecord = AttendanceRecord::create([
            'student_id' => $this->student->id,
            'date' => Carbon::today(),
            'check_in_time' => '07:00:00',
            'status' => 'hadir',
        ]);

        // Create yesterday's attendance
        AttendanceRecord::create([
            'student_id' => $this->student->id,
            'date' => Carbon::yesterday(),
            'check_in_time' => '07:15:00',
            'status' => 'terlambat',
        ]);

        $result = $this->student->getTodayAttendance();

        $this->assertNotNull($result);
        $this->assertEquals($todayRecord->id, $result->id);
        $this->assertEquals(Carbon::today()->toDateString(), $result->date->toDateString());
    }

    /** @test */
    public function it_returns_null_when_no_attendance_today()
    {
        $result = $this->student->getTodayAttendance();

        $this->assertNull($result);
    }

    /** @test */
    public function it_checks_if_student_has_checked_in_today()
    {
        // No attendance yet
        $this->assertFalse($this->student->hasCheckedInToday());

        // Create today's attendance with check-in
        AttendanceRecord::create([
            'student_id' => $this->student->id,
            'date' => Carbon::today(),
            'check_in_time' => '07:00:00',
            'status' => 'hadir',
        ]);

        // Refresh the student to clear any cached relationships
        $this->student->refresh();

        $this->assertTrue($this->student->hasCheckedInToday());
    }

    /** @test */
    public function it_checks_if_student_has_checked_out_today()
    {
        // No attendance yet
        $this->assertFalse($this->student->hasCheckedOutToday());

        // Create today's attendance with check-in only
        AttendanceRecord::create([
            'student_id' => $this->student->id,
            'date' => Carbon::today(),
            'check_in_time' => '07:00:00',
            'status' => 'hadir',
        ]);

        $this->student->refresh();
        $this->assertFalse($this->student->hasCheckedOutToday());

        // Add check-out
        $record = $this->student->getTodayAttendance();
        $record->update(['check_out_time' => '15:00:00']);

        $this->student->refresh();
        $this->assertTrue($this->student->hasCheckedOutToday());
    }

    /** @test */
    public function it_uses_correct_table_name()
    {
        $this->assertEquals('attendance_students', $this->student->getTable());
    }

    /** @test */
    public function nis_is_unique()
    {
        $this->expectException(\Illuminate\Database\UniqueConstraintViolationException::class);

        AttendanceStudent::create([
            'nis' => '12345', // Same NIS as setUp student
            'nama' => 'Another Student',
            'kelas_id' => $this->class->id,
            'is_active' => true,
        ]);
    }
}
