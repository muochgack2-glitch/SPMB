<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\AttendanceStudent;
use App\Models\AttendanceClass;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\StudentTemplateExport;
use App\Imports\AttendanceStudentImport;

class AttendanceStudentImportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Bypass authentication for testing
        $this->withoutMiddleware(\Illuminate\Auth\Middleware\Authenticate::class);
        
        // Create test classes
        AttendanceClass::create([
            'nama_kelas' => '10 RPL',
            'tingkat' => 10,
            'jurusan' => 'RPL',
            'is_active' => true
        ]);
        
        AttendanceClass::create([
            'nama_kelas' => '11 TKJ',
            'tingkat' => 11,
            'jurusan' => 'TKJ',
            'is_active' => true
        ]);
    }

    public function test_can_display_import_form()
    {
        $response = $this->get(route('attendance.students.import'));

        $response->assertStatus(200);
        $response->assertViewIs('attendance.students.import');
        $response->assertSee('Import Data Siswa');
        $response->assertSee('Download Template');
    }

    public function test_can_download_import_template()
    {
        $response = $this->get(route('attendance.students.template'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertTrue(
            str_contains($response->headers->get('Content-Disposition'), 'template-siswa-absensi')
        );
    }

    public function test_can_import_valid_excel_file()
    {
        // Create a temporary Excel file with valid data
        $file = UploadedFile::fake()->createWithContent('import.xlsx', 
            $this->createValidExcelContent()
        );

        $response = $this->post(route('attendance.students.import.store'), [
            'file' => $file
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify students were created
        $this->assertDatabaseHas('attendance_students', [
            'nis' => '24001',
            'nama' => 'Ahmad Fauzi'
        ]);

        $this->assertDatabaseHas('attendance_students', [
            'nis' => '24002',
            'nama' => 'Siti Nurhaliza'
        ]);

        // Verify QR codes were generated
        $student = AttendanceStudent::where('nis', '24001')->first();
        $this->assertNotNull($student->qr_code_path);
    }

    public function test_validates_required_file()
    {
        $response = $this->post(route('attendance.students.import.store'), []);

        $response->assertSessionHasErrors('file');
    }

    public function test_validates_file_type()
    {
        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->post(route('attendance.students.import.store'), [
            'file' => $file
        ]);

        $response->assertSessionHasErrors('file');
    }

    public function test_validates_duplicate_nis_in_import()
    {
        // Create existing student
        AttendanceStudent::create([
            'nis' => '24001',
            'nama' => 'Existing Student',
            'kelas_id' => 1,
            'no_hp_ortu' => '628123456789',
            'is_active' => true
        ]);

        // Try to import file with duplicate NIS
        $file = UploadedFile::fake()->createWithContent('import.xlsx', 
            $this->createValidExcelContent()
        );

        $response = $this->post(route('attendance.students.import.store'), [
            'file' => $file
        ]);

        // Should show error about duplicate
        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_validates_invalid_class_id()
    {
        $file = UploadedFile::fake()->createWithContent('import.xlsx', 
            $this->createInvalidClassExcelContent()
        );

        $response = $this->post(route('attendance.students.import.store'), [
            'file' => $file
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_validates_invalid_phone_format()
    {
        $file = UploadedFile::fake()->createWithContent('import.xlsx', 
            $this->createInvalidPhoneExcelContent()
        );

        $response = $this->post(route('attendance.students.import.store'), [
            'file' => $file
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_can_import_large_dataset()
    {
        $file = UploadedFile::fake()->createWithContent('import.xlsx', 
            $this->createLargeExcelContent(50)
        );

        $response = $this->post(route('attendance.students.import.store'), [
            'file' => $file
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify all 50 students were created
        $this->assertEquals(50, AttendanceStudent::count());

        // Verify all have QR codes
        $studentsWithoutQR = AttendanceStudent::whereNull('qr_code_path')->count();
        $this->assertEquals(0, $studentsWithoutQR);
    }

    public function test_generates_qr_codes_after_import()
    {
        $file = UploadedFile::fake()->createWithContent('import.xlsx', 
            $this->createValidExcelContent()
        );

        $this->post(route('attendance.students.import.store'), [
            'file' => $file
        ]);

        // Verify QR codes exist
        $students = AttendanceStudent::all();
        
        foreach ($students as $student) {
            $this->assertNotNull($student->qr_code_path);
            $this->assertStringContainsString($student->nis, $student->qr_code_path);
        }
    }

    public function test_shows_import_summary()
    {
        $file = UploadedFile::fake()->createWithContent('import.xlsx', 
            $this->createValidExcelContent()
        );

        $response = $this->post(route('attendance.students.import.store'), [
            'file' => $file
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $message = session('success');
        $this->assertStringContainsString('berhasil', strtolower($message));
    }

    /**
     * Helper method to create valid Excel content
     */
    private function createValidExcelContent()
    {
        // This would be actual Excel binary content
        // For testing purposes, we'll use a simplified approach
        return Excel::raw(new class implements \Maatwebsite\Excel\Concerns\FromArray {
            public function array(): array
            {
                return [
                    ['NIS', 'Nama', 'Kelas ID', 'No HP Orang Tua'],
                    ['24001', 'Ahmad Fauzi', 1, '628123456789'],
                    ['24002', 'Siti Nurhaliza', 2, '628234567890']
                ];
            }
        }, \Maatwebsite\Excel\Excel::XLSX);
    }

    /**
     * Helper method to create Excel with invalid class ID
     */
    private function createInvalidClassExcelContent()
    {
        return Excel::raw(new class implements \Maatwebsite\Excel\Concerns\FromArray {
            public function array(): array
            {
                return [
                    ['NIS', 'Nama', 'Kelas ID', 'No HP Orang Tua'],
                    ['24001', 'Ahmad Fauzi', 999, '628123456789']
                ];
            }
        }, \Maatwebsite\Excel\Excel::XLSX);
    }

    /**
     * Helper method to create Excel with invalid phone
     */
    private function createInvalidPhoneExcelContent()
    {
        return Excel::raw(new class implements \Maatwebsite\Excel\Concerns\FromArray {
            public function array(): array
            {
                return [
                    ['NIS', 'Nama', 'Kelas ID', 'No HP Orang Tua'],
                    ['24001', 'Ahmad Fauzi', 1, '12345']
                ];
            }
        }, \Maatwebsite\Excel\Excel::XLSX);
    }

    /**
     * Helper method to create large dataset
     */
    private function createLargeExcelContent($count)
    {
        $data = [['NIS', 'Nama', 'Kelas ID', 'No HP Orang Tua']];
        
        for ($i = 1; $i <= $count; $i++) {
            $data[] = [
                sprintf('24%03d', $i),
                'Student ' . $i,
                ($i % 2) + 1, // Alternate between class 1 and 2
                sprintf('6281234%05d', $i)
            ];
        }

        return Excel::raw(new class($data) implements \Maatwebsite\Excel\Concerns\FromArray {
            private $data;
            
            public function __construct($data)
            {
                $this->data = $data;
            }
            
            public function array(): array
            {
                return $this->data;
            }
        }, \Maatwebsite\Excel\Excel::XLSX);
    }
}
