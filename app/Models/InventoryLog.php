<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryLog extends Model
{

    public const UPDATED_AT = null; // Disable the updated_at timestamp

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'inventory_id',
        'change_amount',
        'reason',
        'order_id',
        'created_by',
    ];

    /**
     * Get the attibutes that should be cast to native types.
     */
    protected $casts = [
        'change_amount' => 'integer'
    ];

    /**
     * Get the inventory item associated with the log.
     */
    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }

    /**
     * Get the order associated with the log.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the user who created the log.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
}
