<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pendaftar extends Model
{
    use SoftDeletes;
    
    protected $table = 'pendaftar';
    protected $primaryKey = 'id_pendaftar';
    
    protected $fillable = [
        'no_registrasi',
        'nisn',
        'nik',
        'no_kip',
        'nama_lengkap',
        'email',
        'no_telepon',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'agama',
        'asal_sekolah',
        'tahun_lulus',
        'alamat',
        'alamat_jalan',
        'alamat_dukuh',
        'alamat_rt',
        'alamat_rw',
        'alamat_kelurahan',
        'alamat_kecamatan',
        'alamat_kabupaten',
        'alamat_provinsi',
        'nama_ayah',
        'pekerjaan_ayah',
        'alamat_ayah',
        'nama_ibu',
        'pekerjaan_ibu',
        'alamat_ibu',
        'no_hp_ortu',
        'nama_wali',
        'pekerjaan_wali',
        'no_hp_wali',
        'alamat_wali',
        'jurusan',
        'jurusan_id',
        'nama_jaringan',
        'gelombang',
        'tgl_daftar',
        'status_siswa',
        'status_data',
        'tahun_ajaran',
        'wa_welcome_sent',
        'wa_welcome_sent_at',
        'wa_welcome_sent_to',
        'wa_welcome_recipient_type',
        'deleted_by',
        'deleted_reason',
    ];

    protected $casts = [
        'tgl_daftar' => 'datetime',
        'tanggal_lahir' => 'date',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relationship to user who deleted this record
     */
    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by', 'id');
    }

    /**
     * Get the logistik for this pendaftar
     */
    public function logistik(): HasOne
    {
        return $this->hasOne(LogistikBayar::class, 'id_pendaftar', 'id_pendaftar');
    }

    public function masterJurusan(): BelongsTo
    {
        return $this->belongsTo(Jurusan::class, 'jurusan_id');
    }

    /**
     * Get all WhatsApp messages sent to this pendaftar
     */
    public function whatsappLogs()
    {
        return $this->hasMany(WhatsAppLog::class, 'pendaftar_id', 'id_pendaftar');
    }
    
    /**
     * Relationship to TahunAjaran
     */
    public function tahunAjaranRelation(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class, 'tahun_ajaran', 'tahun');
    }
}
