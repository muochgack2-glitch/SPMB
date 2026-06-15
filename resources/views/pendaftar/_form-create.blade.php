{{-- Form Create Pendaftar - Data Awal Saja --}}

<div class="dashboard-content">
    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-3">
        <div>
            <h2 class="mb-2">Tambah Pendaftar Baru</h2>
            <p class="text-muted mb-0">Isi data awal pendaftar. Data lengkap dapat dilengkapi setelah pendaftar dibuat.</p>
        </div>
        <a href="{{ route('pendaftar.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i> Kembali
        </a>
    </div>

    {{-- Error Alert --}}
    @if ($errors->any())
        <x-alert type="danger" dismissible="true">
            <strong>Terjadi kesalahan:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-alert>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <form action="{{ route('pendaftar.store') }}" method="POST" id="formPendaftar">
                @csrf

                {{-- DATA AWAL PENDAFTAR --}}
                <x-section-card title="Data Awal Pendaftar" icon="fas fa-user-plus" class="mb-4">
                    <div class="row">
                        <div class="col-md-6">
                            <x-form-group label="NISN" name="nisn" required="true">
                                <x-input 
                                    name="nisn" 
                                    value="{{ old('nisn') }}"
                                    placeholder="Nomor Induk Siswa Nasional"
                                    icon="fas fa-id-card"
                                    required
                                />
                            </x-form-group>
                        </div>
                        <div class="col-md-6">
                            <x-form-group label="Nama Lengkap" name="nama_lengkap" required="true">
                                <x-input 
                                    name="nama_lengkap" 
                                    value="{{ old('nama_lengkap') }}"
                                    placeholder="Nama lengkap siswa"
                                    icon="fas fa-user"
                                    required
                                />
                            </x-form-group>
                        </div>
                    </div>

                    <x-form-group label="Nomor HP Siswa" name="no_telepon" required="true">
                        <x-input 
                            name="no_telepon" 
                            value="{{ old('no_telepon') }}"
                            placeholder="Contoh: 08123456789"
                            icon="fas fa-phone"
                            required
                        />
                        <small class="text-muted">Nomor HP untuk notifikasi WhatsApp</small>
                    </x-form-group>

                    <x-form-group label="Asal Sekolah" name="asal_sekolah" required="true">
                        <x-input 
                            name="asal_sekolah" 
                            value="{{ old('asal_sekolah') }}"
                            placeholder="SMP/Sekolah asal"
                            icon="fas fa-school"
                            required
                        />
                    </x-form-group>

                    <x-form-group label="Alamat Ringkas" name="alamat" required="true">
                        <x-textarea 
                            name="alamat" 
                            rows="2"
                            placeholder="Alamat ringkas"
                            value="{{ old('alamat') }}"
                        ></x-textarea>
                    </x-form-group>

                    <x-form-group label="Jurusan yang Dipilih" name="jurusan_id" required="true">
                        <x-select name="jurusan_id" required>
                            <option value="">-- Pilih Jurusan --</option>
                            @foreach($jurusans as $jurusan)
                                <option value="{{ $jurusan->id }}" {{ old('jurusan_id') == $jurusan->id ? 'selected' : '' }}>
                                    {{ $jurusan->kode }} - {{ $jurusan->nama }}
                                </option>
                            @endforeach
                        </x-select>
                    </x-form-group>

                    <x-form-group label="Nama Jaringan (Opsional)" name="nama_jaringan">
                        <x-input 
                            name="nama_jaringan" 
                            value="{{ old('nama_jaringan') }}"
                            placeholder="Nama jaringan/referensi (jika ada)"
                            icon="fas fa-network-wired"
                        />
                    </x-form-group>
                </x-section-card>

                {{-- Form Actions --}}
                <div class="d-flex gap-3 justify-content-end">
                    <a href="{{ route('pendaftar.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-2"></i> Batal
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i> Simpan Data Awal
                    </button>
                </div>
            </form>
        </div>

        {{-- Sidebar Info --}}
        <div class="col-lg-4">
            <x-info-card type="info" title="Petunjuk Pengisian">
                <ul class="mb-0 ps-3">
                    <li class="mb-2">Isi data awal pendaftar terlebih dahulu</li>
                    <li class="mb-2">Nomor HP diperlukan untuk notifikasi WhatsApp</li>
                    <li class="mb-2">Setelah disimpan, Anda dapat melengkapi biodata</li>
                    <li class="mb-2">Data lengkap diperlukan untuk verifikasi daftar ulang</li>
                    <li>Semua field dengan tanda <span class="text-danger">*</span> wajib diisi</li>
                </ul>
            </x-info-card>

            <x-info-card type="success" title="Notifikasi Otomatis" class="mt-3">
                <p class="mb-2 small">
                    <i class="fas fa-whatsapp me-1"></i>
                    <strong>WhatsApp akan otomatis terkirim</strong> ke nomor HP siswa setelah data disimpan.
                </p>
                <p class="mb-0 small text-muted">
                    Notifikasi berisi nomor registrasi dan link portal SPMB.
                </p>
            </x-info-card>

            <x-info-card type="warning" title="Catatan" class="mt-3">
                <p class="mb-0 small">
                    <i class="fas fa-info-circle me-1"></i>
                    Biodata lengkap seperti NIK, tempat/tanggal lahir, data orang tua, dll dapat dilengkapi setelah data awal disimpan.
                </p>
            </x-info-card>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Loading state on form submit
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('formPendaftar');
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.innerHTML;
        
        form.addEventListener('submit', function(e) {
            // Disable button and show loading
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Menyimpan...';
            
            // Re-enable after 10 seconds (fallback jika ada error)
            setTimeout(function() {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            }, 10000);
        });
    });

    // Show success modal if session exists
    @if (Session::has('success'))
        document.addEventListener('DOMContentLoaded', function() {
            Modal.alert('{{ addslashes(Session::get('success')) }}', 'Berhasil!', 'success');
        });
    @endif

    @if (Session::has('error'))
        document.addEventListener('DOMContentLoaded', function() {
            Modal.alert('{{ addslashes(Session::get('error')) }}', 'Gagal!', 'danger');
        });
    @endif
</script>
@endpush
