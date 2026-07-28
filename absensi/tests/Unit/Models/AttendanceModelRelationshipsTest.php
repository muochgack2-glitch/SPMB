<?php

namespace Tests\Unit\Models;

use App\Models\AttendanceClass;
use App\Models\AttendanceStudent;
use App\Models\AttendanceRecord;
use App\Models\AttendanceLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class AttendanceModelRelationshipsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test AttendanceClass has many students relationship
     */
    public function test_attendance_class_has_many_students(): void
    {
        // Create a class
        $class = AttendanceClass::create([
            'nama_kelas' => '12 RPL',
            'tingkat' => '12',
            'jurusan' => 'RPL',
            'is_active' => true,
        ]);

        // Create students for this class
        $student1 = AttendanceStudent::create([
            'nis' => '12345',
            'nama' => 'Budi Santoso',
            'kelas_id' => $class->id,
            'no_hp_ortu' => '628123456789',
            'is_active' => true,
        ]);

        $student2 = AttendanceStudent::create([
            'nis' => '12346',
            'nama' => 'Siti Aminah',
            'kelas_id' => $class->id,
            'no_hp_ortu' => '628123456790',
            'is_active' => true,
        ]);

        // Test the relationship
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $class->students);
        $this->assertCount(2, $class->students);
        $this->assertTrue($class->students->contains($student1));
        $this->assertTrue($class->students->contains($student2));
    }

    /**
     * Test AttendanceStudent belongs to class relationship
     */
    public function test_attendance_student_belongs_to_class(): void
    {
        // Create a class
        $class = AttendanceClass::create([
            'nama_kelas' => '11 TKJ',
            'tingkat' => '11',
            'jurusan' => 'TKJ',
            'is_active' => true,
        ]);

        // Create a student
        $student = AttendanceStudent::create([
            'nis' => '11001',
            'nama' => 'Ahmad Hidayat',
            'kelas_id' => $class->id,
            'no_hp_ortu' => '628987654321',
            'is_active' => true,
        ]);

        // Test the relationship
        $this->assertInstanceOf(AttendanceClass::class, $student->kelas);
        $this->assertEquals($class->id, $student->kelas->id);
        $this->assertEquals('11 TKJ', $student->kelas->nama_kelas);
    }

    /**
     * Test AttendanceStudent has many attendance records relationship
     */
    public function test_attendance_student_has_many_attendance_records(): void
    {
        // Create class and student
        $class = AttendanceClass::create([
            'nama_kelas' => '10 MM',
            'tingkat' => '10',
            'jurusan' => 'Multimedia',
            'is_active' => true,
        ]);

        $student = AttendanceStudent::create([
            'nis' => '10001',
            'nama' => 'Dewi Lestari',
            'kelas_id' => $class->id,
            'no_hp_ortu' => '628111222333',
            'is_active' => true,
        ]);

        // Create attendance records
        $record1 = AttendanceRecord::create([
            'student_id' => $student->id,
            'date' => Carbon::today(),
            'check_in_time' => '07:00:00',
            'status' => 'hadir',
        ]);

        $record2 = AttendanceRecord::create([
            'student_id' => $student->id,
            'date' => Carbon::yesterday(),
            'check_in_time' => '07:30:00',
            'status' => 'terlambat',
        ]);

        // Test the relationship
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $student->attendanceRecords);
        $this->assertCount(2, $student->attendanceRecords);
        $this->assertTrue($student->attendanceRecords->contains($record1));
        $this->assertTrue($student->attendanceRecords->contains($record2));
    }

    /**
     * Test AttendanceStudent has many logs relationship
     */
    public function test_attendance_student_has_many_logs(): void
    {
        // Create class and student
        $class = AttendanceClass::create([
            'nama_kelas' => '12 OTKP',
            'tingkat' => '12',
            'jurusan' => 'OTKP',
            'is_active' => true,
        ]);

        $student = AttendanceStudent::create([
            'nis' => '12999',
            'nama' => 'Rina Sari',
            'kelas_id' => $class->id,
            'no_hp_ortu' => '628555666777',
            'is_active' => true,
        ]);

        // Create logs
        $log1 = AttendanceLog::create([
            'student_id' => $student->id,
            'action' => 'check_in',
            'message' => 'Student checked in successfully',
            'status' => 'success',
        ]);

        $log2 = AttendanceLog::create([
            'student_id' => $student->id,
            'action' => 'notification',
            'message' => 'WhatsApp notification sent',
            'status' => 'success',
        ]);

        // Test the relationship
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $student->logs);
        $this->assertCount(2, $student->logs);
        $this->assertTrue($student->logs->contains($log1));
        $this->assertTrue($student->logs->contains($log2));
    }

    /**
     * Test AttendanceRecord belongs to student relationship
     */
    public function test_attendance_record_belongs_to_student(): void
    {
        // Create class and student
        $class = AttendanceClass::create([
            'nama_kelas' => '11 AKL',
            'tingkat' => '11',
            'jurusan' => 'AKL',
            'is_active' => true,
        ]);

        $student = AttendanceStudent::create([
            'nis' => '11777',
            'nama' => 'Eko Prasetyo',
            'kelas_id' => $class->id,
            'no_hp_ortu' => '628444555666',
            'is_active' => true,
        ]);

        // Create attendance record
        $record = AttendanceRecord::create([
            'student_id' => $student->id,
            'date' => Carbon::today(),
            'check_in_time' => '06:55:00',
            'status' => 'hadir',
        ]);

        // Test the relationship
        $this->assertInstanceOf(AttendanceStudent::class, $record->student);
        $this->assertEquals($student->id, $record->student->id);
        $this->assertEquals('Eko Prasetyo', $record->student->nama);
    }

    /**
     * Test AttendanceLog belongs to student relationship
     */
    public function test_attendance_log_belongs_to_student(): void
    {
        // Create class and student
        $class = AttendanceClass::create([
            'nama_kelas' => '10 BDP',
            'tingkat' => '10',
            'jurusan' => 'BDP',
            'is_active' => true,
        ]);

        $student = AttendanceStudent::create([
            'nis' => '10555',
            'nama' => 'Fitri Nurhaliza',
            'kelas_id' => $class->id,
            'no_hp_ortu' => '628222333444',
            'is_active' => true,
        ]);

        // Create log
        $log = AttendanceLog::create([
            'student_id' => $student->id,
            'action' => 'qr_scan',
            'message' => 'QR Code scanned',
            'status' => 'success',
        ]);

        // Test the relationship
        $this->assertInstanceOf(AttendanceStudent::class, $log->student);
        $this->assertEquals($student->id, $log->student->id);
        $this->assertEquals('Fitri Nurhaliza', $log->student->nama);
    }

    /**
     * Test eager loading works for AttendanceClass with students
     */
    public function test_eager_loading_class_with_students(): void
    {
        // Create classes with students
        $class1 = AttendanceClass::create([
            'nama_kelas' => '12 RPL A',
            'tingkat' => '12',
            'jurusan' => 'RPL',
            'is_active' => true,
        ]);

        $class2 = AttendanceClass::create([
            'nama_kelas' => '12 RPL B',
            'tingkat' => '12',
            'jurusan' => 'RPL',
            'is_active' => true,
        ]);

        AttendanceStudent::create([
            'nis' => '12001',
            'nama' => 'Student 1',
            'kelas_id' => $class1->id,
            'is_active' => true,
        ]);

        AttendanceStudent::create([
            'nis' => '12002',
            'nama' => 'Student 2',
            'kelas_id' => $class1->id,
            'is_active' => true,
        ]);

        AttendanceStudent::create([
            'nis' => '12003',
            'nama' => 'Student 3',
            'kelas_id' => $class2->id,
            'is_active' => true,
        ]);

        // Test eager loading
        $classes = AttendanceClass::with('students')->get();

        $this->assertCount(2, $classes);
        $this->assertCount(2, $classes->firstWhere('id', $class1->id)->students);
        $this->assertCount(1, $classes->firstWhere('id', $class2->id)->students);
    }

    /**
     * Test eager loading works for AttendanceStudent with kelas, records, and logs
     */
    public function test_eager_loading_student_with_all_relationships(): void
    {
        // Create class
        $class = AttendanceClass::create([
            'nama_kelas' => '11 TKJ A',
            'tingkat' => '11',
            'jurusan' => 'TKJ',
            'is_active' => true,
        ]);

        // Create student
        $student = AttendanceStudent::create([
            'nis' => '11888',
            'nama' => 'Budi Setiawan',
            'kelas_id' => $class->id,
            'no_hp_ortu' => '628999888777',
            'is_active' => true,
        ]);

        // Create attendance record
        AttendanceRecord::create([
            'student_id' => $student->id,
            'date' => Carbon::today(),
            'check_in_time' => '07:15:00',
            'status' => 'hadir',
        ]);

        // Create log
        AttendanceLog::create([
            'student_id' => $student->id,
            'action' => 'check_in',
            'message' => 'Check in successful',
            'status' => 'success',
        ]);

        // Test eager loading all relationships
        $loadedStudent = AttendanceStudent::with(['kelas', 'attendanceRecords', 'logs'])->find($student->id);

        $this->assertNotNull($loadedStudent->kelas);
        $this->assertEquals('11 TKJ A', $loadedStudent->kelas->nama_kelas);
        $this->assertCount(1, $loadedStudent->attendanceRecords);
        $this->assertCount(1, $loadedStudent->logs);
    }

    /**
     * Test inverse relationship works - from record to student to class
     */
    public function test_inverse_relationship_record_to_student_to_class(): void
    {
        // Create full chain
        $class = AttendanceClass::create([
            'nama_kelas' => '10 RPL',
            'tingkat' => '10',
            'jurusan' => 'RPL',
            'is_active' => true,
        ]);

        $student = AttendanceStudent::create([
            'nis' => '10101',
            'nama' => 'Andi Wijaya',
            'kelas_id' => $class->id,
            'no_hp_ortu' => '628111000999',
            'is_active' => true,
        ]);

        $record = AttendanceRecord::create([
            'student_id' => $student->id,
            'date' => Carbon::today(),
            'check_in_time' => '07:05:00',
            'status' => 'hadir',
        ]);

        // Test inverse chain: record -> student -> class
        $this->assertInstanceOf(AttendanceStudent::class, $record->student);
        $this->assertInstanceOf(AttendanceClass::class, $record->student->kelas);
        $this->assertEquals('10 RPL', $record->student->kelas->nama_kelas);
        $this->assertEquals('Andi Wijaya', $record->student->nama);
    }

    /**
     * Test inverse relationship works - from log to student to class
     */
    public function test_inverse_relationship_log_to_student_to_class(): void
    {
        // Create full chain
        $class = AttendanceClass::create([
            'nama_kelas' => '12 MM',
            'tingkat' => '12',
            'jurusan' => 'Multimedia',
            'is_active' => true,
        ]);

        $student = AttendanceStudent::create([
            'nis' => '12202',
            'nama' => 'Sari Wulandari',
            'kelas_id' => $class->id,
            'no_hp_ortu' => '628777888999',
            'is_active' => true,
        ]);

        $log = AttendanceLog::create([
            'student_id' => $student->id,
            'action' => 'notification',
            'message' => 'Parent notified',
            'status' => 'success',
        ]);

        // Test inverse chain: log -> student -> class
        $this->assertInstanceOf(AttendanceStudent::class, $log->student);
        $this->assertInstanceOf(AttendanceClass::class, $log->student->kelas);
        $this->assertEquals('12 MM', $log->student->kelas->nama_kelas);
        $this->assertEquals('Sari Wulandari', $log->student->nama);
    }

    /**
     * Test cascade deletion - student deletion cascades to records but sets NULL for logs
     */
    public function test_cascade_deletion_student_to_records_and_logs(): void
    {
        // Create class and student
        $class = AttendanceClass::create([
            'nama_kelas' => '11 OTKP',
            'tingkat' => '11',
            'jurusan' => 'OTKP',
            'is_active' => true,
        ]);

        $student = AttendanceStudent::create([
            'nis' => '11303',
            'nama' => 'Rini Astuti',
            'kelas_id' => $class->id,
            'no_hp_ortu' => '628555444333',
            'is_active' => true,
        ]);

        // Create related records
        $recordId = AttendanceRecord::create([
            'student_id' => $student->id,
            'date' => Carbon::today(),
            'check_in_time' => '07:00:00',
            'status' => 'hadir',
        ])->id;

        $logId = AttendanceLog::create([
            'student_id' => $student->id,
            'action' => 'check_in',
            'message' => 'Checked in',
            'status' => 'success',
        ])->id;

        // Delete student
        $student->delete();

        // Verify cascade deletion for records (ON DELETE CASCADE)
        $this->assertNull(AttendanceRecord::find($recordId));
        
        // Verify logs are preserved but student_id is set to NULL (ON DELETE SET NULL)
        $log = AttendanceLog::find($logId);
        $this->assertNotNull($log);
        $this->assertNull($log->student_id);
        $this->assertEquals('check_in', $log->action);
    }

    /**
     * Test multiple students with multiple records and logs
     */
    public function test_multiple_students_with_multiple_records_and_logs(): void
    {
        // Create class
        $class = AttendanceClass::create([
            'nama_kelas' => '12 TKJ',
            'tingkat' => '12',
            'jurusan' => 'TKJ',
            'is_active' => true,
        ]);

        // Create multiple students
        $student1 = AttendanceStudent::create([
            'nis' => '12401',
            'nama' => 'Ali Rahman',
            'kelas_id' => $class->id,
            'is_active' => true,
        ]);

        $student2 = AttendanceStudent::create([
            'nis' => '12402',
            'nama' => 'Maya Sari',
            'kelas_id' => $class->id,
            'is_active' => true,
        ]);

        // Create records for each student
        AttendanceRecord::create([
            'student_id' => $student1->id,
            'date' => Carbon::today(),
            'check_in_time' => '07:00:00',
            'status' => 'hadir',
        ]);

        AttendanceRecord::create([
            'student_id' => $student1->id,
            'date' => Carbon::yesterday(),
            'check_in_time' => '07:30:00',
            'status' => 'terlambat',
        ]);

        AttendanceRecord::create([
            'student_id' => $student2->id,
            'date' => Carbon::today(),
            'check_in_time' => '06:50:00',
            'status' => 'hadir',
        ]);

        // Create logs
        AttendanceLog::create([
            'student_id' => $student1->id,
            'action' => 'check_in',
            'status' => 'success',
        ]);

        AttendanceLog::create([
            'student_id' => $student2->id,
            'action' => 'check_in',
            'status' => 'success',
        ]);

        // Verify relationships
        $this->assertCount(2, $student1->attendanceRecords);
        $this->assertCount(1, $student2->attendanceRecords);
        $this->assertCount(1, $student1->logs);
        $this->assertCount(1, $student2->logs);
        $this->assertCount(2, $class->students);
    }

    /**
     * Test eager loading with nested relationships
     */
    public function test_eager_loading_with_nested_relationships(): void
    {
        // Create class
        $class = AttendanceClass::create([
            'nama_kelas' => '10 AKL',
            'tingkat' => '10',
            'jurusan' => 'AKL',
            'is_active' => true,
        ]);

        // Create student
        $student = AttendanceStudent::create([
            'nis' => '10501',
            'nama' => 'Dimas Putra',
            'kelas_id' => $class->id,
            'is_active' => true,
        ]);

        // Create record
        AttendanceRecord::create([
            'student_id' => $student->id,
            'date' => Carbon::today(),
            'check_in_time' => '07:00:00',
            'status' => 'hadir',
        ]);

        // Test nested eager loading from record through student to class
        $record = AttendanceRecord::with('student.kelas')->first();
        
        $this->assertNotNull($record->student);
        $this->assertNotNull($record->student->kelas);
        $this->assertEquals('10 AKL', $record->student->kelas->nama_kelas);
    }

    /**
     * Test class cannot be deleted when it has students (RESTRICT constraint)
     */
    public function test_class_deletion_restricted_when_has_students(): void
    {
        // Create class with student
        $class = AttendanceClass::create([
            'nama_kelas' => '11 BDP',
            'tingkat' => '11',
            'jurusan' => 'BDP',
            'is_active' => true,
        ]);

        AttendanceStudent::create([
            'nis' => '11601',
            'nama' => 'Nina Kartika',
            'kelas_id' => $class->id,
            'is_active' => true,
        ]);

        // Attempt to delete class should throw exception due to foreign key constraint
        $this->expectException(\Illuminate\Database\QueryException::class);
        $class->delete();
    }

    /**
     * Test student with no records or logs
     */
    public function test_student_with_no_records_or_logs(): void
    {
        $class = AttendanceClass::create([
            'nama_kelas' => '12 OTKP',
            'tingkat' => '12',
            'jurusan' => 'OTKP',
            'is_active' => true,
        ]);

        $student = AttendanceStudent::create([
            'nis' => '12701',
            'nama' => 'Fikri Ramadan',
            'kelas_id' => $class->id,
            'is_active' => true,
        ]);

        // Test empty relationships
        $this->assertCount(0, $student->attendanceRecords);
        $this->assertCount(0, $student->logs);
        $this->assertInstanceOf(AttendanceClass::class, $student->kelas);
    }

    /**
     * Test querying through relationships
     */
    public function test_querying_through_relationships(): void
    {
        // Create class with students
        $class = AttendanceClass::create([
            'nama_kelas' => '11 RPL',
            'tingkat' => '11',
            'jurusan' => 'RPL',
            'is_active' => true,
        ]);

        $student1 = AttendanceStudent::create([
            'nis' => '11801',
            'nama' => 'Adi Nugroho',
            'kelas_id' => $class->id,
            'is_active' => true,
        ]);

        $student2 = AttendanceStudent::create([
            'nis' => '11802',
            'nama' => 'Putri Lestari',
            'kelas_id' => $class->id,
            'is_active' => true,
        ]);

        // Create records with different statuses
        AttendanceRecord::create([
            'student_id' => $student1->id,
            'date' => Carbon::today(),
            'check_in_time' => '07:00:00',
            'status' => 'hadir',
        ]);

        AttendanceRecord::create([
            'student_id' => $student2->id,
            'date' => Carbon::today(),
            'check_in_time' => '08:00:00',
            'status' => 'terlambat',
        ]);

        // Query students through class who have 'hadir' status today
        $hadirStudents = $class->students()
            ->whereHas('attendanceRecords', function ($query) {
                $query->whereDate('date', Carbon::today())
                      ->where('status', 'hadir');
            })
            ->get();

        $this->assertCount(1, $hadirStudents);
        $this->assertEquals('Adi Nugroho', $hadirStudents->first()->nama);
    }

    /**
     * Test log with null student_id (orphaned log)
     */
    public function test_log_with_null_student_id(): void
    {
        // Create a log without student (or with null student_id)
        $log = AttendanceLog::create([
            'student_id' => null,
            'action' => 'error',
            'message' => 'System error',
            'status' => 'failed',
        ]);

        // Verify log exists and student relationship returns null
        $this->assertNotNull($log);
        $this->assertNull($log->student_id);
        $this->assertNull($log->student);
    }
}
