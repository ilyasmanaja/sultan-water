<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Order;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {
    }

    /**
     * Ekspor Laporan Keuangan ke format PDF resmi.
     */
    public function exportPdf(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $summary = $this->reportService->getFinancialSummary($startDate, $endDate);

        $expenses = Expense::with('createdBy')
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->orderBy('expense_date', 'asc')
            ->get();

        $pdf = Pdf::loadView('reports.pdf', [
            'summary' => $summary,
            'expenses' => $expenses,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'printedAt' => Carbon::now()->translatedFormat('d F Y H:i'),
        ])->setPaper('a4', 'portrait');

        $filename = "Laporan-Keuangan-Sulthan-Air-Rebus_{$startDate}_sd_{$endDate}.pdf";

        return $pdf->download($filename);
    }

    /**
     * Ekspor Rekapitulasi Laporan ke format Excel asli (.xlsx) dengan styling rapi.
     */
    public function exportExcel(Request $request): StreamedResponse
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        $summary = $this->reportService->getFinancialSummary($startDate, $endDate);

        $startDateTime = Carbon::parse($startDate)->startOfDay();
        $endDateTime = Carbon::parse($endDate)->endOfDay();

        $orders = Order::with('customer')
            ->where('payment_status', 'lunas')
            ->where('delivery_status', '!=', Order::STATUS_BATAL)
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->oldest()
            ->get();

        $expenses = Expense::whereBetween('expense_date', [$startDate, $endDate])
            ->orderBy('expense_date', 'asc')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Keuangan');

        // Styling Presets
        $headerTableStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E293B']],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '64748B']]],
        ];

        $sectionStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => '0369A1'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E0F2FE']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '94A3B8']]],
        ];

        $cellBorderStyle = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
        ];

        $subtotalStyle = [
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F1F5F9']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '94A3B8']]],
        ];

        $rupiahFormat = '#,##0';

        // 1. KOP DOKUMEN
        $sheet->setCellValue('A1', 'DEPOT AIR MINUM SULTHAN AIR REBUS');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(15)->getColor()->setRGB('0369A1');

        $sheet->setCellValue('A2', 'Perumahan Griya Savana Suka Makmur Blok AD No 5, Pekanbaru, Riau');
        $sheet->getStyle('A2')->getFont()->setSize(10)->getColor()->setRGB('475569');

        $periodeText = Carbon::parse($startDate)->translatedFormat('d F Y') . ' s/d ' . Carbon::parse($endDate)->translatedFormat('d F Y');
        $sheet->setCellValue('A3', "Laporan Keuangan & Operasional | Periode: {$periodeText}");
        $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(10);

        $sheet->setCellValue('A4', 'Waktu Cetak: ' . Carbon::now()->translatedFormat('d F Y H:i'));
        $sheet->getStyle('A4')->getFont()->setSize(9)->getColor()->setRGB('64748B');

        // 2. BAGIAN I: RINGKASAN KEUANGAN
        $row = 6;
        $sheet->mergeCells("A{$row}:D{$row}");
        $sheet->setCellValue("A{$row}", 'I. RINGKASAN KEUANGAN DEPOT');
        $sheet->getStyle("A{$row}:D{$row}")->applyFromArray($sectionStyle);

        $row++;
        $sheet->setCellValue("A{$row}", 'Komponen Keuangan');
        $sheet->setCellValue("B{$row}", 'Nominal (Rp)');
        $sheet->mergeCells("C{$row}:D{$row}");
        $sheet->setCellValue("C{$row}", 'Keterangan');
        $sheet->getStyle("A{$row}:D{$row}")->applyFromArray($headerTableStyle);
        $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $summaryRows = [
            ['Total Omzet Penjualan', $summary['revenue'], 'Total pemasukan transaksi lunas', true, false],
            ['   • Kemasan & Produksi', $summary['breakdown_expenses']['kemasan_produksi'] ?? 0, 'Tutup galon segel, galon baru, tisu', false, false],
            ['   • Operasional & Logistik', $summary['breakdown_expenses']['operasional_logistik'] ?? 0, 'Bensin kurir, servis, ganti filter & lampu UV', false, false],
            ['   • Beban Tetap', $summary['breakdown_expenses']['beban_tetap'] ?? 0, 'Listrik depot, air baku tangki, sewa tempat', false, false],
            ['Total Pengeluaran Operasional', $summary['expenses'], 'Total seluruh pengeluaran operasional', true, true],
        ];

        foreach ($summaryRows as [$label, $val, $desc, $isBold, $isSubtotal]) {
            $row++;
            $sheet->setCellValue("A{$row}", $label);
            $sheet->setCellValue("B{$row}", (float) $val);
            $sheet->mergeCells("C{$row}:D{$row}");
            $sheet->setCellValue("C{$row}", $desc);

            $sheet->getStyle("A{$row}:D{$row}")->applyFromArray($isSubtotal ? $subtotalStyle : $cellBorderStyle);
            $sheet->getStyle("B{$row}")->getNumberFormat()->setFormatCode($rupiahFormat);
            if ($isBold) {
                $sheet->getStyle("A{$row}:B{$row}")->getFont()->setBold(true);
            }
        }

        // Baris Laba Bersih
        $row++;
        $sheet->setCellValue("A{$row}", 'LABA BERSIH DEPOT');
        $sheet->setCellValue("B{$row}", (float) $summary['net_profit']);
        $sheet->mergeCells("C{$row}:D{$row}");
        $sheet->setCellValue("C{$row}", $summary['is_profit'] ? 'UNTUNG / LABA' : 'RUGI / DEFISIT');

        $profitBg = $summary['is_profit'] ? 'DCFCE7' : 'FEE2E2';
        $profitColor = $summary['is_profit'] ? '166534' : '991B1B';
        $sheet->getStyle("A{$row}:D{$row}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => $profitColor]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $profitBg]],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '94A3B8']]],
        ]);
        $sheet->getStyle("B{$row}")->getNumberFormat()->setFormatCode($rupiahFormat);

        // Statistik Fisik
        $row++;
        $sheet->setCellValue("A{$row}", 'Total Galon Terjual');
        $sheet->setCellValue("B{$row}", number_format($summary['total_gallons'], 0, ',', '.') . ' Galon');
        $sheet->mergeCells("C{$row}:D{$row}");
        $sheet->setCellValue("C{$row}", 'Total transaksi lunas: ' . number_format($summary['total_orders'], 0, ',', '.') . ' transaksi');
        $sheet->getStyle("A{$row}:D{$row}")->applyFromArray($cellBorderStyle);
        $sheet->getStyle("A{$row}:B{$row}")->getFont()->setBold(true);
        $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // 3. BAGIAN II: REKAPITULASI PENJUALAN HARIAN
        $row += 2;
        $sheet->mergeCells("A{$row}:D{$row}");
        $sheet->setCellValue("A{$row}", 'II. REKAPITULASI PENJUALAN HARIAN');
        $sheet->getStyle("A{$row}:D{$row}")->applyFromArray($sectionStyle);

        $row++;
        $sheet->setCellValue("A{$row}", 'Tanggal');
        $sheet->setCellValue("B{$row}", 'Omzet Harian (Rp)');
        $sheet->setCellValue("C{$row}", 'Volume Galon');
        $sheet->setCellValue("D{$row}", 'Jumlah Transaksi');
        $sheet->getStyle("A{$row}:D{$row}")->applyFromArray($headerTableStyle);
        $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("C{$row}:D{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        foreach ($summary['daily_trend'] as $day) {
            $row++;
            $sheet->setCellValue("A{$row}", Carbon::parse($day['date'])->translatedFormat('d F Y'));
            $sheet->setCellValue("B{$row}", (float) $day['revenue']);
            $sheet->setCellValue("C{$row}", (int) $day['total_gallons'] . ' Galon');
            $sheet->setCellValue("D{$row}", (int) $day['orders_count'] . ' Transaksi');

            $sheet->getStyle("A{$row}:D{$row}")->applyFromArray($cellBorderStyle);
            $sheet->getStyle("B{$row}")->getNumberFormat()->setFormatCode($rupiahFormat);
            $sheet->getStyle("C{$row}:D{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        // 4. BAGIAN III: RINCIAN PENGELUARAN OPERASIONAL
        $row += 2;
        $sheet->mergeCells("A{$row}:E{$row}");
        $sheet->setCellValue("A{$row}", 'III. RINCIAN PENGELUARAN OPERASIONAL');
        $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($sectionStyle);

        $row++;
        $sheet->setCellValue("A{$row}", 'Tanggal');
        $sheet->setCellValue("B{$row}", 'Kategori');
        $sheet->setCellValue("C{$row}", 'Judul Pengeluaran');
        $sheet->setCellValue("D{$row}", 'Catatan / Keterangan');
        $sheet->setCellValue("E{$row}", 'Nominal (Rp)');
        $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($headerTableStyle);
        $sheet->getStyle("E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        if ($expenses->isEmpty()) {
            $row++;
            $sheet->mergeCells("A{$row}:E{$row}");
            $sheet->setCellValue("A{$row}", 'Tidak ada catatan pengeluaran pada periode ini.');
            $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($cellBorderStyle);
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        } else {
            foreach ($expenses as $exp) {
                $row++;
                $catLabel = match ($exp->category) {
                    'kemasan_produksi' => 'Kemasan & Produksi',
                    'operasional_logistik' => 'Operasional & Logistik',
                    default => 'Beban Tetap',
                };

                $sheet->setCellValue("A{$row}", $exp->expense_date->format('d/m/Y'));
                $sheet->setCellValue("B{$row}", $catLabel);
                $sheet->setCellValue("C{$row}", $exp->title);
                $sheet->setCellValue("D{$row}", $exp->description ?: '-');
                $sheet->setCellValue("E{$row}", (float) $exp->amount);

                $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($cellBorderStyle);
                $sheet->getStyle("E{$row}")->getNumberFormat()->setFormatCode($rupiahFormat);
            }
        }

        // 5. BAGIAN IV: DAFTAR TRANSAKSI PENJUALAN (LUNAS)
        $row += 2;
        $sheet->mergeCells("A{$row}:F{$row}");
        $sheet->setCellValue("A{$row}", 'IV. DAFTAR TRANSAKSI PENJUALAN (LUNAS)');
        $sheet->getStyle("A{$row}:F{$row}")->applyFromArray($sectionStyle);

        $row++;
        $sheet->setCellValue("A{$row}", 'No. Order');
        $sheet->setCellValue("B{$row}", 'Waktu Transaksi');
        $sheet->setCellValue("C{$row}", 'Tipe Layanan');
        $sheet->setCellValue("D{$row}", 'Nama Pelanggan');
        $sheet->setCellValue("E{$row}", 'Metode Bayar');
        $sheet->setCellValue("F{$row}", 'Total Tagihan (Rp)');
        $sheet->getStyle("A{$row}:F{$row}")->applyFromArray($headerTableStyle);
        $sheet->getStyle("E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("F{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        foreach ($orders as $ord) {
            $row++;
            $sheet->setCellValue("A{$row}", 'SW-' . str_pad($ord->id, 5, '0', STR_PAD_LEFT));
            $sheet->setCellValue("B{$row}", $ord->created_at->format('d/m/Y H:i'));
            $sheet->setCellValue("C{$row}", $ord->order_type === 'ambil_sendiri' ? 'Ambil Sendiri' : 'Pesan Antar');
            $sheet->setCellValue("D{$row}", $ord->customer->name ?? 'Pelanggan Umum');
            $sheet->setCellValue("E{$row}", strtoupper($ord->payment_method));
            $sheet->setCellValue("F{$row}", (float) $ord->total_amount);

            $sheet->getStyle("A{$row}:F{$row}")->applyFromArray($cellBorderStyle);
            $sheet->getStyle("E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("F{$row}")->getNumberFormat()->setFormatCode($rupiahFormat);
        }

        // Auto-size lebar kolom A sampai F agar tidak ada teks terpotong atau ########
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = "Laporan-Keuangan-Sulthan-Air-Rebus_{$startDate}_sd_{$endDate}.xlsx";

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Menampilkan layout laporan keuangan ramah cetak (print-friendly) ukuran A4.
     */
    public function printView(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $summary = $this->reportService->getFinancialSummary($startDate, $endDate);

        $startDateTime = Carbon::parse($startDate)->startOfDay();
        $endDateTime = Carbon::parse($endDate)->endOfDay();

        $orders = Order::with('customer')
            ->where('payment_status', 'lunas')
            ->where('delivery_status', '!=', Order::STATUS_BATAL)
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->oldest()
            ->get();

        $expenses = Expense::with('createdBy')
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->orderBy('expense_date', 'asc')
            ->get();

        return view('reports.print', [
            'summary' => $summary,
            'orders' => $orders,
            'expenses' => $expenses,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'printedAt' => Carbon::now()->translatedFormat('d F Y H:i'),
        ]);
    }
}