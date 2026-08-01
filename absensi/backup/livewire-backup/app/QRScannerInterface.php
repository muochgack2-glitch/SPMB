<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;

class QRScannerInterface extends Component
{
    public $scanResult = null;
    public $showResult = false;
    public $action = 'check_in'; // check_in or check_out
    public $lastScanNis = null;
    public $errorMessage = null;

    /**
     * Handle QR code scan event
     */
    #[On('qr-scanned')]
    public function handleScan($nis, $photoBase64)
    {
        // Prevent duplicate scans
        if ($this->lastScanNis === $nis && $this->showResult) {
            return;
        }

        $this->lastScanNis = $nis;
        $this->errorMessage = null;

        try {
            // Send AJAX request to scan endpoint
            // This will be handled by JavaScript
            $this->showResult = true;
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
            $this->showResult = false;
        }
    }

    /**
     * Reject the current scan
     */
    public function reject($nis, $reason = 'Manual rejection by petugas')
    {
        // This will trigger JavaScript to send reject request
        $this->dispatch('reject-scan', nis: $nis, reason: $reason);
        $this->hideResult();
    }

    /**
     * Hide result card
     */
    public function hideResult()
    {
        $this->showResult = false;
        $this->scanResult = null;
        $this->lastScanNis = null;
        $this->errorMessage = null;
    }

    /**
     * Change action type (check in / check out)
     */
    public function setAction($action)
    {
        if (in_array($action, ['check_in', 'check_out'])) {
            $this->action = $action;
            $this->hideResult();
        }
    }

    /**
     * Render the component
     */
    public function render()
    {
        return view('livewire.qr-scanner-interface');
    }
}
