<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PriceTier extends Model
{
    /**
     * The attributes that are mass assignable.
     * 
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'name',
        'price',
        'applies_to',
        'is_active',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var list<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean'
        ];
    }

    /**
     * Get the order items associated with the price tier.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }        
}