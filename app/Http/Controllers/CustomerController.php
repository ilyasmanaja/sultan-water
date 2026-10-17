<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    /**
     * S3-BE-05: Autocomplete Smart Search Pelanggan
     * GET /customers/search?q=...
     */
    public function search(Request $request): JsonResponse
    {
        $query = trim((string) $request->query('q', ''));

        // Jika input kurang dari 2 karakter, kembalikan array kosong tanpa error server
        if (mb_strlen($query) < 2) {
            return response()->json([], 200);
        }

        // Escape karakter wildcard SQL (% dan _)
        $escapedQuery = addcslashes($query, '%_');

        $customers = Customer::query()
            ->select('id', 'name', 'whatsapp_number', 'address', 'borrowed_gallons')
            ->where(function ($q) use ($escapedQuery) {
                $q->where('name', 'LIKE', "%{$escapedQuery}%")
                    ->orWhere('whatsapp_number', 'LIKE', "%{$escapedQuery}%");
            })
            ->orderBy('name', 'asc')
            ->limit(10)
            ->get();

        return response()->json($customers, 200);
    }

    /**
     * S3-BE-06: Quick Add Pelanggan Baru Langsung dari Transaksi
     * POST /customers/quick-add
     */
    public function quickStore(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'whatsapp_number' => ['required', 'regex:/^(08|628)[0-9]{8,13}$/'],
            'address' => ['nullable', 'string', 'min:5'],
        ], [
            'name.required' => 'Nama pelanggan baru wajib diisi.',
            'whatsapp_number.required' => 'Nomor WhatsApp wajib diisi.',
            'whatsapp_number.regex' => 'Nomor WhatsApp tidak valid (minimal 10-15 digit angka).',
            'address.min' => 'Alamat pelanggan minimal 5 karakter.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        // Cek apakah nomor WhatsApp sudah terdaftar (Pencegahan Duplikasi)
        $existingCustomer = Customer::where('whatsapp_number', $validated['whatsapp_number'])->first();

        if ($existingCustomer) {
            return response()->json([
                'success' => true,
                'is_existing' => true,
                'message' => 'Pelanggan dengan nomor WhatsApp ini sudah terdaftar.',
                'data' => [
                    'id' => $existingCustomer->id,
                    'name' => $existingCustomer->name,
                    'whatsapp_number' => $existingCustomer->whatsapp_number,
                    'address' => $existingCustomer->address,
                    'borrowed_gallons' => (int) $existingCustomer->borrowed_gallons,
                ],
            ], 200);
        }

        // Buat pelanggan baru
        $customer = Customer::create([
            'name' => $validated['name'],
            'whatsapp_number' => $validated['whatsapp_number'],
            'address' => $validated['address'] ?? null,
            'borrowed_gallons' => 0,
        ]);

        return response()->json([
            'success' => true,
            'is_existing' => false,
            'message' => 'Pelanggan baru berhasil ditambahkan.',
            'data' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'whatsapp_number' => $customer->whatsapp_number,
                'address' => $customer->address,
                'borrowed_gallons' => 0,
            ],
        ], 201);
    }

    /**
     * S4-BE-07 (Tiket #79): Agregasi Riwayat Pembelian Bulanan per Pelanggan.
     * GET /customers/{customer}/monthly-purchases?month=8&year=2026
     */
    public function monthlyPurchaseHistory(Request $request, Customer $customer): JsonResponse
    {
        $validated = $request->validate([
            'month' => ['nullable', 'integer', 'between:1,12'],
            'year' => ['nullable', 'integer', 'min:2020', 'max:2099'],
        ]);

        $month = (int) ($validated['month'] ?? now()->month);
        $year = (int) ($validated['year'] ?? now()->year);

        // Ambil transaksi selesai pelanggan pada bulan & tahun terpilih
        $orders = $customer->orders()
            ->with(['items.product', 'items.priceTier'])
            ->where('delivery_status', \App\Models\Order::STATUS_SELESAI)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->latest('created_at')
            ->get();

        $totalGallons = 0;
        $totalSpent = 0.0;
        $orderHistory = [];

        foreach ($orders as $order) {
            $totalSpent += (float) $order->total_amount;
            $orderGallonQty = 0;
            $itemsDetail = [];

            foreach ($order->items as $item) {
                $qty = (int) $item->quantity;
                $orderGallonQty += $qty;

                $itemsDetail[] = [
                    'product_name' => $item->product?->name ?? 'Air Minum',
                    'tier_name' => $item->priceTier?->name ?? 'Isi Ulang',
                    'tier_code' => $item->priceTier?->code ?? '-',
                    'quantity' => $qty,
                    'unit_price' => (float) $item->unit_price,
                    'subtotal' => (float) $item->subtotal,
                    'gallon_action' => $item->gallon_action,
                    'gallon_qty' => (int) $item->gallon_qty,
                ];
            }

            $totalGallons += $orderGallonQty;

            $orderHistory[] = [
                'order_id' => $order->id,
                'order_number' => 'SW-' . str_pad($order->id, 5, '0', STR_PAD_LEFT),
                'order_type' => $order->order_type,
                'payment_method' => $order->payment_method,
                'total_amount' => (float) $order->total_amount,
                'gallons' => $orderGallonQty,
                'created_at' => $order->created_at->format('Y-m-d H:i'),
                'items' => $itemsDetail,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'whatsapp_number' => $customer->whatsapp_number,
                    'borrowed_gallons' => (int) $customer->borrowed_gallons,
                ],
                'period' => [
                    'month' => $month,
                    'year' => $year,
                ],
                'total_gallons' => $totalGallons,
                'total_spent' => $totalSpent,
                'orders_count' => count($orderHistory),
                'orders' => $orderHistory,
            ],
        ], 200);
    }

    /**
     * Menampilkan halaman detail/profil pelanggan beserta riwayat transaksi.
     */
    public function show(\App\Models\Customer $customer): \Illuminate\View\View
    {
        return view('customers.show', compact('customer'));
    }

    /**
     * Menampilkan direktori pelanggan dan pemantauan galon dipinjam (Gp).
     * Mendukung filter pencarian, filter galon dipinjam, dan pengurutan.
     */
    public function index(Request $request): \Illuminate\View\View
    {
        $query = Customer::query();

        // Pencarian berdasarkan nama atau no WhatsApp
        if ($request->filled('q')) {
            $searchTerm = addcslashes($request->string('q'), '%_');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('whatsapp_number', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Filter tab: 'borrowing' (hanya yang sedang pinjam > 0), 'all' (semua pelanggan)
        $filter = $request->input('filter', 'borrowing');
        if ($filter === 'borrowing') {
            $query->where('borrowed_gallons', '>', 0);
        }

        // Pengurutan: default dari pinjaman terbanyak
        $sort = $request->input('sort', 'borrowed_desc');
        if ($sort === 'borrowed_desc') {
            $query->orderBy('borrowed_gallons', 'desc')->orderBy('name', 'asc');
        } elseif ($sort === 'name_asc') {
            $query->orderBy('name', 'asc');
        } else {
            $query->latest();
        }

        // Statistik agregat pemantauan
        $totalBorrowedActive = (int) Customer::sum('borrowed_gallons');
        $borrowerCount = Customer::where('borrowed_gallons', '>', 0)->count();
        $totalCustomers = Customer::count();

        $customers = $query->paginate(15)->withQueryString();

        return view('customers.index', compact(
            'customers',
            'totalBorrowedActive',
            'borrowerCount',
            'totalCustomers',
            'filter',
            'sort'
        ));
    }
}
