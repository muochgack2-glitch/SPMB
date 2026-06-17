# WhatsApp Diagnostic Tool

## Masalah yang Diselesaikan

**Issue:** Database log WhatsApp menunjukkan status "sent" tapi pesan tidak sampai ke HP penerima.

**Kemungkinan Penyebab:**
1. Gateway return `{success: true}` padahal tidak terkirim (false positive)
2. Nomor HP tidak valid format
3. Nomor tidak terdaftar di WhatsApp
4. Gateway tidak connected tapi status check bilang OK
5. Rate limiting dari WhatsApp

## Fitur Diagnostic Tool

### 1. Test Send dengan Full Analysis
- Kirim pesan test ke nomor tertentu
- Lihat response detail dari gateway
- Validasi messageId (proof pengiriman)
- Deteksi false positive
- Rekomendasi perbaikan

### 2. Enhanced Logging
- Log response detail dari gateway
- Check keberadaan messageId
- Warning jika success tanpa messageId
- Debug info lengkap

## Cara Menggunakan

### Method 1: Via API (Postman/Insomnia)

**Endpoint:** `POST /whatsapp/diagnostic/test-send`

**Request:**
```json
{
  "phone": "628123456789",
  "message": "Test message"
}
```

**Response:**
```json
{
  "success": true,
  "diagnostic": {
    "test_phone": "628123456789",
    "test_message": "Test message dari WhatsApp Diagnostic Tool - 2026-06-17 10:30:00",
    "timestamp": "2026-06-17T10:30:00Z",
    "user": "Administrator Sistem",
    
    "gateway_status": {
      "server_url": "http://localhost:3000",
      "status_check": {
        "success": true,
        "data": {
          "status": "connected",
          "qr": null
        }
      }
    },
    
    "send_result": {
      "success": true,
      "message": "Message sent successfully",
      "data": {
        "success": true,
        "messageId": "BAE5XXXXXXXXXXXXX",
        "message": "Sent"
      },
      "log_id": 123,
      "has_message_id": true
    },
    
    "analysis": {
      "gateway_connected": true,
      "send_api_success": true,
      "has_message_id": true,
      "has_error": false,
      "log_id": 123
    },
    
    "recommendations": [
      {
        "issue": "Tidak ada masalah terdeteksi",
        "severity": "info",
        "action": "Cek HP penerima untuk konfirmasi pesan diterima"
      }
    ]
  }
}
```

### Method 2: Via Browser Console

```javascript
// Login ke sistem dulu, lalu buka console browser (F12)

fetch('/whatsapp/diagnostic/test-send', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({
        phone: '628123456789',
        message: 'Test dari console'
    })
})
.then(r => r.json())
.then(data => console.log(data));
```

## Interpretasi Hasil

### ✅ Normal (Sukses)
```json
{
  "send_result": {
    "success": true,
    "has_message_id": true
  },
  "recommendations": [
    {
      "issue": "Tidak ada masalah terdeteksi",
      "severity": "info"
    }
  ]
}
```
**Action:** Cek HP penerima, seharusnya pesan masuk.

---

### ⚠️ FALSE POSITIVE (Bahaya!)
```json
{
  "send_result": {
    "success": true,
    "has_message_id": false  // ← TIDAK ADA messageId!
  },
  "recommendations": [
    {
      "issue": "Tidak ada messageId dari gateway",
      "severity": "medium",
      "warning": "FALSE POSITIVE - Database log 'sent' tapi mungkin tidak sampai ke HP"
    }
  ]
}
```
**Action:** 
1. Cek HP penerima - kemungkinan besar **TIDAK** ada pesan masuk
2. **Bug di Gateway** - Gateway bilang success tapi tidak kirim
3. **Solusi:** Update/fix gateway code untuk return messageId
4. **Workaround:** Jangan percaya status "sent" tanpa messageId

---

### ❌ Gateway Tidak Terhubung
```json
{
  "gateway_status": {
    "status_check": {
      "success": false
    }
  },
  "recommendations": [
    {
      "issue": "Gateway tidak terhubung",
      "severity": "critical",
      "action": "Pastikan WhatsApp Gateway (PM2) berjalan dan scan QR code"
    }
  ]
}
```
**Action:**
1. Cek PM2: `pm2 status whatsapp-server`
2. Restart jika perlu: `pm2 restart whatsapp-server`
3. Scan QR code di `/whatsapp`

---

### ❌ Nomor Tidak Valid
```json
{
  "send_result": {
    "success": false,
    "message": "Phone not registered on WhatsApp"
  },
  "recommendations": [
    {
      "issue": "Format nomor HP mungkin salah",
      "severity": "medium",
      "action": "Nomor harus format 62xxx (contoh: 628123456789)",
      "current_format": "08123456789"
    }
  ]
}
```
**Action:** Fix format nomor HP ke 62xxx

---

## Enhanced Logging

### Log Laravel (storage/logs/laravel.log)

**Log Saat Send:**
```
[2026-06-17 10:30:00] local.INFO: WhatsApp gateway response detail
{
  "phone": "628123456789",
  "log_id": 123,
  "response_data": {
    "success": true,
    "messageId": "BAE5XXXXXXXXXXXXX",  // ← Proof pengiriman
    "message": "Sent"
  },
  "has_success_key": true,
  "success_value": true,
  "has_message_id": true,  // ← Validasi
  "message_id": "BAE5XXXXXXXXXXXXX",
  "server_url": "http://localhost:3000"
}
```

**Warning Jika False Positive:**
```
[2026-06-17 10:30:00] local.WARNING: WhatsApp gateway returned success without messageId
{
  "phone": "628123456789",
  "response": {
    "success": true
    // ← TIDAK ADA messageId!
  },
  "log_id": 123,
  "warning": "Message may not be actually sent - no messageId proof"
}
```

---

## Cek Database

### Query untuk Cek Log dengan messageId

```sql
SELECT 
    id,
    phone,
    message,
    status,
    response_data,
    JSON_EXTRACT(response_data, '$.messageId') as message_id_proof,
    created_at
FROM whatsapp_logs
WHERE type = 'external_broadcast'
    AND status = 'sent'
    AND DATE(created_at) = CURDATE()
ORDER BY created_at DESC
LIMIT 20;
```

**Interpretasi:**
- `message_id_proof` **ada** → ✅ Benar-benar terkirim
- `message_id_proof` **NULL** → ⚠️ FALSE POSITIVE, mungkin tidak terkirim

---

## Troubleshooting

### Masalah: Semua log "sent" tapi tidak ada yang masuk ke HP

**Diagnosis:**
```bash
# 1. Test send manual
curl -X POST http://localhost:3000/send \
  -H "Content-Type: application/json" \
  -d '{"phone":"628123456789","message":"test"}'

# 2. Cek response
# Jika response tidak punya messageId → Bug di gateway
```

**Solusi:**
1. Update gateway code untuk return `messageId` setelah kirim
2. Atau validasi `response_data` di Laravel sebelum mark as "sent"
3. Temporary: Filter di query hanya tampilkan yang ada messageId

---

### Masalah: Gateway bilang success tapi WhatsApp tidak connected

**Diagnosis:**
- Gateway status endpoint return `{status: "connected"}` padahal belum scan QR

**Solusi:**
- Fix gateway `/status` endpoint untuk cek real connection state
- Atau tambahkan validasi di Laravel: cek QR code null/tidak

---

### Masalah: Rate limiting

**Gejala:**
- 10 pesan pertama sukses, sisanya gagal
- Error: "Too many messages"

**Solusi:**
- Tambahkan delay lebih lama antara pesan (2-3 detik)
- Batasi jumlah pesan per batch (max 20-30)
- Split broadcast ke beberapa batch kecil

---

## Gateway Response Format Documentation

**Endpoint:** `GET /whatsapp/diagnostic/gateway-docs`

**Response:**
```json
{
  "success": true,
  "documentation": {
    "expected_success_response": {
      "success": true,
      "messageId": "BAE5XXXXXXXXXXXXX",
      "message": "Message sent successfully"
    },
    "expected_failure_response": {
      "success": false,
      "error": "Phone not registered on WhatsApp",
      "message": "Failed to send message"
    },
    "validation_rules": {
      "messageId_required": "Response harus punya messageId sebagai proof pengiriman",
      "success_key_required": "Response harus punya key 'success' (boolean)",
      "error_handling": "Kalau success=false, harus ada error message"
    },
    "known_issues": {
      "false_positive": "Gateway return success=true tapi tidak ada messageId",
      "phone_not_registered": "Nomor tidak terdaftar di WhatsApp",
      "rate_limiting": "Terlalu banyak pesan dalam waktu singkat",
      "connection_lost": "Gateway kehilangan koneksi ke WhatsApp"
    }
  }
}
```

---

## Next Steps

1. ✅ **Test Send** - Gunakan diagnostic tool untuk test
2. ✅ **Cek Log** - Lihat apakah ada messageId di response_data
3. ⚠️ **Jika Tidak Ada messageId** - FALSE POSITIVE detected
4. 🔧 **Fix Gateway** - Update gateway code untuk return messageId
5. 🔍 **Monitor** - Track false positive rate

---

**Date:** June 17, 2026  
**Commit:** 9f9b1c9  
**Status:** ✅ Diagnostic Tool Active
