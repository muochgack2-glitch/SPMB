# Implementation Plan

## Phase 1: Exploratory Testing (Write Tests BEFORE Fix)

- [x] 1. Write bug condition exploration test
  - **Property 1: Bug Condition** - Sequential Confirmation Wait Violation
  - **CRITICAL**: This test MUST FAIL on unfixed code - failure confirms the bug exists
  - **DO NOT attempt to fix the test or the code when it fails**
  - **NOTE**: This test encodes the expected behavior - it will validate the fix when it passes after implementation
  - **GOAL**: Surface counterexamples that demonstrate the bug exists
  - **Scoped PBT Approach**: Test external broadcast scenarios where system proceeds without waiting for gateway confirmation
  - Create PHPUnit test class `ExternalBroadcastBugConditionTest`
  - Mock WhatsAppService to simulate gateway responses with delays (3-5 seconds)
  - Test implementation details:
    - External broadcast with 2 recipients
    - Mock gateway to delay response 5 seconds for first message
    - Assert second message NOT sent before first message confirmed (should fail on unfixed code)
    - Assert delay NOT applied before confirmation received (should fail on unfixed code)
    - Assert messageId verified before proceeding (should fail on unfixed code)
  - Test failed message handling:
    - Mock gateway to return `success: false` (no messageId)
    - Assert delay NOT applied for failed messages (should fail on unfixed code)
  - Test success without messageId:
    - Mock gateway to return `success: true` but no messageId
    - Assert delay NOT applied without messageId proof (should fail on unfixed code)
  - Run test on UNFIXED code
  - **EXPECTED OUTCOME**: Test FAILS - confirms bug exists (delay applied immediately, no confirmation wait, no messageId check)
  - Document counterexamples found:
    - Second message sent before first confirmed
    - Delay applied before gateway response received
    - Failed messages receive delay
    - No messageId verification
  - Mark task complete when test is written, run, and failures are documented
  - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5_

- [ ] 2. Write preservation property tests (BEFORE implementing fix)
  - **Property 2: Preservation** - Non-External Broadcast Behavior Unchanged
  - **IMPORTANT**: Follow observation-first methodology
  - Observe behavior on UNFIXED code for non-buggy inputs:
    - SPMB broadcasts (non-external) - observe delay patterns, success/failed counts
    - Rate limiting settings retrieval from database
    - WhatsAppLog creation format (type, external_batch_id fields)
    - Phone list UI external tab display
  - Create PHPUnit test class `ExternalBroadcastPreservationTest`
  - Write property-based tests capturing observed behavior patterns:
    - Test 1: SPMB Broadcast Behavior Preserved
      - Generate random SPMB broadcast configurations (5-20 recipients)
      - Execute on unfixed code, record results (counts, delays, logs)
      - Write test asserting same results after fix
    - Test 2: Rate Limiting Settings Unchanged
      - Retrieve rate limiting settings (min_delay, max_delay, break_interval, break_duration)
      - Assert settings retrieved from database identically
      - Assert values used in delay calculations unchanged
    - Test 3: WhatsAppLog Format Unchanged
      - Create external broadcast and observe log entry structure
      - Assert log has type "external_broadcast", external_batch_id present
      - Assert status values and fields structure identical
    - Test 4: Phone List UI Display Unchanged
      - Load phone list external tab
      - Observe recipient list, message counts, batch info display
      - Assert display format and data identical after fix
  - Run tests on UNFIXED code
  - **EXPECTED OUTCOME**: Tests PASS (confirms baseline behavior to preserve)
  - Mark task complete when tests are written, run, and passing on unfixed code
  - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 3.6_

## Phase 2: Implementation

- [ ] 3. Backend Enhancement - sendExternalBroadcast Method

  - [ ] 3.1 Enhance response processing with messageId verification
    - File: `app/Http/Controllers/WhatsAppController.php`
    - Modify response handling logic (around line ~1906)
    - Add messageId presence check after receiving gateway response
    - Check multiple messageId formats:
      - `$result['data']['messageId']`
      - `$result['data']['message_id']`
      - `$result['has_message_id']` flag
    - Treat success WITHOUT messageId as suspicious/failed
    - Update success/failed count logic based on messageId presence
    - _Bug_Condition: isBugCondition(input) where input.is_external_broadcast = true AND input.gateway_response_received = false AND input.proceeding_to_next = true_
    - _Expected_Behavior: System SHALL verify messageId presence and only count as success if messageId present_
    - _Preservation: Rate limiting settings continue from database (3.1)_
    - _Requirements: 2.2_

  - [ ] 3.2 Implement conditional delay application
    - File: `app/Http/Controllers/WhatsAppController.php`
    - Modify delay logic (around line ~1910-1928)
    - Add condition: only apply delay if messageId present (confirmed success)
    - Skip delay entirely for failed messages (no messageId)
    - Preserve existing rate limiting settings usage:
      - `WhatsAppSetting::getExternalBroadcastMinDelay()`
      - `WhatsAppSetting::getExternalBroadcastMaxDelay()`
      - `WhatsAppSetting::getExternalBroadcastBreakInterval()`
      - `WhatsAppSetting::getExternalBroadcastBreakDuration()`
    - Apply random delay between min and max only after confirmed success
    - Apply break duration at correct intervals
    - _Bug_Condition: input.delay_applied_without_confirmation = true_
    - _Expected_Behavior: Delay applied ONLY if messageId present (success), skipped for failed messages_
    - _Preservation: Rate limiting calculation and break intervals unchanged (3.1)_
    - _Requirements: 2.3, 2.4, 3.1_

  - [ ] 3.3 Add timeout protection mechanism
    - File: `app/Http/Controllers/WhatsAppController.php`
    - Wrap send operation with timeout tracking (around line ~1870)
    - Set max timeout: 300 seconds (5 minutes) per message
    - Track send start time before calling `whatsappService->send()`
    - Check duration after send operation completes
    - Log warning if timeout exceeded
    - Mark message as failed if timeout exceeded and not already marked
    - Allow broadcast to continue to next recipient after timeout
    - _Expected_Behavior: System SHALL timeout after 5 minutes max per message_
    - _Preservation: WhatsApp Gateway send method signature unchanged (3.3)_
    - _Requirements: 2.5_

  - [ ] 3.4 Verify accurate sent_at timestamp recording
    - File: `app/Http/Controllers/WhatsAppController.php`
    - Verify timestamp logic in WhatsAppLog creation
    - Ensure `sent_at` recorded AFTER gateway confirmation received
    - Current implementation uses `WhatsAppLog::markAsSent()` in `WhatsAppService`
    - Verify it's called after response received, not before
    - Log is created as 'pending', then marked as 'sent' after response
    - This should already be correct, just verify and document
    - _Expected_Behavior: sent_at timestamp SHALL be accurate (after confirmation)_
    - _Preservation: WhatsAppLog creation flow unchanged (3.2)_
    - _Requirements: 2.6_

  - [ ] 3.5 Verify bug condition exploration test now passes
    - **Property 1: Expected Behavior** - Sequential Confirmation Wait Works
    - **IMPORTANT**: Re-run the SAME test from task 1 - do NOT write a new test
    - The test from task 1 encodes the expected behavior
    - When this test passes, it confirms the expected behavior is satisfied
    - Run `ExternalBroadcastBugConditionTest` test class
    - **EXPECTED OUTCOME**: Test PASSES (confirms bug is fixed)
    - Verify assertions pass:
      - Second message NOT sent before first confirmed ✓
      - Delay applied only AFTER confirmation ✓
      - MessageId verified before proceeding ✓
      - Failed messages skip delay ✓
      - Success without messageId skips delay ✓
    - _Requirements: 2.1, 2.2, 2.3, 2.4_

  - [ ] 3.6 Verify preservation tests still pass
    - **Property 2: Preservation** - Non-External Broadcast Behavior Unchanged
    - **IMPORTANT**: Re-run the SAME tests from task 2 - do NOT write new tests
    - Run `ExternalBroadcastPreservationTest` test class
    - **EXPECTED OUTCOME**: Tests PASS (confirms no regressions)
    - Verify all preservation tests pass:
      - SPMB broadcast behavior identical ✓
      - Rate limiting settings unchanged ✓
      - WhatsAppLog format unchanged ✓
      - Phone list UI display unchanged ✓
    - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 3.6_

- [ ] 4. Backend Enhancement - Progress Tracking Endpoint

  - [ ] 4.1 Add progress status endpoint
    - File: `routes/web.php`
    - Add new route: `GET /whatsapp/broadcast/external/status/{batch_id}`
    - Route name: `whatsapp.broadcast.external.status`
    - Method: `externalBroadcastStatus` in `WhatsAppController`
    - _Preservation: Existing routes unchanged_
    - _Requirements: 2.7_

  - [ ] 4.2 Implement externalBroadcastStatus method
    - File: `app/Http/Controllers/WhatsAppController.php`
    - Create new method `externalBroadcastStatus($batchId)`
    - Retrieve ExternalBroadcastBatch by ID
    - Return 404 JSON response if batch not found
    - Return JSON response with:
      - `status`: batch status (pending/processing/completed/failed)
      - `total`: total_recipients count
      - `sent`: sent_count (successful with messageId)
      - `failed`: failed_count
      - `current_index`: sent + failed (current position)
      - `progress_percent`: calculated percentage
    - Include success flag in response
    - _Expected_Behavior: Provide real-time progress data for polling_
    - _Preservation: Existing broadcast methods unchanged_
    - _Requirements: 2.7_

  - [ ] 4.3 Add unit tests for progress endpoint
    - Create test class `ExternalBroadcastProgressTest`
    - Test batch found scenario - verify correct data returned
    - Test batch not found scenario - verify 404 response
    - Test progress calculation accuracy
    - Test response format matches specification
    - _Requirements: 2.7_

- [ ] 5. Frontend Enhancement - Real-Time Progress Updates

  - [ ] 5.1 Implement AJAX polling for progress updates
    - File: `resources/views/whatsapp/broadcast.blade.php`
    - Modify `executeExternalBroadcast()` JavaScript function
    - Start broadcast request asynchronously (non-blocking)
    - Implement polling loop using `setInterval` (500ms interval)
    - Fetch progress from `/whatsapp/broadcast/external/status/{batch_id}`
    - Handle fetch errors gracefully
    - Stop polling when status is 'completed' or 'failed'
    - Clear interval on completion
    - _Expected_Behavior: Poll backend every 500ms for progress updates_
    - _Preservation: Existing broadcast UI structure unchanged_
    - _Requirements: 2.7_

  - [ ] 5.2 Implement progress display updates
    - File: `resources/views/whatsapp/broadcast.blade.php`
    - Update progress bar width and text on each poll response
    - Update success count display (`#successCount` element)
    - Update failed count display (`#failedCount` element)
    - Update remaining count display (`#remainingCount` element)
    - Update current progress text with index/total
    - Show completion message when broadcast finishes
    - Display final statistics (total sent, failed, success rate)
    - _Expected_Behavior: Real-time UI updates showing progress_
    - _Preservation: Existing UI elements and styles unchanged_
    - _Requirements: 2.7_

  - [ ] 5.3 Add error handling for progress polling
    - Handle network errors during polling
    - Show user-friendly error message if polling fails
    - Implement fallback: continue showing last known progress
    - Add retry logic (3 retries with exponential backoff)
    - Stop polling after max retries, show warning
    - _Expected_Behavior: Graceful degradation if polling fails_
    - _Requirements: 2.7_

## Phase 3: Testing & Validation

- [ ] 6. Unit Testing

  - [ ] 6.1 Response processing unit tests
    - Test messageId detection from `data.messageId` format
    - Test messageId detection from `data.message_id` format
    - Test messageId detection from `has_message_id` flag
    - Test response without messageId (should treat as failed)
    - Test response with success=false (explicit failure)
    - Mock WhatsAppService responses for each scenario
    - _Requirements: 2.2_

  - [ ] 6.2 Delay logic unit tests
    - Test delay applied after success with messageId
    - Test no delay after failure (no messageId)
    - Test delay calculation uses database settings correctly
    - Test break interval logic at correct intervals
    - Test edge case: last message in batch (no delay needed)
    - Mock time and database settings
    - _Requirements: 2.3, 2.4, 3.1_

  - [ ] 6.3 Timeout protection unit tests
    - Test normal send within timeout (fast response)
    - Test send approaching timeout (290 seconds)
    - Test send exceeding timeout (400 seconds) - should fail gracefully
    - Test timeout marked as failed in statistics
    - Mock gateway delays using sleep simulation
    - _Requirements: 2.5_

  - [ ] 6.4 Timestamp accuracy unit tests
    - Test sent_at recorded after confirmation received
    - Test sent_at NOT recorded before send operation
    - Test sent_at accuracy for slow responses (3-5 seconds)
    - Compare timestamp differences
    - _Requirements: 2.6_

- [ ] 7. Integration Testing

  - [ ] 7.1 Full external broadcast flow test
    - Setup: Create ExternalBroadcastBatch with 10 recipients
    - Mock WhatsAppService with varied responses
    - Execute `sendExternalBroadcast` method
    - Verify confirmations waited for each message
    - Verify delays applied only after successful confirmations
    - Verify database updates (sent_count, failed_count) accurate
    - Verify batch status transitions correctly
    - Test with real database (SQLite in-memory for CI)
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.6_

  - [ ] 7.2 Mixed success/failure scenario test
    - Setup: 5 recipients, configure #2 and #4 to fail (no messageId)
    - Execute broadcast
    - Verify delays only after #1, #3, #5 (successes)
    - Verify no delays after #2, #4 (failures)
    - Verify accurate counts in database (3 success, 2 failed)
    - Verify total execution time reflects skipped delays
    - _Requirements: 2.2, 2.3, 2.4_

  - [ ] 7.3 Gateway timeout scenario test
    - Setup: 3 recipients, configure #2 to hang for 360 seconds (6 minutes)
    - Execute broadcast
    - Verify #1 processed successfully with delay
    - Verify #2 timeout after 300 seconds (5 minutes), marked as failed
    - Verify #3 processed after #2 timeout
    - Verify total execution time reasonable (~305-310 seconds + delays)
    - _Requirements: 2.5_

  - [ ] 7.4 Real-time progress updates integration test
    - Execute broadcast with 5 recipients
    - Poll progress endpoint at 500ms intervals
    - Verify progress updates received consistently
    - Verify counts accurate at each poll (sent, failed, remaining)
    - Verify progress_percent calculation correct
    - Verify completion notification received
    - Test using Laravel HTTP testing tools
    - _Requirements: 2.7_

## Phase 4: Documentation

- [ ] 8. Code Documentation

  - [ ] 8.1 Add inline documentation to sendExternalBroadcast
    - Add PHPDoc block explaining sequential confirmation wait pattern
    - Document messageId verification logic
    - Document conditional delay application
    - Document timeout protection mechanism
    - Add code comments for complex logic sections
    - _Requirements: All_

  - [ ] 8.2 Update WhatsAppService documentation
    - Document expected response format from send() method
    - Document messageId field requirements
    - Document timeout behavior
    - Add examples of successful and failed responses
    - _Requirements: 2.2, 2.5_

  - [ ] 8.3 Document progress polling API
    - Add API documentation for `/whatsapp/broadcast/external/status/{batch_id}`
    - Document request format (GET with batch_id parameter)
    - Document response format with all fields
    - Document error responses (404 when batch not found)
    - Add usage examples for frontend integration
    - _Requirements: 2.7_

- [ ] 9. User Documentation

  - [ ] 9.1 Update user guide for external broadcast
    - Document new real-time progress display feature
    - Explain what "waiting for confirmation" means to users
    - Document expected timing differences (slower but more reliable)
    - Add screenshots of progress display
    - Explain success/failed counts meaning
    - _Requirements: 2.7_

  - [ ] 9.2 Document troubleshooting steps
    - What to do if broadcast seems stuck (timeout protection)
    - How to interpret failed message counts
    - When to retry failed broadcasts
    - Contact support if issues persist
    - _Requirements: 2.5, 2.7_

- [ ] 10. Checkpoint - Ensure all tests pass
  - Run all unit tests: `php artisan test --filter=ExternalBroadcast`
  - Run all integration tests: `php artisan test --testsuite=Feature`
  - Verify bug condition test passes (was failing before fix)
  - Verify preservation tests still pass (no regressions)
  - Run full test suite: `php artisan test`
  - Verify code coverage meets minimum threshold (>80%)
  - Review test results, fix any failures
  - Ensure all assertions pass
  - Ask user if questions arise or manual testing needed

## Summary

**Phases:**
1. **Exploratory Testing** - Write tests BEFORE fix to confirm bug exists
2. **Implementation** - Apply fix with confirmation wait, messageId verification, conditional delays
3. **Testing & Validation** - Comprehensive unit and integration tests
4. **Documentation** - Code and user documentation

**Key Changes:**
- Enhanced response processing with messageId verification
- Conditional delay application (only after confirmed success)
- Timeout protection (5 minutes max per message)
- Real-time progress updates via AJAX polling
- Accurate sent_at timestamp recording

**Testing Strategy:**
- Bug condition exploration test (Property 1) - confirms bug on unfixed code
- Preservation tests (Property 2) - ensures no regressions
- Comprehensive unit tests for all logic changes
- Integration tests for end-to-end flows
- Property-based tests for preservation guarantees
