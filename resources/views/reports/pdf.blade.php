@php
    $sys = \App\Models\SettingSystem::instance()->toSettingsArray();

    $schoolName    = $sys['school_name']       ?: 'SMK NEGERI JAKARTA';
    $schoolAddress = $sys['school_address']    ?: 'Jl. Pendidikan No. 123';
    $schoolCity    = $sys['school_city']       ?: 'Jakarta';
    $schoolPhone   = $sys['school_phone']      ?: ($sys['school_contact'] ?: '(021) 1234-5678');
    $schoolEmail   = $sys['school_email']      ?: 'info@smkn.sch.id';
    $printFooter   = $sys['print_footer_text'] ?: 'Dokumen ini dicetak otomatis oleh sistem SPMB (Sistem Penerimaan Murid Baru).';

    $logoUrl = !empty($sys['school_logo']) ? asset('storage/' . $sys['school_logo']) : null;
    $docHeaderText = $sys['document_header_text'] ?: '';
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan SPMB (Sistem Penerimaan Murid Baru) - {{ now()->format('d-m-Y') }}</title>
    @include('partials.favicon')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', 'Arial', sans-serif; background: #f1f5f9; padding: 20px; color: #1e293b; font-size: 12px; }

        @media print {
            body { padding: 0; background: white; font-size: 11px; }
            .no-print { display: none !important; }
            .page { box-shadow: none; }
            .page-break { page-break-before: always; }
        }

        .no-print { text-align: center; margin-bottom: 24px; display: flex; gap: 10px; justify-content: center; }
        .btn { font-family: 'Inter', sans-serif; border: none; padding: 12px 28px; border-radius: 12px; cursor: pointer; font-size: 13px; font-weight: 700; transition: all 0.3s; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; }
        .btn-print { background: linear-gradient(135deg, #6366f1, #a855f7); color: white; }
        .btn-back { background: white; color: #6366f1; border: 2px solid #6366f1 !important; }

        .page { width: 100%; max-width: 260mm; background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 28px 32px; margin: 0 auto 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }

        /* Header */
        .doc-header { display: flex; align-items: center; gap: 16px; border-bottom: 3px double #1e293b; padding-bottom: 14px; margin-bottom: 20px; }
        .doc-header .school-name { font-size: 16px; font-weight: 800; }
        .doc-header .school-sub { font-size: 10px; color: #64748b; margin-top: 2px; }
        .doc-header .doc-meta { margin-left: auto; text-align: right; font-size: 10px; color: #94a3b8; }

        .report-title { text-align: center; font-size: 14px; font-weight: 800; text-transform: uppercase; letter-spacing: 1.5px; padding: 10px; border: 2px solid #6366f1; border-radius: 8px; background: #eef2ff; color: #3730a3; margin-bottom: 20px; }

        /* Summary boxes */
        .summary-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 20px; }
        .sum-box { border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; text-align: center; }
        .sum-value { font-size: 24px; font-weight: 800; }
        .sum-label { font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; margin-top: 2px; }

        /* Section */
        .section { margin-bottom: 20px; }
        .section-head { font-size: 10px; font-weight: 700; background: #f1f5f9; padding: 7px 12px; border-left: 3px solid #6366f1; border-radius: 0 6px 6px 0; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.5px; color: #475569; }

        /* Tables */
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th { background: #1e1b4b; color: white; padding: 9px 12px; text-align: left; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        .data-table td { padding: 8px 12px; border-bottom: 1px solid #f1f5f9; font-size: 11px; }
        .data-table tr:nth-child(even) td { background: #fafbff; }
        .data-table tfoot td { background: #f1f5f9; font-weight: 700; border-top: 2px solid #e2e8f0; }

        .badge { display: inline-block; padding: 2px 8px; border-radius: 50px; font-size: 9px; font-weight: 700; }
        .b-green { background: #d1fae5; color: #065f46; }
        .b-red { background: #fee2e2; color: #991b1b; }
        .b-yellow { background: #fef3c7; color: #92400e; }

        .doc-footer { text-align: center; margin-top: 16px; font-size: 9px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 10px; line-height: 1.7; }
    </style>
</head>
<body>
    <div class="no-print">
        <button class="btn btn-print" onclick="window.print()">Cetak / Simpan PDF</button>
        <a href="{{ route('report.index', request()->query()) }}" class="btn btn-back">Kembali</a>
    </div>

    <!-- Page 1: Summary + Jurusan + Gelombang + Jaringan -->
    <div class="page">
        <div class="doc-header">
            <div>
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" alt="Logo {{ $schoolName }}" style="width:48px;height:48px;object-fit:contain;">
                @else
                    🎓
                @endif
            </div>
            <div>
                @if($docHeaderText)
                    <div style="font-size:10px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;">{{ $docHeaderText }}</div>
                @endif
                <div class="school-name">{{ $schoolName }}</div>
                <div class="school-sub">{{ $schoolAddress }}, {{ $schoolCity }} | Telp: {{ $schoolPhone }} | Email: {{ $schoolEmail }}</div>
            </div>
            <div class="doc-meta">
                @if ($gelombang !== 'all') Gelombang: {{ $gelombang }}<br> @endif
                @if ($jurusan !== 'all') Jurusan: {{ $jurusan }}<br> @endif
                Dicetak: {{ now()->format('d-m-Y H:i') }}
            </div>
        </div>

        <div class="report-title">Laporan Rekap Pendaftaran — SPMB (Sistem Penerimaan Murid Baru) {{ now()->year }}</div>

        <!-- Summary -->
        <div class="summary-grid" style="grid-template-columns: repeat(3, 1fr);">
            <div class="sum-box" style="border-color:#6366f1;">
                <div class="sum-value" style="color:#6366f1;">{{ $pendaftars->count() }}</div>
                <div class="sum-label">Total Pendaftar</div>
            </div>
            <div class="sum-box" style="border-color:#10b981;">
                <div class="sum-value" style="color:#10b981;">{{ $totalLunas }}</div>
                <div class="sum-label">Sudah Daftar Ulang</div>
            </div>
            <div class="sum-box" style="border-color:#ef4444;">
                <div class="sum-value" style="color:#ef4444;">{{ $pendaftars->count() - $totalLunas }}</div>
                <div class="sum-label">Belum Daftar Ulang</div>
            </div>
        </div>

        <!-- Per Jurusan -->
        <div class="section">
            <div class="section-head">Rekap per Jurusan</div>
            <table class="data-table">
                <thead><tr><th>Jurusan</th><th class="text-center">Total</th><th class="text-center">Sudah Daftar Ulang</th><th class="text-center">Belum Daftar Ulang</th><th class="text-center">% Daftar Ulang</th></tr></thead>
                <tbody>
                    @foreach (collect($jurusanAktif ?? [])->pluck('kode') as $j)
                        @php
                            $d = $perJurusan[$j] ?? ['total'=>0,'lunas'=>0];
                            $pct = $d['total'] > 0 ? round($d['lunas']/$d['total']*100) : 0;
                        @endphp
                        <tr>
                            <td><strong>{{ $j }}</strong></td>
                            <td style="text-align:center;">{{ $d['total'] }}</td>
                            <td style="text-align:center;color:#059669;font-weight:700;">{{ $d['lunas'] }}</td>
                            <td style="text-align:center;color:#dc2626;">{{ $d['total'] - $d['lunas'] }}</td>
                            <td style="text-align:center;">{{ $pct }}%</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot><tr>
                    <td>TOTAL</td>
                    <td style="text-align:center;">{{ $pendaftars->count() }}</td>
                    <td style="text-align:center;color:#059669;">{{ $totalLunas }}</td>
                    <td style="text-align:center;color:#dc2626;">{{ $pendaftars->count() - $totalLunas }}</td>
                    <td style="text-align:center;">{{ $pendaftars->count() > 0 ? round($totalLunas/$pendaftars->count()*100) : 0 }}%</td>
                </tr></tfoot>
            </table>
        </div>

        <!-- Per Gelombang -->
        <div class="section">
            <div class="section-head">Rekap per Gelombang</div>
            <table class="data-table">
                <thead><tr><th>Gelombang</th><th class="text-center">Total</th><th class="text-center">% dari Keseluruhan</th></tr></thead>
                <tbody>
                    @foreach ($perGelombang as $gel => $count)
                        <tr>
                            <td>Gelombang {{ $gel }}</td>
                            <td style="text-align:center;font-weight:700;">{{ $count }}</td>
                            <td style="text-align:center;">{{ $pendaftars->count() > 0 ? round($count/$pendaftars->count()*100) : 0 }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Per Jaringan -->
        <div class="section">
            <div class="section-head">Rekap per Jaringan / Vendor</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Jaringan</th>
                        <th class="text-center">Total</th>
                        @foreach(collect($jurusanAktif ?? []) as $mj)
                            <th class="text-center">{{ $mj->kode }}</th>
                        @endforeach
                        <th class="text-center">Sudah Daftar Ulang</th>
                        <th class="text-center">%</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($perJaringan as $i => $j)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td><strong>{{ $j['nama'] }}</strong></td>
                            <td style="text-align:center;font-weight:700;">{{ $j['total'] }}</td>
                            @foreach(collect($jurusanAktif ?? []) as $mj)
                                <td style="text-align:center;">{{ $j['jurusan'][$mj->kode] ?? 0 }}</td>
                            @endforeach
                            <td style="text-align:center;color:#059669;font-weight:700;">{{ $j['lunas'] }}</td>
                            <td style="text-align:center;">{{ $j['total'] > 0 ? round($j['lunas']/$j['total']*100) : 0 }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="doc-footer">
            {{ $schoolName }} | Laporan SPMB (Sistem Penerimaan Murid Baru) {{ now()->year }} | Dicetak: {{ now()->format('d-m-Y H:i') }} WIB<br>
            {{ $printFooter }}
        </div>
    </div>

    <!-- Page 2: Full Data Table -->
    <div class="page page-break">
        <div class="doc-header">
            <div>
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" alt="Logo {{ $schoolName }}" style="width:48px;height:48px;object-fit:contain;">
                @else
                    🎓
                @endif
            </div>
            <div>
                @if($docHeaderText)
                    <div style="font-size:10px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;">{{ $docHeaderText }}</div>
                @endif
                <div class="school-name">{{ $schoolName }}</div>
                <div class="school-sub">{{ $schoolAddress }}, {{ $schoolCity }}</div>
            </div>
            <div class="doc-meta">Halaman 2 — Data Lengkap<br>{{ now()->format('d-m-Y H:i') }}</div>
        </div>

        <div class="report-title">Data Lengkap Seluruh Pendaftar Diterima</div>

        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:20px;">No</th>
                    <th>No. Registrasi</th>
                    <th>Nama Lengkap</th>
                    <th>NISN</th>
                    <th>Jurusan</th>
                    <th>Asal Sekolah</th>
                    <th>Gel.</th>
                    <th>Daftar Ulang</th>
                    <th>Kaos</th>
                    <th>Jaringan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pendaftarDiterima as $p)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td style="font-family:'Courier New',monospace;font-size:10px;">{{ $p->no_registrasi }}</td>
                        <td><strong>{{ $p->nama_lengkap }}</strong></td>
                        <td>{{ $p->nisn }}</td>
                        <td><span class="badge" style="background:#eef2ff;color:#3730a3;">{{ $p->jurusan }}</span></td>
                        <td style="font-size:10px;">{{ $p->asal_sekolah }}</td>
                        <td style="text-align:center;">{{ $p->gelombang }}</td>
                        <td>
                            <span class="badge {{ optional($p->logistik)->status_bayar === 'Lunas' ? 'b-green' : 'b-red' }}">
                                {{ optional($p->logistik)->status_bayar === 'Lunas' ? 'Sudah Daftar Ulang' : 'Belum Daftar Ulang' }}
                            </span>
                        </td>
                        <td>
                            @php $sk = optional($p->logistik)->status_kaos; @endphp
                            <span class="badge {{ $sk === 'Sudah' ? 'b-green' : ($sk === 'Proses' ? 'b-yellow' : 'b-red') }}">
                                {{ $sk ?? '-' }}
                            </span>
                        </td>
                        <td style="font-size:10px;">{{ $p->nama_jaringan ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="10" style="text-align:center;padding:20px;color:#94a3b8;">Belum ada data</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="doc-footer">
            Total: {{ $pendaftarDiterima->count() }} pendaftar diterima | {{ $schoolName }} | SPMB (Sistem Penerimaan Murid Baru) {{ now()->year }}<br>
            {{ $printFooter }}
        </div>
    </div>

    <!-- Page 3: All Pendaftar Data Table -->
    <div class="page page-break">
        <div class="doc-header">
            <div>
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" alt="Logo {{ $schoolName }}" style="width:48px;height:48px;object-fit:contain;">
                @else
                    🎓
                @endif
            </div>
            <div>
                @if($docHeaderText)
                    <div style="font-size:10px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;">{{ $docHeaderText }}</div>
                @endif
                <div class="school-name">{{ $schoolName }}</div>
                <div class="school-sub">{{ $schoolAddress }}, {{ $schoolCity }}</div>
            </div>
            <div class="doc-meta">Halaman 3 — Data Seluruh Pendaftar<br>{{ now()->format('d-m-Y H:i') }}</div>
        </div>

        <div class="report-title">Data Lengkap Seluruh Pendaftar</div>

        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:20px;">No</th>
                    <th>No. Registrasi</th>
                    <th>Nama Lengkap</th>
                    <th>NISN</th>
                    <th>Jurusan</th>
                    <th>Asal Sekolah</th>
                    <th>Gel.</th>
                    <th>Daftar Ulang</th>
                    <th>Kaos</th>
                    <th>Jaringan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pendaftars as $p)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td style="font-family:'Courier New',monospace;font-size:10px;">{{ $p->no_registrasi }}</td>
                        <td><strong>{{ $p->nama_lengkap }}</strong></td>
                        <td>{{ $p->nisn }}</td>
                        <td><span class="badge" style="background:#eef2ff;color:#3730a3;">{{ $p->jurusan }}</span></td>
                        <td style="font-size:10px;">{{ $p->asal_sekolah }}</td>
                        <td style="text-align:center;">{{ $p->gelombang }}</td>
                        <td>
                            <span class="badge {{ optional($p->logistik)->status_bayar === 'Lunas' ? 'b-green' : 'b-red' }}">
                                {{ optional($p->logistik)->status_bayar === 'Lunas' ? 'Sudah Daftar Ulang' : 'Belum Daftar Ulang' }}
                            </span>
                        </td>
                        <td>
                            @php $sk = optional($p->logistik)->status_kaos; @endphp
                            <span class="badge {{ $sk === 'Sudah' ? 'b-green' : ($sk === 'Proses' ? 'b-yellow' : 'b-red') }}">
                                {{ $sk ?? '-' }}
                            </span>
                        </td>
                        <td style="font-size:10px;">{{ $p->nama_jaringan ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="10" style="text-align:center;padding:20px;color:#94a3b8;">Belum ada data</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="doc-footer">
            Total: {{ $pendaftars->count() }} pendaftar | {{ $schoolName }} | SPMB (Sistem Penerimaan Murid Baru) {{ now()->year }}<br>
            {{ $printFooter }}
        </div>
    </div>
</body>
</html>


