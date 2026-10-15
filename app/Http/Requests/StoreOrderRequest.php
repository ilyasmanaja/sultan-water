<?php

namespace App\Http\Requests;

use App\Models\Customer;
use App\Models\PriceTier;
use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && in_array(auth()->user()->role, ['admin', 'kasir']);
    }

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

            'payment_method' => ['required', 'in:tunai,qris'],

            // Validasi Item Transaksi
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            
            // price_tier_id boleh null jika quantity = 0 (retur galon murni)
            'items.*.price_tier_id' => ['nullable', 'exists:price_tiers,id'],
            
            // quantity boleh 0 khusus retur galon murni
            'items.*.quantity' => ['required', 'integer', 'min:0'],
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

            'items.*.price_tier_id.exists' => 'Tier harga tidak valid atau tidak sesuai jenis produk.',

            'items.*.quantity.required' => 'Jumlah item minimal 0.',
            'items.*.quantity.integer' => 'Jumlah item minimal 0.',
            'items.*.quantity.min' => 'Jumlah item minimal 0.',

            'items.*.gallon_action.required' => 'Opsi galon fisik tidak valid.',
            'items.*.gallon_action.in' => 'Opsi galon fisik tidak valid.',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
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

                // 1. Validasi Wajib Identitas Pelanggan (TC-VAL-02, TC-VAL-03, TC-VAL-10)
                if (($orderType === 'pesan_antar' || $hasLoanAction) && !$hasValidCustomer) {
                    $validator->errors()->add(
                        'customer_id',
                        'Pelanggan wajib dipilih jika meminjam atau mengembalikan galon.'
                    );
                }

                // Validasi Alamat Wajib untuk Pesan Antar (S3-BE-01)
                if ($orderType === 'pesan_antar') {
                    if ($hasQuickAdd && empty(trim($newCustomer['address'] ?? ''))) {
                        $validator->errors()->add(
                            'new_customer.address',
                            'Alamat pengantaran wajib diisi untuk layanan pesan antar.'
                        );
                    } elseif ($hasRegisteredCustomer) {
                        $existingCust = Customer::find($customerId);
                        if (!$existingCust || empty(trim($existingCust->address ?? ''))) {
                            $validator->errors()->add(
                                'customer_id',
                                'Alamat pengantaran pelanggan wajib terdaftar untuk layanan pesan antar.'
                            );
                        }
                    }
                }

                // 2. Validasi Relasi Product, Price Tier, Quantity, dan Gallon Action
                $productIds = collect($items)->pluck('product_id')->filter()->unique();
                $priceTierIds = collect($items)->pluck('price_tier_id')->filter()->unique();

                $products = Product::whereIn('id', $productIds)->get()->keyBy('id');
                $priceTiers = PriceTier::whereIn('id', $priceTierIds)->get()->keyBy('id');

                foreach ($items as $index => $item) {
                    $product = $products->get($item['product_id']);
                    $itemQty = (int) ($item['quantity'] ?? 0);
                    $gallonAction = $item['gallon_action'] ?? '';
                    $tierId = $item['price_tier_id'] ?? null;

                    if (!$product) {
                        continue;
                    }

                    // Aturan Retur Galon Murni (quantity = 0) (S3-BE-03)
                    if ($itemQty === 0) {
                        if ($gallonAction !== 'kembalikan') {
                            $validator->errors()->add(
                                "items.{$index}.quantity",
                                'Jumlah item 0 hanya diizinkan untuk opsi retur galon pinjaman (kembalikan).'
                            );
                        }
                        if ((int) ($item['gallon_qty'] ?? 0) < 1) {
                            $validator->errors()->add(
                                "items.{$index}.gallon_qty",
                                'Jumlah galon yang dikembalikan minimal 1.'
                            );
                        }
                        continue;
                    }

                    // Untuk quantity >= 1, price_tier_id wajib diisi
                    if (empty($tierId)) {
                        $validator->errors()->add(
                            "items.{$index}.price_tier_id",
                            'Tier harga tidak valid atau tidak sesuai jenis produk.'
                        );
                        continue;
                    }

                    $priceTier = $priceTiers->get($tierId);
                    if (!$priceTier) {
                        continue;
                    }

                    if (!$priceTier->is_active || $priceTier->applies_to !== $product->type) {
                        $validator->errors()->add(
                            "items.{$index}.price_tier_id",
                            'Tier harga tidak valid atau tidak sesuai jenis produk.'
                        );
                    }

                    if ($product->type === 'galon_baru' && $gallonAction !== 'tidak_ada') {
                        $validator->errors()->add(
                            "items.{$index}.gallon_action",
                            'Galon baru tidak dapat memakai opsi tukar, pinjam, atau kembalikan.'
                        );
                    }
                }

                // 3. Validasi Saldo Pinjaman (TC-VAL-04 / TC-GAL-06)
                if ($totalReturnQty > 0) {
                    $customerBorrowed = 0;
                    if ($hasRegisteredCustomer) {
                        $customer = Customer::find($customerId);
                        $customerBorrowed = $customer ? (int) $customer->borrowed_gallons : 0;
                    }

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