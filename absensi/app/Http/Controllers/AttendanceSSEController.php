<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceSSEController extends Controller
{
    /**
     * SSE endpoint for real-time attendance updates.
     * 
     * GET /api/attendance/sse
     * 
     * This endpoint keeps connection open and sends new scan events to all connected clients.
     * 
     * @return StreamedResponse
     */
    public function stream(): StreamedResponse
    {
        $response = new StreamedResponse(function () {
            // Set headers for SSE
            header('Content-Type: text/event-stream');
            header('Cache-Control: no-cache');
            header('Connection: keep-alive');
            header('X-Accel-Buffering: no'); // Disable nginx buffering
            
            // Keep connection alive
            while (true) {
                // Check if client is still connected
                if (connection_aborted()) {
                    break;
                }
                
                // Get latest scan from cache (will be set by scan controller)
                $latestScan = cache()->get('latest_attendance_scan');
                
                if ($latestScan) {
                    // Send event to client
                    echo "event: new-scan\n";
                    echo "data: " . json_encode($latestScan) . "\n\n";
                    
                    // Clear cache so we don't send duplicate
                    cache()->forget('latest_attendance_scan');
                    
                    // Flush output buffer
                    if (ob_get_level() > 0) {
                        ob_flush();
                    }
                    flush();
                }
                
                // Send heartbeat every 30 seconds to keep connection alive
                echo ": heartbeat\n\n";
                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();
                
                // Sleep for 2 seconds before next check
                sleep(2);
            }
        });
        
        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('X-Accel-Buffering', 'no');
        
        return $response;
    }
}
