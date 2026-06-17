# Broadcast Logic Unification

## Problem
The `sendBroadcast()` method (used from Broadcast page) kept experiencing JSON parse errors and instability, especially when sending to 50+ recipients. Meanwhile, `sendBulkBroadcast()` (used from Rekap Nomor HP page) was stable and reliable.

## Root Cause
`sendBroadcast()` was using a simple `sendBulk()` approach that:
- Sent all messages at once without proper feedback
- Had poor error handling for large batches
- Didn't adapt to batch size

## Solution Implemented
Unified both methods to use the **HYBRID AUTO-DETECT** logic:

### Hybrid Logic (Threshold: 30 Recipients)

#### Method A: Detail Feedback (≤30 recipients)
- **Behavior**: Loop one-by-one, synchronous
- **Benefits**: 
  - Real-time detailed results per phone
  - Better error tracking
  - Immediate user feedback
- **Use Case**: Small to medium broadcasts

#### Method B: Bulk/Batch (>30 recipients)
- **Behavior**: Use `sendBulk()` service, asynchronous
- **Benefits**:
  - Better performance for large batches
  - Non-blocking process
  - Background processing
- **Use Case**: Large broadcasts
- **Note**: Results available in WhatsApp Logs

## Safe Broadcast Limits

With the 300-second timeout:
- **Safe**: 100 messages (~1.5 minutes)
- **Comfort zone**: 50 messages
- **Max capacity**: ~280 messages

## Changes Made

### File: `app/Http/Controllers/WhatsAppController.php`

**Method `sendBroadcast()` (line ~379)**
- ✅ Added hybrid auto-detect logic
- ✅ Normalized phone data format
- ✅ Reuses `sendWithDetailFeedback()` for ≤30 recipients
- ✅ Reuses `sendWithBulkMethod()` for >30 recipients
- ✅ Same timeout handling (300 seconds)

**Shared Methods:**
- `sendWithDetailFeedback()` - Detail feedback for small batches
- `sendWithBulkMethod()` - Bulk processing for large batches

## Testing Checklist

- [ ] Test with 10 recipients (should use detail feedback)
- [ ] Test with 30 recipients (should use detail feedback)
- [ ] Test with 31 recipients (should use bulk method)
- [ ] Test with 50 recipients (should use bulk method)
- [ ] Test with 100 recipients (should use bulk method)
- [ ] Verify error handling works correctly
- [ ] Check WhatsApp logs for all sent messages

## Expected Behavior

### Small Batch (≤30)
```json
{
  "success": true,
  "method": "detail_feedback",
  "message": "Broadcast selesai. Terkirim: 28, Gagal: 2",
  "total": 30,
  "success_count": 28,
  "failed_count": 2,
  "results": [
    {
      "phone": "628123456789",
      "name": "John Doe",
      "no_reg": "REG001",
      "jurusan": "TKJ",
      "success": true,
      "message": "Message sent successfully"
    },
    // ... detail per phone
  ],
  "note": "Hasil detail per nomor tersedia"
}
```

### Large Batch (>30)
```json
{
  "success": true,
  "method": "bulk",
  "message": "Broadcast diproses untuk 50 nomor",
  "total": 50,
  "success_count": 48,
  "failed_count": 2,
  "note": "Proses berjalan di background. Detail hasil dapat dilihat di Log WhatsApp."
}
```

## Benefits

1. **Consistency**: Both broadcast pages use same stable logic
2. **Performance**: Adaptive to batch size
3. **User Experience**: Clear feedback for small batches, background processing for large
4. **Reliability**: Proven stable method from `sendBulkBroadcast()`
5. **Error Handling**: Better error tracking and reporting

## Related Issues

This fix addresses:
- JSON parse errors (DOCTYPE error)
- Timeout issues with large batches
- Inconsistent behavior between broadcast pages
- Poor error feedback

## Additional Notes

**Migration Issue**: User still needs to run `php artisan migrate` on hosting to fix `matched_status` column error in external broadcast feature.

**Priority Order**:
1. ✅ Unify broadcast logic (DONE)
2. ⏳ Run migration on hosting
3. ⏳ Test with real broadcasts

---

**Date**: June 17, 2026
**Commit**: Broadcast logic unification
