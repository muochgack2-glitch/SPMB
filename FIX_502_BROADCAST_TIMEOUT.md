# Fix 502 Timeout di External Broadcast

## Masalah

**Gejala:**
- UI stuck di "Mengirim Broadcast..." dengan progress "0 / 10" dan "10 Tersisa"
- Setelah beberapa saat muncul error 502 di console
- **Padahal di log WhatsApp, semua pesan sudah terkirim**

**Screenshot Error:**
```
Failed to load resource: the server responded with a status of 502 ()
broadcast/external/send:1
```

## Root Cause

1. **Backend mengirim pesan synchronous** → 10 pesan × 1 detik = 10+ detik
2. **Server hosting timeout pendek** → Biasanya 30 detik, tapi bisa 10-15 detik
3. **Response JSON tidak sampai ke browser** → Meskipun proses selesai
4. **UI tidak dapat update** → Stuck di progress awal

## Solusi Implemented

### Early Response Pattern

**Konsep:** Kirim response HTTP dulu, lalu proses di background

```php
// 1. Send immediate response to browser
ignore_user_abort(true);
ob_start();

header('Content-Type: application/json');
header('Connection: close');
header('Content-Length: ' . ob_get_length());

echo json_encode([
    'success' => true,
    'message' => 'Broadcast sedang diproses di background',
    'batch_id' => $batch->id,
    'total_recipients' => $batch->recipients->count(),
]);

ob_end_flush();
flush();
session_write_close();

// 2. Connection closed, browser receives response

// 3. Continue processing in background
foreach ($recipients as $recipient) {
    // Send messages...
}
```

### Flow Diagram

```
┌─────────────┐
│   Browser   │
│  (Frontend) │
└──────┬──────┘
       │ POST /broadcast/external/send
       │
       ▼
┌─────────────────────────────────────┐
│  Laravel Controller                 │
│                                     │
│  1. Validate request               │
│  2. Check WA Gateway               │
│  3. Load batch & recipients        │
│                                     │
│  ⚡ EARLY RESPONSE ⚡              │
│  4. Send JSON response immediately │
│  5. Close HTTP connection          │
└──────┬──────────────────────────────┘
       │ Response: { success: true, message: "Processing..." }
       ▼
┌─────────────┐
│   Browser   │ ✅ UI updates immediately
│  Shows OK   │ No more stuck/502 error
└─────────────┘

       ┌──────────────────────────────┐
       │  Background Processing       │
       │  (Connection already closed) │
       │                              │
       │  6. Loop through recipients  │
       │  7. Send WhatsApp messages   │
       │  8. Log results to database  │
       │  9. Mark batch as completed  │
       └──────────────────────────────┘
```

## Changes Made

### File: `app/Http/Controllers/WhatsAppController.php`

**Method:** `sendExternalBroadcast()`

**Changes:**
1. ✅ Added `ignore_user_abort(true)` - Continue after connection closes
2. ✅ Output buffering with `ob_start()`
3. ✅ Send custom headers: `Connection: close`, `Content-Length`
4. ✅ Echo JSON response immediately
5. ✅ Flush output and close session
6. ✅ Continue processing broadcast in background

## Testing

### Before Fix
```
User clicks "Kirim Broadcast"
→ Progress: 0/10, 10 Tersisa
→ Wait 10+ seconds...
→ ERROR 502 (server timeout)
→ UI stuck
→ But messages sent (check logs)
```

### After Fix
```
User clicks "Kirim Broadcast"
→ Progress: Processing...
→ Response received immediately (<1 second)
→ ✅ UI shows success message
→ Messages sending in background
→ Check Log WhatsApp for results
```

## Benefits

1. **No More 502 Errors** → Response sent before timeout
2. **Better UX** → Immediate feedback to user
3. **No Code Duplication** → Same broadcast logic
4. **Server Friendly** → Works on slow/timeout-restricted hosting
5. **Reliable** → Messages still sent even if user closes browser

## Alternative Solutions Considered

### Option 1: Increase Server Timeout ❌
- Requires server config access
- Not always possible on shared hosting
- Temporary fix, doesn't scale

### Option 2: Queue System ❌
- Requires Redis/Database queue
- More complex setup
- Overkill for this use case

### Option 3: Early Response ✅ (CHOSEN)
- No external dependencies
- Works on any hosting
- Simple and effective
- Native PHP solution

## Important Notes

### For Users

**Expected Behavior:**
- Klik "Kirim Broadcast"
- Muncul notif "Broadcast sedang diproses"
- Modal ditutup
- **Cek hasil di Log WhatsApp** (bukan di modal)

**Checking Results:**
1. Buka menu **WhatsApp → Logs**
2. Filter by type: "external_broadcast"
3. Atau filter by date: Today
4. Lihat status: Sent / Failed

### For Developers

**Background Processing:**
- Script continues after `flush()` and `session_write_close()`
- PHP max_execution_time still applies (300 seconds set)
- `ignore_user_abort(true)` prevents termination if user closes tab
- Logs still written to `whatsapp_logs` table

**Error Handling:**
- Errors in background won't show to user
- Check Laravel logs for errors: `storage/logs/laravel.log`
- Monitor batch status: `external_broadcast_batches.status`

## Monitoring

### Check if Broadcast Completed

**Database Query:**
```sql
SELECT 
    id,
    batch_name,
    status,
    total_recipients,
    sent_count,
    created_at,
    processed_at
FROM external_broadcast_batches
ORDER BY created_at DESC
LIMIT 10;
```

**Expected Status:**
- `pending` → Not started yet
- `processing` → Currently sending (or stuck if >5 min)
- `completed` → All done
- `failed` → Error occurred

### Check Message Logs

**Database Query:**
```sql
SELECT 
    phone,
    message,
    status,
    error_message,
    created_at
FROM whatsapp_logs
WHERE type = 'external_broadcast'
    AND DATE(created_at) = CURDATE()
ORDER BY created_at DESC;
```

## Troubleshooting

### Issue: Response still times out

**Possible Cause:** Nginx/Apache timeout too aggressive

**Solution:** Add these to `.env`:
```env
# Increase PHP timeout
MAX_EXECUTION_TIME=300

# For Nginx (if you have access)
fastcgi_read_timeout 300;
proxy_read_timeout 300;
```

### Issue: Messages not sent in background

**Possible Cause:** PHP-FPM kills process

**Solution:** Check if `ignore_user_abort(true)` is working:
```php
// Test script
ignore_user_abort(true);
echo "Response sent";
flush();
sleep(10);
\Log::info('Background completed'); // Check if this appears
```

### Issue: Some messages missing

**Possible Cause:** Script timeout before all sent

**Solution:** Increase `set_time_limit()` or split into smaller batches

---

**Date:** June 17, 2026  
**Commit:** 516228e  
**Status:** ✅ Fixed and Deployed
