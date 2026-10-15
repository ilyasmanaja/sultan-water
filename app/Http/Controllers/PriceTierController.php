<?php

namespace App\Http\Controllers;

use App\Models\PriceTier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PriceTierController extends Controller
{
    /**
     * Menampilkan daftar seluruh master tier harga.
     */
    public function index(): JsonResponse
    {
        $tiers = PriceTier::orderBy('applies_to')->orderBy('price')->get();

        return response()->json([
            'success' => true,
            'data'    => $tiers,
        ]);
    }

    /**
     * Memperbarui nominal harga dan status aktif tier.
     * Kolom 'code' dan 'applies_to' bersifat immutable.
     *
     * @throws ValidationException
     */
    public function update(Request $request, PriceTier $priceTier): JsonResponse
    {
        $validated = $request->validate([
            'price'     => ['required', 'numeric', 'gt:0', 'max:99999999.99'],
            'is_active' => ['required', 'boolean'],
        ], [
            'price.required' => 'Nominal harga wajib diisi.',
            'price.numeric'  => 'Nominal harga harus berupa angka.',
            'price.gt'       => 'Nominal harga harus lebih besar dari 0.',
            'is_active.required' => 'Status aktif tier wajib ditentukan.',
        ]);

        // Guard: Setiap kategori (applies_to) minimal harus memiliki 1 tier aktif
        if (!$validated['is_active'] && $priceTier->is_active) {
            $otherActiveCount = PriceTier::where('applies_to', $priceTier->applies_to)
                ->where('is_active', true)
                ->where('id', '!=', $priceTier->id)
                ->count();

            if ($otherActiveCount === 0) {
                throw ValidationException::withMessages([
                    'is_active' => [
                        "Kategori {$priceTier->applies_to} minimal harus memiliki satu tier harga yang aktif.",
                    ],
                ]);
            }
        }

        $priceTier->update([
            'price'     => $validated['price'],
            'is_active' => $validated['is_active'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tier harga berhasil diperbarui.',
            'data'    => $priceTier,
        ]);
    }
}