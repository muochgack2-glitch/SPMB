<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\QRCodeService;
use App\Services\PhotoCaptureService;
use App\Services\AttendanceStatusService;
use App\Services\AttendanceService;
use App\Services\AttendanceWhatsAppService;
use App\Services\AttendanceNotificationService;
use App\Services\AttendanceExportService;

class AttendanceServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register all attendance services as singletons
        $this->app->singleton(QRCodeService::class);
        $this->app->singleton(PhotoCaptureService::class);
        $this->app->singleton(AttendanceStatusService::class);
        $this->app->singleton(AttendanceWhatsAppService::class);
        $this->app->singleton(AttendanceExportService::class);
        
        // Register services with dependencies
        $this->app->singleton(AttendanceService::class, function ($app) {
            return new AttendanceService(
                $app->make(PhotoCaptureService::class),
                $app->make(AttendanceStatusService::class)
            );
        });
        
        $this->app->singleton(AttendanceNotificationService::class, function ($app) {
            return new AttendanceNotificationService(
                $app->make(AttendanceWhatsAppService::class)
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
