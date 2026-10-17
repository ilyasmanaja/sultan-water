<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use App\Models\Order;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {
    }

    /**
     * Menampilkan halaman laporan finansial depot.
     * Default rentang: Awal bulan s/d akhir bulan berjalan.
     */
    public function index(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        $summary = $this->reportService->getFinancialSummary($startDate, $endDate);

        // Ambil daftar transaksi penjualan lunas non-batal dalam rentang tanggal
        $startDateTime = Carbon::parse($startDate)->startOfDay();
        $endDateTime = Carbon::parse($endDate)->endOfDay();

        $orders = Order::with(['customer', 'items.product', 'items.priceTier'])
            ->where('payment_status', 'lunas')
            ->where('delivery_status', '!=', Order::STATUS_BATAL)
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->latest('created_at')
            ->get();

        return view('reports.index', compact('summary', 'orders', 'startDate', 'endDate'));
    }

    /**
     * Endpoint API JSON khusus penyuplai kartu ringkasan & grafik (AJAX).
     */
    public function summary(Request $request): JsonResponse
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        $summary = $this->reportService->getFinancialSummary($startDate, $endDate);

        return response()->json([
            'success' => true,
            'data' => $summary,
        ], 200);
    }


}