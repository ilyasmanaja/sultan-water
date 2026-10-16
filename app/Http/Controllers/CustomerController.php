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
            'name'            => ['required', 'string', 'max:255'],
            'whatsapp_number' => ['required', 'regex:/^(08|628)[0-9]{8,13}$/'],
            'address'         => ['nullable', 'string', 'min:5'],
        ], [
            'name.required'            => 'Nama pelanggan baru wajib diisi.',
            'whatsapp_number.required' => 'Nomor WhatsApp wajib diisi.',
            'whatsapp_number.regex'    => 'Nomor WhatsApp tidak valid (minimal 10-15 digit angka).',
            'address.min'              => 'Alamat pelanggan minimal 5 karakter.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors'  => $validator->errors(),
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
                'data'    => [
                    'id'               => $existingCustomer->id,
                    'name'             => $existingCustomer->name,
                    'whatsapp_number'  => $existingCustomer->whatsapp_number,
                    'address'          => $existingCustomer->address,
                    'borrowed_gallons' => (int) $existingCustomer->borrowed_gallons,
                ],
            ], 200);
        }

        // Buat pelanggan baru
        $customer = Customer::create([
            'name'             => $validated['name'],
            'whatsapp_number'  => $validated['whatsapp_number'],
            'address'          => $validated['address'] ?? null,
            'borrowed_gallons' => 0,
        ]);

        return response()->json([
            'success'     => true,
            'is_existing' => false,
            'message'     => 'Pelanggan baru berhasil ditambahkan.',
            'data'        => [
                'id'               => $customer->id,
                'name'             => $customer->name,
                'whatsapp_number'  => $customer->whatsapp_number,
                'address'          => $customer->address,
                'borrowed_gallons' => 0,
            ],
        ], 201);
    }
}