<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    /**
     * The attributes that are mass assignable.
     * 
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'whatsapp_number',
        'address',
        'borrowed_gallons',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var list<string, string>
     */
    protected function casts(): array
    {
        return [
            'borrowed_gallons' => 'integer',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
