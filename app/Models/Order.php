<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class Order extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_DIANTAR = 'diantar';
    public const STATUS_SELESAI = 'selesai';
    public const STATUS_BATAL   = 'batal';

    /**
     * The attributes that are mass assignable.
     * 
     * @var list<string>
     */
    protected $fillable = [
        'customer_id',
        'order_type',
        'delivery_status',
        'payment_method',
        'payment_status',
        'total_amount',
        'created_by',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var list<string, string>
     */
    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
        ];
    }

    /**
     * Matriks transisi delivery_status yang valid.
     * Status 'selesai' dan 'batal' bersifat final (terminal).
     */
    public function canTransitionTo(string $targetStatus): bool
    {
        $validTransitions = [
            self::STATUS_PENDING => [self::STATUS_DIANTAR, self::STATUS_BATAL],
            self::STATUS_DIANTAR => [self::STATUS_SELESAI, self::STATUS_BATAL],
            self::STATUS_SELESAI => [],
            self::STATUS_BATAL   => [],
        ];

        return in_array($targetStatus, $validTransitions[$this->delivery_status] ?? [], true);
    }

    /**
     * Mengeksekusi transisi status pengantaran.
     *
     * @throws ValidationException
     */
    public function transitionTo(string $targetStatus): void
    {
        if (!$this->canTransitionTo($targetStatus)) {
            throw ValidationException::withMessages([
                'delivery_status' => [
                    "Transisi status tidak valid (dari {$this->delivery_status} ke {$targetStatus}).",
                ],
            ]);
        }

        $this->delivery_status = $targetStatus;
        $this->save();
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function orderItems(): HasMany
    {
        return $this->items();
    }
}