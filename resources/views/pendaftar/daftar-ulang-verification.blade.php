@extends('layouts.admin')

@section('title', 'Verifikasi Daftar Ulang - SPMB (Sistem Penerimaan Murid Baru)')

@push('styles')
<link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<style>
    .dashboard-content {
        animation: zoomFadeIn 0.35s ease-out;
    }

    @keyframes zoomFadeIn {
        from {
            opacity: 0;
            transform: scale(0.97);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    .info-box {
        background: linear-gradient(135deg, var(--theme-primary) 0%, var(--theme-secondary) 100%);
        color: white;
        padding: 20px;
        border-radius: 12px;
        margin-bottom: 20px;
    }
    .info-box label {
        font-weight: 600;
        font-size: 12px;
        text-transform: uppercase;
        opacity: 0.9;
        display: block;
        margin-bottom: 5px;
    }
    .info-box .value {
        font-size: 20px;
        font-weight: 700;
    }
    .size-selector {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 12px;
        margin: 20px 0;
    }
    .size-btn {
        padding: 20px 15px;
        border: 2px solid #e0e0e0;
        border-radius: 12px;
        background: white;
        cursor: pointer;
        transition: all 0.3s ease;
        font-weight: 600;
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 100px;
        position: relative;
    }
    .size-btn > div:first-of-type {
        font-size: 28px;
        font-weight: 800;
        margin-bottom: 4px;
        color: #1f2937;
    }
    .size-btn small {
        font-size: 13px;
        font-weight: 500;
        color: #6b7280;
    }
    .size-btn:hover {
        border-color: var(--primary);
        background: #f0f4ff;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }
    .size-btn.selected {
        background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        color: white;
        border-color: var(--primary);
        box-shadow: 0 6px 20px rgba(var(--primary-rgb), 0.3);
    }
    .size-btn.selected > div:first-of-type {
        color: white;
    }
    .size-btn.selected small {
        color: rgba(255, 255, 255, 0.9);
    }
    .size-btn.selected::before {
        content: '✓';
        position: absolute;
        top: 8px;
        right: 8px;
        width: 24px;
        height: 24px;
        background: white;
        color: var(--primary);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 14px;
    }
    .rollback-card {
        border: 1px solid rgba(239, 68, 68, 0.2);
        background: linear-gradient(145deg, rgba(255, 255, 255, 0.98), rgba(254, 242, 242, 0.95));
        border-radius: 14px;
        padding: 16px;
        box-shadow: 0 10px 24px rgba(239, 68, 68, 0.1);
    }
    .rollback-title {
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 700;
        color: #b91c1c;
        margin-bottom: 8px;
    }
    .rollback-desc {
        margin-bottom: 14px;
        color: #7f1d1d;
        font-size: 14px;
    }
    
    /* Modern Button Styles */
    .btn-lg {
        padding: 12px 24px;
        font-size: 16px;
        font-weight: 600;
        border-radius: 12px;
        transition: all 0.3s ease;
    }
    .btn-success {
        background: linear-gradient(135deg, #10b981, #059669);
        border: none;
        color: white;
    }
    .btn-success:hover:not(:disabled) {
        background: linear-gradient(135deg, #059669, #047857);
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(16, 185, 129, 0.3);
    }
    .btn-success:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    .btn-warning {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        border: none;
        color: white;
    }
    .btn-warning:hover {
        background: linear-gradient(135deg, #d97706, #b45309);
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(245, 158, 11, 0.3);
        color: white;
    }
    .btn-secondary {
        background: #6b7280;
        border: none;
        color: white;
    }
    .btn-secondary:hover {
        background: #4b5563;
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(107, 114, 128, 0.3);
        color: white;
    }
    .btn-danger {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        border: none;
        color: white;
        padding: 10px 16px;
        font-weight: 600;
        border-radius: 10px;
        transition: all 0.25s ease;
    }
    .btn-danger:hover {
        background: linear-gradient(135deg, #dc2626, #b91c1c);
        transform: translateY(-1px);
        box-shadow: 0 8px 18px rgba(220, 38, 38, 0.28);
        color: white;
    }
</style>
@endpush

@section('content')
<div class="dashboard-content">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-3">
        <div>
            <h2 class="mb-2">Verifikasi Daftar Ulang</h2>
            <p class="text-muted mb-0">Proses verifikasi dan pilih ukuran kaos</p>
        </div>
        <x-button 
            variant="secondary" 
            outline="true"
            icon="fas fa-arrow-left"
            href="{{ route('pendaftar.verification-index') }}"
        >
            Kembali
        </x-button>
    </div>

    @if (Session::has('success'))
        <x-alert type="success" dismissible="true">
            {{ Session::get('success') }}
            
            @if (Session::has('wa_sent'))
                <div class="mt-2 pt-2 border-top border-success-subtle">
                    <i class="fas fa-check-circle text-success me-1"></i>
                    <strong>WhatsApp berhasil dikirim</strong> ke 
                    @if (Session::has('wa_phone_type'))
                        <span class="badge bg-info">{{ Session::get('wa_phone_type') }}</span>
                    @endif
                    <code>{{ Session::get('wa_phone') }}</code>
                </div>
            @endif
            
            @if (Session::has('wa_failed'))
                <div class="mt-2 pt-2 border-top border-warning-subtle">
                    <i class="fas fa-exclamation-triangle text-warning me-1"></i>
                    <strong>WhatsApp gagal dikirim:</strong> {{ Session::get('wa_error') }}
                </div>
            @endif
        </x-alert>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <!-- Data Pendaftar -->
            <x-section-card title="Data Pendaftar" icon="fas fa-user-circle" class="mb-4">
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-box">
                            <label>No. Registrasi</label>
                            <div class="value">{{ $pendaftar->no_registrasi }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-box">
                            <label>Status Daftar Ulang</label>
                            <div class="value">
                                @if ($logistik->status_bayar === 'Belum')
                                    <span style="font-size: 16px;">🔴 Belum Daftar Ulang</span>
                                @else
                                    <span style="font-size: 16px;">✅ Pendaftaran Selesai</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <x-form-group label="Nama">
                            <x-input 
                                name="nama" 
                                value="{{ $pendaftar->nama_lengkap }}" 
                                readonly="true"
                                icon="fas fa-user"
                            />
                        </x-form-group>
                        <x-form-group label="NISN">
                            <x-input 
                                name="nisn" 
                                value="{{ $pendaftar->nisn }}" 
                                readonly="true"
                                icon="fas fa-id-card"
                            />
                        </x-form-group>
                        <x-form-group label="Jurusan">
                            <x-input 
                                name="jurusan" 
                                value="{{ $pendaftar->jurusan }}" 
                                readonly="true"
                                icon="fas fa-graduation-cap"
                            />
                        </x-form-group>
                    </div>
                    <div class="col-md-6">
                        <x-form-group label="Asal Sekolah">
                            <x-input 
                                name="asal_sekolah" 
                                value="{{ $pendaftar->asal_sekolah }}" 
                                readonly="true"
                                icon="fas fa-school"
                            />
                        </x-form-group>
                        <x-form-group label="Alamat">
                            <x-textarea 
                                name="alamat" 
                                rows="2"
                                readonly="true"
                            >{{ $pendaftar->alamat }}</x-textarea>
                        </x-form-group>
                        <x-form-group label="Gelombang">
                            <x-input 
                                name="gelombang" 
                                value="{{ $pendaftar->gelombang }}" 
                                readonly="true"
                                icon="fas fa-calendar"
                            />
                        </x-form-group>
                    </div>
                </div>
            </x-section-card>

            <!-- Pilih Ukuran Kaos -->
            @if ($logistik->status_bayar === 'Belum')
                <x-section-card title="Pilih Ukuran Kaos" icon="fas fa-shirt">
                    <form id="formPayment" method="POST" action="{{ route('pendaftar.process-daftar-ulang', $pendaftar->id_pendaftar) }}">
                        @csrf

                        <p class="text-muted mb-3">Sebelum menyelesaikan daftar ulang, pilih ukuran kaos yang diinginkan:</p>

                        <div class="size-selector">
                            <div>
                                <input type="radio" id="size_s" name="ukuran_kaos" value="S" required style="display: none;">
                                <label for="size_s" class="size-btn">
                                    <div>S</div>
                                    <small>Kecil</small>
                                </label>
                            </div>
                            <div>
                                <input type="radio" id="size_m" name="ukuran_kaos" value="M" required style="display: none;">
                                <label for="size_m" class="size-btn">
                                    <div>M</div>
                                    <small>Sedang</small>
                                </label>
                            </div>
                            <div>
                                <input type="radio" id="size_l" name="ukuran_kaos" value="L" required style="display: none;">
                                <label for="size_l" class="size-btn">
                                    <div>L</div>
                                    <small>Besar</small>
                                </label>
                            </div>
                            <div>
                                <input type="radio" id="size_xl" name="ukuran_kaos" value="XL" required style="display: none;">
                                <label for="size_xl" class="size-btn">
                                    <div>XL</div>
                                    <small>Sangat Besar</small>
                                </label>
                            </div>
                            <div>
                                <input type="radio" id="size_xxl" name="ukuran_kaos" value="XXL" required style="display: none;">
                                <label for="size_xxl" class="size-btn">
                                    <div>XXL</div>
                                    <small>Extra Besar</small>
                                </label>
                            </div>
                            <div>
                                <input type="radio" id="size_jumbo" name="ukuran_kaos" value="JUMBO" required style="display: none;">
                                <label for="size_jumbo" class="size-btn">
                                    <div>JUMBO</div>
                                    <small>Jumbo</small>
                                </label>
                            </div>
                        </div>

                        <input type="hidden" name="selected_size" id="selected_size">

                        <div class="d-flex gap-2 mt-4 flex-wrap">
                            <button type="button" id="btnVerifyPayment" class="btn btn-success btn-lg" disabled>
                                <i class="fas fa-check-circle"></i> Verifikasi Daftar Ulang
                            </button>
                            <a href="{{ route('pendaftar.print.ambil-barang', $pendaftar->id_pendaftar) }}" target="_blank" class="btn btn-warning btn-lg">
                                <i class="fas fa-print"></i> Cetak Bukti Ambil Barang
                            </a>
                            <a href="{{ route('pendaftar.verification-index') }}" class="btn btn-secondary btn-lg">
                                <i class="fas fa-times"></i> Kembali
                            </a>
                        </div>
                    </form>
                </x-section-card>
            @else
                <x-alert type="info" class="mb-3">
                    Pendaftaran sudah selesai. <strong>Kaos dipesankan</strong>.
                </x-alert>

                <div class="rollback-card mb-3">
                    <div class="rollback-title">
                        <i class="fas fa-triangle-exclamation"></i>
                        Rollback Verifikasi Daftar Ulang
                    </div>
                    <p class="rollback-desc">
                        Aksi ini akan mengembalikan status menjadi <strong>Belum Daftar Ulang</strong> dan mereset ukuran kaos/logistik.
                    </p>
                    <form id="rollbackPaymentForm" method="POST" action="{{ route('pendaftar.cancel-daftar-ulang', $pendaftar->id_pendaftar) }}">
                        @csrf
                        <button type="submit" id="btnRollbackPayment" class="btn btn-danger">
                            <i class="fas fa-rotate-left"></i> Ya, Kembalikan ke Belum Daftar Ulang
                        </button>
                    </form>
                </div>

                <x-button 
                    variant="warning"
                    icon="fas fa-print"
                    href="{{ route('pendaftar.print.ambil-barang', $pendaftar->id_pendaftar) }}"
                    target="_blank"
                >
                    Cetak Bukti Ambil Barang
                </x-button>
            @endif
        </div>

        <div class="col-lg-4">
            <x-section-card title="Checklist Proses" icon="fas fa-list-check">
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <i class="fas fa-check-circle" style="color: {{ $logistik->status_bayar === 'Lunas' ? '#27ae60' : '#ccc' }};"></i>
                        Verifikasi Daftar Ulang
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check-circle" style="color: {{ $logistik->status_kain === 'Sudah' ? '#27ae60' : '#ccc' }};"></i>
                        Kain Disiapkan
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check-circle" style="color: {{ $logistik->status_kaos === 'Proses' || $logistik->status_kaos === 'Sudah' ? '#27ae60' : '#ccc' }};"></i>
                        Kaos Dipesankan
                    </li>
                </ul>
                <div class="mt-4 pt-3 border-top">
                    <h6>Ukuran Kaos Dipilih:</h6>
                    @if ($logistik->ukuran_kaos)
                        <p style="font-size: 24px; font-weight: 700; color: var(--primary);">{{ $logistik->ukuran_kaos }}</p>
                    @else
                        <p class="text-muted">Belum dipilih</p>
                    @endif
                </div>
            </x-section-card>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Size selector UI
    const sizeInputs = document.querySelectorAll('input[name="ukuran_kaos"]');
    const btnVerify = document.getElementById('btnVerifyPayment');
    const formPayment = document.getElementById('formPayment');
    const sizeLabels = document.querySelectorAll('.size-btn');

    sizeLabels.forEach((label, index) => {
        label.addEventListener('click', (e) => {
            e.preventDefault();
            sizeInputs[index].checked = true;

            // Update UI
            sizeLabels.forEach(l => l.classList.remove('selected'));
            label.classList.add('selected');

            // Enable button
            if (btnVerify) {
                btnVerify.disabled = false;
            }
        });
    });

    // Form submission with Modal.confirm
    if (btnVerify) {
        btnVerify.addEventListener('click', () => {
            const selected = document.querySelector('input[name="ukuran_kaos"]:checked');
            if (!selected) return;
            const selectedSize = selected.value;

            Modal.confirm(
                `Verifikasi daftar ulang dan ukuran kaos <strong>${selectedSize}</strong>?`,
                function() {
                    // On confirm
                    btnVerify.disabled = true;
                    btnVerify.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
                    formPayment.submit();
                },
                {
                    title: 'Konfirmasi Daftar Ulang',
                    confirmText: 'Ya, Verifikasi',
                    cancelText: 'Batal',
                    type: 'info'
                }
            );
        });
    }

    // Rollback confirmation with Modal.confirm
    const rollbackForm = document.getElementById('rollbackPaymentForm');
    const btnRollback = document.getElementById('btnRollbackPayment');
    
    if (rollbackForm) {
        rollbackForm.addEventListener('submit', (e) => {
            e.preventDefault();
            e.stopImmediatePropagation();

            Modal.confirm(
                'Status daftar ulang akan kembali ke <strong>Belum Daftar Ulang</strong>.<br><small class="text-muted">Ukuran kaos dan progres logistik akan direset.</small>',
                function() {
                    // On confirm - use native submit (bypasses event listeners)
                    if (btnRollback) {
                        btnRollback.disabled = true;
                        btnRollback.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
                    }
                    console.log('Submitting form via native method');
                    HTMLFormElement.prototype.submit.call(rollbackForm);
                },
                {
                    title: 'Batalkan Verifikasi?',
                    confirmText: 'Ya, Rollback',
                    cancelText: 'Tetap Pendaftaran Selesai',
                    type: 'danger'
                }
            );
            
            return false;
        });
    }
</script>
@endpush
