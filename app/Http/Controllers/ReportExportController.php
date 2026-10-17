<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Order;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    /**
     * Ekspor Laporan Keuangan ke format PDF resmi.
     */
    public function exportPdf(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        // Validasi format tanggal
        $validated = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date'   => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $summary = $this->reportService->getFinancialSummary($startDate, $endDate);

        // Ambil rincian beban operasional dalam periode
        $expenses = Expense::with('createdBy')
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->orderBy('expense_date', 'asc')
            ->get();

        // Render view PDF menggunakan DomPDF
        $pdf = Pdf::loadView('reports.pdf', [
            'summary'   => $summary,
            'expenses'  => $expenses,
            'startDate' => $startDate,
            'endDate'   => $endDate,
            'printedAt' => Carbon::now()->translatedFormat('d F Y H:i'),
        ])->setPaper('a4', 'portrait');

        $filename = "Laporan-Keuangan-Sultan-Water_{$startDate}_sd_{$endDate}.pdf";

        return $pdf->download($filename);
    }

    /**
     * Ekspor Rekapitulasi Laporan ke format Spreadsheet (CSV/Excel).
     * Menggunakan UTF-8 BOM agar kompatibel langsung dengan Microsoft Excel.
     */
    public function exportExcel(Request $request): StreamedResponse
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        $summary = $this->reportService->getFinancialSummary($startDate, $endDate);

        $startDateTime = Carbon::parse($startDate)->startOfDay();
        $endDateTime = Carbon::parse($endDate)->endOfDay();

        // Ambil daftar transaksi penjualan lunas non-batal
        $orders = Order::with('customer')
            ->where('payment_status', 'lunas')
            ->where('delivery_status', '!=', Order::STATUS_BATAL)
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->oldest()
            ->get();

        // Ambil daftar pengeluaran operasional
        $expenses = Expense::whereBetween('expense_date', [$startDate, $endDate])
            ->orderBy('expense_date', 'asc')
            ->get();

        $filename = "Laporan-Keuangan-Sultan-Water_{$startDate}_sd_{$endDate}.csv";

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return response()->stream(function () use ($summary, $orders, $expenses, $startDate, $endDate) {
            $handle = fopen('php://output', 'w');

            // Tambahkan UTF-8 BOM agar Excel di Windows membaca karakter tanpa korup
            fputs($handle, "\xEF\xBB\xBF");

            // 1. HEADER DOKUMEN
            fputcsv($handle, ['DEPOT AIR MINUM ISI ULANG SULTAN WATER']);
            fputcsv($handle, ['LAPORAN KEUANGAN & REKAPITULASI OPERASIONAL']);
            fputcsv($handle, ["Periode: {$startDate} s/d {$endDate}"]);
            fputcsv($handle, ['Tanggal Cetak: ' . Carbon::now()->translatedFormat('d F Y H:i:s')]);
            fputcsv($handle, []);

            // 2. RINGKASAN EKSEKUTIF LABA BERSIH
            fputcsv($handle, ['=== RINGKASAN KINERJA KEUANGAN (NP = R - E) ===']);
            fputcsv($handle, ['Komponen', 'Nominal (Rp)', 'Keterangan']);
            fputcsv($handle, ['Total Omzet Penjualan (R)', $summary['revenue'], 'Order lunas non-batal']);
            fputcsv($handle, ['Beban Kemasan & Produksi', $summary['breakdown_expenses']['kemasan_produksi'], 'Tutup galon, tisu, galon kosong']);
            fputcsv($handle, ['Beban Operasional & Logistik', $summary['breakdown_expenses']['operasional_logistik'], 'Bensin, servis, penggantian filter/UV']);
            fputcsv($handle, ['Beban Beban Tetap', $summary['breakdown_expenses']['beban_tetap'], 'Listrik, air baku tangki, sewa']);
            fputcsv($handle, ['Total Beban Operasional (E)', $summary['expenses'], 'Akumulasi seluruh beban']);
            fputcsv($handle, [
                'Laba Bersih (Net Profit: NP)',
                $summary['net_profit'],
                $summary['is_profit'] ? 'SURPLUS / LABA' : 'DEFISIT / RUGI'
            ]);
            fputcsv($handle, ['Total Galon Terjual', $summary['total_gallons'] . ' Galon', '']);
            fputcsv($handle, ['Total Transaksi Selesai', $summary['total_orders'] . ' Transaksi', '']);
            fputcsv($handle, []);

            // 3. TREN PENJUALAN HARIAN
            fputcsv($handle, ['=== REKAPITULASI TREN HARIAN ===']);
            fputcsv($handle, ['Tanggal', 'Omzet Penjualan (Rp)', 'Galon Terjual (Unit)', 'Jumlah Transaksi']);
            foreach ($summary['daily_trend'] as $day) {
                fputcsv($handle, [
                    $day['date'],
                    $day['revenue'],
                    $day['total_gallons'],
                    $day['orders_count']
                ]);
            }
            fputcsv($handle, []);

            // 4. RINCIAN PENGELUARAN OPERASIONAL
            fputcsv($handle, ['=== RINCIAN PENGELUARAN OPERASIONAL (EXPENSES) ===']);
            fputcsv($handle, ['Tanggal', 'Kategori', 'Judul Pengeluaran', 'Nominal (Rp)', 'Catatan / Keterangan']);
            foreach ($expenses as $exp) {
                fputcsv($handle, [
                    $exp->expense_date->format('Y-m-d'),
                    str_replace('_', ' ', strtoupper($exp->category)),
                    $exp->title,
                    $exp->amount,
                    $exp->description ?? '-'
                ]);
            }
            fputcsv($handle, []);

            // 5. RINCIAN TRANSAKSI PENJUALAN
            fputcsv($handle, ['=== DAFTAR TRANSAKSI PENJUALAN (LUNAS) ===']);
            fputcsv($handle, ['No. Order', 'Waktu Transaksi', 'Tipe Pesanan', 'Nama Pelanggan', 'Metode Bayar', 'Total Tagihan (Rp)']);
            foreach ($orders as $ord) {
                fputcsv($handle, [
                    'SW-' . str_pad($ord->id, 5, '0', STR_PAD_LEFT),
                    $ord->created_at->format('Y-m-d H:i'),
                    str_replace('_', ' ', strtoupper($ord->order_type)),
                    $ord->customer->name ?? 'Pelanggan Umum',
                    strtoupper($ord->payment_method),
                    $ord->total_amount
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}