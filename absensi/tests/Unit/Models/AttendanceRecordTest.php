<?php

namespace Tests\Unit\Models;

use App\Models\AttendanceRecord;
use App\Models\AttendanceStudent;
use App\Models\AttendanceClass;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class AttendanceRecordTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a class
        $this->class = AttendanceClass::create([
            'nama_kelas' => '12 RPL 1',
            'tingkat' => '12',
            'jurusan' => 'RPL',
            'is_active' => true,
        ]);
        
        // Create a student
        $this->student = AttendanceStudent::create([
            'nis' => '12345',
            'nama' => 'Test Student',
            'kelas_id' => $this->class->id,
            'no_hp_ortu' => '628123456789',
            'is_active' => true,
        ]);
    }

    public function test_attendance_record_has_fillable_fields()
    {
        $record = new AttendanceRecord([
            'student_id' => $this->student->id,
            'date' => Carbon::today(),
            'check_in_time' => '07:00:00',
            'check_out_time' => '15:00:00',
            'check_in_photo' => 'attendance/photos/12345/2024-01-15/checkin_070000.jpg',
            'check_out_photo' => 'attendance/photos/12345/2024-01-15/checkout_150000.jpg',
            'status' => 'hadir',
            'notes' => 'Test note',
        ]);

        $this->assertEquals($this->student->id, $record->student_id);
        $this->assertEquals('hadir', $record->status);
        $this->assertEquals('Test note', $record->notes);
    }

    public function test_attendance_record_casts_dates_correctly()
    {
        $record = AttendanceRecord::create([
            'student_id' => $this->student->id,
            'date' => '2024-01-15',
            'check_in_time' => '07:00:00',
            'check_out_time' => '15:00:00',
            'status' => 'hadir',
        ]);

        $this->assertInstanceOf(Carbon::class, $record->date);
        $this->assertEquals('2024-01-15', $record->date->format('Y-m-d'));
    }

    public function test_attendance_record_belongs_to_student()
    {
        $record = AttendanceRecord::create([
            'student_id' => $this->student->id,
            'date' => Carbon::today(),
            'check_in_time' => '07:00:00',
            'status' => 'hadir',
        ]);

        $this->assertInstanceOf(AttendanceStudent::class, $record->student);
        $this->assertEquals($this->student->id, $record->student->id);
        $this->assertEquals('Test Student', $record->student->nama);
    }

    public function test_today_scope_filters_todays_records()
    {
        // Create today's record
        AttendanceRecord::create([
            'student_id' => $this->student->id,
            'date' => Carbon::today(),
            'check_in_time' => '07:00:00',
            'status' => 'hadir',
        ]);

        // Create yesterday's record
        AttendanceRecord::create([
            'student_id' => $this->student->id,
            'date' => Carbon::yesterday(),
            'check_in_time' => '07:00:00',
            'status' => 'hadir',
        ]);

        $todayRecords = AttendanceRecord::today()->get();

        $this->assertCount(1, $todayRecords);
        $this->assertTrue($todayRecords->first()->date->isToday());
    }

    public function test_by_status_scope_filters_by_status()
    {
        AttendanceRecord::create([
            'student_id' => $this->student->id,
            'date' => Carbon::today(),
            'check_in_time' => '07:00:00',
            'status' => 'hadir',
        ]);

        AttendanceRecord::create([
            'student_id' => $this->student->id,
            'date' => Carbon::yesterday(),
            'check_in_time' => '07:30:00',
            'status' => 'terlambat',
        ]);

        $hadirRecords = AttendanceRecord::byStatus('hadir')->get();
        $terlambatRecords = AttendanceRecord::byStatus('terlambat')->get();

        $this->assertCount(1, $hadirRecords);
        $this->assertEquals('hadir', $hadirRecords->first()->status);
        
        $this->assertCount(1, $terlambatRecords);
        $this->assertEquals('terlambat', $terlambatRecords->first()->status);
    }

    public function test_by_class_scope_filters_by_class()
    {
        // Create another class and student
        $class2 = AttendanceClass::create([
            'nama_kelas' => '11 TKJ 1',
            'tingkat' => '11',
            'jurusan' => 'TKJ',
            'is_active' => true,
        ]);
        
        $student2 = AttendanceStudent::create([
            'nis' => '54321',
            'nama' => 'Another Student',
            'kelas_id' => $class2->id,
            'no_hp_ortu' => '628987654321',
            'is_active' => true,
        ]);

        // Create records for both students
        AttendanceRecord::create([
            'student_id' => $this->student->id,
            'date' => Carbon::today(),
            'check_in_time' => '07:00:00',
            'status' => 'hadir',
        ]);

        AttendanceRecord::create([
            'student_id' => $student2->id,
            'date' => Carbon::today(),
            'check_in_time' => '07:00:00',
            'status' => 'hadir',
        ]);

        $class1Records = AttendanceRecord::byClass($this->class->id)->get();
        $class2Records = AttendanceRecord::byClass($class2->id)->get();

        $this->assertCount(1, $class1Records);
        $this->assertEquals($this->student->id, $class1Records->first()->student_id);
        
        $this->assertCount(1, $class2Records);
        $this->assertEquals($student2->id, $class2Records->first()->student_id);
    }

    public function test_check_in_photo_url_accessor_returns_storage_url()
    {
        $record = AttendanceRecord::create([
            'student_id' => $this->student->id,
            'date' => Carbon::today(),
            'check_in_time' => '07:00:00',
            'check_in_photo' => 'attendance/photos/12345/2024-01-15/checkin_070000.jpg',
            'status' => 'hadir',
        ]);

        $url = $record->check_in_photo_url;

        $this->assertNotNull($url);
        $this->assertStringContainsString('attendance/photos/12345/2024-01-15/checkin_070000.jpg', $url);
    }

    public function test_check_in_photo_url_accessor_returns_null_when_no_photo()
    {
        $record = AttendanceRecord::create([
            'student_id' => $this->student->id,
            'date' => Carbon::today(),
            'check_in_time' => '07:00:00',
            'status' => 'hadir',
        ]);

        $this->assertNull($record->check_in_photo_url);
    }

    public function test_check_out_photo_url_accessor_returns_storage_url()
    {
        $record = AttendanceRecord::create([
            'student_id' => $this->student->id,
            'date' => Carbon::today(),
            'check_in_time' => '07:00:00',
            'check_out_time' => '15:00:00',
            'check_out_photo' => 'attendance/photos/12345/2024-01-15/checkout_150000.jpg',
            'status' => 'hadir',
        ]);

        $url = $record->check_out_photo_url;

        $this->assertNotNull($url);
        $this->assertStringContainsString('attendance/photos/12345/2024-01-15/checkout_150000.jpg', $url);
    }

    public function test_check_out_photo_url_accessor_returns_null_when_no_photo()
    {
        $record = AttendanceRecord::create([
            'student_id' => $this->student->id,
            'date' => Carbon::today(),
            'check_in_time' => '07:00:00',
            'status' => 'hadir',
        ]);

        $this->assertNull($record->check_out_photo_url);
    }

    public function test_status_label_accessor_returns_human_readable_labels()
    {
        $hadirRecord = AttendanceRecord::create([
            'student_id' => $this->student->id,
            'date' => Carbon::today(),
            'check_in_time' => '07:00:00',
            'status' => 'hadir',
        ]);

        $terlambatRecord = AttendanceRecord::create([
            'student_id' => $this->student->id,
            'date' => Carbon::yesterday(),
            'check_in_time' => '07:30:00',
            'status' => 'terlambat',
        ]);

        $alphaRecord = AttendanceRecord::create([
            'student_id' => $this->student->id,
            'date' => Carbon::today()->subDays(2),
            'status' => 'alpha',
        ]);

        $this->assertEquals('Hadir', $hadirRecord->status_label);
        $this->assertEquals('Terlambat', $terlambatRecord->status_label);
        $this->assertEquals('Alpha', $alphaRecord->status_label);
    }

    public function test_status_label_accessor_handles_all_valid_statuses()
    {
        // Test all valid statuses from the migration enum
        $statuses = [
            'hadir' => 'Hadir',
            'terlambat' => 'Terlambat',
            'alpha' => 'Alpha',
        ];

        $index = 0;
        foreach ($statuses as $status => $expectedLabel) {
            $record = AttendanceRecord::create([
                'student_id' => $this->student->id,
                'date' => Carbon::today()->subDays($index),
                'check_in_time' => $status === 'alpha' ? null : '07:00:00',
                'status' => $status,
            ]);

            $this->assertEquals($expectedLabel, $record->status_label);
            $index++;
        }
    }
}
