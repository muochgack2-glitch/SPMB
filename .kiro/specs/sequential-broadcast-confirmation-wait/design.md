# Sequential Broadcast Confirmation Wait Bugfix Design

## Overview

Bug ini terjadi pada external broadcast WhatsApp di mana sistem mengirim pesan tanpa menunggu konfirmasi dari gateway sebelum proceed ke pesan berikutnya. Sistem langsung menerapkan sleep delay setelah mengirim request tanpa memverifikasi bahwa gateway telah menerima dan mengonfirmasi pengiriman pesan melalui `messageId`.

Dampak utama bug ini adalah pattern pengiriman yang terlalu teratur dan cepat, mudah terdeteksi sebagai bot/spam oleh WhatsApp Gateway. Selain itu, sistem tidak memvalidasi apakah pesan benar-benar diterima gateway sebelum lanjut, dan failed messages tetap mendapat delay yang sama.

**Fix Approach:**
Mengimplementasikan sequential confirmation wait pattern yang menunggu response dari gateway dan memverifikasi keberadaan `messageId` sebagai bukti sukses sebelum proceed. Delay hanya diterapkan setelah sukses (messageId present), dan skip delay jika gagal. Tambahan real-time progress feedback menggunakan Server-Sent Events (SSE) atau AJAX polling.

## Glossary

- **Bug_Condition (C)**: Kondisi di mana broadcast loop proceed ke pesan berikutnya sebelum menerima dan memverifikasi response confirmation dari gateway
- **Property (P)**: Perilaku yang diharapkan - sistem menunggu response, verifikasi messageId, dan apply delay hanya setelah sukses
- **Preservation**: Rate limiting settings, WhatsAppLog format, dan UI behavior yang harus tetap unchanged
- **sendExternalBroadcast()**: Method di `WhatsAppController.php` yang mengirim external broadcast messages secara loop
- **WhatsAppService::send()**: Method yang mengirim pesan ke gateway dan return response array dengan `success`, `data`, dan `messageId`
- **messageId**: Unique identifier dari gateway yang membuktikan pesan berhasil diterima dan akan dikirim
- **Rate Limiting Settings**: Database-driven delays (min_delay, max_delay, break_interval, break_duration) dari `WhatsAppSetting`
- **Sequential Confirmation Wait**: Pattern menunggu response confirmation sebelum proceed ke item berikutnya dalam loop

## Bug Details

### Bug Condition

Bug terjadi ketika external broadcast loop mengirim request ke WhatsApp Gateway dan langsung menerapkan sleep delay tanpa:
1. Menunggu response dari gateway
2. Memverifikasi keberadaan `messageId` sebagai bukti sukses
3. Skip delay untuk failed messages

Ini menyebabkan pattern pengiriman teratur yang mudah terdeteksi sebagai spam, dan membuang waktu dengan delay pada failed messages.

**Formal Specification:**
```
FUNCTION isBugCondition(input)
  INPUT: input of type BroadcastIteration
  OUTPUT: boolean
  
  RETURN input.is_external_broadcast = true
         AND input.message_sent = true
         AND input.gateway_response_received = false
         AND input.proceeding_to_next = true
END FUNCTION
```

**Alternative Bug Condition (More Specific):**
```
FUNCTION isBugCondition(input)
  INPUT: input of type BroadcastIteration
  OUTPUT: boolean
  
  RETURN input.is_external_broadcast = true
         AND input.delay_applied_without_confirmation = true
END FUNCTION
```

### Examples

**Example 1: Delay applied before confirmation**
- **Input**: External broadcast dengan 10 recipients
- **Current Behavior**: Send request → sleep(2-4 detik) → send next request
- **Expected Behavior**: Send request → wait for response → check messageId → if success then sleep(2-4 detik) → send next request

**Example 2: Failed message gets delay**
- **Input**: Send message ke nomor invalid (gateway returns `success: false`)
- **Current Behavior**: Send fails → sleep(2-4 detik) → send next
- **Expected Behavior**: Send fails (no messageId) → skip delay → send next immediately

**Example 3: Slow gateway response**
- **Input**: Gateway membutuhkan 10 detik untuk respond
- **Current Behavior**: Send request → system proceeds without waiting → next message sent while first still processing
- **Expected Behavior**: Send request → wait up to 300 seconds → get confirmation → apply delay → proceed

**Edge Case: Timeout protection**
- **Input**: Gateway hang/tidak respond selama 10 menit
- **Expected Behavior**: System timeout setelah 5 menit (300 detik), mark as failed, proceed to next

## Expected Behavior

### Preservation Requirements

**Unchanged Behaviors:**
- Rate limiting settings MUST continue to use database-driven values dari `WhatsAppSetting::getExternalBroadcastMinDelay()`, `getExternalBroadcastMaxDelay()`, `getExternalBroadcastBreakInterval()`, dan `getExternalBroadcastBreakDuration()`
- WhatsAppLog creation MUST continue to use type "external_broadcast" dan external_batch_id
- WhatsAppService::send() method signature dan basic behavior MUST remain unchanged untuk non-external broadcasts
- Phone list UI MUST continue to display external broadcast history dengan format yang sama
- Success rate calculation (sent/total) untuk batch MUST remain unchanged
- User cancellation behavior MUST continue to work (stop dan simpan partial results)

**Scope:**
Semua input yang BUKAN external broadcast (SPMB broadcasts, manual sends, template sends) harus completely unaffected oleh fix ini. Rate limiting configuration interface dan storage juga harus tetap unchanged.

## Hypothesized Root Cause

Based on the bug description and current code analysis, the root causes are:

1. **No Response Wait**: Method `sendExternalBroadcast()` tidak menunggu response dari `$this->whatsappService->send()` sebelum proceed. Meskipun method return value di-check untuk success/failed counting, system langsung apply sleep tanpa verifikasi messageId.

2. **Unconditional Delay Application**: Sleep delay diterapkan untuk semua messages (sukses dan gagal) dengan logic:
   ```php
   if ($batch->recipients->count() > 1) {
       // ... calculate delay ...
       sleep($delay);
   }
   ```
   Tidak ada kondisi check `if (messageId present) then delay`.

3. **Missing MessageId Validation**: Meskipun `WhatsAppService::send()` sudah return `has_message_id` flag dalam response array, value ini tidak digunakan di `sendExternalBroadcast()` untuk decision making.

4. **No Timeout Protection**: Tidak ada timeout mechanism untuk mencegah system hang jika gateway tidak respond. Current `Http::timeout()` di `WhatsAppService` adalah 30 detik default, tapi untuk broadcast loop sebaiknya ada explicit timeout per message.

## Correctness Properties

Property 1: Bug Condition - Sequential Confirmation Wait

_For any_ external broadcast iteration where a message is sent to the gateway, the fixed sendExternalBroadcast' function SHALL wait for the gateway response, verify the presence of messageId as proof of success, and only apply delay if messageId is present (success case). Failed messages (no messageId) SHALL skip delay and proceed immediately.

**Validates: Requirements 2.1, 2.2, 2.3, 2.4, 2.5, 2.6, 2.7**

Property 2: Preservation - Rate Limiting and Logging

_For any_ external broadcast execution, the fixed code SHALL continue to use rate limiting settings from the database (min_delay, max_delay, break_interval, break_duration), create WhatsAppLog entries with type "external_broadcast" and external_batch_id, and display broadcast history in the phone list UI exactly as before.

**Validates: Requirements 3.1, 3.2, 3.3, 3.4, 3.5, 3.6**

## Fix Implementation

### Changes Required

Assuming our root cause analysis is correct:

**File**: `app/Http/Controllers/WhatsAppController.php`

**Function**: `sendExternalBroadcast()`

**Specific Changes**:

1. **Enhance Response Processing**: Modify response handling untuk check messageId presence
   ```php
   // Current (line ~1906):
   if (is_array($result) && isset($result['success']) && $result['success']) {
       $successCount++;
       $batch->incrementSent();
   } else {
       $failedCount++;
       $batch->incrementFailed();
   }
   
   // Fixed:
   $messageIdPresent = false;
   if (is_array($result) && isset($result['success']) && $result['success']) {
       // Check for messageId as proof of actual sending
       $messageIdPresent = isset($result['data']['messageId']) 
                        || isset($result['data']['message_id'])
                        || (isset($result['has_message_id']) && $result['has_message_id']);
       
       if ($messageIdPresent) {
           $successCount++;
           $batch->incrementSent();
       } else {
           // Gateway says success but no messageId - treat as suspicious/failed
           $failedCount++;
           $batch->incrementFailed();
       }
   } else {
       $failedCount++;
       $batch->incrementFailed();
   }
   ```

2. **Conditional Delay Application**: Apply delay ONLY after confirmed success (messageId present)
   ```php
   // Current (line ~1910-1928):
   if ($batch->recipients->count() > 1) {
       $currentIndex = $successCount + $failedCount;
       $totalRecipients = $batch->recipients->count() - $skippedCount;
       
       if ($currentIndex < $totalRecipients) {
           $minDelay = WhatsAppSetting::getExternalBroadcastMinDelay();
           $maxDelay = WhatsAppSetting::getExternalBroadcastMaxDelay();
           $delay = rand($minDelay, $maxDelay);
           sleep($delay);
           
           $breakInterval = WhatsAppSetting::getExternalBroadcastBreakInterval();
           $breakDuration = WhatsAppSetting::getExternalBroadcastBreakDuration();
           
           if ($breakInterval > 0 && $currentIndex % $breakInterval === 0 && $currentIndex > 0) {
               sleep($breakDuration);
           }
       }
   }
   
   // Fixed:
   if ($batch->recipients->count() > 1) {
       $currentIndex = $successCount + $failedCount;
       $totalRecipients = $batch->recipients->count() - $skippedCount;
       
       // Only apply delay if message was successfully confirmed (messageId present)
       if ($currentIndex < $totalRecipients && $messageIdPresent) {
           $minDelay = WhatsAppSetting::getExternalBroadcastMinDelay();
           $maxDelay = WhatsAppSetting::getExternalBroadcastMaxDelay();
           $delay = rand($minDelay, $maxDelay);
           sleep($delay);
           
           $breakInterval = WhatsAppSetting::getExternalBroadcastBreakInterval();
           $breakDuration = WhatsAppSetting::getExternalBroadcastBreakDuration();
           
           if ($breakInterval > 0 && $currentIndex % $breakInterval === 0 && $currentIndex > 0) {
               sleep($breakDuration);
           }
       }
       // If messageId not present (failed), skip delay and proceed immediately
   }
   ```

3. **Add Timeout Protection**: Wrap send operation dengan timeout protection
   ```php
   // Add before send operation (line ~1870):
   $sendTimeout = 300; // 5 minutes max per message
   $sendStartTime = time();
   
   // Send message
   $result = $this->whatsappService->send(
       $recipient->phone_normalized,
       $personalizedMessage,
       [
           'type' => 'external_broadcast',
           'sent_by' => auth()->id(),
           'template_id' => $request->template_id,
           'external_batch_id' => $batch->id,
       ]
   );
   
   // Check if operation exceeded timeout
   $sendDuration = time() - $sendStartTime;
   if ($sendDuration > $sendTimeout) {
       // Log timeout
       \Log::warning('External broadcast message send timeout', [
           'recipient' => $recipient->name,
           'phone' => $recipient->phone,
           'duration' => $sendDuration,
           'batch_id' => $batch->id,
       ]);
       
       // Mark as failed if not already marked
       if (!isset($result['success']) || $result['success'] !== true) {
           $failedCount++;
           $batch->incrementFailed();
       }
   }
   ```

4. **Accurate sent_at Timestamp**: Ensure timestamp dicatat setelah confirmation
   ```php
   // This is already handled by WhatsAppLog::markAsSent() in WhatsAppService
   // But verify it's called AFTER response received, not before
   // Current implementation sudah correct - log created as 'pending', 
   // then marked as 'sent' after response received
   ```

5. **Real-Time Progress Feedback**: Add SSE endpoint atau modify untuk streaming progress

   **Option A: Server-Sent Events (SSE) - Recommended**
   
   Add new route in `routes/web.php`:
   ```php
   Route::get('/whatsapp/broadcast/external/progress/{batch_id}', 
       [WhatsAppController::class, 'externalBroadcastProgress'])
       ->name('whatsapp.broadcast.external.progress');
   ```
   
   Add new method in `WhatsAppController.php`:
   ```php
   public function externalBroadcastProgress($batchId)
   {
       return response()->stream(function() use ($batchId) {
           while (true) {
               $batch = ExternalBroadcastBatch::find($batchId);
               
               if (!$batch) {
                   echo "data: " . json_encode(['error' => 'Batch not found']) . "\n\n";
                   ob_flush();
                   flush();
                   break;
               }
               
               $progress = [
                   'status' => $batch->status,
                   'total' => $batch->total_recipients,
                   'sent' => $batch->sent_count,
                   'failed' => $batch->failed_count,
                   'current_index' => $batch->sent_count + $batch->failed_count,
                   'current_recipient' => $batch->getCurrentRecipientName(), // Need to implement
               ];
               
               echo "data: " . json_encode($progress) . "\n\n";
               ob_flush();
               flush();
               
               // Stop streaming if batch completed
               if ($batch->status === 'completed' || $batch->status === 'failed') {
                   break;
               }
               
               sleep(1); // Update every second
           }
       }, 200, [
           'Content-Type' => 'text/event-stream',
           'Cache-Control' => 'no-cache',
           'X-Accel-Buffering' => 'no',
       ]);
   }
   ```
   
   **Option B: AJAX Polling - Simpler**
   
   Add new route in `routes/web.php`:
   ```php
   Route::get('/whatsapp/broadcast/external/status/{batch_id}', 
       [WhatsAppController::class, 'externalBroadcastStatus'])
       ->name('whatsapp.broadcast.external.status');
   ```
   
   Add new method in `WhatsAppController.php`:
   ```php
   public function externalBroadcastStatus($batchId)
   {
       $batch = ExternalBroadcastBatch::find($batchId);
       
       if (!$batch) {
           return response()->json([
               'success' => false,
               'message' => 'Batch not found'
           ], 404);
       }
       
       return response()->json([
           'success' => true,
           'data' => [
               'status' => $batch->status,
               'total' => $batch->total_recipients,
               'sent' => $batch->sent_count,
               'failed' => $batch->failed_count,
               'current_index' => $batch->sent_count + $batch->failed_count,
               'progress_percent' => round(($batch->sent_count + $batch->failed_count) / $batch->total_recipients * 100),
           ]
       ]);
   }
   ```

**File**: `resources/views/whatsapp/broadcast.blade.php`

**Function**: `executeExternalBroadcast()`

**Specific Changes**:

6. **Implement Progress Polling/SSE**: Modify frontend untuk display real-time progress

   **For SSE Approach**:
   ```javascript
   function executeExternalBroadcast() {
       // ... existing code ...
       
       // Start broadcast request (non-blocking)
       fetch('{{ route("whatsapp.broadcast.external.send") }}', {
           method: 'POST',
           headers: {
               'Content-Type': 'application/json',
               'X-CSRF-TOKEN': '{{ csrf_token() }}'
           },
           body: JSON.stringify(payload)
       });
       
       // Connect to SSE for real-time progress
       const eventSource = new EventSource(`/whatsapp/broadcast/external/progress/${batchId}`);
       
       eventSource.onmessage = function(event) {
           const progress = JSON.parse(event.data);
           
           // Update progress bar
           const progressPercent = Math.round((progress.current_index / progress.total) * 100);
           document.getElementById('progressBar').style.width = progressPercent + '%';
           document.getElementById('progressBar').textContent = progressPercent + '%';
           
           // Update stats
           document.getElementById('successCount').textContent = progress.sent;
           document.getElementById('failedCount').textContent = progress.failed;
           document.getElementById('remainingCount').textContent = progress.total - progress.current_index;
           
           // Update current recipient
           document.getElementById('progressText').textContent = 
               `Mengirim ke ${progress.current_recipient || '...'} (${progress.current_index}/${progress.total})`;
           
           // Close connection when completed
           if (progress.status === 'completed' || progress.status === 'failed') {
               eventSource.close();
               // Show final result
               // ... existing completion code ...
           }
       };
       
       eventSource.onerror = function(error) {
           console.error('SSE Error:', error);
           eventSource.close();
           // Fallback to polling or show error
       };
   }
   ```
   
   **For AJAX Polling Approach**:
   ```javascript
   function executeExternalBroadcast() {
       // ... existing code ...
       
       // Start broadcast request
       fetch('{{ route("whatsapp.broadcast.external.send") }}', {
           method: 'POST',
           headers: {
               'Content-Type': 'application/json',
               'X-CSRF-TOKEN': '{{ csrf_token() }}'
           },
           body: JSON.stringify(payload)
       });
       
       // Poll for progress every 500ms
       const pollInterval = setInterval(() => {
           fetch(`/whatsapp/broadcast/external/status/${batchId}`)
               .then(response => response.json())
               .then(data => {
                   if (!data.success) return;
                   
                   const progress = data.data;
                   
                   // Update progress bar
                   document.getElementById('progressBar').style.width = progress.progress_percent + '%';
                   document.getElementById('progressBar').textContent = progress.progress_percent + '%';
                   
                   // Update stats
                   document.getElementById('successCount').textContent = progress.sent;
                   document.getElementById('failedCount').textContent = progress.failed;
                   document.getElementById('remainingCount').textContent = progress.total - progress.current_index;
                   
                   // Stop polling when completed
                   if (progress.status === 'completed' || progress.status === 'failed') {
                       clearInterval(pollInterval);
                       // Show final result
                       // ... existing completion code ...
                   }
               })
               .catch(error => {
                   console.error('Polling error:', error);
                   clearInterval(pollInterval);
               });
       }, 500);
   }
   ```

**Recommendation**: Use **AJAX Polling** karena lebih simple untuk implement dan compatible dengan semua server configurations. SSE membutuhkan server configuration khusus dan bisa bermasalah dengan buffering.

## Testing Strategy

### Validation Approach

Testing strategy menggunakan two-phase approach: exploratory bug condition checking untuk surface counterexamples pada unfixed code, kemudian fix checking dan preservation checking untuk verify fix works correctly.

### Exploratory Bug Condition Checking

**Goal**: Surface counterexamples yang demonstrate bug BEFORE implementing fix. Confirm atau refute root cause analysis. If refuted, re-hypothesize.

**Test Plan**: Write tests yang simulate external broadcast dengan mocked WhatsApp Gateway responses (sukses dengan messageId, sukses tanpa messageId, failed). Run tests on UNFIXED code untuk observe failures dan understand root cause.

**Test Cases**:

1. **Test: Delay Applied Without Confirmation**
   - Setup: Mock gateway untuk delay response 5 detik
   - Execute: Send 2 messages dalam external broadcast
   - Observe: System applies sleep delay sebelum gateway respond (will fail on unfixed code)
   - Expected Counterexample: Second message sent sebelum first message confirmed

2. **Test: Failed Message Gets Delay**
   - Setup: Mock gateway untuk return `success: false` (no messageId)
   - Execute: Send failed message dalam broadcast
   - Observe: System applies sleep delay meskipun message failed (will fail on unfixed code)
   - Expected Counterexample: Delay applied for failed message

3. **Test: Success Without MessageId Gets Delay**
   - Setup: Mock gateway untuk return `success: true` tapi tanpa messageId
   - Execute: Send message dengan suspicious success
   - Observe: System applies delay meskipun no messageId proof (will fail on unfixed code)
   - Expected Counterexample: Delay applied without messageId verification

4. **Test: No Timeout Protection**
   - Setup: Mock gateway untuk never respond (hang)
   - Execute: Send message
   - Observe: System hangs indefinitely atau very long time (may fail on unfixed code)
   - Expected Counterexample: No timeout error after 5 minutes

**Expected Counterexamples**:
- Sleep delay diterapkan immediately setelah send() call tanpa check response
- Failed messages (no messageId) tetap mendapat delay
- Tidak ada timeout protection untuk slow/hanging gateway

**Possible Causes Validation**:
- Root cause #1 confirmed: No response wait sebelum delay
- Root cause #2 confirmed: Unconditional delay application
- Root cause #3 confirmed: Missing messageId validation
- Root cause #4 confirmed: No timeout protection

### Fix Checking

**Goal**: Verify bahwa untuk all inputs where bug condition holds (external broadcast iterations), fixed function produces expected behavior (wait confirmation, verify messageId, conditional delay).

**Pseudocode:**
```
FOR ALL input WHERE isBugCondition(input) DO
  result := sendExternalBroadcast'(input)
  
  // Verify waited for confirmation
  ASSERT result.gateway_response_received = true
  
  // Verify messageId checked
  ASSERT result.messageId_verified = true
  
  // Verify conditional delay
  IF result.messageId_present THEN
    ASSERT result.delay_applied = true
  ELSE
    ASSERT result.delay_applied = false
  END IF
  
  // Verify timeout protection
  ASSERT result.send_duration <= 300 seconds
  
  // Verify accurate timestamp
  ASSERT result.sent_at_recorded_after_confirmation = true
END FOR
```

**Testing Approach**: Unit tests dengan mocked gateway responses untuk different scenarios (success with messageId, success without messageId, failure, timeout).

**Test Cases**:

1. **Test: Delay Only After Confirmed Success**
   - Input: External broadcast dengan 3 recipients, all success dengan messageId
   - Assert: Delay applied after each successful send (before next send)
   - Assert: No delay applied before confirmation received

2. **Test: Skip Delay For Failed Messages**
   - Input: External broadcast dengan 3 recipients, second one fails (no messageId)
   - Assert: Delay applied after first message (success)
   - Assert: No delay after second message (failed)
   - Assert: Delay applied after third message (success)

3. **Test: Timeout Protection Works**
   - Input: External broadcast dengan 1 recipient, gateway hangs for 400 seconds
   - Assert: Operation timeout after 300 seconds
   - Assert: Message marked as failed
   - Assert: Next message processed (if exists)

4. **Test: Accurate Sent_at Timestamp**
   - Input: External broadcast dengan 1 recipient, gateway takes 3 seconds to respond
   - Assert: sent_at timestamp recorded after response received (not before send)
   - Assert: Timestamp difference between send and sent_at is ~3 seconds

### Preservation Checking

**Goal**: Verify bahwa untuk all inputs where bug condition does NOT hold (non-external broadcasts, rate limiting settings, logging format), fixed function produces same result as original function.

**Pseudocode:**
```
FOR ALL input WHERE NOT isBugCondition(input) DO
  ASSERT sendBroadcast_original(input) = sendBroadcast_fixed(input)
  ASSERT getRateLimitSettings_original() = getRateLimitSettings_fixed()
  ASSERT WhatsAppLog_format_original = WhatsAppLog_format_fixed
  ASSERT phoneListUI_display_original = phoneListUI_display_fixed
END FOR
```

**Testing Approach**: Property-based testing recommended karena:
- Generates many test cases automatically across input domain
- Catches edge cases yang manual unit tests might miss
- Provides strong guarantees behavior unchanged for non-buggy inputs

**Test Plan**: Observe behavior on UNFIXED code first untuk non-external broadcasts dan UI displays, then write property-based tests capturing that behavior.

**Test Cases**:

1. **Preservation Test: SPMB Broadcast Unchanged**
   - Observe: SPMB broadcast (non-external) behavior on unfixed code
   - Write test: Verify SPMB broadcast produces identical results after fix
   - Assert: Success/failed counts same, delays same, logs same format

2. **Preservation Test: Rate Limiting Settings Unchanged**
   - Observe: Rate limiting settings retrieval and usage on unfixed code
   - Write test: Verify settings retrieved from database dengan values yang sama
   - Assert: min_delay, max_delay, break_interval, break_duration unchanged

3. **Preservation Test: WhatsAppLog Format Unchanged**
   - Observe: WhatsAppLog entries untuk external broadcast on unfixed code
   - Write test: Verify log entries have same structure and fields after fix
   - Assert: type = "external_broadcast", external_batch_id present, status values same

4. **Preservation Test: Phone List UI Display Unchanged**
   - Observe: Phone list external tab display on unfixed code
   - Write test: Verify external broadcast history displayed identically after fix
   - Assert: Recipient list, message counts, batch info displayed sama

### Unit Tests

**Test Categories**:

- **Response Processing Tests**: Test messageId detection dari berbagai response formats
  - Test with `data.messageId`
  - Test with `data.message_id`
  - Test with `has_message_id` flag
  - Test without messageId (should treat as failed)

- **Delay Logic Tests**: Test conditional delay application
  - Test delay applied after success with messageId
  - Test no delay after failure (no messageId)
  - Test delay calculation using database settings
  - Test break interval logic

- **Timeout Tests**: Test timeout protection mechanism
  - Test normal send within timeout
  - Test send exceeding timeout (should fail gracefully)
  - Test timeout value configurable

- **Timestamp Tests**: Test sent_at accuracy
  - Test timestamp recorded after confirmation
  - Test timestamp not recorded before send
  - Test timestamp accuracy for slow responses

### Property-Based Tests

**Property Test Categories**:

- **Confirmation Wait Property**: FOR ALL external broadcast iterations, system MUST wait for gateway response before proceeding
  - Generate random broadcast sizes (1-100 recipients)
  - Generate random gateway response delays (0-10 seconds)
  - Verify no message sent before previous confirmed

- **MessageId Verification Property**: FOR ALL send operations, delay MUST only be applied if messageId present
  - Generate random response formats (with/without messageId)
  - Verify delay pattern matches messageId presence pattern

- **Preservation Property**: FOR ALL non-external broadcasts, behavior MUST remain identical
  - Generate random SPMB broadcast configurations
  - Compare results with reference implementation
  - Verify no behavioral changes

### Integration Tests

**Integration Test Scenarios**:

- **Full External Broadcast Flow**: Test end-to-end external broadcast dengan real database dan mocked gateway
  - Create batch with 10 recipients
  - Execute sendExternalBroadcast
  - Verify all confirmations waited, delays applied correctly
  - Verify database updates (sent_count, failed_count)
  - Verify UI progress updates

- **Mixed Success/Failure Scenario**: Test broadcast dengan mixed results
  - Setup: 5 recipients, #2 and #4 fail (no messageId)
  - Execute broadcast
  - Verify delays only after #1, #3, #5 (successes)
  - Verify no delays after #2, #4 (failures)
  - Verify accurate counts in database

- **Gateway Timeout Scenario**: Test broadcast dengan slow/hanging gateway
  - Setup: 3 recipients, #2 hangs for 6 minutes
  - Execute broadcast
  - Verify #1 processed successfully
  - Verify #2 timeout after 5 minutes, marked as failed
  - Verify #3 processed after #2 timeout

- **Real-Time Progress Updates**: Test progress feedback mechanism
  - Execute broadcast with 5 recipients
  - Poll/listen for progress updates
  - Verify updates received at regular intervals
  - Verify accurate counts and current recipient info
  - Verify completion notification
