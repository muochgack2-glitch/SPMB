<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsAppMessage extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_messages';

    protected $fillable = [
        'student_id',
        'phone',
        'phone_normalized',
        'message',
        'type',
        'status',
        'response',
        'error_message',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'response' => 'array',
    ];

    /**
     * Relationship to AttendanceStudent
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(AttendanceStudent::class, 'student_id');
    }

    /**
     * Scope: Filter by status
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Filter by type
     */
    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: Sent messages
     */
    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    /**
     * Scope: Failed messages
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope: Pending messages
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: Today's messages
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Mark message as sent
     */
    public function markAsSent($response = null)
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
            'response' => $response,
        ]);
    }

    /**
     * Mark message as failed
     */
    public function markAsFailed($errorMessage, $response = null)
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
            'response' => $response,
        ]);
    }

    /**
     * Get formatted phone number
     */
    public function getFormattedPhoneAttribute()
    {
        return $this->phone;
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'sent' => 'success',
            'failed' => 'danger',
            'pending' => 'warning',
            default => 'secondary',
        };
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'sent' => 'Terkirim',
            'failed' => 'Gagal',
            'pending' => 'Pending',
            default => 'Unknown',
        };
    }

    /**
     * Get type label
     */
    public function getTypeLabelAttribute()
    {
        return match($this->type) {
            'manual' => 'Manual',
            'auto_checkin' => 'Auto Check-In',
            'auto_checkout' => 'Auto Check-Out',
            'auto_alpha' => 'Auto Alpha',
            'broadcast' => 'Broadcast',
            default => ucfirst($this->type),
        };
    }

    /**
     * Normalize phone number to standard format (62xxx)
     */
    public static function normalizePhone($phone)
    {
        if (empty($phone)) {
            return null;
        }

        // Remove +, -, spaces
        $phone = str_replace(['+', '-', ' '], '', trim($phone));

        // Convert 08xxx to 628xxx
        if (substr($phone, 0, 2) === '08') {
            $phone = '62' . substr($phone, 1);
        }

        // Ensure starts with 62
        if (substr($phone, 0, 2) !== '62') {
            $phone = '62' . $phone;
        }

        return $phone;
    }

    /**
     * Boot method to auto-fill phone_normalized
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($message) {
            if (!empty($message->phone) && empty($message->phone_normalized)) {
                $message->phone_normalized = self::normalizePhone($message->phone);
            }
        });

        static::updating(function ($message) {
            if ($message->isDirty('phone')) {
                $message->phone_normalized = self::normalizePhone($message->phone);
            }
        });
    }
}
