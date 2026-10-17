<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Inventory;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    /**
     * Menampilkan halaman Dashboard Pemilik Depot.
     */
    public function index(Request $request): View
    {
        // Periode default: awal bulan hingga akhir bulan berjalan
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate   = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        // 1. Agregasi Ringkasan Finansial (NP = R - E) via ReportService
        $financialSummary = $this->reportService->getFinancialSummary($startDate, $endDate);

        // 2. Pemantauan Makro Aset Armada Galon (BR-INV-02 §6.A)
        // G_total: Baris penampung 'galon_kosong_depot' pada tabel inventories
        $totalGalonOwned = (int) (Inventory::where('item_type', 'galon_kosong_depot')->value('quantity') ?? 0);
        
        // G_dipinjam: Akumulasi SUM(customers.borrowed_gallons)
        $borrowedGallons = (int) Customer::sum('borrowed_gallons');
        
        // G_standby: Total Galon Dimiliki - Galon Dipinjam
        $standbyGallons  = $totalGalonOwned - $borrowedGallons;

        // Guard: Penanda jika Total Galon atau Standby bernilai minus
        $gallonNeedsAudit = ($totalGalonOwned < 0 || $standbyGallons < 0);

        $gallonFleet = [
            'total_owned'  => $totalGalonOwned,
            'borrowed'     => $borrowedGallons,
            'standby'      => $standbyGallons,
            'needs_audit'  => $gallonNeedsAudit,
        ];

        // 3. Status Stok Tutup Galon & Low-Stock Alert (Tiket #76 / #77)
        $capInventory = Inventory::where('item_type', 'tutup_galon')->first();
        $capStock     = $capInventory ? (int) $capInventory->quantity : 0;
        $capThreshold = $capInventory && $capInventory->low_stock_threshold ? (int) $capInventory->low_stock_threshold : 600;
        $isLowStock   = $capStock <= $capThreshold;

        $capStatus = [
            'stock'        => $capStock,
            'threshold'    => $capThreshold,
            'is_low_stock' => $isLowStock,
        ];

        // 4. Transformasi Dataset Harian untuk Visualisasi Chart.js
        $dailyTrend   = $financialSummary['daily_trend'] ?? [];
        $chartLabels  = [];
        $chartRevenue = [];
        $chartGallons = [];

        foreach ($dailyTrend as $day) {
            $chartLabels[]  = Carbon::parse($day['date'])->translatedFormat('d M');
            $chartRevenue[] = (float) $day['revenue'];
            $chartGallons[] = (int) $day['total_gallons'];
        }

        $chartData = [
            'labels'  => $chartLabels,
            'revenue' => $chartRevenue,
            'gallons' => $chartGallons,
        ];

        return view('admin.dashboard', compact(
            'financialSummary',
            'gallonFleet',
            'capStatus',
            'chartData',
            'startDate',
            'endDate'
        ));
    }
}