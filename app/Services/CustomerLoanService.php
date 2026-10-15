<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Validation\ValidationException;

class CustomerLoanService
{
    /**
     * Memperbarui saldo galon pinjaman pelanggan: Gp' = Gp + g_pinjam - g_kembali.
     * Menolak mutasi jika g_kembali melebihi Gp yang sedang aktif.
     *
     * @throws ValidationException
     */
    public function apply(Customer $customer, int $gPinjam, int $gKembali): Customer
    {
        // Kunci baris customer untuk mencegah race condition (BR-INV-02 §4.2)
        $lockedCustomer = Customer::where('id', $customer->id)
            ->lockForUpdate()
            ->firstOrFail();

        if ($gKembali > $lockedCustomer->borrowed_gallons) {
            throw ValidationException::withMessages([
                'items' => [
                    "Jumlah pengembalian ({$gKembali}) melebihi galon yang sedang dipinjam pelanggan ({$lockedCustomer->borrowed_gallons} unit).",
                ],
            ]);
        }

        $lockedCustomer->borrowed_gallons = $lockedCustomer->borrowed_gallons + $gPinjam - $gKembali;
        $lockedCustomer->save();

        return $lockedCustomer;
    }

    /**
     * Membalik mutasi pinjaman galon dari order yang dibatalkan (rollback).
     * Rumus pembalikan: Gp' = Gp - g_pinjam + g_kembali.
     *
     * @throws ValidationException
     */
    public function reverseOrder(Order $order): ?Customer
    {
        if (!$order->customer_id) {
            return null;
        }

        $lockedCustomer = Customer::where('id', $order->customer_id)
            ->lockForUpdate()
            ->firstOrFail();

        $gPinjam = 0;
        $gKembali = 0;

        foreach ($order->items as $item) {
            if ($item->gallon_action === 'pinjam') {
                $gPinjam += (int) $item->gallon_qty;
            } elseif ($item->gallon_action === 'kembalikan') {
                $gKembali += (int) $item->gallon_qty;
            }
        }

        if ($gPinjam === 0 && $gKembali === 0) {
            return $lockedCustomer;
        }

        $newBorrowedBalance = $lockedCustomer->borrowed_gallons - $gPinjam + $gKembali;

        if ($newBorrowedBalance < 0) {
            throw ValidationException::withMessages([
                'order' => [
                    "Pembalikan pinjaman galon menghasilkan saldo negatif ({$newBorrowedBalance}). Pembatalan pesanan ditolak.",
                ],
            ]);
        }

        $lockedCustomer->borrowed_gallons = $newBorrowedBalance;
        $lockedCustomer->save();

        return $lockedCustomer;
    }
}