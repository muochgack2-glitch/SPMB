<?php

namespace App\Console\Commands;

use App\Models\AttendanceStudent;
use App\Services\QRCodeService;
use Illuminate\Console\Command;

class GenerateQRCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:generate-qr 
                            {--all : Generate QR codes for all students}
                            {--missing : Generate QR codes only for students without QR}
                            {--regenerate : Regenerate all QR codes}
                            {--nis=* : Generate QR code for specific NIS}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate QR codes for attendance students';

    protected QRCodeService $qrCodeService;

    /**
     * Create a new command instance.
     */
    public function __construct(QRCodeService $qrCodeService)
    {
        parent::__construct();
        $this->qrCodeService = $qrCodeService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Starting QR Code generation...');
        $this->newLine();

        // Determine which students to process
        $students = $this->getStudentsToProcess();

        if ($students->isEmpty()) {
            $this->warn('⚠️  No students found to process.');
            return 0;
        }

        $this->info("📋 Found {$students->count()} student(s) to process");
        $this->newLine();

        // Confirm if regenerating all
        if ($this->option('regenerate') && !$this->confirm('This will regenerate ALL QR codes. Continue?')) {
            $this->warn('Operation cancelled.');
            return 1;
        }

        // Progress bar
        $bar = $this->output->createProgressBar($students->count());
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% -- %message%');
        $bar->setMessage('Initializing...');
        $bar->start();

        $success = 0;
        $failed = 0;
        $skipped = 0;
        $errors = [];

        foreach ($students as $student) {
            $bar->setMessage("Processing: {$student->nama} ({$student->nis})");

            try {
                // Check if QR already exists and not regenerating
                if (!$this->option('regenerate') && $student->qr_code_path && !$this->option('all')) {
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                // Generate QR Code
                if ($this->option('regenerate') && $student->qr_code_path) {
                    $qrPath = $this->qrCodeService->regenerateQRCode($student->nis);
                } else {
                    $qrPath = $this->qrCodeService->generateQRCode($student->nis);
                }

                // Update student record
                $student->update(['qr_code_path' => $qrPath]);

                $success++;
            } catch (\Exception $e) {
                $failed++;
                $errors[] = "❌ {$student->nama} ({$student->nis}): {$e->getMessage()}";
            }

            $bar->advance();
        }

        $bar->setMessage('Completed!');
        $bar->finish();
        $this->newLine(2);

        // Summary
        $this->info('📊 Generation Summary:');
        $this->table(
            ['Status', 'Count'],
            [
                ['✅ Success', $success],
                ['❌ Failed', $failed],
                ['⏭️  Skipped', $skipped],
                ['📋 Total', $students->count()],
            ]
        );

        // Show errors if any
        if (!empty($errors)) {
            $this->newLine();
            $this->error('⚠️  Errors encountered:');
            foreach ($errors as $error) {
                $this->line($error);
            }
        }

        // Success message
        if ($success > 0) {
            $this->newLine();
            $this->info("✅ Successfully generated {$success} QR code(s)!");
        }

        return $failed > 0 ? 1 : 0;
    }

    /**
     * Get students to process based on options
     */
    private function getStudentsToProcess()
    {
        $query = AttendanceStudent::query();

        // Specific NIS provided
        if ($nisArray = $this->option('nis')) {
            return $query->whereIn('nis', $nisArray)->get();
        }

        // Only missing QR codes
        if ($this->option('missing')) {
            return $query->whereNull('qr_code_path')
                ->orWhere('qr_code_path', '')
                ->get();
        }

        // All students (default or --all or --regenerate)
        return $query->where('is_active', true)->get();
    }
}
