<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\CarbonPeriod;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Menghitung total omzet (R) dari pesanan lunas non-batal dalam rentang tanggal.
     */
    public function getRevenue(Carbon|string $startDate, Carbon|string $endDate): float
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        return (float) Order::query()
            ->where('payment_status', 'lunas')
            ->where('delivery_status', '!=', Order::STATUS_BATAL)
            ->whereBetween('created_at', [$start, $end])
            ->sum('total_amount');
    }

    /**
     * Menghitung total pengeluaran operasional (E) beserta breakdown 3 kategori:
     * - kemasan_produksi
     * - operasional_logistik (termasuk biaya filter & UV)
     * - beban_tetap
     */
    public function getExpenses(Carbon|string $startDate, Carbon|string $endDate): array
    {
        $start = Carbon::parse($startDate)->toDateString();
        $end = Carbon::parse($endDate)->toDateString();

        $categoryExpenses = Expense::query()
            ->select('category', DB::raw('SUM(amount) as total_amount'))
            ->whereBetween('expense_date', [$start, $end])
            ->groupBy('category')
            ->pluck('total_amount', 'category')
            ->toArray();

        $breakdown = [
            'kemasan_produksi'     => (float) ($categoryExpenses['kemasan_produksi'] ?? 0),
            'operasional_logistik' => (float) ($categoryExpenses['operasional_logistik'] ?? 0),
            'beban_tetap'          => (float) ($categoryExpenses['beban_tetap'] ?? 0),
        ];

        $totalExpenses = array_sum($breakdown);

        return [
            'total'     => (float) $totalExpenses,
            'breakdown' => $breakdown,
        ];
    }

    /**
     * Menghitung Laba Bersih (Net Profit: NP = R - E).
     * Menangani kondisi laba (positif) maupun rugi (negatif).
     */
    public function getNetProfit(Carbon|string $startDate, Carbon|string $endDate): array
    {
        $revenue = $this->getRevenue($startDate, $endDate);
        $expensesData = $this->getExpenses($startDate, $endDate);
        $totalExpenses = $expensesData['total'];

        $netProfit = $revenue - $totalExpenses;

        return [
            'revenue'            => $revenue,
            'expenses'           => $totalExpenses,
            'breakdown_expenses' => $expensesData['breakdown'],
            'net_profit'         => (float) $netProfit,
            'is_profit'          => $netProfit >= 0,
        ];
    }

    /**
     * Menghasilkan dataset tren penjualan harian untuk visualisasi grafik.
     * Mengisi tanggal yang tidak memiliki transaksi dengan nilai 0.
     */
    public function getDailySalesTrend(Carbon|string $startDate, Carbon|string $endDate): array
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        // 1. Agregasi Omzet dan Total Transaksi per Tanggal
        $dailyOrders = Order::query()
            ->selectRaw('DATE(created_at) as order_date, SUM(total_amount) as daily_revenue, COUNT(id) as orders_count')
            ->where('payment_status', 'lunas')
            ->where('delivery_status', '!=', Order::STATUS_BATAL)
            ->whereBetween('created_at', [$start, $end])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->get()
            ->keyBy('order_date');

        // 2. Agregasi Total Galon Terjual per Tanggal
        $dailyGallons = OrderItem::query()
            ->selectRaw('DATE(orders.created_at) as order_date, SUM(order_items.quantity) as total_gallons')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.payment_status', 'lunas')
            ->where('orders.delivery_status', '!=', Order::STATUS_BATAL)
            ->whereBetween('orders.created_at', [$start, $end])
            ->groupBy(DB::raw('DATE(orders.created_at)'))
            ->pluck('total_gallons', 'order_date')
            ->toArray();

        // 3. Bangun timeline runtut menggunakan CarbonPeriod
        $trend = [];
        $period = CarbonPeriod::create($start->toDateString(), $end->toDateString());

        foreach ($period as $date) {
            $dateString = $date->toDateString();
            $orderRecord = $dailyOrders->get($dateString);

            $trend[] = [
                'date'          => $dateString,
                'label'         => $date->translatedFormat('d M'),
                'revenue'       => (float) ($orderRecord?->daily_revenue ?? 0),
                'total_gallons' => (int) ($dailyGallons[$dateString] ?? 0),
                'orders_count'  => (int) ($orderRecord?->orders_count ?? 0),
            ];
        }

        return $trend;
    }

    /**
     * Ringkasan performa finansial dan operasional gabungan dalam satu paket.
     */
    public function getFinancialSummary(Carbon|string $startDate, Carbon|string $endDate): array
    {
        $profitData = $this->getNetProfit($startDate, $endDate);
        $trend = $this->getDailySalesTrend($startDate, $endDate);

        $totalGallons = array_sum(array_column($trend, 'total_gallons'));
        $totalOrders = array_sum(array_column($trend, 'orders_count'));

        return array_merge($profitData, [
            'period' => [
                'start_date' => Carbon::parse($startDate)->toDateString(),
                'end_date'   => Carbon::parse($endDate)->toDateString(),
            ],
            'total_gallons' => $totalGallons,
            'total_orders'  => $totalOrders,
            'daily_trend'   => $trend,
        ]);
    }
}