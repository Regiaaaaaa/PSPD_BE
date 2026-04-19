<?php

namespace App\Http\Controllers\Api\Operator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\Denda;
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

        $query = Transaksi::with([
            'user',
            'details.buku'
        ])
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

                'buku' => $trx->details->map(function ($dt) {
                    return [
                        'id'          => optional($dt->buku)->id,
                        'judul'       => optional($dt->buku)->judul ?? 'Buku Dihapus',
                        'tgl_kembali' => $dt->tgl_kembali, 
                    ];
                }),
                'tgl_kembali' => $trx->details->whereNotNull('tgl_kembali')->first()?->tgl_kembali,

                'total_buku' => $trx->details->count()
            ];
        });

        return response()->json([
            'message' => 'Laporan transaksi',
            'data'    => $data
        ]);
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

        $query = Transaksi::with([
            'user',
            'details.buku'  
        ])
        ->whereBetween('created_at', [$dari, $sampai])
        ->whereIn('status', ['dipinjam', 'kembali']);

        if ($status) {
            $query->where('status', $status);
        }

        $data = $query->get();

        $pdf = Pdf::loadView('pdf.laporan-transaksi', [
            'data'    => $data,
            'summary' => $summary,
            'periode' => $summary['periode']
        ]);

        return $pdf->download('laporan-transaksi.pdf');
    }

    // Denda
    public function denda(Request $request)
    {
        [$dari, $sampai] = $this->getPeriode($request);

        $query = Denda::with([
            'transaksiDetail.transaksi.user',
            'transaksiDetail.buku'
        ])
        ->whereBetween('created_at', [$dari, $sampai]);

        if ($request->status_pembayaran) {
            $query->where('status_pembayaran', $request->status_pembayaran);
        }

        return response()->json([
            'message' => 'Laporan denda',
            'data'    => $query->get()
        ]);
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

        $query = Denda::with([
            'transaksiDetail.transaksi.user',
            'transaksiDetail.buku'
        ])
        ->whereBetween('created_at', [$dari, $sampai]);

        if ($status) {
            $query->where('status_pembayaran', $status);
        }

        $data = $query->get();

        $pdf = Pdf::loadView('pdf.laporan-denda', [
            'data'    => $data,
            'periode' => [                                    
                'bulan'  => $dari->translatedFormat('F'),
                'tahun'  => $dari->year,
                'dari'   => $dari->toDateString(),
                'sampai' => $sampai->toDateString(),
            ]
        ]);

        return $pdf->download('laporan-denda.pdf');
    }

    // Summary
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

        $baseDenda = Denda::whereBetween('created_at', [$dari, $sampai]);

        if ($request->status_pembayaran) {
            $baseDenda->where('status_pembayaran', $request->status_pembayaran);
        }

        $totalNominal = (clone $baseDenda)->sum('nominal');
        $lunas        = (clone $baseDenda)->where('status_pembayaran', 'lunas')->count();
        $belumLunas   = (clone $baseDenda)->where('status_pembayaran', 'belum_lunas')->count();

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
                'total_nominal' => $totalNominal,
                'lunas'         => $lunas,
                'belum_lunas'   => $belumLunas,
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
