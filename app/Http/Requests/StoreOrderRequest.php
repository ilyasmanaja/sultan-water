<?php

namespace App\Http\Requests;

use App\Models\Customer;
use App\Models\PriceTier;
use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreOrderRequest extends FormRequest
{
    /**
     * Tentukan apakah user memiliki izin untuk request ini.
     */
    public function authorize(): bool
    {
        return auth()->check() && in_array(auth()->user()->role, ['admin', 'kasir']);
    }

    /**
     * Aturan validasi dasar menggunakan notasi array Laravel.
     * Mengacu ke DOC-VAL-POS-02 v1.3 Bagian 2.A
     */
    public function rules(): array
    {
        return [
            'order_type' => ['required', 'in:ambil_sendiri,pesan_antar'],
            'customer_id' => ['nullable', 'exists:customers,id'],

            // Quick Add Pelanggan Baru
            'new_customer' => ['nullable', 'array'],
            'new_customer.name' => ['required_with:new_customer', 'nullable', 'string', 'max:255'],
            'new_customer.whatsapp_number' => [
                'required_with:new_customer',
                'nullable',
                'regex:/^(08|628)[0-9]{8,13}$/',
            ],
            'new_customer.address' => ['nullable', 'string', 'min:5'],

            // Validasi Pembayaran (S2-BE-04)
            'payment_method' => ['required', 'in:tunai,qris'],

            // Validasi Item Transaksi (S2-BE-01 & S2-BE-02)
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.price_tier_id' => ['required', 'exists:price_tiers,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.gallon_action' => ['required', 'in:tukar_seimbang,pinjam,kembalikan,tidak_ada'],
            'items.*.gallon_qty' => [
                'nullable',
                'integer',
                function ($attribute, $value, $fail) {
                    $index = explode('.', $attribute)[1] ?? null;
                    $action = request()->input("items.{$index}.gallon_action");

                    if (in_array($action, ['pinjam', 'kembalikan'])) {
                        if (empty($value) || (int) $value < 1) {
                            $fail('Jumlah galon yang dipinjam/dikembalikan minimal 1.');
                        }
                    }
                },
            ],
        ];
    }

    /**
     * Pesan error kustom persis mengikuti DOC-VAL-POS-02 v1.3.
     */
    public function messages(): array
    {
        return [
            'order_type.required' => 'Tipe pesanan wajib dipilih (ambil sendiri / pesan antar).',
            'order_type.in' => 'Tipe pesanan wajib dipilih (ambil sendiri / pesan antar).',

            'new_customer.name.required_with' => 'Nama pelanggan baru wajib diisi.',
            'new_customer.whatsapp_number.regex' => 'Nomor WhatsApp tidak valid (minimal 10-15 digit angka).',
            'new_customer.address.min' => 'Alamat pengantaran wajib diisi untuk layanan pesan antar.',

            'payment_method.required' => 'Metode pembayaran wajib dipilih (tunai / QRIS).',
            'payment_method.in' => 'Metode pembayaran wajib dipilih (tunai / QRIS).',

            'items.required' => 'Minimal satu item transaksi harus diisi.',
            'items.min' => 'Minimal satu item transaksi harus diisi.',

            'items.*.product_id.required' => 'Produk tidak valid.',
            'items.*.product_id.exists' => 'Produk tidak valid.',

            'items.*.price_tier_id.required' => 'Tier harga tidak valid atau tidak sesuai jenis produk.',
            'items.*.price_tier_id.exists' => 'Tier harga tidak valid atau tidak sesuai jenis produk.',

            'items.*.quantity.required' => 'Jumlah item minimal 1.',
            'items.*.quantity.integer' => 'Jumlah item minimal 1.',
            'items.*.quantity.min' => 'Jumlah item minimal 1.',

            'items.*.gallon_action.required' => 'Opsi galon fisik tidak valid.',
            'items.*.gallon_action.in' => 'Opsi galon fisik tidak valid.',
        ];
    }

    /**
     * Validasi lanjutan lintas field & integritas bisnis.
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                // Lewati jika validasi dasar gagal untuk mencegah error null reference
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $orderType = $this->input('order_type');
                $customerId = $this->input('customer_id');
                $newCustomer = $this->input('new_customer');
                $items = $this->input('items', []);

                $hasRegisteredCustomer = !empty($customerId);
                $hasQuickAdd = !empty($newCustomer['name']) && !empty($newCustomer['whatsapp_number']);
                $hasValidCustomer = $hasRegisteredCustomer || $hasQuickAdd;

                $hasLoanAction = false;
                $totalReturnQty = 0;

                foreach ($items as $item) {
                    $action = $item['gallon_action'] ?? 'tidak_ada';
                    $qty = (int) ($item['gallon_qty'] ?? 0);

                    if (in_array($action, ['pinjam', 'kembalikan'])) {
                        $hasLoanAction = true;
                    }
                    if ($action === 'kembalikan') {
                        $totalReturnQty += $qty;
                    }
                }

                // 1. Validasi Identitas Pelanggan (TC-VAL-02, TC-VAL-03, TC-VAL-10 / TC-GAL-07, TC-GAL-08)
                if (($orderType === 'pesan_antar' || $hasLoanAction) && !$hasValidCustomer) {
                    $validator->errors()->add(
                        'customer_id',
                        'Pelanggan wajib dipilih jika meminjam atau mengembalikan galon.'
                    );
                }

                // Wajib alamat jika pesan antar dan memakai Quick Add
                if ($orderType === 'pesan_antar' && $hasQuickAdd && empty($newCustomer['address'])) {
                    $validator->errors()->add(
                        'new_customer.address',
                        'Alamat pengantaran wajib diisi untuk layanan pesan antar.'
                    );
                }

                // 2. Validasi Konsistensi Product, Price Tier, dan Gallon Action (TC-VAL-07, TC-VAL-09, TC-VAL-13)
                $productIds = collect($items)->pluck('product_id')->filter()->unique();
                $priceTierIds = collect($items)->pluck('price_tier_id')->filter()->unique();

                $products = Product::whereIn('id', $productIds)->get()->keyBy('id');
                $priceTiers = PriceTier::whereIn('id', $priceTierIds)->get()->keyBy('id');

                $calculatedTotalAmount = 0;

                foreach ($items as $index => $item) {
                    $product = $products->get($item['product_id']);
                    $priceTier = $priceTiers->get($item['price_tier_id']);
                    $itemQty = (int) ($item['quantity'] ?? 0);

                    if (!$product || !$priceTier) {
                        continue;
                    }

                    // Tier harga harus aktif (TC-VAL-09)
                    if (!$priceTier->is_active) {
                        $validator->errors()->add(
                            "items.{$index}.price_tier_id",
                            'Tier harga tidak valid atau tidak sesuai jenis produk.'
                        );
                    }

                    // Tier harga harus cocok dengan tipe produk (TC-VAL-07)
                    if ($priceTier->applies_to !== $product->type) {
                        $validator->errors()->add(
                            "items.{$index}.price_tier_id",
                            'Tier harga tidak valid atau tidak sesuai jenis produk.'
                        );
                    }

                    // Khusus produk galon_baru: gallon_action wajib 'tidak_ada' (TC-VAL-13)
                    if ($product->type === 'galon_baru' && ($item['gallon_action'] ?? '') !== 'tidak_ada') {
                        $validator->errors()->add(
                            "items.{$index}.gallon_action",
                            'Galon baru tidak dapat memakai opsi tukar, pinjam, atau kembalikan.'
                        );
                    }

                    // Hitung total harga server-side untuk proteksi pembayaran
                    $calculatedTotalAmount += ($itemQty * (float) $priceTier->price);
                }

                // 3. Proteksi Saldo Pinjaman Pelanggan (TC-VAL-04 / TC-GAL-06)
                if ($totalReturnQty > 0) {
                    $customerBorrowed = 0;

                    if ($hasRegisteredCustomer) {
                        $customer = Customer::find($customerId);
                        $customerBorrowed = $customer ? (int) $customer->borrowed_gallons : 0;
                    }
                    // Jika pelanggan baru via Quick Add, saldo pinjaman awalnya adalah 0

                    if ($totalReturnQty > $customerBorrowed) {
                        $validator->errors()->add(
                            'items',
                            "Jumlah pengembalian ({$totalReturnQty}) melebihi galon yang sedang dipinjam pelanggan ({$customerBorrowed} unit)."
                        );
                    }
                }
            },
        ];
    }
}