<?php

namespace App\Http\Controllers\Api\Operator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LaporanTransaksiExport;
use App\Exports\LaporanDendaExport;
use App\Exports\LaporanSummaryExport;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanController extends Controller
{
    // Transaksi
    public function transaksi(Request $request)
    {
        [$dari, $sampai] = $this->getPeriode($request);

        $query = Transaksi::with(['user', 'details.buku'])
            ->whereBetween('created_at', [$dari, $sampai])
            ->whereIn('status', ['dipinjam', 'kembali']);

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $data = $query->orderBy('created_at', 'desc')->get()->map(function ($trx) {
            return [
                'id'           => $trx->id,
                'nama_user'    => optional($trx->user)->name ?? 'User Dihapus',
                'status'       => $trx->status,
                'tgl_pinjam'   => $trx->tgl_pinjam,
                'tgl_deadline' => $trx->tgl_deadline,
                'buku'         => $trx->details->map(function ($dt) {
                    return [
                        'id'          => optional($dt->buku)->id,
                        'judul'       => optional($dt->buku)->judul ?? 'Buku Dihapus',
                        'tgl_kembali' => $dt->tgl_kembali,
                        'status'      => $dt->status,
                    ];
                }),
                'tgl_kembali' => $trx->details->whereNotNull('tgl_kembali')->first()?->tgl_kembali,
                'total_buku'  => $trx->details->count(),
            ];
        });

        return response()->json(['message' => 'Laporan transaksi', 'data' => $data]);
    }

    public function exportTransaksiExcel(Request $request)
    {
        [$dari, $sampai] = $this->getPeriode($request);
        $status = $request->status ?? null;

        return Excel::download(
            new LaporanTransaksiExport($dari, $sampai, $status),
            'laporan-transaksi.xlsx'
        );
    }

    public function exportTransaksiPdf(Request $request)
    {
        [$dari, $sampai] = $this->getPeriode($request);
        $status = $request->status ?? null;
        $summary = $this->summary($request)->getData(true);

        $query = Transaksi::with(['user', 'details.buku'])
            ->whereBetween('created_at', [$dari, $sampai])
            ->whereIn('status', ['dipinjam', 'kembali']);

        if ($status) $query->where('status', $status);

        $pdf = Pdf::loadView('pdf.laporan-transaksi', [
            'data'    => $query->get(),
            'summary' => $summary,
            'periode' => $summary['periode'],
        ]);

        return $pdf->download('laporan-transaksi.pdf');
    }

    // Denda
    public function denda(Request $request)
    {
        [$dari, $sampai] = $this->getPeriode($request);

        $query = DetailTransaksi::with([
            'buku',
            'transaksi.user.siswa',
            'transaksi.user.staff',
        ])
        ->where('total_denda_item', '>', 0)
        ->whereHas('transaksi', function ($q) use ($dari, $sampai) {
            $q->whereBetween('created_at', [$dari, $sampai]);
        });

        if ($request->status_pembayaran) {
            $query->whereHas('transaksi', function ($q) use ($request) {
                $q->where('status_denda', $request->status_pembayaran);
            });
        }

        $data = $query->orderBy('created_at', 'desc')->get()->map(function ($dt) {
            return [
                'id'               => $dt->id,
                'nama_user'        => optional($dt->transaksi?->user)->name ?? 'User Dihapus',
                'judul_buku'       => optional($dt->buku)->judul ?? 'Buku Dihapus',
                'status_buku'      => $dt->status,
                'denda_telat'      => $dt->denda_telat,
                'denda_kerusakan'  => $dt->denda_kerusakan,
                'denda_hilang'     => $dt->denda_hilang,
                'total_denda_item' => $dt->total_denda_item,
                'tgl_kembali'      => $dt->tgl_kembali,
                'status_denda'     => $dt->transaksi?->status_denda,
                'tgl_lunas'        => $dt->transaksi?->tgl_lunas,
            ];
        });

        return response()->json(['message' => 'Laporan denda', 'data' => $data]);
    }

    public function exportDendaExcel(Request $request)
    {
        [$dari, $sampai] = $this->getPeriode($request);
        $status = $request->status_pembayaran ?? null;

        return Excel::download(
            new LaporanDendaExport($dari, $sampai, $status),
            'laporan-denda.xlsx'
        );
    }

    public function exportDendaPdf(Request $request)
    {
        [$dari, $sampai] = $this->getPeriode($request);
        $status = $request->status_pembayaran ?? null;

        $query = DetailTransaksi::with(['buku', 'transaksi.user'])
            ->where('total_denda_item', '>', 0)
            ->whereHas('transaksi', function ($q) use ($dari, $sampai) {
                $q->whereBetween('created_at', [$dari, $sampai]);
            });

        if ($status) {
            $query->whereHas('transaksi', function ($q) use ($status) {
                $q->where('status_denda', $status);
            });
        }

        $pdf = Pdf::loadView('pdf.laporan-denda', [
            'data'    => $query->get(),
            'periode' => [
                'bulan'  => $dari->translatedFormat('F'),
                'tahun'  => $dari->year,
                'dari'   => $dari->toDateString(),
                'sampai' => $sampai->toDateString(),
            ],
        ]);

        return $pdf->download('laporan-denda.pdf');
    }

    public function summary(Request $request)
{
    [$dari, $sampai] = $this->getPeriode($request);

    $baseTransaksi = Transaksi::whereBetween('created_at', [$dari, $sampai])
        ->whereIn('status', ['dipinjam', 'kembali']);

    if ($request->status) {
        $baseTransaksi->where('status', $request->status);
    }

    $dipinjam = (clone $baseTransaksi)->where('status', 'dipinjam')->count();
    $kembali  = (clone $baseTransaksi)->where('status', 'kembali')->count();

    $baseDenda = Transaksi::where('total_denda', '>', 0)
        ->whereBetween('created_at', [$dari, $sampai]);

    $totalNominal      = (clone $baseDenda)->sum('total_denda');
    $lunas             = (clone $baseDenda)->where('status_denda', 'lunas')->count();
    $belumLunas        = (clone $baseDenda)->where('status_denda', 'belum_bayar')->count();
    $nominalLunas      = (clone $baseDenda)->where('status_denda', 'lunas')->sum('total_denda');
    $nominalBelumLunas = (clone $baseDenda)->where('status_denda', 'belum_bayar')->sum('total_denda');

    return response()->json([
        'periode' => [
            'bulan'  => $dari->translatedFormat('F'),
            'tahun'  => $dari->year,
            'dari'   => $dari->toDateString(),
            'sampai' => $sampai->toDateString(),
        ],
        'transaksi' => [
            'total'    => $dipinjam + $kembali,
            'dipinjam' => $dipinjam,
            'kembali'  => $kembali,
        ],
        'denda' => [
            'total_nominal'       => $totalNominal,
            'lunas'               => $lunas,
            'belum_lunas'         => $belumLunas,
            'nominal_lunas'       => $nominalLunas,
            'nominal_belum_lunas' => $nominalBelumLunas,
        ],
    ]);
}

public function exportSummaryExcel(Request $request)
{
    [$dari, $sampai] = $this->getPeriode($request);

    return Excel::download(
        new LaporanSummaryExport($dari, $sampai),
        'laporan-summary.xlsx'
    );
}

public function exportSummaryPdf(Request $request)
{
    [$dari, $sampai] = $this->getPeriode($request);
    $summary = $this->summary($request)->getData(true);

    $pdf = Pdf::loadView('pdf.laporan-summary', [
        'periode'   => $summary['periode'],
        'transaksi' => $summary['transaksi'],
        'denda'     => $summary['denda'],
    ]);

    return $pdf->download('laporan-summary.pdf');
}

    // Helper
    private function getPeriode(Request $request)
    {
        if ($request->bulan && $request->tahun) {
            $dari   = Carbon::createFromDate($request->tahun, $request->bulan, 1)->startOfMonth();
            $sampai = (clone $dari)->endOfMonth();
        } else {
            $dari   = now()->startOfMonth();
            $sampai = now()->endOfMonth();
        }

        return [$dari, $sampai];
    }
}