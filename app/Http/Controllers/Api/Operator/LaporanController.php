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

        $query = Transaksi::with(['user', 'buku'])
                    ->whereBetween('created_at', [$dari, $sampai])
                    ->whereIn('status', ['kembali', 'ditolak']);

        if ($request->status) $query->where('status', $request->status);

        return response()->json([
            'message' => 'Laporan transaksi',
            'data'    => $query->orderBy('created_at', 'desc')->get()
        ]);
    }

    public function exportTransaksiExcel(Request $request)
    {
        [$dari, $sampai] = $this->getPeriode($request);
        $status = $request->status ?? null;

        return Excel::download(new LaporanTransaksiExport($dari, $sampai, $status), 'laporan-transaksi.xlsx');
    }

    public function exportTransaksiPdf(Request $request)
    {
        [$dari, $sampai] = $this->getPeriode($request);
        $status = $request->status ?? null;

        $query = Transaksi::with(['user', 'buku'])
                    ->whereBetween('created_at', [$dari, $sampai])
                    ->whereIn('status', ['kembali', 'ditolak']);
        if ($status) $query->where('status', $status);

        $data    = $query->get();
        $summary = $this->summary($request)->getData(true);

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

        $query = Denda::with(['transaksi.user', 'transaksi.buku'])
                    ->whereBetween('created_at', [$dari, $sampai]);

        if ($request->status_pembayaran) $query->where('status_pembayaran', $request->status_pembayaran);

        return response()->json([
            'message' => 'Laporan denda',
            'data'    => $query->get()
        ]);
    }

    public function exportDendaExcel(Request $request)
    {
        [$dari, $sampai] = $this->getPeriode($request);
        $status = $request->status_pembayaran ?? null;

        return Excel::download(new LaporanDendaExport($dari, $sampai, $status), 'laporan-denda.xlsx');
    }

    public function exportDendaPdf(Request $request)
    {
        [$dari, $sampai] = $this->getPeriode($request);
        $status = $request->status_pembayaran ?? null;

        $query = Denda::with(['transaksi.user', 'transaksi.buku'])->whereBetween('created_at', [$dari, $sampai]);
        if ($status) $query->where('status_pembayaran', $status);

        $data = $query->get();
        $periode = [
            'bulan'  => $dari->translatedFormat('F'),
            'tahun'  => $dari->year,
            'dari'   => $dari->toDateString(),
            'sampai' => $sampai->toDateString(),
        ];

        $pdf = Pdf::loadView('pdf.laporan-denda', [
            'data'    => $data,
            'periode' => $periode
        ]);

        return $pdf->download('laporan-denda.pdf');
    }

    public function summary(Request $request)
    {
        [$dari, $sampai] = $this->getPeriode($request);
        $statusTransaksi = $request->status ?? null;
        $statusDenda     = $request->status_pembayaran ?? null;

        // Transaksi
        $baseTransaksi = Transaksi::whereBetween('created_at', [$dari, $sampai])
                            ->whereIn('status', ['kembali', 'ditolak']);

        if ($statusTransaksi) {
            $baseTransaksi->where('status', $statusTransaksi);
        }

        $kembali = (clone $baseTransaksi)->where('status', 'kembali')->count();
        $ditolak = (clone $baseTransaksi)->where('status', 'ditolak')->count();

        $totalTransaksi = $kembali + $ditolak;

        // Denda
        $baseDenda = Denda::whereBetween('created_at', [$dari, $sampai]);

        if ($statusDenda) {
            $baseDenda->where('status_pembayaran', $statusDenda);
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
                'total'   => $totalTransaksi,
                'kembali' => $kembali,
                'ditolak' => $ditolak,
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
        $statusTransaksi = $request->status ?? null;
        $statusDenda     = $request->status_pembayaran ?? null;

        return Excel::download(new LaporanSummaryExport($dari, $sampai, $statusTransaksi, $statusDenda), 'laporan-summary.xlsx');
    }

    public function exportSummaryPdf(Request $request)
    {
        [$dari, $sampai] = $this->getPeriode($request);
        $statusTransaksi = $request->status ?? null;
        $statusDenda     = $request->status_pembayaran ?? null;

        $transaksiQuery = Transaksi::whereBetween('created_at', [$dari, $sampai])
                            ->whereIn('status', ['kembali', 'ditolak']);
        if ($statusTransaksi) $transaksiQuery->where('status', $statusTransaksi);
        $transaksiData = $transaksiQuery->get();

        $dendaQuery = Denda::whereBetween('created_at', [$dari, $sampai]);
        if ($statusDenda) $dendaQuery->where('status_pembayaran', $statusDenda);
        $dendaData = $dendaQuery->get();

        $kembali = $transaksiData->where('status', 'kembali')->count();
        $ditolak = $transaksiData->where('status', 'ditolak')->count();

        $summaryTransaksi = [
            'total'   => $kembali + $ditolak,
            'kembali' => $kembali,
            'ditolak' => $ditolak,
        ];

        $summaryDenda = [
            'total_nominal' => $dendaData->sum('nominal'),
            'lunas'         => $dendaData->where('status_pembayaran', 'lunas')->count(),
            'belum_lunas'   => $dendaData->where('status_pembayaran', 'belum_lunas')->count(),
        ];

        $periode = [
            'bulan'  => $dari->translatedFormat('F'),
            'tahun'  => $dari->year,
            'dari'   => $dari->toDateString(),
            'sampai' => $sampai->toDateString(),
        ];

        $pdf = Pdf::loadView('pdf.laporan-summary', [
            'transaksi'      => $summaryTransaksi,
            'denda'          => $summaryDenda,
            'periode'        => $periode,
            'list_transaksi' => $transaksiData,
            'list_denda'     => $dendaData,
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