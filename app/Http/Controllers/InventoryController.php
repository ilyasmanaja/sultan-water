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

    /**
     * Endpoint JSON status stok tutup galon & low-stock alert untuk dikonsumsi frontend (Kasir POS & Admin).
     * GET /inventory/status/cap
     */
    public function capStatus(): JsonResponse
    {
        $status = $this->inventoryService->getCapStockStatus();

        return response()->json([
            'success' => true,
            'data'    => $status,
        ], 200);
    }

    /**
     * Endpoint pembaruan nilai ambang batas low_stock_threshold oleh Admin.
     * PUT /inventory/threshold
     */
    public function updateThreshold(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'low_stock_threshold' => ['required', 'integer', 'min:1'],
        ], [
            'low_stock_threshold.required' => 'Ambang batas minimum wajib diisi.',
            'low_stock_threshold.integer'  => 'Ambang batas minimum harus berupa bilangan bulat.',
            'low_stock_threshold.min'      => 'Ambang batas minimum harus minimal 1 unit.',
        ]);

        $updatedCap = $this->inventoryService->updateThreshold((int) $validated['low_stock_threshold']);

        $message = "Batas minimum peringatan stok tutup galon berhasil diperbarui menjadi {$updatedCap->low_stock_threshold} unit.";

        if ($request->wantsJson()) {
            return response()->json([
                'success'             => true,
                'message'             => $message,
                'low_stock_threshold' => $updatedCap->low_stock_threshold,
            ], 200);
        }

        return redirect()
            ->back()
            ->with('success', $message);
    }

    public function writeOffGallon(Request $request, InventoryService $inventoryService): RedirectResponse
    {
        $validated = $request->validate([
            'quantity'    => 'required|integer|min:1',
            'reason'      => 'required|string|max:255',
            'customer_id' => 'nullable|exists:customers,id',
        ]);

        $inventoryService->writeOffGallon(
            (int) $validated['quantity'],
            $validated['reason'],
            (int) Auth::id(),
            isset($validated['customer_id']) ? (int) $validated['customer_id'] : null
        );

        return redirect()->back()->with('success', "Berhasil mencatat {$validated['quantity']} galon rusak/afkir.");
    }
}
