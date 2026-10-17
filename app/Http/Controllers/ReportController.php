<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    /**
     * Menampilkan halaman laporan finansial depot.
     * Default rentang: Awal bulan s/d akhir bulan berjalan.
     */
    public function index(Request $request): View|JsonResponse
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        $summary = $this->reportService->getFinancialSummary($startDate, $endDate);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data'    => $summary,
            ], 200);
        }

        return view('reports.index', compact('summary', 'startDate', 'endDate'));
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
            'data'    => $summary,
        ], 200);
    }
}