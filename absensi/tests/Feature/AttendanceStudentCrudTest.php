<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\AttendanceStudent;
use App\Models\AttendanceClass;
use App\Services\QRCodeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AttendanceStudentCrudTest extends TestCase
{
    use RefreshDatabase;

    protected $qrCodeService;
    protected $kelas;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        Storage::fake('public');
        $this->qrCodeService = $this->app->make(QRCodeService::class);
        
        // Create dummy user for authentication
        $this->user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password')
        ]);
        
        // Create sample class
        $this->kelas = AttendanceClass::create([
            'nama_kelas' => '10 RPL',
            'tingkat' => 10,
            'jurusan' => 'RPL',
            'is_active' => true
        ]);
    }

    public function test_it_can_display_students_index_page()
    {
        $response = $this->actingAs($this->user)->get(route('attendance.students.index'));
        
        $response->assertStatus(200);
        $response->assertViewIs('attendance.students.index');
        $response->assertViewHas('students');
    }

    public function test_it_can_display_create_student_form()
    {
        $response = $this->actingAs($this->user)->get(route('attendance.students.create'));
        
        $response->assertStatus(200);
        $response->assertViewIs('attendance.students.create');
        $response->assertViewHas('classes');
    }

    public function test_it_can_create_new_student_with_qr_code()
    {
        $studentData = [
            'nis' => '24001',
            'nama' => 'Budi Santoso',
            'kelas_id' => $this->kelas->id,
            'no_hp_ortu' => '6281234567890',
            'is_active' => true
        ];

        $response = $this->actingAs($this->user)->post(route('attendance.students.store'), $studentData);
        
        $response->assertRedirect(route('attendance.students.index'));
        $response->assertSessionHas('success');

        // Verify student created
        $this->assertDatabaseHas('attendance_students', [
            'nis' => '24001',
            'nama' => 'Budi Santoso',
            'kelas_id' => $this->kelas->id,
            'no_hp_ortu' => '6281234567890'
        ]);

        // Verify QR code was generated
        $student = AttendanceStudent::where('nis', '24001')->first();
        $this->assertNotNull($student->qr_code_path);
        $this->assertStringContainsString('24001.svg', $student->qr_code_path);
    }

    public function test_it_validates_required_fields_when_creating_student()
    {
        $response = $this->actingAs($this->user)->post(route('attendance.students.store'), []);
        
        $response->assertSessionHasErrors(['nis', 'nama', 'kelas_id']);
    }

    public function test_it_validates_unique_nis()
    {
        AttendanceStudent::create([
            'nis' => '24001',
            'nama' => 'Existing Student',
            'kelas_id' => $this->kelas->id,
            'is_active' => true
        ]);

        $response = $this->actingAs($this->user)->post(route('attendance.students.store'), [
            'nis' => '24001',
            'nama' => 'New Student',
            'kelas_id' => $this->kelas->id,
            'is_active' => true
        ]);
        
        $response->assertSessionHasErrors(['nis']);
    }

    public function test_it_can_display_student_details()
    {
        $student = AttendanceStudent::create([
            'nis' => '24001',
            'nama' => 'Budi Santoso',
            'kelas_id' => $this->kelas->id,
            'no_hp_ortu' => '6281234567890',
            'qr_code_path' => 'attendance/qrcodes/24001.svg',
            'is_active' => true
        ]);

        $response = $this->actingAs($this->user)->get(route('attendance.students.show', $student->id));
        
        $response->assertStatus(200);
        $response->assertViewIs('attendance.students.show');
        $response->assertViewHas('student', $student);
        $response->assertSee('Budi Santoso');
        $response->assertSee('24001');
    }

    public function test_it_can_display_edit_student_form()
    {
        $student = AttendanceStudent::create([
            'nis' => '24001',
            'nama' => 'Budi Santoso',
            'kelas_id' => $this->kelas->id,
            'is_active' => true
        ]);

        $response = $this->actingAs($this->user)->get(route('attendance.students.edit', $student->id));
        
        $response->assertStatus(200);
        $response->assertViewIs('attendance.students.edit');
        $response->assertViewHas('student', $student);
        $response->assertViewHas('classes');
    }

    public function test_it_can_update_existing_student()
    {
        $student = AttendanceStudent::create([
            'nis' => '24001',
            'nama' => 'Budi Santoso',
            'kelas_id' => $this->kelas->id,
            'no_hp_ortu' => '6281234567890',
            'is_active' => true
        ]);

        $updatedData = [
            'nis' => '24001',
            'nama' => 'Budi Santoso Updated',
            'kelas_id' => $this->kelas->id,
            'no_hp_ortu' => '6281234567899',
            'is_active' => true
        ];

        $response = $this->actingAs($this->user)->put(route('attendance.students.update', $student->id), $updatedData);
        
        $response->assertRedirect(route('attendance.students.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('attendance_students', [
            'id' => $student->id,
            'nama' => 'Budi Santoso Updated',
            'no_hp_ortu' => '6281234567899'
        ]);
    }

    public function test_it_can_delete_student()
    {
        $student = AttendanceStudent::create([
            'nis' => '24001',
            'nama' => 'Budi Santoso',
            'kelas_id' => $this->kelas->id,
            'is_active' => true
        ]);

        $response = $this->actingAs($this->user)->delete(route('attendance.students.destroy', $student->id));
        
        $response->assertRedirect(route('attendance.students.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('attendance_students', [
            'id' => $student->id
        ]);
    }

    public function test_it_can_search_students_by_name()
    {
        AttendanceStudent::create([
            'nis' => '24001',
            'nama' => 'Budi Santoso',
            'kelas_id' => $this->kelas->id,
            'is_active' => true
        ]);

        AttendanceStudent::create([
            'nis' => '24002',
            'nama' => 'Siti Nurhaliza',
            'kelas_id' => $this->kelas->id,
            'is_active' => true
        ]);

        $response = $this->actingAs($this->user)->get(route('attendance.students.index', ['search' => 'Budi']));
        
        $response->assertStatus(200);
        $response->assertSee('Budi Santoso');
        $response->assertDontSee('Siti Nurhaliza');
    }

    public function test_it_can_search_students_by_nis()
    {
        AttendanceStudent::create([
            'nis' => '24001',
            'nama' => 'Budi Santoso',
            'kelas_id' => $this->kelas->id,
            'is_active' => true
        ]);

        AttendanceStudent::create([
            'nis' => '24002',
            'nama' => 'Siti Nurhaliza',
            'kelas_id' => $this->kelas->id,
            'is_active' => true
        ]);

        $response = $this->actingAs($this->user)->get(route('attendance.students.index', ['search' => '24001']));
        
        $response->assertStatus(200);
        $response->assertSee('Budi Santoso');
        $response->assertDontSee('Siti Nurhaliza');
    }

    public function test_it_can_filter_students_by_class()
    {
        $kelas2 = AttendanceClass::create([
            'nama_kelas' => '11 RPL',
            'tingkat' => 11,
            'jurusan' => 'RPL',
            'is_active' => true
        ]);

        AttendanceStudent::create([
            'nis' => '24001',
            'nama' => 'Student Kelas 10',
            'kelas_id' => $this->kelas->id,
            'is_active' => true
        ]);

        AttendanceStudent::create([
            'nis' => '24002',
            'nama' => 'Student Kelas 11',
            'kelas_id' => $kelas2->id,
            'is_active' => true
        ]);

        $response = $this->actingAs($this->user)->get(route('attendance.students.index', ['class_id' => $this->kelas->id]));
        
        $response->assertStatus(200);
        $response->assertSee('Student Kelas 10');
        $response->assertDontSee('Student Kelas 11');
    }

    public function test_it_displays_pagination()
    {
        // Create 20 students
        for ($i = 1; $i <= 20; $i++) {
            AttendanceStudent::create([
                'nis' => '2400' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'nama' => 'Student ' . $i,
                'kelas_id' => $this->kelas->id,
                'is_active' => true
            ]);
        }

        $response = $this->actingAs($this->user)->get(route('attendance.students.index'));
        
        $response->assertStatus(200);
        $response->assertViewHas('students');
        
        // Verify pagination exists (default 15 per page)
        $students = $response->viewData('students');
        $this->assertEquals(15, $students->perPage());
        $this->assertEquals(20, $students->total());
    }
}
