<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Services\InventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    /**
     * Menampilkan halaman form restock tutup galon & pembelian galon baru (S2-FE-05).
     */
    public function showRestockForm(): View
    {
        $capInventory = Inventory::where('item_type', InventoryService::ITEM_CAP)->firstOrFail();
        $fleetInventory = Inventory::where('item_type', InventoryService::ITEM_FLEET_TOTAL)->firstOrFail();

        return view('inventory.restock', [
            'capStock'   => $capInventory->quantity,
            'fleetTotal' => $fleetInventory->quantity,
        ]);
    }

    /**
     * Endpoint Restock Tutup Galon (S2-BE-05).
     * Selalu menambah baris tutup_galon dengan reason = 'restock_stok_jual'.
     */
    public function restockCap(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ], [
            'quantity.required' => 'Jumlah restock harus bilangan bulat minimal 1.',
            'quantity.integer'  => 'Jumlah restock harus bilangan bulat minimal 1.',
            'quantity.min'      => 'Jumlah restock harus bilangan bulat minimal 1.',
        ]);

        $updatedCap = $this->inventoryService->restockCap(
            (int) $validated['quantity'],
            (int) Auth::id()
        );

        $message = "Restock tutup galon berhasil (+{$validated['quantity']} unit). Stok tutup saat ini: {$updatedCap->quantity} unit.";

        if ($request->wantsJson()) {
            return response()->json([
                'message'       => $message,
                'item_type'     => $updatedCap->item_type,
                'current_stock' => $updatedCap->quantity,
            ], 200);
        }

        return redirect()
            ->route('inventory.restock')
            ->with('success_cap', $message)
            ->with('latest_cap_stock', $updatedCap->quantity);
    }

    /**
     * Endpoint Pembelian Galon Baru dari Distributor (S2-BE-05).
     * Menambah G_total (baris galon_kosong_depot) sebesar R.
     * Pilihan allocation murni menentukan reason di inventory_logs.
     */
    public function restockGallon(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'quantity'   => ['required', 'integer', 'min:1'],
            'allocation' => ['required', 'in:stok_dijual,armada_depot'],
        ], [
            'quantity.required'   => 'Jumlah restock harus bilangan bulat minimal 1.',
            'quantity.integer'    => 'Jumlah restock harus bilangan bulat minimal 1.',
            'quantity.min'        => 'Jumlah restock harus bilangan bulat minimal 1.',
            'allocation.required' => 'Alokasi restock wajib dipilih (Stok Dijual / Armada Depot).',
            'allocation.in'       => 'Alokasi restock wajib dipilih (Stok Dijual / Armada Depot).',
        ]);

        $updatedFleet = $this->inventoryService->restockGallon(
            (int) $validated['quantity'],
            $validated['allocation'],
            (int) Auth::id()
        );

        $allocationLabel = $validated['allocation'] === 'stok_dijual' ? 'Stok Dijual' : 'Armada Depot';
        $message = "Pembelian galon baru berhasil (+{$validated['quantity']} unit, alokasi: {$allocationLabel}). Total Galon Dimiliki saat ini: {$updatedFleet->quantity} unit.";

        if ($request->wantsJson()) {
            return response()->json([
                'message'       => $message,
                'item_type'     => $updatedFleet->item_type,
                'current_stock' => $updatedFleet->quantity,
            ], 200);
        }

        return redirect()
            ->route('inventory.restock')
            ->with('success_gallon', $message)
            ->with('latest_fleet_total', $updatedFleet->quantity);
    }
}