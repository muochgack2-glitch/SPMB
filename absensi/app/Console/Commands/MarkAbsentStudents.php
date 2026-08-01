<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AttendanceService;

class MarkAbsentStudents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:mark-absent';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark all students who have not checked in as absent';

    protected AttendanceService $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        parent::__construct();
        $this->attendanceService = $attendanceService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting to mark absent students...');
        $this->newLine();

        try {
            $result = $this->attendanceService->markAbsentStudents();

            if ($result['success']) {
                $this->line('✓ Total students checked: ' . $result['total_students']);
                $this->line('✓ Marked as absent: ' . $result['marked_absent']);
                $this->line('✓ Already recorded: ' . $result['already_recorded']);
                $this->line('✓ Inactive students skipped: ' . $result['inactive_skipped']);
                
                if ($result['marked_absent'] > 0) {
                    $this->newLine();
                    $this->info('✓ Successfully marked ' . $result['marked_absent'] . ' students as absent');
                    
                    // Show list of marked students
                    if (!empty($result['marked_students'])) {
                        $this->newLine();
                        $this->line('Students marked as absent:');
                        foreach ($result['marked_students'] as $student) {
                            $this->line('  • ' . $student['nis'] . ' - ' . $student['nama'] . ' (' . $student['kelas'] . ')');
                        }
                    }
                } else {
                    $this->newLine();
                    $this->comment('No students need to be marked as absent.');
                }

                return self::SUCCESS;
            } else {
                $this->error('✗ Failed to mark absent students: ' . ($result['message'] ?? 'Unknown error'));
                return self::FAILURE;
            }
        } catch (\Exception $e) {
            $this->error('✗ Error occurred: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return self::FAILURE;
        }
    }
}
