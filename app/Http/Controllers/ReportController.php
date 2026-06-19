<?php

namespace App\Http\Controllers;

use App\Models\Jurusan;
use App\Models\Pendaftar;
use App\Models\LogistikBayar;
use App\Exports\LaporanExport;
use App\Exports\JaringanExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        // Get active tahun ajaran
        $activeTahun = \App\Models\SettingSystem::get('active_tahun_ajaran', '2026/2027');
        
        $gelombang  = $request->get('gelombang', 'all');
        $jurusanId  = $request->get('jurusan_id', 'all');

        // FILTER BY ACTIVE YEAR
        $query = Pendaftar::with('logistik')
            ->where('tahun_ajaran', $activeTahun);
        if ($gelombang !== 'all') $query->where('gelombang', $gelombang);
        if ($jurusanId !== 'all') $query->where('jurusan_id', $jurusanId);
        $pendaftars = $query->get();

        $totalPendaftar  = $pendaftars->count();
        $totalLunas      = $pendaftars->filter(fn($p) => optional($p->logistik)->status_bayar === 'Lunas')->count();
        $totalBelumBayar = $totalPendaftar - $totalLunas;
        $totalSelesai    = $pendaftars->filter(fn($p) => optional($p->logistik)->status_kaos === 'Sudah')->count();

        $jurusanAktif = Jurusan::where('aktif', true)->orderBy('kode')->get();

        $perJurusan = [];
        foreach ($jurusanAktif as $j) {
            $group = $pendaftars->where('jurusan', $j->kode);
            $perJurusan[$j->kode] = [
                'total'  => $group->count(),
                'lunas'  => $group->filter(fn($p) => optional($p->logistik)->status_bayar === 'Lunas')->count(),
                'selesai'=> $group->filter(fn($p) => optional($p->logistik)->status_kaos === 'Sudah')->count(),
            ];
        }

        $perGelombang = $pendaftars->groupBy('gelombang')->map(fn($g) => [
            'total' => $g->count(),
            'lunas' => $g->filter(fn($p) => optional($p->logistik)->status_bayar === 'Lunas')->count(),
        ])->sortKeys();

        $perJaringan = $pendaftars
            ->groupBy(fn($p) => strtoupper(trim($p->nama_jaringan ?: 'PANITIA')))
            ->map(function ($group, $nama) use ($jurusanAktif) {
                $jurusanCounts = [];
                foreach ($jurusanAktif as $j) {
                    $jurusanCounts[$j->kode] = $group->where('jurusan', $j->kode)->count();
                }
                return [
                    'nama'    => $nama,
                    'total'   => $group->count(),
                    'lunas'   => $group->filter(fn($p) => optional($p->logistik)->status_bayar === 'Lunas')->count(),
                    'jurusan' => $jurusanCounts,
                ];
            })
            ->sortByDesc('total')
            ->values();

        $perUkuranKaos = $pendaftars
            ->filter(fn($p) => optional($p->logistik)->ukuran_kaos)
            ->groupBy(fn($p) => $p->logistik->ukuran_kaos)
            ->map->count()
            ->sortKeys();

        // Get gelombang options - FILTERED BY ACTIVE YEAR
        $gelombangOptions = Pendaftar::where('tahun_ajaran', $activeTahun)
            ->select('gelombang')
            ->distinct()
            ->orderBy('gelombang')
            ->pluck('gelombang');

        return view('reports.index', compact(
            'pendaftars', 'totalPendaftar', 'totalLunas', 'totalBelumBayar', 'totalSelesai',
            'perJurusan', 'perGelombang', 'perJaringan', 'perUkuranKaos',
            'gelombangOptions', 'gelombang', 'jurusanId', 'jurusanAktif', 'activeTahun'
        ));
    }

    public function stats(Request $request)
    {
        try {
            // Get active tahun ajaran
            $activeTahun = \App\Models\SettingSystem::get('active_tahun_ajaran', '2026/2027');
            
            $gelombang = $request->get('gelombang', 'all');
            $jurusanId = $request->get('jurusan_id', 'all');

            // FILTER BY ACTIVE YEAR ONLY
            $query = Pendaftar::with('logistik')
                ->where('tahun_ajaran', $activeTahun);
                
            if ($gelombang !== 'all') $query->where('gelombang', $gelombang);
            if ($jurusanId !== 'all') $query->where('jurusan_id', $jurusanId);
            $pendaftars = $query->get();

            $totalPendaftar   = $pendaftars->count();
            $totalLunas       = $pendaftars->filter(fn($p) => optional($p->logistik)->status_bayar === 'Lunas')->count();
            $totalBelumBayar  = $totalPendaftar - $totalLunas;
            $tz = config('app.timezone', 'UTC');
            $today = \Carbon\Carbon::now($tz)->startOfDay();
            $tomorrow = \Carbon\Carbon::now($tz)->addDay()->startOfDay();
            
            // More reliable way to check if registered today
            $totalBaruHariIni = $pendaftars->filter(function($p) use ($today, $tomorrow) {
                if ($p->tgl_daftar && $p->tgl_daftar->gte($today) && $p->tgl_daftar->lt($tomorrow)) {
                    return true;
                }
                if ($p->created_at && $p->created_at->gte($today) && $p->created_at->lt($tomorrow)) {
                    return true;
                }
                return false;
            })->count();
            
            \Log::debug('Dashboard stats query', [
                'total_pendaftar' => $totalPendaftar,
                'total_baru_hari_ini' => $totalBaruHariIni,
                'today_start' => $today->toDateTimeString(),
                'tomorrow_start' => $tomorrow->toDateTimeString(),
                'sample_data' => $pendaftars->take(3)->map(fn($p) => [
                    'id' => $p->id_pendaftar,
                    'tgl_daftar' => $p->tgl_daftar?->toDateTimeString(),
                    'created_at' => $p->created_at?->toDateTimeString(),
                ])->toArray(),
            ]);
            
            $pctLunas         = $totalPendaftar > 0 ? round($totalLunas / $totalPendaftar * 100) : 0;

            
            $perJurusanStats = $pendaftars->groupBy('jurusan')->map(function ($items, $jurusan) use ($today, $tomorrow) {
                $lunas = $items->filter(fn($p) => optional($p->logistik)->status_bayar === 'Lunas')->count();
                $baruHariIni = $items->filter(function($p) use ($today, $tomorrow) {
                    if ($p->tgl_daftar && $p->tgl_daftar->gte($today) && $p->tgl_daftar->lt($tomorrow)) {
                        return true;
                    }
                    if ($p->created_at && $p->created_at->gte($today) && $p->created_at->lt($tomorrow)) {
                        return true;
                    }
                    return false;
                })->count();
                return [
                    'jurusan'          => $jurusan,
                    'totalPendaftar'   => $items->count(),
                    'totalBaruHariIni' => $baruHariIni,
                    'totalBelumBayar'  => $items->count() - $lunas,
                    'totalLunas'       => $lunas,
                ];
            })->sortKeys()->values();

            return response()->json([
                'totalPendaftar'   => $totalPendaftar,
                'totalLunas'       => $totalLunas,
                'totalBelumBayar'  => $totalBelumBayar,
                'totalBaruHariIni' => $totalBaruHariIni,
                'pctLunas'         => $pctLunas,
                'perJurusanStats'  => $perJurusanStats,
                'updatedAt'        => now()->format('H:i:s'),
            ], 200, [
                'Content-Type' => 'application/json; charset=utf-8',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
            ]);
        } catch (\Exception $e) {
            \Log::error('Stats endpoint error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'error' => 'Gagal mengambil data statistik',
                'message' => $e->getMessage(),
                'debug_class' => class_basename($e),
                'totalPendaftar'   => 0,
                'totalLunas'       => 0,
                'totalBelumBayar'  => 0,
                'totalBaruHariIni' => 0,
                'pctLunas'         => 0,
                'perJurusanStats'  => [],
                'updatedAt'        => now()->format('H:i:s'),
            ], 200, [
                'Content-Type' => 'application/json; charset=utf-8',
            ]);
        }
    }

    public function exportExcel(Request $request)
    {
        // Get active tahun ajaran
        $activeTahun = \App\Models\SettingSystem::get('active_tahun_ajaran', '2026/2027');
        
        $gelombang = $request->get('gelombang', 'all');
        $jurusanId = $request->get('jurusan_id', 'all');

        // FILTER BY ACTIVE YEAR
        $query = Pendaftar::with('logistik')
            ->where('tahun_ajaran', $activeTahun);
        if ($gelombang !== 'all') $query->where('gelombang', $gelombang);
        if ($jurusanId !== 'all') $query->where('jurusan_id', $jurusanId);
        $pendaftars = $query->orderBy('no_registrasi')->get();

        // Get pendaftar diterima
        $pendaftarDiterima = $pendaftars->where('status_siswa', 'Diterima');

        $filename = 'Laporan-Pendaftar-SPMB-' . now()->format('Ymd-His') . '.xlsx';
        return Excel::download(new LaporanExport($pendaftars, $pendaftarDiterima), $filename);
    }

    public function exportJaringanExcel(Request $request)
    {
        // Get active tahun ajaran
        $activeTahun = \App\Models\SettingSystem::get('active_tahun_ajaran', '2026/2027');
        
        // FILTER BY ACTIVE YEAR
        $pendaftars   = Pendaftar::with('logistik')
            ->where('tahun_ajaran', $activeTahun)
            ->get();
        $jurusanAktif = Jurusan::where('aktif', true)->orderBy('kode')->pluck('kode');

        $perJaringan = $pendaftars
            ->groupBy(fn($p) => strtoupper(trim($p->nama_jaringan ?: 'PANITIA')))
            ->map(function ($group, $nama) use ($jurusanAktif) {
                $jurusanCounts = [];
                foreach ($jurusanAktif as $kode) {
                    $jurusanCounts[$kode] = $group->where('jurusan', $kode)->count();
                }
                return [
                    'nama'    => $nama,
                    'total'   => $group->count(),
                    'lunas'   => $group->filter(fn($p) => optional($p->logistik)->status_bayar === 'Lunas')->count(),
                    'jurusan' => $jurusanCounts,
                ];
            })
            ->sortByDesc('total')
            ->values();

        $filename = 'Rekap-Jaringan-SPMB-' . now()->format('Ymd-His') . '.xlsx';
        return Excel::download(new JaringanExport($perJaringan, $jurusanAktif, $pendaftars->count()), $filename);
    }

    public function exportPdf(Request $request)
    {
        // Get active tahun ajaran
        $activeTahun = \App\Models\SettingSystem::get('active_tahun_ajaran', '2026/2027');
        
        $gelombang = $request->get('gelombang', 'all');
        $jurusanId = $request->get('jurusan_id', 'all');

        // FILTER BY ACTIVE YEAR
        $query = Pendaftar::with('logistik')
            ->where('tahun_ajaran', $activeTahun);
        if ($gelombang !== 'all') $query->where('gelombang', $gelombang);
        if ($jurusanId !== 'all') $query->where('jurusan_id', $jurusanId);
        $pendaftars = $query->orderBy('no_registrasi')->get();

        $jurusanAktif = Jurusan::where('aktif', true)->orderBy('kode')->get();

        $perJurusan = [];
        foreach ($jurusanAktif as $j) {
            $group = $pendaftars->where('jurusan', $j->kode);
            $perJurusan[$j->kode] = [
                'total' => $group->count(),
                'lunas' => $group->filter(fn($p) => optional($p->logistik)->status_bayar === 'Lunas')->count(),
            ];
        }

        $perGelombang = $pendaftars->groupBy('gelombang')->map->count()->sortKeys();
        $totalLunas   = $pendaftars->filter(fn($p) => optional($p->logistik)->status_bayar === 'Lunas')->count();

        $perJaringan = $pendaftars
            ->groupBy(fn($p) => strtoupper(trim($p->nama_jaringan ?: 'PANITIA')))
            ->map(function ($group, $nama) use ($jurusanAktif) {
                $jurusanCounts = [];
                foreach ($jurusanAktif as $j) {
                    $jurusanCounts[$j->kode] = $group->where('jurusan', $j->kode)->count();
                }
                return [
                    'nama'    => $nama,
                    'total'   => $group->count(),
                    'lunas'   => $group->filter(fn($p) => optional($p->logistik)->status_bayar === 'Lunas')->count(),
                    'jurusan' => $jurusanCounts,
                ];
            })
            ->sortByDesc('total')
            ->values();

        // Get pendaftar diterima for detailed table
        $pendaftarDiterima = $pendaftars->where('status_siswa', 'Diterima');

        $jurusan = $jurusanId !== 'all' ? (Jurusan::find($jurusanId)?->kode ?? 'all') : 'all';

        return view('reports.pdf', compact(
            'pendaftars', 'perJurusan', 'perGelombang', 'totalLunas',
            'perJaringan', 'gelombang', 'jurusan', 'jurusanAktif', 'activeTahun',
            'pendaftarDiterima'
        ));
    }
}

