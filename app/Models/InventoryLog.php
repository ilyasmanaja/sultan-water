<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryLog extends Model
{
    public const UPDATED_AT = null; // Disable the updated_at timestamp

    protected $fillable = [
        'inventory_id',
        'change_amount',
        'current_stock', // 👈 WAJIB DITAMBAHKAN
        'reason',
        'order_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'change_amount' => 'integer',
            'current_stock' => 'integer', // 👈 Cast ke integer
        ];
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}