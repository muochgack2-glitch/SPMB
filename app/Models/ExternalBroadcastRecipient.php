<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExternalBroadcastRecipient extends Model
{
    protected $fillable = [
        'batch_id',
        'name',
        'phone',
        'phone_normalized',
        'notes',
        'is_duplicate_spmb',
        'matched_pendaftar_id',
        'matched_status',
        'will_be_skipped'
    ];

    protected $casts = [
        'is_duplicate_spmb' => 'boolean',
        'will_be_skipped' => 'boolean',
    ];

    /**
     * Get the batch this recipient belongs to
     */
    public function batch()
    {
        return $this->belongsTo(ExternalBroadcastBatch::class, 'batch_id');
    }

    /**
     * Get the matched SPMB pendaftar if duplicate
     */
    public function matchedPendaftar()
    {
        return $this->belongsTo(Pendaftar::class, 'matched_pendaftar_id', 'id_pendaftar');
    }

    /**
     * Get all messages sent to this recipient
     */
    public function messages()
    {
        return WhatsAppLog::where('phone', $this->phone_normalized)
            ->where('external_batch_id', $this->batch_id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get last message date
     */
    public function getLastMessageDateAttribute()
    {
        $lastLog = WhatsAppLog::where('phone', $this->phone_normalized)
            ->where('external_batch_id', $this->batch_id)
            ->latest()
            ->first();
        
        return $lastLog ? $lastLog->created_at->format('d M Y, H:i') : '-';
    }

    /**
     * Get message count
     */
    public function getMessageCountAttribute()
    {
        return WhatsAppLog::where('phone', $this->phone_normalized)
            ->where('external_batch_id', $this->batch_id)
            ->count();
    }
}
