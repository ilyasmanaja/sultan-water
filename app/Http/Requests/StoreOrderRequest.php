<?php

namespace App\Http\Requests;

use App\Models\PriceTier;
use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * [S2-BE-01] Validasi transaksi isi ulang walk-in.
 * Acuan: DOC-VAL-POS-02 v1.3 §2.A & §3, BR-INV-02 v2.1 §4-5.
 *
 * Cakupan Sprint 2: hanya order_type 'ambil_sendiri', hanya produk 'isi_ulang',
 * quantity minimal 1. Quick Add (new_customer.*), pesan antar, dan retur murni
 * adalah cakupan tiket lain.
 */
class StoreOrderRequest extends FormRequest
{
    private const LOAN_ACTIONS = ['pinjam', 'kembalikan'];

    /** Baris item yang sudah dinormalisasi server (diisi di after-validation). */
    private array $lines = [];
    private float $total = 0;

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'order_type'            => ['required', 'in:ambil_sendiri, pesan_antar'],
            'customer_id'           => ['nullable', 'exists:customers,id'],
            'payment_method'        => ['required', 'in:tunai,qris'],
            'cash_received'         => ['required_if:payment_method,tunai', 'nullable', 'numeric', 'min:0'],
            'items'                 => ['required', 'array', 'min:1'],
            'items.*.product_id'    => ['required', 'exists:products,id'],
            'items.*.price_tier_id' => ['required', 'exists:price_tiers,id'],
            'items.*.quantity'      => ['required', 'integer', 'min:1'],
            'items.*.gallon_action' => ['required', 'in:tukar_seimbang,pinjam,kembalikan,tidak_ada'],
            'items.*.gallon_qty'    => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'order_type.required'            => 'Tipe pesanan wajib dipilih (ambil sendiri / pesan antar).',
            'order_type.in'                  => 'Tipe pesanan wajib dipilih (ambil sendiri / pesan antar).',
            'customer_id.exists'             => 'Pelanggan tidak ditemukan.',
            'payment_method.required'        => 'Metode pembayaran wajib dipilih (tunai / QRIS).',
            'payment_method.in'              => 'Metode pembayaran wajib dipilih (tunai / QRIS).',
            'cash_received.required_if'      => 'Uang tunai yang diterima kurang dari total tagihan.',
            'items.required'                 => 'Minimal satu item transaksi harus diisi.',
            'items.min'                      => 'Minimal satu item transaksi harus diisi.',
            'items.*.product_id.required'    => 'Produk tidak valid.',
            'items.*.product_id.exists'      => 'Produk tidak valid.',
            'items.*.price_tier_id.required' => 'Tier harga tidak valid atau tidak sesuai jenis produk.',
            'items.*.price_tier_id.exists'   => 'Tier harga tidak valid atau tidak sesuai jenis produk.',
            'items.*.quantity.required'      => 'Jumlah item minimal 1.',
            'items.*.quantity.integer'       => 'Jumlah item minimal 1.',
            'items.*.quantity.min'           => 'Jumlah item minimal 1.',
            'items.*.gallon_action.required' => 'Opsi galon fisik tidak valid.',
            'items.*.gallon_action.in'       => 'Opsi galon fisik tidak valid.',
            'items.*.gallon_qty.integer'     => 'Jumlah galon yang dipinjam/dikembalikan minimal 1.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            // Cek lintas field hanya jika rule dasar sudah lolos.
            if ($v->errors()->isNotEmpty()) {
                return;
            }
            $this->crossFieldChecks($v);
        });
    }

    private function crossFieldChecks(Validator $v): void
    {
        $items    = $this->input('items');
        $products = Product::whereIn('id', collect($items)->pluck('product_id'))->get()->keyBy('id');
        $tiers    = PriceTier::whereIn('id', collect($items)->pluck('price_tier_id'))->get()->keyBy('id');

        $needsCustomer = false;
        $lines = [];
        $total = 0.0;

        foreach ($items as $i => $item) {
            $product = $products[$item['product_id']];
            $tier    = $tiers[$item['price_tier_id']];
            $action  = $item['gallon_action'];
            $qty     = (int) $item['quantity'];

            // Endpoint ini khusus isi ulang; galon baru = S2-BE-02.
            if ($product->type !== 'isi_ulang') {
                $v->errors()->add("items.$i.product_id", 'Produk tidak valid.');
                continue;
            }
            // Tier harus aktif dan cocok dengan tipe produk (checklist §3.C).
            if (! $tier->is_active || $tier->applies_to !== 'isi_ulang') {
                $v->errors()->add("items.$i.price_tier_id", 'Tier harga tidak valid atau tidak sesuai jenis produk.');
                continue;
            }

            // gallon_qty ditentukan server (BR-INV-02 §4.6).
            $gallonQty = match ($action) {
                'tukar_seimbang'       => $qty,                          // = quantity, kiriman klien diabaikan
                'pinjam', 'kembalikan' => (int) ($item['gallon_qty'] ?? 0),
                default                => 0,                             // tidak_ada
            };

            if (in_array($action, self::LOAN_ACTIONS, true)) {
                $needsCustomer = true;
                if ($gallonQty < 1) {
                    $v->errors()->add("items.$i.gallon_qty", 'Jumlah galon yang dipinjam/dikembalikan minimal 1.');
                    continue;
                }
            }

            $unitPrice = (float) $tier->price;   // snapshot harga saat transaksi
            $subtotal  = $qty * $unitPrice;
            $total    += $subtotal;

            $lines[] = [
                'product_id'    => $product->id,
                'price_tier_id' => $tier->id,
                'quantity'      => $qty,
                'unit_price'    => $unitPrice,
                'subtotal'      => $subtotal,
                'gallon_action' => $action,
                'gallon_qty'    => $gallonQty,
            ];
        }

        if ($needsCustomer && blank($this->input('customer_id'))) {
            $v->errors()->add('customer_id', 'Pelanggan wajib dipilih jika meminjam atau mengembalikan galon.');
        }

        if ($this->input('payment_method') === 'tunai'
            && (float) $this->input('cash_received') < $total) {
            $v->errors()->add('cash_received', 'Uang tunai yang diterima kurang dari total tagihan.');
        }

        $this->lines = $lines;
        $this->total = $total;
    }

    /** Item hasil normalisasi server (harga, subtotal, gallon_qty). */
    public function lines(): array
    {
        return $this->lines;
    }

    /** total_amount hasil hitung server. */
    public function total(): float
    {
        return $this->total;
    }
}