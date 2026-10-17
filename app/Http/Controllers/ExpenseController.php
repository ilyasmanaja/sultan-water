<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    /**
     * Menampilkan daftar riwayat pengeluaran operasional.
     * Mendukung filter bulan, tahun, pencarian judul, dan pagination.
     */
    public function index(Request $request): View|JsonResponse
    {
        $query = Expense::with('createdBy')->latest('expense_date')->latest('id');

        // Filter berdasarkan bulan dan tahun (default: bulan & tahun saat ini)
        if ($request->filled('month')) {
            $query->whereMonth('expense_date', $request->integer('month'));
        }
        if ($request->filled('year')) {
            $query->whereYear('expense_date', $request->integer('year'));
        }

        // Filter berdasarkan kategori (kemasan_produksi, operasional_logistik, beban_tetap)
        if ($request->filled('category')) {
            $query->where('category', $request->string('category'));
        }

        // Pencarian berdasarkan judul pengeluaran
        if ($request->filled('q')) {
            $searchTerm = addcslashes($request->string('q'), '%_');
            $query->where('title', 'LIKE', "%{$searchTerm}%");
        }

        // Total akumulasi pengeluaran sesuai filter yang sedang aktif
        $totalAmount = (clone $query)->sum('amount');

        $expenses = $query->paginate(15)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'total_amount' => (float) $totalAmount,
                    'expenses' => $expenses,
                ],
            ], 200);
        }

        return view('expenses.index', compact('expenses', 'totalAmount'));
    }

    /**
     * Menyimpan data pengeluaran baru ke tabel expenses.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'title'        => ['required', 'string', 'max:100'],
            'category'     => ['required', 'in:kemasan_produksi,operasional_logistik,beban_tetap'],
            'amount'       => ['required', 'numeric', 'min:1'],
            'expense_date' => ['required', 'date'],
            'description'  => ['nullable', 'string', 'max:1000'],
        ], [
            'title.required'        => 'Judul pengeluaran wajib diisi.',
            'title.max'             => 'Judul pengeluaran maksimal 100 karakter.',
            'category.required'     => 'Kategori pengeluaran wajib dipilih.',
            'category.in'           => 'Kategori pengeluaran tidak valid (pilih: kemasan_produksi, operasional_logistik, atau beban_tetap).',
            'amount.required'       => 'Nominal pengeluaran wajib diisi.',
            'amount.numeric'        => 'Nominal pengeluaran harus berupa angka.',
            'amount.min'            => 'Nominal pengeluaran minimal Rp1.',
            'expense_date.required' => 'Tanggal pengeluaran wajib diisi.',
            'expense_date.date'     => 'Format tanggal pengeluaran tidak valid.',
            'description.max'       => 'Deskripsi pengeluaran maksimal 1000 karakter.',
        ]);

        $expense = Expense::create([
            'title'        => $validated['title'],
            'category'     => $validated['category'],
            'amount'       => $validated['amount'],
            'expense_date' => $validated['expense_date'],
            'description'  => $validated['description'] ?? null,
            'created_by'   => Auth::id(),
        ]);

        $message = "Pengeluaran \"{$expense->title}\" sebesar Rp " . number_format($expense->amount, 0, ',', '.') . " berhasil dicatat.";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'data'    => $expense,
            ], 201);
        }

        return redirect()
            ->route('admin.expenses.index')
            ->with('success', $message);
    }

    /**
     * Menghapus pencatatan pengeluaran.
     */
    public function destroy(Request $request, Expense $expense): RedirectResponse|JsonResponse
    {
        $title = $expense->title;
        $amount = $expense->amount;

        $expense->delete();

        $message = "Pengeluaran \"{$title}\" (Rp " . number_format($amount, 0, ',', '.') . ") berhasil dihapus.";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ], 200);
        }

        return redirect()
            ->route('admin.expenses.index')
            ->with('success', $message);
    }
}