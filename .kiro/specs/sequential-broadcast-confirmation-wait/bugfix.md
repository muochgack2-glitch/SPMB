# Bugfix Requirements Document

## Introduction

External broadcast mengirim pesan WhatsApp tanpa menunggu konfirmasi dari gateway sebelum mengirim pesan berikutnya. Hal ini menyebabkan pattern pengiriman yang teratur dan mudah terdeteksi sebagai spam oleh WhatsApp Gateway. Bug ini terjadi pada method `sendExternalBroadcast` di `WhatsAppController.php` yang langsung menerapkan sleep delay setelah mengirim request tanpa memverifikasi bahwa gateway telah menerima dan mengonfirmasi pengiriman pesan.

Dampak dari bug ini:
- Pattern pengiriman terlalu teratur dan cepat, mudah terdeteksi sebagai bot
- Gateway bisa overwhelmed jika response lambat karena sistem tidak menunggu
- Tidak ada validasi apakah pesan benar-benar diterima gateway sebelum lanjut
- Failed messages tetap mendapat delay yang sama, membuang waktu

Fix ini akan mengimplementasikan sequential confirmation wait pattern yang menunggu response dari gateway (dengan messageId sebagai bukti sukses) sebelum proceed ke pesan berikutnya, dengan delay hanya diterapkan setelah sukses.

## Bug Analysis

### Current Behavior (Defect)

1.1 WHEN external broadcast loop mengirim request ke gateway THEN sistem langsung sleep delay 2-4 detik tanpa menunggu response confirmation

1.2 WHEN sistem mengirim pesan berikutnya THEN sistem tidak memverifikasi apakah pesan sebelumnya telah diterima gateway (tidak ada check untuk messageId)

1.3 WHEN pattern pengiriman berjalan dengan interval tetap THEN gateway mendeteksi pattern teratur yang mudah diidentifikasi sebagai bot/spam

1.4 WHEN gateway response lambat THEN sistem tetap mengirim pesan berikutnya tanpa menunggu, menyebabkan gateway overwhelmed

1.5 WHEN pesan gagal dikirim THEN sistem tetap menerapkan delay yang sama seperti pesan sukses, membuang waktu tunggu

### Expected Behavior (Correct)

2.1 WHEN external broadcast loop mengirim request ke gateway THEN sistem SHALL menunggu hingga menerima response confirmation dari gateway sebelum proceed

2.2 WHEN sistem menerima response dari gateway THEN sistem SHALL memverifikasi keberadaan messageId sebagai bukti pesan berhasil diterima

2.3 WHEN messageId ditemukan dalam response (sukses) THEN sistem SHALL menerapkan random delay 2-4 detik sebelum mengirim pesan berikutnya

2.4 WHEN messageId tidak ditemukan dalam response (gagal) THEN sistem SHALL langsung proceed ke pesan berikutnya tanpa delay

2.5 WHEN sistem menunggu response THEN sistem SHALL menerapkan timeout maksimal 5 menit (300 detik) untuk mencegah hang

2.6 WHEN setiap pesan selesai diproses THEN sistem SHALL menyimpan timestamp `sent_at` yang akurat ke database

2.7 WHEN broadcast berjalan THEN sistem SHALL memberikan real-time progress feedback menampilkan: X/Y sent, Z failed, current message

### Unchanged Behavior (Regression Prevention)

3.1 WHEN sistem mengirim broadcast THEN sistem SHALL CONTINUE TO menggunakan rate limiting settings dari database (min_delay, max_delay, break_interval, break_duration)

3.2 WHEN sistem membuat WhatsAppLog entry THEN sistem SHALL CONTINUE TO menyimpan type "external_broadcast" dan external_batch_id

3.3 WHEN sistem mengirim pesan ke WhatsApp Gateway THEN sistem SHALL CONTINUE TO menggunakan method send() dari WhatsAppService yang sudah ada

3.4 WHEN admin melihat phone list THEN sistem SHALL CONTINUE TO menampilkan external broadcast history dengan format yang sama

3.5 WHEN broadcast selesai THEN sistem SHALL CONTINUE TO menghitung success rate (sent/total) untuk batch

3.6 WHEN user membatalkan broadcast THEN sistem SHALL CONTINUE TO berhenti dan menyimpan partial results

## Bug Condition and Property

### Bug Condition C(X)

```pascal
FUNCTION isBugCondition(X)
  INPUT: X of type BroadcastRequest
  OUTPUT: boolean
  
  // Returns true when broadcast sends next message without waiting for confirmation
  RETURN (X.is_external_broadcast = true) AND 
         (X.current_message_sent = true) AND 
         (X.gateway_response_received = false) AND
         (X.proceeding_to_next = true)
END FUNCTION
```

### Property Specification: Fix Checking

```pascal
// Property: Sequential Confirmation Wait
FOR ALL X WHERE isBugCondition(X) DO
  result ← sendExternalBroadcast'(X)
  
  // Verify confirmation wait
  ASSERT result.waited_for_confirmation = true
  
  // Verify messageId check
  ASSERT (result.messageId_present = true) OR (result.send_failed = true)
  
  // Verify delay only after success
  ASSERT (result.delay_applied = true AND result.messageId_present = true) OR
         (result.delay_applied = false AND result.send_failed = true)
  
  // Verify timeout protection
  ASSERT result.wait_time <= 300 seconds
  
  // Verify accurate timestamp
  ASSERT result.sent_at_timestamp IS NOT NULL
  
  // Verify progress feedback
  ASSERT result.progress_updated = true
END FOR
```

### Preservation Goal

```pascal
// Property: Preservation Checking
FOR ALL X WHERE NOT isBugCondition(X) DO
  // For non-external broadcasts (SPMB broadcasts)
  ASSERT sendBroadcast(X) = sendBroadcast'(X)
  
  // Rate limiting settings preserved
  ASSERT getRateLimitSettings(X) = getRateLimitSettings'(X)
  
  // WhatsAppLog format preserved
  ASSERT createLog(X).format = createLog'(X).format
  
  // Existing UI behavior preserved
  ASSERT displayPhoneList(X) = displayPhoneList'(X)
END FOR
```

**Key Definitions:**
- **F**: `sendExternalBroadcast()` - original function (tidak tunggu konfirmasi)
- **F'**: `sendExternalBroadcast'()` - fixed function (tunggu konfirmasi dengan messageId check)

**Bug Condition Explanation:**
Bug terjadi ketika external broadcast loop proceed ke pesan berikutnya (`proceeding_to_next = true`) sebelum menerima response confirmation dari gateway (`gateway_response_received = false`).

**Fix Goal:**
Sistem harus menunggu response dari gateway dan memverifikasi keberadaan `messageId` sebelum proceed. Delay hanya diterapkan setelah sukses (messageId present), dan skip delay jika gagal.
